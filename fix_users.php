<?php
// Script to check and fix user roles in the database
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

$conn = getDBConnection();

// Check current users
echo "<h2>Current Users in Database:</h2>";
$result = $conn->query("SELECT id, email, role, name FROM users");
if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr><th>ID</th><th>Email</th><th>Role</th><th>Name</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found in database.</p>";
}

// Update or insert correct users
echo "<h2>Ensuring Correct Users Exist:</h2>";

$users = [
    ['admin@gmail.com', password_hash('123456', PASSWORD_DEFAULT), 'admin', 'Administrator'],
    ['zone_ho@gmail.com', password_hash('123456', PASSWORD_DEFAULT), 'zone_health_officer', 'Zone Health Officer'],
    ['zone_hr@gmail.com', password_hash('123456', PASSWORD_DEFAULT), 'zone_hr', 'Zone HR Officer'],
    ['wereda_ho@gmail.com', password_hash('123456', PASSWORD_DEFAULT), 'wereda_health_officer', 'Wereda Health Officer'],
    ['wereda_hr@gmail.com', password_hash('123456', PASSWORD_DEFAULT), 'wereda_hr', 'Wereda HR Officer'],
    ['kebele_ho@gmail.com', password_hash('123456', PASSWORD_DEFAULT), 'kebele_health_officer', 'Kebele Health Officer'],
    ['kebele_hr@gmail.com', password_hash('123456', PASSWORD_DEFAULT), 'kebele_hr', 'Kebele HR Officer'],
];

foreach ($users as $user) {
    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $user[0]);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing user
        $updateStmt = $conn->prepare("UPDATE users SET password = ?, role = ?, name = ? WHERE email = ?");
        $updateStmt->bind_param("ssss", $user[1], $user[2], $user[3], $user[0]);
        if ($updateStmt->execute()) {
            echo "✅ Updated user: " . $user[0] . " (Role: " . $user[2] . ")<br>";
        } else {
            echo "❌ Failed to update user: " . $user[0] . "<br>";
        }
        $updateStmt->close();
    } else {
        // Insert new user
        $insertStmt = $conn->prepare("INSERT INTO users (email, password, role, name) VALUES (?, ?, ?, ?)");
        $insertStmt->bind_param("ssss", $user[0], $user[1], $user[2], $user[3]);
        if ($insertStmt->execute()) {
            echo "✅ Inserted user: " . $user[0] . " (Role: " . $user[2] . ")<br>";
        } else {
            echo "❌ Failed to insert user: " . $user[0] . "<br>";
        }
        $insertStmt->close();
    }
    $stmt->close();
}

echo "<h2>Final User List:</h2>";
$result = $conn->query("SELECT id, email, role, name FROM users ORDER BY email");
if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr><th>ID</th><th>Email</th><th>Role</th><th>Name</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$conn->close();

echo "<br><strong>Now try logging in again with the correct credentials!</strong>";
?>