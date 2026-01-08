<?php
require_once '../db.php';

// Create File Manager database tables
echo "<h2>Creating File Manager Database Tables</h2>";

// Create files table
$sql = "CREATE TABLE IF NOT EXISTS files (
    file_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type ENUM('employee', 'patient', 'payroll', 'recruitment', 'training', 'emergency', 'quality', 'system') NOT NULL,
    entity_id VARCHAR(50) NOT NULL,
    category VARCHAR(50) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    system_name VARCHAR(255) NOT NULL UNIQUE,
    file_path VARCHAR(500) NOT NULL,
    file_size INT(11) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    uploaded_by INT(6) UNSIGNED NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'deleted', 'archived') DEFAULT 'active',
    geographic_scope ENUM('zone', 'wereda', 'kebele') NOT NULL,
    zone_id VARCHAR(50),
    wereda_id VARCHAR(50),
    kebele_id VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_geographic (geographic_scope, zone_id, wereda_id, kebele_id),
    INDEX idx_upload_date (upload_date)
)";

if ($conn->query($sql) === TRUE) {
    echo "✅ Files table created successfully.<br>";
} else {
    echo "❌ Error creating files table: " . $conn->error . "<br>";
}

// Create file_shares table
$sql = "CREATE TABLE IF NOT EXISTS file_shares (
    share_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_id INT(11) UNSIGNED NOT NULL,
    shared_by INT(6) UNSIGNED NOT NULL,
    shared_with INT(6) UNSIGNED,
    shared_with_role VARCHAR(50),
    permission_type ENUM('view', 'download') DEFAULT 'view',
    expiry_date DATETIME NULL,
    message TEXT,
    status ENUM('active', 'expired', 'revoked') DEFAULT 'active',
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    accessed_date TIMESTAMP NULL,
    access_count INT DEFAULT 0,
    FOREIGN KEY (file_id) REFERENCES files(file_id) ON DELETE CASCADE,
    FOREIGN KEY (shared_by) REFERENCES users(id),
    FOREIGN KEY (shared_with) REFERENCES users(id),
    INDEX idx_file_shares (file_id),
    INDEX idx_shared_with (shared_with),
    INDEX idx_status_expiry (status, expiry_date)
)";

if ($conn->query($sql) === TRUE) {
    echo "✅ File shares table created successfully.<br>";
} else {
    echo "❌ Error creating file_shares table: " . $conn->error . "<br>";
}

// Create file_audit_log table
$sql = "CREATE TABLE IF NOT EXISTS file_audit_log (
    log_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_id INT(11) UNSIGNED,
    user_id INT(6) UNSIGNED NOT NULL,
    action_type ENUM('upload', 'download', 'view', 'rename', 'delete', 'share', 'send', 'revoke', 'access_denied') NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    details JSON,
    geographic_location VARCHAR(200),
    success BOOLEAN DEFAULT TRUE,
    error_message TEXT,
    FOREIGN KEY (file_id) REFERENCES files(file_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_file_audit (file_id),
    INDEX idx_user_audit (user_id),
    INDEX idx_action_timestamp (action_type, timestamp),
    INDEX idx_timestamp (timestamp)
)";

if ($conn->query($sql) === TRUE) {
    echo "✅ File audit log table created successfully.<br>";
} else {
    echo "❌ Error creating file_audit_log table: " . $conn->error . "<br>";
}

// Create file_config table
$sql = "CREATE TABLE IF NOT EXISTS file_config (
    config_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT NOT NULL,
    module ENUM('global', 'employee', 'patient', 'payroll', 'recruitment', 'training', 'emergency', 'quality', 'system') DEFAULT 'global',
    description TEXT,
    updated_by INT(6) UNSIGNED NOT NULL,
    updated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id),
    INDEX idx_config_key (config_key),
    INDEX idx_module (module)
)";

if ($conn->query($sql) === TRUE) {
    echo "✅ File config table created successfully.<br>";
} else {
    echo "❌ Error creating file_config table: " . $conn->error . "<br>";
}

// Insert default configuration values
$defaultConfigs = [
    ['allowed_file_types', 'pdf,jpg,jpeg,png,doc,docx', 'global', 'Allowed file types for upload'],
    ['max_file_size', '10485760', 'global', 'Maximum file size in bytes (10MB)'],
    ['storage_quota_per_entity', '104857600', 'global', 'Storage quota per entity in bytes (100MB)'],
    ['retention_days', '2555', 'global', 'File retention period in days (7 years)'],
    ['auto_archive_enabled', '1', 'global', 'Enable automatic archiving of old files'],
    ['preview_enabled', '1', 'global', 'Enable file preview functionality'],
    ['share_expiry_default_days', '30', 'global', 'Default expiry for shared files in days']
];

foreach ($defaultConfigs as $config) {
    $checkConfig = $conn->prepare("SELECT config_id FROM file_config WHERE config_key = ?");
    $checkConfig->bind_param("s", $config[0]);
    $checkConfig->execute();
    $result = $checkConfig->get_result();
    
    if ($result->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO file_config (config_key, config_value, module, description, updated_by) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("ssss", $config[0], $config[1], $config[2], $config[3]);
        if ($stmt->execute()) {
            echo "✅ Default config '{$config[0]}' inserted.<br>";
        } else {
            echo "❌ Error inserting config '{$config[0]}': " . $stmt->error . "<br>";
        }
        $stmt->close();
    }
    $checkConfig->close();
}

// Create file storage directories
$baseDir = '../files';
$directories = [
    'employees',
    'patients', 
    'payroll',
    'recruitment',
    'training',
    'emergency',
    'quality',
    'system'
];

if (!file_exists($baseDir)) {
    if (mkdir($baseDir, 0755, true)) {
        echo "✅ Base files directory created.<br>";
    } else {
        echo "❌ Error creating base files directory.<br>";
    }
}

foreach ($directories as $dir) {
    $fullPath = $baseDir . '/' . $dir;
    if (!file_exists($fullPath)) {
        if (mkdir($fullPath, 0755, true)) {
            echo "✅ Directory '{$dir}' created.<br>";
        } else {
            echo "❌ Error creating directory '{$dir}'.<br>";
        }
    }
}

// Create .htaccess file for security
$htaccessContent = "# Deny direct access to files\nDeny from all\n\n# Allow only authorized access through PHP scripts\n<Files \"*.php\">\n    Allow from all\n</Files>";
$htaccessPath = $baseDir . '/.htaccess';

if (!file_exists($htaccessPath)) {
    if (file_put_contents($htaccessPath, $htaccessContent)) {
        echo "✅ Security .htaccess file created.<br>";
    } else {
        echo "❌ Error creating .htaccess file.<br>";
    }
}

echo "<br><h3>✅ File Manager database setup completed successfully!</h3>";
echo "<p>All tables and directories have been created with proper security measures.</p>";

$conn->close();
?>