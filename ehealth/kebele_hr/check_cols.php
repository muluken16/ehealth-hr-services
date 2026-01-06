<?php
require_once '../db.php';
$conn = getDBConnection();
$res = $conn->query("SHOW COLUMNS FROM employees");
$cols = [];
while($r = $res->fetch_assoc()) $cols[] = $r['Field'];
echo json_encode($cols);
?>
