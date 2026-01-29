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
            e.stopPropagation();
            const isOpen = userDropdown.classList.contains('show');

            // Close all first
            document.querySelectorAll('.dropdown-menu, .notification-dropdown').forEach(d => {
                d.classList.remove('show');
            });

            // Toggle target
            if (!isOpen) {
                userDropdown.classList.add('show');
            }
        });
    }

    // 2. Notification Trigger
    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = notifDropdown.classList.contains('show');

            // Close all
            document.querySelectorAll('.dropdown-menu, .notification-dropdown').forEach(d => {
                d.classList.remove('show');
            });

            if (!isOpen) {
                notifDropdown.classList.add('show');
            }
        });
    }

    // 3. Document Click (Close All)
    document.addEventListener('click', function (e) {
        // Close if click is outside any dropdown
        if (!e.target.closest('.dropdown-menu') && !e.target.closest('.notification-dropdown')) {
            document.querySelectorAll('.dropdown-menu, .notification-dropdown').forEach(d => {
                d.classList.remove('show');
            });
        }
    });

    // 4. Prevent inner clicks from closing (already partially handled by closest check above)
    [userDropdown, notifDropdown].forEach(d => {
        if (d) {
            d.addEventListener('click', (e) => e.stopPropagation());
        }
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
