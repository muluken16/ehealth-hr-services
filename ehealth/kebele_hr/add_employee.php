<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug output
echo "<!-- Debug: PHP started -->\n";

session_start();

echo "<!-- Debug: Session started -->\n";

// if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kebele_hr') {
//     header('Location: index.html');
//     exit();
// }

// Default user for demo
$_SESSION['user_name'] = 'Kebele HR Officer';
$_SESSION['role'] = 'kebele_hr';

echo "<!-- Debug: Session variables set -->\n";

// Create fresh database connection (db.php closes connection at end)
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<!-- Debug: Database connected -->\n";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    
    // Validate required fields (only fields that exist in database)
    $required_fields = ['first_name', 'last_name', 'gender', 'date_of_birth', 'email', 'position'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
        }
    }
    
    // Validate email format
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Validate date of birth (must be at least 18 years old)
    if (!empty($_POST['date_of_birth'])) {
        $dob = new DateTime($_POST['date_of_birth']);
        $today = new DateTime();
        $age = $today->diff($dob)->y;
        if ($age < 18) {
            $errors[] = "Employee must be at least 18 years old";
        }
    }
    
    // Validate salary (must be positive)
    if (!empty($_POST['salary']) && $_POST['salary'] <= 0) {
        $errors[] = "Salary must be a positive number";
    }
    
    if (!empty($errors)) {
        $error_message = implode('<br>', $errors);
        echo "<script>alert('Please fix the following errors:\\n" . addslashes($error_message) . "'); window.history.back();</script>";
        exit();
    }
    
    // Generate unique employee ID
    do {
        $year = date('Y');
        $random = rand(1000, 9999);
        $employee_id = "HF-{$year}-{$random}";
        
        // Check if employee ID already exists
        $check_stmt = $conn->prepare("SELECT employee_id FROM employees WHERE employee_id = ?");
        $check_stmt->bind_param("s", $employee_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $check_stmt->close();
    } while ($result->num_rows > 0);

    // Basic Information
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $religion = $_POST['religion'] ?? '';
    $citizenship = $_POST['citizenship'] ?? '';
    $other_citizenship = trim($_POST['other_citizenship'] ?? '');

    // Location
    $region = $_POST['region'] ?? '';
    $zone = $_POST['zone'] ?? '';
    $woreda = $_POST['woreda'] ?? '';
    $kebele = $_POST['kebele'] ?? '';

    // Education
    $education_level = $_POST['education_level'] ?? '';
    $primary_school = $_POST['primary_school'] ?? '';
    $secondary_school = $_POST['secondary_school'] ?? '';
    $college = $_POST['college'] ?? '';
    $university = $_POST['university'] ?? '';

    // Department
    $department_assigned = $_POST['department_assigned'] ?? '';
    $other_department = $_POST['other_department'] ?? '';

    // Banking
    $bank_name = $_POST['bank_name'] ?? '';
    $other_bank_name = $_POST['other_bank_name'] ?? '';
    $bank_account = $_POST['bank_account'] ?? '';

    // Job Level
    $job_level = $_POST['job_level'] ?? '';
    $other_job_level = $_POST['other_job_level'] ?? '';

    // Marital Status
    $marital_status = $_POST['marital_status'] ?? '';
    $other_marital_status = $_POST['other_marital_status'] ?? '';

    // Warranty
    $warranty_status = $_POST['warranty_status'] ?? '';
    $person_name = $_POST['person_name'] ?? '';
    $warranty_woreda = $_POST['warranty_woreda'] ?? '';
    $warranty_kebele = $_POST['warranty_kebele'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $warranty_type = $_POST['warranty_type'] ?? '';

    // Criminal
    $criminal_status = $_POST['criminal_status'] ?? '';

    // FIN
    $fin_id = $_POST['fin_id'] ?? '';

    // Loan
    $loan_status = $_POST['loan_status'] ?? '';

    // Leave
    $leave_request = $_POST['leave_request'] ?? '';

    // Contact
    $email = $_POST['email'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';

    // Employment
    $position = $_POST['position'] ?? '';
    $join_date = $_POST['join_date'] ?? '';
    $salary = $_POST['salary'] ?? '';
    $employment_type = $_POST['employment_type'] ?? '';
    $status = $_POST['status'] ?? 'active';
    $address = $_POST['address'] ?? '';
    $emergency_contact = $_POST['emergency_contact'] ?? '';

    // Language
    $language = $_POST['language'] ?? '';
    $other_language = $_POST['other_language'] ?? '';

    // Additional fields with defaults
    $department = $_POST['department'] ?? '';
    $person_relationship = $_POST['person_relationship'] ?? '';
    $warranty_region = $_POST['warranty_region'] ?? '';
    $warranty_zone = $_POST['warranty_zone'] ?? '';
    $warranty_email = $_POST['warranty_email'] ?? '';
    $warranty_amount = $_POST['warranty_amount'] ?? '';
    $warranty_address = $_POST['warranty_address'] ?? '';
    $warranty_start_date = $_POST['warranty_start_date'] ?? NULL;
    $warranty_end_date = $_POST['warranty_end_date'] ?? NULL;
    $warranty_notes = $_POST['warranty_notes'] ?? '';
    $criminal_type = $_POST['criminal_type'] ?? '';
    $criminal_date = $_POST['criminal_date'] ?? NULL;
    $criminal_location = $_POST['criminal_location'] ?? '';
    $criminal_court = $_POST['criminal_court'] ?? '';
    $criminal_description = $_POST['criminal_description'] ?? '';
    $criminal_sentence = $_POST['criminal_sentence'] ?? '';
    $criminal_status_current = $_POST['criminal_status_current'] ?? '';
    $criminal_additional_docs = '';
    $criminal_notes = $_POST['criminal_notes'] ?? '';
    $loan_type = $_POST['loan_type'] ?? '';
    $loan_amount = $_POST['loan_amount'] ?? '';
    $loan_lender = $_POST['loan_lender'] ?? '';
    $loan_account = $_POST['loan_account'] ?? '';
    $loan_start_date = $_POST['loan_start_date'] ?? NULL;
    $loan_end_date = $_POST['loan_end_date'] ?? NULL;
    $monthly_payment = $_POST['monthly_payment'] ?? '';
    $remaining_balance = $_POST['remaining_balance'] ?? '';
    $loan_status_current = $_POST['loan_status_current'] ?? '';
    $loan_collateral = $_POST['loan_collateral'] ?? '';
    $loan_purpose = $_POST['loan_purpose'] ?? '';
    $loan_payment_proof = '';
    $loan_notes = $_POST['loan_notes'] ?? '';
    $leave_type = $_POST['leave_type'] ?? '';
    $leave_duration = $_POST['leave_duration'] ?? '';
    $leave_start_date = $_POST['leave_start_date'] ?? NULL;
    $leave_end_date = $_POST['leave_end_date'] ?? NULL;
    $leave_reason = $_POST['leave_reason'] ?? '';
    $leave_contact = $_POST['leave_contact'] ?? '';
    $leave_supervisor = $_POST['leave_supervisor'] ?? '';
    $leave_address = $_POST['leave_address'] ?? '';
    $leave_medical_cert = '';
    $leave_supporting_docs = '';
    $leave_notes = $_POST['leave_notes'] ?? '';

    // Language
    $language = $_POST['language'] ?? '';
    $other_language = $_POST['other_language'] ?? '';

    // Handle file uploads with better error handling
    $upload_dir = '../uploads/employees/';
    
    // Create upload directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            throw new Exception("Failed to create upload directory: $upload_dir");
        }
    }
    
    // Check if directory is writable
    if (!is_writable($upload_dir)) {
        throw new Exception("Upload directory is not writable: $upload_dir");
    }

    $documents = [];
    $scan_file = '';
    $criminal_file = '';
    $fin_scan = '';
    $loan_file = '';
    $leave_document = '';

    echo "<!-- Debug: Upload directory: $upload_dir -->\n";
    echo "<!-- Debug: Directory exists: " . (is_dir($upload_dir) ? 'Yes' : 'No') . " -->\n";
    echo "<!-- Debug: Directory writable: " . (is_writable($upload_dir) ? 'Yes' : 'No') . " -->\n";

    // Documents (multiple files)
    if (isset($_FILES['documents']) && is_array($_FILES['documents']['name'])) {
        $files = $_FILES['documents'];
        echo "<!-- Debug: Processing " . count($files['name']) . " document files -->\n";
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] == 0 && !empty($files['name'][$i])) {
                $original_name = $files['name'][$i];
                $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                
                // Validate file type
                $allowed_types = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
                if (!in_array($file_extension, $allowed_types)) {
                    echo "<!-- Debug: Skipping file $original_name - invalid type -->\n";
                    continue;
                }
                
                $file_name = $employee_id . '_doc_' . $i . '_' . time() . '.' . $file_extension;
                $target_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($files['tmp_name'][$i], $target_path)) {
                    $documents[] = $file_name;
                    echo "<!-- Debug: Successfully uploaded: $file_name -->\n";
                } else {
                    echo "<!-- Debug: Failed to upload: $original_name -->\n";
                }
            } else {
                echo "<!-- Debug: File $i has error: " . $files['error'][$i] . " -->\n";
            }
        }
    } else {
        echo "<!-- Debug: No documents array found in FILES -->\n";
    }

    // Scan file for warranty
    if (isset($_FILES['scan_file']) && $_FILES['scan_file']['error'] == 0) {
        $original_name = $_FILES['scan_file']['name'];
        $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $file_name = $employee_id . '_scan_' . time() . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['scan_file']['tmp_name'], $target_path)) {
            $scan_file = $file_name;
            echo "<!-- Debug: Scan file uploaded: $file_name -->\n";
        } else {
            echo "<!-- Debug: Failed to upload scan file -->\n";
        }
    }

    // Criminal file
    if (isset($_FILES['criminal_file']) && $_FILES['criminal_file']['error'] == 0) {
        $original_name = $_FILES['criminal_file']['name'];
        $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $file_name = $employee_id . '_criminal_' . time() . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['criminal_file']['tmp_name'], $target_path)) {
            $criminal_file = $file_name;
            echo "<!-- Debug: Criminal file uploaded: $file_name -->\n";
        } else {
            echo "<!-- Debug: Failed to upload criminal file -->\n";
        }
    }

    // FIN scan
    if (isset($_FILES['fin_scan']) && $_FILES['fin_scan']['error'] == 0) {
        $original_name = $_FILES['fin_scan']['name'];
        $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $file_name = $employee_id . '_fin_scan_' . time() . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['fin_scan']['tmp_name'], $target_path)) {
            $fin_scan = $file_name;
            echo "<!-- Debug: FIN scan uploaded: $file_name -->\n";
        } else {
            echo "<!-- Debug: Failed to upload FIN scan -->\n";
        }
    }

    // Loan file
    if (isset($_FILES['loan_file']) && $_FILES['loan_file']['error'] == 0) {
        $original_name = $_FILES['loan_file']['name'];
        $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $file_name = $employee_id . '_loan_' . time() . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['loan_file']['tmp_name'], $target_path)) {
            $loan_file = $file_name;
            echo "<!-- Debug: Loan file uploaded: $file_name -->\n";
        } else {
            echo "<!-- Debug: Failed to upload loan file -->\n";
        }
    }

    // Leave document
    if (isset($_FILES['leave_document']) && $_FILES['leave_document']['error'] == 0) {
        $original_name = $_FILES['leave_document']['name'];
        $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $file_name = $employee_id . '_leave_' . time() . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['leave_document']['tmp_name'], $target_path)) {
            $leave_document = $file_name;
            echo "<!-- Debug: Leave document uploaded: $file_name -->\n";
        } else {
            echo "<!-- Debug: Failed to upload leave document -->\n";
        }
    }

    echo "<!-- Debug: Total documents uploaded: " . count($documents) . " -->\n";

    // Insert into database
    // Insert into database with transaction
    $conn->begin_transaction();
    
    try {
        // Use exact fields that exist in the database (53 fields)
        $sql = "INSERT INTO employees (
            employee_id, first_name, middle_name, last_name, gender, date_of_birth, religion, citizenship, other_citizenship, region, zone, woreda, kebele, education_level, primary_school, secondary_school, college, university, department, other_department, bank_name, bank_account, job_level, other_job_level, marital_status, other_marital_status, warranty_status, person_name, warranty_woreda, warranty_kebele, phone, warranty_type, scan_file, criminal_status, criminal_file, fin_id, fin_scan, loan_status, loan_file, leave_request, leave_document, email, phone_number, department_assigned, position, join_date, salary, employment_type, status, address, emergency_contact, documents, created_by
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $documents_json = json_encode($documents);
        $stmt->bind_param(
            "sssssssssssssssssssssssssssssssssssssssssssssssssssss",
            $employee_id, $first_name, $middle_name, $last_name, $gender, $date_of_birth, $religion, $citizenship, $other_citizenship, $region, $zone, $woreda, $kebele, $education_level, $primary_school, $secondary_school, $college, $university, $department, $other_department, $bank_name, $bank_account, $job_level, $other_job_level, $marital_status, $other_marital_status, $warranty_status, $person_name, $warranty_woreda, $warranty_kebele, $phone, $warranty_type, $scan_file, $criminal_status, $criminal_file, $fin_id, $fin_scan, $loan_status, $loan_file, $leave_request, $leave_document, $email, $phone_number, $department_assigned, $position, $join_date, $salary, $employment_type, $status, $address, $emergency_contact, $documents_json, $_SESSION['user_name']
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $conn->commit();
        $stmt->close();
        
        echo "<script>
            alert('✅ Employee added successfully!\\nEmployee ID: {$employee_id}\\nName: {$first_name} {$last_name}');
            window.location.href='hr-employees.html';
        </script>";
        
    } catch (Exception $e) {
        $conn->rollback();
        $detailed_error = "Employee insertion error: " . $e->getMessage();
        error_log($detailed_error);
        
        // Show detailed error in development
        echo "<script>
            console.log('Database Error Details: " . addslashes($e->getMessage()) . "');
            alert('❌ Error adding employee: " . addslashes($e->getMessage()) . "\\n\\nPlease check the console for more details.');
            window.history.back();
        </script>";
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Add Employee</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        /* Enhanced Form Styling */
        .content {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Form Container */
        #employeeForm {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #fafbfc;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            background: white;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        /* Form Rows */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        /* Language Selector */
        .language-selector {
            margin-bottom: 25px;
        }

        .language-selector label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: block;
        }

        .language-selector select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 14px;
            background: #fafbfc;
            transition: all 0.3s ease;
        }

        .language-selector select:focus {
            outline: none;
            border-color: #3498db;
            background: white;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        #otherLanguageDiv {
            margin-top: 15px;
        }

        /* Scan Button */
        .scan-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .scan-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .scan-btn:active {
            transform: translateY(0);
        }

        /* Preview Container */
        .preview-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .preview-box {
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            background: #fafbfc;
            transition: all 0.3s ease;
        }

        .preview-box:hover {
            border-color: #3498db;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.15);
        }

        .preview-box img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .preview-box p {
            font-size: 12px;
            color: #666;
            margin: 0;
            word-break: break-word;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e1e8ed;
        }

        .submit-btn {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 180px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
        }

        .cancel-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 180px;
        }

        .cancel-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }

            #employeeForm {
                padding: 25px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .form-actions {
                flex-direction: column;
                align-items: center;
            }

            .submit-btn,
            .cancel-btn {
                width: 100%;
                max-width: 300px;
            }

            .preview-container {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }

            .preview-box img {
                height: 100px;
            }
        }

        @media (max-width: 480px) {
            .content {
                padding: 15px;
            }

            #employeeForm {
                padding: 20px;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 10px 12px;
                font-size: 16px; /* Prevents zoom on iOS */
            }

            .scan-btn {
                padding: 10px 20px;
                font-size: 13px;
            }

            .preview-container {
                grid-template-columns: 1fr;
            }

            .preview-box {
                padding: 12px;
            }

            .preview-box img {
                height: 80px;
            }
        }

        /* Section Spacing */
        .form-group + .form-group {
            margin-top: 25px;
        }

        /* Conditional Fields Animation - FIXED */
        .conditional-field {
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
            margin-top: 0;
            padding: 0;
            border: 1px solid transparent;
            border-radius: 8px;
            background: rgba(52, 152, 219, 0.02);
        }

        .conditional-field.show {
            opacity: 1;
            max-height: 1200px;
            margin-top: 20px;
            padding: 20px;
            border: 1px solid #e1e8ed;
            background: #fafbfc;
        }

        /* Enhanced Input States */
        .form-group input:invalid,
        .form-group select:invalid {
            border-color: #e74c3c;
        }

        .form-group input:valid,
        .form-group select:valid {
            border-color: #27ae60;
        }

        /* Loading States */
        .submit-btn.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .submit-btn.loading::after {
            content: '';
            width: 16px;
            height: 16px;
            margin-left: 10px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes slideDown {
            0% {
                opacity: 0;
                transform: translateY(-10px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Focus States for Better Accessibility */
        .form-group input:focus + label,
        .form-group select:focus + label,
        .form-group textarea:focus + label {
            color: #3498db;
        }

        /* Better Button Hover States */
        .submit-btn:active,
        .cancel-btn:active,
        .scan-btn:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="hr-container">
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="#" class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <span class="logo-text">HealthFirst HR</span>
                </a>
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>

            <nav class="sidebar-menu">
                <ul>
                    <li class="menu-item">
                        <a href="kebele_hr_dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="menu-text">HR Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item active">
                        <a href="hr-employees.html">
                            <i class="fas fa-users"></i>
                            <span class="menu-text">Employees</span>
                            <span class="menu-badge">142</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="hr-attendance.html">
                            <i class="fas fa-calendar-check"></i>
                            <span class="menu-text">Attendance</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="hr-leave.html">
                            <i class="fas fa-umbrella-beach"></i>
                            <span class="menu-text">Leave Management</span>
                            <span class="menu-badge">8</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="hr-recruitment.html">
                            <i class="fas fa-user-plus"></i>
                            <span class="menu-text">Recruitment</span>
                            <span class="menu-badge">5</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="hr-training.html">
                            <i class="fas fa-graduation-cap"></i>
                            <span class="menu-text">Training</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="hr-payroll.html">
                            <i class="fas fa-money-check-alt"></i>
                            <span class="menu-text">Payroll</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="hr-reports.html">
                            <i class="fas fa-chart-bar"></i>
                            <span class="menu-text">HR Reports</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="hr-settings.html">
                            <i class="fas fa-cog"></i>
                            <span class="menu-text">HR Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <button class="mobile-menu-btn" id="mobileMenuBtn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">Add New Employee</h1>
                </div>

                <div class="header-actions">
                    <button class="btn-secondary" onclick="window.history.back()">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <form id="employeeForm" enctype="multipart/form-data" method="POST">
                    <!-- Basic Information -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">First Name *</label>
                            <input type="text" id="firstName" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="middleName">Middle Name</label>
                            <input type="text" id="middleName" name="middle_name">
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name *</label>
                            <input type="text" id="lastName" name="last_name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="newPatientGender">Gender *</label>
                        <select id="newPatientGender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="dateOfBirth">Date of Birth *</label>
                        <input type="date" id="dateOfBirth" name="date_of_birth" required>
                    </div>

                    <div class="form-group">
                        <label for="religion">Religion *</label>
                        <select id="religion" name="religion" required>
                            <option value="">Select Religion</option>
                            <option value="christianity">Orthodox</option>
                            <option value="islam">Islam</option>
                            <option value="protestant">Protestant</option>
                            <option value="judaism">Judaism</option>
                            <option value="hinduism">Hinduism</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="citizenship">Citizenship *</label>
                        <select id="citizenship" name="citizenship" required onchange="checkOther(this)">
                            <option value="">Select your country</option>
                            <option value="Ethiopia">Ethiopia</option>
                            <option value="United States">United States</option>
                            <option value="United Kingdom">United Kingdom</option>
                            <option value="Canada">Canada</option>
                            <option value="Germany">Germany</option>
                            <option value="France">France</option>
                            <option value="India">India</option>
                            <option value="China">China</option>
                            <option value="Japan">Japan</option>
                            <option value="Other">Other</option>
                        </select>

                        <!-- Input for "Other" country -->
                        <input type="text" id="otherCitizenship" name="other_citizenship" placeholder="Enter your country" style="display:none; margin-top:5px;">
                    </div>

                    <script>
                    function checkOther(select) {
                        var otherInput = document.getElementById('otherCitizenship');
                        if(select.value === "Other") {
                            otherInput.style.display = "block";
                            otherInput.required = true;
                            otherInput.style.animation = "slideDown 0.3s ease";
                        } else {
                            otherInput.style.display = "none";
                            otherInput.required = false;
                        }
                    }
                    </script>

                    <!-- Region Zone Woreda Selection -->
                    <div class="form-group">
                        <label for="region">Region *</label>
                        <select id="region" name="region" required onchange="loadZones()">
                            <option value="">Select Region</option>
                            <option value="amhara">Amhara</option>
                            <option value="oromia">Oromia</option>
                            <option value="tigray">Tigray</option>
                            <option value="snnpr">SNNP</option>
                            <option value="afar">Afar</option>
                            <option value="somali">Somali</option>
                            <option value="gambela">Gambela</option>
                            <option value="benishangul">Benishangul Gumuz</option>
                            <option value="harari">Harari</option>
                            <option value="addis_ababa">Addis Ababa</option>
                            <option value="dire_dawa">Dire Dawa</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="zone">Zone *</label>
                        <select id="zone" name="zone" required onchange="loadWoredas()">
                            <option value="">Select Zone</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="woreda">Woreda *</label>
                        <select id="woreda" name="woreda" required onchange="loadKebeles()">
                            <option value="">Select Woreda</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="kebele">Kebele *</label>
                        <select id="kebele" name="kebele" required>
                            <option value="">Select Kebele</option>
                        </select>
                    </div>

                    <script>
                    const amharaData = {
                        "North Wollo": {
                           "Woldiya": [],
                           "Lalibela": [],
                           "Kobo": [],
                           "Guba Lafto": [],
                           "Meket": [],
                           "Habru": [],
                           "Wadla": [],
                           "Delanta": [],
                           "Tehuledere": [],
                           "Raya Kobo": [],
                           "Haik": [],
                           "Kalu": []
                        },
                        "South Wollo": {
                            "Dessie": [],
                            "Kombolcha": [],
                            "Mekdela": [],
                            "Werebabo": [],
                            "Kutaber": [],
                            "Tenta": [],
                            "Legambo": [],
                            "Ambassel": [],
                            "Kelala": [],
                            "Wegde": [],
                            "Sayint": [],
                            "Tehuledere": []
                        },
                        "North Gondar": {
                            "Gondar": [],
                            "Debark": [],
                            "Dabat": [],
                            "Jan Amora": [],
                            "Metemma": [],
                            "Quara": [],
                            "Alefa": [],
                            "Wegera": [],
                            "Lay Armachiho": [],
                            "Chilga": [],
                            "Tach Armachiho": [],
                            "Tegede": []
                        },
                        "South Gondar": {
                            "Debre Tabor": [],
                            "Fogera": [],
                            "Farta": [],
                            "Dera": [],
                            "Simada": [],
                            "Libo Kemkem": [],
                            "Lay Gayint": [],
                            "Tach Gayint": [],
                            "Ebinat": [],
                            "Mirab Belesa": [],
                            "Gondar Zuria": []
                        },
                        "West Gojjam": {
                            "Finote Selam": [],
                            "Bure": [],
                            "Dangila": [],
                            "Injibara": [],
                            "Jawi": [],
                            "Mecha": [],
                            "Yilmana Densa": [],
                            "Banja": [],
                            "Dega Damot": []
                        },
                        "East Gojjam": {
                            "Debre Markos": [],
                            "Dejen": [],
                            "Gozamen": [],
                            "Machakel": [],
                            "Aneded": [],
                            "Bibugn": [],
                            "Shebel Berenta": [],
                            "Enebsie Sar Midir": [],
                            "Debre Elias": [],
                            "Dega Damot": [],
                            "Awabel": [],
                            "Hulet Ej Enese": []
                        },
                        "Wag Hemra": {
                            "Sekota": [],
                            "Dehana": [],
                            "Zikuala": [],
                            "Abergele": [],
                            "Soqota": []
                        },
                        "Awi": {
                            "Injibara": [],
                            "Dangila": [],
                            "Banja": [],
                            "Ankesha": [],
                            "Guangua": [],
                            "Fagita Lekoma": [],
                            "Jawi": []
                        },
                        "Oromia Zone (Amhara)": {
                            "Artuma Fursi": [],
                            "Bati": [],
                            "Jile Timuga": [],
                            "Kemise": [],
                            "Dewe": []
                        },
                        "Bahir Dar (Special Zone)": {
                            "Bahir Dar City": [],
                            "Surrounding Rural Areas": []
                        }
                    };

                    // Load zones when region is selected
                    function loadZones() {
                        const region = document.getElementById("region").value;
                        const zoneSelect = document.getElementById("zone");
                        const woredaSelect = document.getElementById("woreda");
                        const kebeleSelect = document.getElementById("kebele");

                        zoneSelect.innerHTML = '<option value="">Select Zone</option>';
                        woredaSelect.innerHTML = '<option value="">Select Woreda</option>';
                        kebeleSelect.innerHTML = '<option value="">Select Kebele</option>';

                        if (region === "amhara") {
                            Object.keys(amharaData).forEach(zone => {
                                const option = document.createElement("option");
                                option.value = zone;
                                option.textContent = zone;
                                zoneSelect.appendChild(option);
                            });
                        }
                    }

                    // Load woredas when zone is selected
                    function loadWoredas() {
                        const zone = document.getElementById("zone").value;
                        const woredaSelect = document.getElementById("woreda");
                        const kebeleSelect = document.getElementById("kebele");

                        woredaSelect.innerHTML = '<option value="">Select Woreda</option>';
                        kebeleSelect.innerHTML = '<option value="">Select Kebele</option>';

                        if (amharaData[zone]) {
                            Object.keys(amharaData[zone]).forEach(woreda => {
                                const option = document.createElement("option");
                                option.value = woreda;
                                option.textContent = woreda;
                                woredaSelect.appendChild(option);
                            });
                        }
                    }

                    // Load kebeles when woreda is selected
                    function loadKebeles() {
                        const kebeleSelect = document.getElementById("kebele");
                        kebeleSelect.innerHTML = '<option value="">Select Kebele</option>';

                        for (let i = 1; i <= 50; i++) {
                            const number = i.toString().padStart(2, '0');
                            const option = document.createElement("option");
                            option.value = `Kebele ${number}`;
                            option.textContent = `Kebele ${number}`;
                            kebeleSelect.appendChild(option);
                        }
                    }
                    </script>

                    <!-- Academic Information -->
                    <div class="form-group">
                        <label for="educationLevel">Education Level *</label>
                        <select id="educationLevel" name="education_level" required>
                            <option value="">Select Education Level</option>
                            <option value="none">No Formal Education</option>
                            <option value="primary">Primary School</option>
                            <option value="secondary">Secondary School</option>
                            <option value="diploma">Diploma</option>
                            <option value="bachelor">Bachelor's Degree</option>
                            <option value="master">Master's Degree</option>
                            <option value="phd">PhD</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="primarySchool">Primary School *</label>
                        <input type="text" id="primarySchool" name="primary_school" placeholder="Enter details about Primary School" required>
                    </div>

                    <div class="form-group">
                        <label for="secondarySchool">Secondary School *</label>
                        <input type="text" id="secondarySchool" name="secondary_school" placeholder="Enter details about Secondary School" required>
                    </div>

                    <div class="form-group">
                        <label for="college">College *</label>
                        <input type="text" id="college" name="college" placeholder="Enter details about College" required>
                    </div>

                    <div class="form-group">
                        <label for="university">University *</label>
                        <input type="text" id="university" name="university" placeholder="Enter details about University" required>
                    </div>

                    <!-- Department Information -->
                    <div class="form-group">
                        <label for="allDepartments">Department *</label>
                        <select id="allDepartments" name="department_assigned" required onchange="checkOtherDepartment()">
                            <option value="">Select Department</option>
                            <option value="general_medicine">General Medicine</option>
                            <option value="pediatrics">Pediatrics</option>
                            <option value="obstetrics_gynecology">Obstetrics & Gynecology</option>
                            <option value="surgery">Surgery</option>
                            <option value="orthopedics">Orthopedics</option>
                            <option value="dermatology">Dermatology</option>
                            <option value="ophthalmology">Ophthalmology</option>
                            <option value="dentistry">Dentistry</option>
                            <option value="psychiatry">Psychiatry / Mental Health</option>
                            <option value="rehabilitation">Rehabilitation / Physiotherapy</option>
                            <option value="nutrition">Nutrition / Dietetics</option>
                            <option value="laboratory">Laboratory</option>
                            <option value="radiology">Radiology / Imaging</option>
                            <option value="pathology">Pathology</option>
                            <option value="emergency">Emergency / ER</option>
                            <option value="intensive_care">Intensive Care Unit (ICU)</option>
                            <option value="cardiology">Cardiology</option>
                            <option value="neurology">Neurology</option>
                            <option value="oncology">Oncology</option>
                            <option value="pharmacy">Pharmacy</option>
                            <option value="medical_records">Medical Records</option>
                            <option value="hospital_administration">Administration</option>
                            <option value="billing_finance">Billing & Finance</option>
                            <option value="human_resources">Human Resources</option>
                            <option value="housekeeping">Housekeeping</option>
                            <option value="security">Security</option>
                            <option value="transportation">Transportation / Ambulance</option>
                            <option value="maintenance">Maintenance / Engineering</option>
                            <option value="information_technology">Information Technology (IT)</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Textarea for Other Department -->
                    <div class="form-group conditional-field" id="otherDepartmentDiv">
                        <label for="otherDepartment">Specify Other Department *</label>
                        <textarea id="otherDepartment" name="other_department" rows="2" placeholder="Enter department name"></textarea>
                    </div>

                    <script>
                    function checkOtherDepartment() {
                        const select = document.getElementById("allDepartments");
                        const otherDiv = document.getElementById("otherDepartmentDiv");

                        if(select.value === "other") {
                            otherDiv.classList.add("show");
                        } else {
                            otherDiv.classList.remove("show");
                        }
                    }
                    </script>

                    <!-- Banking Information -->
                    <div class="form-group">
                        <label for="bankName">Bank *</label>
                        <select id="bankName" name="bank_name" required onchange="toggleBankAccountField()">
                            <option value="">Select Bank</option>
                            <option value="commercial_bank">Commercial Bank of Ethiopia</option>
                            <option value="dashen_bank">Dashen Bank</option>
                            <option value="abreha_weatsbeha_bank">Abreha Weatsbeha Bank</option>
                            <option value="zemen_bank">Zemen Bank</option>
                            <option value="nib_international_bank">Nib International Bank</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Hidden field for other bank name -->
                    <div class="form-group conditional-field" id="otherBankDiv">
                        <label for="otherBankName">Specify Other Bank *</label>
                        <input type="text" id="otherBankName" name="other_bank_name" placeholder="Enter bank name">
                    </div>

                    <!-- Bank Account Field (shows when bank is selected) -->
                    <div class="form-group conditional-field" id="bankAccountDiv">
                        <label for="bankAccount">Bank Account Number *</label>
                        <input type="text" id="bankAccount" name="bank_account" placeholder="Enter bank account number" required>
                    </div>

                    <script>
                    function toggleBankAccountField() {
                        const bankSelect = document.getElementById("bankName");
                        const bankAccountDiv = document.getElementById("bankAccountDiv");
                        const otherBankDiv = document.getElementById("otherBankDiv");
                        const bankAccountInput = document.getElementById("bankAccount");
                        
                        if (bankSelect.value !== "") {
                            // Show bank account field
                            bankAccountDiv.classList.add("show");
                            bankAccountInput.required = true;
                            
                            // Check if "Other" is selected
                            if (bankSelect.value === "other") {
                                otherBankDiv.classList.add("show");
                            } else {
                                otherBankDiv.classList.remove("show");
                            }
                        } else {
                            // Hide both fields if no bank selected
                            bankAccountDiv.classList.remove("show");
                            otherBankDiv.classList.remove("show");
                            bankAccountInput.required = false;
                            bankAccountInput.value = "";
                        }
                    }
                    </script>

                    <!-- Job Level -->
                    <div class="form-group">
                        <label for="jobLevel">Job Level *</label>
                        <select id="jobLevel" name="job_level" required onchange="checkOtherJobLevel()">
                            <option value="">Select Job Level</option>
                            <option value="entry">Entry Level</option>
                            <option value="junior">Junior</option>
                            <option value="mid">Mid Level</option>
                            <option value="senior">Senior</option>
                            <option value="lead">Lead / Team Lead</option>
                            <option value="manager">Manager</option>
                            <option value="director">Director</option>
                            <option value="executive">Executive / C-Level</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Textarea for Other Job Level -->
                    <div class="form-group conditional-field" id="otherJobLevelDiv">
                        <label for="otherJobLevel">Specify Other Job Level *</label>
                        <textarea id="otherJobLevel" name="other_job_level" rows="2" placeholder="Enter job level"></textarea>
                    </div>

                    <script>
                    function checkOtherJobLevel() {
                        const select = document.getElementById("jobLevel");
                        const otherDiv = document.getElementById("otherJobLevelDiv");

                        if(select.value === "other") {
                            otherDiv.classList.add("show");
                        } else {
                            otherDiv.classList.remove("show");
                        }
                    }
                    </script>

                    <!-- Marital Status -->
                    <div class="form-group">
                        <label for="maritalStatus">Marital Status *</label>
                        <select id="maritalStatus" name="marital_status" required onchange="checkMaritalStatus()">
                            <option value="">Select Marital Status</option>
                            <option value="single">Single</option>
                            <option value="married">Married</option>
                            <option value="divorced">Divorced</option>
                            <option value="widowed">Widowed</option>
                            <option value="separated">Separated</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Textarea for Other Marital Status -->
                    <div class="form-group conditional-field" id="otherMaritalStatusDiv">
                        <label for="otherMaritalStatus">Specify Marital Status *</label>
                        <textarea id="otherMaritalStatus" name="other_marital_status" rows="2" placeholder="Enter marital status"></textarea>
                    </div>

                    <script>
                    function checkMaritalStatus() {
                        const select = document.getElementById("maritalStatus");
                        const otherDiv = document.getElementById("otherMaritalStatusDiv");

                        if(select.value === "other") {
                            otherDiv.classList.add("show");
                        } else {
                            otherDiv.classList.remove("show");
                        }
                    }
                    </script>

                    <!-- Warranty Information -->
                    <div class="form-group">
                        <label for="warranty_status">Warranty Status *</label>
                        <select id="warranty_status" name="warranty_status" required onchange="toggleWarrantyFields()">
                            <option value="">Select Status</option>
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>

                    <!-- Hidden fields that show only if warranty = yes -->
                    <div class="conditional-field" id="warranty_fields">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="person_name">Person Name *</label>
                                <input type="text" id="person_name" name="person_name" placeholder="Enter Name">
                            </div>
                            <div class="form-group">
                                <label for="warranty_woreda">Woreda *</label>
                                <input type="text" id="warranty_woreda" name="warranty_woreda" placeholder="Enter Woreda">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="warranty_kebele">Kebele *</label>
                                <select id="warranty_kebele" name="warranty_kebele">
                                    <option value="">Select Kebele</option>
                                    <?php for($i=1; $i<=50; $i++): ?>
                                        <option value="Kebele <?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>">
                                            Kebele <?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number *</label>
                                <input type="text" id="phone" name="phone" placeholder="Enter Phone Number">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="warranty_type">Warranty Type *</label>
                                <select id="warranty_type" name="warranty_type">
                                    <option value="">Select Type</option>
                                    <option value="loan">Loan</option>
                                    <option value="employee">For Employee</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="scan_file">Scan File *</label>
                                <input type="file" id="scan_file" name="scan_file" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                    </div>

                    <script>
                    function toggleWarrantyFields() {
                        try {
                            const statusEl = document.getElementById("warranty_status");
                            const fieldsEl = document.getElementById("warranty_fields");
                            
                            if (!statusEl || !fieldsEl) {
                                console.warn("Warranty toggle elements missing from DOM");
                                return;
                            }
                            
                            const status = statusEl.value;
                            if (status === "yes") {
                                fieldsEl.classList.add("show");
                            } else {
                                fieldsEl.classList.remove("show");
                            }
                        } catch (error) {
                            console.error("Error in toggleWarrantyFields:", error);
                        }
                    }
                    </script>

                    <!-- Criminal Status -->
                    <div class="form-group">
                        <label for="criminal_status">Criminal Status *</label>
                        <select id="criminal_status" name="criminal_status" required onchange="toggleCriminalFields()">
                            <option value="">Select Status</option>
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>

                    <div class="conditional-field" id="criminal_fields">
                        <div class="form-group">
                            <label for="criminal_file">Criminal Record Document *</label>
                            <input type="file" id="criminal_file" name="criminal_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        </div>
                    </div>

                    <script>
                    function toggleCriminalFields() {
                        try {
                            const statusEl = document.getElementById("criminal_status");
                            const fieldsEl = document.getElementById("criminal_fields");
                            const fileInput = document.getElementById("criminal_file");

                            if (!statusEl || !fieldsEl || !fileInput) {
                                console.warn("Criminal toggle elements missing from DOM");
                                return;
                            }

                            const status = statusEl.value;
                            if (status === "yes") {
                                fieldsEl.classList.add("show");
                                fileInput.required = true;
                            } else {
                                fieldsEl.classList.remove("show");
                                fileInput.required = false;
                                fileInput.value = "";
                            }
                        } catch (error) {
                            console.error("Error in toggleCriminalFields:", error);
                        }
                    }
                    </script>

                    <!-- Financial Information -->
                    <div class="form-group">
                        <label for="fin_id">Fayda FIN / FIN ID *</label>
                        <input type="text" id="fin_id" name="fin_id" placeholder="Enter FIN or FIN ID" required>
                    </div>

                    <div class="form-group">
                        <label for="fin_scan">Scan FIN Document *</label>
                        <input type="file" id="fin_scan" name="fin_scan" accept="image/*,.pdf" required>
                    </div>

                    <!-- Loan Status -->
                    <div class="form-group">
                        <label for="loan_status">Loan Case *</label>
                        <select id="loan_status" name="loan_status" required onchange="toggleLoanFields()">
                            <option value="">Select Status</option>
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>

                    <div class="conditional-field" id="loan_fields">
                        <div class="form-group">
                            <label for="loan_file">Loan Agreement Document *</label>
                            <input type="file" id="loan_file" name="loan_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        </div>
                    </div>

                    <script>
                    function toggleLoanFields() {
                        try {
                            const statusEl = document.getElementById("loan_status");
                            const fieldsEl = document.getElementById("loan_fields");
                            const fileInput = document.getElementById("loan_file");

                            if (!statusEl || !fieldsEl || !fileInput) {
                                console.warn("Loan toggle elements missing from DOM");
                                return;
                            }

                            const status = statusEl.value;
                            if (status === "yes") {
                                fieldsEl.classList.add("show");
                                fileInput.required = true;
                            } else {
                                fieldsEl.classList.remove("show");
                                fileInput.required = false;
                                fileInput.value = "";
                            }
                        } catch (error) {
                            console.error("Error in toggleLoanFields:", error);
                        }
                    }
                    </script>

                    <!-- Document Upload -->
                    <div class="form-group">
                        <label>Employee Document Scan (Primary to Current)</label>
                        <input type="file" id="documents" name="documents[]" accept="image/*,.pdf,.doc,.docx" multiple style="display:none;" onchange="addDocument()">
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <button type="button" class="scan-btn" onclick="document.getElementById('documents').click();">
                                <i class="fas fa-camera"></i> Select Documents
                            </button>
                            <button type="button" class="scan-btn" onclick="clearDocuments()" style="background: #dc3545;">
                                <i class="fas fa-trash"></i> Clear All
                            </button>
                        </div>
                        <small style="color: #666; display: block; margin-top: 5px;">
                            You can select multiple files at once. Supported formats: PDF, JPG, PNG, DOC, DOCX
                        </small>
                    </div>

                    <div class="preview-container" id="previewContainer"></div>

                    <script>
                    function addDocument() {
                        try {
                            const input = document.getElementById("documents");
                            const previewContainer = document.getElementById("previewContainer");
                            
                            if (!input || !previewContainer) {
                                throw new Error("Document upload or preview elements not found");
                            }

                            const files = input.files;
                            if (files.length === 0) return;
                            
                            // Clear previous previews
                            previewContainer.innerHTML = '';

                            for (let i = 0; i < files.length; i++) {
                                const file = files[i];
                                
                                // Basic validation for large files
                                if (file.size > 10 * 1024 * 1024) { // 10MB limit
                                    console.warn(`File ${file.name} is too large (>10MB)`);
                                    continue;
                                }

                                const previewBox = document.createElement("div");
                                previewBox.className = "preview-box";

                                if (file.type.startsWith("image/")) {
                                    const img = document.createElement("img");
                                    img.src = URL.createObjectURL(file);
                                    img.alt = "Document Preview";
                                    // Handle preview generation errors
                                    img.onerror = () => { img.src = "https://cdn-icons-png.flaticon.com/512/337/337948.png"; };
                                    previewBox.appendChild(img);
                                } else {
                                    const pdfIcon = document.createElement("img");
                                    pdfIcon.src = "https://upload.wikimedia.org/wikipedia/commons/8/87/PDF_file_icon.svg";
                                    pdfIcon.alt = "File Icon";
                                    previewBox.appendChild(pdfIcon);
                                }

                                const fileName = document.createElement("p");
                                fileName.textContent = file.name;
                                previewBox.appendChild(fileName);
                                
                                const fileSize = document.createElement("small");
                                fileSize.textContent = formatFileSize(file.size);
                                fileSize.style.color = "#666";
                                previewBox.appendChild(fileSize);
                                
                                previewContainer.appendChild(previewBox);
                            }
                        } catch (error) {
                            console.error("Error in addDocument:", error);
                            alert("There was an error processing your documents. Please try again.");
                        }
                    }
                    
                    function clearDocuments() {
                        const input = document.getElementById("documents");
                        const previewContainer = document.getElementById("previewContainer");
                        
                        input.value = "";
                        previewContainer.innerHTML = "";
                    }
                    
                    function formatFileSize(bytes) {
                        if (bytes === 0) return '0 Bytes';
                        const k = 1024;
                        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                        const i = Math.floor(Math.log(bytes) / Math.log(k));
                        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                    }
                    </script>

                    <!-- Leave Request -->
                    <div class="form-group">
                        <label for="leaveRequest">Request Leave? *</label>
                        <select id="leaveRequest" name="leave_request" onchange="checkLeaveRequest()">
                            <option value="">--Select--</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>

                    <!-- Leave Document Scan Section -->
                    <div class="form-group conditional-field" id="leaveDocumentDiv">
                        <div class="form-group">
                            <label for="leave_document">Leave Application Document</label>
                            <input type="file" id="leave_document" name="leave_document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        </div>
                    </div>

                    <script>
                    function checkLeaveRequest() {
                        try {
                            const leaveSelect = document.getElementById("leaveRequest");
                            const docDiv = document.getElementById("leaveDocumentDiv");
                            
                            if (!leaveSelect || !docDiv) {
                                console.warn("Leave request elements missing from DOM");
                                return;
                            }
                        
                            if(leaveSelect.value === "yes" || leaveSelect.value === "no") {
                                docDiv.classList.add("show");
                            } else {
                                docDiv.classList.remove("show");
                            }
                        } catch (error) {
                            console.error("Error in checkLeaveRequest:", error);
                        }
                    }
                    </script>

                    <!-- Contact Information -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="tel" id="phone_number" name="phone_number">
                        </div>
                    </div>

                    <!-- Employment Details -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="position">Position *</label>
                            <input type="text" id="position" name="position" required>
                        </div>
                        <div class="form-group">
                            <label for="joinDate">Join Date *</label>
                            <input type="date" id="joinDate" name="join_date" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="salary">Salary *</label>
                            <input type="number" id="salary" name="salary" required step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="employmentType">Employment Type</label>
                            <select id="employmentType" name="employment_type">
                                <option value="full-time">Full-time</option>
                                <option value="part-time">Part-time</option>
                                <option value="contract">Contract</option>
                                <option value="intern">Intern</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="active">Active</option>
                            <option value="on-leave">On Leave</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="emergencyContact">Emergency Contact</label>
                        <input type="text" id="emergencyContact" name="emergency_contact">
                    </div>

                    <!-- Language Selection -->
                    <div class="language-selector">
                        <label for="language">Select Language *</label>
                        <select id="language" name="language" onchange="checkOtherLanguage()">
                            <option value="">--Select Language--</option>
                            <option value="english">English</option>
                            <option value="amharic">Amharic</option>
                            <option value="oromifa">Oromifa</option>
                            <option value="tigrigna">Tigrigna</option>
                            <option value="afar">Afar</option>
                            <option value="other">Other</option>
                        </select>

                        <div class="conditional-field" id="otherLanguageDiv">
                            <label for="otherLanguage">Specify Other Language *</label>
                            <input type="text" id="otherLanguage" name="other_language" placeholder="Enter language">
                        </div>
                    </div>

                    <script>
                    function checkOtherLanguage() {
                        try {
                            const langSelect = document.getElementById("language");
                            const otherDiv = document.getElementById("otherLanguageDiv");

                            if (!langSelect || !otherDiv) {
                                console.warn("Language selection elements missing from DOM");
                                return;
                            }

                            if(langSelect.value === "other") {
                                otherDiv.classList.add("show");
                            } else {
                                otherDiv.classList.remove("show");
                            }
                        } catch (error) {
                            console.error("Error in checkOtherLanguage:", error);
                        }
                    }
                    </script>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">Add Employee</button>
                        <button type="button" class="cancel-btn" onclick="window.history.back()">Cancel</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Sidebar toggle
        document.getElementById('toggleSidebar').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });

        document.getElementById('mobileMenuBtn').addEventListener('click', () => {
            document.getElementById('sidebar').classList.add('mobile-open');
            document.getElementById('mobileOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        document.getElementById('mobileOverlay').addEventListener('click', () => {
            document.getElementById('sidebar').classList.remove('mobile-open');
            document.getElementById('mobileOverlay').classList.remove('active');
            document.body.style.overflow = 'auto';
        });

        // Set default dates
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('joinDate').value = today;
        });

        // Form validation
        document.getElementById('employeeForm').addEventListener('submit', function(e) {
            try {
                const requiredFields = [
                    'first_name', 'last_name', 'gender', 'date_of_birth', 'email', 'position'
                ];
                
                let missingFields = [];
                
                requiredFields.forEach(field => {
                    const element = document.querySelector(`[name="${field}"]`);
                    if (element && !element.value.trim()) {
                        missingFields.push(field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()));
                    }
                });
                
                // Validate email format
                const emailEl = document.querySelector('[name="email"]');
                const email = emailEl ? emailEl.value : '';
                if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    missingFields.push('Valid Email Address');
                }
                
                // Validate age (must be at least 18)
                const dobEl = document.querySelector('[name="date_of_birth"]');
                const dob = dobEl ? dobEl.value : '';
                if (dob) {
                    const birthDate = new Date(dob);
                    if (isNaN(birthDate.getTime())) {
                        missingFields.push('Valid Date of Birth');
                    } else {
                        const today = new Date();
                        let age = today.getFullYear() - birthDate.getFullYear();
                        const monthDiff = today.getMonth() - birthDate.getMonth();
                        
                        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                            age--;
                        }
                        
                        if (age < 18) {
                            missingFields.push('Employee must be at least 18 years old');
                        }
                    }
                }
                
                // Validate salary if provided
                const salaryEl = document.querySelector('[name="salary"]');
                const salary = salaryEl ? salaryEl.value : '';
                if (salary && (isNaN(parseFloat(salary)) || parseFloat(salary) <= 0)) {
                    missingFields.push('Valid Salary Amount');
                }
                
                if (missingFields.length > 0) {
                    e.preventDefault();
                    alert('Please fix the following issues:\n\n• ' + missingFields.join('\n• '));
                    return false;
                }
                
                // Show loading state
                const submitBtn = document.querySelector('.submit-btn');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Adding Employee...';
                }
                
                return true;
            } catch (error) {
                e.preventDefault();
                console.error("Form submission error:", error);
                alert("An unexpected error occurred during form submission. Please try again.");
                return false;
            }
        });

        // Real-time validation feedback
        document.querySelectorAll('input[required], select[required]').forEach(field => {
            field.addEventListener('blur', function() {
                if (this.value.trim()) {
                    this.style.borderColor = '#27ae60';
                } else {
                    this.style.borderColor = '#e74c3c';
                }
            });
        });
    </script>
</body>
</html>