<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['role'])) {
    // Demo fallback
    $_SESSION['role'] = 'kebele_hr';
    $_SESSION['user_name'] = 'Kebele HR Officer';
    $_SESSION['kebele'] = 'Kebele 1';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | HR Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="hr-container">
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>
        
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
                    <li class="menu-item active">
                        <a href="kebele_hr_dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="menu-text">HR Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="hr-employees.html">
                            <i class="fas fa-users"></i>
                            <span class="menu-text">Employees</span>
                            <span class="menu-badge">142</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="hr-attendance.html">
                            <i class="fas fa-calendar-check"></i>
                            <span class="menu-text">Attendance</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="hr-leave.html">
                            <i class="fas fa-umbrella-beach"></i>
                            <span class="menu-text">Leave Management</span>
                            <span class="menu-badge">8</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="hr-recruitment.html">
                            <i class="fas fa-user-plus"></i>
                            <span class="menu-text">Recruitment</span>
                            <span class="menu-badge">5</span>
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
                    <h1 class="page-title">Human Resources Management</h1>
                </div>
                
                <div class="header-actions">
                    <div class="user-profile" id="userProfile">
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?></div>
                        <div class="user-info">
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            <span class="user-role"><?php
                                $role_names = [
                                    'admin' => 'Administrator',
                                    'zone_health_officer' => 'Zone Health Officer',
                                    'zone_hr' => 'Zone HR Officer',
                                    'wereda_health_officer' => 'Wereda Health Officer',
                                    'wereda_hr' => 'Wereda HR Officer',
                                    'kebele_health_officer' => 'Kebele Health Officer',
                                    'kebele_hr' => 'Kebele HR Officer'
                                ];
                                echo $role_names[$_SESSION['role']] ?? $_SESSION['role'];
                            ?></span>
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
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- HR Dashboard -->
            <div class="hr-dashboard">
                <!-- HR Stats -->
                <div class="hr-stats">
                    <div class="hr-stat-card employees">
                        <div class="hr-stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3 id="totalEmployees">0</h3>
                            <p>Total Employees</p>
                        </div>
                    </div>
                    
                    <div class="hr-stat-card vacancy">
                        <div class="hr-stat-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3 id="openPositions">0</h3>
                            <p>Open Positions</p>
                        </div>
                    </div>
                    
                    <div class="hr-stat-card on-leave">
                        <div class="hr-stat-icon">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3 id="onLeave">0</h3>
                            <p>On Leave Today</p>
                        </div>
                    </div>
                    
                    <div class="hr-stat-card attendance">
                        <div class="hr-stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3>94%</h3>
                            <p>Attendance Rate</p>
                        </div>
                    </div>
                </div>

                <!-- HR Quick Actions -->
                <div class="hr-actions">
                    <div class="hr-action-btn" id="addEmployeeBtn">
                        <i class="fas fa-user-plus"></i>
                        <span>Add New Employee</span>
                    </div>
                    <div class="hr-action-btn" id="processPayrollBtn">
                        <i class="fas fa-money-check-alt"></i>
                        <span>Process Payroll</span>
                    </div>
                    <div class="hr-action-btn" id="postJobBtn">
                        <i class="fas fa-bullhorn"></i>
                        <span>Post New Job</span>
                    </div>
                    <div class="hr-action-btn" id="scheduleTrainingBtn">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Schedule Training</span>
                    </div>
                    <div class="hr-action-btn" id="generateReportBtn">
                        <i class="fas fa-file-pdf"></i>
                        <span>Generate Report</span>
                    </div>
                    <div class="hr-action-btn" id="leaveRequestsBtn">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Leave Requests</span>
                        <span style="font-size: 0.8rem; margin-top: 5px; color: var(--accent);" id="pendingLeaveCount">0 pending</span>
                    </div>
                </div>

                 <!-- HR Analytics Section -->
                 <div class="hr-section" id="analyticsSection">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Kebele Workforce Analytics</h2>
                    </div>
                    <div class="hr-section-body">
                        <div class="charts-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; margin-bottom: 25px;">
                            <div class="chart-container-premium">
                                <canvas id="genderChart"></canvas>
                            </div>
                            <div class="chart-container-premium">
                                <canvas id="academicChart"></canvas>
                            </div>
                            <div class="chart-container-premium">
                                <canvas id="departmentChart"></canvas>
                            </div>
                            <div class="chart-container-premium">
                                <canvas id="jobLevelChart"></canvas>
                            </div>
                            <div class="chart-container-premium">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employees Section -->
                <div class="hr-section" id="employeesSection">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Employee Directory</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn" id="addEmployeeBtn2">
                                <i class="fas fa-plus"></i> Add Employee
                            </button>
                            <button class="section-action-btn" onclick="location.reload()">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Employee ID</th>
                                        <th>Department</th>
                                        <th>Position</th>
                                        <th>Join Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="employee-info">
                                                <div class="employee-avatar">SJ</div>
                                                <div>
                                                    <div class="employee-name">Dr. Sarah Johnson</div>
                                                    <div class="employee-id">sarah.j@healthfirst.com</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>HF-001</td>
                                        <td><span class="department-badge medical">Medical</span></td>
                                        <td>Senior Physician</td>
                                        <td>15/03/2018</td>
                                        <td><span class="status-badge active">Active</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                                <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                                <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="employee-info">
                                                <div class="employee-avatar">MC</div>
                                                <div>
                                                    <div class="employee-name">Dr. Michael Chen</div>
                                                    <div class="employee-id">michael.c@healthfirst.com</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>HF-002</td>
                                        <td><span class="department-badge medical">Medical</span></td>
                                        <td>Cardiologist</td>
                                        <td>22/07/2019</td>
                                        <td><span class="status-badge active">Active</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                                <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                                <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="employee-info">
                                                <div class="employee-avatar">PG</div>
                                                <div>
                                                    <div class="employee-name">Patricia Garcia</div>
                                                    <div class="employee-id">patricia.g@healthfirst.com</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>HF-003</td>
                                        <td><span class="department-badge admin">Administration</span></td>
                                        <td>HR Manager</td>
                                        <td>10/01/2020</td>
                                        <td><span class="status-badge on-leave">On Leave</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                                <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                                <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="employee-info">
                                                <div class="employee-avatar">RS</div>
                                                <div>
                                                    <div class="employee-name">Robert Smith</div>
                                                    <div class="employee-id">robert.s@healthfirst.com</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>HF-004</td>
                                        <td><span class="department-badge support">Support</span></td>
                                        <td>IT Specialist</td>
                                        <td>05/11/2021</td>
                                        <td><span class="status-badge active">Active</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                                <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                                <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="employee-info">
                                                <div class="employee-avatar">LB</div>
                                                <div>
                                                    <div class="employee-name">Lisa Brown</div>
                                                    <div class="employee-id">lisa.b@healthfirst.com</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>HF-005</td>
                                        <td><span class="department-badge technical">Technical</span></td>
                                        <td>Lab Technician</td>
                                        <td>18/09/2022</td>
                                        <td><span class="status-badge active">Active</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                                <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                                <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Leave Requests Section -->
                <div class="hr-section" id="leaveSection">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Pending Leave Requests</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn">
                                <i class="fas fa-plus"></i> New Request
                            </button>
                            <button class="section-action-btn">
                                <i class="fas fa-history"></i> View All
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="leave-requests">
                            <div class="leave-request-card">
                                <div class="leave-header">
                                    <div class="leave-employee">
                                        <div class="employee-avatar">SJ</div>
                                        <div>
                                            <div class="employee-name">Dr. Sarah Johnson</div>
                                            <div class="employee-id">Medical Department</div>
                                        </div>
                                    </div>
                                    <div class="leave-type">Annual Leave</div>
                                </div>
                                <div class="leave-dates">
                                    <div class="leave-date">
                                        <div class="leave-date-label">From</div>
                                        <div class="leave-date-value">15 Dec 2023</div>
                                    </div>
                                    <div class="leave-date">
                                        <div class="leave-date-label">To</div>
                                        <div class="leave-date-value">22 Dec 2023</div>
                                    </div>
                                </div>
                                <p style="margin-bottom: 15px; color: var(--gray);">Family vacation planned for the holidays.</p>
                                <div class="leave-actions">
                                    <button class="leave-action-btn approve">Approve</button>
                                    <button class="leave-action-btn reject">Reject</button>
                                </div>
                            </div>
                            
                            <div class="leave-request-card">
                                <div class="leave-header">
                                    <div class="leave-employee">
                                        <div class="employee-avatar">MC</div>
                                        <div>
                                            <div class="employee-name">Dr. Michael Chen</div>
                                            <div class="employee-id">Cardiology</div>
                                        </div>
                                    </div>
                                    <div class="leave-type">Sick Leave</div>
                                </div>
                                <div class="leave-dates">
                                    <div class="leave-date">
                                        <div class="leave-date-label">From</div>
                                        <div class="leave-date-value">10 Dec 2023</div>
                                    </div>
                                    <div class="leave-date">
                                        <div class="leave-date-label">To</div>
                                        <div class="leave-date-value">12 Dec 2023</div>
                                    </div>
                                </div>
                                <p style="margin-bottom: 15px; color: var(--gray);">Medical certificate attached for flu.</p>
                                <div class="leave-actions">
                                    <button class="leave-action-btn approve">Approve</button>
                                    <button class="leave-action-btn reject">Reject</button>
                                </div>
                            </div>
                            
                            <div class="leave-request-card">
                                <div class="leave-header">
                                    <div class="leave-employee">
                                        <div class="employee-avatar">LB</div>
                                        <div>
                                            <div class="employee-name">Lisa Brown</div>
                                            <div class="employee-id">Lab Technician</div>
                                        </div>
                                    </div>
                                    <div class="leave-type">Maternity Leave</div>
                                </div>
                                <div class="leave-dates">
                                    <div class="leave-date">
                                        <div class="leave-date-label">From</div>
                                        <div class="leave-date-value">01 Jan 2024</div>
                                    </div>
                                    <div class="leave-date">
                                        <div class="leave-date-label">To</div>
                                        <div class="leave-date-value">01 Apr 2024</div>
                                    </div>
                                </div>
                                <p style="margin-bottom: 15px; color: var(--gray);">Expected due date in December.</p>
                                <div class="leave-actions">
                                    <button class="leave-action-btn approve">Approve</button>
                                    <button class="leave-action-btn reject">Reject</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recruitment Section -->
                <div class="hr-section" id="recruitmentSection">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Open Positions</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn" id="postJobBtn2">
                                <i class="fas fa-plus"></i> Post New Job
                            </button>
                            <button class="section-action-btn">
                                <i class="fas fa-eye"></i> View Applications
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="recruitment-jobs">
                            <div class="job-card">
                                <div class="job-header">
                                    <div>
                                        <div class="job-title">Senior Nurse</div>
                                        <div class="job-department">Medical Department</div>
                                    </div>
                                    <div class="job-type">Full-time</div>
                                </div>
                                <div class="job-details">
                                    <div class="job-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Main Hospital</span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>Posted: 2 days ago</span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-users"></i>
                                        <span>12 Applications</span>
                                    </div>
                                </div>
                                <div class="job-actions">
                                    <button class="job-action-btn">View Details</button>
                                    <button class="job-action-btn">Edit</button>
                                </div>
                            </div>
                            
                            <div class="job-card">
                                <div class="job-header">
                                    <div>
                                        <div class="job-title">Medical Receptionist</div>
                                        <div class="job-department">Administration</div>
                                    </div>
                                    <div class="job-type">Part-time</div>
                                </div>
                                <div class="job-details">
                                    <div class="job-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>North Branch</span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>Posted: 1 week ago</span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-users"></i>
                                        <span>8 Applications</span>
                                    </div>
                                </div>
                                <div class="job-actions">
                                    <button class="job-action-btn">View Details</button>
                                    <button class="job-action-btn">Edit</button>
                                </div>
                            </div>
                            
                            <div class="job-card">
                                <div class="job-header">
                                    <div>
                                        <div class="job-title">IT Support Specialist</div>
                                        <div class="job-department">Technical</div>
                                    </div>
                                    <div class="job-type">Full-time</div>
                                </div>
                                <div class="job-details">
                                    <div class="job-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Main Hospital</span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>Posted: 3 days ago</span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-users"></i>
                                        <span>5 Applications</span>
                                    </div>
                                </div>
                                <div class="job-actions">
                                    <button class="job-action-btn">View Details</button>
                                    <button class="job-action-btn">Edit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Training Section -->
                <div class="hr-section" id="trainingSection">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Training Programs</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn" id="scheduleTrainingBtn2">
                                <i class="fas fa-plus"></i> Schedule Training
                            </button>
                            <button class="section-action-btn">
                                <i class="fas fa-history"></i> Past Trainings
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="training-courses">
                            <div class="training-card">
                                <div class="training-header">
                                    <div>
                                        <div class="training-title">Emergency Response Training</div>
                                        <div class="training-category">Medical & Safety</div>
                                    </div>
                                    <div class="training-status upcoming">Upcoming</div>
                                </div>
                                <div class="training-details">
                                    <div class="training-detail">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Date: 15 Dec 2023</span>
                                    </div>
                                    <div class="training-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>Time: 9:00 AM - 4:00 PM</span>
                                    </div>
                                    <div class="training-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Venue: Conference Room A</span>
                                    </div>
                                    <div class="training-detail">
                                        <i class="fas fa-user-tie"></i>
                                        <span>Trainer: Dr. James Lee</span>
                                    </div>
                                </div>
                                <div class="training-participants">
                                    <span>Participants:</span>
                                    <div class="participant-avatars">
                                        <div class="participant-avatar">SJ</div>
                                        <div class="participant-avatar">MC</div>
                                        <div class="participant-avatar">LB</div>
                                        <div class="participant-avatar">+5</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="training-card">
                                <div class="training-header">
                                    <div>
                                        <div class="training-title">HIPAA Compliance Training</div>
                                        <div class="training-category">Legal & Compliance</div>
                                    </div>
                                    <div class="training-status ongoing">Ongoing</div>
                                </div>
                                <div class="training-details">
                                    <div class="training-detail">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Date: 01-15 Dec 2023</span>
                                    </div>
                                    <div class="training-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>Format: Online Self-paced</span>
                                    </div>
                                    <div class="training-detail">
                                        <i class="fas fa-user-tie"></i>
                                        <span>Provider: Compliance Solutions Inc.</span>
                                    </div>
                                </div>
                                <div class="training-participants">
                                    <span>Progress: 24/42 completed</span>
                                </div>
                            </div>
                            
                            <div class="training-card">
                                <div class="training-header">
                                    <div>
                                        <div class="training-title">New Medical Software Training</div>
                                        <div class="training-category">Technical</div>
                                    </div>
                                    <div class="training-status completed">Completed</div>
                                </div>
                                <div class="training-details">
                                    <div class="training-detail">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Date: 20 Nov 2023</span>
                                    </div>
                                    <div class="training-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>Duration: 3 hours</span>
                                    </div>
                                    <div class="training-detail">
                                        <i class="fas fa-user-tie"></i>
                                        <span>Trainer: Robert Smith</span>
                                    </div>
                                    <div class="training-detail">
                                        <i class="fas fa-check-circle"></i>
                                        <span>28 participants attended</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>


    <!-- Post Job Modal -->
    <div class="modal" id="postJobModal">
        <div class="modal-content">
            <span class="close-modal" id="closeJobModal">&times;</span>
            <h2 class="modal-title">Post New Job Opening</h2>
            
            <form id="jobForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="jobTitle">Job Title *</label>
                        <input type="text" id="jobTitle" required>
                    </div>
                    <div class="form-group">
                        <label for="jobDepartment">Department *</label>
                        <select id="jobDepartment" required>
                            <option value="">Select Department</option>
                            <option value="medical">Medical</option>
                            <option value="administration">Administration</option>
                            <option value="technical">Technical</option>
                            <option value="support">Support</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="jobType">Employment Type</label>
                        <select id="jobType">
                            <option value="full-time">Full-time</option>
                            <option value="part-time">Part-time</option>
                            <option value="contract">Contract</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="jobLocation">Location</label>
                        <input type="text" id="jobLocation">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="salaryRange">Salary Range</label>
                        <input type="text" id="salaryRange" placeholder="e.g., $50,000 - $70,000">
                    </div>
                    <div class="form-group">
                        <label for="applicationDeadline">Application Deadline</label>
                        <input type="date" id="applicationDeadline">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="jobDescription">Job Description *</label>
                    <textarea id="jobDescription" required placeholder="Describe the role, responsibilities, and requirements..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="qualifications">Qualifications & Requirements</label>
                    <textarea id="qualifications" placeholder="List required education, experience, and skills..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="submit-btn">Post Job Opening</button>
                    <button type="button" class="cancel-btn" id="cancelJobBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileOverlay = document.getElementById('mobileOverlay');
        const toggleSidebarBtn = document.getElementById('toggleSidebar');

        // Toggle Sidebar
        if(toggleSidebarBtn) {
            toggleSidebarBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
            });
        }

        if(mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', () => {
                sidebar.classList.add('mobile-open');
                mobileOverlay.classList.add('active');
            });
        }

        if(mobileOverlay) {
             mobileOverlay.addEventListener('click', () => {
                sidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('active');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Set default dates
            const today = new Date().toISOString().split('T')[0];
            const joinDate = document.getElementById('joinDate');
            if(joinDate) joinDate.value = today;

            const futureDate = new Date();
            futureDate.setDate(futureDate.getDate() + 30);
            const appDeadline = document.getElementById('applicationDeadline');
            if(appDeadline) appDeadline.value = futureDate.toISOString().split('T')[0];

            // Load Data
            loadGlobalStats();
            loadEmployees();
            loadAllCharts();
        });

        function loadGlobalStats() {
            fetch('get_kebele_hr_stats.php')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                         // Auto-seed if empty
                         if (data.stats.total_employees == 0) {
                            if (!sessionStorage.getItem('kebele_seeded')) {
                                console.log('Kebele System Empty: Initializing default data...');
                                fetch('seed_data.php')
                                    .then(r => r.json())
                                    .then(res => {
                                        if(res.success) {
                                            sessionStorage.setItem('kebele_seeded', 'true');
                                            location.reload(); 
                                        }
                                    });
                            }
                        }

                        if(document.getElementById('totalEmployees')) document.getElementById('totalEmployees').textContent = data.stats.total_employees;
                        if(document.getElementById('openPositions')) document.getElementById('openPositions').textContent = data.stats.open_positions;
                        if(document.getElementById('onLeave')) document.getElementById('onLeave').textContent = data.stats.on_leave;
                        if(document.getElementById('attendanceRate')) document.getElementById('attendanceRate').parentNode.querySelector('h3').textContent = data.stats.attendance_rate + '%';
                        if(document.getElementById('pendingLeaveCount')) document.getElementById('pendingLeaveCount').textContent = data.stats.pending_leave + ' pending';
                    }
                });
        }

        function loadEmployees() {
            fetch('get_kebele_hr_employees.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('employeeTableBody');
                    if (!tbody) return;
                    tbody.innerHTML = '';

                    if (data.success && data.employees && data.employees.length > 0) {
                        data.employees.slice(0, 5).forEach(employee => {
                            const firstName = employee.first_name || '';
                            const lastName = employee.last_name || '';
                            const email = employee.email || 'N/A';
                            const empId = employee.employee_id || 'N/A';
                            const dept = employee.department_assigned || 'Unassigned';
                            const position = employee.position || 'Staff';
                            const joinDate = employee.join_date ? new Date(employee.join_date).toLocaleDateString() : '-';
                            const status = employee.status || 'active';
                            const statusClass = status.toLowerCase();
                            
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>
                                    <div class="employee-info">
                                        <div class="employee-avatar">${firstName.charAt(0)}${lastName.charAt(0)}</div>
                                        <div>
                                            <div class="employee-name">${firstName} ${lastName}</div>
                                            <div class="employee-id">${email}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>${empId}</td>
                                <td><span class="department-badge ${dept.toLowerCase()}">${dept}</span></td>
                                <td>${position}</td>
                                <td>${joinDate}</td>
                                <td><span class="status-badge ${statusClass}">${status}</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                        <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                    </div>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 20px;">No employees found.</td></tr>';
                    }
                })
                .catch(err => console.error(err));
        }

        function loadAllCharts() {
            loadGenderChart();
            loadAcademicChart();
            loadDepartmentChart();
            loadJobLevelChart();
            loadStatusChart();
        }

        function loadGenderChart() {
            fetch('get_kebele_hr_gender_stats.php').then(r=>r.json()).then(d=>{
                if(!d.success) return;
                new Chart(document.getElementById('genderChart'), {
                    type: 'doughnut',
                    data: { labels: Object.keys(d.data), datasets: [{ data: Object.values(d.data), backgroundColor: ['#4cb5ae', '#ff7e5f', '#ffc107', '#17a2b8'], borderWidth: 0 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Gender Distribution' }, legend: { position: 'bottom' } } }
                });
            });
        }

        function loadAcademicChart() {
            fetch('get_kebele_hr_academic_stats.php').then(r=>r.json()).then(d=>{
                if(!d.success) return;
                new Chart(document.getElementById('academicChart'), {
                    type: 'pie',
                    data: { labels: Object.keys(d.data), datasets: [{ data: Object.values(d.data), backgroundColor: ['#1a4a5f', '#2a6e8c', '#4cb5ae', '#ff7e5f', '#ffc107'], borderWidth: 0 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Academic Qualification' }, legend: { position: 'bottom' } } }
                });
            });
        }

        function loadDepartmentChart() {
            fetch('get_kebele_hr_department_stats.php').then(r=>r.json()).then(d=>{
                if(!d.success) return;
                new Chart(document.getElementById('departmentChart'), {
                    type: 'bar',
                    data: { labels: Object.keys(d.data), datasets: [{ label: 'Employees', data: Object.values(d.data), backgroundColor: '#4cb5ae', borderRadius: 5 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Department Distribution' }, legend: { display: false } } }
                });
            });
        }

        function loadJobLevelChart() {
            fetch('get_kebele_hr_job_level_stats.php').then(r=>r.json()).then(d=>{
                if(!d.success) return;
                new Chart(document.getElementById('jobLevelChart'), {
                    type: 'bar',
                    data: { labels: Object.keys(d.data), datasets: [{ label: 'Employees', data: Object.values(d.data), backgroundColor: '#2a6e8c', borderRadius: 5 }] },
                    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Job Levels' }, legend: { display: false } } }
                });
            });
        }

        function loadStatusChart() {
            fetch('get_kebele_hr_status_stats.php').then(r=>r.json()).then(d=>{
                if(!d.success) return;
                new Chart(document.getElementById('statusChart'), {
                    type: 'pie',
                    data: { labels: Object.keys(d.data), datasets: [{ data: Object.values(d.data), backgroundColor: ['#28a745', '#ffc107', '#dc3545'], borderWidth: 0 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Employment Status' }, legend: { position: 'bottom' } } }
                });
            });
        }
    </script>
</body>
</html>