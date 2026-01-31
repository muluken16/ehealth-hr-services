<?php
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$res = $conn->query("DESCRIBE leave_requests");
if ($res) {
    echo "Column | Type\n";
    echo "---------------------------\n";
    while($row = $res->fetch_assoc()) {
        echo $row['Field'] . " | " . $row['Type'] . "\n";
    }
} else {
    echo "Table 'leave_requests' does not exist.\n";
}
$conn->close();
?>
