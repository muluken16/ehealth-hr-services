<?php
session_start();
if (!isset($_SESSION['employee_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: login.php');
    exit;
}
require_once '../db.php';
$conn = getDBConnection();
$emp_id = $_SESSION['employee_id'];

// Get employee name
$stmt = $conn->prepare("SELECT first_name, last_name FROM employees WHERE employee_id = ?");
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

// Mock documents - In production, this would fetch from a documents table
$documents = [
    [
        'id' => 1,
        'title' => 'Employment Contract',
        'category' => 'Contract',
        'uploaded_date' => '2024-01-15',
        'file_type' => 'PDF',
        'file_size' => '2.4 MB',
        'icon_color' => '#ef4444'
    ],
    [
        'id' => 2,
        'title' => 'Job Description',
        'category' => 'HR Documents',
        'uploaded_date' => '2024-01-15',
        'file_type' => 'PDF',
        'file_size' => '1.1 MB',
        'icon_color' => '#3b82f6'
    ],
    [
        'id' => 3,
        'title' => 'Educational Certificates',
        'category' => 'Certificates',
        'uploaded_date' => '2024-01-20',
        'file_type' => 'PDF',
        'file_size' => '5.8 MB',
        'icon_color' => '#8b5cf6'
    ],
    [
        'id' => 4,
        'title' => 'ID Card Copy',
        'category' => 'Identification',
        'uploaded_date' => '2024-01-22',
        'file_type' => 'JPG',
        'file_size' => '1.5 MB',
        'icon_color' => '#f59e0b'
    ],
    [
        'id' => 5,
        'title' => 'Bank Details',
        'category' => 'Financial',
        'uploaded_date' => '2024-02-01',
        'file_type' => 'PDF',
        'file_size' => '0.8 MB',
        'icon_color' => '#10b981'
    ],
    [
        'id' => 6,
        'title' => 'Performance Review 2024',
        'category' => 'Performance',
        'uploaded_date' => '2024-12-15',
        'file_type' => 'PDF',
        'file_size' => '1.9 MB',
        'icon_color' => '#06b6d4'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Documents | HealthFirst</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .document-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border-left: 4px solid #1a4a5f;
            cursor: pointer;
        }
        
        .document-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .document-header {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .document-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            flex-shrink: 0;
        }
        
        .document-info {
            flex: 1;
        }
        
        .document-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }
        
        .document-category {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 600;
        }
        
        .document-meta {
            display: flex;
            justify-content: space-between;
            padding-top: 15px;
            border-top: 1px solid #f1f5f9;
            font-size: 0.85rem;
            color: #94a3b8;
        }
        
        .document-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .doc-action-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .doc-action-btn.view {
            background: #eff6ff;
            color: #1e40af;
        }
        
        .doc-action-btn.view:hover {
            background: #1e40af;
            color: white;
        }
        
        .doc-action-btn.download {
            background: #f0fdf4;
            color: #166534;
        }
        
        .doc-action-btn.download:hover {
            background: #166534;
            color: white;
        }
        
        .upload-section {
            background: linear-gradient(135deg, #1a4a5f 0%, #2a6e8c 100%);
            color: white;
            padding: 35px;
            border-radius: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .upload-btn {
            background: white;
            color: #1a4a5f;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
        }
        
        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .category-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .filter-chip {
            padding: 8px 16px;
            border-radius: 20px;
            background: #f1f5f9;
            color: #475569;
            border: 2px solid transparent;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .filter-chip:hover {
            background: #e2e8f0;
        }
        
        .filter-chip.active {
            background: #1a4a5f;
            color: white;
            border-color: #1a4a5f;
        }
    </style>
</head>
<body>
    <div class="hr-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-heartbeat"></i> <span>HealthFirst</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="leave_request.php"><i class="fas fa-calendar-plus"></i> Request Leave</a>
                <a href="leave_history.php"><i class="fas fa-history"></i> My Leave History</a>
                <a href="payslips.php"><i class="fas fa-money-check-alt"></i> Payslips</a>
                <a href="attendance.php"><i class="fas fa-clock"></i> My Attendance</a>
                <a href="documents.php" class="active"><i class="fas fa-folder-open"></i> Documents</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php $page_title = "My Documents"; include 'navbar.php'; ?>

            <div class="content">
                <!-- Upload Section -->
                <div class="upload-section">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; margin-bottom: 15px;"></i>
                    <h2 style="margin: 0 0 10px 0; font-size: 1.8rem;">Upload Your Documents</h2>
                    <p style="margin: 0; font-size: 1.05rem; opacity: 0.9;">Keep all your important files in one secure place</p>
                    <button class="upload-btn" onclick="alert('Upload feature coming soon!')">
                        <i class="fas fa-plus"></i> Upload New Document
                    </button>
                </div>

                <!-- Category Filter -->
                <div class="category-filter">
                    <div class="filter-chip active" onclick="filterDocuments('all')">All Documents</div>
                    <div class="filter-chip" onclick="filterDocuments('Contract')">Contracts</div>
                    <div class="filter-chip" onclick="filterDocuments('Certificates')">Certificates</div>
                    <div class="filter-chip" onclick="filterDocuments('Identification')">Identification</div>
                    <div class="filter-chip" onclick="filterDocuments('Financial')">Financial</div>
                    <div class="filter-chip" onclick="filterDocuments('Performance')">Performance</div>
                </div>

                <!-- Documents Grid -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">My Documents (<?php echo count($documents); ?>)</h2>
                        <button class="section-action-btn">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                    <div class="hr-section-body">
                        <div class="documents-grid" id="documentsGrid">
                            <?php foreach ($documents as $doc): ?>
                            <div class="document-card" data-category="<?php echo $doc['category']; ?>" style="border-left-color: <?php echo $doc['icon_color']; ?>;">
                                <div class="document-header">
                                    <div class="document-icon" style="background: <?php echo $doc['icon_color']; ?>;">
                                        <i class="fas fa-file-<?php echo strtolower($doc['file_type']) == 'pdf' ? 'pdf' : 'image'; ?>"></i>
                                    </div>
                                    <div class="document-info">
                                        <div class="document-title"><?php echo $doc['title']; ?></div>
                                        <div class="document-category"><?php echo $doc['category']; ?></div>
                                    </div>
                                </div>
                                
                                <div class="document-meta">
                                    <span><i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($doc['uploaded_date'])); ?></span>
                                    <span><i class="fas fa-file"></i> <?php echo $doc['file_size']; ?></span>
                                </div>
                                
                                <div class="document-actions">
                                    <button class="doc-action-btn view" onclick="viewDocument(<?php echo $doc['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="doc-action-btn download" onclick="downloadDocument(<?php echo $doc['id']; ?>)">
                                        <i class="fas fa-download"></i> Download
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function filterDocuments(category) {
            const cards = document.querySelectorAll('.document-card');
            const chips = document.querySelectorAll('.filter-chip');
            
            // Update active chip
            chips.forEach(chip => chip.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filter documents
            cards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        function viewDocument(id) {
            alert('Viewing document ID: ' + id + ' - Feature coming soon!');
            // In production, this would open document in viewer
        }
        
        function downloadDocument(id) {
            alert('Downloading document ID: ' + id + ' - Feature coming soon!');
            // In production, this would download the file
        }
    </script>
</body>
</html>
