<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'zone_hr') {
    header('Location: ../index.html');
    exit();
}
// Default timezone
date_default_timezone_set('Africa/Addis_Ababa');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zone HR | Attendance Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
</head>

<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <?php
            $page_title = "Zone Attendance Management";
            include 'navbar.php';
            ?>
            <div class="content">
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Zone-wide Attendance Monitor</h2>
                        <div class="hr-section-actions">
                            <input type="date" id="attendanceDate" class="section-action-btn"
                                value="<?php echo date('Y-m-d'); ?>">
                            <button class="section-action-btn" id="exportBtn">
                                <i class="fas fa-download"></i> Export Reports
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="table-container">
                            <table class="table" id="attendanceTable">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Woreda</th>
                                        <th>Kebele</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="attendanceTableBody">
                                    <tr>
                                        <td colspan="6" style="text-align:center; padding: 20px;">Loading zone
                                            attendance data...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            loadAttendance();
            document.getElementById('attendanceDate').addEventListener('change', loadAttendance);
        });

        function loadAttendance() {
            const date = document.getElementById('attendanceDate').value;
            fetch(`get_zone_attendance.php?date=${date}`)
                .then(r => r.json())
                .then(data => {
                    const tbody = document.getElementById('attendanceTableBody');
                    tbody.innerHTML = '';
                    if (data.success && data.records.length > 0) {
                        data.records.forEach(r => {
                            const row = `<tr>
                                <td>${r.name}</td>
                                <td>${r.woreda}</td>
                                <td>${r.kebele}</td>
                                <td>${r.check_in || '--:--'}</td>
                                <td>${r.check_out || '--:--'}</td>
                                <td><span class="status-badge ${r.status}">${r.status}</span></td>
                            </tr>`;
                            tbody.innerHTML += row;
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px;">No records found for this date.</td></tr>';
                    }
                });
        }
    </script>
</body>

</html>