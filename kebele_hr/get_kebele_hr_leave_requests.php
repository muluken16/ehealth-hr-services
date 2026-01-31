<?php
session_start();
require_once dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

$kebele = $_SESSION['kebele'] ?? 'Kebele 1';

// Check if leave_requests table exists
$table_check = $conn->query("SHOW TABLES LIKE 'leave_requests'");
if ($table_check->num_rows === 0) {
    // Create the table if it doesn't exist
    $conn->query("CREATE TABLE IF NOT EXISTS leave_requests (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        employee_id VARCHAR(20) NOT NULL,
        leave_type ENUM('annual','sick','maternity','paternity','emergency','marriage','bereavement','special') NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        days_requested INT NOT NULL,
        reason TEXT,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        requested_by INT(6) UNSIGNED,
        rejection_reason TEXT,
        supporting_document VARCHAR(255),
        emergency_contact VARCHAR(100),
        leave_address TEXT,
        approved_by INT(6) UNSIGNED,
        approved_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
}

// Get leave requests with entitlements for current year
$year = date('Y');
$result = $conn->query("SELECT lr.*, e.first_name, e.last_name, e.department_assigned as department, e.phone_number,
                       le.annual_leave_days, le.used_annual_leave, le.carry_forward_days,
                       le.sick_leave_days, le.used_sick_leave,
                       le.maternity_leave_days, le.used_maternity_leave,
                       le.paternity_leave_days, le.used_paternity_leave,
                       le.emergency_leave_days, le.used_emergency_leave,
                       le.marriage_leave_days, le.used_marriage_leave,
                       le.bereavement_leave_days, le.used_bereavement_leave
                       FROM leave_requests lr 
                       JOIN employees e ON lr.employee_id = e.employee_id 
                       LEFT JOIN leave_entitlements le ON e.employee_id = le.employee_id AND le.year = $year
                       WHERE (e.kebele = '$kebele' OR e.working_kebele = '$kebele') AND lr.status = 'pending' 
                       ORDER BY lr.created_at DESC");

$leave_requests = [];
while ($row = $result->fetch_assoc()) {
    $type = strtolower($row['leave_type']);
    $remaining = 0;
    
    if ($type == 'annual') {
        $remaining = ($row['annual_leave_days'] + $row['carry_forward_days']) - $row['used_annual_leave'];
    } elseif (isset($row["{$type}_leave_days"])) {
        $remaining = $row["{$type}_leave_days"] - $row["used_{$type}_leave"];
    }
    
    $row['remaining_balance'] = $remaining;
    $row['leave_document'] = $row['supporting_document'];
    $leave_requests[] = $row;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($leave_requests);
?>