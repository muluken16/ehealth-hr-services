<?php
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "Synchronizing Database Attributes from Code Attributes...\n";

// 1. Ensure all NEW fields from code are in DB
$required = [
    'photo' => 'VARCHAR(255)',
    'credit_status' => 'VARCHAR(50)',
    'credit_details' => 'TEXT',
    'criminal_record_details' => 'TEXT',
    'national_id_details' => 'TEXT',
    'fin_scan' => 'VARCHAR(255)'
];

$res = $conn->query("DESCRIBE employees");
$existing = [];
while($row = $res->fetch_assoc()) $existing[] = $row['Field'];

foreach ($required as $f => $t) {
    if (!in_array($f, $existing)) {
        $conn->query("ALTER TABLE employees ADD COLUMN $f $t");
        echo "Added missing column: $f\n";
    }
}

// 2. Remove OLD fields no longer in code (Guarantor Photo)
if (in_array('guarantor_photo', $existing)) {
    $conn->query("ALTER TABLE employees DROP COLUMN guarantor_photo");
    echo "Removed obsolete column: guarantor_photo\n";
}

// 3. Fix Column Types for consistent data handling (if needed)
// Ensure ENUMs have correct options if we rely on them
$conn->query("ALTER TABLE employees MODIFY COLUMN criminal_status ENUM('yes', 'no') DEFAULT 'no'");
$conn->query("ALTER TABLE employees MODIFY COLUMN loan_status ENUM('yes', 'no') DEFAULT 'no'");

echo "Database synchronization complete.\n";
$conn->close();
?>
