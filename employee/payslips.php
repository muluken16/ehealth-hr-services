<?php
session_start();
if (!isset($_SESSION['employee_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: login.php');
    exit;
}
require_once '../db.php';
$conn = getDBConnection();
$emp_id = $_SESSION['employee_id'];

// Get employee details
$stmt = $conn->prepare("SELECT first_name, last_name, position, salary, bank_name, bank_account FROM employees WHERE employee_id = ?");
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

// Generate mock payslip data for last 6 months
$payslips = [];
for ($i = 0; $i < 6; $i++) {
    $date = date('Y-m', strtotime("-{$i} months"));
    $basic_salary = $employee['salary'] ?? 15000;
    $allowances = $basic_salary * 0.15;
    $gross = $basic_salary + $allowances;
    $pension = $gross * 0.07;
    $tax = $gross * 0.1;
    $net = $gross - $pension - $tax;
    
    $payslips[] = [
        'month' => date('F Y', strtotime($date)),
        'period' => $date,
        'basic_salary' => $basic_salary,
        'allowances' => $allowances,
        'gross_salary' => $gross,
        'pension' => $pension,
        'income_tax' => $tax,
        'net_salary' => $net,
        'status' => 'Paid',
        'paid_date' => date('d M Y', strtotime("$date-28"))
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Payslips | HealthFirst</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        .salary-overview {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 35px;
            border-radius: 20px;
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
        }
        
        .salary-stat {
            text-align: center;
        }
        
        .salary-stat h3 {
            margin: 0 0 8px 0;
            font-size: 0.9rem;
            opacity: 0.9;
            font-weight: 600;
        }
        
        .salary-stat .amount {
            font-size: 2rem;
            font-weight: 800;
        }
        
        .payslip-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-left: 4px solid #10b981;
            transition: all 0.3s;
        }
        
        .payslip-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .payslip-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }
        
        .payslip-month {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
        }
        
        .payslip-status {
            background: #dcfce7;
            color: #166534;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .payslip-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background: #f8fafc;
            border-radius: 8px;
        }
        
        .detail-label {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .detail-value {
            color: #1e293b;
            font-weight: 700;
        }
        
        .net-salary-row {
            background: #eff6ff;
            border: 2px solid #3b82f6;
            padding: 15px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }
        
        .net-salary-label {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e40af;
        }
        
        .net-salary-amount {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e40af;
        }
        
        .download-btn {
            background: #1a4a5f;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .download-btn:hover {
            background: #2a6e8c;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="hr-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-heartbeat"></i> <span>HealthFirst</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="leave_request.php"><i class="fas fa-calendar-plus"></i> Request Leave</a>
                <a href="leave_history.php"><i class="fas fa-history"></i> My Leave History</a>
                <a href="payslips.php" class="active"><i class="fas fa-money-check-alt"></i> Payslips</a>
                <a href="attendance.php"><i class="fas fa-clock"></i> My Attendance</a>
                <a href="documents.php"><i class="fas fa-folder-open"></i> Documents</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php $page_title = "My Payslips"; include 'navbar.php'; ?>

            <div class="content">
                <!-- Salary Overview -->
                <div class="salary-overview">
                    <div class="salary-stat">
                        <h3>Monthly Salary</h3>
                        <div class="amount">ETB <?php echo number_format($payslips[0]['net_salary'], 2); ?></div>
                    </div>
                    <div class="salary-stat">
                        <h3>YTD Earnings</h3>
                        <div class="amount">ETB <?php echo number_format(array_sum(array_column($payslips, 'net_salary')), 2); ?></div>
                    </div>
                    <div class="salary-stat">
                        <h3>Bank Account</h3>
                        <div class="amount" style="font-size: 1.3rem;"><?php echo $employee['bank_name'] ?? 'N/A'; ?></div>
                        <div style="font-size: 0.9rem; opacity: 0.9; margin-top: 5px;"><?php echo $employee['bank_account'] ?? 'Not Set'; ?></div>
                    </div>
                </div>

                <!-- Payslips Section -->
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Payslip History</h2>
                        <button class="section-action-btn" onclick="window.print()">
                            <i class="fas fa-print"></i> Print All
                        </button>
                    </div>
                    <div class="hr-section-body">
                        <?php foreach ($payslips as $slip): ?>
                        <div class="payslip-card">
                            <div class="payslip-header">
                                <div>
                                    <div class="payslip-month"><i class="fas fa-calendar-alt"></i> <?php echo $slip['month']; ?></div>
                                    <div style="color: #64748b; font-size: 0.9rem; margin-top: 5px;">Paid on <?php echo $slip['paid_date']; ?></div>
                                </div>
                                <div>
                                    <span class="payslip-status"><?php echo $slip['status']; ?></span>
                                </div>
                            </div>

                            <div class="payslip-details">
                                <div class="detail-row">
                                    <span class="detail-label">Basic Salary</span>
                                    <span class="detail-value">ETB <?php echo number_format($slip['basic_salary'], 2); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Allowances</span>
                                    <span class="detail-value">ETB <?php echo number_format($slip['allowances'], 2); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Gross Salary</span>
                                    <span class="detail-value">ETB <?php echo number_format($slip['gross_salary'], 2); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Pension (7%)</span>
                                    <span class="detail-value" style="color: #dc2626;">- ETB <?php echo number_format($slip['pension'], 2); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Income Tax</span>
                                    <span class="detail-value" style="color: #dc2626;">- ETB <?php echo number_format($slip['income_tax'], 2); ?></span>
                                </div>
                            </div>

                            <div class="net-salary-row">
                                <span class="net-salary-label">Net Salary</span>
                                <span class="net-salary-amount">ETB <?php echo number_format($slip['net_salary'], 2); ?></span>
                            </div>

                            <div style="margin-top: 15px; text-align: right;">
                                <button class="download-btn" onclick="downloadPayslip('<?php echo $slip['period']; ?>')">
                                    <i class="fas fa-download"></i> Download PDF
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function downloadPayslip(period) {
            alert('Payslip download for ' + period + ' - Feature coming soon!');
            // In production, this would generate and download a PDF
        }
    </script>
</body>
</html>
