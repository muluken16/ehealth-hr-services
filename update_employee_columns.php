<?php
require_once 'db.php';
$conn = getDBConnection();

echo "<h2>Checking Employee Table Columns...</h2>";

// List of all columns needed by add_employee.php form
$requiredColumns = [
    'photo' => 'VARCHAR(255)',
    'middle_name' => 'VARCHAR(50)',
    'religion' => 'VARCHAR(50)',
    'other_religion' => 'VARCHAR(100)',
    'citizenship' => 'VARCHAR(50)',
    'other_citizenship' => 'VARCHAR(100)',
    'language' => 'VARCHAR(50)',
    'other_language' => 'VARCHAR(100)',
    'education_level' => 'VARCHAR(50)',
    'university' => 'TEXT',
    'department' => 'VARCHAR(100)',
    'secondary_school' => 'TEXT',
    'department_assigned' => 'VARCHAR(50)',
    'position' => 'VARCHAR(100)',
    'join_date' => 'DATE',
    'employment_type' => 'VARCHAR(20)',
    'status' => "ENUM('active', 'on-leave', 'inactive') DEFAULT 'active'",
    'salary' => 'DECIMAL(10,2)',
    'phone_number' => 'VARCHAR(20)',
    'emergency_contact' => 'VARCHAR(100)',
    'email' => 'VARCHAR(100)',
    'region' => 'VARCHAR(50)',
    'zone' => 'VARCHAR(50)',
    'woreda' => 'VARCHAR(50)',
    'kebele' => 'VARCHAR(50)',
    'address' => 'TEXT',
    'bank_name' => 'VARCHAR(100)',
    'bank_account' => 'VARCHAR(100)',
    'credit_status' => "ENUM('good', 'active', 'bad') DEFAULT 'good'",
    'credit_details' => 'TEXT',
    'loan_file' => 'VARCHAR(255)',
    'warranty_status' => "ENUM('yes', 'no') DEFAULT 'no'",
    'person_name' => 'VARCHAR(100)',
    'phone' => 'VARCHAR(20)',
    'warranty_woreda' => 'VARCHAR(50)',
    'warranty_kebele' => 'VARCHAR(50)',
    'fin_id' => 'VARCHAR(50)',
    'fin_scan' => 'VARCHAR(255)',
    'national_id_details' => 'TEXT',
    'scan_file' => 'VARCHAR(255)',
    'criminal_status' => "ENUM('yes', 'no') DEFAULT 'no'",
    'criminal_file' => 'VARCHAR(255)',
    'criminal_record_details' => 'TEXT',
    'job_level' => 'VARCHAR(50)',
    'marital_status' => 'VARCHAR(20)'
];

$missingColumns = [];
$existingColumns = [];

// Check which columns exist
foreach ($requiredColumns as $column => $dataType) {
    $result = $conn->query("SHOW COLUMNS FROM employees LIKE '$column'");
    if ($result->num_rows == 0) {
        $missingColumns[$column] = $dataType;
        echo "❌ Missing: <strong>$column</strong><br>";
    } else {
        $existingColumns[] = $column;
        echo "✅ Exists: $column<br>";
    }
}

if (count($missingColumns) > 0) {
    echo "<br><h3>Adding Missing Columns...</h3>";
    
    foreach ($missingColumns as $column => $dataType) {
        $sql = "ALTER TABLE employees ADD COLUMN `$column` $dataType";
        
        if ($conn->query($sql) === TRUE) {
            echo "✅ Added column: <strong>$column</strong> ($dataType)<br>";
        } else {
            echo "❌ Error adding $column: " . $conn->error . "<br>";
        }
    }
    
    echo "<br><h3>✅ Database Update Complete!</h3>";
    echo "<p>" . count($missingColumns) . " columns added successfully.</p>";
} else {
    echo "<br><h3>✅ All Required Columns Exist!</h3>";
    echo "<p>Database schema is up to date. Total columns checked: " . count($requiredColumns) . "</p>";
}

$conn->close();
?>
<br>
<a href="add_employee.php" style="display: inline-block; padding: 10px 20px; background: #1a4a5f; color: white; text-decoration: none; border-radius: 5px;">Go to Add Employee Form</a>
