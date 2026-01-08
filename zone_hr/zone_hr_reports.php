<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'zone_hr') {
    header('Location: ../index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Zone HR Reports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>
        <?php
        $page_title = 'HR Reports';
        include 'navbar.php';
        ?>
            <div class="hr-dashboard">
                <div class="report-cards">
                    <div class="report-card" onclick="generateReport('employee_summary')">
                        <div class="report-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="report-info">
                            <h3>Employee Summary</h3>
                            <p>Overview of all employees</p>
                        </div>
                    </div>

                    <div class="report-card" onclick="generateReport('department_stats')">
                        <div class="report-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="report-info">
                            <h3>Department Statistics</h3>
                            <p>Employee distribution by department</p>
                        </div>
                    </div>

                    <div class="report-card" onclick="generateReport('leave_report')">
                        <div class="report-icon">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                        <div class="report-info">
                            <h3>Leave Report</h3>
                            <p>Leave requests and approvals</p>
                        </div>
                    </div>

                    <div class="report-card" onclick="generateReport('recruitment_report')">
                        <div class="report-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="report-info">
                            <h3>Recruitment Report</h3>
                            <p>Job postings and applications</p>
                        </div>
                    </div>

                    <div class="report-card" onclick="generateReport('training_report')">
                        <div class="report-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="report-info">
                            <h3>Training Report</h3>
                            <p>Training sessions and attendance</p>
                        </div>
                    </div>

                    <div class="report-card" onclick="generateReport('payroll_report')">
                        <div class="report-icon">
                            <i class="fas fa-money-check-alt"></i>
                        </div>
                        <div class="report-info">
                            <h3>Payroll Report</h3>
                            <p>Salary and payroll summaries</p>
                        </div>
                    </div>

                    <div class="report-card" onclick="generateReport('performance_report')">
                        <div class="report-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="report-info">
                            <h3>Performance Report</h3>
                            <p>Employee performance metrics</p>
                        </div>
                    </div>

                    <div class="report-card" onclick="generateReport('turnover_report')">
                        <div class="report-icon">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div class="report-info">
                            <h3>Turnover Report</h3>
                            <p>Employee turnover analysis</p>
                        </div>
                    </div>
                </div>

                <div class="hr-section" id="reportSection" style="display: none;">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title" id="reportTitle">Report Results</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn" onclick="exportReport()">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <button class="section-action-btn" onclick="printReport()">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div id="reportContent">
                            <!-- Report content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        .report-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .report-card {
            background: var(--white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid var(--light-gray);
        }

        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .report-icon {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .report-info h3 {
            margin: 0 0 8px 0;
            color: var(--dark);
            font-size: 1.1rem;
        }

        .report-info p {
            margin: 0;
            color: var(--gray);
            font-size: 0.9rem;
        }
    </style>

    <script src="scripts.js"></script>
    <script>
        let currentReportType = '';

        // Generate report
        function generateReport(reportType) {
            currentReportType = reportType;

            // Show loading
            document.getElementById('reportSection').style.display = 'block';
            document.getElementById('reportTitle').textContent = getReportTitle(reportType);
            document.getElementById('reportContent').innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> Generating report...</div>';

            // Fetch report data
            fetch('generate_report.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `report_type=${reportType}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayReport(data, reportType);
                } else {
                    document.getElementById('reportContent').innerHTML = `<div style="text-align: center; padding: 40px; color: var(--danger);"><i class="fas fa-exclamation-triangle"></i> Failed to generate report: ${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('reportContent').innerHTML = '<div style="text-align: center; padding: 40px; color: var(--danger);"><i class="fas fa-exclamation-triangle"></i> An error occurred while generating the report.</div>';
            });
        }

        // Get report title
        function getReportTitle(reportType) {
            const titles = {
                'employee_summary': 'Employee Summary Report',
                'department_stats': 'Department Statistics Report',
                'leave_report': 'Leave Report',
                'recruitment_report': 'Recruitment Report',
                'training_report': 'Training Report',
                'payroll_report': 'Payroll Report',
                'performance_report': 'Performance Report',
                'turnover_report': 'Turnover Report'
            };
            return titles[reportType] || 'Report';
        }

        // Display report content
        function displayReport(data, reportType) {
            let content = '';

            switch(reportType) {
                case 'employee_summary':
                    content = generateEmployeeSummaryReport(data);
                    break;
                case 'department_stats':
                    content = generateDepartmentStatsReport(data);
                    break;
                case 'leave_report':
                    content = generateLeaveReport(data);
                    break;
                case 'recruitment_report':
                    content = generateRecruitmentReport(data);
                    break;
                case 'training_report':
                    content = generateTrainingReport(data);
                    break;
                case 'payroll_report':
                    content = generatePayrollReport(data);
                    break;
                case 'performance_report':
                    content = generatePerformanceReport(data);
                    break;
                case 'turnover_report':
                    content = generateTurnoverReport(data);
                    break;
                default:
                    content = '<div style="text-align: center; padding: 40px;">Report type not supported yet.</div>';
            }

            document.getElementById('reportContent').innerHTML = content;
        }

        // Generate employee summary report
        function generateEmployeeSummaryReport(data) {
            return `
                <div class="report-summary">
                    <div class="summary-stats">
                        <div class="stat-card">
                            <h4>Total Employees</h4>
                            <span class="stat-number">${data.total_employees || 0}</span>
                        </div>
                        <div class="stat-card">
                            <h4>Active Employees</h4>
                            <span class="stat-number">${data.active_employees || 0}</span>
                        </div>
                        <div class="stat-card">
                            <h4>On Leave</h4>
                            <span class="stat-number">${data.on_leave || 0}</span>
                        </div>
                        <div class="stat-card">
                            <h4>New Hires (This Month)</h4>
                            <span class="stat-number">${data.new_hires || 0}</span>
                        </div>
                    </div>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Join Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.employees ? data.employees.map(emp => `
                                <tr>
                                    <td>${emp.employee_id}</td>
                                    <td>${emp.first_name} ${emp.last_name}</td>
                                    <td>${emp.department_assigned}</td>
                                    <td>${emp.position}</td>
                                    <td>${new Date(emp.join_date).toLocaleDateString()}</td>
                                    <td><span class="status-badge ${emp.status}">${emp.status}</span></td>
                                </tr>
                            `).join('') : '<tr><td colspan="6">No employee data available</td></tr>'}
                        </tbody>
                    </table>
                </div>
            `;
        }

        // Generate department stats report
        function generateDepartmentStatsReport(data) {
            return `
                <div class="report-summary">
                    <div class="summary-stats">
                        <div class="stat-card">
                            <h4>Total Departments</h4>
                            <span class="stat-number">${data.total_departments || 0}</span>
                        </div>
                        <div class="stat-card">
                            <h4>Largest Department</h4>
                            <span class="stat-number">${data.largest_department || 'N/A'}</span>
                        </div>
                    </div>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Total Employees</th>
                                <th>Active</th>
                                <th>On Leave</th>
                                <th>Inactive</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.departments ? data.departments.map(dept => `
                                <tr>
                                    <td>${dept.name}</td>
                                    <td>${dept.total}</td>
                                    <td>${dept.active}</td>
                                    <td>${dept.on_leave}</td>
                                    <td>${dept.inactive}</td>
                                </tr>
                            `).join('') : '<tr><td colspan="5">No department data available</td></tr>'}
                        </tbody>
                    </table>
                </div>
            `;
        }

        // Generate leave report
        function generateLeaveReport(data) {
            return `
                <div class="report-summary">
                    <div class="summary-stats">
                        <div class="stat-card">
                            <h4>Total Requests</h4>
                            <span class="stat-number">${data.total_requests || 0}</span>
                        </div>
                        <div class="stat-card">
                            <h4>Pending</h4>
                            <span class="stat-number">${data.pending_requests || 0}</span>
                        </div>
                        <div class="stat-card">
                            <h4>Approved</h4>
                            <span class="stat-number">${data.approved_requests || 0}</span>
                        </div>
                        <div class="stat-card">
                            <h4>Rejected</h4>
                            <span class="stat-number">${data.rejected_requests || 0}</span>
                        </div>
                    </div>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Days</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.requests ? data.requests.map(req => `
                                <tr>
                                    <td>${req.employee_name}</td>
                                    <td>${req.leave_type}</td>
                                    <td>${new Date(req.start_date).toLocaleDateString()}</td>
                                    <td>${new Date(req.end_date).toLocaleDateString()}</td>
                                    <td>${req.days}</td>
                                    <td><span class="status-badge ${req.status}">${req.status}</span></td>
                                </tr>
                            `).join('') : '<tr><td colspan="6">No leave data available</td></tr>'}
                        </tbody>
                    </table>
                </div>
            `;
        }

        // Placeholder functions for other reports
        function generateRecruitmentReport(data) {
            return '<div style="text-align: center; padding: 40px;">Recruitment report functionality coming soon.</div>';
        }

        function generateTrainingReport(data) {
            return '<div style="text-align: center; padding: 40px;">Training report functionality coming soon.</div>';
        }

        function generatePayrollReport(data) {
            return '<div style="text-align: center; padding: 40px;">Payroll report functionality coming soon.</div>';
        }

        function generatePerformanceReport(data) {
            return '<div style="text-align: center; padding: 40px;">Performance report functionality coming soon.</div>';
        }

        function generateTurnoverReport(data) {
            return '<div style="text-align: center; padding: 40px;">Turnover report functionality coming soon.</div>';
        }

        // Export report
        function exportReport() {
            alert('Export functionality would generate and download the report here.');
        }

        // Print report
        function printReport() {
            window.print();
        }
    </script>

    <style>
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            border: 1px solid var(--light-gray);
        }

        .stat-card h4 {
            margin: 0 0 10px 0;
            color: var(--gray);
            font-size: 0.9rem;
            text-transform: uppercase;
            font-weight: 600;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
        }
    </style>
</body>
</html>