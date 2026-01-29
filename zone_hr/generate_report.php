<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'zone_hr') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include '../db.php';
$conn = getDBConnection();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$zone = $_SESSION['zone'];
$type = $data['type'];
$start_date = $data['start_date'];
$end_date = $data['end_date'];

$report_data = [];

switch ($type) {
    case 'employee_summary':
        $sql = "SELECT COUNT(*) as total, status, department FROM employees WHERE zone = ? GROUP BY status, department";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $zone);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
        }
        break;

    case 'payroll_report':
        $sql = "SELECT p.*, e.first_name, e.last_name FROM payroll p JOIN employees e ON p.employee_id = e.employee_id WHERE e.zone = ? AND p.period_start >= ? AND p.period_end <= ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $zone, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
        }
        break;

    case 'leave_report':
        $sql = "SELECT lr.*, e.first_name, e.last_name FROM leave_requests lr JOIN employees e ON lr.employee_id = e.employee_id WHERE e.zone = ? AND lr.created_at >= ? AND lr.created_at <= ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $zone, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
        }
        break;

    case 'recruitment_report':
        $sql = "SELECT jp.*, COUNT(ja.id) as applications FROM job_postings jp LEFT JOIN job_applications ja ON jp.id = ja.job_id WHERE jp.posted_at >= ? AND jp.posted_at <= ? GROUP BY jp.id";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
        }
        break;

    case 'training_report':
        $sql = "SELECT ts.*, COUNT(tp.id) as participants FROM training_sessions ts LEFT JOIN training_participants tp ON ts.id = tp.training_id WHERE ts.created_at >= ? AND ts.created_at <= ? GROUP BY ts.id";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid report type']);
        exit();
}

echo json_encode(['success' => true, 'data' => $report_data]);

$conn->close();
?>