<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'zone_health_officer') {
    header('Location: ../index.html');
    exit();
}

include '../db.php';
$conn = getDBConnection();
$user_zone = $_SESSION['zone'] ?? 'West Shewa';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action == 'add') {
            $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_name, appointment_date, appointment_time, department, notes, zone, woreda, kebele, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssssssi", $_POST['patient_id'], $_POST['doctor_name'], $_POST['appointment_date'], $_POST['appointment_time'], $_POST['department'], $_POST['notes'], $user_zone, $_POST['woreda'], $_POST['kebele'], $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
        } elseif ($action == 'update') {
            $stmt = $conn->prepare("UPDATE appointments SET patient_id=?, doctor_name=?, appointment_date=?, appointment_time=?, department=?, status=?, notes=?, woreda=?, kebele=? WHERE id=? AND zone=?");
            $stmt->bind_param("issssssssis", $_POST['patient_id'], $_POST['doctor_name'], $_POST['appointment_date'], $_POST['appointment_time'], $_POST['department'], $_POST['status'], $_POST['notes'], $_POST['woreda'], $_POST['kebele'], $_POST['id'], $user_zone);
            $stmt->execute();
            $stmt->close();
        } elseif ($action == 'delete') {
            $stmt = $conn->prepare("DELETE FROM appointments WHERE id=? AND zone=?");
            $stmt->bind_param("is", $_POST['id'], $user_zone);
            $stmt->execute();
            $stmt->close();
        }
    }
    header('Location: zone_appointments.php');
    exit();
}

// Get appointments with filters
$date_filter = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$woreda_filter = isset($_GET['woreda']) ? $_GET['woreda'] : '';

$query = "SELECT a.*, p.first_name, p.last_name FROM appointments a JOIN patients p ON a.patient_id = p.id WHERE a.zone = ?";
$params = [$user_zone];
$types = "s";

if (!empty($date_filter)) {
    $query .= " AND a.appointment_date = ?";
    $params[] = $date_filter;
    $types .= "s";
}

if (!empty($status_filter)) {
    $query .= " AND a.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($woreda_filter)) {
    $query .= " AND a.woreda = ?";
    $params[] = $woreda_filter;
    $types .= "s";
}

$query .= " ORDER BY a.appointment_date DESC, a.appointment_time ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$appointments = $stmt->get_result();

// Get patients for dropdown
$patients = $conn->query("SELECT id, first_name, last_name FROM patients WHERE zone = '$user_zone' AND status = 'active' ORDER BY first_name");

// Get weredas for filter
$weredas = $conn->query("SELECT DISTINCT woreda FROM appointments WHERE zone = '$user_zone' ORDER BY woreda");

// Get appointment stats
$stats = $conn->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM appointments
    WHERE zone = '$user_zone' AND appointment_date >= CURDATE()
")->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Zone Appointments Management</title>
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
                <a href="zone_ho_dashboard.php" class="logo">
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
                        <a href="zone_ho_dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="menu-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="zone_patients.php">
                            <i class="fas fa-user-injured"></i>
                            <span class="menu-text">Patients</span>
                        </a>
                    </li>
                    <li class="menu-item active">
                        <a href="zone_appointments.php">
                            <i class="fas fa-calendar-check"></i>
                            <span class="menu-text">Appointments</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="zone_inventory.php">
                            <i class="fas fa-pills"></i>
                            <span class="menu-text">Inventory</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="zone_reports.php">
                            <i class="fas fa-chart-bar"></i>
                            <span class="menu-text">Reports</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="zone_emergency.php">
                            <i class="fas fa-ambulance"></i>
                            <span class="menu-text">Emergency</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="zone_qa.php">
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
                    <h1 class="page-title">Appointments Management - <?php echo htmlspecialchars($user_zone); ?></h1>
                </div>

                <div class="header-actions">
                    <button class="btn-primary" onclick="openModal('addAppointmentModal')">
                        <i class="fas fa-plus"></i> Schedule Appointment
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total']; ?></h3>
                            <p>Upcoming Appointments</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['scheduled']; ?></h3>
                            <p>Scheduled</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['confirmed']; ?></h3>
                            <p>Confirmed</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['cancelled']; ?></h3>
                            <p>Cancelled</p>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="filters-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status">
                                        <option value="">All Status</option>
                                        <option value="scheduled" <?php echo $status_filter == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                        <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Wereda</label>
                                    <select name="woreda">
                                        <option value="">All Weredas</option>
                                        <?php while ($woreda = $weredas->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($woreda['woreda']); ?>" <?php echo $woreda_filter == $woreda['woreda'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($woreda['woreda']); ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn-secondary">Filter</button>
                                    <a href="zone_appointments.php" class="btn-secondary">Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Appointments Table -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Appointments</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Date & Time</th>
                                        <th>Department</th>
                                        <th>Wereda</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($appt = $appointments->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($appt['first_name'] . ' ' . $appt['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appt['doctor_name']); ?></td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($appt['appointment_date'] . ' ' . $appt['appointment_time'])); ?></td>
                                        <td><?php echo htmlspecialchars($appt['department']); ?></td>
                                        <td><?php echo htmlspecialchars($appt['woreda']); ?></td>
                                        <td><span class="status-badge <?php echo $appt['status']; ?>"><?php echo ucfirst($appt['status']); ?></span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" onclick="viewAppointment(<?php echo $appt['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="action-btn edit" onclick="editAppointment(<?php echo $appt['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="action-btn delete" onclick="deleteAppointment(<?php echo $appt['id']; ?>)">
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

    <!-- Add Appointment Modal -->
    <div id="addAppointmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Schedule Appointment</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addAppointmentForm" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="patient_id">Patient *</label>
                        <select id="patient_id" name="patient_id" required>
                            <option value="">Select Patient</option>
                            <?php
                            $patients->data_seek(0);
                            while ($patient = $patients->fetch_assoc()): ?>
                            <option value="<?php echo $patient['id']; ?>">
                                <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="doctor_name">Doctor *</label>
                        <input type="text" id="doctor_name" name="doctor_name" placeholder="Dr. John Smith" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="appointment_date">Date *</label>
                            <input type="date" id="appointment_date" name="appointment_date" required>
                        </div>
                        <div class="form-group">
                            <label for="appointment_time">Time *</label>
                            <input type="time" id="appointment_time" name="appointment_time" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="department">Department *</label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <option value="Cardiology">Cardiology</option>
                            <option value="Pediatrics">Pediatrics</option>
                            <option value="Orthopedics">Orthopedics</option>
                            <option value="Dermatology">Dermatology</option>
                            <option value="General Medicine">General Medicine</option>
                            <option value="Emergency">Emergency</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="woreda">Wereda *</label>
                            <input type="text" id="woreda" name="woreda" required>
                        </div>
                        <div class="form-group">
                            <label for="kebele">Kebele *</label>
                            <input type="text" id="kebele" name="kebele" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="3"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary cancel-btn">Cancel</button>
                        <button type="submit" class="btn-primary">Schedule Appointment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Appointment Modal -->
    <div id="editAppointmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Appointment</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editAppointmentForm" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label for="edit_patient_id">Patient *</label>
                        <select id="edit_patient_id" name="patient_id" required>
                            <option value="">Select Patient</option>
                            <?php
                            $patients->data_seek(0);
                            while ($patient = $patients->fetch_assoc()): ?>
                            <option value="<?php echo $patient['id']; ?>">
                                <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_doctor_name">Doctor *</label>
                        <input type="text" id="edit_doctor_name" name="doctor_name" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_appointment_date">Date *</label>
                            <input type="date" id="edit_appointment_date" name="appointment_date" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_appointment_time">Time *</label>
                            <input type="time" id="edit_appointment_time" name="appointment_time" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_department">Department *</label>
                        <select id="edit_department" name="department" required>
                            <option value="Cardiology">Cardiology</option>
                            <option value="Pediatrics">Pediatrics</option>
                            <option value="Orthopedics">Orthopedics</option>
                            <option value="Dermatology">Dermatology</option>
                            <option value="General Medicine">General Medicine</option>
                            <option value="Emergency">Emergency</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_status">Status *</label>
                        <select id="edit_status" name="status" required>
                            <option value="scheduled">Scheduled</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_woreda">Wereda *</label>
                            <input type="text" id="edit_woreda" name="woreda" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_kebele">Kebele *</label>
                            <input type="text" id="edit_kebele" name="kebele" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_notes">Notes</label>
                        <textarea id="edit_notes" name="notes" rows="3"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary cancel-btn">Cancel</button>
                        <button type="submit" class="btn-primary">Update Appointment</button>
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

        function viewAppointment(id) {
            // Implement view appointment details
            alert('View appointment details for ID: ' + id);
        }

        function editAppointment(id) {
            // Fetch appointment data and populate edit form
            fetch('get_appointment.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_patient_id').value = data.patient_id;
                    document.getElementById('edit_doctor_name').value = data.doctor_name;
                    document.getElementById('edit_appointment_date').value = data.appointment_date;
                    document.getElementById('edit_appointment_time').value = data.appointment_time;
                    document.getElementById('edit_department').value = data.department;
                    document.getElementById('edit_status').value = data.status;
                    document.getElementById('edit_woreda').value = data.woreda;
                    document.getElementById('edit_kebele').value = data.kebele;
                    document.getElementById('edit_notes').value = data.notes;
                    openModal('editAppointmentModal');
                });
        }

        function deleteAppointment(id) {
            if (confirm('Are you sure you want to delete this appointment?')) {
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