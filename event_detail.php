<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('db_connect.php');

if (!isset($_SESSION['user_email']) || $_SESSION['role'] !== 'Resident') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['event_title'])) {
    header("Location: browse_events.php");
    exit();
}

$event_title = urldecode($_GET['event_title']);

// Fetch complete event details
$stmt = $conn->prepare("SELECT * FROM `event` WHERE event_title = ?");
$stmt->bind_param("s", $event_title);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    echo "<script>alert('Event not found!'); window.location.href='browse_events.php';</script>";
    exit();
}

// Check for existing registration status
$resident_email = $_SESSION['user_email'];
$check_stmt = $conn->prepare("SELECT * FROM `event_participation` WHERE event_title = ? AND resident_email = ?");
$check_stmt->bind_param("ss", $event_title, $resident_email);
$check_stmt->execute();
$already_joined = $check_stmt->get_result()->num_rows > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SmartVille - <?php echo htmlspecialchars($event['event_title']); ?></title>
    <style>
        :root {
            --bg-main: #070a13;
            --bg-card: #141a2f;
            --bg-input: #1e2642;
            --text-primary: #ffffff;
            --text-muted: #94a3b8;
            --accent-purple: #c084fc;
            --border-line: rgba(255, 255, 255, 0.06);
        }
        body { background-color: var(--bg-main); color: var(--text-primary); font-family: system-ui, sans-serif; padding: 40px; }
        .detail-container { max-width: 800px; margin: 0 auto; background: var(--bg-card); border: 1px solid var(--border-line); border-radius: 20px; overflow: hidden; padding: 30px; }
        h1 { color: var(--accent-purple); margin-bottom: 10px; }
        .meta-info { margin-bottom: 20px; color: var(--text-muted); font-size: 14px; }
        .description { line-height: 1.6; margin-bottom: 30px; }
        .form-box { background: var(--bg-input); padding: 20px; border-radius: 12px; border: 1px solid var(--border-line); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px; }
        input[type="number"], input[type="text"] { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-line); background: var(--bg-main); color: white; box-sizing: border-box; }
        input:disabled { opacity: 0.6; cursor: not-allowed; }
        .btn { background: linear-gradient(135deg, #7c3aed, var(--accent-purple)); color: white; padding: 12px 24px; border-radius: 8px; border: none; font-weight: bold; cursor: pointer; width: 100%; }
        .btn-secondary { background: #334155; color: white; text-decoration: none; display: inline-block; text-align: center; margin-top: 10px; padding: 10px; border-radius: 8px; width: 100%; box-sizing: border-box; }
    </style>
</head>
<body>

<div class="detail-container">
    <h1><?php echo htmlspecialchars($event['event_title']); ?></h1>
    <div class="meta-info">
        <span>📅 <?php echo htmlspecialchars($event['date']); ?> at <?php echo htmlspecialchars($event['time']); ?></span> | 
        <span>📍 <?php echo htmlspecialchars($event['venue'] ?? 'Community Workspace'); ?></span> |
        <span>🏷️ Category: <?php echo htmlspecialchars($event['category'] ?? 'General'); ?></span>
    </div>

    <div class="description">
        <h3>Description:</h3>
        <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
    </div>

    <div class="form-box">
        <h3>Event Registration</h3>
        <p style="margin-bottom: 15px; font-size: 13px; color: var(--text-muted);">
            Status: <strong><?php echo ($event['event_type'] === 'Paid') ? "Paid (RM " . htmlspecialchars($event['ticket_price']) . ")" : htmlspecialchars($event['event_type']); ?></strong>
        </p>

        <?php if ($already_joined): ?>
            <div style="background: rgba(16, 185, 129, 0.2); color: #10b981; padding: 15px; border-radius: 8px; text-align: center; font-weight: bold;">
                ✓ You have already registered for this event. Registration details cannot be modified.
            </div>
            <a href="my_events.php" class="btn-secondary">Go to My Events</a>
        <?php else: ?>
            <form action="join_event.php" method="POST">
                <input type="hidden" name="event_title" value="<?php echo htmlspecialchars($event['event_title']); ?>">
                
                <div class="form-group">
                    <label for="pax">Number of Pax / Attendees (Total Tickets):</label>
                    <input type="number" id="pax" name="pax" min="1" max="10" value="1" required>
                </div>

                <button type="submit" class="btn">Confirm & Join Event Now</button>
            </form>
        <?php endif; ?>
    </div>
    
    <a href="browse_events.php" class="btn-secondary" style="background: transparent; border: 1px solid var(--border-line);">← Back to Discover Events</a>
</div>

</body>
</html>