<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require_once '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'wereda_health_officer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$response = [
    'success' => true,
    'stats' => [],
    'recent_employees' => [],
    'leave_requests' => [],
    'activities' => []
];

try {
    $conn = getDBConnection();
    $user_woreda = $_SESSION['woreda'] ?? 'West Shewa Woreda 1';
    $user_woreda_escaped = $conn->real_escape_string($user_woreda);

    // 1. Stats
    $stats = [];

    // Total Employees (Entire Woreda + All Kebeles)
    $res = $conn->query("SELECT COUNT(*) as count FROM employees WHERE woreda = '$user_woreda_escaped'");
    $stats['totalEmployees'] = ($res && $row = $res->fetch_assoc()) ? $row['count'] : 0;

    // Active Employees
    $res = $conn->query("SELECT COUNT(*) as count FROM employees WHERE woreda = '$user_woreda_escaped' AND status = 'active'");
    $stats['activeEmployees'] = ($res && $row = $res->fetch_assoc()) ? $row['count'] : 0;

    // On Leave
    $res = $conn->query("SELECT COUNT(*) as count FROM employees WHERE woreda = '$user_woreda_escaped' AND status = 'on_leave'");
    $stats['onLeave'] = ($res && $row = $res->fetch_assoc()) ? $row['count'] : 0;

    // Total Kebeles Overseen
    $res = $conn->query("SELECT COUNT(DISTINCT kebele) as count FROM employees WHERE woreda = '$user_woreda_escaped' AND kebele IS NOT NULL AND kebele != ''");
    $stats['totalKebeles'] = ($res && $row = $res->fetch_assoc()) ? $row['count'] : 0;

    $response['stats'] = $stats;

    // 2. Employees (All for Woreda & Kebeles)
    $res = $conn->query("SELECT id, employee_id, first_name, last_name, email, department_assigned, position, join_date, status, kebele FROM employees WHERE woreda = '$user_woreda_escaped' ORDER BY first_name ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $response['recent_employees'][] = $row;
        }
    }

    // 3. Pending Leave Requests
    $sql_leave = "SELECT lr.*, e.first_name, e.last_name, e.department_assigned, e.kebele 
                  FROM leave_requests lr 
                  JOIN employees e ON lr.employee_id = e.employee_id 
                  WHERE e.woreda = '$user_woreda_escaped' AND lr.status = 'pending' 
                  ORDER BY lr.created_at DESC LIMIT 5";
    $res_leave = $conn->query($sql_leave);
    if ($res_leave) {
        while ($row = $res_leave->fetch_assoc()) {
            $response['leave_requests'][] = $row;
        }
    }

    // 4. Mock Activities
    $response['activities'] = [
        ['type' => 'user-plus', 'title' => 'New Staff Registered', 'desc' => 'Medical staff joined Kebele center', 'time' => '1 hour ago'],
        ['type' => 'check-circle', 'title' => 'Inventory Updated', 'desc' => 'Medical supplies replenished', 'time' => '3 hours ago'],
        ['type' => 'clock', 'title' => 'Shift Change', 'desc' => 'Night shift team checked in', 'time' => '5 hours ago']
    ];

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>