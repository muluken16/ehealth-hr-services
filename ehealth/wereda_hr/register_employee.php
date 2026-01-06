<?php
session_start();
require_once '../db.php';

// Set default session for demo
if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'Wereda HR Manager';
    $_SESSION['role'] = 'wereda_hr';
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Generate unique employee ID
        $year = date('Y');
        $random = rand(1000, 9999);
        $employee_id = "WRD-{$year}-{$random}";
        
        // Basic Information
        $first_name = trim($_POST['first_name']);
        $middle_name = trim($_POST['middle_name'] ?? '');
        $last_name = trim($_POST['last_name']);
        $gender = $_POST['gender'];
        $date_of_birth = $_POST['date_of_birth'];
        $email = $_POST['email'];
        $phone_number = $_POST['phone_number'] ?? '';
        
        // Position Information
        $department_assigned = $_POST['department_assigned'] ?? '';
        $position = $_POST['position'];
        $job_level = $_POST['job_level'] ?? '';
        $employment_type = $_POST['employment_type'] ?? 'full-time';
        $salary = $_POST['salary'] ?? 0;
        $join_date = $_POST['join_date'] ?? date('Y-m-d');
        
        // Address Information
        $region = $_POST['region'] ?? '';
        $zone = $_POST['zone'] ?? '';
        $woreda = $_POST['woreda'] ?? '';
        $kebele = $_POST['kebele'] ?? '';
        $address = $_POST['address'] ?? '';
        $emergency_contact = $_POST['emergency_contact'] ?? '';
        
        // Education
        $education_level = $_POST['education_level'] ?? '';
        
        // Created by
        $created_by = $_SESSION['user_name'];
        
        // Insert into database
        $sql = "INSERT INTO employees (
            employee_id, first_name, middle_name, last_name, gender, date_of_birth,
            email, phone_number, department_assigned, position, job_level,
            employment_type, salary, join_date, region, zone, woreda, kebele,
            address, emergency_contact, education_level, status, created_by, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssssssdssssssss",
            $employee_id, $first_name, $middle_name, $last_name, $gender, $date_of_birth,
            $email, $phone_number, $department_assigned, $position, $job_level,
            $employment_type, $salary, $join_date, $region, $zone, $woreda, $kebele,
            $address, $emergency_contact, $education_level, $created_by
        );
        
        if ($stmt->execute()) {
            $success_message = "Employee {$first_name} {$last_name} successfully registered!";
            // Redirect after 2 seconds
            header("refresh:2;url=wereda_hr_employee.php");
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Employee | Wereda HR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        /* Modern Multi-Step Form Styling */
        .registration-container {
            max-width: 950px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .progress-bar-container {
            background: white;
            border-radius: 15px;
            padding: 30px 35px;
            margin-bottom: 30px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.06);
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 15px;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 22px;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #e2e8f0 0%, #cbd5e1 100%);
            z-index: 0;
        }

        .progress-line {
            position: absolute;
            top: 22px;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary) 0%, #1a5270 100%);
            transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1;
            box-shadow: 0 2px 10px rgba(26, 74, 95, 0.3);
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }

        .step-circle {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: white;
            border: 3px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #94a3b8;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .step.active .step-circle {
            border-color: var(--primary);
            background: linear-gradient(135deg, var(--primary) 0%, #1a5270 100%);
            color: white;
            transform: scale(1.15);
            box-shadow: 0 6px 20px rgba(26, 74, 95, 0.35);
        }

        .step.completed .step-circle {
            border-color: #10b981;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .step.completed .step-circle::before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }

        .step.completed .step-circle span {
            display: none;
        }

        .step-label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
        }

        .step.active .step-label {
            color: var(--primary);
            font-weight: 700;
        }

        /* Form Card */
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 45px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.06);
            margin-bottom: 30px;
        }

        .form-step {
            display: none;
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-step.active {
            display: block;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(25px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-step-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 12px;
        }

        .form-step-icon {
            width: 55px;
            height: 55px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary) 0%, #1a5270 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 15px rgba(26, 74, 95, 0.25);
        }

        .form-step-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .form-step-description {
            color: #64748b;
            margin-bottom: 35px;
            margin-left: 70px;
            font-size: 0.95rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 22px;
            margin-bottom: 30px;
        }

        .form-grid.single-column {
            grid-template-columns: 1fr;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 9px;
            font-size: 0.95rem;
        }

        .form-group label .required {
            color: #ef4444;
            margin-left: 3px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 13px 17px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f8fafc;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(26, 74, 95, 0.08);
            transform: translateY(-1px);
        }

        .form-group input.valid {
            border-color: #10b981;
            background: #f0fdf4;
        }

        .form-group input.invalid {
            border-color: #ef4444;
            background: #fef2f2;
        }

        .input-hint {
            font-size: 0.8rem;
            color: #94a3b8;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .input-hint i {
            font-size: 0.7rem;
        }

        /* Form Navigation */
        .form-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 35px;
            padding-top: 30px;
            border-top: 2px solid #f1f5f9;
        }

        .btn {
            padding: 13px 32px;
            border-radius: 11px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #1a5270 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(26, 74, 95, 0.25);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(26, 74, 95, 0.35);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #64748b;
            border: 2px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
            border-color: #cbd5e1;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.35);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Alert Messages */
        .alert {
            padding: 18px 22px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 14px;
            animation: slideDown 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            border: 2px solid #10b981;
        }

        .alert-error {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border: 2px solid #ef4444;
        }

        .review-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .review-section {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 22px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
        }

        .review-section h3 {
            color: var(--primary);
            margin-bottom: 16px;
            font-size: 1.15rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .review-section p {
            margin: 10px 0;
            color: #475569;
            font-size: 0.9rem;
        }

        .review-section strong {
            color: #1e293b;
            min-width: 140px;
            display: inline-block;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-grid, .review-grid {
                grid-template-columns: 1fr;
            }

            .form-card {
                padding: 28px;
            }

            .form-step-description {
                margin-left: 0;
            }

            .step-label {
                font-size: 0.72rem;
            }
        }
    </style>
</head>
<body>
    <div class="hr-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <?php 
            $page_title = "Register New Employee";
            include 'navbar.php'; 
        ?>

        <!-- Content -->
        <div class="content">
            <div class="registration-container">
                <!-- Success/Error Messages -->
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle" style="font-size: 1.4rem;"></i>
                        <div>
                            <strong>Success!</strong> <?php echo $success_message; ?>
                            <br><small>Redirecting to employee list...</small>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle" style="font-size: 1.4rem;"></i>
                        <div>
                            <strong>Error!</strong> <?php echo $error_message; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Progress Bar -->
                <div class="progress-bar-container">
                    <div class="progress-steps">
                        <div class="progress-line" id="progressLine"></div>
                        <div class="step active" data-step="1">
                            <div class="step-circle"><span>1</span></div>
                            <div class="step-label">Personal Info</div>
                        </div>
                        <div class="step" data-step="2">
                            <div class="step-circle"><span>2</span></div>
                            <div class="step-label">Position</div>
                        </div>
                        <div class="step" data-step="3">
                            <div class="step-circle"><span>3</span></div>
                            <div class="step-label">Location</div>
                        </div>
                        <div class="step" data-step="4">
                            <div class="step-circle"><span>4</span></div>
                            <div class="step-label">Review</div>
                        </div>
                    </div>
                </div>

                <!-- Registration Form -->
                <form id="registrationForm" method="POST" action="">
                    <div class="form-card">
                        <!-- Step 1: Personal Information -->
                        <div class="form-step active" data-step="1">
                            <div class="form-step-header">
                                <div class="form-step-icon">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <h2 class="form-step-title">Personal Information</h2>
                            </div>
                            <p class="form-step-description">Enter the employee's basic personal details</p>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label>First Name <span class="required">*</span></label>
                                    <input type="text" name="first_name" required>
                                </div>
                                <div class="form-group">
                                    <label>Middle Name</label>
                                    <input type="text" name="middle_name">
                                </div>
                                <div class="form-group">
                                    <label>Last Name <span class="required">*</span></label>
                                    <input type="text" name="last_name" required>
                                </div>
                                <div class="form-group">
                                    <label>Gender <span class="required">*</span></label>
                                    <select name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Date of Birth <span class="required">*</span></label>
                                    <input type="date" name="date_of_birth" required>
                                    <span class="input-hint"><i class="fas fa-info-circle"></i> Must be at least 18 years old</span>
                                </div>
                                <div class="form-group">
                                    <label>Email <span class="required">*</span></label>
                                    <input type="email" name="email" required>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="tel" name="phone_number" placeholder="+251 9XX XXX XXX">
                                </div>
                                <div class="form-group">
                                    <label>Education Level</label>
                                    <select name="education_level">
                                        <option value="">Select Education Level</option>
                                        <option value="primary">Primary School</option>
                                        <option value="secondary">Secondary School</option>
                                        <option value="diploma">Diploma</option>
                                        <option value="bachelor">Bachelor's Degree</option>
                                        <option value="master">Master's Degree</option>
                                        <option value="phd">PhD</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Position Information -->
                        <div class="form-step" data-step="2">
                            <div class="form-step-header">
                                <div class="form-step-icon">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                                <h2 class="form-step-title">Position Information</h2>
                            </div>
                            <p class="form-step-description">Define the employee's role and employment terms</p>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Position <span class="required">*</span></label>
                                    <input type="text" name="position" placeholder="e.g., Senior Nurse, HR Manager" required>
                                </div>
                                <div class="form-group">
                                    <label>Department</label>
                                    <select name="department_assigned">
                                        <option value="">Select Department</option>
                                        <option value="general_medicine">General Medicine</option>
                                        <option value="pediatrics">Pediatrics</option>
                                        <option value="obstetrics_gynecology">Obstetrics & Gynecology</option>
                                        <option value="surgery">Surgery</option>
                                        <option value="emergency">Emergency / ER</option>
                                        <option value="intensive_care">Intensive Care Unit (ICU)</option>
                                        <option value="pharmacy">Pharmacy</option>
                                        <option value="laboratory">Laboratory</option>
                                        <option value="radiology">Radiology / Imaging</option>
                                        <option value="hospital_administration">Administration</option>
                                        <option value="human_resources">Human Resources</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Job Level</label>
                                    <select name="job_level">
                                        <option value="">Select Job Level</option>
                                        <option value="entry">Entry Level</option>
                                        <option value="junior">Junior</option>
                                        <option value="mid">Mid-Level</option>
                                        <option value="senior">Senior</option>
                                        <option value="lead">Lead</option>
                                        <option value="manager">Manager</option>
                                        <option value="director">Director</option>
                                        <option value="executive">Executive</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Employment Type</label>
                                    <select name="employment_type">
                                        <option value="full-time">Full-Time</option>
                                        <option value="part-time">Part-Time</option>
                                        <option value="contract">Contract</option>
                                        <option value="temporary">Temporary</option>
                                        <option value="internship">Internship</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Monthly Salary (ETB)</label>
                                    <input type="number" name="salary" placeholder="e.g., 25000" step="100">
                                    <span class="input-hint"><i class="fas fa-coins"></i> Enter monthly salary amount</span>
                                </div>
                                <div class="form-group">
                                    <label>Join Date</label>
                                    <input type="date" name="join_date" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Location & Contact -->
                        <div class="form-step" data-step="3">
                            <div class="form-step-header">
                                <div class="form-step-icon">
                                    <i class="fas fa-map-marked-alt"></i>
                                </div>
                                <h2 class="form-step-title">Location & Contact</h2>
                            </div>
                            <p class="form-step-description">Enter the employee's address and emergency contact details</p>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Region</label>
                                    <select name="region">
                                        <option value="">Select Region</option>
                                        <option value="addis_ababa">Addis Ababa</option>
                                        <option value="amhara">Amhara</option>
                                        <option value="oromia">Oromia</option>
                                        <option value="tigray">Tigray</option>
                                        <option value="snnpr">SNNPR</option>
                                        <option value="afar">Afar</option>
                                        <option value="somali">Somali</option>
                                        <option value="gambela">Gambela</option>
                                        <option value="benishangul">Benishangul-Gumuz</option>
                                        <option value="harari">Harari</option>
                                        <option value="dire_dawa">Dire Dawa</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Zone</label>
                                    <input type="text" name="zone" placeholder="Enter Zone">
                                </div>
                                <div class="form-group">
                                    <label>Woreda</label>
                                    <input type="text" name="woreda" placeholder="Enter Woreda">
                                </div>
                                <div class="form-group">
                                    <label>Kebele</label>
                                    <input type="text" name="kebele" placeholder="Enter Kebele">
                                </div>
                            </div>

                            <div class="form-grid single-column">
                                <div class="form-group">
                                    <label>Full Address</label>
                                    <textarea name="address" rows="3" placeholder="Enter complete address details (street, house number, etc.)"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Emergency Contact</label>
                                    <input type="text" name="emergency_contact" placeholder="Name and phone number of emergency contact">
                                    <span class="input-hint"><i class="fas fa-phone-alt"></i> Person to contact in case of emergency</span>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Review & Submit -->
                        <div class="form-step" data-step="4">
                            <div class="form-step-header">
                                <div class="form-step-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h2 class="form-step-title">Review & Submit</h2>
                            </div>
                            <p class="form-step-description">Please review all information before submitting</p>

                            <div id="reviewContent" class="review-grid">
                                <p style="grid-column: 1/-1; text-align: center; color: #64748b; padding: 30px;">
                                    <i class="fas fa-info-circle" style="font-size: 2.5rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                                    Review summary will appear here
                                </p>
                            </div>

                            <div style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border: 2px solid #fb923c; border-radius: 12px; padding: 18px; margin-top: 25px;">
                                <i class="fas fa-exclamation-triangle" style="color: #ea580c; margin-right: 10px; font-size: 1.1rem;"></i>
                                <strong style="color: #9a3412;">Important:</strong>
                                <span style="color: #9a3412;">Please verify all information is correct before submitting. This will create a new employee record in the system.</span>
                            </div>
                        </div>

                        <!-- Form Navigation -->
                        <div class="form-navigation">
                            <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">
                                <i class="fas fa-arrow-left"></i>
                                Previous
                            </button>
                            <div></div>
                            <button type="button" class="btn btn-primary" id="nextBtn">
                                Next Step
                                <i class="fas fa-arrow-right"></i>
                            </button>
                            <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                                <i class="fas fa-paper-plane"></i>
                                Submit Registration
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="scripts.js"></script>
    <script>
        let currentStep = 1;
        const totalSteps = 4;

        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        const progressLine = document.getElementById('progressLine');

        function showStep(step) {
            document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.step').forEach(s => s.classList.remove('active', 'completed'));

            document.querySelector(`.form-step[data-step="${step}"]`).classList.add('active');
            document.querySelector(`.step[data-step="${step}"]`).classList.add('active');

            for (let i = 1; i < step; i++) {
                document.querySelector(`.step[data-step="${i}"]`).classList.add('completed');
            }

            const progress = ((step - 1) / (totalSteps - 1)) * 100;
            progressLine.style.width = progress + '%';

            prevBtn.style.display = step === 1 ? 'none' : 'inline-flex';
            nextBtn.style.display = step === totalSteps ? 'none' : 'inline-flex';
            submitBtn.style.display = step === totalSteps ? 'inline-flex' : 'none';

            if (step === 4) {
                populateReview();
            }
        }

        function validateStep(step) {
            const currentFormStep = document.querySelector(`.form-step[data-step="${step}"]`);
            const requiredInputs = currentFormStep.querySelectorAll('[required]');
            let isValid = true;

            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('invalid');
                } else {
                    input.classList.remove('invalid');
                    input.classList.add('valid');
                }
            });

            if (!isValid) {
                alert('Please fill in all required fields before proceeding.');
            }

            return isValid;
        }

        function populateReview() {
            const formData = new FormData(document.getElementById('registrationForm'));
            let html = '';

            html += '<div class="review-section">';
            html += '<h3><i class="fas fa-user"></i> Personal Information</h3>';
            html += `<p><strong>Full Name:</strong> ${formData.get('first_name')} ${formData.get('middle_name')} ${formData.get('last_name')}</p>`;
            html += `<p><strong>Gender:</strong> ${formData.get('gender')}</p>`;
            html += `<p><strong>Date of Birth:</strong> ${formData.get('date_of_birth')}</p>`;
            html += `<p><strong>Email:</strong> ${formData.get('email')}</p>`;
            html += `<p><strong>Phone:</strong> ${formData.get('phone_number') || 'Not provided'}</p>`;
            html += `<p><strong>Education:</strong> ${formData.get('education_level') || 'Not specified'}</p>`;
            html += '</div>';

            html += '<div class="review-section">';
            html += '<h3><i class="fas fa-briefcase"></i> Position Details</h3>';
            html += `<p><strong>Position:</strong> ${formData.get('position')}</p>`;
            html += `<p><strong>Department:</strong> ${formData.get('department_assigned') || 'Not specified'}</p>`;
            html += `<p><strong>Job Level:</strong> ${formData.get('job_level') || 'Not specified'}</p>`;
            html += `<p><strong>Employment Type:</strong> ${formData.get('employment_type')}</p>`;
            html += `<p><strong>Monthly Salary:</strong> ${formData.get('salary') ? formData.get('salary') + ' ETB' : 'Not specified'}</p>`;
            html += `<p><strong>Join Date:</strong> ${formData.get('join_date')}</p>`;
            html += '</div>';

            html += '<div class="review-section" style="grid-column: 1/-1;">';
            html += '<h3><i class="fas fa-map-marker-alt"></i> Location & Contact</h3>';
            html += '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">';
            html += `<p><strong>Region:</strong> ${formData.get('region') || 'Not specified'}</p>`;
            html += `<p><strong>Zone:</strong> ${formData.get('zone') || 'Not specified'}</p>`;
            html += `<p><strong>Woreda:</strong> ${formData.get('woreda') || 'Not specified'}</p>`;
            html += `<p><strong>Kebele:</strong> ${formData.get('kebele') || 'Not specified'}</p>`;
            html += `<p style="grid-column: 1/-1;"><strong>Full Address:</strong> ${formData.get('address') || 'Not provided'}</p>`;
            html += `<p style="grid-column: 1/-1;"><strong>Emergency Contact:</strong> ${formData.get('emergency_contact') || 'Not provided'}</p>`;
            html += '</div></div>';

            document.getElementById('reviewContent').innerHTML = html;
        }

        nextBtn.addEventListener('click', () => {
            if (validateStep(currentStep)) {
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                }
            }
        });

        prevBtn.addEventListener('click', () => {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });

        document.querySelectorAll('input[required], select[required]').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim()) {
                    this.classList.remove('invalid');
                    this.classList.add('valid');
                } else {
                    this.classList.remove('valid');
                    this.classList.add('invalid');
                }
            });
        });

        showStep(1);
    </script>
</body>
</html>
