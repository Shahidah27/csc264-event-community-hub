<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
include('db_connect.php'); 

if (!isset($_SESSION['user_email']) || $_SESSION['role'] !== 'Organizer') {
    header("Location: login.php?role=organizer"); 
    exit();
}

$session_email = $_SESSION['user_email'];

//Automatic notification system (organizer)
$unread_noti_count = 0;

//count all events that being approved/rejected/published for this email
// 🛠️ UPDATED: Changed organizer_email to EMAIL_ORGANIZER to align with database architecture schema
$count_query = $conn->prepare("SELECT COUNT(*) as total FROM `event` 
                               WHERE LOWER(EMAIL_ORGANIZER) = LOWER(?) 
                               AND LOWER(approval_status) IN ('approved', 'rejected', 'published')");
$count_query->bind_param("s", $session_email);
$count_query->execute();
$count_result = $count_query->get_result();

if ($count_result && $count_result->num_rows > 0) {
    $count_row = $count_result->fetch_assoc();
    $unread_noti_count = (int)$count_row['total'];
}
$count_query->close();

$current_page = basename($_SERVER['PHP_SELF']);
?>

<script>
(function () {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    if (savedTheme === 'light') {
        document.documentElement.classList.add('light-mode');
    } else {
        document.documentElement.classList.remove('light-mode');
    }
    
    const savedSize = localStorage.getItem('fontSize') || '16';
    document.documentElement.style.setProperty('--base-font-size', savedSize + 'px');
    
    const savedFont = localStorage.getItem('fontStyle') || "'Inter', sans-serif";
    document.documentElement.style.setProperty('--base-font-family', savedFont);
})();

function confirmLogout(event) {
    return confirm("Are you sure you want to sign out of your SmartVille session?");
}
</script>

<style>
    /* ==========================================================================
       FONTS, CONFIG & INITIAL LOAD TRANSITION FREEZE 
       ========================================================================== */
    :root {
        --base-font-size: 16px;
    }
    
    html {
        font-size: var(--base-font-size) !important;
    }

    body, .sidebar, .main-content, .card, .panel, .settings-card, .info-card {
        transition: none !important; 
    }
    
    input, select, textarea, .sidebar a {
        transition: background-color 0.2s ease, color 0.2s ease !important;
    }

    body, h1, h2, h3, h4, h5, h6, p, span, a, label, select, input, textarea, td, th, button {
        font-family: var(--base-font-family, 'Inter', sans-serif) !important;
    }

    body { font-size: 1rem !important; }
    p, span, td, th, label, input, select, textarea, .sidebar a { font-size: 1rem; }
    h1 { font-size: 2rem !important; }
    h2 { font-size: 1.5rem !important; }
    h3 { font-size: 1.25rem !important; }
    small { font-size: 0.75rem !important; }

    /* ==========================================================================
       📊 DARK MODE INLINE TEXT PATCH
       ========================================================================== */
    [style*="background:#121224"] span, 
    [style*="background: #121224"] span,
    [style*="background-color: #121224"] span,
    div[style*="background"] span,
    .dashboard-card span {
        color: #e2e8f0 !important;
        opacity: 1 !important;
    }

    [style*="background:#121224"] h2,
    [style*="background: #121224"] h2,
    [style*="background-color: #121224"] h2,
    div[style*="background"] h2 {
        color: #ffffff !important;
    }

    th, thead th, .table th, [style*="background:#0f0f1a"] th, [style*="background: #0f0f1a"] th {
        color: #71717a !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
    }

    /* ==========================================================================
       ☀️ LIGHT MODE AUTO OVERRIDE SYSTEM
       ========================================================================== */
    html.light-mode body {
        background-color: #f4f4f7 !important;
        color: #0f172a !important;
    }

    html.light-mode .sidebar {
        background-color: #ffffff !important;
        border-right: 1px solid rgba(0, 0, 0, 0.08) !important;
    }
    html.light-mode .sidebar img,
    html.light-mode .smartville-brand-header img {
        max-height: 35px !important;
        width: auto !important;
        object-fit: contain !important;
    }
    html.light-mode .sidebar a {
        color: #475569 !important;
    }
    html.light-mode .sidebar a:hover {
        background-color: #f1f5f9 !important;
    }
    html.light-mode .sidebar a.active {
        background-color: rgba(219, 39, 119, 0.08) !important;
        color: #db2777 !important;
    }
    html.light-mode .sidebar-avatar-wrapper {
        background: #f8fafc !important;
        border: 1px solid rgba(0, 0, 0, 0.06) !important;
    }
    html.light-mode .sidebar-meta-info span {
        color: #0f172a !important;
    }
    html.light-mode .sidebar-meta-info span:first-child {
        color: #64748b !important;
    }

    html.light-mode .main-content,
    html.light-mode .content-container,
    html.light-mode .panel,
    html.light-mode .settings-card,
    html.light-mode .card,
    html.light-mode [style*="background:#0f0f1a"],
    html.light-mode [style*="background: #0f0f1a"],
    html.light-mode [style*="background-color: #0f0f1a"],
    html.light-mode [style*="background-color:#0f0f1a"] {
        background-color: #ffffff !important;
        color: #0f172a !important;
        border-color: rgba(0, 0, 0, 0.08) !important;
    }

    html.light-mode .stat-card {
        background: #ffffff !important;
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
    }
    html.light-mode .stat-card h3 { color: #64748b !important; }
    html.light-mode .stat-card p { color: #0f172a !important; }

    html.light-mode th, html.light-mode thead th, html.light-mode [style*="background:#0f0f1a"] th {
        color: #0f172a !important; 
    }
    html.light-mode td { color: #334155 !important; }
    html.light-mode td strong { color: #0f172a !important; }

    /* Badges & Status Controls */
    html.light-mode .status-badge, html.light-mode [class*="status-"] { opacity: 1 !important; }
    html.light-mode [style*="border-left: 5px solid #10b981"], html.light-mode [style*="border-left: 4px solid #10b981"] { border-left: 5px solid #10b981 !important; }
    html.light-mode [style*="border-left: 5px solid #ef4444"], html.light-mode [style*="border-left: 4px solid #ef4444"] { border-left: 5px solid #ef4444 !important; }
    html.light-mode [style*="border-left: 5px solid #f59e0b"], html.light-mode [style*="border-left: 4px solid #f59e0b"] { border-left: 5px solid #f59e0b !important; }

    html.light-mode .status-pending, html.light-mode [class*="pending"] {
        background: rgba(245, 158, 11, 0.12) !important; color: #b45309 !important; border: 1px solid rgba(245, 158, 11, 0.4) !important;
    }
    html.light-mode .status-approved, html.light-mode [class*="approved"], html.light-mode [class*="success"] {
        background: rgba(16, 185, 129, 0.12) !important; color: #047857 !important; border: 1px solid rgba(16, 185, 129, 0.4) !important;
    }
    html.light-mode .status-published, html.light-mode [class*="published"] {
        background: rgba(236, 72, 153, 0.12) !important; color: #be185d !important; border: 1px solid rgba(236, 72, 153, 0.4) !important;
    }
    html.light-mode [class*="decline"], html.light-mode [class*="reject"] {
        background: rgba(239, 68, 68, 0.12) !important; color: #b91c1c !important; border: 1px solid rgba(239, 68, 68, 0.4) !important;
    }

    /* Info Card & Boxes Override */
    html.light-mode .info-card {
        background: #ffffff !important; border: 1px solid rgba(0, 0, 0, 0.08) !important; box-shadow: 0 2px 8px rgba(0,0,0,0.03) !important;
    }
    html.light-mode .info-card p { color: #1e293b !important; }
    html.light-mode .info-card small { color: #64748b !important; }
    html.light-mode .info-card:hover { background: #f8fafc !important; border-color: #cbd5e1 !important; }

    html.light-mode [style*="background: #1c1c34"], html.light-mode [style*="background:#1c1c34"],
    html.light-mode [style*="background-color: #1c1c34"], html.light-mode [style*="background: #131324"], html.light-mode [style*="background:#131324"] {
        background: #ffffff !important; background-color: #ffffff !important; border: 1px solid rgba(0, 0, 0, 0.08) !important;
    }

    html.light-mode [style*="background: #1c1c34"] h3, html.light-mode [style*="color: #ec4899"], html.light-mode [style*="color:#ec4899"] { color: #0f172a !important; }
    html.light-mode [style*="background: #1c1c34"] span, html.light-mode [style*="background: #1c1c34"] p, html.light-mode [style*="background: #131324"] p { color: #334155 !important; }

    html.light-mode .btn-primary {
        background: #f1f5f9 !important; color: #0f172a !important; border: 1px solid #cbd5e1 !important;
    }
    html.light-mode .btn-primary:hover { background: #e2e8f0 !important; }

    html.light-mode [style*="background:#131326"], html.light-mode [style*="background: #131326"], html.light-mode .upcoming-event-box, html.light-mode .event-item {
        background-color: #ffffff !important; color: #0f172a !important; border: 1px solid rgba(0, 0, 0, 0.06) !important;
    }

    html.light-mode h1, html.light-mode h2, html.light-mode h3, html.light-mode h4, html.light-mode label { color: #0f172a !important; }
    html.light-mode p, html.light-mode small, html.light-mode span, html.light-mode .settings-desc { color: #475569 !important; }

    /* Badges & Status Controls */
    html.light-mode input[type="file"] {
        background-color: #ffffff !important; color: #0f172a !important; border: 1px solid #cbd5e1 !important; padding: 8px !important;
    }
    html.light-mode input[type="text"], html.light-mode input[type="date"], html.light-mode select, html.light-mode textarea {
        background-color: #ffffff !important; color: #0f172a !important; border: 1px solid #cbd5e1 !important;
    }
    html.light-mode [style*="background:#1e1b4b"] { background-color: #fce7f3 !important; color: #db2777 !important; border-color: #fbcfe8 !important; }

    /* ==========================================================================
       STRUCTURAL ALIGNMENTS & DYNAMIC FLEX LAYOUT
       ========================================================================== */
    .sidebar {
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        width: 240px;
        z-index: 1000;
        padding: 20px 0; 
    }

    .sidebar-menu-items {
        flex: 1; 
        display: flex;
        flex-direction: column;
    }

    .smartville-brand-header { display: flex; align-items: center; gap: 12px; margin-bottom: 30px; padding: 0 20px; }
    .smartville-brand-header img { height: 32px; width: auto; object-fit: contain; }
    .smartville-brand-header h2 { color: #ec4899 !important; font-size: 24px !important; font-weight: 700; margin: 0 !important; letter-spacing: -0.5px; }
    
    .sidebar-avatar-wrapper { display: flex; align-items: center; gap: 12px; padding: 14px; margin: 0 16px 25px 16px; background: #1c1c34; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.05); }
    .sidebar-avatar-circle { width: 44px; height: 44px; background: #ec4899; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: bold; color: white !important; overflow: hidden; flex-shrink: 0; }
    .sidebar-avatar-circle img { width: 100%; height: 100%; object-fit: cover; }
    .sidebar-meta-info { min-width: 0; flex: 1; }
    
    /* Navigation link configurations */
    .sidebar a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        text-decoration: none;
    }

    /* 🚪 Sign Out bottom wrapper configuration */
    .sidebar-footer-wrapper {
        margin-top: auto; 
        padding-top: 15px;
    }
</style>

<?php
// 🛠️ UPDATED: Changed column 'email' to 'EMAIL_ORGANIZER' to resolve SQL column exceptions
$profile_query = $conn->prepare("SELECT full_name, profile_pic FROM `profile_organizer` WHERE EMAIL_ORGANIZER = ?");
$profile_query->bind_param("s", $session_email);
$profile_query->execute();
$profile_result = $profile_query->get_result();

$displayName = $session_email;
$sidebar_image_src = null;
$first_initial = '?';

if ($profile_result && $profile_result->num_rows > 0) {
    $user_row = $profile_result->fetch_assoc();
    if (!empty($user_row['full_name'])) {
        $full_name_clean = trim($user_row['full_name']);
        $name_parts = explode(' ', $full_name_clean);
        $displayName = htmlspecialchars($name_parts[0]);
        $first_initial = strtoupper(substr($displayName, 0, 1));
    }
    
    if (!empty($user_row['profile_pic'])) {
        $sidebar_image_src = 'data:image/jpeg;base64,' . base64_encode($user_row['profile_pic']);
    }
}
$profile_query->close();
?>

<nav class="sidebar" id="sidebar">
    <div class="smartville-brand-header">
        <img src="logo.png" alt="SmartVille Logo">
        <h2>SmartVille</h2>
    </div>
    
    <div class="sidebar-avatar-wrapper">
        <div class="sidebar-avatar-circle">
            <?php if ($sidebar_image_src): ?>
                <img src="<?php echo $sidebar_image_src; ?>" alt="Organizer Pic">
            <?php else: ?>
                <?php echo $first_initial; ?>
            <?php endif; ?>
        </div>
        <div class="sidebar-meta-info">
            <span style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: #a1a1aa;">Organizer</span>
            <span style="font-weight: 600; font-size: 14px; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #ffffff;" title="<?php echo $displayName; ?>">
                <?php echo $displayName; ?>
            </span>
        </div>
    </div>

    <div class="sidebar-menu-items">
        <a href="organizer_dashboard.php" class="<?php echo $current_page == 'organizer_dashboard.php' ? 'active' : ''; ?>">
            📊 Dashboard
        </a>
        <a href="inbox.php" class="<?php echo $current_page == 'inbox.php' ? 'active' : ''; ?>" style="display: flex; justify-content: space-between; align-items: center;" id="notiMenuLink">
            <span>📥 Notifications</span>
            <span id="notiBadge" style="display: none; background: #ef4444; color: white !important; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 20px; line-height: 1.4; transition: none !important;">
                <?php echo $unread_noti_count; ?>
            </span>
        </a>
        <a href="create_event.php" class="<?php echo $current_page == 'create_event.php' ? 'active' : ''; ?>">
            ✨ Create Event
        </a>
        <a href="manage_events.php" class="<?php echo $current_page == 'manage_events.php' ? 'active' : ''; ?>">
            🎫 Project / Events
        </a>
        <a href="profile.php" class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
            👤 My Profile
        </a>
        <a href="settings.php" class="<?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
            ⚙️ Settings
        </a>
    </div>
    
    <div class="sidebar-footer-wrapper">
        <a href="logout.php" onclick="return confirmLogout(event);" style="color: #ef4444; border-left: none !important;">
            🚪 Sign Out
        </a>
    </div>
</nav>

<script>
// ==========================================================================
//				Notification will gone after see
// ==========================================================================
(function() {
    const currentCountFromDB = <?php echo $unread_noti_count; ?>;
    const lastReadCount = parseInt(localStorage.getItem('smartville_last_read_count') || '0', 10);
    const badge = document.getElementById('notiBadge');
    const currentPage = "<?php echo $current_page; ?>";

   //if its in inbox, automatically update
    if (currentPage === 'inbox.php') {
        localStorage.setItem('smartville_last_read_count', currentCountFromDB);
        if (badge) badge.style.display = 'none';
    } else {
        //if in different interfaces, show only update from the admin
        if (currentCountFromDB > lastReadCount && currentCountFromDB > 0) {
            if (badge) {
                //show remaining notification that its new
                badge.innerText = currentCountFromDB - lastReadCount;
                badge.style.display = 'inline-block';
            }
        } else {
            if (badge) badge.style.display = 'none';
        }
    }
})();

function syncSidebarState() {
    if (localStorage.getItem('sidebarHidden') === 'true') {
        document.getElementById('sidebar')?.classList.add('hidden');
        document.getElementById('main-content')?.classList.add('expanded');
    }
}
syncSidebarState();
window.addEventListener('DOMContentLoaded', syncSidebarState);

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    if (sidebar && mainContent) {
        requestAnimationFrame(() => {
            sidebar.classList.toggle('hidden');
            mainContent.classList.toggle('expanded');
            localStorage.setItem('sidebarHidden', sidebar.classList.contains('hidden'));
        });
    }
}
</script>