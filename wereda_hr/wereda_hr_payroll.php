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
    <title>HealthFirst | Payroll Management</title>
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

        <main class="hr-main">
            <!-- Payroll Content -->
            <div class="hr-dashboard">
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Payroll Processing</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn" id="processPayrollBtn">
                                <i class="fas fa-money-check-alt"></i> Process Payroll
                            </button>
                            <button class="section-action-btn">
                                <i class="fas fa-history"></i> Payroll History
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <p>Payroll management system for Ethiopian healthcare workers.</p>
                        <div id="payrollContent">
                            <!-- Payroll content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
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
                    <button type="button" class="submit-btn" id="processPayrollBtnFinal">Process Payroll</button>
                    <button type="button" class="cancel-btn" id="cancelPayrollBtn">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script src="scripts.js"></script>
    <script>
        // Process Payroll Button
        document.getElementById('processPayrollBtn').addEventListener('click', () => {
            document.getElementById('processPayrollModal').style.display = 'block';
            loadEmployeesForPayroll();
        });

        // Close modal
        document.getElementById('closePayrollModal').addEventListener('click', () => {
            document.getElementById('processPayrollModal').style.display = 'none';
        });

        document.getElementById('cancelPayrollBtn').addEventListener('click', () => {
            document.getElementById('processPayrollModal').style.display = 'none';
        });

        // Load employees for payroll
        function loadEmployeesForPayroll() {
            fetch('get_employees.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('employeePayrollList');
                    container.innerHTML = '';

                    data.employees.forEach(employee => {
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

        // Calculate payroll
        document.getElementById('calculatePayrollBtn').addEventListener('click', function() {
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
        });

        // Process payroll
        document.getElementById('processPayrollBtnFinal').addEventListener('click', function() {
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
                    document.getElementById('processPayrollModal').style.display = 'none';
                } else {
                    alert('Error processing payroll: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });

        // Load payroll content on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('payrollContent').innerHTML = '<p>Payroll management system ready. Click "Process Payroll" to begin.</p>';
        });
    </script>
</body>
</html>