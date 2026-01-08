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
    <title>HealthFirst | Zone HR Payroll</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>
        <?php
        $page_title = 'Payroll Management';
        include 'navbar.php';
        ?>
            <div class="hr-dashboard">
                <div class="filters-section">
                    <div class="search-box"><input type="text" placeholder="Search employees..." id="payrollSearch"><i class="fas fa-search"></i></div>
                    <select class="filter-select" id="monthFilter">
                        <option value="">Select Month</option>
                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                    <select class="filter-select" id="yearFilter">
                        <option value="">Select Year</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                    </select>
                    <button class="add-btn" onclick="processPayroll()"><i class="fas fa-calculator"></i> Process Payroll</button>
                </div>
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Payroll Records</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn" onclick="exportPayroll()">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <button class="section-action-btn" onclick="refreshPayroll()">
                                <i class="fas fa-sync-alt"></i> Refresh
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
                                        <th>Basic Salary</th>
                                        <th>Allowances</th>
                                        <th>Deductions</th>
                                        <th>Net Salary</th>
                                        <th>Period</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="payrollTableBody">
                                    <!-- Payroll records will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="scripts.js"></script>
    <script>
        let allPayrollRecords = [];
        const payrollSearch = document.getElementById('payrollSearch');
        const monthFilter = document.getElementById('monthFilter');
        const yearFilter = document.getElementById('yearFilter');

        // Load payroll records on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadPayrollRecords();
        });

        // Load payroll records from database
        function loadPayrollRecords() {
            // For now, show empty table with message
            displayPayrollRecords([]);
        }

        // Display payroll records in table
        function displayPayrollRecords(records) {
            const tbody = document.getElementById('payrollTableBody');
            tbody.innerHTML = '';

            if (records.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" style="text-align: center; padding: 40px; color: var(--gray);">No payroll records found. Select a month and year to view records.</td></tr>';
                return;
            }

            records.forEach(record => {
                const fullName = `${record.first_name} ${record.middle_name ? record.middle_name + ' ' : ''}${record.last_name}`;
                const initials = fullName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);

                const period = `${record.month}/${record.year}`;

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <div class="employee-info">
                            <div class="employee-avatar">${initials}</div>
                            <div>
                                <div class="employee-name">${fullName}</div>
                                <div class="employee-id">${record.employee_id}</div>
                            </div>
                        </div>
                    </td>
                    <td>${record.employee_id}</td>
                    <td><span class="department-badge ${record.department}">${record.department}</span></td>
                    <td>${record.basic_salary ? record.basic_salary + ' ETB' : 'N/A'}</td>
                    <td>${record.allowances ? record.allowances + ' ETB' : '0 ETB'}</td>
                    <td>${record.deductions ? record.deductions + ' ETB' : '0 ETB'}</td>
                    <td><strong>${record.net_salary ? record.net_salary + ' ETB' : 'N/A'}</strong></td>
                    <td>${period}</td>
                    <td><span class="status-badge ${record.status}">${record.status}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view" onclick="viewPayrollRecord(${record.id})"><i class="fas fa-eye"></i></button>
                            <button class="action-btn edit" onclick="editPayrollRecord(${record.id})"><i class="fas fa-edit"></i></button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Filter functionality
        function filterPayrollRecords() {
            const searchTerm = payrollSearch.value.toLowerCase();
            const monthValue = monthFilter.value;
            const yearValue = yearFilter.value;

            const filtered = allPayrollRecords.filter(record => {
                const fullName = `${record.first_name} ${record.middle_name ? record.middle_name + ' ' : ''}${record.last_name}`.toLowerCase();
                const employeeId = record.employee_id.toLowerCase();

                const matchesSearch = fullName.includes(searchTerm) || employeeId.includes(searchTerm);
                const matchesMonth = monthValue === '' || record.month === monthValue;
                const matchesYear = yearValue === '' || record.year === yearValue;

                return matchesSearch && matchesMonth && matchesYear;
            });

            displayPayrollRecords(filtered);
        }

        payrollSearch.addEventListener('input', filterPayrollRecords);
        monthFilter.addEventListener('change', filterPayrollRecords);
        yearFilter.addEventListener('change', filterPayrollRecords);

        // Process payroll
        function processPayroll() {
            const month = monthFilter.value;
            const year = yearFilter.value;

            if (!month || !year) {
                alert('Please select both month and year to process payroll.');
                return;
            }

            if (confirm(`Process payroll for ${month}/${year}? This will calculate salaries for all active employees.`)) {
                fetch('process_payroll.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `month=${month}&year=${year}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Payroll processed successfully!');
                        loadPayrollRecords();
                    } else {
                        alert('Failed to process payroll: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing payroll.');
                });
            }
        }

        // View payroll record details
        function viewPayrollRecord(id) {
            // Find record data
            const record = allPayrollRecords.find(r => r.id == id);
            if (record) {
                const fullName = `${record.first_name} ${record.middle_name ? record.middle_name + ' ' : ''}${record.last_name}`;
                let details = `Payroll Details:\n\n`;
                details += `Employee: ${fullName}\n`;
                details += `Employee ID: ${record.employee_id}\n`;
                details += `Period: ${record.month}/${record.year}\n`;
                details += `Basic Salary: ${record.basic_salary || 0} ETB\n`;
                details += `Allowances: ${record.allowances || 0} ETB\n`;
                details += `Deductions: ${record.deductions || 0} ETB\n`;
                details += `Net Salary: ${record.net_salary || 0} ETB\n`;
                details += `Status: ${record.status}\n`;
                alert(details);
            } else {
                alert('Payroll record not found.');
            }
        }

        // Edit payroll record
        function editPayrollRecord(id) {
            alert('Edit functionality would be implemented here.');
        }

        // Export function
        function exportPayroll() {
            alert('Export functionality would generate and download payroll data here.');
        }

        // Refresh function
        function refreshPayroll() {
            loadPayrollRecords();
        }
    </script>
</body>
</html>