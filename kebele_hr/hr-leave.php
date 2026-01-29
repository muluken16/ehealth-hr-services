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
    <style>
        .leave-requests {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .leave-request-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-left: 5px solid #3498db;
        }

        .leave-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .leave-employee {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .employee-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #3498db;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
        }

        .employee-name {
            font-weight: 600;
            color: #2c3e50;
        }

        .employee-id {
            font-size: 13px;
            color: #6c757d;
        }

        .leave-type {
            background: #e3f2fd;
            color: #1565c0;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .leave-dates {
            display: flex;
            gap: 30px;
            margin-bottom: 15px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .leave-date-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 4px;
        }

        .leave-date-value {
            font-weight: 600;
            color: #2c3e50;
        }

        .leave-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e1e8ed;
        }

        .leave-action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 11px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .leave-action-btn.approve {
            background: #27ae60;
            color: white;
        }

        .leave-action-btn.approve:hover {
            background: #219a52;
        }

        .leave-action-btn.reject {
            background: #e74c3c;
            color: white;
        }

        .leave-action-btn.reject:hover {
            background: #c0392b;
        }

        .leave-action-btn.view {
            background: #3498db;
            color: white;
        }

        .leave-action-btn.view:hover {
            background: #2980b9;
        }

        /* Success Modal Styles */
        .success-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .success-modal.show {
            display: flex;
        }

        .success-modal-content {
            background: white;
            border-radius: 16px;
            padding: 30px;
            max-width: 450px;
            width: 90%;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: modalSlide 0.3s ease;
        }

        @keyframes modalSlide {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .success-icon {
            width: 70px;
            height: 70px;
            background: #d4edda;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .success-icon i {
            font-size: 35px;
            color: #28a745;
        }

        .success-title {
            font-size: 22px;
            font-weight: 700;
            color: #28a745;
            margin-bottom: 20px;
        }

        .employee-info-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            text-align: left;
            margin-bottom: 20px;
        }

        .employee-info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
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

        .employee-info-value.status-on-leave {
            color: #e67e22;
        }

        .close-modal-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .close-modal-btn:hover {
            background: #2980b9;
        }

        /* Detail Modal Specifics */
        .detail-row {
            margin-bottom: 15px;
            display: grid;
            grid-template-columns: 140px 1fr;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 8px;
        }

        .detail-label {
            font-weight: 600;
            color: #64748b;
            font-size: 0.9rem;
        }

        .detail-value {
            color: #1e293b;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .attachment-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 12px;
            background: #f0f9ff;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .attachment-link:hover {
            background: #e0f2fe;
            color: #0284c7;
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
                <!-- Leave Requests Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Pending Leave Requests</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn"
                                onclick="window.location.href='submit_leave_request.php'">
                                <i class="fas fa-plus"></i> New Request
                            </button>
                            <button class="section-action-btn" onclick="window.location.href='leave_history.php'">
                                <i class="fas fa-history"></i> View All
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="leave-requests" id="leaveRequestsContainer">
                            <div style="text-align: center; padding: 40px;">
                                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #3498db;"></i>
                                <p style="margin-top: 10px; color: #6c757d;">Loading leave requests...</p>
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

    <!-- Leave Details Modal -->
    <div class="success-modal" id="detailsModal">
        <div class="success-modal-content" style="max-width: 600px; text-align: left;">
            <div
                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px;">
                <h3 style="margin: 0; color: #1e293b; font-size: 1.4rem;"><i class="fas fa-file-alt"
                        style="color: #3498db; margin-right: 10px;"></i> Leave Request Details</h3>
                <button onclick="closeDetailsModal()"
                    style="background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer;"><i
                        class="fas fa-times"></i></button>
            </div>
            <div id="detailsContent">
                <!-- Data populated via JS -->
            </div>
            <div style="margin-top: 25px; display: flex; justify-content: flex-end; gap: 12px;">
                <button class="close-modal-btn" style="background: #f1f5f9; color: #64748b;"
                    onclick="closeDetailsModal()">Close</button>
                <button id="modalApproveBtn" class="close-modal-btn">Approve</button>
            </div>
        </div>
    </div>

    <script>
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        function ucfirst(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function updateBadgeCount() {
            const cards = document.querySelectorAll('.leave-request-card');
            const badge = document.getElementById('leaveBadge');
            if (badge) {
                badge.textContent = cards.length;
            }
        }

        // Load leave requests on page load
        document.addEventListener('DOMContentLoaded', function () {
            console.log('Loading leave requests...');
            fetch('get_kebele_hr_leave_requests.php')
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Data received:', data);
                    const container = document.getElementById('leaveRequestsContainer');
                    container.innerHTML = '';

                    if (data.length === 0) {
                        container.innerHTML = '<p style="text-align: center; padding: 40px; color: #6c757d;">No pending leave requests found.</p>';
                        return;
                    }

                    data.forEach(request => {
                        const initials = (request.first_name ? request.first_name.charAt(0) : '') + (request.last_name ? request.last_name.charAt(0) : '');
                        const card = document.createElement('div');
                        card.className = 'leave-request-card';
                        card.id = 'leave-card-' + request.id;

                        card.innerHTML = `
                            <div class="leave-header">
                                <div class="leave-employee">
                                    <div class="employee-avatar">${initials}</div>
                                    <div>
                                        <div class="employee-name">${request.first_name || ''} ${request.last_name || ''}</div>
                                        <div class="employee-id">${request.department || 'N/A'}</div>
                                    </div>
                                </div>
                                <div class="leave-type">${ucfirst(request.leave_type || 'annual')} Leave</div>
                            </div>
                            <div class="leave-dates">
                                <div class="leave-date">
                                    <div class="leave-date-label">From</div>
                                    <div class="leave-date-value">${formatDate(request.start_date)}</div>
                                </div>
                                <div class="leave-date">
                                    <div class="leave-date-label">To</div>
                                    <div class="leave-date-value">${formatDate(request.end_date)}</div>
                                </div>
                                <div class="leave-date">
                                    <div class="leave-date-label">Days</div>
                                    <div class="leave-date-value" style="color: #3498db; font-weight: 700;">${request.days_requested || 0}</div>
                                </div>
                            </div>
                            <p style="margin-bottom: 15px; color: #495057; line-height: 1.5;">${request.reason || 'No reason provided.'}</p>
                            <div class="leave-actions">
                                <button class="leave-action-btn view" onclick="viewLeaveDetails(${request.id})">
                                    <i class="fas fa-eye"></i> View
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

                    // Update badge
                    updateBadgeCount();
                })
                .catch(error => {
                    console.error('Error loading leave requests:', error);
                    document.getElementById('leaveRequestsContainer').innerHTML = '<p style="text-align: center; padding: 40px; color: #e74c3c;">Error loading leave requests. Please try again.</p>';
                });
        });

        let currentLeaveData = []; // To store loaded requests for quick lookup

        function viewLeaveDetails(leaveId) {
            console.log('Viewing details for:', leaveId);
            // We need the data from the initial fetch or fetch it again
            // For now, let's fetch it from a dedicated endpoint if possible, or search in our local array

            fetch('get_kebele_hr_leave_requests.php')
                .then(res => res.json())
                .then(data => {
                    const request = data.find(r => r.id == leaveId);
                    if (!request) return;

                    const content = document.getElementById('detailsContent');
                    content.innerHTML = `
                        <div class="detail-row">
                            <span class="detail-label">Employee</span>
                            <span class="detail-value">${request.first_name} ${request.last_name} (${request.employee_id})</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Department</span>
                            <span class="detail-value">${request.department || 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Leave Type</span>
                            <span class="detail-value">${ucfirst(request.leave_type)} Leave</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Duration</span>
                            <span class="detail-value">${formatDate(request.start_date)} to ${formatDate(request.end_date)} (${request.days_requested} days)</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Reason</span>
                            <span class="detail-value" style="background: #f8f9fa; padding: 10px; border-radius: 6px;">${request.reason || 'No reason provided.'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Contact</span>
                            <span class="detail-value">${request.phone_number || 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Supporting Docs</span>
                            <span class="detail-value">
                                ${request.leave_document ? `<a href="../uploads/leaves/${request.leave_document}" target="_blank" class="attachment-link"><i class="fas fa-paperclip"></i> View Document</a>` : '<span style="color: #94a3b8;">No documents uploaded</span>'}
                            </span>
                        </div>
                    `;

                    // Configure buttons in modal
                    const approveBtn = document.getElementById('modalApproveBtn');
                    approveBtn.onclick = () => {
                        closeDetailsModal();
                        approveLeave(request.id, request.first_name + ' ' + request.last_name);
                    };

                    document.getElementById('detailsModal').classList.add('show');
                });
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.remove('show');
        }

        // Approve leave function
        function approveLeave(leaveId, employeeName) {
            console.log('Approving leave:', leaveId, employeeName);
            if (!confirm('Approve leave request for ' + employeeName + '?')) {
                return;
            }

            const card = document.getElementById('leave-card-' + leaveId);
            if (!card) {
                alert('Error: Card not found');
                return;
            }

            fetch('approve_leave.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ leave_id: leaveId })
            })
                .then(response => {
                    console.log('Approve response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Approve response data:', data);
                    if (data.success) {
                        card.style.opacity = '0.5';
                        setTimeout(() => {
                            card.remove();
                            updateBadgeCount();
                            // Check if empty
                            const container = document.getElementById('leaveRequestsContainer');
                            const remainingCards = container.querySelectorAll('.leave-request-card').length;
                            if (remainingCards === 0) {
                                container.innerHTML = '<p style="text-align: center; padding: 40px; color: #6c757d;">No pending leave requests found.</p>';
                            }
                            // Show success modal with employee details
                            showSuccessModal(data.employee);
                        }, 300);
                    } else {
                        alert('❌ Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Approve error:', error);
                    alert('❌ Error: ' + error.message);
                });
        }

        // Show success modal with employee details
        function showSuccessModal(employee) {
            const modal = document.getElementById('successModal');
            const infoCard = document.getElementById('employeeInfoCard');

            const fullName = (employee.first_name || '') + ' ' + (employee.last_name || '');
            const leaveType = (employee.leave_type || 'annual').charAt(0).toUpperCase() + (employee.leave_type || 'annual').slice(1);

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
                    <span class="employee-info-label">Status</span>
                    <span class="employee-info-value status-on-leave">${employee.status || 'on-leave'}</span>
                </div>
                <div class="employee-info-row">
                    <span class="employee-info-label">Leave Type</span>
                    <span class="employee-info-value">${leaveType} Leave</span>
                </div>
                <div class="employee-info-row">
                    <span class="employee-info-label">Start Date</span>
                    <span class="employee-info-value">${formatDate(employee.start_date)}</span>
                </div>
                <div class="employee-info-row">
                    <span class="employee-info-label">End Date</span>
                    <span class="employee-info-value">${formatDate(employee.end_date)}</span>
                </div>
                <div class="employee-info-row">
                    <span class="employee-info-label">Days Requested</span>
                    <span class="employee-info-value">${employee.days_requested} days</span>
                </div>
            `;

            modal.classList.add('show');
        }

        // Close success modal
        function closeSuccessModal() {
            const modal = document.getElementById('successModal');
            modal.classList.remove('show');
        }

        // Close modals on outside click
        document.addEventListener('click', function (e) {
            const successModal = document.getElementById('successModal');
            const detailsModal = document.getElementById('detailsModal');
            if (e.target === successModal) {
                closeSuccessModal();
            }
            if (e.target === detailsModal) {
                closeDetailsModal();
            }
        });

        // Reject leave function
        function rejectLeave(leaveId, employeeName) {
            console.log('Rejecting leave:', leaveId, employeeName);
            const reason = prompt('Enter reason for rejecting ' + employeeName + "'s leave:");

            if (reason === null) {
                return; // User cancelled
            }

            const card = document.getElementById('leave-card-' + leaveId);
            if (!card) {
                alert('Error: Card not found');
                return;
            }

            fetch('reject_leave.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ leave_id: leaveId, reason: reason || 'Rejected by HR' })
            })
                .then(response => {
                    console.log('Reject response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Reject response data:', data);
                    if (data.success) {
                        card.style.opacity = '0.5';
                        setTimeout(() => {
                            card.remove();
                            updateBadgeCount();
                            // Check if empty
                            const container = document.getElementById('leaveRequestsContainer');
                            const remainingCards = container.querySelectorAll('.leave-request-card').length;
                            if (remainingCards === 0) {
                                container.innerHTML = '<p style="text-align: center; padding: 40px; color: #6c757d;">No pending leave requests found.</p>';
                            }
                            alert('✅ Leave request rejected!');
                        }, 300);
                    } else {
                        alert('❌ Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Reject error:', error);
                    alert('❌ Error: ' + error.message);
                });
        }
    </script>
    <script src="scripts.js"></script>
</body>

</html>