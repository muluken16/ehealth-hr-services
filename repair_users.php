<?php
/**
 * Repair Script: Ensure all employees have a corresponding user account
 */
require_once 'db.php';
$conn = getDBConnection();

echo "Starting user repair process...\n";

// Function to generate password (same as before)
function generatePassword($first_name, $phone_number) {
    $first_three = strtolower(substr($first_name, 0, 3));
    $phone_cleaned = preg_replace('/[^0-9]/', '', $phone_number);
    $last_three = substr($phone_cleaned, -3);
    return $first_three . $last_three;
}

// 1. Get all employees who are NOT in the users table
$query = "SELECT e.* 
          FROM employees e 
          LEFT JOIN users u ON e.employee_id = u.employee_id 
          WHERE u.employee_id IS NULL";

$result = $conn->query($query);
$count = $result->num_rows;

echo "Found $count employees missing from users table.\n";

if ($count > 0) {
    while ($emp = $result->fetch_assoc()) {
        $employee_id = $emp['employee_id'];
        $first_name = $emp['first_name'];
        $phone = $emp['phone_number'] ?? '000';
        
        $password = generatePassword($first_name, $phone);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert missing user
        $stmt = $conn->prepare("INSERT INTO users (employee_id, password, role, created_at) VALUES (?, ?, 'employee', NOW())");
        $stmt->bind_param("ss", $employee_id, $hash);
        
        if ($stmt->execute()) {
            echo " [OK] Created user for $employee_id ($password)\n";
        } else {
            echo " [ERR] Failed for $employee_id: " . $stmt->error . "\n";
        }
    }
} else {
    echo "All employees already have user accounts.\n";
}

// 2. Double check the specific user we are debugging
$test_id = 'HF-2025-AMWAWA7691';
$check = $conn->query("SELECT * FROM users WHERE employee_id = '$test_id'");
if ($check->num_rows > 0) {
    echo "\nVerification: User $test_id now exists in users table.";
} else {
    echo "\nVerification FAILED: User $test_id still missing!";
}

echo "\nDone.";
?>
