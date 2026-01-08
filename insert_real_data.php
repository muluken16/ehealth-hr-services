<?php
include 'db.php';
$conn = getDBConnection();

// Ethiopian administrative divisions
$regions = ['Tigray', 'Afar', 'Amhara', 'Oromia', 'Somali', 'Benishangul-Gumuz', 'SNNPR', 'Gambela', 'Harari', 'Addis Ababa', 'Dire Dawa'];
$zones = [
    'Tigray' => ['Central Zone', 'Eastern Zone', 'Southern Zone', 'Western Zone', 'Northwestern Zone'],
    'Afar' => ['Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5'],
    'Amhara' => ['North Gondar', 'South Gondar', 'North Wollo', 'South Wollo', 'North Shewa', 'East Gojjam', 'West Gojjam', 'Wag Hemra', 'Awi'],
    'Oromia' => ['West Shewa', 'East Shewa', 'North Shewa', 'Arsi', 'West Arsi', 'East Wollega', 'West Wollega', 'Jimma', 'Illubabor', 'Bale', 'Borena'],
    'Somali' => ['Liben', 'Afder', 'Gode', 'Wardher', 'Dollo', 'Nogob', 'Siti', 'Fafan', 'Jarre'],
    'Benishangul-Gumuz' => ['Metekel', 'Kamashi', 'Agnuak', 'Mao Komo'],
    'SNNPR' => ['Gurage', 'Hadiya', 'Kembata Tembaro', 'Sidama', 'Wolayita', 'Gamo Gofa', 'Dawro', 'South Omo'],
    'Gambela' => ['Anuak', 'Mezhenger', 'Nuwer'],
    'Harari' => ['Harari'],
    'Addis Ababa' => ['Bole', 'Yeka', 'Arada', 'Kirkos', 'Akaki Kality', 'Nefas Silk Lafto', 'Kolfe Keranio', 'Gullele', 'Lideta', 'Addis Ketema', 'Tafo'],
    'Dire Dawa' => ['Dire Dawa']
];
$woredas = [
    'Addis Ababa' => [
        'Bole' => ['Bole Michael', 'Bole Medhanealem', 'Kazanchis', 'Piassa'],
        'Yeka' => ['Entoto', 'Shiromeda', 'Mekanisa', 'Geffersa'],
        'Arada' => ['Arada', 'Merkato', 'Piassa', 'St. George']
    ],
    'Oromia' => [
        'West Shewa' => ['Ambo', 'Gedo', 'Dendi', 'Jibat', 'Bako Tibe'],
        'East Shewa' => ['Adama', 'Bishoftu', 'Dubti', 'Metehara', 'Mojo']
    ]
];
$kebeles = ['Kebele 01', 'Kebele 02', 'Kebele 03', 'Kebele 04', 'Kebele 05', 'Kebele 06', 'Kebele 07', 'Kebele 08', 'Kebele 09', 'Kebele 10'];

// Ethiopian names
$male_names = ['Abebe', 'Kebede', 'Tesfaye', 'Alemayehu', 'Tadesse', 'Bekele', 'Mengistu', 'Haile', 'Girma', 'Solomon', 'Dawit', 'Yohannes', 'Mekonnen', 'Assefa', 'Getachew', 'Tsegaye', 'Worku', 'Demeke', 'Mesfin', 'Zewdu'];
$female_names = ['Abeba', 'Worknesh', 'Almaz', 'Tigist', 'Meseret', 'Eleni', 'Senait', 'Hirut', 'Mulu', 'Aster', 'Selam', 'Rahel', 'Betelhem', 'Hanan', 'Fikir', 'Tsion', 'Mahlet', 'Kidist', 'Yordanos', 'Feven'];
$surnames = ['Kebede', 'Tesfaye', 'Bekele', 'Mengistu', 'Haile', 'Girma', 'Solomon', 'Dawit', 'Yohannes', 'Mekonnen', 'Assefa', 'Getachew', 'Tsegaye', 'Worku', 'Demeke', 'Mesfin', 'Zewdu', 'Alemu', 'Tadesse', 'Alemayehu'];

// Health departments
$departments = ['Internal Medicine', 'Pediatrics', 'Surgery', 'Obstetrics & Gynecology', 'Emergency Medicine', 'Radiology', 'Laboratory', 'Pharmacy', 'Dental', 'Ophthalmology', 'ENT', 'Dermatology', 'Psychiatry', 'Cardiology', 'Neurology'];

// Ethiopian health facilities
$facilities = ['Black Lion Hospital', 'St. Paul Hospital Millennium Medical College', 'Tikur Anbessa Specialized Hospital', 'Yekatit 12 Hospital', 'Ras Desta Damtew Hospital', 'Zewditu Memorial Hospital', 'Gandhi Memorial Hospital', 'Alert Hospital', 'MyungSung Christian Medical Center', 'Adama Hospital', 'Hawassa University Hospital', 'Jimma University Hospital'];

// Medical conditions
$conditions = ['Hypertension', 'Diabetes Mellitus', 'Acute Respiratory Infection', 'Malaria', 'Tuberculosis', 'HIV/AIDS', 'Pneumonia', 'Diarrhea', 'Anemia', 'Malnutrition', 'Typhoid Fever', 'Hepatitis', 'Asthma', 'Bronchitis', 'Skin Infection', 'Urinary Tract Infection', 'Pregnancy-related complications', 'Mental Health Disorders'];

// Blood types
$blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

// Suppliers
$suppliers = ['Ethiopian Pharmaceuticals', 'Sheba Pharmaceuticals', 'Julphar Ethiopia', 'MedPharm', 'HealthCare Supplies', 'Medical Equipment Ltd', 'PharmaCorp Ethiopia', 'BioMed Supplies'];

// Job titles
$job_titles = ['Medical Doctor', 'Nurse', 'Pharmacist', 'Laboratory Technician', 'Radiographer', 'Health Officer', 'Midwife', 'Public Health Specialist', 'Medical Administrator', 'IT Specialist', 'Accountant', 'HR Officer', 'Cleaner', 'Security Guard', 'Driver'];

// Education levels
$education_levels = ['Primary School', 'Secondary School', 'Certificate', 'Diploma', 'Bachelor Degree', 'Master Degree', 'PhD', 'Medical Doctor (MD)', 'Specialist Training'];

// Religions
$religions = ['Orthodox Christian', 'Muslim', 'Protestant', 'Catholic', 'Traditional', 'Other'];

// Marital status
$marital_status = ['Single', 'Married', 'Divorced', 'Widowed'];

// Employment types
$employment_types = ['Permanent', 'Contract', 'Part-time', 'Temporary'];

// Leave types
$leave_types = ['annual', 'sick', 'maternity', 'paternity', 'emergency'];

// Training topics
$training_topics = ['Basic Life Support', 'Infection Control', 'Patient Safety', 'Medical Ethics', 'Emergency Response', 'Data Management', 'Quality Assurance', 'Leadership Skills', 'Communication Skills', 'Health Policy'];

// Incident types
$incident_types = ['Medical Emergency', 'Trauma', 'Fire', 'Natural Disaster', 'Disease Outbreak', 'Accident', 'Poisoning', 'Mental Health Crisis', 'Childbirth Emergency', 'Cardiac Arrest'];

// Assessment types
$assessment_types = ['Facility Inspection', 'Quality Audit', 'Patient Satisfaction Survey', 'Staff Performance Review', 'Equipment Maintenance Check', 'Hygiene Assessment', 'Record Keeping Review'];

// Report types
$report_types = ['Monthly Statistics', 'Patient Records', 'Inventory Status', 'Financial Report', 'Quality Assurance', 'Emergency Response', 'Training Report', 'HR Report', 'Facility Performance'];

// Function to get random element from array
function randomElement($array) {
    if (empty($array)) return 'Default';
    return $array[array_rand($array)];
}

// Function to get random zone
function getRandomZone($zones) {
    $all_zones = [];
    foreach ($zones as $region_zones) {
        $all_zones = array_merge($all_zones, $region_zones);
    }
    return randomElement($all_zones);
}

// Function to generate Ethiopian phone number
function generateEthiopianPhone() {
    $prefixes = ['091', '092', '093', '094', '095', '096', '097', '098', '099'];
    return randomElement($prefixes) . rand(1000000, 9999999);
}

// Function to generate employee ID
function generateEmployeeID($region, $zone, $woreda) {
    $year = date('Y');
    $region_code = substr(strtoupper($region), 0, 2);
    $zone_code = substr(strtoupper(str_replace(' ', '', $zone)), 0, 2);
    $woreda_code = substr(strtoupper(str_replace(' ', '', $woreda)), 0, 2);
    $serial = rand(1000, 9999);
    return "HF-{$year}-{$region_code}{$zone_code}{$woreda_code}{$serial}";
}

// Clear existing sample data first
$conn->query("DELETE FROM patients WHERE id > 0");
$conn->query("DELETE FROM appointments WHERE id > 0");
$conn->query("DELETE FROM inventory WHERE id > 0");
$conn->query("DELETE FROM reports WHERE id > 0");
$conn->query("DELETE FROM emergency_responses WHERE id > 0");
$conn->query("DELETE FROM quality_assurance WHERE id > 0");
$conn->query("DELETE FROM employees WHERE id > 0");
$conn->query("DELETE FROM payroll WHERE id > 0");
$conn->query("DELETE FROM leave_requests WHERE id > 0");
$conn->query("DELETE FROM job_postings WHERE id > 0");
$conn->query("DELETE FROM training_sessions WHERE id > 0");

// Insert real employees
echo "Inserting real employees...\n";
$employees_data = [];
for ($i = 0; $i < 100; $i++) {
    $gender = randomElement(['male', 'female']);
    $first_name = $gender == 'male' ? randomElement($male_names) : randomElement($female_names);
    $last_name = randomElement($surnames);
    $region = randomElement($regions);
    $zone = randomElement($zones[$region] ?? ['Zone A']);
    $woreda = randomElement($woredas[$region][$zone] ?? [$zone . ' Woreda 1']);
    $kebele = randomElement($kebeles);
    $employee_id = generateEmployeeID($region, $zone, $woreda);

    $employee = [
        'employee_id' => $employee_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'gender' => $gender,
        'date_of_birth' => date('Y-m-d', strtotime('-' . rand(20, 60) . ' years')),
        'religion' => randomElement($religions),
        'citizenship' => 'Ethiopian',
        'region' => $region,
        'zone' => $zone,
        'woreda' => $woreda,
        'kebele' => $kebele,
        'education_level' => randomElement($education_levels),
        'department' => randomElement($departments),
        'job_level' => randomElement($job_titles),
        'marital_status' => randomElement($marital_status),
        'phone' => generateEthiopianPhone(),
        'email' => strtolower($first_name . '.' . $last_name . '@health.et'),
        'phone_number' => generateEthiopianPhone(),
        'department_assigned' => randomElement($departments),
        'position' => randomElement($job_titles),
        'join_date' => date('Y-m-d', strtotime('-' . rand(0, 10) . ' years')),
        'salary' => rand(5000, 25000),
        'employment_type' => randomElement($employment_types),
        'status' => 'active',
        'address' => $kebele . ', ' . $woreda . ', ' . $zone . ', ' . $region
    ];

    $employees_data[] = $employee;

    $stmt = $conn->prepare("INSERT INTO employees (employee_id, first_name, last_name, gender, date_of_birth, religion, citizenship, region, zone, woreda, kebele, education_level, department, job_level, marital_status, phone, email, phone_number, department_assigned, position, join_date, salary, employment_type, status, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssssssssssssssssss", $employee['employee_id'], $employee['first_name'], $employee['last_name'], $employee['gender'], $employee['date_of_birth'], $employee['religion'], $employee['citizenship'], $employee['region'], $employee['zone'], $employee['woreda'], $employee['kebele'], $employee['education_level'], $employee['department'], $employee['job_level'], $employee['marital_status'], $employee['phone'], $employee['email'], $employee['phone_number'], $employee['department_assigned'], $employee['position'], $employee['join_date'], $employee['salary'], $employee['employment_type'], $employee['status'], $employee['address']);
    $stmt->execute();
    $stmt->close();
}

echo "Inserted " . count($employees_data) . " employees.\n";

// Insert real patients
echo "Inserting real patients...\n";
for ($i = 0; $i < 200; $i++) {
    $gender = randomElement(['male', 'female']);
    $first_name = $gender == 'male' ? randomElement($male_names) : randomElement($female_names);
    $last_name = randomElement($surnames);
    $region = randomElement($regions);
    $zone = randomElement($zones[$region] ?? ['Zone A']);
    $woreda = randomElement($woredas[$region][$zone] ?? [$zone . ' Woreda 1']);
    $kebele = randomElement($kebeles);

    $stmt = $conn->prepare("INSERT INTO patients (first_name, last_name, date_of_birth, gender, phone, email, address, blood_type, medical_history, emergency_contact, zone, woreda, kebele) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $date_of_birth = date('Y-m-d', strtotime('-' . rand(1, 80) . ' years'));
    $phone = generateEthiopianPhone();
    $email = strtolower($first_name . '.' . $last_name . '@gmail.com');
    $address = $kebele . ', ' . $woreda . ', ' . $zone . ', ' . $region;
    $blood_type = randomElement($blood_types);
    $medical_history = randomElement($conditions);
    $emergency_contact = 'Emergency Contact: ' . randomElement($male_names) . ' ' . randomElement($surnames) . ' - ' . generateEthiopianPhone();

    $stmt->bind_param("sssssssssssss", $first_name, $last_name, $date_of_birth, $gender, $phone, $email, $address, $blood_type, $medical_history, $emergency_contact, $zone, $woreda, $kebele);
    $stmt->execute();
    $stmt->close();
}

echo "Inserted 200 patients.\n";

// Insert real appointments
echo "Inserting real appointments...\n";
$result = $conn->query("SELECT id FROM patients");
$patient_ids = [];
while ($row = $result->fetch_assoc()) {
    $patient_ids[] = $row['id'];
}

$result = $conn->query("SELECT id FROM users WHERE role LIKE '%health_officer%'");
$user_ids = [];
while ($row = $result->fetch_assoc()) {
    $user_ids[] = $row['id'];
}

for ($i = 0; $i < 150; $i++) {
    $patient_id = randomElement($patient_ids);
    $doctor_name = 'Dr. ' . randomElement($male_names) . ' ' . randomElement($surnames);
    $appointment_date = date('Y-m-d', strtotime('+' . rand(0, 30) . ' days'));
    $appointment_time = date('H:i:s', strtotime(rand(8, 17) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT)));
    $department = randomElement($departments);
    $status = randomElement(['scheduled', 'confirmed', 'completed', 'cancelled']);
    $notes = 'Regular checkup for ' . randomElement($conditions);
    $zone = randomElement(array_column($zones, 0)); // Simplified
    $woreda = $zone . ' Woreda 1';
    $kebele = randomElement($kebeles);
    $created_by = randomElement($user_ids);

    $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_name, appointment_date, appointment_time, department, status, notes, zone, woreda, kebele, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssssssi", $patient_id, $doctor_name, $appointment_date, $appointment_time, $department, $status, $notes, $zone, $woreda, $kebele, $created_by);
    $stmt->execute();
    $stmt->close();
}

echo "Inserted 150 appointments.\n";

// Insert real inventory
echo "Inserting real inventory...\n";
$medicines = ['Paracetamol 500mg', 'Ibuprofen 200mg', 'Aspirin 100mg', 'Amoxicillin 500mg', 'Ciprofloxacin 500mg', 'Metformin 500mg', 'Omeprazole 20mg', 'Amlodipine 5mg', 'Losartan 50mg', 'Furosemide 40mg', 'Prednisone 5mg', 'Diazepam 5mg', 'Insulin', 'Vitamin D', 'Iron supplements', 'ORS sachets', 'Antimalarial drugs', 'Antibiotics'];
$supplies = ['Bandages 5cm', 'Bandages 10cm', 'Gauze pads', 'Surgical gloves', 'Face masks', 'Syringes 5ml', 'Syringes 10ml', 'IV cannulas', 'Blood collection tubes', 'Test strips', 'Stethoscopes', 'Blood pressure cuffs', 'Thermometers', 'Weighing scales'];

foreach ($medicines as $medicine) {
    $stmt = $conn->prepare("INSERT INTO inventory (item_name, category, quantity, unit, expiry_date, supplier, min_stock_level, zone, woreda, kebele) VALUES (?, 'Medicine', ?, 'tablets', ?, ?, ?, ?, ?, ?)");
    $quantity = rand(50, 500);
    $expiry = date('Y-m-d', strtotime('+' . rand(6, 24) . ' months'));
    $supplier = randomElement($suppliers);
    $min_stock = rand(20, 100);
    $zone = randomElement(array_column($zones, 0));
    $woreda = $zone . ' Woreda 1';
    $kebele = randomElement($kebeles);

    $stmt->bind_param("sisissss", $medicine, $quantity, $expiry, $supplier, $min_stock, $zone, $woreda, $kebele);
    $stmt->execute();
    $stmt->close();
}

foreach ($supplies as $supply) {
    $stmt = $conn->prepare("INSERT INTO inventory (item_name, category, quantity, unit, supplier, min_stock_level, zone, woreda, kebele) VALUES (?, 'Supplies', ?, 'pieces', ?, ?, ?, ?, ?)");
    $quantity = rand(100, 1000);
    $supplier = randomElement($suppliers);
    $min_stock = rand(50, 200);
    $zone = randomElement(array_column($zones, 0));
    $woreda = $zone . ' Woreda 1';
    $kebele = randomElement($kebeles);

    $stmt->bind_param("sisisss", $supply, $quantity, $supplier, $min_stock, $zone, $woreda, $kebele);
    $stmt->execute();
    $stmt->close();
}

echo "Inserted inventory items.\n";

// Insert real reports
echo "Inserting real reports...\n";
for ($i = 0; $i < 20; $i++) {
    $type = randomElement($report_types);
    $title = $type . ' Report - ' . date('F Y', strtotime('-' . rand(0, 12) . ' months'));
    $content = 'This report contains statistical data for ' . strtolower($type) . ' in the health facility. Total records: ' . rand(100, 1000) . ', Active cases: ' . rand(50, 500) . ', Resolved cases: ' . rand(50, 500);
    $generated_by = randomElement($user_ids);
    $zone = randomElement(array_column($zones, 0));
    $woreda = $zone . ' Woreda 1';
    $kebele = randomElement($kebeles);
    $start_date = date('Y-m-d', strtotime('-' . rand(30, 365) . ' days'));
    $end_date = date('Y-m-d', strtotime($start_date . ' +' . rand(7, 30) . ' days'));

    $stmt = $conn->prepare("INSERT INTO reports (type, title, content, generated_by, zone, woreda, kebele, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisssss", $type, $title, $content, $generated_by, $zone, $woreda, $kebele, $start_date, $end_date);
    $stmt->execute();
    $stmt->close();
}

echo "Inserted 20 reports.\n";

// Insert real emergency responses
echo "Inserting real emergency responses...\n";
for ($i = 0; $i < 15; $i++) {
    $incident_type = randomElement($incident_types);
    $description = 'Emergency incident: ' . $incident_type . ' reported at ' . randomElement($facilities) . '. Immediate response required.';
    $location = randomElement($facilities) . ', ' . randomElement($regions);
    $severity = randomElement(['low', 'medium', 'high', 'critical']);
    $status = randomElement(['reported', 'responding', 'resolved']);
    $reported_by = randomElement($user_ids);
    $assigned_to = randomElement($user_ids);
    $zone = randomElement(array_column($zones, 0));
    $woreda = $zone . ' Woreda 1';
    $kebele = randomElement($kebeles);

    $stmt = $conn->prepare("INSERT INTO emergency_responses (incident_type, description, location, severity, status, reported_by, assigned_to, zone, woreda, kebele) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiisss", $incident_type, $description, $location, $severity, $status, $reported_by, $assigned_to, $zone, $woreda, $kebele);
    $stmt->execute();
    $stmt->close();
}

echo "Inserted 15 emergency responses.\n";

// Insert real quality assurance
echo "Inserting real quality assurance...\n";
for ($i = 0; $i < 10; $i++) {
    $facility_name = randomElement($facilities);
    $assessment_type = randomElement($assessment_types);
    $score = rand(70, 100) + rand(0, 99) / 100; // Random decimal between 70-100
    $total_score = 100.00;
    $findings = 'Assessment completed with overall score of ' . number_format($score, 2) . '/100. ';
    if ($score >= 90) {
        $findings .= 'Excellent performance in all areas.';
        $recommendations = 'Continue current practices and maintain high standards.';
    } elseif ($score >= 80) {
        $findings .= 'Good performance with minor areas for improvement.';
        $recommendations = 'Address identified gaps and implement recommended improvements.';
    } else {
        $findings .= 'Satisfactory performance requiring attention to several areas.';
        $recommendations = 'Develop action plan to address deficiencies and schedule follow-up assessment.';
    }
    $assessed_by = randomElement($user_ids);
    $zone = randomElement(array_column($zones, 0));
    $woreda = $zone . ' Woreda 1';
    $kebele = randomElement($kebeles);
    $assessment_date = date('Y-m-d', strtotime('-' . rand(0, 365) . ' days'));
    $next_assessment_date = date('Y-m-d', strtotime($assessment_date . ' +6 months'));

    $stmt = $conn->prepare("INSERT INTO quality_assurance (facility_name, assessment_type, score, total_score, findings, recommendations, assessed_by, zone, woreda, kebele, assessment_date, next_assessment_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sddsssisssss", $facility_name, $assessment_type, $score, $total_score, $findings, $recommendations, $assessed_by, $zone, $woreda, $kebele, $assessment_date, $next_assessment_date);
    $stmt->execute();
    $stmt->close();
}

echo "Inserted 10 quality assurance records.\n";

// Insert real payroll data
echo "Inserting real payroll data...\n";
foreach ($employees_data as $employee) {
    $period_start = date('Y-m-01', strtotime('-' . rand(0, 6) . ' months'));
    $period_end = date('Y-m-t', strtotime($period_start));
    $basic_salary = $employee['salary'];
    $allowances = $basic_salary * 0.1; // 10% allowances
    $deductions = $basic_salary * 0.05; // 5% deductions
    $net_salary = $basic_salary + $allowances - $deductions;
    $processed_by = randomElement($user_ids);

    $stmt = $conn->prepare("INSERT INTO payroll (employee_id, period_start, period_end, basic_salary, allowances, deductions, net_salary, processed_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'processed')");
    $stmt->bind_param("sssdddds", $employee['employee_id'], $period_start, $period_end, $basic_salary, $allowances, $deductions, $net_salary, $processed_by);
    $stmt->execute();
    $stmt->close();
}

echo "Inserted payroll data for employees.\n";

// Insert real leave requests
echo "Inserting real leave requests...\n";
foreach (array_slice($employees_data, 0, 30) as $employee) { // For 30 employees
    $leave_type = randomElement($leave_types);
    $start_date = date('Y-m-d', strtotime('+' . rand(1, 60) . ' days'));
    $days_requested = rand(1, 30);
    $end_date = date('Y-m-d', strtotime($start_date . ' +' . ($days_requested - 1) . ' days'));
    $reason = 'Personal ' . $leave_type . ' leave request';
    $status = randomElement(['pending', 'approved', 'rejected']);

    $stmt = $conn->prepare("INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, days_requested, reason, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiss", $employee['employee_id'], $leave_type, $start_date, $end_date, $days_requested, $reason, $status);
    $stmt->execute();
    $stmt->close();
}

echo "Inserted 30 leave requests.\n";

// Insert real job postings
echo "Inserting real job postings...\n";
for ($i = 0; $i < 15; $i++) {
    $title = randomElement($job_titles) . ' Position';
    $department = randomElement($departments);
    $description = 'We are looking for a qualified ' . strtolower($title) . ' to join our healthcare team. The successful candidate will be responsible for providing high-quality healthcare services.';
    $requirements = 'Bachelor degree in relevant field, 2+ years experience, valid license, good communication skills.';
    $salary_range = 'ETB ' . number_format(rand(8000, 30000), 0) . ' - ' . number_format(rand(30000, 60000), 0);
    $location = randomElement($regions) . ', Ethiopia';
    $employment_type = randomElement(['full-time', 'part-time', 'contract']);
    $application_deadline = date('Y-m-d', strtotime('+' . rand(7, 60) . ' days'));
    $posted_by = randomElement($user_ids);

    $stmt = $conn->prepare("INSERT INTO job_postings (title, department, description, requirements, salary_range, location, employment_type, application_deadline, posted_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssi", $title, $department, $description, $requirements, $salary_range, $location, $employment_type, $application_deadline, $posted_by);
    $stmt->execute();
    $stmt->close();
}

echo "Inserted 15 job postings.\n";

// Insert real training sessions
echo "Inserting real training sessions...\n";
for ($i = 0; $i < 12; $i++) {
    $title = randomElement($training_topics) . ' Training';
    $description = 'Comprehensive training program on ' . strtolower($title) . '. This session will cover theoretical knowledge and practical applications.';
    $trainer = 'Dr. ' . randomElement($male_names) . ' ' . randomElement($surnames);
    $session_date = date('Y-m-d', strtotime('+' . rand(1, 90) . ' days'));
    $start_time = date('H:i:s', strtotime('09:00:00'));
    $end_time = date('H:i:s', strtotime('17:00:00'));
    $venue = randomElement($facilities) . ' Training Room';
    $max_participants = rand(20, 50);
    $created_by = randomElement($user_ids);

    $stmt = $conn->prepare("INSERT INTO training_sessions (title, description, trainer, session_date, start_time, end_time, venue, max_participants, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssdi", $title, $description, $trainer, $session_date, $start_time, $end_time, $venue, $max_participants, $created_by);
    $stmt->execute();
    $stmt->close();
}

echo "Inserted 12 training sessions.\n";

echo "Real data insertion completed successfully!\n";
$conn->close();
?>