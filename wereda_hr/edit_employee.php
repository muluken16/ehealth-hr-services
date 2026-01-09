<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
// Default user/role for demo if needed
if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'Woreda HR Manager';
    $_SESSION['role'] = 'wereda_hr';
    $_SESSION['woreda'] = 'Woreda 1';
}

// Fallback if set but specific keys missing
if (!isset($_SESSION['woreda']))
    $_SESSION['woreda'] = 'Woreda 1';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Add New Employee</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        :root {
            --primary: #1a4a5f;
            --secondary: #2c7da0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray: #64748b;
            --light: #f8fafc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f1f5f9;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }

        .hr-container {
            display: flex;
            min-height: 100vh;
            background: #f1f5f9;
        }

        .main-content {
            margin-left: var(--sidebar-width, 250px);
            min-height: 100vh;
            background: #f1f5f9;
        }

        .content {
            padding: 20px;
        }


        .add-layout {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 24px;
            align-items: start;
        }


        /* Form Navigation Sidebar */
        .nav-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 90px;
            height: fit-content;
            max-width: 260px;
        }

        .new-employee-badge {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 10px;
            box-shadow: 0 10px 20px rgba(26, 74, 95, 0.2);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .new-employee-badge img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            display: none;
        }

        .new-employee-badge label {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            cursor: pointer;
        }

        .nav-title {
            text-align: center;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--primary);
        }

        .nav-subtitle {
            text-align: center;
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border-radius: 12px;
            color: var(--gray);
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .nav-item:hover {
            background: var(--light);
            color: var(--primary);
        }

        .nav-item.active {
            background: #eff6ff;
            color: var(--primary);
        }

        /* Main Form Area */
        .form-content {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--primary);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }

        .form-group label span.required {
            color: var(--danger);
        }

        .form-control-edit {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background: #f8fafc;
        }

        .form-control-edit:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(26, 74, 95, 0.05);
            background: white;
        }

        textarea.form-control-edit {
            resize: vertical;
            min-height: 80px;
        }

        .conditional-field {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px dashed #cbd5e1;
        }

        .conditional-field.show {
            display: block;
        }

        .action-bar {
            position: sticky;
            bottom: 20px;
            background: white;
            padding: 20px 30px;
            border-radius: 20px;
            box-shadow: 0 -10px 25px rgba(0, 0, 0, 0.05), 0 10px 25px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            z-index: 100;
        }

        .btn-confirm {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 14px 35px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 74, 95, 0.3);
        }

        .btn-cancel {
            background: #f1f5f9;
            color: var(--gray);
            border: none;
            padding: 14px 25px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
        }

        .upload-area {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            background: #f8fafc;
            transition: all 0.2s;
        }

        .upload-area:hover {
            border-color: var(--primary);
            background: #eef2ff;
        }

        /* Responsive adjustments for collapsed sidebar */
        @media (min-width: 993px) {
            .sidebar.collapsed~.main-content {
                margin-left: var(--sidebar-collapsed, 70px);
            }
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 0 !important;
            }
        }

        @media (max-width: 900px) {
            .add-layout {
                grid-template-columns: 1fr;
            }

            .nav-card {
                position: relative;
                top: 0;
                margin-bottom: 20px;
            }
        }

        .main-content {
            overflow-y: auto;
            height: 100vh;
        }
    </style>
</head>

<body>
    <div class="hr-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <?php
        $page_title = "Edit Employee";
        include 'navbar.php';
        ?>

        <div class="content">
            <div class="add-layout">

                <!-- Navbar / Progress -->
                <div class="nav-card">
                    <div class="new-employee-badge">
                        <i class="fas fa-camera" id="cameraIcon"></i>
                        <img id="previewImg">
                        <label for="photoInput"></label>
                    </div>
                    <div style="text-align: center; margin-bottom: 20px;">
                        <small style="color: var(--gray); font-size: 0.8rem;">Click circle to upload photo</small>
                    </div>
                    <div class="nav-title">Edit Employee</div>
                    <div class="nav-subtitle">Update employee record</div>

                    <div class="nav-sections">
                        <div class="nav-item active" data-section="personal"><i class="fas fa-user"></i> Personal</div>
                        <div class="nav-item" data-section="education"><i class="fas fa-graduation-cap"></i> Education
                        </div>
                        <div class="nav-item" data-section="employment"><i class="fas fa-briefcase"></i> Employment
                        </div>
                        <div class="nav-item" data-section="location"><i class="fas fa-map-marker-alt"></i> Contact
                        </div>
                        <div class="nav-item" data-section="financial"><i class="fas fa-university"></i> Finance</div>
                        <div class="nav-item" data-section="warranty"><i class="fas fa-shield-alt"></i> Warranty</div>
                        <div class="nav-item" data-section="documents"><i class="fas fa-folder-open"></i> Documents
                        </div>
                    </div>
                </div>

                <!-- Form Area -->
                <div class="form-content">
                    <form id="editEmployeeForm" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="db_id">
                        <input type="hidden" name="employee_id" id="emp_id_hidden">
                        <!-- Hidden Camera Input Move Inside Form -->
                        <input type="file" name="photo" id="photoInput" accept="image/*" style="display: none;"
                            onchange="previewProfile(this)">


                        <!-- Personal -->
                        <div id="personal">
                            <div class="section-title"><i class="fas fa-user-circle"></i> Personal Information</div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>First Name <span class="required">*</span></label>
                                    <input type="text" name="first_name" class="form-control-edit"
                                        placeholder="e.g. Abebe" required>
                                </div>
                                <div class="form-group">
                                    <label>Middle Name</label>
                                    <input type="text" name="middle_name" class="form-control-edit"
                                        placeholder="e.g. Kebede">
                                </div>
                                <div class="form-group">
                                    <label>Last Name <span class="required">*</span></label>
                                    <input type="text" name="last_name" class="form-control-edit"
                                        placeholder="e.g. Tesfaye" required>
                                </div>
                                <div class="form-group">
                                    <label>Gender <span class="required">*</span></label>
                                    <select name="gender" class="form-control-edit" required>
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Date of Birth <span class="required">*</span></label>
                                    <input type="date" name="date_of_birth" class="form-control-edit" required>
                                </div>
                                <div class="form-group">
                                    <label>Marital Status</label>
                                    <select name="marital_status" class="form-control-edit">
                                        <option value="single">Single</option>
                                        <option value="married">Married</option>
                                        <option value="divorced">Divorced</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Religion</label>
                                    <select name="religion" class="form-control-edit"
                                        onchange="checkOtherReligion(this)">
                                        <option value="">Select Religion</option>
                                        <option value="Orthodox">Orthodox</option>
                                        <option value="Islam">Islam</option>
                                        <option value="Protestant">Protestant</option>
                                        <option value="Catholic">Catholic</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <input type="text" id="otherReligion" name="other_religion"
                                        class="form-control-edit conditional-field" placeholder="Enter religion">
                                </div>
                                <div class="form-group">
                                    <label>Citizenship</label>
                                    <select name="citizenship" class="form-control-edit"
                                        onchange="checkOtherCitizenship(this)">
                                        <option value="Ethiopia" selected>Ethiopia</option>
                                        <option value="United States">United States</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <input type="text" id="otherCitizenship" name="other_citizenship"
                                        class="form-control-edit conditional-field" placeholder="Enter country">
                                </div>
                                <div class="form-group">
                                    <label>Primary Language</label>
                                    <select name="language" class="form-control-edit"
                                        onchange="checkOtherLanguage(this)">
                                        <option value="amharic">Amharic</option>
                                        <option value="oromo">Afaan Oromo</option>
                                        <option value="tigrigna">Tigrigna</option>
                                        <option value="english">English</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <input type="text" id="otherLanguage" name="other_language"
                                        class="form-control-edit conditional-field" placeholder="Enter language">
                                </div>
                            </div>
                        </div>

                        <!-- Education -->
                        <div id="education">
                            <div class="section-title"><i class="fas fa-graduation-cap"></i> Academic Background</div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Highest Level</label>
                                    <select name="education_level" class="form-control-edit">
                                        <option value="">Select Level</option>
                                        <option value="diploma">Diploma</option>
                                        <option value="bachelor">Bachelor's Degree</option>
                                        <option value="master">Master's Degree</option>
                                        <option value="phd">PhD</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Institution</label>
                                    <input type="text" name="university" class="form-control-edit"
                                        placeholder="University/College">
                                </div>
                                <div class="form-group">
                                    <label>Field of Study</label>
                                    <input type="text" name="department" class="form-control-edit"
                                        placeholder="e.g. Computer Science">
                                </div>
                                <div class="form-group">
                                    <label>Secondary School</label>
                                    <input type="text" name="secondary_school" class="form-control-edit"
                                        placeholder="High School Name">
                                </div>
                                <div class="form-group">
                                    <label>Upload Certificate(s)</label>
                                    <div class="upload-area" onclick="document.getElementById('edu_files').click()">
                                        <i class="fas fa-cloud-upload-alt"></i> Click to Add Document(s)
                                        <input type="file" id="edu_files" name="education_files[]" multiple
                                            style="display:none" onchange="handleFileList(this, 'edu_list')">
                                    </div>
                                    <div id="edu_list" style="margin-top: 10px;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Employment -->
                        <div id="employment">
                            <div class="section-title"><i class="fas fa-briefcase"></i> Employment Details</div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Department Assigned <span class="required">*</span></label>
                                    <select name="department_assigned" class="form-control-edit" required>
                                        <option value="">Select Department</option>
                                        <option value="medical">Medical</option>
                                        <option value="admin">Administration</option>
                                        <option value="it">IT/Technical</option>
                                        <option value="support">Support</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Position / Title <span class="required">*</span></label>
                                    <input type="text" name="position" class="form-control-edit" required
                                        placeholder="e.g. Senior Nurse">
                                </div>
                                <div class="form-group">
                                    <label>Join Date</label>
                                    <input type="date" name="join_date" class="form-control-edit"
                                        value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Employment Type</label>
                                    <select name="employment_type" class="form-control-edit">
                                        <option value="full-time">Full Time</option>
                                        <option value="contract">Contract</option>
                                        <option value="part-time">Part Time</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control-edit">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Salary (ETB)</label>
                                    <input type="number" name="salary" class="form-control-edit" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <!-- Assignment Location (Auto-Filled) -->
                        <div id="assignment">
                            <div class="section-title"><i class="fas fa-map-pin"></i> Work Location Assignment</div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Working Woreda (Auto)</label>
                                    <input type="text" name="working_woreda" class="form-control-edit"
                                        value="<?php echo htmlspecialchars($_SESSION['woreda']); ?>" readonly
                                        style="background: #e2e8f0; cursor: not-allowed;">
                                </div>
                                <div class="form-group">
                                    <label>Working Location</label>
                                    <select name="working_kebele" class="form-control-edit">
                                        <option value="Woreda Office">Woreda Office (HQ)</option>
                                        <option value="01">Kebele 01</option>
                                        <option value="02">Kebele 02</option>
                                        <option value="03">Kebele 03</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Contact -->
                        <div id="location">
                            <div class="section-title"><i class="fas fa-map-marked-alt"></i> Contact & Address</div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Phone Number <span class="required">*</span></label>
                                    <input type="tel" name="phone_number" class="form-control-edit" required
                                        placeholder="+251...">
                                </div>
                                <div class="form-group">
                                    <label>Emergency Contact</label>
                                    <input type="text" name="emergency_contact" class="form-control-edit"
                                        placeholder="Name & Phone">
                                </div>
                                <div class="form-group">
                                    <label>Email Address <span class="required">*</span></label>
                                    <input type="email" name="email" class="form-control-edit" required
                                        placeholder="email@example.com">
                                </div>
                                <div class="form-group">
                                    <label>Region</label>
                                    <select id="region" name="region" class="form-control-edit" onchange="loadZones()">
                                        <option value="">Select Region</option>
                                        <option value="Addis Ababa">Addis Ababa</option>
                                        <option value="Afar">Afar</option>
                                        <option value="Amhara">Amhara</option>
                                        <option value="Benishangul-Gumuz">Benishangul-Gumuz</option>
                                        <option value="Dire Dawa">Dire Dawa</option>
                                        <option value="Gambela">Gambela</option>
                                        <option value="Harari">Harari</option>
                                        <option value="Oromia">Oromia</option>
                                        <option value="Sidama">Sidama</option>
                                        <option value="Somali">Somali</option>
                                        <option value="South West Ethiopia">South West Ethiopia</option>
                                        <option value="Southern Nations">Southern Nations (SNNPR)</option>
                                        <option value="Tigray">Tigray</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Zone</label>
                                    <select id="zone" name="zone" class="form-control-edit" onchange="loadWoredas()">
                                        <option value="">Select Zone</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Woreda</label>
                                    <select id="woreda" name="woreda" class="form-control-edit"
                                        onchange="loadKebeles()">
                                        <option value="">Select Woreda</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Kebele</label>
                                    <select id="kebele" name="kebele" class="form-control-edit">
                                        <option value="">Select Kebele</option>
                                    </select>
                                </div>
                                <div class="form-group" style="grid-column: 1/-1;">
                                    <label>Detailed Address (House No.)</label>
                                    <textarea name="address" class="form-control-edit"
                                        placeholder="House No 123..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Finance -->
                        <div id="financial">
                            <div class="section-title"><i class="fas fa-university"></i> Banking & Finance</div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Bank Name</label>
                                    <select name="bank_name" class="form-control-edit">
                                        <option value="">Select Bank</option>
                                        <option value="Commercial Bank of Ethiopia">Commercial Bank of Ethiopia</option>
                                        <option value="Dashen Bank">Dashen Bank</option>
                                        <option value="Abyssinia Bank">Abyssinia Bank</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Account Number</label>
                                    <input type="text" name="bank_account" class="form-control-edit"
                                        placeholder="Account Number">
                                </div>
                                <div class="form-group">
                                    <label>Credit Status</label>
                                    <select name="credit_status" class="form-control-edit"
                                        onchange="toggleCreditFile(this)">
                                        <option value="good">Good / No Debt</option>
                                        <option value="active">Active Credit</option>
                                        <option value="bad">Bad / Default</option>
                                    </select>
                                </div>
                                <div id="creditFileGroup" class="form-group" style="display: none; grid-column: 1/-1;">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                        <div>
                                            <label>Credit Status File (Attached)</label>
                                            <div class="upload-area"
                                                onclick="document.getElementById('loan_file_input').click()"
                                                style="padding: 10px;">
                                                <i class="fas fa-file-invoice-dollar"></i> Upload Credit Document
                                                <input type="file" id="loan_file_input" name="loan_file"
                                                    accept=".pdf,image/*" style="display:none"
                                                    onchange="handleSingleFile(this, 'loan_file_display')">
                                            </div>
                                        </div>
                                        <div>
                                            <label>Credit Information / Details</label>
                                            <textarea name="credit_details" class="form-control-edit"
                                                placeholder="Enter details about the active credit or loan..."
                                                style="height: 70px;"></textarea>
                                        </div>
                                        <div style="grid-column: 1 / -1;">
                                            <div id="loan_file_display"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Warranty -->
                        <div id="warranty">
                            <div class="section-title"><i class="fas fa-shield-halved"></i> Warranty & Legal</div>
                            <div class="form-grid">
                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label>Warranty / Guarantor Required?</label>
                                    <select name="warranty_status" class="form-control-edit"
                                        onchange="toggleWarrantyFields(this)">
                                        <option value="yes">Yes - Guarantor Required</option>
                                        <option value="no">No - Not Required</option>
                                    </select>
                                </div>

                                <div id="warrantyFields" class="form-grid"
                                    style="grid-column: 1 / -1; display: grid; margin-bottom: 0;">
                                    <div class="form-group">
                                        <label>Guarantor Name</label>
                                        <input type="text" name="person_name" class="form-control-edit"
                                            placeholder="Full Name">
                                    </div>
                                    <div class="form-group">
                                        <label>Guarantor Phone</label>
                                        <input type="tel" name="phone" class="form-control-edit" placeholder="+251...">
                                    </div>
                                    <div class="form-group">
                                        <label>Guarantor Woreda</label>
                                        <input type="text" name="warranty_woreda" class="form-control-edit"
                                            placeholder="Woreda">
                                    </div>
                                    <div class="form-group">
                                        <label>Guarantor Kebele</label>
                                        <input type="text" name="warranty_kebele" class="form-control-edit"
                                            placeholder="Kebele">
                                    </div>
                                    <div class="form-group">
                                        <label>FIN / National ID</label>
                                        <input type="text" name="fin_id" class="form-control-edit"
                                            placeholder="ID Number" oninput="toggleFinScan(this)">
                                        <div id="finScanGroup" style="display: none; margin-top: 10px;">
                                            <div class="upload-area"
                                                onclick="document.getElementById('fin_scan_input').click()"
                                                style="padding: 8px; border-style: dashed; background: #f0f7ff;">
                                                <i class="fas fa-id-card" style="font-size: 0.9rem;"></i> <span
                                                    style="font-size: 0.8rem;">Attach ID Scan</span>
                                                <input type="file" id="fin_scan_input" name="fin_scan"
                                                    accept=".pdf,image/*" style="display:none"
                                                    onchange="handleSingleFile(this, 'fin_scan_display')">
                                            </div>
                                            <div id="fin_scan_display"></div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>ID Details</label>
                                        <input type="text" name="national_id_details" class="form-control-edit"
                                            placeholder="Issued Place, Date, etc.">
                                    </div>
                                    <div class="form-group">
                                        <label>Warranty Agreement</label>
                                        <div class="upload-area"
                                            onclick="document.getElementById('warranty_file').click()">
                                            <i class="fas fa-paperclip"></i> Upload Agreement
                                            <input type="file" id="warranty_file" name="scan_file" style="display:none"
                                                onchange="handleSingleFile(this, 'warranty_file_display')">
                                        </div>
                                        <div id="warranty_file_display"></div>
                                    </div>
                                </div>

                                <div class="form-group" style="grid-column: 1 / -1; margin-top: 20px;">
                                    <div
                                        style="font-weight: 600; color: var(--primary); margin-bottom: 15px; border-top: 1px solid #f1f5f9; padding-top: 15px;">
                                        Legal & Criminal Status</div>
                                </div>
                                <div class="form-group">
                                    <label>Criminal Record Status</label>
                                    <select name="criminal_status" class="form-control-edit"
                                        onchange="toggleCriminalFile(this)">
                                        <option value="no">Clean</option>
                                        <option value="yes">Has Record</option>
                                    </select>
                                </div>
                                <div id="criminalFileGroup" class="form-group"
                                    style="display: none; grid-column: 1/-1;">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                        <div>
                                            <label>Criminal Record File (Photo/Scan)</label>
                                            <div class="upload-area"
                                                onclick="document.getElementById('criminal_file_input').click()"
                                                style="padding: 10px;">
                                                <i class="fas fa-balance-scale"></i> Upload Criminal Document
                                                <input type="file" id="criminal_file_input" name="criminal_file"
                                                    accept=".pdf,image/*" style="display:none"
                                                    onchange="handleSingleFile(this, 'criminal_file_display')">
                                            </div>
                                            <div id="criminal_file_display"></div>
                                        </div>
                                        <div>
                                            <label>Criminal Record Details</label>
                                            <textarea name="criminal_record_details" class="form-control-edit"
                                                placeholder="Enter specific details about the record..."
                                                style="height: 70px;"></textarea>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Documents -->
                        <div id="documents">
                            <div class="section-title"><i class="fas fa-folder-open"></i> Additional Documents</div>
                            <div class="form-group">
                                <label>Upload IDs, Certificates, etc.</label>
                                <div class="upload-area" onclick="document.getElementById('multi_docs').click()">
                                    <i class="fas fa-copy"></i> Select Multiple Files
                                    <input type="file" id="multi_docs" name="documents[]" multiple style="display:none"
                                        onchange="handleFileList(this, 'doc_list')">
                                </div>
                                <div id="doc_list"
                                    style="margin-top: 10px; font-size: 0.8rem; color: var(--secondary);"></div>
                                <small style="margin-top:5px; display:block; color:var(--gray)">Supported: PDF, JPG,
                                    PNG</small>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="action-bar">
                            <button type="button" onclick="window.location.href='wereda_hr_employee.php'"
                                class="btn-cancel">Cancel</button>
                            <div style="display:flex; gap: 15px;">
                                <button type="submit" class="btn-confirm">
                                    <i class="fas fa-check-circle"></i> Update Employee
                                </button>
                            </div>
                        </div>

                    </form>
                </div>

            </div>
        </div>
        </main>
    </div>

    <script>
        // Move all event handlers to proper JavaScript instead of inline
        document.addEventListener('DOMContentLoaded', function () {
            // Navigation scroll handlers
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', function () {
                    const sectionId = this.getAttribute('data-section');
                    if (sectionId) scrollToSection(sectionId);
                });
            });
        });

        function scrollToSection(id) {
            document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('data-section') === id) item.classList.add('active');
            });
        }

        function previewProfile(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.getElementById('previewImg');
                    img.src = e.target.result;
                    img.style.display = 'block';
                    document.querySelector('.new-employee-badge i').style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function checkOtherCitizenship(select) {
            const field = document.getElementById('otherCitizenship');
            if (select.value === 'Other') {
                field.classList.add('show');
            } else {
                field.classList.remove('show');
            }
        }

        function checkOtherLanguage(select) {
            const field = document.getElementById('otherLanguage');
            if (select.value === 'other') {
                field.classList.add('show');
            } else {
                field.classList.remove('show');
            }
        }

        function checkOtherReligion(select) {
            const field = document.getElementById('otherReligion');
            if (select.value === 'other') {
                field.classList.add('show');
            } else {
                field.classList.remove('show');
            }
        }

        function handleFileList(input, listId) {
            const list = document.getElementById(listId);
            list.innerHTML = '';
            if (input.files) {
                Array.from(input.files).forEach(file => {
                    const div = document.createElement('div');
                    div.innerHTML = '<i class="fas fa-file-alt"></i> ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                    div.style.marginBottom = '5px';
                    list.appendChild(div);
                });
            }
        }

        // Comprehensive Ethiopian Location Data (Full Source)
        const locationData = {
            "Afar": {
                "Administrative Zone 1 (Awsi Rasu)": ["Afambo", "Asayita", "Chifra", "Dubti", "Elidar", "Kori", "Mille", "Ada'ar"],
                "Administrative Zone 2 (Kilbet Rasu)": ["Abala", "Afdera", "Berhale", "Dallol", "Erebti", "Koneba", "Megale", "Bidu"],
                "Administrative Zone 3 (Gabi Rasu)": ["Amibara", "Awash Fentale", "Bure Mudaytu", "Dulecha", "Gewane"],
                "Administrative Zone 4 (Fantena Rasu)": ["Aura", "Ewa", "Gulina", "Teru", "Yalo"],
                "Administrative Zone 5 (Hari Rasu)": ["Dalifage", "Dewe", "Hadele Ele", "Simurobi Gele'alo", "Telalak"],
                "Argobba (special woreda)": ["Argobba"]
            },
            "Amhara": {
                "Agew Awi": ["Ankasha Guagusa", "Banja Shekudad", "Dangila", "Faggeta Lekoma", "Guagusa Shekudad", "Guangua", "Jawi", "Metekel", "Pawi"],
                "East Gojjam": ["Aneded", "Awabel", "Baso Liben", "Bibugn", "Debay Telatgen", "Debre Elias", "Debre Marqos (town)", "Dejen", "Enarj Enawga", "Enbise Sar Midir", "Enemay", "Goncha", "Goncha Siso Enese", "Guzamn", "Hulet Ej Enese", "Machakel", "Shebel Berenta", "Sinan"],
                "North Gondar": ["Addi Arkay", "Alefa", "Beyeda", "Chilga", "Dabat", "Debarq", "Dembiya", "Gondar (town)", "Gondar Zuria", "Jan Amora", "Kuara", "Lay Armachiho", "Metemma", "Mirab Armachiho", "Mirab Belessa", "Misraq Belessa", "Humera", "Tachi Armachiho"],
                "North Shewa": ["Angolalla Tera", "Ankober", "Antsokiyana Gemza", "Asagirt", "Basona Werana", "Berehet", "Debre Berhan (town)", "Efratana Gidim", "Ensaro", "Geshe", "Hagere Mariamna Kesem", "Kewet", "Menjarna Shenkora", "Menz Gera Midir", "Menz Keya Gebreal", "Menz Lalo Midir", "Menz Mam Midir", "Merhabiete", "Mida Woremo", "Mojana Wadera", "Moretna Jiru", "Siyadebrina Wayu", "Termaber"],
                "North Wollo": ["Bugna", "Dawunt", "Delanta", "Gidan", "Guba Lafto", "Habru", "Kobo", "Lasta", "Meket", "Wadla", "Weldiya (town)"],
                "Oromia": ["Artuma Fursi", "Bati", "Dawa Chefe", "Dawa Harewa", "Jile Timuga", "Kemise (town)"],
                "South Gondar": ["Debre Tabor (town)", "Dera", "Ebenat", "Farta", "Fogera", "Kemekem", "Lay Gayint", "Mirab Este", "Misraq Este", "Simada", "Tach Gayint"],
                "South Wollo": ["Abuko", "Amba Sel", "Borena", "Dessie (town)", "Dessie Zuria", "Jama", "Kalu (woreda)", "Kelala", "Kombolcha (town)", "Kutaber", "Legahida", "Legambo", "Magdala", "Mehal Sayint", "Sayint", "Tehuledere", "Tenta", "Wegde", "Were Babu", "Were Ilu"],
                "Wag Hemra": ["Aberegelle", "Dehana", "Gazbibla", "Sehala", "Soqota (town)", "Soqota Zuria", "Zikuala"],
                "West Gojjam": ["Bahir Dar Zuria", "Bure", "Debub Achefer", "Dega Damot", "Dembecha", "Finote Selam (town)", "Jabi Tehnan", "Kuarit", "Mecha", "Sekela", "Semien Achefer", "Wemberma", "Yilmana Densa"],
                "Bahir Dar (special zone)": ["Bahir Dar (town)"]
            },
            "Benishangul-Gumuz": {
                "Asosa": ["Asosa", "Bambasi", "Komesha", "Horazab", "Menge", "Oda Bildigilu", "Sherkole"],
                "Kamashi": ["Agalo Mite", "Belo Jegonfoy", "Kamashi", "Sadal", "Yaso"],
                "Metekel": ["Bulen", "Dangur", "Dibate", "Guba", "Mandura", "Wenbera", "Pawe"]
            },
            "Gambela": {
                "Anuak": ["Abobo", "Dimma", "Gambela (town)", "Gambela Zuria", "Gog", "Jor", "Akobo"],
                "Mezhenger": ["Godere", "Mengesh"],
                "Nuer": ["Jikawo", "Lare", "Akobo"]
            },
            "Harari": {
                "Harari": ["Amir-Nur Woreda", "Abadir Woreda", "Shenkor Woreda", "Jin'Eala Woreda", "Aboker Woreda", "Hakim Woreda", "Sofi Woreda", "Erer Woreda", "Dire-Teyara Woreda"]
            },
            "Oromia": {
                "Arsi": ["Amigna", "Aseko", "Asella", "Bale Gasegar", "Chole", "Digeluna Tijo", "Diksis", "Dodota", "Enkelo Wabe", "Gololcha", "Guna", "Hitosa", "Jeju", "Limuna Bilbilo", "Lude Hitosa", "Merti", "Munesa", "Robe", "Seru", "Sire", "Sherka", "Sude", "Tena", "Tiyo", "Ziway Dugda"],
                "Bale": ["Agarfa", "Berbere", "Dawe Kachen", "Dawe Serara", "Delo Menna", "Dinsho", "Gasera", "Ginir", "Goba (woreda)", "Goba (town)", "Gololcha", "Goro", "Guradamole", "Harena Buluk", "Legehida", "Meda Welabu", "Raytu", "Robe (town)", "Seweyna", "Sinana"],
                "Borena": ["Abaya", "Arero", "Bule Hora", "Dire", "Dugda Dawa", "Gelana", "Miyu", "Moyale", "Teltele", "Yabelo", "Dehas", "Dillo", "Malka Soda"],
                "East Hararghe": ["Babille", "Bedeno", "Chinaksen", "Deder", "Fedis", "Girawa", "Gola Oda", "Goro Gutu", "Gursum", "Haro Maya", "Jarso", "Kersa", "Kombolcha", "Kurfa Chele", "Malka Balo", "Meta", "Meyumuluke", "Midega Tola", "Deder town"],
                "East Shewa": ["Ada'a", "Adama Zuria", "Adami Tullu and Jido Kombolcha", "Bishoftu (town)", "Bora", "Dugda", "Boset", "Fentale", "Gimbichu", "Liben", "Lome", "Ziway (town)"],
                "East Welega": ["Bonaya Boshe", "Diga", "Gida Ayana", "Kiremu", "Gobu Seyo", "Gudeya Bila", "Guto Gida", "Haro Limmu", "Leka Dulecha", "Ibantu", "Jimma Arjo", "Limmu", "Nekemte (town)", "Nunu Kumba", "Sasiga", "Sibu Sire", "Wama Hagalo", "Wayu Tuka"],
                "Guji": ["Adola", "Ana Sora", "Bore", "Dima", "Girja", "Hambela Wamena", "Harenfema", "Kebri Mangest (town)", "Kercha", "Liben", "Negele Borana (town)", "Odo Shakiso", "Uraga", "Wadera"],
                "Horo Guduru Welega Zone": ["Abay Chomen", "Abe Dongoro", "Amuru", "Guduru", "Hababo Guduru", "Horo", "Jardega Jarte", "Jimma Genete", "Jimma Rare", "Shambu (town)"],
                "Illubabor Zone": ["Illubabor"],
                "Jimma": ["Agaro (town)", "Chora Botor", "Dedo", "Gera", "Gomma", "Guma", "Kersa", "Limmu Kosa", "Limmu Sakka", "Mana", "Omo Nada", "Seka Chekorsa", "Setema", "Shebe Senbo", "Sigmo", "Sokoru", "Tiro Afeta"],
                "Kelam Welega": ["Anfillo", "Dale Sedi", "Dale Wabera", "Dembidolo (town)", "Gawo Kebe", "Gidami", "Hawa Gelan", "Jimma Horo", "Lalo Kile", "Sayo", "Yemalogi Welele"],
                "North Shewa": ["Abichuna Gne'a", "Aleltu", "Debre Libanos", "Degem", "Dera", "Fiche (town)", "Gerar Jarso", "Hidabu Abote", "Jidojidda", "Kembibit", "Kuyu Garba Guracha", "Sendafa", "Wara Jarso", "Wuchalemuke", "Yaya Gulele"],
                "Southwest Shewa": ["Amaya", "Becho", "Dawo", "Elu", "Goro", "Kersana Malima", "Seden Sodo", "Sodo Dacha", "Tole", "Waliso (woreda)", "Waliso (town)", "Wonchi"],
                "West Arsi": ["Adaba", "Arsi Negele", "Dodola", "Gedeb Asasa", "Kofele", "Kokosa", "Kore", "Nensebo", "Seraro", "Shala", "Shashamene (town)", "Shashamene Zuria"],
                "West Haraghe": ["Anchar", "Badessa (town)", "Boke", "Chiro (town)", "Chiro Zuria", "Gemechis", "Darolebu", "Doba", "Guba Koricha", "Habro", "Kuni", "Mesela", "Mieso", "Tulo", "Hawi Gudina"],
                "West Shewa": ["Abuna Ginde Beret", "Ada'a Berga", "Ambo (town)", "Ginchi (town)", "Ambo (woreda)", "Bako Tibe", "Cheliya", "Dano", "Dendi", "Ejere", "Elfata", "Ginde Beret", "Jaldu", "Jibat", "Meta Robi", "Midakegn", "Nono", "Dire Enchini", "Toke Kutaye"],
                "West Welega": ["Ayra", "Babo Gambela", "Begi", "Boji Chokorsa", "Boji Dirmaji", "Genji", "Gimbi (woreda)", "Gimbi (town)", "Guliso", "Haru", "Homa", "Jarso", "Kondala", "Kiltu Kara", "Lalo Asabi", "Mana Sibu", "Nejo", "Nole Kaba", "Sayo Nole", "Yubdo"],
                "Adama (special zone)": ["Adama"],
                "Jimma (special zone)": ["Jimma"],
                "Oromia-Finfinne (special zone)": ["Akaki", "Bereh", "Burayu (town)", "Holeta Genet (town)", "Mulo", "Sebeta Hawas", "Sebeta (town)", "Sendafa (town)", "Walmara"]
            },
            "Somali": {
                "Afder": ["Hargelle", "Baarey", "Cherati", "Ceelgari", "Dolobay", "Iimey galbeed", "Raaso", "God God", "Qooxle"],
                "Jarar": ["Aware", "Dhadax-buur", "Dhagax-madow", "Gunagado", "Gashamo", "Birqod", "Dig", "Bilcil buur", "Daroor", "Araarso", "Yoocaale"],
                "Nogob": ["Ceelweyne", "Dhuxun", "Gerbo", "Xaraarey", "Ayun", "Hor-shagah", "Segeg"],
                "Gode(Shabelle)": ["Cadaadle", "Danan", "Ferfer", "Beer Caano", "Gode", "Iimey bari", "Kelafo", "Mustahil", "Elale", "Abaqorow"],
                "Fafan": ["Tuli Guled", "Awbere", "Babille", "Gursum", "Harshin", "Jijiga", "Kebri Beyah", "Goljano", "Qooraan", "Harawo", "Jigjiga Waqooyi", "Jigjiga Galbeed"],
                "Korahe": ["Dhooboweyn", "Kebri Dahar", "Sheygoosh", "Shilaabo", "Marsin", "Higloley", "Las Dharkaynle", "Kudunbuur", "Bodalay", "Ceel-Ogaden"],
                "Liben": ["Liben"],
                "Sitti": ["Afdem", "Ayesha", "Dembel", "Erer", "Mieso", "Shinile", "Hadhagaale", "Geblalu", "Gota biki"],
                "Dollo": ["Bokh", "Danot", "Geladi", "Werder", "Daratole", "Galxamur"]
            },
            "Southern Nations": {
                "Bench Maji": ["Bero", "Debub Bench", "Guraferda", "Maji", "Meinit Goldiya", "Meinit Shasha", "Mizan Aman (town)", "Semien Bench", "She Bench", "Sheko", "Surma"],
                "Dawro": ["Gena Bosa", "Isara", "Loma", "Mareka", "Tocha"],
                "Gamo Gofa": ["Arba Minch (town)", "Arba Minch Zuria", "Bonke", "Boreda", "Chencha", "Dita", "Deramalo", "Demba Gofa", "Geze Gofa", "Kemba", "Kucha", "Melokoza", "Mirab Abaya", "Oyda", "Sawla (town)", "Uba Debretsehay", "Zala"],
                "Gedeo": ["Bule", "Dila (town)", "Dila Zuria", "Gedeb", "Kochere", "Wenago", "Yirgachefe"],
                "Gurage": ["Abeshge", "Butajira (town)", "Cheha", "Endegagn", "Enemorina Eaner", "Ezha", "Geta", "Gumer", "Kebena", "Gedebano Gutazer Welene", "Mareko", "Meskane", "Muhor Na Aklil", "Soddo", "Welkite (town)"],
                "Hadiya": ["Ana Lemo", "Duna", "Gibe", "Gomibora", "Hosaena (town)", "Limo", "Mirab Badawacho", "Misha", "Misraq Badawacho", "Shashogo", "Soro", "Gimbichu"],
                "Keffa": ["Bita", "Bonga (town)", "Chena", "Cheta", "Decha", "Gesha", "Gewata", "Ginbo", "Menjiwo", "Sayilem", "Telo"],
                "Kembata Tembaro": ["Angacha", "Damboya", "Doyogena", "Durame (town)", "Hadero Tunto", "Kacha Bira", "Kedida Gamela", "Tembaro"],
                "Sheka": ["Anderacha", "Masha", "Yeki"],
                "Sidama": ["Aleta Wendo", "Arbegona", "Aroresa", "Awasa Zuria", "Bensa", "Bona Zuria", "Boricha", "Bursa", "Chere", "Chuko", "Dale", "Dara", "Gorche", "Hula", "Loko Abaya", "Malga", "Shebedino", "Wensho", "Wondo Genet"],
                "Silt'e": ["Alicho Werero", "Dalocha", "Lanfro", "Mirab Azernet Berbere", "Misraq Azernet Berbere", "Sankurra", "Silte", "Wulbareg"],
                "South Omo": ["Bako Gazer", "Bena Tsemay", "Gelila", "Hamer", "Kuraz", "Male", "Nyangatom", "Selamago"],
                "Wolayita": ["Boloso Bombe", "Boloso Sore", "Damot Gale", "Damot Pulasa", "Damot Sore", "Damot Weyde", "Diguna Fango", "Humbo", "Kindo Didaye", "Kindo Koysha", "Offa", "Sodo (town)", "Sodo Zuria"],
                "Alaba (special woreda)": ["Alaba"],
                "Amaro (special woreda)": ["Amaro"],
                "Basketo (special woreda)": ["Basketo"],
                "Burji (special woreda)": ["Burji"],
                "Dirashe (special woreda)": ["Dirashe"],
                "Konso (special woreda)": ["Konso"],
                "Konta (special woreda)": ["Konta"],
                "Yem (special woreda)": ["Yem"]
            },
            "Tigray": {
                "Central Tigray": ["Abergele", "Adwa", "Degua Tembien", "Enticho", "Kola Tembien", "La'ilay Maychew", "Mereb Lehe", "Naeder Adet", "Tahtay Maychew", "Werie Lehe"],
                "East Tigray": ["Atsbi Wenberta", "Ganta Afeshum", "Gulomahda", "Hawzen", "Irob", "Saesi Tsaedaemba", "Wukro"],
                "North West Tigray": ["Asgede Tsimbela", "La'ilay Adiyabo", "Medebay Zana", "Tahtay Adiyabo", "Tahtay Koraro", "Tselemti"],
                "South Tigray": ["Alaje", "Alamata", "Endamehoni", "Ofla", "Raya Azebo"],
                "South East Tigray": ["Enderta", "Hintalo Wajirat", "Samre"],
                "West Tigray": ["Kafta Humera", "Tsegede", "Wolqayt"],
                "Mekele (special zone)": ["Mek'ele"]
            },
            "Addis Ababa": {
                "Addis Ababa": ["Addis Ketema", "Akaky Kaliti", "Arada", "Bole", "Gullele", "Kirkos", "Kolfe Keranio", "Lideta", "Nifas Silk-Lafto", "Yeka"]
            },
            "Dire Dawa": {
                "Dire Dawa": ["Dire Dawa -City", "Dire Dawa -Non-urban"]
            },
            "Other": {
                "Other": ["Other"]
            }
        };

        function loadZones() {
            const reg = document.getElementById('region').value;
            const z = document.getElementById('zone');
            const w = document.getElementById('woreda');
            const k = document.getElementById('kebele');

            // Reset dependent dropdowns
            z.innerHTML = '<option value="">Select Zone</option>';
            w.innerHTML = '<option value="">Select Woreda</option>';
            k.innerHTML = '<option value="">Select Kebele</option>';

            if (locationData[reg]) {
                Object.keys(locationData[reg]).forEach(key => {
                    z.add(new Option(key, key));
                });
            } else if (reg === 'Other') {
                z.add(new Option('Other', 'Other'));
            }
        }


        function loadWoredas() {
            const reg = document.getElementById('region').value;
            const zn = document.getElementById('zone').value;
            const w = document.getElementById('woreda');
            const k = document.getElementById('kebele');

            // Reset dependent dropdowns
            w.innerHTML = '<option value="">Select Woreda</option>';
            k.innerHTML = '<option value="">Select Kebele</option>';

            if (locationData[reg] && locationData[reg][zn]) {
                locationData[reg][zn].forEach(wd => {
                    w.add(new Option(wd, wd));
                });
            } else {
                // Fallback for custom entries
                w.add(new Option('Other', 'Other'));
            }
        }

        function loadKebeles() {
            const k = document.getElementById('kebele');
            k.innerHTML = '<option value="">Select Kebele</option>';
            // Generate Kebele 1 - 50 for more options
            for (let i = 1; i <= 50; i++) {
                const val = 'Kebele ' + i;
                k.add(new Option(val, val));
            }
        }

        // Parse URL Params
        const urlParams = new URLSearchParams(window.location.search);
        const employeeIdParam = urlParams.get('id') || urlParams.get('employee_id');

        if (!employeeIdParam) {
            alert('No Employee ID provided.');
            window.location.href = 'wereda_hr_employee.php';
        }

        // Load Data on Start
        document.addEventListener('DOMContentLoaded', () => {
            if (employeeIdParam) fetchEmployeeData(employeeIdParam);
        });

        function fetchEmployeeData(id) {
            // get_employee_detail.php expects 'employee_id' parameter
            fetch(`get_employee_detail.php?employee_id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        populateForm(data.employee);
                    } else {
                        alert('Error loading employee: ' + data.message);
                        window.location.href = 'wereda_hr_employee.php';
                    }
                })
                .catch(err => console.error('Error fetching data:', err));
        }

        function populateForm(data) {
            // Hidden IDs
            if (document.getElementById('db_id')) document.getElementById('db_id').value = data.id || '';
            if (document.getElementById('emp_id_hidden')) document.getElementById('emp_id_hidden').value = data.employee_id || '';

            const form = document.getElementById('editEmployeeForm');

            for (const [key, value] of Object.entries(data)) {
                // Try to find element by name
                const field = form.elements[key];

                if (field) {
                    if (field instanceof RadioNodeList) {
                        // Radio buttons
                        field.value = value;
                    } else if (field.type === 'checkbox') {
                        field.checked = !!value;
                    } else if (field.type === 'file') {
                        // Cannot set file input value
                    } else {
                        field.value = value || '';
                    }

                    // Trigger change event to update UI (conditional fields)
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            // Handle specific logic manually if needed (images, etc)
        }

        // Form Submission
        document.getElementById('editEmployeeForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const btn = document.querySelector('.btn-confirm');
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Updating...';
            btn.disabled = true;

            const formData = new FormData(this);

            fetch('employee_actions.php?action=edit', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Employee Updated Successfully!');
                        window.location.href = 'wereda_hr_employee.php';
                    } else {
                        alert('Error: ' + data.message);
                        btn.innerHTML = originalContent;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    alert('Network error. Please check your connection.');
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                });
        });

        function toggleCreditFile(select) {
            const group = document.getElementById('creditFileGroup');
            if (select.value === 'active') {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
            }
        }

        function toggleFinScan(input) {
            const group = document.getElementById('finScanGroup');
            group.style.display = input.value.trim() !== '' ? 'block' : 'none';
        }

        function toggleCriminalFile(select) {
            const group = document.getElementById('criminalFileGroup');
            group.style.display = select.value === 'yes' ? 'block' : 'none';
        }

        function toggleWarrantyFields(select) {
            const group = document.getElementById('warrantyFields');
            group.style.display = select.value === 'yes' ? 'grid' : 'none';
        }


        // Advanced File Handler
        const fileStore = new Map(); // Stores DataTransfer objects for each input ID

        function handleFileList(input, containerId) {
            const container = document.getElementById(containerId);
            const inputId = input.id;

            // Initialize storage for this input if new
            if (!fileStore.has(inputId)) {
                fileStore.set(inputId, new DataTransfer());
            }
            const dt = fileStore.get(inputId);

            // Add new files to the DataTransfer object
            for (let i = 0; i < input.files.length; i++) {
                dt.items.add(input.files[i]);
            }

            // Sync input files
            input.files = dt.files;

            // Render the list
            renderFiles(inputId, containerId);
        }

        function renderFiles(inputId, containerId) {
            const input = document.getElementById(inputId);
            const container = document.getElementById(containerId);
            const dt = fileStore.get(inputId);

            container.innerHTML = ''; // Clear current list

            if (dt.files.length === 0) {
                return;
            }

            Array.from(dt.files).forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.style.cssText = 'display:flex; justify-content:space-between; align-items:center; background:#f1f5f9; padding:6px 10px; margin-top:5px; border-radius:6px; font-size:0.85rem;';

                const nameSpan = document.createElement('span');
                nameSpan.textContent = file.name;
                nameSpan.style.cssText = 'overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:80%;';

                const removeBtn = document.createElement('i');
                removeBtn.className = 'fas fa-times';
                removeBtn.style.cssText = 'color:#ef4444; cursor:pointer; margin-left:10px;';
                removeBtn.title = 'Remove file';
                removeBtn.onclick = function () {
                    removeFile(inputId, index, containerId);
                };

                fileItem.appendChild(nameSpan);
                fileItem.appendChild(removeBtn);
                container.appendChild(fileItem);
            });
        }

        function removeFile(inputId, index, containerId) {
            const dt = fileStore.get(inputId);
            dt.items.remove(index);

            // Sync input
            document.getElementById(inputId).files = dt.files;

            // Re-render
            renderFiles(inputId, containerId);
        }

        // Initialize listeners for single file inputs too to show name
        function handleSingleFile(input, displayId) {
            const display = document.getElementById(displayId);
            if (input.files && input.files[0]) {
                display.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center; background:#e0f2fe; padding:8px 12px; margin-top:8px; border-radius:6px; color:#0369a1; font-size:0.9rem;">
                   <span><i class="fas fa-check-circle"></i> ${input.files[0].name}</span>
                   <i class="fas fa-times" onclick="clearSingleFile('${input.id}', '${displayId}')" style="cursor:pointer; color:#0284c7;"></i>
                </div>
                `;
            }
        }

        function clearSingleFile(inputId, displayId) {
            const input = document.getElementById(inputId);
            input.value = ''; // Clear value
            document.getElementById(displayId).innerHTML = '';
        }
    </script>
    <script src="scripts.js"></script>
</body>

</html>