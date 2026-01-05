<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.html');
    exit();
}
include 'db.php';
$conn = getDBConnection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | System Administration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <div class="hr-container">
        <?php include 'admin_sidebar.php'; ?>

        <?php
        $page_title = 'System Administration';
        include 'admin_navbar.php';
        ?>

        <!-- Admin Dashboard -->
        <div class="hr-dashboard">
            <!-- Admin Stats -->
            <div class="hr-stats">
                <div class="hr-stat-card users">
                    <div class="hr-stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="hr-stat-info">
                        <?php
                        $count = executeSafeQuery($conn, "SELECT COUNT(*) as count FROM users");
                        echo "<h3>$count</h3>";
                        ?>
                        <p>Total Users</p>
                    </div>
                </div>

                <div class="hr-stat-card active-sessions">
                    <div class="hr-stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="hr-stat-info">
                        <h3>24</h3>
                        <p>Active Sessions</p>
                    </div>
                </div>

                <div class="hr-stat-card system-health">
                    <div class="hr-stat-icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div class="hr-stat-info">
                        <h3>98%</h3>
                        <p>System Health</p>
                    </div>
                </div>

                <div class="hr-stat-card backups">
                    <div class="hr-stat-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="hr-stat-info">
                        <?php
                        // Check if table exists first as backup_history is an optional admin table
                        $count = executeSafeQuery($conn, "SELECT COUNT(*) as count FROM backup_history WHERE status = 'completed'");
                        echo "<h3>$count</h3>";
                        ?>
                        <p>Successful Backups</p>
                    </div>
                </div>
            </div>

            <!-- User Management Section -->
            <div class="hr-section" id="userManagementSection">
                <div class="hr-section-header">
                    <h2 class="hr-section-title">User Management</h2>
                    <div class="hr-section-actions">
                        <button class="section-action-btn" id="addUserBtn">
                            <i class="fas fa-plus"></i> Add User
                        </button>
                        <button class="section-action-btn">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button class="section-action-btn">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                <div class="hr-section-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Last Login</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = $conn->query("SELECT id, name, email, role, last_login, created_at FROM users ORDER BY created_at DESC");
                                if ($result && $result->num_rows > 0) {
                                    while ($user = $result->fetch_assoc()) {
                                        $role_names = [
                                            'admin' => 'Administrator',
                                            'zone_health_officer' => 'Zone Health Officer',
                                            'zone_hr' => 'Zone HR Officer',
                                            'wereda_health_officer' => 'Wereda Health Officer',
                                            'wereda_hr' => 'Wereda HR Officer',
                                            'kebele_health_officer' => 'Kebele Health Officer',
                                            'kebele_hr' => 'Kebele HR Officer'
                                        ];
                                        $role_display = $role_names[$user['role']] ?? $user['role'];
                                        $last_login = $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Never';
                                        echo "<tr>
                                            <td>
                                                <div class='employee-info'>
                                                    <div class='employee-avatar'>" . strtoupper(substr($user['name'] ?? 'U', 0, 2)) . "</div>
                                                    <div>
                                                        <div class='employee-name'>{$user['name']}</div>
                                                        <div class='employee-id'>ID: {$user['id']}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{$user['email']}</td>
                                            <td><span class='department-badge admin'>{$role_display}</span></td>
                                            <td>{$last_login}</td>
                                            <td><span class='status-badge active'>Active</span></td>
                                            <td>
                                                <div class='action-buttons'>
                                                    <button class='action-btn edit' onclick='editUser({$user['id']})'><i class='fas fa-edit'></i></button>
                                                    <button class='action-btn delete' onclick='deleteUser({$user['id']})'><i class='fas fa-trash'></i></button>
                                                </div>
                                            </td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>No users found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- System Settings Section -->
            <div class="hr-section" id="systemSettingsSection">
                <div class="hr-section-header">
                    <h2 class="hr-section-title">System Settings</h2>
                    <div class="hr-section-actions">
                        <button class="section-action-btn" id="saveSettingsBtn">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </div>
                <div class="hr-section-body">
                    <form id="systemSettingsForm">
                        <?php
                        $settings_result = $conn->query("SELECT * FROM system_settings ORDER BY setting_key");
                        if ($settings_result && $settings_result->num_rows > 0) {
                            while ($setting = $settings_result->fetch_assoc()) {
                                $input_type = 'text';
                                if ($setting['setting_type'] == 'boolean') {
                                    $input_type = 'checkbox';
                                } elseif ($setting['setting_type'] == 'integer') {
                                    $input_type = 'number';
                                }
                                $checked = ($setting['setting_type'] == 'boolean' && $setting['setting_value'] == 'true') ? 'checked' : '';
                                $value = ($setting['setting_type'] == 'boolean') ? 'true' : $setting['setting_value'];
                                echo "<div class='form-group'>
                                    <label for='{$setting['setting_key']}'>{$setting['setting_key']}</label>";
                                if ($input_type == 'checkbox') {
                                    echo "<input type='checkbox' id='{$setting['setting_key']}' name='{$setting['setting_key']}' value='true' $checked>";
                                } else {
                                    echo "<input type='$input_type' id='{$setting['setting_key']}' name='{$setting['setting_key']}' value='$value'>";
                                }
                                echo "<small class='form-help'>{$setting['description']}</small>
                                </div>";
                            }
                        } else {
                            echo "<div class='alert' style='padding:15px; background:#fef3c7; border:1px solid #f59e0b; border-radius:6px; margin:10px 0;'>
                                <i class='fas fa-exclamation-triangle'></i> Settings table not initialized. <a href='create_admin_tables.php' style='color:#b45309; text-decoration:underline;'>Set up admin tables</a>
                            </div>";
                        }
                        ?>
                    </form>
                </div>
            </div>

            <!-- Audit Logs Section -->
            <div class="hr-section" id="auditLogsSection">
                <div class="hr-section-header">
                    <h2 class="hr-section-title">Audit Logs</h2>
                    <div class="hr-section-actions">
                        <button class="section-action-btn">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button class="section-action-btn">
                            <i class="fas fa-download"></i> Export Logs
                        </button>
                    </div>
                </div>
                <div class="hr-section-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Table</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $logs_result = $conn->query("SELECT al.*, u.name FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 50");
                                while ($log = $logs_result->fetch_assoc()) {
                                    $timestamp = date('d/m/Y H:i:s', strtotime($log['created_at']));
                                    echo "<tr>
                                        <td>{$timestamp}</td>
                                        <td>{$log['name']}</td>
                                        <td>{$log['action']}</td>
                                        <td>{$log['table_name']}</td>
                                        <td>{$log['ip_address']}</td>
                                    </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Backup & Restore Section -->
            <div class="hr-section" id="backupRestoreSection">
                <div class="hr-section-header">
                    <h2 class="hr-section-title">Backup & Restore</h2>
                    <div class="hr-section-actions">
                        <button class="section-action-btn" id="createBackupBtn">
                            <i class="fas fa-plus"></i> Create Backup
                        </button>
                        <button class="section-action-btn">
                            <i class="fas fa-upload"></i> Restore
                        </button>
                    </div>
                </div>
                <div class="hr-section-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Backup Name</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Size</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $backup_result = $conn->query("SELECT bh.*, u.name FROM backup_history bh LEFT JOIN users u ON bh.created_by = u.id ORDER BY bh.created_at DESC");
                                while ($backup = $backup_result->fetch_assoc()) {
                                    $created = date('d/m/Y H:i', strtotime($backup['created_at']));
                                    $size = $backup['file_size'] ? number_format($backup['file_size'] / 1024 / 1024, 2) . ' MB' : 'N/A';
                                    $status_class = $backup['status'] == 'completed' ? 'active' : ($backup['status'] == 'failed' ? 'inactive' : 'on-leave');
                                    echo "<tr>
                                        <td>{$backup['backup_name']}</td>
                                        <td>{$backup['backup_type']}</td>
                                        <td><span class='status-badge $status_class'>{$backup['status']}</span></td>
                                        <td>{$created}</td>
                                        <td>{$size}</td>
                                        <td>
                                            <div class='action-buttons'>
                                                <button class='action-btn view'><i class='fas fa-download'></i></button>
                                                <button class='action-btn delete'><i class='fas fa-trash'></i></button>
                                            </div>
                                        </td>
                                    </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- System Monitoring Section -->
            <div class="hr-section" id="systemMonitoringSection">
                <div class="hr-section-header">
                    <h2 class="hr-section-title">System Monitoring</h2>
                    <div class="hr-section-actions">
                        <button class="section-action-btn">
                            <i class="fas fa-refresh"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="hr-section-body">
                    <div class="monitoring-grid">
                        <div class="monitor-card">
                            <h3>Server Information</h3>
                            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                            <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
                            <p><strong>Database:</strong> MySQL</p>
                        </div>
                        <div class="monitor-card">
                            <h3>Database Health</h3>
                            <p><strong>Status:</strong> <span style="color: green;">Healthy</span></p>
                            <p><strong>Connections:</strong> <?php echo $conn->stat(); ?></p>
                        </div>
                        <div class="monitor-card">
                            <h3>Disk Usage</h3>
                            <p><strong>Free Space:</strong> <?php echo number_format(disk_free_space("/") / 1024 / 1024 / 1024, 2); ?> GB</p>
                            <p><strong>Total Space:</strong> <?php echo number_format(disk_total_space("/") / 1024 / 1024 / 1024, 2); ?> GB</p>
                        </div>
                        <div class="monitor-card">
                            <h3>Memory Usage</h3>
                            <p><strong>Peak Usage:</strong> <?php echo number_format(memory_get_peak_usage() / 1024 / 1024, 2); ?> MB</p>
                            <p><strong>Current Usage:</strong> <?php echo number_format(memory_get_usage() / 1024 / 1024, 2); ?> MB</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reports Section -->
            <div class="hr-section" id="reportsSection">
                <div class="hr-section-header">
                    <h2 class="hr-section-title">Reports</h2>
                    <div class="hr-section-actions">
                        <button class="section-action-btn" id="generateUserReportBtn">
                            <i class="fas fa-users"></i> User Report
                        </button>
                        <button class="section-action-btn" id="generateActivityReportBtn">
                            <i class="fas fa-chart-bar"></i> Activity Report
                        </button>
                    </div>
                </div>
                <div class="hr-section-body">
                    <div class="reports-grid">
                        <div class="report-card">
                            <h3>User Statistics</h3>
                            <p>Total Users: <?php echo $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count']; ?></p>
                            <p>Active Users: <?php echo $conn->query("SELECT COUNT(*) as count FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['count']; ?></p>
                        </div>
                        <div class="report-card">
                            <h3>System Activity</h3>
                            <p>Total Log Entries: <?php echo $conn->query("SELECT COUNT(*) as count FROM audit_logs")->fetch_assoc()['count']; ?></p>
                            <p>Today's Activities: <?php echo $conn->query("SELECT COUNT(*) as count FROM audit_logs WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Quick Actions -->
            <div class="hr-actions">
                <div class="hr-action-btn" id="createUserBtn">
                    <i class="fas fa-user-plus"></i>
                    <span>Create User</span>
                </div>
                <div class="hr-action-btn" id="systemSettingsBtn">
                    <i class="fas fa-cogs"></i>
                    <span>System Settings</span>
                </div>
                <div class="hr-action-btn" id="backupSystemBtn">
                    <i class="fas fa-database"></i>
                    <span>Backup System</span>
                </div>
                <div class="hr-action-btn" id="viewLogsBtn">
                    <i class="fas fa-history"></i>
                    <span>View Audit Logs</span>
                </div>
                <div class="hr-action-btn" id="generateReportBtn">
                    <i class="fas fa-file-pdf"></i>
                    <span>Generate Report</span>
                </div>
                <div class="hr-action-btn" id="systemMonitorBtn">
                    <i class="fas fa-chart-line"></i>
                    <span>System Monitor</span>
                </div>
            </div>