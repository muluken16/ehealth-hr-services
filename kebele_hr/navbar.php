<header class="header">
    <div class="header-left">
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title"><?php echo $page_title ?? 'Kebele HR Portal'; ?></h1>
    </div>

    <div class="header-actions">
        <!-- Date Display -->
        <div class="header-date">
            <i class="fas fa-calendar-alt"></i>
            <span><?php echo date('l, d M Y'); ?></span>
        </div>

        <!-- Notifications -->
        <div class="notification-icon" id="notificationBtn">
            <i class="fas fa-bell"></i>
            <span class="notification-badge notif-pulse" id="notifBadge">0</span>
            
            <div class="notification-dropdown" id="notificationDropdown">
                <div class="notif-header">
                    <h3>Notifications</h3>
                    <span class="mark-all">Mark all as read</span>
                </div>
                <div class="notif-list" id="notifList">
                    <div class="notif-empty">No new notifications</div>
                </div>
                <div class="notif-footer">
                    <a href="hr-reports.php">View All Notifications</a>
                </div>
            </div>
        </div>

        <!-- User Profile -->
        <div class="user-profile" id="userProfile">
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'HR', 0, 2)); ?>
            </div>

            <div class="dropdown-menu" id="userDropdown">
                <div class="user-dropdown-header">
                    <div>
                        <div class="avatar-large">
                            <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'HR', 0, 2)); ?>
                        </div>
                        <div class="user-details">
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                            <span class="user-role"><?php echo htmlspecialchars($_SESSION['role'] ?? 'Kebele HR Officer'); ?></span>
                        </div>
                    </div>
                </div>
                
                <a href="#" class="dropdown-item">
                    <i class="fas fa-user-circle"></i> 
                    <span>My Profile</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
                <a href="hr-settings.php" class="dropdown-item">
                    <i class="fas fa-cog"></i> 
                    <span>Account Settings</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-question-circle"></i> 
                    <span>Help & Support</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
                <div class="dropdown-divider"></div>
                <a href="../logout.php" id="logoutBtn" class="dropdown-item logout-item">
                    <i class="fas fa-sign-out-alt"></i> 
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</header>

