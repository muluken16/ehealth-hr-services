<?php
session_start();
if (!isset($_SESSION['kebele'])) { $_SESSION['kebele'] = 'Kebele 1'; }
$user_kebele = $_SESSION['kebele'];

include dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

$stmt = $conn->prepare("SELECT department_assigned, COUNT(*) as count FROM employees WHERE kebele LIKE ? GROUP BY department_assigned");
$kebele_param = "%$user_kebele%";
$stmt->bind_param("s", $kebele_param);
$stmt->execute();
$result = $stmt->get_result();

$stats = [];
while ($row = $result->fetch_assoc()) {
    $stats[$row['department_assigned'] ?: 'Unknown'] = (int)$row['count'];
}

$conn->close();
header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $stats]);
?>