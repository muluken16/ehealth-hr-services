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

        .action-btn.view {
            background: #f0f9ff;
            color: #0369a1;
        }

        .action-btn.view:hover {
            background: #0369a1;
            color: white;
            transform: translateY(-2px);
        }

        .action-btn.edit {
            background: #f0fdf4;
            color: #15803d;
        }

        .action-btn.edit:hover {
            background: #15803d;
            color: white;
            transform: translateY(-2px);
        }

        .action-btn.delete {
            background: #fef2f2;
            color: #991b1b;
        }

        .action-btn.delete:hover {
            background: #991b1b;
            color: white;
            transform: translateY(-2px);
        }

        .action-btn:active {
            transform: scale(0.95);
        }

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

        /* Import Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header h2 {
            margin: 0 0 10px 0;
            color: var(--primary);
            font-size: 1.8rem;
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--gray);
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: #f1f5f9;
            color: #ef4444;
        }

        .upload-area {
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #f8fafc;
            margin-bottom: 20px;
        }

        .upload-area:hover {
            border-color: var(--primary);
            background: #eff6ff;
        }

        .upload-icon {
            font-size: 3rem;
            color: var(--secondary);
            margin-bottom: 15px;
        }

        .file-info {
            background: #eff6ff;
            padding: 15px;
            border-radius: 12px;
            display: none;
            margin-top: 15px;
        }

        .file-info.active {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .template-download {
            background: #f0fdf4;
            border: 2px solid #10b981;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .btn-template {
            background: #10b981;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-template:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .btn-upload {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-upload:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 25px;
        }

        .import-results {
            display: none;
            margin-top: 20px;
            padding: 20px;
            border-radius: 12px;
        }

        .import-results.active {
            display: block;
        }

        .import-results.success {
            background: #d1fae5;
            border: 2px solid #10b981;
        }

        .import-results.error {
            background: #fee2e2;
            border: 2px solid #ef4444;
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
                            <button class="section-action-btn" onclick="openImportModal()">
                                <i class="fas fa-file-import"></i> Import
                            </button>
                            <button class="section-action-btn" onclick="exportEmployees()">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <button class="section-action-btn" onclick="loadEmployees(1)">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
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
            <h2 style="margin:0; font-size:1.4rem; color:var(--primary);"><i class="fas fa-user-tag"
                    style="margin-right:10px;"></i>Employee Details</h2>
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


        // Helper for multi-file display
        function renderFiles(jsonStr, icon = 'fa-file') {
            if (!jsonStr) return '<span style="color:#94a3b8; font-style:italic;">No files attached</span>';
            let files = [];
            try {
                const parsed = JSON.parse(jsonStr);
                files = Array.isArray(parsed) ? parsed : [jsonStr];
            } catch (e) {
                files = [jsonStr];
            }
            if (files.length === 0 || (files.length === 1 && !files[0])) return '<span style="color:#94a3b8; font-style:italic;">No files attached</span>';

            return files.map(file => `
                <div style="background:#f8fafc; padding:8px 12px; border-radius:10px; border:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                    <div style="font-size:0.85rem; color:#475569; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:180px;">
                        <i class="fas ${icon}" style="margin-right:8px; color:var(--primary); opacity:0.7;"></i> ${file}
                    </div>
                    <a href="../uploads/employees/${file}" target="_blank" style="color:var(--primary); font-weight:700; font-size:0.75rem; text-decoration:none; padding:4px 8px; background:white; border-radius:6px; border:1px solid #e2e8f0;"><i class="fas fa-external-link-alt"></i> VIEW</a>
                </div>
            `).join('');
        }

        window.viewEmployeeDetails = function (employeeId) {
            console.log('Action: viewEmployeeDetails triggered for ID:', employeeId);
            const panel = document.getElementById('employeeSidePanel');
            const overlay = document.getElementById('sidePanelOverlay');
            const content = document.getElementById('sidePanelContent');

            content.innerHTML = '<div style="text-align:center; padding:100px 0;"><i class="fas fa-circle-notch fa-spin fa-3x" style="color:var(--primary); opacity:0.5;"></i><p style="margin-top:20px; color:var(--gray);">Loading profile data...</p></div>';
            panel.classList.add('open');
            overlay.classList.add('active');
            overlay.style.display = 'block';

            // Find in current data list or fetch
            let emp = allEmployees.find(e => e.employee_id == employeeId || e.id == employeeId);

            const renderData = (data) => {
                const initials = (data.first_name?.[0] || '') + (data.last_name?.[0] || '');
                const fullName = `${data.first_name} ${data.middle_name || ''} ${data.last_name}`;

                // Badges
                const statusBadge = `<span class="status-badge ${data.status?.toLowerCase() || 'active'}">${(data.status || 'Active').toUpperCase()}</span>`;
                const criminalBadge = data.criminal_status === 'yes'
                    ? `<span style="background:#fef2f2; color:#ef4444; padding:4px 10px; border-radius:20px; font-weight:700; font-size:0.75rem;"><i class="fas fa-exclamation-circle"></i> HAS RECORD</span>`
                    : `<span style="background:#f0fdf4; color:#16a34a; padding:4px 10px; border-radius:20px; font-weight:700; font-size:0.75rem;"><i class="fas fa-check-circle"></i> CLEAN</span>`;

                const loanBadge = data.loan_status === 'yes'
                    ? `<span style="background:#fff7ed; color:#ea580c; padding:4px 10px; border-radius:20px; font-weight:700; font-size:0.75rem;"><i class="fas fa-hand-holding-usd"></i> ACTIVE LOAN</span>`
                    : `<span style="background:#f0fdf4; color:#16a34a; padding:4px 10px; border-radius:20px; font-weight:700; font-size:0.75rem;"><i class="fas fa-check-circle"></i> DEBT FREE</span>`;

                content.innerHTML = `
                    <div style="text-align:center; margin-bottom:30px; position:relative;">
                        <div style="position:absolute; top:-25px; left:-35px; right:-35px; height:120px; background:linear-gradient(135deg, var(--primary), var(--secondary)); opacity:0.1; z-index:0; border-radius: 0 0 50% 50%;"></div>
                        <div style="position:relative; z-index:1; padding-top:20px;">
                            ${data.photo ? `<img src="../uploads/employees/${data.photo}" style="width:110px; height:110px; border-radius:50%; object-fit:cover; border:5px solid white; box-shadow:0 10px 20px rgba(0,0,0,0.1);">`
                        : `<div style="width:110px; height:110px; background:var(--primary); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:3rem; font-weight:800; margin:0 auto; border:5px solid white; box-shadow:0 10px 20px rgba(0,0,0,0.1);">${initials}</div>`}
                            <h2 style="margin:15px 0 5px; font-size:1.6rem; color:var(--primary);">${fullName}</h2>
                            <p style="color:var(--gray); margin-bottom:15px; font-weight:500;">${data.position || 'Professional'} • <span style="color:var(--primary); font-weight:700;">${data.employee_id || data.id}</span></p>
                            <div style="display:flex; justify-content:center; gap:10px;">${statusBadge}</div>
                        </div>
                    </div>

                    <div style="display:flex; gap:10px; margin-bottom:30px;">
                        <button onclick="window.location.href='edit_employee.php?id=${data.employee_id || data.id}'" style="flex:1; padding:12px; background:var(--primary); color:white; border:none; border-radius:12px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                            <i class="fas fa-user-edit"></i> Edit Profile
                        </button>
                        <button onclick="window.deleteEmployee('${data.employee_id || data.id}')" style="padding:12px; background:#fef2f2; color:#ef4444; border:none; border-radius:12px; width:50px; cursor:pointer;">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>

                    <div class="info-card">
                        <div class="step-title-premium" style="margin-bottom:15px;"><i class="fas fa-user"></i> Personal Data</div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                            <div><div class="info-label">Gender</div><div class="info-value" style="text-transform:capitalize;">${data.gender || '-'}</div></div>
                            <div><div class="info-label">Born On</div><div class="info-value">${data.date_of_birth || '-'}</div></div>
                            <div><div class="info-label">Citizenship</div><div class="info-value">${data.citizenship || 'Ethiopian'} ${data.other_citizenship ? '(' + data.other_citizenship + ')' : ''}</div></div>
                            <div><div class="info-label">Marital Status</div><div class="info-value" style="text-transform:capitalize;">${data.marital_status || '-'} ${data.other_marital_status ? '(' + data.other_marital_status + ')' : ''}</div></div>
                            <div><div class="info-label">Religion</div><div class="info-value">${data.religion || '-'}</div></div>
                            <div><div class="info-label">Language</div><div class="info-value">${data.language || '-'} ${data.other_language ? '(' + data.other_language + ')' : ''}</div></div>
                            <div style="grid-column:span 2;"><div class="info-label">Address / Residence</div><div class="info-value">${data.address || '-'}</div></div>
                            <div><div class="info-label">Region</div><div class="info-value" style="text-transform:capitalize;">${(data.region || 'N/A').replace('_', ' ')}</div></div>
                            <div><div class="info-label">Zone</div><div class="info-value">${data.zone || '-'}</div></div>
                            <div><div class="info-label">Woreda</div><div class="info-value">${data.woreda || '-'}</div></div>
                            <div><div class="info-label">Kebele</div><div class="info-value">${data.kebele || '-'}</div></div>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="step-title-premium" style="margin-bottom:15px;"><i class="fas fa-briefcase"></i> Employment & Work</div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                            <div><div class="info-label">Job Title</div><div class="info-value">${data.position || '-'}</div></div>
                            <div><div class="info-label">Work Dep.</div><div class="info-value" style="text-transform:capitalize;">${data.department_assigned || '-'}</div></div>
                            <div><div class="info-label">Hired On</div><div class="info-value">${data.join_date || '-'}</div></div>
                            <div><div class="info-label">Job Type</div><div class="info-value" style="text-transform:capitalize;">${data.employment_type || '-'}</div></div>
                            <div><div class="info-label">Salary</div><div class="info-value" style="color:var(--success); font-weight:700;">${data.salary ? parseFloat(data.salary).toLocaleString() + ' ETB' : 'N/A'}</div></div>
                            <div><div class="info-label">Phone</div><div class="info-value">${data.phone_number || '-'}</div></div>
                            <div style="grid-column:span 2;"><div class="info-label">Email</div><div class="info-value">${data.email || 'N/A'}</div></div>
                            <div style="grid-column:span 2;"><div class="info-label">Work Location (Assigned)</div><div class="info-value">${data.working_woreda || '-'} / ${data.working_kebele || '-'}</div></div>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="step-title-premium" style="margin-bottom:15px;"><i class="fas fa-graduation-cap"></i> Education Records</div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                            <div style="grid-column:span 2;"><div class="info-label">Highest Qualification</div><div class="info-value">${data.education_level || '-'}</div></div>
                            <div style="grid-column:span 2;"><div class="info-label">Field / Department</div><div class="info-value">${data.department || '-'} ${data.other_department ? '(' + data.other_department + ')' : ''}</div></div>
                            <div style="grid-column:span 2;"><div class="info-label">University / College</div><div class="info-value">${data.university || data.college || '-'}</div></div>
                            <div><div class="info-label">Secondary School</div><div class="info-value">${data.secondary_school || '-'}</div></div>
                            <div><div class="info-label">Primary School</div><div class="info-value">${data.primary_school || '-'}</div></div>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="step-title-premium" style="margin-bottom:15px;"><i class="fas fa-university"></i> Banking & Financials</div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                            <div><div class="info-label">Bank Name</div><div class="info-value">${data.bank_name || 'N/A'} ${data.other_bank_name ? '(' + data.other_bank_name + ')' : ''}</div></div>
                            <div><div class="info-label">Acc Number</div><div class="info-value" style="font-family:monospace;">${data.bank_account || '-'}</div></div>
                            <div><div class="info-label">Credit Status</div><div class="info-value">${(data.credit_status || 'Good').toUpperCase()}</div></div>
                            <div><div class="info-label">Debt Profile</div><div class="info-value">${loanBadge}</div></div>
                        </div>
                        ${data.loan_status === 'yes' ? `
                            <div style="background:#fffcf5; border:1px solid #fef3c7; border-radius:12px; padding:15px; margin-bottom:15px;">
                                <div class="info-label" style="color:#92400e; margin-bottom:10px;"><i class="fas fa-info-circle"></i> Active Loan Details</div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div><div class="info-label">Lender</div><div class="info-value">${data.loan_lender || '-'}</div></div>
                                    <div><div class="info-label">Loan Type</div><div class="info-value">${data.loan_type || '-'}</div></div>
                                    <div><div class="info-label">Total Amount</div><div class="info-value">${data.loan_amount ? parseFloat(data.loan_amount).toLocaleString() + ' ETB' : '-'}</div></div>
                                    <div><div class="info-label">Remaining</div><div class="info-value" style="color:#ef4444;">${data.remaining_balance ? parseFloat(data.remaining_balance).toLocaleString() + ' ETB' : '-'}</div></div>
                                    <div><div class="info-label">Monthly Pay</div><div class="info-value">${data.monthly_payment ? parseFloat(data.monthly_payment).toLocaleString() + ' ETB' : '-'}</div></div>
                                    <div><div class="info-label">End Date</div><div class="info-value">${data.loan_end_date || '-'}</div></div>
                                </div>
                                ${data.loan_purpose ? `<div style="margin-top:10px; font-size:0.8rem; border-top:1px solid #fef3c7; padding-top:8px;"><strong>Purpose:</strong> ${data.loan_purpose}</div>` : ''}
                                ${data.loan_collateral ? `<div style="margin-top:5px; font-size:0.8rem;"><strong>Collateral:</strong> ${data.loan_collateral}</div>` : ''}
                            </div>
                        ` : ''}
                        ${data.credit_details ? `<div style="background:#f8fafc; padding:10px; border-radius:10px; border:1px solid #e2e8f0; font-size:0.85rem; color:#475569;"><strong>Financial History:</strong> ${data.credit_details}</div>` : ''}
                    </div>

                    <div class="info-card">
                        <div class="step-title-premium" style="margin-bottom:15px;"><i class="fas fa-shield-alt"></i> Warranty & Legal</div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                            <div style="grid-column:span 2;"><div class="info-label">Guarantor Name</div><div class="info-value" style="font-weight:700;">${data.person_name || 'N/A'}</div></div>
                            <div><div class="info-label">Guarantor Phone</div><div class="info-value">${data.phone || 'N/A'}</div></div>
                            <div><div class="info-label">Relationship</div><div class="info-value">${data.person_relationship || 'N/A'}</div></div>
                            <div><div class="info-label">National ID (FIN)</div><div class="info-value">${data.fin_id || 'N/A'}</div></div>
                            <div><div class="info-label">Court Status</div><div class="info-value">${criminalBadge}</div></div>
                        </div>
                        
                        ${data.warranty_status === 'yes' ? `
                            <div style="background:#f0fdfa; border:1px solid #ccfbf1; border-radius:12px; padding:15px; margin-bottom:15px;">
                                <div class="info-label" style="color:#0f766e; margin-bottom:10px;"><i class="fas fa-file-contract"></i> Warranty Terms</div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div><div class="info-label">Type</div><div class="info-value">${data.warranty_type || '-'}</div></div>
                                    <div><div class="info-label">Amount/Value</div><div class="info-value">${data.warranty_amount ? parseFloat(data.warranty_amount).toLocaleString() + ' ETB' : '-'}</div></div>
                                    <div><div class="info-label">Start Date</div><div class="info-value">${data.warranty_start_date || '-'}</div></div>
                                    <div><div class="info-label">Expiry Date</div><div class="info-value">${data.warranty_end_date || '-'}</div></div>
                                </div>
                                ${data.warranty_address ? `<div style="margin-top:10px; font-size:0.8rem; border-top:1px solid #ccfbf1; padding-top:8px;"><strong>Location:</strong> ${data.warranty_address}</div>` : ''}
                                ${data.warranty_notes ? `<div style="margin-top:5px; font-size:0.8rem;"><strong>Agreement Notes:</strong> ${data.warranty_notes}</div>` : ''}
                            </div>
                        ` : ''}

                        ${data.criminal_status === 'yes' ? `
                            <div style="background:#fef2f2; border:1px solid #fee2e2; border-radius:12px; padding:15px; margin-bottom:15px;">
                                <div class="info-label" style="color:#b91c1c; margin-bottom:10px;"><i class="fas fa-gavel"></i> Case Information</div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div><div class="info-label">Type</div><div class="info-value">${data.criminal_type || '-'}</div></div>
                                    <div><div class="info-label">Date</div><div class="info-value">${data.criminal_date || '-'}</div></div>
                                    <div style="grid-column:span 2;"><div class="info-label">Court</div><div class="info-value">${data.criminal_court || '-'}</div></div>
                                    <div><div class="info-label">Sentence</div><div class="info-value">${data.criminal_sentence || '-'}</div></div>
                                    <div><div class="info-label">Case Status</div><div class="info-value">${data.criminal_status_current || '-'}</div></div>
                                </div>
                                ${data.criminal_description ? `<div style="margin-top:10px; font-size:0.8rem; border-top:1px solid #fee2e2; padding-top:8px;"><strong>Case Details:</strong> ${data.criminal_description}</div>` : ''}
                                ${data.criminal_record_details ? `<div style="margin-top:5px; font-size:0.8rem; color:#b91c1c;"><strong>Notes:</strong> ${data.criminal_record_details}</div>` : ''}
                            </div>
                        ` : ''}
                        
                        ${data.national_id_details ? `<div style="background:#f8fafc; padding:10px; border-radius:10px; border:1px solid #e2e8f0; font-size:0.85rem; color:#475569; margin-top:10px;"><strong>National ID Notes:</strong> ${data.national_id_details}</div>` : ''}
                    </div>

                    <div class="info-card">
                        <div class="step-title-premium" style="margin-bottom:15px;"><i class="fas fa-file-contract"></i> Documents & Scans</div>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <div class="info-label">Identity Docs</div>
                            ${renderFiles(data.fin_scan, 'fa-id-card')}
                            
                            <div class="info-label" style="margin-top:10px;">Education Certificates</div>
                            ${renderFiles(data.education_file, 'fa-graduation-cap')}
                            
                            <div class="info-label" style="margin-top:10px;">Employment Contracts</div>
                            ${renderFiles(data.employment_agreement, 'fa-file-signature')}
                            
                            <div class="info-label" style="margin-top:10px;">Warranty / Guarantor Docs</div>
                            ${renderFiles(data.scan_file, 'fa-shield-halved')}
                            
                            <div class="info-label" style="margin-top:10px;">Financial / Credit Docs</div>
                            ${renderFiles(data.loan_file, 'fa-money-check-alt')}
                            
                            <div class="info-label" style="margin-top:10px;">Legal / Criminal Record Docs</div>
                            ${renderFiles(data.criminal_file, 'fa-balance-scale')}
                            
                            <div class="info-label" style="margin-top:10px;">Additional Full Documentation</div>
                            ${renderFiles(data.documents, 'fa-folder-open')}
                        </div>
                    </div>

                    <div style="padding-bottom:50px;">
                        <button onclick="window.closeSidePanel()" style="width:100%; padding:14px; background:#f1f5f9; color:var(--gray); border:none; border-radius:12px; font-weight:700; cursor:pointer;">
                            Close View
                        </button>
                    </div>
                `;
            };

            if (emp) {
                console.log('Using cached data for render.');
                renderData(emp);
                return;
            }

            // Fallback: Fetch directly from API
            fetch(`get_kebele_hr_employee_detail.php?id=${encodeURIComponent(employeeId)}`)
                .then(res => res.json())
                .then(data => {
                    if (data && !data.error) {
                        renderData(data);
                    } else {
                        content.innerHTML = `<div style="padding:40px; text-align:center; color:#ef4444;"><i class="fas fa-exclamation-circle fa-2x"></i><p style="margin-top:10px;">${data.error || "Profile not found or access denied."}</p></div>`;
                    }
                })
                .catch(err => {
                    content.innerHTML = '<div style="padding:40px; text-align:center; color:#ef4444;"><i class="fas fa-wifi-slash fa-2x"></i><p style="margin-top:10px;">Connection failed.</p></div>';
                });
        };

        window.closeSidePanel = function () {
            console.log('Closing side panel');
            const panel = document.getElementById('employeeSidePanel');
            const overlay = document.getElementById('sidePanelOverlay');
            if (panel) panel.classList.remove('open');
            if (overlay) {
                overlay.classList.remove('active');
                setTimeout(() => { if (!overlay.classList.contains('active')) overlay.style.display = 'none'; }, 400);
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

        // Redundant sections removed (consolidated above)


        window.deleteEmployee = function (id) {
            if (confirm('Are you sure you want to delete this employee? This action cannot be undone.')) {
                const formData = new FormData();
                formData.append('employee_id', id);

                fetch('employee_actions.php?action=delete', {
                    method: 'POST',
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
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
                reader.onload = function (e) {
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
            if (select.value === 'other') {
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
    // Import Modal Functions
        function openImportModal() {
            document.getElementById('importModal').classList.add('active');
            document.getElementById('fileInput').value = '';
            document.getElementById('fileInfo').classList.remove('active');
            document.getElementById('importResults').classList.remove('active');
            document.getElementById('uploadBtn').disabled = true;
        }

        function closeImportModal() {
            document.getElementById('importModal').classList.remove('active');
        }

        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                document.getElementById('fileName').textContent = file.name;
                document.getElementById('fileSize').textContent = (file.size / 1024).toFixed(2) + ' KB';
                document.getElementById('fileInfo').classList.add('active');
                document.getElementById('uploadBtn').disabled = false;
            }
        }

        function uploadImportFile() {
            const file = document.getElementById('fileInput').files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('import_file', file);

            document.getElementById('uploadBtn').disabled = true;
            document.getElementById('uploadBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';

            fetch('import_employees.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const results = document.getElementById('importResults');
                const content = document.getElementById('resultsContent');
                
                results.classList.add('active');
                results.classList.remove('success', 'error');
                results.classList.add(data.success ? 'success' : 'error');
                
                let html = `<h4><i class="fas fa-${data.success ? 'check-circle' : 'exclamation-circle'}"></i> ${data.message}</h4>`;
                if (data.imported) html += `<p><strong>Imported:</strong> ${data.imported} employee(s)</p>`;
                if (data.errors && data.errors.length > 0) {
                    html += '<div style="margin-top: 15px;"><strong>Errors:</strong><ul style="margin: 10px 0; padding-left: 20px;">';
                    data.errors.forEach(error => html += `<li>${error}</li>`);
                    html += '</ul></div>';
                }
                content.innerHTML = html;

                document.getElementById('uploadBtn').innerHTML = '<i class="fas fa-upload"></i> Upload & Import';
                
                if (data.success && data.imported > 0) {
                    setTimeout(() => {
                        closeImportModal();
                        loadEmployees(1);
                    }, 3000);
                } else {
                    document.getElementById('uploadBtn').disabled = false;
                }
            })
            .catch(error => {
                alert('Import failed: ' + error.message);
                document.getElementById('uploadBtn').innerHTML = '<i class="fas fa-upload"></i> Upload & Import';
                document.getElementById('uploadBtn').disabled = false;
            });
        }
    </script>
    <script src="scripts.js"></script>

    <!-- Import Modal -->
    <div id="importModal" class="modal-overlay">
        <div class="modal-box">
            <button class="modal-close" onclick="closeImportModal()">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="modal-header">
                <h2><i class="fas fa-file-import"></i> Import Employees</h2>
                <p>Upload a CSV file to bulk import employee data</p>
            </div>

            <div class="template-download">
                <h4><i class="fas fa-download"></i> Download Template</h4>
                <p>First time importing? Download our sample template to see the correct format.</p>
                <a href="download_template.php" class="btn-template">
                    <i class="fas fa-file-csv"></i> Download CSV Template
                </a>
            </div>

            <div id="uploadArea" class="upload-area" onclick="document.getElementById('fileInput').click()">
                <div class="upload-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 8px;">Click to browse or drag and drop</div>
                <div style="font-size: 0.9rem; color: var(--gray);">Supports CSV files (Excel saved as CSV)</div>
            </div>

            <input type="file" id="fileInput" accept=".csv" style="display: none;" onchange="handleFileSelect(event)">

            <div id="fileInfo" class="file-info">
                <i class="fas fa-file-csv"></i>
                <div>
                    <strong id="fileName">-</strong>
                    <div style="font-size: 0.85rem; color: var(--gray);" id="fileSize">-</div>
                </div>
            </div>

            <div id="importResults" class="import-results">
                <div id="resultsContent"></div>
            </div>

            <div class="modal-actions">
                <button class="btn btn-outline" onclick="closeImportModal()">Cancel</button>
                <button id="uploadBtn" class="btn-upload" onclick="uploadImportFile()" disabled>
                    <i class="fas fa-upload"></i> Upload & Import
                </button>
            </div>
        </div>
    </div>
</body>

</html>