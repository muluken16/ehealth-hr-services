<?php
session_start();
// Default timezone
date_default_timezone_set('Africa/Addis_Ababa');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Attendance Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        /* Specific styles for attendance page */
        .date-picker-container {
            position: relative;
            display: inline-block;
        }
        
        .date-picker-input {
            padding: 8px 15px;
            border: 1px solid var(--primary);
            border-radius: 5px;
            color: var(--primary);
            font-family: inherit;
            cursor: pointer;
            outline: none;
        }

        .status-select {
            padding: 5px 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .status-badge {
            cursor: pointer;
            transition: transform 0.2s;
        }
        .status-badge:hover {
            transform: scale(1.05);
        }

        /* Modal Styles enhancement */
        .modal-content {
            max-width: 500px;
        }
        
        .time-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
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
                $page_title = "Attendance Management";
                include 'navbar.php'; 
            ?>

            <!-- Content -->
            <div class="content">
                <!-- Attendance Overview Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Daily Attendance Overview</h2>
                        <div class="hr-section-actions">
                            <div class="date-picker-container">
                                <input type="date" id="attendanceDate" class="date-picker-input" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <button class="section-action-btn" id="exportBtn">
                                <i class="fas fa-download"></i> Export CSV
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="attendance-stats" id="attendanceStatsContainer">
                            <!-- Loading state -->
                            <div style="text-align: center; color: var(--gray);">Loading stats...</div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Table Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Employee Attendance List</h2>
                        <div class="hr-section-actions">
                            <select id="statusFilter" class="section-action-btn" style="border: 1px solid var(--gray); color: var(--dark);">
                                <option value="all">All Status</option>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="on-leave">On Leave</option>
                            </select>
                            <button class="section-action-btn" id="markAllPresentBtn">
                                <i class="fas fa-check-double"></i> Mark All Present
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="table-container">
                            <table class="table" id="attendanceTable">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Working Hours</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="attendanceTableBody">
                                    <!-- Attendance data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit Attendance Modal -->
    <div class="modal" id="editAttendanceModal">
        <div class="modal-content">
            <span class="close-modal" id="closeEditModal">&times;</span>
            <h2 class="modal-title">Update Attendance</h2>
            <form id="editAttendanceForm">
                <input type="hidden" id="editEmployeeId" name="employee_id">
                <div class="form-group">
                    <label>Employee Name</label>
                    <input type="text" id="editEmployeeName" disabled style="background-color: #f8f9fa;">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="editStatus" required>
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="late">Late</option>
                        <option value="on-leave">On Leave</option>
                    </select>
                </div>
                <div class="time-inputs">
                    <div class="form-group">
                        <label>Check In</label>
                        <input type="time" name="check_in" id="editCheckIn">
                    </div>
                    <div class="form-group">
                        <label>Check Out</label>
                        <input type="time" name="check_out" id="editCheckOut">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="submit-btn" id="saveattendanceBtn">Save Changes</button>
                    <button type="button" class="cancel-btn" onclick="closeEditModalFunc()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Global variables
        let currentDate = new Date().toISOString().split('T')[0];
        let attendanceData = [];

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Date Picker
            const dateInput = document.getElementById('attendanceDate');
            dateInput.value = currentDate;
            dateInput.addEventListener('change', function(e) {
                currentDate = e.target.value;
                loadData();
            });

            // Filter
            document.getElementById('statusFilter').addEventListener('change', renderTable);

            // Export
            document.getElementById('exportBtn').addEventListener('click', exportCSV);

            // Mark All Present
            document.getElementById('markAllPresentBtn').addEventListener('click', markAllPresent);

            // Modal events
            document.getElementById('closeEditModal').addEventListener('click', closeEditModalFunc);
            window.addEventListener('click', (e) => {
                if (e.target == document.getElementById('editAttendanceModal')) closeEditModalFunc();
            });
            document.getElementById('editAttendanceForm').addEventListener('submit', saveAttendance);

            // Initial Load
            loadData();
        });

        function loadData() {
            // Load stats
            fetch(`get_kebele_hr_attendance_stats.php?date=${currentDate}`)
                .then(response => response.json())
                .then(data => renderStats(data))
                .catch(error => console.error('Error loading stats:', error));

            // Load table data
            fetch(`get_kebele_hr_attendance.php?date=${currentDate}`)
                .then(response => response.json())
                .then(data => {
                    attendanceData = data;
                    renderTable();
                })
                .catch(error => console.error('Error loading data:', error));
        }

        function renderStats(data) {
            const container = document.getElementById('attendanceStatsContainer');
            if(!data) return;
            
            container.innerHTML = `
                <div class="attendance-stat-cards">
                    <div class="attendance-stat-card present">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>${data.present_today || 0}</h3>
                            <p>Present</p>
                        </div>
                    </div>
                    <div class="attendance-stat-card absent">
                        <div class="stat-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>${data.absent_today || 0}</h3>
                            <p>Absent</p>
                        </div>
                    </div>
                    <div class="attendance-stat-card late">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3>${data.late_today || 0}</h3>
                            <p>Late</p>
                        </div>
                    </div>
                    <div class="attendance-stat-card on-leave">
                        <div class="stat-icon">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                        <div class="stat-info">
                            <h3>${data.on_leave || 0}</h3>
                            <p>On Leave</p>
                        </div>
                    </div>
                </div>
            `;
        }

        function renderTable() {
            const tbody = document.getElementById('attendanceTableBody');
            const filter = document.getElementById('statusFilter').value;
            tbody.innerHTML = '';

            const filteredData = filter === 'all' 
                ? attendanceData 
                : attendanceData.filter(item => item.status === filter);

            if (filteredData.length > 0) {
                filteredData.forEach(employee => {
                    const checkInTime = employee.check_in ? employee.check_in.substring(0, 5) : '--:--';
                    const checkOutTime = employee.check_out ? employee.check_out.substring(0, 5) : '--:--';
                    const workingHours = employee.working_hours || '0.00';
                    
                    let statusClass = 'inactive';
                    let statusText = 'Absent';
                    
                    if(employee.status === 'present') { statusClass = 'success'; statusText = 'Present'; }
                    else if(employee.status === 'late') { statusClass = 'warning'; statusText = 'Late'; }
                    else if(employee.status === 'on-leave') { statusClass = 'info'; statusText = 'On Leave'; }
                    else { statusClass = 'danger'; statusText = 'Absent'; } // Default

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            <div class="employee-info">
                                <div class="employee-avatar">${employee.first_name.charAt(0)}${employee.last_name.charAt(0)}</div>
                                <div>
                                    <div class="employee-name">${employee.first_name} ${employee.last_name}</div>
                                    <div class="employee-id">${employee.employee_id}</div>
                                </div>
                            </div>
                        </td>
                        <td>${checkInTime}</td>
                        <td>${checkOutTime}</td>
                        <td>${workingHours} hrs</td>
                        <td><span class="status-badge status-${statusClass}" style="background-color: var(--${statusClass}); color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem;">${statusText}</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn edit" onclick="openEditModal('${employee.employee_id}')"><i class="fas fa-edit"></i></button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px;">No records found for this date.</td></tr>';
            }
        }

        function openEditModal(empId) {
            const emp = attendanceData.find(e => e.employee_id === empId);
            if(!emp) return;

            document.getElementById('editEmployeeId').value = emp.employee_id;
            document.getElementById('editEmployeeName').value = `${emp.first_name} ${emp.last_name}`;
            document.getElementById('editStatus').value = emp.status || 'absent';
            document.getElementById('editCheckIn').value = emp.check_in ? emp.check_in.substring(0, 5) : '';
            document.getElementById('editCheckOut').value = emp.check_out ? emp.check_out.substring(0, 5) : '';

            document.getElementById('editAttendanceModal').style.display = 'block';
        }

        function closeEditModalFunc() {
            document.getElementById('editAttendanceModal').style.display = 'none';
        }

        function saveAttendance(e) {
            e.preventDefault();
            const formData = {
                employee_id: document.getElementById('editEmployeeId').value,
                status: document.getElementById('editStatus').value,
                check_in: document.getElementById('editCheckIn').value,
                check_out: document.getElementById('editCheckOut').value,
                date: currentDate
            };

            fetch('mark_attendance.php?action=update_single', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            })
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    closeEditModalFunc();
                    loadData(); // Reload to see changes
                } else {
                    alert('Error: ' + res.message);
                }
            });
        }

        function markAllPresent() {
            if(!confirm('Are you sure you want to mark all employees as Present for ' + currentDate + '? This will only affect employees with no record for today.')) return;

            fetch('mark_attendance.php?action=mark_all_present', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ date: currentDate })
            })
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    alert(res.message);
                    loadData();
                } else {
                    alert('Error: ' + res.message);
                }
            });
        }

        function exportCSV() {
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Employee ID,First Name,Last Name,Check In,Check Out,Working Hours,Status\n";

            attendanceData.forEach(row => {
                const checkIn = row.check_in || '';
                const checkOut = row.check_out || '';
                const hours = row.working_hours || '0';
                csvContent += `${row.employee_id},${row.first_name},${row.last_name},${checkIn},${checkOut},${hours},${row.status}\n`;
            });

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `attendance_${currentDate}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>