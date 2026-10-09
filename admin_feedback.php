<?php
// admin_feedback.php - User Review Assessment Portal
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
include('db_connect.php'); 

if (!isset($_SESSION['user_email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php?role=admin"); 
    exit();
}

// Mengambil seluruh data dari tabel feedback diurutkan dari yang terbaru
$feedbacks = $conn->query("SELECT * FROM `feedback` ORDER BY feedback_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Evaluations Matrix</title>
    <link rel="stylesheet" href="style.css">
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            if (savedTheme === 'light') {
                document.documentElement.classList.add('light-mode');
            }
        })();
    </script>

    <style>
        /* 🎨 CSS Variables Tema Gelap (Default) */
        :root {
            --bg-main: #06060c;
            --bg-panel: #0f0f1a;
            --bg-card: #1c1c34;
            --text-main: #ffffff;
            --text-muted: #e4e4e7;
            --text-author: #a1a1aa;
            --border-line: rgba(255, 255, 255, 0.06);
            --border-card: rgba(255, 255, 255, 0.05);
            --btn-bg: #1c1c34;
        }
        
        /* ☀️ CSS Variables Tema Cerah (Light Mode) */
        html.light-mode {
            --bg-main: #f4f4f7;
            --bg-panel: #ffffff;
            --bg-card: #f8fafc;
            --text-main: #0f172a;      /* Tulisan utama bertukar gelap */
            --text-muted: #334155;     /* Petikan ulasan bertukar kelabu gelap */
            --text-author: #64748b;    /* E-mel bawah bertukar kelabu sedari */
            --border-line: rgba(0, 0, 0, 0.08);
            --border-card: rgba(0, 0, 0, 0.06);
            --btn-bg: #e2e8f0;
        }

        body {
            background-color: var(--bg-main) !important;
            margin: 0;
            font-family: Inter, system-ui, -apple-system, sans-serif;
            color: var(--text-main) !important; /* Bertukar ikut tema */
            overflow-x: hidden;
            transition: background-color 0.2s ease, color 0.2s ease;
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
        
        .main-content.expanded {
            margin-left: 0 !important;
            width: 100% !important;
        }

        .content-container {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header-top h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-main) !important; /* FIX: Dah tak kekal putih */
            margin: 0;
        }

        .btn-control {
            background: var(--btn-bg);
            color: var(--text-main);
            border: 1px solid var(--border-line);
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }

        .btn-control:hover { 
            opacity: 0.9;
        }

        .panel {
            background: var(--bg-panel);
            border: 1px solid var(--border-line);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            transition: background 0.2s ease;
        }

        .panel h2 { 
            font-size: 18px; 
            font-weight: 600; 
            margin: 0 0 20px 0; 
            color: var(--text-main) !important; /* FIX: Tajuk log ikut tema */
        }
        
        .feedback-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); 
            gap: 20px; 
            margin-top: 15px; 
        }

        .stat-card {
            padding: 24px;
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s, border-color 0.2s, background 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            border-color: rgba(236, 72, 153, 0.4);
        }

        .card-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-start; 
            margin-bottom: 14px; 
            gap: 12px; 
        }
        
        .event-tag { 
            font-size: 13px; 
            font-weight: 700; 
            color: #ec4899; 
            line-height: 1.3; 
        }
        
        .rating-badge { 
            font-weight: 700; 
            color: #fbbf24; 
            background: rgba(251, 191, 36, 0.1); 
            padding: 4px 8px; 
            border-radius: 8px; 
            font-size: 13px; 
            white-space: nowrap; 
        }
        
        .comment-text { 
            font-size: 14px; 
            font-weight: 400; 
            color: var(--text-muted) !important; /* FIX: Tulisan ulasan kini gelap pada light mode */
            font-style: italic; 
            line-height: 1.5; 
            margin: 0 0 16px 0; 
        }
        
        .author-meta { 
            display: block; 
            font-size: 12px; 
            color: var(--text-author) !important; /* FIX: Tulisan e-mel pembekal maklum balas kini gelap pada light mode */
            border-top: 1px solid var(--border-card); 
            padding-top: 12px; 
            margin-top: auto; 
        }
    </style>
</head>
<body>

    <?php include('admin_sidebar_helper.php'); ?>

    <main class="main-content" id="main-content">
        <div class="content-container">
            
            <div class="header-top">
                <h1>User Feedbacks & Metrics</h1>
                <div class="top-controls">
                    <button class="btn-control" onclick="toggleSidebar()">☰ Toggle Menu</button>
                </div>
            </div>
            
            <div class="panel">
                <h2>What Our Neighbors Say! 🎉</h2>
                <div class="feedback-grid">
                    <?php if($feedbacks && $feedbacks->num_rows > 0): 
                        while($fb = $feedbacks->fetch_assoc()): 
                            
                            // ✨ LOGIK BARU: Menambahkan ekspresi/emoji seru sesuai dengan nilai rating
                            $rating_value = intval($fb['RATING'] ?? 0);
                            $rating_emoji = '';
                            switch($rating_value) {
                                case 5: $rating_emoji = ' 🤩🔥'; break;
                                case 4: $rating_emoji = ' 😎✨'; break;
                                case 3: $rating_emoji = ' 👍🙃'; break;
                                case 2: $rating_emoji = ' 😕📉'; break;
                                case 1: $rating_emoji = ' 🥱❌'; break;
                                default: $rating_emoji = ''; break;
                            }
                            ?>
                            
                            <div class="stat-card">
                                <div>
                                    <div class="card-header">
                                        <span class="event-tag"><?php echo htmlspecialchars($fb['TITLE'] ?? 'General Experience'); ?></span>
                                        <span class="rating-badge">⭐ <?php echo htmlspecialchars($rating_value) . $rating_emoji; ?></span>
                                    </div>
                                    <p class="comment-text">
                                        "<?php echo htmlspecialchars($fb['COMMENTS'] ?? ''); ?>"
                                    </p>
                                </div>
                                <span class="author-meta">
                                    <strong>By:</strong> <?php echo htmlspecialchars($fb['EMAIL_RESIDENT'] ?? 'Anonymous'); ?>
                                </span>
                            </div>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-author); font-style: italic;">
                            Shhh... No reviews shared by the community yet! 🤫
                        </div>
                    <?php endif; ?>
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
    </script>
</body>
</html>