<?php
// admin_dashboard.php - Real-time Database Connected Admin Panel
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
include('db_connect.php'); 

if (!isset($_SESSION['user_email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php?role=admin"); 
    exit();
}

$session_email = $_SESSION['user_email'];

// Live Average Feedbacks Assessment (Updated to UPPERCASE)
$rating_query = $conn->query("SELECT AVG(CAST(`RATING` AS DECIMAL(3,2))) AS avg_rating FROM `FEEDBACK`");
$rating_row = $rating_query->fetch_assoc();
$average_rating = number_format(($rating_row['avg_rating'] ?? 0.0), 1);

// Combine counts from LOGIN_ORGANIZER & LOGIN_RESIDENT tables safely
$user_count_query = $conn->query("
    SELECT SUM(total) AS total_users FROM (
        SELECT COUNT(*) AS total FROM `LOGIN_ORGANIZER`
        UNION ALL
        SELECT COUNT(*) AS total FROM `LOGIN_RESIDENT`
    ) AS combined_users
");

if ($user_count_query) {
    $user_count_row = $user_count_query->fetch_assoc();
    $total_registered_users = number_format($user_count_row['total_users'] ?? 0);
} else {
    $total_registered_users = 0;
}

$weekly_ratings = [0, 0, 0, 0, 0, 0, 0]; 
$monday_time = strtotime('monday this week');

for ($i = 0; $i < 7; $i++) {
    $target_date = date('Y-m-d', strtotime("+$i days", $monday_time));
    
    // ✨ FIX: Extracted pure date from the SUBMISSION_DATE timestamp to avoid mismatches
    $day_query = $conn->prepare("SELECT AVG(`RATING`) as daily_avg FROM `FEEDBACK` WHERE DATE(`SUBMISSION_DATE`) = ?");
    $day_query->bind_param("s", $target_date);
    $day_query->execute();
    $day_res = $day_query->get_result()->fetch_assoc();
    
    if ($day_res['daily_avg'] !== null) {
        $weekly_ratings[$i] = round($day_res['daily_avg'], 1);
    }
    $day_query->close();
}

$chart_data_js = json_encode($weekly_ratings);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartVille Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #06060c !important;
            margin: 0;
            font-family: Inter, system-ui, -apple-system, sans-serif;
            color: #ffffff;
            overflow-x: hidden;
        }
        
        .main-content {
            margin-left: 240px !important;
            padding: 40px;
            min-height: 100vh;
            width: calc(100% - 240px) !important;
            box-sizing: border-box;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
                        width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: margin-left, width;
        }
        
        .main-content.expanded { margin-left: 0 !important; width: 100% !important; }
        .content-container { max-width: 1200px; margin: 0 auto; width: 100%; }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-top h1 { font-size: 28px; font-weight: 700; color: #ffffff; margin: 0; }

        .btn-control {
            background: #1c1c34; color: #ffffff; border: 1px solid rgba(255,255,255,0.08);
            padding: 10px 16px; border-radius: 8px; font-weight: 500; cursor: pointer; transition: background 0.2s;
        }
        .btn-control:hover { background: #27274a; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #0f0f1a; border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; padding: 24px; }
        .stat-card h3 { color: #71717a; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 12px 0; }
        .stat-card p { font-size: 32px; font-weight: 700; color: #ffffff; margin: 0 0 8px 0; }
        .card-footer-info { font-size: 12px; color: #a1a1aa; }

        .dashboard-split-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; width: 100%; }
        @media (max-width: 992px) { .dashboard-split-grid { grid-template-columns: 1fr; } }

        .panel { background: #0f0f1a; border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; padding: 24px; min-width: 0; }
        .panel h2 { font-size: 18px; font-weight: 600; margin: 0 0 20px 0; color: #ffffff; }

        .feed-item { display: flex; align-items: center; gap: 16px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04); border-radius: 12px; padding: 16px; margin-bottom: 12px; }
        .feed-item-icon { font-size: 20px; background: #1c1c34; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; }
        .feed-item-details { flex: 1; min-width: 0; }
        .feed-item-details h4 { margin: 0 0 4px 0; font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .feed-item-details p { margin: 0 0 4px 0; font-size: 12px; color: #a1a1aa; }
        .feed-item-time { font-size: 11px; color: #71717a; display: block; }

        .btn-action-view { background: linear-gradient(135deg, #a855f7, #ec4899); color: white; text-decoration: none; font-size: 12px; font-weight: 600; padding: 8px 14px; border-radius: 8px; transition: opacity 0.2s; }
        .btn-action-view:hover { opacity: 0.9; }
        .empty-feed-state { color: #71717a; font-size: 13px; text-align: center; padding: 40px 20px; }

        /* Light Mode Styles */
        html.light-mode body { background-color: #f4f4f7 !important; color: #18181b !important; }
        html.light-mode .header-top h1 { color: #18181b !important; }
        html.light-mode .btn-control { background: #ffffff; color: #18181b; border-color: rgba(0,0,0,0.15); }
        html.light-mode .stat-card, html.light-mode .panel { background: #ffffff !important; border: 1px solid rgba(0, 0, 0, 0.08) !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        html.light-mode .stat-card p, html.light-mode .panel h2 { color: #18181b !important; }
        html.light-mode .stat-card h3 { color: #71717a !important; }
        html.light-mode .card-footer-info { color: #4b5563 !important; }
        html.light-mode .feed-item { background: #f9fafb !important; border-color: rgba(0,0,0,0.06) !important; }
        html.light-mode .feed-item-icon { background: #e5e7eb !important; }
        html.light-mode .feed-item-details h4 { color: #18181b !important; }
    </style>
</head>
<body>
    
    <?php include('admin_sidebar_helper.php'); ?>

    <main class="main-content" id="main-content">
        <div class="content-container">
            
            <div class="header-top">
                <h1>Admin Control Panel</h1>
                <div class="top-controls">
                    <button class="btn-control" onclick="toggleSidebar()">☰ Toggle Menu</button>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>TOTAL REGISTERED USERS</h3>
                    <p>👥 <?php echo $total_registered_users; ?></p>
                    <span class="card-footer-info">👥 Total active Organizers and Residents registered</span>
                </div>
                
                <div class="stat-card">
                    <h3>PLATFORM RATING PERFORMANCE</h3>
                    <p>⭐ <?php echo $average_rating; ?> <span style="font-size: 16px; color: #71717a;">/ 5.0</span></p>
                    <span class="card-footer-info">📊 Computed feedback table rating metrics</span>
                </div>
            </div>

            <div class="dashboard-split-grid">
                
                <div class="panel">
                    <h2>Weekly Feedback Rating Chart</h2>
                    <div style="height: 280px; position: relative; width: 100%;">
                        <canvas id="adminWeeklyChart"></canvas>
                    </div>
                </div>
                
                <div class="panel">
                    <h2>Organizer Event Requests</h2>
                    <div class="feed-scroll-container">
                        <?php
                        $request_feed = $conn->query("SELECT `EVENT_TITLE`, `EMAIL_ORGANIZER`, `DATE` FROM `EVENT` WHERE `APPROVAL_STATUS` = 'Pending' ORDER BY `DATE` ASC LIMIT 3");
                        if ($request_feed && $request_feed->num_rows > 0):
                            while($req = $request_feed->fetch_assoc()):
                        ?>
                            <div class="feed-item">
                                <div class="feed-item-icon">📋</div>
                                <div class="feed-item-details">
                                    <h4><?php echo htmlspecialchars($req['EVENT_TITLE']); ?></h4>
                                    <p>From: <strong><?php echo htmlspecialchars(explode('@', $req['EMAIL_ORGANIZER'])[0]); ?></strong></p>
                                    <span class="feed-item-time">Scheduled: <?php echo htmlspecialchars($req['DATE']); ?></span>
                                </div>
                                <a href="admin_inbox.php" class="btn-action-view">Review</a>
                            </div>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <div class="empty-feed-state">
                                <p>✅ Operational check complete. No outstanding event requests require management actions.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <script>
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

    window.addEventListener('DOMContentLoaded', () => {
        const chartCanvas = document.getElementById('adminWeeklyChart');
        if (!chartCanvas) return;

        const ctx = chartCanvas.getContext('2d');
        const isLight = localStorage.getItem('theme') === 'light';

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Rating Averages',
                    data: <?php echo $chart_data_js; ?>,
                    backgroundColor: '#ec4899',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                resizeDelay: 100,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        min: 0, 
                        max: 5, 
                        grid: { color: isLight ? 'rgba(0, 0, 0, 0.06)' : 'rgba(255, 255, 255, 0.04)' },
                        ticks: { color: isLight ? '#4b5563' : '#71717a' }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { color: isLight ? '#4b5563' : '#71717a' }
                    }
                }
            }
        });
    });
    </script>
</body>
</html>