<?php
header('Content-Type: application/json');

$diagnostics = [
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'files_exist' => [],
    'database_test' => null,
    'endpoints_test' => []
];

// Check if required files exist
$requiredFiles = [
    'get_hr_stats.php',
    'get_gender_stats.php',
    'get_academic_stats.php',
    'get_department_stats.php',
    'get_job_level_stats.php',
    'get_status_stats.php',
    'get_employees.php',
    'get_leave_requests.php',
    'get_recent_activity.php'
];

foreach ($requiredFiles as $file) {
    $diagnostics['files_exist'][$file] = file_exists($file);
}

// Test database connection
try {
    require_once '../db.php';
    $conn = getDBConnection();
    
    $result = $conn->query("SELECT COUNT(*) as count FROM employees");
    if ($result) {
        $row = $result->fetch_assoc();
        $diagnostics['database_test'] = [
            'success' => true,
            'employee_count' => (int)$row['count']
        ];
    } else {
        $diagnostics['database_test'] = [
            'success' => false,
            'error' => 'Could not query employees table'
        ];
    }
} catch (Exception $e) {
    $diagnostics['database_test'] = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

// Test endpoints
$endpoints = [
    'get_hr_stats.php',
    'get_gender_stats.php',
    'get_academic_stats.php',
    'get_department_stats.php',
    'get_job_level_stats.php',
    'get_status_stats.php'
];

foreach ($endpoints as $endpoint) {
    if (file_exists($endpoint)) {
        ob_start();
        include $endpoint;
        $output = ob_get_clean();
        
        $data = json_decode($output, true);
        $diagnostics['endpoints_test'][$endpoint] = [
            'exists' => true,
            'valid_json' => $data !== null,
            'success' => isset($data['success']) ? $data['success'] : false,
            'has_data' => isset($data['data']) && !empty($data['data'])
        ];
    } else {
        $diagnostics['endpoints_test'][$endpoint] = [
            'exists' => false,
            'valid_json' => false,
            'success' => false,
            'has_data' => false
        ];
    }
}

echo json_encode($diagnostics, JSON_PRETTY_PRINT);
?>