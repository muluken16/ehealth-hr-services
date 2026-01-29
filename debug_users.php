<?php
require_once 'db.php';
$conn = getDBConnection();
$result = $conn->query("SELECT id, email, role, name FROM users");
echo "<h2>Current Users in Database:</h2>";
if ($result->num_rows > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Email</th><th>Role</th><th>Name</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>".$row["id"]."</td><td>".$row["email"]."</td><td>".$row["role"]."</td><td>".$row["name"]."</td></tr>";
    }
    echo "</table>";
} else {
    echo "0 results";
}
$conn->close();
?>
