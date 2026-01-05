<?php
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

// Initialize variables
$totalEmployees = 0;
$activeEmployees = 0;
$onLeave = 0;
$openPositions = 0;
$pendingLeave = 0;
$attendanceRate = 0;
$employees = [];
$leaveRequests = [];
$jobPostings = [];
$trainings = [];
$activities = [];

try {
    $conn = getDBConnection();
    
    // 1. Get Stats
    $woreda_wildcard = "%$user_woreda%";
    
    // Total Employees
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM employees WHERE woreda LIKE ?");
    $stmt->bind_param("s", $woreda_wildcard);
    $stmt->execute();
    $totalEmployees = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
    
    // Active Employees
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM employees WHERE woreda LIKE ? AND status = 'active'");
    $stmt->bind_param("s", $woreda_wildcard);
    $stmt->execute();
    $activeEmployees = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
    
    // On Leave
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM employees WHERE woreda LIKE ? AND status = 'on-leave'");
    $stmt->bind_param("s", $woreda_wildcard);
    $stmt->execute();
    $onLeave = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
    
    // Open Positions (Assuming postings are per woreda or global if woreda is null, let's filter by woreda if column exists, else global)
    // Checking schema: job_postings has 'woreda' column.
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM job_postings WHERE (woreda LIKE ? OR woreda IS NULL) AND status = 'open'");
    $stmt->bind_param("s", $woreda_wildcard);
    $stmt->execute();
    $openPositions = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
    
    // Pending Leave Requests
    // Join with employees to filter by woreda
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM leave_requests lr JOIN employees e ON lr.employee_id = e.employee_id WHERE e.woreda LIKE ? AND lr.status = 'pending'");
    $stmt->bind_param("s", $woreda_wildcard);
    $stmt->execute();
    $pendingLeave = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
    
    // Calculate attendance rate
    $attendanceRate = ($totalEmployees > 0) ? round(($activeEmployees / $totalEmployees) * 100) : 0;

    // 2. Get Recent Employees
    $stmt = $conn->prepare("SELECT * FROM employees WHERE woreda LIKE ? ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param("s", $woreda_wildcard);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $employees[] = $row;
    }

    // 3. Get Pending Leave Requests
    $stmt = $conn->prepare("SELECT lr.*, e.first_name, e.last_name, e.department_assigned FROM leave_requests lr JOIN employees e ON lr.employee_id = e.employee_id WHERE e.woreda LIKE ? AND lr.status = 'pending' ORDER BY lr.created_at DESC LIMIT 5");
    $stmt->bind_param("s", $woreda_wildcard);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $leaveRequests[] = $row;
    }

    // 4. Get Open Job Postings
    $stmt = $conn->prepare("SELECT * FROM job_postings WHERE (woreda LIKE ? OR woreda IS NULL) AND status = 'open' ORDER BY posted_at DESC LIMIT 5");
    $stmt->bind_param("s", $woreda_wildcard);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $jobPostings[] = $row;
    }

    // 5. Get Upcoming Trainings
    $stmt = $conn->prepare("SELECT * FROM training_sessions WHERE (woreda LIKE ? OR woreda IS NULL) AND session_date >= CURDATE() ORDER BY session_date ASC LIMIT 3");
    $stmt->bind_param("s", $woreda_wildcard);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $trainings[] = $row;
    }

    // 6. Get Recent Activities (Union of Employees, Leaves, Jobs)
    // We can do this via separate queries and merge in PHP for simplicity
    
    // New Employees
    foreach ($employees as $emp) {
        $activities[] = [
            'type' => 'user-plus',
            'title' => 'New Employee Added',
            'desc' => "{$emp['first_name']} {$emp['last_name']} joined {$emp['department_assigned']}",
            'time' => $emp['created_at'],
            'color' => '#4cb5ae',
            'bg' => '#4cb5ae15'
        ];
    }
    
    // Recent Leaves (even if not pending, let's fetch recent ones)
    $stmt = $conn->prepare("SELECT lr.*, e.first_name, e.last_name FROM leave_requests lr JOIN employees e ON lr.employee_id = e.employee_id WHERE e.woreda LIKE ? ORDER BY lr.created_at DESC LIMIT 3");
    $stmt->bind_param("s", $woreda_wildcard);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $activities[] = [
            'type' => 'umbrella-beach',
            'title' => 'Leave Request',
            'desc' => "{$row['first_name']} {$row['last_name']} requested {$row['leave_type']} leave",
            'time' => $row['created_at'],
            'color' => '#ff7e5f',
            'bg' => '#ff7e5f15'
        ];
    }
    
    // New Jobs
    $stmt = $conn->prepare("SELECT * FROM job_postings WHERE (woreda LIKE ? OR woreda IS NULL) ORDER BY posted_at DESC LIMIT 3");
    $stmt->bind_param("s", $woreda_wildcard);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $activities[] = [
            'type' => 'bullhorn',
            'title' => 'Job Posted',
            'desc' => "New position: {$row['title']}",
            'time' => $row['posted_at'],
            'color' => '#ffc107',
            'bg' => '#ffc10715'
        ];
    }
    
    // Sort activities by time desc
    usort($activities, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });
    
    // Limit to 5
    $activities = array_slice($activities, 0, 5);

} catch (Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
}

function time_elapsed($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d == 0 && $diff->h == 0 && $diff->i < 60) return "Just now";
    if ($diff->d == 0 && $diff->h < 24) return $diff->h . " hours ago";
    if ($diff->d < 7) return $diff->d . " days ago";
    return date('d M Y', strtotime($datetime));
}

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
                        <p>Here's what's happening with your Woreda workforce today. You have <span class="highlight"><?php echo $pendingLeave; ?> pending</span> leave requests to review.</p>
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
                            <h3 id="totalEmployees"><?php echo $totalEmployees; ?></h3>
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
                            <h3 id="openPositions"><?php echo $openPositions; ?></h3>
                            <div class="stat-footer">
                                <p>Open Positions</p>
                                <span class="trend down" id="trendPositions">-12%</span>
                            </div>
                        </div>
                    </div>

                    <div class="hr-stat-card on-leave">
                        <div class="hr-stat-icon">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3 id="onLeave"><?php echo $onLeave; ?></h3>
                            <div class="stat-footer">
                                <p>On Leave Today</p>
                                <span class="trend up" id="trendLeave">+2.4%</span>
                            </div>
                        </div>
                    </div>

                    <div class="hr-stat-card attendance">
                        <div class="hr-stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3 id="attendanceRate"><?php echo $attendanceRate; ?>%</h3>
                            <div class="stat-footer">
                                <p>Attendance Rate</p>
                                <span class="trend up" id="trendAttendance">+1.2%</span>
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
                        <?php if($pendingLeave > 0): ?>
                        <span id="pendingLeaveCount" style="font-size: 0.8rem; margin-top: 5px; color: var(--accent);"><?php echo $pendingLeave; ?> pending</span>
                        <?php endif; ?>
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
                                <tbody>
                                    <?php if (count($employees) > 0): ?>
                                        <?php foreach ($employees as $employee): ?>
                                            <?php
                                            $joinDate = $employee['join_date'] ? date('d/m/Y', strtotime($employee['join_date'])) : 'N/A';
                                            $statusClass = strtolower($employee['status']);
                                            $dept = $employee['department_assigned'] ?: 'Unassigned';
                                            $deptClass = strtolower(str_replace(' ', '', $dept));
                                            $status = ucfirst(str_replace('-', ' ', $employee['status']));
                                            $initials = substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1);
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="employee-info">
                                                        <div class="employee-avatar"><?php echo $initials; ?></div>
                                                        <div>
                                                            <div class="employee-name"><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></div>
                                                            <div class="employee-id"><?php echo htmlspecialchars($employee['email']); ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($employee['employee_id']); ?></td>
                                                <td><span class="department-badge <?php echo $deptClass; ?>"><?php echo htmlspecialchars($dept); ?></span></td>
                                                <td><?php echo htmlspecialchars($employee['position']); ?></td>
                                                <td><?php echo $joinDate; ?></td>
                                                <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button class="action-btn view" onclick="window.location.href='wereda_hr_employee.php?id=<?php echo $employee['id']; ?>'"><i class="fas fa-eye"></i></button>
                                                        <button class="action-btn edit" onclick="window.location.href='wereda_hr_employee.php?id=<?php echo $employee['id']; ?>&edit=true'"><i class="fas fa-edit"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" style="text-align:center; padding: 20px;">No employees found.</td></tr>
                                    <?php endif; ?>
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
                            <?php if (count($activities) > 0): ?>
                                <?php foreach ($activities as $act): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-icon" style="background: <?php echo $act['bg']; ?>; color: <?php echo $act['color']; ?>;">
                                            <i class="fas fa-<?php echo $act['type']; ?>"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="timeline-header">
                                                <span class="timeline-title"><?php echo htmlspecialchars($act['title']); ?></span>
                                                <span class="timeline-time"><?php echo time_elapsed($act['time']); ?></span>
                                            </div>
                                            <p class="timeline-desc"><?php echo htmlspecialchars($act['desc']); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="padding: 20px; color: var(--gray);">No recent activity.</p>
                            <?php endif; ?>
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
                        <div class="leave-requests">
                            <?php if (count($leaveRequests) > 0): ?>
                                <?php foreach ($leaveRequests as $request): ?>
                                    <?php
                                    $initials = substr($request['first_name'], 0, 1) . substr($request['last_name'], 0, 1);
                                    $startDate = date('d M Y', strtotime($request['start_date']));
                                    $endDate = date('d M Y', strtotime($request['end_date']));
                                    $leaveType = ucfirst($request['leave_type']) . ' Leave';
                                    ?>
                                    <div class="leave-request-card">
                                        <div class="leave-header">
                                            <div class="leave-employee">
                                                <div class="employee-avatar"><?php echo $initials; ?></div>
                                                <div>
                                                    <div class="employee-name"><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></div>
                                                    <div class="employee-id"><?php echo htmlspecialchars($request['department_assigned']); ?></div>
                                                </div>
                                            </div>
                                            <div class="leave-type"><?php echo $leaveType; ?></div>
                                        </div>
                                        <div class="leave-dates">
                                            <div class="leave-date">
                                                <div class="leave-date-label">From</div>
                                                <div class="leave-date-value"><?php echo $startDate; ?></div>
                                            </div>
                                            <div class="leave-date">
                                                <div class="leave-date-label">To</div>
                                                <div class="leave-date-value"><?php echo $endDate; ?></div>
                                            </div>
                                        </div>
                                        <p style="margin-bottom: 15px; color: var(--gray);"><?php echo htmlspecialchars($request['reason'] ?: 'No reason provided.'); ?></p>
                                        <div class="leave-actions">
                                            <button class="leave-action-btn approve" onclick="approveLeave(<?php echo $request['id']; ?>)">Approve</button>
                                            <button class="leave-action-btn reject" onclick="rejectLeave(<?php echo $request['id']; ?>)">Reject</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="padding: 20px; color: var(--gray);">No pending leave requests.</p>
                            <?php endif; ?>
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
                        <div class="recruitment-jobs">
                            <?php if (count($jobPostings) > 0): ?>
                                <?php foreach ($jobPostings as $job): ?>
                                    <?php
                                    $postedDate = date('d/m/Y', strtotime($job['posted_at']));
                                    ?>
                                    <div class="job-card">
                                        <div class="job-header">
                                            <div>
                                                <div class="job-title"><?php echo htmlspecialchars($job['title']); ?></div>
                                                <div class="job-department"><?php echo htmlspecialchars($job['department']); ?></div>
                                            </div>
                                            <div class="job-type"><?php echo ucfirst($job['employment_type']); ?></div>
                                        </div>
                                        <div class="job-details">
                                            <div class="job-detail">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span><?php echo htmlspecialchars($job['location'] ?: 'Wereda Health Center'); ?></span>
                                            </div>
                                            <div class="job-detail">
                                                <i class="fas fa-clock"></i>
                                                <span>Posted: <?php echo $postedDate; ?></span>
                                            </div>
                                        </div>
                                        <div class="job-actions">
                                            <button class="job-action-btn" onclick="alert('Viewing details for <?php echo htmlspecialchars($job['title']); ?>')">View Details</button>
                                            <button class="job-action-btn" onclick="alert('Editing <?php echo htmlspecialchars($job['title']); ?> posting')">Edit</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="padding: 20px; color: var(--gray);">No open positions found.</p>
                            <?php endif; ?>
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
                        <div class="training-courses">
                            <?php if (count($trainings) > 0): ?>
                                <?php foreach ($trainings as $training): ?>
                                    <?php
                                    $sessionDate = date('d M Y', strtotime($training['session_date']));
                                    $timeRange = substr($training['start_time'], 0, 5) . ' - ' . substr($training['end_time'], 0, 5);
                                    ?>
                                    <div class="training-card">
                                        <div class="training-header">
                                            <div>
                                                <div class="training-title"><?php echo htmlspecialchars($training['title']); ?></div>
                                                <div class="training-category"><?php echo htmlspecialchars($training['category'] ?? 'Professional Development'); ?></div>
                                            </div>
                                            <div class="training-status upcoming"><?php echo ucfirst($training['status']); ?></div>
                                        </div>
                                        <div class="training-details">
                                            <div class="training-detail">
                                                <i class="fas fa-calendar-alt"></i>
                                                <span><?php echo $sessionDate; ?></span>
                                            </div>
                                            <div class="training-detail">
                                                <i class="fas fa-clock"></i>
                                                <span><?php echo $timeRange; ?></span>
                                            </div>
                                            <div class="training-detail">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span><?php echo htmlspecialchars($training['venue']); ?></span>
                                            </div>
                                        </div>
                                        <div class="training-actions">
                                            <button class="training-action-btn primary">Register Employees</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="padding: 20px; color: var(--gray);">No upcoming training sessions.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Chart creation with real data from API
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Chart !== 'undefined') {
                loadAllCharts();
            }
        });

        function loadAllCharts() {
            // Gender Chart with real data
            fetch('get_gender_stats.php')
                .then(response => response.json())
                .then(data => {
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
                    }
                })
                .catch(error => console.error('Gender chart error:', error));

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
                        location.reload();
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
                        location.reload();
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
