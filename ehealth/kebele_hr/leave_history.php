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

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$employee_filter = $_GET['employee'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$where_conditions = [];
$params = [];
$param_types = '';

if ($status_filter !== 'all') {
    $where_conditions[] = "lr.status = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

if (!empty($employee_filter)) {
    $where_conditions[] = "(e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_id LIKE ?)";
    $search_term = "%$employee_filter%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types .= 'sss';
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

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

$sql = "SELECT lr.*, e.first_name, e.last_name, e.position, e.department_assigned, u.name as approved_by_name 
        FROM leave_requests lr 
        JOIN employees e ON lr.employee_id = e.employee_id 
        LEFT JOIN users u ON lr.approved_by = u.id 
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

// Get employees for filter dropdown
$employees_result = $conn->query("SELECT DISTINCT employee_id, first_name, last_name FROM employees ORDER BY first_name, last_name");
$employees = [];
while ($row = $employees_result->fetch_assoc()) {
    $employees[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Leave History</title>
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

        .history-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .history-table th,
        .history-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e1e8ed;
        }

        .history-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        .history-table tr:hover {
            background: #f8f9fa;
        }

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

        .leave-type-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .leave-annual { background: #e3f2fd; color: #1565c0; }
        .leave-sick { background: #ffebee; color: #c62828; }
        .leave-maternity { background: #f3e5f5; color: #7b1fa2; }
        .leave-paternity { background: #e8f5e8; color: #2e7d32; }
        .leave-emergency { background: #fff3e0; color: #ef6c00; }

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

        @media (max-width: 768px) {
            .filters-row {
                grid-template-columns: 1fr;
            }
            
            .history-table {
                font-size: 14px;
            }
            
            .history-table th,
            .history-table td {
                padding: 10px 8px;
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
                    <h1 class="page-title">Leave History</h1>
                </div>

                <div class="header-actions">
                    <button class="btn-primary" onclick="window.location.href='submit_leave_request.php'">
                        <i class="fas fa-plus"></i> New Request
                    </button>
                    <button class="btn-secondary" onclick="window.location.href='hr-leave.html'">
                        <i class="fas fa-arrow-left"></i> Back to Leave Management
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <!-- Statistics -->
                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count(array_filter($leave_requests, fn($r) => $r['status'] === 'pending')); ?></div>
                        <div class="stat-label">Pending</div>
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
                                <label for="employee">Employee</label>
                                <input type="text" id="employee" name="employee" placeholder="Search by name or ID..." value="<?php echo htmlspecialchars($employee_filter); ?>">
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
                                <button type="button" class="filter-btn secondary" onclick="window.location.href='leave_history.php'">
                                    <i class="fas fa-times"></i> Clear
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- History Table -->
                <div class="history-section">
                    <h3 style="margin-bottom: 20px; color: #2c3e50;">
                        <i class="fas fa-history"></i> Leave Requests History
                    </h3>
                    
                    <?php if (empty($leave_requests)): ?>
                        <div style="text-align: center; padding: 40px; color: #6c757d;">
                            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.5;"></i>
                            <p>No leave requests found matching your criteria.</p>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="history-table">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Leave Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Days</th>
                                        <th>Status</th>
                                        <th>Approved By</th>
                                        <th>Requested On</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($leave_requests as $request): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></strong>
                                                    <br>
                                                    <small style="color: #6c757d;"><?php echo htmlspecialchars($request['employee_id']); ?></small>
                                                    <br>
                                                    <small style="color: #6c757d;"><?php echo htmlspecialchars($request['position'] ?? 'N/A'); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="leave-type-badge leave-<?php echo $request['leave_type']; ?>">
                                                    <?php echo ucfirst($request['leave_type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($request['start_date'])); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($request['end_date'])); ?></td>
                                            <td><strong><?php echo $request['days_requested']; ?></strong></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $request['status']; ?>">
                                                    <?php echo ucfirst($request['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($request['approved_by_name']): ?>
                                                    <?php echo htmlspecialchars($request['approved_by_name']); ?>
                                                    <br>
                                                    <small style="color: #6c757d;">
                                                        <?php echo $request['approved_at'] ? date('M d, Y', strtotime($request['approved_at'])) : ''; ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span style="color: #6c757d;">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($request['created_at'])); ?>
                                                <br>
                                                <small style="color: #6c757d;">
                                                    <?php echo date('H:i', strtotime($request['created_at'])); ?>
                                                </small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Sidebar toggle
        document.getElementById('toggleSidebar').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });

        document.getElementById('mobileMenuBtn').addEventListener('click', () => {
            document.getElementById('sidebar').classList.add('mobile-open');
            document.getElementById('mobileOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    </script>
</body>
</html>