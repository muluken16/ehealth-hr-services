<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

$woreda = $_SESSION['woreda'] ?? 'Woreda 1';

try {
    $conn = getDBConnection();
    
    // Get academic qualification statistics
    $stmt = $conn->prepare("SELECT 
                CASE 
                    WHEN education_level = 'primary' THEN 'Primary'
                    WHEN education_level = 'secondary' THEN 'Secondary'
                    WHEN education_level = 'diploma' THEN 'Diploma'
                    WHEN education_level = 'degree' THEN 'Degree'
                    WHEN education_level = 'masters' THEN 'Masters'
                    WHEN education_level = 'phd' THEN 'PhD'
                    ELSE 'Other'
                END as education_label,
                COUNT(*) as count
            FROM employees 
            WHERE status = 'active' AND woreda LIKE ?
            GROUP BY education_level
            ORDER BY count DESC");
            
    $woreda_param = "%$woreda%";
    $stmt->bind_param("s", $woreda_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[$row['education_label']] = (int)$row['count'];
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