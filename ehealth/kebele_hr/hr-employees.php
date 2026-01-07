<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Employees Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        /* Woreda Style Imports */
        .modal-content.large-modal {
            max-width: 1000px;
            width: 95%;
            border-radius: 20px;
            overflow: hidden;
            padding: 0;
        }

        .modal-header-banner {
            background: var(--primary);
            color: white;
            padding: 30px;
            position: relative;
        }

        .modal-header-banner h2 {
            margin: 0;
            font-size: 1.8rem;
        }

        .modal-header-banner p {
            margin: 5px 0 0 0;
            opacity: 0.8;
            font-size: 0.9rem;
        }

        .modal-body {
            padding: 40px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding: 15px 0;
            border-top: 1px solid #eee;
        }

        .pagination-info {
            font-size: 0.9rem;
            color: var(--gray);
        }

        .pagination-btns {
            display: flex;
            gap: 5px;
        }

        .page-link {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            color: var(--primary);
            text-decoration: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .page-link:hover {
            background: #f0f4f8;
        }

        .page-link.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .page-link.disabled {
            color: #ccc;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Side Panel Styles */
        .side-panel {
            position: fixed;
            top: 0;
            right: -550px;
            width: 550px;
            height: 100vh;
            background: white;
            box-shadow: -5px 0 25px rgba(0, 0, 0, 0.1);
            z-index: 1100;
            transition: right 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            overflow-y: auto;
            padding: 0;
        }

        .side-panel.open {
            right: 0;
            box-shadow: -10px 0 50px rgba(15, 23, 42, 0.25);
        }

        .side-panel-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1050;
            display: none;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .side-panel-overlay.active {
            display: block;
            opacity: 1;
        }

        .side-panel-content {
            padding: 25px 35px;
            scroll-behavior: smooth;
        }

        .side-panel-header {
            position: sticky;
            top: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0;
            border-bottom: 1px solid #f1f5f9;
            padding: 25px 35px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            z-index: 10;
        }

        .side-panel-close {
            cursor: pointer;
            font-size: 1.5rem;
            color: var(--gray);
            transition: color 0.2s;
        }

        .side-panel-close:hover {
            color: var(--danger);
        }

        .info-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #718096;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            font-weight: 700;
        }

        .info-value {
            font-size: 0.95rem;
            color: #2d3748;
            font-weight: 500;
        }

        .side-panel-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1099;
            display: none;
            backdrop-filter: blur(2px);
        }

        .side-panel-overlay.active {
            display: block;
        }

        tr.clickable-row {
            cursor: pointer;
            transition: background 0.2s;
        }

        tr.clickable-row:hover {
            background: #f0f7ff !important;
        }

        .employee-avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .scan-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: var(--primary);
            color: white;
            border-radius: 10px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .scan-btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }
        /* Premium Form Styling */
        .form-group label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }

        .form-control-premium {
            width: 100%;
            padding: 12px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            color: #1e293b;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-control-premium:focus {
            background: #fff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
            transform: translateY(-1px);
        }

        .form-step-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            border: 1px solid #f1f5f9;
        }

        .step-title-premium {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .step-title-premium i {
            width: 36px;
            height: 36px;
            background: #eff6ff;
            color: #3b82f6;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        /* Action Buttons Styling */
        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .action-btn {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.9rem;
        }

        .action-btn.view { background: #f0f9ff; color: #0369a1; }
        .action-btn.view:hover { background: #0369a1; color: white; transform: translateY(-2px); }

        .action-btn.edit { background: #f0fdf4; color: #15803d; }
        .action-btn.edit:hover { background: #15803d; color: white; transform: translateY(-2px); }

        .action-btn.delete { background: #fef2f2; color: #991b1b; }
        .action-btn.delete:hover { background: #991b1b; color: white; transform: translateY(-2px); }

        .action-btn:active { transform: scale(0.95); }

        /* New Premium Wizard Steps */
        .step-indicator {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }
        .step-indicator.active {
            background: #3b82f6;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.4);
            transform: scale(1.1);
        }
        .step-indicator.completed {
            background: #10b981;
            color: white;
        }
        .step-line {
            width: 20px;
            height: 2px;
            background: #e2e8f0;
            margin: 0 4px;
        }
    </style>
</head>

<body>
    <div class="hr-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <?php 
                $page_title = "Employee Directory";
                include 'navbar.php'; 
            ?>

            <div class="hr-dashboard">
                <!-- Filters Section (Woreda Dashboard Style) -->
                <div class="filters-section">
                    <div class="search-box">
                        <input type="text" placeholder="Search by name, ID or email..." id="employeeSearch">
                        <i class="fas fa-search"></i>
                    </div>
                    <select class="filter-select" id="departmentFilter">
                        <option value="">All Departments</option>
                        <option value="medical">Medical</option>
                        <option value="administration">Administration</option>
                        <option value="technical">Technical</option>
                        <option value="support">Support</option>
                    </select>
                    <select class="filter-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="on-leave">On Leave</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <button class="add-btn" onclick="window.location.href='add_employee.php'"
                        style="border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="fas fa-plus"></i> Add Employee
                    </button>
                </div>

                <!-- Table Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Employee Records</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn" onclick="exportEmployees()">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <button class="section-action-btn" onclick="loadEmployees(1)">
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
                                        <th>ID</th>
                                        <th>Department</th>
                                        <th>Position</th>
                                        <th>Join Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="employeeTableBody">
                                    <tr>
                                        <td colspan="7" style="text-align:center; padding: 50px;">
                                            <i class="fas fa-circle-notch fa-spin fa-2x"
                                                style="color: var(--primary); margin-bottom: 10px;"></i>
                                            <p>Loading employees...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Container -->
                        <div class="pagination-container" id="paginationContainer">
                            <!-- JS will inject pagination here -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>



    <!-- Side Panel for Details -->
    <div class="side-panel-overlay" id="sidePanelOverlay" onclick="window.closeSidePanel()"></div>
    <div class="side-panel" id="employeeSidePanel">
        <div class="side-panel-header">
            <h2 style="margin:0; font-size:1.4rem; color:var(--primary);"><i class="fas fa-user-tag" style="margin-right:10px;"></i>Employee Details</h2>
            <div class="side-panel-close" onclick="window.closeSidePanel()">&times;</div>
        </div>
        <div id="sidePanelContent" class="side-panel-content">
            <!-- Content injected by JS -->
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Global State
        let allEmployees = [];
        let currentPage = 1;
        const itemsPerPage = 10;


        // --- GLOBAL ACTION FUNCTIONS ---
        
        window.viewEmployeeDetails = function(employeeId) {
            console.log('Action: viewEmployeeDetails triggered for ID:', employeeId);
            if (!employeeId) {
                alert("Invalid Employee ID");
                return;
            }

            // CROSS-CLOSE: Ensure Add Modal is closed before showing details


            // Find in current data list
            let emp = allEmployees.find(e => e.employee_id == employeeId || e.id == employeeId);
            
            if (emp) {
                console.log('Employee found in local registry cache.');
                window.showDetailPanel(emp);
                return;
            }

            // Fallback: Fetch directly from API
            console.log('Employee not in local cache, fetching from server...');
            fetch(`get_kebele_hr_employee_detail.php?id=${encodeURIComponent(employeeId)}`)
                .then(res => res.json())
                .then(data => {
                    if (data && !data.error) {
                        window.showDetailPanel(data);
                    } else {
                        alert("Could not load employee details: " + (data.error || "Unknown error"));
                    }
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    alert("Network error while loading details.");
                });
        };

        window.deleteEmployee = function(id) {
            console.log('Action: deleteEmployee triggered for ID:', id);
            if (!id) return;

            // Identify employee name for confirmation
            const emp = allEmployees.find(e => e.employee_id == id || e.id == id);
            const empName = emp ? `${emp.first_name} ${emp.last_name}` : `ID: ${id}`;

            // MANDATORY PERMISSION DIALOG
            const hasPermission = window.confirm(
                "🛑 SYSTEM PERMISSION REQUIRED\n\n" +
                "Are you absolutely sure you want to PERMANENTLY DELETE " + empName.toUpperCase() + "?\n\n" +
                "This action will erase all personal records, documents, and history. This CANNOT be undone."
            );

            if (!hasPermission) {
                console.log('Delete action cancelled by user.');
                return;
            }

            // Proceed with deletion if permission granted
            const formData = new FormData();
            formData.append('employee_id', id);

            fetch('employee_actions.php?action=delete', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('CONFIRMED: Employee record has been removed.');
                    loadEmployees(currentPage);
                    window.closeSidePanel(); // Close if open
                } else {
                    alert('DELETE FAILED: ' + (data.message || 'Access denied or database error.'));
                }
            })
            .catch(err => {
                console.error('Delete error:', err);
                alert('Network error during deletion attempt.');
            });
        };

        function deleteEmployee(id) {
            // Already defined globally at the top
            window.deleteEmployee(id);
        }

        window.showDetailPanel = function(emp) {
            console.log('Rendering detail panel for:', emp);
            const content = document.getElementById('sidePanelContent');
            const empId = emp.employee_id || emp.id || '';
            
            const initials = (emp.first_name?.charAt(0) || '') + (emp.last_name?.charAt(0) || '');
            const birthDate = emp.date_of_birth ? new Date(emp.date_of_birth).toLocaleDateString('en-GB') : 'N/A';
            const joinDate = emp.join_date ? new Date(emp.join_date).toLocaleDateString('en-GB') : 'N/A';

            const maritalDisplay = emp.marital_status === 'other' ? (emp.other_marital_status || 'Other') : (emp.marital_status || 'N/A');
            const languageDisplay = emp.language === 'other' ? (emp.other_language || 'Other Language') : (emp.language || 'N/A');

            // Profile Picture Logic
            let profileHtml = '';
            if (emp.photo) {
                profileHtml = `<img src="../uploads/employees/${emp.photo}" style="width:100px; height:100px; border-radius:50%; object-fit:cover; border: 4px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">`;
            } else {
                profileHtml = `<div class="user-avatar" style="width: 100px; height: 100px; font-size: 2.5rem; margin: 0 auto 20px; background: white; color: var(--primary); box-shadow: 0 10px 20px rgba(0,0,0,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800;">${initials}</div>`;
            }

            content.innerHTML = `
                <div style="text-align:center; margin-bottom:25px; background: var(--primary); color: white; padding: 40px 20px; border-radius: 20px; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                    <div style="position: absolute; right: -20px; top: -20px; font-size: 15rem; opacity: 0.05;"><i class="fas fa-user-tie"></i></div>
                    ${profileHtml}
                    <h3 style="margin: 0; font-size: 1.8rem;">${emp.first_name} ${emp.middle_name || ''} ${emp.last_name}</h3>
                    <p style="opacity: 0.9; margin-top: 8px; font-size: 1.1rem;">${emp.position || 'Employee'} • <span style="font-weight: 700;">${empId}</span></p>
                    <div style="margin-top: 20px; display: inline-block; padding: 6px 18px; background: rgba(255,255,255,0.25); border-radius: 30px; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700;">${emp.status || 'Active'}</div>
                </div>

                <!-- Personal Info -->
                 <div class="info-card">
                    <div class="info-label"><i class="fas fa-user-circle"></i> Personal Information</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                        <div><div class="info-label">Gender</div><div class="info-value" style="text-transform: capitalize;">${emp.gender || 'N/A'}</div></div>
                        <div><div class="info-label">Date of Birth</div><div class="info-value">${birthDate}</div></div>
                        <div><div class="info-label">Marital Status</div><div class="info-value" style="text-transform: capitalize;">${maritalDisplay}</div></div>
                        <div><div class="info-label">Language</div><div class="info-value">${languageDisplay}</div></div>
                        <div><div class="info-label">Religion</div><div class="info-value">${emp.religion || 'N/A'}</div></div>
                        <div><div class="info-label">Citizenship</div><div class="info-value">${emp.citizenship || 'Ethiopian'} ${emp.other_citizenship ? '('+emp.other_citizenship+')' : ''}</div></div>
                        <div><div class="info-label">National ID (FIN)</div><div class="info-value">${emp.fin_id || 'N/A'}</div></div>
                        <div><div class="info-label">ID Details</div><div class="info-value">${emp.national_id_details || 'N/A'}</div></div>
                        <div><div class="info-label">Address</div><div class="info-value">${emp.address || 'N/A'}</div></div>
                    </div>
                </div>

                <!-- Employment Info -->
                <div class="info-card">
                    <div class="info-label"><i class="fas fa-briefcase"></i> Employment Details</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                        <div><div class="info-label">Position</div><div class="info-value">${emp.position || 'N/A'}</div></div>
                        <div><div class="info-label">Department</div><div class="info-value">${emp.department_assigned || 'N/A'}</div></div>
                        <div><div class="info-label">Join Date</div><div class="info-value">${joinDate}</div></div>
                        <div><div class="info-label">Salary</div><div class="info-value" style="color: var(--success); font-weight: 700;">${emp.salary ? 'ETB ' + parseFloat(emp.salary).toLocaleString() : 'N/A'}</div></div>
                        <div><div class="info-label">Contract Type</div><div class="info-value" style="text-transform: capitalize;">${emp.employment_type || 'N/A'}</div></div>
                    </div>
                </div>

                <!-- Academic Background -->
                <div class="info-card">
                    <div class="info-label"><i class="fas fa-graduation-cap"></i> Academic Background</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                        <div><div class="info-label">Education Level</div><div class="info-value" style="text-transform: capitalize;">${emp.education_level || 'N/A'}</div></div>
                        <div><div class="info-label">Field of Study</div><div class="info-value">${emp.department || 'N/A'}</div></div>
                        <div style="grid-column: 1 / -1;"><div class="info-label">University / College</div><div class="info-value">${emp.university || 'N/A'}</div></div>
                    </div>
                </div>

                <!-- Financial & Banking -->
                <div class="info-card">
                    <div class="info-label"><i class="fas fa-university"></i> Financial Information</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                        <div><div class="info-label">Bank Name</div><div class="info-value">${emp.bank_name || 'N/A'}</div></div>
                        <div><div class="info-label">Account No</div><div class="info-value" style="font-family: monospace;">${emp.bank_account || 'N/A'}</div></div>
                        <div><div class="info-label">Credit Status</div><div class="info-value">${emp.credit_status || 'Good'}</div></div>
                    </div>
                </div>

                <!-- Warranty & Legal -->
                <div class="info-card">
                    <div class="info-label"><i class="fas fa-shield-halved"></i> Warranty & Legal Status</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                        <div><div class="info-label">Guarantor Name</div><div class="info-value">${emp.person_name || 'N/A'}</div></div>
                        <div><div class="info-label">Guarantor Phone</div><div class="info-value">${emp.phone || 'N/A'}</div></div>
                        <div><div class="info-label">Criminal Record</div><div class="info-value">${emp.criminal_status === 'no' ? 'Clean' : 'Has Record'}</div></div>
                        <div><div class="info-label">Loan Status</div><div class="info-value">${emp.loan_status === 'no' ? 'No Loan' : 'Active Loan'}</div></div>
                    </div>
                     ${emp.criminal_record_details ? `<div style="margin-top:10px; padding:10px; background:#fef2f2; border:1px solid #fee2e2; border-radius:8px; color:#991b1b; font-size:0.9rem;"><strong>Record Details:</strong> ${emp.criminal_record_details}</div>` : ''}
                </div>

                <!-- Actions -->
                <div style="padding: 20px 0 40px; display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; gap: 15px;">
                        <button type="button" class="add-btn" style="flex: 2; height: 50px; font-size: 1rem; cursor: pointer; border: none; display: flex; align-items: center; justify-content: center; gap: 10px;" onclick="window.location.href='edit_employee.php?id=${empId}'">
                            <i class="fas fa-user-edit"></i> Edit Full Profile
                        </button>
                        <button type="button" class="cancel-btn" style="flex: 1; height: 50px; font-size: 1rem; cursor: pointer;" onclick="window.closeSidePanel()">Close</button>
                    </div>
                    <button type="button" class="action-btn delete" style="width: 100%; height: 50px; border-radius: 12px; font-size: 1rem; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 10px;" onclick="window.deleteEmployee('${empId}')">
                        <i class="fas fa-trash-alt"></i> Delete Employee Record
                    </button>
                </div>
            `;

            // Force Panel Visibility
            const panel = document.getElementById('employeeSidePanel');
            const overlay = document.getElementById('sidePanelOverlay');
            
            if (panel) panel.classList.add('open');
            if (overlay) {
                overlay.style.display = 'block';
                setTimeout(() => overlay.classList.add('active'), 10);
            }
        };

        window.closeSidePanel = function() {
            console.log('Closing side panel');
            document.getElementById('employeeSidePanel').classList.remove('open');
            const overlay = document.getElementById('sidePanelOverlay');
            if (overlay) {
                overlay.classList.remove('active');
                setTimeout(() => { if(!overlay.classList.contains('active')) overlay.style.display = 'none'; }, 400);
            }
        };





        document.addEventListener('DOMContentLoaded', () => {
            console.log('HR Systems Initializing...');
            loadEmployees(1);

            // Auto-open registration if requested from sidebar
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('register') === 'true') {
                window.openAddEmployeeModal();
            }

            // Inject Quick Action Button into Top Navbar
            const headerActions = document.querySelector('.header-actions');
            if (headerActions) {
                const quickBtn = document.createElement('button');
                quickBtn.className = 'add-btn';
                quickBtn.style.padding = '8px 15px';
                quickBtn.style.fontSize = '0.85rem';
                quickBtn.style.marginRight = '15px';
                quickBtn.style.border = 'none';
                quickBtn.style.cursor = 'pointer';
                quickBtn.innerHTML = '<i class="fas fa-plus"></i> New Employee';
                quickBtn.onclick = () => window.location.href = 'add_employee.php';
                headerActions.insertBefore(quickBtn, headerActions.firstChild);
            }

            // Initialize Search & Filter Listeners
            const searchInput = document.getElementById('employeeSearch');
            const deptFilter = document.getElementById('departmentFilter');
            const statusFilter = document.getElementById('statusFilter');

            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', () => {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        currentPage = 1;
                        loadEmployees(1);
                    }, 400);
                });
            }

            if (deptFilter) deptFilter.addEventListener('change', () => { currentPage = 1; loadEmployees(1); });
            if (statusFilter) statusFilter.addEventListener('change', () => { currentPage = 1; loadEmployees(1); });

            // File Upload Area Handling
            const dropZone = document.getElementById('fileDropZone');
            const fileInput = document.getElementById('multiFileInput');
            if (dropZone && fileInput) {
                dropZone.addEventListener('click', () => fileInput.click());
                dropZone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    dropZone.style.borderColor = 'var(--primary)';
                    dropZone.style.background = '#f0f9ff';
                });
                dropZone.addEventListener('dragleave', () => {
                    dropZone.style.borderColor = '#cbd5e1';
                    dropZone.style.background = '#f8fafc';
                });
                dropZone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    dropZone.style.borderColor = '#cbd5e1';
                    dropZone.style.background = '#f8fafc';
                    fileInput.files = e.dataTransfer.files;
                    updateFilesList();
                });
                fileInput.addEventListener('change', updateFilesList);
            }
        });

        function updateFilesList() {
            const fileInput = document.getElementById('multiFileInput');
            const filesContainer = document.getElementById('selectedFilesContainer');
            const filesList = document.getElementById('filesList');
            const fileCount = document.getElementById('fileCount');

            if (!fileInput || !filesList) return;

            const files = fileInput.files;
            if (files.length > 0) {
                filesContainer.style.display = 'block';
                fileCount.textContent = files.length;
                filesList.innerHTML = Array.from(files).map((file, i) => `
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:10px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:35px; height:35px; background:var(--primary); color:white; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                                <i class="fas ${getFileIcon(file.name)}"></i>
                            </div>
                            <div>
                                <div style="font-size:0.9rem; font-weight:600; color:#334155;">${file.name}</div>
                                <div style="font-size:0.75rem; color:#94a3b8;">${(file.size / 1024).toFixed(1)} KB</div>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                filesContainer.style.display = 'none';
            }
        }

        function getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            if (['jpg', 'jpeg', 'png'].includes(ext)) return 'fa-file-image';
            if (ext === 'pdf') return 'fa-file-pdf';
            return 'fa-file-alt';
        }

        function loadEmployees(page = 1) {
            currentPage = page;
            const tbody = document.getElementById('employeeTableBody');
            if (!tbody) return;

            const searchTerm = document.getElementById('employeeSearch')?.value || '';
            const dept = document.getElementById('departmentFilter')?.value || '';
            const status = document.getElementById('statusFilter')?.value || '';

            // Simple loading state
            if (page === 1) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 50px;"><i class="fas fa-circle-notch fa-spin fa-2x" style="color: var(--primary);"></i><p style="margin-top:10px; color:#64748b;">Loading registry...</p></td></tr>';
            }

            const url = `get_kebele_hr_employees.php?page=${page}&limit=${itemsPerPage}&search=${encodeURIComponent(searchTerm)}&department=${encodeURIComponent(dept)}&status=${status}`;

            console.log('Fetching employees from:', url);
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    console.log('Registry data received:', data);
                    if (data.success) {
                        allEmployees = data.employees;
                        renderTable(allEmployees);
                        renderPagination(data.total, data.total_pages || 1);
                    } else {
                        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:#ef4444; padding: 30px;"><i class="fas fa-exclamation-triangle"></i> ${data.error || 'Failed to load data'}</td></tr>`;
                    }
                })
                .catch(err => {
                    console.error('Fetch Error:', err);
                    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:#ef4444; padding: 30px;"><i class="fas fa-exclamation-circle"></i> Service Unavailable: ${err.message}. Please check if MySQL is running.</td></tr>`;
                });
        }

        function renderTable(data) {
            const tbody = document.getElementById('employeeTableBody');
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 50px;">No employees found matching your criteria.</td></tr>';
                return;
            }

            tbody.innerHTML = data.map(emp => {
                const initials = (emp.first_name?.charAt(0) || '') + (emp.last_name?.charAt(0) || '');
                const joinDate = emp.join_date ? new Date(emp.join_date).toLocaleDateString('en-GB') : 'N/A';
                const statusClass = (emp.status || 'active').toLowerCase();
                const avatarHtml = emp.photo 
                    ? `<img src="../uploads/employees/${emp.photo}" class="employee-avatar-circle" style="object-fit:cover; border:2px solid #e2e8f0;">`
                    : `<div class="employee-avatar-circle">${initials}</div>`;

                const empId = emp.employee_id || emp.id;
                return `
                    <tr class="clickable-row" onclick="window.viewEmployeeDetails('${empId}')">
                        <td>
                            <div style="display:flex; align-items:center; gap:12px;">
                                ${avatarHtml}
                                <div>
                                    <div style="font-weight:600; color:var(--primary);">${emp.first_name} ${emp.last_name}</div>
                                    <div style="font-size:0.8rem; color:#64748b;">${emp.email || 'No email'}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-family: monospace; font-weight: 600;">${empId}</td>
                        <td><span class="department-badge ${emp.department_assigned?.toLowerCase() || 'medical'}">${emp.department_assigned || 'Not Assigned'}</span></td>
                        <td>${emp.position || 'Employee'}</td>
                        <td>${joinDate}</td>
                        <td>
                            <span class="status-badge ${statusClass}">
                                ${statusClass.toUpperCase()}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button type="button" class="action-btn view" onclick="event.stopPropagation(); window.viewEmployeeDetails('${empId}')" title="View Details"><i class="fas fa-eye"></i></button>
                                <button type="button" class="action-btn edit" onclick="event.stopPropagation(); window.location.href='edit_employee.php?id=${empId}'" title="Edit Profile"><i class="fas fa-edit"></i></button>
                                <button type="button" class="action-btn delete" onclick="event.stopPropagation(); window.deleteEmployee('${empId}')" title="Delete Employee"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function renderPagination(totalRows, totalPages) {
            const container = document.getElementById('paginationContainer');
            if (totalPages <= 1) {
                container.innerHTML = `<div class="pagination-info">Total ${totalRows} employees found</div>`;
                return;
            }

            let html = `
                <div class="pagination-info">Showing ${totalRows > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0} to ${Math.min(currentPage * itemsPerPage, totalRows)} of ${totalRows} entries</div>
                <div class="pagination-btns">
                    <button class="page-link ${currentPage === 1 ? 'disabled' : ''}" onclick="loadEmployees(${currentPage - 1})">Prev</button>
            `;

            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    html += `<button class="page-link ${i === currentPage ? 'active' : ''}" onclick="loadEmployees(${i})">${i}</button>`;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    html += `<span class="page-link disabled">...</span>`;
                }
            }

            html += `
                    <button class="page-link ${currentPage === totalPages ? 'disabled' : ''}" onclick="loadEmployees(${currentPage + 1})">Next</button>
                </div>
            `;
            container.innerHTML = html;
        }

        function exportEmployees() {
            const searchTerm = document.getElementById('employeeSearch')?.value || '';
            const dept = document.getElementById('departmentFilter')?.value || '';
            const status = document.getElementById('statusFilter')?.value || '';
            
            const url = `employee_actions.php?action=export&search=${encodeURIComponent(searchTerm)}&department=${encodeURIComponent(dept)}&status=${status}`;
            window.location.href = url;
        }

        // ==========================================
        //  Restored & Enhanced Functions 
        // ==========================================

        window.viewEmployeeDetails = function(id) {
            const panel = document.getElementById('employeeSidePanel');
            const overlay = document.getElementById('sidePanelOverlay');
            const content = document.getElementById('sidePanelContent');
            
            content.innerHTML = '<div style="text-align:center; padding:50px;"><i class="fas fa-circle-notch fa-spin fa-2x" style="color:var(--primary);"></i></div>';
            panel.classList.add('open');
            overlay.classList.add('active');

            fetch(`get_kebele_hr_employee_detail.php?id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        content.innerHTML = `<div style="color:red; text-align:center;">${data.error}</div>`;
                        return;
                    }
                    
                    const fullName = `${data.first_name} ${data.middle_name || ''} ${data.last_name}`;
                    const initials = (data.first_name?.[0] || '') + (data.last_name?.[0] || '');
                    
                    // Profile Picture Logic
                    let profileHtml = '';
                    if (data.photo) {
                        profileHtml = `<img src="../uploads/employees/${data.photo}" style="width:100px; height:100px; border-radius:50%; object-fit:cover; border: 4px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">`;
                    } else {
                        profileHtml = `<div style="width:100px; height:100px; background:var(--primary); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2.5rem; font-weight:bold; margin:0 auto; border: 4px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">${initials}</div>`;
                    }

                    // Helper for badges
                    const statusBadge = `<span class="status-badge ${data.status?.toLowerCase() || 'active'}">${data.status || 'Active'}</span>`;
                    const criminalBadge = data.criminal_status === 'yes' 
                        ? `<span style="background:#fef2f2; color:#ef4444; padding:4px 8px; border-radius:6px; font-weight:600; font-size:0.8rem;"><i class="fas fa-exclamation-circle"></i> Has Record</span>` 
                        : `<span style="background:#f0fdf4; color:#16a34a; padding:4px 8px; border-radius:6px; font-weight:600; font-size:0.8rem;"><i class="fas fa-check-circle"></i> Clean</span>`;
                        
                    const loanBadge = data.loan_status === 'yes'
                        ? `<span style="background:#fff7ed; color:#ea580c; padding:4px 8px; border-radius:6px; font-weight:600; font-size:0.8rem;"><i class="fas fa-hand-holding-usd"></i> Active Loan</span>`
                        : `<span style="background:#f0fdf4; color:#16a34a; padding:4px 8px; border-radius:6px; font-weight:600; font-size:0.8rem;"><i class="fas fa-check-circle"></i> Debt Free</span>`;

                    let creditBadge = '';
                    if (data.credit_status === 'bad') {
                        creditBadge = `<span style="background:#fef2f2; color:#ef4444; padding:4px 8px; border-radius:6px; font-weight:600; font-size:0.8rem;"><i class="fas fa-thumbs-down"></i> Bad Credit</span>`;
                    } else if (data.credit_status === 'active') {
                        creditBadge = `<span style="background:#eff6ff; color:#3b82f6; padding:4px 8px; border-radius:6px; font-weight:600; font-size:0.8rem;"><i class="fas fa-hand-holding-usd"></i> Active Credit</span>`;
                    } else {
                        creditBadge = `<span style="background:#f0fdf4; color:#16a34a; padding:4px 8px; border-radius:6px; font-weight:600; font-size:0.8rem;"><i class="fas fa-thumbs-up"></i> Good Credit</span>`;
                    }

                    content.innerHTML = `
                        <div style="text-align:center; margin-bottom:30px; position: relative;">
                            <div style="position: absolute; top:0; left:0; width:100%; height:80px; background: linear-gradient(180deg, #e2e8f0 0%, transparent 100%); z-index:-1; border-radius: 12px 12px 0 0;"></div>
                            <div style="margin-bottom: 15px;">
                                ${profileHtml}
                            </div>
                            <h2 style="margin:0; font-size:1.5rem; color:#1e293b;">${fullName}</h2>
                            <p style="color:#64748b; margin:5px 0 10px;">${data.position || 'Employee'} • ${data.department_assigned || 'Unassigned'}</p>
                            <div style="display:flex; justify-content:center; gap:8px;">${statusBadge}</div>
                        </div>

                        <div class="info-card">
                            <h3 style="font-size:1.1rem; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:15px; display:flex; gap:10px; align-items:center;">
                                <i class="fas fa-user" style="color:var(--primary);"></i> Personal Information
                            </h3>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                                <div><div class="info-label">Gender</div><div class="info-value">${data.gender || '-'}</div></div>
                                <div><div class="info-label">Date of Birth</div><div class="info-value">${data.date_of_birth || '-'}</div></div>
                                <div><div class="info-label">Citizenship</div><div class="info-value">${data.citizenship || '-'}</div></div>
                                <div><div class="info-label">Marital Status</div><div class="info-value">${data.marital_status || '-'}</div></div>
                                <div><div class="info-label">National ID Details</div><div class="info-value">${data.national_id_details || '-'}</div></div>
                            </div>
                        </div>

                        <div class="info-card">
                            <h3 style="font-size:1.1rem; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:15px; display:flex; gap:10px; align-items:center;">
                                <i class="fas fa-shield-alt" style="color:#6366f1;"></i> Warranty, Legal & Financial
                            </h3>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                                <div><div class="info-label">Criminal Status</div><div class="info-value">${criminalBadge}</div></div>
                                <div><div class="info-label">Loan Status</div><div class="info-value">${loanBadge}</div></div>
                                <div><div class="info-label">Credit Status</div><div class="info-value">${creditBadge}</div></div>
                            </div>
                            ${data.criminal_record_details ? `<div style="background:#fef2f2; padding:10px; border-radius:8px; margin-bottom:15px; border:1px solid #fee2e2;">
                                <div class="info-label" style="color:#b91c1c;">Criminal Record Details</div>
                                <div class="info-value" style="color:#7f1d1d;">${data.criminal_record_details}</div>
                            </div>` : ''}



                            ${data.credit_details ? `<div style="background:#f0f9ff; padding:12px; border-radius:12px; margin-bottom:15px; border:1px solid #e0f2fe;">
                                <div class="info-label" style="color:#0369a1;">Credit Information / Details</div>
                                <div class="info-value" style="color:#0c4a6e;">${data.credit_details}</div>
                            </div>` : ''}
                            
                             <h4 style="font-size:0.95rem; color:#475569; margin:15px 0 10px; border-bottom:1px dashed #e2e8f0; padding-bottom:5px;">Guarantor Information</h4>
                             <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                                 <div style="grid-column:span 2;"><div class="info-label">Guarantor Name</div><div class="info-value">${data.person_name || 'N/A'}</div></div>
                                 <div><div class="info-label">Guarantor Phone</div><div class="info-value">${data.phone || 'N/A'}</div></div>
                                 <div><div class="info-label">National ID / FIN</div><div class="info-value">${data.fin_id || 'N/A'}</div></div>
                             </div>
                            <div style="margin-top:15px; display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                                <div><div class="info-label">Bank Account</div><div class="info-value">${data.bank_name || ''} - ${data.bank_account || 'N/A'}</div></div>
                                <div><div class="info-label">Job Level</div><div class="info-value">${data.job_level || 'N/A'}</div></div>
                            </div>
                        </div>

                        <div class="info-card">
                            <h3 style="font-size:1.1rem; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:15px; display:flex; gap:10px; align-items:center;">
                                <i class="fas fa-briefcase" style="color:#f59e0b;"></i> Employment Details
                            </h3>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                                <div><div class="info-label">Employee ID</div><div class="info-value" style="font-family:monospace;">${data.employee_id}</div></div>
                                <div><div class="info-label">Join Date</div><div class="info-value">${data.join_date || '-'}</div></div>
                                <div><div class="info-label">Salary</div><div class="info-value">${data.salary ? data.salary + ' ETB' : '-'}</div></div>
                                <div><div class="info-label">Employment Type</div><div class="info-value">${data.employment_type || '-'}</div></div>
                            </div>
                        </div>

                         <div class="info-card">
                             <h3 style="font-size:1.1rem; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:15px; display:flex; gap:10px; align-items:center;">
                                 <i class="fas fa-graduation-cap" style="color:#8b5cf6;"></i> Education
                             </h3>
                             <div style="display:grid; grid-template-columns:1fr; gap:15px;">
                                 <div><div class="info-label">Level</div><div class="info-value">${data.education_level || '-'}</div></div>
                                 <div><div class="info-label">Institution</div><div class="info-value">${data.university || data.college || data.school_name || '-'}</div></div>
                                 <div><div class="info-label">Department/Field</div><div class="info-value">${data.department || '-'}</div></div>
                             </div>
                         </div>

                         <div class="info-card">
                             <h3 style="font-size:1.1rem; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:15px; display:flex; gap:10px; align-items:center;">
                                 <i class="fas fa-file-contract" style="color:#6366f1;"></i> File Attachments
                             </h3>
                             <div style="display:flex; flex-direction:column; gap:10px;">
                                ${data.photo ? `<div style="background:#f8fafc; padding:10px; border-radius:10px; border:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
                                    <div style="font-size:0.85rem; color:#475569;"><i class="fas fa-image" style="margin-right:8px; color:#3b82f6;"></i> Profile Photo</div>
                                    <a href="../uploads/employees/${data.photo}" target="_blank" style="color:var(--primary); font-weight:600; font-size:0.8rem; text-decoration:none;"><i class="fas fa-eye"></i> View</a>
                                </div>` : ''}

                                 ${data.fin_scan ? `<div style="background:#f8fafc; padding:10px; border-radius:10px; border:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
                                    <div style="font-size:0.85rem; color:#475569;"><i class="fas fa-id-card" style="margin-right:8px; color:#34d399;"></i> National ID (FIN) Scan</div>
                                    <a href="../uploads/employees/${data.fin_scan}" target="_blank" style="color:var(--primary); font-weight:600; font-size:0.8rem; text-decoration:none;"><i class="fas fa-eye"></i> View</a>
                                </div>` : ''}

                                ${data.loan_file ? `<div style="background:#f8fafc; padding:10px; border-radius:10px; border:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
                                    <div style="font-size:0.85rem; color:#475569;"><i class="fas fa-file-invoice-dollar" style="margin-right:8px; color:#f59e0b;"></i> Credit Status Document</div>
                                    <a href="../uploads/employees/${data.loan_file}" target="_blank" style="color:var(--primary); font-weight:600; font-size:0.8rem; text-decoration:none;"><i class="fas fa-eye"></i> View</a>
                                </div>` : ''}

                                ${data.scan_file ? `<div style="background:#f8fafc; padding:10px; border-radius:10px; border:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
                                    <div style="font-size:0.85rem; color:#475569;"><i class="fas fa-paperclip" style="margin-right:8px; color:#6366f1;"></i> Warranty Agreement</div>
                                    <a href="../uploads/employees/${data.scan_file}" target="_blank" style="color:var(--primary); font-weight:600; font-size:0.8rem; text-decoration:none;"><i class="fas fa-eye"></i> View</a>
                                </div>` : ''}

                                ${data.criminal_file ? `<div style="background:#f8fafc; padding:10px; border-radius:10px; border:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
                                    <div style="font-size:0.85rem; color:#475569;"><i class="fas fa-balance-scale" style="margin-right:8px; color:#ef4444;"></i> Criminal Record Photo/Scan</div>
                                    <a href="../uploads/employees/${data.criminal_file}" target="_blank" style="color:var(--primary); font-weight:600; font-size:0.8rem; text-decoration:none;"><i class="fas fa-eye"></i> View</a>
                                </div>` : ''}
                             </div>
                         </div>

                         <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                            <button onclick="window.closeSidePanel()" style="padding:10px 20px; border:1px solid #cbd5e1; background:white; border-radius:8px; cursor:pointer;">Close</button>
                            <button onclick="window.location.href='edit_employee.php?id=${data.employee_id}'" style="padding:10px 20px; background:var(--primary); color:white; border:none; border-radius:8px; cursor:pointer;">Edit Details</button>
                        </div>
                    `;
                })
                .catch(err => {
                    content.innerHTML = '<div style="color:red; text-align:center;">Network Error</div>';
                    console.error(err);
                });
        };

        window.openAddEmployeeModal = function() {
            window.closeSidePanel();
            document.getElementById('addEmployeeModal').style.display = 'block';
            showStep(1);
        };

        window.closeAddEmployeeModal = function() {
            document.getElementById('addEmployeeModal').style.display = 'none';
            document.getElementById('addEmployeeForm').reset();
            currentStep = 1;
            // Clear files list
            const filesContainer = document.getElementById('selectedFilesContainer');
            if(filesContainer) filesContainer.style.display = 'none';
        };

        window.closeSidePanel = function() {
            document.getElementById('employeeSidePanel').classList.remove('open');
            document.getElementById('sidePanelOverlay').classList.remove('active');
        };

        window.deleteEmployee = function(id) {
            if(confirm('Are you sure you want to delete this employee? This action cannot be undone.')) {
                const formData = new FormData();
                formData.append('employee_id', id);
                
                fetch('employee_actions.php?action=delete', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        alert('Employee deleted successfully');
                        window.closeSidePanel();
                        loadEmployees(currentPage);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => alert('Network error'));
            }
        };



        // Helper functions for UI
        function previewPhoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById('previewImg');
                    img.src = e.target.result;
                    img.style.display = 'block';
                    document.querySelector('#photoPreview i').style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function toggleOtherField(select, targetId) {
            const target = document.getElementById(targetId);
            if(select.value === 'other') {
                target.classList.remove('hidden-field');
            } else {
                target.classList.add('hidden-field');
            }
        }

        // File Handling helpers
        function updateFilesList() {
            const fileInput = document.getElementById('multiFileInput');
            const filesContainer = document.getElementById('selectedFilesContainer');
            const filesList = document.getElementById('filesList');
            const fileCount = document.getElementById('fileCount');

            if (!fileInput || !filesList) return;

            const files = fileInput.files;
            if (files.length > 0) {
                filesContainer.style.display = 'block';
                fileCount.textContent = files.length;
                filesList.innerHTML = Array.from(files).map((file, i) => `
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:10px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:35px; height:35px; background:var(--primary); color:white; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                                <i class="fas ${getFileIcon(file.name)}"></i>
                            </div>
                            <div>
                                <div style="font-size:0.9rem; font-weight:600; color:#334155;">${file.name}</div>
                                <div style="font-size:0.75rem; color:#94a3b8;">${(file.size / 1024).toFixed(1)} KB</div>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                filesContainer.style.display = 'none';
            }
        }

        function getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            if (['jpg', 'jpeg', 'png'].includes(ext)) return 'fa-file-image';
            if (ext === 'pdf') return 'fa-file-pdf';
            return 'fa-file-alt';
        }
    </script>
    <script src="scripts.js"></script>
</body>
</html>