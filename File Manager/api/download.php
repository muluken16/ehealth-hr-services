<?php
session_start();
require_once '../../db.php';
require_once '../FileManager.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $fileId = (int)($_GET['file_id'] ?? 0);
    
    if (!$fileId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid file ID']);
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $fileManager = new FileManager($userId);
    
    // Attempt to download the file
    $result = $fileManager->downloadFile($fileId);
    
    if ($result['code'] !== FM_ACCESS_GRANTED) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
        exit;
    }
    
    // If we reach here, the file should have been served by the FileManager
    // This code should not execute in normal circumstances
    
} catch (Exception $e) {
    error_log("Download API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error downloading file'
    ]);
}
?>