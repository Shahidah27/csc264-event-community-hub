<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('db_connect.php');

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php?role=resident&error=unauthorized");
    exit();
}

$user_id   = $_SESSION['user_id'] ?? 1; 
$user_name = $_SESSION['fullname'] ?? 'Resident1';

// Filters (Dropdown type & categories still process via PHP on change)
$type_filter = isset($_GET['type']) ? $conn->real_escape_string($_GET['type']) : 'All';
$cat_filter = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : 'All';

// Displays active events with 'published' status
$query = "SELECT * FROM `event` WHERE LOWER(`APPROVAL_STATUS`) = 'published'";

if ($type_filter === 'Free') {
    $query .= " AND `EVENT_TYPE` = 'Free'";
} elseif ($type_filter === 'Paid') {
    $query .= " AND `EVENT_TYPE` = 'Paid'";
} elseif ($type_filter === 'Private') {
    $query .= " AND `EVENT_TYPE` = 'Private'";
}

if ($cat_filter !== 'All') { 
    $query .= " AND `CATEGORY` = '$cat_filter'"; 
}

$query .= " ORDER BY `DATE` ASC, `TIME` ASC";
$events_result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartVille - Discover Events</title>
    
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
        /* 🎨 THEME SYSTEM SYNC */
        :root {
            --bg-main: #070a13;
            --bg-card: #141a2f;
            --bg-input: #1e2642;
            --text-primary: #ffffff;
            --text-muted: #94a3b8;
            --accent-purple: #c084fc;
            --accent-pink: #ec4899;
            --border-line: rgba(255, 255, 255, 0.06);
        }

        html.light-mode body, html.light-mode {
            --bg-main: #f8fafc !important;
            --bg-card: #ffffff !important;
            --bg-input: #f1f5f9 !important;
            --text-primary: #0f172a !important;
            --text-muted: #64748b !important;
            --border-line: rgba(0, 0, 0, 0.08) !important;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-primary); min-height: 100vh; display: flex; overflow-x: hidden; }

        body.sidebar-hidden #sidebar, html.preload-sidebar-hidden #sidebar {
            transform: translateX(-240px) !important;
        }

        .app-container {
            flex: 1;
            width: 100%;
            min-width: 0; 
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-left: 240px;
            transition: padding-left 0.4s cubic-bezier(0.25, 1, 0.5, 1);
            will-change: padding-left;
        }

        body.sidebar-hidden .app-container, html.preload-sidebar-hidden .app-container {
            margin-left: 0 !important;
            padding-left: 0 !important;
        }

        .top-action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        html.light-mode .top-action-bar button {
            background: #e4e4e7 !important;
            color: #18181b !important;
            border: 1px solid rgba(0,0,0,0.15) !important;
        }

        .main-content { padding: 40px; max-width: 1400px; width: 100%; margin: 0 auto; box-sizing: border-box; }
        .workspace-heading h2 { font-size: 26px; font-weight: 700; color: var(--text-primary); }
        .workspace-heading p { color: var(--text-muted); font-size: 14px; margin-top: 4px; }

        .filter-bar-container { 
            display: flex; 
            align-items: center;
            gap: 12px; 
            background: var(--bg-card); 
            padding: 12px 16px; 
            border-radius: 14px; 
            margin-bottom: 35px; 
            border: 1px solid var(--border-line); 
        }
        
        .search-wrapper { 
            flex: 1; 
            position: relative;
        }
        .search-wrapper input { 
            width: 100%; 
            padding: 11px 16px; 
            background: var(--bg-input); 
            border: 1px solid var(--border-line); 
            color: var(--text-primary); 
            border-radius: 10px;
            outline: none; 
            font-size: 14px;
        }
        
        .filter-group { 
            display: flex; 
            gap: 6px; 
            align-items: center;
        }

        .filter-btn { 
            background: var(--bg-input); 
            border: 1px solid var(--border-line); 
            color: var(--text-muted); 
            padding: 8px 16px; 
            border-radius: 10px; 
            cursor: pointer; 
            font-size: 13px;  
            font-weight: 600; 
            transition: all 0.2s ease; 
        }
        .filter-btn:hover {
            color: var(--text-primary);
            border-color: var(--text-muted);
        }
        .filter-btn.active { 
            background: linear-gradient(135deg, var(--accent-purple), var(--accent-pink)) !important; 
            color: #fff !important; 
            border-color: transparent !important;
        }
        
        .custom-select { 
            background: var(--bg-input); 
            color: var(--text-primary); 
            border: 1px solid var(--border-line); 
            padding: 10px 16px; 
            border-radius: 10px; 
            outline: none; 
            cursor: pointer; 
            font-size: 13px;
            font-weight: 600;
            min-width: 220px;
        }

        .events-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
            gap: 30px; 
        }
        
        .event-card { 
            background: var(--bg-card); 
            border-radius: 20px; 
            overflow: hidden; 
            display: flex; 
            flex-direction: column; 
            border: 1px solid var(--border-line); 
            box-shadow: 0 4px 20px rgba(0,0,0,0.05); 
            transition: transform 0.2s, cubic-bezier(0.25, 1, 0.5, 1); 
        }
        .event-card:hover { transform: translateY(-4px); }
        
        .card-banner { height: 160px; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); background-size: cover; background-position: center; }
        
        .card-body { padding: 24px; display: flex; flex-direction: column; flex: 1; }
        .card-title { font-size: 19px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
        .card-desc { color: var(--text-muted); font-size: 13.5px; line-height: 1.6; margin-bottom: 12px; flex: 1; }
        .card-meta { font-size: 12px; color: var(--text-muted); margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
        
        .join-btn { background: linear-gradient(135deg, #7c3aed, var(--accent-purple)); color: white; padding: 10px 24px; border-radius: 10px; text-decoration: none; font-size: 13px; font-weight: 700; text-align: center; cursor: pointer; border: none; display: inline-block; transition: opacity 0.2s; }
        .join-btn:hover { opacity: 0.9; }

        @media (max-width: 992px) {
            .app-container, body.sidebar-hidden .app-container { padding-left: 0 !important; }
            .filter-bar-container { flex-direction: column; align-items: stretch; gap: 14px; }
            body:not(.sidebar-hidden) #sidebar { transform: translateX(-240px) !important; }
            body.sidebar-hidden #sidebar { transform: translateX(0) !important; }
        }
    </style>
</head>
<body>

    <?php include('resident_sidebar_helper.php'); ?>

    <div class="app-container" id="mainContentWrapper">
        
        <main class="main-content">
            
            <div class="top-action-bar">
                <div class="workspace-heading">
                    <h2>Browse Events</h2>
                    <p>Discover what's happening around your community</p>
                </div>
                <button type="button" onclick="toggleSidebarMenu()" style="background: #1c1c34; border: 1px solid rgba(255,255,255,0.1); color: white; padding: 8px 16px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; height: 38px; box-sizing: border-box;">
                    &#9776; Toggle Menu
                </button>
            </div>

            <form method="GET" action="browse_events.php" class="filter-bar-container" id="filterForm" onsubmit="return false;">
                <div class="search-wrapper">
                    <input type="text" id="liveSearchInput" placeholder="Search events...">
                </div>
                
                <div class="filter-group">
                    <input type="hidden" name="type" id="type_input" value="<?php echo htmlspecialchars($type_filter); ?>">
                    <button type="button" class="filter-btn <?php echo $type_filter === 'All' ? 'active' : ''; ?>" onclick="setFilterState('All')">All</button>
                    <button type="button" class="filter-btn <?php echo $type_filter === 'Free' ? 'active' : ''; ?>" onclick="setFilterState('Free')">Free</button>
                    <button type="button" class="filter-btn <?php echo $type_filter === 'Paid' ? 'active' : ''; ?>" onclick="setFilterState('Paid')">Paid</button>
                    <button type="button" class="filter-btn <?php echo $type_filter === 'Private' ? 'active' : ''; ?>" onclick="setFilterState('Private')">Private</button>
                </div>

                <div>
                    <select name="category" class="custom-select" onchange="this.form.submit()">
                        <option value="All">All Categories</option>
                        <option value="Sports & Recreation" <?php echo $cat_filter === 'Sports & Recreation' ? 'selected' : ''; ?>>Sports & Recreation</option>
                        <option value="Technology & Coding" <?php echo $cat_filter === 'Technology & Coding' ? 'selected' : ''; ?>>Technology & Coding</option>
                        <option value="Lifestyle & Social" <?php echo $cat_filter === 'Lifestyle & Social' ? 'selected' : ''; ?>>Lifestyle & Social</option>
                        <option value="Academic & Study Groups" <?php echo $cat_filter === 'Academic & Study Groups' ? 'selected' : ''; ?>>Academic & Study Groups</option>
                    </select>
                </div>
            </form>

            <div class="events-grid" id="eventsGridContainer">
                <?php if ($events_result && $events_result->num_rows > 0): ?>
                    <?php while($row = $events_result->fetch_assoc()): 
                        $banner_style = (!empty($row['PICTURE']) && file_exists($row['PICTURE'])) 
                            ? "style='background-image: url(".htmlspecialchars($row['PICTURE']).");'" 
                            : "";
                        
                        $raw_value = $row['TICKET_PRICE'] ?? '';
                        $clean_passcode = trim(str_replace("Private Code:", "", $raw_value));
                        $clean_passcode = preg_replace('/[^A-Za-z0-9]/', '', $clean_passcode); 
                        $is_private = (isset($row['EVENT_TYPE']) && $row['EVENT_TYPE'] === 'Private');

                        $js_type = htmlspecialchars($row['EVENT_TYPE'] ?? '', ENT_QUOTES, 'UTF-8');
                        $js_pass = htmlspecialchars($clean_passcode, ENT_QUOTES, 'UTF-8');
                        $js_title = urlencode($row['EVENT_TITLE']);
                    ?>
                        <div class="event-card" data-title="<?php echo htmlspecialchars(strtolower($row['EVENT_TITLE'])); ?>" data-desc="<?php echo htmlspecialchars(strtolower($row['DESCRIPTION'] ?? '')); ?>">
                            <div class="card-banner" <?php echo $banner_style; ?>></div>
                            <div class="card-body">
                                <div class="card-title"><?php echo htmlspecialchars($row['EVENT_TITLE']); ?></div>
                                <div class="card-desc"><?php echo htmlspecialchars(substr($row['DESCRIPTION'] ?? '', 0, 95)) . (strlen($row['DESCRIPTION'] ?? '') > 95 ? '...' : ''); ?></div>
                                
                                <div class="card-meta">📅 <?php echo htmlspecialchars($row['DATE'] ?? ''); ?> pada <?php echo htmlspecialchars($row['TIME'] ?? ''); ?></div>
                                <div class="card-meta">📍 <?php echo htmlspecialchars($row['VENUE'] ?? 'Community Workspace'); ?></div>

                                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:15px;">
                                    <span style="color:#fbbf24; font-weight:700;">
                                        <?php 
                                        if ($is_private) {
                                            echo "🔒 Private Pass";
                                        } else {
                                            echo (isset($row['EVENT_TYPE']) && $row['EVENT_TYPE'] === 'Paid') ? "RM ".htmlspecialchars($row['TICKET_PRICE']) : htmlspecialchars($row['EVENT_TYPE'] ?? 'Free'); 
                                        }
                                        ?>
                                    </span>
                                    
                                    <button type="button" class="join-btn" onclick="verifyPrivatePass('<?php echo $js_type; ?>', '<?php echo $js_pass; ?>', 'join_event.php?event_title=<?php echo $js_title; ?>')">
                                        Join
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p id="dbEmptyMessage" style="color: var(--text-muted); grid-column: 1/-1; text-align:center; padding: 60px; font-style: italic;">No active events found in the database matching criteria.</p>
                <?php endif; ?>
                
                <p id="liveNoMatchMessage" style="display: none; color: var(--text-muted); grid-column: 1/-1; text-align:center; padding: 60px; font-style: italic;">No events match your current typing filter.</p>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const isHidden = localStorage.getItem('sidebarHidden') === 'true';
            if (isHidden) {
                document.body.classList.add('sidebar-hidden');
            } else {
                document.body.classList.remove('sidebar-hidden');
            }
            document.documentElement.classList.remove('preload-sidebar-hidden');

            // 🔍 INSTANT LIVE SEARCH ENGINE
            const searchInput = document.getElementById('liveSearchInput');
            const eventCards = document.querySelectorAll('.event-card');
            const noMatchMessage = document.getElementById('liveNoMatchMessage');

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const value = this.value.toLowerCase().trim();
                    let visibleCount = 0;

                    eventCards.forEach(card => {
                        const title = card.getAttribute('data-title') || '';
                        const desc = card.getAttribute('data-desc') || '';

                        if (title.includes(value) || desc.includes(value)) {
                            card.style.display = ""; // Show
                            visibleCount++;
                        } else {
                            card.style.display = "none"; // Hide
                        }
                    });

                    // Manage visible alerts if no matching items exist
                    if (visibleCount === 0 && eventCards.length > 0) {
                        noMatchMessage.style.display = "block";
                    } else {
                        noMatchMessage.style.display = "none";
                    }
                });
            }
        });

        function setFilterState(val) {
            document.getElementById('type_input').value = val;
            document.forms['filterForm'].submit();
        }

        function toggleSidebarMenu() {
            const sidebar = document.getElementById('sidebar');
            document.body.classList.toggle('sidebar-hidden');
            
            let isHidden = document.body.classList.contains('sidebar-hidden');
            
            if (sidebar) {
                sidebar.classList.toggle('hidden');
                isHidden = sidebar.classList.contains('hidden') || document.body.classList.contains('sidebar-hidden');
            }
            localStorage.setItem('sidebarHidden', isHidden);
        }

        function verifyPrivatePass(eventType, correctPasscode, targetUrl) {
            if (eventType === 'Private') {
                let residentInput = prompt("🔒 Access Restricted: This is a private community event.\nPlease enter the secure Key Pass code to sign up:");
                
                if (residentInput === null) {
                    return; 
                }
                
                if (residentInput.trim() === correctPasscode.trim()) {
                    alert("✅ Code Verified! Access Granted.");
                    window.location.href = targetUrl;
                } else {
                    alert("❌ Invalid Pass Code. Registration entry denied.");
                }
            } else {
                window.location.href = targetUrl;
            }
        }
    </script>
</body>
</html>