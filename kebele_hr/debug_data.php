<?php
require_once '../db.php';
$conn = getDBConnection();

session_start();
$session_kebele = $_SESSION['kebele'] ?? 'Not Set';

echo "Session Kebele: $session_kebele <br>";

$res = $conn->query("SELECT id, first_name, last_name, kebele, working_kebele FROM employees LIMIT 20");
echo "<h3>Employees Table Data</h3>";
echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Kebele (Residence)</th><th>Working Kebele</th></tr>";
while($row = $res->fetch_assoc()) {
    echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['first_name']} {$row['last_name']}</td>
            <td>{$row['kebele']}</td>
            <td>{$row['working_kebele']}</td>
          </tr>";
}
echo "</table>";
?>
