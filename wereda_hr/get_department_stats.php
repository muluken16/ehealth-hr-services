<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

$woreda = $_SESSION['woreda'] ?? 'Woreda 1';

try {
    $conn = getDBConnection();
    
    // Get department statistics
    $stmt = $conn->prepare("SELECT 
                CASE 
                    WHEN department_assigned IS NULL OR department_assigned = '' THEN 'Unassigned'
                    ELSE department_assigned
                END as dept_name,
                COUNT(*) as count
            FROM employees 
            WHERE status = 'active' AND woreda LIKE ?
            GROUP BY department_assigned
            ORDER BY count DESC");
            
    $woreda_param = "%$woreda%";
    $stmt->bind_param("s", $woreda_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[$row['dept_name']] = (int)$row['count'];
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