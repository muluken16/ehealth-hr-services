<?php
require_once 'db.php';
$conn = getDBConnection();
$result = $conn->query("SHOW CREATE TABLE users");
$row = $result->fetch_assoc();
echo $row['Create Table'];
$conn->close();
?>
