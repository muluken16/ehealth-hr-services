<?php
session_start();
header('Content-Type: application/json');

require_once '../db.php';
$conn = getDBConnection();

$kebele = $_SESSION['kebele'] ?? 'Kebele 1';
$year = date('Y');

try {
    // 1. Initialize records for the current year with 2-Year Carry-Forward Policy
    $prev_year = $year - 1;
    $init_sql = "INSERT IGNORE INTO leave_entitlements 
                  (employee_id, year, annual_leave_days, carry_forward_days, sick_leave_days, maternity_leave_days, paternity_leave_days, emergency_leave_days)
                  SELECT 
                    e.employee_id, 
                    $year, 
                    16, 
                    COALESCE((SELECT (annual_leave_days + carry_forward_days - used_annual_leave) 
                                   FROM leave_entitlements prev 
                                   WHERE prev.employee_id = e.employee_id AND prev.year = $prev_year 
                                   AND TIMESTAMPDIFF(YEAR, e.join_date, CURDATE()) >= 2
                                   AND (annual_leave_days + carry_forward_days - used_annual_leave) > 0), 0), 
                    180, 
                    IF(e.gender='female', 120, 0), 
                    IF(e.gender='male', 3, 0), 
                    3 
                  FROM employees e
                  WHERE e.working_kebele = ? OR e.kebele = ?";

    $init_stmt = $conn->prepare($init_sql);
    $init_stmt->bind_param("ss", $kebele, $kebele);
    $init_stmt->execute();
    $init_stmt->close();

    // 2. Fetch all employees joining with entitlements
    $sql = "SELECT e.first_name, e.last_name, e.employee_id, e.department_assigned, e.gender, e.join_date,
                   TIMESTAMPDIFF(YEAR, e.join_date, CURDATE()) as service_years,
                   le.annual_leave_days, le.carry_forward_days, le.used_annual_leave, 
                   le.sick_leave_days, le.used_sick_leave, 
                   le.emergency_leave_days, le.used_emergency_leave,
                   le.maternity_leave_days, le.used_maternity_leave,
                   le.paternity_leave_days, le.used_paternity_leave
            FROM employees e
            JOIN leave_entitlements le ON e.employee_id = le.employee_id
            WHERE (e.kebele = ? OR e.working_kebele = ?) AND le.year = ?
            ORDER BY e.first_name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $kebele, $kebele, $year);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    $total_annual = 0;
    $low_balance_count = 0;

    while ($row = $result->fetch_assoc()) {
        // Tenure Adjustment Logic: 
        $years = intval($row['service_years']);
        $bonus_days = ($years >= 1) ? floor($years / 2) : 0;

        if ($years < 1) {
            $row['annual_leave_days'] = 0;
            $row['carry_forward_days'] = 0;
        } else {
            // If the DB has 16 or 21, ensure base is corrected but KEEP carry forward column
            if ($row['annual_leave_days'] == 16 || $row['annual_leave_days'] == 21) {
                $row['annual_leave_days'] = 16 + $bonus_days;
            }
        }

        $row['tenure_bonus'] = $bonus_days;
        $data[] = $row;

        $total_entitlement = $row['annual_leave_days'] + $row['carry_forward_days'];
        $remaining = $total_entitlement - $row['used_annual_leave'];
        $total_annual += ($remaining > 0 ? $remaining : 0);

        if ($remaining < 5 && $total_entitlement > 0) {
            $low_balance_count++;
        }
    }

    $avg_annual = count($data) > 0 ? $total_annual / count($data) : 0;

    echo json_encode([
        'success' => true,
        'data' => $data,
        'stats' => [
            'total_employees' => count($data),
            'avg_annual' => $avg_annual,
            'low_balance_count' => $low_balance_count
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>