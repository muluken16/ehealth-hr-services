<?php
session_start();

echo "<h2>Session Test</h2>";

echo "<h3>Current Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Session Status:</h3>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✓ Session is active<br>";
} else if (session_status() === PHP_SESSION_NONE) {
    echo "✗ Session not started<br>";
} else {
    echo "? Session status: " . session_status() . "<br>";
}

echo "<h3>Session ID:</h3>";
echo session_id() . "<br>";

echo "<h3>Cookie:</h3>";
echo "PHPSESSID: " . ($_COOKIE['PHPSESSID'] ?? 'Not set') . "<br>";

echo "<h3>Quick Links:</h3>";
echo "<a href='kebele_hr/kebele_hr_dashboard.php'>HR Dashboard</a><br>";
echo "<a href='kebele_hr/hr-employees.php'>Employees</a><br>";
echo "<a href='kebele_hr/hr-recruitment.php'>Recruitment</a><br>";
echo "<a href='jobs.php'>Public Jobs Page</a><br>";
echo "<a href='login_ui.php'>Login Page</a><br>";
echo "<a href='logout.php'>Logout</a><br>";
?>
<style>
    body {
        font-family: Arial;
        padding: 20px;
        background: #f5f5f5;
    }

    pre {
        background: #fff;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    h2,
    h3 {
        color: #333;
    }

    a {
        display: inline-block;
        margin: 5px 0;
        padding: 8px 15px;
        background: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
    }

    a:hover {
        background: #0056b3;
    }
</style>