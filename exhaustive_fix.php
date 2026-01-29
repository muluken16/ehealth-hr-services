<?php
require_once 'db.php';
$conn = getDBConnection();
$target_cols = [
    'education_file' => 'TEXT',
    'employment_agreement' => 'TEXT',
    'guarantor_photo' => 'VARCHAR(255)'
];

echo "Checking employees table schema...\n";

foreach ($target_cols as $col => $def) {
    $res = $conn->query("SHOW COLUMNS FROM employees LIKE '$col'");
    if ($res->num_rows == 0) {
        echo "Column '$col' is MISSING. Adding it now...\n";
        if ($conn->query("ALTER TABLE employees ADD COLUMN $col $def")) {
            echo "Successfully added '$col'.\n";
        } else {
            echo "Error adding '$col': " . $conn->error . "\n";
        }
    } else {
        echo "Column '$col' already exists.\n";
    }
}

echo "Final schema check for 'employees':\n";
$res = $conn->query("DESCRIBE employees");
$cols = [];
while ($row = $res->fetch_assoc())
    $cols[] = $row['Field'];
if (in_array('education_file', $cols)) {
    echo "VERIFIED: 'education_file' is present.\n";
} else {
    echo "FAILURE: 'education_file' is STILL MISSING! This is very strange.\n";
}
?>