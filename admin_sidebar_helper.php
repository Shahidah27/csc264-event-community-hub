<?php
// admin_sidebar_helper.php - Central Management Hub Shared Component Structure
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
$current_page_file = basename($_SERVER['PHP_SELF']);

// Dynamic notification counter tracking engine calculation
$pnd_cnt = 0;
if (isset($conn)) {
    $cnt_pnd = $conn->query("SELECT COUNT(*) AS count FROM `event` WHERE approval_status = 'Pending'");
    if ($cnt_pnd) {
        $cnt_pnd_row = $cnt_pnd->fetch_assoc();
        $pnd_cnt = $cnt_pnd_row['count'] ?? 0;
    }
}

// Fetch Admin Profile Picture & Data from database
$session_email = $_SESSION['user_email'] ?? '';
$sidebar_image_src = null;
$displayName = !empty($session_email) ? htmlspecialchars(explode('@', $session_email)[0]) : 'Admin';

if (!empty($session_email) && isset($conn)) {
    // FIXED: Using UPPERCASE table and column names matching the ERD schema to prevent sql crashes
    $profile_query = $conn->prepare("SELECT `FULL_NAME`, `PROFILE_PIC` FROM `PROFILE_ADMIN` WHERE `EMAIL_ADMIN` = ?");
    if ($profile_query) {
        $profile_query->bind_param("s", $session_email);
        $profile_query->execute();
        $profile_result = $profile_query->get_result();

        if ($profile_result && $profile_result->num_rows > 0) {
            $user_row = $profile_result->fetch_assoc();
            if (!empty($user_row['FULL_NAME'])) {
                $name_parts = explode(' ', trim($user_row['FULL_NAME']));
                $displayName = htmlspecialchars($name_parts[0]);
            }
            if (!empty($user_row['PROFILE_PIC'])) {
                $sidebar_image_src = 'data:image/jpeg;base64,' . base64_encode($user_row['PROFILE_PIC']);
            }
        }
        $profile_query->close();
    }
}

// Dynamically generate text initials if no profile picture is available
$global_initials = strtoupper(substr($displayName, 0, 1));
?>

<nav id="sidebar" class="sidebar" style="display: flex; flex-direction: column; justify-content: space-between; top: 0; left: 0; box-sizing: border-box; height: 100vh; padding: 20px 14px !important; overflow-y: auto !important;">
    
    <div class="sidebar-menu-top" style="width: 100%; display: flex; flex-direction: column; gap: 4px;">
        <div class="smartville-brand-header" style="display: flex; align-items: center; gap: 12px; margin-bottom: 30px;">
            <img src="logo.png" alt="SmartVille Logo" style="width: 32px; height: 32px; object-fit: contain; flex-shrink: 0;">
            <h2 id="sidebar-brand-text" style="font-size: 20px; font-weight: 700; color: #ec4899; letter-spacing: -0.5px; margin: 0;">SmartVille</h2>
        </div>
        
        <div class="sidebar-avatar-wrapper" style="display: flex; align-items: center; gap: 12px; padding: 14px; margin-bottom: 25px; background: #1c1c34; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.05); transition: all 0.3s ease; box-sizing: border-box; width: 100%;">
            <div class="sidebar-avatar-circle" style="width: 44px; height: 44px; background: #ec4899; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: bold; color: white !important; overflow: hidden; flex-shrink: 0;">
                <?php if ($sidebar_image_src): ?>
                    <img src="<?php echo $sidebar_image_src; ?>" alt="Admin Profile" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <?php echo $global_initials; ?>
                <?php endif; ?>
            </div>
            <div class="sidebar-meta-info" style="min-width: 0; flex: 1; word-wrap: break-word; overflow: hidden;">
                <span class="profile-role-sub" style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: #a1a1aa; line-height: 1.2;">System Admin</span>
                <span class="profile-name-main" style="font-weight: 600; font-size: 14px; display: block; white-space: normal !important; word-break: break-all !important; color: #ffffff;" title="<?php echo $displayName; ?>">
                    <?php echo $displayName; ?>
                </span>
            </div>
        </div>
        
        <a href="admin_dashboard.php" class="<?php echo ($current_page_file === 'admin_dashboard.php') ? 'active' : ''; ?>" style="display: flex !important; align-items: center; gap: 12px; white-space: normal !important; word-break: break-word !important; padding: 12px !important;">
            <span>📊</span> <span>Dashboard</span>
        </a>
        
        <a href="admin_inbox.php" class="<?php echo ($current_page_file === 'admin_inbox.php') ? 'active' : ''; ?>" style="display: flex !important; justify-content: space-between; align-items: center; gap: 12px; white-space: normal !important; word-break: break-word !important; padding: 12px !important;">
            <span style="display: flex; align-items: center; gap: 12px; color: inherit;">
                <span>📥</span> <span>Inbox Requests</span>
            </span>
            <?php if($pnd_cnt > 0): ?>
                <span style="background: #ec4899; color: white; font-size: 11px; padding: 2px 8px; border-radius: 20px; font-weight: 700; line-height: 1; flex-shrink: 0;"><?php echo $pnd_cnt; ?></span>
            <?php endif; ?>
        </a>
        
        <a href="admin_published_event.php" class="<?php echo ($current_page_file === 'admin_published_event.php') ? 'active' : ''; ?>" style="display: flex !important; align-items: center; gap: 12px; white-space: normal !important; word-break: break-word !important; padding: 12px !important;">
            <span>📢</span> <span>Published Events</span>
        </a>
        
        <a href="admin_feedback.php" class="<?php echo ($current_page_file === 'admin_feedback.php') ? 'active' : ''; ?>" style="display: flex !important; align-items: center; gap: 12px; white-space: normal !important; word-break: break-word !important; padding: 12px !important;">
            <span>💬</span> <span>User Feedbacks</span>
        </a>
        
        <a href="admin_schedule.php" class="<?php echo ($current_page_file === 'admin_schedule.php') ? 'active' : ''; ?>" style="display: flex !important; align-items: center; gap: 12px; white-space: normal !important; word-break: break-word !important; padding: 12px !important;">
            <span>📅</span> <span>Booking Schedule</span>
        </a>
        
        <a href="profile.php" class="<?php echo ($current_page_file === 'profile.php' ? 'active' : ''); ?>" style="display: flex !important; align-items: center; gap: 12px; white-space: normal !important; word-break: break-word !important; padding: 12px !important;">
            <span>👤</span> <span>My Profile</span>
        </a>
        
        <a href="settings.php" class="<?php echo ($current_page_file === 'settings.php' ? 'active' : ''); ?>" style="display: flex !important; align-items: center; gap: 12px; white-space: normal !important; word-break: break-word !important; padding: 12px !important;">
            <span>⚙️</span> <span>Settings</span>
        </a>
    </div>

    <div class="sidebar-menu-bottom" style="width: 100%; margin-top: auto; box-sizing: border-box;">
        <a href="logout.php" onclick="return confirm('Are you sure you want to sign out of the SmartVille Admin Panel?');" style="color: #ef4444; margin-bottom: 0; margin-top: 40px; display: flex !important; align-items: center; gap: 12px; white-space: normal !important; word-break: break-word !important; padding: 12px !important;">
            <span>🚪</span> <span>Sign Out</span>
        </a>
    </div>
</nav>

<script>
(function() {
    if (localStorage.getItem('sidebarHidden') === 'true') {
        const sb = document.getElementById('sidebar');
        const mc = document.getElementById('main-content');
        if (sb) sb.classList.add('hidden');
        if (mc) mc.classList.add('expanded');
    }

    function applyGlobalTypography() {
        const savedSize = localStorage.getItem('fontSize') || '16';
        const savedFont = localStorage.getItem('fontStyle') || "'Segoe UI', sans-serif";
        const currentTheme = localStorage.getItem('theme') || 'dark';

        document.documentElement.style.setProperty('--base-font-size', savedSize + 'px');
        document.documentElement.style.setProperty('--base-font-family', savedFont);

        let dynamicStyle = document.getElementById('dynamic-typography-override');
        if (!dynamicStyle) {
            dynamicStyle = document.createElement('style');
            dynamicStyle.id = 'dynamic-typography-override';
            document.head.appendChild(dynamicStyle);
        }

        dynamicStyle.innerHTML = `
            body, .sidebar, .main-content, h1, h2, h3, h4, span, p, label, a { 
                font-family: ${savedFont} !important; 
            }
            body {
                font-size: ${savedSize}px !important;
            }
            .sidebar a, table td, table th, input, select, textarea, .btn-control, .btn-primary { 
                font-size: ${parseInt(savedSize) - 2}px !important; 
            }
            .header-top h1 { 
                font-size: ${parseInt(savedSize) + 12}px !important; 
            }
            .panel h2 { 
                font-size: ${parseInt(savedSize) + 2}px !important; 
            }

            .sidebar a {
                display: flex !important;
                align-items: center !important;
                white-space: normal !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
            }

            html:not(.light-mode) body, html:not(.light-mode) .main-content {
                background: #06060c !important;
                color: #ffffff !important;
            }
            html:not(.light-mode) .sidebar {
                background: #0f0f1a !important;
                border-right: 1px solid rgba(255, 255, 255, 0.05) !important;
            }
            html:not(.light-mode) .sidebar a { color: #a1a1aa !important; }
            html:not(.light-mode) .sidebar a:hover, html:not(.light-mode) .sidebar a.active {
                background: rgba(236, 72, 153, 0.15) !important;
                color: #ec4899 !important;
            }
            html:not(.light-mode) .sidebar-avatar-wrapper {
                background: #1c1c34 !important;
                border: 1px solid rgba(255, 255, 255, 0.05) !important;
            }
            html:not(.light-mode) .profile-name-main { color: #ffffff !important; }
            html:not(.light-mode) .main-content h1, html:not(.light-mode) .main-content h2, html:not(.light-mode) .main-content h3 {
                color: #ffffff !important;
            }

            html.light-mode body, html.light-mode .main-content {
                background: #f4f4f7 !important;
                color: #18181b !important;
            }
            
            html.light-mode .main-content h1, 
            html.light-mode .main-content h2,
            html.light-mode .main-content .header-top h1,
            html.light-mode .main-content h1[style*="color"] {
                color: #18181b !important;
            }

            html.light-mode .panel, 
            html.light-mode .card,
            html.light-mode .dashboard-card,
            html.light-mode .metric-card,
            html.light-mode .event-container,
            html.light-mode .chart-container,
            html.light-mode .table-responsive,
            html.light-mode div[style*="background:#0f0f1a"]:not([style*="width: 100%"]),
            html.light-mode div[style*="background-color:#0f0f1a"]:not([style*="width: 100%"]),
            html.light-mode div[style*="background: #0f0f1a"]:not([style*="width: 100%"]) {
                background-color: #ffffff !important;
                background: #ffffff !important;
                color: #18181b !important;
                border: 1px solid rgba(0, 0, 0, 0.08) !important;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
            }

            html.light-mode .panel h2, 
            html.light-mode .panel h3, 
            html.light-mode .card h2, 
            html.light-mode .card h3,
            html.light-mode .card h4,
            html.light-mode .card span,
            html.light-mode .card p {
                color: #18181b !important;
            }

            html.light-mode th, 
            html.light-mode .table-header,
            html.light-mode td,
            html.light-mode div[style*="color:#a1a1aa"],
            html.light-mode span[style*="color:#a1a1aa"],
            html.light-mode p[style*="color:#a1a1aa"] {
                color: #4b5563 !important;
            }

            html.light-mode .sidebar {
                background: #ffffff !important;
                border-right: 1px solid rgba(0, 0, 0, 0.08) !important;
            }
            html.light-mode .sidebar a { color: #4b5563 !important; }
            html.light-mode .sidebar a:hover, html.light-mode .sidebar a.active {
                background: rgba(236, 72, 153, 0.1) !important;
                color: #ec4899 !important;
            }
            html.light-mode .sidebar-avatar-wrapper { 
                background: #f4f4f5 !important; 
                border: 1px solid rgba(0, 0, 0, 0.06) !important; 
            }
            html.light-mode .profile-role-sub { color: #71717a !important; }
            html.light-mode .profile-name-main { color: #18181b !important; }
            html.light-mode #sidebar-brand-text { color: #ec4899 !important; }
        `;
        
        if (currentTheme === 'light') {
            document.documentElement.classList.add('light-mode');
        } else {
            document.documentElement.classList.remove('light-mode');
        }
    }

    applyGlobalTypography();
    window.addEventListener('storage', applyGlobalTypography);
    window.refreshGlobalTypography = applyGlobalTypography;
})();
</script>