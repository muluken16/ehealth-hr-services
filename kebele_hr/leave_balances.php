<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Intelligence Hub | Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        /* Modern Single-Column Card Feed Dashboard */
        :root {
            --primary: #1e293b;
            --secondary: #6366f1;
            --accent: #10b981;
            --bg-body: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            color: var(--text-main);
        }

        .main-wrapper {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .hub-intro {
            text-align: center;
            margin-bottom: 40px;
        }

        .hub-intro h1 {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: -1.5px;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hub-intro p {
            color: var(--text-muted);
            font-size: 16px;
            font-weight: 500;
        }

        .search-wrapper {
            position: relative;
            margin-bottom: 35px;
        }

        .search-wrapper i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
        }

        .search-wrapper input {
            width: 100%;
            padding: 18px 25px 18px 55px;
            border: 2px solid white;
            border-radius: 20px;
            font-size: 16px;
            font-weight: 600;
            background: white;
            box-shadow: var(--card-shadow);
            outline: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .search-wrapper input:focus {
            border-color: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 15px 35px -10px rgba(99, 102, 241, 0.15);
        }

        .card-feed {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .employee-profile-card {
            background: white;
            border-radius: 24px;
            padding: 28px;
            border: 1px solid #f1f5f9;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .employee-profile-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.1);
            border-color: #e2e8f0;
        }

        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .profile-main {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .profile-avatar {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: linear-gradient(135deg, #1e293b, #475569);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 22px;
            box-shadow: 0 8px 15px rgba(30, 41, 59, 0.2);
        }

        .profile-details h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            color: #1e293b;
        }

        .profile-details p {
            margin: 2px 0 0;
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .tenure-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 800;
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .tag-active {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #dcfce7;
        }

        .tag-probation {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fee2e2;
        }

        .leave-status-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            background: #f8fafc;
            padding: 15px;
            border-radius: 18px;
            border: 1px solid #f1f5f9;
        }

        .leave-stat-box {
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .stat-value {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .stat-label {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }

        .stat-bonus {
            font-size: 9px;
            color: var(--secondary);
            font-weight: 800;
            margin-top: 2px;
        }

        .btn-update-profile {
            background: #fff;
            color: #1e293b;
            border: 1.5px solid #e2e8f0;
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-update-profile:hover {
            background: #1e293b;
            color: white;
            border-color: #1e293b;
        }

        .locked-stat {
            background: #f1f5f9;
            opacity: 0.7;
            position: relative;
        }

        .locked-stat::after {
            content: '\f023';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 10px;
            position: absolute;
            top: 5px;
            right: 5px;
            color: #94a3b8;
        }

        .success-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(8px);
            z-index: 99999;
            justify-content: center;
            align-items: center;
            padding: 20px;
            transition: all 0.3s;
        }

        .success-modal.show {
            display: flex;
        }

        .success-modal-content {
            background: white;
            border-radius: 24px;
            padding: 35px;
            width: 100%;
            max-width: 500px;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalPop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes modalPop {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <?php
            $page_title = "Update Leave Quotas";
            include 'navbar.php';
            ?>

            <div class="main-wrapper">
                <div class="hub-intro">
                    <h1>Leave Balances Hub</h1>
                    <p>Dynamic tenure calculation, carry-forward tracking, and legal entitlement management.</p>
                </div>

                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="empSearch" placeholder="Find employee by name, ID or role..."
                        onkeyup="searchBalances()">
                </div>

                <div id="cardsContainer" class="card-feed">
                    <div style="text-align: center; padding: 60px; color: #64748b;">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p style="margin-top: 15px; font-weight: 600;">Authenticating legal balances...</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Centered Update Modal -->
    <div class="success-modal" id="editBalanceModal">
        <div class="success-modal-content" style="max-width: 480px; text-align: left; padding: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="margin: 0; font-weight: 800; color: #1e293b;">Modify Annual Quota</h3>
                <i class="fas fa-times" onclick="closeEditModal()"
                    style="cursor: pointer; color: #94a3b8; font-size: 20px;"></i>
            </div>

            <form id="editBalanceForm" onsubmit="updateEntitlements(event)">
                <input type="hidden" name="employee_id" id="editEmpId">
                <div id="editEmpBadge"
                    style="background: #eff6ff; padding: 15px; border-radius: 15px; font-weight: 800; color: #1d4ed8; margin-bottom: 30px; border-left: 6px solid #1d4ed8;">
                    Loading...</div>

                <div class="update-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="input-box" style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 13px; font-weight: 700; color: #475569;">Annual Total</label>
                        <input type="number" name="annual_leave_days" id="editAnnual" required
                            style="padding: 12px; border: 2.5px solid #f1f5f9; border-radius: 12px; outline: none; transition: 0.2s;">
                    </div>
                    <div class="input-box" style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 13px; font-weight: 700; color: #475569;">Annual Used</label>
                        <input type="number" name="used_annual_leave" id="editUsedAnnual" required
                            style="padding: 12px; border: 2.5 solid #f1f5f9; border-radius: 12px; outline: none; transition: 0.2s;">
                    </div>
                    <div class="input-box" style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 13px; font-weight: 700; color: #475569;">Sick Total</label>
                        <input type="number" name="sick_leave_days" id="editSick" required
                            style="padding: 12px; border: 2.5px solid #f1f5f9; border-radius: 12px; outline: none; transition: 0.2s;">
                    </div>
                    <div class="input-box" style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 13px; font-weight: 700; color: #475569;">Sick Used</label>
                        <input type="number" name="used_sick_leave" id="editUsedSick" required
                            style="padding: 12px; border: 2.5px solid #f1f5f9; border-radius: 12px; outline: none; transition: 0.2s;">
                    </div>
                    <div class="input-box" style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 13px; font-weight: 700; color: #475569;">Emergency Total</label>
                        <input type="number" name="emergency_leave_days" id="editEmergency" required
                            style="padding: 12px; border: 2.5px solid #f1f5f9; border-radius: 12px; outline: none; transition: 0.2s;">
                    </div>
                    <div class="input-box" style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 13px; font-weight: 700; color: #475569;">Emergency Used</label>
                        <input type="number" name="used_emergency_leave" id="editUsedEmergency" required
                            style="padding: 12px; border: 2.5px solid #f1f5f9; border-radius: 12px; outline: none; transition: 0.2s;">
                    </div>
                </div>

                <div style="margin-top: 35px; display: flex; gap: 15px;">
                    <button type="button" onclick="closeEditModal()"
                        style="flex: 1; padding: 15px; border-radius: 15px; border: 1.5px solid #e2e8f0; background: white; font-weight: 700; cursor: pointer;">Discard</button>
                    <button type="submit" id="saveBtn"
                        style="flex: 2; padding: 15px; border-radius: 15px; border: none; background: #1e293b; color: white; font-weight: 700; cursor: pointer;">Save
                        Modifications</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Alert Popup -->
    <div class="success-modal" id="successPopup">
        <div class="success-modal-content"
            style="max-width: 400px; text-align: center; padding: 40px; border-radius: 30px;">
            <div
                style="width: 80px; height: 80px; background: #f0fdf4; color: #166534; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 35px; border: 2px solid #dcfce7;">
                <i class="fas fa-check"></i>
            </div>
            <h3 style="margin: 0 0 10px; font-weight: 800; color: #1e293b; font-size: 24px;">Quota Updated!</h3>
            <p style="color: #64748b; font-weight: 500; margin-bottom: 25px;">The employee's leave balance has been
                successfully adjusted and synchronized.</p>
            <button onclick="closeSuccessPopup()"
                style="width: 100%; padding: 15px; border-radius: 15px; border: none; background: #1e293b; color: white; font-weight: 700; cursor: pointer; transition: 0.2s;">Great,
                Thanks!</button>
        </div>
    </div>

    <script>
        let balanceData = [];

        document.addEventListener('DOMContentLoaded', () => loadAllBalances());

        function loadAllBalances() {
            fetch('get_all_leave_balances.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        balanceData = data.data;
                        renderFeed(balanceData);
                    }
                });
        }

        function renderFeed(data) {
            const container = document.getElementById('cardsContainer');
            container.innerHTML = '';

            if (data.length === 0) {
                container.innerHTML = '<div style="text-align: center; padding: 50px; color: #64748b; font-weight: 600;">No employees found.</div>';
                return;
            }

            data.forEach(item => {
                const annualRem = item.annual_leave_days - item.used_annual_leave;
                const sickRem = item.sick_leave_days - item.used_sick_leave;
                const emergencyRem = item.emergency_leave_days - item.used_emergency_leave;
                const serviceYears = parseInt(item.service_years);

                const initials = (item.first_name[0] + item.last_name[0]).toUpperCase();
                const genderLabel = item.gender === 'female' ? 'Maternity' : 'Paternity';
                const genderVal = item.gender === 'female' ? (item.maternity_leave_days - item.used_maternity_leave) : (item.paternity_leave_days - item.used_paternity_leave);

                let annualHtml = '';
                if (serviceYears < 1) {
                    annualHtml = `<div class="leave-stat-box locked-stat">
                                    <span class="stat-value">0</span>
                                    <span class="stat-label">Ineligible</span>
                                  </div>`;
                } else {
                    const bonusLine = item.tenure_bonus > 0 ? `<div class="stat-bonus">+${item.tenure_bonus} Carry/Bonus</div>` : '';
                    annualHtml = `<div class="leave-stat-box">
                                    <span class="stat-value" style="color: #6366f1;">${annualRem}<small style="font-size: 11px; opacity:0.5;">/${item.annual_leave_days}</small></span>
                                    <span class="stat-label">Annual</span>
                                    ${bonusLine}
                                  </div>`;
                }

                const card = document.createElement('div');
                card.className = 'employee-profile-card';
                card.innerHTML = `
                    <div class="profile-header">
                        <div class="profile-main">
                            <div class="profile-avatar">${initials}</div>
                            <div class="profile-details">
                                <h2>${item.first_name} ${item.last_name}</h2>
                                <p>${item.employee_id} • ${item.department_assigned}</p>
                                <div style="display:flex; gap:10px; align-items:center;">
                                    ${serviceYears < 1 ? '<span class="tenure-tag tag-probation">Less than 1yr</span>' : '<span class="tenure-tag tag-active">Active • ' + serviceYears + ' Year(s)</span>'}
                                    <span style="font-size: 11px; color:#94a3b8; font-weight:600; margin-top:8px;">Since ${new Date(item.join_date).toLocaleDateString()}</span>
                                </div>
                            </div>
                        </div>
                        <button class="btn-update-profile" onclick="openEditModal('${item.employee_id}')">
                            <i class="fas fa-edit"></i> Update
                        </button>
                    </div>

                    <div class="leave-status-row">
                        ${annualHtml}
                        <div class="leave-stat-box">
                            <span class="stat-value" style="color: #10b981;">${sickRem}<small style="font-size: 11px; opacity:0.5;">/${item.sick_leave_days}</small></span>
                            <span class="stat-label">Sick Leave</span>
                        </div>
                        <div class="leave-stat-box">
                            <span class="stat-value" style="color: #f59e0b;">${emergencyRem}<small style="font-size: 11px; opacity:0.5;">/${item.emergency_leave_days}</small></span>
                            <span class="stat-label">Emergency</span>
                        </div>
                        <div class="leave-stat-box">
                            <span class="stat-value" style="color: #475569;">${genderVal}</span>
                            <span class="stat-label">${genderLabel}</span>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        function searchBalances() {
            const query = document.getElementById('empSearch').value.toLowerCase();
            const filtered = balanceData.filter(item =>
                item.first_name.toLowerCase().includes(query) ||
                item.last_name.toLowerCase().includes(query) ||
                item.employee_id.toLowerCase().includes(query) ||
                item.department_assigned.toLowerCase().includes(query)
            );
            renderFeed(filtered);
        }

        function openEditModal(empId) {
            const emp = balanceData.find(e => e.employee_id === empId);
            if (!emp) return;
            document.getElementById('editEmpId').value = emp.employee_id;
            document.getElementById('editEmpBadge').textContent = emp.first_name + ' ' + emp.last_name;
            document.getElementById('editAnnual').value = emp.annual_leave_days;
            document.getElementById('editUsedAnnual').value = emp.used_annual_leave;
            document.getElementById('editSick').value = emp.sick_leave_days;
            document.getElementById('editUsedSick').value = emp.used_sick_leave;
            document.getElementById('editEmergency').value = emp.emergency_leave_days;
            document.getElementById('editUsedEmergency').value = emp.used_emergency_leave;
            document.getElementById('editBalanceModal').classList.add('show');
        }

        function closeEditModal() {
            document.getElementById('editBalanceModal').classList.remove('show');
        }

        function closeSuccessPopup() {
            document.getElementById('successPopup').classList.remove('show');
        }

        function updateEntitlements(e) {
            e.preventDefault();
            const btn = document.getElementById('saveBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Processing...';
            btn.disabled = true;

            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());

            fetch('update_leave_entitlements.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        closeEditModal();
                        loadAllBalances();
                        // Show Premium Success Popup
                        setTimeout(() => {
                            document.getElementById('successPopup').classList.add('show');
                        }, 300);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }
    </script>
</body>

</html>