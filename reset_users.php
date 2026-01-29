<?php
require_once 'db.php';
$conn = getDBConnection();
$conn->query("DELETE FROM users");
echo "Users cleared.\n";
$conn->close();

// Now include db.php again to re-seed (it identifies empty table and seeds)
include 'db.php';
echo "Users re-seeded.\n";
?>
