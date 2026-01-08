<?php
session_start();
if (!isset($_SESSION['employee_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: login.php');
    exit;
}
require_once '../db.php';
$conn = getDBConnection();
$emp_id = $_SESSION['employee_id'];

// Get all leave requests
$stmt = $conn->prepare("SELECT * FROM leave_requests WHERE employee_id = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$all_leaves = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Leave History | HealthFirst</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
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
        
        .leave-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            margin-bottom: 15px;
            display: grid;
            grid-template-columns: 1fr 2fr 1fr 1fr;
            align-items: center;
        }
        .leave-card-mobile { display: none; }
        @media (max-width: 768px) {
            .leave-card { display: none; }
            .leave-card-mobile { display: block; border: 1px solid #eee; margin-bottom: 15px; padding: 15px; border-radius: 10px; }
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
                <a href="leave_request.php"><i class="fas fa-calendar-plus"></i> Request Leave</a>
                <a href="leave_history.php" class="active"><i class="fas fa-history"></i> My Leave History</a>
                <a href="payslips.php"><i class="fas fa-money-check-alt"></i> Payslips</a>
                <a href="attendance.php"><i class="fas fa-clock"></i> My Attendance</a>
                <a href="documents.php"><i class="fas fa-folder-open"></i> Documents</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="header">
                 <div class="header-left">
                    <h1 class="page-title">My Leave History</h1>
                </div>
            </header>

            <div class="content">
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">All Leave Applications</h2>
                    </div>
                    <div class="hr-section-body" style="padding: 20px;">
                        <?php if($all_leaves->num_rows > 0): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Application Date</th>
                                        <th>Leave Type</th>
                                        <th>Duration</th>
                                        <th>Days</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($leave = $all_leaves->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($leave['created_at'])); ?></td>
                                        <td style="text-transform: capitalize; font-weight: 600;"><?php echo $leave['leave_type']; ?></td>
                                        <td style="font-size: 0.85rem;">
                                            <?php echo date('d M', strtotime($leave['start_date'])); ?> - 
                                            <?php echo date('d M Y', strtotime($leave['end_date'])); ?>
                                        </td>
                                        <td style="text-align: center;"><?php echo $leave['days_requested']; ?></td>
                                        <td>
                                            <span class="status-pill status-<?php echo $leave['status']; ?>">
                                                <?php echo $leave['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($leave['status'] == 'rejected'): ?>
                                                <i class="fas fa-info-circle" title="<?php echo htmlspecialchars($leave['rejected_reason']); ?>" style="color: #c62828; cursor: help;"></i>
                                            <?php elseif($leave['status'] == 'approved'): ?>
                                                <span style="font-size: 0.7rem; color: #999;">Approved at <?php echo date('d/m/y', strtotime($leave['approved_at'])); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div style="text-align: center; padding: 40px;">
                                <i class="fas fa-folder-open" style="font-size: 3rem; color: #ddd; margin-bottom: 15px;"></i>
                                <p>You haven't applied for any leave yet.</p>
                                <a href="leave_request.php" class="btn btn-primary" style="margin-top: 15px;">Apply Now</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
