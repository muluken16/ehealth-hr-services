<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'wereda_health_officer') {
    header('Location: ../index.html');
    exit();
}

include '../db.php';
$conn = getDBConnection();
$user_woreda = $_SESSION['woreda'] ?? 'Wereda 1';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action == 'add') {
            $stmt = $conn->prepare("INSERT INTO emergency_responses (incident_type, description, location, severity, status, reported_by, assigned_to, zone, woreda, kebele) VALUES (?, ?, ?, ?, 'reported', ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssisss", $_POST['incident_type'], $_POST['description'], $_POST['location'], $_POST['severity'], $_SESSION['user_id'], $_POST['assigned_to'], $_POST['zone'], $user_woreda, $_POST['kebele']);
            $stmt->execute();
            $stmt->close();
        } elseif ($action == 'update') {
            $stmt = $conn->prepare("UPDATE emergency_responses SET incident_type=?, description=?, location=?, severity=?, status=?, assigned_to=?, kebele=? WHERE id=? AND woreda=?");
            $stmt->bind_param("sssssisss", $_POST['incident_type'], $_POST['description'], $_POST['location'], $_POST['severity'], $_POST['status'], $_POST['assigned_to'], $_POST['kebele'], $_POST['id'], $user_woreda);
            $stmt->execute();
            $stmt->close();
        } elseif ($action == 'delete') {
            $stmt = $conn->prepare("DELETE FROM emergency_responses WHERE id=? AND woreda=?");
            $stmt->bind_param("is", $_POST['id'], $user_woreda);
            $stmt->execute();
            $stmt->close();
        }
    }
    header('Location: wereda_emergency.php');
    exit();
}

// Get emergency responses with filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$severity_filter = isset($_GET['severity']) ? $_GET['severity'] : '';

$query = "SELECT e.*, u1.name as reported_by_name, u2.name as assigned_to_name FROM emergency_responses e LEFT JOIN users u1 ON e.reported_by = u1.id LEFT JOIN users u2 ON e.assigned_to = u2.id WHERE e.woreda = ?";
$params = [$user_woreda];
$types = "s";

if (!empty($status_filter)) {
    $query .= " AND e.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($severity_filter)) {
    $query .= " AND e.severity = ?";
    $params[] = $severity_filter;
    $types .= "s";
}

$query .= " ORDER BY
    CASE e.severity
        WHEN 'critical' THEN 1
        WHEN 'high' THEN 2
        WHEN 'medium' THEN 3
        WHEN 'low' THEN 4
    END,
    e.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$emergencies = $stmt->get_result();

// Get emergency stats
$stats = $conn->query("
    SELECT
        COUNT(*) as total_incidents,
        SUM(CASE WHEN status = 'reported' THEN 1 ELSE 0 END) as reported,
        SUM(CASE WHEN status = 'responding' THEN 1 ELSE 0 END) as responding,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
        SUM(CASE WHEN severity = 'critical' THEN 1 ELSE 0 END) as critical,
        SUM(CASE WHEN severity = 'high' THEN 1 ELSE 0 END) as high
    FROM emergency_responses
    WHERE woreda = '$user_woreda'
")->fetch_assoc();

// Get available staff for assignment
$staff = $conn->query("SELECT id, name FROM users WHERE role = 'kebele_health_officer' AND woreda = '$user_woreda' ORDER BY name");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Wereda Emergency Response</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/styleho.css">
</head>
<body>
    <div class="admin-container">
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="wereda_ho_dashboard.php" class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <span class="logo-text">HealthFirst</span>
                </a>
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>

            <nav class="sidebar-menu">
                <ul>
                    <li class="menu-item">
                        <a href="wereda_ho_dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="menu-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="wereda_patients.php">
                            <i class="fas fa-user-injured"></i>
                            <span class="menu-text">Patients</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="wereda_appointments.php">
                            <i class="fas fa-calendar-check"></i>
                            <span class="menu-text">Appointments</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="wereda_inventory.php">
                            <i class="fas fa-pills"></i>
                            <span class="menu-text">Inventory</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="wereda_reports.php">
                            <i class="fas fa-chart-bar"></i>
                            <span class="menu-text">Reports</span>
                        </a>
                    </li>
                    <li class="menu-item active">
                        <a href="wereda_emergency.php">
                            <i class="fas fa-ambulance"></i>
                            <span class="menu-text">Emergency</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="wereda_qa.php">
                            <i class="fas fa-clipboard-check"></i>
                            <span class="menu-text">Quality Assurance</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <button class="mobile-menu-btn" id="mobileMenuBtn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">Emergency Response - <?php echo htmlspecialchars($user_woreda); ?></h1>
                </div>

                <div class="header-actions">
                    <button class="btn-primary" onclick="openModal('addEmergencyModal')">
                        <i class="fas fa-plus"></i> Report Emergency
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_incidents']; ?></h3>
                            <p>Total Incidents</p>
                        </div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['reported']; ?></h3>
                            <p>Reported</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['responding']; ?></h3>
                            <p>Responding</p>
                        </div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['resolved']; ?></h3>
                            <p>Resolved</p>
                        </div>
                    </div>
                    <div class="stat-card danger">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['critical']; ?></h3>
                            <p>Critical</p>
                        </div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-icon">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['high']; ?></h3>
                            <p>High Priority</p>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="filters-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status">
                                        <option value="">All Status</option>
                                        <option value="reported" <?php echo $status_filter == 'reported' ? 'selected' : ''; ?>>Reported</option>
                                        <option value="responding" <?php echo $status_filter == 'responding' ? 'selected' : ''; ?>>Responding</option>
                                        <option value="resolved" <?php echo $status_filter == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Severity</label>
                                    <select name="severity">
                                        <option value="">All Severity</option>
                                        <option value="critical" <?php echo $severity_filter == 'critical' ? 'selected' : ''; ?>>Critical</option>
                                        <option value="high" <?php echo $severity_filter == 'high' ? 'selected' : ''; ?>>High</option>
                                        <option value="medium" <?php echo $severity_filter == 'medium' ? 'selected' : ''; ?>>Medium</option>
                                        <option value="low" <?php echo $severity_filter == 'low' ? 'selected' : ''; ?>>Low</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn-secondary">Filter</button>
                                    <a href="wereda_emergency.php" class="btn-secondary">Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Emergency Incidents Table -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Emergency Incidents</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Incident Type</th>
                                        <th>Location</th>
                                        <th>Severity</th>
                                        <th>Status</th>
                                        <th>Reported By</th>
                                        <th>Assigned To</th>
                                        <th>Reported</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($emergency = $emergencies->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($emergency['incident_type']); ?></td>
                                        <td><?php echo htmlspecialchars($emergency['location']); ?></td>
                                        <td>
                                            <span class="status-badge <?php
                                                echo $emergency['severity'] == 'critical' ? 'danger' :
                                                     ($emergency['severity'] == 'high' ? 'warning' :
                                                     ($emergency['severity'] == 'medium' ? '' : 'success'));
                                            ?>">
                                                <?php echo ucfirst($emergency['severity']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php
                                                echo $emergency['status'] == 'resolved' ? 'success' :
                                                     ($emergency['status'] == 'responding' ? '' : 'warning');
                                            ?>">
                                                <?php echo ucfirst($emergency['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($emergency['reported_by_name'] ?? 'Unknown'); ?></td>
                                        <td><?php echo htmlspecialchars($emergency['assigned_to_name'] ?? 'Unassigned'); ?></td>
                                        <td><?php echo date('M j, Y H:i', strtotime($emergency['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" onclick="viewEmergency(<?php echo $emergency['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="action-btn edit" onclick="editEmergency(<?php echo $emergency['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="action-btn delete" onclick="deleteEmergency(<?php echo $emergency['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Emergency Modal -->
    <div id="addEmergencyModal" class="modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>Report Emergency Incident</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addEmergencyForm" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="incident_type">Incident Type *</label>
                        <select id="incident_type" name="incident_type" required>
                            <option value="">Select Incident Type</option>
                            <option value="Medical Emergency">Medical Emergency</option>
                            <option value="Accident">Accident</option>
                            <option value="Fire">Fire</option>
                            <option value="Natural Disaster">Natural Disaster</option>
                            <option value="Violence">Violence</option>
                            <option value="Poisoning">Poisoning</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" rows="3" required placeholder="Describe the emergency situation..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="location">Location *</label>
                        <input type="text" id="location" name="location" required placeholder="Specific location of the incident">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="severity">Severity *</label>
                            <select id="severity" name="severity" required>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="assigned_to">Assign To</label>
                            <select id="assigned_to" name="assigned_to">
                                <option value="">Select Staff Member</option>
                                <?php
                                $staff->data_seek(0);
                                while ($member = $staff->fetch_assoc()): ?>
                                <option value="<?php echo $member['id']; ?>">
                                    <?php echo htmlspecialchars($member['name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="zone">Zone *</label>
                            <input type="text" id="zone" name="zone" required>
                        </div>
                        <div class="form-group">
                            <label for="kebele">Kebele *</label>
                            <input type="text" id="kebele" name="kebele" required>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary cancel-btn">Cancel</button>
                        <button type="submit" class="btn-primary">Report Emergency</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Emergency Modal -->
    <div id="editEmergencyModal" class="modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>Update Emergency Incident</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editEmergencyForm" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label for="edit_incident_type">Incident Type *</label>
                        <select id="edit_incident_type" name="incident_type" required>
                            <option value="Medical Emergency">Medical Emergency</option>
                            <option value="Accident">Accident</option>
                            <option value="Fire">Fire</option>
                            <option value="Natural Disaster">Natural Disaster</option>
                            <option value="Violence">Violence</option>
                            <option value="Poisoning">Poisoning</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description *</label>
                        <textarea id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_location">Location *</label>
                        <input type="text" id="edit_location" name="location" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_severity">Severity *</label>
                            <select id="edit_severity" name="severity" required>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_status">Status *</label>
                            <select id="edit_status" name="status" required>
                                <option value="reported">Reported</option>
                                <option value="responding">Responding</option>
                                <option value="resolved">Resolved</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_assigned_to">Assign To</label>
                        <select id="edit_assigned_to" name="assigned_to">
                            <option value="">Select Staff Member</option>
                            <?php
                            $staff->data_seek(0);
                            while ($member = $staff->fetch_assoc()): ?>
                            <option value="<?php echo $member['id']; ?>">
                                <?php echo htmlspecialchars($member['name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_kebele">Kebele *</label>
                        <input type="text" id="edit_kebele" name="kebele" required>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary cancel-btn">Cancel</button>
                        <button type="submit" class="btn-primary">Update Emergency</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function viewEmergency(id) {
            // Implement view emergency details
            alert('View emergency details for ID: ' + id);
        }

        function editEmergency(id) {
            // Fetch emergency data and populate edit form
            fetch('get_emergency.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_incident_type').value = data.incident_type;
                    document.getElementById('edit_description').value = data.description;
                    document.getElementById('edit_location').value = data.location;
                    document.getElementById('edit_severity').value = data.severity;
                    document.getElementById('edit_status').value = data.status;
                    document.getElementById('edit_assigned_to').value = data.assigned_to;
                    document.getElementById('edit_kebele').value = data.kebele;
                    openModal('editEmergencyModal');
                });
        }

        function deleteEmergency(id) {
            if (confirm('Are you sure you want to delete this emergency incident?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Event listeners
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                closeModal(e.target.id);
            }
        });

        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                closeModal(btn.closest('.modal').id);
            });
        });

        document.querySelectorAll('.cancel-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                closeModal(btn.closest('.modal').id);
            });
        });
    </script>
</body>
</html>