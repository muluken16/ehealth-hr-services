<?php
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

echo "Updating schema to support multiple files (TEXT type)...\n";

$sqls = [
    "ALTER TABLE employees MODIFY COLUMN education_file TEXT",
    "ALTER TABLE employees MODIFY COLUMN employment_agreement TEXT"
];

foreach ($sqls as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Executed: $sql\n";
    } else {
        echo "Error: $sql - " . $conn->error . "\n";
    }
}

$conn->close();
?>
