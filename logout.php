<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Organizer')) {
    goto clear_session_sequence;
}

if (isset($_SESSION['user_email']) && isset($_SESSION['role']) && $_SESSION['role'] === 'Resident' && !isset($_GET['confirm'])) {
    
    $eventTitle = isset($_SESSION['current_viewing_event']) ? $_SESSION['current_viewing_event'] : 'Charity ayam';
    $feedbackUrl = "submit_feedback.php?event_title=" . urlencode($eventTitle);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Before You Leave</title>
        
        <script>
            (function() {
                const savedTheme = localStorage.getItem('theme') || 'dark';
                if (savedTheme === 'light') {
                    document.documentElement.classList.add('light-mode');
                }
            })();
        </script>

        <style>
            /* Default Dark Mode Custom Properties */
            :root {
                --bg-main: #06060c;
                --bg-card: #0f0f1a;
                --text-main: #ffffff;
                --text-muted: #94a3b8;
                --border-line: rgba(255, 255, 255, 0.08);
                --btn-alt-bg: #1e2642;
                --btn-alt-text: #ffffff;
                --overlay-bg: rgba(3, 5, 11, 0.85); 
            }

            /* Clean Light Mode Property Layout Overrides */
            html.light-mode {
                --bg-main: #f4f4f7;
                --bg-card: #ffffff;
                --text-main: #0f172a;
                --text-muted: #475569;
                --border-line: rgba(0, 0, 0, 0.08);
                --btn-alt-bg: #f1f5f9;
                --btn-alt-text: #0f172a;
                --overlay-bg: rgba(244, 244, 247, 0.85); 
            }

            body {
                margin: 0; padding: 0;
                background-color: var(--bg-main);
                color: var(--text-main);
                font-family: 'Inter', system-ui, -apple-system, sans-serif;
                display: flex; justify-content: center; align-items: center;
                min-height: 100vh;
            }

            .logout-modal-overlay {
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: var(--overlay-bg); display: flex;
                justify-content: center; align-items: center; z-index: 10000;
                backdrop-filter: blur(8px);
                transition: background-color 0.3s ease;
            }
            
            .logout-modal-card {
                background: var(--bg-card); 
                border: 1px solid var(--border-line);
                padding: 40px; border-radius: 24px; max-width: 480px; width: 90%;
                box-shadow: 0 25px 50px -12px rgba(0,0,0,0.2); text-align: center;
                animation: modalFadeUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            @keyframes modalFadeUp {
                from { opacity: 0; transform: translateY(15px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .logout-modal-card h3 { 
                color: #ec4899; margin: 0 0 12px 0; font-size: 24px; font-weight: 700; 
            }
            .logout-modal-card p { 
                color: var(--text-muted); font-size: 15px; margin-bottom: 28px; line-height: 1.6; 
            }
            .logout-btn-group { display: flex; gap: 14px; }
            
            .btn-logout-now { 
                flex: 1; background: var(--btn-alt-bg); border: 1px solid var(--border-line); 
                color: var(--btn-alt-text); padding: 14px; border-radius: 12px; cursor: pointer; 
                font-weight: 700; text-decoration: none; font-size: 14px; transition: opacity 0.2s;
                display: inline-block; box-sizing: border-box;
            }

            .btn-give-feedback { 
                flex: 1; background: linear-gradient(135deg, #a855f7, #ec4899); border: none; 
                color: #fff; padding: 14px; border-radius: 12px; cursor: pointer; 
                font-weight: 700; text-decoration: none; font-size: 14px; transition: opacity 0.2s;
                display: inline-block; box-sizing: border-box;
            }
            .btn-logout-now:hover, .btn-give-feedback:hover { opacity: 0.9; }
        </style>
    </head>
    <body>
        <div class="logout-modal-overlay">
            <div class="logout-modal-card">
                <h3>Before You Leave...</h3>
                <p>Would you like to take a quick minute to share your feedback or suggestions about your community event experiences?</p>
                
                <div class="logout-btn-group">
                    <a href="logout.php?confirm=true" class="btn-logout-now">Log Out Only</a>
                    <a href="<?php echo $feedbackUrl; ?>" class="btn-give-feedback">Give Feedback</a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit(); 
}

clear_session_sequence:
$_SESSION = array();

if (ini_get("session_use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();
header("Location: index.php"); 
exit();
?>