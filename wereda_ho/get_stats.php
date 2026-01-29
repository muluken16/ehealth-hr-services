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

// Get aggregated statistics for woreda
$total_patients = $conn->query("SELECT COUNT(*) as count FROM patients WHERE woreda = '$user_woreda'")->fetch_assoc()['count'];
$today_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE woreda = '$user_woreda' AND appointment_date = CURDATE()")->fetch_assoc()['count'];
$active_doctors = $conn->query("SELECT COUNT(*) as count FROM employees WHERE woreda = '$user_woreda' AND position LIKE '%Doctor%' AND status = 'active'")->fetch_assoc()['count'];
$emergency_cases = $conn->query("SELECT COUNT(*) as count FROM emergency_responses WHERE woreda = '$user_woreda' AND status != 'resolved'")->fetch_assoc()['count'];

echo json_encode([
    'success' => true,
    'stats' => [
        'total_patients' => $total_patients,
        'today_appointments' => $today_appointments,
        'active_doctors' => $active_doctors,
        'emergency_cases' => $emergency_cases
    ]
]);

$conn->close();
?>