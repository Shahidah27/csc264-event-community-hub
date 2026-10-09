<?php
// admin_inbox.php - Event Approvals System
ob_start(); // FIX: Menghalang ralat "Cannot modify header information" jika ada output awal terkeluar
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('db_connect.php'); 

if (!isset($_SESSION['user_email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php?role=admin"); exit();
}

// Handle Form Decisions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['event_title'])) {
    $action = $_POST['action'];
    $title = $_POST['event_title'];
    $admin_email = $_SESSION['user_email']; // Get logged-in admin email
    
    // 🛠️ UPDATED: Changed APPROVED_BY to EMAIL_ADMIN to match the updated database column name
    if ($action === 'approve') {
        // UPDATE: Set status 'Approved' AND capture the admin's email into EMAIL_ADMIN
        $update_stmt = $conn->prepare("UPDATE `event` SET `APPROVAL_STATUS` = 'Approved', `EMAIL_ADMIN` = ? WHERE `EVENT_TITLE` = ?");
        $update_stmt->bind_param("ss", $admin_email, $title);
    } else {
        // UPDATE: Set status 'Rejected' and clear the EMAIL_ADMIN column just in case
        $update_stmt = $conn->prepare("UPDATE `event` SET `APPROVAL_STATUS` = 'Rejected', `EMAIL_ADMIN` = NULL WHERE `EVENT_TITLE` = ?");
        $update_stmt->bind_param("s", $title);
    }
    
    $update_stmt->execute();
    $update_stmt->close();
    header("Location: admin_inbox.php"); exit();
}

// 🛠️ FIXED: Query condition changed to evaluate uppercase APPROVAL_STATUS and DATE ordering columns
$pending_events = $conn->query("SELECT * FROM `event` WHERE `APPROVAL_STATUS` = 'Pending' ORDER BY `DATE` ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Requests Inbox</title>
    <link rel="stylesheet" href="style.css">
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
        
        .main-content.expanded {
            margin-left: 0 !important;
            width: 100% !important;
        }

        .content-container {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .btn-control {
            background: #1c1c34 !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            color: white !important;
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
            transition: background 0.2s ease, border-color 0.2s ease;
        }
        .btn-control:hover {
            background: #252542 !important;
            border-color: rgba(255,255,255,0.2) !important;
        }

        .panel {
            background: #0f0f1a;
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 24px;
        }

        .panel h2 {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 20px 0;
            color: #ffffff;
        }

        /* NEW RESPONSIVE QUEUE SYSTEM CONFIGURATION */
        .responsive-queue {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
        }

        .queue-header {
            display: none; /* Hidden on mobile interface scales */
            padding: 14px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            color: #71717a;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .queue-row {
            background: rgba(255, 255, 255, 0.01);
            border: 1px solid rgba(255, 255, 255, 0.04);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            flex-direction: column; /* Stacked layout elements default for small interfaces */
            gap: 14px;
            cursor: pointer;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        .queue-row:hover {
            background-color: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.08);
        }

        .queue-col {
            min-width: 0; /* Important context string constraint rule */
        }

        .inline-label {
            display: inline-block;
            font-size: 11px;
            text-transform: uppercase;
            color: #71717a;
            font-weight: 600;
            margin-right: 6px;
        }

        .text-muted {
            color: #71717a;
            font-size: 13px;
        }

        .truncated-text {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .badge-type {
            background: #1c1c34 !important;
            color: #ffffff;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
            border: 1px solid rgba(255,255,255,0.04);
        }

        /* Action Buttons styling setup */
        .action-container {
            display: flex;
            gap: 10px;
            width: 100%;
        }
        .action-container form {
            flex: 1;
        }

        .btn-action-approve, .btn-action-reject {
            width: 100%;
            padding: 10px 14px;
            font-size: 13px;
            border-radius: 8px;
            border: none;
            color: white;
            cursor: pointer;
            font-weight: 600;
            transition: opacity 0.2s;
            text-align: center;
        }

        .btn-action-approve { background: #10b981; }
        .btn-action-reject { background: #ef4444; }
        .btn-action-approve:hover, .btn-action-reject:hover { opacity: 0.85; }

        .empty-state-message {
            color: #71717a;
            text-align: center;
            padding: 40px 20px;
            font-size: 14px;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(4px);
            display: flex; align-items: center; justify-content: center;
            z-index: 9999; opacity: 0; pointer-events: none;
            transition: opacity 0.25s ease;
        }
        .modal-overlay.active {
            opacity: 1; pointer-events: auto;
        }
        .modal-card {
            background: #0f0f1a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            width: 90%; max-width: 600px;
            padding: 28px; box-sizing: border-box;
            position: relative; transform: translateY(-20px);
            transition: transform 0.25s ease;
            color: #ffffff;
        }
        .modal-overlay.active .modal-card {
            transform: translateY(0);
        }
        .modal-close {
            position: absolute; top: 20px; right: 20px;
            background: none; border: none; color: #71717a;
            font-size: 22px; cursor: pointer; transition: color 0.2s;
        }
        .modal-close:hover { color: #ffffff; }

        /* --- DESKTOP ADAPTIVE BREAKPOINT VIEW RULES --- */
        @media (min-width: 992px) {
            .queue-header {
                display: flex;
                align-items: center;
            }
            .queue-row {
                flex-direction: row;
                align-items: center;
                padding: 16px 20px;
            }
            .inline-label {
                display: none; /* Labels redundant in straight row configurations */
            }
            
            /* Precision structural row spatial scaling allocations - ADJUSTED FOR VENUE/ACTION BUFFER */
            .col-details   { flex: 2.2; }
            .col-organizer { flex: 2.0; padding-right: 10px; }
            .col-desc      { flex: 2.3; padding-right: 10px; }
            .col-meta      { flex: 2.2; padding-right: 15px; } /* Added explicit allocation and right safety buffer */
            .col-actions   { flex: 1.8; display: flex; justify-content: flex-end; flex-shrink: 0; } /* Set flex-shrink to prevent button squeezing */
            
            .action-container {
                width: auto;
                justify-content: flex-end;
            }
            .action-container form {
                flex: none;
            }
            .btn-action-approve, .btn-action-reject {
                width: auto;
                min-width: 85px;
                padding: 8px 14px;
                font-size: 12px;
            }
        }

        /* Light-mode adjustments */
        html.light-mode body { background-color: #f4f4f7 !important; color: #18181b !important; }
        html.light-mode .main-content { background: #f4f4f7 !important; }
        html.light-mode .panel { background: #ffffff !important; border: 1px solid rgba(0,0,0,0.06) !important; }
        html.light-mode .panel h2 { color: #18181b !important; }
        html.light-mode .queue-row { background: rgba(0,0,0,0.01); border: 1px solid rgba(0,0,0,0.05); color: #18181b !important; }
        html.light-mode .queue-row:hover { background-color: rgba(0, 0, 0, 0.02); border-color: rgba(0,0,0,0.1); }
        html.light-mode .text-muted { color: #52525b; }
        html.light-mode .inline-label { color: #71717a; }
        html.light-mode .btn-control { background: #e4e4e7 !important; color: #18181b !important; border: 1px solid rgba(0,0,0,0.15) !important; }
        html.light-mode .btn-control:hover { background: #d4d4d8 !important; }
        html.light-mode .badge-type { background: #e4e4e7 !important; color: #18181b !important; border: 1px solid rgba(0,0,0,0.08) !important; }
        html.light-mode .modal-card { background: #ffffff; border: 1px solid rgba(0,0,0,0.1); color: #18181b; }
    </style>
</head>
<body>

    <?php include('admin_sidebar_helper.php'); ?>

    <main class="main-content" id="main-content">
        <div class="content-container" style="padding: 0;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; gap: 15px;">
                <h1 style="margin: 0; font-size: 28px; font-weight: bold; line-height: 1.2; color: inherit;">Organizer Event Validation Requests</h1>
                <button type="button" class="btn-control" onclick="toggleSidebar()" style="flex-shrink: 0;">
                    &#9776; Toggle Menu
                </button>
            </div>
            
            <div class="panel">
                <h2>Pending Request Queue</h2>
                <?php if ($pending_events && $pending_events->num_rows > 0): ?>
                    
                    <div class="responsive-queue">
                        <div class="queue-header">
                            <div class="col-details">Event Details</div>
                            <div class="col-organizer">Organizer</div>
                            <div class="col-desc">Description</div> 
                            <div class="col-meta">Venue / Date</div>
                            <div class="col-actions" style="text-align: right; padding-right: 12px;">Actions</div>
                        </div>

                        <?php while($row = $pending_events->fetch_assoc()): 
                            // 🛠️ FALLBACK TRANSLATION: Gracefully handles case-insensitive array mapping keys
                            $e_title = $row['EVENT_TITLE'] ?? $row['event_title'] ?? '';
                            $e_org   = $row['EMAIL_ORGANIZER'] ?? $row['organizer_email'] ?? '';
                            $e_desc  = $row['DESCRIPTION'] ?? $row['description'] ?? 'No description provided.';
                            $e_cat   = $row['CATEGORY'] ?? $row['category'] ?? 'Unassigned Category';
                            $e_venue = $row['VENUE'] ?? $row['venue'] ?? '';
                            $e_date  = $row['DATE'] ?? $row['date'] ?? '';
                            $e_time  = $row['TIME'] ?? $row['time'] ?? '';
                            $e_type  = $row['EVENT_TYPE'] ?? $row['event_type'] ?? 'Free';
                            $e_price = $row['TICKET_PRICE'] ?? $row['ticket_price'] ?? '';

                            $clean_desc = htmlspecialchars($e_desc);
                            $price_display = "Free Entry";
                            if (stripos($e_type, 'Private') !== false) { $price_display = htmlspecialchars($e_price); }
                            elseif (stripos($e_type, 'Paid') !== false) { $price_display = "$" . htmlspecialchars($e_price); }
                        ?>
                            <div class="queue-row" onclick="openEventDetails(this)" 
                                 data-title="<?php echo htmlspecialchars($e_title); ?>"
                                 data-category="<?php echo htmlspecialchars($e_cat); ?>"
                                 data-organizer="<?php echo htmlspecialchars($e_org); ?>"
                                 data-venue="<?php echo htmlspecialchars($e_venue); ?>"
                                 data-datetime="📅 <?php echo htmlspecialchars($e_date); ?> @ <?php echo htmlspecialchars($e_time); ?>"
                                 data-type="<?php echo htmlspecialchars($e_type); ?>"
                                 data-price="<?php echo $price_display; ?>"
                                 data-desc="<?php echo $clean_desc; ?>">
                                
                                <div class="queue-col col-details">
                                    <strong style="font-size: 15px; display: block; margin-bottom: 2px; color: inherit;"><?php echo htmlspecialchars($e_title); ?></strong>
                                    <span class="text-muted" style="font-weight: 500;"><?php echo htmlspecialchars($e_cat); ?></span>
                                </div>
                                
                                <div class="queue-col col-organizer">
                                    <span class="inline-label">Organizer:</span>
                                    <span style="font-weight: 500; color: inherit; word-break: break-all;"><?php echo htmlspecialchars($e_org); ?></span>
                                </div>
                                
                                <div class="queue-col col-desc">
                                    <span class="inline-label">Description:</span>
                                    <span class="text-muted truncated-text"><?php echo $clean_desc; ?></span>
                                </div>
                                
                                <div class="queue-col col-meta">
                                    <span class="inline-label">Venue / Date:</span>
                                    <div style="display: flex; flex-direction: column; gap: 2px;">
                                        <span style="color: inherit;" class="truncated-text">📍 <?php echo htmlspecialchars($e_venue); ?></span>
                                        <span class="text-muted">📅 <?php echo htmlspecialchars($e_date); ?></span>
                                    </div>
                                </div>
                                
                                <div class="queue-col col-actions" onclick="event.stopPropagation();">
                                    <div class="action-container">
                                        <form method="POST" style="margin:0;" onsubmit="return confirm('Approve this event request?');">
                                            <input type="hidden" name="event_title" value="<?php echo htmlspecialchars($e_title); ?>">
                                            <button type="submit" name="action" value="approve" class="btn-action-approve">Approve</button>
                                        </form>
                                        <form method="POST" style="margin:0;" onsubmit="return confirm('Reject this event request?');">
                                            <input type="hidden" name="event_title" value="<?php echo htmlspecialchars($e_title); ?>">
                                            <button type="submit" name="action" value="reject" class="btn-action-reject">Reject</button>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        <?php endwhile; ?>
                    </div>

                <?php else: ?>
                    <p class="empty-state-message">✅ No pending organizer event validation requests found.</p>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <div class="modal-overlay" id="eventModal" onclick="closeModal()">
        <div class="modal-card" onclick="event.stopPropagation()">
            <button class="modal-close" onclick="closeModal()">&times;</button>
            <h3 id="m-title" style="margin-top:0; font-size: 22px; color: inherit; margin-bottom: 4px;"></h3>
            <p id="m-category" class="text-muted" style="margin-top:0; font-weight:600; text-transform: uppercase; letter-spacing:0.5px; margin-bottom: 20px;"></p>
            
            <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.08); margin-bottom: 20px;">
            
            <div style="display:flex; flex-direction:column; gap:12px; font-size:14px; margin-bottom: 24px;">
                <div><strong class="text-muted" style="display:inline-block; width:100px;">Organizer:</strong> <span id="m-organizer" style="word-break: break-all;"></span></div>
                <div><strong class="text-muted" style="display:inline-block; width:100px;">Location:</strong> 📍 <span id="m-venue"></span></div>
                <div><strong class="text-muted" style="display:inline-block; width:100px;">Schedule:</strong> <span id="m-datetime"></span></div>
                <div><strong class="text-muted" style="display:inline-block; width:100px;">Admission:</strong> <span id="m-type" class="badge-type" style="margin-right:8px;"></span> <span id="m-price" style="font-weight:600; color:#a855f7;"></span></div>
            </div>

            <h4 style="margin-bottom:8px; font-size:15px; color: inherit;">Event Summary & Description</h4>
            <div id="m-desc" class="text-muted" style="font-size:13.5px; line-height:1.5; background: rgba(255,255,255,0.02); padding:16px; border-radius:8px; max-height:200px; overflow-y:auto; white-space: pre-wrap;"></div>
        </div>
    </div>

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

    function openEventDetails(rowElement) {
        document.getElementById('m-title').innerText = rowElement.getAttribute('data-title');
        document.getElementById('m-category').innerText = rowElement.getAttribute('data-category');
        document.getElementById('m-organizer').innerText = rowElement.getAttribute('data-organizer');
        document.getElementById('m-venue').innerText = rowElement.getAttribute('data-venue');
        document.getElementById('m-datetime').innerText = rowElement.getAttribute('data-datetime');
        document.getElementById('m-type').innerText = rowElement.getAttribute('data-type');
        document.getElementById('m-price').innerText = rowElement.getAttribute('data-price');
        document.getElementById('m-desc').innerText = rowElement.getAttribute('data-desc');

        document.getElementById('eventModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('eventModal').classList.remove('active');
    }
    </script>
</body>
</html>
<?php 
ob_end_flush(); 
?>