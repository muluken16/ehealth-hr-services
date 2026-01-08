<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar" style="padding: 15px 30px; background: white; border-bottom: 1px solid #edf2f7; display: flex; justify-content: space-between; align-items: center;">
    <div class="navbar-left">
        <h3 style="margin: 0; color: #1a4a5f;"><?php echo $page_title ?? 'Employee Portal'; ?></h3>
    </div>
    <div class="navbar-right" style="display: flex; align-items: center; gap: 20px;">
        <div class="notification-info" style="position: relative; cursor: pointer;">
            <i class="fas fa-bell" style="font-size: 1.2rem; color: #64748b;"></i>
            <span id="notifBadge" style="position: absolute; top: -5px; right: -5px; background: #e74c3c; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; display: flex; align-items: center; justify-content: center; font-weight: 700;">0</span>
        </div>
        <div class="user-profile" style="display: flex; align-items: center; gap: 10px;">
            <div class="user-avatar" style="width: 35px; height: 35px; background: #1a4a5f; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                <?php echo substr($_SESSION['emp_name'], 0, 1); ?>
            </div>
            <span style="font-weight: 600; color: #2d3748;"><?php echo $_SESSION['emp_name']; ?></span>
        </div>
    </div>
</nav>
