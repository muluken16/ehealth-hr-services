<?php
include dirname(__DIR__) . '/db.php';

$conn = getDBConnection();

// Assume kebele for demo
$kebele = 'Kebele 01';

// Get total patients in this kebele
$result = $conn->query("SELECT COUNT(*) as total FROM patients WHERE kebele = '$kebele'");
$total_patients = $result->fetch_assoc()['total'];

// Get today's appointments for this kebele
$result = $conn->query("SELECT COUNT(*) as total FROM appointments a JOIN patients p ON a.patient_id = p.id WHERE a.appointment_date = CURDATE() AND p.kebele = '$kebele'");
$total_appointments = $result->fetch_assoc()['total'];

// Get active doctors in this kebele (employees with medical department)
$result = $conn->query("SELECT COUNT(*) as total FROM employees WHERE (department_assigned LIKE '%Medical%' OR department_assigned LIKE '%Cardiology%' OR department_assigned LIKE '%Pediatrics%' OR department_assigned LIKE '%Surgery%') AND kebele = '$kebele'");
$active_doctors = $result->fetch_assoc()['total'];

// Get emergency cases in this kebele
$result = $conn->query("SELECT COUNT(*) as total FROM emergency_responses WHERE kebele = '$kebele'");
$emergency_cases = $result->fetch_assoc()['total'];

$conn->close();

header('Content-Type: application/json');
echo json_encode([
    'total_patients' => $total_patients,
    'total_appointments' => $total_appointments,
    'active_doctors' => $active_doctors,
    'emergency_cases' => $emergency_cases
]);
?>