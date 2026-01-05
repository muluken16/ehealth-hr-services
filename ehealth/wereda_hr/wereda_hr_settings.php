<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'wereda_hr') {
    header('Location: ../index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | HR Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>

        <?php
        $page_title = 'HR Settings';
        include 'navbar.php';
        ?>

        <main class="hr-main">
            <!-- Settings Content -->
            <div class="hr-dashboard">
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">System Settings</h2>
                    </div>
                    <div class="hr-section-body">
                        <div class="settings-grid">
                            <div class="settings-card">
                                <div class="settings-icon">
                                    <i class="fas fa-user-cog"></i>
                                </div>
                                <div class="settings-info">
                                    <h3>Account Settings</h3>
                                    <p>Manage your account preferences</p>
                                </div>
                                <button class="settings-btn">Configure</button>
                            </div>

                            <div class="settings-card">
                                <div class="settings-icon">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div class="settings-info">
                                    <h3>Notifications</h3>
                                    <p>Configure notification preferences</p>
                                </div>
                                <button class="settings-btn">Configure</button>
                            </div>

                            <div class="settings-card">
                                <div class="settings-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="settings-info">
                                    <h3>Security</h3>
                                    <p>Password and security settings</p>
                                </div>
                                <button class="settings-btn">Configure</button>
                            </div>

                            <div class="settings-card">
                                <div class="settings-icon">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div class="settings-info">
                                    <h3>Data Management</h3>
                                    <p>Backup and data settings</p>
                                </div>
                                <button class="settings-btn">Configure</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="scripts.js"></script>
    <script>
        // Settings buttons
        document.querySelectorAll('.settings-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const card = this.closest('.settings-card');
                const settingName = card.querySelector('h3').textContent;
                alert(`${settingName} configuration will be available soon.`);
            });
        });
    </script>
</body>
</html>