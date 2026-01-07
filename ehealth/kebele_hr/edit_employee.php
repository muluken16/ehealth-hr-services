<?php
session_start();
require_once '../db.php';
$conn = getDBConnection();

$id = $_GET['id'] ?? '';
if (!$id) {
    header('Location: hr-employees.php');
    exit;
}

// Fetch employee
$stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ? OR id = ?");
$stmt->bind_param("ss", $id, $id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

if (!$employee) {
    echo "Employee not found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | <?php echo $employee['first_name']; ?></title>
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
        
        .edit-layout {
            max-width: 1100px;
            margin: 40px auto;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
            padding: 0 20px;
        }

        /* Profile Sidebar */
        .profile-card {
            background: white;
            border-radius: 24px;
            padding: 40px 20px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            height: fit-content;
            position: sticky;
            top: 40px;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 800;
            margin: 0 auto 20px;
            box-shadow: 0 10px 20px rgba(26, 74, 95, 0.2);
        }

        .profile-name { font-size: 1.4rem; font-weight: 700; margin-bottom: 5px; }
        .profile-role { color: var(--gray); font-size: 0.9rem; margin-bottom: 20px; }
        
        .nav-sections { text-align: left; margin-top: 30px; }
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

        .status-pill {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-pill.active { background: #d1fae5; color: #065f46; }
        .status-pill.on-leave { background: #fef3c7; color: #92400e; }
        .status-pill.inactive { background: #fee2e2; color: #991b1b; }

        @media (max-width: 900px) {
            .edit-layout { grid-template-columns: 1fr; }
            .profile-card { position: relative; top: 0; margin-bottom: 20px; }
        }

        /* Adjustments for Sidebar Integration */
        .main-content { overflow-y: auto; height: 100vh; }
        .edit-layout { margin: 20px 0; max-width: 100%; }
        .form-content { border: 1px solid #e2e8f0; }

    </style>
</head>
<body>
    <div class="hr-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <?php 
                $page_title = "Edit Employee Profile";
                include 'navbar.php'; 
            ?>

            <div class="content">
                <div class="edit-layout">

        <div class="profile-card">
            <div style="position: relative; display: inline-block;">
                <?php 
                    $initials = substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1); 
                    if (!empty($employee['photo'])) {
                        echo '<img src="../uploads/employees/'.$employee['photo'].'" class="profile-avatar" style="object-fit:cover; border: 4px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">';
                    } else {
                        echo '<div class="profile-avatar" style="border: 4px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">'.strtoupper($initials).'</div>';
                    }
                ?>
                <label for="photoInput" style="position: absolute; bottom: 10px; right: 0; background: var(--secondary); color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <i class="fas fa-camera"></i>
                </label>
            </div>
            <div class="profile-name"><?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?></div>
            <div class="profile-role"><?php echo $employee['position']; ?> • <?php echo $employee['employee_id']; ?></div>
            
            <div class="status-pill <?php echo $employee['status']; ?>">
                <?php echo str_replace('-', ' ', $employee['status']); ?>
            </div>

            <div class="nav-sections">
                <div class="nav-item active" onclick="scrollToSection('personal')"><i class="fas fa-user"></i> Personal Details</div>
                <div class="nav-item" onclick="scrollToSection('employment')"><i class="fas fa-briefcase"></i> Employment</div>
                <div class="nav-item" onclick="scrollToSection('location')"><i class="fas fa-map-marker-alt"></i> Contact & Address</div>
                <div class="nav-item" onclick="scrollToSection('financial')"><i class="fas fa-university"></i> Banking & Finance</div>
                <div class="nav-item" onclick="scrollToSection('warranty')"><i class="fas fa-shield-alt"></i> Warranty & Legal</div>
            </div>
        </div>

        <!-- Form Area -->
        <div class="form-content">
            <form id="editEmployeeForm" enctype="multipart/form-data">
                <input type="hidden" name="employee_id" value="<?php echo $employee['employee_id']; ?>">
                <input type="file" name="photo" id="photoInput" accept="image/*" style="display: none;" onchange="previewProfile(this)">
                
                <!-- Personal -->
                <div id="personal">
                    <div class="section-title"><i class="fas fa-id-card"></i> Personal Information</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control-edit" value="<?php echo $employee['first_name']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" class="form-control-edit" value="<?php echo $employee['middle_name']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control-edit" value="<?php echo $employee['last_name']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender" class="form-control-edit">
                                <option value="male" <?php echo $employee['gender']=='male'?'selected':''; ?>>Male</option>
                                <option value="female" <?php echo $employee['gender']=='female'?'selected':''; ?>>Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control-edit" value="<?php echo $employee['date_of_birth']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Marital Status</label>
                            <select name="marital_status" class="form-control-edit">
                                <option value="single" <?php echo $employee['marital_status']=='single'?'selected':''; ?>>Single</option>
                                <option value="married" <?php echo $employee['marital_status']=='married'?'selected':''; ?>>Married</option>
                                <option value="divorced" <?php echo $employee['marital_status']=='divorced'?'selected':''; ?>>Divorced</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Employment -->
                <div id="employment">
                    <div class="section-title"><i class="fas fa-briefcase"></i> Employment Details</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Position / Title</label>
                            <input type="text" name="position" class="form-control-edit" value="<?php echo $employee['position']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Department Assigned</label>
                            <select name="department_assigned" class="form-control-edit">
                                <option value="medical" <?php echo $employee['department_assigned']=='medical'?'selected':''; ?>>Medical</option>
                                <option value="admin" <?php echo $employee['department_assigned']=='admin'?'selected':''; ?>>Administration</option>
                                <option value="it" <?php echo $employee['department_assigned']=='it'?'selected':''; ?>>IT/Technical</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Join Date</label>
                            <input type="date" name="join_date" class="form-control-edit" value="<?php echo $employee['join_date']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Employment Type</label>
                            <select name="employment_type" class="form-control-edit">
                                <option value="full-time" <?php echo $employee['employment_type']=='full-time'?'selected':''; ?>>Full Time</option>
                                <option value="contract" <?php echo $employee['employment_type']=='contract'?'selected':''; ?>>Contract</option>
                                <option value="part-time" <?php echo $employee['employment_type']=='part-time'?'selected':''; ?>>Part Time</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control-edit">
                                <option value="active" <?php echo $employee['status']=='active'?'selected':''; ?>>Active</option>
                                <option value="on-leave" <?php echo $employee['status']=='on-leave'?'selected':''; ?>>On Leave</option>
                                <option value="inactive" <?php echo $employee['status']=='inactive'?'selected':''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Salary (ETB)</label>
                            <input type="number" name="salary" class="form-control-edit" value="<?php echo $employee['salary']; ?>">
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div id="location">
                    <div class="section-title"><i class="fas fa-map-marked-alt"></i> Contact & Address</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone_number" class="form-control-edit" value="<?php echo $employee['phone_number']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control-edit" value="<?php echo $employee['email']; ?>">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Detailed Address</label>
                            <textarea name="address" class="form-control-edit" rows="2"><?php echo $employee['address']; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Woreda (Residence)</label>
                            <input type="text" name="woreda" class="form-control-edit" value="<?php echo $employee['woreda']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Kebele (Residence)</label>
                            <input type="text" name="kebele" class="form-control-edit" value="<?php echo $employee['kebele']; ?>">
                        </div>
                    </div>
                </div>

                <!-- Banking -->
                <div id="financial">
                    <div class="section-title"><i class="fas fa-university"></i> Banking & Finance</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Bank Name</label>
                            <input type="text" name="bank_name" class="form-control-edit" value="<?php echo $employee['bank_name']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Account Number</label>
                            <input type="text" name="bank_account" class="form-control-edit" value="<?php echo $employee['bank_account']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Credit Status</label>
                            <select name="credit_status" class="form-control-edit" onchange="toggleCreditFile(this)">
                                <option value="good" <?php echo ($employee['credit_status'] ?? 'good') == 'good' ? 'selected' : ''; ?>>Good / No Debt</option>
                                <option value="active" <?php echo ($employee['credit_status'] ?? '') == 'active' ? 'selected' : ''; ?>>Active Credit</option>
                                <option value="bad" <?php echo ($employee['credit_status'] ?? '') == 'bad' ? 'selected' : ''; ?>>Bad / Default</option>
                            </select>
                        </div>
                        <div id="creditFileGroup" class="form-group" style="<?php echo ($employee['credit_status'] ?? '') == 'active' ? '' : 'display: none;'; ?> grid-column: 1/-1;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div>
                                    <label>Credit Status File (Attached)</label>
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <?php if(!empty($employee['loan_file'])): ?>
                                            <a href="../uploads/employees/<?php echo $employee['loan_file']; ?>" target="_blank" class="scan-btn" style="padding: 8px 12px; font-size: 0.8rem;">
                                                <i class="fas fa-file-pdf"></i> View Credit File
                                            </a>
                                        <?php endif; ?>
                                        <input type="file" name="loan_file" accept=".pdf,image/*" class="form-control-edit" style="padding: 9px;">
                                    </div>
                                </div>
                                <div>
                                    <label>Credit Information / Details</label>
                                    <textarea name="credit_details" class="form-control-edit" placeholder="Enter details about the active credit..." style="height: 70px;"><?php echo $employee['credit_details'] ?? ''; ?></textarea>
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
                                 <option value="yes" <?php echo ($employee['warranty_status'] ?? 'yes') == 'yes' ? 'selected' : ''; ?>>Yes - Guarantor Required</option>
                                 <option value="no" <?php echo ($employee['warranty_status'] ?? 'yes') == 'no' ? 'selected' : ''; ?>>No - Not Required</option>
                             </select>
                        </div>

                        <div id="warrantyFields" class="form-grid" style="grid-column: 1 / -1; display: <?php echo ($employee['warranty_status'] ?? 'yes') == 'yes' ? 'grid' : 'none'; ?>; margin-bottom: 0;">
                            <div class="form-group">
                                <label>Guarantor Name</label>
                                <input type="text" name="person_name" class="form-control-edit" value="<?php echo $employee['person_name']; ?>">
                            </div>
                            <div class="form-group">
                                <label>Guarantor Phone</label>
                                <input type="tel" name="phone" class="form-control-edit" value="<?php echo $employee['phone']; ?>">
                            </div>
                            <div class="form-group">
                                <label>Guarantor Woreda</label>
                                <input type="text" name="warranty_woreda" class="form-control-edit" value="<?php echo $employee['warranty_woreda'] ?? ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Guarantor Kebele</label>
                                <input type="text" name="warranty_kebele" class="form-control-edit" value="<?php echo $employee['warranty_kebele'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>FIN / National ID</label>
                                <input type="text" name="fin_id" class="form-control-edit" value="<?php echo $employee['fin_id']; ?>" oninput="toggleFinScan(this)">
                                <div id="finScanGroup" style="margin-top: 10px; <?php echo !empty($employee['fin_id']) ? '' : 'display: none;'; ?>">
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <?php if(!empty($employee['fin_scan'])): ?>
                                            <a href="../uploads/employees/<?php echo $employee['fin_scan']; ?>" target="_blank" class="scan-btn" style="padding: 8px 12px; font-size: 0.75rem; min-width: 100px;">
                                                <i class="fas fa-id-card"></i> View Current
                                            </a>
                                        <?php endif; ?>
                                        <div class="upload-area" onclick="document.getElementById('fin_scan_input').click()" style="padding: 8px; flex: 1; border-style: dashed; background: #f0f7ff; cursor: pointer;">
                                            <i class="fas fa-upload" style="font-size: 0.8rem;"></i> <span style="font-size: 0.75rem;">Attach/Change Scan</span>
                                            <input type="file" id="fin_scan_input" name="fin_scan" accept=".pdf,image/*" style="display:none">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>ID Details</label>
                                <input type="text" name="national_id_details" class="form-control-edit" value="<?php echo $employee['national_id_details'] ?? ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Warranty Agreement</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <?php if(!empty($employee['scan_file'])): ?>
                                        <a href="../uploads/employees/<?php echo $employee['scan_file']; ?>" target="_blank" class="scan-btn" style="padding: 8px 12px; font-size: 0.8rem;">
                                            <i class="fas fa-file-contract"></i> View Agreement
                                        </a>
                                    <?php endif; ?>
                                    <input type="file" name="scan_file" class="form-control-edit" style="padding: 9px; flex: 1;">
                                </div>
                            </div>
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1; margin-top: 20px;">
                            <div style="font-weight: 600; color: var(--primary); margin-bottom: 15px; border-top: 1px solid #f1f5f9; padding-top: 15px;">Legal & Criminal Status</div>
                        </div>
                        
                        <div class="form-group">
                             <label>Criminal Status</label>
                             <select name="criminal_status" class="form-control-edit" onchange="toggleCriminalFile(this)">
                                 <option value="no" <?php echo ($employee['criminal_status'] ?? 'no') == 'no' ? 'selected' : ''; ?>>Clean</option>
                                 <option value="yes" <?php echo ($employee['criminal_status'] ?? 'no') == 'yes' ? 'selected' : ''; ?>>Has Record</option>
                             </select>
                        </div>

                        <div id="criminalFileGroup" class="form-group" style="<?php echo ($employee['criminal_status'] ?? 'no') == 'yes' ? '' : 'display: none;'; ?> grid-column: 1/-1;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div>
                                    <label>Criminal Record File (Photo/Scan)</label>
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <?php if(!empty($employee['criminal_file'])): ?>
                                            <a href="../uploads/employees/<?php echo $employee['criminal_file']; ?>" target="_blank" class="scan-btn" style="padding: 8px 12px; font-size: 0.8rem;">
                                                <i class="fas fa-balance-scale"></i> View File
                                            </a>
                                        <?php endif; ?>
                                        <input type="file" name="criminal_file" accept=".pdf,image/*" class="form-control-edit" style="padding: 9px; flex: 1;">
                                    </div>
                                </div>
                                <div>
                                    <label>Criminal Record Details</label>
                                    <textarea name="criminal_record_details" class="form-control-edit" placeholder="Enter details about the record..." style="height: 70px;"><?php echo $employee['criminal_record_details'] ?? ''; ?></textarea>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>

                <div class="action-bar">
                    <button type="button" onclick="window.location.href='hr-employees.php'" class="btn-cancel">Cancel Changes</button>
                    <div style="display:flex; gap: 15px;">
                        <button type="button" onclick="saveEmployee()" class="btn-confirm">
                            <i class="fas fa-check-double"></i> Update Full Profile
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function saveEmployee() {
            const formData = new FormData(document.getElementById('editEmployeeForm'));
            
            // Visual feedback
            const btn = document.querySelector('.btn-confirm');
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Saving...';
            btn.disabled = true;

            fetch('employee_actions.php?action=edit', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Success Toast or Alert
                    alert('Profile updated successfully!');
                    window.location.href = 'hr-employees.php';
                } else {
                    alert('Error: ' + data.message);
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                }
            })
            .catch(err => {
                alert('Network error. Check connection.');
                btn.innerHTML = originalContent;
                btn.disabled = false;
            });
        }

        function scrollToSection(id) {
            document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
            // Update active nav
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('onclick').includes(id)) item.classList.add('active');
            });
        }

        function previewProfile(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const avatar = document.querySelector('.profile-avatar');
                    if (avatar.tagName === 'IMG') {
                        avatar.src = e.target.result;
                    } else {
                        // Replace div with img
                        const newImg = document.createElement('img');
                        newImg.src = e.target.result;
                        newImg.className = 'profile-avatar';
                        newImg.style.cssText = 'object-fit:cover; border: 4px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.1); width: 120px; height: 120px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;';
                        avatar.replaceWith(newImg);
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
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
