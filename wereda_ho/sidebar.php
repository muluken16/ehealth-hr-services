<?php
$current_page = basename($_SERVER['PHP_SELF']);
$section = $_GET['section'] ?? '';
?>
<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="wereda_ho_dashboard.php" class="logo">
            <i class="fas fa-heartbeat"></i>
            <span class="logo-text">HealthFirst</span>
        </a>
        <button class="toggle-sidebar" id="toggleSidebar">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <nav class="sidebar-menu">
        <ul>
            <li class="menu-item <?php echo ($current_page == 'wereda_ho_dashboard.php' && $section != 'employees') ? 'active' : ''; ?>"
                id="menu-dashboard">
                <a href="wereda_ho_dashboard.php" <?php echo $current_page == 'wereda_ho_dashboard.php' ? 'onclick="window.scrollTo({top:0, behavior:\'smooth\'}); return false;"' : ''; ?>>
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="menu-text">Overview</span>
                </a>
            </li>

            <li class="menu-item <?php echo ($current_page == 'wereda_ho_dashboard.php' && $section == 'employees') ? 'active' : ''; ?>"
                id="menu-employees">
                <a href="wereda_ho_dashboard.php?section=employees" <?php echo $current_page == 'wereda_ho_dashboard.php' ? 'onclick="showSection(\'employeesSection\'); return false;"' : ''; ?>>
                    <i class="fas fa-users-cog"></i>
                    <span class="menu-text">Staff Directory</span>
                </a>
            </li>

            <li class="menu-item <?php echo $current_page == 'leave_requests.php' ? 'active' : ''; ?>" id="menu-leave">
                <a href="leave_requests.php">
                    <i class="fas fa-umbrella-beach"></i>
                    <span class="menu-text">Leave Requests</span>
                </a>
            </li>

            <li class="menu-item <?php echo $current_page == 'wereda_inventory.php' ? 'active' : ''; ?>"
                id="menu-inventory">
                <a href="wereda_inventory.php">
                    <i class="fas fa-pills"></i>
                    <span class="menu-text">Inventory</span>
                </a>
            </li>

            <li class="menu-item <?php echo $current_page == 'wereda_reports.php' ? 'active' : ''; ?>"
                id="menu-reports">
                <a href="wereda_reports.php">
                    <i class="fas fa-chart-bar"></i>
                    <span class="menu-text">Reports</span>
                </a>
            </li>

            <li class="menu-item <?php echo $current_page == 'wereda_emergency.php' ? 'active' : ''; ?>"
                id="menu-emergency">
                <a href="wereda_emergency.php">
                    <i class="fas fa-ambulance"></i>
                    <span class="menu-text">Emergency</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="menu-text">Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>

<script>
    (function () {
        function initNav() {
            const sidebar = document.getElementById('sidebar');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const toggleSidebarBtn = document.getElementById('toggleSidebar');
            const mobileOverlay = document.getElementById('mobileOverlay');

            if (toggleSidebarBtn) {
                toggleSidebarBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('collapsed');
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                });
            }

            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', function () {
                    sidebar.classList.add('mobile-show');
                    if (mobileOverlay) mobileOverlay.classList.add('active');
                });
            }

            if (mobileOverlay) {
                mobileOverlay.addEventListener('click', function () {
                    sidebar.classList.remove('mobile-show');
                    mobileOverlay.classList.remove('active');
                });
            }

            // Restore state
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initNav);
        } else {
            initNav();
        }
    })();
</script>