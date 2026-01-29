<?php
session_start();
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $conn = getDBConnection();
    $employee_id = $_POST['employee_id'];
    $password = $_POST['password'];

    // Check if employee_id column exists in users table, if not add it
    $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'employee_id'");
    if ($columnCheck->num_rows == 0) {
        // Add employee_id column to users table
        $conn->query("ALTER TABLE users ADD COLUMN employee_id VARCHAR(20) NULL AFTER id");
        $conn->query("ALTER TABLE users ADD INDEX idx_employee_id (employee_id)");
    }

    // Check users table for login credentials
    $stmt = $conn->prepare("SELECT u.employee_id, u.password, e.first_name, e.last_name, e.kebele as working_kebele 
                            FROM users u 
                            JOIN employees e ON u.employee_id = e.employee_id 
                            WHERE u.employee_id = ? AND u.role = 'employee'");
    
    // Check if prepare was successful
    if ($stmt === false) {
        $error = "Database error: " . $conn->error . ". Please contact administrator.";
    } else {
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['employee_id'] = $user['employee_id'];
                $_SESSION['emp_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['role'] = 'employee';
                $_SESSION['kebele'] = $user['working_kebele'];
                
                // Update last login
                $update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE employee_id = ?");
                $update->bind_param("s", $employee_id);
                $update->execute();
                
                header('Location: dashboard.php');
                exit;
            } else {
                $error = "Invalid password. Check demo credentials for help.";
            }
        } else {
            $error = "Employee ID not found. Please check your credentials.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Login | HealthFirst</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a4a5f 0%, #2a6e8c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: 'Inter', sans-serif;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-logo {
            font-size: 2.5rem;
            color: #1a4a5f;
            margin-bottom: 10px;
        }
        .login-card h2 {
            margin-bottom: 25px;
            color: #333;
        }
        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-group input:focus {
            border-color: #1a4a5f;
            box-shadow: 0 0 0 3px rgba(26, 74, 95, 0.1);
            outline: none;
        }
        .login-btn {
            width: 100%;
            padding: 14px;
            background: #1a4a5f;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }
        .login-btn:hover {
            background: #2a6e8c;
            transform: translateY(-2px);
        }
        .error-msg {
            color: #e74c3c;
            background: #fdeaea;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo"><i class="fas fa-user-md"></i></div>
        <h2>Employee Portal</h2>
        <?php if(isset($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Employee ID</label>
                <input type="text" name="employee_id" placeholder="EMP-XXXXXX" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" name="login" class="login-btn">Login</button>
        </form>
        <p style="margin-top: 20px; color: #666; font-size: 0.9rem;">
            <a href="employee_credentials.php" style="color: #10b981; font-weight: 600; text-decoration: none;">
                <i class="fas fa-list-alt"></i> View Employee Login Credentials
            </a>
        </p>
        <p style="margin-top: 10px; color: #666; font-size: 0.9rem;">
            Kebele HR staff? <a href="../login_ui.php" style="color: #1a4a5f; font-weight: 600;">Login here</a>
        </p>
    </div>
</body>
</html>
