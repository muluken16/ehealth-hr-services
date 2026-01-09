<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quick Fix - Employee Data</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #2c3e50; }
        .test { padding: 15px; margin: 15px 0; border-radius: 5px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; border: 1px solid #ffeeba; }
        .btn { padding: 12px 24px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #2980b9; }
        pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .loading { text-align: center; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Quick Fix - Employee Data Issue</h1>
        
        <?php
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        session_start();
        if (!isset($_SESSION['role'])) {
            $_SESSION['role'] = 'wereda_hr';
            $_SESSION['woreda'] = 'Woreda 1';
            $_SESSION['user_name'] = 'HR Manager';
        }
        
        require_once '../db.php';
        
        try {
            $conn = getDBConnection();
            $woreda_wildcard = "%Woreda 1%";
            
            // Check employee count
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM employees WHERE woreda LIKE ?");
            $stmt->bind_param('s', $woreda_wildcard);
            $stmt->execute();
            $count = $stmt->get_result()->fetch_assoc()['count'];
            
            echo "<div class='test " . ($count > 0 ? 'success' : 'warning') . "'>";
            echo "<h3>Step 1: Database Check</h3>";
            echo "<p>Employees in Woreda 1: <strong>$count</strong></p>";
            
            if ($count == 0) {
                echo "<p>⚠️ No employees found! Adding sample data...</p>";
                
                // Add sample employees
                $sampleData = [
                    ['Dr. Ahmed', 'Ali', 'Male', 'Doctor', 'Medical', 'Kebele 01', '0911111111'],
                    ['Nurse Fatuma', 'Hassan', 'Female', 'Nurse', 'Nursing', 'Kebele 02', '0922222222'],
                    ['Dr. Yohannes', 'Tesfaye', 'Male', 'Surgeon', 'Surgery', 'Kebele 01', '0933333333'],
                    ['Pharmacist Sarah', 'Mohammed', 'Female', 'Pharmacist', 'Pharmacy', 'Kebele 03', '0944444444'],
                    ['Tech Abdi', 'Ibrahim', 'Male', 'Lab Tech', 'Laboratory', 'Kebele 02', '0955555555']
                ];
                
                $stmt = $conn->prepare("INSERT INTO employees (employee_id, first_name, last_name, gender, position, department_assigned, working_kebele, working_woreda, woreda, phone, salary, status, join_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())");
                
                $added = 0;
                foreach ($sampleData as $emp) {
                    $empId = 'WRD' . rand(1000, 9999);
                    $woreda = 'Woreda 1';
                    $salary = rand(20000, 50000);
                    
                    $stmt->bind_param('ssssssssssd', $empId, $emp[0], $emp[1], $emp[2], $emp[3], $emp[4], $emp[5], $woreda, $woreda, $emp[6], $salary);
                    
                    if ($stmt->execute()) {
                        $added++;
                    }
                }
                
                echo "<p>✅ Added $added employees!</p>";
                $count = $added;
            } else {
                echo "<p>✅ Database has employee data</p>";
            }
            echo "</div>";
            
            // Test API
            echo "<div id='apiTest' class='test'>";
            echo "<h3>Step 2: Testing API</h3>";
            echo "<button class='btn' onclick='testAPI()'>Test Employee API</button>";
            echo "<div id='apiResult'></div>";
            echo "</div>";
            
            // Show sample employees
            echo "<div class='test success'>";
            echo "<h3>Step 3: Sample Employee Data</h3>";
            $stmt = $conn->prepare("SELECT employee_id, first_name, last_name, position, working_kebele, status FROM employees WHERE woreda LIKE ? LIMIT 10");
            $stmt->bind_param('s', $woreda_wildcard);
            $stmt->execute();
            $result = $stmt->get_result();
            
            echo "<table border='1' cellpadding='10' style='width:100%; border-collapse:collapse;'>";
            echo "<tr style='background:#3498db; color:white;'><th>ID</th><th>Name</th><th>Position</th><th>Kebele</th><th>Status</th></tr>";
            
            while ($emp = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$emp['employee_id']}</td>";
                echo "<td>{$emp['first_name']} {$emp['last_name']}</td>";
                echo "<td>{$emp['position']}</td>";
                echo "<td>{$emp['working_kebele']}</td>";
                echo "<td style='color:" . ($emp['status']=='active'?'green':'red') . "'>{$emp['status']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='test error'><h3>Error:</h3><p>" . $e->getMessage() . "</p></div>";
        }
        ?>
        
        <div class="test">
            <h3>Step 4: Go to Pages</h3>
            <a href="wereda_hr_employee.php" class="btn">Open Employee Page</a>
            <a href="wereda_hr_dashboard.php" class="btn">Open Dashboard</a>
            <button class="btn" onclick="window.location.reload()">Refresh This Page</button>
        </div>
        
        <div class="test">
            <h3>Step 5: Browser Console Check</h3>
            <p>1. Press <strong>F12</strong> to open console</p>
            <p>2. Go to Employee Page</p>
            <p>3. Check console for errors</p>
            <p>4. Share any red error messages</p>
        </div>
    </div>

    <script>
        function testAPI() {
            const resultDiv = document.getElementById('apiResult');
            resultDiv.innerHTML = '<div class="loading">Testing API...</div>';
            
            fetch('get_employees.php?page=1&limit=5')
                .then(response => {
                    console.log('API Response Status:', response.status);
                    return response.text();
                })
                .then(text => {
                    console.log('Raw Response:', text);
                    
                    try {
                        const data = JSON.parse(text);
                        
                        if (data.success) {
                            let html = '<div class="success">';
                            html += '<h4>✅ API Working!</h4>';
                            html += '<p>Total Employees: ' + data.total + '</p>';
                            html += '<p>Employees returned: ' + data.employees.length + '</p>';
                            html += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                            html += '</div>';
                            resultDiv.innerHTML = html;
                        } else {
                            resultDiv.innerHTML = '<div class="error">❌ API Error: ' + (data.message || 'Unknown') + '</div>';
                        }
                    } catch (e) {
                        resultDiv.innerHTML = '<div class="error">❌ JSON Parse Error: ' + e.message + '<br><pre>' + text.substring(0, 500) + '</pre></div>';
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = '<div class="error">❌ Fetch Error: ' + error.message + '</div>';
                });
        }
    </script>
</body>
</html>
