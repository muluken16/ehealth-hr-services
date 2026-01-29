<?php
include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

// Assume kebele for demo
$kebele = 'Kebele 01';

// Get reports for this kebele
$result = $conn->query("SELECT * FROM reports WHERE kebele = '$kebele' ORDER BY created_at DESC");

$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($reports);
?>