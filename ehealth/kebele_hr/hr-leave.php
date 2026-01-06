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
                            <button class="section-action-btn" onclick="window.location.href='submit_leave_request.php'">
                                <i class="fas fa-plus"></i> New Request
                            </button>
                            <button class="section-action-btn" onclick="window.location.href='leave_history.php'">
                                <i class="fas fa-history"></i> View All
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="leave-requests" id="leaveRequestsContainer">
                            <!-- Leave requests will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Load leave requests on page load
        window.addEventListener('load', function() {
            fetch('get_kebele_hr_leave_requests.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('leaveRequestsContainer');
                    container.innerHTML = '';

                    if (data.length === 0) {
                        container.innerHTML = '<p>No pending leave requests.</p>';
                        return;
                    }

                    data.forEach(request => {
                        const initials = request.first_name.charAt(0) + request.last_name.charAt(0);
                        const card = document.createElement('div');
                        card.className = 'leave-request-card';
                        card.innerHTML = `
                            <div class="leave-header">
                                <div class="leave-employee">
                                    <div class="employee-avatar">${initials}</div>
                                    <div>
                                        <div class="employee-name">${request.first_name} ${request.last_name}</div>
                                        <div class="employee-id">${request.department}</div>
                                    </div>
                                </div>
                                <div class="leave-type">${ucfirst(request.leave_type)} Leave</div>
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
                            </div>
                            <p style="margin-bottom: 15px; color: var(--gray);">${request.reason || 'No reason provided.'}</p>
                            <div class="leave-actions">
                                <button class="leave-action-btn approve" data-leave-id="${request.id}">Approve</button>
                                <button class="leave-action-btn reject" data-leave-id="${request.id}">Reject</button>
                            </div>
                        `;
                        container.appendChild(card);
                    });

                    // Update badge
                    document.getElementById('leaveBadge').textContent = data.length;

                    // Attach event listeners
                    attachLeaveActionListeners();
                })
                .catch(error => console.error('Error loading leave requests:', error));
        });

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

    </script>
    <script src="scripts.js"></script>
</body>
</html>