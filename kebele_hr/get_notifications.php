<?php
session_start();
include dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

header('Content-Type: application/json');

$user_kebele = isset($_SESSION['kebele']) ? $_SESSION['kebele'] : 'Kebele 1';

try {
    $notifications = [];

    // 1. Pending Leave Requests for this Kebele
    $stmt = $conn->prepare("SELECT e.first_name, e.last_name, e.working_kebele as kebele, lr.leave_type, lr.created_at, lr.id 
                            FROM leave_requests lr 
                            JOIN employees e ON lr.employee_id = e.employee_id 
                            WHERE e.working_kebele = ? AND lr.status = 'pending' 
                            ORDER BY lr.created_at DESC LIMIT 5");
    $stmt->bind_param("s", $user_kebele);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $notifications[] = [
            'id' => 'leave_' . $row['id'],
            'type' => 'leave',
            'icon' => 'fas fa-umbrella-beach',
            'title' => 'Leave Request',
            'message' => "{$row['first_name']} requested {$row['leave_type']} leave.",
            'time' => $row['created_at'],
            'color' => '#ff7e5f',
            'unread' => true,
            'link' => 'hr-leave.php'
        ];
    }

    // 2. Recent Employee Registrations in this Kebele
    $stmt = $conn->prepare("SELECT first_name, last_name, created_at, id
                            FROM employees
                            WHERE kebele = ?
                            ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param("s", $user_kebele);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $notifications[] = [
            'id' => 'emp_' . $row['id'],
            'type' => 'registration',
            'icon' => 'fas fa-user-plus',
            'title' => 'New Staff',
            'message' => "{$row['first_name']} {$row['last_name']} was registered.",
            'time' => $row['created_at'],
            'color' => '#4cb5ae',
            'unread' => true,
            'link' => 'hr-employees.php'
        ];
    }

    // 3. System Alert
    $notifications[] = [
        'id' => 'sys_1',
        'type' => 'system',
        'icon' => 'fas fa-shield-alt',
        'title' => 'Policy Update',
        'message' => 'New HR policy for Kebele staff has been published.',
        'time' => date('Y-m-d H:i:s', strtotime('-2 hours')),
        'color' => '#1a4a5f',
        'unread' => true,
        'link' => 'hr-reports.php'
    ];

    // Sort by time
    usort($notifications, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });

    $notifications = array_slice($notifications, 0, 8);

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
