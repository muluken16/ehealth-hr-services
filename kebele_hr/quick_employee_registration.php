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
        
        // Required fields only
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = $_POST['email'];
        $position = $_POST['position'];
        $department = $_POST['department_assigned'] ?? '';
        $join_date = $_POST['join_date'] ?? date('Y-m-d');
        $created_by = $_SESSION['user_name'];
        $working_kebele = $_SESSION['kebele'] ?? 'Kebele 1';
        
        // Insert into database
        $sql = "INSERT INTO employees (
            employee_id, first_name, last_name, email, position, 
            department_assigned, join_date, status, created_by, 
            working_kebele, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssssss",
            $employee_id, $first_name, $last_name, $email, $position,
            $department, $join_date, $created_by, $working_kebele
        );
        
        if ($stmt->execute()) {
            $success_message = "Employee {$first_name} {$last_name} successfully registered with ID: {$employee_id}";
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
    <title>Quick Employee Registration | Kebele HR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        .quick-registration {
            max-width: 600px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .quick-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .quick-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .quick-icon {
            width: 70px;
            height: 70px;
            border-radius: 15px;
            background: linear-gradient(135deg, var(--primary) 0%, #1a5270 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 20px;
            box-shadow: 0 6px 20px rgba(26, 74, 95, 0.3);
        }

        .quick-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 10px 0;
        }

        .quick-description {
            color: #64748b;
            font-size: 1rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .required {
            color: #ef4444;
        }

        .form-control {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(26, 74, 95, 0.1);
        }

        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #1a5270 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(26, 74, 95, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 74, 95, 0.4);
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

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
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

        .quick-actions {
            text-align: center;
            padding: 25px;
        }

        .quick-actions h3 {
            color: #1e293b;
            margin-bottom: 15px;
        }

        .action-links {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .quick-card {
                padding: 25px;
                margin: 10px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .btn-group {
                flex-direction: column;
            }

            .action-links {
                flex-direction: column;
            }
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
                $page_title = "Quick Employee Registration";
                include 'navbar.php'; 
            ?>

            <div class="content">
                <div class="quick-registration">
                    <!-- Success/Error Messages -->
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle" style="font-size: 1.3rem;"></i>
                            <div>
                                <strong>Success!</strong><br>
                                <?php echo $success_message; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle" style="font-size: 1.3rem;"></i>
                            <div>
                                <strong>Error!</strong><br>
                                <?php echo $error_message; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Quick Registration Form -->
                    <div class="quick-card">
                        <div class="quick-header">
                            <div class="quick-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <h2 class="quick-title">Quick Employee Registration</h2>
                            <p class="quick-description">Register a new employee with essential information only</p>
                        </div>

                        <form method="POST" action="">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>First Name <span class="required">*</span></label>
                                    <input type="text" name="first_name" class="form-control" required placeholder="Enter first name">
                                </div>
                                <div class="form-group">
                                    <label>Last Name <span class="required">*</span></label>
                                    <input type="text" name="last_name" class="form-control" required placeholder="Enter last name">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Email Address <span class="required">*</span></label>
                                    <input type="email" name="email" class="form-control" required placeholder="employee@example.com">
                                </div>
                                <div class="form-group">
                                    <label>Position <span class="required">*</span></label>
                                    <input type="text" name="position" class="form-control" required placeholder="e.g., Nurse, Doctor">
                                </div>
                            </div>

                            <div class="form-row">
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
                                        <option value="administration">Administration</option>
                                        <option value="human_resources">Human Resources</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Join Date</label>
                                    <input type="date" name="join_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>

                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i>
                                    Register Employee
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i>
                                    Clear Form
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Quick Actions -->
                    <div class="quick-card quick-actions">
                        <h3>Need More Options?</h3>
                        <div class="action-links">
                            <a href="modern_employee_registration.php" class="btn btn-primary">
                                <i class="fas fa-user-cog"></i>
                                Full Registration Form
                            </a>
                            <a href="hr-employees.php" class="btn btn-secondary">
                                <i class="fas fa-users"></i>
                                View All Employees
                            </a>
                            <a href="kebele_hr_dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-tachometer-alt"></i>
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="scripts.js"></script>
    <script>
        // Real-time validation
        document.querySelectorAll('input[required]').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim()) {
                    this.style.borderColor = '#10b981';
                    this.style.background = '#f0fdf4';
                } else {
                    this.style.borderColor = '#ef4444';
                    this.style.background = '#fef2f2';
                }
            });

            input.addEventListener('input', function() {
                this.style.borderColor = '#e2e8f0';
                this.style.background = '#f8fafc';
            });
        });

        // Email validation
        const emailInput = document.querySelector('input[name="email"]');
        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (this.value && !emailRegex.test(this.value)) {
                    this.style.borderColor = '#ef4444';
                    this.style.background = '#fef2f2';
                    alert('Please enter a valid email address.');
                }
            });
        }

        // Auto-clear success message
        <?php if ($success_message): ?>
        setTimeout(() => {
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                successAlert.style.opacity = '0';
                successAlert.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    successAlert.remove();
                }, 300);
            }
        }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>