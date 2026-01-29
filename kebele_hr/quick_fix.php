<?php
// Direct fix for education_file column - standalone script
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ehealth";

echo "<h2>Quick Fix for education_file Column</h2>";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>");
}

echo "<p>Connected to database successfully.</p>";

// Columns to add
$columns = [
    "education_file TEXT",
    "employment_agreement TEXT",
    "guarantor_photo VARCHAR(255)"
];

foreach ($columns as $col) {
    $col_name = explode(" ", $col)[0];

    // Check if exists
    $result = $conn->query("SHOW COLUMNS FROM employees LIKE '$col_name'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: orange;'>⏭️ Column '$col_name' already exists</p>";
        continue;
    }

    // Add column
    $sql = "ALTER TABLE employees ADD COLUMN $col";
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✅ Added column: $col_name</p>";
    } else {
        echo "<p style='color: red;'>❌ Error adding $col_name: " . $conn->error . "</p>";
    }
}

echo "<hr>";
echo "<p><a href='register_employee.php'>Go to Employee Registration</a></p>";

$conn->close();
?>