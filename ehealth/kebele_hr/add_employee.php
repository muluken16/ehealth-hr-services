<?php
session_start();
// Default user/role for demo if needed
if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'Kebele HR Officer';
    $_SESSION['role'] = 'kebele_hr';
}
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

        body { background: #f1f5f9; font-family: 'Inter', sans-serif; color: #1e293b; }
        
        .add-layout {
            max-width: 1100px;
            margin: 40px auto;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 30px;
            padding: 0 20px;
        }

        /* Sidebar Navigation */
        .nav-card {
            background: white;
            border-radius: 24px;
            padding: 30px 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            height: fit-content;
            position: sticky;
            top: 40px;
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
        .nav-item:hover { background: var(--light); color: var(--primary); }
        .nav-item.active { background: #eff6ff; color: var(--primary); }

        /* Main Form Area */
        .form-content { background: white; border-radius: 24px; padding: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        
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

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px; }
        
        .form-group { margin-bottom: 0; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; }
        .form-group label span.required { color: var(--danger); }

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

        textarea.form-control-edit { resize: vertical; min-height: 80px; }

        .conditional-field {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px dashed #cbd5e1;
        }
        .conditional-field.show { display: block; }

        .action-bar {
            position: sticky;
            bottom: 20px;
            background: white;
            padding: 20px 30px;
            border-radius: 20px;
            box-shadow: 0 -10px 25px rgba(0,0,0,0.05), 0 10px 25px rgba(0,0,0,0.05);
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
        .btn-confirm:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(26, 74, 95, 0.3); }

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
        .btn-cancel:hover { background: #e2e8f0; }

        .upload-area {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            background: #f8fafc;
            transition: all 0.2s;
        }
        .upload-area:hover { border-color: var(--primary); background: #eef2ff; }

        @media (max-width: 900px) {
            .add-layout { grid-template-columns: 1fr; }
            .nav-card { position: relative; top: 0; margin-bottom: 20px; }
        }
        
        .main-content { overflow-y: auto; height: 100vh; }
    </style>
</head>
<body>
    <div class="hr-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <?php 
                $page_title = "Add New Employee";
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
                        <div class="nav-title">New Employee</div>
                        <div class="nav-subtitle">Create a new record</div>
                        
                        <div class="nav-sections">
                            <div class="nav-item active" onclick="scrollToSection('personal')"><i class="fas fa-user"></i> Personal</div>
                            <div class="nav-item" onclick="scrollToSection('education')"><i class="fas fa-graduation-cap"></i> Education</div>
                            <div class="nav-item" onclick="scrollToSection('employment')"><i class="fas fa-briefcase"></i> Employment</div>
                            <div class="nav-item" onclick="scrollToSection('location')"><i class="fas fa-map-marker-alt"></i> Contact</div>
                            <div class="nav-item" onclick="scrollToSection('financial')"><i class="fas fa-university"></i> Finance</div>
                            <div class="nav-item" onclick="scrollToSection('warranty')"><i class="fas fa-shield-alt"></i> Warranty</div>
                            <div class="nav-item" onclick="scrollToSection('documents')"><i class="fas fa-folder-open"></i> Documents</div>
                        </div>
                    </div>

                    <!-- Form Area -->
                    <div class="form-content">
                        <form id="addEmployeeForm" enctype="multipart/form-data">
                            <!-- Hidden Camera Input Move Inside Form -->
                            <input type="file" name="photo" id="photoInput" accept="image/*" style="display: none;" onchange="previewProfile(this)">
                            
                            
                            <!-- Personal -->
                            <div id="personal">
                                <div class="section-title"><i class="fas fa-user-circle"></i> Personal Information</div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>First Name <span class="required">*</span></label>
                                        <input type="text" name="first_name" class="form-control-edit" placeholder="e.g. Abebe" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Middle Name</label>
                                        <input type="text" name="middle_name" class="form-control-edit" placeholder="e.g. Kebede">
                                    </div>
                                    <div class="form-group">
                                        <label>Last Name <span class="required">*</span></label>
                                        <input type="text" name="last_name" class="form-control-edit" placeholder="e.g. Tesfaye" required>
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
                                        <select name="religion" class="form-control-edit" onchange="checkOtherReligion(this)">
                                            <option value="">Select Religion</option>
                                            <option value="Orthodox">Orthodox</option>
                                            <option value="Islam">Islam</option>
                                            <option value="Protestant">Protestant</option>
                                            <option value="Catholic">Catholic</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <input type="text" id="otherReligion" name="other_religion" class="form-control-edit conditional-field" placeholder="Enter religion">
                                    </div>
                                    <div class="form-group">
                                        <label>Citizenship</label>
                                        <select name="citizenship" class="form-control-edit" onchange="checkOtherCitizenship(this)">
                                            <option value="Ethiopia" selected>Ethiopia</option>
                                            <option value="United States">United States</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <input type="text" id="otherCitizenship" name="other_citizenship" class="form-control-edit conditional-field" placeholder="Enter country">
                                    </div>
                                    <div class="form-group">
                                        <label>Primary Language</label>
                                        <select name="language" class="form-control-edit" onchange="checkOtherLanguage(this)">
                                            <option value="amharic">Amharic</option>
                                            <option value="oromo">Afaan Oromo</option>
                                            <option value="tigrigna">Tigrigna</option>
                                            <option value="english">English</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <input type="text" id="otherLanguage" name="other_language" class="form-control-edit conditional-field" placeholder="Enter language">
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
                                        <input type="text" name="university" class="form-control-edit" placeholder="University/College">
                                    </div>
                                    <div class="form-group">
                                        <label>Field of Study</label>
                                        <input type="text" name="department" class="form-control-edit" placeholder="e.g. Computer Science">
                                    </div>
                                    <div class="form-group">
                                        <label>Secondary School</label>
                                        <input type="text" name="secondary_school" class="form-control-edit" placeholder="High School Name">
                                    </div>
                                    <div class="form-group">
                                        <label>Upload Certificate(s)</label>
                                        <div class="upload-area" onclick="document.getElementById('edu_files').click()">
                                            <i class="fas fa-cloud-upload-alt"></i> Select Document(s)
                                            <input type="file" id="edu_files" name="education_files[]" multiple style="display:none" onchange="handleFileList(this, 'edu_list')">
                                        </div>
                                        <div id="edu_list" style="margin-top: 10px; font-size: 0.8rem; color: var(--secondary);"></div>
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
                                        <input type="text" name="position" class="form-control-edit" required placeholder="e.g. Senior Nurse">
                                    </div>
                                    <div class="form-group">
                                        <label>Join Date</label>
                                        <input type="date" name="join_date" class="form-control-edit" value="<?php echo date('Y-m-d'); ?>">
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

                            <!-- Contact -->
                            <div id="location">
                                <div class="section-title"><i class="fas fa-map-marked-alt"></i> Contact & Address</div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>Phone Number <span class="required">*</span></label>
                                        <input type="tel" name="phone_number" class="form-control-edit" required placeholder="+251...">
                                    </div>
                                    <div class="form-group">
                                        <label>Emergency Contact</label>
                                        <input type="text" name="emergency_contact" class="form-control-edit" placeholder="Name & Phone">
                                    </div>
                                    <div class="form-group">
                                        <label>Email Address <span class="required">*</span></label>
                                        <input type="email" name="email" class="form-control-edit" required placeholder="email@example.com">
                                    </div>
                                    <div class="form-group">
                                        <label>Region</label>
                                        <select id="region" name="region" class="form-control-edit" onchange="loadZones()">
                                            <option value="">Select Region</option>
                                            <option value="amhara">Amhara</option>
                                            <option value="oromia">Oromia</option>
                                            <option value="addis_ababa">Addis Ababa</option>
                                            <option value="other">Other</option>
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
                                        <select id="woreda" name="woreda" class="form-control-edit" onchange="loadKebeles()">
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
                                        <textarea name="address" class="form-control-edit" placeholder="House No 123..."></textarea>
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
                                        <input type="text" name="bank_account" class="form-control-edit" placeholder="Account Number">
                                    </div>
                                    <div class="form-group">
                                        <label>Credit Status</label>
                                        <select name="credit_status" class="form-control-edit" onchange="toggleCreditFile(this)">
                                            <option value="good">Good / No Debt</option>
                                            <option value="active">Active Credit</option>
                                            <option value="bad">Bad / Default</option>
                                        </select>
                                    </div>
                                    <div id="creditFileGroup" class="form-group" style="display: none; grid-column: 1/-1;">
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                            <div>
                                                <label>Credit Status File (Attached)</label>
                                                <div class="upload-area" onclick="document.getElementById('loan_file_input').click()" style="padding: 10px;">
                                                    <i class="fas fa-file-invoice-dollar"></i> Upload Credit Document
                                                    <input type="file" id="loan_file_input" name="loan_file" accept=".pdf,image/*" style="display:none">
                                                </div>
                                            </div>
                                            <div>
                                                <label>Credit Information / Details</label>
                                                <textarea name="credit_details" class="form-control-edit" placeholder="Enter details about the active credit or loan..." style="height: 70px;"></textarea>
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
                                        <select name="warranty_status" class="form-control-edit" onchange="toggleWarrantyFields(this)">
                                            <option value="yes">Yes - Guarantor Required</option>
                                            <option value="no">No - Not Required</option>
                                        </select>
                                    </div>

                                    <div id="warrantyFields" class="form-grid" style="grid-column: 1 / -1; display: grid; margin-bottom: 0;">
                                        <div class="form-group">
                                            <label>Guarantor Name</label>
                                            <input type="text" name="person_name" class="form-control-edit" placeholder="Full Name">
                                        </div>
                                        <div class="form-group">
                                            <label>Guarantor Phone</label>
                                            <input type="tel" name="phone" class="form-control-edit" placeholder="+251...">
                                        </div>
                                        <div class="form-group">
                                            <label>Guarantor Woreda</label>
                                            <input type="text" name="warranty_woreda" class="form-control-edit" placeholder="Woreda">
                                        </div>
                                        <div class="form-group">
                                            <label>Guarantor Kebele</label>
                                            <input type="text" name="warranty_kebele" class="form-control-edit" placeholder="Kebele">
                                        </div>
                                        <div class="form-group">
                                            <label>FIN / National ID</label>
                                            <input type="text" name="fin_id" class="form-control-edit" placeholder="ID Number" oninput="toggleFinScan(this)">
                                            <div id="finScanGroup" style="display: none; margin-top: 10px;">
                                                <div class="upload-area" onclick="document.getElementById('fin_scan_input').click()" style="padding: 8px; border-style: dashed; background: #f0f7ff;">
                                                    <i class="fas fa-id-card" style="font-size: 0.9rem;"></i> <span style="font-size: 0.8rem;">Attach ID Scan</span>
                                                    <input type="file" id="fin_scan_input" name="fin_scan" accept=".pdf,image/*" style="display:none">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>ID Details</label>
                                            <input type="text" name="national_id_details" class="form-control-edit" placeholder="Issued Place, Date, etc.">
                                        </div>
                                        <div class="form-group">
                                            <label>Warranty Agreement</label>
                                            <div class="upload-area" onclick="document.getElementById('warranty_file').click()">
                                                <i class="fas fa-paperclip"></i> Upload Agreement
                                                <input type="file" id="warranty_file" name="scan_file" style="display:none">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group" style="grid-column: 1 / -1; margin-top: 20px;">
                                        <div style="font-weight: 600; color: var(--primary); margin-bottom: 15px; border-top: 1px solid #f1f5f9; padding-top: 15px;">Legal & Criminal Status</div>
                                    </div>
                                    <div class="form-group">
                                        <label>Criminal Record Status</label>
                                        <select name="criminal_status" class="form-control-edit" onchange="toggleCriminalFile(this)">
                                            <option value="no">Clean</option>
                                            <option value="yes">Has Record</option>
                                        </select>
                                    </div>
                                    <div id="criminalFileGroup" class="form-group" style="display: none; grid-column: 1/-1;">
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                            <div>
                                                <label>Criminal Record File (Photo/Scan)</label>
                                                <div class="upload-area" onclick="document.getElementById('criminal_file_input').click()" style="padding: 10px;">
                                                    <i class="fas fa-balance-scale"></i> Upload Criminal Document
                                                    <input type="file" id="criminal_file_input" name="criminal_file" accept=".pdf,image/*" style="display:none">
                                                </div>
                                            </div>
                                            <div>
                                                <label>Criminal Record Details</label>
                                                <textarea name="criminal_record_details" class="form-control-edit" placeholder="Enter specific details about the record..." style="height: 70px;"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Warranty Agreement</label>
                                        <div class="upload-area" onclick="document.getElementById('warranty_file').click()">
                                            <i class="fas fa-paperclip"></i> Upload Agreement
                                            <input type="file" id="warranty_file" name="scan_file" style="display:none">
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
                                        <input type="file" id="multi_docs" name="documents[]" multiple style="display:none" onchange="handleFileList(this, 'doc_list')">
                                    </div>
                                    <div id="doc_list" style="margin-top: 10px; font-size: 0.8rem; color: var(--secondary);"></div>
                                    <small style="margin-top:5px; display:block; color:var(--gray)">Supported: PDF, JPG, PNG</small>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="action-bar">
                                <button type="button" onclick="window.location.href='hr-employees.php'" class="btn-cancel">Cancel</button>
                                <div style="display:flex; gap: 15px;">
                                    <button type="submit" class="btn-confirm">
                                        <i class="fas fa-check-circle"></i> Register Employee
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
        function scrollToSection(id) {
            document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('onclick').includes(id)) item.classList.add('active');
            });
        }

        function previewProfile(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
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
            if(select.value === 'Other') {
                field.classList.add('show');
            } else {
                field.classList.remove('show');
            }
        }

        function checkOtherLanguage(select) {
            const field = document.getElementById('otherLanguage');
            if(select.value === 'other') {
                field.classList.add('show');
            } else {
                field.classList.remove('show');
            }
        }

        function checkOtherReligion(select) {
            const field = document.getElementById('otherReligion');
            if(select.value === 'other') {
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
                    div.innerHTML = `<i class="fas fa-file-alt"></i> ${file.name} (${(file.size/1024).toFixed(1)} KB)`;
                    div.style.marginBottom = '5px';
                    list.appendChild(div);
                });
            }
        }

        // Mock Data for Regions (Same as in previous file)
        const amharaData = { 
            "North Wollo": ["Woldiya","Lalibela"], 
            "South Wollo": ["Dessie", "Kombolcha"],
            "Addis Ababa": ["Bole", "Yeka"]
        };
        const allData = {
            "amhara": amharaData,
            "addis_ababa": { "Addis Ababa": ["Bole", "Yeka", "Kirkos"] }
        };

        function loadZones() {
             const reg = document.getElementById('region').value;
             const z = document.getElementById('zone');
             z.innerHTML = '<option value="">Select Zone</option>';
             
             if(reg === 'amhara') {
                 Object.keys(amharaData).forEach(k => z.add(new Option(k, k)));
             } else if(reg === 'addis_ababa') {
                 z.add(new Option('Addis Ababa', 'Addis Ababa'));
             }
        }

        function loadWoredas() {
             const reg = document.getElementById('region').value;
             const zn = document.getElementById('zone').value;
             const w = document.getElementById('woreda');
             w.innerHTML = '<option value="">Select Woreda</option>';
             
             if(reg === 'amhara' && amharaData[zn]) {
                 amharaData[zn].forEach(wd => w.add(new Option(wd, wd)));
             } else if(reg === 'addis_ababa') {
                 ["Bole", "Yeka", "Kirkos"].forEach(wd => w.add(new Option(wd, wd)));
             }
        }
        
        function loadKebeles() {
             const k = document.getElementById('kebele');
             k.innerHTML = '<option value="">Select Kebele</option>';
             for(let i=1; i<=20; i++) k.add(new Option('Kebele '+i, 'Kebele '+i));
        }

        // Form Submission
        document.getElementById('addEmployeeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.querySelector('.btn-confirm');
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Registering...';
            btn.disabled = true;

            const formData = new FormData(this);
            
            fetch('employee_actions.php?action=add', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('Employee Registered Successfully!');
                    window.location.href = 'hr-employees.php';
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
        }

        function toggleCreditFile(select) {
            const group = document.getElementById('creditFileGroup');
            if(select.value === 'active') {
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
    </script>
</body>
</html>
