<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'zone_health_officer') {
    http_response_code(403);
    exit();
}

include '../db.php';
$conn = getDBConnection();

if (isset($_GET['id'])) {
    $user_zone = $_SESSION['zone'] ?? 'West Shewa';
    $stmt = $conn->prepare("SELECT * FROM inventory WHERE id = ? AND zone = ?");
    $stmt->bind_param("is", $_GET['id'], $user_zone);
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