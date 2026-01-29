<?php
session_start();
if (!isset($_SESSION['employee_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: login.php');
    exit;
}
require_once '../db.php';
$conn = getDBConnection();
$emp_id = $_SESSION['employee_id'];

// Get employee info and balances
$stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

// Get recent leave requests
$stmt = $conn->prepare("SELECT * FROM leave_requests WHERE employee_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$recent_leaves = $stmt->get_result();

$annual_remaining = $employee['annual_entitlement'] - $employee['annual_used'];
$sick_remaining = $employee['sick_entitlement'] - $employee['sick_used'];
$maternity_remaining = $employee['maternity_entitlement'] - $employee['maternity_used'];
$paternity_remaining = $employee['paternity_entitlement'] - $employee['paternity_used'];
$emergency_remaining = $employee['emergency_entitlement'] - $employee['emergency_used'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard | HealthFirst</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .balance-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            text-align: center;
            border-bottom: 4px solid #1a4a5f;
            transition: transform 0.3s;
        }
        .balance-card:hover { transform: translateY(-5px); }
        .balance-card h3 { font-size: 0.9rem; color: #666; margin-bottom: 10px; }
        .balance-card .count { font-size: 2rem; font-weight: 800; color: #1a4a5f; }
        .balance-card .total { font-size: 0.8rem; color: #999; }
        
        .action-btn-large {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            background: #1a4a5f;
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 30px;
            transition: all 0.3s;
        }
        .action-btn-large:hover { background: #2a6e8c; box-shadow: 0 10px 20px rgba(26,74,95,0.2); }
        
        .status-pill {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        .status-pending { background: #fff8e1; color: #ff8f00; }
        .status-approved { background: #e8f5e9; color: #2e7d32; }
        .status-rejected { background: #ffebee; color: #c62828; }
    </style>
</head>
<body>
    <div class="hr-container">
        <!-- Sidebar for Employee (Simplified) -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-heartbeat"></i> <span>HealthFirst</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="leave_request.php"><i class="fas fa-calendar-plus"></i> Request Leave</a>
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
                    <h1 class="page-title">Welcome, <?php echo $_SESSION['emp_name']; ?></h1>
                </div>
                <div class="header-right" style="display:flex; align-items:center; gap:15px;">
                    <div class="emp-id-badge" style="background:rgba(26,74,95,0.1); padding:5px 12px; border-radius:8px; font-weight:600; font-size:0.9rem;">
                        <?php echo $emp_id; ?>
                    </div>
                </div>
            </header>

            <div class="content">
                <a href="leave_request.php" class="action-btn-large">
                    <i class="fas fa-paper-plane"></i> Submit New Leave Request
                </a>

                <h2 style="margin-bottom: 20px;">My Leave Balances (Days Remaining)</h2>
                <div class="dashboard-grid">
                    <div class="balance-card">
                        <h3>Annual Leave</h3>
                        <div class="count"><?php echo $annual_remaining; ?></div>
                        <div class="total">of <?php echo $employee['annual_entitlement']; ?> days</div>
                    </div>
                    <div class="balance-card" style="border-color: #e67e22;">
                        <h3>Sick Leave</h3>
                        <div class="count"><?php echo $sick_remaining; ?></div>
                        <div class="total">of <?php echo $employee['sick_entitlement']; ?> days</div>
                    </div>
                    <?php if($employee['gender'] == 'female'): ?>
                    <div class="balance-card" style="border-color: #9b59b6;">
                        <h3>Maternity Leave</h3>
                        <div class="count"><?php echo $maternity_remaining; ?></div>
                        <div class="total">of <?php echo $employee['maternity_entitlement']; ?> days</div>
                    </div>
                    <?php else: ?>
                    <div class="balance-card" style="border-color: #3498db;">
                        <h3>Paternity Leave</h3>
                        <div class="count"><?php echo $paternity_remaining; ?></div>
                        <div class="total">of <?php echo $employee['paternity_entitlement']; ?> days</div>
                    </div>
                    <?php endif; ?>
                    <div class="balance-card" style="border-color: #e74c3c;">
                        <h3>Emergency Leave</h3>
                        <div class="count"><?php echo $emergency_remaining; ?></div>
                        <div class="total">of <?php echo $employee['emergency_entitlement']; ?> days</div>
                    </div>
                </div>

                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Recent Leave Requests</h2>
                        <a href="leave_history.php" style="color: #1a4a5f; font-weight: 600; text-decoration: none;">View All</a>
                    </div>
                    <div class="hr-section-body" style="padding: 0;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($recent_leaves->num_rows > 0): ?>
                                    <?php while($leave = $recent_leaves->fetch_assoc()): ?>
                                    <tr>
                                        <td style="text-transform: capitalize;"><?php echo $leave['leave_type']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($leave['start_date'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($leave['end_date'])); ?></td>
                                        <td><?php echo $leave['days_requested']; ?></td>
                                        <td>
                                            <span class="status-pill status-<?php echo $leave['status']; ?>">
                                                <?php echo $leave['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" style="text-align: center; padding: 20px;">No recent requests.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
