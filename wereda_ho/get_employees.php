<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'wereda_health_officer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

include '../db.php';
$conn = getDBConnection();
$user_woreda = $_SESSION['woreda'] ?? 'West Shewa Woreda 1';

// Get parameters
$action = $_GET['action'] ?? 'list';
$kebele = $_GET['kebele'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = intval($_GET['per_page'] ?? 20);

// Build base WHERE clause
$where = "WHERE woreda = '" . $conn->real_escape_string($user_woreda) . "'";

if (!empty($kebele)) {
    $where .= " AND kebele = '" . $conn->real_escape_string($kebele) . "'";
}

if (!empty($status)) {
    $where .= " AND status = '" . $conn->real_escape_string($status) . "'";
}

if (!empty($search)) {
    $search_esc = $conn->real_escape_string($search);
    $where .= " AND (first_name LIKE '%$search_esc%' OR last_name LIKE '%$search_esc%' OR employee_id LIKE '%$search_esc%' OR position LIKE '%$search_esc%')";
}

switch ($action) {
    case 'list':
        // Get total count
        $count_query = $conn->query("SELECT COUNT(*) as total FROM employees $where");
        $total = $count_query->fetch_assoc()['total'];
        
        // Get paginated employees
        $offset = ($page - 1) * $per_page;
        $sql = "SELECT * FROM employees $where ORDER BY kebele, first_name LIMIT $offset, $per_page";
        $result = $conn->query($sql);
        
        $employees = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $employees[] = $row;
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => $employees,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => $total,
                'total_pages' => ceil($total / $per_page)
            ]
        ]);
        break;
        
    case 'all':
        // Get ALL employees without pagination
        $sql = "SELECT * FROM employees $where ORDER BY kebele, first_name";
        $result = $conn->query($sql);
        
        $employees = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $employees[] = $row;
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => $employees,
            'total' => count($employees)
        ]);
        break;
        
    case 'by_kebele':
        // Group employees by kebele
        $sql = "SELECT kebele, COUNT(*) as count, 
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count
                FROM employees $where 
                GROUP BY kebele 
                ORDER BY kebele";
        $result = $conn->query($sql);
        
        $kebele_data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $kebele_data[] = $row;
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => $kebele_data
        ]);
        break;
        
    case 'kebeles':
        // Get list of all kebeles
        $sql = "SELECT DISTINCT kebele FROM employees $where ORDER BY kebele";
        $result = $conn->query($sql);
        
        $kebeles = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $kebeles[] = $row['kebele'];
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => $kebeles
        ]);
        break;
        
    case 'stats':
        // Get statistics
        $total = $conn->query("SELECT COUNT(*) as count FROM employees WHERE woreda = '$user_woreda'")->fetch_assoc()['count'];
        $active = $conn->query("SELECT COUNT(*) as count FROM employees WHERE woreda = '$user_woreda' AND status = 'active'")->fetch_assoc()['count'];
        $inactive = $total - $active;
        $kebeles = $conn->query("SELECT COUNT(DISTINCT kebele) as count FROM employees WHERE woreda = '$user_woreda'")->fetch_assoc()['count'];
        
        // Gender stats
        $gender_stats = $conn->query("
            SELECT COALESCE(gender, 'Not Specified') as gender, COUNT(*) as count
            FROM employees WHERE woreda = '$user_woreda'
            GROUP BY gender
        ");
        
        $gender_data = [];
        if ($gender_stats) {
            while ($row = $gender_stats->fetch_assoc()) {
                $gender_data[] = $row;
            }
        }
        
        // Position stats
        $position_stats = $conn->query("
            SELECT position, COUNT(*) as count
            FROM employees WHERE woreda = '$user_woreda' AND position IS NOT NULL
            GROUP BY position
            ORDER BY count DESC
            LIMIT 10
        ");
        
        $position_data = [];
        if ($position_stats) {
            while ($row = $position_stats->fetch_assoc()) {
                $position_data[] = $row;
            }
        }
        
        echo json_encode([
            'success' => true,
            'stats' => [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
                'kebeles' => $kebeles,
                'gender' => $gender_data,
                'positions' => $position_data
            ]
        ]);
        break;
        
    case 'detail':
        // Get single employee details
        $employee_id = $_GET['employee_id'] ?? '';
        if (empty($employee_id)) {
            echo json_encode(['success' => false, 'message' => 'Employee ID required']);
            exit();
        }
        
        $emp_id_esc = $conn->real_escape_string($employee_id);
        $result = $conn->query("SELECT * FROM employees WHERE employee_id = '$emp_id_esc' AND woreda = '$user_woreda'");
        
        if ($result && $result->num_rows > 0) {
            $employee = $result->fetch_assoc();
            echo json_encode(['success' => true, 'data' => $employee]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Employee not found']);
        }
        break;
        
    case 'export':
        // Export all employees to CSV format
        $sql = "SELECT * FROM employees $where ORDER BY kebele, first_name";
        $result = $conn->query($sql);
        
        $employees = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $employees[] = $row;
            }
        }
        
        // Generate CSV
        $csv = "Employee ID,First Name,Last Name,Position,Kebele,Department,Phone,Email,Gender,Status,Salary\n";
        foreach ($employees as $emp) {
            $csv .= '"' . ($emp['employee_id'] ?? '') . '",';
            $csv .= '"' . ($emp['first_name'] ?? '') . '",';
            $csv .= '"' . ($emp['last_name'] ?? '') . '",';
            $csv .= '"' . ($emp['position'] ?? '') . '",';
            $csv .= '"' . ($emp['kebele'] ?? '') . '",';
            $csv .= '"' . ($emp['department'] ?? '') . '",';
            $csv .= '"' . ($emp['phone'] ?? '') . '",';
            $csv .= '"' . ($emp['email'] ?? '') . '",';
            $csv .= '"' . ($emp['gender'] ?? '') . '",';
            $csv .= '"' . ($emp['status'] ?? '') . '",';
            $csv .= '"' . ($emp['salary'] ?? '0') . '"';
            $csv .= "\n";
        }
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="employees_' . date('Y-m-d') . '.csv"');
        echo $csv;
        exit();
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
