<?php
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Add requested_by if it doesn't exist
$res = $conn->query("SHOW COLUMNS FROM leave_requests LIKE 'requested_by'");
if ($res->num_rows == 0) {
    echo "Adding requested_by column...\n";
    $conn->query("ALTER TABLE leave_requests ADD COLUMN requested_by INT(6) UNSIGNED AFTER status");
}

// 2. Add rejection_reason if it doesn't exist
$res = $conn->query("SHOW COLUMNS FROM leave_requests LIKE 'rejection_reason'");
if ($res->num_rows == 0) {
    echo "Adding rejection_reason column...\n";
    $conn->query("ALTER TABLE leave_requests ADD COLUMN rejection_reason TEXT AFTER requested_by");
}

// 3. Update leave_type ENUM
echo "Updating leave_type ENUM...\n";
$conn->query("ALTER TABLE leave_requests MODIFY COLUMN leave_type ENUM('annual','sick','maternity','paternity','emergency','marriage','bereavement','special') NOT NULL");

// 4. Update status ENUM if needed (already seems ok but good to verify)
// Already shows: enum('pending','approved','rejected')

// 5. Add supporting_document if missing
$res = $conn->query("SHOW COLUMNS FROM leave_requests LIKE 'supporting_document'");
if ($res->num_rows == 0) {
    echo "Adding supporting_document column...\n";
    $conn->query("ALTER TABLE leave_requests ADD COLUMN supporting_document VARCHAR(255) AFTER rejection_reason");
}

echo "Database sync complete.\n";
$conn->close();
?>
