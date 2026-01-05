<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    echo json_encode(['logged_in' => true, 'role' => $_SESSION['role']]);
    exit();
}

// Check for remember token cookie
if (isset($_COOKIE['remember_token'])) {
    require_once 'db.php';

    $token = $_COOKIE['remember_token'];

    // Get database connection
    $conn = getDBConnection();

    // Find user with this token
    $stmt = $conn->prepare("SELECT id, role, name FROM users WHERE remember_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];

        echo json_encode(['logged_in' => true, 'role' => $user['role']]);
    } else {
        // Invalid token, remove cookie
        setcookie('remember_token', '', time() - 3600, '/');
        echo json_encode(['logged_in' => false]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['logged_in' => false]);
}
?>