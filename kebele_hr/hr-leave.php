<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Leave Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --danger-gradient: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        /* Modern Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 16px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-gradient);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-bottom: 12px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            color: #667eea;
        }

        .stat-card.success .stat-icon {
            background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.1) 100%);
            color: #11998e;
        }

        .stat-card.danger .stat-icon {
            background: linear-gradient(135deg, rgba(235, 51, 73, 0.1) 0%, rgba(244, 92, 67, 0.1) 100%);
            color: #eb3349;
        }

        .stat-card.warning .stat-icon {
            background: linear-gradient(135deg, rgba(250, 112, 154, 0.1) 0%, rgba(254, 225, 64, 0.1) 100%);
            color: #fa709a;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 800;
            color: #1a202c;
            margin-bottom: 2px;
        }

        .stat-label {
            font-size: 12px;
            font-weight: 600;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Filter Bar */
        .filter-bar {
            background: white;
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-group label {
            font-size: 14px;
            font-weight: 600;
            color: #4a5568;
        }

        .filter-group select {
            padding: 10px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #2d3748;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
            outline: none;
        }

        .filter-group select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* Modern Leave Cards */
        .leave-requests {
            display: grid;
            gap: 20px;
        }

        .leave-request-card {
            background: white;
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #f0f4f8;
        }

        .leave-request-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 5px;
            background: var(--primary-gradient);
        }

        .leave-request-card.annual::before {
            background: var(--primary-gradient);
        }

        .leave-request-card.sick::before {
            background: var(--danger-gradient);
        }

        .leave-request-card.maternity::before {
            background: var(--warning-gradient);
        }

        .leave-request-card.paternity::before {
            background: var(--info-gradient);
        }

        .leave-request-card.bereavement::before {
            background: var(--dark-gradient);
        }

        .leave-request-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
        }

        .leave-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .leave-employee {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .employee-avatar {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .employee-info h3 {
            font-size: 17px;
            font-weight: 700;
            color: #1a202c;
            margin: 0 0 4px 0;
        }

        .employee-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            color: #718096;
        }

        .employee-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .leave-type-badge {
            padding: 8px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .leave-type-badge.annual {
            background: var(--primary-gradient);
        }

        .leave-type-badge.sick {
            background: var(--danger-gradient);
        }

        .leave-type-badge.maternity {
            background: var(--warning-gradient);
        }

        .leave-type-badge.paternity {
            background: var(--info-gradient);
        }

        .leave-type-badge.bereavement {
            background: var(--dark-gradient);
        }

        .leave-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
        }

        .detail-item {
            text-align: center;
        }

        .detail-label {
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .detail-value {
            font-size: 16px;
            font-weight: 700;
            color: #2c3e50;
        }

        .detail-value.highlight {
            color: #667eea;
            font-size: 20px;
        }

        .leave-reason {
            background: #f8f9fa;
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 3px solid #667eea;
        }

        .leave-reason-label {
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .leave-reason-text {
            font-size: 14px;
            line-height: 1.6;
            color: #495057;
        }

        .leave-actions {
            display: flex;
            gap: 10px;
            padding-top: 20px;
            border-top: 2px solid #f0f4f8;
        }

        .leave-action-btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .leave-action-btn.approve {
            background: var(--success-gradient);
            color: white;
            box-shadow: 0 4px 12px rgba(17, 153, 142, 0.3);
        }

        .leave-action-btn.approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(17, 153, 142, 0.4);
        }

        .leave-action-btn.reject {
            background: var(--danger-gradient);
            color: white;
            box-shadow: 0 4px 12px rgba(235, 51, 73, 0.3);
        }

        .leave-action-btn.reject:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(235, 51, 73, 0.4);
        }

        .leave-action-btn.view {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            box-shadow: none;
        }

        .leave-action-btn.view:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        /* Success Modal */
        .success-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            justify-content: center;
            align-items: center;
            z-index: 10000;
            animation: fadeIn 0.3s ease;
        }

        .success-modal.show {
            display: flex;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .success-modal-content {
            background: white;
            border-radius: 24px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlide 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes modalSlide {
            from {
                transform: translateY(-50px) scale(0.9);
                opacity: 0;
            }

            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: var(--success-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 8px 24px rgba(17, 153, 142, 0.4);
            animation: scaleIn 0.5s cubic-bezier(0.4, 0, 0.2, 1) 0.2s backwards;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }

        .success-icon i {
            font-size: 40px;
            color: white;
        }

        .success-title {
            font-size: 26px;
            font-weight: 800;
            background: var(--success-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 24px;
        }

        .employee-info-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 16px;
            padding: 24px;
            text-align: left;
            margin-bottom: 24px;
        }

        .employee-info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .employee-info-row:last-child {
            border-bottom: none;
        }

        .employee-info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 14px;
        }

        .employee-info-value {
            font-weight: 700;
            color: #2c3e50;
            font-size: 14px;
        }

        .close-modal-btn {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 14px 36px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .close-modal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .empty-icon i {
            font-size: 50px;
            color: #667eea;
        }

        .empty-title {
            font-size: 24px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 12px;
        }

        .empty-text {
            font-size: 16px;
            color: #718096;
        }

        /* Loading State */
        .loading-state {
            text-align: center;
            padding: 60px 20px;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            border: 4px solid #f0f4f8;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
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
            $page_title = "Leave Management";
            include 'navbar.php';
            ?>

            <!-- Content -->
            <div class="content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-value" id="pendingCount">0</div>
                        <div class="stat-label">Pending Requests</div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-value" id="approvedCount">0</div>
                        <div class="stat-label">Approved Today</div>
                    </div>
                    <div class="stat-card danger">
                        <div class="stat-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-value" id="rejectedCount">0</div>
                        <div class="stat-label">Rejected Today</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-value" id="totalRequests">0</div>
                        <div class="stat-label">Total This Month</div>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="filter-bar">
                    <div class="filter-group">
                        <label><i class="fas fa-filter"></i> Leave Type:</label>
                        <select id="leaveTypeFilter" onchange="filterLeaveRequests()">
                            <option value="all">All Types</option>
                            <option value="annual">Annual Leave</option>
                            <option value="sick">Sick Leave</option>
                            <option value="maternity">Maternity Leave</option>
                            <option value="paternity">Paternity Leave</option>
                            <option value="bereavement">Bereavement Leave</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label><i class="fas fa-sort"></i> Sort By:</label>
                        <select id="sortFilter" onchange="filterLeaveRequests()">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="duration">Duration (High to Low)</option>
                        </select>
                    </div>
                </div>

                <!-- Leave Requests Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title"><i class="fas fa-clipboard-list"></i> Pending Leave Requests</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn" onclick="openHrRequestModal()"
                                style="background: var(--primary-gradient); color: white;">
                                <i class="fas fa-user-plus"></i> Request for Employee
                            </button>
                            <button class="section-action-btn" onclick="window.location.href='leave_history.php'">
                                <i class="fas fa-history"></i> View History
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="leave-requests" id="leaveRequestsContainer">
                            <div class="loading-state">
                                <div class="loading-spinner"></div>
                                <p style="color: #718096; font-weight: 600;">Loading leave requests...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Success Modal -->
    <div class="success-modal" id="successModal">
        <div class="success-modal-content">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="success-title">Leave Approved Successfully!</div>
            <div class="employee-info-card" id="employeeInfoCard">
                <!-- Employee details will be populated here -->
            </div>
            <button class="close-modal-btn" onclick="closeSuccessModal()">
                <i class="fas fa-check"></i> Done
            </button>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="success-modal" id="detailsModal">
        <div class="success-modal-content" style="max-width: 650px;">
            <div
                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 3px solid #f0f4f8; padding-bottom: 20px;">
                <h3 style="margin: 0; color: #1a202c; font-size: 22px; font-weight: 800;">
                    <i class="fas fa-file-alt" style="color: #667eea; margin-right: 12px;"></i>
                    Leave Request Details
                </h3>
                <button onclick="closeDetailsModal()"
                    style="background: none; border: none; font-size: 24px; color: #cbd5e0; cursor: pointer; transition: color 0.2s;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="detailsContent" style="text-align: left;">
                <!-- Data populated via JS -->
            </div>
            <div style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 12px;">
                <button class="close-modal-btn" style="background: #e2e8f0; color: #4a5568; box-shadow: none;"
                    onclick="closeDetailsModal()">
                    <i class="fas fa-times"></i> Close
                </button>
                <button id="modalApproveBtn" class="close-modal-btn">
                    <i class="fas fa-check"></i> Approve
                </button>
            </div>
        </div>
    </div>
    <div class="success-modal" id="hrRequestModal">
        <div class="success-modal-content" style="max-width: 550px; text-align: left;">
            <div
                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid #f0f4f8; padding-bottom: 15px;">
                <h3 style="margin: 0; color: #1a202c; font-size: 20px; font-weight: 800;">
                    <i class="fas fa-user-clock" style="color: #667eea; margin-right: 12px;"></i>
                    Request Leave for Employee
                </h3>
                <button onclick="closeHrRequestModal()"
                    style="background: none; border: none; font-size: 22px; color: #cbd5e0; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="hrLeaveRequestForm" onsubmit="submitHrLeaveRequest(event)">
                <div style="display: grid; gap: 20px;">
                    <div style="display: flex; flex-direction: column;">
                        <label style="margin-bottom: 8px; font-weight:600;">Select Employee:</label>
                        <select id="hrRequestEmployee" name="employee_id" required
                            onchange="handleEmployeeSelect(this.value)"
                            style="width: 100%; border: 2px solid #e2e8f0; padding: 12px; border-radius: 10px;">
                            <option value="">Loading employees...</option>
                        </select>
                    </div>

                    <!-- Leave Balance Display Area -->
                    <div id="leaveBalanceInfo"
                        style="display: none; background: #f0f7ff; border: 1px solid #cce3ff; border-radius: 12px; padding: 15px; margin-top: -10px;">
                        <div
                            style="font-weight: 700; color: #0056b3; font-size: 14px; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-info-circle"></i> Available Leave Balance
                        </div>
                        <div id="balanceGrid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                            <!-- Dynamically populated -->
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="display: flex; flex-direction: column;">
                            <label style="margin-bottom: 8px; font-weight:600;">Leave Type:</label>
                            <select name="leave_type" required
                                style="width: 100%; border: 2px solid #e2e8f0; padding: 12px; border-radius: 10px;">
                                <option value="annual">Annual Leave</option>
                                <option value="sick">Sick Leave</option>
                                <option value="maternity">Maternity Leave</option>
                                <option value="paternity">Paternity Leave</option>
                                <option value="bereavement">Bereavement Leave</option>
                            </select>
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <label style="margin-bottom: 8px; font-weight:600;">Total Days:</label>
                            <input type="number" name="days_requested" required min="1"
                                style="width: 100%; border: 2px solid #e2e8f0; padding: 12px; border-radius: 10px;"
                                placeholder="e.g. 5">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="display: flex; flex-direction: column;">
                            <label style="margin-bottom: 8px; font-weight:600;">Start Date:</label>
                            <input type="date" name="start_date" required
                                style="width: 100%; border: 2px solid #e2e8f0; padding: 12px; border-radius: 10px;">
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <label style="margin-bottom: 8px; font-weight:600;">End Date:</label>
                            <input type="date" name="end_date" required
                                style="width: 100%; border: 2px solid #e2e8f0; padding: 12px; border-radius: 10px;">
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column;">
                        <label style="margin-bottom: 8px; font-weight:600;">Reason:</label>
                        <textarea name="reason" required
                            style="width: 100%; border: 2px solid #e2e8f0; padding: 12px; border-radius: 10px; min-height: 100px;"
                            placeholder="Enter reason for leave..."></textarea>
                    </div>
                </div>

                <div style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" class="close-modal-btn"
                        style="background: #e2e8f0; color: #4a5568; box-shadow: none;" onclick="closeHrRequestModal()">
                        Cancel
                    </button>
                    <button type="submit" class="close-modal-btn" id="hrSubmitBtn">
                        <i class="fas fa-paper-plane"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let allLeaveRequests = [];

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        function ucfirst(str) {
            return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
        }

        function updateBadgeCount() {
            const cards = document.querySelectorAll('.leave-request-card');
            const badge = document.getElementById('leaveBadge');
            if (badge) {
                badge.textContent = cards.length;
            }
            document.getElementById('pendingCount').textContent = cards.length;
        }

        function filterLeaveRequests() {
            const typeFilter = document.getElementById('leaveTypeFilter').value;
            const sortFilter = document.getElementById('sortFilter').value;

            let filtered = [...allLeaveRequests];

            // Filter by type
            if (typeFilter !== 'all') {
                filtered = filtered.filter(req => req.leave_type.toLowerCase() === typeFilter);
            }

            // Sort
            if (sortFilter === 'newest') {
                filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            } else if (sortFilter === 'oldest') {
                filtered.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            } else if (sortFilter === 'duration') {
                filtered.sort((a, b) => (b.days_requested || 0) - (a.days_requested || 0));
            }

            renderLeaveRequests(filtered);
        }

        function renderLeaveRequests(requests) {
            const container = document.getElementById('leaveRequestsContainer');
            container.innerHTML = '';

            if (requests.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div class="empty-title">No Leave Requests Found</div>
                        <div class="empty-text">All pending requests have been processed or no requests match your filters.</div>
                    </div>
                `;
                updateBadgeCount();
                return;
            }

            requests.forEach(request => {
                const initials = (request.first_name ? request.first_name.charAt(0) : '') +
                    (request.last_name ? request.last_name.charAt(0) : '');
                const leaveType = (request.leave_type || 'annual').toLowerCase();

                const card = document.createElement('div');
                card.className = `leave-request-card ${leaveType}`;
                card.id = 'leave-card-' + request.id;

                card.innerHTML = `
                    <div class="leave-header">
                        <div class="leave-employee">
                            <div class="employee-avatar">${initials}</div>
                            <div class="employee-info">
                                <h3>${request.first_name || ''} ${request.last_name || ''}</h3>
                                <div class="employee-meta">
                                    <span><i class="fas fa-briefcase"></i> ${request.department || 'N/A'}</span>
                                    <span><i class="fas fa-id-badge"></i> ${request.employee_id || 'N/A'}</span>
                                </div>
                            </div>
                        </div>
                        <div class="leave-type-badge ${leaveType}">
                            ${ucfirst(leaveType)} Leave
                        </div>
                    </div>
                    
                    <div class="leave-details-grid">
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-calendar-day"></i> Start Date</div>
                            <div class="detail-value">${formatDate(request.start_date)}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-calendar-check"></i> End Date</div>
                            <div class="detail-value">${formatDate(request.end_date)}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-hourglass-half"></i> Duration</div>
                            <div class="detail-value highlight">${request.days_requested || 0} Days</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-clock"></i> Requested</div>
                            <div class="detail-value">${formatDate(request.created_at)}</div>
                        </div>
                    </div>

                    <div class="leave-reason">
                        <div class="leave-reason-label"><i class="fas fa-comment-alt"></i> Reason</div>
                        <div class="leave-reason-text">${request.reason || 'No reason provided.'}</div>
                    </div>

                    <div class="leave-actions">
                        <button class="leave-action-btn view" onclick="viewLeaveDetails(${request.id})">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                        <button class="leave-action-btn approve" onclick="approveLeave(${request.id}, '${(request.first_name || '') + ' ' + (request.last_name || '')}')">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="leave-action-btn reject" onclick="rejectLeave(${request.id}, '${(request.first_name || '') + ' ' + (request.last_name || '')}')">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </div>
                `;
                container.appendChild(card);
            });

            updateBadgeCount();
        }

        // Load leave requests on page load
        document.addEventListener('DOMContentLoaded', function () {
            fetch('get_kebele_hr_leave_requests.php')
                .then(response => response.json())
                .then(data => {
                    allLeaveRequests = data;
                    renderLeaveRequests(data);

                    // Update Total Monthly counter (as a placeholder for loaded requests)
                    document.getElementById('totalRequests').textContent = data.length;
                })
                .catch(error => {
                    console.error('Error loading leave requests:', error);
                    document.getElementById('leaveRequestsContainer').innerHTML = `
                        <div class="empty-state">
                            <div class="empty-icon" style="background: linear-gradient(135deg, rgba(235, 51, 73, 0.1) 0%, rgba(244, 92, 67, 0.1) 100%);">
                                <i class="fas fa-exclamation-triangle" style="color: #eb3349;"></i>
                            </div>
                            <div class="empty-title" style="color: #eb3349;">Error Loading Requests</div>
                            <div class="empty-text">Please refresh the page or contact support if the problem persists.</div>
                        </div>
                    `;
                });
        });

        function viewLeaveDetails(leaveId) {
            const request = allLeaveRequests.find(r => r.id == leaveId);
            if (!request) return;

            const content = document.getElementById('detailsContent');
            content.innerHTML = `
                <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 24px; border-radius: 16px; margin-bottom: 20px;">
                    <div style="display: grid; gap: 16px;">
                        <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 2px solid rgba(0,0,0,0.05);">
                            <span style="font-weight: 600; color: #6c757d;">Employee</span>
                            <span style="font-weight: 700; color: #2c3e50;">${request.first_name} ${request.last_name}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 2px solid rgba(0,0,0,0.05);">
                            <span style="font-weight: 600; color: #6c757d;">Employee ID</span>
                            <span style="font-weight: 700; color: #2c3e50;">${request.employee_id}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 2px solid rgba(0,0,0,0.05);">
                            <span style="font-weight: 600; color: #6c757d;">Department</span>
                            <span style="font-weight: 700; color: #2c3e50;">${request.department || 'N/A'}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 2px solid rgba(0,0,0,0.05);">
                            <span style="font-weight: 600; color: #6c757d;">Leave Type</span>
                            <span style="font-weight: 700; color: #667eea;">${ucfirst(request.leave_type)} Leave</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 2px solid rgba(0,0,0,0.05);">
                            <span style="font-weight: 600; color: #6c757d;">Duration</span>
                            <span style="font-weight: 700; color: #2c3e50;">${formatDate(request.start_date)} to ${formatDate(request.end_date)}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 2px solid rgba(0,0,0,0.05);">
                            <span style="font-weight: 600; color: #6c757d;">Total Days</span>
                            <span style="font-weight: 700; color: #667eea; font-size: 18px;">${request.days_requested} Days</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 12px 0;">
                            <span style="font-weight: 600; color: #6c757d;">Phone Number</span>
                            <span style="font-weight: 700; color: #2c3e50;">${request.phone_number || 'N/A'}</span>
                        </div>
                    </div>
                </div>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; border-left: 4px solid #667eea;">
                    <div style="font-weight: 600; color: #6c757d; margin-bottom: 10px; font-size: 12px; text-transform: uppercase;">
                        <i class="fas fa-comment-alt"></i> Reason for Leave
                    </div>
                    <div style="color: #2c3e50; line-height: 1.6;">${request.reason || 'No reason provided.'}</div>
                </div>
                ${request.leave_document ? `
                    <div style="margin-top: 20px; text-align: center;">
                        <a href="../uploads/leaves/${request.leave_document}" target="_blank" 
                           style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: var(--info-gradient); color: white; text-decoration: none; border-radius: 10px; font-weight: 600; box-shadow: 0 4px 12px rgba(79, 172, 254, 0.3);">
                            <i class="fas fa-paperclip"></i> View Supporting Document
                        </a>
                    </div>
                ` : ''}
            `;

            const approveBtn = document.getElementById('modalApproveBtn');
            approveBtn.onclick = () => {
                closeDetailsModal();
                approveLeave(request.id, request.first_name + ' ' + request.last_name);
            };

            document.getElementById('detailsModal').classList.add('show');
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.remove('show');
        }

        function approveLeave(leaveId, employeeName) {
            if (!confirm('✅ Approve leave request for ' + employeeName + '?')) {
                return;
            }

            const card = document.getElementById('leave-card-' + leaveId);
            if (!card) return;

            fetch('approve_leave.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ leave_id: leaveId })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        card.style.transition = 'all 0.4s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'translateX(100px)';

                        setTimeout(() => {
                            card.remove();
                            allLeaveRequests = allLeaveRequests.filter(r => r.id != leaveId);
                            updateBadgeCount();

                            const container = document.getElementById('leaveRequestsContainer');
                            if (container.querySelectorAll('.leave-request-card').length === 0) {
                                renderLeaveRequests([]);
                            }

                            if (data.employee) {
                                showSuccessModal(data.employee);
                            }

                            // Increment Approved Today counter
                            const approvedCount = document.getElementById('approvedCount');
                            approvedCount.textContent = parseInt(approvedCount.textContent) + 1;
                        }, 400);
                    } else {
                        alert('❌ Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Error: ' + error.message);
                });
        }

        function showSuccessModal(employee) {
            const modal = document.getElementById('successModal');
            const infoCard = document.getElementById('employeeInfoCard');

            const fullName = (employee.first_name || '') + ' ' + (employee.last_name || '');
            const leaveType = ucfirst(employee.leave_type || 'annual');

            infoCard.innerHTML = `
                <div class="employee-info-row">
                    <span class="employee-info-label">Employee Name</span>
                    <span class="employee-info-value">${fullName}</span>
                </div>
                <div class="employee-info-row">
                    <span class="employee-info-label">Employee ID</span>
                    <span class="employee-info-value">${employee.employee_id || 'N/A'}</span>
                </div>
                <div class="employee-info-row">
                    <span class="employee-info-label">Leave Type</span>
                    <span class="employee-info-value">${leaveType} Leave</span>
                </div>
                <div class="employee-info-row">
                    <span class="employee-info-label">Duration</span>
                    <span class="employee-info-value">${formatDate(employee.start_date)} to ${formatDate(employee.end_date)}</span>
                </div>
                <div class="employee-info-row">
                    <span class="employee-info-label">Total Days</span>
                    <span class="employee-info-value" style="color: #667eea; font-size: 16px;">${employee.days_requested} days</span>
                </div>
            `;

            modal.classList.add('show');
        }

        function closeSuccessModal() {
            document.getElementById('successModal').classList.remove('show');
        }

        function rejectLeave(leaveId, employeeName) {
            const reason = prompt('Enter reason for rejecting ' + employeeName + "'s leave:");

            if (reason === null) return;

            const card = document.getElementById('leave-card-' + leaveId);
            if (!card) return;

            fetch('reject_leave.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ leave_id: leaveId, reason: reason || 'Rejected by HR' })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        card.style.transition = 'all 0.4s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'translateX(-100px)';

                        setTimeout(() => {
                            card.remove();
                            allLeaveRequests = allLeaveRequests.filter(r => r.id != leaveId);
                            updateBadgeCount();

                            const container = document.getElementById('leaveRequestsContainer');
                            if (container.querySelectorAll('.leave-request-card').length === 0) {
                                renderLeaveRequests([]);
                            }

                            alert('✅ Leave request rejected!');

                            // Increment Rejected Today counter
                            const rejectedCount = document.getElementById('rejectedCount');
                            rejectedCount.textContent = parseInt(rejectedCount.textContent) + 1;
                        }, 400);
                    } else {
                        alert('❌ Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Error: ' + error.message);
                });
        }

        // Close modals on outside click
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('success-modal')) {
                closeSuccessModal();
                closeDetailsModal();
                closeHrRequestModal();
            }
        });

        // HR Request Logic
        function openHrRequestModal() {
            document.getElementById('hrRequestModal').classList.add('show');
            loadEmployeesForSelect();
        }

        function closeHrRequestModal() {
            document.getElementById('hrRequestModal').classList.remove('show');
            document.getElementById('hrLeaveRequestForm').reset();
            document.getElementById('leaveBalanceInfo').style.display = 'none';
        }

        function loadEmployeesForSelect() {
            const select = document.getElementById('hrRequestEmployee');
            fetch('get_kebele_hr_employees.php?limit=100')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        select.innerHTML = '<option value="">-- Choose Employee --</option>';
                        data.employees.forEach(emp => {
                            select.innerHTML += `<option value="${emp.employee_id}">${emp.first_name} ${emp.last_name} (${emp.employee_id})</option>`;
                        });
                    }
                });
        }

        function handleEmployeeSelect(employeeId) {
            const infoArea = document.getElementById('leaveBalanceInfo');
            const balanceGrid = document.getElementById('balanceGrid');

            if (!employeeId) {
                infoArea.style.display = 'none';
                return;
            }

            // Show loading state in grid
            infoArea.style.display = 'block';
            balanceGrid.innerHTML = '<div style="grid-column: span 3; color: #667eea; text-align: center; font-size: 12px;"><i class="fas fa-spinner fa-spin"></i> Checking balance...</div>';

            fetch(`get_employee_leave_balance.php?employee_id=${employeeId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        balanceGrid.innerHTML = `<div style="grid-column: span 3; color: #e74c3c; text-align: center; font-size: 12px;">${data.error}</div>`;
                        return;
                    }

                    const balances = data.leave_balance;
                    let html = '';

                    const types = [
                        { key: 'annual', label: 'Annual', icon: 'fa-calendar-alt' },
                        { key: 'sick', label: 'Sick', icon: 'fa-briefcase-medical' },
                        { key: 'emergency', label: 'Emergency', icon: 'fa-exclamation-triangle' }
                    ];

                    types.forEach(type => {
                        const bal = balances[type.key];
                        const isAnnual = type.key === 'annual';
                        const ineligible = isAnnual && !bal.is_eligible;

                        html += `
                            <div style="background: ${ineligible ? '#fff5f5' : 'white'}; padding: 8px; border-radius: 8px; text-align: center; border: 1px solid ${ineligible ? '#feb2b2' : '#e2e8f0'};">
                                <div style="font-size: 10px; color: ${ineligible ? '#c53030' : '#718096'}; text-transform: uppercase; font-weight: 700;">${type.label}</div>
                                <div style="font-size: 16px; font-weight: 800; color: ${ineligible ? '#c53030' : '#2d3748'};">
                                    ${ineligible ? '<i class="fas fa-lock"></i> 0' : bal.remaining}
                                </div>
                                <div style="font-size: 9px; color: ${ineligible ? '#c53030' : '#a0aec0'};">${ineligible ? 'Ineligible (<1yr)' : 'Remaining'}</div>
                            </div>
                        `;
                    });

                    // Add maternity/paternity if applicable
                    if (data.employee.gender === 'female' && (balances.maternity.entitled > 0 || balances.maternity.remaining > 0)) {
                        html += `
                            <div style="background: white; padding: 8px; border-radius: 8px; text-align: center; border: 1px solid #e2e8f0;">
                                <div style="font-size: 10px; color: #718096; text-transform: uppercase; font-weight: 700;">Maternity</div>
                                <div style="font-size: 16px; font-weight: 800; color: #2d3748;">${balances.maternity.remaining}</div>
                                <div style="font-size: 9px; color: #a0aec0;">Remaining</div>
                            </div>
                        `;
                    } else if (data.employee.gender === 'male' && (balances.paternity.entitled > 0 || balances.paternity.remaining > 0)) {
                        html += `
                            <div style="background: white; padding: 8px; border-radius: 8px; text-align: center; border: 1px solid #e2e8f0;">
                                <div style="font-size: 10px; color: #718096; text-transform: uppercase; font-weight: 700;">Paternity</div>
                                <div style="font-size: 16px; font-weight: 800; color: #2d3748;">${balances.paternity.remaining}</div>
                                <div style="font-size: 9px; color: #a0aec0;">Remaining</div>
                            </div>
                        `;
                    }

                    balanceGrid.innerHTML = html;
                })
                .catch(err => {
                    balanceGrid.innerHTML = '<div style="grid-column: span 3; color: #e74c3c; text-align: center; font-size: 12px;">Failed to fetch balance</div>';
                });
        }

        function submitHrLeaveRequest(e) {
            e.preventDefault();
            const form = e.target;
            const btn = document.getElementById('hrSubmitBtn');
            const originalText = btn.innerHTML;

            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            btn.disabled = true;

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            fetch('submit_hr_leave_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Leave request submitted successfully on behalf of the employee!');
                        closeHrRequestModal();
                        // Refresh data
                        location.reload();
                    } else {
                        alert('❌ Error: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('❌ Connection error');
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }
    </script>
    <script src="scripts.js"></script>
</body>

</html>