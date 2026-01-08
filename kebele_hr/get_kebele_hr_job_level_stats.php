<?php
session_start();
if (!isset($_SESSION['kebele'])) { $_SESSION['kebele'] = 'Kebele 1'; }
$user_kebele = $_SESSION['kebele'];

include dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

$stmt = $conn->prepare("SELECT job_level, COUNT(*) as count FROM employees WHERE kebele = ? GROUP BY job_level");
$stmt->bind_param("s", $user_kebele);
$stmt->execute();
$result = $stmt->get_result();

$stats = [];
while ($row = $result->fetch_assoc()) {
    $stats[$row['job_level'] ?: 'Unknown'] = (int)$row['count'];
}

$conn->close();
header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $stats]);
?>
