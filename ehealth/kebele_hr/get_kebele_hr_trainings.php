<?php
include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

// Assume kebele for demo
$kebele = 'Kebele 01';

// Get training programs for this kebele
$result = $conn->query("SELECT * FROM trainings WHERE kebele = '$kebele' ORDER BY scheduled_date DESC");

$trainings = [];
while ($row = $result->fetch_assoc()) {
    $trainings[] = $row;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($trainings);
?>