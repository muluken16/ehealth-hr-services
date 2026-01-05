<?php
// Debug script to check login response
session_start();
require_once 'db.php';

$email = 'wereda_hr@gmail.com'; // Test with wereda_hr

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id, password, role, name FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    echo "<h2>Debug: User found</h2>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Role: " . $user['role'] . "<br>";
    echo "Name: " . $user['name'] . "<br>";
    echo "ID: " . $user['id'] . "<br>";

    // Test password
    $password = '123456';
    if (password_verify($password, $user['password'])) {
        echo "<br>✅ Password verification: SUCCESS<br>";
        echo "Expected redirect URL: ";

        switch($user['role']) {
            case 'admin':
                echo "admin_dashboard.php";
                break;
            case 'zone_health_officer':
                echo "zone_ho/zone_ho_dashboard.php";
                break;
            case 'zone_hr':
                echo "zone_hr/zone_hr_dashboard.php";
                break;
            case 'wereda_health_officer':
                echo "wereda_ho/wereda_ho_dashboard.php";
                break;
            case 'wereda_hr':
                echo "wereda_hr/wereda_hr_dashboard.php";
                break;
            case 'kebele_health_officer':
                echo "kebele_ho/kebele_ho_dashboard.php";
                break;
            case 'kebele_hr':
                echo "kebele_hr/kebele_hr_dashboard.php";
                break;
            default:
                echo "index.html (fallback)";
        }
    } else {
        echo "<br>❌ Password verification: FAILED<br>";
    }
} else {
    echo "<h2>Debug: User NOT found</h2>";
    echo "Email: " . $email . "<br>";
}

$stmt->close();
$conn->close();
?>