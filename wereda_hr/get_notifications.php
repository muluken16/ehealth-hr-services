<?php
session_start();
include dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

header('Content-Type: application/json');

$user_woreda = isset($_SESSION['woreda']) ? $_SESSION['woreda'] : 'Woreda 1';

try {
    $notifications = [];

    // 1. Pending Leave Requests (Alerts)
    $stmt = $conn->prepare("SELECT e.first_name, e.last_name, e.kebele, lr.leave_type, lr.created_at, lr.id 
                            FROM leave_requests lr 
                            JOIN employees e ON lr.employee_id = e.employee_id 
                            WHERE e.woreda = ? AND lr.status = 'pending' 
                            ORDER BY lr.created_at DESC LIMIT 5");
    $stmt->bind_param("s", $user_woreda);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $notifications[] = [
            'id' => 'leave_' . $row['id'],
            'type' => 'leave',
            'icon' => 'fas fa-umbrella-beach',
            'title' => 'New Leave Request',
            'message' => "{$row['first_name']} (Kebele: {$row['kebele']}) requested {$row['leave_type']} leave.",
            'time' => $row['created_at'],
            'color' => '#ff7e5f',
            'unread' => true,
            'link' => 'wereda_hr_leave.php'
        ];
    }

    // 2. Recent Employee Registrations in Kebeles
    $stmt = $conn->prepare("SELECT first_name, last_name, kebele, created_at, id 
                            FROM employees 
                            WHERE woreda = ? 
                            ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param("s", $user_woreda);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $notifications[] = [
            'id' => 'emp_' . $row['id'],
            'type' => 'registration',
            'icon' => 'fas fa-user-plus',
            'title' => 'New Registration',
            'message' => "{$row['first_name']} {$row['last_name']} registered in {$row['kebele']}.",
            'time' => $row['created_at'],
            'color' => '#4cb5ae',
            'unread' => true,
            'link' => 'wereda_hr_employee.php'
        ];
    }

    // 3. Mock Messages (as requested "new mess")
    $notifications[] = [
        'id' => 'msg_1',
        'type' => 'message',
        'icon' => 'fas fa-envelope',
        'title' => 'New Message',
        'message' => 'Zone Health Office sent a new HR directive.',
        'time' => date('Y-m-d H:i:s', strtotime('-1 hour')),
        'color' => '#3498db',
        'unread' => true,
        'link' => 'wereda_hr_reports.php'
    ];

    // Sort by time
    usort($notifications, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });

    // Take top 8
    $notifications = array_slice($notifications, 0, 8);

    // Human readable time
    function time_ago($datetime) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);
        
        if ($diff->d > 0) return $diff->d . 'd ago';
        if ($diff->h > 0) return $diff->h . 'h ago';
        if ($diff->i > 0) return $diff->i . 'm ago';
        return 'just now';
    }

    foreach ($notifications as &$n) {
        $n['time_ago'] = time_ago($n['time']);
    }

    echo json_encode(['success' => true, 'notifications' => $notifications, 'count' => count($notifications)]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
