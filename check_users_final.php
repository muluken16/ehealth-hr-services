<?php
require_once 'db.php';
$conn = getDBConnection();
$result = $conn->query("SELECT email, role FROM users");
while($row = $result->fetch_assoc()) {
    echo $row['email'] . " (" . $row['role'] . ")\n";
}
$conn->close();
?>
