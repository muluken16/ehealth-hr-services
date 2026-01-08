<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../db.php';
$conn = getDBConnection();

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['leave_id'])) {
    echo json_encode(['success' => false, 'message' => 'Leave ID required']);
    exit;
}

$leave_id = $data['leave_id'];
$reason = $data['reason'] ?? 'Rejected by HR';
$admin_id = $_SESSION['user_id'];
$user_kebele = $_SESSION['kebele'] ?? 'Kebele 1';

try {
    // 1. Verify verify kebele
    $sql = "SELECT e.kebele 
            FROM leave_requests lr
            JOIN employees e ON lr.employee_id = e.employee_id
            WHERE lr.id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Leave request not found");
    }
    
    $row = $result->fetch_assoc();
    if ($row['kebele'] !== $user_kebele) {
        throw new Exception("Unauthorized: You can only reject leaves for your Kebele.");
    }
    $stmt->close();

    // 2. Update leave request status
    $stmt = $conn->prepare("UPDATE leave_requests SET status = 'rejected', rejected_reason = ?, approved_by = ?, approved_at = NOW() WHERE id = ?");
    $stmt->bind_param("sii", $reason, $admin_id, $leave_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception($conn->error);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>