<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Debug Employee Insert</h2>";

// Connect to database
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h3>1. Database Connection: ✅</h3>";

// Check employees table structure
echo "<h3>2. Employees Table Structure:</h3>";
$result = $conn->query("DESCRIBE employees");
if ($result) {
    $fields = [];
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $fields[] = $row['Field'];
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><strong>Total fields in database: " . count($fields) . "</strong></p>";
} else {
    echo "❌ Error describing table: " . $conn->error;
}

// Check the INSERT statement fields
echo "<h3>3. INSERT Statement Analysis:</h3>";
$insert_fields = [
    'employee_id', 'first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth', 'religion',
    'citizenship', 'other_citizenship', 'region', 'zone', 'woreda', 'kebele', 'education_level',
    'primary_school', 'secondary_school', 'college', 'university', 'department', 'other_department',
    'bank_name', 'other_bank_name', 'bank_account', 'job_level', 'other_job_level',
    'marital_status', 'other_marital_status', 'warranty_status', 'person_name', 'person_relationship',
    'warranty_region', 'warranty_zone', 'warranty_woreda', 'warranty_kebele', 'warranty_email',
    'phone', 'warranty_type', 'warranty_amount', 'warranty_address', 'warranty_start_date',
    'warranty_end_date', 'warranty_notes', 'scan_file', 'criminal_status', 'criminal_type',
    'criminal_date', 'criminal_location', 'criminal_court', 'criminal_description', 'criminal_sentence',
    'criminal_status_current', 'criminal_file', 'criminal_additional_docs', 'criminal_notes',
    'fin_id', 'fin_scan', 'loan_status', 'loan_type', 'loan_amount', 'loan_lender', 'loan_account',
    'loan_start_date', 'loan_end_date', 'monthly_payment', 'remaining_balance', 'loan_status_current',
    'loan_collateral', 'loan_purpose', 'loan_file', 'loan_payment_proof', 'loan_notes',
    'leave_request', 'leave_type', 'leave_duration', 'leave_start_date', 'leave_end_date',
    'leave_reason', 'leave_contact', 'leave_supervisor', 'leave_address', 'leave_medical_cert',
    'leave_supporting_docs', 'leave_notes', 'leave_document', 'email', 'phone_number',
    'department_assigned', 'position', 'join_date', 'salary', 'employment_type', 'status',
    'address', 'emergency_contact', 'language', 'other_language', 'documents', 'created_by'
];

echo "<p><strong>Fields in INSERT statement: " . count($insert_fields) . "</strong></p>";

// Compare fields
echo "<h3>4. Field Comparison:</h3>";
$missing_in_db = array_diff($insert_fields, $fields);
$missing_in_insert = array_diff($fields, $insert_fields);

if (!empty($missing_in_db)) {
    echo "<p><strong>❌ Fields in INSERT but missing in database:</strong></p>";
    echo "<ul>";
    foreach ($missing_in_db as $field) {
        echo "<li style='color: red;'>$field</li>";
    }
    echo "</ul>";
}

if (!empty($missing_in_insert)) {
    echo "<p><strong>⚠️ Fields in database but missing in INSERT:</strong></p>";
    echo "<ul>";
    foreach ($missing_in_insert as $field) {
        echo "<li style='color: orange;'>$field</li>";
    }
    echo "</ul>";
}

if (empty($missing_in_db) && empty($missing_in_insert)) {
    echo "<p>✅ All fields match perfectly!</p>";
}

// Test a simple insert
echo "<h3>5. Test Simple Insert:</h3>";
try {
    $test_id = "TEST-" . date('Y') . "-" . rand(1000, 9999);
    
    // Try minimal insert first
    $sql = "INSERT INTO employees (employee_id, first_name, last_name, gender, email, status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo "❌ Prepare failed: " . $conn->error;
    } else {
        $first_name = "Test";
        $last_name = "Employee";
        $gender = "male";
        $email = "test@example.com";
        $status = "active";
        
        $stmt->bind_param("ssssss", $test_id, $first_name, $last_name, $gender, $email, $status);
        
        if ($stmt->execute()) {
            echo "✅ Simple insert successful! Test ID: $test_id";
            
            // Clean up test record
            $conn->query("DELETE FROM employees WHERE employee_id = '$test_id'");
        } else {
            echo "❌ Simple insert failed: " . $stmt->error;
        }
        $stmt->close();
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage();
}

$conn->close();
?>