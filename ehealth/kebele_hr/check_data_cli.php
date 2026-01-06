<?php
require_once '../db.php';
$conn = getDBConnection();
$res = $conn->query("SELECT id, first_name, last_name, kebele, working_kebele FROM employees");
while($r = $res->fetch_assoc()) {
    echo "ID: " . $r['id'] . " | Name: ". $r['first_name'] . " " . $r['last_name'] . " | Res: " . $r['kebele'] . " | Work: " . $r['working_kebele'] . "\n";
}
?>
