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
            $stmt = $conn->prepare("INSERT INTO patients (first_name, last_name, date_of_birth, gender, phone, email, address, blood_type, medical_history, emergency_contact, zone, woreda, kebele) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssssssss", $_POST['first_name'], $_POST['last_name'], $_POST['date_of_birth'], $_POST['gender'], $_POST['phone'], $_POST['email'], $_POST['address'], $_POST['blood_type'], $_POST['medical_history'], $_POST['emergency_contact'], $_POST['zone'], $user_woreda, $_POST['kebele']);
            $stmt->execute();
            $stmt->close();
        } elseif ($action == 'update') {
            $stmt = $conn->prepare("UPDATE patients SET first_name=?, last_name=?, date_of_birth=?, gender=?, phone=?, email=?, address=?, blood_type=?, medical_history=?, emergency_contact=?, kebele=? WHERE id=? AND woreda=?");
            $stmt->bind_param("sssssssssssss", $_POST['first_name'], $_POST['last_name'], $_POST['date_of_birth'], $_POST['gender'], $_POST['phone'], $_POST['email'], $_POST['address'], $_POST['blood_type'], $_POST['medical_history'], $_POST['emergency_contact'], $_POST['kebele'], $_POST['id'], $user_woreda);
            $stmt->execute();
            $stmt->close();
        } elseif ($action == 'delete') {
            $stmt = $conn->prepare("DELETE FROM patients WHERE id=? AND woreda=?");
            $stmt->bind_param("is", $_POST['id'], $user_woreda);
            $stmt->execute();
            $stmt->close();
        }
    }
    header('Location: wereda_patients.php');
    exit();
}

// Get patients with pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$kebele_filter = isset($_GET['kebele']) ? $_GET['kebele'] : '';

$query = "SELECT * FROM patients WHERE woreda = ? AND status = 'active'";
$params = [$user_woreda];
$types = "s";

if (!empty($search)) {
    $query .= " AND (first_name LIKE ? OR last_name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $types .= "ssss";
}

if (!empty($kebele_filter)) {
    $query .= " AND kebele = ?";
    $params[] = $kebele_filter;
    $types .= "s";
}

$query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$patients = $stmt->get_result();

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM patients WHERE woreda = ? AND status = 'active'";
$count_params = [$user_woreda];
$count_types = "s";

if (!empty($search)) {
    $count_query .= " AND (first_name LIKE ? OR last_name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $count_params = array_merge($count_params, [$search_param, $search_param, $search_param, $search_param]);
    $count_types .= "ssss";
}

if (!empty($kebele_filter)) {
    $count_query .= " AND kebele = ?";
    $count_params[] = $kebele_filter;
    $count_types .= "s";
}

$stmt = $conn->prepare($count_query);
$stmt->bind_param($count_types, ...$count_params);
$stmt->execute();
$total_patients = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_patients / $per_page);

// Get kebeles for filter
$kebeles = $conn->query("SELECT DISTINCT kebele FROM patients WHERE woreda = '$user_woreda' ORDER BY kebele");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Wereda Patients Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/styleho.css">
</head>

<body>
    <div class="admin-container">
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>

        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <button class="mobile-menu-btn" id="mobileMenuBtn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">Patients Management - <?php echo htmlspecialchars($user_woreda); ?></h1>
                </div>

                <div class="header-actions">
                    <button class="btn-primary" onclick="openModal('addPatientModal')">
                        <i class="fas fa-plus"></i> Add Patient
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <!-- Filters -->
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="filters-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <input type="text" name="search" placeholder="Search patients..."
                                        value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="form-group">
                                    <select name="kebele">
                                        <option value="">All Kebeles</option>
                                        <?php while ($kebele = $kebeles->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($kebele['kebele']); ?>" <?php echo $kebele_filter == $kebele['kebele'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($kebele['kebele']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn-secondary">Filter</button>
                                    <a href="wereda_patients.php" class="btn-secondary">Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Patients Table -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Patients (<?php echo $total_patients; ?>)</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Kebele</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($patient = $patients->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $patient['id']; ?></td>
                                            <td><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($patient['kebele']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($patient['created_at'])); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="action-btn view"
                                                        onclick="viewPatient(<?php echo $patient['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="action-btn edit"
                                                        onclick="editPatient(<?php echo $patient['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="action-btn delete"
                                                        onclick="deletePatient(<?php echo $patient['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&kebele=<?php echo urlencode($kebele_filter); ?>"
                                        class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Patient Modal -->
    <div id="addPatientModal" class="modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>Add New Patient</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addPatientForm" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth *</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" required>
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender *</label>
                            <select id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="2"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="blood_type">Blood Type</label>
                            <select id="blood_type" name="blood_type">
                                <option value="">Select Blood Type</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="zone">Zone *</label>
                            <input type="text" id="zone" name="zone" required>
                        </div>
                        <div class="form-group">
                            <label for="kebele">Kebele *</label>
                            <input type="text" id="kebele" name="kebele" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="medical_history">Medical History</label>
                        <textarea id="medical_history" name="medical_history" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="emergency_contact">Emergency Contact</label>
                        <textarea id="emergency_contact" name="emergency_contact" rows="2"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary cancel-btn">Cancel</button>
                        <button type="submit" class="btn-primary">Add Patient</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Patient Modal -->
    <div id="editPatientModal" class="modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>Edit Patient</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editPatientForm" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    <!-- Same form fields as add modal -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_first_name">First Name *</label>
                            <input type="text" id="edit_first_name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_last_name">Last Name *</label>
                            <input type="text" id="edit_last_name" name="last_name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_date_of_birth">Date of Birth *</label>
                            <input type="date" id="edit_date_of_birth" name="date_of_birth" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_gender">Gender *</label>
                            <select id="edit_gender" name="gender" required>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_phone">Phone</label>
                            <input type="tel" id="edit_phone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="edit_email">Email</label>
                            <input type="email" id="edit_email" name="email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_address">Address</label>
                        <textarea id="edit_address" name="address" rows="2"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_blood_type">Blood Type</label>
                            <select id="edit_blood_type" name="blood_type">
                                <option value="">Select Blood Type</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_zone">Zone *</label>
                            <input type="text" id="edit_zone" name="zone" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_kebele">Kebele *</label>
                            <input type="text" id="edit_kebele" name="kebele" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_medical_history">Medical History</label>
                        <textarea id="edit_medical_history" name="medical_history" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_emergency_contact">Emergency Contact</label>
                        <textarea id="edit_emergency_contact" name="emergency_contact" rows="2"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary cancel-btn">Cancel</button>
                        <button type="submit" class="btn-primary">Update Patient</button>
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

        function viewPatient(id) {
            // Implement view patient details
            alert('View patient details for ID: ' + id);
        }

        function editPatient(id) {
            // Fetch patient data and populate edit form
            fetch('get_patient.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_first_name').value = data.first_name;
                    document.getElementById('edit_last_name').value = data.last_name;
                    document.getElementById('edit_date_of_birth').value = data.date_of_birth;
                    document.getElementById('edit_gender').value = data.gender;
                    document.getElementById('edit_phone').value = data.phone;
                    document.getElementById('edit_email').value = data.email;
                    document.getElementById('edit_address').value = data.address;
                    document.getElementById('edit_blood_type').value = data.blood_type;
                    document.getElementById('edit_zone').value = data.zone;
                    document.getElementById('edit_kebele').value = data.kebele;
                    document.getElementById('edit_medical_history').value = data.medical_history;
                    document.getElementById('edit_emergency_contact').value = data.emergency_contact;
                    openModal('editPatientModal');
                });
        }

        function deletePatient(id) {
            if (confirm('Are you sure you want to delete this patient?')) {
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