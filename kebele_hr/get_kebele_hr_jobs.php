<?php
include dirname(__DIR__) . '/db.php';
session_start();

$conn = getDBConnection();

// Get all job postings (HR should see all jobs, not just their kebele)
$result = $conn->query("SELECT * FROM job_postings ORDER BY posted_at DESC");

$jobs = [];
while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($jobs);
