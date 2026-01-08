<?php
// Set error reporting for development (should be toggled for production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$db_password = "";
$dbname = "ehealth";

// Create connection
$conn = new mysqli($servername, $username, $db_password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    // Database created successfully
} else {
    if (basename($_SERVER['SCRIPT_FILENAME']) === 'db.php') {
        echo "Error creating database: " . $conn->error;
    }
}

// Select database
$conn->select_db($dbname);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
email VARCHAR(50) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
role VARCHAR(30) NOT NULL,
name VARCHAR(100) NOT NULL,
zone VARCHAR(50) NULL,
woreda VARCHAR(50) NULL,
kebele VARCHAR(50) NULL,
remember_token VARCHAR(255) NULL,
last_login TIMESTAMP NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    // Table created successfully
} else {
    if (basename($_SERVER['SCRIPT_FILENAME']) === 'db.php') {
        echo "Error creating table: " . $conn->error;
    }
}

// Check and add columns if not exist
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'remember_token'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN remember_token VARCHAR(255) NULL");
}

$result = $conn->query("SHOW COLUMNS FROM users LIKE 'last_login'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL");
}

$result = $conn->query("SHOW COLUMNS FROM users LIKE 'zone'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN zone VARCHAR(50) NULL");
}

$result = $conn->query("SHOW COLUMNS FROM users LIKE 'woreda'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN woreda VARCHAR(50) NULL");
}

$result = $conn->query("SHOW COLUMNS FROM users LIKE 'kebele'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN kebele VARCHAR(50) NULL");
}

$result = $conn->query("SHOW COLUMNS FROM users LIKE 'employee_id'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN employee_id VARCHAR(20) NULL");
    $conn->query("ALTER TABLE users ADD INDEX idx_employee_id (employee_id)");
}

// Create login_attempts table
$sql = "CREATE TABLE IF NOT EXISTS login_attempts (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
email VARCHAR(50) NOT NULL,
ip_address VARCHAR(45) NOT NULL,
attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
success BOOLEAN NOT NULL
)";

if ($conn->query($sql) === TRUE) {
    // Table created successfully
} else {
    if (basename($_SERVER['SCRIPT_FILENAME']) === 'db.php') {
        echo "Error creating login_attempts table: " . $conn->error;
    }
}

// Create employees table if not exists
$sql = "CREATE TABLE IF NOT EXISTS employees (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    date_of_birth DATE NOT NULL,
    religion VARCHAR(50),
    citizenship VARCHAR(50),
    other_citizenship VARCHAR(100),
    region VARCHAR(50),
    zone VARCHAR(50),
    woreda VARCHAR(50),
    kebele VARCHAR(50),
    education_level VARCHAR(50),
    primary_school TEXT,
    secondary_school TEXT,
    college TEXT,
    university TEXT,
    department VARCHAR(100),
    other_department TEXT,
    bank_name VARCHAR(100),
    other_bank_name VARCHAR(100),
    bank_account VARCHAR(100),
    job_level VARCHAR(50),
    other_job_level TEXT,
    marital_status VARCHAR(20),
    other_marital_status TEXT,
    warranty_status ENUM('yes', 'no'),
    person_name VARCHAR(100),
    person_relationship VARCHAR(50),
    warranty_region VARCHAR(50),
    warranty_zone VARCHAR(50),
    warranty_woreda VARCHAR(50),
    warranty_kebele VARCHAR(50),
    warranty_email VARCHAR(100),
    phone VARCHAR(20),
    warranty_type VARCHAR(20),
    warranty_amount DECIMAL(10,2),
    warranty_address TEXT,
    warranty_start_date DATE,
    warranty_end_date DATE,
    warranty_notes TEXT,
    scan_file VARCHAR(255),
    criminal_status ENUM('yes', 'no'),
    criminal_type VARCHAR(50),
    criminal_date DATE,
    criminal_location VARCHAR(100),
    criminal_court VARCHAR(100),
    criminal_description TEXT,
    criminal_sentence VARCHAR(100),
    criminal_status_current VARCHAR(50),
    criminal_file VARCHAR(255),
    criminal_additional_docs VARCHAR(255),
    criminal_notes TEXT,
    fin_id VARCHAR(50),
    fin_scan VARCHAR(255),
    loan_status ENUM('yes', 'no'),
    loan_type VARCHAR(50),
    loan_amount DECIMAL(10,2),
    loan_lender VARCHAR(100),
    loan_account VARCHAR(50),
    loan_start_date DATE,
    loan_end_date DATE,
    monthly_payment DECIMAL(10,2),
    remaining_balance DECIMAL(10,2),
    loan_status_current VARCHAR(50),
    loan_collateral VARCHAR(50),
    loan_purpose TEXT,
    loan_file VARCHAR(255),
    loan_payment_proof VARCHAR(255),
    loan_notes TEXT,
    leave_request ENUM('yes', 'no'),
    leave_type VARCHAR(50),
    leave_duration INT,
    leave_start_date DATE,
    leave_end_date DATE,
    leave_reason TEXT,
    leave_contact VARCHAR(100),
    leave_supervisor VARCHAR(50),
    leave_address TEXT,
    leave_medical_cert VARCHAR(255),
    leave_supporting_docs VARCHAR(255),
    leave_notes TEXT,
    leave_document VARCHAR(255),
    email VARCHAR(100),
    phone_number VARCHAR(20),
    department_assigned VARCHAR(50),
    position VARCHAR(100),
    join_date DATE,
    salary DECIMAL(10,2),
    employment_type VARCHAR(20),
    status ENUM('active', 'on-leave', 'inactive') DEFAULT 'active',
    address TEXT,
    emergency_contact VARCHAR(100),
    language VARCHAR(50),
    other_language VARCHAR(100),
    documents TEXT,
    created_by VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    // Only output messages if this file is accessed directly (not included)
    if (basename($_SERVER['SCRIPT_FILENAME']) === 'db.php') {
        echo "✅ Employees table created successfully.<br>";
    }
} else {
    if (basename($_SERVER['SCRIPT_FILENAME']) === 'db.php') {
        echo "❌ Error creating employees table: " . $conn->error . "<br>";
    }
}

// Create patients table
$sql = "CREATE TABLE IF NOT EXISTS patients (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    blood_type VARCHAR(5),
    medical_history TEXT,
    emergency_contact VARCHAR(100),
    zone VARCHAR(50),
    woreda VARCHAR(50),
    kebele VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    // Table created successfully
} else {
    if (basename($_SERVER['SCRIPT_FILENAME']) === 'db.php') {
        echo "Error creating patients table: " . $conn->error;
    }
}

// Create appointments table
$sql = "CREATE TABLE IF NOT EXISTS appointments (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT(6) UNSIGNED,
    doctor_name VARCHAR(100),
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    department VARCHAR(50),
    status ENUM('scheduled', 'confirmed', 'completed', 'cancelled') DEFAULT 'scheduled',
    notes TEXT,
    zone VARCHAR(50),
    woreda VARCHAR(50),
    kebele VARCHAR(50),
    created_by INT(6) UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    // Table created successfully
} else {
    if (basename($_SERVER['SCRIPT_FILENAME']) === 'db.php') {
        echo "Error creating appointments table: " . $conn->error;
    }
}

// Create inventory table
$sql = "CREATE TABLE IF NOT EXISTS inventory (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    quantity INT NOT NULL DEFAULT 0,
    unit VARCHAR(20) DEFAULT 'units',
    expiry_date DATE,
    supplier VARCHAR(100),
    min_stock_level INT DEFAULT 10,
    zone VARCHAR(50),
    woreda VARCHAR(50),
    kebele VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    // Table created successfully
} else {
    if (basename($_SERVER['SCRIPT_FILENAME']) === 'db.php') {
        echo "Error creating inventory table: " . $conn->error;
    }
}

// Create reports table
$sql = "CREATE TABLE IF NOT EXISTS reports (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT,
    generated_by INT(6) UNSIGNED,
    zone VARCHAR(50),
    woreda VARCHAR(50),
    kebele VARCHAR(50),
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    // Table created successfully
} else {
    if (basename($_SERVER['SCRIPT_FILENAME']) === 'db.php') {
        echo "Error creating reports table: " . $conn->error;
    }
}

// Create emergency_responses table
$sql = "CREATE TABLE IF NOT EXISTS emergency_responses (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    incident_type VARCHAR(100) NOT NULL,
    description TEXT,
    location VARCHAR(200),
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('reported', 'responding', 'resolved') DEFAULT 'reported',
    reported_by INT(6) UNSIGNED,
    assigned_to INT(6) UNSIGNED,
    zone VARCHAR(50),
    woreda VARCHAR(50),
    kebele VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reported_by) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    // Table created successfully
} else {
    if (basename($_SERVER['SCRIPT_FILENAME']) === 'db.php') {
        echo "Error creating emergency_responses table: " . $conn->error;
    }
}

// Create quality_assurance table
$sql = "CREATE TABLE IF NOT EXISTS quality_assurance (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    facility_name VARCHAR(100),
    assessment_type VARCHAR(50),
    score DECIMAL(5,2),
    total_score DECIMAL(5,2),
    findings TEXT,
    recommendations TEXT,
    assessed_by INT(6) UNSIGNED,
    zone VARCHAR(50),
    woreda VARCHAR(50),
    kebele VARCHAR(50),
    assessment_date DATE,
    next_assessment_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assessed_by) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    // Table created successfully
} else {
    if (basename($_SERVER['SCRIPT_FILENAME']) === 'db.php') {
        echo "Error creating quality_assurance table: " . $conn->error;
    }
}

// Create payroll table
$sql = "CREATE TABLE IF NOT EXISTS payroll (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    basic_salary DECIMAL(10,2) NOT NULL,
    allowances DECIMAL(10,2) DEFAULT 0,
    deductions DECIMAL(10,2) DEFAULT 0,
    net_salary DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processed', 'paid') DEFAULT 'pending',
    processed_by INT(6) UNSIGNED,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (processed_by) REFERENCES users(id),
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    // Table created successfully
} else {
    if (basename($_SERVER['SCRIPT_FILENAME']) === 'db.php') {
        echo "Error creating payroll table: " . $conn->error;
    }
}

// Create leave_requests table
$sql = "CREATE TABLE IF NOT EXISTS leave_requests (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) NOT NULL,
    leave_type ENUM('annual', 'sick', 'maternity', 'paternity', 'emergency') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    days_requested INT NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by INT(6) UNSIGNED,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    // Table created successfully
} else {
    if (basename($_SERVER['SCRIPT_FILENAME']) === 'db.php') {
        echo "Error creating leave_requests table: " . $conn->error;
    }
}

// Create job_postings table
$sql = "CREATE TABLE IF NOT EXISTS job_postings (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    department VARCHAR(100) NOT NULL,
    description TEXT,
    requirements TEXT,
    salary_range VARCHAR(100),
    location VARCHAR(100),
    employment_type ENUM('full-time', 'part-time', 'contract') DEFAULT 'full-time',
    application_deadline DATE,
    status ENUM('open', 'closed', 'filled') DEFAULT 'open',
    posted_by INT(6) UNSIGNED,
    woreda VARCHAR(50) NULL,
    posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    // Table created successfully
} else {
    if (basename($_SERVER['SCRIPT_FILENAME']) === 'db.php') {
        echo "Error creating job_postings table: " . $conn->error;
    }
}

// Create job_applications table
$sql = "CREATE TABLE IF NOT EXISTS job_applications (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT(6) UNSIGNED NOT NULL,
    applicant_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    resume VARCHAR(255),
    cover_letter TEXT,
    status ENUM('pending', 'reviewed', 'shortlisted', 'rejected', 'hired') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES job_postings(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    // Table created successfully
} else {
    if (basename($_SERVER['SCRIPT_FILENAME']) === 'db.php') {
        echo "Error creating job_applications table: " . $conn->error;
    }
}

// Create training_sessions table
$sql = "CREATE TABLE IF NOT EXISTS training_sessions (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    trainer VARCHAR(100),
    session_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    venue VARCHAR(100),
    max_participants INT DEFAULT 0,
    status ENUM('scheduled', 'ongoing', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_by INT(6) UNSIGNED,
    woreda VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    // Table created successfully
} else {
    if (basename($_SERVER['SCRIPT_FILENAME']) === 'db.php') {
        echo "Error creating training_sessions table: " . $conn->error;
    }
}

// Create training_participants table
$sql = "CREATE TABLE IF NOT EXISTS training_participants (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    training_id INT(6) UNSIGNED NOT NULL,
    employee_id VARCHAR(20) NOT NULL,
    status ENUM('invited', 'confirmed', 'attended', 'absent') DEFAULT 'invited',
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (training_id) REFERENCES training_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    // Table created successfully
} else {
    if (basename($_SERVER['SCRIPT_FILENAME']) === 'db.php') {
        echo "Error creating training_participants table: " . $conn->error;
    }
}

// Insert default users only if the table is empty to prevent constant rehashing and potential race conditions
$checkUsers = $conn->query("SELECT id FROM users LIMIT 1");
if ($checkUsers && $checkUsers->num_rows == 0) {
    $users = [
        ['admin@gmail.com', password_hash('123456', PASSWORD_DEFAULT), 'admin', 'Administrator', NULL, NULL, NULL],
        ['zone_ho@gmail.com', password_hash('123456', PASSWORD_DEFAULT), 'zone_health_officer', 'Zone Health Officer', 'Zone A', NULL, NULL],
        ['zone_hr@gmail.com', password_hash('123456', PASSWORD_DEFAULT), 'zone_hr', 'Zone HR Officer', 'Zone A', NULL, NULL],
        ['wereda_ho@gmail.com', password_hash('123456', PASSWORD_DEFAULT), 'wereda_health_officer', 'Wereda Health Officer', 'Zone A', 'Wereda 1', NULL],
        ['wereda_hr@gmail.com', password_hash('123456', PASSWORD_DEFAULT), 'wereda_hr', 'Wereda HR Officer', 'Zone A', 'Wereda 1', NULL],
        ['kebele_ho@gmail.com', password_hash('123456', PASSWORD_DEFAULT), 'kebele_health_officer', 'Kebele Health Officer', 'Zone A', 'Wereda 1', 'Kebele 1'],
        ['kebele_hr@gmail.com', password_hash('123456', PASSWORD_DEFAULT), 'kebele_hr', 'Kebele HR Officer', 'Zone A', 'Wereda 1', 'Kebele 1'],
    ];

    foreach ($users as $user) {
        $stmt = $conn->prepare("INSERT INTO users (email, password, role, name, zone, woreda, kebele) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $user[0], $user[1], $user[2], $user[3], $user[4], $user[5], $user[6]);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Executes a query safely and returns the result or a default value
 * Useful for dashboard stats where a table might be empty or missing
 */
function executeSafeQuery($conn, $query, $defaultValue = 0, $column = 'count') {
    try {
        $result = $conn->query($query);
        if ($result && $row = $result->fetch_assoc()) {
            return $row[$column] ?? $defaultValue;
        }
    } catch (Exception $e) {
        error_log("Query Error: " . $e->getMessage());
    }
    return $defaultValue;
}

// Function to get database connection
function getDBConnection() {
    global $servername, $username, $db_password, $dbname;
    
    try {
        $conn = new mysqli($servername, $username, $db_password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Database Connection failed: " . $conn->connect_error);
        }

        // Set charset to utf8mb4 for emoji/special char support
        $conn->set_charset("utf8mb4");

        return $conn;
    } catch (Exception $e) {
        error_log($e->getMessage());
        if (ini_get('display_errors')) {
            die("<div style='padding: 20px; background: #fff5f5; border: 1px solid #feb2b2; color: #c53030; border-radius: 8px; margin: 20px;'>
                <strong>System Error:</strong> " . $e->getMessage() . "
            </div>");
        } else {
            die("A system error occurred. Please contact the administrator.");
        }
    }
}

// Close connection
// $conn->close(); // Removed to prevent closing connection before it's used
?>