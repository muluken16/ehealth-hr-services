<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

$woreda = $_SESSION['woreda'] ?? 'Woreda 1';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$department = isset($_GET['department']) ? $_GET['department'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

try {
    $conn = getDBConnection();
    
    $query = "SELECT * FROM employees WHERE woreda LIKE ?";
    $params = ["%$woreda%"];
    $types = "s";

    if ($search) {
        $query .= " AND (first_name LIKE ? OR last_name LIKE ? OR employee_id LIKE ?)";
        $searchParam = "%$search%";
        array_push($params, $searchParam, $searchParam, $searchParam);
        $types .= "sss";
    }

    if ($department) {
        $query .= " AND department_assigned = ?";
        array_push($params, $department);
        $types .= "s";
    }

    if ($status) {
        $query .= " AND status = ?";
        array_push($params, $status);
        $types .= "s";
    }

    // Get Total Count for pagination
    $countQuery = str_replace("SELECT *", "SELECT COUNT(*) as total", $query);
    $stmtCount = $conn->prepare($countQuery);
    $stmtCount->bind_param($types, ...$params);
    $stmtCount->execute();
    $totalResult = $stmtCount->get_result()->fetch_assoc();
    $totalRows = $totalResult['total'];

    // Get Paginated Data
    $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    array_push($params, $limit, $offset);
    $types .= "ii";

    $stmt = $conn->prepare($query);
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
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($totalRows / $limit)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'employees' => [],
        'total' => 0
    ]);
}
?>