<?php
session_start();
require_once dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

$woreda = 'Woreda 1'; // Default
$kebele = $_SESSION['kebele'] ?? 'Kebele 1';

// 1. Seed Employees
$result = $conn->query("SELECT COUNT(*) as count FROM employees WHERE kebele = '$kebele'");
if ($result->fetch_assoc()['count'] == 0) {
    $employees_data = [
        ['KB101', 'Kebede', 'Tadesse', 'Male', 'Degree', 'Medical', $kebele, 'Senior', 'active'],
        ['KB102', 'Almaz', 'Ayana', 'Female', 'Masters', 'Medical', $kebele, 'Junior', 'active'],
        ['KB103', 'Chala', 'Megersa', 'Male', 'Diploma', 'Admin', $kebele, 'Senior', 'on-leave'],
        ['KB104', 'Tirunesh', 'Dibaba', 'Female', 'PhD', 'Technical', $kebele, 'Lead', 'active'],
        ['KB105', 'Derartu', 'Tulu', 'Female', 'Degree', 'Support', $kebele, 'Junior', 'inactive']
    ];

    foreach ($employees_data as $emp) {
        $stmt = $conn->prepare("INSERT IGNORE INTO employees (employee_id, first_name, last_name, gender, education_level, department_assigned, kebele, woreda, job_level, status, join_date, date_of_birth) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), '1990-01-01')");
        $stmt->bind_param("ssssssssss", $emp[0], $emp[1], $emp[2], $emp[3], $emp[4], $emp[5], $emp[6], $woreda, $emp[7], $emp[8]);
        $stmt->execute();
        $stmt->close();
    }
}

echo json_encode(['success' => true, 'message' => 'Kebele sample data seeded successfully!']);
$conn->close();
?>
