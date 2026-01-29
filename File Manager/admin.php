<?php
session_start();
require_once '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$userRole = $stmt->get_result()->fetch_assoc()['role'];
$stmt->close();

if ($userRole !== 'admin') {
    header('Location: index.php');
    exit;
}

// Handle configuration updates
if ($_POST && isset($_POST['action'])) {
    require_once 'config.php';
    $fileConfig = new FileManagerConfig($conn);
    
    if ($_POST['action'] === 'update_config') {
        $configs = [
            'allowed_file_types' => $_POST['allowed_file_types'] ?? '',
            'max_file_size' => $_POST['max_file_size'] ?? '',
            'storage_quota_per_entity' => $_POST['storage_quota_per_entity'] ?? '',
            'retention_days' => $_POST['retention_days'] ?? '',
            'auto_archive_enabled' => $_POST['auto_archive_enabled'] ?? '0',
            'preview_enabled' => $_POST['preview_enabled'] ?? '0',
            'share_expiry_default_days' => $_POST['share_expiry_default_days'] ?? ''
        ];
        
        $updated = 0;
        foreach ($configs as $key => $value) {
            if ($fileConfig->set($key, $value, 'global', '', $_SESSION['user_id'])) {
                $updated++;
            }
        }
        
        $message = "Updated $updated configuration settings";
        $messageType = 'success';
    }
}

// Load current configuration
require_once 'config.php';
$fileConfig = new FileManagerConfig($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager Administration - HealthFirst</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .config-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .activity-log {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .log-entry {
            border-left: 4px solid #667eea;
            padding: 0.5rem 1rem;
            margin-bottom: 0.5rem;
            background: #f8f9fa;
        }
        
        .log-entry.error {
            border-left-color: #dc3545;
        }
        
        .log-entry.warning {
            border-left-color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><i class="fas fa-cogs me-3"></i>File Manager Administration</h1>
                    <p class="mb-0">System configuration and monitoring</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="index.php" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to File Manager
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-4">
        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- System Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-number" id="total-files">0</div>
                    <div class="text-muted">Total Files</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-number" id="total-storage">0 GB</div>
                    <div class="text-muted">Storage Used</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-number" id="active-shares">0</div>
                    <div class="text-muted">Active Shares</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-number" id="total-users">0</div>
                    <div class="text-muted">Active Users</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Configuration Panel -->
            <div class="col-md-8">
                <div class="config-section">
                    <h3><i class="fas fa-sliders-h me-2"></i>System Configuration</h3>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="update_config">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Allowed File Types</label>
                                    <input type="text" class="form-control" name="allowed_file_types" 
                                           value="<?php echo htmlspecialchars($fileConfig->get('allowed_file_types', 'global', 'pdf,jpg,jpeg,png,doc,docx')); ?>"
                                           placeholder="pdf,jpg,jpeg,png,doc,docx">
                                    <small class="text-muted">Comma-separated list of allowed file extensions</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Max File Size (bytes)</label>
                                    <input type="number" class="form-control" name="max_file_size" 
                                           value="<?php echo $fileConfig->get('max_file_size', 'global', '10485760'); ?>">
                                    <small class="text-muted">Maximum file size in bytes (10MB = 10485760)</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Storage Quota per Entity (bytes)</label>
                                    <input type="number" class="form-control" name="storage_quota_per_entity" 
                                           value="<?php echo $fileConfig->get('storage_quota_per_entity', 'global', '104857600'); ?>">
                                    <small class="text-muted">Storage limit per entity (100MB = 104857600)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Retention Period (days)</label>
                                    <input type="number" class="form-control" name="retention_days" 
                                           value="<?php echo $fileConfig->get('retention_days', 'global', '2555'); ?>">
                                    <small class="text-muted">How long to keep files (7 years = 2555 days)</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="auto_archive_enabled" value="1"
                                               <?php echo $fileConfig->get('auto_archive_enabled', 'global', '1') ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Auto Archive Old Files</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="preview_enabled" value="1"
                                               <?php echo $fileConfig->get('preview_enabled', 'global', '1') ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Enable File Preview</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Default Share Expiry (days)</label>
                                    <input type="number" class="form-control" name="share_expiry_default_days" 
                                           value="<?php echo $fileConfig->get('share_expiry_default_days', 'global', '30'); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Configuration
                        </button>
                    </form>
                </div>

                <!-- File Management Tools -->
                <div class="config-section">
                    <h3><i class="fas fa-tools me-2"></i>Management Tools</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary" onclick="cleanupExpiredShares()">
                                    <i class="fas fa-broom me-2"></i>Cleanup Expired Shares
                                </button>
                                <button class="btn btn-outline-warning" onclick="archiveOldFiles()">
                                    <i class="fas fa-archive me-2"></i>Archive Old Files
                                </button>
                                <button class="btn btn-outline-info" onclick="generateStorageReport()">
                                    <i class="fas fa-chart-pie me-2"></i>Generate Storage Report
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-success" onclick="exportAuditLog()">
                                    <i class="fas fa-download me-2"></i>Export Audit Log
                                </button>
                                <button class="btn btn-outline-secondary" onclick="validateFileIntegrity()">
                                    <i class="fas fa-shield-alt me-2"></i>Validate File Integrity
                                </button>
                                <button class="btn btn-outline-danger" onclick="emergencyCleanup()">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Emergency Cleanup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Monitor -->
            <div class="col-md-4">
                <div class="config-section">
                    <h3><i class="fas fa-activity me-2"></i>Recent Activity</h3>
                    <div class="activity-log" id="activity-log">
                        <div class="text-center py-3">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p class="mt-2">Loading activity...</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="config-section">
                    <h3><i class="fas fa-chart-line me-2"></i>Quick Stats</h3>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h4 text-primary" id="uploads-today">0</div>
                                <small class="text-muted">Uploads Today</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h4 text-success" id="downloads-today">0</div>
                            <small class="text-muted">Downloads Today</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h4 text-warning" id="shares-active">0</div>
                                <small class="text-muted">Active Shares</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h4 text-danger" id="shares-expired">0</div>
                            <small class="text-muted">Expired Shares</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load admin dashboard data
        async function loadAdminData() {
            try {
                const response = await fetch('api/admin_stats.php');
                const data = await response.json();
                
                if (data.success) {
                    // Update main stats
                    document.getElementById('total-files').textContent = data.stats.total_files;
                    document.getElementById('total-storage').textContent = formatBytes(data.stats.total_storage);
                    document.getElementById('active-shares').textContent = data.stats.active_shares;
                    document.getElementById('total-users').textContent = data.stats.total_users;
                    
                    // Update quick stats
                    document.getElementById('uploads-today').textContent = data.stats.uploads_today;
                    document.getElementById('downloads-today').textContent = data.stats.downloads_today;
                    document.getElementById('shares-active').textContent = data.stats.shares_active;
                    document.getElementById('shares-expired').textContent = data.stats.shares_expired;
                    
                    // Load activity log
                    loadActivityLog(data.recent_activity);
                }
            } catch (error) {
                console.error('Error loading admin data:', error);
            }
        }
        
        function loadActivityLog(activities) {
            const container = document.getElementById('activity-log');
            
            if (!activities || activities.length === 0) {
                container.innerHTML = '<div class="text-center py-3 text-muted">No recent activity</div>';
                return;
            }
            
            const html = activities.map(activity => `
                <div class="log-entry ${activity.success ? '' : 'error'}">
                    <div class="d-flex justify-content-between">
                        <strong>${activity.action_type}</strong>
                        <small class="text-muted">${formatDate(activity.timestamp)}</small>
                    </div>
                    <div class="small text-muted">
                        User: ${activity.user_name} | File: ${activity.file_name || 'N/A'}
                    </div>
                </div>
            `).join('');
            
            container.innerHTML = html;
        }
        
        // Management tool functions
        async function cleanupExpiredShares() {
            if (!confirm('This will remove all expired file shares. Continue?')) return;
            
            try {
                const response = await fetch('api/admin_tools.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'cleanup_expired_shares' })
                });
                
                const data = await response.json();
                alert(data.message);
                
                if (data.success) {
                    loadAdminData();
                }
            } catch (error) {
                alert('Error performing cleanup');
            }
        }
        
        async function archiveOldFiles() {
            if (!confirm('This will archive files older than the retention period. Continue?')) return;
            
            try {
                const response = await fetch('api/admin_tools.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'archive_old_files' })
                });
                
                const data = await response.json();
                alert(data.message);
                
                if (data.success) {
                    loadAdminData();
                }
            } catch (error) {
                alert('Error performing archive');
            }
        }
        
        async function generateStorageReport() {
            try {
                const response = await fetch('api/admin_tools.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'generate_storage_report' })
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'storage_report.csv';
                    a.click();
                    window.URL.revokeObjectURL(url);
                } else {
                    alert('Error generating report');
                }
            } catch (error) {
                alert('Error generating report');
            }
        }
        
        async function exportAuditLog() {
            try {
                const response = await fetch('api/admin_tools.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'export_audit_log' })
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'audit_log.csv';
                    a.click();
                    window.URL.revokeObjectURL(url);
                } else {
                    alert('Error exporting audit log');
                }
            } catch (error) {
                alert('Error exporting audit log');
            }
        }
        
        // Utility functions
        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString();
        }
        
        // Load data on page load
        document.addEventListener('DOMContentLoaded', loadAdminData);
        
        // Refresh data every 30 seconds
        setInterval(loadAdminData, 30000);
    </script>
</body>
</html>