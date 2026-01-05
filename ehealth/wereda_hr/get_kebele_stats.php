<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['role'])) {
    $_SESSION['woreda'] = 'Woreda 1';
}
$user_woreda = $_SESSION['woreda'] ?? 'Woreda 1';

include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

// Fetch status counts grouped by kebele
$stmt = $conn->prepare("SELECT kebele, status, COUNT(*) as count FROM employees WHERE woreda LIKE ? GROUP BY kebele, status");
$woreda_param = "%$user_woreda%";
$stmt->bind_param("s", $woreda_param);
$stmt->execute();
$result = $stmt->get_result();

$stats = [];
while ($row = $result->fetch_assoc()) {
    $kebele = $row['kebele'] ?: 'Unknown';
    $status = strtolower(trim($row['status']));
    
    if (!isset($stats[$kebele])) {
        $stats[$kebele] = ['active' => 0, 'on-leave' => 0, 'inactive' => 0];
    }
    
    // Map database status to standard keys
    if ($status === 'active') {
        $stats[$kebele]['active'] += (int)$row['count'];
    } elseif ($status === 'on-leave' || $status === 'on leave') {
        $stats[$kebele]['on-leave'] += (int)$row['count'];
    } else {
        $stats[$kebele]['inactive'] += (int)$row['count'];
    }
}

// Ensure kebeles are sorted naturally (Kebele 1, Kebele 2, ..., Kebele 10)
uksort($stats, 'strnatcasecmp');

$conn->close();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $stats]);
?>