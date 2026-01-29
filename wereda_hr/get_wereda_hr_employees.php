<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

$woreda = $_SESSION['woreda'] ?? 'Woreda 1';
$woreda_wildcard = "%$woreda%";

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$department = isset($_GET['department']) ? $_GET['department'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$kebele = isset($_GET['kebele']) ? $_GET['kebele'] : '';

$offset = ($page - 1) * $limit;

try {
    $conn = getDBConnection();

    // Base query with woreda filter
    $where = "WHERE woreda LIKE ?";
    $params = [$woreda_wildcard];
    $types = "s";

    if (!empty($search)) {
        $where .= " AND (first_name LIKE ? OR last_name LIKE ? OR employee_id LIKE ? OR middle_name LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
        $types .= "ssss";
    }

    if (!empty($department)) {
        $where .= " AND department_assigned = ?";
        $params[] = $department;
        $types .= "s";
    }

    if (!empty($status)) {
        $where .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }

    if (!empty($kebele)) {
        $where .= " AND (working_kebele = ? OR kebele = ?)";
        $params[] = $kebele;
        $params[] = $kebele;
        $types .= "ss";
    }

    // Count total
    $count_sql = "SELECT COUNT(*) as total FROM employees $where";
    $stmt = $conn->prepare($count_sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];

    // Get employees
    $sql = "SELECT * FROM employees $where ORDER BY id DESC LIMIT ? OFFSET ?";
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

    // Get stats
    $stats_sql = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = 'on-leave' THEN 1 ELSE 0 END) as leave_count
        FROM employees WHERE woreda LIKE ?";
    $stmt = $conn->prepare($stats_sql);
    $stmt->bind_param("s", $woreda_wildcard);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();

    echo json_encode([
        'success' => true,
        'employees' => $employees,
        'total' => (int) $total,
        'total_pages' => ceil($total / $limit),
        'stats' => $stats
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
