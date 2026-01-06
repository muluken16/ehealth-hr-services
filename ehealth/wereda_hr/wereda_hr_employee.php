<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure role is set for the demo (Dev Fallback)
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'wereda_hr';
    $_SESSION['user_name'] = 'Wereda HR Officer';
    $_SESSION['woreda'] = 'Woreda 1';
    $_SESSION['user_id'] = 'DEMO_USER';
}

if ($_SESSION['role'] != 'wereda_hr') {
    header('Location: ../index.html');
    exit();
}

require_once '../db.php';
$kebeles = [];
try {
    $conn = getDBConnection();
    $woreda_wildcard = "%" . ($_SESSION['woreda'] ?? 'Woreda 1') . "%";
    $stmt = $conn->prepare("SELECT DISTINCT working_kebele FROM employees WHERE woreda LIKE ? AND working_kebele IS NOT NULL AND working_kebele != ''");
    $stmt->bind_param("s", $woreda_wildcard);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()) { $kebeles[] = $row['working_kebele']; }
    sort($kebeles);
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | HR Employees</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        .modal-content.large-modal { max-width: 1000px; width: 95%; border-radius: 20px; overflow: hidden; padding: 0; }
        .modal-header-banner { background: var(--primary); color: white; padding: 30px; position: relative; }
        .modal-header-banner h2 { margin: 0; font-size: 1.8rem; }
        .modal-header-banner p { margin: 5px 0 0 0; opacity: 0.8; font-size: 0.9rem; }
        .modal-body { padding: 40px; max-height: 80vh; overflow-y: auto; }
        
        .form-section-title {
            font-size: 1.1rem;
            color: var(--primary);
            border-bottom: 2px solid #f0f2f5;
            padding-bottom: 12px;
            margin: 30px 0 20px 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-section-title i { color: var(--secondary); opacity: 0.8; }
        .form-section-title:first-child { margin-top: 0; }

        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 25px; margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #4a5568; margin-bottom: 8px; }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.2s;
            background: #fafafa;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(26, 74, 95, 0.1);
            outline: none;
        }
        .file-input-wrapper { 
            border: 2px dashed #e2e8f0; 
            padding: 20px; 
            border-radius: 12px; 
            text-align: center; 
            background: #f8fafc;
            transition: all 0.2s;
            cursor: pointer;
        }
        .file-input-wrapper:hover { border-color: var(--primary); background: #f1f5f9; }
        
        /* Custom Scrollbar for Modal Body */
        .modal-body::-webkit-scrollbar { width: 8px; }
        .modal-body::-webkit-scrollbar-track { background: #f1f1f1; }
        .modal-body::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 4px; }
        .modal-body::-webkit-scrollbar-thumb:hover { background: #a0aec0; }
        
        /* Pagination Styles */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding: 15px 0;
            border-top: 1px solid #eee;
        }
        .pagination-info { font-size: 0.9rem; color: var(--gray); }
        .pagination-btns { display: flex; gap: 5px; }
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
        .page-link:hover { background: #f0f4f8; }
        .page-link.active { background: var(--primary); color: white; border-color: var(--primary); }
        .page-link.disabled { color: #ccc; cursor: not-allowed; pointer-events: none; }

        /* Side Panel Styles */
        .side-panel {
            position: fixed;
            top: 0;
            right: -450px;
            width: 450px;
            height: 100vh;
            background: white;
            box-shadow: -5px 0 25px rgba(0,0,0,0.1);
            z-index: 1100;
            transition: right 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            overflow-y: auto;
            padding: 30px;
        }
        .side-panel.open { right: 0; }
        .side-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #f0f2f5;
            padding-bottom: 15px;
        }
        .side-panel-close { cursor: pointer; font-size: 1.5rem; color: var(--gray); transition: color 0.2s; }
        .side-panel-close:hover { color: var(--danger); }
        .info-card { background: #f8fbff; border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 1px solid #edf2f7; }
        .info-label { font-size: 0.75rem; text-transform: uppercase; color: #718096; letter-spacing: 0.5px; margin-bottom: 4px; font-weight: 700; }
        .info-value { font-size: 0.95rem; color: #2d3748; font-weight: 500; }
        .side-panel-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.3);
            z-index: 1099;
            display: none;
        }
        .side-panel-overlay.active { display: block; }
        tr.clickable-row { cursor: pointer; transition: all 0.2s ease; }
        tr.clickable-row:hover { background: #f8fafc !important; transform: scale(1.001); box-shadow: inset 4px 0 0 var(--primary); }
        
        .print-btn {
            background: white;
            color: #4a5568;
            border: 1.5px solid #e2e8f0;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .print-btn:hover { background: #f8fafc; border-color: #cbd5e0; color: #1e293b; }
        /* Badge Styles */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-badge.active { background: #ecfdf5; color: #059669; }
        .status-badge.inactive { background: #fef2f2; color: #dc2626; }
        .status-badge.on-leave { background: #fffbeb; color: #d97706; }

        .department-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .department-badge.medical { background: #eff6ff; color: #2563eb; }
        .department-badge.admin { background: #f5f3ff; color: #7c3aed; }
        .department-badge.finance { background: #ecfdf5; color: #059669; }
        .department-badge.support { background: #f8fafc; color: #475569; }

        /* Action Buttons */
        .action-buttons { display: flex; gap: 8px; }
        .action-btn {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
        }
        .action-btn.view { background: #eff6ff; color: #2563eb; }
        .action-btn.view:hover { background: #2563eb; color: white; }
        .action-btn.edit { background: #f5f3ff; color: #7c3aed; }
        .action-btn.edit:hover { background: #7c3aed; color: white; }
        .action-btn.delete { background: #fef2f2; color: #dc2626; }
        .action-btn.delete:hover { background: #dc2626; color: white; }

        .side-panel::-webkit-scrollbar { width: 6px; }
        .side-panel::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

        /* Side Panel Tabs */
        .side-panel-tabs {
            display: flex;
            gap: 5px;
            margin-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 5px;
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
        }
        .side-tab {
            padding: 10px 15px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            border-radius: 8px 8px 0 0;
            transition: all 0.2s;
            border-bottom: 2px solid transparent;
        }
        .side-tab:hover { color: var(--primary); background: #f8fafc; }
        .side-tab.active {
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
            background: #f0f7ff;
        }
        .tab-content { display: none; animation: fadeIn 0.3s ease; }
        .tab-content.active { display: block; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Enhanced Dashboard Layout */
        .hr-dashboard { padding: 30px; }
        
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); 
            gap: 20px; 
            margin-bottom: 30px; 
        }
        
        .stat-card-premium {
            background: white;
            padding: 24px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            gap: 20px;
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
        }
        .stat-card-premium:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .stat-icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }
        .stat-info .label { font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-info .value { font-size: 1.6rem; font-weight: 800; color: var(--primary); margin-top: 2px; }

        /* Filter Section Cleaning */
        .filters-section {
            background: white;
            padding: 20px 25px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            margin-bottom: 30px;
            border: 1px solid #f1f5f9;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        .search-box { flex: 1; min-width: 250px; position: relative; }
        .search-box input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border-radius: 12px;
            border: 1.5px solid #edf2f7;
            background: #f8fafc;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .search-box input:focus { background: white; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(26, 74, 95, 0.05); outline: none; }
        .search-box i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #94a3b8; }

        .filter-select {
            padding: 12px 20px;
            border-radius: 12px;
            border: 1.5px solid #edf2f7;
            background: #f8fafc;
            color: #4a5568;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .filter-select:hover { border-color: #cbd5e0; }

        .add-btn {
            background: linear-gradient(135deg, var(--primary), #2a6e8c);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(26, 74, 95, 0.2);
            transition: all 0.2s;
        }
        .add-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(26, 74, 95, 0.3); }

        .hr-section { border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.04); border-radius: 20px; overflow: hidden; }
        .hr-section-header { background: white; padding: 25px 30px; border-bottom: 1.5px solid #f1f5f9; }
        .hr-section-title { font-size: 1.3rem; font-weight: 700; color: #1e293b; }
        .hr-section-body { padding: 0; }
        .table { min-width: 100%; border-collapse: separate; border-spacing: 0; }
        .table th { 
            background: #f8fafc; 
            padding: 16px 20px; 
            font-size: 0.8rem; 
            text-transform: uppercase; 
            color: #64748b; 
            font-weight: 700; 
            letter-spacing: 1px; 
            border-bottom: 2px solid #f1f5f9;
        }
        .table td { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; vertical-align: middle; }
        .table tr:last-child td { border-bottom: none; }
        
        /* Premium Profile Header in Side Panel */
        .profile-header-premium {
            text-align: center;
            margin-bottom: 25px;
            background: linear-gradient(135deg, var(--primary) 0%, #2a6e8c 100%);
            color: white;
            padding: 45px 20px;
            border-radius: 24px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(26, 74, 95, 0.2);
        }
        .profile-header-premium::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>
        <?php
        $page_title = 'Employee Directory';
        include 'navbar.php';
        ?>
            <div class="hr-dashboard">
                <!-- Summary Statistics -->
                <div class="stats-grid">
                    <div class="stat-card-premium">
                        <div class="stat-icon" style="background: #eff6ff; color: #2563eb;"><i class="fas fa-users"></i></div>
                        <div class="stat-info">
                            <div class="label">Total Workforce</div>
                            <div class="value" id="stat-total">0</div>
                        </div>
                    </div>
                    <div class="stat-card-premium">
                        <div class="stat-icon" style="background: #ecfdf5; color: #10b981;"><i class="fas fa-user-check"></i></div>
                        <div class="stat-info">
                            <div class="label">Active Staff</div>
                            <div class="value" id="stat-active">0</div>
                        </div>
                    </div>
                    <div class="stat-card-premium">
                        <div class="stat-icon" style="background: #fffbeb; color: #f59e0b;"><i class="fas fa-calendar-alt"></i></div>
                        <div class="stat-info">
                            <div class="label">On Leave</div>
                            <div class="value" id="stat-leave">0</div>
                        </div>
                    </div>
                </div>

                <div class="filters-section">
                    <div class="search-box">
                        <input type="text" placeholder="Search by name or ID..." id="employeeSearch">
                        <i class="fas fa-search"></i>
                    </div>
                    <select class="filter-select" id="departmentFilter">
                        <option value="">All Departments</option>
                        <option value="medical">Medical</option>
                        <option value="admin">Admin</option>
                        <option value="finance">Finance</option>
                        <option value="support">Support</option>
                    </select>
                    <select class="filter-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="on-leave">On Leave</option>
                    </select>
                    <select class="filter-select" id="kebeleFilter">
                        <option value="">All Kebeles</option>
                        <?php foreach($kebeles as $k): ?>
                        <option value="<?php echo htmlspecialchars($k); ?>">Kebele <?php echo htmlspecialchars($k); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="print-btn" onclick="clearFilters()" title="Clear All Filters" style="padding: 12px 15px;">
                        <i class="fas fa-filter-circle-xmark"></i>
                    </button>
                    <button class="add-btn" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Add Employee
                    </button>
                </div>

                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Employee Records</h2>
                        <div class="hr-section-actions">
                            <button class="print-btn" onclick="window.print()">
                                <i class="fas fa-print"></i> Print Report
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
                                        <th>Kebele</th>
                                        <th>Department</th>
                                        <th>Position</th>
                                        <th>Join Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="employeeTableBody">
                                    <tr><td colspan="8" style="text-align:center;">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="pagination-container" id="paginationContainer">
                            <!-- JS will inject pagination here -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Employee Side Panel -->
    <div class="side-panel-overlay" id="sidePanelOverlay"></div>
    <div class="side-panel" id="employeeSidePanel">
        <div class="side-panel-header">
            <h2 id="sidePanelTitle">Employee Details</h2>
            <i class="fas fa-times side-panel-close" onclick="closeSidePanel()"></i>
        </div>
        <div class="side-panel-tabs">
            <div class="side-tab active" onclick="switchTab('general')">General</div>
            <div class="side-tab" onclick="switchTab('education')">Education</div>
            <div class="side-tab" onclick="switchTab('finance')">Finance</div>
            <div class="side-tab" onclick="switchTab('legal')">Legal</div>
        </div>
        <div id="sidePanelContent">
            <!-- Content loaded via JS -->
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div class="modal" id="addEmployeeModal">
        <div class="modal-content large-modal">
            <div class="modal-header-banner">
                <h2 class="modal-title">Add New Employee</h2>
                <p>Register a new health professional or administrative staff member.</p>
                <i class="fas fa-times" style="position: absolute; right: 30px; top: 30px; cursor: pointer; font-size: 1.2rem;" onclick="closeAddModal()"></i>
            </div>
            <div class="modal-body">
                <form id="employeeForm" enctype="multipart/form-data">
                    <input type="hidden" name="isEditMode" id="isEditMode" value="0">
                    <input type="hidden" name="empDbId" id="empDbId">
                    <input type="hidden" name="empStaticId" id="empStaticId">

                    <div class="form-section-title"><i class="fas fa-id-card"></i> Personal Information</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="firstName" required>
                        </div>
                        <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" name="middleName">
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="lastName" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email">
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="dateOfBirth" required>
                        </div>
                        <div class="form-group">
                            <label>Marital Status</label>
                            <select name="maritalStatus">
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="divorced">Divorced</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-section-title"><i class="fas fa-briefcase"></i> Employment & Role</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Department</label>
                            <select name="department" id="department" onchange="syncDepartment()">
                                <option value="medical">Medical</option>
                                <option value="admin">Admin</option>
                                <option value="finance">Finance</option>
                                <option value="support">Support</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Job Position</label>
                            <input type="text" name="position" required>
                        </div>
                        <div class="form-group">
                            <label>Job Level</label>
                            <select name="jobLevel">
                                <option value="entry">Entry Level</option>
                                <option value="mid">Mid Level</option>
                                <option value="senior">Senior Level</option>
                                <option value="expert">Expert</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Join Date</label>
                            <input type="date" name="joinDate" required>
                        </div>
                        <div class="form-group">
                            <label>Monthly Salary (ETB)</label>
                            <input type="number" name="salary" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Employment Status</label>
                            <select name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="on-leave">On Leave</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-section-title"><i class="fas fa-map-marker-alt"></i> Address & Assignment</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Kebele Assignment</label>
                            <input type="text" name="working_kebele" placeholder="e.g. 01, 05">
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label>Residential Address</label>
                            <input type="text" name="address" placeholder="Sub-city, House Number, etc.">
                        </div>
                    </div>

                    <div class="form-section-title"><i class="fas fa-graduation-cap"></i> Academic Background</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Education Level</label>
                            <select name="education_level">
                                <option value="diploma">Diploma</option>
                                <option value="degree">Bachelor Degree</option>
                                <option value="masters">Masters</option>
                                <option value="phd">PhD</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>University / College</label>
                            <input type="text" name="university">
                        </div>
                        <div class="form-group">
                            <label>Major / Department</label>
                            <input type="text" name="major_dept">
                        </div>
                    </div>

                    <div class="form-section-title"><i class="fas fa-university"></i> Financial Information</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Bank Name</label>
                            <input type="text" name="bank_name">
                        </div>
                        <div class="form-group">
                            <label>Account Number</label>
                            <input type="text" name="bank_account">
                        </div>
                        <div class="form-group">
                            <label>Loan Status</label>
                            <select name="loan_status">
                                <option value="no">No Loan</option>
                                <option value="yes">Active Loan</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-section-title"><i class="fas fa-shield-halved"></i> Warranty & Legal</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Guarantor Name</label>
                            <input type="text" name="person_name">
                        </div>
                        <div class="form-group">
                            <label>Guarantor Phone</label>
                            <input type="text" name="guarantor_phone">
                        </div>
                        <div class="form-group">
                            <label>Criminal Record</label>
                            <select name="criminal_status">
                                <option value="no">Clean</option>
                                <option value="yes">Has Record</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="cancel-btn" onclick="closeAddModal()">Cancel</button>
                        <button type="submit" class="submit-btn" id="saveBtn">Save Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let allEmployees = [];
        let currentPage = 1;
        const itemsPerPage = 10;

        document.addEventListener('DOMContentLoaded', () => {
            loadEmployees(1);
            
            // Search & Filter with Debounce
            const searchInput = document.getElementById('employeeSearch');
            const deptFilter = document.getElementById('departmentFilter');
            const statusFilter = document.getElementById('statusFilter');
            const kebeleFilter = document.getElementById('kebeleFilter');

            let searchTimeout;
            function triggerSearch() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentPage = 1;
                    loadEmployees(1);
                }, 400);
            }

            searchInput.addEventListener('input', triggerSearch);
            deptFilter.addEventListener('change', () => { currentPage = 1; loadEmployees(1); });
            statusFilter.addEventListener('change', () => { currentPage = 1; loadEmployees(1); });
            kebeleFilter.addEventListener('change', () => { currentPage = 1; loadEmployees(1); });

            // Close side panel clicking outside
            document.getElementById('sidePanelOverlay').addEventListener('click', closeSidePanel);
        });

        function clearFilters() {
            document.getElementById('employeeSearch').value = '';
            document.getElementById('departmentFilter').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('kebeleFilter').value = '';
            currentPage = 1;
            loadEmployees(1);
        }

        function loadEmployees(page = 1) {
            currentPage = page;
            const tbody = document.getElementById('employeeTableBody');
            const searchTerm = document.getElementById('employeeSearch').value;
            const dept = document.getElementById('departmentFilter').value;
            const status = document.getElementById('statusFilter').value;
            const kebele = document.getElementById('kebeleFilter').value;

            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">Loading...</td></tr>';

            const url = `get_employees.php?page=${page}&limit=${itemsPerPage}&search=${encodeURIComponent(searchTerm)}&department=${encodeURIComponent(dept)}&status=${status}&kebele=${encodeURIComponent(kebele)}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        allEmployees = data.employees;
                        renderTable(allEmployees);
                        renderPagination(data.total, data.total_pages);
                        
                        // Update Stats
                        if(data.stats) {
                            if(document.getElementById('stat-total')) document.getElementById('stat-total').textContent = data.stats.total || 0;
                            if(document.getElementById('stat-active')) document.getElementById('stat-active').textContent = data.stats.active || 0;
                            if(document.getElementById('stat-leave')) document.getElementById('stat-leave').textContent = data.stats.leave_count || 0;
                        }
                    } else {
                        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; color:red;">${data.message || 'Failed to load data'}</td></tr>`;
                    }
                })
                .catch(err => {
                    console.error(err);
                    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; color:red;">Network Error</td></tr>';
                });
        }

        function renderTable(data) {
            const tbody = document.getElementById('employeeTableBody');
            if(data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">No employees found</td></tr>';
                return;
            }

            tbody.innerHTML = data.map(emp => {
                const initials = (emp.first_name?.charAt(0) || '') + (emp.last_name?.charAt(0) || '');
                return `
                <tr class="clickable-row" onclick="viewEmployeeDetails('${emp.employee_id}')">
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="user-avatar" style="width:32px;height:32px;background:var(--secondary);color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;">
                                ${initials}
                            </div>
                            <div>
                                <div style="font-weight:600; color:var(--primary);">${emp.first_name} ${emp.last_name}</div>
                                <div style="font-size:0.8em;color:#777;">${emp.email || 'No Email'}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-family: monospace; font-weight: 600;">${emp.employee_id}</td>
                    <td><span style="font-weight: 600; color: #1e293b;">${emp.working_kebele || emp.kebele || 'N/A'}</span></td>
                    <td><span class="department-badge ${emp.department_assigned?.toLowerCase() || 'medical'}">${emp.department_assigned || 'Not Assigned'}</span></td>
                    <td>${emp.position || 'Employee'}</td>
                    <td>${new Date(emp.join_date).toLocaleDateString('en-GB')}</td>
                    <td>
                        <span class="status-badge ${emp.status || 'active'}">
                            ${(emp.status || 'Active').toUpperCase()}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view" onclick="event.stopPropagation(); viewEmployeeDetails('${emp.employee_id}')" title="View Details"><i class="fas fa-eye"></i></button>
                            <button class="action-btn edit" onclick="event.stopPropagation(); openEditModal('${emp.employee_id}')" title="Edit Profile"><i class="fas fa-edit"></i></button>
                            <button class="action-btn delete" onclick="event.stopPropagation(); deleteEmployee('${emp.employee_id}')" title="Delete Record"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `}).join('');
        }

        function renderPagination(totalRows, totalPages) {
            const container = document.getElementById('paginationContainer');
            if (totalPages <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = `
                <div class="pagination-info">Showing ${(currentPage-1)*itemsPerPage + 1} to ${Math.min(currentPage*itemsPerPage, totalRows)} of ${totalRows} entries</div>
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

        window.deleteEmployee = function(id) {
            if(!id) return;
            if(!confirm("🛑 PERMANENT ACTION REQUIRED\n\nAre you sure you want to PERMANENTLY DELETE employee " + id + "?\nThis action cannot be undone.")) return;

            const formData = new FormData();
            formData.append('employee_id', id);

            fetch('../kebele_hr/employee_actions.php?action=delete', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('Employee removed successfully.');
                    loadEmployees(currentPage);
                    closeSidePanel();
                } else {
                    alert('Delete failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => alert('Network error during deletion.'));
        };

        function viewEmployeeDetails(employeeId) {
            const emp = allEmployees.find(e => e.employee_id === employeeId);
            if (!emp) return;

            const content = document.getElementById('sidePanelContent');
            const initials = (emp.first_name?.charAt(0) || '') + (emp.last_name?.charAt(0) || '');

            content.innerHTML = `
                <div class="profile-header-premium">
                    <div style="position: absolute; left: -20px; bottom: -20px; font-size: 8rem; opacity: 0.05; transform: rotate(-15deg);"><i class="fas fa-id-card"></i></div>
                    <div class="user-avatar" style="width: 110px; height: 110px; font-size: 2.8rem; margin: 0 auto 15px; background: white; color: var(--primary); box-shadow: 0 12px 25px rgba(0,0,0,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; border: 4px solid rgba(255,255,255,0.3);">${initials}</div>
                    <h3 style="margin: 0; font-size: 1.6rem; font-weight: 700; letter-spacing: -0.5px;">${emp.first_name} ${emp.middle_name || ''} ${emp.last_name}</h3>
                    <p style="opacity: 0.9; margin-top: 5px; font-size: 1rem; font-weight: 500;">${emp.position || 'Employee'}</p>
                    <div style="margin-top: 15px; display: inline-flex; align-items: center; gap: 8px; padding: 6px 16px; background: rgba(255,255,255,0.15); border-radius: 30px; font-size: 0.8rem; font-weight: 700; backdrop-filter: blur(4px);">
                        <i class="fas fa-fingerprint"></i> ${emp.employee_id}
                    </div>
                </div>

                <!-- Tab: General -->
                <div id="tab-general" class="tab-content active">
                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-user-circle"></i> Personal Data</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                            <div><div class="info-label"><i class="fas fa-venus-mars"></i> Gender</div><div class="info-value" style="text-transform: capitalize;">${emp.gender || 'N/A'}</div></div>
                            <div><div class="info-label"><i class="fas fa-birthday-cake"></i> Date of Birth</div><div class="info-value">${emp.date_of_birth || emp.dob || 'N/A'}</div></div>
                            <div><div class="info-label"><i class="fas fa-heart"></i> Marital Status</div><div class="info-value" style="text-transform: capitalize;">${emp.marital_status || 'N/A'}</div></div>
                            <div><div class="info-label"><i class="fas fa-language"></i> Language</div><div class="info-value">${emp.language || 'N/A'}</div></div>
                            <div><div class="info-label"><i class="fas fa-pray"></i> Religion</div><div class="info-value">${emp.religion || 'N/A'}</div></div>
                            <div><div class="info-label"><i class="fas fa-flag"></i> Citizenship</div><div class="info-value">${emp.citizenship || 'Ethiopian'}</div></div>
                            <div style="grid-column: span 2;"><div class="info-label"><i class="fas fa-map-marker-alt"></i> Residential Address</div><div class="info-value">${emp.address || 'N/A'}</div></div>
                            <div style="grid-column: span 2;"><div class="info-label"><i class="fas fa-hospital-user"></i> Kebele Assignment</div><div class="info-value" style="color: var(--primary); font-weight: 700;">Kebele ${emp.working_kebele || emp.kebele || 'N/A'}</div></div>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-briefcase"></i> Employment & Role</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                            <div><div class="info-label"><i class="fas fa-user-tag"></i> Designation</div><div class="info-value">${emp.position || 'N/A'}</div></div>
                            <div><div class="info-label"><i class="fas fa-sitemap"></i> Department</div><div class="info-value">${emp.department_assigned || 'N/A'}</div></div>
                            <div><div class="info-label"><i class="fas fa-calendar-check"></i> Join Date</div><div class="info-value">${emp.join_date ? new Date(emp.join_date).toLocaleDateString('en-GB') : 'N/A'}</div></div>
                            <div><div class="info-label"><i class="fas fa-money-bill-wave"></i> Gross Salary</div><div class="info-value" style="color: var(--success); font-weight: 700;">${emp.salary ? 'ETB ' + parseFloat(emp.salary).toLocaleString() : 'N/A'}</div></div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Education -->
                <div id="tab-education" class="tab-content">
                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-graduation-cap"></i> Academic Background</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                            <div><div class="info-label">Education Level</div><div class="info-value" style="text-transform: capitalize;">${emp.education_level || 'N/A'}</div></div>
                            <div><div class="info-label">University / College</div><div class="info-value">${emp.university || 'N/A'}</div></div>
                            <div><div class="info-label">Department</div><div class="info-value">${emp.department || 'N/A'}</div></div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Finance -->
                <div id="tab-finance" class="tab-content">
                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-university"></i> Bank Details</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                            <div><div class="info-label"><i class="fas fa-building-columns"></i> Bank Name</div><div class="info-value">${emp.bank_name || 'N/A'}</div></div>
                            <div><div class="info-label"><i class="fas fa-hashtag"></i> Account Number</div><div class="info-value" style="font-family: monospace; letter-spacing: 1px;">${emp.bank_account || 'N/A'}</div></div>
                            <div style="grid-column: span 2;"><div class="info-label"><i class="fas fa-hand-holding-dollar"></i> Loan Status</div>
                                <div class="info-value" style="margin-top: 5px;">
                                    <span class="status-badge ${emp.loan_status === 'yes' ? 'inactive' : 'active'}" style="font-size: 0.7rem;">
                                        ${emp.loan_status === 'yes' ? 'Outstanding Loan' : 'No Current Loan'}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Legal -->
                <div id="tab-legal" class="tab-content">
                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-shield-halved"></i> Warranty & Legal Status</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                            <div><div class="info-label">Guarantor Name</div><div class="info-value">${emp.person_name || 'N/A'}</div></div>
                            <div><div class="info-label">Guarantor Phone</div><div class="info-value">${emp.phone || 'N/A'}</div></div>
                            <div><div class="info-label">Criminal Record</div><div class="info-value">${emp.criminal_status === 'yes' ? 'Has Record' : 'Clean'}</div></div>
                        </div>
                    </div>
                </div>

                <div style="padding: 10px 0 40px; display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; gap: 10px;">
                        <button class="submit-btn" style="flex: 2; height: 50px; font-size: 1rem; cursor: pointer;" onclick="openEditModal('${emp.employee_id}')"><i class="fas fa-user-edit"></i> Edit Full Profile</button>
                        <button class="cancel-btn" style="flex: 1; height: 50px; font-size: 1rem; cursor: pointer;" onclick="closeSidePanel()">Close</button>
                    </div>
                    <button class="action-btn delete" style="width: 100%; height: 50px; border-radius: 12px; font-size: 1rem; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 10px; border: none; background: #fef2f2; color: #991b1b; cursor: pointer; transition: all 0.2s;" onclick="deleteEmployee('${emp.employee_id}')">
                        <i class="fas fa-trash-alt"></i> Delete Employee Record
                    </button>
                </div>
            `;

            document.getElementById('employeeSidePanel').classList.add('open');
            document.getElementById('sidePanelOverlay').classList.add('active');
            switchTab('general'); // Always start with general tab
        }

        function switchTab(tabId) {
            // Update tabs
            document.querySelectorAll('.side-tab').forEach(tab => {
                tab.classList.remove('active');
                if (tab.textContent.toLowerCase() === tabId.toLowerCase()) {
                    tab.classList.add('active');
                }
            });

            // Update content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById('tab-' + tabId).classList.add('active');
        }

        function closeSidePanel() {
            document.getElementById('employeeSidePanel').classList.remove('open');
            document.getElementById('sidePanelOverlay').classList.remove('active');
        }

        function openEditModal(employeeId) {
            const emp = allEmployees.find(e => e.employee_id === employeeId);
            if (!emp) return;

            document.getElementById('isEditMode').value = "1";
            document.getElementById('empDbId').value = emp.id;
            document.getElementById('empStaticId').value = emp.employee_id;
            document.querySelector('.modal-title').textContent = 'Edit Employee Profile';
            document.getElementById('saveBtn').textContent = 'Update Employee';

            const form = document.getElementById('employeeForm');
            if(form.elements['firstName']) form.elements['firstName'].value = emp.first_name || '';
            if(form.elements['middleName']) form.elements['middleName'].value = emp.middle_name || '';
            if(form.elements['lastName']) form.elements['lastName'].value = emp.last_name || '';
            if(form.elements['dateOfBirth']) form.elements['dateOfBirth'].value = emp.date_of_birth || '';
            if(form.elements['email']) form.elements['email'].value = emp.email || '';
            if(form.elements['phone']) form.elements['phone'].value = emp.phone_number || emp.phone || '';
            if(form.elements['address']) form.elements['address'].value = emp.address || '';
            if(form.elements['position']) form.elements['position'].value = emp.position || '';
            if(form.elements['salary']) form.elements['salary'].value = emp.salary || '';
            if(form.elements['joinDate']) form.elements['joinDate'].value = emp.join_date || '';
            if(form.elements['gender']) form.elements['gender'].value = emp.gender || '';
            if(form.elements['maritalStatus']) form.elements['maritalStatus'].value = emp.marital_status || '';
            if(form.elements['status']) form.elements['status'].value = emp.status || 'active';
            if(form.elements['working_kebele']) form.elements['working_kebele'].value = emp.working_kebele || emp.kebele || '';
            if(form.elements['department']) form.elements['department'].value = (emp.department_assigned || '').toLowerCase();
            
            // New Fields
            if(form.elements['education_level']) form.elements['education_level'].value = emp.education_level || 'degree';
            if(form.elements['university']) form.elements['university'].value = emp.university || '';
            if(form.elements['major_dept']) form.elements['major_dept'].value = emp.department || '';
            if(form.elements['bank_name']) form.elements['bank_name'].value = emp.bank_name || '';
            if(form.elements['bank_account']) form.elements['bank_account'].value = emp.bank_account || '';
            if(form.elements['loan_status']) form.elements['loan_status'].value = emp.loan_status || 'no';
            if(form.elements['person_name']) form.elements['person_name'].value = emp.person_name || '';
            if(form.elements['guarantor_phone']) form.elements['guarantor_phone'].value = emp.phone || '';
            if(form.elements['criminal_status']) form.elements['criminal_status'].value = emp.criminal_status || 'no';

            document.getElementById('addEmployeeModal').style.display = 'block';
            closeSidePanel();
        }

        function openAddModal() {
            document.getElementById('employeeForm').reset();
            document.getElementById('isEditMode').value = "0";
            document.getElementById('empDbId').value = "";
            document.querySelector('.modal-title').textContent = 'Add New Employee';
            document.getElementById('saveBtn').textContent = 'Save Employee';
            document.getElementById('addEmployeeModal').style.display = 'block';
        }

        function closeAddModal() {
            document.getElementById('addEmployeeModal').style.display = 'none';
        }

        function syncDepartment() {
            // Optional syncing logic
        }

        // Form Submit
        document.getElementById('employeeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const isEdit = document.getElementById('isEditMode').value === "1";
            const endpoint = isEdit ? 'edit_employee.php' : 'add_employee.php';
            
            const btn = document.getElementById('saveBtn');
            const originalText = btn.textContent;
            btn.textContent = isEdit ? 'Updating...' : 'Saving...';
            btn.disabled = true;

            const formData = new FormData(this);

            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert(isEdit ? 'Employee updated successfully!' : 'Employee added successfully!');
                    closeAddModal();
                    loadEmployees(currentPage);
                } else {
                    alert('Error: ' + (data.message || 'Action failed'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Submission failed. Please check the server logs.');
            })
            .finally(() => {
                btn.textContent = originalText;
                btn.disabled = false;
            });
        });

        window.onclick = function(event) {
            if (event.target == document.getElementById('addEmployeeModal')) {
                closeAddModal();
            }
        }

        // Sidebar Toggle Logic
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleSidebar');
        const mobileBtn = document.getElementById('mobileMenuBtn');
        const mobileOverlay = document.getElementById('mobileOverlay');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
            });
        }

        if (mobileBtn) { mobileBtn.addEventListener('click', () => { sidebar.classList.add('mobile-open'); mobileOverlay.classList.add('active'); }); }
        if (mobileOverlay) { mobileOverlay.addEventListener('click', () => { sidebar.classList.remove('mobile-open'); mobileOverlay.classList.remove('active'); }); }
    </script>
</body>
</html>
