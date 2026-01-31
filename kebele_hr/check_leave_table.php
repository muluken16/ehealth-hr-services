<?php
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$res = $conn->query("SHOW TABLES LIKE 'leave_entitlements'");
if ($res->num_rows > 0) {
    echo "Table 'leave_entitlements' exists.\n\n";
    $res = $conn->query("DESCRIBE leave_entitlements");
    echo "Column | Type\n";
    echo "---------------------------\n";
    while($row = $res->fetch_assoc()) {
        echo $row['Field'] . " | " . $row['Type'] . "\n";
    }
} else {
    echo "Table 'leave_entitlements' does NOT exist.\n";
}
$conn->close();
?>
