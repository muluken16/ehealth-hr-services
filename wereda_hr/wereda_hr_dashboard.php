<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure role is set for the demo
// Ensure session is initialized for demo
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'wereda_hr';
}
// Force update name for better UI
$_SESSION['user_name'] = 'HR Manager';
$_SESSION['woreda'] = 'Woreda 1';

$user_woreda = $_SESSION['woreda'] ?? 'Woreda 1';

// Include database connection
require_once '../db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wereda HR Dashboard | eHealth</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>
        <?php
        $page_title = 'HR Dashboard';
        include 'navbar.php';
        ?>

        <main class="hr-main">
            <!-- HR Dashboard -->
            <div class="hr-dashboard">
                <!-- Welcome Banner -->
                <div class="welcome-banner">
                    <div class="welcome-content">
                        <h2>Welcome back, <?php echo explode(' ', $_SESSION['user_name'])[0]; ?>! 👋</h2>
                        <p>Here's what's happening with your Woreda workforce today. Check the sections below for updates.</p>
                    </div>
                    <div class="welcome-date">
                        <i class="fas fa-clock"></i>
                        <span id="currentTime"><?php echo date('h:i A'); ?></span>
                    </div>
                </div>

                <!-- HR Stats -->
                <div class="hr-stats">
                    <div class="hr-stat-card employees">
                        <div class="hr-stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3 id="stat-total">--</h3>
                            <div class="stat-footer">
                                <p>Total Employees</p>
                                <span class="trend up" id="trendEmployees">+4.8%</span>
                            </div>
                        </div>
                    </div>

                    <div class="hr-stat-card vacancy">
                        <div class="hr-stat-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3 id="stat-active">--</h3>
                            <div class="stat-footer">
                                <p>Active Employees</p>
                                <span class="trend up" id="trendPositions">--</span>
                            </div>
                        </div>
                    </div>

                    <div class="hr-stat-card on-leave">
                        <div class="hr-stat-icon">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3 id="stat-leave">--</h3>
                            <div class="stat-footer">
                                <p>On Leave Today</p>
                                <span class="trend up" id="trendLeave">--</span>
                            </div>
                        </div>
                    </div>

                    <div class="hr-stat-card attendance">
                        <div class="hr-stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3 id="stat-attendance">--%</h3>
                            <div class="stat-footer">
                                <p>Attendance Rate</p>
                                <span class="trend up" id="trendAttendance">--</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- HR Quick Actions -->
                <div class="hr-actions">
                    <div class="hr-action-btn" onclick="window.location.href='wereda_hr_employee.php?add=true'">
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
                    <div class="hr-action-btn" onclick="document.getElementById('leaveSection').scrollIntoView({behavior: 'smooth'})">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Leave Requests</span>
                        <span class="action-badge" id="leaveRequestBadge" style="display:none;">0</span>
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
                            <!-- Added Kebele Workforce Distribution -->
                            <div class="chart-container-premium" style="height: 350px; position: relative;">
                                <canvas id="kebeleChart" style="height: 300px !important; width: 100% !important;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employees Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Recent Employees</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn" onclick="window.location.href='wereda_hr_employee.php?add=true'">
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
                                <tbody id="recentEmployeesBody">
                                    <tr><td colspan="7" style="text-align:center; padding: 20px;"><div class="loading-spinner"></div> Loading employees...</td></tr>
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
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-title">Activity Feed</span>
                                        <span class="timeline-time">Live Updates</span>
                                    </div>
                                    <p class="timeline-desc">Recent activities will appear here as they happen.</p>
                                </div>
                            </div>
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
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="leave-requests" id="leaveRequestsList">
                            <div class="loading-spinner"></div> Loading leave requests...
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
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="recruitment-jobs" id="jobPostingsList">
                            <div class="loading-spinner"></div> Loading job postings...
                        </div>
                    </div>
                </div>

                <!-- Training Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Upcoming Training Programs</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn">
                                <i class="fas fa-plus"></i> Schedule Training
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="training-courses" id="trainingList">
                            <div class="loading-spinner"></div> Loading training programs...
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing dashboard...');
            loadDashboardData();
            loadAllCharts();
        });

        function loadAllCharts() {
            console.log('Loading all workforce analytics charts...');
            
            // Gender Chart with real data
            console.log('Fetching gender stats...');
            fetch('get_gender_stats.php')
                .then(response => response.json())
                .then(data => {
                    console.log('Gender stats data:', data);
                    const ctx = document.getElementById('genderChart');
                    if (ctx && data.success && data.data) {
                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: Object.keys(data.data),
                                datasets: [{
                                    data: Object.values(data.data),
                                    backgroundColor: ['#4cb5ae', '#ff7e5f', '#ffc107'],
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
                        console.log('✅ Gender chart loaded');
                    } else {
                        console.warn('Gender chart skipped - no data or canvas not found');
                    }
                })
                .catch(error => console.error('❌ Gender chart error:', error));

            // Academic Chart with real data
            fetch('get_academic_stats.php')
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('academicChart');
                    if (ctx && data.success && data.data) {
                        new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: Object.keys(data.data),
                                datasets: [{
                                    data: Object.values(data.data),
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
                })
                .catch(error => console.error('Academic chart error:', error));

            // Department Chart with real data
            fetch('get_department_stats.php')
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('departmentChart');
                    if (ctx && data.success && data.data) {
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: Object.keys(data.data),
                                datasets: [{
                                    label: 'Employees',
                                    data: Object.values(data.data),
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
                })
                .catch(error => console.error('Department chart error:', error));

            // Job Level Chart with real data
            fetch('get_job_level_stats.php')
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('jobLevelChart');
                    if (ctx && data.success && data.data) {
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: Object.keys(data.data),
                                datasets: [{
                                    label: 'Staff Count',
                                    data: Object.values(data.data),
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
                })
                .catch(error => console.error('Job level chart error:', error));

            // Status Chart with real data
            fetch('get_status_stats.php')
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('statusChart');
                    if (ctx && data.success && data.data) {
                        new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: Object.keys(data.data),
                                datasets: [{
                                    data: Object.values(data.data),
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
                                    legend: { position: 'bottom' }
                                }
                            }
                        });
                    }
                })
                .catch(error => console.error('Status chart error:', error));

            // Kebele Chart - Modern Horizontal Breakdown
            fetch('get_kebele_stats.php')
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('kebeleChart');
                    if (ctx && data.success && data.data) {
                        const kebeles = Object.keys(data.data);
                        const activeData = kebeles.map(k => data.data[k].active);
                        const leaveData = kebeles.map(k => data.data[k]['on-leave']);
                        const inactiveData = kebeles.map(k => data.data[k].inactive);

                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: kebeles,
                                datasets: [
                                    {
                                        label: 'Active',
                                        data: activeData,
                                        backgroundColor: '#4cb5ae',
                                        borderRadius: 4
                                    },
                                    {
                                        label: 'On Leave',
                                        data: leaveData,
                                        backgroundColor: '#ffc107',
                                        borderRadius: 4
                                    },
                                    {
                                        label: 'Inactive',
                                        data: inactiveData,
                                        backgroundColor: '#ff7e5f',
                                        borderRadius: 4
                                    }
                                ]
                            },
                            options: {
                                indexAxis: 'y', // Modern horizontal look
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    x: { stacked: true, grid: { display: false } },
                                    y: { stacked: true, grid: { display: false } }
                                },
                                plugins: {
                                    title: { display: true, text: 'Workforce Details by Kebele', font: { size: 16, weight: 'bold' } },
                                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10 } },
                                    tooltip: { mode: 'index', intersect: false }
                                }
                            }
                        });
                    }
                })
                .catch(error => console.error('Kebele chart error:', error));
        }


        function loadDashboardData() {
            console.log('Loading dashboard data...');
            
            fetch('get_wereda_dashboard_data.php')
                .then(response => {
                    console.log('Dashboard API response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(text => {
                    console.log('Dashboard raw response:', text.substring(0, 200));
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Dashboard JSON parse error:', e);
                        console.error('Response was:', text);
                        throw new Error('Invalid JSON: ' + e.message);
                    }
                })
                .then(data => {
                    console.log('Dashboard parsed data:', data);
                    if (data.success) {
                        console.log('Updating stats...');
                        updateStats(data.stats);
                        console.log('Rendering employees...');
                        renderRecentEmployees(data.recent_employees);
                        console.log('Rendering leave requests...');
                        renderLeaveRequests(data.leave_requests);
                        console.log('Rendering job postings...');
                        renderJobPostings(data.job_postings);
                        console.log('Rendering trainings...');
                        renderTrainings(data.trainings);
                        console.log('Dashboard loaded successfully!');
                    } else {
                        console.error('Dashboard API error:', data.message);
                        alert('Dashboard Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Dashboard fetch error:', error);
                    alert('Failed to load dashboard data: ' + error.message + '\nCheck console (F12) for details.');
                });
        }

        function updateStats(stats) {
            if (!stats) return;
            if (document.getElementById('stat-total')) document.getElementById('stat-total').textContent = stats.totalEmployees || 0;
            if (document.getElementById('stat-active')) document.getElementById('stat-active').textContent = stats.activeEmployees || 0;
            if (document.getElementById('stat-leave')) document.getElementById('stat-leave').textContent = stats.onLeave || 0;
            if (document.getElementById('stat-attendance')) document.getElementById('stat-attendance').textContent = (stats.attendanceRate || 0) + '%';
            
            // Update leave request badge
            const badge = document.getElementById('leaveRequestBadge');
            if (badge && stats.onLeave > 0) {
                badge.textContent = stats.onLeave;
                badge.style.display = 'inline-block';
            }
        }

        function renderRecentEmployees(employees) {
            const tbody = document.getElementById('recentEmployeesBody');
            if (!tbody) return;
            tbody.innerHTML = '';
            if (!employees || employees.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 20px;">No recent employees found.</td></tr>';
                return;
            }
            employees.forEach(emp => {
                const initials = (emp.first_name[0] || '') + (emp.last_name[0] || '');
                const deptClass = (emp.department_assigned || 'unassigned').replace(/\s+/g, '').toLowerCase();
                const statusClass = (emp.status || 'inactive').toLowerCase();
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td data-label="Employee"><div class="employee-info"><div class="employee-avatar">${initials}</div><div><div class="employee-name">${emp.first_name} ${emp.last_name}</div><div class="employee-id">${emp.email || ''}</div></div></div></td>
                    <td data-label="Employee ID">${emp.employee_id}</td>
                    <td data-label="Department"><span class="department-badge ${deptClass}">${emp.department_assigned || 'Unassigned'}</span></td>
                    <td data-label="Position">${emp.position}</td>
                    <td data-label="Join Date">${new Date(emp.join_date).toLocaleDateString()}</td>
                    <td data-label="Status"><span class="status-badge ${statusClass}">${emp.status}</span></td>
                    <td data-label="Actions"><div class="action-buttons"><button class="action-btn view" onclick="window.location.href='wereda_hr_employee.php?view_id=${emp.id}'"><i class="fas fa-eye"></i></button><button class="action-btn edit" onclick="window.location.href='edit_employee.php?id=${emp.id}'"><i class="fas fa-edit"></i></button></div></td>`;
                tbody.appendChild(tr);
            });
        }

        function renderLeaveRequests(requests) {
            const container = document.getElementById('leaveRequestsList');
            if (!container) return;
            container.innerHTML = '';
            if (!requests || requests.length === 0) {
                container.innerHTML = '<p style="padding: 20px; color: var(--gray);">No pending leave requests.</p>';
                return;
            }
            requests.forEach(req => {
                const initials = (req.first_name[0] || '') + (req.last_name[0] || '');
                const div = document.createElement('div');
                div.className = 'leave-request-card';
                div.innerHTML = `<div class="leave-header"><div class="leave-employee"><div class="employee-avatar">${initials}</div><div><div class="employee-name">${req.first_name} ${req.last_name}</div><div class="employee-id">${req.department_assigned}</div></div></div><div class="leave-type">${req.leave_type} Leave</div></div><div class="leave-dates"><div class="leave-date"><div class="leave-date-label">From</div><div class="leave-date-value">${new Date(req.start_date).toLocaleDateString()}</div></div><div class="leave-date"><div class="leave-date-label">To</div><div class="leave-date-value">${new Date(req.end_date).toLocaleDateString()}</div></div></div><p style="margin-bottom: 15px; color: var(--gray);">${req.reason || 'No reason provided.'}</p><div class="leave-actions"><button class="leave-action-btn approve" onclick="approveLeave(${req.id})">Approve</button><button class="leave-action-btn reject" onclick="rejectLeave(${req.id})">Reject</button></div>`;
                container.appendChild(div);
            });
        }

        function renderJobPostings(jobs) {
            const container = document.getElementById('jobPostingsList');
            if(!container) return;
            container.innerHTML = '';
            if (!jobs || jobs.length === 0) {
                container.innerHTML = '<p style="padding: 20px; color: var(--gray);">No open positions found.</p>';
                return;
            }
            jobs.forEach(job => {
                const div = document.createElement('div');
                div.className = 'job-card';
                div.innerHTML = `<div class="job-header"><div><div class="job-title">${job.title}</div><div class="job-department">${job.department}</div></div><div class="job-type">${job.employment_type}</div></div><div class="job-details"><div class="job-detail"><i class="fas fa-map-marker-alt"></i> <span>${job.location || 'Wereda Health Center'}</span></div><div class="job-detail"><i class="fas fa-clock"></i> <span>Posted: ${new Date(job.posted_at).toLocaleDateString()}</span></div></div><div class="job-actions"><button class="job-action-btn">View Details</button><button class="job-action-btn">Edit</button></div>`;
                container.appendChild(div);
            });
        }

        function renderTrainings(trainings) {
            const container = document.getElementById('trainingList');
            if(!container) return;
            container.innerHTML = '';
            if (!trainings || trainings.length === 0) {
                container.innerHTML = '<p style="padding: 20px; color: var(--gray);">No upcoming training sessions.</p>';
                return;
            }
            trainings.forEach(training => {
                const div = document.createElement('div');
                div.className = 'training-card';
                div.innerHTML = `<div class="training-header"><div><div class="training-title">${training.title}</div><div class="training-category">${training.category || 'Professional Development'}</div></div><div class="training-status upcoming">Upcoming</div></div><div class="training-details"><div class="training-detail"><i class="fas fa-calendar-alt"></i> <span>${new Date(training.session_date).toLocaleDateString()}</span></div><div class="training-detail"><i class="fas fa-clock"></i> <span>${training.start_time.substring(0,5)} - ${training.end_time.substring(0,5)}</span></div><div class="training-detail"><i class="fas fa-map-marker-alt"></i> <span>${training.venue}</span></div></div><div class="training-actions"><button class="training-action-btn primary">Register Employees</button></div>`;
                container.appendChild(div);
            });
        }
        
        // Leave request functions
        function approveLeave(leaveId) {
            if (confirm('Approve this leave request?')) {
                fetch('approve_leave.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ leave_id: leaveId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Leave request approved successfully!');
                        loadDashboardData();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error approving leave request');
                });
            }
        }

        function rejectLeave(leaveId) {
            if (confirm('Reject this leave request?')) {
                fetch('reject_leave.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ leave_id: leaveId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Leave request rejected successfully!');
                        loadDashboardData();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error rejecting leave request');
                });
            }
        }

        // Dashboard initialization
        document.addEventListener('DOMContentLoaded', function() {
            // Stats are loaded globally by scripts.js
            // Any dashboard-specific init goes here
        });
    </script>
    <script src="scripts.js"></script>
</body>
</html>
