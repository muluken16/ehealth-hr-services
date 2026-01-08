<?php
require_once 'db.php';
$conn = getDBConnection();

$result = $conn->query("SELECT email, role FROM users");
$found_users = [];
while($row = $result->fetch_assoc()) {
    $found_users[] = $row;
}
echo json_encode($found_users, JSON_PRETTY_PRINT);

// Force reset passwords one last time for found users
$new_hash = password_hash('123456', PASSWORD_DEFAULT);
$conn->query("UPDATE users SET password = '$new_hash'");

$conn->close();
?>
