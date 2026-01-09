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

    // Get file ID from query
    $fileId = (int) ($_GET['file_id'] ?? 0);

    if (!$fileId) {
        echo json_encode(['success' => false, 'message' => 'Invalid file ID']);
        exit;
    }

    // Get file info with permission check
    $stmt = $conn->prepare("
        SELECT f.*, u.name as uploader_name, u.role as uploader_role,
               (SELECT COUNT(*) FROM file_audit_log WHERE file_id = f.file_id AND action_type = 'download') as download_count,
               (SELECT COUNT(*) FROM file_audit_log WHERE file_id = f.file_id AND action_type = 'view') as view_count
        FROM files f 
        JOIN users u ON f.uploaded_by = u.id 
        WHERE f.file_id = ? AND f.status = 'active'
    ");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$file = $result->fetch_assoc()) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'File not found']);
        exit;
    }
    $stmt->close();

    // Check user access
    $stmt = $conn->prepare("SELECT role, zone, wereda, kebele FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userInfo = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $hasAccess = false;

    // Admin access
    if ($userInfo['role'] === 'admin') {
        $hasAccess = true;
    }
    // Owner access
    elseif ($file['uploaded_by'] == $userId) {
        $hasAccess = true;
    }
    // Geographic access
    else {
        if ($userInfo['role'] === 'zone_ho' || $userInfo['role'] === 'zone_hr') {
            $hasAccess = ($file['zone_id'] == $userInfo['zone']);
        } elseif ($userInfo['role'] === 'wereda_ho' || $userInfo['role'] === 'wereda_hr') {
            $hasAccess = ($file['zone_id'] == $userInfo['zone'] && $file['wereda_id'] == $userInfo['wereda']);
        } elseif (strpos($userInfo['role'], 'kebele') !== false) {
            $hasAccess = ($file['zone_id'] == $userInfo['zone'] &&
                $file['wereda_id'] == $userInfo['wereda'] &&
                $file['kebele_id'] == $userInfo['kebele']);
        }
    }

    // Check shared access
    if (!$hasAccess) {
        $stmt = $conn->prepare("
            SELECT share_id FROM file_shares 
            WHERE file_id = ? AND (shared_with = ? OR shared_with_role = ?) 
            AND status = 'active' 
            AND (expiry_date IS NULL OR expiry_date > NOW())
        ");
        $stmt->bind_param("iis", $fileId, $userId, $userInfo['role']);
        $stmt->execute();
        $hasAccess = ($stmt->get_result()->num_rows > 0);
        $stmt->close();
    }

    if (!$hasAccess) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }

    // Get share information
    $stmt = $conn->prepare("
        SELECT fs.*, u.name as shared_with_name
        FROM file_shares fs 
        LEFT JOIN users u ON fs.shared_with = u.id
        WHERE fs.file_id = ? AND fs.status = 'active'
    ");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $shares = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get recent activity
    $stmt = $conn->prepare("
        SELECT action_type, timestamp, ip_address, success
        FROM file_audit_log 
        WHERE file_id = ? 
        ORDER BY timestamp DESC 
        LIMIT 10
    ");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $recentActivity = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get file permissions for current user
    $canEdit = ($file['uploaded_by'] == $userId || $userInfo['role'] === 'admin');
    $canDelete = ($file['uploaded_by'] == $userId || $userInfo['role'] === 'admin');
    $canShare = ($file['uploaded_by'] == $userId || $userInfo['role'] === 'admin');

    echo json_encode([
        'success' => true,
        'file' => [
            'file_id' => $file['file_id'],
            'original_name' => $file['original_name'],
            'system_name' => $file['system_name'],
            'file_size' => $file['file_size'],
            'mime_type' => $file['mime_type'],
            'entity_type' => $file['entity_type'],
            'entity_id' => $file['entity_id'],
            'category' => $file['category'],
            'upload_date' => $file['upload_date'],
            'uploader_name' => $file['uploader_name'],
            'download_count' => $file['download_count'],
            'view_count' => $file['view_count'],
            'geographic_scope' => $file['geographic_scope'],
            'zone_id' => $file['zone_id'],
            'wereda_id' => $file['wereda_id'],
            'kebele_id' => $file['kebele_id']
        ],
        'permissions' => [
            'can_edit' => $canEdit,
            'can_delete' => $canDelete,
            'can_share' => $canShare
        ],
        'shares' => $shares,
        'recent_activity' => $recentActivity
    ]);

} catch (Exception $e) {
    error_log("File Info API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading file information'
    ]);
}

$conn->close();
?>