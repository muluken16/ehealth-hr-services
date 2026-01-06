<?php
session_start();
// Ensure Kebele is set
if (!isset($_SESSION['kebele'])) {
    $_SESSION['kebele'] = 'Kebele 1';
}
$user_kebele = $_SESSION['kebele'];

header('Content-Type: application/json');

include dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

try {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $department = isset($_GET['department']) ? $_GET['department'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';

    // Build query with filters - Strict EXACT match for security
    $sql = "SELECT * FROM employees WHERE working_kebele = ? ";
    $params = [$user_kebele];
    $types = "s";

    if (!empty($search)) {
        $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR employee_id LIKE ? OR email LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ssss";
    }

    if (!empty($department)) {
        $sql .= " AND (department LIKE ? OR department_assigned LIKE ?)";
        $params[] = "%$department%";
        $params[] = "%$department%";
        $types .= "ss";
    }

    if (!empty($status)) {
        $sql .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }

    // Get total count for pagination
    $countSql = "SELECT COUNT(*) as total FROM employees WHERE working_kebele = ? ";
    $countParams = [$user_kebele];
    $countTypes = "s";
    
    if (!empty($search)) {
        $countSql .= " AND (first_name LIKE ? OR last_name LIKE ? OR employee_id LIKE ? OR email LIKE ?)";
        $countParams[] = "%$search%"; $countParams[] = "%$search%"; $countParams[] = "%$search%"; $countParams[] = "%$search%";
        $countTypes .= "ssss";
    }
    if (!empty($department)) {
        $countSql .= " AND (department LIKE ? OR department_assigned LIKE ?)";
        $countParams[] = "%$department%"; $countParams[] = "%$department%";
        $countTypes .= "ss";
    }
    if (!empty($status)) {
        $countSql .= " AND status = ?";
        $countParams[] = $status;
        $countTypes .= "s";
    }

    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param($countTypes, ...$countParams);
    $countStmt->execute();
    $totalResult = $countStmt->get_result();
    $totalRows = $totalResult->fetch_assoc()['total'];
    $countStmt->close();

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;
    $totalPages = ceil($totalRows / $limit);

    $sql .= " ORDER BY join_date DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $employees = [];
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'employees' => $employees,
        'total' => $totalRows,
        'total_pages' => $totalPages,
        'current_page' => $page
    ]);
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>