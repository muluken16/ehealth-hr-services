<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wereda HR - System Diagnostics</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        h2 { color: #34495e; margin-top: 30px; }
        .test-section { margin: 20px 0; padding: 20px; background: #ecf0f1; border-radius: 5px; }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        .info { color: #3498db; }
        pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #2980b9; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #3498db; color: white; }
        .iframe-container { margin: 20px 0; }
        iframe { width: 100%; height: 500px; border: 2px solid #3498db; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Wereda HR System Diagnostics</h1>
        <p>This page will help identify and fix any errors in the Wereda HR system.</p>

        <?php
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        session_start();
        if (!isset($_SESSION['role'])) {
            $_SESSION['role'] = 'wereda_hr';
            $_SESSION['user_name'] = 'HR Manager';
            $_SESSION['woreda'] = 'Woreda 1';
        }

        require_once '../db.php';
        
        echo "<div class='test-section'>";
        echo "<h2>1. Database Connection Test</h2>";
        try {
            $conn = getDBConnection();
            echo "<p class='success'>✅ Database connected successfully!</p>";
            echo "<p class='info'>Database: " . $conn->server_info . "</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
        }
        echo "</div>";

        echo "<div class='test-section'>";
        echo "<h2>2. Session Check</h2>";
        echo "<table>";
        echo "<tr><th>Session Key</th><th>Value</th></tr>";
        foreach ($_SESSION as $key => $value) {
            echo "<tr><td>$key</td><td>" . htmlspecialchars($value) . "</td></tr>";
        }
        echo "</table>";
        echo "</div>";

        echo "<div class='test-section'>";
        echo "<h2>3. Employee Count Test</h2>";
        try {
            $woreda_wildcard = "%" . ($_SESSION['woreda'] ?? 'Woreda 1') . "%";
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM employees WHERE woreda LIKE ?");
            $stmt->bind_param('s', $woreda_wildcard);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result['count'] > 0) {
                echo "<p class='success'>✅ Found {$result['count']} employees in " . $_SESSION['woreda'] . "</p>";
            } else {
                echo "<p class='warning'>⚠️ No employees found! Run the seed script first.</p>";
                echo "<p><a href='seed_employees.php' class='btn'>Add Sample Employees</a></p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ Query failed: " . $e->getMessage() . "</p>";
        }
        echo "</div>";

        echo "<div class='test-section'>";
        echo "<h2>4. File Existence Check</h2>";
        $files = [
            'wereda_hr_dashboard.php' => 'Dashboard Page',
            'wereda_hr_employee.php' => 'Employee Management Page',
            'add_employee.php' => 'Add Employee Form',
            'edit_employee.php' => 'Edit Employee Backend',
            'employee_actions.php' => 'Employee Actions API',
            'get_employees.php' => 'Get Employees API',
            'get_wereda_dashboard_data.php' => 'Dashboard Data API',
            'get_employee_detail.php' => 'Employee Detail API',
            'sidebar.php' => 'Sidebar Component',
            'navbar.php' => 'Navbar Component'
        ];
        
        echo "<table>";
        echo "<tr><th>File</th><th>Description</th><th>Status</th></tr>";
        foreach ($files as $file => $desc) {
            $exists = file_exists($file);
            $status = $exists ? "<span class='success'>✅ Exists</span>" : "<span class='error'>❌ Missing</span>";
            echo "<tr><td>$file</td><td>$desc</td><td>$status</td></tr>";
        }
        echo "</table>";
        echo "</div>";

        echo "<div class='test-section'>";
        echo "<h2>5. API Endpoint Tests</h2>";
        
        // Test get_employees.php
        echo "<h3>Testing get_employees.php</h3>";
        $response = @file_get_contents('get_employees.php?page=1&limit=5');
        if ($response) {
            $data = json_decode($response, true);
            if ($data && isset($data['success'])) {
                if ($data['success']) {
                    echo "<p class='success'>✅ API working! Found {$data['total']} employees</p>";
                } else {
                    echo "<p class='error'>❌ API returned error: " . ($data['message'] ?? 'Unknown error') . "</p>";
                }
            } else {
                echo "<p class='error'>❌ Invalid JSON response</p>";
                echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
            }
        } else {
            echo "<p class='error'>❌ Failed to call API</p>";
        }

        // Test dashboard data API
        echo "<h3>Testing get_wereda_dashboard_data.php</h3>";
        $response = @file_get_contents('get_wereda_dashboard_data.php');
        if ($response) {
            $data = json_decode($response, true);
            if ($data && isset($data['success'])) {
                if ($data['success']) {
                    echo "<p class='success'>✅ Dashboard API working!</p>";
                    echo "<p class='info'>Stats: Total={$data['stats']['totalEmployees']}, Active={$data['stats']['activeEmployees']}, On Leave={$data['stats']['onLeave']}</p>";
                } else {
                    echo "<p class='error'>❌ Dashboard API error: " . ($data['message'] ?? 'Unknown error') . "</p>";
                }
            } else {
                echo "<p class='error'>❌ Invalid JSON response</p>";
                echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
            }
        } else {
            echo "<p class='error'>❌ Failed to call Dashboard API</p>";
        }
        echo "</div>";

        echo "<div class='test-section'>";
        echo "<h2>6. Sample Employee Data</h2>";
        try {
            $stmt = $conn->prepare("SELECT employee_id, first_name, last_name, position, working_kebele, status FROM employees WHERE woreda LIKE ? LIMIT 10");
            $stmt->bind_param('s', $woreda_wildcard);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Name</th><th>Position</th><th>Kebele</th><th>Status</th></tr>";
                while ($emp = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$emp['employee_id']}</td>";
                    echo "<td>{$emp['first_name']} {$emp['last_name']}</td>";
                    echo "<td>{$emp['position']}</td>";
                    echo "<td>{$emp['working_kebele']}</td>";
                    echo "<td>{$emp['status']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='warning'>⚠️ No employees to display</p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
        }
        echo "</div>";
        ?>

        <div class="test-section">
            <h2>7. Load Pages in iFrame (Check for Errors)</h2>
            <p>Click buttons to load pages and see if they have errors:</p>
            <button class="btn" onclick="document.getElementById('testFrame').src='wereda_hr_dashboard.php'">Load Dashboard</button>
            <button class="btn" onclick="document.getElementById('testFrame').src='wereda_hr_employee.php'">Load Employee Page</button>
            <button class="btn" onclick="document.getElementById('testFrame').src='add_employee.php'">Load Add Employee</button>
            
            <div class="iframe-container">
                <iframe id="testFrame" src="about:blank"></iframe>
            </div>
        </div>

        <div class="test-section">
            <h2>8. Quick Actions</h2>
            <a href="seed_employees.php" class="btn">🌱 Add Sample Data</a>
            <a href="wereda_hr_dashboard.php" class="btn">📊 Go to Dashboard</a>
            <a href="wereda_hr_employee.php" class="btn">👥 Go to Employees</a>
            <a href="add_employee.php" class="btn">➕ Add Employee</a>
        </div>

        <div class="test-section">
            <h2>9. PHP Error Log</h2>
            <p>Check your PHP error log for detailed errors:</p>
            <p class="info">Location: <code><?php echo ini_get('error_log') ?: 'Check php.ini for error_log setting'; ?></code></p>
        </div>
    </div>
</body>
</html>
