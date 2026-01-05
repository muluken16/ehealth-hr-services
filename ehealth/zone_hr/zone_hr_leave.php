<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'zone_hr') {
    header('Location: ../index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Zone HR Leave Management</title>
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
            <div class="hr-dashboard">
                <div class="filters-section">
                    <div class="search-box"><input type="text" placeholder="Search leave requests..." id="leaveSearch"><i class="fas fa-search"></i></div>
                    <select class="filter-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <button class="add-btn" onclick="refreshLeaveRequests()"><i class="fas fa-sync-alt"></i> Refresh</button>
                </div>
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Leave Requests</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn" onclick="exportLeaveRequests()">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Leave Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Days</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="leaveTableBody">
                                    <!-- Leave requests will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="scripts.js"></script>
    <script>
        let allLeaveRequests = [];
        const leaveSearch = document.getElementById('leaveSearch');
        const statusFilter = document.getElementById('statusFilter');

        // Load leave requests on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadLeaveRequests();
        });

        // Load leave requests from database
        function loadLeaveRequests() {
            fetch('get_leave_requests.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allLeaveRequests = data.requests;
                        displayLeaveRequests(allLeaveRequests);
                    } else {
                        console.error('Failed to load leave requests:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading leave requests:', error);
                });
        }

        // Display leave requests in table
        function displayLeaveRequests(requests) {
            const tbody = document.getElementById('leaveTableBody');
            tbody.innerHTML = '';

            if (requests.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 40px; color: var(--gray);">No leave requests found</td></tr>';
                return;
            }

            requests.forEach(request => {
                const fullName = `${request.first_name} ${request.middle_name ? request.middle_name + ' ' : ''}${request.last_name}`;
                const initials = fullName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);

                const startDate = new Date(request.start_date).toLocaleDateString('en-GB');
                const endDate = new Date(request.end_date).toLocaleDateString('en-GB');

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <div class="employee-info">
                            <div class="employee-avatar">${initials}</div>
                            <div>
                                <div class="employee-name">${fullName}</div>
                                <div class="employee-id">${request.employee_id}</div>
                            </div>
                        </div>
                    </td>
                    <td>${request.leave_type || 'Annual Leave'}</td>
                    <td>${startDate}</td>
                    <td>${endDate}</td>
                    <td>${request.days}</td>
                    <td>${request.reason || 'Not specified'}</td>
                    <td><span class="status-badge ${request.status}">${request.status}</span></td>
                    <td>
                        <div class="action-buttons">
                            ${request.status === 'pending' ? `
                                <button class="action-btn approve" onclick="approveLeave(${request.id})"><i class="fas fa-check"></i></button>
                                <button class="action-btn reject" onclick="rejectLeave(${request.id})"><i class="fas fa-times"></i></button>
                            ` : ''}
                            <button class="action-btn view" onclick="viewLeaveRequest(${request.id})"><i class="fas fa-eye"></i></button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Filter functionality
        function filterLeaveRequests() {
            const searchTerm = leaveSearch.value.toLowerCase();
            const statusValue = statusFilter.value;

            const filtered = allLeaveRequests.filter(request => {
                const fullName = `${request.first_name} ${request.middle_name ? request.middle_name + ' ' : ''}${request.last_name}`.toLowerCase();
                const employeeId = request.employee_id.toLowerCase();

                const matchesSearch = fullName.includes(searchTerm) || employeeId.includes(searchTerm);
                const matchesStatus = statusValue === '' || request.status === statusValue;

                return matchesSearch && matchesStatus;
            });

            displayLeaveRequests(filtered);
        }

        leaveSearch.addEventListener('input', filterLeaveRequests);
        statusFilter.addEventListener('change', filterLeaveRequests);

        // Approve leave
        function approveLeave(id) {
            if (confirm('Are you sure you want to approve this leave request?')) {
                fetch('approve_leave.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Leave request approved successfully!');
                        loadLeaveRequests();
                    } else {
                        alert('Failed to approve leave request: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while approving the leave request.');
                });
            }
        }

        // Reject leave
        function rejectLeave(id) {
            if (confirm('Are you sure you want to reject this leave request?')) {
                fetch('reject_leave.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Leave request rejected successfully!');
                        loadLeaveRequests();
                    } else {
                        alert('Failed to reject leave request: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while rejecting the leave request.');
                });
            }
        }

        // View leave request details
        function viewLeaveRequest(id) {
            const request = allLeaveRequests.find(req => req.id == id);
            if (request) {
                const fullName = `${request.first_name} ${request.middle_name ? request.middle_name + ' ' : ''}${request.last_name}`;
                let details = `Leave Request Details:\n\n`;
                details += `Employee: ${fullName}\n`;
                details += `Employee ID: ${request.employee_id}\n`;
                details += `Leave Type: ${request.leave_type || 'Annual Leave'}\n`;
                details += `Start Date: ${new Date(request.start_date).toLocaleDateString()}\n`;
                details += `End Date: ${new Date(request.end_date).toLocaleDateString()}\n`;
                details += `Days: ${request.days}\n`;
                details += `Reason: ${request.reason || 'Not specified'}\n`;
                details += `Status: ${request.status}\n`;
                details += `Request Date: ${new Date(request.request_date).toLocaleDateString()}\n`;
                alert(details);
            }
        }

        // Export function
        function exportLeaveRequests() {
            alert('Export functionality would generate and download leave requests data here.');
        }

        // Refresh function
        function refreshLeaveRequests() {
            loadLeaveRequests();
        }
    </script>
</body>
</html>