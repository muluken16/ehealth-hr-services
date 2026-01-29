<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'wereda_hr') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include '../db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$conn = getDBConnection();

$type = $data['type'];
$format = $data['format'] ?: 'json';
$start_date = $data['start_date'];
$end_date = $data['end_date'];
$generated_by = $_SESSION['user_id'];

$report_data = [];

switch ($type) {
    case 'employee_summary':
        $sql = "SELECT COUNT(*) as total_employees,
                       SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_employees,
                       SUM(CASE WHEN status = 'on-leave' THEN 1 ELSE 0 END) as on_leave,
                       SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as male_count,
                       SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as female_count
                FROM employees";
        $result = $conn->query($sql);
        $report_data = $result->fetch_assoc();
        break;

    case 'payroll_report':
        $where = "";
        if ($start_date && $end_date) {
            $where = "WHERE period_start >= '$start_date' AND period_end <= '$end_date'";
        }
        $sql = "SELECT p.*, e.first_name, e.last_name, e.department
                FROM payroll p
                JOIN employees e ON p.employee_id = e.employee_id
                $where
                ORDER BY p.period_end DESC";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
        }
        break;

    case 'leave_report':
        $where = "";
        if ($start_date && $end_date) {
            $where = "WHERE start_date >= '$start_date' AND end_date <= '$end_date'";
        }
        $sql = "SELECT lr.*, e.first_name, e.last_name, e.department
                FROM leave_requests lr
                JOIN employees e ON lr.employee_id = e.employee_id
                $where
                ORDER BY lr.created_at DESC";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
        }
        break;

    case 'recruitment_report':
        $where = "";
        if ($start_date && $end_date) {
            $where = "WHERE posted_at >= '$start_date' AND posted_at <= '$end_date'";
        }
        $sql = "SELECT jp.*, COUNT(ja.id) as applications_count
                FROM job_postings jp
                LEFT JOIN job_applications ja ON jp.id = ja.job_id
                $where
                GROUP BY jp.id
                ORDER BY jp.posted_at DESC";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
        }
        break;

    case 'training_report':
        $where = "";
        if ($start_date && $end_date) {
            $where = "WHERE session_date >= '$start_date' AND session_date <= '$end_date'";
        }
        $sql = "SELECT ts.*, COUNT(tp.id) as participants_count
                FROM training_sessions ts
                LEFT JOIN training_participants tp ON ts.id = tp.training_id
                $where
                GROUP BY ts.id
                ORDER BY ts.session_date DESC";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid report type']);
        exit();
}

// Save report to database
$title = ucfirst(str_replace('_', ' ', $type)) . ' Report';
$content = json_encode($report_data);

$sql = "INSERT INTO reports (type, title, content, generated_by, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssiss", $type, $title, $content, $generated_by, $start_date, $end_date);
$stmt->execute();
$stmt->close();

$conn->close();

// For now, return JSON data. In production, generate PDF/Excel
echo json_encode([
    'success' => true,
    'message' => 'Report generated successfully',
    'data' => $report_data,
    'type' => $type
]);
?>