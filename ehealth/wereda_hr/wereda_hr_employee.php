<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure role is set for the demo (Dev Fallback)
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'wereda_hr';
    $_SESSION['user_name'] = 'Wereda HR Officer';
    $_SESSION['woreda'] = 'Woreda 1';
    $_SESSION['user_id'] = 'DEMO_USER';
}

if ($_SESSION['role'] != 'wereda_hr') {
    header('Location: ../index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | HR Employees</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        .modal-content.large-modal { max-width: 900px; width: 95%; }
        .form-section-title {
            font-size: 1.1rem;
            color: var(--primary);
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin: 20px 0 15px 0;
            font-weight: 600;
        }
        .file-input-wrapper { border: 1px dashed #ccc; padding: 10px; border-radius: 8px; text-align: center; }
        
        /* Pagination Styles */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding: 15px 0;
            border-top: 1px solid #eee;
        }
        .pagination-info { font-size: 0.9rem; color: var(--gray); }
        .pagination-btns { display: flex; gap: 5px; }
        .page-link {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            color: var(--primary);
            text-decoration: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .page-link:hover { background: #f0f4f8; }
        .page-link.active { background: var(--primary); color: white; border-color: var(--primary); }
        .page-link.disabled { color: #ccc; cursor: not-allowed; pointer-events: none; }

        /* Side Panel Styles */
        .side-panel {
            position: fixed;
            top: 0;
            right: -450px;
            width: 450px;
            height: 100vh;
            background: white;
            box-shadow: -5px 0 25px rgba(0,0,0,0.1);
            z-index: 1100;
            transition: right 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            overflow-y: auto;
            padding: 30px;
        }
        .side-panel.open { right: 0; }
        .side-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #f0f2f5;
            padding-bottom: 15px;
        }
        .side-panel-close { cursor: pointer; font-size: 1.5rem; color: var(--gray); transition: color 0.2s; }
        .side-panel-close:hover { color: var(--danger); }
        .info-card { background: #f8fbff; border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 1px solid #edf2f7; }
        .info-label { font-size: 0.75rem; text-transform: uppercase; color: #718096; letter-spacing: 0.5px; margin-bottom: 4px; font-weight: 700; }
        .info-value { font-size: 0.95rem; color: #2d3748; font-weight: 500; }
        .side-panel-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.3);
            z-index: 1099;
            display: none;
        }
        .side-panel-overlay.active { display: block; }
        tr.clickable-row { cursor: pointer; transition: background 0.2s; }
        tr.clickable-row:hover { background: #f0f7ff !important; }
    </style>
</head>
<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>
        <?php
        $page_title = 'Employee Directory';
        include 'navbar.php';
        ?>
            <div class="hr-dashboard">
                <div class="filters-section">
                    <div class="search-box">
                        <input type="text" placeholder="Search by name or ID..." id="employeeSearch">
                        <i class="fas fa-search"></i>
                    </div>
                    <select class="filter-select" id="departmentFilter">
                        <option value="">All Departments</option>
                        <option value="medical">Medical</option>
                        <option value="admin">Administration</option>
                        <option value="technical">Technical</option>
                        <option value="support">Support</option>
                    </select>
                    <select class="filter-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="on-leave">On Leave</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <button class="add-btn" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Add Employee
                    </button>
                </div>

                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Employee Records</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn" onclick="exportEmployees()">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <button class="section-action-btn" onclick="loadEmployees(1)">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>ID</th>
                                        <th>Department</th>
                                        <th>Position</th>
                                        <th>Join Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="employeeTableBody">
                                    <tr><td colspan="7" style="text-align:center;">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Container -->
                        <div class="pagination-container" id="paginationContainer">
                            <!-- JS will inject pagination here -->
                        </div>
                    </div>
                </div>
            </div>
    </div>

    <!-- Employee Side Panel -->
    <div class="side-panel-overlay" id="sidePanelOverlay"></div>
    <div class="side-panel" id="employeeSidePanel">
        <div class="side-panel-header">
            <h2 id="sidePanelTitle">Employee Details</h2>
            <i class="fas fa-times side-panel-close" onclick="closeSidePanel()"></i>
        </div>
        <div id="sidePanelContent">
            <!-- Content loaded via JS -->
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div class="modal" id="addEmployeeModal">
        <div class="modal-content large-modal">
            <span class="close-modal" onclick="closeAddModal()">&times;</span>
            <h2 class="modal-title">Add New Employee</h2>

            <form id="employeeForm" enctype="multipart/form-data">
                
                <!-- 1. Personal Information -->
                <div class="form-section-title">1. Personal Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="firstName" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middleName">
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="lastName" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Gender *</label>
                        <select name="gender" required>
                            <option value="">Select</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth *</label>
                        <input type="date" name="dateOfBirth" required>
                    </div>
                    <div class="form-group">
                        <label>Religion</label>
                        <select name="religion">
                            <option value="">Select</option>
                            <option value="Orthodox">Orthodox</option>
                            <option value="Islam">Islam</option>
                            <option value="Protestant">Protestant</option>
                            <option value="Catholic">Catholic</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <!-- Contact Info -->
                 <div class="form-row">
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="tel" name="phone" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Citizenship *</label>
                    <select name="citizenship" onchange="toggleOtherInput('citizenship', 'otherCitizenshipDiv')">
                        <option value="Ethiopia">Ethiopia</option>
                        <option value="Other">Other</option>
                    </select>
                    <div id="otherCitizenshipDiv" style="display:none; margin-top:5px;">
                        <input type="text" name="otherCitizenship" placeholder="Specify Citizenship">
                    </div>
                </div>

                <!-- 2. Address (Amhara Logic) -->
                <div class="form-section-title">2. Address / Location</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Region *</label>
                        <select id="regionSelect" name="region" onchange="loadZones()" required>
                            <option value="">Select Region</option>
                            <option value="amhara">Amhara</option>
                            <option value="oromia">Oromia</option>
                            <option value="addis_ababa">Addis Ababa</option>
                            <option value="tigray">Tigray</option>
                            <option value="snnpr">SNNP</option>
                            <option value="somali">Somali</option>
                            <option value="afar">Afar</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Zone *</label>
                        <select id="zoneSelect" name="zone" onchange="loadWoredas()" required>
                            <option value="">Select Region First</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Woreda *</label>
                        <select id="woredaSelect" name="woreda" onchange="loadKebeles()" required>
                            <option value="">Select Zone First</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kebele *</label>
                        <select id="kebeleSelect" name="kebele" required>
                            <option value="">Select Woreda First</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Detailed Address</label>
                        <input type="text" name="address" placeholder="House No, Street...">
                    </div>
                </div>

                <!-- 3. Employment Details -->
                <div class="form-section-title">3. Employment Details</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Department *</label>
                        <select name="department" id="deptSelect" onchange="syncDepartment()" required>
                            <option value="">Select Department</option>
                            <option value="medical">Medical</option>
                            <option value="administration">Administration</option>
                            <option value="technical">Technical</option>
                            <option value="support">Support</option>
                        </select>
                        <input type="hidden" name="department_assigned" id="deptAssigned">
                    </div>
                    <div class="form-group">
                        <label>Position / Title *</label>
                        <input type="text" name="position" required>
                    </div>
                    <div class="form-group">
                        <label>Job Level *</label>
                         <select name="jobLevel" required>
                            <option value="entry">Entry Level</option>
                            <option value="junior">Junior</option>
                            <option value="senior">Senior</option>
                            <option value="manager">Manager</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Join Date *</label>
                        <input type="date" name="joinDate" required>
                    </div>
                    <div class="form-group">
                        <label>Salary (ETB)</label>
                        <input type="number" name="salary">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                         <select name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                
                <!-- 4. Qualifications -->
                <div class="form-section-title">4. Qualifications</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Education Level</label>
                         <select name="educationLevel">
                            <option value="degree">Degree</option>
                            <option value="diploma">Diploma</option>
                            <option value="masters">Masters</option>
                            <option value="phd">PhD</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>University/College</label>
                        <input type="text" name="university">
                    </div>
                </div>

                <!-- 5. Legal & Documents -->
                <div class="form-section-title">5. Legal & Documents</div>
                <div class="form-row">
                     <div class="form-group">
                        <label>FIN ID *</label>
                        <input type="text" name="fin_id" required>
                    </div>
                     <div class="form-group">
                        <label>Scan FIN Document</label>
                        <div class="file-input-wrapper">
                            <input type="file" name="fin_scan">
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Criminal Record Status *</label>
                         <select name="criminal_status" onchange="toggleFile('criminal_status', 'criminalFileDiv')">
                            <option value="no">Clean</option>
                            <option value="yes">Has Record</option>
                        </select>
                    </div>
                    <div class="form-group" id="criminalFileDiv" style="display:none;">
                         <label>Upload Record</label>
                         <input type="file" name="criminal_file">
                    </div>
                </div>

                <!-- 6. Warranty/guarantee -->
                <div class="form-section-title">6. Warranty / Guarantee</div>
                 <div class="form-group">
                    <label>Warranty Required?</label>
                     <select name="warranty_status" onchange="toggleSection('warranty_status', 'warrantyFields', 'yes')">
                        <option value="no">No</option>
                        <option value="yes">Yes</option>
                    </select>
                </div>
                <div id="warrantyFields" style="display:none; background:#f9f9f9; padding:15px; border-radius:5px;">
                     <div class="form-row">
                        <div class="form-group"><label>Guarantor Name</label><input type="text" name="person_name"></div>
                        <div class="form-group"><label>Guarantor Phone</label><input type="text" name="warranty_phone"></div>
                     </div>
                     <div class="form-group"><label>Warranty Scan</label><input type="file" name="scan_file"></div>
                </div>


                <div class="form-actions" style="margin-top:20px;">
                    <button type="submit" class="submit-btn" id="saveBtn">Save Employee</button>
                    <button type="button" class="cancel-btn" onclick="closeAddModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // --- 1. Address Data (Condensed) ---
        const amharaData = {
            "North Wollo": ["Woldiya", "Lalibela", "Kobo"],
            "South Wollo": ["Dessie", "Kombolcha", "Kutaber"],
            "North Gondar": ["Gondar", "Debark", "Dabat"],
            "South Gondar": ["Debre Tabor", "Fogera"],
            "West Gojjam": ["Bahir Dar", "Finote Selam"],
            "East Gojjam": ["Debre Markos", "Dejen"],
            "Addis Ababa": ["Bole", "Yeka", "Kirkos", "Lideta"]
        };

        // --- 2. Address Logic ---
        function loadZones() {
            const region = document.getElementById("regionSelect").value;
            const zoneSelect = document.getElementById("zoneSelect");
            zoneSelect.innerHTML = '<option value="">Select Zone</option>';
            
            if (region === 'amhara') {
                Object.keys(amharaData).slice(0,6).forEach(z => {
                     zoneSelect.innerHTML += `<option value="${z}">${z}</option>`;
                });
            } else if (region === 'addis_ababa') {
                 zoneSelect.innerHTML += `<option value="Addis Ababa City">Addis Ababa City</option>`;
            } else {
                 zoneSelect.innerHTML += `<option value="Zone 1">Zone 1</option>`;
            }
            loadWoredas();
        }

        function loadWoredas() {
            const region = document.getElementById("regionSelect").value;
            const zone = document.getElementById("zoneSelect").value;
            const woredaSelect = document.getElementById("woredaSelect");
            woredaSelect.innerHTML = '<option value="">Select Woreda</option>';

            if (region === 'amhara' && amharaData[zone]) {
                amharaData[zone].forEach(w => {
                    woredaSelect.innerHTML += `<option value="${w}">${w}</option>`;
                });
            } else if (region === 'addis_ababa') {
                 amharaData["Addis Ababa"].forEach(w => {
                    woredaSelect.innerHTML += `<option value="${w}">${w}</option>`;
                 });
            } else {
                woredaSelect.innerHTML += `<option value="Woreda 1">Woreda 1</option><option value="Woreda 2">Woreda 2</option>`;
            }
            loadKebeles();
        }

        function loadKebeles() {
            const kSelect = document.getElementById("kebeleSelect");
            kSelect.innerHTML = '<option value="">Select Kebele</option>';
            for(let i=1; i<=10; i++) {
                kSelect.innerHTML += `<option value="Kebele ${i}">Kebele ${i}</option>`;
            }
        }

        // --- 3. UI Helpers ---
        function toggleOtherInput(selectName, divId) {
            const val = document.querySelector(`select[name="${selectName}"]`).value;
            document.getElementById(divId).style.display = (val === 'Other') ? 'block' : 'none';
        }

        function toggleFile(selectName, divId) {
             const val = document.querySelector(`select[name="${selectName}"]`).value;
             document.getElementById(divId).style.display = (val === 'yes') ? 'block' : 'none';
        }

        function toggleSection(selectName, divId, triggerVal) {
             const val = document.querySelector(`select[name="${selectName}"]`).value;
             document.getElementById(divId).style.display = (val === triggerVal) ? 'block' : 'none';
        }

        function syncDepartment() {
            document.getElementById('deptAssigned').value = document.getElementById('deptSelect').value;
        }

        function openAddModal() { document.getElementById('addEmployeeModal').style.display = 'block'; }
        function closeAddModal() { document.getElementById('addEmployeeModal').style.display = 'none'; }
        function openEditModal(id) { alert('Edit functionality for ' + id + ' is coming soon in the next update!'); }

        // --- 4. Main Logic ---
        let allEmployees = [];
        let currentPage = 1;
        const itemsPerPage = 10;

        document.addEventListener('DOMContentLoaded', () => {
            loadEmployees(1);
            
            // Search & Filter with Debounce
            const searchInput = document.getElementById('employeeSearch');
            const deptFilter = document.getElementById('departmentFilter');
            const statusFilter = document.getElementById('statusFilter');

            let searchTimeout;
            function triggerSearch() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentPage = 1;
                    loadEmployees(1);
                }, 400);
            }

            searchInput.addEventListener('input', triggerSearch);
            deptFilter.addEventListener('change', () => { currentPage = 1; loadEmployees(1); });
            statusFilter.addEventListener('change', () => { currentPage = 1; loadEmployees(1); });

            // Close side panel clicking outside
            document.getElementById('sidePanelOverlay').addEventListener('click', closeSidePanel);
        });

        function loadEmployees(page = 1) {
            currentPage = page;
            const tbody = document.getElementById('employeeTableBody');
            const searchTerm = document.getElementById('employeeSearch').value;
            const dept = document.getElementById('departmentFilter').value;
            const status = document.getElementById('statusFilter').value;

            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Loading...</td></tr>';

            const url = `get_employees.php?page=${page}&limit=${itemsPerPage}&search=${encodeURIComponent(searchTerm)}&department=${encodeURIComponent(dept)}&status=${status}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        allEmployees = data.employees;
                        renderTable(allEmployees);
                        renderPagination(data.total, data.total_pages);
                    } else {
                        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:red;">${data.message || 'Failed to load data'}</td></tr>`;
                    }
                })
                .catch(err => {
                    console.error(err);
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:red;">Network Error</td></tr>';
                });
        }

        function renderTable(data) {
            const tbody = document.getElementById('employeeTableBody');
            if(data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No employees found</td></tr>';
                return;
            }

            tbody.innerHTML = data.map(emp => `
                <tr class="clickable-row" onclick="viewEmployeeDetails('${emp.employee_id}')">
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="user-avatar" style="width:32px;height:32px;background:var(--secondary);color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;">
                                ${emp.first_name.charAt(0)}${emp.last_name.charAt(0)}
                            </div>
                            <div>
                                <div style="font-weight:600; color:var(--primary);">${emp.first_name} ${emp.last_name}</div>
                                <div style="font-size:0.8em;color:#777;">${emp.email || 'No Email'}</div>
                            </div>
                        </div>
                    </td>
                    <td>${emp.employee_id}</td>
                    <td><span class="department-badge ${emp.department_assigned?.toLowerCase() || 'support'}">${emp.department_assigned || 'Support'}</span></td>
                    <td>${emp.position}</td>
                    <td>${new Date(emp.join_date).toLocaleDateString()}</td>
                    <td>
                        <span class="status-badge ${emp.status || 'active'}">
                            ${(emp.status || 'Active').toUpperCase()}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view" onclick="event.stopPropagation(); viewEmployeeDetails('${emp.employee_id}')"><i class="fas fa-eye"></i></button>
                            <button class="action-btn edit" onclick="event.stopPropagation(); openEditModal('${emp.employee_id}')"><i class="fas fa-edit"></i></button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function renderPagination(totalRows, totalPages) {
            const container = document.getElementById('paginationContainer');
            if (totalPages <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = `
                <div class="pagination-info">Showing ${(currentPage-1)*itemsPerPage + 1} to ${Math.min(currentPage*itemsPerPage, totalRows)} of ${totalRows} entries</div>
                <div class="pagination-btns">
                    <button class="page-link ${currentPage === 1 ? 'disabled' : ''}" onclick="loadEmployees(${currentPage - 1})">Prev</button>
            `;

            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    html += `<button class="page-link ${i === currentPage ? 'active' : ''}" onclick="loadEmployees(${i})">${i}</button>`;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    html += `<span class="page-link disabled">...</span>`;
                }
            }

            html += `
                    <button class="page-link ${currentPage === totalPages ? 'disabled' : ''}" onclick="loadEmployees(${currentPage + 1})">Next</button>
                </div>
            `;
            container.innerHTML = html;
        }

        // --- 5. Side Panel Logic ---
        function viewEmployeeDetails(employeeId) {
            const emp = allEmployees.find(e => e.employee_id === employeeId);
            if (!emp) return;

            const content = document.getElementById('sidePanelContent');
            const initials = emp.first_name.charAt(0) + emp.last_name.charAt(0);

            content.innerHTML = `
                <div style="text-align:center; margin-bottom:25px;">
                    <div class="user-avatar" style="width:80px; height:80px; font-size:2rem; margin:0 auto 15px;">${initials}</div>
                    <h3 style="margin:0; color:var(--primary);">${emp.first_name} ${emp.last_name}</h3>
                    <p style="color:var(--gray); margin-top:5px;">${emp.position} • ${emp.employee_id}</p>
                </div>

                <div class="info-card">
                    <div class="info-label">Contact Information</div>
                    <div style="margin-top:10px;">
                        <div style="margin-bottom:8px;"><i class="fas fa-envelope" style="width:20px; color:var(--secondary);"></i> ${emp.email || 'N/A'}</div>
                        <div><i class="fas fa-phone" style="width:20px; color:var(--secondary);"></i> ${emp.phone_number || 'N/A'}</div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-label">Employment Information</div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-top:10px;">
                        <div>
                            <div class="info-label" style="font-size:0.65rem;">Department</div>
                            <div class="info-value">${emp.department_assigned || 'N/A'}</div>
                        </div>
                        <div>
                            <div class="info-label" style="font-size:0.65rem;">Job Level</div>
                            <div class="info-value">${emp.job_level || 'N/A'}</div>
                        </div>
                        <div>
                            <div class="info-label" style="font-size:0.65rem;">Salary</div>
                            <div class="info-value">${emp.salary ? emp.salary + ' ETB' : 'Confidential'}</div>
                        </div>
                        <div>
                            <div class="info-label" style="font-size:0.65rem;">Join Date</div>
                            <div class="info-value">${new Date(emp.join_date).toLocaleDateString()}</div>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-label">Location Information</div>
                    <div style="margin-top:10px;">
                        <div><i class="fas fa-map-marker-alt" style="width:20px; color:var(--accent);"></i> ${emp.kebele}, ${emp.woreda}</div>
                        <div style="margin-left:20px; font-size:0.85rem; color:#666; margin-top:5px;">${emp.zone}, ${emp.region}</div>
                    </div>
                </div>

                <div style="padding:10px; display:flex; gap:10px;">
                    <button class="submit-btn" style="flex:1;" onclick="openEditModal('${emp.employee_id}')">Edit Employee</button>
                    <button class="cancel-btn" style="flex:1;" onclick="closeSidePanel()">Close</button>
                </div>
            `;

            document.getElementById('employeeSidePanel').classList.add('open');
            document.getElementById('sidePanelOverlay').classList.add('active');
        }

        function closeSidePanel() {
            document.getElementById('employeeSidePanel').classList.remove('open');
            document.getElementById('sidePanelOverlay').classList.remove('active');
        }

        // Form Submit
        document.getElementById('employeeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('saveBtn');
            const originalText = btn.textContent;
            btn.textContent = 'Saving...';
            btn.disabled = true;

            const formData = new FormData(this);

            fetch('add_employee.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('Employee added successfully!');
                    closeAddModal();
                    this.reset();
                    loadEmployees(1);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => alert('Submission failed: ' + err))
            .finally(() => {
                btn.textContent = originalText;
                btn.disabled = false;
            });
        });

        // Close modal on outside click
        window.onclick = function(event) {
            if (event.target == document.getElementById('addEmployeeModal')) {
                closeAddModal();
            }
        }
    </script>
</body>
</html>
