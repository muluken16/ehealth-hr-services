<?php
session_start();
if (!isset($_SESSION['woreda']))
    $_SESSION['woreda'] = 'Woreda 1';
if (!isset($_SESSION['kebele']))
    $_SESSION['kebele'] = '01';

$user_woreda = $_SESSION['woreda'];
$user_kebele = $_SESSION['kebele'];

include dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

// Calculate salary brackets
$sql = "SELECT 
    CASE 
        WHEN salary < 5000 THEN 'Under 5k'
        WHEN salary BETWEEN 5000 AND 10000 THEN '5k-10k'
        WHEN salary BETWEEN 10001 AND 20000 THEN '10k-20k'
        WHEN salary BETWEEN 20001 AND 35000 THEN '20k-35k'
        ELSE 'Over 35k'
    END as salary_bracket,
    COUNT(*) as count
FROM employees 
WHERE working_woreda = ? AND working_kebele = ? 
AND salary IS NOT NULL AND salary > 0
GROUP BY salary_bracket";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $user_woreda, $user_kebele);
$stmt->execute();
$result = $stmt->get_result();

$stats = [];
while ($row = $result->fetch_assoc()) {
    $stats[$row['salary_bracket']] = (int) $row['count'];
}

$conn->close();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $stats]);
?>