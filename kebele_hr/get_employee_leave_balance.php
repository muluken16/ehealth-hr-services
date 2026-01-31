<?php
session_start();
require_once dirname(__DIR__) . '/db.php';

header('Content-Type: application/json');

$conn = getDBConnection();
$employee_id = $_GET['employee_id'] ?? '';
$current_year = date('Y');

if (empty($employee_id)) {
    echo json_encode(['error' => 'Employee ID is required']);
    exit();
}

// Get employee details
$emp_stmt = $conn->prepare("SELECT employee_id, first_name, last_name, join_date, gender FROM employees WHERE employee_id = ?");
$emp_stmt->bind_param("s", $employee_id);
$emp_stmt->execute();
$emp_result = $emp_stmt->get_result();

if ($emp_result->num_rows === 0) {
    echo json_encode(['error' => 'Employee not found']);
    exit();
}

$employee = $emp_result->fetch_assoc();
$emp_stmt->close();

// Calculate service years
$join_date = new DateTime($employee['join_date']);
$current_date = new DateTime();
$diff = $current_date->diff($join_date);
$years_of_service = $diff->y;
$months_of_service = ($years_of_service * 12) + $diff->m;

// Base Entitlement Function (Ethiopian Labor Proclamation 1156/2019)
function calculateLegalBase($years)
{
    if ($years < 1) return 0;
    // 16 days for first year + 1 day for every 2 additional years
    return 16 + floor(($years - 1) / 2);
}

// Get or create leave entitlement
$entitlement_stmt = $conn->prepare("SELECT * FROM leave_entitlements WHERE employee_id = ? AND year = ?");
$entitlement_stmt->bind_param("si", $employee_id, $current_year);
$entitlement_stmt->execute();
$res = $entitlement_stmt->get_result();

if ($res->num_rows === 0) {
    $prev_year = $current_year - 1;
    $carry_forward = 0;

    // Check for carry forward (only if service >= 2 years)
    if ($years_of_service >= 2) {
        $p_stmt = $conn->prepare("SELECT (annual_leave_days + carry_forward_days - used_annual_leave) as unused FROM leave_entitlements WHERE employee_id = ? AND year = ?");
        $p_stmt->bind_param("si", $employee_id, $prev_year);
        $p_stmt->execute();
        $p_res = $p_stmt->get_result();
        if ($p_res->num_rows > 0) {
            $carry_forward = max(0, $p_res->fetch_assoc()['unused']);
        }
        $p_stmt->close();
    }

    $base = 16;
    $sick = 180;
    $mat = ($employee['gender'] === 'female') ? 120 : 0;
    $pat = ($employee['gender'] === 'male') ? 3 : 0;
    $emg = 3;
    $mar = 3;
    $ber = 3;

    $ins = $conn->prepare("INSERT INTO leave_entitlements (employee_id, year, annual_leave_days, carry_forward_days, sick_leave_days, maternity_leave_days, paternity_leave_days, emergency_leave_days, marriage_leave_days, bereavement_leave_days) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $ins->bind_param("siiiiiiiii", $employee_id, $current_year, $base, $carry_forward, $sick, $mat, $pat, $emg, $mar, $ber);
    $ins->execute();
    $ins->close();

    $entitlement_stmt->execute();
    $res = $entitlement_stmt->get_result();
}

$leave = $res->fetch_assoc();
$entitlement_stmt->close();

// Dynamic Tenure adjustment for UI logic (if not manually overridden)
$bonus = ($years_of_service >= 1) ? floor(($years_of_service - 1) / 2) : 0;
$annual_total = $leave['annual_leave_days'];

// If record is at default 16 or was 16+old_bonus, ensure it matches legal base
if ($years_of_service >= 1) {
    $legal_base = 16 + $bonus;
    // We update UI display if the DB version is the standard 16 (initial)
    if ($annual_total == 16 || $annual_total < $legal_base) {
        $annual_total = $legal_base;
    }
}

// Lock for probation
if ($years_of_service < 1) {
    $annual_total = 0;
    $leave['carry_forward_days'] = 0;
}

$grand_total_annual = $annual_total + $leave['carry_forward_days'];

// Get pending
$pending = ['annual' => 0, 'sick' => 0, 'maternity' => 0, 'paternity' => 0, 'emergency' => 0, 'marriage' => 0, 'bereavement' => 0];
$p_stmt = $conn->prepare("SELECT leave_type, SUM(days_requested) as pd FROM leave_requests WHERE employee_id = ? AND status = 'pending' GROUP BY leave_type");
$p_stmt->bind_param("s", $employee_id);
$p_stmt->execute();
$p_res = $p_stmt->get_result();
while ($row = $p_res->fetch_assoc()) {
    if (isset($pending[$row['leave_type']]))
        $pending[$row['leave_type']] = $row['pd'];
}

$response = [
    'employee' => [
        'id' => $employee['employee_id'],
        'name' => $employee['first_name'] . ' ' . $employee['last_name'],
        'join_date' => $employee['join_date'],
        'service_years' => $years_of_service,
        'gender' => $employee['gender']
    ],
    'leave_balance' => [
        'annual' => [
            'entitled' => $grand_total_annual,
            'base' => $annual_total,
            'carried' => $leave['carry_forward_days'],
            'used' => $leave['used_annual_leave'],
            'remaining' => $grand_total_annual - $leave['used_annual_leave'],
            'pending' => $pending['annual'],
            'is_eligible' => ($years_of_service >= 1)
        ],
        'sick' => [
            'entitled' => $leave['sick_leave_days'],
            'used' => $leave['used_sick_leave'],
            'remaining' => $leave['sick_leave_days'] - $leave['used_sick_leave'],
            'pending' => $pending['sick']
        ],
        'maternity' => [
            'entitled' => $leave['maternity_leave_days'],
            'used' => $leave['used_maternity_leave'],
            'remaining' => $leave['maternity_leave_days'] - $leave['used_maternity_leave'],
            'pending' => $pending['maternity']
        ],
        'paternity' => [
            'entitled' => $leave['paternity_leave_days'],
            'used' => $leave['used_paternity_leave'],
            'remaining' => $leave['paternity_leave_days'] - $leave['used_paternity_leave'],
            'pending' => $pending['paternity']
        ],
        'emergency' => [
            'entitled' => $leave['emergency_leave_days'],
            'used' => $leave['used_emergency_leave'],
            'remaining' => $leave['emergency_leave_days'] - $leave['used_emergency_leave'],
            'pending' => $pending['emergency']
        ],
        'marriage' => [
            'entitled' => $leave['marriage_leave_days'],
            'used' => $leave['used_marriage_leave'],
            'remaining' => $leave['marriage_leave_days'] - $leave['used_marriage_leave'],
            'pending' => $pending['marriage']
        ],
        'bereavement' => [
            'entitled' => $leave['bereavement_leave_days'],
            'used' => $leave['used_bereavement_leave'],
            'remaining' => $leave['bereavement_leave_days'] - $leave['used_bereavement_leave'],
            'pending' => $pending['bereavement']
        ]
    ]
];

echo json_encode($response);
$conn->close();
?>