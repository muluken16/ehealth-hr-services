<?php
session_start();
if (!isset($_SESSION['kebele'])) { $_SESSION['kebele'] = 'Kebele 1'; }
$user_kebele = $_SESSION['kebele'];

include dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

$stmt = $conn->prepare("SELECT department_assigned, COUNT(*) as count FROM employees WHERE working_kebele = ? GROUP BY department_assigned");
$stmt->bind_param("s", $user_kebele);
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