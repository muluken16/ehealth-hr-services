<?php
session_start();
if (!isset($_SESSION['woreda']))
    $_SESSION['woreda'] = 'Woreda 1';
if (!isset($_SESSION['kebele']))
    $_SESSION['kebele'] = '01';

$user_woreda = $_SESSION['woreda'];
$user_kebele = $_SESSION['kebele'];

include dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

// Get gender distribution for employees in this kebele
$stmt = $conn->prepare("SELECT gender, COUNT(*) as count FROM employees WHERE working_woreda = ? AND working_kebele = ? GROUP BY gender");
$stmt->bind_param("ss", $user_woreda, $user_kebele);
$stmt->execute();
$result = $stmt->get_result();

$stats = [];
while ($row = $result->fetch_assoc()) {
    $stats[$row['gender'] ?: 'Other'] = (int) $row['count'];
}

$conn->close();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $stats]);
?>