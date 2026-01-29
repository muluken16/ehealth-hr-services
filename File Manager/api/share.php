<?php
session_start();
require_once '../../db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    $conn = getDBConnection();
    
    // Get POST data
    $fileId = (int)($_POST['file_id'] ?? 0);
    $sharedWith = json_decode($_POST['shared_with'] ?? '[]', true);
    $permissionType = $_POST['permission_type'] ?? 'view';
    $expiryDate = $_POST['expiry_date'] ?? null;
    $message = $_POST['message'] ?? '';
    
    if (!$fileId || empty($sharedWith)) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit;
    }
    
    // Verify user has access to the file
    $stmt = $conn->prepare("SELECT * FROM files WHERE file_id = ? AND status = 'active'");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $file = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$file) {
        echo json_encode(['success' => false, 'message' => 'File not found']);
        exit;
    }
    
    // Check if user can share this file (owner or admin)
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userRole = $stmt->get_result()->fetch_assoc()['role'];
    $stmt->close();
    
    if ($file['uploaded_by'] != $userId && $userRole !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to share this file']);
        exit;
    }
    
    // Convert expiry date
    $expiryDateTime = null;
    if (!empty($expiryDate)) {
        $expiryDateTime = date('Y-m-d H:i:s', strtotime($expiryDate));
    }
    
    $successCount = 0;
    $errors = [];
    
    // Share with each selected user
    foreach ($sharedWith as $targetUserId) {
        $targetUserId = (int)$targetUserId;
        
        // Check if already shared with this user
        $stmt = $conn->prepare("
            SELECT share_id FROM file_shares 
            WHERE file_id = ? AND shared_with = ? AND status = 'active'
        ");
        $stmt->bind_param("ii", $fileId, $targetUserId);
        $stmt->execute();
        $existingShare = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($existingShare) {
            // Update existing share
            $stmt = $conn->prepare("
                UPDATE file_shares 
                SET permission_type = ?, expiry_date = ?, message = ?, created_date = NOW()
                WHERE share_id = ?
            ");
            $stmt->bind_param("sssi", $permissionType, $expiryDateTime, $message, $existingShare['share_id']);
        } else {
            // Create new share
            $stmt = $conn->prepare("
                INSERT INTO file_shares (file_id, shared_by, shared_with, permission_type, expiry_date, message)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iiisss", $fileId, $userId, $targetUserId, $permissionType, $expiryDateTime, $message);
        }
        
        if ($stmt->execute()) {
            $successCount++;
            
            // Log the share action
            $logStmt = $conn->prepare("
                INSERT INTO file_audit_log (file_id, user_id, action_type, details, success)
                VALUES (?, ?, 'share', ?, 1)
            ");
            $details = json_encode([
                'shared_with_user_id' => $targetUserId,
                'permission_type' => $permissionType,
                'expiry_date' => $expiryDateTime,
                'message' => $message
            ]);
            $logStmt->bind_param("iis", $fileId, $userId, $details);
            $logStmt->execute();
            $logStmt->close();
            
        } else {
            $errors[] = "Failed to share with user ID: $targetUserId";
        }
        $stmt->close();
    }
    
    if ($successCount > 0) {
        echo json_encode([
            'success' => true,
            'message' => "File shared with $successCount user(s)",
            'shared_count' => $successCount,
            'errors' => $errors
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to share file',
            'errors' => $errors
        ]);
    }
    
} catch (Exception $e) {
    error_log("Share API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error sharing file'
    ]);
}

$conn->close();
?>