<?php
session_start();
require_once '../db.php';

// Set default session for demo
if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'Kebele HR Officer';
    $_SESSION['role'] = 'kebele_hr';
    $_SESSION['kebele'] = 'Kebele 1';
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn = getDBConnection();
        
        // Generate unique employee ID
        $year = date('Y');
        $random = rand(1000, 9999);
        $employee_id = "KBL-{$year}-{$random}";
        
        // Collect all form data
        $data = [
            'employee_id' => $employee_id,
            'first_name' => trim($_POST['first_name']),
            'middle_name' => trim($_POST['middle_name'] ?? ''),
            'last_name' => trim($_POST['last_name']),
            'gender' => $_POST['gender'],
            'date_of_birth' => $_POST['date_of_birth'],
            'email' => $_POST['email'],
            'phone_number' => $_POST['phone_number'] ?? '',
            'department_assigned' => $_POST['department_assigned'] ?? '',
            'position' => $_POST['position'],
            'job_level' => $_POST['job_level'] ?? '',
            'employment_type' => $_POST['employment_type'] ?? 'full-time',
            'salary' => $_POST['salary'] ?? 0,
            'join_date' => $_POST['join_date'] ?? date('Y-m-d'),
            'region' => $_POST['region'] ?? '',
            'zone' => $_POST['zone'] ?? '',
            'woreda' => $_POST['woreda'] ?? '',
            'kebele' => $_POST['kebele'] ?? '',
            'address' => $_POST['address'] ?? '',
            'emergency_contact' => $_POST['emergency_contact'] ?? '',
            'education_level' => $_POST['education_level'] ?? '',
            'status' => 'active',
            'created_by' => $_SESSION['user_name'],
            'working_kebele' => $_SESSION['kebele'] ?? 'Kebele 1'
        ];
        
        // Insert into database
        $sql = "INSERT INTO employees (
            employee_id, first_name, middle_name, last_name, gender, date_of_birth,
            email, phone_number, department_assigned, position, job_level,
            employment_type, salary, join_date, region, zone, woreda, kebele,
            address, emergency_contact, education_level, status, created_by, 
            working_kebele, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssssssdsssssssssss",
            $data['employee_id'], $data['first_name'], $data['middle_name'], 
            $data['last_name'], $data['gender'], $data['date_of_birth'],
            $data['email'], $data['phone_number'], $data['department_assigned'], 
            $data['position'], $data['job_level'], $data['employment_type'], 
            $data['salary'], $data['join_date'], $data['region'], $data['zone'], 
            $data['woreda'], $data['kebele'], $data['address'], 
            $data['emergency_contact'], $data['education_level'], $data['status'], 
            $data['created_by'], $data['working_kebele']
        );
        
        if ($stmt->execute()) {
            $success_message = "Employee {$data['first_name']} {$data['last_name']} successfully registered with ID: {$employee_id}";
            // Clear form data from session
            unset($_SESSION['form_data']);
        } else {
            $error_message = "Database Error: " . $stmt->error;
        }
        
        $stmt->close();
        $conn->close();
        
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
    <title>Modern Employee Registration | Kebele HR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --danger-gradient: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --glass-bg: rgba(255, 255, 255, 0.25);
            --glass-border: rgba(255, 255, 255, 0.18);
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .registration-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 0 20px;
        }

        /* Glass Morphism Card */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            padding: 40px;
            margin-bottom: 20px;
        }

        /* Modern Progress Bar */
        .progress-container {
            margin-bottom: 40px;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 20px;
        }

        .progress-line {
            position: absolute;
            top: 25px;
            left: 0;
            height: 4px;
            background: var(--primary-gradient);
            border-radius: 2px;
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.4);
        }

        .progress-bg {
            position: absolute;
            top: 25px;
            left: 0;
            right: 0;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            border: 3px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 12px;
        }

        .step.active .step-circle {
            background: var(--primary-gradient);
            border-color: white;
            color: white;
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .step.completed .step-circle {
            background: var(--success-gradient);
            border-color: white;
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
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 600;
            text-align: center;
        }

        .step.active .step-label {
            color: white;
            font-weight: 700;
        }

        /* Form Styling */
        .form-step {
            display: none;
            animation: slideInRight 0.5s ease-out;
        }

        .form-step.active {
            display: block;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .step-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .step-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .step-title {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin: 0 0 10px 0;
        }

        .step-description {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: white;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .required {
            color: #ff6b6b;
        }

        .form-control {
            padding: 15px 20px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-control:focus {
            outline: none;
            border-color: white;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .form-control.valid {
            border-color: #38ef7d;
            background: rgba(56, 239, 125, 0.1);
        }

        .form-control.invalid {
            border-color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .input-hint {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Navigation Buttons */
        .form-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn {
            padding: 15px 30px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .btn-success {
            background: var(--success-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(17, 153, 142, 0.6);
        }

        /* Alert Messages */
        .alert {
            padding: 20px 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideDown 0.5s ease-out;
            backdrop-filter: blur(20px);
        }

        .alert-success {
            background: rgba(56, 239, 125, 0.2);
            border: 2px solid rgba(56, 239, 125, 0.4);
            color: white;
        }

        .alert-error {
            background: rgba(255, 107, 107, 0.2);
            border: 2px solid rgba(255, 107, 107, 0.4);
            color: white;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Review Section */
        .review-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .review-section {
            background: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .review-section h3 {
            color: white;
            margin-bottom: 15px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .review-section p {
            margin: 8px 0;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
        }

        .review-section strong {
            color: white;
            min-width: 120px;
            display: inline-block;
        }

        /* Loading Animation */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            backdrop-filter: blur(5px);
        }

        .loading-content {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            border: 1px solid var(--glass-border);
        }

        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Auto-save Indicator */
        .autosave-indicator {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--success-gradient);
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 0.9rem;
            display: none;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);
            animation: slideInRight 0.4s ease;
            z-index: 1000;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .glass-card {
                padding: 25px;
                margin: 10px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .step-title {
                font-size: 1.5rem;
            }

            .step-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }

            .form-navigation {
                flex-direction: column;
                gap: 15px;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Dark mode select styling */
        select.form-control {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px 12px;
            padding-right: 40px;
            appearance: none;
        }

        select.form-control option {
            background: #1e293b;
            color: white;
        }
    </style>
</head>
<body>
    <div class="hr-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <?php 
                $page_title = "Modern Employee Registration";
                include 'navbar.php'; 
            ?>

            <div class="content">
                <div class="registration-container">
                    <!-- Success/Error Messages -->
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle" style="font-size: 1.5rem;"></i>
                            <div>
                                <strong>Registration Successful!</strong><br>
                                <?php echo $success_message; ?>
                                <br><small>You can now view the employee in the employee directory.</small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle" style="font-size: 1.5rem;"></i>
                            <div>
                                <strong>Registration Failed!</strong><br>
                                <?php echo $error_message; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Progress Bar -->
                    <div class="glass-card progress-container">
                        <div class="progress-steps">
                            <div class="progress-bg"></div>
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
                        <div class="glass-card">
                            <!-- Step 1: Personal Information -->
                            <div class="form-step active" data-step="1">
                                <div class="step-header">
                                    <div class="step-icon">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <h2 class="step-title">Personal Information</h2>
                                    <p class="step-description">Let's start with the employee's basic details</p>
                                </div>

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>First Name <span class="required">*</span></label>
                                        <input type="text" name="first_name" class="form-control" required placeholder="Enter first name">
                                    </div>
                                    <div class="form-group">
                                        <label>Middle Name</label>
                                        <input type="text" name="middle_name" class="form-control" placeholder="Enter middle name">
                                    </div>
                                    <div class="form-group">
                                        <label>Last Name <span class="required">*</span></label>
                                        <input type="text" name="last_name" class="form-control" required placeholder="Enter last name">
                                    </div>
                                    <div class="form-group">
                                        <label>Gender <span class="required">*</span></label>
                                        <select name="gender" class="form-control" required>
                                            <option value="">Select Gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Date of Birth <span class="required">*</span></label>
                                        <input type="date" name="date_of_birth" class="form-control" required>
                                        <span class="input-hint"><i class="fas fa-info-circle"></i> Must be at least 18 years old</span>
                                    </div>
                                    <div class="form-group">
                                        <label>Email Address <span class="required">*</span></label>
                                        <input type="email" name="email" class="form-control" required placeholder="employee@example.com">
                                    </div>
                                    <div class="form-group">
                                        <label>Phone Number</label>
                                        <input type="tel" name="phone_number" class="form-control" placeholder="+251 9XX XXX XXX">
                                        <span class="input-hint"><i class="fas fa-phone"></i> Include country code</span>
                                    </div>
                                    <div class="form-group">
                                        <label>Education Level</label>
                                        <select name="education_level" class="form-control">
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
                                <div class="step-header">
                                    <div class="step-icon">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <h2 class="step-title">Position Details</h2>
                                    <p class="step-description">Define the employee's role and employment terms</p>
                                </div>

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>Job Position <span class="required">*</span></label>
                                        <input type="text" name="position" class="form-control" required placeholder="e.g., Nurse, Health Officer, Doctor">
                                    </div>
                                    <div class="form-group">
                                        <label>Department</label>
                                        <select name="department_assigned" class="form-control">
                                            <option value="">Select Department</option>
                                            <option value="general_medicine">General Medicine</option>
                                            <option value="pediatrics">Pediatrics</option>
                                            <option value="obstetrics_gynecology">Obstetrics & Gynecology</option>
                                            <option value="emergency">Emergency / ER</option>
                                            <option value="pharmacy">Pharmacy</option>
                                            <option value="laboratory">Laboratory</option>
                                            <option value="radiology">Radiology</option>
                                            <option value="administration">Administration</option>
                                            <option value="human_resources">Human Resources</option>
                                            <option value="finance">Finance</option>
                                            <option value="maintenance">Maintenance</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Job Level</label>
                                        <select name="job_level" class="form-control">
                                            <option value="">Select Job Level</option>
                                            <option value="entry">Entry Level</option>
                                            <option value="junior">Junior</option>
                                            <option value="mid">Mid-Level</option>
                                            <option value="senior">Senior</option>
                                            <option value="lead">Lead</option>
                                            <option value="manager">Manager</option>
                                            <option value="director">Director</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Employment Type</label>
                                        <select name="employment_type" class="form-control">
                                            <option value="full-time">Full-Time</option>
                                            <option value="part-time">Part-Time</option>
                                            <option value="contract">Contract</option>
                                            <option value="temporary">Temporary</option>
                                            <option value="intern">Intern</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Monthly Salary (ETB)</label>
                                        <input type="number" name="salary" class="form-control" placeholder="15000" step="100" min="0">
                                        <span class="input-hint"><i class="fas fa-coins"></i> Enter monthly salary amount</span>
                                    </div>
                                    <div class="form-group">
                                        <label>Join Date</label>
                                        <input type="date" name="join_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Location & Contact -->
                            <div class="form-step" data-step="3">
                                <div class="step-header">
                                    <div class="step-icon">
                                        <i class="fas fa-map-marked-alt"></i>
                                    </div>
                                    <h2 class="step-title">Location & Contact</h2>
                                    <p class="step-description">Enter address and emergency contact information</p>
                                </div>

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>Region</label>
                                        <select name="region" class="form-control">
                                            <option value="">Select Region</option>
                                            <option value="addis_ababa">Addis Ababa</option>
                                            <option value="amhara">Amhara</option>
                                            <option value="oromia">Oromia</option>
                                            <option value="tigray">Tigray</option>
                                            <option value="snnpr">SNNPR</option>
                                            <option value="somali">Somali</option>
                                            <option value="afar">Afar</option>
                                            <option value="benishangul_gumuz">Benishangul-Gumuz</option>
                                            <option value="gambela">Gambela</option>
                                            <option value="harari">Harari</option>
                                            <option value="dire_dawa">Dire Dawa</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Zone</label>
                                        <input type="text" name="zone" class="form-control" placeholder="Enter Zone">
                                    </div>
                                    <div class="form-group">
                                        <label>Woreda</label>
                                        <input type="text" name="woreda" class="form-control" placeholder="Enter Woreda">
                                    </div>
                                    <div class="form-group">
                                        <label>Kebele</label>
                                        <input type="text" name="kebele" class="form-control" placeholder="Enter Kebele">
                                    </div>
                                </div>

                                <div class="form-grid">
                                    <div class="form-group" style="grid-column: 1/-1;">
                                        <label>Full Address</label>
                                        <textarea name="address" class="form-control" rows="3" placeholder="Enter complete address details"></textarea>
                                    </div>
                                    <div class="form-group" style="grid-column: 1/-1;">
                                        <label>Emergency Contact</label>
                                        <input type="text" name="emergency_contact" class="form-control" placeholder="Name and phone number of emergency contact">
                                        <span class="input-hint"><i class="fas fa-phone-alt"></i> Person to contact in case of emergency</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 4: Review & Submit -->
                            <div class="form-step" data-step="4">
                                <div class="step-header">
                                    <div class="step-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <h2 class="step-title">Review & Submit</h2>
                                    <p class="step-description">Please review all information before submitting</p>
                                </div>

                                <div id="reviewContent" class="review-grid">
                                    <!-- Review content will be populated by JavaScript -->
                                </div>

                                <div style="background: rgba(255, 183, 77, 0.2); border: 2px solid rgba(255, 183, 77, 0.4); border-radius: 12px; padding: 20px; margin-top: 25px; color: white;">
                                    <i class="fas fa-exclamation-triangle" style="margin-right: 10px; font-size: 1.1rem;"></i>
                                    <strong>Important:</strong> Please verify all information is correct before submitting. This will create a new employee record in the system.
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
                                    Register Employee
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Quick Actions -->
                    <div class="glass-card" style="text-align: center; padding: 25px;">
                        <h3 style="color: white; margin-bottom: 20px;">Quick Actions</h3>
                        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                            <a href="hr-employees.php" class="btn btn-secondary">
                                <i class="fas fa-users"></i>
                                View All Employees
                            </a>
                            <a href="kebele_hr_dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-tachometer-alt"></i>
                                Back to Dashboard
                            </a>
                            <button type="button" class="btn btn-secondary" onclick="clearForm()">
                                <i class="fas fa-redo"></i>
                                Clear Form
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Auto-save Indicator -->
    <div class="autosave-indicator" id="autosaveIndicator">
        <i class="fas fa-cloud-upload-alt"></i>
        <span>Progress saved</span>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <h3 style="color: white; margin: 0 0 10px 0;">Registering Employee...</h3>
            <p style="color: rgba(255, 255, 255, 0.8); margin: 0;">Please wait while we create the employee record</p>
        </div>
    </div>

    <script src="scripts.js"></script>
    <script>
        let currentStep = 1;
        const totalSteps = 4;
        let formData = {};

        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        const progressLine = document.getElementById('progressLine');
        const form = document.getElementById('registrationForm');

        // Initialize
        showStep(1);

        function showStep(step) {
            // Hide all steps
            document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.step').forEach(s => s.classList.remove('active', 'completed'));

            // Show current step
            document.querySelector(`.form-step[data-step="${step}"]`).classList.add('active');
            document.querySelector(`.step[data-step="${step}"]`).classList.add('active');

            // Mark completed steps
            for (let i = 1; i < step; i++) {
                document.querySelector(`.step[data-step="${i}"]`).classList.add('completed');
            }

            // Update progress line
            const progress = ((step - 1) / (totalSteps - 1)) * 100;
            progressLine.style.width = progress + '%';

            // Update navigation buttons
            prevBtn.style.display = step === 1 ? 'none' : 'inline-flex';
            nextBtn.style.display = step === totalSteps ? 'none' : 'inline-flex';
            submitBtn.style.display = step === totalSteps ? 'inline-flex' : 'none';

            // Populate review if on last step
            if (step === 4) {
                populateReview();
            }

            // Save current step
            currentStep = step;
            saveProgress();
        }

        function validateStep(step) {
            const currentFormStep = document.querySelector(`.form-step[data-step="${step}"]`);
            const requiredInputs = currentFormStep.querySelectorAll('[required]');
            let isValid = true;

            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('invalid');
                    input.focus();
                } else {
                    input.classList.remove('invalid');
                    input.classList.add('valid');
                }
            });

            // Additional validations
            if (step === 1) {
                const dobInput = currentFormStep.querySelector('input[name="date_of_birth"]');
                if (dobInput.value) {
                    const age = calculateAge(dobInput.value);
                    if (age < 18) {
                        isValid = false;
                        dobInput.classList.add('invalid');
                        alert(`Employee must be at least 18 years old. Current age: ${age} years.`);
                    }
                }

                const emailInput = currentFormStep.querySelector('input[name="email"]');
                if (emailInput.value && !isValidEmail(emailInput.value)) {
                    isValid = false;
                    emailInput.classList.add('invalid');
                    alert('Please enter a valid email address.');
                }
            }

            if (!isValid) {
                // Shake animation for invalid form
                currentFormStep.style.animation = 'shake 0.5s ease-in-out';
                setTimeout(() => {
                    currentFormStep.style.animation = '';
                }, 500);
            }

            return isValid;
        }

        function calculateAge(birthDate) {
            const today = new Date();
            const birth = new Date(birthDate);
            let age = today.getFullYear() - birth.getFullYear();
            const monthDiff = today.getMonth() - birth.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
                age--;
            }
            
            return age;
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function populateReview() {
            const formData = new FormData(form);
            let html = '';

            // Personal Information
            html += '<div class="review-section">';
            html += '<h3><i class="fas fa-user"></i> Personal Information</h3>';
            html += `<p><strong>Full Name:</strong> ${formData.get('first_name')} ${formData.get('middle_name')} ${formData.get('last_name')}</p>`;
            html += `<p><strong>Gender:</strong> ${formData.get('gender') || 'Not specified'}</p>`;
            html += `<p><strong>Date of Birth:</strong> ${formData.get('date_of_birth') || 'Not specified'}</p>`;
            html += `<p><strong>Email:</strong> ${formData.get('email') || 'Not specified'}</p>`;
            html += `<p><strong>Phone:</strong> ${formData.get('phone_number') || 'Not provided'}</p>`;
            html += `<p><strong>Education:</strong> ${formData.get('education_level') || 'Not specified'}</p>`;
            html += '</div>';

            // Position Information
            html += '<div class="review-section">';
            html += '<h3><i class="fas fa-briefcase"></i> Position Details</h3>';
            html += `<p><strong>Position:</strong> ${formData.get('position') || 'Not specified'}</p>`;
            html += `<p><strong>Department:</strong> ${formData.get('department_assigned') || 'Not specified'}</p>`;
            html += `<p><strong>Job Level:</strong> ${formData.get('job_level') || 'Not specified'}</p>`;
            html += `<p><strong>Employment Type:</strong> ${formData.get('employment_type') || 'Full-time'}</p>`;
            html += `<p><strong>Monthly Salary:</strong> ${formData.get('salary') ? formData.get('salary') + ' ETB' : 'Not specified'}</p>`;
            html += `<p><strong>Join Date:</strong> ${formData.get('join_date') || 'Not specified'}</p>`;
            html += '</div>';

            // Location Information
            html += '<div class="review-section" style="grid-column: 1/-1;">';
            html += '<h3><i class="fas fa-map-marker-alt"></i> Location & Contact</h3>';
            html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">';
            html += `<p><strong>Region:</strong> ${formData.get('region') || 'Not specified'}</p>`;
            html += `<p><strong>Zone:</strong> ${formData.get('zone') || 'Not specified'}</p>`;
            html += `<p><strong>Woreda:</strong> ${formData.get('woreda') || 'Not specified'}</p>`;
            html += `<p><strong>Kebele:</strong> ${formData.get('kebele') || 'Not specified'}</p>`;
            html += `<p style="grid-column: 1/-1;"><strong>Address:</strong> ${formData.get('address') || 'Not provided'}</p>`;
            html += `<p style="grid-column: 1/-1;"><strong>Emergency Contact:</strong> ${formData.get('emergency_contact') || 'Not provided'}</p>`;
            html += '</div></div>';

            document.getElementById('reviewContent').innerHTML = html;
        }

        function saveProgress() {
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            data.currentStep = currentStep;
            localStorage.setItem('employeeRegistrationProgress', JSON.stringify(data));
            
            // Show save indicator
            const indicator = document.getElementById('autosaveIndicator');
            indicator.style.display = 'flex';
            setTimeout(() => {
                indicator.style.display = 'none';
            }, 2000);
        }

        function loadProgress() {
            const savedData = localStorage.getItem('employeeRegistrationProgress');
            if (savedData) {
                const data = JSON.parse(savedData);
                Object.keys(data).forEach(key => {
                    if (key !== 'currentStep') {
                        const input = form.querySelector(`[name="${key}"]`);
                        if (input) {
                            input.value = data[key];
                        }
                    }
                });
                if (data.currentStep) {
                    showStep(data.currentStep);
                }
            }
        }

        function clearForm() {
            if (confirm('Are you sure you want to clear all form data?')) {
                form.reset();
                localStorage.removeItem('employeeRegistrationProgress');
                showStep(1);
            }
        }

        // Event Listeners
        nextBtn.addEventListener('click', () => {
            if (validateStep(currentStep)) {
                if (currentStep < totalSteps) {
                    showStep(currentStep + 1);
                }
            }
        });

        prevBtn.addEventListener('click', () => {
            if (currentStep > 1) {
                showStep(currentStep - 1);
            }
        });

        // Form submission
        form.addEventListener('submit', (e) => {
            if (!validateStep(4)) {
                e.preventDefault();
                return;
            }
            
            document.getElementById('loadingOverlay').style.display = 'flex';
            localStorage.removeItem('employeeRegistrationProgress');
        });

        // Auto-save on input
        let saveTimer;
        form.addEventListener('input', () => {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveProgress, 1000);
        });

        // Real-time validation
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

            input.addEventListener('input', function() {
                this.classList.remove('invalid', 'valid');
            });
        });

        // Phone number formatting
        const phoneInput = document.querySelector('input[name="phone_number"]');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.startsWith('251')) {
                    value = '+' + value;
                } else if (value.startsWith('0') && value.length > 1) {
                    value = '+251' + value.substring(1);
                } else if (value.startsWith('9') && value.length === 9) {
                    value = '+251' + value;
                }
                e.target.value = value;
            });
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.altKey && e.key === 'ArrowRight') {
                e.preventDefault();
                if (currentStep < totalSteps && validateStep(currentStep)) {
                    showStep(currentStep + 1);
                }
            }
            
            if (e.altKey && e.key === 'ArrowLeft') {
                e.preventDefault();
                if (currentStep > 1) {
                    showStep(currentStep - 1);
                }
            }
            
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                saveProgress();
            }
        });

        // Load saved progress on page load
        window.addEventListener('DOMContentLoaded', loadProgress);

        // Prevent accidental page leave
        window.addEventListener('beforeunload', (e) => {
            const hasData = form.querySelector('input[name="first_name"]').value.trim();
            if (hasData) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</body>
</html>