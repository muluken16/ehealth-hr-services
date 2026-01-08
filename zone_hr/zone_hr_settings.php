<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'zone_hr') {
    header('Location: ../index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Zone HR Settings</title>
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
            <div class="hr-dashboard">
                <div class="settings-tabs">
                    <button class="tab-btn active" onclick="showTab('general')">General</button>
                    <button class="tab-btn" onclick="showTab('payroll')">Payroll</button>
                    <button class="tab-btn" onclick="showTab('leave')">Leave</button>
                    <button class="tab-btn" onclick="showTab('notifications')">Notifications</button>
                    <button class="tab-btn" onclick="showTab('security')">Security</button>
                </div>

                <div class="settings-content">
                    <!-- General Settings -->
                    <div id="general-tab" class="settings-tab active">
                        <h3>General Settings</h3>
                        <form class="settings-form">
                            <div class="form-group">
                                <label for="companyName">Company Name</label>
                                <input type="text" id="companyName" value="HealthFirst Ethiopia">
                            </div>
                            <div class="form-group">
                                <label for="hrEmail">HR Email</label>
                                <input type="email" id="hrEmail" value="hr@healthfirst.et">
                            </div>
                            <div class="form-group">
                                <label for="hrPhone">HR Phone</label>
                                <input type="tel" id="hrPhone" value="+251-911-123456">
                            </div>
                            <div class="form-group">
                                <label for="workingDays">Working Days per Week</label>
                                <select id="workingDays">
                                    <option value="5">5 Days</option>
                                    <option value="6">6 Days</option>
                                    <option value="7">7 Days</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="timezone">Timezone</label>
                                <select id="timezone">
                                    <option value="Africa/Addis_Ababa">East Africa Time (EAT)</option>
                                    <option value="UTC">UTC</option>
                                </select>
                            </div>
                            <button type="submit" class="save-btn">Save Changes</button>
                        </form>
                    </div>

                    <!-- Payroll Settings -->
                    <div id="payroll-tab" class="settings-tab">
                        <h3>Payroll Settings</h3>
                        <form class="settings-form">
                            <div class="form-group">
                                <label for="payrollCycle">Payroll Cycle</label>
                                <select id="payrollCycle">
                                    <option value="monthly">Monthly</option>
                                    <option value="bi-weekly">Bi-weekly</option>
                                    <option value="weekly">Weekly</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="currency">Currency</label>
                                <select id="currency">
                                    <option value="ETB">Ethiopian Birr (ETB)</option>
                                    <option value="USD">US Dollar (USD)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="taxRate">Default Tax Rate (%)</label>
                                <input type="number" id="taxRate" step="0.01" value="15.00">
                            </div>
                            <div class="form-group">
                                <label for="overtimeRate">Overtime Rate (per hour)</label>
                                <input type="number" id="overtimeRate" step="0.01" value="1.5">
                            </div>
                            <button type="submit" class="save-btn">Save Changes</button>
                        </form>
                    </div>

                    <!-- Leave Settings -->
                    <div id="leave-tab" class="settings-tab">
                        <h3>Leave Settings</h3>
                        <form class="settings-form">
                            <div class="form-group">
                                <label for="annualLeaveDays">Annual Leave Days</label>
                                <input type="number" id="annualLeaveDays" value="25">
                            </div>
                            <div class="form-group">
                                <label for="sickLeaveDays">Sick Leave Days</label>
                                <input type="number" id="sickLeaveDays" value="10">
                            </div>
                            <div class="form-group">
                                <label for="maternityLeaveDays">Maternity Leave Days</label>
                                <input type="number" id="maternityLeaveDays" value="90">
                            </div>
                            <div class="form-group">
                                <label for="paternityLeaveDays">Paternity Leave Days</label>
                                <input type="number" id="paternityLeaveDays" value="5">
                            </div>
                            <div class="form-group">
                                <label for="leaveApprovalRequired">Leave Approval Required</label>
                                <select id="leaveApprovalRequired">
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                            <button type="submit" class="save-btn">Save Changes</button>
                        </form>
                    </div>

                    <!-- Notifications Settings -->
                    <div id="notifications-tab" class="settings-tab">
                        <h3>Notification Settings</h3>
                        <form class="settings-form">
                            <div class="setting-item">
                                <label class="toggle-label">
                                    <input type="checkbox" id="emailNotifications" checked>
                                    <span class="toggle-slider"></span>
                                    Email Notifications
                                </label>
                            </div>
                            <div class="setting-item">
                                <label class="toggle-label">
                                    <input type="checkbox" id="leaveNotifications" checked>
                                    <span class="toggle-slider"></span>
                                    Leave Request Notifications
                                </label>
                            </div>
                            <div class="setting-item">
                                <label class="toggle-label">
                                    <input type="checkbox" id="payrollNotifications" checked>
                                    <span class="toggle-slider"></span>
                                    Payroll Processing Notifications
                                </label>
                            </div>
                            <div class="setting-item">
                                <label class="toggle-label">
                                    <input type="checkbox" id="recruitmentNotifications">
                                    <span class="toggle-slider"></span>
                                    Recruitment Notifications
                                </label>
                            </div>
                            <div class="setting-item">
                                <label class="toggle-label">
                                    <input type="checkbox" id="trainingNotifications" checked>
                                    <span class="toggle-slider"></span>
                                    Training Notifications
                                </label>
                            </div>
                            <button type="submit" class="save-btn">Save Changes</button>
                        </form>
                    </div>

                    <!-- Security Settings -->
                    <div id="security-tab" class="settings-tab">
                        <h3>Security Settings</h3>
                        <form class="settings-form">
                            <div class="form-group">
                                <label for="passwordExpiry">Password Expiry (days)</label>
                                <input type="number" id="passwordExpiry" value="90">
                            </div>
                            <div class="form-group">
                                <label for="sessionTimeout">Session Timeout (minutes)</label>
                                <input type="number" id="sessionTimeout" value="30">
                            </div>
                            <div class="setting-item">
                                <label class="toggle-label">
                                    <input type="checkbox" id="twoFactorAuth">
                                    <span class="toggle-slider"></span>
                                    Enable Two-Factor Authentication
                                </label>
                            </div>
                            <div class="setting-item">
                                <label class="toggle-label">
                                    <input type="checkbox" id="loginAlerts" checked>
                                    <span class="toggle-slider"></span>
                                    Login Alerts
                                </label>
                            </div>
                            <button type="submit" class="save-btn">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        .settings-tabs {
            display: flex;
            border-bottom: 1px solid var(--light-gray);
            margin-bottom: 30px;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 15px 25px;
            cursor: pointer;
            font-size: 1rem;
            color: var(--gray);
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-btn:hover {
            color: var(--primary);
        }

        .settings-content {
            max-width: 800px;
        }

        .settings-tab {
            display: none;
        }

        .settings-tab.active {
            display: block;
        }

        .settings-tab h3 {
            color: var(--dark);
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .settings-form {
            background: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--light-gray);
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .setting-item {
            margin-bottom: 20px;
        }

        .toggle-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-weight: 600;
            color: var(--dark);
        }

        .toggle-label input {
            display: none;
        }

        .toggle-slider {
            position: relative;
            width: 50px;
            height: 24px;
            background: var(--light-gray);
            border-radius: 24px;
            margin-left: auto;
            transition: background 0.3s;
        }

        .toggle-slider:before {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            background: white;
            border-radius: 50%;
            top: 3px;
            left: 3px;
            transition: transform 0.3s;
        }

        .toggle-label input:checked + .toggle-slider {
            background: var(--primary);
        }

        .toggle-label input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }

        .save-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        .save-btn:hover {
            background: var(--primary-dark);
        }
    </style>

    <script src="scripts.js"></script>
    <script>
        // Tab switching
        function showTab(tabName) {
            // Hide all tabs
            const tabs = document.querySelectorAll('.settings-tab');
            tabs.forEach(tab => tab.classList.remove('active'));

            // Remove active class from all buttons
            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => btn.classList.remove('active'));

            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');

            // Add active class to clicked button
            event.target.classList.add('active');
        }

        // Save settings
        document.querySelectorAll('.settings-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Collect form data
                const formData = new FormData(this);
                const settings = {};

                for (let [key, value] of formData.entries()) {
                    settings[key] = value;
                }

                // Add checkbox values
                const checkboxes = this.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach(checkbox => {
                    settings[checkbox.id] = checkbox.checked;
                });

                // Here you would typically send the settings to the server
                console.log('Saving settings:', settings);

                // Show success message
                alert('Settings saved successfully!');

                // In a real implementation, you would make an AJAX call to save the settings
                // fetch('save_settings.php', {
                //     method: 'POST',
                //     headers: {
                //         'Content-Type': 'application/json',
                //     },
                //     body: JSON.stringify(settings)
                // })
                // .then(response => response.json())
                // .then(data => {
                //     if (data.success) {
                //         alert('Settings saved successfully!');
                //     } else {
                //         alert('Failed to save settings: ' + data.message);
                //     }
                // });
            });
        });
    </script>
</body>
</html>