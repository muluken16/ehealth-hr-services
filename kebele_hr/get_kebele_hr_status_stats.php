<?php
session_start();
// Ensure Kebele is set
if (!isset($_SESSION['kebele'])) {
    $_SESSION['kebele'] = 'Kebele 1';
}
$user_kebele = $_SESSION['kebele'];

include dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

// Get status distribution for employees in this kebele
$stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM employees WHERE kebele = ? GROUP BY status");
$stmt->bind_param("s", $user_kebele);
$stmt->execute();
$result = $stmt->get_result();

$stats = [];
while ($row = $result->fetch_assoc()) {
    $stats[$row['status'] ?: 'Unknown'] = (int)$row['count'];
}

$conn->close();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $stats]);
?>