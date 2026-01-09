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
    $geographicFilter = " WHERE f.status = 'active'";
    $params = [];
    $types = "";

    if ($userInfo['role'] !== 'admin') {
        if (strpos($userInfo['role'], 'zone') !== false) {
            $geographicFilter .= " AND f.zone_id = ?";
            $params[] = $userInfo['zone'];
            $types .= "s";
        } elseif (strpos($userInfo['role'], 'wereda') !== false) {
            $geographicFilter .= " AND f.zone_id = ? AND f.wereda_id = ?";
            $params[] = $userInfo['zone'];
            $params[] = $userInfo['wereda'];
            $types .= "ss";
        } elseif (strpos($userInfo['role'], 'kebele') !== false) {
            $geographicFilter .= " AND f.zone_id = ? AND f.wereda_id = ? AND f.kebele_id = ?";
            $params[] = $userInfo['zone'];
            $params[] = $userInfo['wereda'];
            $params[] = $userInfo['kebele'];
            $types .= "sss";
        }
    }

    // Get storage by entity type
    $sql = "SELECT f.entity_type, COUNT(*) as file_count, COALESCE(SUM(f.file_size), 0) as total_size 
            FROM files f" . $geographicFilter . "
            GROUP BY f.entity_type 
            ORDER BY total_size DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $storageByType = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get storage by category
    $sql = "SELECT f.category, COUNT(*) as file_count, COALESCE(SUM(f.file_size), 0) as total_size 
            FROM files f" . $geographicFilter . "
            GROUP BY f.category 
            ORDER BY total_size DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $storageByCategory = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get upload trends (last 7 days)
    $sql = "SELECT DATE(upload_date) as date, COUNT(*) as upload_count, COALESCE(SUM(file_size), 0) as total_size 
            FROM files" . $geographicFilter . "
            AND upload_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(upload_date)
            ORDER BY date ASC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $uploadTrends = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get total storage
    $sql = "SELECT COALESCE(SUM(file_size), 0) as total_storage FROM files f" . $geographicFilter;

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $totalStorage = $stmt->get_result()->fetch_assoc()['total_storage'];
    $stmt->close();

    // Get file type distribution
    $sql = "SELECT 
            CASE 
                WHEN mime_type LIKE 'image/%' THEN 'images'
                WHEN mime_type LIKE 'application/pdf' THEN 'pdf'
                WHEN mime_type LIKE 'application/msword%' OR mime_type LIKE 'application/vnd.openxmlformats%' THEN 'documents'
                WHEN mime_type LIKE 'application/vnd.ms-excel%' OR mime_type LIKE 'application/vnd.openformats%' THEN 'spreadsheets'
                WHEN mime_type LIKE 'text/%' THEN 'text'
                ELSE 'other'
            END as file_type,
            COUNT(*) as count
            FROM files f" . $geographicFilter . "
            GROUP BY file_type
            ORDER BY count DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $fileTypeDistribution = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get top 5 largest files
    $sql = "SELECT file_id, original_name, file_size, upload_date 
            FROM files f" . $geographicFilter . "
            ORDER BY file_size DESC 
            LIMIT 5";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $largestFiles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode([
        'success' => true,
        'total_storage' => (int) $totalStorage,
        'storage_by_type' => $storageByType,
        'storage_by_category' => $storageByCategory,
        'upload_trends' => $uploadTrends,
        'file_type_distribution' => $fileTypeDistribution,
        'largest_files' => $largestFiles
    ]);

} catch (Exception $e) {
    error_log("Storage Stats API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading storage statistics'
    ]);
}

$conn->close();
?>