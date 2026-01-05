<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'wereda_health_officer') {
    http_response_code(403);
    exit();
}

include '../db.php';
$conn = getDBConnection();

if (isset($_GET['id'])) {
    $user_woreda = $_SESSION['woreda'] ?? 'Wereda 1';
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND woreda = ?");
    $stmt->bind_param("is", $_GET['id'], $user_woreda);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        http_response_code(404);
    }
    $stmt->close();
}

$conn->close();
?>