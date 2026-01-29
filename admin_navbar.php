<!-- Main Content -->
<main class="main-content">
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="page-title"><?php echo $page_title ?? 'System Administration'; ?></h1>
        </div>

        <div class="header-actions">
            <div class="user-profile" id="userProfile">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?></div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <span class="user-role"><?php
                        $role_names = [
                            'admin' => 'Administrator',
                            'zone_health_officer' => 'Zone Health Officer',
                            'zone_hr' => 'Zone HR Officer',
                            'wereda_health_officer' => 'Wereda Health Officer',
                            'wereda_hr' => 'Wereda HR Officer',
                            'kebele_health_officer' => 'Kebele Health Officer',
                            'kebele_hr' => 'Kebele HR Officer'
                        ];
                        echo $role_names[$_SESSION['role']] ?? $_SESSION['role'];
                    ?></span>
                </div>
                <i class="fas fa-chevron-down"></i>

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
                    <a href="logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </header>