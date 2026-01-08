<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'zone_hr') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include '../db.php';
$conn = getDBConnection();

// For zone HR, get all open jobs (assuming zone-level)
$sql = "SELECT jp.*, COUNT(ja.id) as applications FROM job_postings jp LEFT JOIN job_applications ja ON jp.id = ja.job_id WHERE jp.status = 'open' GROUP BY jp.id ORDER BY jp.posted_at DESC";
$result = $conn->query($sql);

$jobs = [];
while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
}

echo json_encode($jobs);

$conn->close();
?>