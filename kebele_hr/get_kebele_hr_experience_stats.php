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

// Calculate tenure/experience groups based on join_date
$sql = "SELECT 
    CASE 
        WHEN TIMESTAMPDIFF(YEAR, join_date, CURDATE()) < 1 THEN '< 1 Year'
        WHEN TIMESTAMPDIFF(YEAR, join_date, CURDATE()) BETWEEN 1 AND 3 THEN '1-3 Years'
        WHEN TIMESTAMPDIFF(YEAR, join_date, CURDATE()) BETWEEN 4 AND 7 THEN '4-7 Years'
        WHEN TIMESTAMPDIFF(YEAR, join_date, CURDATE()) BETWEEN 8 AND 12 THEN '8-12 Years'
        ELSE '12+ Years'
    END as tenure_group,
    COUNT(*) as count
FROM employees 
WHERE working_woreda = ? AND working_kebele = ? 
AND join_date IS NOT NULL AND join_date != '0000-00-00'
GROUP BY tenure_group";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $user_woreda, $user_kebele);
$stmt->execute();
$result = $stmt->get_result();

$stats = [];
while ($row = $result->fetch_assoc()) {
    $stats[$row['tenure_group']] = (int) $row['count'];
}

$conn->close();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $stats]);
?>