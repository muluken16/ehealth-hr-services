<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require_once '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'wereda_hr') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$response = [
    'success' => true,
    'stats' => [],
    'recent_employees' => [],
    'leave_requests' => [],
    'activities' => [], // For now empty or static
    'job_postings' => [],
    'trainings' => []
];

try {
    $conn = getDBConnection();
    $woreda_wildcard = "%" . ($_SESSION['woreda'] ?? 'Woreda 1') . "%";

    // 1. Stats
    $stats = [];
    
    // Total Employees
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM employees WHERE woreda LIKE ?");
    $stmt->bind_param("s", $woreda_wildcard);
    $stmt->execute();
    $stats['totalEmployees'] = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

    // Active Employees
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM employees WHERE woreda LIKE ? AND status = 'active'");
    $stmt->bind_param("s", $woreda_wildcard);
    $stmt->execute();
    $stats['activeEmployees'] = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

    // On Leave
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM employees WHERE woreda LIKE ? AND status = 'on-leave'");
    $stmt->bind_param("s", $woreda_wildcard);
    $stmt->execute();
    $stats['onLeave'] = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

    // Attendance Rate (Mock calc)
    $stats['attendanceRate'] = ($stats['totalEmployees'] > 0) ? round(($stats['activeEmployees'] / $stats['totalEmployees']) * 100) : 0;

    $response['stats'] = $stats;

    // 2. Recent Employees (Top 5)
    $stmt = $conn->prepare("SELECT id, employee_id, first_name, last_name, email, department_assigned, position, join_date, status FROM employees WHERE woreda LIKE ? ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param("s", $woreda_wildcard);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $response['recent_employees'][] = $row;
    }

    // 3. Pending Leave Requests (Top 5)
    $stmt = $conn->prepare("SELECT lr.id, lr.start_date, lr.end_date, lr.leave_type, lr.reason, e.first_name, e.last_name, e.department_assigned, e.employee_id 
                            FROM leave_requests lr 
                            JOIN employees e ON lr.employee_id = e.employee_id 
                            WHERE e.woreda LIKE ? AND lr.status = 'pending' 
                            ORDER BY lr.created_at DESC LIMIT 5");
    $stmt->bind_param("s", $woreda_wildcard);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $response['leave_requests'][] = $row;
    }

    // 4. Job Postings (Top 5) - Assuming table 'job_postings' exists
    // Handle table not existing gracefully
    $checkTable = $conn->query("SHOW TABLES LIKE 'job_postings'");
    if ($checkTable->num_rows > 0) {
        $stmt = $conn->prepare("SELECT * FROM job_postings WHERE (woreda LIKE ? OR woreda IS NULL) AND status = 'open' ORDER BY posted_at DESC LIMIT 5");
        $stmt->bind_param("s", $woreda_wildcard);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $response['job_postings'][] = $row;
        }
    }

    // 5. Training Sessions (Top 3) - Assuming table 'training_sessions' or 'training_programs' exists
    // Removed code used 'training_sessions' but some context suggested 'training_programs'. I'll check both or assume one. 
    // I'll try 'training_sessions' as per the removed code.
    $checkTable = $conn->query("SHOW TABLES LIKE 'training_sessions'");
    if ($checkTable->num_rows > 0) {
        $stmt = $conn->prepare("SELECT * FROM training_sessions WHERE (woreda LIKE ? OR woreda IS NULL) AND session_date >= CURDATE() ORDER BY session_date ASC LIMIT 3");
        $stmt->bind_param("s", $woreda_wildcard);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $response['trainings'][] = $row;
        }
    }

    // Mock Activities (since we don't have an activity log table defined clearly yet)
    $response['activities'] = [
        ['type' => 'user-plus', 'title' => 'New Employee Registered', 'desc' => 'Dr. Abebe Kebede joined Medical Dept', 'time' => '2 hours ago', 'bg' => '#eff6ff', 'color' => '#2563eb'],
        ['type' => 'file-alt', 'title' => 'Leave Request Approved', 'desc' => 'Annual leave for Sara T. approved', 'time' => '4 hours ago', 'bg' => '#ecfdf5', 'color' => '#059669'],
        ['type' => 'exclamation-circle', 'title' => 'System Alert', 'desc' => 'Monthly attendance report due', 'time' => '1 day ago', 'bg' => '#fffbeb', 'color' => '#d97706']
    ];

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
