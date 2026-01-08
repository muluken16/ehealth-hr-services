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

// Get today's appointments for this woreda
$result = $conn->query("SELECT a.*, p.first_name, p.last_name FROM appointments a JOIN patients p ON a.patient_id = p.id WHERE a.woreda = '$user_woreda' AND a.appointment_date >= CURDATE() ORDER BY a.appointment_date, a.appointment_time LIMIT 10");

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = [
        'patient_name' => $row['first_name'] . ' ' . $row['last_name'],
        'doctor_name' => $row['doctor_name'],
        'time' => date('g:i A', strtotime($row['appointment_time'])),
        'department' => $row['department'],
        'status' => $row['status']
    ];
}

echo json_encode([
    'success' => true,
    'appointments' => $appointments
]);

$conn->close();
?>