<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure role is set for the demo
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'wereda_hr';
    $_SESSION['user_name'] = 'Wereda HR Officer';
    $_SESSION['woreda'] = 'Woreda 1';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | HR Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>

        <?php
        $page_title = 'Human Resources Management';
        include 'navbar.php';
        ?>

        <main class="hr-main">
            <!-- HR Dashboard -->
            <div class="hr-dashboard">
                <!-- HR Sta