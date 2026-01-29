<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Set default user for demo
$_SESSION['user_name'] = 'Wereda Health Officer';
$_SESSION['user_id'] = 4;
$_SESSION['role'] = 'wereda_health_officer';

// Create database connection
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'pending';
$leave_type_filter = $_GET['leave_type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query for leave requests in this wereda
$where_conditions = ["e.woreda = 'Wereda 1'"]; // In real system, get from user session
$params = [];
$param_types = '';

if ($status_filter !== 'all') {
    $where_conditions[] = "lr.status = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

if (!empty($leave_type_filter)) {
    $where_conditions[] = "lr.leave_type = ?";
    $params[] = $leave_type_filter;
    $param_types .= 's';
}

if (!empty($date_from)) {
    $where_conditions[] = "lr.start_date >= ?";
    $params[] = $date_from;
    $param_types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "lr.end_date <= ?";
    $params[] = $date_to;
    $param_types .= 's';
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

$sql = "SELECT lr.*, e.first_name, e.last_name, e.position, e.department_assigned, e.kebele, u.name as approved_by_name,
               le.annual_leave_days, le.used_annual_leave, le.sick_leave_days, le.used_sick_leave,
               le.maternity_leave_days, le.used_maternity_leave, le.paternity_leave_days, le.used_paternity_leave,
               le.emergency_leave_days, le.used_emergency_leave
        FROM leave_requests lr 
        JOIN employees e ON lr.employee_id = e.employee_id 
        LEFT JOIN users u ON lr.approved_by = u.id 
        LEFT JOIN leave_entitlements le ON e.employee_id = le.employee_id AND le.year = YEAR(CURDATE())
        $where_clause 
        ORDER BY lr.created_at DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

$leave_requests = [];
while ($row = $result->fetch_assoc()) {
    $leave_requests[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Leave Requests - Wereda Health Officer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        .content {
            padding: 30px;
        }

        .filters-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .filters-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .filter-group input,
        .filter-group select {
            padding: 10px 12px;
            border: 2px solid #e1e8ed;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            align-items: end;
        }

        .filter-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-btn.primary {
            background: #3498db;
            color: white;
        }

        .filter-btn.secondary {
            background: #6c757d;
            color: white;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .requests-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .request-card {
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .request-card:hover {
            border-color: #3498db;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.15);
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .employee-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .employee-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3498db, #2980b9);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
        }

        .employee-details h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
        }

        .employee-details p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
        }

        .leave-type-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .leave-annual { background: #e3f2fd; color: #1565c0; }
        .leave-sick { background: #ffebee; color: #c62828; }
        .leave-maternity { background: #f3e5f5; color: #7b1fa2; }
        .leave-paternity { background: #e8f5e8; color: #2e7d32; }
        .leave-emergency { background: #fff3e0; color: #ef6c00; }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .request-dates {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .date-item {
            text-align: center;
        }

        .date-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .date-value {
            font-weight: 600;
            color: #2c3e50;
        }

        .leave-balance {
            background: #e8f5e8;
            border: 1px solid #27ae60;
            border-radius: 8px;
            padding: 10px;
            margin: 15px 0;
            font-size: 14px;
        }

        .balance-insufficient {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }

        .request-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .action-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .approve-btn {
            background: #27ae60;
            color: white;
        }

        .reject-btn {
            background: #e74c3c;
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .action-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #3498db;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-weight: 600;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 12px;
            width: 80%;
            max-width: 500px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        @media (max-width: 768px) {
            .filters-row {
                grid-template-columns: 1fr;
            }
            
            .request-header {
                flex-direction: column;
                gap: 15px;
            }
            
            .request-dates {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .request-actions {
                flex-direction: column;
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
                    <span class="logo-text">HealthFirst HO</span>
                </a>
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>

            <nav class="sidebar-menu">
                <ul>
                    <li class="menu-item">
                        <a href="wereda_ho_dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="menu-text">HO Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item active">
                        <a href="leave_requests.php">
                            <i class="fas fa-umbrella-beach"></i>
                            <span class="menu-text">Leave Requests</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="wereda_ho_patients.php">
                            <i class="fas fa-user-injured"></i>
                            <span class="menu-text">Patients</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="wereda_ho_reports.php">
                            <i class="fas fa-chart-bar"></i>
                            <span class="menu-text">Reports</span>
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
                    <h1 class="page-title">Leave Requests - Wereda Health Officer</h1>
                </div>

                <div class="header-actions">
                    <button class="btn-secondary" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <!-- Statistics -->
                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count(array_filter($leave_requests, fn($r) => $r['status'] === 'pending')); ?></div>
                        <div class="stat-label">Pending Approval</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count(array_filter($leave_requests, fn($r) => $r['status'] === 'approved')); ?></div>
                        <div class="stat-label">Approved</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count(array_filter($leave_requests, fn($r) => $r['status'] === 'rejected')); ?></div>
                        <div class="stat-label">Rejected</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($leave_requests); ?></div>
                        <div class="stat-label">Total Requests</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filters-section">
                    <h3 style="margin-bottom: 20px; color: #2c3e50;">
                        <i class="fas fa-filter"></i> Filter Leave Requests
                    </h3>
                    <form method="GET">
                        <div class="filters-row">
                            <div class="filter-group">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="leave_type">Leave Type</label>
                                <select id="leave_type" name="leave_type">
                                    <option value="">All Types</option>
                                    <option value="annual" <?php echo $leave_type_filter === 'annual' ? 'selected' : ''; ?>>Annual</option>
                                    <option value="sick" <?php echo $leave_type_filter === 'sick' ? 'selected' : ''; ?>>Sick</option>
                                    <option value="maternity" <?php echo $leave_type_filter === 'maternity' ? 'selected' : ''; ?>>Maternity</option>
                                    <option value="paternity" <?php echo $leave_type_filter === 'paternity' ? 'selected' : ''; ?>>Paternity</option>
                                    <option value="emergency" <?php echo $leave_type_filter === 'emergency' ? 'selected' : ''; ?>>Emergency</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="date_from">From Date</label>
                                <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                            </div>
                            <div class="filter-group">
                                <label for="date_to">To Date</label>
                                <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                            </div>
                            <div class="filter-actions">
                                <button type="submit" class="filter-btn primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <button type="button" class="filter-btn secondary" onclick="window.location.href='leave_requests.php'">
                                    <i class="fas fa-times"></i> Clear
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Leave Requests -->
                <div class="requests-section">
                    <h3 style="margin-bottom: 20px; color: #2c3e50;">
                        <i class="fas fa-list"></i> Leave Requests
                    </h3>
                    
                    <?php if (empty($leave_requests)): ?>
                        <div style="text-align: center; padding: 40px; color: #6c757d;">
                            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.5;"></i>
                            <p>No leave requests found matching your criteria.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($leave_requests as $request): 
                            $initials = strtoupper(substr($request['first_name'], 0, 1) . substr($request['last_name'], 0, 1));
                            
                            // Calculate leave balance
                            $field_map = [
                                'annual' => ['entitled' => 'annual_leave_days', 'used' => 'used_annual_leave'],
                                'sick' => ['entitled' => 'sick_leave_days', 'used' => 'used_sick_leave'],
                                'maternity' => ['entitled' => 'maternity_leave_days', 'used' => 'used_maternity_leave'],
                                'paternity' => ['entitled' => 'paternity_leave_days', 'used' => 'used_paternity_leave'],
                                'emergency' => ['entitled' => 'emergency_leave_days', 'used' => 'used_emergency_leave']
                            ];
                            
                            $leave_type = $request['leave_type'];
                            $entitled = $request[$field_map[$leave_type]['entitled']] ?? 0;
                            $used = $request[$field_map[$leave_type]['used']] ?? 0;
                            $remaining = $entitled - $used;
                            $sufficient_balance = $request['days_requested'] <= $remaining;
                        ?>
                            <div class="request-card">
                                <div class="request-header">
                                    <div class="employee-info">
                                        <div class="employee-avatar"><?php echo $initials; ?></div>
                                        <div class="employee-details">
                                            <h4><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></h4>
                                            <p><?php echo htmlspecialchars($request['employee_id']); ?> • <?php echo htmlspecialchars($request['position'] ?? 'N/A'); ?></p>
                                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($request['kebele'] ?? 'N/A'); ?></p>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <span class="leave-type-badge leave-<?php echo $request['leave_type']; ?>">
                                            <?php echo ucfirst($request['leave_type']); ?> Leave
                                        </span>
                                        <br><br>
                                        <span class="status-badge status-<?php echo $request['status']; ?>">
                                            <?php echo ucfirst($request['status']); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="request-dates">
                                    <div class="date-item">
                                        <div class="date-label">Start Date</div>
                                        <div class="date-value"><?php echo date('M d, Y', strtotime($request['start_date'])); ?></div>
                                    </div>
                                    <div class="date-item">
                                        <div class="date-label">End Date</div>
                                        <div class="date-value"><?php echo date('M d, Y', strtotime($request['end_date'])); ?></div>
                                    </div>
                                    <div class="date-item">
                                        <div class="date-label">Days Requested</div>
                                        <div class="date-value"><?php echo $request['days_requested']; ?></div>
                                    </div>
                                    <div class="date-item">
                                        <div class="date-label">Requested On</div>
                                        <div class="date-value"><?php echo date('M d, Y', strtotime($request['created_at'])); ?></div>
                                    </div>
                                </div>

                                <?php if ($entitled > 0): ?>
                                    <div class="leave-balance <?php echo $sufficient_balance ? '' : 'balance-insufficient'; ?>">
                                        <strong>Leave Balance:</strong> 
                                        <?php echo $remaining; ?> days remaining out of <?php echo $entitled; ?> entitled 
                                        (<?php echo $used; ?> used)
                                        <?php if (!$sufficient_balance): ?>
                                            <br><strong>⚠️ Insufficient balance for this request!</strong>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($request['reason'])): ?>
                                    <div style="margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 6px;">
                                        <strong>Reason:</strong> <?php echo htmlspecialchars($request['reason']); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($request['status'] === 'pending'): ?>
                                    <div class="request-actions">
                                        <button class="action-btn approve-btn" onclick="approveRequest(<?php echo $request['id']; ?>, '<?php echo addslashes($request['first_name'] . ' ' . $request['last_name']); ?>')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button class="action-btn reject-btn" onclick="rejectRequest(<?php echo $request['id']; ?>, '<?php echo addslashes($request['first_name'] . ' ' . $request['last_name']); ?>')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                <?php elseif ($request['approved_by_name']): ?>
                                    <div style="margin-top: 15px; padding: 10px; background: #e8f5e8; border-radius: 6px; font-size: 14px;">
                                        <strong><?php echo ucfirst($request['status']); ?> by:</strong> <?php echo htmlspecialchars($request['approved_by_name']); ?>
                                        <br>
                                        <strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($request['approved_at'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Approval Modal -->
    <div id="approvalModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Approve Leave Request</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div id="modalBody">
                <p>Are you sure you want to approve this leave request for <strong id="modalEmployeeName"></strong>?</p>
                <div style="margin: 15px 0;">
                    <label for="approvalComments">Comments (Optional):</label>
                    <textarea id="approvalComments" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;"></textarea>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button onclick="closeModal()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                    <button id="confirmApproval" style="padding: 10px 20px; background: #27ae60; color: white; border: none; border-radius: 4px; cursor: pointer;">Approve</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div id="rejectionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Reject Leave Request</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div id="modalBody">
                <p>Are you sure you want to reject this leave request for <strong id="modalEmployeeNameReject"></strong>?</p>
                <div style="margin: 15px 0;">
                    <label for="rejectionComments">Reason for Rejection:</label>
                    <textarea id="rejectionComments" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;" required></textarea>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button onclick="closeModal()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                    <button id="confirmRejection" style="padding: 10px 20px; background: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer;">Reject</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentLeaveId = null;

        // Sidebar toggle
        document.getElementById('toggleSidebar').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });

        document.getElementById('mobileMenuBtn').addEventListener('click', () => {
            document.getElementById('sidebar').classList.add('mobile-open');
            document.getElementById('mobileOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        function approveRequest(leaveId, employeeName) {
            currentLeaveId = leaveId;
            document.getElementById('modalEmployeeName').textContent = employeeName;
            document.getElementById('approvalModal').style.display = 'block';
        }

        function rejectRequest(leaveId, employeeName) {
            currentLeaveId = leaveId;
            document.getElementById('modalEmployeeNameReject').textContent = employeeName;
            document.getElementById('rejectionModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('approvalModal').style.display = 'none';
            document.getElementById('rejectionModal').style.display = 'none';
            currentLeaveId = null;
            document.getElementById('approvalComments').value = '';
            document.getElementById('rejectionComments').value = '';
        }

        document.getElementById('confirmApproval').addEventListener('click', function() {
            const comments = document.getElementById('approvalComments').value;
            processLeaveRequest('approve', comments);
        });

        document.getElementById('confirmRejection').addEventListener('click', function() {
            const comments = document.getElementById('rejectionComments').value;
            if (!comments.trim()) {
                alert('Please provide a reason for rejection');
                return;
            }
            processLeaveRequest('reject', comments);
        });

        function processLeaveRequest(action, comments) {
            if (!currentLeaveId) return;

            fetch('approve_leave_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    leave_id: currentLeaveId, 
                    action: action,
                    comments: comments
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`✅ Leave request ${data.action} successfully!\nEmployee: ${data.employee_name}\nLeave Type: ${data.leave_type}\nDays: ${data.days}`);
                    window.location.reload();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Failed to process leave request');
            })
            .finally(() => {
                closeModal();
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const approvalModal = document.getElementById('approvalModal');
            const rejectionModal = document.getElementById('rejectionModal');
            if (event.target == approvalModal) {
                approvalModal.style.display = 'none';
            }
            if (event.target == rejectionModal) {
                rejectionModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>