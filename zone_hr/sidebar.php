<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Mobile Overlay -->
<div class="mobile-overlay" id="mobileOverlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="zone_hr_dashboard.php" class="logo">
            <i class="fas fa-heartbeat"></i>
            <span class="logo-text">Zone HR</span>
        </a>
        <button class="toggle-sidebar" id="toggleSidebar">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <nav class="sidebar-menu">
        <ul>
            <li class="menu-item <?php echo $current_page == 'zone_hr_dashboard.php' ? 'active' : ''; ?>">
                <a href="zone_hr_dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="menu-text">HR Dashboard</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'zone_hr_employee.php' ? 'active' : ''; ?>">
                <a href="zone_hr_employee.php">
                    <i class="fas fa-users"></i>
                    <span class="menu-text">Employees</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'zone_hr_leave.php' ? 'active' : ''; ?>">
                <a href="zone_hr_leave.php">
                    <i class="fas fa-umbrella-beach"></i>
                    <span class="menu-text">Leave Management</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'zone_hr_attendance.php' ? 'active' : ''; ?>">
                <a href="zone_hr_attendance.php">
                    <i class="fas fa-calendar-check"></i>
                    <span class="menu-text">Attendance</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'zone_hr_recruitment.php' ? 'active' : ''; ?>">
                <a href="zone_hr_recruitment.php">
                    <i class="fas fa-user-plus"></i>
                    <span class="menu-text">Recruitment</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'zone_hr_training.php' ? 'active' : ''; ?>">
                <a href="zone_hr_training.php">
                    <i class="fas fa-graduation-cap"></i>
                    <span class="menu-text">Training</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'zone_hr_payroll.php' ? 'active' : ''; ?>">
                <a href="zone_hr_payroll.php">
                    <i class="fas fa-money-check-alt"></i>
                    <span class="menu-text">Payroll</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'zone_hr_reports.php' ? 'active' : ''; ?>">
                <a href="zone_hr_reports.php">
                    <i class="fas fa-chart-bar"></i>
                    <span class="menu-text">HR Reports</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'zone_hr_settings.php' ? 'active' : ''; ?>">
                <a href="zone_hr_settings.php">
                    <i class="fas fa-cog"></i>
                    <span class="menu-text">HR Settings</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>