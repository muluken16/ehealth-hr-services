<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'wereda_health_officer') {
    header('Location: ../index.html');
    exit();
}

include '../db.php';
$conn = getDBConnection();

$user_woreda = $_SESSION['woreda'] ?? 'West Shewa Woreda 1';
$user_woreda_escaped = $conn->real_escape_string($user_woreda);

// --- PRE-FETCH DATA FOR INITIAL RENDER ---
$result = $conn->query("SELECT COUNT(*) as count FROM employees WHERE woreda = '$user_woreda_escaped'");
$total_employees = ($result && $row = $result->fetch_assoc()) ? $row['count'] : 0;

$result = $conn->query("SELECT COUNT(*) as count FROM employees WHERE woreda = '$user_woreda_escaped' AND status = 'active'");
$active_employees = ($result && $row = $result->fetch_assoc()) ? $row['count'] : 0;

// Kebele Distribution for Initial Charts
$sql_kebele = "SELECT kebele, COUNT(*) as count, SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count FROM employees WHERE woreda = '$user_woreda_escaped' GROUP BY kebele ORDER BY kebele";
$kebele_stats = $conn->query($sql_kebele);
$kebele_labels = [];
$kebele_counts = [];
$kebele_active = [];
if ($kebele_stats) {
    while ($row = $kebele_stats->fetch_assoc()) {
        $kebele_labels[] = $row['kebele'] ?: 'Unknown';
        $kebele_counts[] = $row['count'];
        $kebele_active[] = $row['active_count'];
    }
}

// Position Dist
$position_stats = $conn->query("SELECT position, COUNT(*) as count FROM employees WHERE woreda = '$user_woreda_escaped' GROUP BY position ORDER BY count DESC LIMIT 8");
$pos_labels = [];
$pos_counts = [];
while ($position_stats && $row = $position_stats->fetch_assoc()) {
    $pos_labels[] = $row['position'] ?: 'Other';
    $pos_counts[] = $row['count'];
}

// Kebele List for Filter
$kebele_list = $conn->query("SELECT DISTINCT kebele FROM employees WHERE woreda = '$user_woreda_escaped' ORDER BY kebele");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wereda HO | Health Workforce Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/styleho.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #1a4a5f;
            --secondary: #4cb5ae;
            --accent: #ff7e5f;
            --bg-light: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2.2fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        .welcome-banner-premium {
            background: linear-gradient(135deg, var(--primary) 0%, #2a6e8c 100%);
            color: white;
            padding: 40px;
            border-radius: 24px;
            margin-bottom: 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(26, 74, 95, 0.15);
            position: relative;
            overflow: hidden;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card-new {
            background: white;
            padding: 24px;
            border-radius: 20px;
            border: 1px solid #f1f5f9;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s;
        }

        .stat-card-new:hover {
            transform: translateY(-5px);
            border-color: var(--secondary);
        }

        .stat-icon-box {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .section-box {
            background: white;
            border-radius: 20px;
            border: 1px solid #f1f5f9;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 25px;
        }

        .section-header {
            padding: 20px 25px;
            border-bottom: 1.5px solid #f8fafc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--primary);
            margin: 0;
        }

        .avatar-circle {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #3b82f6;
            font-size: 0.8rem;
        }

        .side-panel {
            position: fixed;
            right: -550px;
            top: 0;
            width: 500px;
            height: 100vh;
            background: white;
            z-index: 2100;
            box-shadow: -15px 0 40px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
        }

        .side-panel.open {
            right: 0;
        }

        .side-panel-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            z-index: 2099;
            display: none;
        }

        .side-panel-overlay.active {
            display: block;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        <div class="mobile-overlay" id="mobileOverlay"></div>
        <div class="side-panel-overlay" id="sideOverlay" onclick="closeSidePanel()"></div>

        <main class="main-content">
            <header class="header">
                <div class="header-left">
                    <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                    <h1 class="page-title">Workforce Hub</h1>
                </div>
                <div class="header-actions">
                    <div class="user-profile">
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 2)); ?>
                        </div>
                        <div class="user-info">
                            <span
                                class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Officer'); ?></span>
                            <span class="user-role">Wereda Health Officer</span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content">
                <div class="welcome-banner-premium">
                    <div>
                        <h2 style="margin: 0 0 10px 0; font-size: 1.8rem;">Central Health Command</h2>
                        <p style="margin: 0; opacity: 0.9; font-weight: 600;">Managing
                            <?php echo htmlspecialchars($user_woreda); ?> Workforce: Woreda HR & All Kebele Health
                            Units.
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <span
                            style="display: block; font-weight: 700; font-size: 1.1rem;"><?php echo date('F j, Y'); ?></span>
                        <span style="opacity: 0.8; font-size: 0.9rem;"
                            id="currentTime"><?php echo date('h:i A'); ?></span>
                    </div>
                </div>

                <div class="stats-row">
                    <div class="stat-card-new">
                        <div class="stat-icon-box" style="background: #eff6ff; color: #2563eb;"><i
                                class="fas fa-users"></i></div>
                        <div class="stat-info">
                            <div
                                style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">
                                Total Employees</div>
                            <div style="font-size: 1.6rem; font-weight: 800; color: var(--primary);" id="stat-total">
                                <?php echo number_format($total_employees); ?>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card-new">
                        <div class="stat-icon-box" style="background: #ecfdf5; color: #10b981;"><i
                                class="fas fa-user-check"></i></div>
                        <div class="stat-info">
                            <div
                                style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">
                                Active Now</div>
                            <div style="font-size: 1.6rem; font-weight: 800; color: var(--primary);" id="stat-active">
                                <?php echo number_format($active_employees); ?>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card-new">
                        <div class="stat-icon-box" style="background: #fff7ed; color: #ea580c;"><i
                                class="fas fa-map-marked-alt"></i></div>
                        <div class="stat-info">
                            <div
                                style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">
                                Managed Kebeles</div>
                            <div style="font-size: 1.6rem; font-weight: 800; color: var(--primary);" id="stat-kebeles">
                                --</div>
                        </div>
                    </div>
                    <div class="stat-card-new">
                        <div class="stat-icon-box" style="background: #fdf2f8; color: #db2777;"><i
                                class="fas fa-plane-departure"></i></div>
                        <div class="stat-info">
                            <div
                                style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">
                                On Leave</div>
                            <div style="font-size: 1.6rem; font-weight: 800; color: var(--primary);" id="stat-leave">--
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <div class="main-column">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                            <div class="section-box">
                                <div class="section-header">
                                    <h3 class="section-title">Kebele Distribution</h3>
                                </div>
                                <div style="padding: 20px; height: 300px;"><canvas id="kebeleChart"></canvas></div>
                            </div>
                            <div class="section-box">
                                <div class="section-header">
                                    <h3 class="section-title">Staff Roles</h3>
                                </div>
                                <div style="padding: 20px; height: 300px;"><canvas id="roleChart"></canvas></div>
                            </div>
                        </div>

                        <div class="section-box" style="margin-bottom: 25px;">
                            <div class="section-header">
                                <h3 class="section-title">Kebele Workforce Performance</h3>
                                <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted);">Tabular
                                    Overview</span>
                            </div>
                            <div style="padding: 0; overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead style="background: #f8fafc;">
                                        <tr>
                                            <th
                                                style="padding: 12px 25px; text-align: left; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">
                                                Kebele Unit</th>
                                            <th
                                                style="padding: 12px 25px; text-align: center; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">
                                                Total Staff</th>
                                            <th
                                                style="padding: 12px 25px; text-align: center; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">
                                                Active Duty</th>
                                            <th
                                                style="padding: 12px 25px; text-align: center; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">
                                                On Leave</th>
                                            <th
                                                style="padding: 12px 25px; text-align: center; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">
                                                Ratio</th>
                                        </tr>
                                    </thead>
                                    <tbody id="kebelePerformanceBody">
                                        <!-- Populated via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="section-box" id="employeesSection">
                            <div class="section-header">
                                <h3 class="section-title">Global Staff Directory (Woreda & All Kebeles)</h3>
                                <div style="display: flex; gap: 10px;">
                                    <select id="kFilter" onchange="filterList()"
                                        style="padding: 8px; border-radius: 8px; border: 1px solid #ddd; font-weight: 600;">
                                        <option value="">All Areas</option>
                                        <?php if ($kebele_list) {
                                            $kebele_list->data_seek(0);
                                            while ($k = $kebele_list->fetch_assoc())
                                                echo "<option>" . htmlspecialchars($k['kebele']) . "</option>";
                                        } ?>
                                    </select>
                                    <input type="text" id="sFilter" placeholder="Search staff..." oninput="filterList()"
                                        style="padding: 8px; border-radius: 8px; border: 1px solid #ddd;">
                                </div>
                            </div>
                            <div style="padding: 0; overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse;" id="staffTable">
                                    <thead style="background: #f8fafc;">
                                        <tr>
                                            <th
                                                style="padding: 15px 25px; text-align: left; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">
                                                Professional</th>
                                            <th
                                                style="padding: 15px 25px; text-align: left; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">
                                                ID</th>
                                            <th
                                                style="padding: 15px 25px; text-align: left; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">
                                                Kebele</th>
                                            <th
                                                style="padding: 15px 25px; text-align: left; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">
                                                Status</th>
                                            <th
                                                style="padding: 15px 25px; text-align: center; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">
                                                Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="staffBody">
                                        <tr>
                                            <td colspan="5" style="text-align: center; padding: 40px;">Initial load...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="side-column">
                        <div class="section-box">
                            <div class="section-header">
                                <h3 class="section-title">Pending Leave</h3><a href="leave_requests.php"
                                    style="font-size: 0.8rem; color: var(--secondary); font-weight: 700;">View All</a>
                            </div>
                            <div id="pendingLeavesList">
                                <div style="padding: 20px; text-align: center; color: var(--text-muted);">No pending
                                    requests.</div>
                            </div>
                        </div>
                        <div class="section-box">
                            <div class="section-header">
                                <h3 class="section-title">Recent Activity</h3>
                            </div>
                            <div id="activityFeed" style="padding: 20px 25px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="side-panel" id="detailPanel">
        <div
            style="padding: 30px; border-bottom: 2px solid #f8fafc; display: flex; justify-content: space-between; align-items: center; background: #fdfdfd; position: sticky; top: 0; z-index: 10;">
            <h2 style="margin: 0; color: var(--primary); font-size: 1.2rem;"><i class="fas fa-id-card-alt"></i> Staff
                Profile</h2>
            <button onclick="closeSidePanel()"
                style="background: none; border: none; font-size: 1.3rem; cursor: pointer; color: var(--text-muted);"><i
                    class="fas fa-times-circle"></i></button>
        </div>
        <div id="panelContent" style="padding: 30px;"></div>
    </div>

    <script>
        function showSection(id) {
            const el = document.getElementById(id);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                el.style.background = '#f0f9ff';
                setTimeout(() => el.style.background = 'white', 1000);
            }
        }

        function loadFullDashboard() {
            fetch('get_ho_dashboard_data.php')
                .then(r => r.json())
                .then(data => { if (data.success) updateUI(data); });
        }

        function updateUI(data) {
            document.getElementById('stat-total').textContent = data.stats.totalEmployees;
            document.getElementById('stat-active').textContent = data.stats.activeEmployees;
            document.getElementById('stat-leave').textContent = data.stats.onLeave;
            document.getElementById('stat-kebeles').textContent = data.stats.totalKebeles;

            // Kebele Analytics Table
            const kaBody = document.getElementById('kebelePerformanceBody');
            kaBody.innerHTML = '';
            (data.kebele_analytics || []).forEach(ka => {
                const ratio = ka.total > 0 ? Math.round((ka.active / ka.total) * 100) : 0;
                kaBody.innerHTML += `
                    <tr style="border-bottom: 1px solid #f8fafc;">
                        <td style="padding: 12px 25px; font-weight: 700; color: var(--primary); font-size: 0.85rem;">
                            <i class="fas fa-unit" style="margin-right:8px; color:var(--secondary);"></i>${ka.kebele || 'Woreda Office'}
                        </td>
                        <td style="padding: 12px 25px; text-align: center; font-weight: 800; color: var(--text-main); font-size: 0.85rem;">${ka.total}</td>
                        <td style="padding: 12px 25px; text-align: center; font-weight: 700; color: #10b981; font-size: 0.85rem;">${ka.active}</td>
                        <td style="padding: 12px 25px; text-align: center; font-weight: 700; color: #ef4444; font-size: 0.85rem;">${ka.on_leave}</td>
                        <td style="padding: 12px 25px; text-align: center;">
                            <div style="width:100px; height:6px; background:#f1f5f9; border-radius:10px; margin: 0 auto; overflow:hidden;">
                                <div style="width:${ratio}%; height:100%; background:var(--secondary);"></div>
                            </div>
                            <small style="font-size:0.65rem; font-weight:800; color:var(--text-muted);">${ratio}% Active</small>
                        </td>
                    </tr>
                `;
            });

            // Employee Table
            const body = document.getElementById('staffBody');
            body.innerHTML = '';
            (data.recent_employees || []).forEach(emp => {
                const initials = ((emp.first_name?.[0] || '') + (emp.last_name?.[0] || '')).toUpperCase();
                body.innerHTML += `
                    <tr style="border-bottom: 1px solid #f8fafc;" data-kebele="${emp.kebele || ''}">
                        <td style="padding: 15px 25px;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div class="avatar-circle">${initials}</div>
                                <div>
                                    <div style="font-weight:700; color:var(--primary); font-size:0.9rem;">${emp.first_name} ${emp.last_name}</div>
                                    <div style="font-size:0.75rem; color:var(--text-muted); font-weight:600;">${emp.position}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 15px 25px; font-family:monospace; font-weight:600; font-size:0.85rem;">${emp.employee_id}</td>
                        <td style="padding: 15px 25px; font-weight:700; color:var(--primary); font-size:0.85rem;">${emp.kebele || 'Woreda Office'}</td>
                        <td style="padding: 15px 25px;"><span style="padding:4px 10px; border-radius:6px; font-size:0.7rem; font-weight:800; text-transform:uppercase; background:${emp.status === 'active' ? '#dcfce7' : '#fee2e2'}; color:${emp.status === 'active' ? '#166534' : '#991b1b'};">${emp.status}</span></td>
                        <td style="padding: 15px 25px; text-align:center;"><button onclick="viewProfile('${emp.employee_id}')" style="background:var(--secondary); color:white; border:none; padding:8px 12px; border-radius:8px; cursor:pointer;"><i class="fas fa-eye"></i></button></td>
                    </tr>
                `;
            });

            const leaveList = document.getElementById('pendingLeavesList');
            if (data.leave_requests.length > 0) {
                leaveList.innerHTML = '';
                data.leave_requests.forEach(req => {
                    const initials = (req.first_name[0] || '') + (req.last_name[0] || '');
                    leaveList.innerHTML += `<div style="padding:15px 25px; border-bottom:1px solid #f8fafc; display:flex; align-items:center; gap:12px;"><div class="avatar-circle">${initials}</div><div style="flex:1;"><div style="font-weight:700; font-size:0.85rem;">${req.first_name} ${req.last_name}</div><div style="font-size:0.75rem; color:var(--text-muted);">${req.leave_type} - ${req.kebele}</div></div><div style="font-size:0.65rem; font-weight:800; background:#fff1f2; color:#be123c; padding:3px 8px; border-radius:10px;">PENDING</div></div>`;
                });
            }

            const feed = document.getElementById('activityFeed');
            feed.innerHTML = '';
            data.activities.forEach(act => {
                feed.innerHTML += `<div style="margin-bottom:15px;"><div style="font-size:0.75rem; color:var(--text-muted); font-weight:700;">${act.time}</div><div style="font-weight:800; color:var(--primary); font-size:0.9rem;">${act.title}</div><div style="font-size:0.8rem; color:var(--text-muted); font-weight:600;">${act.desc}</div></div>`;
            });
        }

        function filterList() {
            const search = document.getElementById('sFilter').value.toLowerCase();
            const kebele = document.getElementById('kFilter').value;
            const rows = document.querySelectorAll('#staffTable tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const rKebele = row.getAttribute('data-kebele');
                row.style.display = (text.includes(search) && (!kebele || rKebele === kebele)) ? '' : 'none';
            });
        }

        function viewProfile(id) {
            document.getElementById('sideOverlay').classList.add('active');
            document.getElementById('detailPanel').classList.add('open');
            document.getElementById('panelContent').innerHTML = '<div style="text-align:center; padding:50px;"><i class="fas fa-spinner fa-spin" style="font-size:2rem; color:var(--secondary);"></i></div>';
            fetch(`../wereda_hr/get_employee_detail.php?employee_id=${id}`).then(r => r.json()).then(data => { if (data.success) renderPanel(data.employee); });
        }

        function closeSidePanel() { document.getElementById('sideOverlay').classList.remove('active'); document.getElementById('detailPanel').classList.remove('open'); }

        function renderPanel(emp) {
            const initials = ((emp.first_name?.[0] || '') + (emp.last_name?.[0] || '')).toUpperCase();
            document.getElementById('panelContent').innerHTML = `
                <div style="background: linear-gradient(135deg, var(--primary) 0%, #2a6e8c 100%); color:white; padding:40px; border-radius:20px; text-align:center; margin-bottom:30px; position:relative;">
                    <button onclick="window.location.href='../wereda_hr/edit_employee.php?id=${emp.employee_id}'" style="position:absolute; top:15px; right:15px; background:rgba(255,255,255,0.2); border:none; color:white; padding:8px; border-radius:8px; cursor:pointer;"><i class="fas fa-edit"></i></button>
                    <div style="width:80px; height:80px; background:rgba(255,255,255,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2rem; margin:0 auto 15px; border:3px solid white;">${initials}</div>
                    <h3 style="margin:0; font-size:1.4rem;">${emp.first_name} ${emp.last_name}</h3>
                    <p style="margin:5px 0; opacity:0.8; font-weight:600;">${emp.position}</p>
                    <div style="margin-top:10px; display:inline-block; background:rgba(255,255,255,0.2); padding:4px 12px; border-radius:20px; font-size:0.75rem;">${emp.employee_id}</div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                    <div style="background:#f8fafc; padding:15px; border-radius:12px; border:1px solid #edf2f7;"><small style="font-weight:800; color:var(--text-muted); font-size:0.6rem; text-transform:uppercase;">Kebele</small><div style="font-weight:700; color:var(--primary);">${emp.kebele || 'Woreda'}</div></div>
                    <div style="background:#f8fafc; padding:15px; border-radius:12px; border:1px solid #edf2f7;"><small style="font-weight:800; color:var(--text-muted); font-size:0.6rem; text-transform:uppercase;">Contact</small><div style="font-weight:700; color:var(--primary);">${emp.phone_number || 'N/A'}</div></div>
                    <div style="background:#f8fafc; padding:15px; border-radius:12px; border:1px solid #edf2f7; grid-column:span 2;"><small style="font-weight:800; color:var(--text-muted); font-size:0.6rem; text-transform:uppercase;">Unit</small><div style="font-weight:700; color:var(--primary);">${emp.department_assigned || 'N/A'}</div></div>
                </div>
            `;
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadFullDashboard();
            setInterval(() => { document.getElementById('currentTime').textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }); }, 60000);
            new Chart(document.getElementById('kebeleChart'), { type: 'bar', data: { labels: <?php echo json_encode($kebele_labels); ?>, datasets: [{ label: 'Staff Count', data: <?php echo json_encode($kebele_counts); ?>, backgroundColor: 'rgba(26, 74, 95, 0.7)', borderRadius: 5 }] }, options: { maintainAspectRatio: false, plugins: { legend: { display: false } } } });
            new Chart(document.getElementById('roleChart'), { type: 'doughnut', data: { labels: <?php echo json_encode($pos_labels); ?>, datasets: [{ data: <?php echo json_encode($pos_counts); ?>, backgroundColor: ['#1a4a5f', '#4cb5ae', '#ff7e5f', '#f59e0b', '#3b82f6'], borderWidth: 0 }] }, options: { maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } } } });
        });
    </script>
</body>

</html>