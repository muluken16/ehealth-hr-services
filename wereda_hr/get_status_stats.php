<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

$woreda = $_SESSION['woreda'] ?? 'Woreda 1';

try {
    $conn = getDBConnection();
    
    // Get employee status statistics
    $stmt = $conn->prepare("SELECT 
                CASE 
                    WHEN status = 'active' THEN 'Active'
                    WHEN status = 'on-leave' THEN 'On Leave'
                    WHEN status = 'inactive' THEN 'Inactive'
                    ELSE 'Unknown'
                END as status_label,
                COUNT(*) as count
            FROM employees 
            WHERE woreda LIKE ?
            GROUP BY status
            ORDER BY count DESC");
            
    $woreda_param = "%$woreda%";
    $stmt->bind_param("s", $woreda_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[$row['status_label']] = (int)$row['count'];
        }
    }
    
    // If no data, return empty
    if (empty($data)) {
        $data = [];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => []
    ]);
}
?>