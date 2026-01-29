<?php
// Check if all HR pages exist and are accessible
$pages = [
    'kebele_hr_dashboard.php' => 'Dashboard',
    'hr-employees.php' => 'Employee Records',
    'add_employee.php' => 'Add Employee',
    'register_employee.php' => 'Register Employee',
    'hr-attendance.php' => 'Attendance',
    'hr-leave.php' => 'Leave Management',
    'hr-recruitment.php' => 'Recruitment',
    'hr-training.php' => 'Training',
    'hr-payroll.php' => 'Payroll',
    'hr-reports.php' => 'Reports',
    'hr-settings.php' => 'Settings',
];

echo "<h2>HR System Pages Check</h2>";
echo "<ul>";

$allGood = true;
foreach ($pages as $file => $name) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "<li style='color: green;'>✅ $name ($file) - Found</li>";
    } else {
        echo "<li style='color: red;'>❌ $name ($file) - NOT FOUND</li>";
        $allGood = false;
    }
}

echo "</ul>";

if ($allGood) {
    echo "<p style='color: green; font-weight: bold;'>✅ All pages are present!</p>";
    echo "<p>Try accessing these URLs directly:</p>";
    echo "<ul>";
    foreach ($pages as $file => $name) {
        echo "<li><a href='$file'>$name</a></li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Some pages are missing!</p>";
}

echo "<p><a href='kebele_hr_dashboard.php'>Go to Dashboard</a></p>";
