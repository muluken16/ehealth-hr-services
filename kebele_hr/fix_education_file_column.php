<?php
// Fix education_file column and other required columns
require_once '../db.php';

$conn = getDBConnection();

echo "<h2>Fixing Missing Columns for Employee Registration</h2>";

$columns = [
    ["name" => "education_file", "definition" => "TEXT"],
    ["name" => "employment_agreement", "definition" => "TEXT"],
    ["name" => "photo", "definition" => "VARCHAR(255)"],
    ["name" => "criminal_record_details", "definition" => "TEXT"],
    ["name" => "national_id_details", "definition" => "TEXT"],
    ["name" => "credit_status", "definition" => "VARCHAR(50) DEFAULT 'good'"],
    ["name" => "credit_details", "definition" => "TEXT"],
    ["name" => "other_bank_name", "definition" => "VARCHAR(100)"],
    ["name" => "other_department", "definition" => "TEXT"],
    ["name" => "other_job_level", "definition" => "TEXT"],
    ["name" => "other_marital_status", "definition" => "TEXT"],
    ["name" => "other_language", "definition" => "VARCHAR(100)"],
    ["name" => "other_citizenship", "definition" => "VARCHAR(100)"],
    ["name" => "warranty_woreda", "definition" => "VARCHAR(50)"],
    ["name" => "warranty_kebele", "definition" => "VARCHAR(50)"],
    ["name" => "working_woreda", "definition" => "VARCHAR(50)"],
    ["name" => "working_kebele", "definition" => "VARCHAR(50)"],
    ["name" => "guarantor_photo", "definition" => "VARCHAR(255)"],
    ["name" => "guarantor_photo", "definition" => "VARCHAR(255)"],
    ["name" => "language", "definition" => "VARCHAR(100)"],
    ["name" => "religion", "definition" => "VARCHAR(50)"]
];

$success_count = 0;
$error_count = 0;

foreach ($columns as $col) {
    // Check if column exists
    $check = $conn->query("SHOW COLUMNS FROM employees LIKE '{$col['name']}'");

    if ($check && $check->num_rows > 0) {
        echo "<p style='color: orange;'>⏭️ Column '{$col['name']}' already exists</p>";
        continue;
    }

    // Add column
    $sql = "ALTER TABLE employees ADD COLUMN {$col['name']} {$col['definition']}";

    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✅ Added column: {$col['name']}</p>";
        $success_count++;
    } else {
        echo "<p style='color: red;'>❌ Error adding {$col['name']}: " . $conn->error . "</p>";
        $error_count++;
    }
}

echo "<hr>";
echo "<h3>Summary</h3>";
echo "<p>Successfully added: {$success_count} columns</p>";
echo "<p>Errors: {$error_count} columns</p>";

if ($error_count === 0) {
    echo "<p style='color: green; font-weight: bold;'>All columns are now in place! Try registering an employee again.</p>";
}

echo "<p><a href='register_employee.php'>Go to Employee Registration</a></p>";
