<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'zone_hr') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include '../db.php';
$conn = getDBConnection();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$posted_by = $_SESSION['user_id'];

$sql = "INSERT INTO job_postings (title, department, description, requirements, salary_range, location, employment_type, application_deadline, posted_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssi",
    $data['title'],
    $data['department'],
    $data['description'],
    $data['requirements'],
    $data['salary_range'],
    $data['location'],
    $data['employment_type'],
    $data['application_deadline'],
    $posted_by
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>