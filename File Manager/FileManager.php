<?php
require_once '../db.php';
require_once 'config.php';

/**
 * Enhanced File Manager Class
 * Comprehensive file management system with advanced features
 */
class FileManager {
    private $conn;
    private $config;
    private $userId;
    private $userRole;
    private $userZone;
    private $userWereda;
    private $userKebele;
    
    public function __construct($userId) {
        $this->conn = getDBConnection();
        $this->config = new FileManagerConfig($this->conn);
        $this->userId = $userId;
        $this->loadUserInfo();
    }
    
    /**
     * Load user information for permission checks
     */
    private function loadUserInfo() {
        $stmt = $this->conn->prepare("SELECT role, zone, wereda, kebele FROM users WHERE id = ?");
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $this->userRole = $row['role'];
            $this->userZone = $row['zone'];
            $this->userWereda = $row['wereda'];
            $this->userKebele = $row['kebele'];
        }
        $stmt->close();
    }
    
    /**
     * Enhanced file upload with comprehensive validation
     */
    public function uploadFile($entityType, $entityId, $category, $uploadedFile, $metadata = []) {
        try {
            // Validate file
            $validation = $this->validateFile($uploadedFile, $entityType);
            if ($validation['code'] !== FM_UPLOAD_SUCCESS) {
                return $validation;
            }
            
            // Check permissions
            if (!$this->canUploadToEntity($entityType, $entityId)) {
                return [
                    'code' => FM_UPLOAD_ERROR_PERMISSION,
                    'message' => 'You do not have permission to upload files for this entity'
                ];
            }
            
            // Check storage quota
            if (!$this->checkStorageQuota($entityType, $entityId, $uploadedFile['size'])) {
                return [
                    'code' => FM_UPLOAD_ERROR_STORAGE,
                    'message' => 'Storage quota exceeded for this entity'
                ];
            }
            
            // Create entity folders if they don't exist
            $this->config->createEntityFolders($entityType, $entityId);
            
            // Generate system filename and path
            $systemName = $this->config->generateSystemFilename($entityType, $entityId, $category, $uploadedFile['name']);
            $folderPath = $this->config->getEntityFolderPath($entityType, $entityId, $category);
            $filePath = $folderPath . '/' . $systemName;
            
            // Move uploaded file
            if (!move_uploaded_file($uploadedFile['tmp_name'], $filePath)) {
                return [
                    'code' => FM_UPLOAD_ERROR_SYSTEM,
                    'message' => 'Failed to save file to storage'
                ];
            }
            
            // Generate file hash for integrity
            $fileHash = hash_file('sha256', $filePath);
            
            // Save file metadata to database
            $fileId = $this->saveFileMetadata([
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'category' => $category,
                'original_name' => $uploadedFile['name'],
                'system_name' => $systemName,
                'file_path' => $filePath,
                'file_size' => $uploadedFile['size'],
                'mime_type' => $uploadedFile['type'],
                'file_hash' => $fileHash,
                'metadata' => json_encode($metadata)
            ]);
            
            // Log upload action
            $this->logFileAction($fileId, 'upload', [
                'original_name' => $uploadedFile['name'],
                'file_size' => $uploadedFile['size'],
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'category' => $category
            ]);
            
            return [
                'code' => FM_UPLOAD_SUCCESS,
                'message' => 'File uploaded successfully',
                'file_id' => $fileId,
                'system_name' => $systemName
            ];
            
        } catch (Exception $e) {
            error_log("File upload error: " . $e->getMessage());
            return [
                'code' => FM_UPLOAD_ERROR_SYSTEM,
                'message' => 'System error during file upload'
            ];
        }
    }
    
    /**
     * Enhanced file validation
     */
    private function validateFile($file, $entityType) {
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return [
                'code' => FM_UPLOAD_ERROR_SYSTEM,
                'message' => 'No file was uploaded or upload error occurred'
            ];
        }
        
        // Check file type
        if (!$this->config->isFileTypeAllowed($file['name'], $entityType)) {
            $allowedTypes = implode(', ', $this->config->getAllowedFileTypes($entityType));
            return [
                'code' => FM_UPLOAD_ERROR_TYPE,
                'message' => "File type not allowed. Allowed types: {$allowedTypes}"
            ];
        }
        
        // Check file size
        if (!$this->config->isFileSizeAllowed($file['size'], $entityType)) {
            $maxSize = $this->config->formatFileSize($this->config->getMaxFileSize($entityType));
            return [
                'code' => FM_UPLOAD_ERROR_SIZE,
                'message' => "File size exceeds limit. Maximum allowed: {$maxSize}"
            ];
        }
        
        // Check for malicious files
        if ($this->isMaliciousFile($file)) {
            return [
                'code' => FM_UPLOAD_ERROR_TYPE,
                'message' => 'File appears to be malicious and cannot be uploaded'
            ];
        }
        
        return ['code' => FM_UPLOAD_SUCCESS];
    }
    
    /**
     * Check for potentially malicious files
     */
    private function isMaliciousFile($file) {
        // Check file content for suspicious patterns
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle) {
            $content = fread($handle, 1024); // Read first 1KB
            fclose($handle);
            
            // Look for suspicious patterns
            $maliciousPatterns = [
                '<?php', '<%', '<script', 'eval(', 'exec(', 'system(', 'shell_exec('
            ];
            
            foreach ($maliciousPatterns as $pattern) {
                if (stripos($content, $pattern) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Save file metadata to database
     */
    private function saveFileMetadata($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO files (
                entity_type, entity_id, category, original_name, system_name, 
                file_path, file_size, mime_type, uploaded_by, geographic_scope,
                zone_id, wereda_id, kebele_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $geographicScope = $this->determineGeographicScope();
        
        $stmt->bind_param(
            "ssssssissssss",
            $data['entity_type'],
            $data['entity_id'],
            $data['category'],
            $data['original_name'],
            $data['system_name'],
            $data['file_path'],
            $data['file_size'],
            $data['mime_type'],
            $this->userId,
            $geographicScope,
            $this->userZone,
            $this->userWereda,
            $this->userKebele
        );
        
        $stmt->execute();
        $fileId = $this->conn->insert_id;
        $stmt->close();
        
        return $fileId;
    }
    
    /**
     * Determine geographic scope based on user role
     */
    private function determineGeographicScope() {
        if (strpos($this->userRole, 'zone') !== false || $this->userRole === 'admin') {
            return 'zone';
        } elseif (strpos($this->userRole, 'wereda') !== false) {
            return 'wereda';
        } else {
            return 'kebele';
        }
    }
    
    /**
     * Check if user can upload to specific entity
     */
    private function canUploadToEntity($entityType, $entityId) {
        // Admin can upload anywhere
        if ($this->userRole === 'admin') {
            return true;
        }
        
        // For employee files, check if it's the user's own file
        if ($entityType === 'employee') {
            $stmt = $this->conn->prepare("SELECT zone, wereda, kebele FROM employees WHERE employee_id = ?");
            $stmt->bind_param("s", $entityId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return $this->hasGeographicAccess($row['zone'], $row['wereda'], $row['kebele']);
            }
            $stmt->close();
        }
        
        // For other entity types, check geographic scope
        return true; // Simplified for now
    }
    
    /**
     * Check geographic access permissions
     */
    private function hasGeographicAccess($zone, $wereda, $kebele) {
        // Admin has access to everything
        if ($this->userRole === 'admin') {
            return true;
        }
        
        // Zone officers can access their zone
        if (strpos($this->userRole, 'zone') !== false) {
            return $this->userZone === $zone;
        }
        
        // Wereda officers can access their wereda
        if (strpos($this->userRole, 'wereda') !== false) {
            return $this->userZone === $zone && $this->userWereda === $wereda;
        }
        
        // Kebele officers can access their kebele
        if (strpos($this->userRole, 'kebele') !== false) {
            return $this->userZone === $zone && $this->userWereda === $wereda && $this->userKebele === $kebele;
        }
        
        return false;
    }
    
    /**
     * Check storage quota
     */
    private function checkStorageQuota($entityType, $entityId, $fileSize) {
        $quota = $this->config->getStorageQuota($entityType);
        
        $stmt = $this->conn->prepare("
            SELECT COALESCE(SUM(file_size), 0) as used_storage 
            FROM files 
            WHERE entity_type = ? AND entity_id = ? AND status = 'active'
        ");
        $stmt->bind_param("ss", $entityType, $entityId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        $usedStorage = $row['used_storage'];
        return ($usedStorage + $fileSize) <= $quota;
    }
    
    /**
     * Enhanced file viewing with permission checks
     */
    public function viewFile($fileId) {
        $accessCheck = $this->checkFileAccess($fileId, 'view');
        if ($accessCheck['code'] !== FM_ACCESS_GRANTED) {
            return $accessCheck;
        }
        
        $file = $this->getFileInfo($fileId);
        if (!$file) {
            return [
                'code' => FM_ACCESS_DENIED_FILE_NOT_FOUND,
                'message' => 'File not found'
            ];
        }
        
        // Log view action
        $this->logFileAction($fileId, 'view', [
            'file_name' => $file['original_name']
        ]);
        
        // Return file info for preview
        return [
            'code' => FM_ACCESS_GRANTED,
            'file' => $file,
            'preview_url' => $this->generatePreviewUrl($fileId),
            'download_url' => $this->generateDownloadUrl($fileId)
        ];
    }
    
    /**
     * Enhanced file download with security
     */
    public function downloadFile($fileId) {
        $accessCheck = $this->checkFileAccess($fileId, 'download');
        if ($accessCheck['code'] !== FM_ACCESS_GRANTED) {
            return $accessCheck;
        }
        
        $file = $this->getFileInfo($fileId);
        if (!$file || !file_exists($file['file_path'])) {
            return [
                'code' => FM_ACCESS_DENIED_FILE_NOT_FOUND,
                'message' => 'File not found'
            ];
        }
        
        // Log download action
        $this->logFileAction($fileId, 'download', [
            'file_name' => $file['original_name'],
            'file_size' => $file['file_size']
        ]);
        
        // Serve file for download
        $this->serveFile($file);
        
        return [
            'code' => FM_ACCESS_GRANTED,
            'message' => 'File download initiated'
        ];
    }
    
    /**
     * Serve file for download with proper headers
     */
    private function serveFile($file) {
        // Security headers
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . addslashes($file['original_name']) . '"');
        header('Content-Length: ' . $file['file_size']);
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output file content
        readfile($file['file_path']);
        exit;
    }
    
    /**
     * Check file access permissions
     */
    private function checkFileAccess($fileId, $action) {
        $stmt = $this->conn->prepare("
            SELECT f.*, u.role as uploader_role 
            FROM files f 
            JOIN users u ON f.uploaded_by = u.id 
            WHERE f.file_id = ? AND f.status = 'active'
        ");
        $stmt->bind_param("i", $fileId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$row = $result->fetch_assoc()) {
            $stmt->close();
            return [
                'code' => FM_ACCESS_DENIED_FILE_NOT_FOUND,
                'message' => 'File not found or has been deleted'
            ];
        }
        $stmt->close();
        
        // Check geographic access
        if (!$this->hasGeographicAccess($row['zone_id'], $row['wereda_id'], $row['kebele_id'])) {
            $this->logFileAction($fileId, 'access_denied', [
                'reason' => 'geographic_restriction',
                'action' => $action
            ], false);
            
            return [
                'code' => FM_ACCESS_DENIED_GEOGRAPHIC,
                'message' => 'You do not have geographic access to this file'
            ];
        }
        
        // Check if user owns the file or has appropriate role permissions
        if ($row['uploaded_by'] == $this->userId || $this->userRole === 'admin') {
            return ['code' => FM_ACCESS_GRANTED];
        }
        
        // Check shared access
        if ($this->hasSharedAccess($fileId, $action)) {
            return ['code' => FM_ACCESS_GRANTED];
        }
        
        $this->logFileAction($fileId, 'access_denied', [
            'reason' => 'insufficient_permissions',
            'action' => $action
        ], false);
        
        return [
            'code' => FM_ACCESS_DENIED_PERMISSION,
            'message' => 'You do not have permission to access this file'
        ];
    }
    
    /**
     * Check if user has shared access to file
     */
    private function hasSharedAccess($fileId, $action) {
        $stmt = $this->conn->prepare("
            SELECT permission_type, expiry_date 
            FROM file_shares 
            WHERE file_id = ? AND (shared_with = ? OR shared_with_role = ?) 
            AND status = 'active' 
            AND (expiry_date IS NULL OR expiry_date > NOW())
        ");
        $stmt->bind_param("iis", $fileId, $this->userId, $this->userRole);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            if ($action === 'view' || ($action === 'download' && $row['permission_type'] === 'download')) {
                $stmt->close();
                return true;
            }
        }
        
        $stmt->close();
        return false;
    }
    
    /**
     * Get file information
     */
    private function getFileInfo($fileId) {
        $stmt = $this->conn->prepare("SELECT * FROM files WHERE file_id = ? AND status = 'active'");
        $stmt->bind_param("i", $fileId);
        $stmt->execute();
        $result = $stmt->get_result();
        $file = $result->fetch_assoc();
        $stmt->close();
        
        return $file;
    }
    
    /**
     * Log file actions for audit trail
     */
    private function logFileAction($fileId, $action, $details = [], $success = true) {
        $stmt = $this->conn->prepare("
            INSERT INTO file_audit_log (
                file_id, user_id, action_type, ip_address, user_agent, 
                details, geographic_location, success
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $detailsJson = json_encode($details);
        $location = $this->userZone . '/' . $this->userWereda . '/' . $this->userKebele;
        
        $stmt->bind_param("iisssssi", $fileId, $this->userId, $action, $ipAddress, $userAgent, $detailsJson, $location, $success);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Generate secure preview URL
     */
    private function generatePreviewUrl($fileId) {
        $token = $this->generateSecureToken($fileId, 'preview');
        return "preview.php?file_id={$fileId}&token={$token}";
    }
    
    /**
     * Generate secure download URL
     */
    private function generateDownloadUrl($fileId) {
        $token = $this->generateSecureToken($fileId, 'download');
        return "download.php?file_id={$fileId}&token={$token}";
    }
    
    /**
     * Generate secure token for file access
     */
    private function generateSecureToken($fileId, $action) {
        $data = $fileId . '|' . $this->userId . '|' . $action . '|' . time();
        return base64_encode(hash_hmac('sha256', $data, 'file_manager_secret_key', true));
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>