<?php
session_start();
require_once '../../db.php';
require_once '../FileManager.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $fileId = (int)($_GET['file_id'] ?? 0);
    
    if (!$fileId) {
        echo json_encode(['success' => false, 'message' => 'Invalid file ID']);
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $fileManager = new FileManager($userId);
    
    // Get file info and check access
    $result = $fileManager->viewFile($fileId);
    
    if ($result['code'] !== FM_ACCESS_GRANTED) {
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
        exit;
    }
    
    $file = $result['file'];
    
    // Generate secure preview URL
    $previewUrl = null;
    $canPreview = false;
    
    // Check if file type supports preview
    $previewableTypes = [
        'application/pdf',
        'image/jpeg',
        'image/jpg', 
        'image/png',
        'image/gif',
        'text/plain'
    ];
    
    if (in_array($file['mime_type'], $previewableTypes)) {
        $canPreview = true;
        $previewUrl = "secure_file.php?file_id={$fileId}&action=preview&token=" . 
                     $fileManager->generateSecureToken($fileId, 'preview');
    }
    
    echo json_encode([
        'success' => true,
        'file' => [
            'file_id' => $file['file_id'],
            'original_name' => $file['original_name'],
            'file_size' => $file['file_size'],
            'mime_type' => $file['mime_type'],
            'upload_date' => $file['upload_date'],
            'entity_type' => $file['entity_type'],
            'entity_id' => $file['entity_id'],
            'category' => $file['category']
        ],
        'can_preview' => $canPreview,
        'preview_url' => $previewUrl,
        'download_url' => $result['download_url']
    ]);
    
} catch (Exception $e) {
    error_log("Preview API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading file preview'
    ]);
}
?>