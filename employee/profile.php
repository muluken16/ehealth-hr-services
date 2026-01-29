<?php
session_start();
if (!isset($_SESSION['employee_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: login.php');
    exit;
}
require_once '../db.php';
$conn = getDBConnection();
$emp_id = $_SESSION['employee_id'];

// Get employee full details
$stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | HealthFirst</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #1a4a5f 0%, #2a6e8c 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 30px;
        }
        
        .profile-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 700;
            border: 4px solid rgba(255,255,255,0.3);
        }
        
        .profile-info h1 {
            margin: 0 0 10px 0;
            font-size: 2rem;
        }
        
        .profile-meta {
            display: flex;
            gap: 20px;
            font-size: 0.95rem;
            opacity: 0.9;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .info-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-left: 4px solid #1a4a5f;
        }
        
        .info-card h3 {
            margin: 0 0 20px 0;
            color: #1a4a5f;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .info-value {
            color: #2d3748;
            font-weight: 600;
            text-align: right;
        }
        
        .edit-btn {
            background: #1a4a5f;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .edit-btn:hover {
            background: #2a6e8c;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26,74,95,0.2);
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
                <a href="profile.php" class="active"><i class="fas fa-user"></i> My Profile</a>
                <a href="leave_request.php"><i class="fas fa-calendar-plus"></i> Request Leave</a>
                <a href="leave_history.php"><i class="fas fa-history"></i> My Leave History</a>
                <a href="payslips.php"><i class="fas fa-money-check-alt"></i> Payslips</a>
                <a href="attendance.php"><i class="fas fa-clock"></i> My Attendance</a>
                <a href="documents.php"><i class="fas fa-folder-open"></i> Documents</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php $page_title = "My Profile"; include 'navbar.php'; ?>

            <div class="content">
                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="profile-avatar-large">
                        <?php echo strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1)); ?>
                    </div>
                    <div class="profile-info">
                        <h1><?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?></h1>
                        <div class="profile-meta">
                            <span><i class="fas fa-id-badge"></i> <?php echo $employee['employee_id']; ?></span>
                            <span><i class="fas fa-briefcase"></i> <?php echo $employee['position'] ?? 'N/A'; ?></span>
                            <span><i class="fas fa-building"></i> <?php echo $employee['working_kebele'] ?? 'N/A'; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Information Grid -->
                <div class="info-grid">
                    <!-- Personal Information -->
                    <div class="info-card">
                        <h3><i class="fas fa-user-circle"></i> Personal Information</h3>
                        <div class="info-row">
                            <span class="info-label">Full Name</span>
                            <span class="info-value"><?php echo $employee['first_name'] . ' ' . ($employee['middle_name'] ?? '') . ' ' . $employee['last_name']; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Date of Birth</span>
                            <span class="info-value"><?php echo date('d M Y', strtotime($employee['date_of_birth'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Gender</span>
                            <span class="info-value"><?php echo ucfirst($employee['gender']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Marital Status</span>
                            <span class="info-value"><?php echo ucfirst($employee['marital_status'] ?? 'N/A'); ?></span>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="info-card" style="border-left-color: #e67e22;">
                        <h3><i class="fas fa-phone"></i> Contact Information</h3>
                        <div class="info-row">
                            <span class="info-label">Email</span>
                            <span class="info-value"><?php echo $employee['email'] ?? 'N/A'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Phone</span>
                            <span class="info-value"><?php echo $employee['phone_number'] ?? 'N/A'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Emergency Contact</span>
                            <span class="info-value"><?php echo $employee['emergency_contact'] ?? 'N/A'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Address</span>
                            <span class="info-value"><?php echo $employee['address'] ?? 'N/A'; ?></span>
                        </div>
                    </div>

                    <!-- Employment Information -->
                    <div class="info-card" style="border-left-color: #3498db;">
                        <h3><i class="fas fa-briefcase"></i> Employment Details</h3>
                        <div class="info-row">
                            <span class="info-label">Position</span>
                            <span class="info-value"><?php echo $employee['position'] ?? 'N/A'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Department</span>
                            <span class="info-value"><?php echo ucfirst($employee['department_assigned'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Join Date</span>
                            <span class="info-value"><?php echo isset($employee['join_date']) ? date('d M Y', strtotime($employee['join_date'])) : 'N/A'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Working Location</span>
                            <span class="info-value"><?php echo $employee['working_kebele'] ?? 'N/A'; ?></span>
                        </div>
                    </div>

                    <!-- Education Information -->
                    <div class="info-card" style="border-left-color: #9b59b6;">
                        <h3><i class="fas fa-graduation-cap"></i> Educational Background</h3>
                        <div class="info-row">
                            <span class="info-label">Education Level</span>
                            <span class="info-value"><?php echo ucfirst($employee['education_level'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Field of Study</span>
                            <span class="info-value"><?php echo $employee['department'] ?? 'N/A'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">University</span>
                            <span class="info-value"><?php echo $employee['university'] ?? 'N/A'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Language</span>
                            <span class="info-value"><?php echo $employee['language'] ?? 'N/A'; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div style="text-align: center; margin-top: 30px;">
                    <button class="edit-btn" onclick="alert('Profile editing feature coming soon!')">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
