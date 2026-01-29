<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Fix Employee Insert</h2>";

// Connect to database
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get actual table structure
$result = $conn->query("DESCRIBE employees");
$db_fields = [];
while ($row = $result->fetch_assoc()) {
    $db_fields[] = $row['Field'];
}

echo "<h3>Database Fields (" . count($db_fields) . "):</h3>";
echo "<pre>" . implode(", ", $db_fields) . "</pre>";

// Define the correct INSERT fields based on actual database
$correct_fields = array_filter($db_fields, function($field) {
    return !in_array($field, ['id', 'created_at', 'updated_at']); // Exclude auto fields
});

echo "<h3>Fields for INSERT (" . count($correct_fields) . "):</h3>";
echo "<pre>" . implode(", ", $correct_fields) . "</pre>";

// Generate corrected PHP code
echo "<h3>Corrected PHP Code:</h3>";
echo "<textarea style='width: 100%; height: 200px; font-family: monospace;'>";

$placeholders = implode(',', array_fill(0, count($correct_fields), '?'));
echo '$sql = "INSERT INTO employees (' . "\n";
echo '    ' . implode(', ', $correct_fields) . "\n";
echo ') VALUES (' . $placeholders . ')";' . "\n\n";

echo '$stmt = $conn->prepare($sql);' . "\n";
echo '$stmt->bind_param("' . str_repeat('s', count($correct_fields)) . '", ' . "\n";

// Generate variable list
$var_list = [];
foreach ($correct_fields as $field) {
    $var_list[] = '$' . $field;
}
echo '    ' . implode(', ', $var_list) . "\n";
echo ');';

echo "</textarea>";

// Test with minimal required fields
echo "<h3>Testing with Required Fields Only:</h3>";
$required_fields = ['employee_id', 'first_name', 'last_name', 'gender', 'date_of_birth', 'email', 'status'];

try {
    $test_id = "FIX-" . date('Y') . "-" . rand(1000, 9999);
    
    $sql = "INSERT INTO employees (" . implode(', ', $required_fields) . ") VALUES (" . implode(',', array_fill(0, count($required_fields), '?')) . ")";
    
    echo "<p>SQL: <code>$sql</code></p>";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "❌ Prepare failed: " . $conn->error;
    } else {
        $values = [
            $test_id,
            'Test',
            'Employee',
            'male',
            '1990-01-01',
            'test@example.com',
            'active'
        ];
        
        $stmt->bind_param(str_repeat('s', count($values)), ...$values);
        
        if ($stmt->execute()) {
            echo "✅ Test insert successful! ID: $test_id<br>";
            
            // Verify the insert
            $check = $conn->query("SELECT * FROM employees WHERE employee_id = '$test_id'");
            if ($check && $check->num_rows > 0) {
                echo "✅ Record verified in database<br>";
                
                // Clean up
                $conn->query("DELETE FROM employees WHERE employee_id = '$test_id'");
                echo "✅ Test record cleaned up<br>";
            }
        } else {
            echo "❌ Test insert failed: " . $stmt->error;
        }
        $stmt->close();
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage();
}

$conn->close();
?>