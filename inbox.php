<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('db_connect.php');

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$user_email = trim($_SESSION['user_email']); 

// 📝 PERBAIKAN: Menggunakan nama lajur database 'EMAIL_ORGANIZER' berbanding 'organizer_email'
$query = "SELECT event_title, category, event_type, approval_status, date, venue, ticket_price 
          FROM `event` 
          WHERE LOWER(EMAIL_ORGANIZER) = LOWER('$user_email') 
          AND LOWER(approval_status) IN ('approved', 'rejected', 'published') 
          ORDER BY date DESC";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox - SmartVille</title>
    <link rel="stylesheet" href="style.css">
    <style>
        html.light-mode { background-color: #f4f4f7 !important; }
        html.light-mode body, html.light-mode .main-content { background: #f4f4f7 !important; color: #18181b !important; }
        html.light-mode .header-top h1 { color: #18181b !important; }

        html.light-mode .session-log-box {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            color: #52525b !important;
        }
        html.light-mode .panel { 
            background: #ffffff !important; 
            border-color: rgba(0,0,0,0.08) !important; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); 
        }
        
        html.light-mode .panel h3 { color: #18181b !important; }
        html.light-mode .panel p { color: #27272a !important; }
        html.light-mode .panel .meta-text { color: #71717a !important; }
        html.light-mode .panel .meta-text strong { color: #18181b !important; }

        html.light-mode .panel-history { opacity: 0.65; }
        html.light-mode .panel-history h3, html.light-mode .panel-history p { color: #71717a !important; }
        html.light-mode .panel-history .meta-text { color: #a1a1aa !important; }
        html.light-mode .panel-history .inline-code { background: #f4f4f7 !important; border-color: rgba(0,0,0,0.1) !important; color: #18181b !important; }

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
                <h1>Notifications Inbox</h1>
                <div class="top-controls">
                    <button id="mainToggleBtn" class="btn-control" onclick="toggleSidebar()">☰ Toggle Menu</button>
                </div>
            </div>

            <div class="session-log-box" style="background: #1c1c34; border: 1px solid rgba(255, 255, 255, 0.05); padding: 14px; margin-bottom: 24px; border-radius: 8px; font-family: monospace; font-size: 13px; color: #a1a1aa; transition: background 0.3s, border-color 0.3s, color 0.3s;">
                Logged in user email session tracking: <strong style="color: #ec4899;"><?php echo htmlspecialchars($user_email); ?></strong>
            </div>

            <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <?php 
                            $status_check = strtolower($row['approval_status']);
                            $event_type_check = strtolower($row['event_type']);
                            
                            if ($status_check === 'published'): 
                                $borderColorPub = "#ec4899"; 
                                $statusTitlePub = "📢 Event Successfully Published!";
                                $statusMessagePub = "Your " . strtolower(htmlspecialchars($row['event_type'])) . " event <strong>\"" . htmlspecialchars($row['event_title']) . "\"</strong> is now officially LIVE and visible to all community residents.";
                        ?>
                                <div class="panel" style="border-left: 4px solid <?php echo $borderColorPub; ?>; padding: 24px; background: #0f0f1a; margin-bottom: 0px; transition: background 0.3s, border-color 0.3s;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                        <h3 style="margin: 0; font-size: 16px; color: #ffffff; font-weight: 600;"><?php echo $statusTitlePub; ?></h3>
                                        <span style="background: rgba(255, 255, 255, 0.05); border: 1px solid <?php echo $borderColorPub; ?>; color: <?php echo $borderColorPub; ?>; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                            PUBLISHED
                                        </span>
                                    </div>
                                    <p style="margin: 0 0 14px 0; font-size: 14px; color: #e4e4e7; line-height: 1.6;">
                                        <?php echo $statusMessagePub; ?>
                                    </p>
                                    <div class="meta-text" style="color: #71717a; font-size: 13px; display: flex; gap: 16px; flex-wrap: wrap;">
                                        <span>📍 Venue: <strong style="color: #ffffff; font-weight: 500;"><?php echo htmlspecialchars($row['venue']); ?></strong></span>
                                        <span>📅 Date: <strong style="color: #ffffff; font-weight: 500;"><?php echo htmlspecialchars($row['date']); ?></strong></span>
                                    </div>
                                </div>

                        <?php 
                                $borderColorApp = "#10b981"; 
                                $statusTitleApp = "🎉 Event Approved (Past Notification History)";
                                if (strpos($event_type_check, 'private') !== false) {
                                    $statusMessageApp = "Your proposed Private event <strong>\"" . htmlspecialchars($row['event_title']) . "\"</strong> was successfully approved. <br><span class='inline-code' style='display:inline-block; margin-top:8px; padding:6px 10px; background:#1c1c34; border: 1px solid rgba(255,255,255,0.08); border-radius:6px; font-family:monospace; color:#ffffff;'>🔑 Invite Code: " . htmlspecialchars($row['ticket_price']) . "</span> Use this code to register your attendee lists.";
                                } else {
                                    $statusMessageApp = "Your proposed event <strong>\"" . htmlspecialchars($row['event_title']) . "\"</strong> was approved by the administration board and was set to pending publication.";
                                }
                        ?>
                                <div class="panel panel-history" style="border-left: 4px solid <?php echo $borderColorApp; ?>; padding: 24px; background: #0f0f1a; margin-bottom: 0px; opacity: 0.7; transition: background 0.3s, border-color 0.3s, opacity 0.3s;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                        <h3 style="margin: 0; font-size: 16px; color: #a1a1aa; font-weight: 600;"><?php echo $statusTitleApp; ?></h3>
                                        <span style="background: rgba(255, 255, 255, 0.02); border: 1px solid <?php echo $borderColorApp; ?>; color: <?php echo $borderColorApp; ?>; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                            APPROVED
                                        </span>
                                    </div>
                                    <p style="margin: 0 0 14px 0; font-size: 14px; color: #a1a1aa; line-height: 1.6;">
                                        <?php echo $statusMessageApp; ?>
                                    </p>
                                    <div class="meta-text" style="color: #52525b; font-size: 13px; display: flex; gap: 16px; flex-wrap: wrap;">
                                        <span>📍 Venue: <strong><?php echo htmlspecialchars($row['venue']); ?></strong></span>
                                        <span>📅 Date: <strong><?php echo htmlspecialchars($row['date']); ?></strong></span>
                                    </div>
                                </div>

                        <?php 
                            else: 
                                if ($status_check === 'approved') {
                                    $borderColor = "#10b981"; 
                                    $statusTitle = "🎉 Event Approved";
                                    if (strpos($event_type_check, 'private') !== false) {
                                        $statusMessage = "Your proposed Private event <strong>\"" . htmlspecialchars($row['event_title']) . "\"</strong> has been approved. <br><span class='inline-code' style='display:inline-block; margin-top:8px; padding:6px 10px; background:#1c1c34; border: 1px solid rgba(255,255,255,0.08); border-radius:6px; font-family:monospace; color:#ffffff;'>🔑 Invite Code: " . htmlspecialchars($row['ticket_price']) . "</span> Use this code to invite your specific attendees list entries.";
                                    } else {
                                        $statusMessage = "Your proposed " . strtolower(htmlspecialchars($row['event_type'])) . " event <strong>\"" . htmlspecialchars($row['event_title']) . "\"</strong> has been approved by the administration board and is now ready to be managed.";
                                    }
                                } else {
                                    $borderColor = "#ef4444"; 
                                    $statusTitle = "⚠️ Event Rejected";
                                    $statusMessage = "Your proposed event <strong>\"" . htmlspecialchars($row['event_title']) . "\"</strong> was reviewed and declined by the administration board.";
                                }
                        ?>
                                <div class="panel" style="border-left: 4px solid <?php echo $borderColor; ?>; padding: 24px; background: #0f0f1a; margin-bottom: 0px; transition: background 0.3s, border-color 0.3s;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                        <h3 style="margin: 0; font-size: 16px; color: #ffffff; font-weight: 600;"><?php echo $statusTitle; ?></h3>
                                        <span style="background: rgba(255, 255, 255, 0.05); border: 1px solid <?php echo $borderColor; ?>; color: <?php echo $borderColor; ?>; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                            <?php echo htmlspecialchars($row['approval_status']); ?>
                                        </span>
                                    </div>
                                    <p style="margin: 0 0 14px 0; font-size: 14px; color: #e4e4e7; line-height: 1.6;">
                                        <?php echo $statusMessage; ?>
                                    </p>
                                    <div class="meta-text" style="color: #71717a; font-size: 13px; display: flex; gap: 16px; flex-wrap: wrap;">
                                        <span>📍 Venue: <strong style="color: #ffffff; font-weight: 500;"><?php echo htmlspecialchars($row['venue']); ?></strong></span>
                                        <span>📅 Date: <strong style="color: #ffffff; font-weight: 500;"><?php echo htmlspecialchars($row['date']); ?></strong></span>
                                    </div>
                                </div>
                        <?php endif; ?>

                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="panel" style="text-align: center; padding: 40px; background: #0f0f1a;">
                        <p style="color: #71717a; margin: 0; font-style: italic; font-size: 14px;">
                            No decision updates found matching this user account identity details.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <script>
    function updateInboxBtnTheme(theme) {
        const btn = document.getElementById('mainToggleBtn');
        if (!btn) return;
        
        if (theme === 'light') {
            btn.classList.add('btn-toggle-menu-light');
            btn.style.setProperty('background', '#e4e4e7', 'important');
            btn.style.setProperty('color', '#18181b', 'important');
            btn.style.setProperty('border', '1px solid rgba(0, 0, 0, 0.15)', 'important');
            
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
        } else {
            document.body.classList.toggle('sidebar-hidden');
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        const currentTheme = localStorage.getItem('theme') || 'dark';
        
        if (currentTheme === 'light') {
            document.documentElement.classList.add('light-mode');
            updateInboxBtnTheme('light');
        } else {
            document.documentElement.classList.remove('light-mode');
            updateInboxBtnTheme('dark');
        }
    });
    </script>
</body>
</html>