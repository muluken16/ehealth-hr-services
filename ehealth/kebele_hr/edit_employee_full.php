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

    // Handle individual file uploads
    $file_fields = ['scan_file', 'criminal_file', 'fin_scan', 'loan_file', 'leave_document'];
    foreach ($file_fields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            $file_extension = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
            $file_name = $employee['employee_id'] . '_' . str_replace('_', '', $field) . '_' . time() . '.' . $file_extension;
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $upload_dir . $file_name)) {
                $$field = $file_name; // Dynamic variable assignment
            }
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
                            <label for="joinDate">Join Date *</label>
                            <input type="date" id="joinDate" name="join_date" value="<?php echo $employee['join_date']; ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="salary">Salary *</label>
                            <input type="number" id="salary" name="salary" value="<?php echo $employee['salary']; ?>" required step="0.01">
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