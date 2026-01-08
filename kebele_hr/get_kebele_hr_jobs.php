<?php
include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

// Assume kebele for demo
$kebele = 'Kebele 01';

// Get job postings for this kebele
$result = $conn->query("SELECT * FROM job_postings WHERE kebele = '$kebele' AND status = 'active' ORDER BY posted_at DESC");

$jobs = [];
while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($jobs);
?>