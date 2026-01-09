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

// Calculate age groups
$sql = "SELECT 
    CASE 
        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 25 THEN 'Under 25'
        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 25 AND 35 THEN '25-35'
        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 36 AND 45 THEN '36-45'
        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 46 AND 55 THEN '46-55'
        ELSE '55+'
    END as age_group,
    COUNT(*) as count
FROM employees 
WHERE working_woreda = ? AND working_kebele = ? 
AND date_of_birth IS NOT NULL AND date_of_birth != '0000-00-00'
GROUP BY age_group
ORDER BY age_group";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $user_woreda, $user_kebele);
$stmt->execute();
$result = $stmt->get_result();

$stats = [];
while ($row = $result->fetch_assoc()) {
    $stats[$row['age_group']] = (int) $row['count'];
}

$conn->close();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $stats]);
?>