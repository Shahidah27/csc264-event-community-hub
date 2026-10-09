<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('db_connect.php');

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php?role=resident&error=unauthorized");
    exit();
}

$resident_email = $_SESSION['user_email'];
$user_name = $_SESSION['fullname'] ?? 'Resident1';
// Dynamically get current filename so links don't break if you rename the file
$current_page = basename(__FILE__); 

// 1. Process inline detailed data request if a card's "View Detail" button is targeted
$selected_event = null;
$participation_details = null;

if (isset($_GET['detail_title'])) {
    $detail_title = trim($_GET['detail_title']);
    
    // ✨ FIX: Menggunakan nama kolom HURUF KAPITAL sesuai database (`EVENT_TITLE`)
    $stmt_detail = $conn->prepare("SELECT * FROM `event` WHERE `EVENT_TITLE` = ?");
    $stmt_detail->bind_param("s", $detail_title);
    $stmt_detail->execute();
    $selected_event = $stmt_detail->get_result()->fetch_assoc();
    $stmt_detail->close();
    
    if ($selected_event) {
        // ✨ FIX: Kolom diubah ke HURUF KAPITAL (`PAX`, `PAYMENT_STATUS`, `JOIN_DATE`, `EVENT_TITLE`, `EMAIL_RESIDENT`)
        $stmt_part = $conn->prepare("SELECT `PAX`, `PAYMENT_STATUS`, `JOIN_DATE` FROM `event_participation` WHERE `EVENT_TITLE` = ? AND `EMAIL_RESIDENT` = ?");
        $stmt_part->bind_param("ss", $detail_title, $resident_email);
        $stmt_part->execute();
        $participation_details = $stmt_part->get_result()->fetch_assoc();
        $stmt_part->close();
    }
}

// 2. Fetch all items that are not marked as 'Removed'
// 🛠️ FIXED: Removed 'LIMIT 3' to show all joined events
$query = "SELECT e.*, ep.`PAX`, ep.`PAYMENT_STATUS`, ep.`JOIN_DATE` 
          FROM `event_participation` ep
          INNER JOIN `event` e ON ep.`EVENT_TITLE` = e.`EVENT_TITLE` 
          WHERE ep.`EMAIL_RESIDENT` = ? 
          AND LOWER(e.`PUBLISH_STATUS`) != 'removed'
          ORDER BY e.`DATE` ASC, e.`TIME` ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $resident_email);
$stmt->execute();
$registered_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartVille - My Registered Events</title>
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            const savedSize = localStorage.getItem('fontSize') || '16';
            if (savedTheme === 'light') {
                document.documentElement.classList.add('light-mode');
            }
            document.documentElement.style.setProperty('--base-font-size', savedSize + 'px');
            
            const sidebarHidden = localStorage.getItem('sidebarHidden');
            if (sidebarHidden === 'true') {
                document.documentElement.classList.add('preload-sidebar-hidden');
            }
        })();
    </script>

    <style>
        :root {
            --bg-main: #070a13;
            --bg-card: #141a2f;
            --bg-input: #1e2642;
            --text-primary: #ffffff;
            --text-muted: #94a3b8;
            --accent-purple: #c084fc;
            --accent-pink: #ec4899;
            --border-line: rgba(255, 255, 255, 0.06);
            --modal-overlay: rgba(3, 5, 11, 0.75);
            
            --modal-bg: #141a2f;
            --modal-text-main: #ffffff;
            --modal-text-sub: #94a3b8;
            --modal-box-bg: rgba(255, 255, 255, 0.02);
            --modal-box-border: rgba(255, 255, 255, 0.06);
            --modal-status-bg: rgba(16, 185, 129, 0.08);
            --modal-status-border: rgba(16, 185, 129, 0.2);
            --modal-status-text: #10b981;
            --modal-close-hover: #ffffff;

            --toggle-btn-bg: #1c1c34;
            --toggle-btn-border: rgba(255, 255, 255, 0.1);
            --toggle-btn-text: #ffffff;
        }

        html.light-mode body, html.light-mode {
            --bg-main: #f8fafc !important;
            --bg-card: #ffffff !important;
            --bg-input: #f1f5f9 !important;
            --text-primary: #0f172a !important;
            --text-muted: #64748b !important;
            --border-line: rgba(0, 0, 0, 0.08) !important;
            --modal-overlay: rgba(15, 23, 42, 0.5) !important;
            
            --modal-bg: #ffffff;
            --modal-text-main: #0f172a;
            --modal-text-sub: #475569;
            --modal-box-bg: #f8fafc;
            --modal-box-border: #e2e8f0;
            --modal-status-bg: #f0fdf4;
            --modal-status-border: #bbf7d0;
            --modal-status-text: #16a34a;
            --modal-close-hover: #0f172a;

            --toggle-btn-bg: #1c1c34 !important;
            --toggle-btn-border: rgba(255, 255, 255, 0.1) !important;
            --toggle-btn-text: #ffffff !important;
        }

        html.light-mode .top-action-bar button {
            background: #e4e4e7 !important;
            color: #18181b !important;
            border: 1px solid rgba(0,0,0,0.15) !important;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-primary); min-height: 100vh; display: flex; overflow-x: hidden; }

        body.sidebar-hidden #sidebar, html.preload-sidebar-hidden #sidebar {
            transform: translateX(-240px) !important;
        }
        
        body.sidebar-hidden .app-container, html.preload-sidebar-hidden .app-container {
            margin-left: 0;
        }

        .app-container {
            flex: 1;
            width: 100%;
            min-width: 0; 
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin-left: 240px;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        }

        .main-content { padding: 40px; max-width: 1200px; width: 100%; margin: 0 auto; box-sizing: border-box; }
        .top-action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .workspace-heading h2 { font-size: 26px; font-weight: 700; color: var(--text-primary); }
        .workspace-heading p { color: var(--text-muted); font-size: 14px; margin-top: 4px; }

        .toggle-menu-btn {
            background: var(--toggle-btn-bg) !important; 
            border: 1px solid var(--toggle-btn-border) !important; 
            color: var(--toggle-btn-text) !important; 
            padding: 8px 16px; 
            border-radius: 8px; 
            cursor: pointer; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            font-size: 14px; 
            font-weight: 500; 
            height: 38px; 
            box-sizing: border-box;
            transition: background 0.2s;
        }
        .toggle-menu-btn:hover {
            background: #252542 !important;
        }

        .events-grid { 
            display: flex;
            flex-wrap: wrap;
            gap: 20px; 
            width: 100%;
        }
        
        .event-card { 
            background: var(--bg-card); 
            border-radius: 20px; 
            overflow: hidden; 
            display: flex; 
            flex-direction: column; 
            border: 1px solid var(--border-line); 
            box-shadow: 0 4px 20px rgba(0,0,0,0.05); 
            flex: 1 1 calc(33.333% - 14px);
            min-width: 280px;
            max-width: calc(33.333% - 14px);
            transition: transform 0.2s, box-shadow 0.2s, all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        }

        @media (max-width: 992px) {
            .event-card { flex: 1 1 calc(50% - 10px); max-width: calc(50% - 10px); } 
        }
        @media (max-width: 576px) {
            .event-card { flex: 1 1 100%; max-width: 100%; } 
        }

        .event-card:hover { transform: translateY(-4px); }
        
        .card-banner { height: 160px; background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%); padding: 24px; }
        .card-body { padding: 24px; display: flex; flex-direction: column; flex: 1; }
        .card-title { font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
        .card-desc { color: var(--text-muted); font-size: 14px; line-height: 1.6; margin-bottom: 16px; flex: 1; }
        .pax-badge { font-size: 13px; margin-bottom: 10px; color: var(--accent-pink); font-weight: bold; }
        
        .view-btn { background: var(--bg-input); border: 1px solid var(--border-line); color: var(--text-primary); padding: 10px 20px; border-radius: 10px; text-decoration: none; font-size: 13px; font-weight: 700; text-align: center; transition: all 0.2s; }
        .view-btn:hover { background: var(--border-line); }

        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: var(--modal-overlay);
            display: flex; justify-content: center; align-items: center;
            z-index: 9999; padding: 20px;
            backdrop-filter: blur(5px);
        }
        .detail-workspace-card {
            background: var(--modal-bg);
            border-radius: 28px; padding: 35px; max-width: 680px; width: 100%;
            max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 60px rgba(0,0,0,0.4);
            animation: modalSlideIn 0.2s ease-out;
            border: 1px solid rgba(192, 132, 252, 0.2);
        }
        html.light-mode .detail-workspace-card { border: none; }
        @keyframes modalSlideIn {
            from { transform: scale(0.96); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        .modal-title-text { font-size: 26px; font-weight: 800; color: var(--modal-text-main); }
        .modal-sub-meta { font-size: 14px; color: var(--modal-text-sub); font-weight: 600; display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px; }
        .modal-sub-meta strong { color: var(--modal-text-main); }
        
        .detail-section-header { font-size: 13px; font-weight: 800; text-transform: uppercase; color: #ec4899; margin-top: 22px; margin-bottom: 8px; letter-spacing: 0.5px; }
        
        .detail-content-box { 
            background: var(--modal-box-bg); 
            border: 1px solid var(--modal-box-border); 
            border-radius: 12px; 
            padding: 14px 18px; 
            color: var(--modal-text-main) !important;
            font-size: 14.5px; 
            line-height: 1.6;
            font-weight: 500;
        }
        html.light-mode .detail-content-box { color: #000000 !important; }
        
        .detail-status-banner { 
            background: var(--modal-status-bg); 
            border: 1px solid var(--modal-status-border); 
            color: var(--modal-status-text); 
            padding: 16px; 
            border-radius: 12px; 
            font-size: 14px; 
            text-align: center; 
            font-weight: 700; 
            margin-top: 25px; 
        }
        .detail-status-banner .sub-badge-txt { font-weight: 600; font-size: 12.5px; margin-top: 6px; color: var(--modal-text-sub); }
        html.light-mode .detail-status-banner .sub-badge-txt strong { color: #000000; }
        
        .close-icon-btn { color: #64748b; text-decoration: none; font-size: 26px; font-weight: bold; line-height: 1; transition: color 0.2s; }
        .close-icon-btn:hover { color: var(--modal-close-hover); }
        
        .modal-action-close-btn {
            background: var(--modal-bg); color: var(--modal-text-main); border: 1px solid var(--modal-box-border); padding: 12px 28px;
            border-radius: 10px; font-size: 13.5px; font-weight: 700; text-decoration: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.04); transition: all 0.2s;
        }
        .modal-action-close-btn:hover { background: var(--bg-input); }

        @media (max-width: 992px) {
            .app-container, body.sidebar-hidden .app-container { margin-left: 0 !important; }
            body:not(.sidebar-hidden) #sidebar { transform: translateX(-240px) !important; }
            body.sidebar-hidden #sidebar { transform: translateX(0) !important; }
        }
    </style>
</head>
<body>

    <?php include('resident_sidebar_helper.php'); ?>

    <div class="app-container" id="mainContentWrapper">
        <main class="main-content" id="main-content">
            
            <div class="top-action-bar">
                <div class="workspace-heading">
                    <h2>My Registered Events</h2>
                    <p>View upcoming community schedules (Read-Only)</p>
                </div>
                <button type="button" class="toggle-menu-btn" onclick="toggleSidebarMenu()">
                    &#9776; Toggle Menu
                </button>
            </div>

            <div class="events-grid">
                <?php if ($registered_result && $registered_result->num_rows > 0): ?>
                    <?php while($row = $registered_result->fetch_assoc()): ?>
                        <div class="event-card">
                            <div class="card-banner"></div>
                            <div class="card-body">
                                <div class="card-title"><?php echo htmlspecialchars($row['EVENT_TITLE'] ?? ''); ?></div>
                                <div class="card-desc"><?php echo htmlspecialchars(substr($row['DESCRIPTION'] ?? '', 0, 75)) . '...'; ?></div>
                                
                                <div class="pax-badge">👥 Registered for: <?php echo htmlspecialchars($row['PAX'] ?? '0'); ?> Pax</div>
                                <div style="color: var(--text-muted); font-size:13px; margin-bottom: 10px;">📍 Venue: <?php echo htmlspecialchars($row['VENUE'] ?? 'TBA'); ?></div>

                                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:auto;">
                                    <span style="color:var(--accent-purple); font-size: 13px; font-weight:600;">📅 <?php echo htmlspecialchars($row['DATE'] ?? 'Upcoming'); ?></span>
                                    <a href="<?php echo $current_page; ?>?detail_title=<?php echo urlencode($row['EVENT_TITLE'] ?? ''); ?>" class="view-btn">View Detail</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: var(--text-muted); grid-column: 1/-1; text-align:left; font-size: 14.5px; font-style: italic;">You haven't joined any events yet.</p>
                <?php endif; ?>
            </div>

            <?php if ($selected_event && $participation_details): ?>
                <div class="modal-overlay" id="detailModal">
                    <div class="detail-workspace-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h2 class="modal-title-text"><?php echo htmlspecialchars($selected_event['EVENT_TITLE'] ?? ''); ?></h2>
                            <a href="<?php echo $current_page; ?>" class="close-icon-btn">&times;</a>
                        </div>

                        <div class="modal-sub-meta">
                            <span>📅 <strong>Date:</strong> <?php echo htmlspecialchars($selected_event['DATE'] ?? 'TBA'); ?></span>
                            <span>⏰ <strong>Time:</strong> <?php echo htmlspecialchars($selected_event['TIME'] ?? 'TBA'); ?></span>
                            <span>📍 <strong>Venue:</strong> <?php echo htmlspecialchars($selected_event['VENUE'] ?? 'TBA'); ?></span>
                            <span>🏷️ <strong>Category:</strong> <?php echo htmlspecialchars($selected_event['CATEGORY'] ?? 'General'); ?></span>
                        </div>

                        <div class="detail-section-header">Description</div>
                        <div class="detail-content-box">
                            <?php echo nl2br(htmlspecialchars($selected_event['DESCRIPTION'] ?? '')); ?>
                        </div>

                        <div class="detail-section-header">🕒 Event Flow / Timeline Agenda</div>
                        <div class="detail-content-box" style="border-left: 3px solid var(--accent-pink);">
                            <?php echo nl2br(htmlspecialchars($selected_event['EVENT_FLOW'] ?? 'No schedule sequence configured.')); ?>
                        </div>

                        <div class="detail-status-banner">
                            ✓ You have already registered for this event. Information cannot be edited anymore.
                            <div class="sub-badge-txt">
                                Booked Allocation: <strong><?php echo htmlspecialchars($participation_details['PAX'] ?? '0'); ?> Pax</strong> | 
                                Access Tier Code: <strong><?php echo htmlspecialchars($participation_details['PAYMENT_STATUS'] ?? ''); ?></strong> | 
                                Registered On: <strong><?php echo htmlspecialchars($participation_details['JOIN_DATE'] ?? ''); ?></strong>
                            </div>
                        </div>

                        <div style="margin-top: 30px; display: flex; justify-content: flex-end;">
                            <a href="<?php echo $current_page; ?>" class="modal-action-close-btn">Close Details</a>
                        </div>
                    </div>
                </div>
                
                <script>
                    document.getElementById('detailModal').addEventListener('click', function(e) {
                        if (e.target === this) {
                            window.location.href = '<?php echo $current_page; ?>';
                        }
                    });
                </script>
            <?php endif; ?>

        </main>
    </div>

    <script>
        function toggleSidebarMenu() {
            const body = document.body;
            const isHidden = body.classList.contains('sidebar-hidden');
            
            if (isHidden) {
                body.classList.remove('sidebar-hidden');
                document.documentElement.classList.remove('preload-sidebar-hidden');
                localStorage.setItem('sidebarHidden', 'false');
            } else {
                body.classList.add('sidebar-hidden');
                localStorage.setItem('sidebarHidden', 'true');
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            const sidebarHidden = localStorage.getItem('sidebarHidden');
            if (sidebarHidden === 'true') {
                document.body.classList.add('sidebar-hidden');
            } else {
                document.body.classList.remove('sidebar-hidden');
            }
        });
    </script>
</body>
</html>