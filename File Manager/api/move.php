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
    $conn = getDBConnection();
    $fileManager = new FileManager($userId);

    // Get POST data
    $fileId = (int) ($_POST['file_id'] ?? 0);
    $newCategory = trim($_POST['category'] ?? '');

    if (!$fileId || empty($newCategory)) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
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
    $oldCategory = $file['category'];
    $stmt->close();

    // Check if user can move this file (owner or admin)
    if ($file['uploaded_by'] != $userId && $userRole !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to move this file']);
        exit;
    }

    // Check if new category is valid for this entity type
    $config = require '../config.php';
    $validCategories = $config->getFileCategories($file['entity_type']);

    if (!in_array($newCategory, $validCategories)) {
        echo json_encode(['success' => false, 'message' => 'Invalid category for this entity type']);
        exit;
    }

    // Get configuration for file paths
    $fileConfig = new FileManagerConfig($conn);

    // Generate new file path
    $newFolderPath = $fileConfig->getEntityFolderPath($file['entity_type'], $file['entity_id'], $newCategory);
    $newSystemName = $fileConfig->generateSystemFilename($file['entity_type'], $file['entity_id'], $newCategory, $file['original_name']);
    $newFilePath = $newFolderPath . '/' . $newSystemName;

    // Create destination folder if it doesn't exist
    if (!file_exists($newFolderPath)) {
        mkdir($newFolderPath, 0755, true);
    }

    // Move the actual file
    if (file_exists($file['file_path'])) {
        if (!rename($file['file_path'], $newFilePath)) {
            echo json_encode(['success' => false, 'message' => 'Failed to move file on disk']);
            exit;
        }
    }

    // Update database
    $stmt = $conn->prepare("UPDATE files SET category = ?, file_path = ?, system_name = ?, updated_at = NOW() WHERE file_id = ?");
    $stmt->bind_param("sssi", $newCategory, $newFilePath, $newSystemName, $fileId);

    if ($stmt->execute()) {
        // Log the move action
        $logStmt = $conn->prepare("
            INSERT INTO file_audit_log (file_id, user_id, action_type, details, success)
            VALUES (?, ?, 'move', ?, 1)
        ");
        $details = json_encode([
            'old_category' => $oldCategory,
            'new_category' => $newCategory,
            'old_path' => $file['file_path'],
            'new_path' => $newFilePath
        ]);
        $logStmt->bind_param("iis", $fileId, $userId, $details);
        $logStmt->execute();
        $logStmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'File moved successfully',
            'new_path' => $newFilePath
        ]);
    } else {
        // Revert file move if database update fails
        rename($newFilePath, $file['file_path']);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update file record'
        ]);
    }

    $stmt->close();

} catch (Exception $e) {
    error_log("Move API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error moving file'
    ]);
}

$conn->close();
?>