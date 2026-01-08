<?php
session_start();
require_once dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

$woreda = $_SESSION['woreda'] ?? 'Woreda 1';

// 1. Seed Employees
$employees_data = [
    ['EMP101', 'Abebe', 'Bikila', 'Male', 'Degree', 'Medical', 'Kebele 1', 'Senior', 'active'],
    ['EMP102', 'Mulu', 'Tesfaye', 'Female', 'Masters', 'Medical', 'Kebele 2', 'Junior', 'active'],
    ['EMP103', 'Hanna', 'Selassie', 'Female', 'Diploma', 'Admin', 'Kebele 1', 'Senior', 'on-leave'],
    ['EMP104', 'Yohannes', 'Haile', 'Male', 'PhD', 'Technical', 'Kebele 3', 'Lead', 'active'],
    ['EMP105', 'Sara', 'Kassa', 'Female', 'Degree', 'Support', 'Kebele 2', 'Junior', 'inactive'],
    ['EMP106', 'Desta', 'Alemu', 'Male', 'Degree', 'Medical', 'Kebele 4', 'Senior', 'active'],
    ['EMP107', 'Helen', 'Tadesse', 'Female', 'Masters', 'Admin', 'Kebele 3', 'Junior', 'active'],
    ['EMP108', 'Samuel', 'Berhanu', 'Male', 'Degree', 'Technical', 'Kebele 1', 'Lead', 'active']
];

foreach ($employees_data as $emp) {
    $stmt = $conn->prepare("INSERT IGNORE INTO employees (employee_id, first_name, last_name, gender, education_level, department_assigned, kebele, woreda, job_level, status, join_date, date_of_birth) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), '1990-01-01')");
    $stmt->bind_param("ssssssssss", $emp[0], $emp[1], $emp[2], $emp[3], $emp[4], $emp[5], $emp[6], $woreda, $emp[7], $emp[8]);
    $stmt->execute();
    $stmt->close();
}

// 2. Seed Job Postings
$result = $conn->query("SELECT COUNT(*) as count FROM job_postings WHERE woreda = '$woreda'");
if ($result->fetch_assoc()['count'] == 0) {
    $jobs_data = [
        ['Senior Nurse', 'Medical', 'Full-time', 'Hospital A'],
        ['Billing Officer', 'Admin', 'Part-time', 'Clinic B'],
        ['IT Specialist', 'Technical', 'Full-time', 'Woreda HQ']
    ];

    foreach ($jobs_data as $job) {
        $stmt = $conn->prepare("INSERT INTO job_postings (title, department, employment_type, location, status, posted_at, woreda) VALUES (?, ?, ?, ?, 'open', NOW(), ?)");
        $stmt->bind_param("sssss", $job[0], $job[1], $job[2], $job[3], $woreda);
        $stmt->execute();
        $stmt->close();
    }
}

// 3. Seed Leave Requests
$result = $conn->query("SELECT COUNT(*) as count FROM leave_requests JOIN employees ON leave_requests.employee_id = employees.employee_id WHERE employees.woreda = '$woreda'");
if ($result->fetch_assoc()['count'] == 0) {
    $leave_data = [
        ['EMP103', 'annual', '2026-01-10', '2026-01-20', 10, 'Family vacation'],
        ['EMP101', 'sick', '2026-01-02', '2026-01-05', 3, 'Flu symptoms']
    ];

    foreach ($leave_data as $leave) {
        $stmt = $conn->prepare("INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, days_requested, reason, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("ssssis", $leave[0], $leave[1], $leave[2], $leave[3], $leave[4], $leave[5]);
        $stmt->execute();
        $stmt->close();
    }
}

// 4. Seed Training Sessions
$result = $conn->query("SELECT COUNT(*) as count FROM training_sessions WHERE woreda = '$woreda'");
if ($result->fetch_assoc()['count'] == 0) {
    $training_data = [
        ['Digital Health Records', 'Intro to new system', 'Dr. Smith', '2026-02-15', '09:00:00', '12:00:00', 'Hall A'],
        ['Patient Ethics', 'Advanced workshop', 'Prof. Kebede', '2026-03-01', '14:00:00', '17:00:00', 'Room 203']
    ];

    foreach ($training_data as $t) {
        $stmt = $conn->prepare("INSERT INTO training_sessions (title, description, trainer, session_date, start_time, end_time, venue, status, woreda) VALUES (?, ?, ?, ?, ?, ?, ?, 'scheduled', ?)");
        $stmt->bind_param("ssssssss", $t[0], $t[1], $t[2], $t[3], $t[4], $t[5], $t[6], $woreda);
        $stmt->execute();
        $stmt->close();
    }
}



echo json_encode(['success' => true, 'message' => 'Sample data seeded successfully!']);
$conn->close();
?>
