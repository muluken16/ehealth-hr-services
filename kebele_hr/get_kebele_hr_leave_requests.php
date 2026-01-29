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
        leave_type VARCHAR(20) NOT NULL,
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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
}

// Get leave requests for employees in this kebele (check both kebele and working_kebele)
$result = $conn->query("SELECT lr.*, e.first_name, e.last_name, e.department_assigned as department 
                       FROM leave_requests lr 
                       JOIN employees e ON lr.employee_id = e.employee_id 
                       WHERE (e.kebele = '$kebele' OR e.working_kebele = '$kebele') AND lr.status = 'pending' 
                       ORDER BY lr.created_at DESC");

$leave_requests = [];
while ($row = $result->fetch_assoc()) {
    $leave_requests[] = $row;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($leave_requests);
?>