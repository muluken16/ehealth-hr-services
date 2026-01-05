<?php
session_start();
// Ensure Kebele is set
if (!isset($_SESSION['kebele'])) {
    $_SESSION['kebele'] = 'Kebele 1';
}
$user_kebele = $_SESSION['kebele'];

header('Content-Type: application/json');

include dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

try {
    // Get employees for this kebele
    $sql = "SELECT * FROM employees WHERE kebele LIKE ? ORDER BY join_date DESC";
    
    $stmt = $conn->prepare($sql);
    $kebele_param = "%$user_kebele%";
    $stmt->bind_param("s", $kebele_param);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $employees = [];
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'employees' => $employees,
        'total_count' => count($employees)
    ]);
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>