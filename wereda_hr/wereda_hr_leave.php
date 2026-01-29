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
    <title>HealthFirst | Leave Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>

        <?php
        $page_title = 'Leave Management';
        include 'navbar.php';
        ?>

        <main class="hr-main">
            <!-- Leave Management Content -->
            <div class="hr-dashboard">
                <!-- Leave Summary Cards -->
                <div class="hr-stats" style="margin-bottom: 30px;">
                    <div class="hr-stat-card attendance">
                        <div class="hr-stat-icon" style="background: rgba(76, 181, 174, 0.1); color: #4cb5ae;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3 id="pendingCount">--</h3>
                            <p>Pending Review</p>
                        </div>
                    </div>
                    <div class="hr-stat-card employees">
                        <div class="hr-stat-icon" style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3 id="approvedTodayText">--</h3>
                            <p>Approved Today</p>
                        </div>
                    </div>
                    <div class="hr-stat-card vacancy">
                        <div class="hr-stat-icon" style="background: rgba(255, 126, 95, 0.1); color: #ff7e5f;">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                        <div class="hr-stat-info">
                            <h3 id="totalOnLeaveText">--</h3>
                            <p>Total on Leave</p>
                        </div>
                    </div>
                </div>

                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Leave Request Queue</h2>
                        <div class="hr-section-actions">
                            <select class="filter-select" id="leaveTypeFilter" style="padding: 8px 15px; border-radius: 8px; border: 1px solid #ddd; margin-right: 10px;">
                                <option value="all">All Leave Types</option>
                                <option value="annual">Annual</option>
                                <option value="sick">Sick</option>
                                <option value="emergency">Emergency</option>
                                <option value="maternity">Maternity</option>
                            </select>
                            <button class="section-action-btn">
                                <i class="fas fa-calendar-alt"></i> Visual Calendar
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="leave-requests" id="leaveRequestsContainer">
                            <div style="text-align: center; padding: 40px;">
                                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary);"></i>
                                <p style="margin-top: 10px;">Loading requests...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="scripts.js"></script>
    <script>
        // Initialize stats
        function loadLeaveStats() {
            fetch('get_hr_stats.php')
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        document.getElementById('pendingCount').textContent = data.stats.pending_leave;
                        document.getElementById('approvedTodayText').textContent = '4'; // Mock for demo
                        document.getElementById('totalOnLeaveText').textContent = data.stats.on_leave;
                    }
                });
        }

        // Load leave requests
        let allRequests = [];
        function loadLeaveRequests() {
            fetch('get_leave_requests.php')
                .then(response => response.json())
                .then(data => {
                    allRequests = data;
                    applyFilters();
                })
                .catch(error => console.error('Error loading leave requests:', error));
        }

        function applyFilters() {
            const container = document.getElementById('leaveRequestsContainer');
            const typeFilter = document.getElementById('leaveTypeFilter').value;
            
            const filtered = allRequests.filter(req => {
                return typeFilter === 'all' || req.leave_type.toLowerCase() === typeFilter;
            });

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div style="text-align:center; padding:50px; color:var(--gray);">
                        <i class="fas fa-folder-open" style="font-size:3rem; margin-bottom:15px; opacity:0.3;"></i>
                        <p>No pending requests found for this filter.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = filtered.map(request => {
                const initials = request.first_name.charAt(0) + request.last_name.charAt(0);
                const typeClass = request.leave_type.toLowerCase();
                return `
                    <div class="leave-request-card" style="border-left: 5px solid var(--${typeClass === 'sick' ? 'danger' : (typeClass === 'annual' ? 'success' : 'accent')});">
                        <div class="leave-header">
                            <div class="leave-employee">
                                <div class="employee-avatar" style="background:var(--primary); color:white;">${initials}</div>
                                <div>
                                    <div class="employee-name">${request.first_name} ${request.last_name}</div>
                                    <div class="employee-id">${request.department_assigned} • ${request.employee_id}</div>
                                </div>
                            </div>
                            <div class="leave-type-badge ${typeClass}">${ucfirst(request.leave_type)}</div>
                        </div>
                        <div class="leave-dates">
                            <div class="leave-date">
                                <div class="leave-date-label">Start Date</div>
                                <div class="leave-date-value"><i class="far fa-calendar-alt"></i> ${formatDate(request.start_date)}</div>
                            </div>
                            <div class="leave-date">
                                <div class="leave-date-label">End Date</div>
                                <div class="leave-date-value"><i class="far fa-calendar-check"></i> ${formatDate(request.end_date)}</div>
                            </div>
                            <div class="leave-date">
                                <div class="leave-date-label">Duration</div>
                                <div class="leave-date-value" style="color:var(--primary); font-weight:700;">${request.days_requested} Days</div>
                            </div>
                        </div>
                        <div class="leave-reason-box">
                            <span style="font-size:0.75rem; color:var(--gray); text-transform:uppercase; font-weight:700;">Reason provided:</span>
                            <p style="margin-top:5px; line-height:1.5;">${request.reason || 'No specific reason mentioned.'}</p>
                        </div>
                        <div class="leave-actions" style="border-top: 1px solid #eee; padding-top:15px;">
                            <button class="leave-action-btn approve" data-leave-id="${request.id}"><i class="fas fa-check"></i> Approve Request</button>
                            <button class="leave-action-btn reject" data-leave-id="${request.id}"><i class="fas fa-times"></i> Reject</button>
                        </div>
                    </div>
                `;
            }).join('');

            attachLeaveActionListeners();
        }

        document.getElementById('leaveTypeFilter').addEventListener('change', applyFilters);

        function attachLeaveActionListeners() {
            document.querySelectorAll('.leave-action-btn.approve').forEach(btn => {
                btn.addEventListener('click', function() {
                    const card = this.closest('.leave-request-card');
                    const employeeName = card.querySelector('.employee-name').textContent;
                    const leaveId = this.getAttribute('data-leave-id');
                    if (confirm(`Approve leave request for ${employeeName}?`)) {
                        fetch('approve_leave.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ leave_id: leaveId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                card.style.opacity = '0.5';
                                setTimeout(() => {
                                    card.remove();
                                    alert(`Leave request for ${employeeName} has been approved.`);
                                }, 300);
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    }
                });
            });

            document.querySelectorAll('.leave-action-btn.reject').forEach(btn => {
                btn.addEventListener('click', function() {
                    const card = this.closest('.leave-request-card');
                    const employeeName = card.querySelector('.employee-name').textContent;
                    const leaveId = this.getAttribute('data-leave-id');
                    if (confirm(`Reject leave request for ${employeeName}?`)) {
                        fetch('reject_leave.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ leave_id: leaveId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                card.style.opacity = '0.5';
                                setTimeout(() => {
                                    card.remove();
                                    alert(`Leave request for ${employeeName} has been rejected.`);
                                }, 300);
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    }
                });
            });
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        function ucfirst(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        // Load leave requests on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadLeaveStats();
            loadLeaveRequests();
        });
    </script>
</body>
</html>