<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="kebele_hr_dashboard.php" class="logo">
            <i class="fas fa-heartbeat"></i>
            <span class="logo-text">HealthFirst HR</span>
        </a>
        <button class="toggle-sidebar" id="toggleSidebar">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>
    
    <nav class="sidebar-menu">
        <ul>
            <li class="menu-item <?php echo $current_page == 'kebele_hr_dashboard.php' ? 'active' : ''; ?>">
                <a href="kebele_hr_dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="menu-text">HR Dashboard</span>
                </a>
            </li>
            <li class="menu-item <?php echo ($current_page == 'hr-employees.php' && !isset($_GET['register'])) ? 'active' : ''; ?>">
                <a href="hr-employees.php">
                    <i class="fas fa-users"></i>
                    <span class="menu-text">Employees</span>
                    <span class="menu-badge" id="sidebarEmpBadge">0</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="hr-employees.php?register=true" style="color: var(--success);">
                    <i class="fas fa-user-plus"></i>
                    <span class="menu-text">Register New</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'hr-attendance.php' ? 'active' : ''; ?>">
                <a href="hr-attendance.php">
                    <i class="fas fa-calendar-check"></i>
                    <span class="menu-text">Attendance</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'hr-leave.php' ? 'active' : ''; ?>">
                <a href="hr-leave.php">
                    <i class="fas fa-umbrella-beach"></i>
                    <span class="menu-text">Leave Management</span>
                    <span class="menu-badge" id="sidebarLeaveBadge">0</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'hr-recruitment.php' ? 'active' : ''; ?>">
                <a href="hr-recruitment.php">
                    <i class="fas fa-user-plus"></i>
                    <span class="menu-text">Recruitment</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'hr-training.php' ? 'active' : ''; ?>">
                <a href="hr-training.php">
                    <i class="fas fa-graduation-cap"></i>
                    <span class="menu-text">Training</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'hr-payroll.php' ? 'active' : ''; ?>">
                <a href="hr-payroll.php">
                    <i class="fas fa-money-check-alt"></i>
                    <span class="menu-text">Payroll</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'hr-reports.php' ? 'active' : ''; ?>">
                <a href="hr-reports.php">
                    <i class="fas fa-chart-bar"></i>
                    <span class="menu-text">HR Reports</span>
                </a>
            </li>
            <li class="menu-item <?php echo $current_page == 'hr-settings.php' ? 'active' : ''; ?>">
                <a href="hr-settings.php">
                    <i class="fas fa-cog"></i>
                    <span class="menu-text">HR Settings</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>

<script>
    // Update sidebar badges if stats available
    document.addEventListener('DOMContentLoaded', function() {
        fetch('get_kebele_hr_stats.php')
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    const empBadge = document.getElementById('sidebarEmpBadge');
                    const leaveBadge = document.getElementById('sidebarLeaveBadge');
                    if(empBadge) empBadge.textContent = data.stats.total_employees;
                    if(leaveBadge) leaveBadge.textContent = data.stats.pending_leave;
                }
            })
            .catch(err => console.error('Error fetching sidebar stats:', err));
    });
</script>
