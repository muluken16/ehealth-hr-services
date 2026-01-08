<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Default user for demo
$_SESSION['user_name'] = 'Kebele HR Officer';
$_SESSION['role'] = 'kebele_hr';

include 'db.php';
$conn = new mysqli("localhost", "root", "", "ehealth");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all employees
$sql = "SELECT employee_id, first_name, middle_name, last_name, gender, position, department_assigned, join_date, status, email, phone_number FROM employees ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Employee List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #3498db;
        }

        .stat-card h3 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-card p {
            color: #7f8c8d;
            font-weight: 500;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .table-header h2 {
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .table-header p {
            color: #6c757d;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
            position: sticky;
            top: 0;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status.active {
            background: #d4edda;
            color: #155724;
        }

        .status.on-leave {
            background: #fff3cd;
            color: #856404;
        }

        .status.inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .employee-id {
            font-family: 'Courier New', monospace;
            background: #e9ecef;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .no-data i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .actions {
                justify-content: center;
            }

            .table-container {
                overflow-x: auto;
            }

            table {
                min-width: 800px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-users"></i> Employee Management</h1>
            <p>Manage and view all employees in the HealthFirst system</p>
        </div>

        <div class="actions">
            <a href="kebele_hr/add_employee.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Employee
            </a>
            <a href="test_employee_add.php" class="btn btn-secondary">
                <i class="fas fa-cog"></i> System Test
            </a>
            <button onclick="window.location.reload()" class="btn btn-secondary">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>

        <?php
        // Calculate statistics
        $total_employees = $result->num_rows;
        $active_count = 0;
        $on_leave_count = 0;
        $inactive_count = 0;

        if ($total_employees > 0) {
            $result->data_seek(0); // Reset result pointer
            while ($row = $result->fetch_assoc()) {
                switch ($row['status']) {
                    case 'active':
                        $active_count++;
                        break;
                    case 'on-leave':
                        $on_leave_count++;
                        break;
                    case 'inactive':
                        $inactive_count++;
                        break;
                }
            }
        }
        ?>

        <div class="stats">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <h3><?php echo $total_employees; ?></h3>
                <p>Total Employees</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-user-check"></i>
                <h3><?php echo $active_count; ?></h3>
                <p>Active</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-user-clock"></i>
                <h3><?php echo $on_leave_count; ?></h3>
                <p>On Leave</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-user-times"></i>
                <h3><?php echo $inactive_count; ?></h3>
                <p>Inactive</p>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h2>Employee Directory</h2>
                <p>Complete list of all registered employees</p>
            </div>

            <?php if ($total_employees > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Join Date</th>
                            <th>Status</th>
                            <th>Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result->data_seek(0); // Reset result pointer
                        while ($row = $result->fetch_assoc()):
                            $full_name = trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
                        ?>
                            <tr>
                                <td>
                                    <span class="employee-id"><?php echo htmlspecialchars($row['employee_id']); ?></span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($full_name); ?></strong>
                                </td>
                                <td><?php echo ucfirst(htmlspecialchars($row['gender'])); ?></td>
                                <td><?php echo htmlspecialchars($row['position'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['department_assigned'] ?? 'N/A'); ?></td>
                                <td><?php echo $row['join_date'] ? date('M d, Y', strtotime($row['join_date'])) : 'N/A'; ?></td>
                                <td>
                                    <span class="status <?php echo $row['status']; ?>">
                                        <?php echo ucfirst(str_replace('-', ' ', $row['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['email']): ?>
                                        <div><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($row['email']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($row['phone_number']): ?>
                                        <div><i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['phone_number']); ?></div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-users-slash"></i>
                    <h3>No Employees Found</h3>
                    <p>Start by adding your first employee to the system.</p>
                    <br>
                    <a href="kebele_hr/add_employee.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add First Employee
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>