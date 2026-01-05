<?php
// Script to create database tables manually
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ehealth";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database '$dbname' created successfully or already exists.<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select database
$conn->select_db($dbname);

// Create employees table
$sql = "CREATE TABLE IF NOT EXISTS employees (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    date_of_birth DATE NOT NULL,
    religion VARCHAR(50),
    citizenship VARCHAR(50),
    other_citizenship VARCHAR(100),
    region VARCHAR(50),
    zone VARCHAR(50),
    woreda VARCHAR(50),
    kebele VARCHAR(50),
    education_level VARCHAR(50),
    primary_school TEXT,
    secondary_school TEXT,
    college TEXT,
    university TEXT,
    department VARCHAR(100),
    other_department TEXT,
    bank_name VARCHAR(100),
    bank_account VARCHAR(100),
    job_level VARCHAR(50),
    other_job_level TEXT,
    marital_status VARCHAR(20),
    other_marital_status TEXT,
    warranty_status ENUM('yes', 'no'),
    person_name VARCHAR(100),
    warranty_woreda VARCHAR(50),
    warranty_kebele VARCHAR(50),
    phone VARCHAR(20),
    warranty_type VARCHAR(20),
    scan_file VARCHAR(255),
    criminal_status ENUM('yes', 'no'),
    criminal_file VARCHAR(255),
    fin_id VARCHAR(50),
    fin_scan VARCHAR(255),
    loan_status ENUM('yes', 'no'),
    loan_file VARCHAR(255),
    leave_request ENUM('yes', 'no'),
    leave_document VARCHAR(255),
    email VARCHAR(100),
    phone_number VARCHAR(20),
    department_assigned VARCHAR(50),
    position VARCHAR(100),
    join_date DATE,
    salary DECIMAL(10,2),
    employment_type VARCHAR(20),
    status ENUM('active', 'on-leave', 'inactive') DEFAULT 'active',
    address TEXT,
    emergency_contact VARCHAR(100),
    documents TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Employees table created successfully.<br>";
} else {
    echo "Error creating employees table: " . $conn->error . "<br>";
}

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'employees'");
if ($result->num_rows > 0) {
    echo "✅ Employees table exists in the database.<br>";
} else {
    echo "❌ Employees table does not exist.<br>";
}

// Show table structure
$result = $conn->query("DESCRIBE employees");
if ($result) {
    echo "<br>Table structure:<br>";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
    }
}

$conn->close();
echo "<br>Database setup completed.";
?>