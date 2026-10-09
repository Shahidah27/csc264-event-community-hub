<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
include('db_connect.php');

// Retrieve the latest profile data directly from the database so that photos & names are always in sync
$sidebar_email = $_SESSION['user_email'] ?? '';
$sidebar_name = $_SESSION['fullname'] ?? 'Resident Member';
$sidebar_image_src = ""; 

if (!empty($sidebar_email)) {
    // 🛠️ FIX: Using the correct capitalized column names mapping your database structure pattern
    // Based on your admin table structure pattern (EMAIL_ADMIN), the resident table uses EMAIL_RESIDENT or similar exact match.
    // If your resident email column is named exactly something else, replace EMAIL_RESIDENT below with that column name.
    $sidebar_fetch = $conn->prepare("SELECT FULL_NAME as full_name, PROFILE_PIC as profile_pic FROM `profile_resident` WHERE LOWER(EMAIL_RESIDENT) = LOWER(?)");
    
    // Fallback alternative query setup if it matches the base schema:
    // $sidebar_fetch = $conn->prepare("SELECT FULL_NAME as full_name, PROFILE_PIC as profile_pic FROM `profile_resident` WHERE LOWER(EMAIL) = LOWER(?)");

    $sidebar_fetch->bind_param("s", $sidebar_email);
    $sidebar_fetch->execute();
    $sidebar_res = $sidebar_fetch->get_result()->fetch_assoc();
    $sidebar_fetch->close();

    if ($sidebar_res) {
        if (!empty($sidebar_res['full_name'])) {
            $sidebar_name = $sidebar_res['full_name'];
        }
        if (!empty($sidebar_res['profile_pic'])) {
            $sidebar_image_src = 'data:image/jpeg;base64,' . base64_encode($sidebar_res['profile_pic']);
        }
    }
}

// EXTRACT FIRST NAME ONLY
$name_parts = explode(' ', trim($sidebar_name));
$first_name_display = !empty($name_parts[0]) ? $name_parts[0] : 'Member';
$first_initial = strtoupper(substr($first_name_display, 0, 1));

$current_page = basename($_SERVER['PHP_SELF']);

// READ DYNAMIC SETTINGS FROM COOKIES
$ssr_theme = $_COOKIE['user_theme'] ?? 'dark';
$ssr_size = $_COOKIE['user_font_size'] ?? '16';
$ssr_font = $_COOKIE['user_font_style'] ?? "'Inter', sans-serif";
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">

<script>
(function () {
    const savedFont = localStorage.getItem('fontStyle') || "'Inter', sans-serif";
    const savedTheme = localStorage.getItem('theme') || 'dark';
    constsavedSize = localStorage.getItem('fontSize') || '16';
    
    if (savedTheme === 'light') {
        document.documentElement.classList.add('light-mode');
    } else {
        document.documentElement.classList.remove('light-mode');
    }
    
    document.documentElement.style.setProperty('--base-font-size', savedSize + 'px');
    document.documentElement.style.setProperty('--base-font-family', savedFont);
})();
</script>

<style>
    html, body, *, *::before, *::after, 
    .sidebar, .sidebar a, .sidebar span, .profile-name-main, .profile-role-sub,
    .main-content, .main-contentWrapper, .card, .container, h1, h2, h3, p, span, div {
        font-family: var(--base-font-family, 'Inter', sans-serif) !important;
    }

    :root {
        --base-font-size: <?php echo htmlspecialchars($ssr_size); ?>px;
        --base-font-family: <?php echo $ssr_font; ?>;
    }
    
    html {
        font-size: var(--base-font-size) !important;
    }

    body, .card, .panel, .settings-card, a, span {
        transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease; 
    }
    
    .main-content, .main-contentWrapper, #main-content, #mainContentWrapper {
        transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
                    padding 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
                    max-width 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .sidebar a {
        transition: background-color 0.2s ease, color 0.2s ease !important;
    }

    body { font-size: 1rem !important; }
    p, span, td, th, label, input, select, textarea, .sidebar a { font-size: 1rem; }
    h1 { font-size: 2rem !important; }
    h2 { font-size: 1.5rem !important; }
    h3 { font-size: 1.25rem !important; }
    small { font-size: 0.75rem !important; }

    .sidebar {
        width: 240px;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        padding: 30px 20px;
        z-index: 999;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .sidebar.hidden {
        transform: translateX(-240px) !important;
    }

    .sidebar a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        text-decoration: none;
        border-radius: 10px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .smartville-brand-header { display: flex; align-items: center; gap: 12px; margin-bottom: 30px; padding-left: 4px; }
    .smartville-brand-header img { height: 32px; width: auto; object-fit: contain; }
    .smartville-brand-header h2 { color: #ec4899 !important; font-size: 24px !important; font-weight: 700; margin: 0 !important; letter-spacing: -0.5px; }
    
    .sidebar-avatar-wrapper { display: flex; align-items: center; gap: 12px; padding: 14px; margin-bottom: 25px; border-radius: 12px; }
    .sidebar-avatar-circle { width: 44px; height: 44px; background: #ec4899; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: bold; color: white !important; overflow: hidden; flex-shrink: 0; }
    .sidebar-avatar-circle img { width: 100%; height: 100%; object-fit: cover; }
    .sidebar-meta-info { min-width: 0; flex: 1; }

    html:not(.light-mode) body { background-color: #06060c !important; color: #ffffff !important; }
    html:not(.light-mode) .sidebar { background-color: #0f0f1a !important; border-right: 1px solid rgba(255, 255, 255, 0.05) !important; }
    html:not(.light-mode) .sidebar a { color: #a1a1aa !important; }
    html:not(.light-mode) .sidebar a:hover, html:not(.light-mode) .sidebar a.active { background-color: rgba(236, 72, 153, 0.15) !important; color: #ec4899 !important; }
    html:not(.light-mode) .sidebar-avatar-wrapper { background: #1c1c34 !important; border: 1px solid rgba(255, 255, 255, 0.05) !important; }
    html:not(.light-mode) .profile-name-main { color: #ffffff !important; }

    html.light-mode body { background-color: #f4f4f7 !important; color: #0f172a !important; }
    html.light-mode .sidebar { background-color: #ffffff !important; border-right: 1px solid rgba(0, 0, 0, 0.08) !important; }
    html.light-mode .sidebar a { color: #475569 !important; }
    html.light-mode .sidebar a:hover { background-color: #f1f5f9 !important; }
    html.light-mode .sidebar a.active { background-color: rgba(219, 39, 119, 0.08) !important; color: #db2777 !important; }
    html.light-mode .sidebar-avatar-wrapper { background: #f8fafc !important; border: 1px solid rgba(0, 0, 0, 0.06) !important; }
    html.light-mode .profile-name-main { color: #0f172a !important; }
    html.light-mode .profile-role-sub { color: #64748b !important; }
    html.light-mode h1, html.light-mode h2, html.light-mode h3, html.light-mode h4, html.light-mode label { color: #0f172a !important; }
    html.light-mode p, html.light-mode small, html.light-mode span { color: #475569 !important; }

    html.light-mode #toggleMenu, 
    html.light-mode .toggle-menu,
    html.light-mode .toggle-menu-btn,
    html.light-mode .toggle-sidebar,
    html.light-mode [id*="toggle-menu"],
    html.light-mode [class*="toggle-menu"] {
        background-color: #1e2642 !important; 
        color: #ffffff !important;            
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }

    html.light-mode #toggleMenu:hover,
    html.light-mode .toggle-menu-btn:hover,
    html.light-mode .toggle-menu:hover {
        background-color: #283358 !important; 
        color: #ffffff !important;
    }
</style>

<nav class="sidebar" id="sidebar">
    <div class="smartville-brand-header">
        <img src="logo.png" alt="SmartVille Logo">
        <h2>SmartVille</h2>
    </div>
    
    <div class="sidebar-avatar-wrapper">
        <div class="sidebar-avatar-circle">
            <?php if (!empty($sidebar_image_src)): ?>
                <img src="<?php echo $sidebar_image_src; ?>" alt="Resident Pic">
            <?php else: ?>
                <?php echo $first_initial; ?>
            <?php endif; ?>
        </div>
        <div class="sidebar-meta-info">
            <span class="profile-role-sub" style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700;">Resident</span>
            <span class="profile-name-main" style="font-weight: 600; font-size: 14px; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($sidebar_name); ?>">
                <?php echo htmlspecialchars($first_name_display); ?>
            </span>
        </div>
    </div>

    <a href="browse_events.php" class="<?php echo $current_page == 'browse_events.php' ? 'active' : ''; ?>">
        🔍 Browse Events
    </a>
    <a href="my_events.php" class="<?php echo $current_page == 'my_events.php' ? 'active' : ''; ?>">
        🎫 My Events
    </a>
    <a href="profile.php" class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
        👤 My Profile
    </a>
    <a href="settings.php" class="<?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
        ⚙️ Settings
    </a>
    
    <a href="logout.php" onclick="return confirm('Are you sure you want to sign out of your SmartVille session?');" style="margin-top: auto; color: #ef4444; border-left: none !important;">
        🚪 Sign Out
    </a>
</nav>

<script>
function syncSidebarState() {
    if (localStorage.getItem('sidebarHidden') === 'true') {
        document.getElementById('sidebar')?.classList.add('hidden');
        document.getElementById('main-content')?.classList.add('expanded');
        document.getElementById('mainContentWrapper')?.classList.add('sidebar-hidden');
    }
}
syncSidebarState();
window.addEventListener('DOMContentLoaded', syncSidebarState);

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content') || document.getElementById('mainContentWrapper');
    if (sidebar) {
        sidebar.classList.toggle('hidden');
        if (mainContent) {
            mainContent.classList.toggle('expanded');
            mainContent.classList.toggle('sidebar-hidden');
        }
        localStorage.setItem('sidebarHidden', sidebar.classList.contains('hidden'));
    }
}

function refreshGlobalTypography() {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    const savedSize = localStorage.getItem('fontSize') || '16';
    const savedFont = localStorage.getItem('fontStyle') || "'Inter', sans-serif"; 

    document.documentElement.style.setProperty('--base-font-size', savedSize + 'px');
    document.documentElement.style.setProperty('--base-font-family', savedFont);

    document.cookie = "user_theme=" + savedTheme + "; path=/; max-age=31536000";
    document.cookie = "user_font_size=" + savedSize + "; path=/; max-age=31536000";
    document.cookie = "user_font_style=" + savedFont + "; path=/; max-age=31536000";

    if (savedTheme === 'light') {
        document.documentElement.classList.add('light-mode');
    } else {
        document.documentElement.classList.remove('light-mode');
    }
}
window.addEventListener('storage', refreshGlobalTypography);
refreshGlobalTypography();
</script>