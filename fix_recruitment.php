<?php
require_once 'db.php';
$conn = getDBConnection();

echo "<h2>Recruitment Module Fix</h2>";

// Check and add kebele column to job_postings
echo "<h3>Checking job_postings table...</h3>";
$result = $conn->query("SHOW COLUMNS FROM job_postings LIKE 'kebele'");
if ($result && $result->num_rows > 0) {
    echo "✓ kebele column exists<br>";
} else {
    echo "✗ kebele column does NOT exist, adding...<br>";
    if ($conn->query("ALTER TABLE job_postings ADD COLUMN kebele VARCHAR(50) NULL AFTER woreda")) {
        echo "✓ Added kebele column successfully<br>";
    } else {
        echo "✗ Error: " . $conn->error . "<br>";
    }
}

// Show current table structure
echo "<h3>Current job_postings table structure:</h3>";
$cols = $conn->query("DESCRIBE job_postings");
while ($row = $cols->fetch_assoc()) {
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
}

echo "<h3>Testing INSERT with sample data...</h3>";
// Test insert
$test_sql = "INSERT INTO job_postings (title, department, description, requirements, employment_type, salary_range, location, application_deadline, status, posted_by, kebele) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($test_sql);
if ($stmt) {
    $title = "Test Job";
    $dept = "Test Department";
    $desc = "Test Description";
    $req = "Test Requirements";
    $etype = "full-time";
    $salary = "5000-8000";
    $location = "Test Location";
    $deadline = "2025-12-31";
    $status = "open";
    $posted_by = 1;
    $kebele = "Kebele 1";

    $stmt->bind_param("ssssssssssi", $title, $dept, $desc, $req, $etype, $salary, $location, $deadline, $status, $posted_by, $kebele);

    if ($stmt->execute()) {
        echo "✓ Test INSERT successful! Job ID: " . $conn->insert_id . "<br>";
        // Clean up test data
        $conn->query("DELETE FROM job_postings WHERE title = 'Test Job'");
        echo "✓ Test data cleaned up<br>";
    } else {
        echo "✗ Test INSERT failed: " . $stmt->error . "<br>";
    }
    $stmt->close();
} else {
    echo "✗ Prepare failed: " . $conn->error . "<br>";
}

echo "<h3>Done! The recruitment module should now work.</h3>";
$conn->close();
?>
<p><a href="kebele_hr/hr-recruitment.php">Go to Recruitment Page</a></p>