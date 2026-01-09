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
    $fileIds = $input['file_ids'] ?? [];

    if (empty($fileIds) || !is_array($fileIds)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file IDs']);
        exit;
    }

    // Get user role
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userRole = $stmt->get_result()->fetch_assoc()['role'];
    $stmt->close();

    // Get files that user can delete
    $placeholders = implode(',', array_fill(0, count($fileIds), '?'));
    $types = str_repeat('i', count($fileIds));

    $stmt = $conn->prepare("
        SELECT f.file_id, f.original_name, f.file_path, f.uploaded_by 
        FROM files f 
        WHERE f.file_id IN ({$placeholders}) AND f.status = 'active'
    ");
    $stmt->bind_param($types, ...$fileIds);
    $stmt->execute();
    $result = $stmt->get_result();

    $filesToDelete = [];
    $deletedCount = 0;
    $errors = [];

    while ($file = $result->fetch_assoc()) {
        // Check if user can delete this file (owner or admin)
        if ($file['uploaded_by'] == $userId || $userRole === 'admin') {
            $filesToDelete[] = $file;
        } else {
            $errors[] = "Permission denied for: " . $file['original_name'];
        }
    }
    $stmt->close();

    // Perform soft delete on allowed files
    foreach ($filesToDelete as $file) {
        $stmt = $conn->prepare("UPDATE files SET status = 'deleted', updated_at = NOW() WHERE file_id = ?");
        $stmt->bind_param("i", $file['file_id']);

        if ($stmt->execute()) {
            $deletedCount++;

            // Log the delete action
            $logStmt = $conn->prepare("
                INSERT INTO file_audit_log (file_id, user_id, action_type, details, success)
                VALUES (?, ?, 'bulk_delete', ?, 1)
            ");
            $details = json_encode([
                'original_name' => $file['original_name'],
                'file_size' => $file['file_path'],
                'delete_type' => 'bulk_soft_delete'
            ]);
            $logStmt->bind_param("iis", $file['file_id'], $userId, $details);
            $logStmt->execute();
            $logStmt->close();

            // Revoke all active shares for this file
            $revokeStmt = $conn->prepare("UPDATE file_shares SET status = 'revoked' WHERE file_id = ? AND status = 'active'");
            $revokeStmt->bind_param("i", $file['file_id']);
            $revokeStmt->execute();
            $revokeStmt->close();
        } else {
            $errors[] = "Failed to delete: " . $file['original_name'];
        }
        $stmt->close();
    }

    echo json_encode([
        'success' => true,
        'message' => "Deleted {$deletedCount} file(s)",
        'deleted_count' => $deletedCount,
        'errors' => $errors
    ]);

} catch (Exception $e) {
    error_log("Bulk Delete API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting files'
    ]);
}

$conn->close();
?>