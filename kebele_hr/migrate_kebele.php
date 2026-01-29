<?php
require_once '../db.php';
$conn = getDBConnection();

// Update working_kebele to match residence kebele where work_kebele is empty
$sql = "UPDATE employees SET working_kebele = kebele WHERE (working_kebele IS NULL OR working_kebele = '') AND (kebele IS NOT NULL AND kebele != '')";
if ($conn->query($sql)) {
    echo "Migration Success: Updated " . $conn->affected_rows . " records.";
} else {
    echo "Migration Failed: " . $conn->error;
}
?>
