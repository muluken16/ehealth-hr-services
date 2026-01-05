<?php
// session_start();
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kebele_health_officer') {
//     header('Location: index.html');
//     exit();
// }

// Default user for demo
$_SESSION['user_name'] = 'Kebele Health Officer';
$_SESSION['role'] = 'kebele_health_officer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Admin Dashboard</title>
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
                <a href="#" class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <span class="logo-text">HealthFirst</span>
                </a>
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>
            
            <nav class="sidebar-menu">
                <ul>
                    <li class="menu-item active">
                        <a href="adminpan.html">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="menu-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="patients.html">
                            <i class="fas fa-user-injured"></i>
                            <span class="menu-text">Patients</span>
                            <span class="menu-badge">24</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="appointments.html">
                            <i class="fas fa-calendar-check"></i>
                            <span class="menu-text">Appointments</span>
                            <span class="menu-badge">12</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="inventory.html">
                            <i class="fas fa-pills"></i>
                            <span class="menu-text">Inventory</span>
                            <span class="menu-badge">3</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="reports.html">
                            <i class="fas fa-chart-bar"></i>
                            <span class="menu-text">Reports</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- User Drawer (Mobile) -->
        <div class="user-drawer" id="userDrawer">
            <div class="user-drawer-header">
                <h3>Account</h3>
                <button class="close-user-drawer" id="closeUserDrawer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="user-drawer-content">
                <div class="user-drawer-profile">
                    <div class="user-drawer-avatar">SJ</div>
                    <h3>Dr. Sarah Johnson</h3>
                    <p>Health Officer</p>
                    <p class="user-role">sarah.j@healthfirst.com</p>
                </div>
                
                <ul class="user-drawer-menu">
                    <li><a href="#"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Account Settings</a></li>
                    <li><a href="#"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li><a href="#"><i class="fas fa-question-circle"></i> Help & Support</a></li>
                    <li><a href="#"><i class="fas fa-moon"></i> Dark Mode</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <button class="mobile-menu-btn" id="mobileMenuBtn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">Dashboard</h1>
                </div>
                
                <div class="header-actions">
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">5</span>
                    </button>
                    
                    <div class="user-profile" id="userProfile">
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?></div>
                        <div class="user-info">
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            <span class="user-role"><?php
                                $role_names = [
                                    'admin' => 'Administrator',
                                    'zone_health_officer' => 'Zone Health Officer',
                                    'zone_hr' => 'Zone HR Officer',
                                    'wereda_health_officer' => 'Wereda Health Officer',
                                    'wereda_hr' => 'Wereda HR Officer',
                                    'kebele_health_officer' => 'Kebele Health Officer',
                                    'kebele_hr' => 'Kebele HR Officer'
                                ];
                                echo $role_names[$_SESSION['role']] ?? $_SESSION['role'];
                            ?></span>
                        </div>
                        <i class="fas fa-chevron-down"></i>
                        
                        <div class="dropdown-menu" id="userDropdown">
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-cog"></i> Account Settings
                            </a>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-question-circle"></i> Help & Support
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="../logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                    
                    <button class="mobile-user-menu-btn" id="mobileUserMenuBtn">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card patients">
                        <div class="stat-icon">
                            <i class="fas fa-user-injured"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="totalPatients">0</h3>
                            <p>Total Patients</p>
                        </div>
                        <div class="stat-change positive">+12%</div>
                    </div>
                    
                    <div class="stat-card appointments">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="totalAppointments">0</h3>
                            <p>Today's Appointments</p>
                        </div>
                        <div class="stat-change positive">+5%</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="activeDoctors">0</h3>
                            <p>Active Doctors</p>
                        </div>
                        <div class="stat-change positive">+2</div>
                    </div>
                    
                    <div class="stat-card emergency">
                        <div class="stat-icon">
                            <i class="fas fa-ambulance"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="emergencyCases">0</h3>
                            <p>Emergency Cases</p>
                        </div>
                        <div class="stat-change negative">-2%</div>
                    </div>
                </div>

                <!-- Charts and Tables Row -->
                <div class="content-row">
                    <!-- Appointments Chart -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Monthly Appointments</h2>
                            <div class="card-actions">
                                <button class="card-action-btn">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="appointmentsChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Recent Activity</h2>
                        </div>
                        <div class="card-body">
                            <ul class="activity-list">
                                <li class="activity-item">
                                    <div class="activity-icon appointment">
                                        <i class="fas fa-calendar-plus"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-text">New appointment scheduled for John Doe</div>
                                        <div class="activity-time">10 minutes ago</div>
                                    </div>
                                </li>
                                <li class="activity-item">
                                    <div class="activity-icon patient">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-text">New patient registered: Emily Johnson</div>
                                        <div class="activity-time">45 minutes ago</div>
                                    </div>
                                </li>
                                <li class="activity-item">
                                    <div class="activity-icon prescription">
                                        <i class="fas fa-file-prescription"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-text">Prescription updated for Robert Smith</div>
                                        <div class="activity-time">2 hours ago</div>
                                    </div>
                                </li>
                                <li class="activity-item">
                                    <div class="activity-icon appointment">
                                        <i class="fas fa-calendar-times"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-text">Appointment cancelled by Lisa Brown</div>
                                        <div class="activity-time">4 hours ago</div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Appointments Table -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Today's Appointments</h2>
                        <div class="card-actions">
                            <button class="card-action-btn">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button class="card-action-btn">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Time</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>John Doe</td>
                                        <td>Dr. Sarah Johnson</td>
                                        <td>09:00 AM</td>
                                        <td>Cardiology</td>
                                        <td><span class="status-badge confirmed">Confirmed</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                                <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Mary Smith</td>
                                        <td>Dr. Michael Chen</td>
                                        <td>10:30 AM</td>
                                        <td>Pediatrics</td>
                                        <td><span class="status-badge pending">Pending</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                                <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Robert Wilson</td>
                                        <td>Dr. James Lee</td>
                                        <td>11:15 AM</td>
                                        <td>Orthopedics</td>
                                        <td><span class="status-badge confirmed">Confirmed</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                                <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Lisa Brown</td>
                                        <td>Dr. Sarah Johnson</td>
                                        <td>02:00 PM</td>
                                        <td>General Medicine</td>
                                        <td><span class="status-badge cancelled">Cancelled</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                                <button class="action-btn delete"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>David Miller</td>
                                        <td>Dr. Patricia Garcia</td>
                                        <td>03:30 PM</td>
                                        <td>Dermatology</td>
                                        <td><span class="status-badge completed">Completed</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view"><i class="fas fa-eye"></i></button>
                                                <button class="action-btn edit"><i class="fas fa-edit"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Quick Actions</h2>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="#" class="quick-action-btn">
                                <i class="fas fa-plus-circle"></i>
                                <span>Add New Patient</span>
                            </a>
                            <a href="#" class="quick-action-btn">
                                <i class="fas fa-calendar-plus"></i>
                                <span>Schedule Appointment</span>
                            </a>
                            <a href="#" class="quick-action-btn">
                                <i class="fas fa-file-prescription"></i>
                                <span>Create Prescription</span>
                            </a>
                            <a href="#" class="quick-action-btn">
                                <i class="fas fa-chart-line"></i>
                                <span>Generate Report</span>
                            </a>
                            <a href="#" class="quick-action-btn">
                                <i class="fas fa-bell"></i>
                                <span>Send Notification</span>
                            </a>
                            <a href="#" class="quick-action-btn">
                                <i class="fas fa-pills"></i>
                                <span>Update Inventory</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modals -->
    <!-- Notifications Modal -->
    <div id="notificationsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Notifications</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="notification-list">
                    <div class="notification-item unread">
                        <div class="notification-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">New Appointment</div>
                            <div class="notification-text">John Doe has scheduled an appointment for tomorrow</div>
                            <div class="notification-time">5 minutes ago</div>
                        </div>
                    </div>
                    <div class="notification-item unread">
                        <div class="notification-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">New Patient Registration</div>
                            <div class="notification-text">Emily Johnson has registered as a new patient</div>
                            <div class="notification-time">1 hour ago</div>
                        </div>
                    </div>
                    <div class="notification-item">
                        <div class="notification-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">Low Inventory Alert</div>
                            <div class="notification-text">Paracetamol stock is running low</div>
                            <div class="notification-time">2 hours ago</div>
                        </div>
                    </div>
                    <div class="notification-item">
                        <div class="notification-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">Appointment Cancelled</div>
                            <div class="notification-text">Lisa Brown cancelled her appointment</div>
                            <div class="notification-time">4 hours ago</div>
                        </div>
                    </div>
                    <div class="notification-item">
                        <div class="notification-icon">
                            <i class="fas fa-file-prescription"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">Prescription Updated</div>
                            <div class="notification-text">Prescription for Robert Smith has been updated</div>
                            <div class="notification-time">6 hours ago</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Details Modal -->
    <div id="patientModal" class="modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>Patient Details</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="patient-info">
                    <div class="patient-avatar">
                        <div class="avatar-circle">JD</div>
                    </div>
                    <div class="patient-details">
                        <h4 id="patientName">John Doe</h4>
                        <p><strong>ID:</strong> <span id="patientId">P001</span></p>
                        <p><strong>Phone:</strong> <span id="patientPhone">(555) 123-4567</span></p>
                        <p><strong>Email:</strong> <span id="patientEmail">john.doe@email.com</span></p>
                        <p><strong>Address:</strong> <span id="patientAddress">123 Main St, City, State 12345</span></p>
                        <p><strong>Date of Birth:</strong> <span id="patientDob">January 15, 1985</span></p>
                        <p><strong>Blood Type:</strong> <span id="patientBloodType">O+</span></p>
                    </div>
                </div>
                <div class="patient-history">
                    <h5>Recent Appointments</h5>
                    <ul id="patientAppointments">
                        <li>Cardiology - Dr. Sarah Johnson - Jan 15, 2024</li>
                        <li>General Checkup - Dr. Michael Chen - Dec 10, 2023</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointment Edit Modal -->
    <div id="appointmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Appointment</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="appointmentForm">
                    <div class="form-group">
                        <label for="apptPatient">Patient</label>
                        <input type="text" id="apptPatient" required>
                    </div>
                    <div class="form-group">
                        <label for="apptDoctor">Doctor</label>
                        <select id="apptDoctor" required>
                            <option value="">Select Doctor</option>
                            <option value="sarah">Dr. Sarah Johnson</option>
                            <option value="michael">Dr. Michael Chen</option>
                            <option value="james">Dr. James Lee</option>
                            <option value="patricia">Dr. Patricia Garcia</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="apptDate">Date</label>
                        <input type="date" id="apptDate" required>
                    </div>
                    <div class="form-group">
                        <label for="apptTime">Time</label>
                        <input type="time" id="apptTime" required>
                    </div>
                    <div class="form-group">
                        <label for="apptDepartment">Department</label>
                        <select id="apptDepartment" required>
                            <option value="">Select Department</option>
                            <option value="cardiology">Cardiology</option>
                            <option value="pediatrics">Pediatrics</option>
                            <option value="orthopedics">Orthopedics</option>
                            <option value="dermatology">Dermatology</option>
                            <option value="general">General Medicine</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="apptNotes">Notes</label>
                        <textarea id="apptNotes" rows="3"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary cancel-btn">Cancel</button>
                        <button type="submit" class="btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add New Patient Modal -->
    <div id="addPatientModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Patient</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addPatientForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="newPatientFirstName">First Name</label>
                            <input type="text" id="newPatientFirstName" required>
                        </div>
                        <div class="form-group">
                            <label for="newPatientLastName">Last Name</label>
                            <input type="text" id="newPatientLastName" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="newPatientEmail">Email</label>
                        <input type="email" id="newPatientEmail" required>
                    </div>
                    <div class="form-group">
                        <label for="newPatientPhone">Phone</label>
                        <input type="tel" id="newPatientPhone" required>
                    </div>
                    <div class="form-group">
                        <label for="newPatientDob">Date of Birth</label>
                        <input type="date" id="newPatientDob" required>
                    </div>
                    <div class="form-group">
                        <label for="newPatientAddress">Address</label>
                        <textarea id="newPatientAddress" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="newPatientBloodType">Blood Type</label>
                        <select id="newPatientBloodType">
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
                    <div class="form-actions">
                        <button type="button" class="btn-secondary cancel-btn">Cancel</button>
                        <button type="submit" class="btn-primary">Add Patient</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Schedule Appointment Modal -->
    <div id="scheduleAppointmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Schedule Appointment</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="scheduleAppointmentForm">
                    <div class="form-group">
                        <label for="schedulePatient">Patient</label>
                        <select id="schedulePatient" required>
                            <option value="">Select Patient</option>
                            <option value="john">John Doe</option>
                            <option value="mary">Mary Smith</option>
                            <option value="robert">Robert Wilson</option>
                            <option value="lisa">Lisa Brown</option>
                            <option value="david">David Miller</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="scheduleDoctor">Doctor</label>
                        <select id="scheduleDoctor" required>
                            <option value="">Select Doctor</option>
                            <option value="sarah">Dr. Sarah Johnson</option>
                            <option value="michael">Dr. Michael Chen</option>
                            <option value="james">Dr. James Lee</option>
                            <option value="patricia">Dr. Patricia Garcia</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="scheduleDate">Date</label>
                        <input type="date" id="scheduleDate" required>
                    </div>
                    <div class="form-group">
                        <label for="scheduleTime">Time</label>
                        <input type="time" id="scheduleTime" required>
                    </div>
                    <div class="form-group">
                        <label for="scheduleDepartment">Department</label>
                        <select id="scheduleDepartment" required>
                            <option value="">Select Department</option>
                            <option value="cardiology">Cardiology</option>
                            <option value="pediatrics">Pediatrics</option>
                            <option value="orthopedics">Orthopedics</option>
                            <option value="dermatology">Dermatology</option>
                            <option value="general">General Medicine</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="scheduleNotes">Notes</label>
                        <textarea id="scheduleNotes" rows="3"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary cancel-btn">Cancel</button>
                        <button type="submit" class="btn-primary">Schedule Appointment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Prescription Modal -->
    <div id="prescriptionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create Prescription</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="prescriptionForm">
                    <div class="form-group">
                        <label for="prescriptionPatient">Patient</label>
                        <select id="prescriptionPatient" required>
                            <option value="">Select Patient</option>
                            <option value="john">John Doe</option>
                            <option value="mary">Mary Smith</option>
                            <option value="robert">Robert Wilson</option>
                            <option value="lisa">Lisa Brown</option>
                            <option value="david">David Miller</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="prescriptionDoctor">Doctor</label>
                        <select id="prescriptionDoctor" required>
                            <option value="">Select Doctor</option>
                            <option value="sarah">Dr. Sarah Johnson</option>
                            <option value="michael">Dr. Michael Chen</option>
                            <option value="james">Dr. James Lee</option>
                            <option value="patricia">Dr. Patricia Garcia</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="medication">Medication</label>
                        <input type="text" id="medication" placeholder="e.g., Paracetamol 500mg" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dosage">Dosage</label>
                            <input type="text" id="dosage" placeholder="e.g., 1 tablet" required>
                        </div>
                        <div class="form-group">
                            <label for="frequency">Frequency</label>
                            <input type="text" id="frequency" placeholder="e.g., 3 times daily" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="duration">Duration</label>
                        <input type="text" id="duration" placeholder="e.g., 7 days" required>
                    </div>
                    <div class="form-group">
                        <label for="instructions">Instructions</label>
                        <textarea id="instructions" rows="3" placeholder="Special instructions..."></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary cancel-btn">Cancel</button>
                        <button type="submit" class="btn-primary">Create Prescription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Generate Report Modal -->
    <div id="reportModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Generate Report</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="reportForm">
                    <div class="form-group">
                        <label for="reportType">Report Type</label>
                        <select id="reportType" required>
                            <option value="">Select Report Type</option>
                            <option value="appointments">Appointments Report</option>
                            <option value="patients">Patients Report</option>
                            <option value="revenue">Revenue Report</option>
                            <option value="inventory">Inventory Report</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reportStartDate">Start Date</label>
                            <input type="date" id="reportStartDate" required>
                        </div>
                        <div class="form-group">
                            <label for="reportEndDate">End Date</label>
                            <input type="date" id="reportEndDate" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reportFormat">Format</label>
                        <select id="reportFormat" required>
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
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

    <!-- Send Notification Modal -->
    <div id="sendNotificationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Send Notification</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="sendNotificationForm">
                    <div class="form-group">
                        <label for="notificationRecipient">Recipient</label>
                        <select id="notificationRecipient" required>
                            <option value="">Select Recipient</option>
                            <option value="all">All Patients</option>
                            <option value="specific">Specific Patient</option>
                            <option value="staff">All Staff</option>
                        </select>
                    </div>
                    <div class="form-group" id="specificPatientGroup" style="display: none;">
                        <label for="specificPatient">Patient</label>
                        <select id="specificPatient">
                            <option value="">Select Patient</option>
                            <option value="john">John Doe</option>
                            <option value="mary">Mary Smith</option>
                            <option value="robert">Robert Wilson</option>
                            <option value="lisa">Lisa Brown</option>
                            <option value="david">David Miller</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="notificationSubject">Subject</label>
                        <input type="text" id="notificationSubject" required>
                    </div>
                    <div class="form-group">
                        <label for="notificationMessage">Message</label>
                        <textarea id="notificationMessage" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="notificationType">Type</label>
                        <select id="notificationType" required>
                            <option value="info">Information</option>
                            <option value="reminder">Reminder</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary cancel-btn">Cancel</button>
                        <button type="submit" class="btn-primary">Send Notification</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Inventory Modal -->
    <div id="inventoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Inventory</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="inventoryForm">
                    <div class="form-group">
                        <label for="inventoryItem">Medication</label>
                        <select id="inventoryItem" required>
                            <option value="">Select Medication</option>
                            <option value="paracetamol">Paracetamol</option>
                            <option value="ibuprofen">Ibuprofen</option>
                            <option value="aspirin">Aspirin</option>
                            <option value="amoxicillin">Amoxicillin</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="currentStock">Current Stock</label>
                            <input type="number" id="currentStock" readonly>
                        </div>
                        <div class="form-group">
                            <label for="newStock">New Stock</label>
                            <input type="number" id="newStock" min="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="supplier">Supplier</label>
                        <input type="text" id="supplier" placeholder="Supplier name">
                    </div>
                    <div class="form-group">
                        <label for="expiryDate">Expiry Date</label>
                        <input type="date" id="expiryDate">
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary cancel-btn">Cancel</button>
                        <button type="submit" class="btn-primary">Update Inventory</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Mobile Footer Navigation -->
    <footer class="mobile-footer">
        <nav>
            <ul class="mobile-nav">
                <li>
                    <a href="adminpan.html" class="mobile-nav-item active">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="patients.html" class="mobile-nav-item">
                        <i class="fas fa-user-injured"></i>
                        <span>Patients</span>
                    </a>
                </li>
                <li>
                    <a href="appointments.html" class="mobile-nav-item">
                        <i class="fas fa-calendar-check"></i>
                        <span>Appointments</span>
                    </a>
                </li>
                <li>
                    <a href="notifications.html" class="mobile-nav-item">
                        <i class="fas fa-bell"></i>
                        <span>Alerts</span>
                    </a>
                </li>
                <li>
                    <a href="#menu" class="mobile-nav-item" id="mobileNavMenuBtn">
                        <i class="fas fa-bars"></i>
                        <span>Menu</span>
                    </a>
                </li>
            </ul>
        </nav>
    </footer>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Load kebele stats on page load
        window.addEventListener('load', function() {
            fetch('get_kebele_stats.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalPatients').textContent = data.total_patients.toLocaleString();
                    document.getElementById('totalAppointments').textContent = data.total_appointments;
                    document.getElementById('activeDoctors').textContent = data.active_doctors;
                    document.getElementById('emergencyCases').textContent = data.emergency_cases;
                })
                .catch(error => console.error('Error loading kebele stats:', error));
        });

        // DOM Elements
        const sidebar = document.getElementById('sidebar');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileOverlay = document.getElementById('mobileOverlay');
        const toggleSidebarBtn = document.getElementById('toggleSidebar');
        const userProfile = document.getElementById('userProfile');
        const userDropdown = document.getElementById('userDropdown');
        const mobileUserMenuBtn = document.getElementById('mobileUserMenuBtn');
        const userDrawer = document.getElementById('userDrawer');
        const closeUserDrawer = document.getElementById('closeUserDrawer');
        const mobileNavMenuBtn = document.getElementById('mobileNavMenuBtn');
        const menuItems = document.querySelectorAll('.menu-item a');
        const mobileNavItems = document.querySelectorAll('.mobile-nav-item');
        
        // Toggle Sidebar on Desktop
        toggleSidebarBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });
        
        // Open/Close Sidebar on Mobile
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.add('mobile-open');
            mobileOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        mobileOverlay.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            userDrawer.classList.remove('open');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        });
        
        // Open User Drawer on Mobile
        mobileUserMenuBtn.addEventListener('click', () => {
            userDrawer.classList.add('open');
            mobileOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        // Open Sidebar from Mobile Footer Menu
        mobileNavMenuBtn.addEventListener('click', (e) => {
            e.preventDefault();
            sidebar.classList.add('mobile-open');
            mobileOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        // Close User Drawer
        closeUserDrawer.addEventListener('click', () => {
            userDrawer.classList.remove('open');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        });
        
        // User Profile Dropdown (Desktop)
        userProfile.addEventListener('click', (e) => {
            if (window.innerWidth > 768) {
                e.stopPropagation();
                userDropdown.classList.toggle('show');
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!userProfile.contains(e.target) && window.innerWidth > 768) {
                userDropdown.classList.remove('show');
            }
        });
        
        // Active Menu Item
        function setActiveMenuItem(clickedItem) {
            // Remove active class from all items
            menuItems.forEach(item => {
                item.parentElement.classList.remove('active');
            });
            
            mobileNavItems.forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active class to clicked item
            clickedItem.parentElement.classList.add('active');
            
            // Find corresponding mobile nav item
            const menuText = clickedItem.querySelector('.menu-text').textContent;
            mobileNavItems.forEach(item => {
                if (item.querySelector('span').textContent === menuText) {
                    item.classList.add('active');
                }
            });
            
            // If on mobile, close sidebar after clicking
            if (window.innerWidth <= 992) {
                sidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        }
        
        // Ensure sidebar navigation works properly
        menuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                // Only handle if it's not already navigating
                if (this.href) {
                    // Let the default navigation happen
                    // Active class will be set by the target page
                }
            });
        });
        
        // Mobile Navigation - only prevent default for menu button
        mobileNavItems.forEach(item => {
            item.addEventListener('click', function(e) {
                if (this === mobileNavMenuBtn) {
                    e.preventDefault();
                    return;
                }

                // For actual navigation items, let default behavior work
                // Just handle active state and closing
                if (this.tagName === 'A' && this.href) {
                    // Remove active class from all items
                    mobileNavItems.forEach(navItem => {
                        navItem.classList.remove('active');
                    });

                    // Add active class to clicked item
                    this.classList.add('active');

                    // Close sidebar on mobile
                    if (window.innerWidth <= 992) {
                        sidebar.classList.remove('mobile-open');
                        mobileOverlay.classList.remove('active');
                        document.body.style.overflow = 'auto';
                    }
                }
            });
        });
        
        // Initialize Chart
        const ctx = document.getElementById('appointmentsChart').getContext('2d');
        
        // Chart data
        const appointmentsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Appointments',
                    data: [120, 150, 180, 200, 220, 240, 260, 250, 230, 210, 240, 280],
                    backgroundColor: 'rgba(76, 181, 174, 0.1)',
                    borderColor: 'rgba(76, 181, 174, 1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'rgba(76, 181, 174, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            stepSize: 50
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                }
            }
        });
        
        // Table row actions
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const action = this.classList.contains('view') ? 'view' :
                               this.classList.contains('edit') ? 'edit' : 'delete';

                const row = this.closest('tr');
                const patientName = row.cells[0].textContent;
                const doctorName = row.cells[1].textContent;
                const time = row.cells[2].textContent;
                const department = row.cells[3].textContent;

                switch(action) {
                    case 'view':
                        // Populate patient modal with data
                        document.getElementById('patientName').textContent = patientName;
                        document.getElementById('patientId').textContent = 'P' + Math.floor(Math.random() * 1000).toString().padStart(3, '0');
                        document.getElementById('patientPhone').textContent = '(555) ' + Math.floor(Math.random() * 900 + 100) + '-' + Math.floor(Math.random() * 9000 + 1000);
                        document.getElementById('patientEmail').textContent = patientName.toLowerCase().replace(' ', '.') + '@email.com';
                        document.getElementById('patientAddress').textContent = Math.floor(Math.random() * 999 + 1) + ' Main St, City, State ' + Math.floor(Math.random() * 90000 + 10000);
                        document.getElementById('patientDob').textContent = 'January ' + Math.floor(Math.random() * 28 + 1) + ', ' + (1980 + Math.floor(Math.random() * 40));
                        document.getElementById('patientBloodType').textContent = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'][Math.floor(Math.random() * 8)];
                        openModal('patientModal');
                        break;
                    case 'edit':
                        // Populate appointment edit modal
                        document.getElementById('apptPatient').value = patientName;
                        document.getElementById('apptDoctor').value = doctorName.toLowerCase().replace('dr. ', '').replace(' ', '');
                        document.getElementById('apptTime').value = time.toLowerCase().replace(' am', ':00').replace(' pm', ':00');
                        document.getElementById('apptDepartment').value = department.toLowerCase();
                        openModal('appointmentModal');
                        break;
                    case 'delete':
                        if (confirm(`Are you sure you want to delete the appointment for ${patientName}?`)) {
                            row.remove();
                            // Update stats
                            updateStats();
                        }
                        break;
                }
            });
        });
        
        // Quick action buttons - redirect to respective pages
        document.querySelectorAll('.quick-action-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const action = this.querySelector('span').textContent;

                switch(action) {
                    case 'Add New Patient':
                        window.location.href = 'patients.html';
                        break;
                    case 'Schedule Appointment':
                        window.location.href = 'appointments.html';
                        break;
                    case 'Create Prescription':
                        window.location.href = 'prescriptions.html';
                        break;
                    case 'Generate Report':
                        window.location.href = 'reports.html';
                        break;
                    case 'Send Notification':
                        // For notifications, we can stay on dashboard and open modal
                        openModal('sendNotificationModal');
                        break;
                    case 'Update Inventory':
                        window.location.href = 'inventory.html';
                        break;
                }
            });
        });
        
        // Notification button
        document.querySelector('.notification-btn').addEventListener('click', function() {
            openModal('notificationsModal');
        });
        
        // User drawer menu items
        document.querySelectorAll('.user-drawer-menu a').forEach(item => {
            item.addEventListener('click', function(e) {
                if (this.querySelector('i').classList.contains('fa-sign-out-alt')) {
                    e.preventDefault();
                    if (confirm('Are you sure you want to logout?')) {
                        window.location.href = '../logout.php';
                    }
                    return;
                }

                e.preventDefault();
                const action = this.textContent.trim();

                switch(action) {
                    case 'My Profile':
                        alert('Opening user profile page...');
                        break;
                    case 'Account Settings':
                        alert('Opening account settings...');
                        break;
                    case 'Notifications':
                        openModal('notificationsModal');
                        break;
                    case 'Help & Support':
                        alert('Opening help & support...');
                        break;
                    case 'Dark Mode':
                        alert('Toggling dark mode...');
                        break;
                }

                // Close drawer on mobile after clicking
                if (window.innerWidth <= 768) {
                    userDrawer.classList.remove('open');
                    mobileOverlay.classList.remove('active');
                    document.body.style.overflow = 'auto';
                }
            });
        });

        // Dropdown menu items
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                if (this.querySelector('i') && this.querySelector('i').classList.contains('fa-sign-out-alt')) {
                    e.preventDefault();
                    if (confirm('Are you sure you want to logout?')) {
                        window.location.href = '../logout.php';
                    }
                    return;
                }

                e.preventDefault();
                const action = this.textContent.trim();

                switch(action) {
                    case 'My Profile':
                        alert('Opening user profile page...');
                        break;
                    case 'Account Settings':
                        alert('Opening account settings...');
                        break;
                    case 'Help & Support':
                        alert('Opening help & support...');
                        break;
                }

                // Close dropdown
                userDropdown.classList.remove('show');
            });
        });

        // Form submissions
        // Add Patient Form
        document.getElementById('addPatientForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const patientData = Object.fromEntries(formData);

            alert(`Patient "${patientData.firstName} ${patientData.lastName}" added successfully!`);
            closeAllModals();
            this.reset();
            // In a real app, send to server and update patient list
        });

        // Schedule Appointment Form
        document.getElementById('scheduleAppointmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const appointmentData = Object.fromEntries(formData);

            alert(`Appointment scheduled for ${appointmentData.patient} with ${appointmentData.doctor} on ${appointmentData.date} at ${appointmentData.time}`);
            closeAllModals();
            this.reset();
            updateStats();
            // In a real app, add to appointments table
        });

        // Prescription Form
        document.getElementById('prescriptionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const prescriptionData = Object.fromEntries(formData);

            alert(`Prescription created for ${prescriptionData.patient}`);
            closeAllModals();
            this.reset();
        });

        // Report Form
        document.getElementById('reportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const reportData = Object.fromEntries(formData);

            alert(`${reportData.type} report generated and downloaded as ${reportData.format.toUpperCase()}`);
            closeAllModals();
            this.reset();
        });

        // Send Notification Form
        document.getElementById('sendNotificationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const notificationData = Object.fromEntries(formData);

            alert(`Notification sent to ${notificationData.recipient}`);
            closeAllModals();
            this.reset();
        });

        // Inventory Form
        document.getElementById('inventoryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const inventoryData = Object.fromEntries(formData);

            alert(`Inventory updated for ${inventoryData.inventoryItem}`);
            closeAllModals();
            this.reset();
        });

        // Appointment Edit Form
        document.getElementById('appointmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const appointmentData = Object.fromEntries(formData);

            alert('Appointment updated successfully!');
            closeAllModals();
            this.reset();
        });

        // Dynamic form handling
        document.getElementById('notificationRecipient').addEventListener('change', function() {
            const specificGroup = document.getElementById('specificPatientGroup');
            if (this.value === 'specific') {
                specificGroup.style.display = 'block';
            } else {
                specificGroup.style.display = 'none';
            }
        });

        // Card action buttons
        document.querySelectorAll('.card-action-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const icon = this.querySelector('i');
                if (icon.classList.contains('fa-ellipsis-h')) {
                    // Show options menu (simplified)
                    alert('Chart options menu would open here');
                } else if (icon.classList.contains('fa-sync-alt')) {
                    // Refresh data
                    alert('Refreshing data...');
                    updateStats();
                } else if (icon.classList.contains('fa-filter')) {
                    // Show filter options
                    alert('Filter options would open here');
                }
            });
        });

        // Update stats function
        function updateStats() {
            // Simulate updating stats
            const appointmentsStat = document.querySelector('.stat-card.appointments h3');
            const currentAppointments = parseInt(appointmentsStat.textContent);
            appointmentsStat.textContent = currentAppointments + Math.floor(Math.random() * 3 - 1); // -1 to +1

            const patientsStat = document.querySelector('.stat-card.patients h3');
            const currentPatients = parseInt(patientsStat.textContent);
            patientsStat.textContent = currentPatients + Math.floor(Math.random() * 5); // 0 to 4
        }

        // Sidebar menu navigation - now links to actual pages
        // Sidebar navigation - let default link behavior work
        // Active class is set by the page itself
        
        // Handle window resize
        function handleResize() {
            // Auto-hide sidebar on mobile when resizing to desktop
            if (window.innerWidth > 992) {
                sidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('active');
                document.body.style.overflow = 'auto';
                
                // Restore desktop sidebar state
                if (sidebar.classList.contains('collapsed')) {
                    sidebar.classList.add('collapsed');
                }
            }
            
            // Show/hide user dropdown based on screen size
            if (window.innerWidth <= 768) {
                userDropdown.style.display = 'none';
            } else {
                userDropdown.style.display = '';
            }
        }
        
        // Initial check
        handleResize();
        
        // Listen for resize
        window.addEventListener('resize', handleResize);
        
        // Close drawers with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                sidebar.classList.remove('mobile-open');
                userDrawer.classList.remove('open');
                mobileOverlay.classList.remove('active');
                document.body.style.overflow = 'auto';
                closeAllModals();
            }
        });

        // Modal Functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        function closeAllModals() {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.style.display = 'none';
            });
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                closeAllModals();
            }
        });

        // Close modal buttons
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                closeAllModals();
            });
        });

        // Cancel buttons
        document.querySelectorAll('.cancel-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                closeAllModals();
            });
        });
    </script>
</body>
</html>