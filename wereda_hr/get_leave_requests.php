<?php
session_start();
header('Content-Type: application/json');

$woreda = $_SESSION['woreda'] ?? 'Woreda 1';

require_once dirname(__DIR__) . '/db.php';

try {
    $conn = getDBConnection();

    // Enhanced query with employee details for AI decision-making
    $stmt = $conn->prepare("
        SELECT 
            lr.id, 
            lr.employee_id, 
            lr.leave_type, 
            lr.start_date, 
            lr.end_date, 
            lr.reason, 
            lr.status, 
            lr.created_at,
            lr.ai_decision,
            lr.ai_reason,
            e.first_name, 
            e.middle_name,
            e.last_name, 
            e.gender,
            e.join_date,
            e.department_assigned as department,
            e.position,
            e.phone_number as phone,
            DATEDIFF(lr.end_date, lr.start_date) + 1 as requested_days,
            TIMESTAMPDIFF(YEAR, e.join_date, NOW()) as service_years,
            TIMESTAMPDIFF(MONTH, e.join_date, NOW()) % 12 as service_months
        FROM leave_requests lr
        JOIN employees e ON lr.employee_id = e.employee_id
        WHERE lr.status = 'pending' AND e.woreda LIKE ?
        ORDER BY lr.created_at DESC
    ");

    $woreda_param = "%$woreda%";
    $stmt->bind_param("s", $woreda_param);
    $stmt->execute();
    $result = $stmt->get_result();

    $leave_requests = [];
    while ($row = $result->fetch_assoc()) {
        // Add calculated fields
        $row['employee_name'] = trim("{$row['first_name']} {$row['middle_name']} {$row['last_name']}");

        // Add service info for quick reference
        $row['service_info'] = "{$row['service_years']} years, {$row['service_months']} months";

        $leave_requests[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $leave_requests,
        'total' => count($leave_requests)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching leave requests: ' . $e->getMessage()
    ]);
}

$conn->close();