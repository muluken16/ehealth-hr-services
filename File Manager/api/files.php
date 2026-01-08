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
    $section = $_GET['section'] ?? 'all';
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    $limit = (int)($_GET['limit'] ?? 50);
    $offset = (int)($_GET['offset'] ?? 0);
    
    $conn = getDBConnection();
    
    // Get user info for geographic filtering
    $stmt = $conn->prepare("SELECT role, zone, wereda, kebele FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userInfo = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Build query conditions
    $conditions = ["f.status = 'active'"];
    $params = [];
    $types = "";
    
    // Geographic filtering
    if ($userInfo['role'] !== 'admin') {
        if (strpos($userInfo['role'], 'zone') !== false) {
            $conditions[] = "f.zone_id = ?";
            $params[] = $userInfo['zone'];
            $types .= "s";
        } elseif (strpos($userInfo['role'], 'wereda') !== false) {
            $conditions[] = "f.zone_id = ? AND f.wereda_id = ?";
            $params[] = $userInfo['zone'];
            $params[] = $userInfo['wereda'];
            $types .= "ss";
        } elseif (strpos($userInfo['role'], 'kebele') !== false) {
            $conditions[] = "f.zone_id = ? AND f.wereda_id = ? AND f.kebele_id = ?";
            $params[] = $userInfo['zone'];
            $params[] = $userInfo['wereda'];
            $params[] = $userInfo['kebele'];
            $types .= "sss";
        }
    }
    
    // Section filtering
    if ($section !== 'all' && $section !== 'shared') {
        $conditions[] = "f.entity_type = ?";
        $params[] = $section;
        $types .= "s";
    }
    
    // Category filtering
    if (!empty($category)) {
        $conditions[] = "f.category = ?";
        $params[] = $category;
        $types .= "s";
    }
    
    // Search filtering
    if (!empty($search)) {
        $conditions[] = "(f.original_name LIKE ? OR f.entity_id LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }
    
    // Handle shared files section
    if ($section === 'shared') {
        $sql = "SELECT f.*, u.name as uploader_name, fs.permission_type, fs.expiry_date, fs.shared_by
                FROM files f 
                JOIN file_shares fs ON f.file_id = fs.file_id
                JOIN users u ON f.uploaded_by = u.id 
                WHERE " . implode(' AND ', $conditions) . "
                AND fs.status = 'active' 
                AND (fs.shared_with = ? OR fs.shared_with_role = ?)
                AND (fs.expiry_date IS NULL OR fs.expiry_date > NOW())
                ORDER BY f.upload_date DESC 
                LIMIT ? OFFSET ?";
        
        $params[] = $userId;
        $params[] = $userInfo['role'];
        $params[] = $limit;
        $params[] = $offset;
        $types .= "isii";
    } else {
        $sql = "SELECT f.*, u.name as uploader_name 
                FROM files f 
                JOIN users u ON f.uploaded_by = u.id 
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY f.upload_date DESC 
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
    }
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $files = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Get total count for pagination
    $countSql = "SELECT COUNT(*) as total FROM files f";
    if ($section === 'shared') {
        $countSql .= " JOIN file_shares fs ON f.file_id = fs.file_id";
    }
    $countSql .= " WHERE " . implode(' AND ', array_slice($conditions, 0, -2)); // Remove limit/offset params
    
    if ($section === 'shared') {
        $countSql .= " AND fs.status = 'active' AND (fs.shared_with = ? OR fs.shared_with_role = ?) AND (fs.expiry_date IS NULL OR fs.expiry_date > NOW())";
    }
    
    $countParams = array_slice($params, 0, -2); // Remove limit/offset
    $countTypes = substr($types, 0, -2);
    
    if ($section === 'shared') {
        $countParams[] = $userId;
        $countParams[] = $userInfo['role'];
        $countTypes .= "is";
    }
    
    $stmt = $conn->prepare($countSql);
    if (!empty($countParams)) {
        $stmt->bind_param($countTypes, ...$countParams);
    }
    $stmt->execute();
    $totalCount = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    
    // Add file access URLs
    foreach ($files as &$file) {
        $file['preview_url'] = "api/preview.php?file_id=" . $file['file_id'];
        $file['download_url'] = "api/download.php?file_id=" . $file['file_id'];
        $file['can_edit'] = ($file['uploaded_by'] == $userId || $userInfo['role'] === 'admin');
        $file['can_delete'] = ($file['uploaded_by'] == $userId || $userInfo['role'] === 'admin');
    }
    
    echo json_encode([
        'success' => true,
        'files' => $files,
        'pagination' => [
            'total' => (int)$totalCount,
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $totalCount
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Files API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading files'
    ]);
}

$conn->close();
?>