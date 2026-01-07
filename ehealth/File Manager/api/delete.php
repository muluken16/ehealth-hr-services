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
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $fileId = (int)($input['file_id'] ?? 0);
    
    if (!$fileId) {
        echo json_encode(['success' => false, 'message' => 'Invalid file ID']);
        exit;
    }
    
    // Get file info and check permissions
    $stmt = $conn->prepare("
        SELECT f.*, u.role as user_role 
        FROM files f, users u 
        WHERE f.file_id = ? AND u.id = ? AND f.status = 'active'
    ");
    $stmt->bind_param("ii", $fileId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result->num_rows) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'File not found']);
        exit;
    }
    
    $data = $result->fetch_assoc();
    $file = $data;
    $userRole = $data['user_role'];
    $stmt->close();
    
    // Check if user can delete this file (owner or admin)
    if ($file['uploaded_by'] != $userId && $userRole !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this file']);
        exit;
    }
    
    // Perform soft delete
    $stmt = $conn->prepare("UPDATE files SET status = 'deleted', updated_at = NOW() WHERE file_id = ?");
    $stmt->bind_param("i", $fileId);
    
    if ($stmt->execute()) {
        // Log the delete action
        $logStmt = $conn->prepare("
            INSERT INTO file_audit_log (file_id, user_id, action_type, details, success)
            VALUES (?, ?, 'delete', ?, 1)
        ");
        $details = json_encode([
            'original_name' => $file['original_name'],
            'entity_type' => $file['entity_type'],
            'entity_id' => $file['entity_id'],
            'category' => $file['category'],
            'delete_type' => 'soft_delete'
        ]);
        $logStmt->bind_param("iis", $fileId, $userId, $details);
        $logStmt->execute();
        $logStmt->close();
        
        // Revoke all active shares for this file
        $revokeStmt = $conn->prepare("UPDATE file_shares SET status = 'revoked' WHERE file_id = ? AND status = 'active'");
        $revokeStmt->bind_param("i", $fileId);
        $revokeStmt->execute();
        $revokeStmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'File deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete file'
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Delete API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting file'
    ]);
}

$conn->close();
?>