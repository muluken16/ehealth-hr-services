<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'wereda_health_officer') {
    header('Location: ../index.html');
    exit();
}

include '../db.php';
$conn = getDBConnection();
$user_woreda = $_SESSION['woreda'] ?? 'West Shewa Woreda 1';
$user_woreda_escaped = $conn->real_escape_string($user_woreda);

// Handle filters
$search = $_GET['search'] ?? '';
$kebele_filter = $_GET['kebele'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build employee query
$emp_where = "WHERE woreda = '$user_woreda_escaped'";
if ($search) {
    $search_esc = $conn->real_escape_string($search);
    $emp_where .= " AND (first_name LIKE '%$search_esc%' OR last_name LIKE '%$search_esc%' OR employee_id LIKE '%$search_esc%')";
}
if ($kebele_filter) {
    $kebele_esc = $conn->real_escape_string($kebele_filter);
    $emp_where .= " AND kebele = '$kebele_esc'";
}

// Get employees
$employees = $conn->query("SELECT * FROM employees $emp_where ORDER BY kebele, first_name");

// Get kebeles for filter
$kebeles = $conn->query("SELECT DISTINCT kebele FROM employees WHERE woreda = '$user_woreda' ORDER BY kebele");

// Get statistics
$total_employees = $conn->query("SELECT COUNT(*) as count FROM employees WHERE woreda = '$user_woreda_escaped'")->fetch_assoc()['count'];
$active_employees = $conn->query("SELECT COUNT(*) as count FROM employees WHERE woreda = '$user_woreda_escaped' AND status = 'active'")->fetch_assoc()['count'];
$total_kebeles = $conn->query("SELECT COUNT(DISTINCT kebele) as count FROM employees WHERE woreda = '$user_woreda_escaped'")->fetch_assoc()['count'];

// Get attendance data (simulated - would need attendance table)
$today = date('Y-m-d');
$attendance_data = $conn->query("
    SELECT e.employee_id, e.first_name, e.last_name, e.position, e.kebele, 
           COALESCE(a.status, 'absent') as status, 
           COALESCE(a.check_in, '08:00:00') as check_in,
           COALESCE(a.check_out, '17:00:00') as check_out,
           COALESCE(a.working_hours, 8) as working_hours
    FROM employees e
    LEFT JOIN attendance a ON e.employee_id = a.employee_id AND a.date = '$today'
    WHERE e.woreda = '$user_woreda'
");

// Weekly attendance stats
$weekly_stats = $conn->query("
    SELECT 
        COALESCE(a.status, 'absent') as status,
        COUNT(*) as count
    FROM employees e
    LEFT JOIN attendance a ON e.employee_id = a.employee_id AND a.date >= DATE_SUB('$today', INTERVAL 7 DAY)
    WHERE e.woreda = '$user_woreda'
    GROUP BY a.status
");

// Attendance by kebele
$attendance_by_kebele = $conn->query("
    SELECT e.kebele,
           COUNT(*) as total,
           SUM(CASE WHEN COALESCE(a.status, 'absent') = 'present' THEN 1 ELSE 0 END) as present,
           SUM(CASE WHEN COALESCE(a.status, 'absent') = 'absent' THEN 1 ELSE 0 END) as absent,
           SUM(CASE WHEN COALESCE(a.status, 'absent') = 'late' THEN 1 ELSE 0 END) as late
    FROM employees e
    LEFT JOIN attendance a ON e.employee_id = a.employee_id AND a.date = '$today'
    WHERE e.woreda = '$user_woreda'
    GROUP BY e.kebele
");

// Monthly attendance trends (simulated)
$monthly_trends = $conn->query("
    SELECT 
        DAYOFWEEK(a.date) as day_of_week,
        AVG(COALESCE(a.working_hours, 0)) as avg_hours
    FROM employees e
    LEFT JOIN attendance a ON e.employee_id = a.employee_id
    WHERE e.woreda = '$user_woreda' AND a.date IS NOT NULL
    GROUP BY DAYOFWEEK(a.date)
");

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 20;
$total_pages = ceil($employees->num_rows / $per_page);
$offset = ($page - 1) * $per_page;
$paginated_sql = "SELECT * FROM employees $emp_where ORDER BY kebele, first_name LIMIT $offset, $per_page";
$employees_paginated = $conn->query($paginated_sql);

// Chart data preparation
$present_count = 0;
$absent_count = 0;
$late_count = 0;
$leave_count = 0;

$weekly_stats->data_seek(0);
while ($row = $weekly_stats->fetch_assoc()) {
    switch ($row['status']) {
        case 'present':
            $present_count = $row['count'];
            break;
        case 'absent':
            $absent_count = $row['count'];
            break;
        case 'late':
            $late_count = $row['count'];
            break;
        case 'leave':
            $leave_count = $row['count'];
            break;
    }
}

$kebele_labels = [];
$kebele_present = [];
$kebele_absent = [];
$attendance_by_kebele->data_seek(0);
while ($row = $attendance_by_kebele->fetch_assoc()) {
    $kebele_labels[] = $row['kebele'];
    $kebele_present[] = $row['present'];
    $kebele_absent[] = $row['absent'];
}

// Payroll stats
$payroll_stats = $conn->query("SELECT COUNT(*) as total, COALESCE(SUM(salary), 0) as total_salary, COALESCE(AVG(salary), 0) as avg_salary FROM employees WHERE woreda = '$user_woreda'")->fetch_assoc();

// Position distribution for charts
$position_stats = $conn->query("SELECT position, COUNT(*) as count FROM employees WHERE woreda = '$user_woreda' AND position IS NOT NULL GROUP BY position ORDER BY count DESC LIMIT 10");
$position_labels = [];
$position_counts = [];
while ($row = $position_stats->fetch_assoc()) {
    $position_labels[] = $row['position'] ?: 'Not Specified';
    $position_counts[] = $row['count'];
}

// Initialize color arrays for charts
$gender_colors = ['Male' => '#4a90d9', 'Female' => '#e74c3c', 'Not Specified' => '#95a5a6'];
$status_colors = ['active' => '#27ae60', 'inactive' => '#e74c3c', 'on_leave' => '#f39c12'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Kebele Employees, Payroll & Attendance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/styleho.css">
    <style>
        .page-container {
            padding: 20px;
        }

        .page-header {
            margin-bottom: 25px;
        }

        .page-header h1 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 5px;
        }

        .page-header p {
            color: #666;
        }

        /* Tabs */
        .tabs-container {
            margin-bottom: 25px;
        }

        .tabs {
            display: flex;
            gap: 5px;
            background: #fff;
            padding: 5px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            flex-wrap: wrap;
        }

        .tab {
            padding: 12px 20px;
            border: none;
            background: none;
            cursor: pointer;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #666;
            transition: all 0.3s;
        }

        .tab:hover {
            background: #f8f9fa;
        }

        .tab.active {
            background: #4a90d9;
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card-small {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-card-small .icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-card-small .icon.blue {
            background: rgba(74, 144, 217, 0.1);
            color: #4a90d9;
        }

        .stat-card-small .icon.green {
            background: rgba(39, 174, 96, 0.1);
            color: #27ae60;
        }

        .stat-card-small .icon.orange {
            background: rgba(243, 156, 18, 0.1);
            color: #f39c12;
        }

        .stat-card-small .icon.red {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .stat-card-small .icon.purple {
            background: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
        }

        .stat-card-small .icon.teal {
            background: rgba(26, 188, 156, 0.1);
            color: #1abc9c;
        }

        .stat-card-small .info h3 {
            font-size: 1.5rem;
            margin: 0;
            color: #333;
        }

        .stat-card-small .info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }

        /* Charts Section */
        .charts-section {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 25px;
        }

        .charts-section h2 {
            margin: 0 0 20px 0;
            font-size: 1.3rem;
            color: #333;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }

        .chart-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
        }

        .chart-card h3 {
            margin: 0 0 15px 0;
            font-size: 1rem;
            color: #555;
        }

        .chart-container {
            position: relative;
            height: 250px;
        }

        .chart-container.small {
            height: 200px;
        }

        /* Filters */
        .filters-section {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .filters-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 150px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
            font-size: 0.9rem;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn-search {
            background: #4a90d9;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-reset {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        /* Tables */
        .data-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 25px;
        }

        .card-header-custom {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .card-header-custom h2 {
            margin: 0;
            font-size: 1.2rem;
            color: #333;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-export {
            background: #27ae60;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-export:hover {
            background: #219a52;
        }

        .btn-action {
            background: #4a90d9;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-action:hover {
            background: #357abd;
        }

        .btn-attendance {
            background: #f39c12;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-payroll {
            background: #9b59b6;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
            white-space: nowrap;
        }

        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
            font-size: 0.9rem;
        }

        .data-table tr:hover {
            background: #f8f9fa;
        }

        .data-table .actions {
            display: flex;
            gap: 5px;
        }

        .action-btn {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            color: white;
        }

        .action-btn.view {
            background: #4a90d9;
        }

        .action-btn.edit {
            background: #27ae60;
        }

        .action-btn.payroll {
            background: #9b59b6;
        }

        .action-btn.attendance {
            background: #f39c12;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-badge.active {
            background: rgba(39, 174, 96, 0.1);
            color: #27ae60;
        }

        .status-badge.inactive {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .status-badge.present {
            background: rgba(39, 174, 96, 0.1);
            color: #27ae60;
        }

        .status-badge.absent {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .status-badge.late {
            background: rgba(243, 156, 18, 0.1);
            color: #f39c12;
        }

        .status-badge.on_leave {
            background: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
        }

        .attendance-status {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .attendance-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .attendance-dot.present {
            background: #27ae60;
        }

        .attendance-dot.absent {
            background: #e74c3c;
        }

        .attendance-dot.late {
            background: #f39c12;
        }

        .attendance-dot.leave {
            background: #9b59b6;
        }

        .employee-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #4a90d9;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }
        }

        .employee-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Premium Side Panel CSS */
        .side-panel {
            position: fixed;
            right: -500px;
            top: 0;
            width: 450px;
            height: 100vh;
            background: white;
            z-index: 2000;
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            padding-bottom: 30px;
        }

        .side-panel.open {
            right: 0;
        }

        .side-panel-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            z-index: 1999;
            display: none;
        }

        .side-panel-overlay.active {
            display: block;
        }

        .side-panel-header {
            padding: 30px;
            background: #f8fafc;
            border-bottom: 1.5px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .side-panel-close {
            font-size: 1.5rem;
            cursor: pointer;
            color: #64748b;
            transition: color 0.2s;
        }

        .side-panel-close:hover {
            color: #f43f5e;
        }

        .side-panel-tabs {
            display: flex;
            padding: 0 30px;
            background: white;
            border-bottom: 1px solid #f1f5f9;
            position: sticky;
            top: 88px;
            z-index: 10;
        }

        .side-tab {
            padding: 15px 20px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            transition: all 0.3s;
            border-bottom: 2px solid transparent;
        }

        .side-tab:hover {
            color: #1a4a5f;
        }

        .side-tab.active {
            color: #1a4a5f;
            border-bottom-color: #1a4a5f;
            background: #f0f7ff;
        }

        .side-panel-body {
            padding: 30px;
        }

        .profile-header-premium {
            text-align: center;
            margin-bottom: 25px;
            background: linear-gradient(135deg, #1a4a5f 0%, #2a6e8c 100%);
            color: white;
            padding: 40px 20px;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
        }

        .avatar-lg {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
            margin: 0 auto 15px;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .info-item {
            background: #f8fafc;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #f1f5f9;
        }

        .info-label {
            font-size: 0.7rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .info-value {
            font-size: 0.9rem;
            color: #1e293b;
            font-weight: 600;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        /* Pagination */
        .pagination {
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #eee;
            flex-wrap: wrap;
            gap: 10px;
        }

        .pagination-info {
            color: #666;
        }

        .pagination-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .pagination-btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
        }

        .pagination-btn:hover {
            background: #f8f9fa;
        }

        .pagination-btn.active {
            background: #4a90d9;
            color: white;
            border-color: #4a90d9;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }

        /* Payroll Stats */
        .payroll-stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .payroll-stats .icon {
            background: rgba(255, 255, 255, 0.2) !important;
            color: white !important;
        }

        .payroll-stats .info h3 {
            color: white !important;
        }

        .payroll-stats .info p {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        .salary-positive {
            color: #27ae60;
            font-weight: 600;
        }

        /* Section title */
        .section-title {
            font-size: 1.1rem;
            color: #333;
            margin: 20px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        /* Calendar attendance */
        .attendance-calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            margin-top: 15px;
        }

        .calendar-day {
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            font-size: 12px;
        }

        .calendar-day.present {
            background: rgba(39, 174, 96, 0.2);
            color: #27ae60;
        }

        .calendar-day.absent {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
        }

        .calendar-day.leave {
            background: rgba(155, 89, 182, 0.2);
            color: #9b59b6;
        }

        .calendar-day.today {
            border: 2px solid #4a90d9;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="admin-container">
        <div class="mobile-overlay" id="mobileOverlay"></div>
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="header">
                <div class="header-left">
                    <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                    <h1 class="page-title">Kebele Employees, Payroll & Attendance</h1>
                </div>

                <div class="header-actions">
                    <button class="notification-btn"><i class="fas fa-bell"></i><span
                            class="notification-badge">5</span></button>

                    <div class="user-profile" id="userProfile">
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 2)); ?>
                        </div>
                        <div class="user-info">
                            <span
                                class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                            <span class="user-role">Wereda Health Officer</span>
                        </div>
                        <i class="fas fa-chevron-down"></i>
                        <div class="dropdown-menu" id="userDropdown">
                            <a href="#" class="dropdown-item"><i class="fas fa-user"></i> My Profile</a>
                            <a href="#" class="dropdown-item"><i class="fas fa-cog"></i> Account Settings</a>
                            <a href="../logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content">
                <div class="page-container">
                    <div class="page-header">
                        <h1><i class="fas fa-users-cog"></i> Management Dashboard</h1>
                        <p>Employees, payroll & attendance for kebeles in <?php echo htmlspecialchars($user_woreda); ?>
                        </p>
                    </div>

                    <!-- Tabs -->
                    <div class="tabs-container">
                        <div class="tabs">
                            <button class="tab active" onclick="showTab('employees')"><i class="fas fa-users"></i>
                                Employees</button>
                            <button class="tab" onclick="showTab('attendance')"><i class="fas fa-clock"></i>
                                Attendance</button>
                            <button class="tab" onclick="showTab('payroll')"><i class="fas fa-money-bill-wave"></i>
                                Payroll</button>
                            <button class="tab" onclick="showTab('analytics')"><i class="fas fa-chart-pie"></i>
                                Analytics</button>
                        </div>
                    </div>

                    <!-- Employees Tab -->
                    <div id="employeesTab" class="tab-content active">
                        <div class="stats-cards">
                            <div class="stat-card-small">
                                <div class="icon blue"><i class="fas fa-users"></i></div>
                                <div class="info">
                                    <h3><?php echo number_format($total_employees); ?></h3>
                                    <p>Total Employees</p>
                                </div>
                            </div>
                            <div class="stat-card-small">
                                <div class="icon green"><i class="fas fa-user-check"></i></div>
                                <div class="info">
                                    <h3><?php echo number_format($active_employees); ?></h3>
                                    <p>Active</p>
                                </div>
                            </div>
                            <div class="stat-card-small">
                                <div class="icon orange"><i class="fas fa-user-clock"></i></div>
                                <div class="info">
                                    <h3><?php echo $total_employees - $active_employees; ?></h3>
                                    <p>Inactive</p>
                                </div>
                            </div>
                            <div class="stat-card-small">
                                <div class="icon purple"><i class="fas fa-building"></i></div>
                                <div class="info">
                                    <h3><?php echo $total_kebeles; ?></h3>
                                    <p>Kebeles</p>
                                </div>
                            </div>
                        </div>

                        <div class="filters-section">
                            <form method="GET" action="">
                                <div class="filters-row">
                                    <div class="filter-group">
                                        <label>Search</label>
                                        <input type="text" name="search" placeholder="Name or ID..."
                                            value="<?php echo htmlspecialchars($search); ?>">
                                    </div>
                                    <div class="filter-group">
                                        <label>Kebele</label>
                                        <select name="kebele">
                                            <option value="">All Kebeles</option>
                                            <?php
                                            $kebeles->data_seek(0);
                                            while ($k = $kebeles->fetch_assoc()): ?>
                                                <option value="<?php echo htmlspecialchars($k['kebele']); ?>" <?php echo ($kebele_filter === $k['kebele']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($k['kebele']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label>Status</label>
                                        <select name="status">
                                            <option value="">All Status</option>
                                            <option value="active" <?php echo ($status_filter === 'active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo ($status_filter === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn-search"><i class="fas fa-search"></i>
                                            Search</button>
                                    </div>
                                    <div class="filter-group">
                                        <label>&nbsp;</label>
                                        <a href="wereda_ho_employees.php" class="btn-reset"><i class="fas fa-sync"></i>
                                            Reset</a>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="data-card">
                            <div class="card-header-custom">
                                <h2><i class="fas fa-list"></i> Employee List</h2>
                                <div class="header-actions">
                                    <button class="btn-action" onclick="openModal('addModal')"><i
                                            class="fas fa-plus"></i> Add</button>
                                    <button class="btn-export" onclick="exportData('employees')"><i
                                            class="fas fa-download"></i> Export</button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>ID</th>
                                            <th>Position</th>
                                            <th>Kebele</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($employees_paginated && $employees_paginated->num_rows > 0): ?>
                                            <?php while ($emp = $employees_paginated->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <div class="employee-cell">
                                                            <div class="employee-avatar">
                                                                <?php echo strtoupper(substr($emp['first_name'] ?? 'U', 0, 1) . substr($emp['last_name'] ?? '', 0, 1)); ?>
                                                            </div>
                                                            <?php echo htmlspecialchars(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? '')); ?>
                                                        </div>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($emp['employee_id'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($emp['position'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($emp['kebele'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($emp['phone'] ?? 'N/A'); ?></td>
                                                    <td><span
                                                            class="status-badge <?php echo $emp['status'] ?? 'inactive'; ?>"><?php echo ucfirst($emp['status'] ?? 'Inactive'); ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="actions">
                                                            <button class="action-btn view"
                                                                onclick="viewEmployee('<?php echo htmlspecialchars($emp['employee_id'] ?? ''); ?>')"><i
                                                                    class="fas fa-eye"></i></button>
                                                            <button class="action-btn attendance"
                                                                onclick="viewAttendance('<?php echo htmlspecialchars($emp['employee_id'] ?? ''); ?>')"><i
                                                                    class="fas fa-clock"></i></button>
                                                            <button class="action-btn payroll"
                                                                onclick="viewPayroll('<?php echo htmlspecialchars($emp['employee_id'] ?? ''); ?>')"><i
                                                                    class="fas fa-money-bill"></i></button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="empty-state"><i class="fas fa-users"></i>
                                                    <p>No employees found</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if ($total_pages > 1): ?>
                                <div class="pagination">
                                    <div class="pagination-info">Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                                    </div>
                                    <div class="pagination-buttons">
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&kebele=<?php echo urlencode($kebele_filter); ?>"
                                                class="pagination-btn <?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Attendance Tab -->
                    <div id="attendanceTab" class="tab-content">
                        <div class="stats-cards">
                            <div class="stat-card-small">
                                <div class="icon green"><i class="fas fa-check-circle"></i></div>
                                <div class="info">
                                    <h3 id="presentCount"><?php echo $present_count; ?></h3>
                                    <p>Present Today</p>
                                </div>
                            </div>
                            <div class="stat-card-small">
                                <div class="icon red"><i class="fas fa-times-circle"></i></div>
                                <div class="info">
                                    <h3 id="absentCount"><?php echo $absent_count; ?></h3>
                                    <p>Absent Today</p>
                                </div>
                            </div>
                            <div class="stat-card-small">
                                <div class="icon orange"><i class="fas fa-clock"></i></div>
                                <div class="info">
                                    <h3 id="lateCount"><?php echo $late_count; ?></h3>
                                    <p>Late Today</p>
                                </div>
                            </div>
                            <div class="stat-card-small">
                                <div class="icon purple"><i class="fas fa-calendar-minus"></i></div>
                                <div class="info">
                                    <h3 id="leaveCount"><?php echo $leave_count; ?></h3>
                                    <p>On Leave</p>
                                </div>
                            </div>
                        </div>

                        <div class="data-card">
                            <div class="card-header-custom">
                                <h2><i class="fas fa-clock"></i> Today's Attendance - <?php echo date('F j, Y'); ?></h2>
                                <div class="header-actions">
                                    <button class="btn-attendance" onclick="markAttendance()"><i
                                            class="fas fa-check"></i> Mark Attendance</button>
                                    <button class="btn-export" onclick="exportAttendance()"><i
                                            class="fas fa-download"></i> Export</button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>ID</th>
                                            <th>Position</th>
                                            <th>Kebele</th>
                                            <th>Check In</th>
                                            <th>Check Out</th>
                                            <th>Hours</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($attendance_data && $attendance_data->num_rows > 0): ?>
                                            <?php while ($att = $attendance_data->fetch_assoc()):
                                                $att_status = $att['status'];
                                                $status_class = $att_status === 'present' ? 'present' : ($att_status === 'late' ? 'late' : ($att_status === 'leave' ? 'on_leave' : 'absent'));
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="employee-cell">
                                                            <div class="employee-avatar">
                                                                <?php echo strtoupper(substr($att['first_name'] ?? 'U', 0, 1) . substr($att['last_name'] ?? '', 0, 1)); ?>
                                                            </div>
                                                            <?php echo htmlspecialchars(($att['first_name'] ?? '') . ' ' . ($att['last_name'] ?? '')); ?>
                                                        </div>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($att['employee_id'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($att['position'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($att['kebele'] ?? 'N/A'); ?></td>
                                                    <td><?php echo $att['check_in']; ?></td>
                                                    <td><?php echo $att['check_out']; ?></td>
                                                    <td><?php echo $att['working_hours']; ?> hrs</td>
                                                    <td>
                                                        <div class="attendance-status">
                                                            <span
                                                                class="attendance-dot <?php echo $att_status === 'present' ? 'present' : ($att_status === 'late' ? 'late' : 'absent'); ?>"></span>
                                                            <span
                                                                class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($att_status); ?></span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="actions">
                                                            <button class="action-btn view"
                                                                onclick="viewAttendanceDetail('<?php echo htmlspecialchars($att['employee_id'] ?? ''); ?>')"><i
                                                                    class="fas fa-eye"></i></button>
                                                            <button class="action-btn edit"
                                                                onclick="editAttendance('<?php echo htmlspecialchars($att['employee_id'] ?? ''); ?>')"><i
                                                                    class="fas fa-edit"></i></button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="empty-state"><i class="fas fa-clock"></i>
                                                    <p>No attendance data for today</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="charts-section">
                            <h2><i class="fas fa-chart-bar"></i> Attendance Overview</h2>
                            <div class="charts-grid">
                                <div class="chart-card">
                                    <h3><i class="fas fa-map-marker-alt"></i> Attendance by Kebele</h3>
                                    <div class="chart-container"><canvas id="kebeleAttendanceChart"></canvas></div>
                                </div>
                                <div class="chart-card">
                                    <h3><i class="fas fa-chart-pie"></i> Today's Status</h3>
                                    <div class="chart-container small"><canvas id="attendanceStatusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payroll Tab -->
                    <div id="payrollTab" class="tab-content">
                        <div class="stats-cards">
                            <div class="stat-card-small payroll-stats">
                                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                                <div class="info">
                                    <h3><?php echo number_format($payroll_stats['total_salary'], 0); ?></h3>
                                    <p>Monthly Payroll</p>
                                </div>
                            </div>
                            <div class="stat-card-small">
                                <div class="icon green"><i class="fas fa-calculator"></i></div>
                                <div class="info">
                                    <h3><?php echo number_format($payroll_stats['avg_salary'], 0); ?></h3>
                                    <p>Avg Salary</p>
                                </div>
                            </div>
                            <div class="stat-card-small">
                                <div class="icon blue"><i class="fas fa-users"></i></div>
                                <div class="info">
                                    <h3><?php echo number_format($payroll_stats['total']); ?></h3>
                                    <p>Employees</p>
                                </div>
                            </div>
                            <div class="stat-card-small">
                                <div class="icon purple"><i class="fas fa-building"></i></div>
                                <div class="info">
                                    <h3><?php echo $total_kebeles; ?></h3>
                                    <p>Kebeles</p>
                                </div>
                            </div>
                        </div>

                        <div class="data-card">
                            <div class="card-header-custom">
                                <h2><i class="fas fa-money-bill-wave"></i> Payroll Summary</h2>
                                <div class="header-actions">
                                    <button class="btn-payroll" onclick="processPayroll()"><i class="fas fa-cog"></i>
                                        Process</button>
                                    <button class="btn-export" onclick="exportPayroll()"><i class="fas fa-download"></i>
                                        Export</button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>ID</th>
                                            <th>Position</th>
                                            <th>Kebele</th>
                                            <th>Basic Salary</th>
                                            <th>Net Pay</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $payroll_employees = $conn->query("SELECT * FROM employees WHERE woreda = '$user_woreda' AND salary > 0 ORDER BY salary DESC LIMIT 20");
                                        while ($p = $payroll_employees->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <div class="employee-cell">
                                                        <div class="employee-avatar">
                                                            <?php echo strtoupper(substr($p['first_name'] ?? 'U', 0, 1) . substr($p['last_name'] ?? '', 0, 1)); ?>
                                                        </div>
                                                        <?php echo htmlspecialchars(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')); ?>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($p['employee_id'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($p['position'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($p['kebele'] ?? 'N/A'); ?></td>
                                                <td class="salary-positive">
                                                    <?php echo number_format($p['salary'] ?? 0, 2); ?>
                                                </td>
                                                <td class="salary-positive">
                                                    <strong><?php echo number_format(($p['salary'] ?? 0) * 0.9, 2); ?></strong>
                                                </td>
                                                <td>
                                                    <div class="actions">
                                                        <button class="action-btn view"
                                                            onclick="viewPayrollSlip('<?php echo htmlspecialchars($p['employee_id'] ?? ''); ?>')"><i
                                                                class="fas fa-file-invoice"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics Tab -->
                    <div id="analyticsTab" class="tab-content">
                        <div class="charts-section">
                            <h2><i class="fas fa-chart-pie"></i> Analytics Overview</h2>
                            <div class="charts-grid">
                                <div class="chart-card">
                                    <h3><i class="fas fa-map-marker-alt"></i> Employees by Kebele</h3>
                                    <div class="chart-container"><canvas id="kebeleChart"></canvas></div>
                                </div>
                                <div class="chart-card">
                                    <h3><i class="fas fa-briefcase"></i> Positions</h3>
                                    <div class="chart-container"><canvas id="positionChart"></canvas></div>
                                </div>
                                <div class="chart-card">
                                    <h3><i class="fas fa-chart-bar"></i> Attendance Trends</h3>
                                    <div class="chart-container"><canvas id="trendsChart"></canvas></div>
                                </div>
                                <div class="chart-card">
                                    <h3><i class="fas fa-venus-mars"></i> Gender Distribution</h3>
                                    <div class="chart-container small"><canvas id="genderChart"></canvas></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Employee Side Panel (copied from Premium HR) -->
    <div class="side-panel-overlay" id="hoSidePanelOverlay" onclick="closeSidePanelHO()"></div>
    <div class="side-panel" id="hoEmployeeSidePanel">
        <div class="side-panel-header">
            <h2 id="hoSidePanelTitle">Employee Details</h2>
            <i class="fas fa-times side-panel-close" onclick="closeSidePanelHO()"></i>
        </div>
        <div class="side-panel-tabs">
            <div class="side-tab active" onclick="switchTabHO('general', event)">General</div>
            <div class="side-tab" onclick="switchTabHO('education', event)">Education</div>
            <div class="side-tab" onclick="switchTabHO('finance', event)">Finance</div>
            <div class="side-tab" onclick="switchTabHO('legal', event)">Legal</div>
        </div>
        <div class="side-panel-body" id="hoSidePanelContent">
            <!-- Content loaded via AJAX -->
        </div>
    </div>

    <!-- Modals -->
    <div class="modal-overlay" id="addModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Employee</h2><button class="close-modal" onclick="closeModal('addModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="employeeForm">
                    <div class="form-row">
                        <div class="form-group"><label>First Name</label><input type="text" name="first_name" required>
                        </div>
                        <div class="form-group"><label>Last Name</label><input type="text" name="last_name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Position</label><input type="text" name="position" required>
                        </div>
                        <div class="form-group"><label>Kebele</label><input type="text" name="kebele" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Salary</label><input type="number" name="salary" step="0.01">
                        </div>
                        <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-reset" onclick="closeModal('addModal')">Cancel</button>
                <button class="btn-action" onclick="saveEmployee()">Save</button>
            </div>
        </div>
    </div>

    <script>
        // Tab functionality
        function showTab(tabName) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            event.target.classList.add('active');
            document.getElementById(tabName + 'Tab').classList.add('active');
            if (tabName === 'analytics') initCharts();
            if (tabName === 'attendance') initAttendanceCharts();
        }

        // Initialize Charts
        function initCharts() {
            new Chart(document.getElementById('kebeleChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($kebele_labels); ?>,
                    datasets: [{ label: 'Employees', data: <?php echo json_encode($kebele_present); ?>, backgroundColor: 'rgba(74, 144, 217, 0.7)' }]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
            });

            new Chart(document.getElementById('positionChart'), {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($position_labels); ?>,
                    datasets: [{ data: <?php echo json_encode($position_counts); ?>, backgroundColor: ['#4a90d9', '#27ae60', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c', '#3498db'] }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
            });

            new Chart(document.getElementById('trendsChart'), {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{ label: 'Present', data: [45, 48, 47, 46, 44, 20, 15], borderColor: '#27ae60', tension: 0.4 }, { label: 'Absent', data: [5, 2, 3, 4, 6, 30, 35], borderColor: '#e74c3c', tension: 0.4 }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            new Chart(document.getElementById('genderChart'), {
                type: 'pie',
                data: { labels: ['Male', 'Female', 'Not Specified'], datasets: [{ data: [65, 35, 5], backgroundColor: ['#4a90d9', '#e91e63', '#95a5a6'] }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });
        }

        function initAttendanceCharts() {
            new Chart(document.getElementById('kebeleAttendanceChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($kebele_labels); ?>,
                    datasets: [{ label: 'Present', data: <?php echo json_encode($kebele_present); ?>, backgroundColor: '#27ae60' }, { label: 'Absent', data: <?php echo json_encode($kebele_absent); ?>, backgroundColor: '#e74c3c' }]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } }
            });

            new Chart(document.getElementById('attendanceStatusChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Absent', 'Late', 'Leave'],
                    datasets: [{ data: [<?php echo $present_count; ?>, <?php echo $absent_count; ?>, <?php echo $late_count; ?>, <?php echo $leave_count; ?>], backgroundColor: ['#27ae60', '#e74c3c', '#f39c12', '#9b59b6'] }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });
        }

        // Sidebar and Mobile Menu are handled by sidebar.php include

        function openModal(id) { document.getElementById(id).classList.add('active'); document.body.style.overflow = 'hidden'; }
        function closeModal(id) { document.getElementById(id).classList.remove('active'); document.body.style.overflow = 'auto'; }

        // Side Panel Logic
        let currentEmployeeHO = null;
        function viewEmployee(empId) {
            document.getElementById('hoSidePanelOverlay').classList.add('active');
            document.getElementById('hoEmployeeSidePanel').classList.add('open');
            document.getElementById('hoSidePanelContent').innerHTML = '<div style="text-align:center; padding:50px;"><i class="fas fa-circle-notch fa-spin" style="font-size:2rem; color:#1a4a5f;"></i><p>Loading details...</p></div>';

            fetch(`../wereda_hr/get_employee_detail.php?employee_id=${empId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        currentEmployeeHO = data.employee;
                        renderEmployeeDetailsHO('general');
                    } else {
                        document.getElementById('hoSidePanelContent').innerHTML = `<div style="color:red; text-align:center; padding:20px;">Error: ${data.message}</div>`;
                    }
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById('hoSidePanelContent').innerHTML = `<div style="color:red; text-align:center; padding:20px;">Network Error.</div>`;
                });
        }

        function closeSidePanelHO() {
            document.getElementById('hoSidePanelOverlay').classList.remove('active');
            document.getElementById('hoEmployeeSidePanel').classList.remove('open');
        }

        function switchTabHO(tabName, event) {
            const tabs = document.querySelectorAll('.side-tab');
            tabs.forEach(t => t.classList.remove('active'));
            if (event) event.target.classList.add('active');
            renderEmployeeDetailsHO(tabName);
        }

        function editEmployeeHO(id) {
            window.location.href = `../wereda_hr/edit_employee.php?id=${id}`;
        }

        function renderEmployeeDetailsHO(tab) {
            const content = document.getElementById('hoSidePanelContent');
            if (!currentEmployeeHO) return;
            const emp = currentEmployeeHO;
            const initials = (emp.first_name?.charAt(0) || '') + (emp.last_name?.charAt(0) || '');

            let html = `
                <div class="profile-header-premium" style="background: linear-gradient(135deg, #1a4a5f 0%, #2a6e8c 100%); color:white; padding:30px; border-radius:15px; margin:20px; position:relative;">
                    <button onclick="editEmployeeHO('${emp.employee_id}')" style="position:absolute; top:15px; right:15px; background:rgba(255,255,255,0.2); border:none; color:white; padding:8px 12px; border-radius:8px; cursor:pointer;" title="Edit Profile">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <div class="avatar-lg" style="width:80px; height:80px; background:rgba(255,255,255,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2rem; margin-bottom:15px; border:3px solid rgba(255,255,255,0.3);">
                        ${initials}
                    </div>
                    <h3 style="font-size:1.4rem; margin-bottom:5px;">${emp.first_name} ${emp.last_name}</h3>
                    <p style="opacity:0.9; font-size:0.9rem;">${emp.position || 'Employee'} | ${emp.employee_id}</p>
                    <span class="status-badge ${emp.status || 'active'}" style="background:white; color:var(--primary); margin-top:10px; display:inline-block; border:none;">
                        ${(emp.status || 'Active').toUpperCase()}
                    </span>
                </div>
            `;

            if (tab === 'general') {
                html += `
                    <div class="info-grid">
                        <div class="info-item"><div class="info-label">Full Name</div><div class="info-value">${emp.first_name} ${emp.middle_name || ''} ${emp.last_name}</div></div>
                        <div class="info-item"><div class="info-label">Employee ID</div><div class="info-value">${emp.employee_id}</div></div>
                        <div class="info-item"><div class="info-label">Date of Birth</div><div class="info-value">${emp.dob || 'N/A'}</div></div>
                        <div class="info-item"><div class="info-label">Gender</div><div class="info-value">${emp.gender || 'N/A'}</div></div>
                        <div class="info-item"><div class="info-label">Department</div><div class="info-value">${emp.department_assigned || 'N/A'}</div></div>
                        <div class="info-item"><div class="info-label">Join Date</div><div class="info-value">${emp.join_date || 'N/A'}</div></div>
                        <div class="info-item full-width"><div class="info-label">Official Work Location</div><div class="info-value">${emp.working_kebele || emp.kebele || 'N/A'}</div></div>
                        <div class="info-item"><div class="info-label">Phone</div><div class="info-value">${emp.phone_number || 'N/A'}</div></div>
                        <div class="info-item"><div class="info-label">Email</div><div class="info-value">${emp.email || 'N/A'}</div></div>
                    </div>
                `;
            } else if (tab === 'education') {
                html += `
                    <div class="info-grid">
                        <div class="info-item full-width"><div class="info-label">Highest Qualification</div><div class="info-value">${emp.qualification || 'N/A'}</div></div>
                        <div class="info-item full-width"><div class="info-label">Field of Study</div><div class="info-value">${emp.department || 'N/A'}</div></div>
                    </div>
                `;
            } else if (tab === 'finance') {
                html += `<div class="info-grid"><div class="info-item"><div class="info-label">Salary</div><div class="info-value">${emp.salary || 'N/A'}</div></div></div>`;
            } else if (tab === 'legal') {
                html += `<div class="info-grid"><div class="info-item"><div class="info-label">Warranty</div><div class="info-value">${emp.warranty_status || 'N/A'}</div></div></div>`;
            }

            content.innerHTML = html;
        }

        function viewAttendance(id) { showTab('attendance'); }
        function viewPayroll(id) { showTab('payroll'); }
        function viewAttendanceDetail(id) { alert('Attendance detail: ' + id); }
        function editAttendance(id) { alert('Edit attendance: ' + id); }
        function viewPayrollSlip(id) { alert('Payroll slip: ' + id); }
        function markAttendance() { alert('Opening attendance marking...'); }
        function processPayroll() { alert('Processing payroll...'); }
        function saveEmployee() { alert('Saved!'); closeModal('addModal'); }
        function exportData(type) { alert('Exporting ' + type + '...'); }
        function exportAttendance() { alert('Exporting attendance...'); }
        function exportPayroll() { alert('Exporting payroll...'); }

        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', (e) => { if (e.target === modal) { modal.classList.remove('active'); document.body.style.overflow = 'auto'; } });
        });
    </script>
</body>

</html>