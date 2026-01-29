<?php
session_start();
require_once '../db.php';
require_once 'FileManager.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die('Unauthorized');
}

try {
    $fileId = (int)($_GET['file_id'] ?? 0);
    $action = $_GET['action'] ?? '';
    $token = $_GET['token'] ?? '';
    
    if (!$fileId || !$action || !$token) {
        http_response_code(400);
        die('Invalid parameters');
    }
    
    $userId = $_SESSION['user_id'];
    $fileManager = new FileManager($userId);
    
    // Verify token (simplified - in production, implement proper token verification)
    // For now, we'll rely on session-based access control
    
    if ($action === 'preview') {
        $result = $fileManager->viewFile($fileId);
    } elseif ($action === 'download') {
        $result = $fileManager->downloadFile($fileId);
        return; // downloadFile handles the response
    } else {
        http_response_code(400);
        die('Invalid action');
    }
    
    if ($result['code'] !== FM_ACCESS_GRANTED) {
        http_response_code(403);
        die($result['message']);
    }
    
    $file = $result['file'];
    
    // Serve file for preview
    if ($action === 'preview' && file_exists($file['file_path'])) {
        // Set appropriate headers for preview
        header('Content-Type: ' . $file['mime_type']);
        header('Content-Length: ' . $file['file_size']);
        header('Cache-Control: private, max-age=3600');
        header('X-Content-Type-Options: nosniff');
        
        // For PDFs, allow inline viewing
        if ($file['mime_type'] === 'application/pdf') {
            header('Content-Disposition: inline; filename="' . addslashes($file['original_name']) . '"');
        }
        
        // Output file content
        readfile($file['file_path']);
    } else {
        http_response_code(404);
        die('File not found');
    }
    
} catch (Exception $e) {
    error_log("Secure File Error: " . $e->getMessage());
    http_response_code(500);
    die('Server error');
}
?>