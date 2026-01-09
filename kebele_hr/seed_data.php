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

// Sample data arrays
$first_names = ['Abebe', 'Kebede', 'Mulugeta', 'Aster', 'Tigist', 'Yonas', 'Hanna', 'Biniyam', 'Selam', 'Dawit'];
$last_names = ['Tesfaye', 'Bekele', 'Girma', 'Tadesse', 'Haile', 'Assefa', 'Mekonnen', 'Zewde', 'Desta', 'Alemu'];
$genders = ['male', 'female'];
$depts = ['Medical', 'Administration', 'Technical', 'Support'];
$positions = ['Senior Officer', 'Junior Staff', 'Department Head', 'Specialist', 'Technician'];
$edu_levels = ['diploma', 'bachelor', 'master', 'phd'];
$statuses = ['active', 'active', 'active', 'on-leave', 'inactive'];

// Check if we already have employees for this kebele
$check = $conn->prepare("SELECT COUNT(*) as count FROM employees WHERE working_woreda = ? AND working_kebele = ?");
$check->bind_param("ss", $user_woreda, $user_kebele);
$check->execute();
$count = $check->get_result()->fetch_assoc()['count'];

if ($count >= 10) {
    echo json_encode(['success' => true, 'message' => 'Data already exists']);
    exit;
}

// Insert 15-20 random employees
for ($i = 0; $i < 20; $i++) {
    $fn = $first_names[array_rand($first_names)];
    $ln = $last_names[array_rand($last_names)];
    $gender = $genders[array_rand($genders)];
    $dept = $depts[array_rand($depts)];
    $pos = $positions[array_rand($positions)];
    $edu = $edu_levels[array_rand($edu_levels)];
    $status = $statuses[array_rand($statuses)];
    $salary = rand(4500, 45000);
    $email = strtolower($fn . "." . $ln . rand(10, 99) . "@ehealth.com");
    $emp_id = "EMP-" . strtoupper(substr($fn, 0, 1) . substr($ln, 0, 1)) . "-" . rand(1000, 9999);

    // Dates
    $age_years = rand(22, 60);
    $dob = date('Y-m-d', strtotime("-$age_years years -" . rand(0, 365) . " days"));
    $join_years = rand(0, 15);
    $join_date = date('Y-m-d', strtotime("-$join_years years -" . rand(0, 365) . " days"));

    $stmt = $conn->prepare("INSERT INTO employees (
        employee_id, first_name, last_name, email, gender, date_of_birth, 
        department_assigned, position, join_date, education_level, 
        status, salary, working_woreda, working_kebele
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "sssssssssssdss",
        $emp_id,
        $fn,
        $ln,
        $email,
        $gender,
        $dob,
        $dept,
        $pos,
        $join_date,
        $edu,
        $status,
        $salary,
        $user_woreda,
        $user_kebele
    );
    $stmt->execute();
}

// Seed some attendance data for the last 15 days
$emp_ids = [];
$res = $conn->prepare("SELECT employee_id FROM employees WHERE working_kebele = ?");
$res->bind_param("s", $user_kebele);
$res->execute();
$result = $res->get_result();
while ($row = $result->fetch_assoc())
    $emp_ids[] = $row['employee_id'];

for ($day = 0; $day < 15; $day++) {
    $date = date('Y-m-d', strtotime("-$day days"));
    foreach ($emp_ids as $eid) {
        $status = (rand(0, 10) > 1) ? 'present' : 'absent';
        if ($status == 'absent' && rand(0, 5) == 0)
            $status = 'on-leave';

        $conn->query("INSERT IGNORE INTO attendance (employee_id, attendance_date, status, kebele) VALUES ('$eid', '$date', '$status', '$user_kebele')");
    }
}

$conn->close();
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Seeded 20 employees and 15 days of attendance']);
?>