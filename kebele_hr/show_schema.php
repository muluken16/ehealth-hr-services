<?php
$conn = new mysqli("localhost", "root", "", "ehealth");
$res = $conn->query("DESCRIBE employees");
echo "Column | Type\n";
echo "---------------------------\n";
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " | " . $row['Type'] . "\n";
}
$conn->close();
?>
