<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'wereda_health_officer') {
    header('Location: ../index.html');
    exit();
}

include '../db.php';
$conn = getDBConnection();
$user_woreda = $_SESSION['woreda'] ?? 'Wereda 1';

// Handle report generation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_report'])) {
    $report_type = $_POST['report_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $kebele_filter = $_POST['kebele'] ?? '';

    $report_data = [];
    $report_title = '';

    switch ($report_type) {
        case 'patients':
            $report_title = 'Patient Statistics Report';
            $query = "SELECT COUNT(*) as total_patients,
                             SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as male_patients,
                             SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as female_patients,
                             AVG(TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE())) as avg_age
                      FROM patients
                      WHERE woreda = ? AND created_at BETWEEN ? AND ?";
            $params = [$user_woreda, $start_date . ' 00:00:00', $end_date . ' 23:59:59'];
            if (!empty($kebele_filter)) {
                $query .= " AND kebele = ?";
                $params[] = $kebele_filter;
            }
            $stmt = $conn->prepare($query);
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
            $stmt->execute();
            $report_data = $stmt->get_result()->fetch_assoc();
            break;

        case 'appointments':
            $report_title = 'Appointment Management Report';
            $query = "SELECT COUNT(*) as total_appointments,
                             SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_appointments,
                             SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_appointments,
                             department, COUNT(*) as dept_count
                      FROM appointments
                      WHERE woreda = ? AND created_at BETWEEN ? AND ?
                      GROUP BY department";
            $params = [$user_woreda, $start_date . ' 00:00:00', $end_date . ' 23:59:59'];
            if (!empty($kebele_filter)) {
                $query .= " AND kebele = ?";
                $params[] = $kebele_filter;
            }
            $stmt = $conn->prepare($query);
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
            $stmt->execute();
            $report_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            break;

        case 'inventory':
            $report_title = 'Inventory Status Report';
            $query = "SELECT category, COUNT(*) as item_count, SUM(quantity) as total_quantity,
                             SUM(CASE WHEN quantity <= min_stock_level THEN 1 ELSE 0 END) as low_stock_items
                      FROM inventory
                      WHERE woreda = ?
                      GROUP BY category";
            $params = [$user_woreda];
            if (!empty($kebele_filter)) {
                $query .= " AND kebele = ?";
                $params[] = $kebele_filter;
            }
            $stmt = $conn->prepare($query);
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
            $stmt->execute();
            $report_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            break;

        case 'emergency':
            $report_title = 'Emergency Response Report';
            $query = "SELECT incident_type, COUNT(*) as incident_count,
                             SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_incidents
                      FROM emergency_responses
                      WHERE woreda = ? AND created_at BETWEEN ? AND ?
                      GROUP BY incident_type";
            $params = [$user_woreda, $start_date . ' 00:00:00', $end_date . ' 23:59:59'];
            if (!empty($kebele_filter)) {
                $query .= " AND kebele = ?";
                $params[] = $kebele_filter;
            }
            $stmt = $conn->prepare($query);
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
            $stmt->execute();
            $report_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            break;
    }

    // Save report to database
    $stmt = $conn->prepare("INSERT INTO reports (type, title, content, generated_by, woreda, kebele, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $content = json_encode($report_data);
    $stmt->bind_param("sssissss", $report_type, $report_title, $content, $_SESSION['user_id'], $user_woreda, $kebele_filter, $start_date, $end_date);
    $stmt->execute();
    $report_id = $conn->insert_id;
    $stmt->close();

    // Redirect to view the report
    header('Location: wereda_reports.php?view=' . $report_id);
    exit();
}

// Get existing reports
$reports = $conn->query("SELECT r.*, u.name as generated_by_name FROM reports r JOIN users u ON r.generated_by = u.id WHERE r.woreda = '$user_woreda' ORDER BY r.created_at DESC LIMIT 20");

// Get kebeles for filter
$kebeles = $conn->query("SELECT DISTINCT kebele FROM patients WHERE woreda = '$user_woreda' UNION SELECT DISTINCT kebele FROM appointments WHERE woreda = '$user_woreda' UNION SELECT DISTINCT kebele FROM inventory WHERE woreda = '$user_woreda' ORDER BY kebele");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Wereda Reports</title>
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
                    <h1 class="page-title">Reports - <?php echo htmlspecialchars($user_woreda); ?></h1>
                </div>

                <div class="header-actions">
                    <button class="btn-primary" onclick="openModal('generateReportModal')">
                        <i class="fas fa-plus"></i> Generate Report
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <?php if (isset($_GET['view'])): ?>
                    <!-- View Report -->
                    <?php
                    $conn = getDBConnection();
                    $stmt = $conn->prepare("SELECT * FROM reports WHERE id = ? AND woreda = ?");
                    $stmt->bind_param("is", $_GET['view'], $user_woreda);
                    $stmt->execute();
                    $report = $stmt->get_result()->fetch_assoc();
                    $conn->close();

                    if ($report):
                        $data = json_decode($report['content'], true);
                        ?>
                        <div class="card">
                            <div class="card-header">
                                <h2 class="card-title"><?php echo htmlspecialchars($report['title']); ?></h2>
                                <div class="card-actions">
                                    <button class="btn-secondary" onclick="window.print()">
                                        <i class="fas fa-print"></i> Print
                                    </button>
                                    <button class="btn-secondary" onclick="exportToPDF()">
                                        <i class="fas fa-download"></i> Export PDF
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="report-meta">
                                    <p><strong>Report Type:</strong> <?php echo ucfirst($report['type']); ?></p>
                                    <p><strong>Generated By:</strong>
                                        <?php echo htmlspecialchars($report['generated_by_name'] ?? 'Unknown'); ?></p>
                                    <p><strong>Period:</strong> <?php echo date('M j, Y', strtotime($report['start_date'])); ?>
                                        - <?php echo date('M j, Y', strtotime($report['end_date'])); ?></p>
                                    <p><strong>Generated:</strong>
                                        <?php echo date('M j, Y H:i', strtotime($report['created_at'])); ?></p>
                                    <?php if (!empty($report['kebele'])): ?>
                                        <p><strong>Kebele:</strong> <?php echo htmlspecialchars($report['kebele']); ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="report-content">
                                    <?php if ($report['type'] == 'patients' && is_array($data)): ?>
                                        <h3>Patient Statistics</h3>
                                        <ul>
                                            <li>Total Patients: <?php echo $data['total_patients']; ?></li>
                                            <li>Male Patients: <?php echo $data['male_patients']; ?></li>
                                            <li>Female Patients: <?php echo $data['female_patients']; ?></li>
                                            <li>Average Age: <?php echo round($data['avg_age'], 1); ?> years</li>
                                        </ul>
                                    <?php elseif ($report['type'] == 'appointments' && is_array($data)): ?>
                                        <h3>Appointment Statistics</h3>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Department</th>
                                                    <th>Total Appointments</th>
                                                    <th>Completed</th>
                                                    <th>Cancelled</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($data as $dept): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($dept['department']); ?></td>
                                                        <td><?php echo $dept['dept_count']; ?></td>
                                                        <td><?php echo $dept['completed_appointments'] ?? 0; ?></td>
                                                        <td><?php echo $dept['cancelled_appointments'] ?? 0; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php elseif ($report['type'] == 'inventory' && is_array($data)): ?>
                                        <h3>Inventory Statistics</h3>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Items Count</th>
                                                    <th>Total Quantity</th>
                                                    <th>Low Stock Items</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($data as $cat): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($cat['category']); ?></td>
                                                        <td><?php echo $cat['item_count']; ?></td>
                                                        <td><?php echo $cat['total_quantity']; ?></td>
                                                        <td><?php echo $cat['low_stock_items']; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php elseif ($report['type'] == 'emergency' && is_array($data)): ?>
                                        <h3>Emergency Response Statistics</h3>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Incident Type</th>
                                                    <th>Total Incidents</th>
                                                    <th>Resolved</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($data as $incident): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($incident['incident_type']); ?></td>
                                                        <td><?php echo $incident['incident_count']; ?></td>
                                                        <td><?php echo $incident['resolved_incidents']; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <a href="wereda_reports.php" class="btn-secondary">Back to Reports</a>
                        </div>
                    <?php else: ?>
                        <p>Report not found.</p>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Reports List -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Generated Reports</h2>
                        </div>
                        <div class="card-body">
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Report Title</th>
                                            <th>Type</th>
                                            <th>Generated By</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($report = $reports->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($report['title']); ?></td>
                                                <td><?php echo ucfirst($report['type']); ?></td>
                                                <td><?php echo htmlspecialchars($report['generated_by_name']); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($report['created_at'])); ?></td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="?view=<?php echo $report['id']; ?>" class="action-btn view">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button class="action-btn delete"
                                                            onclick="deleteReport(<?php echo $report['id']; ?>)">
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
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Generate Report Modal -->
    <div id="generateReportModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Generate Report</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="generateReportForm" method="POST">
                    <input type="hidden" name="generate_report" value="1">
                    <div class="form-group">
                        <label for="report_type">Report Type *</label>
                        <select id="report_type" name="report_type" required>
                            <option value="">Select Report Type</option>
                            <option value="patients">Patient Statistics</option>
                            <option value="appointments">Appointment Management</option>
                            <option value="inventory">Inventory Status</option>
                            <option value="emergency">Emergency Response</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start_date">Start Date *</label>
                            <input type="date" id="start_date" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">End Date *</label>
                            <input type="date" id="end_date" name="end_date" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="kebele">Kebele (Optional)</label>
                        <select id="kebele" name="kebele">
                            <option value="">All Kebeles</option>
                            <?php while ($kebele = $kebeles->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($kebele['kebele']); ?>">
                                    <?php echo htmlspecialchars($kebele['kebele']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary cancel-btn">Cancel</button>
                        <button type="submit" class="btn-primary">Generate Report</button>
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

        function deleteReport(id) {
            if (confirm('Are you sure you want to delete this report?')) {
                // Implement delete functionality
                alert('Delete report functionality not implemented yet');
            }
        }

        function exportToPDF() {
            // Implement PDF export
            alert('PDF export functionality not implemented yet');
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

        // Set default dates
        document.getElementById('start_date').value = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
        document.getElementById('end_date').value = new Date().toISOString().split('T')[0];
    </script>
</body>

</html>