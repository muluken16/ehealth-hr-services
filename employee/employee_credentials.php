<?php
require_once '../db.php';
$conn = getDBConnection();

// Get all employees with their details
$query = "SELECT employee_id, first_name, last_name, phone_number, working_kebele, position 
          FROM employees 
          ORDER BY first_name 
          LIMIT 500";
$result = $conn->query($query);

// Function to generate password: First 3 letters of first name + last 3 digits of phone
function generatePassword($first_name, $phone_number) {
    $first_three = strtolower(substr($first_name, 0, 3));
    $phone_cleaned = preg_replace('/[^0-9]/', '', $phone_number); // Remove non-digits
    $last_three = substr($phone_cleaned, -3);
    return $first_three . $last_three;
}

$employees = [];
while ($row = $result->fetch_assoc()) {
    $password = generatePassword($row['first_name'], $row['phone_number'] ?? '000');
    $employees[] = [
        'employee_id' => $row['employee_id'],
        'name' => $row['first_name'] . ' ' . $row['last_name'],
        'phone' => $row['phone_number'] ?? 'N/A',
        'position' => $row['position'] ?? 'N/A',
        'kebele' => $row['working_kebele'] ?? 'N/A',
        'password' => $password
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Login Credentials | HealthFirst</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 20px 20px 0 0;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #1a4a5f;
            font-size: 2rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .header h1 i {
            color: #667eea;
        }
        
        .header p {
            color: #64748b;
            font-size: 1.05rem;
        }
        
        .info-banner {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 600;
        }
        
        .info-banner i {
            font-size: 1.5rem;
        }
        
        .password-formula {
            background: #f8fafc;
            padding: 25px 30px;
            border-left: 4px solid #667eea;
            margin: 0;
        }
        
        .password-formula h3 {
            color: #1e293b;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .password-formula code {
            background: #e0e7ff;
            color: #4338ca;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 1rem;
            display: inline-block;
            margin: 5px 0;
            font-weight: 600;
        }
        
        .credentials-grid {
            background: white;
            padding: 0;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .credentials-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .credentials-table thead {
            background: linear-gradient(135deg, #1a4a5f 0%, #2a6e8c 100%);
            color: white;
        }
        
        .credentials-table th {
            padding: 18px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .credentials-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.3s;
        }
        
        .credentials-table tbody tr:hover {
            background: #f8fafc;
            transform: scale(1.01);
        }
        
        .credentials-table tbody tr:last-child {
            border-bottom: none;
        }
        
        .credentials-table td {
            padding: 18px 20px;
            color: #334155;
        }
        
        .employee-name {
            font-weight: 700;
            color: #1e293b;
            font-size: 1rem;
        }
        
        .employee-id {
            font-family: 'Courier New', monospace;
            background: #e0e7ff;
            color: #4338ca;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
            display: inline-block;
        }
        
        .password-cell {
            font-family: 'Courier New', monospace;
            background: #dcfce7;
            color: #166534;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.05rem;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .copy-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.75rem;
            transition: all 0.3s;
        }
        
        .copy-btn:hover {
            background: #059669;
            transform: scale(1.05);
        }
        
        .position-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .login-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            color: white;
        }
        
        .footer a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            background: rgba(255,255,255,0.2);
            padding: 12px 30px;
            border-radius: 10px;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .footer a:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        
        .example-box {
            background: white;
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            padding: 15px 20px;
            margin: 10px 0;
        }
        
        .example-box strong {
            color: #1e293b;
        }
        
        .example-box .example-item {
            margin: 8px 0;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-id-card"></i> Employee Login Credentials</h1>
            <p>Access credentials for HealthFirst Employee Portal</p>
        </div>
        
        <div class="info-banner">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>Password Format:</strong> First 3 letters of first name (lowercase) + Last 3 digits of phone number
            </div>
        </div>
        
        <div class="password-formula">
            <h3><i class="fas fa-key"></i> Password Generation Formula:</h3>
            <code>Password = FirstName[0-2].toLowerCase() + PhoneNumber[-3:]</code>
            
            <div class="example-box">
                <strong>Example:</strong>
                <div class="example-item">
                    • Name: <strong>Abebe Bekele</strong><br>
                    • Phone: <strong>+251 912 345 678</strong><br>
                    • Password: <strong style="color: #059669;">abe678</strong> (first 3: "abe" + last 3: "678")
                </div>
            </div>
        </div>
        
        <div class="credentials-grid">
            <table class="credentials-table">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Position</th>
                        <th>Phone Number</th>
                        <th>Password</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td><span class="employee-id"><?php echo htmlspecialchars($emp['employee_id']); ?></span></td>
                        <td class="employee-name"><?php echo htmlspecialchars($emp['name']); ?></td>
                        <td><span class="position-badge"><?php echo htmlspecialchars($emp['position']); ?></span></td>
                        <td style="font-family: monospace; color: #64748b;"><?php echo htmlspecialchars($emp['phone']); ?></td>
                        <td>
                            <span class="password-cell">
                                <?php echo htmlspecialchars($emp['password']); ?>
                                <button class="copy-btn" onclick="copyPassword('<?php echo $emp['password']; ?>')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </span>
                        </td>
                        <td>
                            <a href="login.php" class="login-button" title="Go to login page">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="footer">
            <a href="login.php">
                <i class="fas fa-arrow-right"></i> Go to Employee Login Page
            </a>
        </div>
    </div>

    <script>
        function copyPassword(password) {
            navigator.clipboard.writeText(password).then(() => {
                alert('Password copied: ' + password);
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        }
        
        // Highlight copied text
        document.querySelectorAll('.copy-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                this.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-copy"></i>';
                }, 2000);
            });
        });
    </script>
</body>
</html>
