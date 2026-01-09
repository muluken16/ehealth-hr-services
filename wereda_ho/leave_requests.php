<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'wereda_health_officer') {
    header('Location: ../index.html');
    exit();
}

include '../db.php';
$conn = getDBConnection();

$user_woreda = $_SESSION['woreda'] ?? 'West Shewa Woreda 1';
$user_woreda_escaped = $conn->real_escape_string($user_woreda);

// Filters
$status_filter = $_GET['status'] ?? 'pending';
$kebele_filter = $_GET['kebele'] ?? '';

$sql = "SELECT lr.*, e.first_name, e.last_name, e.department_assigned, e.kebele 
        FROM leave_requests lr 
        JOIN employees e ON lr.employee_id = e.employee_id 
        WHERE e.woreda = '$user_woreda_escaped'";

if ($status_filter != 'all') {
    $sql .= " AND lr.status = '" . $conn->real_escape_string($status_filter) . "'";
}
if (!empty($kebele_filter)) {
    $sql .= " AND e.kebele = '" . $conn->real_escape_string($kebele_filter) . "'";
}
$sql .= " ORDER BY lr.created_at DESC";

$requests = $conn->query($sql);
$kebeles = $conn->query("SELECT DISTINCT kebele FROM employees WHERE woreda = '$user_woreda_escaped' ORDER BY kebele");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management | Wereda HO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/styleho.css">
    <style>
        .filter-bar {
            background: white;
            padding: 20px 25px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .leave-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .leave-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #f1f5f9;
            transition: transform 0.2s;
        }

        .leave-card:hover {
            transform: translateY(-5px);
        }

        .leave-card-header {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            background: #fbfcfd;
            border-bottom: 1px solid #f1f5f9;
        }

        .leave-card-body {
            padding: 20px;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .btn-action {
            padding: 10px 18px;
            border-radius: 10px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <header class="header">
                <div class="header-left">
                    <h1 class="page-title">Leave Management</h1>
                </div>
            </header>

            <div class="content">
                <div class="filter-bar">
                    <form method="GET" style="display: flex; gap: 15px; width: 100%;">
                        <select name="status"
                            style="padding: 10px; border-radius: 10px; border: 1px solid #ddd; min-width: 150px;">
                            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending
                            </option>
                            <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved
                            </option>
                            <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected
                            </option>
                        </select>
                        <select name="kebele" style="padding: 10px; border-radius: 10px; border: 1px solid #ddd;">
                            <option value="">All Kebeles</option>
                            <?php while ($k = $kebeles->fetch_assoc())
                                echo "<option " . ($kebele_filter == $k['kebele'] ? 'selected' : '') . ">" . htmlspecialchars($k['kebele']) . "</option>"; ?>
                        </select>
                        <button type="submit" class="btn-action"
                            style="background: var(--primary); color: white;">Filter</button>
                    </form>
                </div>

                <div class="leave-grid">
                    <?php if ($requests && $requests->num_rows > 0): ?>
                        <?php while ($req = $requests->fetch_assoc()): ?>
                            <div class="leave-card">
                                <div class="leave-card-header">
                                    <div
                                        style="width: 45px; height: 45px; border-radius: 50%; background: #eff6ff; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #3b82f6;">
                                        <?php echo strtoupper(substr($req['first_name'], 0, 1) . substr($req['last_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 800; font-size: 0.95rem; color: var(--primary);">
                                            <?php echo $req['first_name'] . ' ' . $req['last_name']; ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">
                                            <?php echo $req['kebele'] ?: 'Woreda Office'; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="leave-card-body">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted);">
                                            <?php echo $req['leave_type']; ?>
                                        </span>
                                        <span class="status-badge"
                                            style="background:<?php echo $req['status'] == 'pending' ? '#fff7ed' : ($req['status'] == 'approved' ? '#f0fdf4' : '#fef2f2'); ?>; color:<?php echo $req['status'] == 'pending' ? '#9a3412' : ($req['status'] == 'approved' ? '#166534' : '#991b1b'); ?>;">
                                            <?php echo $req['status']; ?>
                                        </span>
                                    </div>
                                    <div
                                        style="background: #f8fafc; padding: 12px; border-radius: 12px; font-size: 0.85rem; font-weight: 600; margin-bottom: 15px;">
                                        <i class="far fa-calendar-alt" style="margin-right: 8px; color: var(--secondary);"></i>
                                        <?php echo date('M d', strtotime($req['start_date'])); ?> -
                                        <?php echo date('M d, Y', strtotime($req['end_date'])); ?>
                                    </div>
                                    <?php if ($req['status'] == 'pending'): ?>
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                            <button onclick="approveLeave(<?php echo $req['id']; ?>)" class="btn-action"
                                                style="background:#10b981; color:white; justify-content:center;"><i
                                                    class="fas fa-check"></i> Approve</button>
                                            <button onclick="rejectLeave(<?php echo $req['id']; ?>)" class="btn-action"
                                                style="background:#ef4444; color:white; justify-content:center;"><i
                                                    class="fas fa-times"></i> Reject</button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="grid-column: span 3; text-align: center; padding: 100px; color: var(--text-muted);">
                            <i class="fas fa-umbrella-beach"
                                style="font-size: 3rem; opacity: 0.2; margin-bottom: 20px; display: block;"></i>
                            <p style="font-weight: 700;">No leave requests found for the selected criteria.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>

</html>