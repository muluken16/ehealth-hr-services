<?php
/**
 * Create Real User Accounts for All Employees
 * This script creates login credentials for all employees in the system
 * Default Password: ehealth2026
 */

require_once 'db.php';
$conn = getDBConnection();

// Default password for all employees
$default_password = "ehealth2026";
$default_password_hash = password_hash($default_password, PASSWORD_DEFAULT);

// Function to generate email from employee data
function generateEmail($employee_id, $first_name, $last_name) {
    // Clean and lowercase names
    $first = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $first_name));
    $last = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $last_name));
    
    // Try different email formats in order of preference
    // 1. firstname.lastname@ehealth.gov.et
    // 2. If exists, try employeeid@ehealth.gov.et
    
    $email = $first . '.' . $last . '@ehealth.gov.et';
    
    return $email;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Employee User Accounts</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #1a4a5f 0%, #2a6e8c 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px;
        }
        
        .alert {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 500;
            animation: slideIn 0.5s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 5px solid #28a745;
        }
        
        .alert-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-left: 5px solid #dc3545;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
            border-left: 5px solid #17a2b8;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border-left: 5px solid #ffc107;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card .number {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stat-card .label {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        thead {
            background: linear-gradient(135deg, #1a4a5f 0%, #2a6e8c 100%);
            color: white;
        }
        
        th {
            padding: 18px;
            text-align: left;
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 15px 18px;
            border-bottom: 1px solid #e9ecef;
        }
        
        tbody tr {
            transition: all 0.3s ease;
        }
        
        tbody tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 50%);
            transform: scale(1.01);
        }
        
        tbody tr:last-child td {
            border-bottom: none;
        }
        
        code {
            background: #667eea;
            color: white;
            padding: 5px 12px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .password-display {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1rem;
            display: inline-block;
            letter-spacing: 2px;
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
        }
        
        .btn-container {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        
        .btn {
            padding: 15px 30px;
            background: linear-gradient(135deg, #1a4a5f 0%, #2a6e8c 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(26, 74, 95, 0.3);
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(26, 74, 95, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        
        .section-title {
            font-size: 1.8rem;
            color: #1a4a5f;
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .icon {
            font-size: 1.2em;
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .default-password-box {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin: 25px 0;
            text-align: center;
            box-shadow: 0 10px 25px rgba(251, 191, 36, 0.3);
        }
        
        .default-password-box h3 {
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        
        .default-password-box .password {
            font-size: 2.5rem;
            font-weight: bold;
            letter-spacing: 5px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><span class="icon">🔐</span>Employee User Account Creator</h1>
            <p>Automatically create login credentials for all employees</p>
        </div>
        
        <div class="content">
            
            <div class="default-password-box">
                <h3>🔑 Default Password for All Employees</h3>
                <div class="password"><?php echo $default_password; ?></div>
                <p style="margin-top: 15px; font-size: 1.1rem;">All employees can use their email and this password to login</p>
            </div>
            
            <?php
            
            // Get all employees from the database
            $employees_query = "SELECT 
                employee_id, 
                first_name, 
                middle_name,
                last_name, 
                phone_number,
                email,
                zone,
                woreda,
                kebele,
                department_assigned
            FROM employees 
            ORDER BY first_name, last_name";
            
            $employees_result = $conn->query($employees_query);
            
            if (!$employees_result) {
                echo "<div class='alert alert-error'>
                    <span class='icon'>❌</span>
                    <div>
                        <strong>Error fetching employees:</strong><br>" . $conn->error . "
                    </div>
                </div>";
                exit;
            }
            
            $total_employees = $employees_result->num_rows;
            
            if ($total_employees === 0) {
                echo "<div class='alert alert-warning'>
                    <span class='icon'>⚠️</span>
                    <div>
                        <strong>No employees found!</strong><br>
                        Please add employees to the system first.
                    </div>
                </div>";
                exit;
            }
            
            echo "<div class='alert alert-info'>
                <span class='icon'>ℹ️</span>
                <div>
                    <strong>Found {$total_employees} employees</strong><br>
                    Creating user accounts for all employees...
                </div>
            </div>";
            
            $success_count = 0;
            $updated_count = 0;
            $error_count = 0;
            $credentials = [];
            $errors = [];
            
            // Process each employee
            while ($emp = $employees_result->fetch_assoc()) {
                $employee_id = $emp['employee_id'];
                $first_name = $emp['first_name'];
                $middle_name = $emp['middle_name'] ?? '';
                $last_name = $emp['last_name'];
                $phone = $emp['phone_number'] ?? '';
                $existing_email = $emp['email'] ?? '';
                $zone = $emp['zone'] ?? '';
                $woreda = $emp['woreda'] ?? '';
                $kebele = $emp['kebele'] ?? '';
                $department = $emp['department_assigned'] ?? '';
                
                // Determine email to use
                if (!empty($existing_email) && filter_var($existing_email, FILTER_VALIDATE_EMAIL)) {
                    $email = $existing_email;
                } else {
                    $email = generateEmail($employee_id, $first_name, $last_name);
                }
                
                // Check if email already exists in users table
                $check_query = "SELECT id, email FROM users WHERE email = ?";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bind_param("s", $email);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                $full_name = trim($first_name . ' ' . $middle_name . ' ' . $last_name);
                
                if ($check_result->num_rows > 0) {
                    // Update existing user
                    $update_query = "UPDATE users SET 
                        password = ?,
                        role = 'employee',
                        name = ?,
                        zone = ?,
                        woreda = ?,
                        kebele = ?
                    WHERE email = ?";
                    
                    $stmt = $conn->prepare($update_query);
                    $stmt->bind_param("ssssss", 
                        $default_password_hash,
                        $full_name,
                        $zone,
                        $woreda,
                        $kebele,
                        $email
                    );
                    
                    if ($stmt->execute()) {
                        $updated_count++;
                        $credentials[] = [
                            'id' => $employee_id,
                            'name' => $full_name,
                            'email' => $email,
                            'phone' => $phone,
                            'department' => $department,
                            'location' => $kebele ? "$kebele, $woreda" : $woreda,
                            'status' => 'Updated'
                        ];
                    } else {
                        $error_count++;
                        $errors[] = "Failed to update user for: $full_name ($email)";
                    }
                } else {
                    // Create new user
                    $insert_query = "INSERT INTO users (email, password, role, name, zone, woreda, kebele) 
                                   VALUES (?, ?, 'employee', ?, ?, ?, ?)";
                    
                    $stmt = $conn->prepare($insert_query);
                    $stmt->bind_param("ssssss",
                        $email,
                        $default_password_hash,
                        $full_name,
                        $zone,
                        $woreda,
                        $kebele
                    );
                    
                    if ($stmt->execute()) {
                        $success_count++;
                        $credentials[] = [
                            'id' => $employee_id,
                            'name' => $full_name,
                            'email' => $email,
                            'phone' => $phone,
                            'department' => $department,
                            'location' => $kebele ? "$kebele, $woreda" : $woreda,
                            'status' => 'Created'
                        ];
                    } else {
                        $error_count++;
                        $errors[] = "Failed to create user for: $full_name ($email) - " . $stmt->error;
                    }
                }
            }
            
            // Display statistics
            ?>
            
            <div class="stats">
                <div class="stat-card">
                    <div class="number"><?php echo $total_employees; ?></div>
                    <div class="label">Total Employees</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <div class="number"><?php echo $success_count; ?></div>
                    <div class="label">New Accounts</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                    <div class="number"><?php echo $updated_count; ?></div>
                    <div class="label">Updated Accounts</div>
                </div>
                <?php if ($error_count > 0): ?>
                <div class="stat-card" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                    <div class="number"><?php echo $error_count; ?></div>
                    <div class="label">Errors</div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($success_count > 0 || $updated_count > 0): ?>
            <div class="alert alert-success">
                <span class="icon">✅</span>
                <div>
                    <strong>Success!</strong><br>
                    Created <?php echo $success_count; ?> new user accounts and updated <?php echo $updated_count; ?> existing accounts.
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($error_count > 0): ?>
            <div class="alert alert-error">
                <span class="icon">❌</span>
                <div>
                    <strong><?php echo $error_count; ?> errors occurred:</strong><br>
                    <ul style="margin-top: 10px; margin-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Credentials Table -->
            <h2 class="section-title">
                <span class="icon">📋</span>
                Employee Login Credentials
            </h2>
            
            <div class="alert alert-info">
                <span class="icon">💡</span>
                <div>
                    <strong>Instructions for Employees:</strong><br>
                    1. Go to the employee login page<br>
                    2. Use your email address as shown below<br>
                    3. Use the default password: <code><?php echo $default_password; ?></code><br>
                    4. You will be prompted to change your password on first login
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Email (Username)</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Location</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($credentials as $cred): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($cred['id']); ?></code></td>
                        <td><?php echo htmlspecialchars($cred['name']); ?></td>
                        <td><strong><?php echo htmlspecialchars($cred['email']); ?></strong></td>
                        <td><?php echo htmlspecialchars($cred['phone']); ?></td>
                        <td><?php echo htmlspecialchars($cred['department']); ?></td>
                        <td><?php echo htmlspecialchars($cred['location']); ?></td>
                        <td>
                            <?php if ($cred['status'] === 'Created'): ?>
                                <span style="color: #059669; font-weight: bold;">✓ Created</span>
                            <?php else: ?>
                                <span style="color: #2563eb; font-weight: bold;">↻ Updated</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="btn-container">
                <a href="employee/login.php" class="btn btn-success">
                    <span class="icon">🔓</span>
                    Go to Employee Login
                </a>
                <a href="print_credentials.php" class="btn btn-primary">
                    <span class="icon">🖨️</span>
                    Print Credentials Report
                </a>
                <a href="wereda_hr/wereda_hr_dashboard.php" class="btn">
                    <span class="icon">📊</span>
                    Back to Dashboard
                </a>
            </div>
            
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
