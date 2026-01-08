<?php
session_start();
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
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <?php 
                $page_title = "HR Settings";
                include 'navbar.php'; 
            ?>

            <!-- Content -->
            <div class="content">
                <!-- General Settings Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">General Settings</h2>
                    </div>
                    <div class="hr-section-body">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <label for="companyName">Company Name</label>
                                <input type="text" id="companyName" value="HealthFirst Ethiopia">
                            </div>
                            <div class="setting-item">
                                <label for="workingDays">Working Days per Week</label>
                                <select id="workingDays">
                                    <option value="5">5 Days</option>
                                    <option value="6">6 Days</option>
                                    <option value="7">7 Days</option>
                                </select>
                            </div>
                            <div class="setting-item">
                                <label for="workingHours">Working Hours per Day</label>
                                <input type="number" id="workingHours" value="8">
                            </div>
                            <div class="setting-item">
                                <label for="currency">Currency</label>
                                <select id="currency">
                                    <option value="ETB">ETB (Ethiopian Birr)</option>
                                    <option value="USD">USD (US Dollar)</option>
                                    <option value="EUR">EUR (Euro)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leave Settings Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Leave Settings</h2>
                    </div>
                    <div class="hr-section-body">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <label for="annualLeave">Annual Leave Days</label>
                                <input type="number" id="annualLeave" value="30">
                            </div>
                            <div class="setting-item">
                                <label for="sickLeave">Sick Leave Days</label>
                                <input type="number" id="sickLeave" value="10">
                            </div>
                            <div class="setting-item">
                                <label for="maternityLeave">Maternity Leave Days</label>
                                <input type="number" id="maternityLeave" value="90">
                            </div>
                            <div class="setting-item">
                                <label for="paternityLeave">Paternity Leave Days</label>
                                <input type="number" id="paternityLeave" value="5">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payroll Settings Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Payroll Settings</h2>
                    </div>
                    <div class="hr-section-body">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <label for="payrollCycle">Payroll Cycle</label>
                                <select id="payrollCycle">
                                    <option value="monthly">Monthly</option>
                                    <option value="bi-weekly">Bi-weekly</option>
                                    <option value="weekly">Weekly</option>
                                </select>
                            </div>
                            <div class="setting-item">
                                <label for="taxRate">Tax Rate (%)</label>
                                <input type="number" id="taxRate" value="15" step="0.1">
                            </div>
                            <div class="setting-item">
                                <label for="pensionRate">Pension Contribution (%)</label>
                                <input type="number" id="pensionRate" value="7" step="0.1">
                            </div>
                            <div class="setting-item">
                                <label for="overtimeRate">Overtime Rate (per hour)</label>
                                <input type="number" id="overtimeRate" value="1.5" step="0.1">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Notification Settings</h2>
                    </div>
                    <div class="hr-section-body">
                        <div class="settings-list">
                            <div class="setting-toggle">
                                <div class="toggle-info">
                                    <h4>Email Notifications</h4>
                                    <p>Receive email notifications for important HR events</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="setting-toggle">
                                <div class="toggle-info">
                                    <h4>Leave Request Alerts</h4>
                                    <p>Get notified when new leave requests are submitted</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="setting-toggle">
                                <div class="toggle-info">
                                    <h4>Payroll Reminders</h4>
                                    <p>Receive reminders before payroll processing</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="setting-toggle">
                                <div class="toggle-info">
                                    <h4>Training Notifications</h4>
                                    <p>Get notified about upcoming training sessions</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="form-actions" style="margin-top: 20px; text-align: right; padding-bottom: 30px;">
                    <button class="btn-primary" style="background: var(--primary); color: white; padding: 12px 25px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Save settings functionality
        document.querySelector('.btn-primary').addEventListener('click', function() {
            // Collect all settings
            const settings = {
                companyName: document.getElementById('companyName').value,
                workingDays: document.getElementById('workingDays').value,
                workingHours: document.getElementById('workingHours').value,
                currency: document.getElementById('currency').value,
                annualLeave: document.getElementById('annualLeave').value,
                sickLeave: document.getElementById('sickLeave').value,
                maternityLeave: document.getElementById('maternityLeave').value,
                paternityLeave: document.getElementById('paternityLeave').value,
                payrollCycle: document.getElementById('payrollCycle').value,
                taxRate: document.getElementById('taxRate').value,
                pensionRate: document.getElementById('pensionRate').value,
                overtimeRate: document.getElementById('overtimeRate').value
            };

            // Here you would typically send this to the server
            console.log('Saving settings:', settings);
            alert('Settings saved successfully!');
        });
    </script>
    <script src="scripts.js"></script>
</body>
</html>