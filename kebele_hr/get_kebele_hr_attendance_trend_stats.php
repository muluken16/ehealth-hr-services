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

// Get attendance for the last 15 days
$sql = "SELECT 
    attendance_date,
    COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
    COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_count,
    COUNT(CASE WHEN status = 'on-leave' THEN 1 END) as leave_count
FROM attendance 
WHERE kebele = ? 
AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
GROUP BY attendance_date
ORDER BY attendance_date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_kebele);
$stmt->execute();
$result = $stmt->get_result();

$stats = [
    'dates' => [],
    'present' => [],
    'absent' => [],
    'leave' => []
];

while ($row = $result->fetch_assoc()) {
    $stats['dates'][] = $row['attendance_date'];
    $stats['present'][] = (int) $row['present_count'];
    $stats['absent'][] = (int) $row['absent_count'];
    $stats['leave'][] = (int) $row['leave_count'];
}

$conn->close();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $stats]);
?>