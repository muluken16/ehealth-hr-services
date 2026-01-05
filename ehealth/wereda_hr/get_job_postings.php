<?php
session_start();
include dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

header('Content-Type: application/json');

$user_woreda = isset($_SESSION['woreda']) ? $_SESSION['woreda'] : 'Woreda 1';

try {
    $stmt = $conn->prepare("SELECT id, title, department, employment_type as type, location, posted_at as posted_date, (SELECT COUNT(*) FROM job_applications WHERE job_id = job_postings.id) as applications_count FROM job_postings WHERE woreda LIKE ? ORDER BY posted_at DESC LIMIT 5");
    $woreda_param = "%$user_woreda%";
    $stmt->bind_param("s", $woreda_param);
    $stmt->execute();
    $result = $stmt->get_result();

    $jobs = [];
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }

    echo json_encode(['success' => true, 'jobs' => $jobs]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
