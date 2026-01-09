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

    if (!$fileId) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit;
    }

    // Get file info
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

    // Check if user can copy this file (owner or admin)
    if ($file['uploaded_by'] != $userId && $userRole !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to copy this file']);
        exit;
    }

    // Check storage quota
    require_once '../config.php';
    $fileConfig = new FileManagerConfig($conn);
    $quota = $fileConfig->getStorageQuota($file['entity_type']);

    $stmt = $conn->prepare("SELECT COALESCE(SUM(file_size), 0) as used_storage FROM files WHERE entity_type = ? AND entity_id = ? AND status = 'active'");
    $stmt->bind_param("ss", $file['entity_type'], $file['entity_id']);
    $stmt->execute();
    $usedStorage = $stmt->get_result()->fetch_assoc()['used_storage'];
    $stmt->close();

    if (($usedStorage + $file['file_size']) > $quota) {
        echo json_encode(['success' => false, 'message' => 'Storage quota exceeded for this entity']);
        exit;
    }

    // Get user geographic info
    $stmt = $conn->prepare("SELECT role, zone, wereda, kebele FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userInfo = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Determine geographic scope
    if (strpos($userInfo['role'], 'zone') !== false || $userInfo['role'] === 'admin') {
        $geographicScope = 'zone';
    } elseif (strpos($userInfo['role'], 'wereda') !== false) {
        $geographicScope = 'wereda';
    } else {
        $geographicScope = 'kebele';
    }

    // Generate new system name
    $newSystemName = $fileConfig->generateSystemFilename($file['entity_type'], $file['entity_id'], $file['category'], $file['original_name']);
    $newFolderPath = $fileConfig->getEntityFolderPath($file['entity_type'], $file['entity_id'], $file['category']);
    $newFilePath = $newFolderPath . '/' . $newSystemName;

    // Copy the actual file
    if (file_exists($file['file_path'])) {
        if (!copy($file['file_path'], $newFilePath)) {
            echo json_encode(['success' => false, 'message' => 'Failed to copy file on disk']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Original file not found']);
        exit;
    }

    // Generate file hash for integrity
    $fileHash = hash_file('sha256', $newFilePath);

    // Create new file record in database
    $stmt = $conn->prepare("
        INSERT INTO files (
            entity_type, entity_id, category, original_name, system_name, 
            file_path, file_size, mime_type, uploaded_by, geographic_scope,
            zone_id, wereda_id, kebele_id, file_hash
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssssssissssss",
        $file['entity_type'],
        $file['entity_id'],
        $file['category'],
        $file['original_name'],
        $newSystemName,
        $newFilePath,
        $file['file_size'],
        $file['mime_type'],
        $userId,
        $geographicScope,
        $userInfo['zone'],
        $userInfo['wereda'],
        $userInfo['kebele'],
        $fileHash
    );

    if ($stmt->execute()) {
        $newFileId = $conn->insert_id;

        // Log the copy action
        $logStmt = $conn->prepare("
            INSERT INTO file_audit_log (file_id, user_id, action_type, details, success)
            VALUES (?, ?, 'copy', ?, 1)
        ");
        $details = json_encode([
            'original_file_id' => $fileId,
            'original_name' => $file['original_name'],
            'new_file_id' => $newFileId,
            'new_system_name' => $newSystemName
        ]);
        $logStmt->bind_param("iis", $newFileId, $userId, $details);
        $logStmt->execute();
        $logStmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'File copied successfully',
            'new_file_id' => $newFileId,
            'file_name' => $file['original_name']
        ]);
    } else {
        // Delete the copied file if database insert fails
        if (file_exists($newFilePath)) {
            unlink($newFilePath);
        }
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create file record'
        ]);
    }

    $stmt->close();

} catch (Exception $e) {
    error_log("Copy API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error copying file'
    ]);
}

$conn->close();
?>