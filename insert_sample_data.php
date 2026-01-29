<?php
include 'db.php';
$conn = getDBConnection();

// Insert sample patients
$patients = [
    ['John', 'Doe', '1985-01-15', 'male', '+251911123456', 'john.doe@email.com', '123 Main St, Addis Ababa', 'O+', 'Hypertension', 'Emergency Contact: Jane Doe', 'Zone A', 'Wereda 1', 'Kebele 1'],
    ['Mary', 'Smith', '1990-05-20', 'female', '+251922654321', 'mary.smith@email.com', '456 Oak Ave, Addis Ababa', 'A+', 'Diabetes', 'Emergency Contact: Bob Smith', 'Zone A', 'Wereda 1', 'Kebele 2'],
    ['Robert', 'Wilson', '1978-11-10', 'male', '+251933789012', 'robert.wilson@email.com', '789 Pine St, Addis Ababa', 'B+', 'Asthma', 'Emergency Contact: Sarah Wilson', 'Zone A', 'Wereda 2', 'Kebele 1'],
    ['Lisa', 'Brown', '1982-03-25', 'female', '+251944567890', 'lisa.brown@email.com', '321 Elm St, Addis Ababa', 'AB+', 'None', 'Emergency Contact: Mike Brown', 'Zone A', 'Wereda 2', 'Kebele 2'],
    ['David', 'Miller', '1995-07-08', 'male', '+251955234567', 'david.miller@email.com', '654 Cedar St, Addis Ababa', 'O-', 'Allergies', 'Emergency Contact: Anna Miller', 'Zone A', 'Wereda 1', 'Kebele 1'],
];

foreach ($patients as $patient) {
    $stmt = $conn->prepare("INSERT INTO patients (first_name, last_name, date_of_birth, gender, phone, email, address, blood_type, medical_history, emergency_contact, zone, woreda, kebele) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssssss", $patient[0], $patient[1], $patient[2], $patient[3], $patient[4], $patient[5], $patient[6], $patient[7], $patient[8], $patient[9], $patient[10], $patient[11], $patient[12]);
    $stmt->execute();
    $stmt->close();
}

// Insert sample appointments
$appointments = [
    [1, 'Dr. Sarah Johnson', '2024-01-15', '09:00:00', 'Cardiology', 'confirmed', 'Regular checkup', 'Zone A', 'Wereda 1', 'Kebele 1', 2],
    [2, 'Dr. Michael Chen', '2024-01-15', '10:30:00', 'Pediatrics', 'pending', 'Follow-up visit', 'Zone A', 'Wereda 1', 'Kebele 2', 2],
    [3, 'Dr. James Lee', '2024-01-15', '11:15:00', 'Orthopedics', 'confirmed', 'Consultation', 'Zone A', 'Wereda 2', 'Kebele 1', 2],
    [4, 'Dr. Sarah Johnson', '2024-01-15', '14:00:00', 'General Medicine', 'cancelled', 'Annual physical', 'Zone A', 'Wereda 2', 'Kebele 2', 2],
    [5, 'Dr. Patricia Garcia', '2024-01-15', '15:30:00', 'Dermatology', 'completed', 'Skin check', 'Zone A', 'Wereda 1', 'Kebele 1', 2],
];

foreach ($appointments as $appointment) {
    $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_name, appointment_date, appointment_time, department, status, notes, zone, woreda, kebele, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssssss", $appointment[0], $appointment[1], $appointment[2], $appointment[3], $appointment[4], $appointment[5], $appointment[6], $appointment[7], $appointment[8], $appointment[9], $appointment[10]);
    $stmt->execute();
    $stmt->close();
}

// Insert sample inventory
$inventory = [
    ['Paracetamol 500mg', 'Medicine', 150, 'tablets', '2025-12-31', 'PharmaCorp', 20, 'Zone A', 'Wereda 1', 'Kebele 1'],
    ['Ibuprofen 200mg', 'Medicine', 200, 'tablets', '2025-10-15', 'MediSupply', 25, 'Zone A', 'Wereda 1', 'Kebele 2'],
    ['Aspirin 100mg', 'Medicine', 100, 'tablets', '2025-08-20', 'HealthPharm', 15, 'Zone A', 'Wereda 2', 'Kebele 1'],
    ['Amoxicillin 500mg', 'Antibiotic', 75, 'capsules', '2025-06-30', 'BioMed', 10, 'Zone A', 'Wereda 2', 'Kebele 2'],
    ['Bandages 5cm', 'Supplies', 500, 'pieces', NULL, 'MediSupply', 50, 'Zone A', 'Wereda 1', 'Kebele 1'],
];

foreach ($inventory as $item) {
    $stmt = $conn->prepare("INSERT INTO inventory (item_name, category, quantity, unit, expiry_date, supplier, min_stock_level, zone, woreda, kebele) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisssisss", $item[0], $item[1], $item[2], $item[3], $item[4], $item[5], $item[6], $item[7], $item[8], $item[9]);
    $stmt->execute();
    $stmt->close();
}

// Insert sample reports
$reports = [
    ['Patient Statistics', 'Monthly Patient Statistics Report', 'Total patients: 1247, New registrations: 45', 2, 'Zone A', NULL, NULL, '2024-01-01', '2024-01-31'],
    ['Appointment Summary', 'Appointment Management Report', 'Total appointments: 320, Completed: 280, Cancelled: 15', 2, 'Zone A', NULL, NULL, '2024-01-01', '2024-01-31'],
    ['Inventory Status', 'Zone Inventory Report', 'Total items: 5 categories, Low stock alerts: 2 items', 2, 'Zone A', NULL, NULL, '2024-01-01', '2024-01-31'],
];

foreach ($reports as $report) {
    $stmt = $conn->prepare("INSERT INTO reports (type, title, content, generated_by, zone, woreda, kebele, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisssss", $report[0], $report[1], $report[2], $report[3], $report[4], $report[5], $report[6], $report[7], $report[8]);
    $stmt->execute();
    $stmt->close();
}

// Insert sample emergency responses
$emergencies = [
    ['Medical Emergency', 'Patient with severe chest pain', 'Hospital A, Addis Ababa', 'high', 'resolved', 2, 2, 'Zone A', 'Wereda 1', 'Kebele 1'],
    ['Accident', 'Car accident with multiple injuries', 'Highway 5, Addis Ababa', 'critical', 'responding', 2, 2, 'Zone A', 'Wereda 2', 'Kebele 2'],
];

foreach ($emergencies as $emergency) {
    $stmt = $conn->prepare("INSERT INTO emergency_responses (incident_type, description, location, severity, status, reported_by, assigned_to, zone, woreda, kebele) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiisss", $emergency[0], $emergency[1], $emergency[2], $emergency[3], $emergency[4], $emergency[5], $emergency[6], $emergency[7], $emergency[8], $emergency[9]);
    $stmt->execute();
    $stmt->close();
}

// Insert sample quality assurance
$qa = [
    ['Hospital A', 'Facility Assessment', 85.5, 100, 'Good overall performance, minor issues with record keeping', 'Improve documentation processes', 2, 'Zone A', 'Wereda 1', 'Kebele 1', '2024-01-10', '2024-07-10'],
    ['Clinic B', 'Quality Audit', 92.0, 100, 'Excellent patient care standards', 'Continue current practices', 2, 'Zone A', 'Wereda 2', 'Kebele 1', '2024-01-15', '2024-07-15'],
];

foreach ($qa as $assessment) {
    $stmt = $conn->prepare("INSERT INTO quality_assurance (facility_name, assessment_type, score, total_score, findings, recommendations, assessed_by, zone, woreda, kebele, assessment_date, next_assessment_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sddsssisssss", $assessment[0], $assessment[1], $assessment[2], $assessment[3], $assessment[4], $assessment[5], $assessment[6], $assessment[7], $assessment[8], $assessment[9], $assessment[10], $assessment[11]);
    $stmt->execute();
    $stmt->close();
}

echo "Sample data inserted successfully!";
$conn->close();
?>