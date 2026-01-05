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
            $stmt = $conn->prepare("INSERT INTO inventory (item_name, category, quantity, unit, expiry_date, supplier, min_stock_level, zone, woreda, kebele) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisssisss", $_POST['item_name'], $_POST['category'], $_POST['quantity'], $_POST['unit'], $_POST['expiry_date'], $_POST['supplier'], $_POST['min_stock_level'], $user_zone, $_POST['woreda'], $_POST['kebele']);
            $stmt->execute();
            $stmt->close();
        } elseif ($action == 'update') {
            $stmt = $conn->prepare("UPDATE inventory SET item_name=?, category=?, quantity=?, unit=?, expiry_date=?, supplier=?, min_stock_level=?, woreda=?, kebele=? WHERE id=? AND zone=?");
            $stmt->bind_param("ssisssissss", $_POST['item_name'], $_POST['category'], $_POST['quantity'], $_POST['unit'], $_POST['expiry_date'], $_POST['supplier'], $_POST['min_stock_level'], $_POST['woreda'], $_POST['kebele'], $_POST['id'], $user_zone);
            $stmt->execute();
            $stmt->close();
        } elseif ($action == 'delete') {
            $stmt = $conn->prepare("DELETE FROM inventory WHERE id=? AND zone=?");
            $stmt->bind_param("is", $_POST['id'], $user_zone);
            $stmt->execute();
            $stmt->close();
        }
    }
    header('Location: zone_inventory.php');
    exit();
}

// Get inventory with filters
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$woreda_filter = isset($_GET['woreda']) ? $_GET['woreda'] : '';
$low_stock = isset($_GET['low_stock']) ? true : false;

$query = "SELECT * FROM inventory WHERE zone = ?";
$params = [$user_zone];
$types = "s";

if (!empty($category_filter)) {
    $query .= " AND category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

if (!empty($woreda_filter)) {
    $query .= " AND woreda = ?";
    $params[] = $woreda_filter;
    $types .= "s";
}

if ($low_stock) {
    $query .= " AND quantity <= min_stock_level";
}

$query .= " ORDER BY item_name ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$inventory = $stmt->get_result();

// Get inventory stats
$stats = $conn->query("
    SELECT
        COUNT(*) as total_items,
        SUM(quantity) as total_quantity,
        SUM(CASE WHEN quantity <= min_stock_level THEN 1 ELSE 0 END) as low_stock_items,
        COUNT(DISTINCT category) as categories
    FROM inventory
    WHERE zone = '$user_zone'
")->fetch_assoc();

// Get categories and weredas for filters
$categories = $conn->query("SELECT DISTINCT category FROM inventory WHERE zone = '$user_zone' ORDER BY category");
$weredas = $conn->query("SELECT DISTINCT woreda FROM inventory WHERE zone = '$user_zone' ORDER BY woreda");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Zone Inventory Management</title>
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
                    <li class="menu-item">
                        <a href="zone_appointments.php">
                            <i class="fas fa-calendar-check"></i>
                            <span class="menu-text">Appointments</span>
                        </a>
                    </li>
                    <li class="menu-item active">
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
                    <h1 class="page-title">Inventory Management - <?php echo htmlspecialchars($user_zone); ?></h1>
                </div>

                <div class="header-actions">
                    <button class="btn-primary" onclick="openModal('addInventoryModal')">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_items']; ?></h3>
                            <p>Total Items</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-cubes"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($stats['total_quantity']); ?></h3>
                            <p>Total Quantity</p>
                        </div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['low_stock_items']; ?></h3>
                            <p>Low Stock Items</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['categories']; ?></h3>
                            <p>Categories</p>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="filters-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Category</label>
                                    <select name="category">
                                        <option value="">All Categories</option>
                                        <?php while ($category = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($category['category']); ?>" <?php echo $category_filter == $category['category'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['category']); ?>
                                        </option>
                                        <?php endwhile; ?>
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
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="low_stock" value="1" <?php echo $low_stock ? 'checked' : ''; ?>> Show Low Stock Only
                                    </label>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn-secondary">Filter</button>
                                    <a href="zone_inventory.php" class="btn-secondary">Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Inventory Table -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Inventory Items</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        <th>Expiry Date</th>
                                        <th>Wereda</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = $inventory->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['category']); ?></td>
                                        <td><?php echo number_format($item['quantity']); ?></td>
                                        <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                        <td><?php echo $item['expiry_date'] ? date('M j, Y', strtotime($item['expiry_date'])) : 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($item['woreda']); ?></td>
                                        <td>
                                            <?php if ($item['quantity'] <= $item['min_stock_level']): ?>
                                                <span class="status-badge warning">Low Stock</span>
                                            <?php elseif ($item['expiry_date'] && strtotime($item['expiry_date']) < time() + (30 * 24 * 60 * 60)): ?>
                                                <span class="status-badge danger">Expiring Soon</span>
                                            <?php else: ?>
                                                <span class="status-badge success">In Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" onclick="viewItem(<?php echo $item['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="action-btn edit" onclick="editItem(<?php echo $item['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="action-btn delete" onclick="deleteItem(<?php echo $item['id']; ?>)">
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

    <!-- Add Inventory Modal -->
    <div id="addInventoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Inventory Item</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addInventoryForm" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="item_name">Item Name *</label>
                        <input type="text" id="item_name" name="item_name" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Medicine">Medicine</option>
                            <option value="Supplies">Supplies</option>
                            <option value="Equipment">Equipment</option>
                            <option value="Vaccines">Vaccines</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="quantity">Quantity *</label>
                            <input type="number" id="quantity" name="quantity" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="unit">Unit *</label>
                            <select id="unit" name="unit" required>
                                <option value="units">Units</option>
                                <option value="tablets">Tablets</option>
                                <option value="capsules">Capsules</option>
                                <option value="ml">ML</option>
                                <option value="pieces">Pieces</option>
                                <option value="boxes">Boxes</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="date" id="expiry_date" name="expiry_date">
                        </div>
                        <div class="form-group">
                            <label for="min_stock_level">Min Stock Level</label>
                            <input type="number" id="min_stock_level" name="min_stock_level" min="0" value="10">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="supplier">Supplier</label>
                        <input type="text" id="supplier" name="supplier">
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
                    <div class="form-actions">
                        <button type="button" class="btn-secondary cancel-btn">Cancel</button>
                        <button type="submit" class="btn-primary">Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Inventory Modal -->
    <div id="editInventoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Inventory Item</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editInventoryForm" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label for="edit_item_name">Item Name *</label>
                        <input type="text" id="edit_item_name" name="item_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_category">Category *</label>
                        <select id="edit_category" name="category" required>
                            <option value="Medicine">Medicine</option>
                            <option value="Supplies">Supplies</option>
                            <option value="Equipment">Equipment</option>
                            <option value="Vaccines">Vaccines</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_quantity">Quantity *</label>
                            <input type="number" id="edit_quantity" name="quantity" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_unit">Unit *</label>
                            <select id="edit_unit" name="unit" required>
                                <option value="units">Units</option>
                                <option value="tablets">Tablets</option>
                                <option value="capsules">Capsules</option>
                                <option value="ml">ML</option>
                                <option value="pieces">Pieces</option>
                                <option value="boxes">Boxes</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_expiry_date">Expiry Date</label>
                            <input type="date" id="edit_expiry_date" name="expiry_date">
                        </div>
                        <div class="form-group">
                            <label for="edit_min_stock_level">Min Stock Level</label>
                            <input type="number" id="edit_min_stock_level" name="min_stock_level" min="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_supplier">Supplier</label>
                        <input type="text" id="edit_supplier" name="supplier">
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
                    <div class="form-actions">
                        <button type="button" class="btn-secondary cancel-btn">Cancel</button>
                        <button type="submit" class="btn-primary">Update Item</button>
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

        function viewItem(id) {
            // Implement view item details
            alert('View item details for ID: ' + id);
        }

        function editItem(id) {
            // Fetch item data and populate edit form
            fetch('get_inventory.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_item_name').value = data.item_name;
                    document.getElementById('edit_category').value = data.category;
                    document.getElementById('edit_quantity').value = data.quantity;
                    document.getElementById('edit_unit').value = data.unit;
                    document.getElementById('edit_expiry_date').value = data.expiry_date;
                    document.getElementById('edit_min_stock_level').value = data.min_stock_level;
                    document.getElementById('edit_supplier').value = data.supplier;
                    document.getElementById('edit_woreda').value = data.woreda;
                    document.getElementById('edit_kebele').value = data.kebele;
                    openModal('editInventoryModal');
                });
        }

        function deleteItem(id) {
            if (confirm('Are you sure you want to delete this inventory item?')) {
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