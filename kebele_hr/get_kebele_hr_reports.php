<?php
include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

// Assume kebele for demo
$kebele = 'Kebele 01';

// Get generated reports for this kebele
$result = $conn->query("SELECT * FROM hr_reports WHERE kebele = '$kebele' ORDER BY generated_at DESC LIMIT 10");

$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($reports);
?>