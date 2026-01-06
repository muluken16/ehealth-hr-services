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
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --bg-color: #f3f4f6;
            --card-bg: #ffffff;
            --border-color: #e5e7eb;
            --text-main: #111827;
            --text-secondary: #6b7280;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --radius: 12px;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--text-main);
        }

        .main-content {
            padding-bottom: 60px;
        }

        .header {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 20px 30px;
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.025em;
        }

        .content {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Form Sections */
        .form-section {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            padding: 30px;
            margin-bottom: 25px;
            border: 1px solid var(--border-color);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .form-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .section-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #eef2ff;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-main);
            margin: 0;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .form-label span.required {
            color: #ef4444;
            margin-left: 2px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: #f9fafb;
            font-size: 0.95rem;
            transition: all 0.2s;
            color: var(--text-main);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        /* Floating Actions Bottom Bar */
        .form-actions-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 15px 30px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            z-index: 900;
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.05);
            /* Adjust for sidebar based on layout, assuming full width responsive */
        }
        
        .main-content {
             margin-bottom: 80px; /* Space for fixed footer */
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: white;
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        /* Conditional & Uploads */
        .conditional-field {
            display: none;
            margin-top: 15px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px dashed #cbd5e1;
            animation: slideDown 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .conditional-field.show {
            display: block;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .upload-area {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            background: #f8fafc;
            transition: all 0.2s;
        }

        .upload-area:hover {
            border-color: var(--primary-color);
            background: #eef2ff;
        }
    </style>
</head>
<body>
    <div class="hr-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="header">
                 <div class="header-left">
                    <button class="mobile-menu-btn" id="mobileMenuBtn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">Add New Employee</h1>
                </div>
                <div class="header-actions">
                    <button class="btn-secondary" onclick="window.location.href='hr-employees.php'">
                        <i class="fas fa-arrow-left"></i> Back to Directory
                    </button>
                </div>
            </div>

            <div class="content">
                <form id="employeeForm" enctype="multipart/form-data">
                    
                    <!-- Identification Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon"><i class="fas fa-user"></i></div>
                            <h3 class="section-title">Personal Identification</h3>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">First Name <span class="required">*</span></label>
                                <input type="text" name="first_name" class="form-control" placeholder="e.g. Abebe" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control" placeholder="e.g. Kebede">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last Name <span class="required">*</span></label>
                                <input type="text" name="last_name" class="form-control" placeholder="e.g. Tesfaye" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Gender <span class="required">*</span></label>
                                <select name="gender" class="form-control" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Date of Birth <span class="required">*</span></label>
                                <input type="date" name="date_of_birth" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Religion <span class="required">*</span></label>
                                <select name="religion" class="form-control" required>
                                    <option value="">Select Religion</option>
                                    <option value="christianity">Orthodox</option>
                                    <option value="islam">Islam</option>
                                    <option value="protestant">Protestant</option>
                                    <option value="judaism">Judaism</option>
                                    <option value="hinduism">Hinduism</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                             <div class="form-group">
                                <label class="form-label">Citizenship <span class="required">*</span></label>
                                <select name="citizenship" class="form-control" required onchange="checkOther(this)">
                                    <option value="Ethiopia">Ethiopia</option>
                                    <option value="United States">United States</option>
                                    <option value="Other">Other</option>
                                </select>
                                <input type="text" id="otherCitizenship" name="other_citizenship" class="form-control conditional-field" placeholder="Enter country">
                            </div>
                        </div>
                    </div>

                    <!-- Address / Location Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <h3 class="section-title">Residential Address</h3>
                        </div>
                        <div class="form-row">
                             <div class="form-group"><label class="form-label">Region</label><select id="region" name="region" class="form-control" onchange="loadZones()"><option value="">Select Region</option><option value="amhara">Amhara</option><option value="oromia">Oromia</option><option value="addis_ababa">Addis Ababa</option><option value="other">Other</option></select></div>
                             <div class="form-group"><label class="form-label">Zone</label><select id="zone" name="zone" class="form-control" onchange="loadWoredas()"><option value="">Select Zone</option></select></div>
                        </div>
                        <div class="form-row">
                             <div class="form-group"><label class="form-label">Woreda</label><select id="woreda" name="woreda" class="form-control" onchange="loadKebeles()"><option value="">Select Woreda</option></select></div>
                             <div class="form-group"><label class="form-label">Kebele</label><select id="kebele" name="kebele" class="form-control"><option value="">Select Kebele</option></select></div>
                        </div>
                         <div class="form-group">
                            <label class="form-label">Specific Address / House No.</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="e.g. House No. 123, Street Name..."></textarea>
                        </div>
                    </div>

                    <!-- Academic Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon"><i class="fas fa-graduation-cap"></i></div>
                            <h3 class="section-title">Academic Background</h3>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Highest Education Level</label>
                            <select name="education_level" class="form-control">
                                <option value="">Select Level</option>
                                <option value="primary">Primary</option>
                                <option value="secondary">Secondary</option>
                                <option value="diploma">Diploma</option>
                                <option value="bachelor">Bachelor's Degree</option>
                                <option value="master">Master's Degree</option>
                                <option value="phd">PhD</option>
                            </select>
                        </div>
                        <div class="form-row">
                             <div class="form-group"><label class="form-label">University / College</label><input type="text" name="university" class="form-control" placeholder="Institution Name"></div>
                             <div class="form-group"><label class="form-label">Field of Study</label><input type="text" name="department" class="form-control" placeholder="Major / Department"></div>
                        </div>
                    </div>

                    <!-- Work Info Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon"><i class="fas fa-briefcase"></i></div>
                            <h3 class="section-title">Employment Details</h3>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                 <label class="form-label">Department Assigned <span class="required">*</span></label>
                                 <select name="department_assigned" class="form-control" required>
                                     <option value="">Select Department</option>
                                     <option value="medical">Medical</option>
                                     <option value="administration">Administration</option>
                                     <option value="technical">Technical</option>
                                     <option value="support">Support</option>
                                 </select>
                            </div>
                            <div class="form-group">
                                 <label class="form-label">Position Title <span class="required">*</span></label>
                                 <input type="text" name="position" class="form-control" required placeholder="e.g. Senior Nurse">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                 <label class="form-label">Join Date</label>
                                 <input type="date" name="join_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                 <label class="form-label">Monthly Salary (ETB)</label>
                                 <input type="number" name="salary" class="form-control" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                     <!-- Bank Info Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon"><i class="fas fa-university"></i></div>
                            <h3 class="section-title">Financial Information</h3>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bank Name</label>
                            <select id="bankName" name="bank_name" class="form-control" onchange="toggleBankAccountField()">
                                <option value="">Select Bank</option>
                                <option value="commercial_bank">Commercial Bank of Ethiopia</option>
                                <option value="dashen_bank">Dashen Bank</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group conditional-field" id="bankAccountDiv">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="bank_account" class="form-control" placeholder="Enter account number">
                        </div>
                    </div>

                    <!-- Warranty / Guarantor Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon"><i class="fas fa-shield-alt"></i></div>
                            <h3 class="section-title">Warranty & Guarantor</h3>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Has Warranty/Guarantor?</label>
                            <select id="warranty_status" name="warranty_status" class="form-control" onchange="toggleWarrantyFields()">
                                <option value="no">No</option>
                                <option value="yes">Yes</option>
                            </select>
                        </div>
                        <div class="conditional-field" id="warranty_fields">
                            <div class="form-row">
                                 <div class="form-group"><label class="form-label">Guarantor Name</label><input type="text" name="person_name" class="form-control"></div>
                                 <div class="form-group"><label class="form-label">Guarantor Phone</label><input type="text" name="phone" class="form-control"></div>
                            </div>
                            <div class="form-group">
                                 <label class="form-label">Upload Warranty Agreement</label>
                                 <div class="upload-area" onclick="document.getElementById('warranty_file').click()">
                                     <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 10px;"></i>
                                     <p style="margin:0; color:#64748b;">Click to upload PDF or Image</p>
                                     <input type="file" id="warranty_file" name="scan_file" style="display:none">
                                 </div>
                            </div>
                        </div>
                    </div>

                    <!-- Files Section -->
                    <div class="form-section">
                        <div class="section-header">
                             <div class="section-icon"><i class="fas fa-folder-open"></i></div>
                             <h3 class="section-title">Documents</h3>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Upload Additional Documents (IDs, Certificates)</label>
                             <div class="upload-area" onclick="document.getElementById('multi_docs').click()">
                                 <i class="fas fa-copy" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 10px;"></i>
                                 <p style="margin:0; color:#64748b;">Click to select multiple files</p>
                                 <input type="file" id="multi_docs" name="documents[]" multiple style="display:none">
                             </div>
                            <small style="display:block; margin-top:10px; color:#94a3b8;">Supported formats: PDF, JPG, PNG</small>
                        </div>
                    </div>

                    <!-- Contact Section -->
                     <div class="form-section">
                        <div class="section-header">
                             <div class="section-icon"><i class="fas fa-address-book"></i></div>
                             <h3 class="section-title">Contact Details</h3>
                        </div>
                         <div class="form-row">
                            <div class="form-group"><label class="form-label">Email Address <span class="required">*</span></label><input type="email" name="email" class="form-control" required placeholder="email@example.com"></div>
                            <div class="form-group"><label class="form-label">Phone Number</label><input type="text" name="phone_number" class="form-control" placeholder="+251..."></div>
                        </div>
                    </div>

                    <!-- Floating Actions Bar -->
                    <div class="form-actions-bar">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='hr-employees.php'">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle"></i> Register Employee</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="scripts.js"></script>
    <script>
        // Copy relevant helpers from edit_employee.php logic
        function checkOther(select) {
            document.getElementById('otherCitizenship').style.display = (select.value === 'Other') ? 'block' : 'none';
        }
        function toggleBankAccountField() {
            const val = document.getElementById('bankName').value;
            const div = document.getElementById('bankAccountDiv');
            if(val) div.classList.add('show'); else div.classList.remove('show');
        }
        function toggleWarrantyFields() {
             const val = document.getElementById('warranty_status').value;
             const div = document.getElementById('warranty_fields');
             if(val === 'yes') div.classList.add('show'); else div.classList.remove('show');
        }

        // Region/Zone/Woreda mocking
        const amharaData = { 
            "North Wollo": ["Woldiya","Lalibela"], 
            "South Wollo": ["Dessie", "Kombolcha"],
            "Addis Ababa": ["Bole", "Yeka"]
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
                  amharaData["Addis Ababa"].forEach(wd => w.add(new Option(wd, wd)));
             }
        }
        function loadKebeles() {
             const k = document.getElementById('kebele');
             k.innerHTML = '<option value="">Select Kebele</option>';
             for(let i=1; i<=20; i++) k.add(new Option('Kebele '+i, 'Kebele '+i));
        }

        // Submission
        document.getElementById('employeeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.querySelector('.submit-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            btn.disabled = true;

            const formData = new FormData(this);
            fetch('employee_actions.php?action=add', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    alert('Employee Registered Successfully!');
                    window.location.href = 'hr-employees.php';
                } else {
                    alert('Error: ' + res.message);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(err => {
                alert('Network Error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    </script>
</body>
</html>
