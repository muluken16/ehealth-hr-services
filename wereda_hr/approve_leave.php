<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'wereda_hr') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../db.php';
require_once 'ethiopian_leave_ai.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['leave_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

try {
    $conn = getDBConnection();
    $leave_id = $data['leave_id'];
    $approved_by = $_SESSION['user_id'] ?? $_SESSION['user_name'];

    // Initialize Ethiopian Leave AI
    $ai = new EthiopianLeaveAI($conn);

    // Get AI decision
    $decision = $ai->evaluateLeaveRequest($leave_id);

    // If AI rejects, return rejection with detailed reasoning
    if ($decision['decision'] === 'Rejected') {
        echo json_encode([
            'success' => false,
            'ai_decision' => 'rejected',
            'message' => $decision['reason'],
            'details' => $decision,
            'law_reference' => $decision['law_reference'] ?? null
        ]);
        exit();
    }

    // AI APPROVED - Process the approval
    $stmt = $conn->prepare("UPDATE leave_requests SET status = 'approved', approved_by = ?, approved_at = NOW(), ai_decision = ?, ai_reason = ? WHERE id = ?");
    $ai_decision_json = json_encode($decision);
    $ai_reason = $decision['reason'];
    $stmt->bind_param("sssi", $approved_by, $ai_decision_json, $ai_reason, $leave_id);

    if (!$stmt->execute()) {
        throw new Exception('Error updating leave request: ' . $stmt->error);
    }

    // Deduct balance if applicable (for annual and sick leave)
    if (isset($decision['balance_to_deduct']) && $decision['balance_to_deduct'] > 0) {
        // Get leave type
        $leaveStmt = $conn->prepare("SELECT employee_id, leave_type FROM leave_requests WHERE id = ?");
        $leaveStmt->bind_param("i", $leave_id);
        $leaveStmt->execute();
        $leaveData = $leaveStmt->get_result()->fetch_assoc();

        if ($leaveData) {
            $employee_id = $leaveData['employee_id'];
            $leave_type = strtolower($leaveData['leave_type']);

            if (strpos($leave_type, 'annual') !== false) {
                // Deduct from annual leave balance
                $balanceStmt = $conn->prepare("UPDATE leave_balances SET annual_balance = annual_balance - ? WHERE employee_id = ?");
                $balanceStmt->bind_param("is", $decision['balance_to_deduct'], $employee_id);
                $balanceStmt->execute();
            }
        }
    }

    // Success response with AI decision details
    echo json_encode([
        'success' => true,
        'ai_decision' => 'approved',
        'message' => '✅ ' . $decision['reason'],
        'details' => $decision,
        'days_approved' => $decision['days_approved'],
        'balance_remaining' => $decision['balance_remaining'],
        'law_reference' => $decision['law_reference'] ?? null,
        'payment_status' => $decision['payment_status'] ?? 'Standard',
        'note' => $decision['note'] ?? null
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'System error: ' . $e->getMessage()
    ]);
}

$conn->close();