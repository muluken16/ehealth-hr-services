<?php
// Create enhanced leave management tables
$conn = new mysqli("localhost", "root", "", "ehealth");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create leave_entitlements table to track annual leave allowances
$sql = "CREATE TABLE IF NOT EXISTS leave_entitlements (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) NOT NULL,
    year INT NOT NULL,
    annual_leave_days INT DEFAULT 21,
    sick_leave_days INT DEFAULT 14,
    maternity_leave_days INT DEFAULT 120,
    paternity_leave_days INT DEFAULT 10,
    emergency_leave_days INT DEFAULT 5,
    used_annual_leave INT DEFAULT 0,
    used_sick_leave INT DEFAULT 0,
    used_maternity_leave INT DEFAULT 0,
    used_paternity_leave INT DEFAULT 0,
    used_emergency_leave INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_year (employee_id, year)
)";

if ($conn->query($sql) === TRUE) {
    echo "✅ Leave entitlements table created successfully.<br>";
} else {
    echo "❌ Error creating leave_entitlements table: " . $conn->error . "<br>";
}

// Update leave_requests table to include more fields
$conn->query("ALTER TABLE leave_requests ADD COLUMN IF NOT EXISTS requested_by INT(6) UNSIGNED");
$conn->query("ALTER TABLE leave_requests ADD COLUMN IF NOT EXISTS rejection_reason TEXT");
$conn->query("ALTER TABLE leave_requests ADD COLUMN IF NOT EXISTS supporting_document VARCHAR(255)");
$conn->query("ALTER TABLE leave_requests ADD COLUMN IF NOT EXISTS emergency_contact VARCHAR(100)");
$conn->query("ALTER TABLE leave_requests ADD COLUMN IF NOT EXISTS leave_address TEXT");

echo "✅ Leave requests table updated successfully.<br>";

// Create leave_approvals table for multi-level approval
$sql = "CREATE TABLE IF NOT EXISTS leave_approvals (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    leave_request_id INT(6) UNSIGNED NOT NULL,
    approver_level ENUM('hr', 'ho', 'admin') NOT NULL,
    approver_id INT(6) UNSIGNED NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    comments TEXT,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (leave_request_id) REFERENCES leave_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (approver_id) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "✅ Leave approvals table created successfully.<br>";
} else {
    echo "❌ Error creating leave_approvals table: " . $conn->error . "<br>";
}

// Function to calculate leave entitlement based on years of service
function calculateLeaveEntitlement($years_of_service) {
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
        'maternity_leave_days' => 120,
        'paternity_leave_days' => 10,
        'emergency_leave_days' => 5
    ];
}

// Initialize leave entitlements for existing employees
$current_year = date('Y');
$employees_result = $conn->query("SELECT employee_id, join_date FROM employees WHERE status = 'active'");

while ($employee = $employees_result->fetch_assoc()) {
    $employee_id = $employee['employee_id'];
    $join_date = new DateTime($employee['join_date']);
    $current_date = new DateTime();
    $years_of_service = $current_date->diff($join_date)->y;
    
    $entitlements = calculateLeaveEntitlement($years_of_service);
    
    // Check if entitlement already exists for this year
    $check_stmt = $conn->prepare("SELECT id FROM leave_entitlements WHERE employee_id = ? AND year = ?");
    $check_stmt->bind_param("si", $employee_id, $current_year);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Insert new entitlement
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
    }
    $check_stmt->close();
}

echo "✅ Leave entitlements initialized for all active employees.<br>";

$conn->close();
echo "<br><strong>Leave management system setup completed!</strong>";
?>