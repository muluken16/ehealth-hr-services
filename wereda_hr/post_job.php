<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'wereda_hr') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include '../db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$conn = getDBConnection();

$title = $data['title'];
$department = $data['department'];
$description = $data['description'];
$requirements = $data['requirements'];
$salary_range = $data['salary_range'];
$location = $data['location'];
$employment_type = $data['employment_type'];
$application_deadline = $data['application_deadline'];
$posted_by = $_SESSION['user_id'];

$sql = "INSERT INTO job_postings (title, department, description, requirements, salary_range, location, employment_type, application_deadline, posted_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssss", $title, $department, $description, $requirements, $salary_range, $location, $employment_type, $application_deadline, $posted_by);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Job posted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error posting job: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>