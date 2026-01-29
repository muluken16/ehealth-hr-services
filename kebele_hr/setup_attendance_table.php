<?php
include dirname(__DIR__) . '/db.php';
$conn = getDBConnection();

$sql = "CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'on-leave') DEFAULT 'absent',
    check_in TIME,
    check_out TIME,
    working_hours DECIMAL(5,2) DEFAULT 0.00,
    kebele VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (employee_id, attendance_date)
)";

if ($conn->query($sql) === TRUE) {
    echo "Attendance table created or already exists.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Also ensure leave_requests table exists for the join in stats/attendance list
$sqlLeave = "CREATE TABLE IF NOT EXISTS leave_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) NOT NULL,
    leave_type VARCHAR(50),
    start_date DATE,
    end_date DATE,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sqlLeave) === TRUE) {
    echo "Leave requests table created or already exists.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();
?>
