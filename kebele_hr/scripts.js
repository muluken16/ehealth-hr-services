// Kebele HR Common Scripts
document.addEventListener('DOMContentLoaded', function () {
    // Sidebar Elements
    const sidebar = document.getElementById('sidebar');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const toggleSidebarBtn = document.getElementById('toggleSidebar');

    // Sidebar Toggling
    if (toggleSidebarBtn) {
        toggleSidebarBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });
    }

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.add('mobile-open');
            if (mobileOverlay) mobileOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = 'auto';
            // Also close side panels if any
            const sidePanel = document.getElementById('employeeSidePanel');
            if (sidePanel) {
                sidePanel.classList.remove('open');
            }
        });
    }

    // Standard Dropdown Handler (Bubbling Pattern)
    const userProfile = document.getElementById('userProfile');
    const userDropdown = document.getElementById('userDropdown');
    const notifBtn = document.getElementById('notificationBtn');
    const notifDropdown = document.getElementById('notificationDropdown');

    // 1. User Profile Trigger
    if (userProfile && userDropdown) {
        userProfile.addEventListener('click', function (e) {
            e.stopPropagation(); // Prevent document click
            const isVisible = userDropdown.style.display === 'block';

            // Close others
            if (notifDropdown) notifDropdown.style.display = 'none';

            // Toggle Self
            userDropdown.style.display = isVisible ? 'none' : 'block';
        });

        // 2. Prevent Menu Clicks from Toggling/Closing
        userDropdown.addEventListener('click', function (e) {
            e.stopPropagation(); // Stop bubbling to Profile (Toggle) or Document (Close)
        });
    }

    // 3. Notification Trigger
    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isVisible = notifDropdown.style.display === 'block';

            // Close others
            if (userDropdown) userDropdown.style.display = 'none';

            // Toggle Self
            notifDropdown.style.display = isVisible ? 'none' : 'block';
        });

        notifDropdown.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    // 4. Document Click (Close All)
    document.addEventListener('click', function () {
        if (userDropdown) userDropdown.style.display = 'none';
        if (notifDropdown) notifDropdown.style.display = 'none';
    });

    // Load Notifications
    loadNotifications();
    setInterval(loadNotifications, 30000); // Every 30 seconds
});

function loadNotifications() {
    const notifBadge = document.getElementById('notifBadge');
    const notifList = document.getElementById('notifList');

    if (!notifList) return;

    fetch('get_notifications.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (notifBadge) {
                    notifBadge.textContent = data.count;
                    notifBadge.style.display = data.count > 0 ? 'flex' : 'none';
                }

                if (data.count === 0) {
                    notifList.innerHTML = '<div class="notif-empty" style="padding: 30px; text-align: center; color: #94a3b8;">No new notifications</div>';
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
        .catch(err => console.error('Error loading notifications:', err));
}

// End of common scripts
