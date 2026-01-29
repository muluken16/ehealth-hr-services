<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Payroll</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <div class="hr-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <?php 
                $page_title = "Payroll Management";
                include 'navbar.php'; 
            ?>

            <!-- Content -->
            <div class="content">
                <!-- Payroll Overview Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Payroll Overview</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn">
                                <i class="fas fa-calendar-alt"></i> Select Period
                            </button>
                            <button class="section-action-btn">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="payroll-stats" id="payrollStatsContainer">
                            <!-- Payroll stats will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Employee Payroll Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Employee Payroll</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn">
                                <i class="fas fa-plus"></i> Add Bonus
                            </button>
                            <button class="section-action-btn">
                                <i class="fas fa-edit"></i> Adjust Salary
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Basic Salary</th>
                                        <th>Allowances</th>
                                        <th>Deductions</th>
                                        <th>Net Salary</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="payrollTableBody">
                                    <!-- Payroll data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Load payroll data on page load
        window.addEventListener('load', function() {
            // Load payroll stats
            fetch('get_kebele_hr_payroll_stats.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('payrollStatsContainer');
                    container.innerHTML = `
                        <div class="payroll-stat-cards">
                            <div class="payroll-stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-info">
                                    <h3>${data.total_employees}</h3>
                                    <p>Total Employees</p>
                                </div>
                            </div>
                            <div class="payroll-stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="stat-info">
                                    <h3>ETB ${data.total_payroll.toLocaleString()}</h3>
                                    <p>Total Payroll</p>
                                </div>
                            </div>
                            <div class="payroll-stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <div class="stat-info">
                                    <h3>ETB ${data.total_allowances.toLocaleString()}</h3>
                                    <p>Total Allowances</p>
                                </div>
                            </div>
                            <div class="payroll-stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-minus-circle"></i>
                                </div>
                                <div class="stat-info">
                                    <h3>ETB ${data.total_deductions.toLocaleString()}</h3>
                                    <p>Total Deductions</p>
                                </div>
                            </div>
                        </div>
                    `;
                })
                .catch(error => console.error('Error loading payroll stats:', error));

            // Load employee payroll data
            fetch('get_kebele_hr_payroll.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('payrollTableBody');
                    tbody.innerHTML = '';

                    if (data.length > 0) {
                        data.forEach(employee => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>
                                    <div class="employee-info">
                                        <div class="employee-avatar">${employee.first_name.charAt(0)}${employee.last_name.charAt(0)}</div>
                                        <div>
                                            <div class="employee-name">${employee.first_name} ${employee.last_name}</div>
                                            <div class="employee-id">${employee.employee_id}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>ETB ${employee.basic_salary ? employee.basic_salary.toLocaleString() : '0'}</td>
                                <td>ETB ${employee.allowances ? employee.allowances.toLocaleString() : '0'}</td>
                                <td>ETB ${employee.deductions ? employee.deductions.toLocaleString() : '0'}</td>
                                <td><strong>ETB ${employee.net_salary ? employee.net_salary.toLocaleString() : '0'}</strong></td>
                                <td><span class="status-badge ${employee.payroll_status === 'processed' ? 'active' : 'inactive'}">${employee.payroll_status || 'Pending'}</span></td>
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
                        tbody.innerHTML = '<tr><td colspan="7">No payroll data available.</td></tr>';
                    }
                })
                .catch(error => console.error('Error loading payroll data:', error));
        });

    </script>
    <script src="scripts.js"></script>
</body>
</html>