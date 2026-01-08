<?php
include 'db.php';
$conn = getDBConnection();

// Fix Kebele names by removing leading zeros (e.g., 'Kebele 01' -> 'Kebele 1')
echo "Fixing Kebele names...\n";
for ($i = 1; $i <= 9; $i++) {
    $old = "Kebele 0$i";
    $new = "Kebele $i";
    $conn->query("UPDATE employees SET kebele = '$new' WHERE kebele = '$old'");
    echo "Updated $old to $new\n";
    
    // Also update other tables that might use this
    $conn->query("UPDATE inventory SET kebele = '$new' WHERE kebele = '$old'");
    $conn->query("UPDATE users SET kebele = '$new' WHERE kebele = '$old'");
}

// Ensure plenty of employees are in Kebele 1 for the demo user
echo "Ensuring data for Kebele 1 demo user...\n";
$conn->query("UPDATE employees SET kebele = 'Kebele 1' WHERE kebele = 'Kebele 2' OR kebele = 'Kebele 3' LIMIT 15");

// Check count for Kebele 1
$res = $conn->query("SELECT COUNT(*) as cnt FROM employees WHERE kebele = 'Kebele 1'");
$row = $res->fetch_assoc();
echo "Total employees in Kebele 1: " . $row['cnt'] . "\n";

echo "Done.";
?>
