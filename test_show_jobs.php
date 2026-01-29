<?php
require_once 'db.php';
$conn = getDBConnection();

echo "<h2>Job Postings in Database</h2>";

$result = $conn->query("SELECT * FROM job_postings ORDER BY posted_at DESC");

if ($result && $result->num_rows > 0) {
    echo "<p>Found " . $result->num_rows . " job(s):</p>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Department</th><th>Status</th><th>Posted By</th><th>Kebele</th><th>Posted At</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . htmlspecialchars($row['department']) . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['posted_by'] . "</td>";
        echo "<td>" . ($row['kebele'] ?? 'NULL') . "</td>";
        echo "<td>" . $row['posted_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No jobs found in the database.</p>";
}

echo "<p><a href='kebele_hr/hr-recruitment.php'>Go to Recruitment Page</a></p>";

$conn->close();
