<!-- Main Content -->
<main class="main-content">
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="page-title"><?php echo $page_title ?? 'HR Portal'; ?></h1>
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
                <span class="notification-badge" id="notifBadge">0</span>
                
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notif-header">
                        <h3>Notifications</h3>
                        <span class="mark-all">Mark all as read</span>
                    </div>
                    <div class="notif-list" id="notifList">
                        <!-- Notifications will be loaded here -->
                        <div class="notif-empty">No new notifications</div>
                    </div>
                    <div class="notif-footer">
                        <a href="#">View All Notifications</a>
                    </div>
                </div>
            </div>

            <div class="user-profile" id="userProfile">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?></div>

                <div class="dropdown-menu" id="userDropdown">
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-cog"></i> Account Settings
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-question-circle"></i> Help & Support
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </header>