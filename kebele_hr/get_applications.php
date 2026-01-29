<?php
session_start();
require_once dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

$job_id = $_GET['job_id'] ?? '';
$status = $_GET['status'] ?? '';

$kebele = $_SESSION['kebele'] ?? 'Kebele 1';

// Get applications for jobs posted in this kebele
$sql = "SELECT ja.*, jp.title as job_title 
        FROM job_applications ja 
        JOIN job_postings jp ON ja.job_id = jp.id 
        WHERE jp.kebele = ?";
$params = [$kebele];
$types = "s";

if (!empty($job_id)) {
    $sql .= " AND ja.job_id = ?";
    $params[] = $job_id;
    $types .= "i";
}

if (!empty($status)) {
    $sql .= " AND ja.status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY ja.applied_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$applications = [];
while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($applications);
