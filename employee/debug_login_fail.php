<?php
require_once '../db.php';
$conn = getDBConnection();
$employee_id = $_GET['id'] ?? 'HF-2025-AMWAWA7691';

echo "Debugging Login for: $employee_id\n";
echo "----------------------------------------\n";

// 1. Check if user exists in users table
$stmt = $conn->prepare("SELECT * FROM users WHERE employee_id = ?");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user) {
    echo "[OK] Found in users table.\n";
    echo "Role: " . $user['role'] . "\n";
    echo "Password Hash: " . substr($user['password'], 0, 10) . "...\n";
} else {
    echo "[FAIL] Not found in users table!\n";
}

// 2. Check if user exists in employees table
$stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$emp = $stmt->get_result()->fetch_assoc();

if ($emp) {
    echo "[OK] Found in employees table.\n";
    echo "Name: " . $emp['first_name'] . " " . $emp['last_name'] . "\n";
} else {
    echo "[FAIL] Not found in employees table!\n";
}

// 3. Test Join Query (copied from login.php)
$stmt = $conn->prepare("SELECT u.employee_id, u.password, e.first_name, e.last_name, e.kebele as working_kebele 
                        FROM users u 
                        JOIN employees e ON u.employee_id = e.employee_id 
                        WHERE u.employee_id = ? AND u.role = 'employee'");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    echo "[OK] Login query returns 1 row.\n";
} else {
    echo "[FAIL] Login query returned " . $result->num_rows . " rows.\n";
    echo "Possible cause: 'role' column mismatch or missing join data.\n";
}
?>
