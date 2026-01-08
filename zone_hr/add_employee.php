<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'zone_hr') {
    header('Location: ../index.html');
    exit();
}

include '../db.php';
$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle form submission
    $employee_id = $_POST['employee_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $department = $_POST['department'];
    $position = $_POST['position'];
    $salary = $_POST['salary'];
    $join_date = $_POST['join_date'];
    $zone = $_SESSION['zone'];

    $sql = "INSERT INTO employees (employee_id, first_name, last_name, email, phone_number, department, position, salary, join_date, zone)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssss", $employee_id, $first_name, $last_name, $email, $phone, $department, $position, $salary, $join_date, $zone);

    if ($stmt->execute()) {
        header('Location: zone_hr_dashboard.php?success=1');
    } else {
        $error = $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee - Zone HR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>
        <?php include 'navbar.php'; ?>

        <div class="main-content">
            <div class="form-container">
                <h2>Add New Employee</h2>
                <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Employee ID *</label>
                            <input type="text" name="employee_id" required>
                        </div>
                        <div class="form-group">
                            <label>First Name *</label>
                            <input type="text" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name *</label>
                            <input type="text" name="last_name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email">
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Department</label>
                            <select name="department">
                                <option value="medical">Medical</option>
                                <option value="administration">Administration</option>
                                <option value="technical">Technical</option>
                                <option value="support">Support</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Position</label>
                            <input type="text" name="position">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Salary</label>
                            <input type="number" step="0.01" name="salary">
                        </div>
                        <div class="form-group">
                            <label>Join Date</label>
                            <input type="date" name="join_date">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">Add Employee</button>
                        <a href="zone_hr_dashboard.php" class="cancel-btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>