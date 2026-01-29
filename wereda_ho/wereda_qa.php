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
            $stmt = $conn->prepare("INSERT INTO quality_assurance (facility_name, assessment_type, score, total_score, findings, recommendations, assessed_by, zone, woreda, kebele, assessment_date, next_assessment_date) VALUES (?, ?, ?, 100, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sddssisssss", $_POST['facility_name'], $_POST['assessment_type'], $_POST['score'], $_POST['findings'], $_POST['recommendations'], $_SESSION['user_id'], $_POST['zone'], $user_woreda, $_POST['kebele'], $_POST['assessment_date'], $_POST['next_assessment_date']);
            $stmt->execute();
            $stmt->close();
        } elseif ($action == 'update') {
            $stmt = $conn->prepare("UPDATE quality_assurance SET facility_name=?, assessment_type=?, score=?, findings=?, recommendations=?, kebele=?, assessment_date=?, next_assessment_date=? WHERE id=? AND woreda=?");
            $stmt->bind_param("ssdsssssss", $_POST['facility_name'], $_POST['assessment_type'], $_POST['score'], $_POST['findings'], $_POST['recommendations'], $_POST['kebele'], $_POST['assessment_date'], $_POST['next_assessment_date'], $_POST['id'], $user_woreda);
            $stmt->execute();
            $stmt->close();
        } elseif ($action == 'delete') {
            $stmt = $conn->prepare("DELETE FROM quality_assurance WHERE id=? AND woreda=?");
            $stmt->bind_param("is", $_POST['id'], $user_woreda);
            $stmt->execute();
            $stmt->close();
        }
    }
    header('Location: wereda_qa.php');
    exit();
}

// Get quality assurance assessments with filters
$assessment_type_filter = isset($_GET['assessment_type']) ? $_GET['assessment_type'] : '';
$kebele_filter = isset($_GET['kebele']) ? $_GET['kebele'] : '';

$query = "SELECT q.*, u.name as assessed_by_name FROM quality_assurance q JOIN users u ON q.assessed_by = u.id WHERE q.woreda = ?";
$params = [$user_woreda];
$types = "s";

if (!empty($assessment_type_filter)) {
    $query .= " AND q.assessment_type = ?";
    $params[] = $assessment_type_filter;
    $types .= "s";
}

if (!empty($kebele_filter)) {
    $query .= " AND q.kebele = ?";
    $params[] = $kebele_filter;
    $types .= "s";
}

$query .= " ORDER BY q.assessment_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$assessments = $stmt->get_result();

// Get QA stats
$stats = $conn->query("
    SELECT
        COUNT(*) as total_assessments,
        AVG(score) as avg_score,
        MIN(score) as min_score,
        MAX(score) as max_score,
        SUM(CASE WHEN score >= 90 THEN 1 ELSE 0 END) as excellent,
        SUM(CASE WHEN score >= 80 AND score < 90 THEN 1 ELSE 0 END) as good,
        SUM(CASE WHEN score < 80 THEN 1 ELSE 0 END) as needs_improvement
    FROM quality_assurance
    WHERE woreda = '$user_woreda'
")->fetch_assoc();

// Get assessment types and kebeles for filters
$assessment_types = $conn->query("SELECT DISTINCT assessment_type FROM quality_assurance WHERE woreda = '$user_woreda' ORDER BY assessment_type");
$kebeles = $conn->query("SELECT DISTINCT kebele FROM quality_assurance WHERE woreda = '$user_woreda' ORDER BY kebele");

// Get upcoming assessments
$upcoming = $conn->query("SELECT * FROM quality_assurance WHERE woreda = '$user_woreda' AND next_assessment_date >= CURDATE() AND next_assessment_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY next_assessment_date LIMIT 5");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Wereda Quality Assurance</title>
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
                    <li class="menu-item">
                        <a href="wereda_emergency.php">
                            <i class="fas fa-ambulance"></i>
                            <span class="menu-text">Emergency</span>
                        </a>
                    </li>
                    <li class="menu-item active">
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
                    <h1 class="page-title">Quality Assurance - <?php echo htmlspecialchars($user_woreda); ?></h1>
                </div>

                <div class="header-actions">
                    <button class="btn-primary" onclick="openModal('addQAModal')">
                        <i class="fas fa-plus"></i> Add Assessment
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_assessments']; ?></h3>
                            <p>Total Assessments</p>
                        </div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo round($stats['avg_score'], 1); ?>%</h3>
                            <p>Average Score</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['excellent']; ?></h3>
                            <p>Excellent (≥90%)</p>
                        </div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['needs_improvement']; ?></h3>
                            <p>Needs Improvement (<80%)</p>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Assessments -->
                <?php if ($upcoming->num_rows > 0): ?>
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Upcoming Assessments</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Facility</th>
                                        <th>Assessment Type</th>
                                        <th>Next Assessment</th>
                                        <th>Days Left</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($assessment = $upcoming->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($assessment['facility_name']); ?></td>
                                        <td><?php echo htmlspecialchars($assessment['assessment_type']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($assessment['next_assessment_date'])); ?></td>
                                        <td>
                                            <?php
                                            $days_left = ceil((strtotime($assessment['next_assessment_date']) - time()) / (60 * 60 * 24));
                                            echo $days_left . ' days';
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="filters-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Assessment Type</label>
                                    <select name="assessment_type">
                                        <option value="">All Types</option>
                                        <?php while ($type = $assessment_types->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($type['assessment_type']); ?>" <?php echo $assessment_type_filter == $type['assessment_type'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($type['assessment_type']); ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Kebele</label>
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
                                    <a href="wereda_qa.php" class="btn-secondary">Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Quality Assurance Assessments Table -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Quality Assurance Assessments</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Facility</th>
                                        <th>Assessment Type</th>
                                        <th>Score</th>
                                        <th>Rating</th>
                                        <th>Assessed By</th>
                                        <th>Assessment Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($assessment = $assessments->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($assessment['facility_name']); ?></td>
                                        <td><?php echo htmlspecialchars($assessment['assessment_type']); ?></td>
                                        <td><?php echo $assessment['score']; ?>%</td>
                                        <td>
                                            <span class="status-badge <?php
                                                echo $assessment['score'] >= 90 ? 'success' :
                                                     ($assessment['score'] >= 80 ? '' :
                                                     ($assessment['score'] >= 70 ? 'warning' : 'danger'));
                                            ?>">
                                                <?php
                                                echo $assessment['score'] >= 90 ? 'Excellent' :
                                                     ($assessment['score'] >= 80 ? 'Good' :
                                                     ($assessment['score'] >= 70 ? 'Fair' : 'Poor'));
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($assessment['assessed_by_name']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($assessment['assessment_date'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" onclick="viewAssessment(<?php echo $assessment['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="action-btn edit" onclick="editAssessment(<?php echo $assessment['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="action-btn delete" onclick="deleteAssessment(<?php echo $assessment['id']; ?>)">
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

    <!-- Add QA Assessment Modal -->
    <div id="addQAModal" class="modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>Add Quality Assessment</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addQAForm" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="facility_name">Facility Name *</label>
                        <input type="text" id="facility_name" name="facility_name" required>
                    </div>
                    <div class="form-group">
                        <label for="assessment_type">Assessment Type *</label>
                        <select id="assessment_type" name="assessment_type" required>
                            <option value="">Select Assessment Type</option>
                            <option value="Facility Assessment">Facility Assessment</option>
                            <option value="Quality Audit">Quality Audit</option>
                            <option value="Patient Care Review">Patient Care Review</option>
                            <option value="Staff Performance">Staff Performance</option>
                            <option value="Equipment Check">Equipment Check</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="score">Score (0-100) *</label>
                            <input type="number" id="score" name="score" min="0" max="100" required>
                        </div>
                        <div class="form-group">
                            <label for="assessment_date">Assessment Date *</label>
                            <input type="date" id="assessment_date" name="assessment_date" required>
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
                    <div class="form-group">
                        <label for="next_assessment_date">Next Assessment Date</label>
                        <input type="date" id="next_assessment_date" name="next_assessment_date">
                    </div>
                    <div class="form-group">
                        <label for="findings">Findings *</label>
                        <textarea id="findings" name="findings" rows="3" required placeholder="Key findings from the assessment..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="recommendations">Recommendations *</label>
                        <textarea id="recommendations" name="recommendations" rows="3" required placeholder="Recommendations for improvement..."></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary cancel-btn">Cancel</button>
                        <button type="submit" class="btn-primary">Add Assessment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit QA Assessment Modal -->
    <div id="editQAModal" class="modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>Edit Quality Assessment</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editQAForm" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label for="edit_facility_name">Facility Name *</label>
                        <input type="text" id="edit_facility_name" name="facility_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_assessment_type">Assessment Type *</label>
                        <select id="edit_assessment_type" name="assessment_type" required>
                            <option value="Facility Assessment">Facility Assessment</option>
                            <option value="Quality Audit">Quality Audit</option>
                            <option value="Patient Care Review">Patient Care Review</option>
                            <option value="Staff Performance">Staff Performance</option>
                            <option value="Equipment Check">Equipment Check</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_score">Score (0-100) *</label>
                            <input type="number" id="edit_score" name="score" min="0" max="100" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_assessment_date">Assessment Date *</label>
                            <input type="date" id="edit_assessment_date" name="assessment_date" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_kebele">Kebele *</label>
                        <input type="text" id="edit_kebele" name="kebele" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_next_assessment_date">Next Assessment Date</label>
                        <input type="date" id="edit_next_assessment_date" name="next_assessment_date">
                    </div>
                    <div class="form-group">
                        <label for="edit_findings">Findings *</label>
                        <textarea id="edit_findings" name="findings" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_recommendations">Recommendations *</label>
                        <textarea id="edit_recommendations" name="recommendations" rows="3" required></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary cancel-btn">Cancel</button>
                        <button type="submit" class="btn-primary">Update Assessment</button>
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

        function viewAssessment(id) {
            // Implement view assessment details
            alert('View assessment details for ID: ' + id);
        }

        function editAssessment(id) {
            // Fetch assessment data and populate edit form
            fetch('get_qa.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_facility_name').value = data.facility_name;
                    document.getElementById('edit_assessment_type').value = data.assessment_type;
                    document.getElementById('edit_score').value = data.score;
                    document.getElementById('edit_assessment_date').value = data.assessment_date;
                    document.getElementById('edit_kebele').value = data.kebele;
                    document.getElementById('edit_next_assessment_date').value = data.next_assessment_date;
                    document.getElementById('edit_findings').value = data.findings;
                    document.getElementById('edit_recommendations').value = data.recommendations;
                    openModal('editQAModal');
                });
        }

        function deleteAssessment(id) {
            if (confirm('Are you sure you want to delete this quality assessment?')) {
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

        // Set default assessment date
        document.getElementById('assessment_date').value = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>