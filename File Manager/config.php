<?php
require_once '../db.php';

/**
 * File Manager Configuration Class
 * Handles all configuration management for the File Manager system
 */
class FileManagerConfig {
    private $conn;
    private $cache = [];
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Get configuration value
     */
    public function get($key, $module = 'global', $default = null) {
        $cacheKey = $module . '.' . $key;
        
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        $stmt = $this->conn->prepare("SELECT config_value FROM file_config WHERE config_key = ? AND module = ?");
        $stmt->bind_param("ss", $key, $module);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $value = $row['config_value'];
            $this->cache[$cacheKey] = $value;
            $stmt->close();
            return $value;
        }
        
        $stmt->close();
        return $default;
    }
    
    /**
     * Set configuration value
     */
    public function set($key, $value, $module = 'global', $description = '', $userId = 1) {
        $stmt = $this->conn->prepare("
            INSERT INTO file_config (config_key, config_value, module, description, updated_by) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            config_value = VALUES(config_value),
            description = VALUES(description),
            updated_by = VALUES(updated_by),
            updated_date = CURRENT_TIMESTAMP
        ");
        
        $stmt->bind_param("ssssi", $key, $value, $module, $description, $userId);
        $success = $stmt->execute();
        $stmt->close();
        
        if ($success) {
            // Clear cache
            $cacheKey = $module . '.' . $key;
            unset($this->cache[$cacheKey]);
        }
        
        return $success;
    }
    
    /**
     * Get allowed file types as array
     */
    public function getAllowedFileTypes($module = 'global') {
        $types = $this->get('allowed_file_types', $module, 'pdf,jpg,jpeg,png,doc,docx');
        return array_map('trim', explode(',', strtolower($types)));
    }
    
    /**
     * Get maximum file size in bytes
     */
    public function getMaxFileSize($module = 'global') {
        return (int) $this->get('max_file_size', $module, 10485760); // 10MB default
    }
    
    /**
     * Get storage quota per entity in bytes
     */
    public function getStorageQuota($module = 'global') {
        return (int) $this->get('storage_quota_per_entity', $module, 104857600); // 100MB default
    }
    
    /**
     * Check if file type is allowed
     */
    public function isFileTypeAllowed($filename, $module = 'global') {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowedTypes = $this->getAllowedFileTypes($module);
        return in_array($extension, $allowedTypes);
    }
    
    /**
     * Check if file size is within limits
     */
    public function isFileSizeAllowed($fileSize, $module = 'global') {
        $maxSize = $this->getMaxFileSize($module);
        return $fileSize <= $maxSize;
    }
    
    /**
     * Format file size for display
     */
    public function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Get file categories for entity type
     */
    public function getFileCategories($entityType) {
        $categories = [
            'employee' => ['personal', 'banking', 'education', 'criminal', 'warranty', 'leave', 'documents'],
            'patient' => ['medical', 'insurance', 'emergency', 'documents'],
            'payroll' => ['payslips', 'tax', 'benefits', 'documents'],
            'recruitment' => ['applications', 'interviews', 'contracts', 'documents'],
            'training' => ['materials', 'certificates', 'evaluations', 'documents'],
            'emergency' => ['reports', 'responses', 'media', 'documents'],
            'quality' => ['assessments', 'reports', 'certifications', 'documents'],
            'system' => ['backups', 'logs', 'configurations', 'documents']
        ];
        
        return $categories[$entityType] ?? ['documents'];
    }
    
    /**
     * Generate unique system filename
     */
    public function generateSystemFilename($entityType, $entityId, $category, $originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $timestamp = time();
        $random = substr(md5(uniqid(rand(), true)), 0, 8);
        
        return strtoupper($entityType) . '-' . $entityId . '_' . $category . '_' . $timestamp . '_' . $random . '.' . $extension;
    }
    
    /**
     * Get entity folder path
     */
    public function getEntityFolderPath($entityType, $entityId, $category = null) {
        $basePath = '../files/' . $entityType . '/' . $entityId;
        
        if ($category) {
            $basePath .= '/' . $category;
        }
        
        return $basePath;
    }
    
    /**
     * Create entity folder structure
     */
    public function createEntityFolders($entityType, $entityId) {
        $categories = $this->getFileCategories($entityType);
        $created = [];
        
        foreach ($categories as $category) {
            $folderPath = $this->getEntityFolderPath($entityType, $entityId, $category);
            
            if (!file_exists($folderPath)) {
                if (mkdir($folderPath, 0755, true)) {
                    $created[] = $category;
                }
            }
        }
        
        return $created;
    }
}

// Global configuration instance
$fileConfig = new FileManagerConfig($conn);

// File Manager Constants
define('FM_UPLOAD_SUCCESS', 1);
define('FM_UPLOAD_ERROR_TYPE', 2);
define('FM_UPLOAD_ERROR_SIZE', 3);
define('FM_UPLOAD_ERROR_PERMISSION', 4);
define('FM_UPLOAD_ERROR_STORAGE', 5);
define('FM_UPLOAD_ERROR_SYSTEM', 6);

define('FM_ACCESS_GRANTED', 1);
define('FM_ACCESS_DENIED_PERMISSION', 2);
define('FM_ACCESS_DENIED_GEOGRAPHIC', 3);
define('FM_ACCESS_DENIED_FILE_NOT_FOUND', 4);

// File operation result codes
define('FM_OPERATION_SUCCESS', 1);
define('FM_OPERATION_ERROR_PERMISSION', 2);
define('FM_OPERATION_ERROR_NOT_FOUND', 3);
define('FM_OPERATION_ERROR_SYSTEM', 4);
?>