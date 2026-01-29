<?php
require_once 'db.php';
$conn = getDBConnection();

// Add kebele column if not exists
$result = $conn->query("SHOW COLUMNS FROM job_postings LIKE 'kebele'");
if ($result->num_rows == 0) {
    if ($conn->query("ALTER TABLE job_postings ADD COLUMN kebele VARCHAR(50) NULL AFTER woreda")) {
        echo "Added kebele column successfully<br>";
    } else {
        echo "Error adding kebele column: " . $conn->error . "<br>";
    }
} else {
    echo "kebele column already exists<br>";
}
$conn->close();
echo "Done!";
