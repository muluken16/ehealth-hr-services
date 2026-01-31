<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Balances | HR Intelligence</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            --accent-green: #10b981;
            --accent-blue: #3b82f6;
            --accent-red: #ef4444;
            --accent-yellow: #f59e0b;
        }

        body {
            background-color: #f1f5f9;
            font-family: 'Outfit', sans-serif;
            color: #1e293b;
        }

        .main-wrapper {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Stats Overview */
        .hub-header {
            background: var(--primary-gradient);
            padding: 40px;
            border-radius: 30px;
            color: white;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px -15px rgba(79, 70, 229, 0.3);
        }

        .hub-header::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .hub-header h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .hub-header p {
            opacity: 0.9;
            font-size: 15px;
            max-width: 500px;
            line-height: 1.6;
        }

        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .stat-badge {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 15px 20px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-badge .label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .stat-badge .value {
            font-size: 24px;
            font-weight: 800;
        }

        /* Search Bar */
        .search-area {
            position: sticky;
            top: 20px;
            z-index: 100;
            margin-bottom: 30px;
        }

        .search-input-wrapper {
            background: white;
            border-radius: 20px;
            padding: 5px;
            display: flex;
            align-items: center;
            box-shadow: var(--card-shadow);
            border: 1px solid #e2e8f0;
        }

        .search-input-wrapper i {
            padding-left: 20px;
            color: #94a3b8;
            font-size: 18px;
        }

        .search-input-wrapper input {
            width: 100%;
            padding: 15px 20px;
            border: none;
            outline: none;
            font-family: inherit;
            font-size: 16px;
            font-weight: 500;
            border-radius: 20px;
        }

        /* Grid Layout */
        .balance-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 35px;
        }

        @media (max-width: 1024px) { .balance-grid { grid-template-columns: 1fr; } }

        /* Card Design */
        .employee-balance-card {
            background: white;
            border-radius: 30px;
            padding: 35px;
            box-shadow: var(--card-shadow);
            border: 1px solid #f1f5f9;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            gap: 25px;
            position: relative;
            overflow: hidden;
        }

        .employee-balance-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.12);
            border-color: #cbd5e1;
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 22px;
            background: #f1f5f9;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 28px;
            border: 1px solid #e2e8f0;
            flex-shrink: 0;
        }

        .emp-info h3 {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            color: #1e293b;
        }

        .emp-info span {
            font-size: 14px;
            color: #64748b;
            font-weight: 600;
        }

        .tenure-badge {
            display: inline-block;
            margin-top: 8px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 12px;
            font-weight: 800;
            padding: 5px 14px;
            border-radius: 8px;
            text-transform: uppercase;
        }

        .tenure-badge.probation {
            background: #fff1f2;
            color: #e11d48;
        }

        /* Quota Grid */
        .quota-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .quota-box {
            background: #f8fafc;
            padding: 18px;
            border-radius: 20px;
            text-align: center;
            border: 1px solid #f1f5f9;
        }

        .quota-box.ineligible {
            background: #f1f5f9;
            opacity: 0.6;
            cursor: not-allowed;
        }

        .quota-box .q-label {
            display: block;
            font-size: 11px;
            color: #94a3b8;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .quota-box .q-value {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
        }

        .quota-box .q-total {
            font-size: 12px;
            color: #cbd5e1;
            font-weight: 700;
        }

        /* Action Footer */
        .card-footer {
            margin-top: auto;
            border-top: 1px solid #f1f5f9;
            padding-top: 15px;
            display: flex;
            gap: 10px;
        }

        .btn-action {
            flex: 1;
            padding: 10px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            background: white;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-action:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #1e293b;
        }

        .btn-action.edit:hover {
            border-color: #4f46e5;
            color: #4f46e5;
        }

        /* Modal Overwrite */
        .modal-glass {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(10px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-glass.show { display: flex; }

        .modal-content-premium {
            background: white;
            border-radius: 30px;
            width: 90%;
            max-width: 550px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalSlide 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes modalSlide {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .input-premium {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #f1f5f9;
            border-radius: 12px;
            outline: none;
            font-family: inherit;
            font-weight: 600;
            transition: 0.2s;
        }

        .input-premium:focus {
            border-color: #4f46e5;
            background: #f5f3ff;
        }

        .label-premium {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 8px;
        }

        .btn-save-premium {
            background: #4f46e5;
            color: white;
            padding: 15px;
            border-radius: 15px;
            border: none;
            width: 100%;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
            transition: 0.2s;
        }

        .btn-save-premium:hover {
            background: #4338ca;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="main-wrapper" style="padding: 10px 20px;">
                <div class="search-area" style="margin-bottom: 20px;">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" id="balanceSearch" placeholder="Search by name, ID or department..." onkeyup="filterBalances()">
                    </div>
                </div>

                <div id="balancesList" class="balance-grid">
                    <!-- Dynamic Cards -->
                    <div style="grid-column: 1 / -1; text-align: center; padding: 100px;">
                        <i class="fas fa-circle-notch fa-spin fa-2x" style="color: #4f46e5;"></i>
                        <p style="margin-top: 15px; font-weight: 600; color: #64748b;">Synchronising leave data...</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit Modal -->
    <div class="modal-glass" id="editModal">
        <div class="modal-content-premium">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px;">
                <div>
                    <h2 style="margin: 0; font-size: 20px; font-weight: 800;">Modify Leave Quota</h2>
                    <p style="margin: 5px 0 0; font-size: 13px; color: #64748b;" id="editEmpSub">Updating balances for employee</p>
                </div>
                <button onclick="closeModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="editBalanceForm" onsubmit="saveChanges(event)">
                <input type="hidden" id="editEmpId" name="employee_id">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                    <div>
                        <label class="label-premium">Annual Base</label>
                        <input type="number" name="annual_leave_days" id="inAnnual" class="input-premium">
                    </div>
                    <div>
                        <label class="label-premium">Carry Forward</label>
                        <input type="number" name="carry_forward_days" id="inCarry" class="input-premium">
                    </div>
                    <div style="grid-column: span 2;">
                        <label class="label-premium">Annual Used (Current)</label>
                        <input type="number" name="used_annual_leave" id="inAnnualUsed" class="input-premium">
                    </div>
                    <hr style="grid-column: span 2; border: none; border-top: 1px solid #f1f5f9; margin: 10px 0;">
                    <div>
                        <label class="label-premium">Sick Total</label>
                        <input type="number" name="sick_leave_days" id="inSick" class="input-premium">
                    </div>
                    <div>
                        <label class="label-premium">Sick Used</label>
                        <input type="number" name="used_sick_leave" id="inSickUsed" class="input-premium">
                    </div>
                    <div>
                        <label class="label-premium">Emergency Total</label>
                        <input type="number" name="emergency_leave_days" id="inEmergency" class="input-premium">
                    </div>
                    <div>
                        <label class="label-premium">Emergency Used</label>
                        <input type="number" name="used_emergency_leave" id="inEmergencyUsed" class="input-premium">
                    </div>
                </div>

                <div style="display: flex; gap: 15px;">
                    <button type="button" onclick="closeModal()" class="btn-action" style="padding: 15px;">Discard</button>
                    <button type="submit" class="btn-save-premium">Save Entitlements</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let fullData = [];

        document.addEventListener('DOMContentLoaded', loadBalances);

        function loadBalances() {
            fetch('get_all_leave_balances.php')
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        fullData = res.data;
                        const avgEl = document.getElementById('avgAnnualVal');
                        const lowEl = document.getElementById('lowBalCount');
                        const totEl = document.getElementById('totalEmpCount');
                        
                        if(avgEl) avgEl.innerText = res.stats.avg_annual.toFixed(1);
                        if(lowEl) lowEl.innerText = res.stats.low_balance_count;
                        if(totEl) totEl.innerText = res.stats.total_employees;
                        
                        renderCards(fullData);
                    }
                });
        }

        function renderCards(data) {
            const container = document.getElementById('balancesList');
            container.innerHTML = '';

            if (data.length === 0) {
                container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 50px; color: #94a3b8;">No records found matching your criteria.</div>';
                return;
            }

            data.forEach(emp => {
                const initials = (emp.first_name?.[0] || 'E') + (emp.last_name?.[0] || 'M');
                const isProbation = parseInt(emp.service_years) < 1;
                
                const avatarContent = emp.photo 
                    ? `<img src="../uploads/employees/${emp.photo}" style="width:100%; height:100%; object-fit:cover; border-radius:15px;">`
                    : initials;

                const annualRem = (parseInt(emp.annual_leave_days) + parseInt(emp.carry_forward_days)) - parseInt(emp.used_annual_leave);
                const annualTotal = parseInt(emp.annual_leave_days) + parseInt(emp.carry_forward_days);
                
                const sickRem = parseInt(emp.sick_leave_days) - parseInt(emp.used_sick_leave);
                const emerRem = parseInt(emp.emergency_leave_days) - parseInt(emp.used_emergency_leave);
                const marRem = parseInt(emp.marriage_leave_days) - parseInt(emp.used_marriage_leave);
                const berRem = parseInt(emp.bereavement_leave_days) - parseInt(emp.used_bereavement_leave);
                
                // Carry forward breakdown for UI
                const carryText = emp.carry_forward_days > 0 ? `<div style="font-size: 10px; color: #4f46e5; font-weight: 700;">Includes +${emp.carry_forward_days} Carry</div>` : '';

                const card = document.createElement('div');
                card.className = 'employee-balance-card';
                card.innerHTML = `
                    <div class="card-header">
                        <div class="avatar" style="padding:0; overflow:hidden;">${avatarContent}</div>
                        <div class="emp-info">
                            <h3>${emp.first_name} ${emp.last_name}</h3>
                            <span>${emp.employee_id} • ${emp.department_assigned}</span>
                            <br>
                            ${isProbation 
                                ? '<span class="tenure-badge probation">Under Probation (<1yr)</span>' 
                                : `<span class="tenure-badge">Active • ${emp.service_years} Years</span>`
                            }
                        </div>
                    </div>

                    <div class="quota-grid" style="grid-template-columns: repeat(3, 1fr);">
                        <div class="quota-box ${isProbation ? 'ineligible' : ''}">
                            <span class="q-label">Annual</span>
                            <span class="q-value" style="color: ${annualRem < 5 ? '#e11d48' : '#4f46e5'}">${isProbation ? '0' : annualRem}</span>
                            <span class="q-total">/${isProbation ? '0' : annualTotal}</span>
                        </div>
                        <div class="quota-box">
                            <span class="q-label">Sick</span>
                            <span class="q-value">${sickRem}</span>
                        </div>
                        <div class="quota-box">
                            <span class="q-label">Emergency</span>
                            <span class="q-value" style="color:#f59e0b;">${emerRem}</span>
                        </div>
                        <div class="quota-box" style="background:#fff7ed;">
                            <span class="q-label">Marriage</span>
                            <span class="q-value" style="color:#f97316;">${marRem}</span>
                        </div>
                        <div class="quota-box" style="background:#f0fdfa;">
                            <span class="q-label">Bereave.</span>
                            <span class="q-value" style="color:#0d9488;">${berRem}</span>
                        </div>
                        <div class="quota-box" style="background:#fef2f2;">
                            <span class="q-label">${emp.gender === 'female' ? 'Maternity' : 'Paternity'}</span>
                            <span class="q-value" style="color:#b91c1c;">${emp.gender === 'female' ? (emp.maternity_leave_days - emp.used_maternity_leave) : (emp.paternity_leave_days - emp.used_paternity_leave)}</span>
                        </div>
                    </div>

                    ${carryText}

                    <div class="card-footer">
                        <button class="btn-action edit" onclick="openEditModal('${emp.employee_id}')">
                            <i class="fas fa-sliders-h"></i> Adjust Quotas
                        </button>
                        <button class="btn-action" onclick="window.location.href='hr-leave.php?emp=${emp.employee_id}'">
                            <i class="fas fa-history"></i> Log
                        </button>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        function filterBalances() {
            const query = document.getElementById('balanceSearch').value.toLowerCase();
            const filtered = fullData.filter(e => 
                `${e.first_name} ${e.last_name}`.toLowerCase().includes(query) ||
                e.employee_id.toLowerCase().includes(query) ||
                e.department_assigned.toLowerCase().includes(query)
            );
            renderCards(filtered);
        }

        function openEditModal(id) {
            const emp = fullData.find(e => e.employee_id === id);
            if (!emp) return;

            document.getElementById('editEmpId').value = emp.employee_id;
            document.getElementById('editEmpSub').innerText = `${emp.first_name} ${emp.last_name} (${emp.employee_id})`;
            
            document.getElementById('inAnnual').value = emp.annual_leave_days;
            document.getElementById('inCarry').value = emp.carry_forward_days;
            document.getElementById('inAnnualUsed').value = emp.used_annual_leave;
            document.getElementById('inSick').value = emp.sick_leave_days;
            document.getElementById('inSickUsed').value = emp.used_sick_leave;
            document.getElementById('inEmergency').value = emp.emergency_leave_days;
            document.getElementById('inEmergencyUsed').value = emp.used_emergency_leave;

            document.getElementById('editModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('editModal').classList.remove('show');
        }

        function saveChanges(e) {
            e.preventDefault();
            const form = e.target;
            const btn = form.querySelector('.btn-save-premium');
            const originalText = btn.innerHTML;

            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Synchronising...';
            btn.disabled = true;

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            fetch('update_leave_entitlements.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    closeModal();
                    loadBalances();
                    // Optional: Add a toast notification here
                } else {
                    alert('Sync Error: ' + res.message);
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