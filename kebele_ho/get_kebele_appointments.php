<?php
include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

// Assume kebele for demo
$kebele = 'Kebele 01';

// Get appointments for patients in this kebele
$result = $conn->query("SELECT a.*, p.first_name, p.last_name FROM appointments a JOIN patients p ON a.patient_id = p.id WHERE p.kebele = '$kebele' AND a.appointment_date = CURDATE() ORDER BY a.appointment_time");

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($appointments);
?>