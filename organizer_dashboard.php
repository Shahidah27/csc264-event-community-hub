<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
include('db_connect.php');

// 🔒 Sekatan Keselamatan: Pastikan pengguna sudah log masuk
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$user_email = trim($_SESSION['user_email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Dashboard - SmartVille</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Light mode*/
        html.light-mode { background-color: #f4f4f7 !important; }
        html.light-mode body, html.light-mode .main-content { background: #f4f4f7 !important; color: #18181b !important; }
        html.light-mode .header-top h1, html.light-mode .panel h2 { color: #18181b !important; }
        
        html.light-mode .stat-card, html.light-mode .panel { 
            background: #ffffff !important; 
            border-color: rgba(0,0,0,0.08) !important; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); 
        }
        html.light-mode .stat-card h3 { color: #52525b !important; }
        html.light-mode .stat-card p { color: #18181b !important; }

        html.light-mode .event-list-item {
            background: #fdfdfd !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
        }
        html.light-mode .event-meta-date { color: #27272a !important; }
        html.light-mode .event-meta-venue { color: #71717a !important; }

        .btn-control {
            background: #1c1c34; 
            border: 1px solid rgba(255,255,255,0.1); 
            color: white; 
            padding: 8px 16px; 
            border-radius: 8px; 
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            height: 38px;
            box-sizing: border-box;
            transition: background 0.3s, color 0.3s, border-color 0.3s;
        }
        .btn-control:hover {
            background: #252542;
        }

        html.light-mode .btn-toggle-menu-light {
            background: #e4e4e7 !important;
            color: #18181b !important;
            border: 1px solid rgba(0, 0, 0, 0.15) !important;
        }
        html.light-mode .btn-toggle-menu-light:hover {
            background: #d4d4d8 !important;
            color: #18181b !important;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>

    <main class="main-content" id="main-content">
        <div class="content-container">
            
            <div class="header-top">
                <h1>Organizer Dashboard</h1>
                <div class="top-controls">
                    <button id="mainToggleBtn" class="btn-control" onclick="toggleSidebar()">☰ Toggle Menu</button>
                </div>
            </div>

            <p id="welcomeUserText" style="color: #a1a1aa; font-size: 14px; margin-bottom: 20px;">
                Welcome back, <strong><?php echo htmlspecialchars($user_email); ?></strong>
            </p>

            <?php
            // 📝 DIKEMAS KINI: Menggunakan lajur database 'EMAIL_ORGANIZER'
            $total_stmt = $conn->prepare("SELECT COUNT(*) as c FROM `event` WHERE LOWER(`EMAIL_ORGANIZER`) = LOWER(?)");
            $total_stmt->bind_param("s", $user_email);
            $total_stmt->execute();
            $total_res = $total_stmt->get_result()->fetch_assoc();
            $total = $total_res ? $total_res['c'] : 0;
            $total_stmt->close();

            // 🔒 DIKEMAS KINI: Mengubah lajur condition ke UPPERCASE (`APPROVAL_STATUS` & `PUBLISH_STATUS`)
            $pub_stmt = $conn->prepare("SELECT COUNT(*) as c FROM `event` WHERE LOWER(`EMAIL_ORGANIZER`) = LOWER(?) AND `APPROVAL_STATUS`='Published' AND LOWER(`PUBLISH_STATUS`) != 'removed'");
            $pub_stmt->bind_param("s", $user_email);
            $pub_stmt->execute();
            $pub_res = $pub_stmt->get_result()->fetch_assoc();
            $published = $pub_res ? $pub_res['c'] : 0;
            $pub_stmt->close();
            ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Your Total Events</h3>
                    <p><?php echo $total; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Your Published Events</h3>
                    <p><?php echo $published; ?></p>
                </div>
            </div>

            <div class="panel">
                <h2>Your Upcoming Events</h2>
                
                <?php
                // 🔥 DIKEMAS KINI: Menggunakan lajur SQL berhuruf besar (EMAIL_ORGANIZER, APPROVAL_STATUS, PUBLISH_STATUS, DATE)
                $event_stmt = $conn->prepare("SELECT * FROM `event` 
                                              WHERE LOWER(`EMAIL_ORGANIZER`) = LOWER(?) 
                                              AND `APPROVAL_STATUS` = 'Published' 
                                              AND LOWER(`PUBLISH_STATUS`) != 'removed' 
                                              ORDER BY `DATE` DESC LIMIT 3");
                $event_stmt->bind_param("s", $user_email);
                $event_stmt->execute();
                $event_records = $event_stmt->get_result();

                if ($event_records && $event_records->num_rows > 0) {
                    while($row = $event_records->fetch_assoc()) {
                        ?>
                        <div class="event-list-item" style="padding: 24px; border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; margin-bottom: 20px; background: #1c1c34; display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap; transition: background 0.3s, border-color 0.3s;">
                            <div>
                                <h3 style="color: #ec4899; font-size: 18px; margin-bottom: 12px; font-weight: 600;"><?php echo htmlspecialchars($row['EVENT_TITLE']); ?></h3>
                                <div style="display: flex; gap: 24px; flex-wrap: wrap;">
                                    <span class="event-meta-date" style="color: #e4e4e7; font-size: 14px; display: flex; align-items: center; gap: 6px;">📅 <?php echo htmlspecialchars($row['DATE']); ?></span>
                                    <span class="event-meta-venue" style="color: #a1a1aa; font-size: 14px; display: flex; align-items: center; gap: 6px;">📍 <?php echo htmlspecialchars($row['VENUE']); ?></span>
                                </div>
                            </div>
                            <div>
                                <a href="manage_events.php" class="btn-primary" style="font-size: 14px; padding: 10px 18px; text-decoration: none; border-radius: 6px; display: inline-block;">View Details</a>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p style='color: #71717a; font-style: italic; padding: 10px 0;'>No upcoming published events. Go to 'Project / Events' to check your status or create a new event.</p>";
                }
                $event_stmt->close();
                ?>
            </div>

        </div>
    </main>

    <script>
    function updateDashboardBtnTheme(theme) {
        const btn = document.getElementById('mainToggleBtn');
        const welcomeText = document.getElementById('welcomeUserText');
        if (!btn) return;
        
        if (theme === 'light') {
            btn.classList.add('btn-toggle-menu-light');
            btn.style.setProperty('background', '#e4e4e7', 'important');
            btn.style.setProperty('color', '#18181b', 'important');
            btn.style.setProperty('border', '1px solid rgba(0, 0, 0, 0.15)', 'important');
            if (welcomeText) welcomeText.style.setProperty('color', '#52525b', 'important');
            
            btn.onmouseover = function() {
                this.style.setProperty('background', '#d4d4d8', 'important');
            };
            btn.onmouseout = function() {
                this.style.setProperty('background', '#e4e4e7', 'important');
            };
        } else {
            btn.classList.remove('btn-toggle-menu-light');
            btn.style.removeProperty('background');
            btn.style.removeProperty('color');
            btn.style.removeProperty('border');
            if (welcomeText) welcomeText.style.setProperty('color', '#a1a1aa');
            
            btn.onmouseover = function() {
                this.style.setProperty('background', '#252542', 'important');
            };
            btn.onmouseout = function() {
                this.style.removeProperty('background');
            };
        }
    }

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        if (sidebar && mainContent) {
            sidebar.classList.toggle('hidden');
            mainContent.classList.toggle('expanded');
            localStorage.setItem('sidebarHidden', sidebar.classList.contains('hidden'));
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        const currentTheme = localStorage.getItem('theme') || 'dark';
        
        if (currentTheme === 'light') {
            document.documentElement.classList.add('light-mode');
            updateDashboardBtnTheme('light');
        } else {
            document.documentElement.classList.remove('light-mode');
            updateDashboardBtnTheme('dark');
        }
    });
    </script>
</body>
</html>