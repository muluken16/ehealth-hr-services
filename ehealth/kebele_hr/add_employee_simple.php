<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

echo "<!DOCTYPE html>";
echo "<html><head><title>Test</title></head><body>";
echo "<h1>🧪 Simple Add Employee Test</h1>";

// Default user for demo
$_SESSION['user_name'] = 'Kebele HR Officer';
$_SESSION['role'] = 'kebele_hr';

echo "✅ Session variables set<br>";

// Test database connection
try {
    $conn = new mysqli("localhost", "root", "", "ehealth");
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "✅ Database connected<br>";
    
    // Test employees table
    $result = $conn->query("SELECT COUNT(*) as count FROM employees");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✅ Employees table accessible, count: " . $row['count'] . "<br>";
    } else {
        echo "❌ Cannot access employees table<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

echo "<br><h2>📝 Simple Employee Form</h2>";
?>

<form method="POST" style="max-width: 600px; margin: 20px 0;">
    <div style="margin: 10px 0;">
        <label>First Name *</label><br>
        <input type="text" name="first_name" required style="width: 100%; padding: 8px;">
    </div>
    
    <div style="margin: 10px 0;">
        <label>Last Name *</label><br>
        <input type="text" name="last_name" required style="width: 100%; padding: 8px;">
    </div>
    
    <div style="margin: 10px 0;">
        <label>Email *</label><br>
        <input type="email" name="email" required style="width: 100%; padding: 8px;">
    </div>
    
    <div style="margin: 10px 0;">
        <label>Position *</label><br>
        <input type="text" name="position" required style="width: 100%; padding: 8px;">
    </div>
    
    <div style="margin: 10px 0;">
        <label>Gender *</label><br>
        <select name="gender" required style="width: 100%; padding: 8px;">
            <option value="">Select Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
        </select>
    </div>
    
    <div style="margin: 10px 0;">
        <label>Date of Birth *</label><br>
        <input type="date" name="date_of_birth" required style="width: 100%; padding: 8px;">
    </div>
    
    <div style="margin: 10px 0;">
        <label>Salary *</label><br>
        <input type="number" name="salary" required step="0.01" style="width: 100%; padding: 8px;">
    </div>
    
    <button type="submit" style="background: #007bff; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;">
        Add Employee
    </button>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "<h3>🔄 Processing Form Submission...</h3>";
    
    try {
        // Generate employee ID
        $year = date('Y');
        $random = rand(1000, 9999);
        $employee_id = "HF-{$year}-{$random}";
        
        // Get form data
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $position = trim($_POST['position']);
        $gender = $_POST['gender'];
        $date_of_birth = $_POST['date_of_birth'];
        $salary = $_POST['salary'];
        
        echo "✅ Form data collected<br>";
        echo "Employee ID: $employee_id<br>";
        echo "Name: $first_name $last_name<br>";
        
        // Simple insert
        $sql = "INSERT INTO employees (employee_id, first_name, last_name, email, position, gender, date_of_birth, salary, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("sssssssds", $employee_id, $first_name, $last_name, $email, $position, $gender, $date_of_birth, $salary, $_SESSION['user_name']);
        
        if ($stmt->execute()) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
            echo "✅ Employee added successfully!<br>";
            echo "Employee ID: $employee_id<br>";
            echo "Name: $first_name $last_name<br>";
            echo "</div>";
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 10px 0;'>";
        echo "❌ Error: " . $e->getMessage();
        echo "</div>";
    }
}

echo "<br><h3>🔗 Navigation</h3>";
echo "<a href='../view_employees.php'>View All Employees</a> | ";
echo "<a href='../debug_add_employee.php'>Debug Page</a>";

echo "</body></html>";
?>