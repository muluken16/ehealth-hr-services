<?php
require_once 'db.php';
$conn = getDBConnection();

$email = 'admin@gmail.com';
$password = '123456';

$stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    $hash = $user['password'];
    echo "Email: $email\n";
    echo "Hash in DB: $hash\n";
    echo "Verify Status: " . (password_verify($password, $hash) ? "MATCH" : "FAIL") . "\n";
    
    // Test a fresh hash
    $new_hash = password_hash($password, PASSWORD_DEFAULT);
    echo "Fresh Hash: $new_hash\n";
    echo "Verify Fresh: " . (password_verify($password, $new_hash) ? "MATCH" : "FAIL") . "\n";
} else {
    echo "User $email not found.\n";
}
$conn->close();
?>
