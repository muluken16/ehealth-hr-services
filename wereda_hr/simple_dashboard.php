<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure role is set for the demo
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'wereda_hr';
    $_SESSION['user_name'] = 'Wereda HR Officer';
    $_SESSION['woreda'] = 'Woreda 1';
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
        <?php include 'sidebar.php'; ?>
        <?php
        $page_title = 'Human Resources Management';
        include 'navbar.php';
        ?>

        <main class="hr-main">
            <!-- HR Dashboard -->
            <div class="hr-dashboard">
                <!-- HR Stats -->
                <div class="hr-stats">
                    <div class="hr-stat-card employees">
                        <div class="hr-stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3>28</h3>
                            <p>Total Employees</p>
                        </div>
                    </div>

                    <div class="hr-stat-card vacancy">
                        <div class="hr-stat-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3>3</h3>
                            <p>Open Positions</p>
                        </div>
                    </div>

                    <div class="hr-stat-card on-leave">
                        <div class="hr-stat-icon">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3>2</h3>
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
                    <div class="hr-action-btn">
                        <i class="fas fa-user-plus"></i>
                        <span>Add New Employee</span>
                    </div>
                    <div class="hr-action-btn">
                        <i class="fas fa-money-check-alt"></i>
                        <span>Process Payroll</span>
                    </div>
                    <div class="hr-action-btn">
                        <i class="fas fa-bullhorn"></i>
                        <span>Post New Job</span>
                    </div>
                    <div class="hr-action-btn">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Schedule Training</span>
                    </div>
                    <div class="hr-action-btn">
                        <i class="fas fa-file-pdf"></i>
                        <span>Generate Report</span>
                    </div>
                    <div class="hr-action-btn">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Leave Requests</span>
                        <span style="font-size: 0.8rem; margin-top: 5px; color: var(--accent);">2 pending</span>
                    </div>
                </div>

                <!-- HR Analytics Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Wereda Workforce Analytics</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn">
                                <i class="fas fa-chart-line"></i> Detailed Reports
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="charts-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; margin-bottom: 25px;">
                            <div class="chart-container-premium" style="height: 350px; position: relative;">
                                <canvas id="genderChart" style="height: 300px !important; width: 100% !important;"></canvas>
                            </div>
                            <div class="chart-container-premium" style="height: 350px; position: relative;">
                                <canvas id="academicChart" style="height: 300px !important; width: 100% !important;"></canvas>
                            </div>
                            <div class="chart-container-premium" style="height: 350px; position: relative;">
                                <canvas id="departmentChart" style="height: 300px !important; width: 100% !important;"></canvas>
                            </div>
                            <div class="chart-container-premium" style="height: 350px; position: relative;">
                                <canvas id="jobLevelChart" style="height: 300px !important; width: 100% !important;"></canvas>
                            </div>
                            <div class="chart-container-premium" style="height: 350px; position: relative;">
                                <canvas id="statusChart" style="height: 300px !important; width: 100% !important;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employees Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Employee Directory</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn">
                                <i class="fas fa-plus"></i> Add Employee
                            </button>
                            <button class="section-action-btn">
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
                                                <div class="employee-avatar">JD</div>
                                                <div>
                                                    <div class="employee-name">John Doe</div>
                                                    <div class="employee-id">john.doe@health.gov.et</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>HF-2024-001</td>
                                        <td><span class="department-badge medical">Medical</span></td>
                                        <td>Doctor</td>
                                        <td>15/01/2020</td>
                                        <td><span class="status-badge active">Active</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                                <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="employee-info">
                                                <div class="employee-avatar">MS</div>
                                                <div>
                                                    <div class="employee-name">Mary Smith</div>
                                                    <div class="employee-id">mary.smith@health.gov.et</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>HF-2024-002</td>
                                        <td><span class="department-badge administration">Administration</span></td>
                                        <td>HR Manager</td>
                                        <td>10/03/2019</td>
                                        <td><span class="status-badge active">Active</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                                <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="employee-info">
                                                <div class="employee-avatar">AH</div>
                                                <div>
                                                    <div class="employee-name">Ahmed Hassan</div>
                                                    <div class="employee-id">ahmed.hassan@health.gov.et</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>HF-2024-003</td>
                                        <td><span class="department-badge technical">Technical</span></td>
                                        <td>Lab Technician</td>
                                        <td>01/06/2021</td>
                                        <td><span class="status-badge active">Active</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                                <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="employee-info">
                                                <div class="employee-avatar">FA</div>
                                                <div>
                                                    <div class="employee-name">Fatima Ali</div>
                                                    <div class="employee-id">fatima.ali@health.gov.et</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>HF-2024-004</td>
                                        <td><span class="department-badge medical">Medical</span></td>
                                        <td>Nurse</td>
                                        <td>15/02/2022</td>
                                        <td><span class="status-badge on-leave">On Leave</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                                <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="employee-info">
                                                <div class="employee-avatar">DW</div>
                                                <div>
                                                    <div class="employee-name">David Wilson</div>
                                                    <div class="employee-id">david.wilson@health.gov.et</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>HF-2024-005</td>
                                        <td><span class="department-badge support">Support</span></td>
                                        <td>IT Specialist</td>
                                        <td>20/08/2020</td>
                                        <td><span class="status-badge active">Active</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                                <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Recent HR Activity</h2>
                    </div>
                    <div class="hr-section-body">
                        <div class="activity-timeline">
                            <div class="timeline-item">
                                <div class="timeline-icon" style="background: #4cb5ae15; color: #4cb5ae;">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-title">New Employee Added</span>
                                        <span class="timeline-time">2 hours ago</span>
                                    </div>
                                    <p class="timeline-desc">John Doe joined the Medical department as a Doctor</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-icon" style="background: #ffc10715; color: #ffc107;">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-title">Leave Request Approved</span>
                                        <span class="timeline-time">4 hours ago</span>
                                    </div>
                                    <p class="timeline-desc">Mary Smith's annual leave request has been approved</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-icon" style="background: #17a2b815; color: #17a2b8;">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-title">Training Scheduled</span>
                                        <span class="timeline-time">1 day ago</span>
                                    </div>
                                    <p class="timeline-desc">Emergency Response Training scheduled for next week</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-icon" style="background: #ff7e5f15; color: #ff7e5f;">
                                    <i class="fas fa-bullhorn"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-title">Job Posted</span>
                                        <span class="timeline-time">2 days ago</span>
                                    </div>
                                    <p class="timeline-desc">Senior Nurse position posted in Medical department</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leave Requests Section -->
                <div class="hr-section">
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
                                        <div class="employee-avatar">JD</div>
                                        <div>
                                            <div class="employee-name">John Doe</div>
                                            <div class="employee-id">Medical</div>
                                        </div>
                                    </div>
                                    <div class="leave-type">Annual Leave</div>
                                </div>
                                <div class="leave-dates">
                                    <div class="leave-date">
                                        <div class="leave-date-label">From</div>
                                        <div class="leave-date-value">15 Feb 2024</div>
                                    </div>
                                    <div class="leave-date">
                                        <div class="leave-date-label">To</div>
                                        <div class="leave-date-value">20 Feb 2024</div>
                                    </div>
                                </div>
                                <p style="margin-bottom: 15px; color: var(--gray);">Family vacation</p>
                                <div class="leave-actions">
                                    <button class="leave-action-btn approve">Approve</button>
                                    <button class="leave-action-btn reject">Reject</button>
                                </div>
                            </div>
                            <div class="leave-request-card">
                                <div class="leave-header">
                                    <div class="leave-employee">
                                        <div class="employee-avatar">AH</div>
                                        <div>
                                            <div class="employee-name">Ahmed Hassan</div>
                                            <div class="employee-id">Technical</div>
                                        </div>
                                    </div>
                                    <div class="leave-type">Sick Leave</div>
                                </div>
                                <div class="leave-dates">
                                    <div class="leave-date">
                                        <div class="leave-date-label">From</div>
                                        <div class="leave-date-value">20 Jan 2024</div>
                                    </div>
                                    <div class="leave-date">
                                        <div class="leave-date-label">To</div>
                                        <div class="leave-date-value">22 Jan 2024</div>
                                    </div>
                                </div>
                                <p style="margin-bottom: 15px; color: var(--gray);">Medical treatment</p>
                                <div class="leave-actions">
                                    <button class="leave-action-btn approve">Approve</button>
                                    <button class="leave-action-btn reject">Reject</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recruitment Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Open Positions</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn">
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
                                        <div class="job-department">Medical</div>
                                    </div>
                                    <div class="job-type">Full-time</div>
                                </div>
                                <div class="job-details">
                                    <div class="job-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Wereda Health Center</span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>Posted: 15/01/2024</span>
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
                                        <div class="job-title">Lab Assistant</div>
                                        <div class="job-department">Technical</div>
                                    </div>
                                    <div class="job-type">Full-time</div>
                                </div>
                                <div class="job-details">
                                    <div class="job-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Wereda Health Center</span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>Posted: 10/01/2024</span>
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
                                        <div class="job-title">Administrative Assistant</div>
                                        <div class="job-department">Administration</div>
                                    </div>
                                    <div class="job-type">Part-time</div>
                                </div>
                                <div class="job-details">
                                    <div class="job-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Wereda Health Center</span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>Posted: 05/01/2024</span>
                                    </div>
                                    <div class="job-detail">
                                        <i class="fas fa-users"></i>
                                        <span>15 Applications</span>
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
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Training Programs</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn">
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
                                        <div class="training-category">Professional Development</div>
                                    </div>
                                    <div class="training-status upcoming">Scheduled</div>
                                </div>
                                <div class="training-details">
                                    <div class="training-detail">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>20 Feb 2024</span>
                                    </div>
                                    <div class="training-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>09:00 - 17:00</span>
                                    </div>
                                    <div class="training-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Main Conference Room</span>
                                    </div>
                                </div>
                                <div class="training-actions">
                                    <button class="training-action-btn primary">Register Employees</button>
                                </div>
                            </div>
                            <div class="training-card">
                                <div class="training-header">
                                    <div>
                                        <div class="training-title">Patient Care Excellence</div>
                                        <div class="training-category">Professional Development</div>
                                    </div>
                                    <div class="training-status upcoming">Scheduled</div>
                                </div>
                                <div class="training-details">
                                    <div class="training-detail">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>25 Feb 2024</span>
                                    </div>
                                    <div class="training-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>14:00 - 16:00</span>
                                    </div>
                                    <div class="training-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Training Hall</span>
                                    </div>
                                </div>
                                <div class="training-actions">
                                    <button class="training-action-btn primary">Register Employees</button>
                                </div>
                            </div>
                            <div class="training-card">
                                <div class="training-header">
                                    <div>
                                        <div class="training-title">Medical Equipment Handling</div>
                                        <div class="training-category">Professional Development</div>
                                    </div>
                                    <div class="training-status upcoming">Scheduled</div>
                                </div>
                                <div class="training-details">
                                    <div class="training-detail">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>01 Mar 2024</span>
                                    </div>
                                    <div class="training-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>10:00 - 12:00</span>
                                    </div>
                                    <div class="training-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Equipment Room</span>
                                    </div>
                                </div>
                                <div class="training-actions">
                                    <button class="training-action-btn primary">Register Employees</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Simple chart creation - no API calls, just static data
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Chart !== 'undefined') {
                // Gender Chart
                const genderCtx = document.getElementById('genderChart');
                if (genderCtx) {
                    new Chart(genderCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Male', 'Female'],
                            datasets: [{
                                data: [15, 13],
                                backgroundColor: ['#4cb5ae', '#ff7e5f'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: { display: true, text: 'Gender Distribution' },
                                legend: { position: 'bottom' }
                            }
                        }
                    });
                }

                // Academic Chart
                const academicCtx = document.getElementById('academicChart');
                if (academicCtx) {
                    new Chart(academicCtx, {
                        type: 'pie',
                        data: {
                            labels: ['Degree', 'Diploma', 'Secondary', 'Masters'],
                            datasets: [{
                                data: [10, 8, 6, 4],
                                backgroundColor: ['#1a4a5f', '#2a6e8c', '#4cb5ae', '#ff7e5f'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: { display: true, text: 'Academic Qualification' },
                                legend: { position: 'bottom' }
                            }
                        }
                    });
                }

                // Department Chart
                const departmentCtx = document.getElementById('departmentChart');
                if (departmentCtx) {
                    new Chart(departmentCtx, {
                        type: 'bar',
                        data: {
                            labels: ['Medical', 'Administration', 'Technical', 'Support'],
                            datasets: [{
                                label: 'Employees',
                                data: [12, 8, 5, 3],
                                backgroundColor: '#4cb5ae',
                                borderRadius: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: { display: true, text: 'Employees by Department' },
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                }

                // Job Level Chart
                const jobLevelCtx = document.getElementById('jobLevelChart');
                if (jobLevelCtx) {
                    new Chart(jobLevelCtx, {
                        type: 'bar',
                        data: {
                            labels: ['Senior', 'Junior', 'Manager', 'Director'],
                            datasets: [{
                                label: 'Staff Count',
                                data: [8, 12, 4, 2],
                                backgroundColor: '#1a4a5f',
                                borderRadius: 8
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: { display: true, text: 'Job Level Distribution' },
                                legend: { display: false }
                            }
                        }
                    });
                }

                // Status Chart
                const statusCtx = document.getElementById('statusChart');
                if (statusCtx) {
                    new Chart(statusCtx, {
                        type: 'pie',
                        data: {
                            labels: ['Active', 'On Leave', 'Inactive'],
                            datasets: [{
                                data: [25, 2, 1],
                                backgroundColor: ['#4cb5ae', '#ffc107', '#ff7e5f'],
                                borderWidth: 2,
                                borderColor: 'white'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: { display: true, text: 'Staff Status Distribution' },
