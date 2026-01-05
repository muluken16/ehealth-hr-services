<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Set default user for demo (same as in add_employee.php)
$_SESSION['user_name'] = 'Kebele HR Officer';
$_SESSION['role'] = 'kebele_hr';

// Create database connection
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get employee ID from URL
$employee_id = $_GET['id'] ?? null;

if (!$employee_id) {
    echo "<script>alert('Employee ID is required'); window.location.href='hr-employees.html';</script>";
    exit();
}

// Get current user
$current_user = $_SESSION['user_name'] ?? 'Unknown';

// Fetch employee data
$sql = "SELECT * FROM employees WHERE (id = ? OR employee_id = ?) AND created_by = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $employee_id, $employee_id, $current_user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Employee not found or you do not have permission to edit this employee'); window.location.href='hr-employees.html';</script>";
    exit();
}

$employee = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    
    // Validate required fields
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
    
    // Validate age (must be at least 18)
    if (!empty($_POST['date_of_birth'])) {
        $dob = new DateTime($_POST['date_of_birth']);
        $today = new DateTime();
        $age = $today->diff($dob)->y;
        if ($age < 18) {
            $errors[] = "Employee must be at least 18 years old";
        }
    }
    
    // Validate salary if provided
    if (!empty($_POST['salary']) && $_POST['salary'] <= 0) {
        $errors[] = "Salary must be a positive number";
    }
    
    if (!empty($errors)) {
        $error_message = implode('<br>', $errors);
        echo "<script>alert('Please fix the following errors:\\n" . addslashes($error_message) . "'); window.history.back();</script>";
        exit();
    }
    
    // Handle file uploads (same as add_employee.php)
    $upload_dir = '../uploads/employees/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $documents = [];
    $scan_file = $employee['scan_file'] ?? '';
    $criminal_file = $employee['criminal_file'] ?? '';
    $fin_scan = $employee['fin_scan'] ?? '';
    $loan_file = $employee['loan_file'] ?? '';
    $leave_document = $employee['leave_document'] ?? '';

    // Handle new document uploads
    if (isset($_FILES['documents']) && is_array($_FILES['documents']['name'])) {
        $files = $_FILES['documents'];
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] == 0 && !empty($files['name'][$i])) {
                $file_extension = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                $allowed_types = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
                if (in_array($file_extension, $allowed_types)) {
                    $file_name = $employee['employee_id'] . '_doc_' . $i . '_' . time() . '.' . $file_extension;
                    if (move_uploaded_file($files['tmp_name'][$i], $upload_dir . $file_name)) {
                        $documents[] = $file_name;
                    }
                }
            }
        }
    }

    // Handle individual file uploads with proper variable assignment
    if (isset($_FILES['scan_file']) && $_FILES['scan_file']['error'] == 0) {
        $file_extension = strtolower(pathinfo($_FILES['scan_file']['name'], PATHINFO_EXTENSION));
        $file_name = $employee['employee_id'] . '_scan_' . time() . '.' . $file_extension;
        if (move_uploaded_file($_FILES['scan_file']['tmp_name'], $upload_dir . $file_name)) {
            $scan_file = $file_name;
        }
    }

    if (isset($_FILES['criminal_file']) && $_FILES['criminal_file']['error'] == 0) {
        $file_extension = strtolower(pathinfo($_FILES['criminal_file']['name'], PATHINFO_EXTENSION));
        $file_name = $employee['employee_id'] . '_criminal_' . time() . '.' . $file_extension;
        if (move_uploaded_file($_FILES['criminal_file']['tmp_name'], $upload_dir . $file_name)) {
            $criminal_file = $file_name;
        }
    }

    if (isset($_FILES['fin_scan']) && $_FILES['fin_scan']['error'] == 0) {
        $file_extension = strtolower(pathinfo($_FILES['fin_scan']['name'], PATHINFO_EXTENSION));
        $file_name = $employee['employee_id'] . '_fin_scan_' . time() . '.' . $file_extension;
        if (move_uploaded_file($_FILES['fin_scan']['tmp_name'], $upload_dir . $file_name)) {
            $fin_scan = $file_name;
        }
    }

    if (isset($_FILES['loan_file']) && $_FILES['loan_file']['error'] == 0) {
        $file_extension = strtolower(pathinfo($_FILES['loan_file']['name'], PATHINFO_EXTENSION));
        $file_name = $employee['employee_id'] . '_loan_' . time() . '.' . $file_extension;
        if (move_uploaded_file($_FILES['loan_file']['tmp_name'], $upload_dir . $file_name)) {
            $loan_file = $file_name;
        }
    }

    if (isset($_FILES['leave_document']) && $_FILES['leave_document']['error'] == 0) {
        $file_extension = strtolower(pathinfo($_FILES['leave_document']['name'], PATHINFO_EXTENSION));
        $file_name = $employee['employee_id'] . '_leave_' . time() . '.' . $file_extension;
        if (move_uploaded_file($_FILES['leave_document']['tmp_name'], $upload_dir . $file_name)) {
            $leave_document = $file_name;
        }
    }

    // Merge existing documents with new ones
    if ($employee['documents']) {
        $existing_docs = json_decode($employee['documents'], true);
        if (is_array($existing_docs)) {
            $documents = array_merge($existing_docs, $documents);
        }
    }
    
    // Update employee data
    try {
        $conn->begin_transaction();
        
        // Prepare all update data (matching the database structure)
        $first_name = trim($_POST['first_name']);
        $middle_name = trim($_POST['middle_name'] ?? '');
        $last_name = trim($_POST['last_name']);
        $gender = $_POST['gender'];
        $date_of_birth = $_POST['date_of_birth'];
        $religion = $_POST['religion'] ?? '';
        $citizenship = $_POST['citizenship'] ?? '';
        $other_citizenship = trim($_POST['other_citizenship'] ?? '');
        $region = $_POST['region'] ?? '';
        $zone = $_POST['zone'] ?? '';
        $woreda = $_POST['woreda'] ?? '';
        $kebele = $_POST['kebele'] ?? '';
        $education_level = $_POST['education_level'] ?? '';
        $primary_school = $_POST['primary_school'] ?? '';
        $secondary_school = $_POST['secondary_school'] ?? '';
        $college = $_POST['college'] ?? '';
        $university = $_POST['university'] ?? '';
        $department = $_POST['department'] ?? '';
        $other_department = $_POST['other_department'] ?? '';
        $bank_name = $_POST['bank_name'] ?? '';
        $bank_account = $_POST['bank_account'] ?? '';
        $job_level = $_POST['job_level'] ?? '';
        $other_job_level = $_POST['other_job_level'] ?? '';
        $marital_status = $_POST['marital_status'] ?? '';
        $other_marital_status = $_POST['other_marital_status'] ?? '';
        $warranty_status = $_POST['warranty_status'] ?? '';
        $person_name = $_POST['person_name'] ?? '';
        $warranty_woreda = $_POST['warranty_woreda'] ?? '';
        $warranty_kebele = $_POST['warranty_kebele'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $warranty_type = $_POST['warranty_type'] ?? '';
        $criminal_status = $_POST['criminal_status'] ?? '';
        $fin_id = $_POST['fin_id'] ?? '';
        $loan_status = $_POST['loan_status'] ?? '';
        $leave_request = $_POST['leave_request'] ?? '';
        $email = $_POST['email'];
        $phone_number = $_POST['phone_number'] ?? '';
        $department_assigned = $_POST['department_assigned'] ?? '';
        $position = $_POST['position'];
        $join_date = $_POST['join_date'] ?? $employee['join_date'];
        $salary = $_POST['salary'] ?? '';
        $employment_type = $_POST['employment_type'] ?? 'full-time';
        $status = $_POST['status'] ?? 'active';
        $address = $_POST['address'] ?? '';
        $emergency_contact = $_POST['emergency_contact'] ?? '';
        $documents_json = json_encode($documents);
        
        // Update SQL (matching the exact database structure)
        $update_sql = "UPDATE employees SET 
            first_name = ?, middle_name = ?, last_name = ?, gender = ?, date_of_birth = ?, religion = ?, citizenship = ?, other_citizenship = ?, region = ?, zone = ?, woreda = ?, kebele = ?, education_level = ?, primary_school = ?, secondary_school = ?, college = ?, university = ?, department = ?, other_department = ?, bank_name = ?, bank_account = ?, job_level = ?, other_job_level = ?, marital_status = ?, other_marital_status = ?, warranty_status = ?, person_name = ?, warranty_woreda = ?, warranty_kebele = ?, phone = ?, warranty_type = ?, scan_file = ?, criminal_status = ?, criminal_file = ?, fin_id = ?, fin_scan = ?, loan_status = ?, loan_file = ?, leave_request = ?, leave_document = ?, email = ?, phone_number = ?, department_assigned = ?, position = ?, join_date = ?, salary = ?, employment_type = ?, status = ?, address = ?, emergency_contact = ?, documents = ?, updated_at = CURRENT_TIMESTAMP
            WHERE (id = ? OR employee_id = ?) AND created_by = ?";
        
        $update_stmt = $conn->prepare($update_sql);
        if (!$update_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $update_stmt->bind_param(
            "ssssssssssssssssssssssssssssssssssssssssssssssssssssss",
            $first_name, $middle_name, $last_name, $gender, $date_of_birth, $religion, $citizenship, $other_citizenship, $region, $zone, $woreda, $kebele, $education_level, $primary_school, $secondary_school, $college, $university, $department, $other_department, $bank_name, $bank_account, $job_level, $other_job_level, $marital_status, $other_marital_status, $warranty_status, $person_name, $warranty_woreda, $warranty_kebele, $phone, $warranty_type, $scan_file, $criminal_status, $criminal_file, $fin_id, $fin_scan, $loan_status, $loan_file, $leave_request, $leave_document, $email, $phone_number, $department_assigned, $position, $join_date, $salary, $employment_type, $status, $address, $emergency_contact, $documents_json, $employee_id, $employee_id, $current_user
        );
        
        if (!$update_stmt->execute()) {
            throw new Exception("Update failed: " . $update_stmt->error);
        }
        
        $conn->commit();
        $update_stmt->close();
        
        echo "<script>
            alert('✅ Employee updated successfully!\\nEmployee: {$first_name} {$last_name}');
            window.location.href='hr-employees.html';
        </script>";
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>
            alert('❌ Error updating employee: " . addslashes($e->getMessage()) . "');
            window.history.back();
        </script>";
    }
    
    $conn->close();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Edit Employee</title>
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

        /* Conditional Fields Animation */
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
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
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
            box-shadow: 0 6px 20px rgba(243, 156, 18, 0.4);
        }

        .cancel-btn {
            background: #6c757d;
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
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
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
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="hr-recruitment.html">
                            <i class="fas fa-user-plus"></i>
                            <span class="menu-text">Recruitment</span>
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
                    <h1 class="page-title">Edit Employee: <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></h1>
                </div>

                <div class="header-actions">
                    <button class="btn-secondary" onclick="window.location.href='hr-employees.html'">
                        <i class="fas fa-arrow-left"></i> Back to Employees
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
                            <input type="text" id="firstName" name="first_name" value="<?php echo htmlspecialchars($employee['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="middleName">Middle Name</label>
                            <input type="text" id="middleName" name="middle_name" value="<?php echo htmlspecialchars($employee['middle_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name *</label>
                            <input type="text" id="lastName" name="last_name" value="<?php echo htmlspecialchars($employee['last_name']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="newPatientGender">Gender *</label>
                        <select id="newPatientGender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male" <?php echo ($employee['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo ($employee['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                            <option value="other" <?php echo ($employee['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="dateOfBirth">Date of Birth *</label>
                        <input type="date" id="dateOfBirth" name="date_of_birth" value="<?php echo $employee['date_of_birth']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="religion">Religion *</label>
                        <select id="religion" name="religion" required>
                            <option value="">Select Religion</option>
                            <option value="christianity" <?php echo ($employee['religion'] == 'christianity') ? 'selected' : ''; ?>>Orthodox</option>
                            <option value="islam" <?php echo ($employee['religion'] == 'islam') ? 'selected' : ''; ?>>Islam</option>
                            <option value="protestant" <?php echo ($employee['religion'] == 'protestant') ? 'selected' : ''; ?>>Protestant</option>
                            <option value="judaism" <?php echo ($employee['religion'] == 'judaism') ? 'selected' : ''; ?>>Judaism</option>
                            <option value="hinduism" <?php echo ($employee['religion'] == 'hinduism') ? 'selected' : ''; ?>>Hinduism</option>
                            <option value="other" <?php echo ($employee['religion'] == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="citizenship">Citizenship *</label>
                        <select id="citizenship" name="citizenship" required onchange="checkOther(this)">
                            <option value="">Select your country</option>
                            <option value="Ethiopia" <?php echo ($employee['citizenship'] == 'Ethiopia') ? 'selected' : ''; ?>>Ethiopia</option>
                            <option value="United States" <?php echo ($employee['citizenship'] == 'United States') ? 'selected' : ''; ?>>United States</option>
                            <option value="United Kingdom" <?php echo ($employee['citizenship'] == 'United Kingdom') ? 'selected' : ''; ?>>United Kingdom</option>
                            <option value="Canada" <?php echo ($employee['citizenship'] == 'Canada') ? 'selected' : ''; ?>>Canada</option>
                            <option value="Germany" <?php echo ($employee['citizenship'] == 'Germany') ? 'selected' : ''; ?>>Germany</option>
                            <option value="France" <?php echo ($employee['citizenship'] == 'France') ? 'selected' : ''; ?>>France</option>
                            <option value="India" <?php echo ($employee['citizenship'] == 'India') ? 'selected' : ''; ?>>India</option>
                            <option value="China" <?php echo ($employee['citizenship'] == 'China') ? 'selected' : ''; ?>>China</option>
                            <option value="Japan" <?php echo ($employee['citizenship'] == 'Japan') ? 'selected' : ''; ?>>Japan</option>
                            <option value="Other" <?php echo ($employee['citizenship'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>

                        <!-- Input for "Other" country -->
                        <input type="text" id="otherCitizenship" name="other_citizenship" placeholder="Enter your country" value="<?php echo htmlspecialchars($employee['other_citizenship'] ?? ''); ?>" style="<?php echo ($employee['citizenship'] == 'Other') ? 'display:block; margin-top: 10px;' : 'display:none; margin-top: 10px;'; ?>">
                    </div>

                    <!-- Region Zone Woreda Selection -->
                    <div class="form-group">
                        <label for="region">Region</label>
                        <select id="region" name="region" onchange="loadZones()">
                            <option value="">Select Region</option>
                            <option value="amhara" <?php echo ($employee['region'] == 'amhara') ? 'selected' : ''; ?>>Amhara</option>
                            <option value="oromia" <?php echo ($employee['region'] == 'oromia') ? 'selected' : ''; ?>>Oromia</option>
                            <option value="tigray" <?php echo ($employee['region'] == 'tigray') ? 'selected' : ''; ?>>Tigray</option>
                            <option value="snnpr" <?php echo ($employee['region'] == 'snnpr') ? 'selected' : ''; ?>>SNNP</option>
                            <option value="afar" <?php echo ($employee['region'] == 'afar') ? 'selected' : ''; ?>>Afar</option>
                            <option value="somali" <?php echo ($employee['region'] == 'somali') ? 'selected' : ''; ?>>Somali</option>
                            <option value="gambela" <?php echo ($employee['region'] == 'gambela') ? 'selected' : ''; ?>>Gambela</option>
                            <option value="benishangul" <?php echo ($employee['region'] == 'benishangul') ? 'selected' : ''; ?>>Benishangul Gumuz</option>
                            <option value="harari" <?php echo ($employee['region'] == 'harari') ? 'selected' : ''; ?>>Harari</option>
                            <option value="addis_ababa" <?php echo ($employee['region'] == 'addis_ababa') ? 'selected' : ''; ?>>Addis Ababa</option>
                            <option value="dire_dawa" <?php echo ($employee['region'] == 'dire_dawa') ? 'selected' : ''; ?>>Dire Dawa</option>
                            <option value="other" <?php echo ($employee['region'] == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="zone">Zone</label>
                        <select id="zone" name="zone" onchange="loadWoredas()">
                            <option value="">Select Zone</option>
                            <?php if ($employee['zone']): ?>
                                <option value="<?php echo htmlspecialchars($employee['zone']); ?>" selected><?php echo htmlspecialchars($employee['zone']); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="woreda">Woreda</label>
                        <select id="woreda" name="woreda" onchange="loadKebeles()">
                            <option value="">Select Woreda</option>
                            <?php if ($employee['woreda']): ?>
                                <option value="<?php echo htmlspecialchars($employee['woreda']); ?>" selected><?php echo htmlspecialchars($employee['woreda']); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="kebele">Kebele</label>
                        <select id="kebele" name="kebele">
                            <option value="">Select Kebele</option>
                            <?php if ($employee['kebele']): ?>
                                <option value="<?php echo htmlspecialchars($employee['kebele']); ?>" selected><?php echo htmlspecialchars($employee['kebele']); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Academic Information -->
                    <div class="form-group">
                        <label for="educationLevel">Education Level</label>
                        <select id="educationLevel" name="education_level">
                            <option value="">Select Education Level</option>
                            <option value="none" <?php echo ($employee['education_level'] == 'none') ? 'selected' : ''; ?>>No Formal Education</option>
                            <option value="primary" <?php echo ($employee['education_level'] == 'primary') ? 'selected' : ''; ?>>Primary School</option>
                            <option value="secondary" <?php echo ($employee['education_level'] == 'secondary') ? 'selected' : ''; ?>>Secondary School</option>
                            <option value="diploma" <?php echo ($employee['education_level'] == 'diploma') ? 'selected' : ''; ?>>Diploma</option>
                            <option value="bachelor" <?php echo ($employee['education_level'] == 'bachelor') ? 'selected' : ''; ?>>Bachelor's Degree</option>
                            <option value="master" <?php echo ($employee['education_level'] == 'master') ? 'selected' : ''; ?>>Master's Degree</option>
                            <option value="phd" <?php echo ($employee['education_level'] == 'phd') ? 'selected' : ''; ?>>PhD</option>
                            <option value="other" <?php echo ($employee['education_level'] == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="primarySchool">Primary School</label>
                        <input type="text" id="primarySchool" name="primary_school" placeholder="Enter details about Primary School" value="<?php echo htmlspecialchars($employee['primary_school'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="secondarySchool">Secondary School</label>
                        <input type="text" id="secondarySchool" name="secondary_school" placeholder="Enter details about Secondary School" value="<?php echo htmlspecialchars($employee['secondary_school'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="college">College</label>
                        <input type="text" id="college" name="college" placeholder="Enter details about College" value="<?php echo htmlspecialchars($employee['college'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="university">University</label>
                        <input type="text" id="university" name="university" placeholder="Enter details about University" value="<?php echo htmlspecialchars($employee['university'] ?? ''); ?>">
                    </div>

                    <!-- Department Information -->
                    <div class="form-group">
                        <label for="allDepartments">Department</label>
                        <select id="allDepartments" name="department_assigned" onchange="checkOtherDepartment()">
                            <option value="">Select Department</option>
                            <option value="general_medicine" <?php echo ($employee['department_assigned'] == 'general_medicine') ? 'selected' : ''; ?>>General Medicine</option>
                            <option value="pediatrics" <?php echo ($employee['department_assigned'] == 'pediatrics') ? 'selected' : ''; ?>>Pediatrics</option>
                            <option value="obstetrics_gynecology" <?php echo ($employee['department_assigned'] == 'obstetrics_gynecology') ? 'selected' : ''; ?>>Obstetrics & Gynecology</option>
                            <option value="surgery" <?php echo ($employee['department_assigned'] == 'surgery') ? 'selected' : ''; ?>>Surgery</option>
                            <option value="orthopedics" <?php echo ($employee['department_assigned'] == 'orthopedics') ? 'selected' : ''; ?>>Orthopedics</option>
                            <option value="dermatology" <?php echo ($employee['department_assigned'] == 'dermatology') ? 'selected' : ''; ?>>Dermatology</option>
                            <option value="ophthalmology" <?php echo ($employee['department_assigned'] == 'ophthalmology') ? 'selected' : ''; ?>>Ophthalmology</option>
                            <option value="dentistry" <?php echo ($employee['department_assigned'] == 'dentistry') ? 'selected' : ''; ?>>Dentistry</option>
                            <option value="psychiatry" <?php echo ($employee['department_assigned'] == 'psychiatry') ? 'selected' : ''; ?>>Psychiatry / Mental Health</option>
                            <option value="rehabilitation" <?php echo ($employee['department_assigned'] == 'rehabilitation') ? 'selected' : ''; ?>>Rehabilitation / Physiotherapy</option>
                            <option value="nutrition" <?php echo ($employee['department_assigned'] == 'nutrition') ? 'selected' : ''; ?>>Nutrition / Dietetics</option>
                            <option value="laboratory" <?php echo ($employee['department_assigned'] == 'laboratory') ? 'selected' : ''; ?>>Laboratory</option>
                            <option value="radiology" <?php echo ($employee['department_assigned'] == 'radiology') ? 'selected' : ''; ?>>Radiology / Imaging</option>
                            <option value="pathology" <?php echo ($employee['department_assigned'] == 'pathology') ? 'selected' : ''; ?>>Pathology</option>
                            <option value="emergency" <?php echo ($employee['department_assigned'] == 'emergency') ? 'selected' : ''; ?>>Emergency / ER</option>
                            <option value="intensive_care" <?php echo ($employee['department_assigned'] == 'intensive_care') ? 'selected' : ''; ?>>Intensive Care Unit (ICU)</option>
                            <option value="cardiology" <?php echo ($employee['department_assigned'] == 'cardiology') ? 'selected' : ''; ?>>Cardiology</option>
                            <option value="neurology" <?php echo ($employee['department_assigned'] == 'neurology') ? 'selected' : ''; ?>>Neurology</option>
                            <option value="oncology" <?php echo ($employee['department_assigned'] == 'oncology') ? 'selected' : ''; ?>>Oncology</option>
                            <option value="pharmacy" <?php echo ($employee['department_assigned'] == 'pharmacy') ? 'selected' : ''; ?>>Pharmacy</option>
                            <option value="medical_records" <?php echo ($employee['department_assigned'] == 'medical_records') ? 'selected' : ''; ?>>Medical Records</option>
                            <option value="hospital_administration" <?php echo ($employee['department_assigned'] == 'hospital_administration') ? 'selected' : ''; ?>>Administration</option>
                            <option value="billing_finance" <?php echo ($employee['department_assigned'] == 'billing_finance') ? 'selected' : ''; ?>>Billing & Finance</option>
                            <option value="human_resources" <?php echo ($employee['department_assigned'] == 'human_resources') ? 'selected' : ''; ?>>Human Resources</option>
                            <option value="housekeeping" <?php echo ($employee['department_assigned'] == 'housekeeping') ? 'selected' : ''; ?>>Housekeeping</option>
                            <option value="security" <?php echo ($employee['department_assigned'] == 'security') ? 'selected' : ''; ?>>Security</option>
                            <option value="transportation" <?php echo ($employee['department_assigned'] == 'transportation') ? 'selected' : ''; ?>>Transportation / Ambulance</option>
                            <option value="maintenance" <?php echo ($employee['department_assigned'] == 'maintenance') ? 'selected' : ''; ?>>Maintenance / Engineering</option>
                            <option value="information_technology" <?php echo ($employee['department_assigned'] == 'information_technology') ? 'selected' : ''; ?>>Information Technology (IT)</option>
                            <option value="other" <?php echo ($employee['department_assigned'] == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <!-- Textarea for Other Department -->
                    <div class="form-group conditional-field <?php echo ($employee['department_assigned'] == 'other') ? 'show' : ''; ?>" id="otherDepartmentDiv">
                        <label for="otherDepartment">Specify Other Department</label>
                        <textarea id="otherDepartment" name="other_department" rows="2" placeholder="Enter department name"><?php echo htmlspecialchars($employee['other_department'] ?? ''); ?></textarea>
                    </div>

                    <!-- Banking Information -->
                    <div class="form-group">
                        <label for="bankName">Bank</label>
                        <select id="bankName" name="bank_name" onchange="toggleBankAccountField()">
                            <option value="">Select Bank</option>
                            <option value="commercial_bank" <?php echo ($employee['bank_name'] == 'commercial_bank') ? 'selected' : ''; ?>>Commercial Bank of Ethiopia</option>
                            <option value="dashen_bank" <?php echo ($employee['bank_name'] == 'dashen_bank') ? 'selected' : ''; ?>>Dashen Bank</option>
                            <option value="abreha_weatsbeha_bank" <?php echo ($employee['bank_name'] == 'abreha_weatsbeha_bank') ? 'selected' : ''; ?>>Abreha Weatsbeha Bank</option>
                            <option value="zemen_bank" <?php echo ($employee['bank_name'] == 'zemen_bank') ? 'selected' : ''; ?>>Zemen Bank</option>
                            <option value="nib_international_bank" <?php echo ($employee['bank_name'] == 'nib_international_bank') ? 'selected' : ''; ?>>Nib International Bank</option>
                            <option value="other" <?php echo ($employee['bank_name'] == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <!-- Bank Account Field -->
                    <div class="form-group conditional-field <?php echo (!empty($employee['bank_name'])) ? 'show' : ''; ?>" id="bankAccountDiv">
                        <label for="bankAccount">Bank Account Number</label>
                        <input type="text" id="bankAccount" name="bank_account" placeholder="Enter bank account number" value="<?php echo htmlspecialchars($employee['bank_account'] ?? ''); ?>">
                    </div>

                    <!-- Job Level -->
                    <div class="form-group">
                        <label for="jobLevel">Job Level</label>
                        <select id="jobLevel" name="job_level" onchange="checkOtherJobLevel()">
                            <option value="">Select Job Level</option>
                            <option value="entry" <?php echo ($employee['job_level'] == 'entry') ? 'selected' : ''; ?>>Entry Level</option>
                            <option value="junior" <?php echo ($employee['job_level'] == 'junior') ? 'selected' : ''; ?>>Junior</option>
                            <option value="mid" <?php echo ($employee['job_level'] == 'mid') ? 'selected' : ''; ?>>Mid Level</option>
                            <option value="senior" <?php echo ($employee['job_level'] == 'senior') ? 'selected' : ''; ?>>Senior</option>
                            <option value="lead" <?php echo ($employee['job_level'] == 'lead') ? 'selected' : ''; ?>>Lead / Team Lead</option>
                            <option value="manager" <?php echo ($employee['job_level'] == 'manager') ? 'selected' : ''; ?>>Manager</option>
                            <option value="director" <?php echo ($employee['job_level'] == 'director') ? 'selected' : ''; ?>>Director</option>
                            <option value="executive" <?php echo ($employee['job_level'] == 'executive') ? 'selected' : ''; ?>>Executive / C-Level</option>
                            <option value="other" <?php echo ($employee['job_level'] == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <!-- Textarea for Other Job Level -->
                    <div class="form-group conditional-field <?php echo ($employee['job_level'] == 'other') ? 'show' : ''; ?>" id="otherJobLevelDiv">
                        <label for="otherJobLevel">Specify Other Job Level</label>
                        <textarea id="otherJobLevel" name="other_job_level" rows="2" placeholder="Enter job level"><?php echo htmlspecialchars($employee['other_job_level'] ?? ''); ?></textarea>
                    </div>

                    <!-- Marital Status -->
                    <div class="form-group">
                        <label for="maritalStatus">Marital Status</label>
                        <select id="maritalStatus" name="marital_status" onchange="checkMaritalStatus()">
                            <option value="">Select Marital Status</option>
                            <option value="single" <?php echo ($employee['marital_status'] == 'single') ? 'selected' : ''; ?>>Single</option>
                            <option value="married" <?php echo ($employee['marital_status'] == 'married') ? 'selected' : ''; ?>>Married</option>
                            <option value="divorced" <?php echo ($employee['marital_status'] == 'divorced') ? 'selected' : ''; ?>>Divorced</option>
                            <option value="widowed" <?php echo ($employee['marital_status'] == 'widowed') ? 'selected' : ''; ?>>Widowed</option>
                            <option value="separated" <?php echo ($employee['marital_status'] == 'separated') ? 'selected' : ''; ?>>Separated</option>
                            <option value="other" <?php echo ($employee['marital_status'] == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <!-- Textarea for Other Marital Status -->
                    <div class="form-group conditional-field <?php echo ($employee['marital_status'] == 'other') ? 'show' : ''; ?>" id="otherMaritalStatusDiv">
                        <label for="otherMaritalStatus">Specify Marital Status</label>
                        <textarea id="otherMaritalStatus" name="other_marital_status" rows="2" placeholder="Enter marital status"><?php echo htmlspecialchars($employee['other_marital_status'] ?? ''); ?></textarea>
                    </div>

                    <!-- Warranty Information -->
                    <div class="form-group">
                        <label for="warranty_status">Warranty Status</label>
                        <select id="warranty_status" name="warranty_status" onchange="toggleWarrantyFields()">
                            <option value="">Select Status</option>
                            <option value="no" <?php echo ($employee['warranty_status'] == 'no') ? 'selected' : ''; ?>>No</option>
                            <option value="yes" <?php echo ($employee['warranty_status'] == 'yes') ? 'selected' : ''; ?>>Yes</option>
                        </select>
                    </div>

                    <!-- Hidden fields that show only if warranty = yes -->
                    <div class="conditional-field <?php echo ($employee['warranty_status'] == 'yes') ? 'show' : ''; ?>" id="warranty_fields">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="person_name">Person Name</label>
                                <input type="text" id="person_name" name="person_name" placeholder="Enter Name" value="<?php echo htmlspecialchars($employee['person_name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="warranty_woreda">Woreda</label>
                                <input type="text" id="warranty_woreda" name="warranty_woreda" placeholder="Enter Woreda" value="<?php echo htmlspecialchars($employee['warranty_woreda'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="warranty_kebele">Kebele</label>
                                <select id="warranty_kebele" name="warranty_kebele">
                                    <option value="">Select Kebele</option>
                                    <?php for($i=1; $i<=50; $i++): 
                                        $kebele_value = "Kebele " . str_pad($i, 2, '0', STR_PAD_LEFT);
                                        $selected = ($employee['warranty_kebele'] == $kebele_value) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $kebele_value; ?>" <?php echo $selected; ?>><?php echo $kebele_value; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="text" id="phone" name="phone" placeholder="Enter Phone Number" value="<?php echo htmlspecialchars($employee['phone'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="warranty_type">Warranty Type</label>
                                <select id="warranty_type" name="warranty_type">
                                    <option value="">Select Type</option>
                                    <option value="loan" <?php echo ($employee['warranty_type'] == 'loan') ? 'selected' : ''; ?>>Loan</option>
                                    <option value="employee" <?php echo ($employee['warranty_type'] == 'employee') ? 'selected' : ''; ?>>For Employee</option>
                                    <option value="other" <?php echo ($employee['warranty_type'] == 'other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="scan_file">Scan File</label>
                                <input type="file" id="scan_file" name="scan_file" accept=".pdf,.jpg,.jpeg,.png">
                                <?php if (!empty($employee['scan_file'])): ?>
                                    <small style="color: #666;">Current file: <?php echo htmlspecialchars($employee['scan_file']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Criminal Status -->
                    <div class="form-group">
                        <label for="criminal_status">Criminal Status</label>
                        <select id="criminal_status" name="criminal_status" onchange="toggleCriminalFields()">
                            <option value="">Select Status</option>
                            <option value="no" <?php echo ($employee['criminal_status'] == 'no') ? 'selected' : ''; ?>>No</option>
                            <option value="yes" <?php echo ($employee['criminal_status'] == 'yes') ? 'selected' : ''; ?>>Yes</option>
                        </select>
                    </div>

                    <div class="conditional-field <?php echo ($employee['criminal_status'] == 'yes') ? 'show' : ''; ?>" id="criminal_fields">
                        <div class="form-group">
                            <label for="criminal_file">Criminal Record Document</label>
                            <input type="file" id="criminal_file" name="criminal_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <?php if (!empty($employee['criminal_file'])): ?>
                                <small style="color: #666;">Current file: <?php echo htmlspecialchars($employee['criminal_file']); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Financial Information -->
                    <div class="form-group">
                        <label for="fin_id">Fayda FIN / FIN ID</label>
                        <input type="text" id="fin_id" name="fin_id" placeholder="Enter FIN or FIN ID" value="<?php echo htmlspecialchars($employee['fin_id'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="fin_scan">Scan FIN Document</label>
                        <input type="file" id="fin_scan" name="fin_scan" accept="image/*,.pdf">
                        <?php if (!empty($employee['fin_scan'])): ?>
                            <small style="color: #666;">Current file: <?php echo htmlspecialchars($employee['fin_scan']); ?></small>
                        <?php endif; ?>
                    </div>

                    <!-- Loan Status -->
                    <div class="form-group">
                        <label for="loan_status">Loan Case</label>
                        <select id="loan_status" name="loan_status" onchange="toggleLoanFields()">
                            <option value="">Select Status</option>
                            <option value="no" <?php echo ($employee['loan_status'] == 'no') ? 'selected' : ''; ?>>No</option>
                            <option value="yes" <?php echo ($employee['loan_status'] == 'yes') ? 'selected' : ''; ?>>Yes</option>
                        </select>
                    </div>

                    <div class="conditional-field <?php echo ($employee['loan_status'] == 'yes') ? 'show' : ''; ?>" id="loan_fields">
                        <div class="form-group">
                            <label for="loan_file">Loan Agreement Document</label>
                            <input type="file" id="loan_file" name="loan_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <?php if (!empty($employee['loan_file'])): ?>
                                <small style="color: #666;">Current file: <?php echo htmlspecialchars($employee['loan_file']); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>

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
                        <?php if (!empty($employee['documents'])): 
                            $existing_docs = json_decode($employee['documents'], true);
                            if (is_array($existing_docs) && count($existing_docs) > 0):
                        ?>
                            <small style="color: #666; display: block; margin-top: 5px;">
                                <strong>Current documents:</strong> <?php echo count($existing_docs); ?> files uploaded
                            </small>
                        <?php endif; endif; ?>
                    </div>

                    <div class="preview-container" id="previewContainer"></div>

                    <!-- Leave Request -->
                    <div class="form-group">
                        <label for="leaveRequest">Request Leave?</label>
                        <select id="leaveRequest" name="leave_request" onchange="checkLeaveRequest()">
                            <option value="">--Select--</option>
                            <option value="yes" <?php echo ($employee['leave_request'] == 'yes') ? 'selected' : ''; ?>>Yes</option>
                            <option value="no" <?php echo ($employee['leave_request'] == 'no') ? 'selected' : ''; ?>>No</option>
                        </select>
                    </div>

                    <!-- Leave Document Scan Section -->
                    <div class="form-group conditional-field <?php echo (!empty($employee['leave_request']) && $employee['leave_request'] != '') ? 'show' : ''; ?>" id="leaveDocumentDiv">
                        <div class="form-group">
                            <label for="leave_document">Leave Application Document</label>
                            <input type="file" id="leave_document" name="leave_document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <?php if (!empty($employee['leave_document'])): ?>
                                <small style="color: #666;">Current file: <?php echo htmlspecialchars($employee['leave_document']); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($employee['email'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($employee['phone_number'] ?? ''); ?>">
                        </div>
                    </div>

                    <!-- Employment Details -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="position">Position *</label>
                            <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($employee['position'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="joinDate">Join Date</label>
                            <input type="date" id="joinDate" name="join_date" value="<?php echo $employee['join_date']; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="salary">Salary</label>
                            <input type="number" id="salary" name="salary" value="<?php echo $employee['salary']; ?>" step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="employmentType">Employment Type</label>
                            <select id="employmentType" name="employment_type">
                                <option value="full-time" <?php echo ($employee['employment_type'] == 'full-time') ? 'selected' : ''; ?>>Full-time</option>
                                <option value="part-time" <?php echo ($employee['employment_type'] == 'part-time') ? 'selected' : ''; ?>>Part-time</option>
                                <option value="contract" <?php echo ($employee['employment_type'] == 'contract') ? 'selected' : ''; ?>>Contract</option>
                                <option value="intern" <?php echo ($employee['employment_type'] == 'intern') ? 'selected' : ''; ?>>Intern</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="active" <?php echo ($employee['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="on-leave" <?php echo ($employee['status'] == 'on-leave') ? 'selected' : ''; ?>>On Leave</option>
                            <option value="inactive" <?php echo ($employee['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address"><?php echo htmlspecialchars($employee['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="emergencyContact">Emergency Contact</label>
                        <input type="text" id="emergencyContact" name="emergency_contact" value="<?php echo htmlspecialchars($employee['emergency_contact'] ?? ''); ?>">
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-save"></i> Update Employee
                        </button>
                        <button type="button" class="cancel-btn" onclick="window.location.href='hr-employees.html'">
                            <i class="fas fa-times"></i> Cancel
                        </button>
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

        // JavaScript functions for conditional fields
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

        function checkOtherDepartment() {
            const select = document.getElementById("allDepartments");
            const otherDiv = document.getElementById("otherDepartmentDiv");

            if(select.value === "other") {
                otherDiv.classList.add("show");
            } else {
                otherDiv.classList.remove("show");
            }
        }

        function toggleBankAccountField() {
            const bankSelect = document.getElementById("bankName");
            const bankAccountDiv = document.getElementById("bankAccountDiv");
            const bankAccountInput = document.getElementById("bankAccount");
            
            if (bankSelect.value !== "") {
                bankAccountDiv.classList.add("show");
                bankAccountInput.required = false; // Not required for edit
            } else {
                bankAccountDiv.classList.remove("show");
                bankAccountInput.required = false;
                bankAccountInput.value = "";
            }
        }

        function checkOtherJobLevel() {
            const select = document.getElementById("jobLevel");
            const otherDiv = document.getElementById("otherJobLevelDiv");

            if(select.value === "other") {
                otherDiv.classList.add("show");
            } else {
                otherDiv.classList.remove("show");
            }
        }

        function checkMaritalStatus() {
            const select = document.getElementById("maritalStatus");
            const otherDiv = document.getElementById("otherMaritalStatusDiv");

            if(select.value === "other") {
                otherDiv.classList.add("show");
            } else {
                otherDiv.classList.remove("show");
            }
        }

        function toggleWarrantyFields() {
            const status = document.getElementById("warranty_status").value;
            const fields = document.getElementById("warranty_fields");
            
            if (status === "yes") {
                fields.classList.add("show");
            } else {
                fields.classList.remove("show");
            }
        }

        function toggleCriminalFields() {
            const status = document.getElementById("criminal_status").value;
            const fields = document.getElementById("criminal_fields");
            const fileInput = document.getElementById("criminal_file");

            if (status === "yes") {
                fields.classList.add("show");
                fileInput.required = false; // Not required for edit
            } else {
                fields.classList.remove("show");
                fileInput.required = false;
                fileInput.value = "";
            }
        }

        function toggleLoanFields() {
            const status = document.getElementById("loan_status").value;
            const fields = document.getElementById("loan_fields");
            const fileInput = document.getElementById("loan_file");

            if (status === "yes") {
                fields.classList.add("show");
                fileInput.required = false; // Not required for edit
            } else {
                fields.classList.remove("show");
                fileInput.required = false;
                fileInput.value = "";
            }
        }

        function checkLeaveRequest() {
            const leaveSelect = document.getElementById("leaveRequest");
            const docDiv = document.getElementById("leaveDocumentDiv");
        
            if(leaveSelect.value === "yes" || leaveSelect.value === "no") {
                docDiv.classList.add("show");
            } else {
                docDiv.classList.remove("show");
            }
        }

        // Document handling functions
        function addDocument() {
            const input = document.getElementById("documents");
            const files = input.files;
            if (files.length === 0) return;

            const previewContainer = document.getElementById("previewContainer");
            
            // Clear previous previews
            previewContainer.innerHTML = '';

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const previewBox = document.createElement("div");
                previewBox.className = "preview-box";

                if (file.type.startsWith("image/")) {
                    const img = document.createElement("img");
                    img.src = URL.createObjectURL(file);
                    img.alt = "Document";
                    previewBox.appendChild(img);
                } else {
                    const pdfIcon = document.createElement("img");
                    pdfIcon.src = "https://upload.wikimedia.org/wikipedia/commons/8/87/PDF_file_icon.svg";
                    pdfIcon.alt = "Document";
                    previewBox.appendChild(pdfIcon);
                }

                const fileName = document.createElement("p");
                fileName.textContent = file.name;
                previewBox.appendChild(fileName);
                
                // Add file size
                const fileSize = document.createElement("small");
                fileSize.textContent = formatFileSize(file.size);
                fileSize.style.color = "#666";
                previewBox.appendChild(fileSize);
                
                previewContainer.appendChild(previewBox);
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

        // Region/Zone/Woreda data (simplified for edit form)
        const amharaData = {
            "North Wollo": {
               "Woldiya": [], "Lalibela": [], "Kobo": [], "Guba Lafto": [], "Meket": [], "Habru": [], "Wadla": [], "Delanta": [], "Tehuledere": [], "Raya Kobo": [], "Haik": [], "Kalu": []
            },
            "South Wollo": {
                "Dessie": [], "Kombolcha": [], "Mekdela": [], "Werebabo": [], "Kutaber": [], "Tenta": [], "Legambo": [], "Ambassel": [], "Kelala": [], "Wegde": [], "Sayint": [], "Tehuledere": []
            },
            "North Gondar": {
                "Gondar": [], "Debark": [], "Dabat": [], "Jan Amora": [], "Metemma": [], "Quara": [], "Alefa": [], "Wegera": [], "Lay Armachiho": [], "Chilga": [], "Tach Armachiho": [], "Tegede": []
            },
            "South Gondar": {
                "Debre Tabor": [], "Fogera": [], "Farta": [], "Dera": [], "Simada": [], "Libo Kemkem": [], "Lay Gayint": [], "Tach Gayint": [], "Ebinat": [], "Mirab Belesa": [], "Gondar Zuria": []
            },
            "West Gojjam": {
                "Finote Selam": [], "Bure": [], "Dangila": [], "Injibara": [], "Jawi": [], "Mecha": [], "Yilmana Densa": [], "Banja": [], "Dega Damot": []
            },
            "East Gojjam": {
                "Debre Markos": [], "Dejen": [], "Gozamen": [], "Machakel": [], "Aneded": [], "Bibugn": [], "Shebel Berenta": [], "Enebsie Sar Midir": [], "Debre Elias": [], "Dega Damot": [], "Awabel": [], "Hulet Ej Enese": []
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

        // Form validation
        document.getElementById('employeeForm').addEventListener('submit', function(e) {
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
            const email = document.querySelector('[name="email"]').value;
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                missingFields.push('Valid Email Address');
            }
            
            // Validate age (must be at least 18)
            const dob = document.querySelector('[name="date_of_birth"]').value;
            if (dob) {
                const birthDate = new Date(dob);
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
            
            // Validate salary if provided
            const salary = document.querySelector('[name="salary"]').value;
            if (salary && parseFloat(salary) <= 0) {
                missingFields.push('Valid Salary Amount');
            }
            
            if (missingFields.length > 0) {
                e.preventDefault();
                alert('Please fix the following required fields:\n\n• ' + missingFields.join('\n• '));
                return false;
            }
            
            // Show loading state
            const submitBtn = document.querySelector('.submit-btn');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating Employee...';
            
            return true;
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