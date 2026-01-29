<?php
require_once 'db.php';
$conn = getDBConnection();

// Check if kebele column exists
$result = $conn->query("SHOW COLUMNS FROM job_postings LIKE 'kebele'");
if ($result->num_rows > 0) {
    echo "✓ kebele column exists in job_postings table<br>";
} else {
    echo "✗ kebele column does NOT exist in job_postings table<br>";
    // Add it
    if ($conn->query("ALTER TABLE job_postings ADD COLUMN kebele VARCHAR(50) NULL AFTER woreda")) {
        echo "✓ Added kebele column<br>";
    } else {
        echo "✗ Error: " . $conn->error . "<br>";
    }
}

// Show table structure
echo "<br><strong>Table structure:</strong><br>";
$cols = $conn->query("DESCRIBE job_postings");
while ($row = $cols->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "<br>";
}

$conn->close();
