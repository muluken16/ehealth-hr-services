<?php
error_reporting(0);
session_start();

// Set session timeout to 30 minutes
ini_set('session.gc_maxlifetime', 1800);
session_set_cookie_params(1800);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    // Use centralized database connection
    require_once 'db.php';
    $conn = getDBConnection();
    
    $ip = $_SERVER['REMOTE_ADDR'];

    $stmt = $conn->prepare("SELECT id, password, role, name, zone, woreda, kebele, email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['zone'] = $user['zone'];
            $_SESSION['woreda'] = $user['woreda'];
            $_SESSION['kebele'] = $user['kebele'];
            $_SESSION['email'] = $user['email'];

            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $updateStmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $updateStmt->bind_param("si", $token, $user['id']);
                $updateStmt->execute();
                $updateStmt->close();
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), "/"); // 30 days
            }

            // Update last login
            $updateLoginStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateLoginStmt->bind_param("i", $user['id']);
            $updateLoginStmt->execute();
            $updateLoginStmt->close();

            // Log successful login
            $logStmt = $conn->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 1)");
            $logStmt->bind_param("ss", $email, $ip);
            $logStmt->execute();
            $logStmt->close();

            echo json_encode(['success' => true, 'role' => $user['role'], 'message' => 'Login successful']);
        } else {
            // Log failed login
            $logStmt = $conn->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 0)");
            $logStmt->bind_param("ss", $email, $ip);
            $logStmt->execute();
            $logStmt->close();

            echo json_encode(['success' => false, 'message' => 'Invalid password']);
        }
    } else {
        // Log failed login
        $logStmt = $conn->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 0)");
        $logStmt->bind_param("ss", $email, $ip);
        $logStmt->execute();
        $logStmt->close();

        echo json_encode(['success' => false, 'message' => 'User not found']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>