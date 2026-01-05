<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'zone_hr') {
    header('Location: ../index.html');
    exit();
}

include '../db.php';
$conn = getDBConnection();

$employee_id = $_GET['id'] ?? '';
$view_mode = isset($_GET['view']);

if (!$employee_id) {
    header('Location: zone_hr_dashboard.php');
    exit();
}

$zone = $_SESSION['zone'];

// Get employee data
$sql = "SELECT * FROM employees WHERE employee_id = ? AND zone = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $employee_id, $zone);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    header('Location: zone_hr_dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update employee
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $department = $_POST['department'];
    $position = $_POST['position'];
    $salary = $_POST['salary'];
    $status = $_POST['status'];

    $sql = "UPDATE employees SET first_name=?, last_name=?, email=?, phone_number=?, department=?, position=?, salary=?, status=? WHERE employee_id=? AND zone=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssdsss", $first_name, $last_name, $email, $phone, $department, $position, $salary, $status, $employee_id, $zone);

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
    <title>Edit Employee - Zone HR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>
        <?php include 'navbar.php'; ?>

        <div class="main-content">
            <div class="form-container">
                <h2><?php echo $view_mode ? 'View Employee' : 'Edit Employee'; ?></h2>
                <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Employee ID</label>
                            <input type="text" value="<?php echo htmlspecialchars($employee['employee_id']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>First Name *</label>
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($employee['first_name']); ?>" required <?php if ($view_mode) echo 'readonly'; ?>>
                        </div>
                        <div class="form-group">
                            <label>Last Name *</label>
                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($employee['last_name']); ?>" required <?php if ($view_mode) echo 'readonly'; ?>>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($employee['email']); ?>" <?php if ($view_mode) echo 'readonly'; ?>>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($employee['phone_number']); ?>" <?php if ($view_mode) echo 'readonly'; ?>>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Department</label>
                            <select name="department" <?php if ($view_mode) echo 'disabled'; ?>>
                                <option value="medical" <?php if ($employee['department'] == 'medical') echo 'selected'; ?>>Medical</option>
                                <option value="administration" <?php if ($employee['department'] == 'administration') echo 'selected'; ?>>Administration</option>
                                <option value="technical" <?php if ($employee['department'] == 'technical') echo 'selected'; ?>>Technical</option>
                                <option value="support" <?php if ($employee['department'] == 'support') echo 'selected'; ?>>Support</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Position</label>
                            <input type="text" name="position" value="<?php echo htmlspecialchars($employee['position']); ?>" <?php if ($view_mode) echo 'readonly'; ?>>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Salary</label>
                            <input type="number" step="0.01" name="salary" value="<?php echo htmlspecialchars($employee['salary']); ?>" <?php if ($view_mode) echo 'readonly'; ?>>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" <?php if ($view_mode) echo 'disabled'; ?>>
                                <option value="active" <?php if ($employee['status'] == 'active') echo 'selected'; ?>>Active</option>
                                <option value="on-leave" <?php if ($employee['status'] == 'on-leave') echo 'selected'; ?>>On Leave</option>
                                <option value="inactive" <?php if ($employee['status'] == 'inactive') echo 'selected'; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <?php if (!$view_mode): ?>
                        <button type="submit" class="submit-btn">Update Employee</button>
                        <?php endif; ?>
                        <a href="zone_hr_dashboard.php" class="cancel-btn">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>