<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'wereda_health_officer') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include '../db.php';
$conn = getDBConnection();

// Get user's woreda
$user_woreda = $_SESSION['woreda'] ?? 'West Shewa Woreda 1';

// Get monthly appointment data for the last 12 months
$chart_data = [];
for ($i = 11; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $count = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE woreda = '$user_woreda' AND DATE_FORMAT(appointment_date, '%Y-%m') = '$date'")->fetch_assoc()['count'];
    $chart_data[] = $count;
}

echo json_encode([
    'success' => true,
    'data' => $chart_data
]);

$conn->close();
?>