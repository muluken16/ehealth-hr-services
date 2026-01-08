<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);

// Dynamic Sidebar Badge Counts
require_once dirname(__DIR__) . '/db.php';
$woreda_val = $_SESSION['woreda'] ?? 'Woreda 1';
$woreda_match = "%$woreda_val%";

$counts = [
    'employees' => 0,
    'leaves' => 0,
    'recruitment' => 0,
    'training' => 0
];

try {
    $c = getDBConnection();
    
    // Employee Count
    $stmt = $c->prepare("SELECT COUNT(*) as cnt FROM employees WHERE woreda LIKE ?");
    $stmt->bind_param("s", $woreda_match);
    $stmt->execute();
    $counts['employees'] = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
    
    // Pending Leave Count
    $stmt = $c->prepare("SELECT COUNT(*) as cnt FROM leave_requests lr JOIN employees e ON lr.employee_id = e.employee_id WHERE e.woreda LIKE ? AND lr.status = 'pending'");
    $stmt->bind_param("s", $woreda_match);
    $stmt->execute();
    $counts['leaves'] = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
    
    // Open Recruitment Count
    $stmt = $c->prepare("SELECT COUNT(*) as cnt FROM job_postings WHERE (woreda LIKE ? OR woreda IS NULL) AND status = 'open'");
    $stmt->bind_param("s", $woreda_match);
    $stmt->execute();
    $counts['recruitment'] = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
    
    // Upcoming Training Count
    $stmt = $c->prepare("SELECT COUNT(*) as cnt FROM training_sessions WHERE (woreda LIKE ? OR woreda IS NULL) AND session_date >= CURDATE()");
    $stmt->bind_param("s", $woreda_match);
    $stmt->execute();
    $counts['training'] = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
    
} catch (Exception $e) {
    // Silently fail to avoid breaking UI if DB is down
}
?>
<!-- Mobile Overlay -->
<div class="mobile-overlay" id="mobileOverlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="#" class="logo">
            <i class="fas fa-id-card-alt"></i> <!-- Updated icon to be more HR specific -->
            <span class="logo-text">HR Management</span>
        </a>
        <button class="toggle-sidebar" id="toggleSidebar">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <nav class="sidebar-menu">
        <ul>
            <li class="menu-item <?php echo $current_page == 'wereda_hr_dashboard.php' ? 'active' : ''; ?>">
                <a href="wereda_hr_dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="menu-text">HR Dashboard</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'wereda_hr_employee.php' ? 'active' : ''; ?>">
                <a href="wereda_hr_employee.php">
                    <i class="fas fa-users"></i>
                    <span class="menu-text">Employees</span>
                    <span class="menu-badge"><?php echo $counts['employees']; ?></span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'wereda_hr_leave.php' ? 'active' : ''; ?>">
                <a href="wereda_hr_leave.php">
                    <i class="fas fa-umbrella-beach"></i>
                    <span class="menu-text">Leave Management</span>
                    <span class="menu-badge"><?php echo $counts['leaves']; ?></span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'wereda_hr_recruitment.php' ? 'active' : ''; ?>">
                <a href="wereda_hr_recruitment.php">
                    <i class="fas fa-user-plus"></i>
                    <span class="menu-text">Recruitment</span>
                    <span class="menu-badge"><?php echo $counts['recruitment']; ?></span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'wereda_hr_training.php' ? 'active' : ''; ?>">
                <a href="wereda_hr_training.php">
                    <i class="fas fa-graduation-cap"></i>
                    <span class="menu-text">Training</span>
                    <span class="menu-badge"><?php echo $counts['training']; ?></span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'wereda_hr_payroll.php' ? 'active' : ''; ?>">
                <a href="wereda_hr_payroll.php">
                    <i class="fas fa-money-check-alt"></i>
                    <span class="menu-text">Payroll</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'wereda_hr_reports.php' ? 'active' : ''; ?>">
                <a href="wereda_hr_reports.php">
                    <i class="fas fa-chart-bar"></i>
                    <span class="menu-text">HR Reports</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'wereda_hr_settings.php' ? 'active' : ''; ?>">
                <a href="wereda_hr_settings.php">
                    <i class="fas fa-cog"></i>
                    <span class="menu-text">HR Settings</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
