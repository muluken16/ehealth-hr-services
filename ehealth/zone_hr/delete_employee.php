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

if (!$data || !isset($data['employee_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$employee_id = $data['employee_id'];
$zone = $_SESSION['zone'];

$sql = "DELETE FROM employees WHERE employee_id = ? AND zone = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $employee_id, $zone);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>