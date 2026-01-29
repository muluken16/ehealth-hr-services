<?php
require_once 'db.php';
$conn = getDBConnection();
$result = $conn->query("DESCRIBE users");
echo "<h2>Users Table Structure:</h2>";
echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr><td>".$row["Field"]."</td><td>".$row["Type"]."</td><td>".$row["Null"]."</td><td>".$row["Key"]."</td><td>".$row["Default"]."</td><td>".$row["Extra"]."</td></tr>";
}
echo "</table>";
$conn->close();
?>
