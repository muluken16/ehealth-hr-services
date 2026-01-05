<?php
require_once 'db.php';
$conn = getDBConnection();

// Clear attempts
$conn->query("DELETE FROM login_attempts");

// Ensure structure is correct
$conn->query("ALTER TABLE users MODIFY id INT(6) UNSIGNED AUTO_INCREMENT");

// Force refresh all users with exact emails and known hashes
$new_hash = password_hash('123456', PASSWORD_DEFAULT);
$users = [
    ['admin@gmail.com', $new_hash, 'admin', 'Administrator'],
    ['zone_ho@gmail.com', $new_hash, 'zone_health_officer', 'Zone Health Officer'],
    ['zone_hr@gmail.com', $new_hash, 'zone_hr', 'Zone HR Officer'],
    ['wereda_ho@gmail.com', $new_hash, 'wereda_health_officer', 'Wereda Health Officer'],
    ['wereda_hr@gmail.com', $new_hash, 'wereda_hr', 'Wereda HR Officer'],
    ['kebele_ho@gmail.com', $new_hash, 'kebele_health_officer', 'Kebele Health Officer'],
    ['kebele_hr@gmail.com', $new_hash, 'kebele_hr', 'Kebele HR Officer'],
];

// Delete specific roles to re-insert cleanly without affecting other potential users
$conn->query("DELETE FROM users WHERE email LIKE '%@gmail.com'");

foreach ($users as $u) {
    $stmt = $conn->prepare("INSERT INTO users (email, password, role, name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $u[0], $u[1], $u[2], $u[3]);
    $stmt->execute();
    $stmt->close();
}

$result = $conn->query("SELECT email, role FROM users WHERE email LIKE '%@gmail.com'");
echo "<h2>Synchronized Users:</h2><ul>";
while($row = $result->fetch_assoc()) {
    echo "<li><b>Email:</b> {$row['email']} | <b>Role:</b> {$row['role']}</li>";
}
echo "</ul><br><b>Password for all: 123456</b>";
$conn->close();
?>
