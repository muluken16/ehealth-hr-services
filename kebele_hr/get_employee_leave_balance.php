<?php
header('Content-Type: application/json');

// Create database connection
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$employee_id = $_GET['employee_id'] ?? '';
$current_year = date('Y');

if (empty($employee_id)) {
    echo json_encode(['error' => 'Employee ID is required']);
    exit();
}

// Get employee details and calculate years of service
$emp_stmt = $conn->prepare("SELECT employee_id, first_name, last_name, join_date, gender FROM employees WHERE employee_id = ?");
$emp_stmt->bind_param("s", $employee_id);
$emp_stmt->execute();
$emp_result = $emp_stmt->get_result();

if ($emp_result->num_rows === 0) {
    echo json_encode(['error' => 'Employee not found']);
    exit();
}

$employee = $emp_result->fetch_assoc();
$emp_stmt->close();

// Calculate years of service
$join_date = new DateTime($employee['join_date']);
$current_date = new DateTime();
$years_of_service = $current_date->diff($join_date)->y;
$months_of_service = $current_date->diff($join_date)->m + ($years_of_service * 12);

// Function to calculate leave entitlement based on years of service
function calculateLeaveEntitlement($years_of_service, $gender) {
    $annual_leave = 21; // Base annual leave
    
    // Add extra days based on years of service
    if ($years_of_service >= 5) {
        $annual_leave += 2; // 23 days after 5 years
    }
    if ($years_of_service >= 10) {
        $annual_leave += 2; // 25 days after 10 years
    }
    if ($years_of_service >= 15) {
        $annual_leave += 3; // 28 days after 15 years
    }
    if ($years_of_service >= 20) {
        $annual_leave += 2; // 30 days after 20 years
    }
    
    return [
        'annual_leave_days' => $annual_leave,
        'sick_leave_days' => 14,
        'maternity_leave_days' => ($gender === 'female') ? 120 : 0,
        'paternity_leave_days' => ($gender === 'male') ? 10 : 0,
        'emergency_leave_days' => 5
    ];
}

// Get or create leave entitlement for current year
$entitlements = calculateLeaveEntitlement($years_of_service, $employee['gender']);

$entitlement_stmt = $conn->prepare("SELECT * FROM leave_entitlements WHERE employee_id = ? AND year = ?");
$entitlement_stmt->bind_param("si", $employee_id, $current_year);
$entitlement_stmt->execute();
$entitlement_result = $entitlement_stmt->get_result();

if ($entitlement_result->num_rows === 0) {
    // Create new entitlement record
    $insert_stmt = $conn->prepare("INSERT INTO leave_entitlements (employee_id, year, annual_leave_days, sick_leave_days, maternity_leave_days, paternity_leave_days, emergency_leave_days) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("siiiiii", 
        $employee_id, 
        $current_year, 
        $entitlements['annual_leave_days'],
        $entitlements['sick_leave_days'],
        $entitlements['maternity_leave_days'],
        $entitlements['paternity_leave_days'],
        $entitlements['emergency_leave_days']
    );
    $insert_stmt->execute();
    $insert_stmt->close();
    
    // Get the newly created record
    $entitlement_stmt->execute();
    $entitlement_result = $entitlement_stmt->get_result();
}

$leave_balance = $entitlement_result->fetch_assoc();
$entitlement_stmt->close();

// Calculate remaining leave days
$remaining_annual = $leave_balance['annual_leave_days'] - $leave_balance['used_annual_leave'];
$remaining_sick = $leave_balance['sick_leave_days'] - $leave_balance['used_sick_leave'];
$remaining_maternity = $leave_balance['maternity_leave_days'] - $leave_balance['used_maternity_leave'];
$remaining_paternity = $leave_balance['paternity_leave_days'] - $leave_balance['used_paternity_leave'];
$remaining_emergency = $leave_balance['emergency_leave_days'] - $leave_balance['used_emergency_leave'];

// Get pending leave requests
$pending_stmt = $conn->prepare("SELECT leave_type, SUM(days_requested) as pending_days FROM leave_requests WHERE employee_id = ? AND status = 'pending' GROUP BY leave_type");
$pending_stmt->bind_param("s", $employee_id);
$pending_stmt->execute();
$pending_result = $pending_stmt->get_result();

$pending_leaves = [];
while ($row = $pending_result->fetch_assoc()) {
    $pending_leaves[$row['leave_type']] = $row['pending_days'];
}
$pending_stmt->close();

$response = [
    'employee' => [
        'id' => $employee['employee_id'],
        'name' => $employee['first_name'] . ' ' . $employee['last_name'],
        'join_date' => $employee['join_date'],
        'years_of_service' => $years_of_service,
        'months_of_service' => $months_of_service,
        'gender' => $employee['gender']
    ],
    'leave_balance' => [
        'annual' => [
            'entitled' => $leave_balance['annual_leave_days'],
            'used' => $leave_balance['used_annual_leave'],
            'remaining' => $remaining_annual,
            'pending' => $pending_leaves['annual'] ?? 0
        ],
        'sick' => [
            'entitled' => $leave_balance['sick_leave_days'],
            'used' => $leave_balance['used_sick_leave'],
            'remaining' => $remaining_sick,
            'pending' => $pending_leaves['sick'] ?? 0
        ],
        'maternity' => [
            'entitled' => $leave_balance['maternity_leave_days'],
            'used' => $leave_balance['used_maternity_leave'],
            'remaining' => $remaining_maternity,
            'pending' => $pending_leaves['maternity'] ?? 0
        ],
        'paternity' => [
            'entitled' => $leave_balance['paternity_leave_days'],
            'used' => $leave_balance['used_paternity_leave'],
            'remaining' => $remaining_paternity,
            'pending' => $pending_leaves['paternity'] ?? 0
        ],
        'emergency' => [
            'entitled' => $leave_balance['emergency_leave_days'],
            'used' => $leave_balance['used_emergency_leave'],
            'remaining' => $remaining_emergency,
            'pending' => $pending_leaves['emergency'] ?? 0
        ]
    ]
];

echo json_encode($response);
$conn->close();
?>