<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['import_file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// Validate file type
if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Please upload CSV or Excel file.']);
    exit;
}

$conn = getDBConnection();
$session_user = $_SESSION['user_name'] ?? 'System';
$session_kebele = $_SESSION['kebele'] ?? 'Kebele 01';

$imported = 0;
$errors = [];
$rows = [];

try {
    // Process CSV file
    if ($ext === 'csv') {
        if (($handle = fopen($file['tmp_name'], 'r')) !== FALSE) {
            $header = fgetcsv($handle); // Skip header row

            while (($data = fgetcsv($handle)) !== FALSE) {
                if (count($data) < 5)
                    continue; // Skip invalid rows

                $rows[] = [
                    'first_name' => trim($data[0] ?? ''),
                    'middle_name' => trim($data[1] ?? ''),
                    'last_name' => trim($data[2] ?? ''),
                    'gender' => strtolower(trim($data[3] ?? 'male')),
                    'email' => trim($data[4] ?? ''),
                    'phone_number' => trim($data[5] ?? ''),
                    'position' => trim($data[6] ?? 'Employee'),
                    'department_assigned' => trim($data[7] ?? ''),
                    'employment_type' => trim($data[8] ?? 'full-time'),
                    'salary' => floatval($data[9] ?? 0),
                    'join_date' => trim($data[10] ?? date('Y-m-d')),
                    'status' => trim($data[11] ?? 'active'),
                    'working_woreda' => trim($data[12] ?? ''),
                    'date_of_birth' => trim($data[13] ?? ''),
                    'address' => trim($data[14] ?? '')
                ];
            }
            fclose($handle);
        }
    }
    // Process Excel file (basic support without PhpSpreadsheet)
    else {
        echo json_encode(['success' => false, 'message' => 'Excel import requires CSV format. Please save your Excel file as CSV and try again.']);
        exit;
    }

    // Insert each employee
    foreach ($rows as $index => $emp) {
        // Validate required fields
        if (empty($emp['first_name']) || empty($emp['last_name']) || empty($emp['email'])) {
            $errors[] = "Row " . ($index + 2) . ": Missing required fields (First Name, Last Name, or Email)";
            continue;
        }

        // Generate unique employee ID
        $year = date('Y');
        $random = rand(1000, 9999);
        $employee_id = "KBL-{$year}-{$random}";

        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT employee_id FROM employees WHERE email = ?");
        $checkStmt->bind_param('s', $emp['email']);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "Row " . ($index + 2) . ": Email '{$emp['email']}' already exists";
            $checkStmt->close();
            continue;
        }
        $checkStmt->close();

        // Insert employee
        $sql = "INSERT INTO employees (
            employee_id, first_name, middle_name, last_name, gender, email, phone_number,
            position, department_assigned, employment_type, salary, join_date, status,
            working_kebele, working_woreda, date_of_birth, address, created_by, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssssdsssssss",
            $employee_id,
            $emp['first_name'],
            $emp['middle_name'],
            $emp['last_name'],
            $emp['gender'],
            $emp['email'],
            $emp['phone_number'],
            $emp['position'],
            $emp['department_assigned'],
            $emp['employment_type'],
            $emp['salary'],
            $emp['join_date'],
            $emp['status'],
            $session_kebele,
            $emp['working_woreda'],
            $emp['date_of_birth'],
            $emp['address'],
            $session_user
        );

        if ($stmt->execute()) {
            $imported++;
        } else {
            $errors[] = "Row " . ($index + 2) . ": Database error - " . $stmt->error;
        }
        $stmt->close();
    }

    $message = "Successfully imported {$imported} employee(s)";
    if (count($errors) > 0) {
        $message .= ". " . count($errors) . " error(s) occurred.";
    }

    echo json_encode([
        'success' => true,
        'message' => $message,
        'imported' => $imported,
        'errors' => $errors,
        'total_rows' => count($rows)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Import failed: ' . $e->getMessage()
    ]);
}

$conn->close();
