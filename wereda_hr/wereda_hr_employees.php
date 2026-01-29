<?php
session_start();
$user = $_SESSION['user_name'] ?? 'Demo User';
$role = $_SESSION['role'] ?? 'wereda_hr';
$woreda = $_SESSION['woreda'] ?? 'Woreda 1';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wereda HR | Employees</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        :root {
            --primary: #1a4a5f;
            --secondary: #2c7da0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray: #64748b;
            --light: #f8fafc;
        }

        body {
            background: #f1f5f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            background: #f1f5f9;
            min-height: 100vh;
        }

        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 74, 95, 0.3);
        }

        .btn-outline {
            background: white;
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--light);
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .search-filters {
            display: grid;
            grid-template-columns: 200px 1fr 150px 150px 100px;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #f1f5f9;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background: #f8fafc;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(26, 74, 95, 0.05);
        }

        .table-container {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            text-align: left;
            padding: 16px 20px;
            background: #f8fafc;
            color: var(--gray);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table td {
            padding: 18px 20px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .data-table tr:hover {
            background: #f8fafc;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .employee-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
        }

        .employee-name {
            font-weight: 600;
            color: #1e293b;
        }

        .employee-id {
            font-size: 0.8rem;
            color: var(--gray);
        }

        .status-pill {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-on-leave {
            background: #fef3c7;
            color: #92400e;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .action-btn-edit {
            background: #eff6ff;
            color: var(--secondary);
        }

        .action-btn-edit:hover {
            background: var(--secondary);
            color: white;
        }

        .action-btn-delete {
            background: #fef2f2;
            color: var(--danger);
        }

        .action-btn-delete:hover {
            background: var(--danger);
            color: white;
        }

        .action-btn-view {
            background: #f0fdf4;
            color: var(--success);
        }

        .action-btn-view:hover {
            background: var(--success);
            color: white;
        }

        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }

        .pagination-info {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .pagination-buttons {
            display: flex;
            gap: 8px;
        }

        .pagination-btn {
            padding: 8px 14px;
            border: 1px solid #e2e8f0;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .pagination-btn:hover,
        .pagination-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 15px;
        }

        .stat-icon.blue {
            background: #eff6ff;
            color: var(--secondary);
        }

        .stat-icon.green {
            background: #f0fdf4;
            color: var(--success);
        }

        .stat-icon.yellow {
            background: #fefce8;
            color: var(--warning);
        }

        .stat-icon.red {
            background: #fef2f2;
            color: var(--danger);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 4rem;
            color: #e2e8f0;
            margin-bottom: 20px;
        }

        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .search-filters {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .header-actions {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <?php include 'navbar.php'; ?>

            <div class="content">
                <div class="page-header">
                    <div>
                        <h1 class="page-title"><i class="fas fa-users"></i> Employee Management</h1>
                        <p style="margin: 5px 0 0; color: var(--gray);">Manage all employees under your woreda</p>
                    </div>
                    <div class="header-actions">
                        <button onclick="exportToCSV()" class="btn btn-outline">
                            <i class="fas fa-file-export"></i> Export
                        </button>
                        <a href="add_employee.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Add New Employee
                        </a>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                        <div class="stat-value" id="totalEmployees">0</div>
                        <div class="stat-label">Total Employees</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
                        <div class="stat-value" id="activeEmployees">0</div>
                        <div class="stat-label">Active</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon yellow"><i class="fas fa-user-clock"></i></div>
                        <div class="stat-value" id="onLeaveEmployees">0</div>
                        <div class="stat-label">On Leave</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon red"><i class="fas fa-user-times"></i></div>
                        <div class="stat-value" id="inactiveEmployees">0</div>
                        <div class="stat-label">Inactive</div>
                    </div>
                </div>

                <div class="card">
                    <div class="search-filters">
                        <select class="form-control" id="statusFilter" onchange="loadEmployees(1)">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="on-leave">On Leave</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search employees..."
                            onkeyup="searchEmployees()">
                        <select class="form-control" id="departmentFilter" onchange="loadEmployees(1)">
                            <option value="">All Departments</option>
                            <option value="Outpatient Department (OPD)">Outpatient Department (OPD)</option>
                            <option value="Emergency / Casualty">Emergency / Casualty</option>
                            <option value="Maternal and Child Health (MCH)">Maternal and Child Health (MCH)</option>
                            <option value="Pharmacy">Pharmacy</option>
                            <option value="Laboratory">Laboratory</option>
                            <option value="Administration and Finance">Administration and Finance</option>
                        </select>
                        <select class="form-control" id="genderFilter" onchange="loadEmployees(1)">
                            <option value="">All Genders</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                        <button class="btn btn-primary" onclick="loadEmployees(1)">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Position</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Join Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="employeeTableBody">
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        <i class="fas fa-users"></i>
                                        <p>Loading employees...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination">
                        <div class="pagination-info" id="paginationInfo">Showing 0 of 0 employees</div>
                        <div class="pagination-buttons" id="paginationButtons"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let currentPage = 1;
        const itemsPerPage = 10;
        let allEmployees = [];

        function loadEmployees(page = 1) {
            currentPage = page;
            const search = document.getElementById('searchInput').value;
            const status = document.getElementById('statusFilter').value;
            const department = document.getElementById('departmentFilter').value;
            const gender = document.getElementById('genderFilter').value;

            const params = new URLSearchParams({
                page: page,
                limit: itemsPerPage,
                search: search,
                status: status,
                department: department,
                gender: gender
            });

            fetch(`get_wereda_hr_employees.php?${params}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        allEmployees = data.employees;
                        renderEmployees(data.employees);
                        renderPagination(data.total, data.page, data.limit);
                        updateStats(data.stats);
                        document.getElementById('paginationInfo').textContent = `Showing ${data.employees.length} of ${data.total} employees`;
                    } else {
                        document.getElementById('employeeTableBody').innerHTML = `
                            <tr><td colspan="6" class="empty-state">
                                <i class="fas fa-exclamation-circle"></i>
                                <p>Error loading employees: ${data.message}</p>
                            </td></tr>`;
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    document.getElementById('employeeTableBody').innerHTML = `
                        <tr><td colspan="6" class="empty-state">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Network error. Please try again.</p>
                        </td></tr>`;
                });
        }

        function renderEmployees(employees) {
            const tbody = document.getElementById('employeeTableBody');
            if (employees.length === 0) {
                tbody.innerHTML = `
                    <tr><td colspan="6" class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <p>No employees found matching your criteria.</p>
                    </td></tr>`;
                return;
            }

            tbody.innerHTML = employees.map(emp => {
                const initials = (emp.first_name?.charAt(0) || '') + (emp.last_name?.charAt(0) || '');
                const avatarHtml = emp.photo
                    ? `<img src="../uploads/employees/${emp.photo}" alt="Photo" class="avatar">`
                    : `<div class="avatar">${initials.toUpperCase()}</div>`;

                return `
                    <tr>
                        <td>
                            <div class="employee-info">
                                ${avatarHtml}
                                <div>
                                    <div class="employee-name">${emp.first_name || ''} ${emp.last_name || ''}</div>
                                    <div class="employee-id">${emp.employee_id || emp.id || ''}</div>
                                </div>
                            </div>
                        </td>
                        <td>${emp.position || '-'}</td>
                        <td>${emp.department_assigned || '-'}</td>
                        <td>
                            <span class="status-pill status-${emp.status?.replace('-', '-') || 'active'}">
                                ${(emp.status || 'active').replace('-', ' ')}
                            </span>
                        </td>
                        <td>${emp.join_date || '-'}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn action-btn-view" title="View" onclick="viewEmployee('${emp.employee_id || emp.id}')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn action-btn-edit" title="Edit" onclick="editEmployee('${emp.employee_id || emp.id}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn action-btn-delete" title="Delete" onclick="deleteEmployee('${emp.employee_id || emp.id}')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;
            }).join('');
        }

        function renderPagination(total, page, limit) {
            const totalPages = Math.ceil(total / limit);
            const buttons = document.getElementById('paginationButtons');

            if (totalPages <= 1) {
                buttons.innerHTML = '';
                return;
            }

            let html = '';
            html += `<button class="pagination-btn" onclick="loadEmployees(${page - 1})" ${page === 1 ? 'disabled' : ''}>Prev</button>`;

            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= page - 2 && i <= page + 2)) {
                    html += `<button class="pagination-btn ${i === page ? 'active' : ''}" onclick="loadEmployees(${i})">${i}</button>`;
                } else if (i === page - 3 || i === page + 3) {
                    html += `<button class="pagination-btn" disabled>...</button>`;
                }
            }

            html += `<button class="pagination-btn" onclick="loadEmployees(${page + 1})" ${page === totalPages ? 'disabled' : ''}>Next</button>`;
            buttons.innerHTML = html;
        }

        function updateStats(stats) {
            document.getElementById('totalEmployees').textContent = stats.total || 0;
            document.getElementById('activeEmployees').textContent = stats.active || 0;
            document.getElementById('onLeaveEmployees').textContent = stats.on_leave || 0;
            document.getElementById('inactiveEmployees').textContent = stats.inactive || 0;
        }

        function searchEmployees() {
            loadEmployees(1);
        }

        function editEmployee(id) {
            window.location.href = `edit_employee.php?id=${id}`;
        }

        function viewEmployee(id) {
            alert('View employee: ' + id);
        }

        function deleteEmployee(id) {
            if (confirm('Are you sure you want to delete this employee? This action cannot be undone.')) {
                fetch(`employee_actions.php?action=delete&id=${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert('Employee deleted successfully!');
                            loadEmployees(currentPage);
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(err => {
                        alert('Network error. Please try again.');
                    });
            }
        }

        function exportToCSV() {
            const search = document.getElementById('searchInput').value;
            const status = document.getElementById('statusFilter').value;
            const department = document.getElementById('departmentFilter').value;
            const gender = document.getElementById('genderFilter').value;

            const params = new URLSearchParams({
                export: 'csv',
                search: search,
                status: status,
                department: department,
                gender: gender
            });

            window.open(`get_wereda_hr_employees.php?${params}`, '_blank');
        }

        document.addEventListener('DOMContentLoaded', function () {
            loadEmployees(1);
        });
    </script>
</body>

</html>