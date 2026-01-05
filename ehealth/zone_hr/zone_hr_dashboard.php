<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'zone_hr') {
    header('Location: ../index.html');
    exit();
}
include '../db.php';
$conn = getDBConnection();

// Get zone from session
$zone = $_SESSION['zone'];

// Get aggregated stats
$total_employees = $conn->query("SELECT COUNT(*) as count FROM employees WHERE zone = '$zone'")->fetch_assoc()['count'];
$open_positions = $conn->query("SELECT COUNT(*) as count FROM job_postings WHERE status = 'open'")->fetch_assoc()['count']; // Assuming zone-level jobs
$on_leave_today = $conn->query("SELECT COUNT(*) as count FROM leave_requests WHERE status = 'approved' AND CURDATE() BETWEEN start_date AND end_date AND employee_id IN (SELECT employee_id FROM employees WHERE zone = '$zone')")->fetch_assoc()['count'];
$attendance_rate = 94; // Placeholder, would need attendance table

$pending_leaves = $conn->query("SELECT COUNT(*) as count FROM leave_requests WHERE status = 'pending' AND employee_id IN (SELECT employee_id FROM employees WHERE zone = '$zone')")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Zone HR Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>

        <?php
        $page_title = 'Zone Human Resources Management';
        include 'navbar.php';
        ?>

            <!-- HR Dashboard -->
            <div class="hr-dashboard">
                <!-- HR Stats -->
                <div class="hr-stats">
                    <div class="hr-stat-card employees">
                        <div class="hr-stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3><?php echo $total_employees; ?></h3>
                            <p>Total Employees</p>
                        </div>
                    </div>

                    <div class="hr-stat-card vacancy">
                        <div class="hr-stat-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3><?php echo $open_positions; ?></h3>
                            <p>Open Positions</p>
                        </div>
                    </div>

                    <div class="hr-stat-card on-leave">
                        <div class="hr-stat-icon">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3><?php echo $on_leave_today; ?></h3>
                            <p>On Leave Today</p>
                        </div>
                    </div>

                    <div class="hr-stat-card attendance">
                        <div class="hr-stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3><?php echo $attendance_rate; ?>%</h3>
                            <p>Attendance Rate</p>
                        </div>
                    </div>
                </div>

                <!-- HR Charts -->
                <div class="hr-charts">
                    <div class="chart-container">
                        <h3>Employee Distribution by Department</h3>
                        <canvas id="departmentChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h3>Employee Status Overview</h3>
                        <canvas id="statusChart"></canvas>
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
                        <span style="font-size: 0.8rem; margin-top: 5px; color: var(--accent);"><?php echo $pending_leaves; ?> pending</span>
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
                            <button class="section-action-btn">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <button class="section-action-btn">
                                <i class="fas fa-download"></i> Export
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
                                        <th>Wereda</th>
                                        <th>Department</th>
                                        <th>Position</th>
                                        <th>Join Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="employeesTableBody">
                                    <!-- Employees will be loaded here -->
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
                        <div class="leave-requests" id="leaveRequestsContainer">
                            <!-- Leave requests will be loaded here -->
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
                        <div class="recruitment-jobs" id="recruitmentJobsContainer">
                            <!-- Jobs will be loaded here -->
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
                        <div class="training-courses" id="trainingCoursesContainer">
                            <!-- Training courses will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modals -->
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

    <!-- Process Payroll Modal -->
    <div class="modal" id="processPayrollModal">
        <div class="modal-content large-modal">
            <span class="close-modal" id="closePayrollModal">&times;</span>
            <h2 class="modal-title">Process Payroll</h2>

            <div class="payroll-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="payrollPeriodStart">Period Start *</label>
                        <input type="date" id="payrollPeriodStart" required>
                    </div>
                    <div class="form-group">
                        <label for="payrollPeriodEnd">Period End *</label>
                        <input type="date" id="payrollPeriodEnd" required>
                    </div>
                </div>

                <div class="payroll-employees">
                    <h3>Select Employees for Payroll</h3>
                    <div id="employeePayrollList">
                        <!-- Employee list will be loaded here -->
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="submit-btn" id="calculatePayrollBtn">Calculate Payroll</button>
                    <button type="button" class="submit-btn" id="processPayrollBtn">Process Payroll</button>
                    <button type="button" class="cancel-btn" id="cancelPayrollBtn">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Training Modal -->
    <div class="modal" id="scheduleTrainingModal">
        <div class="modal-content">
            <span class="close-modal" id="closeTrainingModal">&times;</span>
            <h2 class="modal-title">Schedule Training Session</h2>

            <form id="trainingForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="trainingTitle">Training Title *</label>
                        <input type="text" id="trainingTitle" required>
                    </div>
                    <div class="form-group">
                        <label for="trainingTrainer">Trainer</label>
                        <input type="text" id="trainingTrainer">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="trainingDate">Date *</label>
                        <input type="date" id="trainingDate" required>
                    </div>
                    <div class="form-group">
                        <label for="trainingStartTime">Start Time</label>
                        <input type="time" id="trainingStartTime">
                    </div>
                    <div class="form-group">
                        <label for="trainingEndTime">End Time</label>
                        <input type="time" id="trainingEndTime">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="trainingVenue">Venue</label>
                        <input type="text" id="trainingVenue">
                    </div>
                    <div class="form-group">
                        <label for="maxParticipants">Max Participants</label>
                        <input type="number" id="maxParticipants" min="1">
                    </div>
                </div>

                <div class="form-group">
                    <label for="trainingDescription">Description</label>
                    <textarea id="trainingDescription" placeholder="Training objectives, agenda, etc."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">Schedule Training</button>
                    <button type="button" class="cancel-btn" id="cancelTrainingBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Generate Report Modal -->
    <div class="modal" id="generateReportModal">
        <div class="modal-content">
            <span class="close-modal" id="closeReportModal">&times;</span>
            <h2 class="modal-title">Generate HR Report</h2>

            <form id="reportForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="reportType">Report Type *</label>
                        <select id="reportType" required>
                            <option value="">Select Report Type</option>
                            <option value="employee_summary">Employee Summary</option>
                            <option value="payroll_report">Payroll Report</option>
                            <option value="leave_report">Leave Report</option>
                            <option value="recruitment_report">Recruitment Report</option>
                            <option value="training_report">Training Report</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reportFormat">Format</label>
                        <select id="reportFormat">
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="reportStartDate">Start Date</label>
                        <input type="date" id="reportStartDate">
                    </div>
                    <div class="form-group">
                        <label for="reportEndDate">End Date</label>
                        <input type="date" id="reportEndDate">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">Generate Report</button>
                    <button type="button" class="cancel-btn" id="cancelReportBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="scripts.js"></script>
    <script>
        // Page-specific JavaScript for dashboard
        const scheduleTrainingBtns = document.querySelectorAll('#scheduleTrainingBtn, #scheduleTrainingBtn2');
        const processPayrollBtn = document.getElementById('processPayrollBtn');
        const generateReportBtn = document.getElementById('generateReportBtn');
        const leaveRequestsBtn = document.getElementById('leaveRequestsBtn');
        const postJobBtns = document.querySelectorAll('#postJobBtn, #postJobBtn2');

        // Modal elements
        const postJobModal = document.getElementById('postJobModal');
        const processPayrollModal = document.getElementById('processPayrollModal');
        const scheduleTrainingModal = document.getElementById('scheduleTrainingModal');
        const generateReportModal = document.getElementById('generateReportModal');

        // Close modal buttons
        const closeJobModal = document.getElementById('closeJobModal');
        const closePayrollModal = document.getElementById('closePayrollModal');
        const closeTrainingModal = document.getElementById('closeTrainingModal');
        const closeReportModal = document.getElementById('closeReportModal');

        // Schedule Training Button
        scheduleTrainingBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                scheduleTrainingModal.style.display = 'block';
            });
        });

        // Process Payroll Button
        processPayrollBtn.addEventListener('click', () => {
            processPayrollModal.style.display = 'block';
            loadEmployeesForPayroll();
        });

        // Generate Report Button
        generateReportBtn.addEventListener('click', () => {
            generateReportModal.style.display = 'block';
        });

        // Post Job Buttons
        postJobBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                postJobModal.style.display = 'block';
            });
        });

        // Leave Requests Button
        leaveRequestsBtn.addEventListener('click', () => {
            document.getElementById('leaveSection').scrollIntoView({ behavior: 'smooth' });
        });

        // Close modals
        closeJobModal.addEventListener('click', () => {
            postJobModal.style.display = 'none';
        });

        closePayrollModal.addEventListener('click', () => {
            processPayrollModal.style.display = 'none';
        });

        closeTrainingModal.addEventListener('click', () => {
            scheduleTrainingModal.style.display = 'none';
        });

        closeReportModal.addEventListener('click', () => {
            generateReportModal.style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', (event) => {
            if (event.target === postJobModal) postJobModal.style.display = 'none';
            if (event.target === processPayrollModal) processPayrollModal.style.display = 'none';
            if (event.target === scheduleTrainingModal) scheduleTrainingModal.style.display = 'none';
            if (event.target === generateReportModal) generateReportModal.style.display = 'none';
        });

        // Cancel buttons
        document.getElementById('cancelJobBtn').addEventListener('click', () => {
            postJobModal.style.display = 'none';
        });

        document.getElementById('cancelPayrollBtn').addEventListener('click', () => {
            processPayrollModal.style.display = 'none';
        });

        document.getElementById('cancelTrainingBtn').addEventListener('click', () => {
            scheduleTrainingModal.style.display = 'none';
        });

        document.getElementById('cancelReportBtn').addEventListener('click', () => {
            generateReportModal.style.display = 'none';
        });

        // Form submissions
        document.getElementById('jobForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitJobPosting();
        });

        document.getElementById('trainingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitTrainingSession();
        });

        document.getElementById('reportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            generateReport();
        });

        // Payroll buttons
        document.getElementById('calculatePayrollBtn').addEventListener('click', calculatePayroll);
        document.getElementById('processPayrollBtn').addEventListener('click', processPayroll);

        // Functions
        function loadEmployeesForPayroll() {
            fetch('get_employees.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('employeePayrollList');
                    container.innerHTML = '';

                    data.forEach(employee => {
                        const employeeDiv = document.createElement('div');
                        employeeDiv.className = 'payroll-employee-item';
                        employeeDiv.innerHTML = `
                            <input type="checkbox" id="emp_${employee.employee_id}" value="${employee.employee_id}">
                            <label for="emp_${employee.employee_id}">
                                ${employee.first_name} ${employee.last_name} (${employee.employee_id}) - Basic: $${employee.salary || 0}
                            </label>
                            <div class="payroll-inputs" style="display: none;">
                                <input type="number" placeholder="Allowances" class="allowance-input" data-emp="${employee.employee_id}">
                                <input type="number" placeholder="Deductions" class="deduction-input" data-emp="${employee.employee_id}">
                                <span class="net-salary" data-emp="${employee.employee_id}">Net: $0</span>
                            </div>
                        `;
                        container.appendChild(employeeDiv);

                        // Show/hide inputs on checkbox change
                        const checkbox = employeeDiv.querySelector('input[type="checkbox"]');
                        const inputs = employeeDiv.querySelector('.payroll-inputs');
                        checkbox.addEventListener('change', () => {
                            inputs.style.display = checkbox.checked ? 'block' : 'none';
                        });
                    });
                })
                .catch(error => console.error('Error loading employees:', error));
        }

        function calculatePayroll() {
            const selectedEmployees = document.querySelectorAll('#employeePayrollList input[type="checkbox"]:checked');
            selectedEmployees.forEach(checkbox => {
                const empId = checkbox.value;
                const basicSalary = parseFloat(checkbox.parentElement.querySelector('label').textContent.match(/\$(\d+)/)[1]);
                const allowanceInput = document.querySelector(`.allowance-input[data-emp="${empId}"]`);
                const deductionInput = document.querySelector(`.deduction-input[data-emp="${empId}"]`);
                const netSalarySpan = document.querySelector(`.net-salary[data-emp="${empId}"]`);

                const allowances = parseFloat(allowanceInput.value) || 0;
                const deductions = parseFloat(deductionInput.value) || 0;
                const netSalary = basicSalary + allowances - deductions;

                netSalarySpan.textContent = `Net: $${netSalary.toFixed(2)}`;
            });
        }

        function processPayroll() {
            const periodStart = document.getElementById('payrollPeriodStart').value;
            const periodEnd = document.getElementById('payrollPeriodEnd').value;

            if (!periodStart || !periodEnd) {
                alert('Please select payroll period.');
                return;
            }

            const selectedEmployees = document.querySelectorAll('#employeePayrollList input[type="checkbox"]:checked');
            const payrollData = [];

            selectedEmployees.forEach(checkbox => {
                const empId = checkbox.value;
                const basicSalary = parseFloat(checkbox.parentElement.querySelector('label').textContent.match(/\$(\d+)/)[1]);
                const allowances = parseFloat(document.querySelector(`.allowance-input[data-emp="${empId}"]`).value) || 0;
                const deductions = parseFloat(document.querySelector(`.deduction-input[data-emp="${empId}"]`).value) || 0;
                const netSalary = basicSalary + allowances - deductions;

                payrollData.push({
                    employee_id: empId,
                    period_start: periodStart,
                    period_end: periodEnd,
                    basic_salary: basicSalary,
                    allowances: allowances,
                    deductions: deductions,
                    net_salary: netSalary
                });
            });

            if (payrollData.length === 0) {
                alert('Please select at least one employee.');
                return;
            }

            fetch('process_payroll.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payrollData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Payroll processed successfully!');
                    processPayrollModal.style.display = 'none';
                } else {
                    alert('Error processing payroll: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function submitJobPosting() {
            const formData = {
                title: document.getElementById('jobTitle').value,
                department: document.getElementById('jobDepartment').value,
                description: document.getElementById('jobDescription').value,
                requirements: document.getElementById('qualifications').value,
                salary_range: document.getElementById('salaryRange').value,
                location: document.getElementById('jobLocation').value,
                employment_type: document.getElementById('jobType').value,
                application_deadline: document.getElementById('applicationDeadline').value
            };

            fetch('post_job.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Job posted successfully!');
                    postJobModal.style.display = 'none';
                    document.getElementById('jobForm').reset();
                    // Refresh job listings
                    location.reload();
                } else {
                    alert('Error posting job: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function submitTrainingSession() {
            const formData = {
                title: document.getElementById('trainingTitle').value,
                description: document.getElementById('trainingDescription').value,
                trainer: document.getElementById('trainingTrainer').value,
                session_date: document.getElementById('trainingDate').value,
                start_time: document.getElementById('trainingStartTime').value,
                end_time: document.getElementById('trainingEndTime').value,
                venue: document.getElementById('trainingVenue').value,
                max_participants: document.getElementById('maxParticipants').value
            };

            fetch('schedule_training.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Training scheduled successfully!');
                    scheduleTrainingModal.style.display = 'none';
                    document.getElementById('trainingForm').reset();
                    // Refresh training listings
                    location.reload();
                } else {
                    alert('Error scheduling training: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function generateReport() {
            const formData = {
                type: document.getElementById('reportType').value,
                format: document.getElementById('reportFormat').value,
                start_date: document.getElementById('reportStartDate').value,
                end_date: document.getElementById('reportEndDate').value
            };

            fetch('generate_report.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Report generated successfully! Data: ' + JSON.stringify(data.data, null, 2));
                    generateReportModal.style.display = 'none';
                } else {
                    alert('Error generating report: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Load data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadEmployees();
            loadLeaveRequests();
            loadRecruitmentJobs();
            loadTrainingCourses();
            loadCharts();

            // Set default dates
            const today = new Date().toISOString().split('T')[0];
            const futureDate = new Date();
            futureDate.setDate(futureDate.getDate() + 30);
            document.getElementById('applicationDeadline').value = futureDate.toISOString().split('T')[0];
        });

        function loadEmployees() {
            fetch('get_employees.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('employeesTableBody');
                    tbody.innerHTML = '';

                    data.forEach(employee => {
                        const row = document.createElement('tr');
                        const initials = employee.first_name.charAt(0) + employee.last_name.charAt(0);
                        row.innerHTML = `
                            <td>
                                <div class="employee-info">
                                    <div class="employee-avatar">${initials}</div>
                                    <div>
                                        <div class="employee-name">${employee.first_name} ${employee.last_name}</div>
                                        <div class="employee-id">${employee.email}</div>
                                    </div>
                                </div>
                            </td>
                            <td>${employee.employee_id}</td>
                            <td>${employee.woreda}</td>
                            <td><span class="department-badge ${employee.department}">${ucfirst(employee.department)}</span></td>
                            <td>${employee.position}</td>
                            <td>${employee.join_date ? new Date(employee.join_date).toLocaleDateString() : 'N/A'}</td>
                            <td><span class="status-badge ${employee.status}">${ucfirst(employee.status)}</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn view" data-emp-id="${employee.employee_id}"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit" data-emp-id="${employee.employee_id}"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete" data-emp-id="${employee.employee_id}"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });

                    // Attach action listeners
                    attachEmployeeActionListeners();
                })
                .catch(error => console.error('Error loading employees:', error));
        }

        function attachEmployeeActionListeners() {
            document.querySelectorAll('.action-btn.view').forEach(btn => {
                btn.addEventListener('click', function() {
                    const empId = this.getAttribute('data-emp-id');
                    window.location.href = `edit_employee.php?id=${empId}&view=1`;
                });
            });

            document.querySelectorAll('.action-btn.edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const empId = this.getAttribute('data-emp-id');
                    window.location.href = `edit_employee.php?id=${empId}`;
                });
            });

            document.querySelectorAll('.action-btn.delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const empId = this.getAttribute('data-emp-id');
                    const employeeName = this.closest('tr').querySelector('.employee-name').textContent;
                    if (confirm(`Are you sure you want to delete ${employeeName} from the system?`)) {
                        fetch('delete_employee.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ employee_id: empId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.closest('tr').remove();
                                alert(`${employeeName} has been removed from the system.`);
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    }
                });
            });
        }

        // Add Employee Buttons
        document.querySelectorAll('#addEmployeeBtn, #addEmployeeBtn2').forEach(btn => {
            btn.addEventListener('click', function() {
                window.location.href = 'add_employee.php';
            });
        });

        function loadLeaveRequests() {
            fetch('get_leave_requests.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('leaveRequestsContainer');
                    container.innerHTML = '';

                    if (data.length === 0) {
                        container.innerHTML = '<p>No pending leave requests.</p>';
                        return;
                    }

                    data.forEach(request => {
                        const initials = request.first_name.charAt(0) + request.last_name.charAt(0);
                        const card = document.createElement('div');
                        card.className = 'leave-request-card';
                        card.innerHTML = `
                            <div class="leave-header">
                                <div class="leave-employee">
                                    <div class="employee-avatar">${initials}</div>
                                    <div>
                                        <div class="employee-name">${request.first_name} ${request.last_name}</div>
                                        <div class="employee-id">${request.woreda}</div>
                                    </div>
                                </div>
                                <div class="leave-type">${ucfirst(request.leave_type)} Leave</div>
                            </div>
                            <div class="leave-dates">
                                <div class="leave-date">
                                    <div class="leave-date-label">From</div>
                                    <div class="leave-date-value">${formatDate(request.start_date)}</div>
                                </div>
                                <div class="leave-date">
                                    <div class="leave-date-label">To</div>
                                    <div class="leave-date-value">${formatDate(request.end_date)}</div>
                                </div>
                            </div>
                            <p style="margin-bottom: 15px; color: var(--gray);">${request.reason || 'No reason provided.'}</p>
                            <div class="leave-actions">
                                <button class="leave-action-btn approve" data-leave-id="${request.id}">Approve</button>
                                <button class="leave-action-btn reject" data-leave-id="${request.id}">Reject</button>
                            </div>
                        `;
                        container.appendChild(card);
                    });

                    // Attach event listeners
                    attachLeaveActionListeners();
                })
                .catch(error => console.error('Error loading leave requests:', error));
        }

        function attachLeaveActionListeners() {
            document.querySelectorAll('.leave-action-btn.approve').forEach(btn => {
                btn.addEventListener('click', function() {
                    const leaveId = this.getAttribute('data-leave-id');
                    if (confirm('Approve this leave request?')) {
                        fetch('approve_leave.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ leave_id: leaveId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.closest('.leave-request-card').remove();
                                alert('Leave request approved.');
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    }
                });
            });

            document.querySelectorAll('.leave-action-btn.reject').forEach(btn => {
                btn.addEventListener('click', function() {
                    const leaveId = this.getAttribute('data-leave-id');
                    if (confirm('Reject this leave request?')) {
                        fetch('reject_leave.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ leave_id: leaveId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.closest('.leave-request-card').remove();
                                alert('Leave request rejected.');
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    }
                });
            });
        }

        function loadRecruitmentJobs() {
            fetch('get_jobs.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('recruitmentJobsContainer');
                    container.innerHTML = '';

                    data.forEach(job => {
                        const card = document.createElement('div');
                        card.className = 'job-card';
                        card.innerHTML = `
                            <div class="job-header">
                                <div>
                                    <div class="job-title">${job.title}</div>
                                    <div class="job-department">${ucfirst(job.department)} Department</div>
                                </div>
                                <div class="job-type">${ucfirst(job.employment_type)}</div>
                            </div>
                            <div class="job-details">
                                <div class="job-detail">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>${job.location || 'Zone'}</span>
                                </div>
                                <div class="job-detail">
                                    <i class="fas fa-clock"></i>
                                    <span>Posted: ${new Date(job.posted_at).toLocaleDateString()}</span>
                                </div>
                                <div class="job-detail">
                                    <i class="fas fa-users"></i>
                                    <span>${job.applications || 0} Applications</span>
                                </div>
                            </div>
                            <div class="job-actions">
                                <button class="job-action-btn">View Details</button>
                                <button class="job-action-btn">Edit</button>
                            </div>
                        `;
                        container.appendChild(card);
                    });
                })
                .catch(error => console.error('Error loading jobs:', error));
        }

        function loadTrainingCourses() {
            fetch('get_trainings.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('trainingCoursesContainer');
                    container.innerHTML = '';

                    data.forEach(training => {
                        const card = document.createElement('div');
                        card.className = 'training-card';
                        card.innerHTML = `
                            <div class="training-header">
                                <div>
                                    <div class="training-title">${training.title}</div>
                                    <div class="training-category">General</div>
                                </div>
                                <div class="training-status ${training.status}">${ucfirst(training.status)}</div>
                            </div>
                            <div class="training-details">
                                <div class="training-detail">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Date: ${new Date(training.session_date).toLocaleDateString()}</span>
                                </div>
                                <div class="training-detail">
                                    <i class="fas fa-clock"></i>
                                    <span>Time: ${training.start_time} - ${training.end_time}</span>
                                </div>
                                <div class="training-detail">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>Venue: ${training.venue || 'TBD'}</span>
                                </div>
                                <div class="training-detail">
                                    <i class="fas fa-user-tie"></i>
                                    <span>Trainer: ${training.trainer || 'TBD'}</span>
                                </div>
                            </div>
                        `;
                        container.appendChild(card);
                    });
                })
                .catch(error => console.error('Error loading trainings:', error));
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        function ucfirst(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function loadCharts() {
            // Department Chart
            fetch('get_department_stats.php')
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('departmentChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: data.map(item => ucfirst(item.department)),
                            datasets: [{
                                data: data.map(item => item.count),
                                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error loading department stats:', error));

            // Status Chart
            fetch('get_status_stats.php')
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('statusChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: data.map(item => ucfirst(item.status)),
                            datasets: [{
                                data: data.map(item => item.count),
                                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error loading status stats:', error));
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>