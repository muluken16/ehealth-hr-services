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
                    <button class="add-btn" onclick="window.openAddEmployeeModal()"
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

    <!-- Add Employee Modal (Refined Premium Version) -->
    <div class="modal" id="addEmployeeModal" style="backdrop-filter: blur(10px); background: rgba(15, 23, 42, 0.4);">
        <div class="modal-content" style="max-width: 850px; border-radius: 24px; border: 1px solid rgba(255,255,255,0.2); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); overflow: hidden; display: flex; flex-direction: column; background: white;">
            
            <!-- Sleek Progress Top Bar -->
            <div style="height: 5px; background: #f1f5f9; width: 100%; position: relative; z-index: 10;">
                <div id="progressBar" style="height: 100%; width: 25%; background: linear-gradient(90deg, #3b82f6, #0ea5e9); transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);"></div>
            </div>

            <!-- Modal Header (Minimalist) -->
            <div style="padding: 24px 32px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; background: #fff;">
                <div>
                    <h2 style="margin:0; font-size:1.4rem; color:#1e293b; font-weight:700;">
                        <i class="fas fa-user-plus" style="margin-right:10px; color:var(--primary);"></i>Register Employee
                    </h2>
                    <div id="stepLabel" style="font-size: 0.85rem; color: #64748b; margin-top: 4px; font-weight: 500;">Step 1: Personal Information</div>
                </div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <!-- Shortcut Register Button -->
                    <button type="button" id="topRegisterBtn" onclick="submitEmployee()" style="padding: 8px 16px; background: #f1f5f9; color: #94a3b8; border: none; border-radius: 8px; font-weight: 600; cursor: not-allowed; font-size: 0.85rem; transition: all 0.3s;" disabled>
                        <i class="fas fa-check"></i> Register Now
                    </button>
                    <span class="close" onclick="closeAddEmployeeModal()" style="font-size: 1.8rem; color: #94a3b8; cursor: pointer; transition: color 0.2s;">&times;</span>
                </div>
            </div>

            <!-- Modal Body (Clean Layout) -->
            <div style="flex: 1; overflow-y: auto; padding: 24px 40px; scrollbar-width: thin; max-height: 70vh;">
                <form id="addEmployeeForm">
                    <input type="hidden" name="working_kebele" value="<?php echo $_SESSION['kebele'] ?? 'Kebele 1'; ?>">
                    <!-- Step 1: Personal Information -->
                    <div class="form-step" data-step="1">
                        <div class="form-step-card" style="border-top: 4px solid #3b82f6;">
                            <div class="step-title-premium">
                                <i class="fas fa-id-card"></i>
                                <span>Personal Information</span>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                                <div class="form-group">
                                    <label>First Name <span style="color: #ef4444;">*</span></label>
                                    <input type="text" name="first_name" required class="form-control-premium" placeholder="e.g. Abebe">
                                </div>
                                <div class="form-group">
                                    <label>Last Name <span style="color: #ef4444;">*</span></label>
                                    <input type="text" name="last_name" required class="form-control-premium" placeholder="e.g. Bekele">
                                </div>
                                <div class="form-group">
                                    <label>Date of Birth <span style="color: #ef4444;">*</span></label>
                                    <input type="date" name="date_of_birth" required class="form-control-premium">
                                </div>
                                <div class="form-group">
                                    <label>Gender <span style="color: #ef4444;">*</span></label>
                                    <select name="gender" required class="form-control-premium">
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Middle Name</label>
                                    <input type="text" name="middle_name" class="form-control-premium" placeholder="Enter middle name">
                                </div>
                                <div class="form-group">
                                    <label>Marital Status</label>
                                    <select name="marital_status" class="form-control-premium">
                                        <option value="single">Single</option>
                                        <option value="married">Married</option>
                                        <option value="divorced">Divorced</option>
                                        <option value="widowed">Widowed</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Religion</label>
                                    <input type="text" name="religion" class="form-control-premium" placeholder="e.g. Christian/Muslim">
                                </div>
                                <div class="form-group">
                                    <label>Language</label>
                                    <input type="text" name="language" class="form-control-premium" placeholder="e.g. Amharic/Oromifa">
                                </div>
                                <div class="form-group">
                                    <label>Citizenship</label>
                                    <input type="text" name="citizenship" class="form-control-premium" value="Ethiopian">
                                </div>

                    <!-- Step 2: Employment Information -->
                    <div class="form-step" data-step="2" style="display: none;">
                        <div class="form-step-card" style="border-top: 4px solid #f59e0b;">
                            <div class="step-title-premium">
                                <i class="fas fa-briefcase"></i>
                                <span>Employment Details</span>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                                <div class="form-group">
                                    <label>Position / Title <span style="color: #ef4444;">*</span></label>
                                    <input type="text" name="position" required class="form-control-premium" placeholder="e.g. Senior Nurse">
                                </div>
                                <div class="form-group">
                                    <label>Department Assigned</label>
                                    <select name="department_assigned" class="form-control-premium">
                                        <option value="">Select Department</option>
                                        <option value="medical">Medical</option>
                                        <option value="administration">Administration</option>
                                        <option value="pharmacy">Pharmacy</option>
                                        <option value="laboratory">Laboratory</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Contract Type</label>
                                    <select name="employment_type" class="form-control-premium">
                                        <option value="full-time">Full-Time</option>
                                        <option value="part-time">Part-Time</option>
                                        <option value="contract">Contract</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Monthly Salary (ETB)</label>
                                    <input type="number" name="salary" class="form-control-premium" placeholder="e.g. 12500">
                                </div>
                            </div>
                        </div>
                    </div>

                                <div class="form-group">
                                    <label>Zone <span style="color: #ef4444;">*</span></label>
                                    <input type="text" name="zone" required class="form-control-premium" placeholder="Enter Zone">
                                </div>
                                <div class="form-group">
                                    <label>Email Address <span style="color: #ef4444;">*</span></label>
                                    <input type="email" name="email" required class="form-control-premium" placeholder="abebe@example.com">
                                </div>
                                <div class="form-group">
                                    <label>Personal Phone <span style="color: #ef4444;">*</span></label>
                                    <input type="tel" name="phone_number" required class="form-control-premium" placeholder="+251 9XX XXX XXX">
                                </div>
                                <div class="form-group">
                                    <label>Emergency Contact</label>
                                    <input type="text" name="emergency_contact" class="form-control-premium" placeholder="+251 9XX XXX XXX">
                                </div>
                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label>Residence Address</label>
                                    <textarea name="address" rows="2" class="form-control-premium" style="font-family: inherit;" placeholder="Detailed house address"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Education & Academic -->
                    <div class="form-step" data-step="3" style="display: none;">
                        <div class="form-step-card" style="border-top: 4px solid #8b5cf6;">
                            <div class="step-title-premium">
                                <i class="fas fa-graduation-cap"></i>
                                <span>Academic Background</span>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                                <div class="form-group">
                                    <label>Education Level</label>
                                    <select name="education_level" class="form-control-premium">
                                        <option value="diploma">Diploma</option>
                                        <option value="degree">Bachelor Degree</option>
                                        <option value="masters">Masters</option>
                                        <option value="phd">PhD</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Field of Study / Department</label>
                                    <input type="text" name="department" class="form-control-premium" placeholder="e.g. Nursing">
                                </div>
                                <div class="form-group">
                                    <label>University / College</label>
                                    <input type="text" name="university" class="form-control-premium" placeholder="Enter institution name">
                                </div>
                                <div class="form-group">
                                    <label>Secondary School</label>
                                    <input type="text" name="secondary_school" class="form-control-premium" placeholder="Enter school name">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Employment & Financial -->
                    <div class="form-step" data-step="4" style="display: none;">
                        <div class="form-step-card" style="border-top: 4px solid #f59e0b;">
                            <div class="step-title-premium">
                                <i class="fas fa-briefcase"></i>
                                <span>Employment & Financials</span>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                                <div class="form-group">
                                    <label>Job Title <span style="color: #ef4444;">*</span></label>
                                    <input type="text" name="position" required class="form-control-premium" placeholder="e.g. Senior Nurse">
                                </div>
                                <div class="form-group">
                                    <label>Dept (Workplace)</label>
                                    <select name="department_assigned" class="form-control-premium">
                                        <option value="medical">Medical</option>
                                        <option value="admin">Administration</option>
                                        <option value="it">IT/Technical</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Salary (ETB)</label>
                                    <input type="number" name="salary" class="form-control-premium" placeholder="e.g. 15000">
                                </div>
                                <div class="form-group">
                                    <label>Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control-premium" placeholder="e.g. CBE / Abyssinia">
                                </div>
                                <div class="form-group">
                                    <label>Bank Account Number</label>
                                    <input type="text" name="bank_account" class="form-control-premium" placeholder="Enter account no">
                                </div>
                                <div class="form-group">
                                    <label>Join Date</label>
                                    <input type="date" name="join_date" class="form-control-premium" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Warranty & Legal -->
                    <div class="form-step" data-step="5" style="display: none;">
                        <div class="form-step-card" style="border-top: 4px solid #ef4444;">
                            <div class="step-title-premium">
                                <i class="fas fa-shield-halved"></i>
                                <span>Warranty & Legal Status</span>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                                <div class="form-group">
                                    <label>Guarantor Name</label>
                                    <input type="text" name="person_name" class="form-control-premium" placeholder="Enter guarantor name">
                                </div>
                                <div class="form-group">
                                    <label>Guarantor Phone</label>
                                    <input type="tel" name="phone" class="form-control-premium" placeholder="Enter phone">
                                </div>
                                <div class="form-group">
                                    <label>Criminal Record Status</label>
                                    <select name="criminal_status" class="form-control-premium">
                                        <option value="no">No Record</option>
                                        <option value="yes">Has Record</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Loan Status</label>
                                    <select name="loan_status" class="form-control-premium">
                                        <option value="no">No Loan</option>
                                        <option value="yes">Active Loan</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>FIN / National ID No</label>
                                    <input type="text" name="fin_id" class="form-control-premium" placeholder="Enter FIN ID">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 6: Documents Upload -->
                    <div class="form-step" data-step="6" style="display: none;">

                    <!-- Step 4: Documents Upload -->
                    <div class="form-step" data-step="4" style="display: none;">
                        <div style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); padding: 20px; border-radius: 12px; margin-bottom: 25px; border-left: 4px solid #10b981;">
                            <h3 style="margin: 0 0 5px 0; color: #065f46; font-size: 1.2rem;">
                                <i class="fas fa-cloud-upload-alt" style="margin-right: 8px;"></i>Document Uploads
                            </h3>
                            <p style="margin: 0; font-size: 0.85rem; color: #047857;">Upload employee documents (Optional - drag & drop or click to browse)</p>
                        </div>

                        <!-- File Upload Area with Drag & Drop -->
                        <div id="fileDropZone" style="border: 3px dashed #cbd5e1; border-radius: 12px; padding: 40px 20px; text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.3s; margin-bottom: 20px;">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #94a3b8; margin-bottom: 15px;"></i>
                            <h4 style="margin: 0 0 8px 0; color: #475569; font-size: 1.1rem;">Drop files here or click to browse</h4>
                            <p style="margin: 0; color: #94a3b8; font-size: 0.85rem;">Accepted: PDF, JPG, PNG (Max 5MB each)</p>
                            <input type="file" id="multiFileInput" multiple accept=".pdf,.jpg,.jpeg,.png" style="display: none;">
                        </div>

                        <!-- Selected Files Display -->
                        <div id="selectedFilesContainer" style="display: none; background: white; border-radius: 12px; padding: 20px; border: 2px solid #e2e8f0;">
                            <h4 style="margin: 0 0 15px 0; color: #334155; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-paperclip"></i> Selected Files (<span id="fileCount">0</span>)
                            </h4>
                            <div id="filesList" style="display: grid; gap: 10px;">
                                <!-- Files will be listed here -->
                            </div>
                        </div>

                        <!-- Document Type Labels (Hidden inputs for file mapping) -->
                        <input type="hidden" name="uploaded_files" id="uploadedFilesData">
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div style="background: #f8fafc; padding: 20px 40px; border-top: 2px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <button type="button" id="prevStepBtn" onclick="previousStep()" style="padding: 12px 28px; background: #e2e8f0; color: #64748b; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: none; transition: all 0.3s;">
                    <i class="fas fa-arrow-left"></i> Previous
                </button>
                <div id="loadingIndicator" style="display: none; color: var(--primary); font-weight: 600;">
                    <i class="fas fa-circle-notch fa-spin"></i> Registering employee...
                </div>
                <div style="flex: 1;"></div>
                <button type="button" onclick="window.closeAddEmployeeModal()" style="padding: 12px 28px; background: #e2e8f0; color: #64748b; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-right: 10px; transition: all 0.3s;">
                    Cancel
                </button>
                <button type="button" id="nextStepBtn" onclick="nextStep()" style="padding: 12px 28px; background: linear-gradient(135deg, var(--primary) 0%, #0f4c75 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(26, 74, 95, 0.25);">
                    Next <i class="fas fa-arrow-right"></i>
                </button>
                <button type="button" id="submitBtn" onclick="submitEmployee()" style="display: none; padding: 12px 28px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);">
                    <i class="fas fa-check"></i> Register Employee
                </button>
            </div>
        </div>
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
        let currentStep = 1;

        // --- GLOBAL ACTION FUNCTIONS ---
        
        window.viewEmployeeDetails = function(employeeId) {
            console.log('Action: viewEmployeeDetails triggered for ID:', employeeId);
            if (!employeeId) {
                alert("Invalid Employee ID");
                return;
            }

            // CROSS-CLOSE: Ensure Add Modal is closed before showing details
            window.closeAddEmployeeModal();

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

            content.innerHTML = `
                <div style="text-align:center; margin-bottom:25px; background: var(--primary); color: white; padding: 40px 20px; border-radius: 20px; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                    <div style="position: absolute; right: -20px; top: -20px; font-size: 15rem; opacity: 0.05;"><i class="fas fa-user-tie"></i></div>
                    <div class="user-avatar" style="width: 100px; height: 100px; font-size: 2.5rem; margin: 0 auto 20px; background: white; color: var(--primary); box-shadow: 0 10px 20px rgba(0,0,0,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800;">${initials}</div>
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

        window.openAddEmployeeModal = function() {
            console.log('Opening add modal');
            window.closeSidePanel();
            document.getElementById('addEmployeeModal').style.display = 'block';
            showStep(1);
        };

        window.closeAddEmployeeModal = function() {
            console.log('Closing add modal');
            document.getElementById('addEmployeeModal').style.display = 'none';
            document.getElementById('addEmployeeForm').reset();
            currentStep = 1;
        };

        function showStep(step) {
            currentStep = step;
            document.querySelectorAll('.form-step').forEach(el => el.style.display = 'none');
            const targetStep = document.querySelector(`.form-step[data-step="${step}"]`);
            if (targetStep) targetStep.style.display = 'block';

            // Update Label
            const labels = [
                "Personal Information", 
                "Residence & Contact", 
                "Academic Background", 
                "Employment & Financials", 
                "Warranty & Legal Status", 
                "Document Uploads"
            ];
            document.getElementById('stepLabel').textContent = `Step ${step}: ${labels[step-1]}`;

            // Update progress bar
            const progress = (step / 6) * 100;
            const progressBar = document.getElementById('progressBar');
            if (progressBar) progressBar.style.width = progress + '%';

            // Update Top Register Button State
            const topRegBtn = document.getElementById('topRegisterBtn');
            if (step === 6) {
                topRegBtn.style.background = 'var(--success)';
                topRegBtn.style.color = 'white';
                topRegBtn.style.cursor = 'pointer';
                topRegBtn.disabled = false;
            } else {
                topRegBtn.style.background = '#f1f5f9';
                topRegBtn.style.color = '#94a3b8';
                topRegBtn.style.cursor = 'not-allowed';
                topRegBtn.disabled = true;
            }

            // Toggle buttons
            const prevBtn = document.getElementById('prevStepBtn');
            const nextBtn = document.getElementById('nextStepBtn');
            const submitBtn = document.getElementById('submitBtn');

            if (prevBtn) prevBtn.style.display = step === 1 ? 'none' : 'block';
            if (nextBtn) nextBtn.style.display = step === 6 ? 'none' : 'block';
            if (submitBtn) submitBtn.style.display = step === 6 ? 'block' : 'none';
        }

        function nextStep() {
            if (currentStep < 6) showStep(currentStep + 1);
        }

        function previousStep() {
            if (currentStep > 1) showStep(currentStep - 1);
        }

        function submitEmployee() {
            const form = document.getElementById('addEmployeeForm');
            const formData = new FormData(form);
            
            // Show loading
            const submitBtn = document.getElementById('submitBtn');
            const loading = document.getElementById('loadingIndicator');
            
            if (submitBtn) submitBtn.style.display = 'none';
            if (loading) loading.style.display = 'block';

            fetch('employee_actions.php?action=add', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (loading) loading.style.display = 'none';
                if (data.success) {
                    alert('Employee registered successfully!');
                    closeAddEmployeeModal();
                    loadEmployees(1);
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                    if (submitBtn) submitBtn.style.display = 'block';
                }
            })
            .catch(err => {
                console.error(err);
                if (loading) loading.style.display = 'none';
                if (submitBtn) submitBtn.style.display = 'block';
                alert('Network error. Please try again.');
            });
        }

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
                quickBtn.onclick = window.openAddEmployeeModal;
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
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:#ef4444; padding: 30px;"><i class="fas fa-wifi"></i> Network error. Please check your connection.</td></tr>';
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

                const empId = emp.employee_id || emp.id;
                return `
                    <tr class="clickable-row" onclick="window.viewEmployeeDetails('${empId}')">
                        <td>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div class="employee-avatar-circle">${initials}</div>
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
    </script>
    <script src="scripts.js"></script>
</body>
</html>