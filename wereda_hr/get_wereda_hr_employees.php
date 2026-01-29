<?php
session_start();
header('Content-Type: application/json');

require_once '../db.php';
$conn = getDBConnection();

$export = $_GET['export'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = min(100, max(1, intval($_GET['limit'] ?? 10)));
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$department = $_GET['department'] ?? '';
$gender = $_GET['gender'] ?? '';

try {
    $where = [];
    $params = [];
    $types = '';

    if (!empty($search)) {
        $where[] = "(first_name LIKE ? OR last_name LIKE ? OR employee_id LIKE ? OR position LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        $types .= 'ssss';
    }

    if (!empty($status)) {
        $where[] = "status = ?";
        $params[] = $status;
        $types .= 's';
    }

    if (!empty($department)) {
        $where[] = "department_assigned = ?";
        $params[] = $department;
        $types .= 's';
    }

    if (!empty($gender)) {
        $where[] = "gender = ?";
        $params[] = $gender;
        $types .= 's';
    }

    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    $countSql = "SELECT COUNT(*) as total FROM employees $whereClause";
    $countStmt = $conn->prepare($countSql);
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $total = $countResult->fetch_assoc()['total'];
    $countStmt->close();

    if ($export === 'csv') {
        $sql = "SELECT employee_id, first_name, middle_name, last_name, gender, phone_number, email, 
                       position, department_assigned, status, join_date, salary 
                FROM employees $whereClause ORDER BY id DESC";
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="employees_export_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'ID',
            'First Name',
            'Middle Name',
            'Last Name',
            'Gender',
            'Phone',
            'Email',
            'Position',
            'Department',
            'Status',
            'Join Date',
            'Salary'
        ]);

        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['employee_id'] ?? '',
                $row['first_name'] ?? '',
                $row['middle_name'] ?? '',
                $row['last_name'] ?? '',
                $row['gender'] ?? '',
                $row['phone_number'] ?? '',
                $row['email'] ?? '',
                $row['position'] ?? '',
                $row['department_assigned'] ?? '',
                $row['status'] ?? '',
                $row['join_date'] ?? '',
                $row['salary'] ?? ''
            ]);
        }
        fclose($output);
        $stmt->close();
        exit;
    }

    $sql = "SELECT id, employee_id, first_name, middle_name, last_name, gender, phone_number, email, 
                   position, department_assigned, status, join_date, salary, photo, working_kebele
            FROM employees $whereClause ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $bindParams = array_merge($params, [$limit, $offset]);
        $bindTypes = $types . 'ii';
        $stmt->bind_param($bindTypes, ...$bindParams);
    } else {
        $stmt->bind_param('ii', $limit, $offset);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $employees = [];
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    $stmt->close();

    $statsSql = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = 'on-leave' THEN 1 ELSE 0 END) as on_leave,
        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
        FROM employees $whereClause";
    $statsStmt = $conn->prepare($statsSql);
    if (!empty($params)) {
        $statsStmt->bind_param($types, ...$params);
    }
    $statsStmt->execute();
    $stats = $statsStmt->get_result()->fetch_assoc();
    $statsStmt->close();

    echo json_encode([
        'success' => true,
        'employees' => $employees,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    error_log("Error in get_wereda_hr_employees.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
