<?php
require_once 'db.php';
$conn = getDBConnection();

$columns = [
    'education_file' => "TEXT",
    'employment_agreement' => "TEXT",
    'photo' => "VARCHAR(255)",
    'criminal_record_details' => "TEXT",
    'national_id_details' => "VARCHAR(255)",
    'credit_status' => "VARCHAR(50)",
    'credit_details' => "TEXT",
    'guarantor_photo' => "VARCHAR(255)"
];

$success_count = 0;
$error_count = 0;

foreach ($columns as $col => $def) {
    $result = $conn->query("SHOW COLUMNS FROM employees LIKE '$col'");
    if ($result && $result->num_rows == 0) {
        if ($conn->query("ALTER TABLE employees ADD COLUMN $col $def")) {
            echo "✓ Added column: $col<br>";
            $success_count++;
        } else {
            echo "✗ Error adding $col: " . $conn->error . "<br>";
            $error_count++;
        }
    } else {
        echo "✓ Column '$col' already exists<br>";
        $success_count++;
    }
}

echo "<br><strong>Summary:</strong> $success_count columns processed, $error_count errors";
$conn->close();
