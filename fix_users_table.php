<?php
require_once 'db.php';
$conn = getDBConnection();

// Fix the ID column to be AUTO_INCREMENT
$sql = "ALTER TABLE users MODIFY id INT(6) UNSIGNED AUTO_INCREMENT";
if ($conn->query($sql) === TRUE) {
    echo "Users table ID column updated to AUTO_INCREMENT successfully.\n";
} else {
    echo "Error updating ID column: " . $conn->error . "\n";
}

// Clear existing users to avoid conflicts and start clean for demo
$conn->query("DELETE FROM users");
echo "Cleared old user data.\n";

$conn->close();
?>
