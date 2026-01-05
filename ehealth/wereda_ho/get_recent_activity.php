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

// Get recent activities (last 5)
$activities = [];
$activities_query = $conn->query("
    (SELECT 'appointment' as type, CONCAT('New appointment scheduled for ', p.first_name, ' ', p.last_name) as text, a.created_at as time FROM appointments a JOIN patients p ON a.patient_id = p.id WHERE a.woreda = '$user_woreda' ORDER BY a.created_at DESC LIMIT 2)
    UNION ALL
    (SELECT 'patient' as type, CONCAT('New patient registered: ', first_name, ' ', last_name) as text, created_at as time FROM patients WHERE woreda = '$user_woreda' ORDER BY created_at DESC LIMIT 2)
    UNION ALL
    (SELECT 'emergency' as type, CONCAT('Emergency reported: ', incident_type) as text, created_at as time FROM emergency_responses WHERE woreda = '$user_woreda' ORDER BY created_at DESC LIMIT 1)
    ORDER BY time DESC LIMIT 5
");

while ($activity = $activities_query->fetch_assoc()) {
    $activities[] = [
        'type' => $activity['type'],
        'text' => $activity['text'],
        'time' => date('M j, Y g:i A', strtotime($activity['time']))
    ];
}

echo json_encode([
    'success' => true,
    'activities' => $activities
]);

$conn->close();
?>