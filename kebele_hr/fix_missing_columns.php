<?php
// Fix missing columns in employees table
require_once '../db.php';

$conn = getDBConnection();
$errors = [];
$success = [];

// List of columns that should exist in the employees table
$columns_to_add = [
    // File upload columns
    "education_file" => "TEXT COMMENT 'JSON array of education document filenames'",
    "employment_agreement" => "TEXT COMMENT 'JSON array of employment agreement filenames'",
    "photo" => "VARCHAR(255) DEFAULT NULL COMMENT 'Employee photo filename'",
    "guarantor_photo" => "VARCHAR(255) DEFAULT NULL COMMENT 'Guarantor photo filename'",

    // Detail columns
    "criminal_record_details" => "TEXT DEFAULT NULL COMMENT 'Criminal record details'",
    "national_id_details" => "TEXT DEFAULT NULL COMMENT 'National ID details'",
    "credit_status" => "VARCHAR(50) DEFAULT 'good' COMMENT 'Credit status'",
    "credit_details" => "TEXT DEFAULT NULL COMMENT 'Credit details'",
    "other_bank_name" => "VARCHAR(100) DEFAULT NULL COMMENT 'Other bank name'",
    "other_department" => "TEXT DEFAULT NULL COMMENT 'Other department description'",
    "other_job_level" => "TEXT DEFAULT NULL COMMENT 'Other job level description'",
    "other_marital_status" => "VARCHAR(50) DEFAULT NULL COMMENT 'Other marital status description'",
    "other_language" => "VARCHAR(100) DEFAULT NULL COMMENT 'Other language spoken'",
    "other_citizenship" => "VARCHAR(100) DEFAULT NULL COMMENT 'Other citizenship description'",

    // Location columns
    "warranty_woreda" => "VARCHAR(50) DEFAULT NULL COMMENT 'Warranty person woreda'",
    "warranty_kebele" => "VARCHAR(50) DEFAULT NULL COMMENT 'Warranty person kebele'",
    "working_woreda" => "VARCHAR(50) DEFAULT NULL COMMENT 'Working woreda'",
    "working_kebele" => "VARCHAR(50) DEFAULT NULL COMMENT 'Working kebele'",

    // Additional columns
    "language" => "VARCHAR(100) DEFAULT NULL COMMENT 'Languages spoken'",
    "religion" => "VARCHAR(50) DEFAULT NULL COMMENT 'Religion'"
];

// Check and add each column
foreach ($columns_to_add as $column => $definition) {
    // Check if column exists
    $check_sql = "SHOW COLUMNS FROM employees LIKE '{$column}'";
    $result = $conn->query($check_sql);

    if ($result && $result->num_rows === 0) {
        // Column doesn't exist, add it
        $alter_sql = "ALTER TABLE employees ADD COLUMN {$column} {$definition}";
        if ($conn->query($alter_sql)) {
            $success[] = "Added column: {$column}";
        } else {
            $errors[] = "Error adding {$column}: " . $conn->error;
        }
    } else {
        $success[] = "Column already exists: {$column}";
    }
}

// Report results
echo "<h2>Database Schema Fix Results</h2>";
echo "<h3 style='color: green;'>Success:</h3>";
echo "<ul>";
foreach ($success as $msg) {
    echo "<li>{$msg}</li>";
}
echo "</ul>";

if (!empty($errors)) {
    echo "<h3 style='color: red;'>Errors:</h3>";
    echo "<ul>";
    foreach ($errors as $err) {
        echo "<li>{$err}</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: green; font-weight: bold;'>All columns are now properly configured!</p>";
}

echo "<p><a href='register_employee.php'>Go to Employee Registration</a></p>";
