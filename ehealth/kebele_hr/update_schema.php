<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to DB
$conn = new mysqli("localhost", "root", "", "ehealth");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check Schema
$result = $conn->query("SHOW COLUMNS FROM employees");
if (!$result) {
    die("Query failed: " . $conn->error);
}

$existing_columns = [];
while ($row = $result->fetch_assoc()) {
    $existing_columns[] = $row['Field'];
}

echo "<h3>Existing Columns:</h3>";
echo implode(", ", $existing_columns);
echo "<hr>";

// List of potentially missing columns to add
$needed_columns = [
    'photo' => "VARCHAR(255) DEFAULT NULL", // Profile picture
    'credit_status' => "VARCHAR(50) DEFAULT 'good'", // Credit/Loan attribute (user requested "cridet aterbit")
    'criminal_record_details' => "TEXT DEFAULT NULL", // "criminal atibite" - maybe details? we have criminal_status
    'national_id_details' => "TEXT DEFAULT NULL", // "all aational atribite" - maybe more details? we have fin_id
    'guarantor_photo' => "VARCHAR(255) DEFAULT NULL" // Guarantor photo?
];

$added = [];
foreach ($needed_columns as $col => $def) {
    if (!in_array($col, $existing_columns)) {
        if ($conn->query("ALTER TABLE employees ADD COLUMN $col $def")) {
            $added[] = $col;
        } else {
            echo "Error adding $col: " . $conn->error . "<br>";
        }
    }
}

if (!empty($added)) {
    echo "<h3>Added Columns:</h3>";
    echo implode(", ", $added);
} else {
    echo "<h3>No new columns added.</h3>";
}

$conn->close();
?>
