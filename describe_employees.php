<?php
require_once 'db.php';

$conn = getDBConnection();

$result = $conn->query("DESCRIBE employees");

$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}

echo "Columns in employees table:\n";
foreach ($columns as $col) {
    echo $col . "\n";
}

echo "\nTotal columns: " . count($columns) . "\n";

$conn->close();
?>