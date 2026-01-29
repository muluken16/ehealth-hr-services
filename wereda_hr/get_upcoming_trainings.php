<?php
session_start();
include dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

header('Content-Type: application/json');

try {
    $user_woreda = $_SESSION['woreda'] ?? 'Woreda 1';
    $stmt = $conn->prepare("SELECT title, description, trainer, session_date, start_time, end_time, venue, status FROM training_sessions WHERE session_date >= CURDATE() AND woreda LIKE ? ORDER BY session_date ASC LIMIT 5");
    $woreda_param = "%$user_woreda%";
    $stmt->bind_param("s", $woreda_param);
    $stmt->execute();
    $result = $stmt->get_result();

    $trainings = [];
    while ($row = $result->fetch_assoc()) {
        $trainings[] = $row;
    }

    echo json_encode(['success' => true, 'trainings' => $trainings]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
