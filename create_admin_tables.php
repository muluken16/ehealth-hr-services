<?php
include 'db.php';

$conn = getDBConnection();

// Audit logs table
$sql = "CREATE TABLE IF NOT EXISTS audit_logs (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(6) UNSIGNED,
    action VARCHAR(255) NOT NULL,
    table_name VARCHAR(100),
    record_id INT(6) UNSIGNED,
    old_values TEXT,
    new_values TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "✅ Audit logs table created successfully.<br>";
} else {
    echo "❌ Error creating audit logs table: " . $conn->error . "<br>";
}

// System settings table
$sql = "CREATE TABLE IF NOT EXISTS system_settings (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    updated_by INT(6) UNSIGNED,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "✅ System settings table created successfully.<br>";
} else {
    echo "❌ Error creating system settings table: " . $conn->error . "<br>";
}

// Backup history table
$sql = "CREATE TABLE IF NOT EXISTS backup_history (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    backup_name VARCHAR(255) NOT NULL,
    backup_type ENUM('full', 'incremental', 'database', 'files') DEFAULT 'full',
    file_path VARCHAR(500),
    file_size BIGINT,
    status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
    created_by INT(6) UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    error_message TEXT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "✅ Backup history table created successfully.<br>";
} else {
    echo "❌ Error creating backup history table: " . $conn->error . "<br>";
}

// Insert default system settings
$settings = [
    ['site_name', 'eHealth Management System', 'string', 'Name of the system'],
    ['site_description', 'Comprehensive health management platform', 'string', 'Description of the system'],
    ['admin_email', 'admin@ehealth.et', 'string', 'Administrator email'],
    ['maintenance_mode', 'false', 'boolean', 'Enable maintenance mode'],
    ['max_login_attempts', '5', 'integer', 'Maximum login attempts before lockout'],
    ['session_timeout', '3600', 'integer', 'Session timeout in seconds'],
    ['backup_retention_days', '30', 'integer', 'Number of days to retain backups'],
    ['email_notifications', 'true', 'boolean', 'Enable email notifications'],
    ['auto_backup', 'true', 'boolean', 'Enable automatic backups'],
    ['backup_frequency', 'daily', 'string', 'Backup frequency (daily, weekly, monthly)']
];

foreach ($settings as $setting) {
    $stmt = $conn->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $setting[0], $setting[1], $setting[2], $setting[3]);
    if ($stmt->execute()) {
        echo "✅ Setting '{$setting[0]}' inserted.<br>";
    } else {
        echo "❌ Error inserting setting '{$setting[0]}': " . $stmt->error . "<br>";
    }
    $stmt->close();
}

$conn->close();
echo "Admin tables creation completed.";
?>