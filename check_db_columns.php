<?php
require_once 'db.php';
$conn = getDBConnection();
$result = $conn->query("SHOW COLUMNS FROM employees");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}
file_put_contents('db_columns_list.txt', implode("\n", $columns));
echo "Found " . count($columns) . " columns. List saved to db_columns_list.txt\n";
?>