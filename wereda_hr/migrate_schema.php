<?php
require_once dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

// Add woreda column to job_postings if not exists
$result = $conn->query("SHOW COLUMNS FROM job_postings LIKE 'woreda'");
if ($result->num_rows == 0) {
    if ($conn->query("ALTER TABLE job_postings ADD COLUMN woreda VARCHAR(50) NULL AFTER posted_by")) {
        echo "✅ Added woreda column to job_postings\n";
    } else {
        echo "❌ Error adding woreda column to job_postings: " . $conn->error . "\n";
    }
}

// Add woreda column to training_sessions if not exists
$result = $conn->query("SHOW COLUMNS FROM training_sessions LIKE 'woreda'");
if ($result->num_rows == 0) {
    if ($conn->query("ALTER TABLE training_sessions ADD COLUMN woreda VARCHAR(50) NULL AFTER created_by")) {
        echo "✅ Added woreda column to training_sessions\n";
    } else {
        echo "❌ Error adding woreda column to training_sessions: " . $conn->error . "\n";
    }
}

// Fix education_level column name in employees if needed (casing)
// Based on db.php it is 'education_level' correctly.

$conn->close();
?>
