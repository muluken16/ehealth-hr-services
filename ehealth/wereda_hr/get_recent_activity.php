<?php
session_start();
include dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

header('Content-Type: application/json');

$user_woreda = isset($_SESSION['woreda']) ? $_SESSION['woreda'] : 'Woreda 1';

try {
    $activities = [];

    // 1. Recent Employee Registrations
    $stmt = $conn->prepare("SELECT first_name, last_name, department_assigned, created_at FROM employees WHERE woreda = ? ORDER BY created_at DESC LIMIT 3");
    $stmt->bind_param("s", $user_woreda);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $activities[] = [
            'type' => 'user-plus',
            'title' => 'New Employee Registered',
            'desc' => "{$row['first_name']} {$row['last_name']} joined {$row['department_assigned']}.",
            'time' => $row['created_at'],
            'color' => '#4cb5ae'
        ];
    }

    // 2. Recent Leave Requests
    $stmt = $conn->prepare("SELECT e.first_name, e.last_name, lr.leave_type, lr.created_at FROM leave_requests lr JOIN employees e ON lr.employee_id = e.employee_id WHERE e.woreda = ? ORDER BY lr.created_at DESC LIMIT 3");
    $stmt->bind_param("s", $user_woreda);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $activities[] = [
            'type' => 'umbrella-beach',
            'title' => 'Leave Request',
            'desc' => "{$row['first_name']} {$row['last_name']} requested {$row['leave_type']} leave.",
            'time' => $row['created_at'],
            'color' => '#ff7e5f'
        ];
    }

    // 3. Recent Job Postings
    // Note: job_postings currently doesn't have a woreda column in the schema I saw in db.php? 
    // Wait, let me check job_postings schema in db.php again.
    // Line 427: title, department, description, requirements, salary_range, location, employment_type, application_deadline, status, posted_by, posted_at
    // No woreda. I'll just pull overall recent ones for now.
    $stmt = $conn->prepare("SELECT title, posted_at FROM job_postings ORDER BY posted_at DESC LIMIT 2");
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $activities[] = [
            'type' => 'bullhorn',
            'title' => 'New Job Posted',
            'desc' => "Role '{$row['title']}' is now open for applications.",
            'time' => $row['posted_at'],
            'color' => '#ffc107'
        ];
    }

    // Sort all by time
    usort($activities, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });

    // Take top 5
    $activities = array_slice($activities, 0, 5);

    // Human readable time function
    function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= ($diff->w * 7);

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    foreach ($activities as &$act) {
        $act['time_ago'] = time_elapsed_string($act['time']);
        $act['formatted_date'] = date('M d, Y h:i A', strtotime($act['time']));
    }

    echo json_encode(['success' => true, 'activities' => $activities]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
