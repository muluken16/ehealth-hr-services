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
    $userId = $_SESSION['user_id'];
    $fileManager = new FileManager($userId);
    
    // Validate required fields
    if (!isset($_POST['entity_type']) || !isset($_POST['entity_id']) || !isset($_POST['category'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
        exit;
    }
    
    $entityType = $_POST['entity_type'];
    $entityId = $_POST['entity_id'];
    $category = $_POST['category'];
    $uploadedFile = $_FILES['file'];
    
    // Optional metadata
    $metadata = [
        'description' => $_POST['description'] ?? '',
        'uploaded_via' => 'web_interface',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];
    
    // Upload the file
    $result = $fileManager->uploadFile($entityType, $entityId, $category, $uploadedFile, $metadata);
    
    if ($result['code'] === FM_UPLOAD_SUCCESS) {
        echo json_encode([
            'success' => true,
            'message' => $result['message'],
            'file_id' => $result['file_id'],
            'system_name' => $result['system_name']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $result['message'],
            'code' => $result['code']
        ]);
    }
    
} catch (Exception $e) {
    error_log("Upload API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'System error during upload'
    ]);
}
?>