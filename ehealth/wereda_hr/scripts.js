// DOM Elements
const sidebar = document.getElementById('sidebar');
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const mobileOverlay = document.getElementById('mobileOverlay');
const toggleSidebarBtn = document.getElementById('toggleSidebar');
const userProfile = document.getElementById('userProfile');
const userDropdown = document.getElementById('userDropdown');
const menuItems = document.querySelectorAll('.menu-item a');

// Toggle Sidebar on Desktop
if (toggleSidebarBtn) {
    toggleSidebarBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
    });
}

// Open/Close Sidebar on Mobile
if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', () => {
        sidebar.classList.add('mobile-open');
        mobileOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    });
}

if (mobileOverlay) {
    mobileOverlay.addEventListener('click', () => {
        sidebar.classList.remove('mobile-open');
        mobileOverlay.classList.remove('active');
        document.body.style.overflow = 'auto';
    });
}

// Active Menu Item & Smooth Navigation
menuItems.forEach(item => {
    item.addEventListener('click', (e) => {
        const href = item.getAttribute('href');

        // Handle in-page sections (SPA effect) if on dashboard
        if (window.location.pathname.includes('wereda_hr_dashboard.php')) {
            let sectionId = '';
            if (href.includes('employee')) sectionId = 'employeesSection';
            else if (href.includes('leave')) sectionId = 'leaveSection';
            else if (href.includes('recruitment')) sectionId = 'recruitmentSection';
            else if (href.includes('training')) sectionId = 'trainingSection';

            const section = sectionId ? document.getElementById(sectionId) : null;

            if (section) {
                e.preventDefault();
                section.scrollIntoView({ behavior: 'smooth' });

                // Update active state
                menuItems.forEach(i => i.parentElement.classList.remove('active'));
                item.parentElement.classList.add('active');
            }
        }

        // If on mobile, close sidebar after clicking
        if (window.innerWidth <= 992) {
            sidebar.classList.remove('mobile-open');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });
});

// User Profile Dropdown
if (userProfile) {
    userProfile.addEventListener('click', (e) => {
        e.stopPropagation();
        userDropdown.classList.toggle('show');
    });
}

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
    if (userProfile && !userProfile.contains(e.target)) {
        userDropdown.classList.remove('show');
    }
});

// Dropdown menu items
document.querySelectorAll('.dropdown-item').forEach(item => {
    item.addEventListener('click', function (e) {
        if (this.querySelector('i') && this.querySelector('i').classList.contains('fa-sign-out-alt')) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../logout.php';
            }
            return;
        }

        e.preventDefault();
        const action = this.textContent.trim();

        switch (action) {
            case 'My Profile':
                alert('Opening user profile page...');
                break;
            case 'Account Settings':
                alert('Opening account settings...');
                break;
            case 'Help & Support':
                alert('Opening help & support...');
                break;
        }

        // Close dropdown
        userDropdown.classList.remove('show');
    });
});

// Handle window resize
function handleResize() {
    if (window.innerWidth > 992 && sidebar) {
        sidebar.classList.remove('mobile-open');
        mobileOverlay.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

// Global HR Stats loading (Synchronizes sidebar badges)
function loadGlobalHRStats() {
    fetch('get_hr_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update sidebar badges globally
                updateBadge('wereda_hr_employee.php', data.stats.total_employees);
                updateBadge('wereda_hr_leave.php', data.stats.pending_leave);
                updateBadge('wereda_hr_recruitment.php', data.stats.open_positions);
                updateBadge('wereda_hr_training.php', data.stats.scheduled_trainings);

                // Update dashboard specific elements if they exist
                const ids = {
                    'totalEmployees': data.stats.total_employees,
                    'openPositions': data.stats.open_positions,
                    'onLeave': data.stats.on_leave,
                    'attendanceRate': data.stats.attendance_rate + '%',
                    'pendingLeaveCount': data.stats.pending_leave + ' pending',
                    // Trends
                    'trendEmployees': data.stats.trends.total_employees,
                    'trendPositions': data.stats.trends.open_positions,
                    'trendLeave': data.stats.trends.on_leave,
                    'trendAttendance': data.stats.trends.attendance_rate
                };

                for (const [id, value] of Object.entries(ids)) {
                    const el = document.getElementById(id);
                    if (el) el.textContent = value;
                }
            }
        })
        .catch(error => console.error('Error loading global HR stats:', error));
}

function updateClock() {
    const clockEl = document.getElementById('currentTime');
    if (clockEl) {
        const now = new Date();
        clockEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
}

function loadRecentActivity() {
    const container = document.querySelector('.activity-timeline');
    if (!container) return;

    fetch('get_recent_activity.php')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.activities.length > 0) {
                container.innerHTML = data.activities.map(act => `
                    <div class="timeline-item">
                        <div class="timeline-icon" style="background: ${act.color}15; color: ${act.color};">
                            <i class="fas fa-${act.type}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <span class="timeline-title">${act.title}</span>
                                <span class="timeline-time">${act.time_ago}</span>
                            </div>
                            <p class="timeline-desc">${act.desc}</p>
                        </div>
                    </div>
                `).join('');
            }
        })
        .catch(err => console.error('Error loading activity:', err));
}

function updateBadge(href, value) {
    const badge = document.querySelector(`.menu-item a[href*="${href}"] .menu-badge`);
    if (badge) badge.textContent = value;
}

// Initialize
handleResize();
loadGlobalHRStats();
loadNotifications(); // Initial load
loadRecentActivity(); // Dashboard activity
updateClock();
window.addEventListener('resize', handleResize);

// Refresh stats and notifications periodically
setInterval(() => {
    loadGlobalHRStats();
    loadNotifications();
    loadRecentActivity();
}, 60000);

// Clock update every minute
setInterval(updateClock, 60000);

// Notification Handling
function loadNotifications() {
    const notifBadge = document.getElementById('notifBadge');
    const notifList = document.getElementById('notifList');
    const notifBtn = document.getElementById('notificationBtn');
    const notifDropdown = document.getElementById('notificationDropdown');

    if (!notifBadge || !notifList) return;

    // Handle Dropdown Toggle
    if (notifBtn && !notifBtn.hasAttribute('data-handler')) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notifDropdown.classList.toggle('show');
            // Close other dropdowns
            if (userDropdown) userDropdown.classList.remove('show');
        });
        notifBtn.setAttribute('data-handler', 'true');
    }

    fetch('get_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update Badge
                notifBadge.textContent = data.count;
                notifBadge.style.display = data.count > 0 ? 'flex' : 'none';

                // Update List
                if (data.count === 0) {
                    notifList.innerHTML = '<div class="notif-empty">No new notifications</div>';
                } else {
                    notifList.innerHTML = data.notifications.map(n => `
                        <div class="notif-item" onclick="window.location.href='${n.link}'">
                            <div class="notif-icon-circle" style="background: ${n.color}">
                                <i class="${n.icon}"></i>
                            </div>
                            <div class="notif-content">
                                <div class="notif-title">${n.title}</div>
                                <div class="notif-message">${n.message}</div>
                                <div class="notif-time">${n.time_ago}</div>
                            </div>
                        </div>
                    `).join('');
                }
            }
        })
        .catch(error => console.error('Error loading notifications:', error));
}

// Close dropdowns when clicking outside
document.addEventListener('click', (e) => {
    const notifDropdown = document.getElementById('notificationDropdown');
    const notifBtn = document.getElementById('notificationBtn');

    if (userProfile && !userProfile.contains(e.target)) {
        userDropdown.classList.remove('show');
    }

    if (notifDropdown && !notifBtn.contains(e.target)) {
        notifDropdown.classList.remove('show');
    }
});