<?php
/**
 * Printable Employee Credentials Report
 * This page generates a printer-friendly document with all employee login credentials
 */

require_once 'db.php';
$conn = getDBConnection();

// Default password
$default_password = "ehealth2026";

// Get all users with employee role
$query = "SELECT u.email, u.name, u.zone, u.woreda, u.kebele, u.created_at, e.employee_id, e.phone_number, e.department_assigned
          FROM users u
          LEFT JOIN employees e ON u.email LIKE CONCAT('%', REPLACE(LOWER(CONCAT(e.first_name, '.', e.last_name)), ' ', ''), '%')
          WHERE u.role = 'employee'
          ORDER BY u.name";

$result = $conn->query($query);

// If the above join doesn't work well, try a simpler approach
if (!$result || $result->num_rows === 0) {
    $query = "SELECT email, name, zone, woreda, kebele, created_at FROM users WHERE role = 'employee' ORDER BY name";
    $result = $conn->query($query);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Employee Login Credentials - Print Report</title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .page-break { page-break-after: always; }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 40px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #1a4a5f;
        }
        
        .header h1 {
            color: #1a4a5f;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 1.2rem;
        }
        
        .header .date {
            color: #999;
            font-size: 1rem;
            margin-top: 10px;
        }
        
        .password-box {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            padding: 20px;
            margin: 30px 0;
            text-align: center;
            border-radius: 10px;
        }
        
        .password-box h2 {
            color: #92400e;
            margin-bottom: 15px;
        }
        
        .password-box .password {
            font-size: 2rem;
            font-weight: bold;
            color: #b45309;
            font-family: 'Courier New', monospace;
            letter-spacing: 5px;
            margin: 15px 0;
        }
        
        .instructions {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            margin: 30px 0;
        }
        
        .instructions h3 {
            color: #1e40af;
            margin-bottom: 15px;
        }
        
        .instructions ol {
            margin-left: 25px;
            line-height: 1.8;
            color: #1e3a8a;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        
        thead {
            background: #1a4a5f;
            color: white;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border: 1px solid #1a4a5f;
        }
        
        td {
            padding: 12px 15px;
            border: 1px solid #ddd;
        }
        
        tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .btn-container {
            margin: 30px 0;
            text-align: center;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: #1a4a5f;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 0 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: #2a6e8c;
            transform: translateY(-2px);
        }
        
        .btn-print {
            background: #059669;
        }
        
        .btn-print:hover {
            background: #047857;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .credential-card {
            border: 2px solid #e5e7eb;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            display: none; /* Hidden by default, shown in print view if needed */
        }
        
        .credential-card h3 {
            color: #1a4a5f;
            margin-bottom: 15px;
        }
        
        .credential-info {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 10px;
            margin: 5px 0;
        }
        
        .credential-info .label {
            font-weight: bold;
            color: #4b5563;
        }
        
        .credential-info .value {
            color: #1f2937;
        }
        
        .stats-box {
            background: #f0f9ff;
            border: 1px solid #3b82f6;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            text-align: center;
        }
        
        .stats-box h3 {
            color: #1e40af;
            margin-bottom: 10px;
        }
        
        .stats-box .number {
            font-size: 3rem;
            font-weight: bold;
            color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Print Buttons -->
        <div class="btn-container no-print">
            <button onclick="window.print()" class="btn btn-print">🖨️ Print This Report</button>
            <a href="create_employee_accounts.php" class="btn">← Back to Account Creator</a>
        </div>
        
        <!-- Header -->
        <div class="header">
            <h1>📋 Employee Login Credentials</h1>
            <div class="subtitle">eHealth HR Management System</div>
            <div class="date">Generated on: <?php echo date('F d, Y h:i A'); ?></div>
        </div>
        
        <!-- Statistics -->
        <div class="stats-box">
            <h3>Total Employee Accounts</h3>
            <div class="number"><?php echo $result ? $result->num_rows : 0; ?></div>
        </div>
        
        <!-- Default Password -->
        <div class="password-box">
            <h2>🔑 Default Password for All Employees</h2>
            <div class="password"><?php echo $default_password; ?></div>
            <p style="margin-top: 10px; color: #78350f;">All employees use this password for initial login</p>
        </div>
        
        <!-- Instructions -->
        <div class="instructions">
            <h3>📖 Login Instructions for Employees</h3>
            <ol>
                <li>Navigate to the Employee Login page at: <strong>http://localhost/ehealth/ehealth/employee/login.php</strong></li>
                <li>Enter your email address as the username (see table below)</li>
                <li>Enter the default password: <strong><?php echo $default_password; ?></strong></li>
                <li>Click "Login" to access your employee dashboard</li>
                <li>You will be prompted to change your password after first login (recommended)</li>
            </ol>
        </div>
        
        <!-- Credentials Table -->
        <h2 style="color: #1a4a5f; margin: 30px 0 20px;">Employee Login Details</h2>
        
        <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Employee Name</th>
                    <th>Email (Username)</th>
                    <th>Password</th>
                    <th>Location</th>
                    <th>Account Created</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 1;
                while ($row = $result->fetch_assoc()): 
                    $location = '';
                    if (!empty($row['kebele'])) {
                        $location = $row['kebele'] . ', ' . $row['woreda'];
                    } elseif (!empty($row['woreda'])) {
                        $location = $row['woreda'];
                    } elseif (!empty($row['zone'])) {
                        $location = $row['zone'];
                    }
                ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                    <td style="color: #2563eb; font-weight: 600;"><?php echo htmlspecialchars($row['email']); ?></td>
                    <td style="background: #fef3c7; font-weight: bold; color: #b45309; font-family: monospace;"><?php echo $default_password; ?></td>
                    <td><?php echo htmlspecialchars($location); ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="background: #fee2e2; border: 1px solid #ef4444; padding: 20px; border-radius: 10px; text-align: center; color: #991b1b;">
            <h3>No employee accounts found!</h3>
            <p style="margin-top: 10px;">Please run the account creation script first.</p>
        </div>
        <?php endif; ?>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>CONFIDENTIAL:</strong> This document contains sensitive login information. Handle with care and distribute securely.</p>
            <p style="margin-top: 10px;">eHealth HR Management System © <?php echo date('Y'); ?></p>
        </div>
        
        <!-- Second Page: Individual Cards (Optional) -->
        <?php if ($result && $result->num_rows > 0): 
            $result->data_seek(0); // Reset pointer
        ?>
        <div class="page-break"></div>
        
        <div style="margin-top: 40px;">
            <h2 style="color: #1a4a5f; text-align: center; margin-bottom: 30px;">Individual Credential Cards (Cut & Distribute)</h2>
            
            <?php while ($row = $result->fetch_assoc()): ?>
            <div class="credential-card" style="display: block; page-break-inside: avoid;">
                <h3>🔐 Login Credentials</h3>
                <div class="credential-info">
                    <div class="label">Name:</div>
                    <div class="value"><strong><?php echo htmlspecialchars($row['name']); ?></strong></div>
                </div>
                <div class="credential-info">
                    <div class="label">Email (Username):</div>
                    <div class="value" style="color: #2563eb; font-weight: bold;"><?php echo htmlspecialchars($row['email']); ?></div>
                </div>
                <div class="credential-info">
                    <div class="label">Password:</div>
                    <div class="value" style="background: #fef3c7; padding: 5px 10px; display: inline-block; border-radius: 5px; font-family: monospace; font-weight: bold;"><?php echo $default_password; ?></div>
                </div>
                <div class="credential-info">
                    <div class="label">Login URL:</div>
                    <div class="value" style="font-size: 0.85rem;">http://localhost/ehealth/ehealth/employee/login.php</div>
                </div>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #d1d5db; color: #6b7280; font-size: 0.85rem;">
                    Please change your password after first login for security.
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
        
    </div>
</body>
</html>

<?php
$conn->close();
?>
