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
    
    // Get user info for geographic filtering
    $stmt = $conn->prepare("SELECT role, zone, wereda, kebele FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userInfo = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Build geographic filter based on user role
    $geographicFilter = "";
    $params = [];
    $types = "";
    
    if ($userInfo['role'] !== 'admin') {
        if (strpos($userInfo['role'], 'zone') !== false) {
            $geographicFilter = " AND zone_id = ?";
            $params[] = $userInfo['zone'];
            $types .= "s";
        } elseif (strpos($userInfo['role'], 'wereda') !== false) {
            $geographicFilter = " AND zone_id = ? AND wereda_id = ?";
            $params[] = $userInfo['zone'];
            $params[] = $userInfo['wereda'];
            $types .= "ss";
        } elseif (strpos($userInfo['role'], 'kebele') !== false) {
            $geographicFilter = " AND zone_id = ? AND wereda_id = ? AND kebele_id = ?";
            $params[] = $userInfo['zone'];
            $params[] = $userInfo['wereda'];
            $params[] = $userInfo['kebele'];
            $types .= "sss";
        }
    }
    
    // Get total files count
    $sql = "SELECT COUNT(*) as total FROM files WHERE status = 'active'" . $geographicFilter;
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $totalFiles = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    
    // Get total storage used
    $sql = "SELECT COALESCE(SUM(file_size), 0) as total_size FROM files WHERE status = 'active'" . $geographicFilter;
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $storageUsed = $stmt->get_result()->fetch_assoc()['total_size'];
    $stmt->close();
    
    // Get shared files count
    $sql = "SELECT COUNT(DISTINCT f.file_id) as shared_count 
            FROM files f 
            JOIN file_shares fs ON f.file_id = fs.file_id 
            WHERE f.status = 'active' AND fs.status = 'active'" . $geographicFilter;
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $sharedFiles = $stmt->get_result()->fetch_assoc()['shared_count'];
    $stmt->close();
    
    // Get recent uploads (this week)
    $sql = "SELECT COUNT(*) as recent_count 
            FROM files 
            WHERE status = 'active' 
            AND upload_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)" . $geographicFilter;
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $recentUploads = $stmt->get_result()->fetch_assoc()['recent_count'];
    $stmt->close();
    
    // Get recent files (last 10)
    $sql = "SELECT f.*, u.name as uploader_name 
            FROM files f 
            JOIN users u ON f.uploaded_by = u.id 
            WHERE f.status = 'active'" . $geographicFilter . "
            ORDER BY f.upload_date DESC 
            LIMIT 10";
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $recentFiles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Prepare response
    $response = [
        'success' => true,
        'stats' => [
            'total_files' => (int)$totalFiles,
            'storage_used' => (int)$storageUsed,
            'shared_files' => (int)$sharedFiles,
            'recent_uploads' => (int)$recentUploads
        ],
        'recent_files' => $recentFiles
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Dashboard API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading dashboard data'
    ]);
}

$conn->close();
?>