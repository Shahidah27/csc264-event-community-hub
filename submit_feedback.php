<?php
// submit_feedback.php - Resident Event Feedback Form
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
include('db_connect.php');

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$resident_email = $_SESSION['user_email'];

// Catch parameters if redirected from a specific event page
if (isset($_GET['event_title'])) {
    $passed_title = urldecode($_GET['event_title']);
} elseif (isset($_GET['title'])) {
    $passed_title = urldecode($_GET['title']);
} else {
    $passed_title = '';
}

// GET A LIST OF EVENTS THAT THIS RESIDENT HAS ATTENDED
$joined_events = [];
$list_stmt = $conn->prepare("SELECT DISTINCT `EVENT_TITLE` FROM `event_participation` WHERE `EMAIL_RESIDENT` = ? ORDER BY `JOIN_DATE` DESC");
$list_stmt->bind_param("s", $resident_email);
$list_stmt->execute();
$result_list = $list_stmt->get_result();
while ($row = $result_list->fetch_assoc()) {
    $joined_events[] = $row['EVENT_TITLE']; 
}
$list_stmt->close();

// Specify default options if no URL parameters are sent.
if (empty($passed_title)) {
    $event_title = $joined_events[0] ?? 'General Platform Experience';
} else {
    $event_title = $passed_title;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_title_post = $_POST['event_title'] ?? 'General Platform Experience';
    $rating = intval($_POST['rating']);
    $comments = trim($_POST['comments']);
    
    // ✨ FIX: Generate a completely unique identifier for the FEEDBACK_ID string column
    $feedback_id = uniqid('FB_', true); 
    $submission_date = date('Y-m-d H:i:s'); 

    if ($rating >= 1 && $rating <= 5 && !empty($comments)) {
        try {
            // ✨ FIX: Explicitly map the new $feedback_id token to the primary key slot
            $stmt = $conn->prepare("INSERT INTO `feedback` (`FEEDBACK_ID`, `TITLE`, `EMAIL_RESIDENT`, `RATING`, `COMMENTS`, `SUBMISSION_DATE`) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssiss", $feedback_id, $event_title_post, $resident_email, $rating, $comments, $submission_date);
            
            if ($stmt->execute()) {
                echo "<script>alert('Thank you for your feedback!'); window.location.href='logout.php?confirm=true';</script>";
                exit();
            } else {
                $error = "Database error. Failed to save your feedback.";
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = "Sistem mengesan maklum balas trùng atau ralat kekunci unik.";
            } else {
                $error = "Ralat pangkalan data: " . $e->getMessage();
            }
        }
    } else {
        $error = "Please provide both a rating score and written comments.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Give Event Feedback</title>
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            if (savedTheme === 'light') {
                document.documentElement.classList.add('light-mode');
            }
        })();
    </script>

    <style>
        :root {
            --bg-main: #06060c;
            --bg-card: #0f0f1a;
            --text-main: #ffffff;
            --text-muted: #71717a;
            --label-color: #a1a1aa;
            --input-bg: #1c1c34;
            --border-line: rgba(255, 255, 255, 0.06);
            --select-text: #ffffff;
            --btn-alt-bg: #1e2642;
            --btn-alt-text: #ffffff;
        }

        html.light-mode {
            --bg-main: #f4f4f7;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #475569;
            --label-color: #334155;
            --input-bg: #f8fafc;
            --border-line: rgba(0, 0, 0, 0.08);
            --select-text: #0f172a;
            --btn-alt-bg: #e2e8f0;
            --btn-alt-text: #0f172a;
        }

        body { 
            background-color: var(--bg-main); 
            color: var(--text-main); 
            font-family: Inter, system-ui, sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0;
            transition: background-color 0.2s ease;
        }

        .feedback-card { 
            background: var(--bg-card); 
            border: 1px solid var(--border-line); 
            padding: 35px; 
            border-radius: 20px; 
            width: 90%; 
            max-width: 450px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        }

        h3 { 
            color: #ec4899; 
            margin-top: 0; 
            font-size: 24px;
            font-weight: 700;
        }

        .form-group { 
            margin-bottom: 22px; 
        }

        label { 
            display: block; 
            margin-bottom: 8px; 
            font-size: 14px; 
            font-weight: 600;
            color: var(--label-color); 
        }

        select, textarea { 
            width: 100%; 
            background: var(--input-bg); 
            border: 1px solid var(--border-line); 
            padding: 14px; 
            border-radius: 10px; 
            color: var(--select-text); 
            font-size: 14px;
            box-sizing: border-box; 
            font-family: inherit;
        }

        textarea::placeholder {
            color: #64748b;
        }

        .btn-action-group {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }

        .btn-submit { 
            flex: 2;
            background: linear-gradient(135deg, #a855f7, #ec4899); 
            border: none; 
            color: white; 
            padding: 14px; 
            border-radius: 10px; 
            font-weight: bold; 
            font-size: 15px;
            cursor: pointer; 
            transition: opacity 0.2s ease;
        }

        .btn-skip {
            flex: 1;
            background: var(--btn-alt-bg);
            border: 1px solid var(--border-line);
            color: var(--btn-alt-text);
            padding: 14px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            display: inline-block;
            box-sizing: border-box;
            transition: opacity 0.2s ease;
        }

        .btn-submit:hover, .btn-skip:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="feedback-card">
        <h3>Community Event Evaluation</h3>
        <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 25px;">
            We appreciate your thoughts on your participated community events.
        </p>
        
        <?php if(isset($error)) { echo "<p style='color:#ef4444; font-size:13px; font-weight:600; margin-bottom: 20px;'>$error</p>"; } ?>
        
        <form action="submit_feedback.php" method="POST">
            
            <div class="form-group">
                <label for="event_title">Select Joined Event</label>
                <select name="event_title" id="event_title" required>
                    <?php if (empty($joined_events)): ?>
                        <option value="General Platform Experience">General Platform Experience</option>
                    <?php else: ?>
                        <option value="General Platform Experience" <?php echo ($event_title == 'General Platform Experience') ? 'selected' : ''; ?>>General Platform Experience</option>
                        
                        <?php foreach ($joined_events as $joined_title): ?>
                            <option value="<?php echo htmlspecialchars($joined_title); ?>" <?php echo ($event_title === $joined_title) ? 'selected' : ''; ?>>
                                📅 <?php echo htmlspecialchars($joined_title); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Rating Score</label>
                <select name="rating" required>
                    <option value="5">⭐⭐⭐⭐⭐ (5/5 Excellent)</option>
                    <option value="4">⭐⭐⭐⭐ (4/5 Good)</option>
                    <option value="3">⭐⭐⭐ (3/5 Satisfactory)</option>
                    <option value="2">⭐⭐ (2/5 Unsatisfactory)</option>
                    <option value="1">⭐ (1/5 Poor)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Comments / Suggestions</label>
                <textarea name="comments" rows="4" placeholder="Share your experience with us..." required></textarea>
            </div>

            <div class="btn-action-group">
                <a href="logout.php?confirm=true" class="btn-skip">Skip</a>
                <button type="submit" class="btn-submit">Submit Feedback</button>
            </div>
        </form>
    </div>
</body>
</html>