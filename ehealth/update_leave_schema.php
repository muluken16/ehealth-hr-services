<?php
require_once 'db.php';
$conn = getDBConnection();

$queries = [
    // Add leave entitlement and usage columns to employees table
    "ALTER TABLE employees ADD COLUMN IF NOT EXISTS service_years INT DEFAULT 0",
    "ALTER TABLE employees ADD COLUMN IF NOT EXISTS annual_entitlement INT DEFAULT 21",
    "ALTER TABLE employees ADD COLUMN IF NOT EXISTS sick_entitlement INT DEFAULT 14",
    "ALTER TABLE employees ADD COLUMN IF NOT EXISTS maternity_entitlement INT DEFAULT 120",
    "ALTER TABLE employees ADD COLUMN IF NOT EXISTS paternity_entitlement INT DEFAULT 10",
    "ALTER TABLE employees ADD COLUMN IF NOT EXISTS emergency_entitlement INT DEFAULT 5",
    "ALTER TABLE employees ADD COLUMN IF NOT EXISTS annual_used INT DEFAULT 0",
    "ALTER TABLE employees ADD COLUMN IF NOT EXISTS sick_used INT DEFAULT 0",
    "ALTER TABLE employees ADD COLUMN IF NOT EXISTS maternity_used INT DEFAULT 0",
    "ALTER TABLE employees ADD COLUMN IF NOT EXISTS paternity_used INT DEFAULT 0",
    "ALTER TABLE employees ADD COLUMN IF NOT EXISTS emergency_used INT DEFAULT 0",
    "ALTER TABLE employees ADD COLUMN IF NOT EXISTS password VARCHAR(255) NULL",
    
    // Add columns to leave_requests if missing
    "ALTER TABLE leave_requests ADD COLUMN IF NOT EXISTS attachment VARCHAR(255) NULL",
    "ALTER TABLE leave_requests ADD COLUMN IF NOT EXISTS rejected_reason TEXT NULL",
    
    // Ensure days_requested is present (already in db.php but double check)
    "ALTER TABLE leave_requests MODIFY COLUMN days_requested INT NOT NULL"
];

foreach ($queries as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "✅ Success: $sql <br>";
    } else {
        echo "❌ Error: " . $conn->error . " for query: $sql <br>";
    }
}

// Set default passwords for existing employees for demo (123456)
$password = password_hash('123456', PASSWORD_DEFAULT);
$conn->query("UPDATE employees SET password = '$password' WHERE password IS NULL");

echo "Schema update completed!";
$conn->close();
?>
