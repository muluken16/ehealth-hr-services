<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'wereda_hr') {
    header('Location: ../index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | HR Reports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        .report-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .report-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            border: 1px solid #f0f2f5;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        }

        .report-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 45px rgba(26, 74, 95, 0.1);
            border-color: var(--primary-light);
        }

        .report-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary);
            opacity: 0.1;
        }

        .report-card:hover::before { opacity: 1; }

        .report-icon {
            width: 70px;
            height: 70px;
            background: #f8fbff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .report-card:hover .report-icon {
            background: var(--primary);
            color: white;
            transform: scale(1.1);
        }

        .report-info h3 { font-size: 1.25rem; margin-bottom: 8px; color: var(--primary); }
        .report-info p { color: var(--gray); font-size: 0.9rem; margin-bottom: 20px; }

        .report-btn {
            width: 100%;
            padding: 12px;
            background: #f0f4f8;
            border: none;
            border-radius: 8px;
            color: var(--primary);
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }

        .report-card:hover .report-btn {
            background: var(--primary);
            color: white;
        }

        .analytics-preview {
            margin-top: 40px;
            background: white;
            border-radius: 16px;
            padding: 30px;
            border: 1px solid #f0f2f5;
        }
    </style>
</head>
<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>

        <?php
        $page_title = 'HR Reports';
        include 'navbar.php';
        ?>

        <main class="hr-main">
            <!-- Reports Content -->
            <div class="hr-dashboard">
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Generate HR Reports</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn" id="generateReportBtn">
                                <i class="fas fa-plus"></i> Generate Report
                            </button>
                            <button class="section-action-btn">
                                <i class="fas fa-history"></i> Report History
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="report-types">
                            <div class="report-card">
                                <div class="report-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="report-info">
                                    <h3>Employee Summary</h3>
                                    <p>Comprehensive overview of all employees</p>
                                </div>
                                <button class="report-btn" data-type="employee_summary">Generate</button>
                            </div>

                            <div class="report-card">
                                <div class="report-icon">
                                    <i class="fas fa-money-check-alt"></i>
                                </div>
                                <div class="report-info">
                                    <h3>Payroll Report</h3>
                                    <p>Salary and payroll information</p>
                                </div>
                                <button class="report-btn" data-type="payroll_report">Generate</button>
                            </div>

                            <div class="report-card">
                                <div class="report-icon">
                                    <i class="fas fa-umbrella-beach"></i>
                                </div>
                                <div class="report-info">
                                    <h3>Leave Report</h3>
                                    <p>Leave requests and approvals</p>
                                </div>
                                <button class="report-btn" data-type="leave_report">Generate</button>
                            </div>

                            <div class="report-card">
                                <div class="report-icon">
                                    <i class="fas fa-search-dollar"></i>
                                </div>
                                <div class="report-info">
                                    <h3>Recruitment Report</h3>
                                    <p>Hiring trends and open vacancies</p>
                                </div>
                                <button class="report-btn" data-type="recruitment_report">Generate</button>
                            </div>
                        </div>

                        <!-- analytics preview -->
                        <div class="analytics-preview">
                            <div class="hr-section-header" style="background: none; border: none; padding-left: 0;">
                                <h2 class="hr-section-title" style="font-size: 1.2rem;">Live Workforce Analytics Preview</h2>
                                <div class="hr-section-actions">
                                    <select class="filter-select" id="previewMetric">
                                        <option value="attendance">Attendance Trend</option>
                                        <option value="leaves">Leave Distribution</option>
                                        <option value="growth">Staff Growth</option>
                                    </select>
                                </div>
                            </div>
                            <div style="height: 300px; position: relative;">
                                <canvas id="reportPreviewChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Generate Report Modal -->
    <div class="modal" id="generateReportModal">
        <div class="modal-content large-modal">
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

                <div class="form-actions" style="margin-top: 20px;">
                    <button type="submit" class="submit-btn">Generate & Download</button>
                    <button type="button" class="cancel-btn" id="cancelReportBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="scripts.js"></script>
    <script>
        // Analytics Preview Chart
        let previewChart;
        function initPreviewChart() {
            const ctx = document.getElementById('reportPreviewChart').getContext('2d');
            previewChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Average Attendance %',
                        data: [92, 94, 93, 95, 96, 94],
                        borderColor: '#4cb5ae',
                        backgroundColor: 'rgba(76, 181, 174, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: false, min: 80, grid: { color: '#f0f2f5' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        document.getElementById('previewMetric').addEventListener('change', function() {
            const metric = this.value;
            if (metric === 'leaves') {
                previewChart.data.datasets[0].label = 'Pending Leaves';
                previewChart.data.datasets[0].data = [12, 19, 15, 8, 22, 14];
                previewChart.data.datasets[0].borderColor = '#ff7e5f';
                previewChart.config.type = 'bar';
            } else if (metric === 'growth') {
                previewChart.data.datasets[0].label = 'New Hires';
                previewChart.data.datasets[0].data = [2, 5, 3, 8, 12, 10];
                previewChart.data.datasets[0].borderColor = '#1a4a5f';
                previewChart.config.type = 'line';
            } else {
                previewChart.data.datasets[0].label = 'Average Attendance %';
                previewChart.data.datasets[0].data = [92, 94, 93, 95, 96, 94];
                previewChart.data.datasets[0].borderColor = '#4cb5ae';
                previewChart.config.type = 'line';
            }
            previewChart.update();
        });

        // Initialize chart on load
        document.addEventListener('DOMContentLoaded', initPreviewChart);
        // Generate Report Button
        document.getElementById('generateReportBtn').addEventListener('click', () => {
            document.getElementById('generateReportModal').style.display = 'block';
        });

        // Report type buttons
        document.querySelectorAll('.report-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const reportType = this.getAttribute('data-type');
                document.getElementById('reportType').value = reportType;
                document.getElementById('generateReportModal').style.display = 'block';
            });
        });

        // Close modal
        document.getElementById('closeReportModal').addEventListener('click', () => {
            document.getElementById('generateReportModal').style.display = 'none';
        });

        document.getElementById('cancelReportBtn').addEventListener('click', () => {
            document.getElementById('generateReportModal').style.display = 'none';
        });

        // Form submission
        document.getElementById('reportForm').addEventListener('submit', function(e) {
            e.preventDefault();
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
                    document.getElementById('generateReportModal').style.display = 'none';
                } else {
                    alert('Error generating report: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    </script>
</body>
</html>