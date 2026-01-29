<?php
session_start();
require_once '../db.php';
require_once 'FileManager.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$fileManager = new FileManager($userId);

// Get user info
$stmt = $conn->prepare("SELECT name, role, zone, wereda, kebele FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userInfo = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst File Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .file-manager-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        
        .main-panel {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .sidebar {
            background: #2c3e50;
            color: white;
            min-height: 600px;
            padding: 0;
        }
        
        .sidebar .nav-link {
            color: #bdc3c7;
            padding: 15px 20px;
            border-bottom: 1px solid #34495e;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: #3498db;
            color: white;
            transform: translateX(5px);
        }
        
        .content-area {
            padding: 30px;
        }
        
        .upload-zone {
            border: 3px dashed #3498db;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .upload-zone:hover {
            border-color: #2980b9;
            background: #e3f2fd;
        }
        
        .upload-zone.dragover {
            border-color: #27ae60;
            background: #d5f4e6;
        }
        
        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .file-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            border: 1px solid #e0e0e0;
        }
        
        .file-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .file-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .file-icon.pdf { color: #e74c3c; }
        .file-icon.doc { color: #3498db; }
        .file-icon.image { color: #27ae60; }
        .file-icon.default { color: #95a5a6; }
        
        .file-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-action {
            flex: 1;
            padding: 8px;
            font-size: 0.9rem;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .search-bar {
            position: relative;
            margin-bottom: 20px;
        }
        
        .search-bar input {
            padding-left: 45px;
            border-radius: 25px;
            border: 2px solid #e0e0e0;
        }
        
        .search-bar .fa-search {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #95a5a6;
        }
        
        .breadcrumb-custom {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .progress-container {
            display: none;
            margin-top: 20px;
        }
        
        .file-preview {
            max-width: 100%;
            max-height: 400px;
            border-radius: 10px;
        }
        
        .share-user-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 10px;
        }
        
        .user-item {
            padding: 8px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .user-item:hover {
            background: #f8f9fa;
        }
        
        .user-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="file-manager-container">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="main-panel">
                        <div class="row g-0">
                            <!-- Sidebar -->
                            <div class="col-md-3 sidebar">
                                <div class="p-3 border-bottom">
                                    <h5 class="mb-0"><i class="fas fa-folder-open me-2"></i>File Manager</h5>
                                    <small class="text-muted"><?php echo htmlspecialchars($userInfo['name']); ?></small>
                                </div>
                                
                                <nav class="nav flex-column">
                                    <a class="nav-link active" href="#" data-section="dashboard">
                                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                    </a>
                                    <a class="nav-link" href="#" data-section="employees">
                                        <i class="fas fa-users me-2"></i>Employee Files
                                    </a>
                                    <a class="nav-link" href="#" data-section="patients">
                                        <i class="fas fa-user-injured me-2"></i>Patient Files
                                    </a>
                                    <a class="nav-link" href="#" data-section="payroll">
                                        <i class="fas fa-money-bill-wave me-2"></i>Payroll Files
                                    </a>
                                    <a class="nav-link" href="#" data-section="recruitment">
                                        <i class="fas fa-user-plus me-2"></i>Recruitment
                                    </a>
                                    <a class="nav-link" href="#" data-section="training">
                                        <i class="fas fa-graduation-cap me-2"></i>Training
                                    </a>
                                    <a class="nav-link" href="#" data-section="emergency">
                                        <i class="fas fa-exclamation-triangle me-2"></i>Emergency
                                    </a>
                                    <a class="nav-link" href="#" data-section="quality">
                                        <i class="fas fa-award me-2"></i>Quality Assurance
                                    </a>
                                    <a class="nav-link" href="#" data-section="shared">
                                        <i class="fas fa-share-alt me-2"></i>Shared Files
                                    </a>
                                    <a class="nav-link" href="#" data-section="reports">
                                        <i class="fas fa-chart-bar me-2"></i>Reports
                                    </a>
                                    <a class="nav-link" href="#" data-section="settings">
                                        <i class="fas fa-cog me-2"></i>Settings
                                    </a>
                                </nav>
                            </div>
                            
                            <!-- Main Content -->
                            <div class="col-md-9 content-area">
                                <!-- Dashboard Section -->
                                <div id="dashboard-section" class="content-section">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                            <i class="fas fa-upload me-2"></i>Upload File
                                        </button>
                                    </div>
                                    
                                    <!-- Stats Cards -->
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <div class="stats-card">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h3 id="total-files">0</h3>
                                                        <p class="mb-0">Total Files</p>
                                                    </div>
                                                    <i class="fas fa-file fa-2x opacity-75"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="stats-card">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h3 id="storage-used">0 MB</h3>
                                                        <p class="mb-0">Storage Used</p>
                                                    </div>
                                                    <i class="fas fa-hdd fa-2x opacity-75"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="stats-card">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h3 id="shared-files">0</h3>
                                                        <p class="mb-0">Shared Files</p>
                                                    </div>
                                                    <i class="fas fa-share-alt fa-2x opacity-75"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="stats-card">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h3 id="recent-uploads">0</h3>
                                                        <p class="mb-0">This Week</p>
                                                    </div>
                                                    <i class="fas fa-calendar-week fa-2x opacity-75"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Recent Files -->
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Files</h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="recent-files-list">
                                                <div class="text-center py-4">
                                                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                                                    <p class="mt-2 text-muted">Loading recent files...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Other sections will be loaded dynamically -->
                                <div id="dynamic-content" class="content-section" style="display: none;">
                                    <!-- Dynamic content will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-upload me-2"></i>Upload File</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Entity Type</label>
                                    <select class="form-select" name="entity_type" required>
                                        <option value="">Select Entity Type</option>
                                        <option value="employee">Employee</option>
                                        <option value="patient">Patient</option>
                                        <option value="payroll">Payroll</option>
                                        <option value="recruitment">Recruitment</option>
                                        <option value="training">Training</option>
                                        <option value="emergency">Emergency</option>
                                        <option value="quality">Quality Assurance</option>
                                        <option value="system">System</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Entity ID</label>
                                    <input type="text" class="form-control" name="entity_id" placeholder="e.g., HF-2024-0001" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category" required>
                                <option value="">Select Category</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">File</label>
                            <div class="upload-zone" id="uploadZone">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                <h5>Drag & Drop Files Here</h5>
                                <p class="text-muted">or click to browse</p>
                                <input type="file" class="d-none" name="file" id="fileInput" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Add a description for this file..."></textarea>
                        </div>
                        
                        <div class="progress-container">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="text-muted mt-2">Uploading...</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="uploadBtn">
                        <i class="fas fa-upload me-2"></i>Upload File
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- File Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-eye me-2"></i>File Preview</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="previewContent">
                        <!-- Preview content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="downloadFromPreview">
                        <i class="fas fa-download me-2"></i>Download
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Share Modal -->
    <div class="modal fade" id="shareModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-share-alt me-2"></i>Share File</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="shareForm">
                        <input type="hidden" name="file_id" id="shareFileId">
                        
                        <div class="mb-3">
                            <label class="form-label">Share With</label>
                            <input type="text" class="form-control" id="shareUserSearch" placeholder="Search users...">
                            <div class="share-user-list mt-2" id="shareUserList">
                                <!-- User list will be populated here -->
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Permission</label>
                            <select class="form-select" name="permission_type">
                                <option value="view">View Only</option>
                                <option value="download">View & Download</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Expiry Date (Optional)</label>
                            <input type="datetime-local" class="form-control" name="expiry_date">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Message (Optional)</label>
                            <textarea class="form-control" name="message" rows="3" placeholder="Add a message..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="shareBtn">
                        <i class="fas fa-share-alt me-2"></i>Share File
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/file-manager.js"></script>
</body>
</html>