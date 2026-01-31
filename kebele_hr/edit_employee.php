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
if (is_numeric($id)) {
    $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->bind_param("i", $id);
} else {
    $stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
    $stmt->bind_param("s", $id);
}
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    // Debug: Show what went wrong
    die("Employee not found with ID: " . htmlspecialchars($id) . "<br><a href='hr-employees.php'>Go Back</a>");
}

// Helper function to safely echo values
function safeEcho($value, $default = '')
{
    echo isset($value) && $value !== '' ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $default;
}

function displayFiles($jsonStr, $icon = 'fa-file')
{
    if (empty($jsonStr))
        return;
    $files = json_decode($jsonStr, true);
    if (!is_array($files)) {
        // Fallback for old single files
        echo ' <a href="../uploads/employees/' . htmlspecialchars($jsonStr) . '" target="_blank" class="scan-btn" style="padding: 8px 12px; font-size: 0.8rem;"><i class="fas ' . $icon . '"></i> View File</a>';
        return;
    }
    foreach ($files as $file) {
        echo ' <a href="../uploads/employees/' . htmlspecialchars($file) . '" target="_blank" class="scan-btn" style="padding: 8px 12px; font-size: 0.8rem; margin-bottom:5px;"><i class="fas ' . $icon . '"></i> View File</a>';
    }
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

        body {
            background: #f1f5f9;
            font-family: 'Inter', sans-serif;
            color: #1e293b;
        }

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
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
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

        .profile-name {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .profile-role {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .nav-sections {
            text-align: left;
            margin-top: 30px;
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

        .form-control-edit {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background: #f8fafc;
        }

        .btn-clear-files {
            background: #fef2f2;
            color: #ef4444;
            border: 1px solid #fee2e2;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 10px;
            transition: all 0.2s;
        }

        .btn-clear-files:hover {
            background: #fee2e2;
        }

        .file-item-preview {
            background: #f1f5f9;
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #475569;
            font-size: 0.85rem;
        }

        .form-control-edit:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(26, 74, 95, 0.05);
            background: white;
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

        .status-pill {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-pill.active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-pill.on-leave {
            background: #fef3c7;
            color: #92400e;
        }

        .status-pill.inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 900px) {
            .edit-layout {
                grid-template-columns: 1fr;
            }

            .profile-card {
                position: relative;
                top: 0;
                margin-bottom: 20px;
            }
        }

        /* Adjustments for Sidebar Integration */
        .main-content {
            overflow-y: auto;
            height: 100vh;
        }

        .edit-layout {
            margin: 20px 0;
            max-width: 100%;
        }

        .form-content {
            border: 1px solid #e2e8f0;
        }
    </style>
</head>

<body>
    <div class="hr-container">
        <!-- Location Data -->
        <script src="locations.js"></script>

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
                                echo '<img src="../uploads/employees/' . $employee['photo'] . '" class="profile-avatar" style="object-fit:cover; border: 4px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">';
                            } else {
                                echo '<div class="profile-avatar" style="border: 4px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">' . strtoupper($initials) . '</div>';
                            }
                            ?>
                            <label for="photoInput"
                                style="position: absolute; bottom: 10px; right: 0; background: var(--secondary); color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                <i class="fas fa-camera"></i>
                            </label>
                        </div>
                        <div class="profile-name"><?php safeEcho($employee['first_name']); ?>
                            <?php safeEcho($employee['last_name']); ?>
                        </div>
                        <div class="profile-role"><?php safeEcho($employee['position']); ?> •
                            <?php safeEcho($employee['employee_id']); ?>
                        </div>

                        <div class="status-pill <?php echo $employee['status']; ?>">
                            <?php echo str_replace('-', ' ', $employee['status']); ?>
                        </div>

                        <div class="nav-sections">
                            <div class="nav-item active" onclick="scrollToSection('personal')"><i
                                    class="fas fa-user-circle"></i> Personal</div>
                            <div class="nav-item" onclick="scrollToSection('education')"><i
                                    class="fas fa-graduation-cap"></i> Education</div>
                            <div class="nav-item" onclick="scrollToSection('employment')"><i
                                    class="fas fa-briefcase"></i> Employment</div>
                            <div class="nav-item" onclick="scrollToSection('location')"><i
                                    class="fas fa-map-marker-alt"></i> Address</div>
                            <div class="nav-item" onclick="scrollToSection('financial')"><i
                                    class="fas fa-university"></i> Banking</div>
                            <div class="nav-item" onclick="scrollToSection('warranty')"><i
                                    class="fas fa-shield-halved"></i> Warranty</div>
                            <div class="nav-item" onclick="scrollToSection('legal')"><i
                                    class="fas fa-gavel"></i> Legal</div>
                            <div class="nav-item" onclick="scrollToSection('documents')"><i
                                    class="fas fa-folder-open"></i> Documents</div>
                        </div>
                    </div>

                    <!-- Form Area -->
                    <div class="form-content">
                        <form id="editEmployeeForm" enctype="multipart/form-data">
                            <input type="hidden" name="employee_id" value="<?php echo $employee['employee_id']; ?>">
                            <input type="file" name="photo" id="photoInput" accept="image/*" style="display: none;"
                                onchange="previewProfile(this)">

                            <!-- Personal -->
                            <div id="personal">
                                <div class="section-title"><i class="fas fa-id-card"></i> Personal Information</div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>First Name</label>
                                        <input type="text" name="first_name" class="form-control-edit"
                                            value="<?php safeEcho($employee['first_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Middle Name</label>
                                        <input type="text" name="middle_name" class="form-control-edit"
                                            value="<?php safeEcho($employee['middle_name']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Last Name</label>
                                        <input type="text" name="last_name" class="form-control-edit"
                                            value="<?php safeEcho($employee['last_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Gender</label>
                                        <select name="gender" class="form-control-edit">
                                            <option value="male" <?php echo $employee['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                                            <option value="female" <?php echo $employee['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Date of Birth</label>
                                        <input type="date" name="date_of_birth" class="form-control-edit"
                                            value="<?php safeEcho($employee['date_of_birth']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Marital Status</label>
                                        <select name="marital_status" class="form-control-edit">
                                            <option value="single" <?php echo $employee['marital_status'] == 'single' ? 'selected' : ''; ?>>Single</option>
                                            <option value="married" <?php echo $employee['marital_status'] == 'married' ? 'selected' : ''; ?>>Married</option>
                                            <option value="divorced" <?php echo $employee['marital_status'] == 'divorced' ? 'selected' : ''; ?>>Divorced</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Religion</label>
                                        <?php
                                        $religions = ["Orthodox", "Islam", "Protestant", "Catholic"];
                                        $cur_rel = $employee['religion'] ?? '';
                                        $is_other_rel = !empty($cur_rel) && !in_array($cur_rel, $religions);
                                        ?>
                                        <select name="religion" class="form-control-edit" onchange="checkOtherReligion(this)">
                                            <option value="">Select Religion</option>
                                            <?php foreach ($religions as $r): ?>
                                                <option value="<?php echo $r; ?>" <?php echo $cur_rel == $r ? 'selected' : ''; ?>><?php echo $r; ?></option>
                                            <?php endforeach; ?>
                                            <option value="other" <?php echo $is_other_rel ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                        <input type="text" id="otherReligion" name="other_religion" class="form-control-edit conditional-field <?php echo $is_other_rel ? 'show' : ''; ?>" value="<?php echo $is_other_rel ? safeEcho($cur_rel) : ''; ?>" placeholder="Enter religion">
                                    </div>
                                    <div class="form-group">
                                        <label>Citizenship</label>
                                        <?php
                                        $citizenships = ["Ethiopia", "United States"];
                                        $cur_cit = $employee['citizenship'] ?? '';
                                        $is_other_cit = !empty($cur_cit) && !in_array($cur_cit, $citizenships);
                                        ?>
                                        <select name="citizenship" class="form-control-edit" onchange="checkOtherCitizenship(this)">
                                            <option value="Ethiopia" <?php echo $cur_cit == 'Ethiopia' ? 'selected' : ''; ?>>Ethiopia</option>
                                            <option value="United States" <?php echo $cur_cit == 'United States' ? 'selected' : ''; ?>>United States</option>
                                            <option value="Other" <?php echo $is_other_cit ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                        <input type="text" id="otherCitizenship" name="other_citizenship" class="form-control-edit conditional-field <?php echo $is_other_cit ? 'show' : ''; ?>" value="<?php echo $is_other_cit ? safeEcho($cur_cit) : ''; ?>" placeholder="Enter country">
                                    </div>
                                    <div class="form-group">
                                        <label>Primary Language</label>
                                        <?php
                                        $langs = ["amharic", "oromo", "tigrigna", "english"];
                                        $cur_lang = $employee['language'] ?? '';
                                        $is_other_lang = !empty($cur_lang) && !in_array($cur_lang, $langs);
                                        ?>
                                        <select name="language" class="form-control-edit" onchange="checkOtherLanguage(this)">
                                            <option value="amharic" <?php echo $cur_lang == 'amharic' ? 'selected' : ''; ?>>Amharic</option>
                                            <option value="oromo" <?php echo $cur_lang == 'oromo' ? 'selected' : ''; ?>>Afaan Oromo</option>
                                            <option value="tigrigna" <?php echo $cur_lang == 'tigrigna' ? 'selected' : ''; ?>>Tigrigna</option>
                                            <option value="english" <?php echo $cur_lang == 'english' ? 'selected' : ''; ?>>English</option>
                                            <option value="other" <?php echo $is_other_lang ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                        <input type="text" id="otherLanguage" name="other_language" class="form-control-edit conditional-field <?php echo $is_other_lang ? 'show' : ''; ?>" value="<?php echo $is_other_lang ? safeEcho($cur_lang) : ''; ?>" placeholder="Enter language">
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
                                            <option value="diploma" <?php echo ($employee['education_level'] ?? '') == 'diploma' ? 'selected' : ''; ?>>Diploma</option>
                                            <option value="bachelor" <?php echo ($employee['education_level'] ?? '') == 'bachelor' ? 'selected' : ''; ?>>Bachelor's Degree</option>
                                            <option value="master" <?php echo ($employee['education_level'] ?? '') == 'master' ? 'selected' : ''; ?>>Master's Degree</option>
                                            <option value="phd" <?php echo ($employee['education_level'] ?? '') == 'phd' ? 'selected' : ''; ?>>PhD</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Institution</label>
                                        <input type="text" name="university" class="form-control-edit" value="<?php safeEcho($employee['university'] ?? ''); ?>" placeholder="University/College">
                                    </div>
                                    <div class="form-group">
                                        <label>Field of Study</label>
                                        <input type="text" name="department" class="form-control-edit" value="<?php safeEcho($employee['department'] ?? ''); ?>" placeholder="e.g. Computer Science">
                                    </div>
                                    <div class="form-group">
                                        <label>Secondary School</label>
                                        <input type="text" name="secondary_school" class="form-control-edit" value="<?php safeEcho($employee['secondary_school'] ?? ''); ?>" placeholder="High School Name">
                                    </div>
                                    <div class="form-group">
                                        <label>Certificate(s)</label>
                                        <div style="display: flex; flex-direction: column; gap: 10px;">
                                            <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                                <?php displayFiles($employee['education_file'] ?? '', 'fa-graduation-cap'); ?>
                                            </div>
                                            <div class="upload-area" onclick="document.getElementById('edu_files_edit').click()">
                                                <i class="fas fa-cloud-upload-alt"></i> Select Document(s)
                                                <input type="file" id="edu_files_edit" name="education_files[]" multiple style="display:none" onchange="handleFileList(this, 'edu_edit_list')">
                                            </div>
                                            <div id="edu_edit_list" style="font-size: 0.8rem; color: var(--secondary);"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Employment -->
                            <div id="employment">
                                <div class="section-title"><i class="fas fa-briefcase"></i> Employment Details</div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>Position / Title</label>
                                        <input type="text" name="position" class="form-control-edit"
                                            value="<?php safeEcho($employee['position']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Department Assigned</label>
                                        <?php
                                        $depts = [
                                            "Outpatient Department (OPD)",
                                            "Emergency / Casualty",
                                            "Maternal and Child Health (MCH)",
                                            "Antenatal Care (ANC)",
                                            "Delivery / Labor Ward",
                                            "Postnatal Care (PNC)",
                                            "Family Planning",
                                            "Expanded Program on Immunization (EPI)",
                                            "Tuberculosis (TB) Clinic",
                                            "HIV/AIDS Care and Treatment (ART Clinic)",
                                            "Pharmacy",
                                            "Laboratory",
                                            "Nutrition Unit",
                                            "Medical Records / Health Information Management",
                                            "Administration and Finance"
                                        ];
                                        $current_dept = $employee['department_assigned'] ?? '';
                                        $is_other = !empty($current_dept) && !in_array($current_dept, $depts);
                                        ?>
                                        <select name="department_assigned" class="form-control-edit"
                                            onchange="toggleOtherField(this, 'other_dept_group_edit')">
                                            <option value="">Select Department</option>
                                            <?php foreach ($depts as $d): ?>
                                                <option value="<?php echo $d; ?>" <?php echo $current_dept == $d ? 'selected' : ''; ?>><?php echo $d; ?></option>
                                            <?php endforeach; ?>
                                            <option value="other" <?php echo $is_other ? 'selected' : ''; ?>>Other
                                                (please specify)</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="other_dept_group_edit"
                                        style="display: <?php echo $is_other ? 'block' : 'none'; ?>;">
                                        <label>Specify Department <span class="required">*</span></label>
                                        <input type="text" name="other_department_assigned" class="form-control-edit"
                                            value="<?php echo $is_other ? safeEcho($current_dept) : ''; ?>"
                                            placeholder="Enter department name">
                                    </div>
                                    <div class="form-group">
                                        <label>Join Date</label>
                                        <input type="date" name="join_date" class="form-control-edit"
                                            value="<?php safeEcho($employee['join_date']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Employment Type</label>
                                        <?php
                                        $emp_types = ["full-time", "contract", "part-time"];
                                        $current_emp_type = $employee['employment_type'] ?? '';
                                        $is_other_type = !empty($current_emp_type) && !in_array($current_emp_type, $emp_types);
                                        ?>
                                        <select name="employment_type" class="form-control-edit"
                                            onchange="toggleOtherField(this, 'other_emp_type_group_edit')">
                                            <option value="full-time" <?php echo $current_emp_type == 'full-time' ? 'selected' : ''; ?>>Full Time</option>
                                            <option value="contract" <?php echo $current_emp_type == 'contract' ? 'selected' : ''; ?>>Contract</option>
                                            <option value="part-time" <?php echo $current_emp_type == 'part-time' ? 'selected' : ''; ?>>Part Time</option>
                                            <option value="other" <?php echo $is_other_type ? 'selected' : ''; ?>>Other
                                                (please specify)</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="other_emp_type_group_edit"
                                        style="display: <?php echo $is_other_type ? 'block' : 'none'; ?>;">
                                        <label>Specify Employment Type <span class="required">*</span></label>
                                        <input type="text" name="other_employment_type" class="form-control-edit"
                                            value="<?php echo $is_other_type ? safeEcho($current_emp_type) : ''; ?>"
                                            placeholder="Enter employment type">
                                    </div>
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control-edit">
                                            <option value="active" <?php echo $employee['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="on-leave" <?php echo $employee['status'] == 'on-leave' ? 'selected' : ''; ?>>On Leave</option>
                                            <option value="inactive" <?php echo $employee['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Salary (ETB)</label>
                                        <input type="number" name="salary" class="form-control-edit"
                                            value="<?php safeEcho($employee['salary']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>National ID Number</label>
                                        <input type="text" name="fin_id" class="form-control-edit"
                                            value="<?php safeEcho($employee['fin_id'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>National ID Scan(s)</label>
                                        <div style="display: flex; flex-direction: column; gap: 10px;">
                                            <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                                <?php displayFiles($employee['fin_scan'] ?? '', 'fa-id-card'); ?>
                                            </div>
                                            <div class="upload-area" onclick="document.getElementById('fin_scan_edit_input').click()">
                                                <i class="fas fa-id-card"></i> Select ID(s)
                                                <input type="file" id="fin_scan_edit_input" name="fin_scan[]" multiple style="display:none" onchange="handleFileList(this, 'fin_edit_list')">
                                            </div>
                                            <div id="fin_edit_list" style="font-size: 0.8rem; color: var(--secondary);"></div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Employment Agreements / Contracts</label>
                                        <div style="display: flex; flex-direction: column; gap: 10px;">
                                            <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                                <?php displayFiles($employee['employment_agreement'] ?? '', 'fa-file-signature'); ?>
                                            </div>
                                            <div class="upload-area" onclick="document.getElementById('contract_edit_input').click()">
                                                <i class="fas fa-file-contract"></i> Select Contract(s)
                                                <input type="file" id="contract_edit_input" name="employment_agreements[]" multiple style="display:none" onchange="handleFileList(this, 'contract_edit_list')">
                                            </div>
                                            <div id="contract_edit_list" style="font-size: 0.8rem; color: var(--secondary);"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Location -->
                            <div id="location">
                                <div class="section-title"><i class="fas fa-map-marked-alt"></i> Contact & Address</div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>Phone Number</label>
                                        <input type="tel" name="phone_number" class="form-control-edit"
                                            value="<?php safeEcho($employee['phone_number']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Email Address</label>
                                        <input type="email" name="email" class="form-control-edit"
                                            value="<?php safeEcho($employee['email']); ?>">
                                    </div>
                                    <div class="form-group" style="grid-column: 1 / -1;">
                                        <label>Detailed Address</label>
                                        <textarea name="address" class="form-control-edit"
                                            rows="2"><?php safeEcho($employee['address']); ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Region</label>
                                        <select id="region" name="region" class="form-control-edit"
                                            onchange="loadZones()">
                                            <option value="">Select Region</option>
                                            <option value="addis_ababa">Addis Ababa</option>
                                            <option value="afar">Afar</option>
                                            <option value="amhara">Amhara</option>
                                            <option value="benishangul_gumuz">Benishangul-Gumuz</option>
                                            <option value="dire_dawa">Dire Dawa</option>
                                            <option value="gambela">Gambela</option>
                                            <option value="harari">Harari</option>
                                            <option value="oromia">Oromia</option>
                                            <option value="sidama">Sidama</option>
                                            <option value="somali">Somali</option>
                                            <option value="south_ethiopia">South Ethiopia</option>
                                            <option value="south_west_ethiopia">South West Ethiopia</option>
                                            <option value="central_ethiopia">Central Ethiopia</option>
                                            <option value="tigray">Tigray</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Zone</label>
                                        <select id="zone" name="zone" class="form-control-edit"
                                            onchange="loadWoredas()">
                                            <option value="">Select Zone</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Woreda</label>
                                        <select id="woreda" name="woreda" class="form-control-edit"
                                            onchange="loadKebeles()">
                                            <option value="">Select Woreda</option>
                                            <input type="hidden" id="initial_woreda"
                                                value="<?php safeEcho($employee['woreda']); ?>">
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Kebele</label>
                                        <select id="kebele" name="kebele" class="form-control-edit">
                                            <option value="">Select Kebele</option>
                                        </select>
                                    </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Banking -->
                            <div id="financial">
                                <div class="section-title"><i class="fas fa-university"></i> Banking & Finance</div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>Bank Name</label>
                                        <input type="text" name="bank_name" class="form-control-edit" value="<?php safeEcho($employee['bank_name']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Account Number</label>
                                        <input type="text" name="bank_account" class="form-control-edit" value="<?php safeEcho($employee['bank_account']); ?>">
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
                                        <div style="background: #fffcf5; padding: 20px; border-radius: 12px; border: 1px solid #fef3c7;">
                                            <div style="font-weight:700; color:#92400e; margin-bottom:15px;"><i class="fas fa-info-circle"></i> Active Loan Details</div>
                                            <div class="form-grid" style="margin-bottom: 20px;">
                                                <div class="form-group">
                                                    <label>Lender</label>
                                                    <input type="text" name="loan_lender" class="form-control-edit" value="<?php safeEcho($employee['loan_lender'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Loan Type</label>
                                                    <input type="text" name="loan_type" class="form-control-edit" value="<?php safeEcho($employee['loan_type'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Total Amount (ETB)</label>
                                                    <input type="number" name="loan_amount" class="form-control-edit" value="<?php safeEcho($employee['loan_amount'] ?? '0'); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Remaining (ETB)</label>
                                                    <input type="number" name="remaining_balance" class="form-control-edit" value="<?php safeEcho($employee['remaining_balance'] ?? '0'); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Monthly Pay (ETB)</label>
                                                    <input type="number" name="monthly_payment" class="form-control-edit" value="<?php safeEcho($employee['monthly_payment'] ?? '0'); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>End Date</label>
                                                    <input type="date" name="loan_end_date" class="form-control-edit" value="<?php safeEcho($employee['loan_end_date'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            
                                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                                                <div class="form-group">
                                                    <label>Credit Information / Details</label>
                                                    <textarea name="credit_details" class="form-control-edit" style="height: 100px;"><?php safeEcho($employee['credit_details'] ?? ''); ?></textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label>Credit Status File(s) (Attached)</label>
                                                    <div style="display: flex; flex-direction: column; gap: 10px;">
                                                        <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                                            <?php displayFiles($employee['loan_file'] ?? '', 'fa-file-pdf'); ?>
                                                        </div>
                                                        <div class="upload-area" onclick="document.getElementById('loan_edit_input').click()" style="padding:15px;">
                                                            <i class="fas fa-file-invoice-dollar"></i> Select File(s)
                                                            <input type="file" id="loan_edit_input" name="loan_file[]" multiple accept=".pdf,image/*" style="display:none" onchange="handleFileList(this, 'loan_edit_list')">
                                                        </div>
                                                        <div id="loan_edit_list" style="font-size: 0.8rem; color: var(--secondary);"></div>
                                                    </div>
                                                </div>
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
                                            <option value="no" <?php echo ($employee['warranty_status'] ?? 'no') == 'no' ? 'selected' : ''; ?>>No - Not Required</option>
                                            <option value="yes" <?php echo ($employee['warranty_status'] ?? 'no') == 'yes' ? 'selected' : ''; ?>>Yes - Guarantor Required</option>
                                        </select>
                                    </div>

                                    <div id="warrantyFields" class="form-grid" style="grid-column: 1 / -1; display: <?php echo ($employee['warranty_status'] ?? 'no') == 'yes' ? 'grid' : 'none'; ?>; margin-bottom: 0;">
                                        <div style="grid-column: 1/-1; background: #f0fdfa; padding: 20px; border-radius: 12px; border: 1px solid #ccfbf1;">
                                            <div style="font-weight:700; color:#0f766e; margin-bottom:15px;"><i class="fas fa-user-shield"></i> Guarantor Information</div>
                                            <div class="form-grid">
                                                <div class="form-group">
                                                    <label>Guarantor Name</label>
                                                    <input type="text" name="person_name" class="form-control-edit" value="<?php safeEcho($employee['person_name']); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Guarantor Phone</label>
                                                    <input type="tel" name="phone" class="form-control-edit" value="<?php safeEcho($employee['phone']); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Guarantor Woreda</label>
                                                    <input type="text" name="warranty_woreda" class="form-control-edit" value="<?php safeEcho($employee['warranty_woreda'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Guarantor Kebele</label>
                                                    <input type="text" name="warranty_kebele" class="form-control-edit" value="<?php safeEcho($employee['warranty_kebele'] ?? ''); ?>">
                                                </div>
                                            </div>

                                            <div style="font-weight:700; color:#0f766e; margin: 20px 0 15px;"><i class="fas fa-id-card"></i> Additional ID Details</div>
                                            <div class="form-grid">
                                                <div class="form-group">
                                                    <label>Relationship</label>
                                                    <input type="text" name="person_relationship" class="form-control-edit" value="<?php safeEcho($employee['person_relationship'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>National ID (FIN)</label>
                                                    <input type="text" name="fin_id" class="form-control-edit" value="<?php safeEcho($employee['fin_id'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Court Status</label>
                                                    <select name="warranty_court_status" class="form-control-edit">
                                                        <option value="clean" <?php echo ($employee['warranty_court_status'] ?? 'clean') == 'clean' ? 'selected' : ''; ?>>CLEAN</option>
                                                        <option value="has_record" <?php echo ($employee['warranty_court_status'] ?? '') == 'has_record' ? 'selected' : ''; ?>>HAS RECORD</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Additional ID Notes</label>
                                                    <input type="text" name="national_id_details" class="form-control-edit" value="<?php safeEcho($employee['national_id_details'] ?? ''); ?>">
                                                </div>
                                            </div>

                                            <div style="font-weight:700; color:#0f766e; margin: 20px 0 15px;"><i class="fas fa-file-contract"></i> Warranty Terms</div>
                                            <div class="form-grid">
                                                <div class="form-group">
                                                    <label>Type</label>
                                                    <input type="text" name="warranty_type" class="form-control-edit" value="<?php safeEcho($employee['warranty_type'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Amount / Value (ETB)</label>
                                                    <input type="number" name="warranty_amount" class="form-control-edit" value="<?php safeEcho($employee['warranty_amount'] ?? '0'); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Start Date</label>
                                                    <input type="date" name="warranty_start_date" class="form-control-edit" value="<?php safeEcho($employee['warranty_start_date'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Expiry Date</label>
                                                    <input type="date" name="warranty_end_date" class="form-control-edit" value="<?php safeEcho($employee['warranty_end_date'] ?? ''); ?>">
                                                </div>
                                            </div>

                                            <div class="form-group" style="margin-top:20px;">
                                                <label>Warranty Agreement Document(s)</label>
                                                <div style="display: flex; flex-direction: column; gap: 10px;">
                                                    <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                                        <?php displayFiles($employee['scan_file'] ?? '', 'fa-file-contract'); ?>
                                                    </div>
                                                    <div class="upload-area" onclick="document.getElementById('warranty_edit_file').click()" style="padding: 15px; border-style: dashed; background: white;">
                                                        <i class="fas fa-paperclip" style="font-size: 1.5rem; margin-bottom: 10px; color: #0891b2;"></i>
                                                        <div style="font-size: 0.9rem;">Upload Agreement Documents</div>
                                                        <input type="file" id="warranty_edit_file" name="scan_file[]" multiple style="display:none" onchange="handleFileList(this, 'warranty_edit_list')">
                                                    </div>
                                                    <div id="warranty_edit_list" style="font-size: 0.8rem; color: var(--secondary);"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group" style="grid-column: 1 / -1; margin-top: 20px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                                        <div style="font-weight: 700; color: var(--primary); margin-bottom: 15px;"><i class="fas fa-gavel"></i> Legal & Criminal Status</div>
                                    </div>

                                    <div class="form-group">
                                        <label>Criminal Status</label>
                                        <select name="criminal_status" class="form-control-edit" onchange="toggleCriminalFile(this)">
                                            <option value="no" <?php echo ($employee['criminal_status'] ?? 'no') == 'no' ? 'selected' : ''; ?>>Clean</option>
                                            <option value="yes" <?php echo ($employee['criminal_status'] ?? 'no') == 'yes' ? 'selected' : ''; ?>>Has Record</option>
                                        </select>
                                    </div>

                                    <div id="criminalFileGroup" class="form-group" style="<?php echo ($employee['criminal_status'] ?? 'no') == 'yes' ? '' : 'display: none;'; ?> grid-column: 1/-1;">
                                        <div style="background: #fef2f2; padding: 20px; border-radius: 12px; border: 1px solid #fee2e2;">
                                            <div style="font-weight:700; color:#b91c1c; margin-bottom:15px;"><i class="fas fa-balance-scale"></i> Case Information</div>
                                            <div class="form-grid">
                                                <div class="form-group">
                                                    <label>Type</label>
                                                    <input type="text" name="criminal_type" class="form-control-edit" value="<?php safeEcho($employee['criminal_type'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Date</label>
                                                    <input type="date" name="criminal_date" class="form-control-edit" value="<?php safeEcho($employee['criminal_date'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Court</label>
                                                    <input type="text" name="criminal_court" class="form-control-edit" value="<?php safeEcho($employee['criminal_court'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Sentence</label>
                                                    <input type="text" name="criminal_sentence" class="form-control-edit" value="<?php safeEcho($employee['criminal_sentence'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label>Case Status</label>
                                                    <select name="criminal_status_current" class="form-control-edit">
                                                        <option value="">Select Status</option>
                                                        <option value="Open" <?php echo ($employee['criminal_status_current'] ?? '') == 'Open' ? 'selected' : ''; ?>>Open</option>
                                                        <option value="Ongoing" <?php echo ($employee['criminal_status_current'] ?? '') == 'Ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                                        <option value="Closed" <?php echo ($employee['criminal_status_current'] ?? '') == 'Closed' ? 'selected' : ''; ?>>Closed</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Notes</label>
                                                    <textarea name="criminal_record_details" class="form-control-edit" style="height: 38px;"><?php safeEcho($employee['criminal_record_details'] ?? ''); ?></textarea>
                                                </div>
                                            </div>

                                            <div class="form-group" style="margin-top:20px;">
                                                <label>Case File(s) (Photo/Scan)</label>
                                                <div style="display: flex; flex-direction: column; gap: 10px;">
                                                    <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                                        <?php displayFiles($employee['criminal_file'] ?? '', 'fa-balance-scale'); ?>
                                                    </div>
                                                    <div class="upload-area" onclick="document.getElementById('criminal_edit_file_input').click()" style="padding: 15px; border-style: dashed; background: white;">
                                                        <i class="fas fa-file-alt" style="font-size: 1.5rem; margin-bottom: 10px; color: #dc2626;"></i>
                                                        <div style="font-size: 0.9rem;">Upload Case Documents</div>
                                                        <input type="file" id="criminal_edit_file_input" name="criminal_file[]" multiple accept=".pdf,image/*" style="display:none" onchange="handleFileList(this, 'criminal_edit_list')">
                                                    </div>
                                                    <div id="criminal_edit_list" style="font-size: 0.8rem; color: var(--secondary);"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Documents -->
                            <div id="documents">
                                <div class="section-title"><i class="fas fa-folder-open"></i> Additional Documents</div>
                                <div class="form-group">
                                    <label>Upload IDs, Certificates, etc.</label>
                                    <div style="display: flex; flex-direction: column; gap: 10px;">
                                        <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                            <?php displayFiles($employee['documents'] ?? '', 'fa-folder-open'); ?>
                                        </div>
                                        <div class="upload-area" onclick="document.getElementById('multi_docs_edit').click()">
                                            <i class="fas fa-copy"></i> Select Multiple Files
                                            <input type="file" id="multi_docs_edit" name="documents[]" multiple style="display:none" onchange="handleFileList(this, 'doc_edit_list')">
                                        </div>
                                        <div id="doc_edit_list" style="font-size: 0.8rem; color: var(--secondary);"></div>
                                    </div>
                                    <small style="margin-top:5px; display:block; color:var(--gray)">Supported: PDF, JPG, PNG</small>
                                </div>
                            </div>

                            <div class="action-bar">
                                <button type="button" onclick="window.location.href='hr-employees.php'"
                                    class="btn-cancel">Cancel Changes</button>
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
                        const target = document.getElementById(id);
                        if (target) {
                            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                        // Update active nav
                        document.querySelectorAll('.nav-item').forEach(item => {
                            item.classList.remove('active');
                            if (item.getAttribute('onclick')?.includes(id)) item.classList.add('active');
                        });
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

                    function previewProfile(input) {
                        if (input.files && input.files[0]) {
                            const reader = new FileReader();
                            reader.onload = function (e) {
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

                    function toggleOtherField(select, targetId) {
                        const group = document.getElementById(targetId);
                        if (select.value === 'other') {
                            group.style.display = 'block';
                            group.querySelector('input, textarea')?.setAttribute('required', 'required');
                        } else {
                            group.style.display = 'none';
                            group.querySelector('input, textarea')?.removeAttribute('required');
                        }
                    }

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

                    function handleFileList(input, listId) {
                        const list = document.getElementById(listId);
                        list.innerHTML = '';
                        if (input.files && input.files.length > 0) {
                            const clearBtn = document.createElement('div');
                            clearBtn.innerHTML = `<button type="button" class="btn-clear-files" onclick="clearFileInput('${input.id}', '${listId}')"><i class="fas fa-times-circle"></i> Clear Selection</button>`;
                            list.appendChild(clearBtn);

                            Array.from(input.files).forEach(file => {
                                const div = document.createElement('div');
                                div.className = 'file-item-preview';
                                div.innerHTML = `<i class="fas fa-file-alt"></i> ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                                list.appendChild(div);
                            });
                        }
                    }

                    function clearFileInput(inputId, listId) {
                        const input = document.getElementById(inputId);
                        input.value = '';
                        document.getElementById(listId).innerHTML = '';
                    }

                    // Pre-populate locations
                    document.addEventListener('DOMContentLoaded', function () {
                        const region = "<?php safeEcho($employee['region']); ?>";
                        const zone = "<?php safeEcho($employee['zone']); ?>";
                        const woreda = "<?php safeEcho($employee['woreda']); ?>";
                        const kebele = "<?php safeEcho($employee['kebele']); ?>";

                        if (region) {
                            document.getElementById('region').value = region;
                            loadZones();
                            if (zone) {
                                document.getElementById('zone').value = zone;
                                loadWoredas();
                                if (woreda) {
                                    document.getElementById('woreda').value = woreda;
                                    loadKebeles();
                                    if (kebele) {
                                        document.getElementById('kebele').value = kebele;
                                    }
                                }
                            }
                        }
                    });

                </script>
</body>

</html>