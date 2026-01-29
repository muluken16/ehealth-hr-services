<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

$woreda = $_SESSION['woreda'] ?? 'Woreda 1';

try {
    $conn = getDBConnection();
    
    // Get job level statistics
    $stmt = $conn->prepare("SELECT 
                CASE 
                    WHEN job_level IS NULL OR job_level = '' THEN 'Unassigned'
                    WHEN job_level = 'senior' THEN 'Senior'
                    WHEN job_level = 'junior' THEN 'Junior'
                    WHEN job_level = 'manager' THEN 'Manager'
                    WHEN job_level = 'director' THEN 'Director'
                    ELSE job_level
                END as level_name,
                COUNT(*) as count
            FROM employees 
            WHERE status = 'active' AND woreda LIKE ?
            GROUP BY job_level
            ORDER BY count DESC");
            
    $woreda_param = "%$woreda%";
    $stmt->bind_param("s", $woreda_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[$row['level_name']] = (int)$row['count'];
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