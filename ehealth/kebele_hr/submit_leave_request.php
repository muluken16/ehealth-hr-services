<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Set default user for demo
$_SESSION['user_name'] = 'Kebele HR Officer';
$_SESSION['role'] = 'kebele_hr';

// Create database connection
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    
    // Validate required fields
    $required_fields = ['employee_id', 'leave_type', 'start_date', 'end_date', 'reason'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
        }
    }
    
    // Validate dates
    if (!empty($_POST['start_date']) && !empty($_POST['end_date'])) {
        $start_date = new DateTime($_POST['start_date']);
        $end_date = new DateTime($_POST['end_date']);
        $today = new DateTime();
        
        if ($start_date < $today) {
            $errors[] = "Start date cannot be in the past";
        }
        
        if ($end_date < $start_date) {
            $errors[] = "End date cannot be before start date";
        }
        
        // Calculate days requested
        $days_requested = $start_date->diff($end_date)->days + 1;
        
        if ($days_requested > 365) {
            $errors[] = "Leave request cannot exceed 365 days";
        }
    }
    
    if (!empty($errors)) {
        $error_message = implode('<br>', $errors);
        echo "<script>alert('Please fix the following errors:\\n" . addslashes($error_message) . "'); window.history.back();</script>";
        exit();
    }
    
    // Check if employee exists and validate leave balance
    $employee_id = $_POST['employee_id'];
    $leave_type = $_POST['leave_type'];
    $current_year = date('Y');
    
    $check_stmt = $conn->prepare("SELECT e.employee_id, e.first_name, e.last_name, e.join_date, e.gender, le.* FROM employees e LEFT JOIN leave_entitlements le ON e.employee_id = le.employee_id AND le.year = ? WHERE e.employee_id = ?");
    $check_stmt->bind_param("is", $current_year, $employee_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        $errors[] = "Employee ID not found";
    } else {
        $employee = $result->fetch_assoc();
        
        // Calculate years of service and create entitlement if not exists
        if (!$employee['annual_leave_days']) {
            $join_date = new DateTime($employee['join_date']);
            $current_date = new DateTime();
            $years_of_service = $current_date->diff($join_date)->y;
            
            // Calculate entitlements
            $annual_leave = 21;
            if ($years_of_service >= 5) $annual_leave += 2;
            if ($years_of_service >= 10) $annual_leave += 2;
            if ($years_of_service >= 15) $annual_leave += 3;
            if ($years_of_service >= 20) $annual_leave += 2;
            
            $maternity_days = ($employee['gender'] === 'female') ? 120 : 0;
            $paternity_days = ($employee['gender'] === 'male') ? 10 : 0;
            
            // Create entitlement record
            $create_entitlement = $conn->prepare("INSERT INTO leave_entitlements (employee_id, year, annual_leave_days, sick_leave_days, maternity_leave_days, paternity_leave_days, emergency_leave_days) VALUES (?, ?, ?, 14, ?, ?, 5)");
            $create_entitlement->bind_param("siiii", $employee_id, $current_year, $annual_leave, $maternity_days, $paternity_days);
            $create_entitlement->execute();
            $create_entitlement->close();
            
            // Reload employee data
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $employee = $result->fetch_assoc();
        }
        
        // Validate leave balance
        $field_map = [
            'annual' => 'annual_leave_days',
            'sick' => 'sick_leave_days',
            'maternity' => 'maternity_leave_days',
            'paternity' => 'paternity_leave_days',
            'emergency' => 'emergency_leave_days'
        ];
        
        $used_field_map = [
            'annual' => 'used_annual_leave',
            'sick' => 'used_sick_leave',
            'maternity' => 'used_maternity_leave',
            'paternity' => 'used_paternity_leave',
            'emergency' => 'used_emergency_leave'
        ];
        
        if (isset($field_map[$leave_type])) {
            $entitled_days = $employee[$field_map[$leave_type]];
            $used_days = $employee[$used_field_map[$leave_type]];
            $available_days = $entitled_days - $used_days;
            
            if ($entitled_days == 0) {
                $errors[] = "Employee is not entitled to {$leave_type} leave";
            } elseif ($days_requested > $available_days) {
                $errors[] = "Insufficient leave balance. Available: {$available_days} days, Requested: {$days_requested} days";
            }
        }
    }
    $check_stmt->close();
    
    try {
        $conn->begin_transaction();
        
        // Insert leave request
        $leave_type = $_POST['leave_type'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $reason = $_POST['reason'];
        $days_requested = (new DateTime($start_date))->diff(new DateTime($end_date))->days + 1;
        
        $insert_sql = "INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, days_requested, reason, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssssss", $employee_id, $leave_type, $start_date, $end_date, $days_requested, $reason);
        
        if (!$insert_stmt->execute()) {
            throw new Exception("Failed to submit leave request: " . $insert_stmt->error);
        }
        
        $conn->commit();
        $insert_stmt->close();
        
        echo "<script>
            alert('✅ Leave request submitted successfully!\\nEmployee: {$employee['first_name']} {$employee['last_name']}\\nDays: {$days_requested}\\nStatus: Pending Approval');
            window.location.href='hr-leave.html';
        </script>";
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>
            alert('❌ Error submitting leave request: " . addslashes($e->getMessage()) . "');
            window.history.back();
        </script>";
    }
    
    $conn->close();
    exit();
}

// Get all employees for dropdown
$employees_result = $conn->query("SELECT employee_id, first_name, last_name, position FROM employees WHERE status = 'active' ORDER BY first_name, last_name");
$employees = [];
while ($row = $employees_result->fetch_assoc()) {
    $employees[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Submit Leave Request</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        .content {
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

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

        .days-display {
            background: #e8f5e8;
            border: 2px solid #27ae60;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            font-weight: 600;
            color: #27ae60;
            margin-top: 10px;
        }

        .leave-balance-card {
            background: #f8f9fa;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }

        .leave-balance-card h4 {
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .balance-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .balance-item {
            background: white;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            border: 2px solid #e1e8ed;
            transition: all 0.3s ease;
        }

        .balance-item:hover {
            border-color: #3498db;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.15);
        }

        .balance-type {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-size: 12px;
        }

        .balance-numbers {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .balance-remaining {
            font-size: 1.5rem;
            font-weight: 700;
            color: #27ae60;
        }

        .balance-total {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .balance-used {
            font-size: 0.8rem;
            color: #e74c3c;
        }

        .balance-pending {
            font-size: 0.8rem;
            color: #f39c12;
        }

        .insufficient-balance {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
            padding: 10px;
            border-radius: 6px;
            margin-top: 10px;
            font-weight: 600;
        }

        .sufficient-balance {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
            padding: 10px;
            border-radius: 6px;
            margin-top: 10px;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="hr-container">
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
                    <li class="menu-item">
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
                    <li class="menu-item active">
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
                    <h1 class="page-title">Submit Leave Request</h1>
                </div>

                <div class="header-actions">
                    <button class="btn-secondary" onclick="window.location.href='hr-leave.html'">
                        <i class="fas fa-arrow-left"></i> Back to Leave Management
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <div class="form-container">
                    <form method="POST">
                        <div class="form-group">
                            <label for="employee_id">Employee *</label>
                            <select id="employee_id" name="employee_id" required onchange="loadEmployeeBalance()">
                                <option value="">Select Employee</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?php echo htmlspecialchars($emp['employee_id']); ?>">
                                        <?php echo htmlspecialchars($emp['employee_id'] . ' - ' . $emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['position'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Employee Leave Balance Display -->
                        <div id="leave_balance_section" style="display: none;">
                            <div class="leave-balance-card">
                                <h4><i class="fas fa-user-clock"></i> <span id="employee_name"></span> - Leave Balance</h4>
                                <div class="balance-grid" id="balance_grid">
                                    <!-- Balance will be loaded here -->
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="leave_type">Leave Type *</label>
                            <select id="leave_type" name="leave_type" required onchange="validateLeaveBalance()">
                                <option value="">Select Leave Type</option>
                                <option value="annual">Annual Leave</option>
                                <option value="sick">Sick Leave</option>
                                <option value="maternity">Maternity Leave</option>
                                <option value="paternity">Paternity Leave</option>
                                <option value="emergency">Emergency Leave</option>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="start_date">Start Date *</label>
                                <input type="date" id="start_date" name="start_date" required onchange="calculateDays(); validateLeaveBalance();">
                            </div>
                            <div class="form-group">
                                <label for="end_date">End Date *</label>
                                <input type="date" id="end_date" name="end_date" required onchange="calculateDays(); validateLeaveBalance();">
                            </div>
                        </div>

                        <div id="days_display" class="days-display" style="display: none;">
                            <i class="fas fa-calendar-alt"></i> Total Days: <span id="total_days">0</span>
                        </div>

                        <div id="balance_validation" style="display: none;"></div>

                        <div class="form-group">
                            <label for="reason">Reason for Leave *</label>
                            <textarea id="reason" name="reason" rows="4" placeholder="Please provide a detailed reason for your leave request..." required></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="submit-btn">
                                <i class="fas fa-paper-plane"></i> Submit Request
                            </button>
                            <button type="button" class="cancel-btn" onclick="window.location.href='hr-leave.html'">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        let employeeLeaveBalance = null;

        // Sidebar toggle
        document.getElementById('toggleSidebar').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });

        document.getElementById('mobileMenuBtn').addEventListener('click', () => {
            document.getElementById('sidebar').classList.add('mobile-open');
            document.getElementById('mobileOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        // Load employee leave balance
        function loadEmployeeBalance() {
            const employeeId = document.getElementById('employee_id').value;
            
            if (!employeeId) {
                document.getElementById('leave_balance_section').style.display = 'none';
                employeeLeaveBalance = null;
                return;
            }

            fetch(`get_employee_leave_balance.php?employee_id=${employeeId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }

                    employeeLeaveBalance = data;
                    displayLeaveBalance(data);
                    document.getElementById('leave_balance_section').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error loading leave balance:', error);
                    alert('Failed to load employee leave balance');
                });
        }

        function displayLeaveBalance(data) {
            document.getElementById('employee_name').textContent = data.employee.name;
            
            const balanceGrid = document.getElementById('balance_grid');
            balanceGrid.innerHTML = '';

            const leaveTypes = ['annual', 'sick', 'maternity', 'paternity', 'emergency'];
            const leaveLabels = {
                'annual': 'Annual Leave',
                'sick': 'Sick Leave',
                'maternity': 'Maternity Leave',
                'paternity': 'Paternity Leave',
                'emergency': 'Emergency Leave'
            };

            leaveTypes.forEach(type => {
                const balance = data.leave_balance[type];
                
                // Skip if not entitled (e.g., maternity for males)
                if (balance.entitled === 0) return;

                const balanceItem = document.createElement('div');
                balanceItem.className = 'balance-item';
                balanceItem.innerHTML = `
                    <div class="balance-type">${leaveLabels[type]}</div>
                    <div class="balance-numbers">
                        <span class="balance-remaining">${balance.remaining}</span>
                        <span class="balance-total">/ ${balance.entitled}</span>
                    </div>
                    <div class="balance-used">Used: ${balance.used}</div>
                    ${balance.pending > 0 ? `<div class="balance-pending">Pending: ${balance.pending}</div>` : ''}
                `;
                balanceGrid.appendChild(balanceItem);
            });

            // Show years of service info
            const serviceInfo = document.createElement('div');
            serviceInfo.style.marginTop = '15px';
            serviceInfo.style.textAlign = 'center';
            serviceInfo.style.color = '#6c757d';
            serviceInfo.innerHTML = `
                <small>
                    <i class="fas fa-briefcase"></i> 
                    ${data.employee.years_of_service} years of service 
                    (Joined: ${new Date(data.employee.join_date).toLocaleDateString()})
                </small>
            `;
            balanceGrid.appendChild(serviceInfo);
        }

        function validateLeaveBalance() {
            const leaveType = document.getElementById('leave_type').value;
            const totalDays = parseInt(document.getElementById('total_days').textContent) || 0;
            const validationDiv = document.getElementById('balance_validation');

            if (!employeeLeaveBalance || !leaveType || totalDays === 0) {
                validationDiv.style.display = 'none';
                return;
            }

            const balance = employeeLeaveBalance.leave_balance[leaveType];
            const availableDays = balance.remaining;

            validationDiv.style.display = 'block';

            if (totalDays > availableDays) {
                validationDiv.innerHTML = `
                    <div class="insufficient-balance">
                        <i class="fas fa-exclamation-triangle"></i>
                        Insufficient leave balance! Requested: ${totalDays} days, Available: ${availableDays} days
                    </div>
                `;
                document.querySelector('.submit-btn').disabled = true;
            } else {
                validationDiv.innerHTML = `
                    <div class="sufficient-balance">
                        <i class="fas fa-check-circle"></i>
                        Leave balance sufficient. Remaining after this request: ${availableDays - totalDays} days
                    </div>
                `;
                document.querySelector('.submit-btn').disabled = false;
            }
        }

        // Calculate days when dates change
        function calculateDays() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                if (end >= start) {
                    const timeDiff = end.getTime() - start.getTime();
                    const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
                    
                    document.getElementById('total_days').textContent = daysDiff;
                    document.getElementById('days_display').style.display = 'block';
                } else {
                    document.getElementById('days_display').style.display = 'none';
                }
            } else {
                document.getElementById('days_display').style.display = 'none';
            }
        }

        document.getElementById('start_date').addEventListener('change', calculateDays);
        document.getElementById('end_date').addEventListener('change', calculateDays);

        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start_date').min = today;
        document.getElementById('end_date').min = today;

        // Update end date minimum when start date changes
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });

        // Form validation before submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const leaveType = document.getElementById('leave_type').value;
            const totalDays = parseInt(document.getElementById('total_days').textContent) || 0;

            if (employeeLeaveBalance && leaveType && totalDays > 0) {
                const balance = employeeLeaveBalance.leave_balance[leaveType];
                const availableDays = balance.remaining;

                if (totalDays > availableDays) {
                    e.preventDefault();
                    alert(`Cannot submit leave request. Insufficient leave balance!\nRequested: ${totalDays} days\nAvailable: ${availableDays} days`);
                    return false;
                }
            }
        });
    </script>
</body>
</html>