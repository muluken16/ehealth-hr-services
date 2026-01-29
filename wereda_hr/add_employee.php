<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure role is set for the demo (Dev Fallback)
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'wereda_hr';
    $_SESSION['user_name'] = 'Wereda HR Officer';
    $_SESSION['woreda'] = 'Woreda 1';
    $_SESSION['user_id'] = 'DEMO_USER';
}

if ($_SESSION['role'] != 'wereda_hr') {
    header('Location: ../index.html');
    exit();
}

$page_title = 'Add New Employee';
require_once '../db.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $marital_status = $_POST['marital_status'] ?? '';
    $religion = $_POST['religion'] ?? '';
    $citizenship = $_POST['citizenship'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';

    // Employment details
    $position = $_POST['position'] ?? '';
    $department_assigned = $_POST['department_assigned'] ?? '';
    $join_date = $_POST['join_date'] ?? '';
    $employment_type = $_POST['employment_type'] ?? '';
    $salary = $_POST['salary'] ?? '';
    $working_kebele = $_POST['working_kebele'] ?? '';

    // Education
    $education_level = $_POST['education_level'] ?? '';
    $university = $_POST['university'] ?? '';
    $field_of_study = $_POST['field_of_study'] ?? '';

    // Banking
    $bank_name = $_POST['bank_name'] ?? '';
    $bank_account = $_POST['bank_account'] ?? '';

    // Emergency contact
    $emergency_name = $_POST['emergency_name'] ?? '';
    $emergency_phone = $_POST['emergency_phone'] ?? '';
    $emergency_relation = $_POST['emergency_relation'] ?? '';

    // Validation
    if (empty($first_name))
        $errors[] = 'First name is required';
    if (empty($last_name))
        $errors[] = 'Last name is required';
    if (empty($gender))
        $errors[] = 'Gender is required';
    if (empty($phone))
        $errors[] = 'Phone is required';
    if (empty($position))
        $errors[] = 'Position is required';
    if (empty($join_date))
        $errors[] = 'Join date is required';

    if (empty($errors)) {
        try {
            $conn = getDBConnection();

            // Generate employee ID
            $emp_prefix = 'EMP-' . date('ym');
            $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM employees WHERE employee_id LIKE ?");
            $stmt->bind_param("s", $emp_prefix . '%');
            $stmt->execute();
            $count = $stmt->get_result()->fetch_assoc()['cnt'] + 1;
            $employee_id = $emp_prefix . strtoupper(dechex($count));

            $sql = "INSERT INTO employees (
                employee_id, first_name, middle_name, last_name, gender, date_of_birth,
                marital_status, religion, citizenship, phone, email, address,
                position, department_assigned, join_date, employment_type, salary, working_kebele,
                education_level, university, field_of_study,
                bank_name, bank_account,
                emergency_name, emergency_phone, emergency_relation,
                status, woreda, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, NOW())";

            $woreda = $_SESSION['woreda'] ?? 'Woreda 1';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssssssssssssssssssssssss",
                $employee_id,
                $first_name,
                $middle_name,
                $last_name,
                $gender,
                $date_of_birth,
                $marital_status,
                $religion,
                $citizenship,
                $phone,
                $email,
                $address,
                $position,
                $department_assigned,
                $join_date,
                $employment_type,
                $salary,
                $working_kebele,
                $education_level,
                $university,
                $field_of_study,
                $bank_name,
                $bank_account,
                $emergency_name,
                $emergency_phone,
                $emergency_relation,
                $woreda
            );

            if ($stmt->execute()) {
                $success = true;
                $_SESSION['success_message'] = "Employee added successfully! ID: $employee_id";
                header('Location: wereda_hr_employee.php');
                exit();
            } else {
                $errors[] = 'Failed to add employee: ' . $stmt->error;
            }
        } catch (Exception $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
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
    <script src="locations.js"></script>
    <style>
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--primary);
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1.5px solid #edf2f7;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s;
            background: #f8fafc;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            background: white;
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 74, 95, 0.1);
        }

        .form-section {
            background: white;
            padding: 25px;
            border-radius: 16px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            border: 1px solid #f1f5f9;
        }

        .form-section h3 {
            margin-bottom: 20px;
            color: var(--primary);
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 10px;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary), #2a6e8c);
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 74, 95, 0.3);
        }

        .btn-cancel {
            background: white;
            color: #64748b;
            border: 1.5px solid #e2e8f0;
            padding: 14px 28px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-cancel:hover {
            background: #f8fafc;
        }

        .error-msg {
            background: #fef2f2;
            color: #dc2626;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
        }

        .success-msg {
            background: #ecfdf5;
            color: #059669;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #a7f3d0;
        }

        .required::after {
            content: ' *';
            color: #dc2626;
        }
    </style>
</head>

<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>
        <?php include 'navbar.php'; ?>
        <div class="hr-dashboard">
            <div class="page-header">
                <h1><i class="fas fa-user-plus"></i> Add New Employee</h1>
                <a href="wereda_hr_employee.php" class="btn-cancel"><i class="fas fa-arrow-left"></i> Back to
                    Employees</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-msg">
                    <strong><i class="fas fa-exclamation-circle"></i> Please fix these errors:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" id="employeeForm">
                <!-- Personal Information -->
                <div class="form-section">
                    <h3><i class="fas fa-user"></i> Personal Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="required">First Name</label>
                            <input type="text" name="first_name"
                                value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name"
                                value="<?php echo htmlspecialchars($_POST['middle_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="required">Last Name</label>
                            <input type="text" name="last_name"
                                value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="required">Gender</label>
                            <select name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male" <?php echo ($_POST['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>
                                    Male</option>
                                <option value="female" <?php echo ($_POST['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="date_of_birth"
                                value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Marital Status</label>
                            <select name="marital_status">
                                <option value="">Select Status</option>
                                <option value="single" <?php echo ($_POST['marital_status'] ?? '') === 'single' ? 'selected' : ''; ?>>Single</option>
                                <option value="married" <?php echo ($_POST['marital_status'] ?? '') === 'married' ? 'selected' : ''; ?>>Married</option>
                                <option value="divorced" <?php echo ($_POST['marital_status'] ?? '') === 'divorced' ? 'selected' : ''; ?>>Divorced</option>
                                <option value="widowed" <?php echo ($_POST['marital_status'] ?? '') === 'widowed' ? 'selected' : ''; ?>>Widowed</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Religion</label>
                            <input type="text" name="religion"
                                value="<?php echo htmlspecialchars($_POST['religion'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Citizenship</label>
                            <input type="text" name="citizenship"
                                value="<?php echo htmlspecialchars($_POST['citizenship'] ?? 'Ethiopian'); ?>">
                        </div>
                        <div class="form-group">
                            <label class="required">Phone</label>
                            <input type="tel" name="phone"
                                value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label>Address</label>
                            <textarea name="address"
                                rows="2"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Employment Details -->
                <div class="form-section">
                    <h3><i class="fas fa-briefcase"></i> Employment Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="required">Position</label>
                            <input type="text" name="position"
                                value="<?php echo htmlspecialchars($_POST['position'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Department</label>
                            <select name="department_assigned">
                                <option value="">Select Department</option>
                                <option value="medical" <?php echo ($_POST['department_assigned'] ?? '') === 'medical' ? 'selected' : ''; ?>>Medical</option>
                                <option value="admin" <?php echo ($_POST['department_assigned'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="finance" <?php echo ($_POST['department_assigned'] ?? '') === 'finance' ? 'selected' : ''; ?>>Finance</option>
                                <option value="support" <?php echo ($_POST['department_assigned'] ?? '') === 'support' ? 'selected' : ''; ?>>Support</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="required">Join Date</label>
                            <input type="date" name="join_date"
                                value="<?php echo htmlspecialchars($_POST['join_date'] ?? date('Y-m-d')); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Employment Type</label>
                            <select name="employment_type">
                                <option value="full-time">Full-time</option>
                                <option value="part-time" <?php echo ($_POST['employment_type'] ?? '') === 'part-time' ? 'selected' : ''; ?>>Part-time</option>
                                <option value="contract" <?php echo ($_POST['employment_type'] ?? '') === 'contract' ? 'selected' : ''; ?>>Contract</option>
                                <option value="temporary" <?php echo ($_POST['employment_type'] ?? '') === 'temporary' ? 'selected' : ''; ?>>Temporary</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Salary (ETB)</label>
                            <input type="number" name="salary"
                                value="<?php echo htmlspecialchars($_POST['salary'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Working Kebele</label>
                            <input type="text" name="working_kebele"
                                value="<?php echo htmlspecialchars($_POST['working_kebele'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Education -->
                <div class="form-section">
                    <h3><i class="fas fa-graduation-cap"></i> Education</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Education Level</label>
                            <select name="education_level">
                                <option value="">Select Level</option>
                                <option value="high-school">High School</option>
                                <option value="certificate">Certificate</option>
                                <option value="diploma">Diploma</option>
                                <option value="bachelor" <?php echo ($_POST['education_level'] ?? '') === 'bachelor' ? 'selected' : ''; ?>>Bachelor's Degree</option>
                                <option value="master" <?php echo ($_POST['education_level'] ?? '') === 'master' ? 'selected' : ''; ?>>Master's Degree</option>
                                <option value="phd" <?php echo ($_POST['education_level'] ?? '') === 'phd' ? 'selected' : ''; ?>>PhD</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>University/College</label>
                            <input type="text" name="university"
                                value="<?php echo htmlspecialchars($_POST['university'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Field of Study</label>
                            <input type="text" name="field_of_study"
                                value="<?php echo htmlspecialchars($_POST['field_of_study'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Banking Information -->
                <div class="form-section">
                    <h3><i class="fas fa-university"></i> Banking Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Bank Name</label>
                            <input type="text" name="bank_name"
                                value="<?php echo htmlspecialchars($_POST['bank_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Account Number</label>
                            <input type="text" name="bank_account"
                                value="<?php echo htmlspecialchars($_POST['bank_account'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="form-section">
                    <h3><i class="fas fa-phone-alt"></i> Emergency Contact</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Contact Name</label>
                            <input type="text" name="emergency_name"
                                value="<?php echo htmlspecialchars($_POST['emergency_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Contact Phone</label>
                            <input type="tel" name="emergency_phone"
                                value="<?php echo htmlspecialchars($_POST['emergency_phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Relationship</label>
                            <input type="text" name="emergency_relation"
                                value="<?php echo htmlspecialchars($_POST['emergency_relation'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 15px; justify-content: flex-end;">
                    <a href="wereda_hr_employee.php" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Save Employee</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>