<?php
include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

// Assume kebele for demo
$kebele = 'Kebele 01';

// Get inventory for this kebele
$result = $conn->query("SELECT * FROM inventory WHERE kebele = '$kebele' ORDER BY item_name");

$inventory = [];
while ($row = $result->fetch_assoc()) {
    $inventory[] = $row;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($inventory);
?>