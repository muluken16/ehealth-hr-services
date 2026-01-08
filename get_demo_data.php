<?php
require_once 'db.php';
$conn = getDBConnection();
$res = $conn->query("SELECT employee_id, first_name, last_name FROM employees LIMIT 5");
$emps = [];
while($row = $res->fetch_assoc()) $emps[] = $row;
file_put_contents('temp_emp_list.json', json_encode($emps));
?>
