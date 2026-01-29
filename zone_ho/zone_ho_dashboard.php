<?php
session_start();
error_log("Dashboard accessed. Session user_id: " . ($_SESSION['user_id'] ?? 'not set') . ", role: " . ($_SESSION['role'] ?? 'not set'));
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'zone_health_officer') {
    error_log("Access denied: user_id or role check failed");
    header('Location: ../index.html');
    exit();
}

include '../db.php';
$conn = getDBConnection();
error_log("Database connection established");

// Get user's zone
$user_zone = $_SESSION['zone'] ?? 'West Shewa';
error_log("User zone: $user_zone");

// Get aggregated statistics
$total_patients = $conn->query("SELECT COUNT(*) as count FROM patients WHERE zone = '$user_zone'")->fetch_assoc()['count'];
error_log("Total patients: $total_patients");
$today_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE zone = '$user_zone' AND appointment_date >= CURDATE()")->fetch_assoc()['count'];
error_log("Today appointments: $today_appointments");
$active_doctors = $conn->query("SELECT COUNT(*) as count FROM employees WHERE zone = '$user_zone' AND position LIKE '%Doctor%' AND status = 'active'")->fetch_assoc()['count'];
error_log("Active doctors: $active_doctors");
$emergency_cases = $conn->query("SELECT COUNT(*) as count FROM emergency_responses WHERE zone = '$user_zone' AND status != 'resolved'")->fetch_assoc()['count'];
error_log("Emergency cases: $emergency_cases");

// Get recent appointments (today and future)
$today_appts_query = $conn->query("SELECT a.*, p.first_name, p.last_name FROM appointments a JOIN patients p ON a.patient_id = p.id WHERE a.zone = '$user_zone' AND a.appointment_date >= CURDATE() ORDER BY a.appointment_date, a.appointment_time LIMIT 10");

// Get recent activities (last 5)
$recent_activities = [];
$activities_query = $conn->query("
    (SELECT 'appointment' as type, CONCAT('New appointment scheduled for ', p.first_name, ' ', p.last_name) as description, a.created_at as time FROM appointments a JOIN patients p ON a.patient_id = p.id WHERE a.zone = '$user_zone' ORDER BY a.created_at DESC LIMIT 2)
    UNION ALL
    (SELECT 'patient' as type, CONCAT('New patient registered: ', first_name, ' ', last_name) as description, created_at as time FROM patients WHERE zone = '$user_zone' ORDER BY created_at DESC LIMIT 2)
    UNION ALL
    (SELECT 'emergency' as type, CONCAT('Emergency reported: ', incident_type) as description, created_at as time FROM emergency_responses WHERE zone = '$user_zone' ORDER BY created_at DESC LIMIT 1)
    ORDER BY time DESC LIMIT 5
");

while ($activity = $activities_query->fetch_assoc()) {
    $recent_activities[] = $activity;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Zone Health Officer Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/styleho.css">
</head>
<body>
    <div class="admin-container">
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="#" class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <span class="logo-text">HealthFirst</span>
                </a>
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>

            <nav class="sidebar-menu">
                <ul>
                    <li class="menu-item active">
                        <a href="#">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="menu-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="zone_patients.php">
                            <i class="fas fa-user-injured"></i>
                            <span class="menu-text">Patients</span>
                            <span class="menu-badge"><?php echo $total_patients; ?></span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="zone_appointments.php">
                            <i class="fas fa-calendar-check"></i>
                            <span class="menu-text">Appointments</span>
                            <span class="menu-badge"><?php echo $today_appointments; ?></span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="zone_inventory.php">
                            <i class="fas fa-pills"></i>
                            <span class="menu-text">Inventory</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="zone_reports.php">
                            <i class="fas fa-chart-bar"></i>
                            <span class="menu-text">Reports</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="zone_emergency.php">
                            <i class="fas fa-ambulance"></i>
                            <span class="menu-text">Emergency</span>
                            <span class="menu-badge"><?php echo $emergency_cases; ?></span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="zone_qa.php">
                            <i class="fas fa-clipboard-check"></i>
                            <span class="menu-text">Quality Assurance</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="zone_settings.php">
                            <i class="fas fa-cog"></i>
                            <span class="menu-text">Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- User Drawer (Mobile) -->
        <div class="user-drawer" id="userDrawer">
            <div class="user-drawer-header">
                <h3>Account</h3>
                <button class="close-user-drawer" id="closeUserDrawer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="user-drawer-content">
                <div class="user-drawer-profile">
                    <div class="user-drawer-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?></div>
                    <h3><?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
                    <p>Zone Health Officer</p>
                    <p class="user-role"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                </div>

                <ul class="user-drawer-menu">
                    <li><a href="#"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Account Settings</a></li>
                    <li><a href="#"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li><a href="#"><i class="fas fa-question-circle"></i> Help & Support</a></li>
                    <li><a href="#"><i class="fas fa-moon"></i> Dark Mode</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <button class="mobile-menu-btn" id="mobileMenuBtn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">Zone Dashboard - <?php echo htmlspecialchars($user_zone); ?></h1>
                </div>

                <div class="header-actions">
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>

                    <div class="user-profile" id="userProfile">
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?></div>
                        <div class="user-info">
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            <span class="user-role">Zone Health Officer</span>
                        </div>
                        <i class="fas fa-chevron-down"></i>

                        <div class="dropdown-menu" id="userDropdown">
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-cog"></i> Account Settings
                            </a>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-question-circle"></i> Help & Support
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="../logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>

                    <button class="mobile-user-menu-btn" id="mobileUserMenuBtn">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card patients">
                        <div class="stat-icon">
                            <i class="fas fa-user-injured"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($total_patients); ?></h3>
                            <p>Total Patients</p>
                        </div>
                        <div class="stat-change positive">+<?php echo rand(5, 15); ?>%</div>
                    </div>

                    <div class="stat-card appointments">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $today_appointments; ?></h3>
                            <p>Upcoming Appointments</p>
                        </div>
                        <div class="stat-change positive">+<?php echo rand(2, 8); ?>%</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $active_doctors; ?></h3>
                            <p>Active Doctors</p>
                        </div>
                        <div class="stat-change positive">+<?php echo rand(1, 3); ?></div>
                    </div>

                    <div class="stat-card emergency">
                        <div class="stat-icon">
                            <i class="fas fa-ambulance"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $emergency_cases; ?></h3>
                            <p>Active Emergencies</p>
                        </div>
                        <div class="stat-change negative">-<?php echo rand(2, 10); ?>%</div>
                    </div>
                </div>

                <!-- Charts and Tables Row -->
                <div class="content-row">
                    <!-- Appointments Chart -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Monthly Appointments Overview</h2>
                            <div class="card-actions">
                                <button class="card-action-btn">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="appointmentsChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Recent Activity</h2>
                        </div>
                        <div class="card-body">
                            <ul class="activity-list">
                                <?php foreach ($recent_activities as $activity): ?>
                                <li class="activity-item">
                                    <div class="activity-icon <?php echo $activity['type']; ?>">
                                        <i class="fas fa-<?php
                                            echo $activity['type'] == 'appointment' ? 'calendar-plus' :
                                                 ($activity['type'] == 'patient' ? 'user-plus' :
                                                 ($activity['type'] == 'emergency' ? 'exclamation-triangle' : 'info-circle'));
                                        ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-text"><?php echo htmlspecialchars($activity['description']); ?></div>
                                        <div class="activity-time"><?php echo date('M j, Y g:i A', strtotime($activity['time'])); ?></div>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Today's Appointments Table -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Upcoming Appointments</h2>
                        <div class="card-actions">
                            <button class="card-action-btn">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button class="card-action-btn">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Time</th>
                                        <th>Department</th>
                                        <th>Wereda</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($appt = $today_appts_query->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($appt['first_name'] . ' ' . $appt['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appt['doctor_name']); ?></td>
                                        <td><?php echo date('g:i A', strtotime($appt['appointment_time'])); ?></td>
                                        <td><?php echo htmlspecialchars($appt['department']); ?></td>
                                        <td><?php echo htmlspecialchars($appt['woreda']); ?></td>
                                        <td><span class="status-badge <?php echo $appt['status']; ?>"><?php echo ucfirst($appt['status']); ?></span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                                <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Quick Actions</h2>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="zone_patients.php" class="quick-action-btn">
                                <i class="fas fa-plus-circle"></i>
                                <span>View Patients</span>
                            </a>
                            <a href="zone_appointments.php" class="quick-action-btn">
                                <i class="fas fa-calendar-plus"></i>
                                <span>Manage Appointments</span>
                            </a>
                            <a href="zone_inventory.php" class="quick-action-btn">
                                <i class="fas fa-pills"></i>
                                <span>Check Inventory</span>
                            </a>
                            <a href="zone_reports.php" class="quick-action-btn">
                                <i class="fas fa-chart-line"></i>
                                <span>Generate Report</span>
                            </a>
                            <a href="zone_emergency.php" class="quick-action-btn">
                                <i class="fas fa-bell"></i>
                                <span>Emergency Response</span>
                            </a>
                            <a href="zone_qa.php" class="quick-action-btn">
                                <i class="fas fa-clipboard-check"></i>
                                <span>Quality Assurance</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileOverlay = document.getElementById('mobileOverlay');
        const toggleSidebarBtn = document.getElementById('toggleSidebar');
        const userProfile = document.getElementById('userProfile');
        const userDropdown = document.getElementById('userDropdown');
        const mobileUserMenuBtn = document.getElementById('mobileUserMenuBtn');
        const userDrawer = document.getElementById('userDrawer');
        const closeUserDrawer = document.getElementById('closeUserDrawer');

        // Toggle Sidebar on Desktop
        toggleSidebarBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });

        // Open/Close Sidebar on Mobile
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.add('mobile-open');
            mobileOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        mobileOverlay.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            userDrawer.classList.remove('open');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        });

        // Open User Drawer on Mobile
        mobileUserMenuBtn.addEventListener('click', () => {
            userDrawer.classList.add('open');
            mobileOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        // Close User Drawer
        closeUserDrawer.addEventListener('click', () => {
            userDrawer.classList.remove('open');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        });

        // User Profile Dropdown (Desktop)
        userProfile.addEventListener('click', (e) => {
            if (window.innerWidth > 768) {
                e.stopPropagation();
                userDropdown.classList.toggle('show');
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!userProfile.contains(e.target) && window.innerWidth > 768) {
                userDropdown.classList.remove('show');
            }
        });

        // Initialize Chart
        const ctx = document.getElementById('appointmentsChart').getContext('2d');

        // Sample chart data (in real app, this would come from PHP/database)
        const appointmentsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Zone Appointments',
                    data: [120, 150, 180, 200, 220, 240, 260, 250, 230, 210, 240, 280],
                    backgroundColor: 'rgba(76, 181, 174, 0.1)',
                    borderColor: 'rgba(76, 181, 174, 1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'rgba(76, 181, 174, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            stepSize: 50
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                }
            }
        });

        // Handle window resize
        function handleResize() {
            // Auto-hide sidebar on mobile when resizing to desktop
            if (window.innerWidth > 992) {
                sidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('active');
                document.body.style.overflow = 'auto';

                // Restore desktop sidebar state
                if (sidebar.classList.contains('collapsed')) {
                    sidebar.classList.add('collapsed');
                }
            }

            // Show/hide user dropdown based on screen size
            if (window.innerWidth <= 768) {
                userDropdown.style.display = 'none';
            } else {
                userDropdown.style.display = '';
            }
        }

        // Initial check
        handleResize();

        // Listen for resize
        window.addEventListener('resize', handleResize);

        // Close drawers with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                sidebar.classList.remove('mobile-open');
                userDrawer.classList.remove('open');
                mobileOverlay.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });
    </script>
</body>
</html>