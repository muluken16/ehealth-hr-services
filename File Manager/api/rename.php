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
    $fileId = (int) ($_POST['file_id'] ?? 0);
    $newName = trim($_POST['new_name'] ?? '');

    if (!$fileId || empty($newName)) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit;
    }

    // Validate new filename
    if (!preg_match('/^[a-zA-Z0-9_\-\.\s]+$/', $newName)) {
        echo json_encode(['success' => false, 'message' => 'Filename contains invalid characters']);
        exit;
    }

    // Get file info and check permissions
    $stmt = $conn->prepare("SELECT f.*, u.role as user_role FROM files f, users u WHERE f.file_id = ? AND u.id = ? AND f.status = 'active'");
    $stmt->bind_param("ii", $fileId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result->num_rows) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'File not found']);
        exit;
    }

    $file = $result->fetch_assoc();
    $userRole = $file['user_role'];
    $stmt->close();

    // Check if user can rename this file (owner or admin)
    if ($file['uploaded_by'] != $userId && $userRole !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to rename this file']);
        exit;
    }

    // Generate new system name with same extension
    $oldExtension = pathinfo($file['original_name'], PATHINFO_EXTENSION);
    $newExtension = pathinfo($newName, PATHINFO_EXTENSION);

    if (empty($newExtension) && !empty($oldExtension)) {
        $newName .= '.' . $oldExtension;
    }

    // Update file name in database
    $stmt = $conn->prepare("UPDATE files SET original_name = ?, updated_at = NOW() WHERE file_id = ?");
    $stmt->bind_param("si", $newName, $fileId);

    if ($stmt->execute()) {
        // Log the rename action
        $logStmt = $conn->prepare("
            INSERT INTO file_audit_log (file_id, user_id, action_type, details, success)
            VALUES (?, ?, 'rename', ?, 1)
        ");
        $details = json_encode([
            'old_name' => $file['original_name'],
            'new_name' => $newName
        ]);
        $logStmt->bind_param("iis", $fileId, $userId, $details);
        $logStmt->execute();
        $logStmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'File renamed successfully',
            'new_name' => $newName
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to rename file'
        ]);
    }

    $stmt->close();

} catch (Exception $e) {
    error_log("Rename API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error renaming file'
    ]);
}

$conn->close();
?>