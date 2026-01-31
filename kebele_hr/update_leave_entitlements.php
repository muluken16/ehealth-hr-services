<?php
session_start();
header('Content-Type: application/json');

require_once '../db.php';
$conn = getDBConnection();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['employee_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$employee_id = $conn->real_escape_string($data['employee_id']);
$annual = intval($data['annual_leave_days']);
$carry = intval($data['carry_forward_days'] ?? 0);
$used_annual = intval($data['used_annual_leave']);
$sick = intval($data['sick_leave_days']);
$used_sick = intval($data['used_sick_leave']);
$emergency = intval($data['emergency_leave_days']);
$used_emergency = intval($data['used_emergency_leave']);
$marriage = intval($data['marriage_leave_days'] ?? 3);
$used_marriage = intval($data['used_marriage_leave'] ?? 0);
$bereavement = intval($data['bereavement_leave_days'] ?? 3);
$used_bereavement = intval($data['used_bereavement_leave'] ?? 0);
$year = date('Y');

try {
    $sql = "UPDATE leave_entitlements SET 
            annual_leave_days = ?, 
            carry_forward_days = ?,
            used_annual_leave = ?,
            sick_leave_days = ?, 
            used_sick_leave = ?,
            emergency_leave_days = ?, 
            used_emergency_leave = ?,
            marriage_leave_days = ?,
            used_marriage_leave = ?,
            bereavement_leave_days = ?,
            used_bereavement_leave = ?
            WHERE employee_id = ? AND year = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiiiiiiiiiss", $annual, $carry, $used_annual, $sick, $used_sick, $emergency, $used_emergency, $marriage, $used_marriage, $bereavement, $used_bereavement, $employee_id, $year);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Entitlements updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>