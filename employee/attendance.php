<?php
session_start();
if (!isset($_SESSION['employee_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: login.php');
    exit;
}
require_once '../db.php';
$conn = getDBConnection();
$emp_id = $_SESSION['employee_id'];

// Get employee name
$stmt = $conn->prepare("SELECT first_name, last_name FROM employees WHERE employee_id = ?");
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

// Get attendance records for last 30 days
$stmt = $conn->prepare("SELECT * FROM attendance WHERE employee_id = ? ORDER BY attendance_date DESC LIMIT 30");
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$attendance_records = $stmt->get_result();

// Calculate statistics
$total_days = 30;
$present_days = 0;
$absent_days = 0;
$late_days = 0;
$on_leave_days = 0;

$records_array = [];
while ($row = $attendance_records->fetch_assoc()) {
    $records_array[] = $row;
    if ($row['status'] == 'present') $present_days++;
    elseif ($row['status'] == 'absent') $absent_days++;
    elseif ($row['status'] == 'late') $late_days++;
    elseif ($row['status'] == 'on-leave') $on_leave_days++;
}

$attendance_rate = $total_days > 0 ? round(($present_days + $late_days) / $total_days * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance | HealthFirst</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        .attendance-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            text-align: center;
            border-bottom: 4px solid;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.present { border-color: #10b981; }
        .stat-card.absent { border-color: #ef4444; }
        .stat-card.late { border-color: #f59e0b; }
        .stat-card.on-leave { border-color: #3b82f6; }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .stat-card.present .stat-icon { color: #10b981; }
        .stat-card.absent .stat-icon { color: #ef4444; }
        .stat-card.late .stat-icon { color: #f59e0b; }
        .stat-card.on-leave .stat-icon { color: #3b82f6; }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #64748b;
            font-size: 0.95rem;
            font-weight: 600;
        }
        
        .attendance-rate {
            background: linear-gradient(135deg, #1a4a5f 0%, #2a6e8c 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .rate-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            border: 5px solid rgba(255,255,255,0.3);
        }
        
        .rate-percentage {
            font-size: 3rem;
            font-weight: 800;
        }
        
        .rate-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .attendance-table-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            align-items: center;
        }
        
        .attendance-table-row:hover {
            background: #f8fafc;
        }
        
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        
        .status-indicator.present { background: #10b981; }
        .status-indicator.absent { background: #ef4444; }
        .status-indicator.late { background: #f59e0b; }
        .status-indicator.on-leave { background: #3b82f6; }
    </style>
</head>
<body>
    <div class="hr-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-heartbeat"></i> <span>HealthFirst</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="leave_request.php"><i class="fas fa-calendar-plus"></i> Request Leave</a>
                <a href="leave_history.php"><i class="fas fa-history"></i> My Leave History</a>
                <a href="payslips.php"><i class="fas fa-money-check-alt"></i> Payslips</a>
                <a href="attendance.php" class="active"><i class="fas fa-clock"></i> My Attendance</a>
                <a href="documents.php"><i class="fas fa-folder-open"></i> Documents</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php $page_title = "My Attendance"; include 'navbar.php'; ?>

            <div class="content">
                <!-- Attendance Rate Card -->
                <div class="attendance-rate">
                    <div class="rate-circle">
                        <div class="rate-percentage"><?php echo $attendance_rate; ?>%</div>
                        <div class="rate-label">Attendance Rate</div>
                    </div>
                    <p style="margin: 0; font-size: 1.1rem; opacity: 0.9;">Based on last 30 days</p>
                </div>

                <!-- Statistics Grid -->
                <div class="attendance-stats-grid">
                    <div class="stat-card present">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-value"><?php echo $present_days; ?></div>
                        <div class="stat-label">Present Days</div>
                    </div>
                    
                    <div class="stat-card late">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-value"><?php echo $late_days; ?></div>
                        <div class="stat-label">Late Arrivals</div>
                    </div>
                    
                    <div class="stat-card absent">
                        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                        <div class="stat-value"><?php echo $absent_days; ?></div>
                        <div class="stat-label">Absent Days</div>
                    </div>
                    
                    <div class="stat-card on-leave">
                        <div class="stat-icon"><i class="fas fa-umbrella-beach"></i></div>
                        <div class="stat-value"><?php echo $on_leave_days; ?></div>
                        <div class="stat-label">On Leave</div>
                    </div>
                </div>

                <!-- Attendance Records -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Attendance Records (Last 30 Days)</h2>
                        <button class="section-action-btn" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                    <div class="hr-section-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Working Hours</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($records_array) > 0): ?>
                                        <?php foreach ($records_array as $record): ?>
                                        <tr>
                                            <td><?php echo date('d M Y', strtotime($record['attendance_date'])); ?></td>
                                            <td><?php echo $record['check_in'] ? date('h:i A', strtotime($record['check_in'])) : '--:--'; ?></td>
                                            <td><?php echo $record['check_out'] ? date('h:i A', strtotime($record['check_out'])) : '--:--'; ?></td>
                                            <td><?php echo $record['working_hours'] ?? '0'; ?> hrs</td>
                                            <td>
                                                <span class="status-indicator <?php echo $record['status']; ?>"></span>
                                                <?php echo ucfirst($record['status']); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center; padding: 40px; color: #64748b;">
                                                <i class="fas fa-calendar-times" style="font-size: 3rem; margin-bottom: 15px; color: #cbd5e1;"></i>
                                                <p>No attendance records found for the last 30 days.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
