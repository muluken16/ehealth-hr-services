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
    $search = $_GET['search'] ?? '';
    $conn = getDBConnection();
    
    // Get current user's info for geographic filtering
    $stmt = $conn->prepare("SELECT role, zone, wereda, kebele FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $currentUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Build query to get users within the same geographic scope
    $conditions = ["id != ?"];
    $params = [$userId];
    $types = "i";
    
    // Geographic filtering - users can only share with users in their scope or below
    if ($currentUser['role'] !== 'admin') {
        if (strpos($currentUser['role'], 'zone') !== false) {
            // Zone officers can share with anyone in their zone
            $conditions[] = "zone = ?";
            $params[] = $currentUser['zone'];
            $types .= "s";
        } elseif (strpos($currentUser['role'], 'wereda') !== false) {
            // Wereda officers can share with anyone in their wereda
            $conditions[] = "zone = ? AND wereda = ?";
            $params[] = $currentUser['zone'];
            $params[] = $currentUser['wereda'];
            $types .= "ss";
        } elseif (strpos($currentUser['role'], 'kebele') !== false) {
            // Kebele officers can share with anyone in their kebele
            $conditions[] = "zone = ? AND wereda = ? AND kebele = ?";
            $params[] = $currentUser['zone'];
            $params[] = $currentUser['wereda'];
            $params[] = $currentUser['kebele'];
            $types .= "sss";
        }
    }
    
    // Add search filter
    if (!empty($search)) {
        $conditions[] = "(name LIKE ? OR email LIKE ? OR role LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }
    
    $sql = "SELECT id, name, email, role, zone, wereda, kebele 
            FROM users 
            WHERE " . implode(' AND ', $conditions) . "
            ORDER BY name ASC 
            LIMIT 50";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Format user data for display
    foreach ($users as &$user) {
        $user['display_name'] = $user['name'];
        $user['display_role'] = ucwords(str_replace('_', ' ', $user['role']));
        $user['location'] = implode(' > ', array_filter([$user['zone'], $user['wereda'], $user['kebele']]));
        
        // Remove sensitive data
        unset($user['email']);
    }
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
    
} catch (Exception $e) {
    error_log("Users API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading users'
    ]);
}

$conn->close();
?>