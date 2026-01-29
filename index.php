<?php
// Main entry point - redirect to login or dashboard based on session
session_start();

if (isset($_SESSION['user_name'])) {
    // User is logged in, redirect to appropriate dashboard based on role
    $role = $_SESSION['role'] ?? '';

    switch ($role) {
        case 'admin':
            header('Location: admin_dashboard.php');
            break;
        case 'zone_health_officer':
        case 'zone_hr':
        case 'wereda_health_officer':
        case 'wereda_hr':
        case 'kebele_health_officer':
            // For now, redirect kebele health officers to their dashboard
            header('Location: kebele_ho/kebele_ho_dashboard.php');
            break;
        case 'kebele_hr':
            header('Location: kebele_hr/kebele_hr_dashboard.php');
            break;
        default:
            header('Location: login_ui.php');
    }
} else {
    // User not logged in, redirect to login
    header('Location: login_ui.php');
}
exit;
