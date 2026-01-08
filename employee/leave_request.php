<?php
session_start();
if (!isset($_SESSION['employee_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: login.php');
    exit;
}
require_once '../db.php';
$conn = getDBConnection();
$emp_id = $_SESSION['employee_id'];

// Get current balances for validation hints
$stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

$balances = [
    'annual' => $employee['annual_entitlement'] - $employee['annual_used'],
    'sick' => $employee['sick_entitlement'] - $employee['sick_used'],
    'maternity' => $employee['maternity_entitlement'] - $employee['maternity_used'],
    'paternity' => $employee['paternity_entitlement'] - $employee['paternity_used'],
    'emergency' => $employee['emergency_entitlement'] - $employee['emergency_used']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Leave | HealthFirst</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        .form-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            max-width: 700px;
            margin: 0 auto;
        }
        .form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #444; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1.5px solid #edf2f7;
            border-radius: 10px;
            background: #f8fafc;
        }
        .form-group input:focus { outline: none; border-color: #1a4a5f; background: white; }
        
        .balance-hint {
            display: block;
            margin-top: 5px;
            font-size: 0.8rem;
            color: #666;
            font-weight: 500;
        }
        .submit-btn {
            background: #1a4a5f;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
        }
        .submit-btn:disabled { background: #cbd5e0; cursor: not-allowed; }
        
        .error-alert {
            background: #fde8e8;
            color: #9b1c1c;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="hr-container">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-heartbeat"></i> <span>HealthFirst</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="leave_request.php" class="active"><i class="fas fa-calendar-plus"></i> Request Leave</a>
                <a href="leave_history.php"><i class="fas fa-history"></i> My Leave History</a>
                <a href="payslips.php"><i class="fas fa-money-check-alt"></i> Payslips</a>
                <a href="attendance.php"><i class="fas fa-clock"></i> My Attendance</a>
                <a href="documents.php"><i class="fas fa-folder-open"></i> Documents</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                <div class="header-left">
                    <h1 class="page-title">Submit Leave Request</h1>
                </div>
            </header>

            <div class="content">
                <div class="form-card">
                    <div id="errorAlert" class="error-alert"></div>
                    <form id="leaveForm" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Leave Type</label>
                            <select name="leave_type" id="leaveType" required>
                                <option value="annual">Annual Leave</option>
                                <option value="sick">Sick Leave</option>
                                <?php if($employee['gender'] == 'female'): ?>
                                <option value="maternity">Maternity Leave</option>
                                <?php else: ?>
                                <option value="paternity">Paternity Leave</option>
                                <?php endif; ?>
                                <option value="emergency">Emergency Leave</option>
                            </select>
                            <span class="balance-hint" id="balanceHint">Remaining: <?php echo $balances['annual']; ?> days</span>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date" name="start_date" id="startDate" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" name="end_date" id="endDate" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Calculated Days: <span id="daysDisplay" style="color: #1a4a5f; border-bottom: 2px solid;">0</span></label>
                        </div>

                        <div class="form-group">
                            <label>Reason (Optional)</label>
                            <textarea name="reason" rows="3" placeholder="State your reason for leave..."></textarea>
                        </div>

                        <div class="form-group">
                            <label>Attachment (Optional)</label>
                            <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png">
                            <small>Required for Sick Leave > 3 days</small>
                        </div>

                        <button type="submit" class="submit-btn" id="submitBtn">Submit Request</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        const balances = <?php echo json_encode($balances); ?>;
        
        document.getElementById('leaveType').addEventListener('change', function() {
            const type = this.value;
            document.getElementById('balanceHint').textContent = `Remaining: ${balances[type]} days`;
            validateDates();
        });

        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        const daysDisplay = document.getElementById('daysDisplay');
        const submitBtn = document.getElementById('submitBtn');
        const errorAlert = document.getElementById('errorAlert');

        function calculateDays() {
            const start = new Date(startDateInput.value);
            const end = new Date(endDateInput.value);
            if(start && end && end >= start) {
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; 
                return diffDays;
            }
            return 0;
        }

        function validateDates() {
            const days = calculateDays();
            const type = document.getElementById('leaveType').value;
            daysDisplay.textContent = days;
            
            errorAlert.style.display = 'none';
            submitBtn.disabled = false;

            if(days > 0) {
                if(days > balances[type]) {
                    errorAlert.textContent = `Insufficient balance. You requested ${days} days but only have ${balances[type]} ${type} leave days remaining.`;
                    errorAlert.style.display = 'block';
                    submitBtn.disabled = true;
                }
            }
        }

        startDateInput.addEventListener('change', validateDates);
        endDateInput.addEventListener('change', validateDates);

        document.getElementById('leaveForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            fetch('submit_leave.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('Leave request submitted successfully!');
                    window.location.href = 'dashboard.php';
                } else {
                    errorAlert.textContent = data.message;
                    errorAlert.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Request';
                }
            })
            .catch(err => {
                console.error(err);
                alert('Network error. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Request';
            });
        });
    </script>
</body>
</html>
