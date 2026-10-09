<?php
ob_start(); 
session_start();
include('db_connect.php'); 

if(!isset($_GET['title'])) { 
    header("Location: manage_events.php"); 
    exit; 
}

$title = $conn->real_escape_string($_GET['title']);

// 🛠️ DIBAIKI: Menggunakan nama jadual berhuruf besar penuh `EVENT` supaya konsisten dengan sistem database anda
$event_query = $conn->query("SELECT * FROM `EVENT` WHERE `EVENT_TITLE`='$title'");
$event = ($event_query && $event_query->num_rows > 0) ? $event_query->fetch_assoc() : null;

// Fallback safety check if the event is truly not found in the database
if(!$event) { 
    die("<div style='background:#06060c; color:#ef4444; padding:40px; text-align:center; font-family:sans-serif; border-radius:12px; margin:20px;'><h3>⚠️ Error: Event configuration not found or access denied.</h3></div>"); 
}

// 🛠️ MENGALIKAN KUNCI KE HURUF BESAR: Memastikan tiada isu "Undefined array key" untuk kod di bawah
$event = array_change_key_case($event, CASE_UPPER);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Live Event - SmartVille</title>
    <link rel="stylesheet" href="style.css">
    
    <script>
        // Mengesan tema pilihan pengguna daripada sistem utama (Light / Dark)
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            if (savedTheme === 'light') {
                document.documentElement.classList.add('light-mode');
            }
        })();
    </script>

    <style>
        /* 🎨 Konfigurasi CSS Variables bagi Tema Gelap (Default) */
        :root {
            --bg-main: #070a13;
            --bg-panel: #0f1423;
            --bg-table: #141a2f;
            --bg-th: #1e2642;
            --text-main: #ffffff;
            --text-muted: #a1a1aa;
            --text-table-td: #e2e8f0;
            --text-table-th: #94a3b8;
            --border-line: rgba(255, 255, 255, 0.08);
            --border-table: rgba(255, 255, 255, 0.06);
            --input-bg: rgba(255, 255, 255, 0.02);
            --table-title: #c084fc;
        }
        
        /* ☀️ Konfigurasi CSS Variables bagi Tema Cerah (Light Mode) */
        html.light-mode {
            --bg-main: #f4f4f7;
            --bg-panel: #ffffff;
            --bg-table: #ffffff;
            --bg-th: #f1f5f9;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --text-table-td: #334155;
            --text-table-th: #475569;
            --border-line: rgba(0, 0, 0, 0.08);
            --border-table: rgba(0, 0, 0, 0.08);
            --input-bg: #f8fafc;
            --table-title: #7c3aed;
        }

        body { 
            background-color: var(--bg-main) !important; 
            color: var(--text-main); 
            font-family: Inter, system-ui, -apple-system, sans-serif;
            margin: 0;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .panel {
            background: var(--bg-panel);
            border: 1px solid var(--border-line);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
        }

        input, textarea, select { 
            background: var(--input-bg) !important; 
            color: var(--text-muted) !important; 
            border: 1px solid var(--border-line) !important;
            cursor: not-allowed !important;
            opacity: 0.8;
            padding: 10px;
            border-radius: 6px;
            width: 100%;
            box-sizing: border-box;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
            color: var(--text-main);
        }

        .lock-notice {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid rgba(16, 185, 129, 0.2);
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 500;
        }

        .img-preview {
            max-width: 100%;
            max-height: 250px;
            border-radius: 8px;
            margin-top: 10px;
            border: 1px solid var(--border-line);
        }

        /* 🔒 Custom Style untuk Letak Kotak Kod Rahsia */
        .private-letter-box {
            background: rgba(168, 85, 247, 0.1);
            color: #a855f7;
            border: 1px dashed #a855f7;
            padding: 12px 16px;
            border-radius: 8px;
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            font-size: 16px;
            letter-spacing: 1.5px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        html.light-mode .private-letter-box {
            background: rgba(124, 58, 237, 0.06);
            color: #6d28d9;
            border: 1px dashed #6d28d9;
        }

        /* --- Elegant Participant Table Styling --- */
        .section-divider {
            margin: 40px 0 20px 0;
            border: 0;
            border-top: 1px solid var(--border-line);
        }

        .table-title {
            color: var(--table-title);
            font-size: 20px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--border-table);
            background: var(--bg-table);
            transition: background 0.2s;
        }

        .resident-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            text-align: left;
        }

        .resident-table th {
            background: var(--bg-th);
            color: var(--text-table-th);
            padding: 14px 16px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.05em;
            border-bottom: 2px solid var(--border-table);
        }

        .resident-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-table);
            color: var(--text-table-td);
        }

        .resident-table tr:last-child td {
            border-bottom: none;
        }

        .resident-table tr:hover {
            background: rgba(148, 163, 184, 0.05);
        }

        .badge-status {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-free { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
        .badge-paid { background: rgba(16, 185, 129, 0.15); color: #10b981; }

        .no-data-msg {
            padding: 20px;
            text-align: center;
            color: var(--text-muted);
            font-style: italic;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-top: 20px;
        }
        .header-top h1 { margin: 0; font-size: 26px; }
        .btn-control {
            background: var(--bg-th);
            color: var(--text-main);
            border: 1px solid var(--border-line);
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <main class="main-content" id="main-content">
        <div class="content-container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <div class="header-top">
                <div>
                    <h1>👁️ View Event Configuration</h1>
                    <div style="color: var(--text-muted); font-size: 14px; margin-top: 4px;">
                        Live published event: <strong style="color: #ec4899;"><?php echo htmlspecialchars($event['EVENT_TITLE'] ?? ''); ?></strong>
                    </div>
                </div>
                <div class="top-controls">
                    <a href="manage_events.php" class="btn-control" style="text-decoration:none; display: inline-block;">← Back to Log</a>
                </div>
            </div>
            
            <div class="panel">
                <div class="lock-notice">🔒 <strong>Information Locked:</strong> This event has been published live to the community. Changes are no longer allowed.</div>
                
                <div class="form-group">
                    <label>Description Summary</label>
                    <textarea rows="5" disabled><?php echo htmlspecialchars($event['DESCRIPTION'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Promotional Banner</label>
                    <?php if (!empty($event['PICTURE'])): ?>
                        <br><img src="<?php echo htmlspecialchars($event['PICTURE']); ?>" class="img-preview">
                    <?php else: ?>
                        <input type="text" value="No banner uploaded" disabled>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Event Start Time</label>
                    <input type="time" value="<?php echo htmlspecialchars($event['TIME'] ?? ''); ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Event Access Type</label>
                    <input type="text" value="<?php echo htmlspecialchars($event['EVENT_TYPE'] ?? ''); ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Event Flow / Timeline Agenda</label>
                    <textarea rows="5" disabled><?php echo htmlspecialchars($event['EVENT_FLOW'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <?php 
                    $is_private = isset($event['EVENT_TYPE']) && strtolower(trim($event['EVENT_TYPE'])) === 'private';
                    if ($is_private): 
                    ?>
                        <label>Private Access Code</label>
                        <div>
                            <span class="private-letter-box">
                                🔑 CODE: <?php 
                                // 🛠️ DIBAIKI: Ditambah semakan supaya tidak memaparkan nilai '0' atau '0.00' sekiranya kod belum di-set
                                $code = trim($event['TICKET_PRICE'] ?? '');
                                if ($code !== '' && $code !== '0' && $code !== '0.00') {
                                    echo htmlspecialchars(strtoupper($code));
                                } else {
                                    echo 'NOT_SET';
                                }
                                ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <label>Ticket Entry Price</label>
                        <input type="text" value="<?php 
                            if (!empty($event['TICKET_PRICE']) && $event['TICKET_PRICE'] !== '0.00') {
                                echo is_numeric($event['TICKET_PRICE']) ? 'RM ' . number_format((float)$event['TICKET_PRICE'], 2) : htmlspecialchars($event['TICKET_PRICE']);
                            } else {
                                echo 'Free Entry';
                            }
                         ?>" disabled>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Maximum Attendance Capacity</label>
                    <input type="text" value="<?php echo htmlspecialchars($event['MAX_PARTICIPANTS'] ?? '0'); ?> Participants" disabled>
                </div>

                <hr class="section-divider">
                
                <h2 class="table-title">👥 Resident Participations</h2>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 15px;">
                    Real-time list of community members registered for this event.
                </p>

                <div class="table-responsive">
                    <table class="resident-table">
                        <thead>
                            <tr>
                                <th>Resident Email</th>
                                <th style="text-align: center;">Pax (Tickets)</th>
                                <th>Payment Status</th>
                                <th>Registration Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $current_title = $event['EVENT_TITLE'] ?? '';
                            
                            // 🛠️ DIBAIKI: Struktur kod table event_participation dibiarkan kekal dinamik & diprepare dengan selamat
                            $stmt = $conn->prepare("SELECT `EMAIL_RESIDENT`, `PAX`, `PAYMENT_STATUS`, `JOIN_DATE` FROM `event_participation` WHERE `EVENT_TITLE` = ? ORDER BY `JOIN_DATE` DESC");
                            $stmt->bind_param("s", $current_title);
                            $stmt->execute();
                            $participations = $stmt->get_result();

                            if ($participations->num_rows === 0): 
                            ?>
                                <tr>
                                    <td colspan="4" class="no-data-msg">
                                        No residents have joined this event yet.
                                    </td>
                                </tr>
                            <?php 
                            else: 
                                while ($row = $participations->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['EMAIL_RESIDENT']); ?></td>
                                    <td style="text-align: center; font-weight: bold; color: var(--table-title);">
                                        <?php echo htmlspecialchars($row['PAX']); ?>
                                    </td>
                                    <td>
                                        <span class="badge-status <?php echo (strtolower($row['PAYMENT_STATUS']) === 'free' || strtolower($row['PAYMENT_STATUS']) === 'free entry') ? 'badge-free' : 'badge-paid'; ?>">
                                            <?php echo htmlspecialchars($row['PAYMENT_STATUS']); ?>
                                        </span>
                                    </td>
                                    <td style="color: var(--text-muted);"><?php echo htmlspecialchars($row['JOIN_DATE']); ?></td>
                                </tr>
                            <?php 
                                endwhile; 
                            endif; 
                            $stmt->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
<?php ob_end_flush(); ?>