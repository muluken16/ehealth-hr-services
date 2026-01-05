<?php
include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

// Assume kebele for demo
$kebele = 'Kebele 01';

// Get patients for this kebele
$result = $conn->query("SELECT * FROM patients WHERE kebele = '$kebele' ORDER BY created_at DESC");

$patients = [];
while ($row = $result->fetch_assoc()) {
    $patients[] = $row;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($patients);
?>