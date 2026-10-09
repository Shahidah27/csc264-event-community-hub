<?php
// admin_published_event.php - Manage Active & Expired Listings
ob_start(); // FIX: Menghalang ralat "Cannot modify header information"
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
include('db_connect.php'); 

if (!isset($_SESSION['user_email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php?role=admin"); 
    exit();
}

// DIKEMAS KINI: Memadam rekod secara manual dari kedua-dua table menggunakan PHP (Kolum UPPERCASE)
if (isset($_GET['delete_title'])) {
    $title = $_GET['delete_title'];
    
    // Mulakan SQL Transaction untuk keselamatan integriti data
    $conn->begin_transaction();

    try {
        // 1. Padam semua rekod pendaftaran peserta bagi event ini terlebih dahulu
        $del_participation = $conn->prepare("DELETE FROM `event_participation` WHERE EVENT_TITLE = ?");
        $del_participation->bind_param("s", $title);
        $del_participation->execute();
        $del_participation->close();

        // 2. Seterusnya, padam rekod event itu sendiri
        $del_event = $conn->prepare("DELETE FROM `event` WHERE EVENT_TITLE = ?");
        $del_event->bind_param("s", $title);
        $del_event->execute(); 
        $del_event->close();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback(); // Batalkan jika ada ralat database
    }
    
    header("Location: admin_published_event.php"); 
    exit();
}

// DIKEMAS KINI: Mengemaskini rekod dalam kedua-dua table sekiranya Admin menukar Tajuk Event (Kolum UPPERCASE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event'])) {
    $old_title = $_POST['old_title'];
    $new_title = $_POST['event_title'];
    $desc = $_POST['description'];
    $venue = $_POST['venue'];
    $price = $_POST['ticket_price'];
    
    $conn->begin_transaction();

    try {
        // 1. Jika tajuk ditukar, kemaskini pendaftaran peserta dahulu supaya rekod tidak terputus (orphaned)
        if ($old_title !== $new_title) {
            $upd_part = $conn->prepare("UPDATE `event_participation` SET EVENT_TITLE=? WHERE EVENT_TITLE=?");
            $upd_part->bind_param("ss", $new_title, $old_title);
            $upd_part->execute();
            $upd_part->close();
        }

        // 2. Kemaskini maklumat induk event
        $upd = $conn->prepare("UPDATE `event` SET EVENT_TITLE=?, DESCRIPTION=?, VENUE=?, TICKET_PRICE=? WHERE EVENT_TITLE=?");
        $upd->bind_param("sssss", $new_title, $desc, $venue, $price, $old_title);
        $upd->execute(); 
        $upd->close();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
    }
    
    header("Location: admin_published_event.php"); 
    exit();
}

// Hanya menarik data berciri 'published' sahaja (Kolum APPROVAL_STATUS & DATE ditukar ke UPPERCASE)
$events = $conn->query("SELECT * FROM `event` WHERE LOWER(APPROVAL_STATUS) = 'published' ORDER BY DATE DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Published Listings</title>
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
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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

        /* Search Container Styles */
        .search-wrapper {
            position: relative;
            margin-bottom: 20px;
            max-width: 400px;
        }
        .search-input {
            width: 100%;
            background: #1c1c34;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            padding: 10px 16px 10px 40px;
            color: #ffffff;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        .search-input:focus {
            outline: none;
            border-color: #ec4899;
        }
        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #71717a;
            font-size: 16px;
            pointer-events: none;
        }

        .panel {
            background: #0f0f1a;
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .panel h2 {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 20px 0;
            color: #ffffff;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #a1a1aa;
            margin-bottom: 8px;
        }

        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            background: #1c1c34;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            padding: 12px 16px;
            color: #ffffff;
            font-size: 14px;
            font-family: inherit;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }

        html.light-mode .form-group input[type="text"],
        html.light-mode .form-group textarea {
            background: #f4f4f7;
            border: 1px solid rgba(0, 0, 0, 0.12);
            color: #18181b;
        }

        /* Light mode search overrides */
        html.light-mode .search-input {
            background: #e4e4e7;
            border: 1px solid rgba(0, 0, 0, 0.15);
            color: #18181b;
        }
        html.light-mode .search-icon {
            color: #71717a;
        }

        .form-group input[type="text"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ec4899;
        }

        .btn-submit-update {
            background: linear-gradient(135deg, #a855f7, #ec4899);
            color: white;
            border: none;
            font-size: 14px;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 10px;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .btn-submit-update:hover {
            opacity: 0.9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        th {
            color: #71717a;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            padding: 14px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        td {
            padding: 16px;
            color: #ffffff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .text-muted {
            color: #a1a1aa;
        }

        .badge-live {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
            background: rgba(16, 185, 129, 0.15); 
            color: #10b981;
        }

        .action-link-edit {
            color: #ec4899;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            margin-right: 12px;
            transition: opacity 0.2s;
        }

        .action-link-remove {
            color: #ef4444;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: opacity 0.2s;
        }

        .action-link-edit:hover, .action-link-remove:hover {
            opacity: 0.8;
        }

        .badge-concluded {
            font-size: 10px; 
            padding: 3px 8px; 
            background: rgba(255, 255, 255, 0.1); 
            color: #a1a1aa; 
            border-radius: 20px; 
            margin-left: 8px;
            font-weight: 600;
        }

        html.light-mode body { background-color: #f4f4f7 !important; color: #18181b !important; }
        html.light-mode .main-content { background: #f4f4f7 !important; }
        html.light-mode .panel { background: #ffffff !important; border: 1px solid rgba(0,0,0,0.06) !important; }
        html.light-mode .panel h2 { color: #18181b !important; }
        html.light-mode td { color: #18181b !important; border-bottom: 1px solid rgba(0,0,0,0.06); }
        html.light-mode td strong { color: #18181b !important; }
        html.light-mode td span { color: #18181b; }
        html.light-mode td .text-muted { color: #71717a; }

        html.light-mode .btn-control {
            background: #e4e4e7 !important;
            color: #18181b !important;
            border: 1px solid rgba(0,0,0,0.15) !important;
            box-shadow: none !important;
        }
        html.light-mode .btn-control:hover {
            background: #d4d4d8 !important;
            border-color: rgba(0,0,0,0.25) !important;
            color: #18181b !important;
        }
        html.light-mode .badge-concluded {
            background: rgba(0, 0, 0, 0.08);
            color: #71717a;
        }
    </style>
</head>
<body>

    <?php include('admin_sidebar_helper.php'); ?>

    <main class="main-content" id="main-content">
        <div class="content-container" style="padding: 0;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h1 style="margin: 0; font-size: 32px; font-weight: bold; line-height: 1.2; color: inherit;">Active Published Events Portal</h1>
                <button type="button" class="btn-control" onclick="toggleSidebar()">
                    &#9776; Toggle Menu
                </button>
            </div>
            
            <?php 
            // Semak jika mod Edit sedang aktif
            if(isset($_GET['edit_title'])): 
                $edit_title = $_GET['edit_title'];
                // DITUKAR: EVENT_TITLE (UPPERCASE)
                $find = $conn->prepare("SELECT * FROM `event` WHERE EVENT_TITLE=?");
                $find->bind_param("s", $edit_title); 
                $find->execute();
                $item = $find->get_result()->fetch_assoc(); 
                $find->close();
                
                if($item): ?>
                    <div class="panel" style="border: 1px solid #a855f7;">
                        <h2>Modify Mistaken / Incorrect Information Record</h2>
                        <form action="admin_published_event.php" method="POST">
                            <input type="hidden" name="old_title" value="<?php echo htmlspecialchars($item['EVENT_TITLE']); ?>">
                            
                            <div class="form-group">
                                <label>Event Title</label>
                                <input type="text" name="event_title" value="<?php echo htmlspecialchars($item['EVENT_TITLE']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Description Narrative</label>
                                <textarea name="description" rows="3" required><?php echo htmlspecialchars($item['DESCRIPTION']); ?></textarea>
                            </div>
                            
                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                                <div class="form-group">
                                    <label>Venue Base Selection</label>
                                    <input type="text" name="venue" value="<?php echo htmlspecialchars($item['VENUE']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Ticket Price Entry ($)</label>
                                    <input type="text" name="ticket_price" value="<?php echo htmlspecialchars($item['TICKET_PRICE']); ?>" required>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: center; margin-top: 10px;">
                                <button type="submit" name="update_event" class="btn-submit-update">Save Operational Update</button>
                                <a href="admin_published_event.php" style="margin-left:20px; color:#71717a; text-decoration:none; font-size:14px; font-weight:500;">Cancel Change</a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="panel">
                    <h2>What's Happening in Town? 🎪✨</h2>
                    
                    <div class="search-wrapper">
                        <span class="search-icon">&#128269;</span>
                        <input type="text" id="eventSearchInput" class="search-input" placeholder="Search event title or venue..." onkeyup="filterEvents()">
                    </div>

                    <div style="overflow-x: auto;">
                        <table id="eventsTable">
                            <thead>
                                <tr>
                                    <th>Listing Identity</th>
                                    <th>Schedule Timestamps</th>
                                    <th>Venue Site</th>
                                    <th>Status Metric</th>
                                    <th style="text-align: right;">Control Settings</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($events && $events->num_rows > 0): ?>
                                    <?php while($row = $events->fetch_assoc()): 
                                        // DITUKAR: Row key ditukar kepada DATE
                                        $is_past = strtotime($row['DATE']) < time();
                                    ?>
                                        <tr style="<?php echo $is_past ? 'opacity: 0.55;' : ''; ?>">
                                            <td>
                                                <div style="display: flex; align-items: center;">
                                                    <strong style="font-size: 15px; color: inherit;" class="searchable-title"><?php echo htmlspecialchars($row['EVENT_TITLE']); ?></strong>
                                                    <?php if($is_past): ?>
                                                        <span class="badge-concluded">Concluded</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span style="color: inherit;">📅 <?php echo htmlspecialchars($row['DATE']); ?></span>
                                            </td>
                                            <td>
                                                <span class="text-muted searchable-venue">📍 <?php echo htmlspecialchars($row['VENUE']); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge-live">Active & Published</span>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 4px; justify-content: flex-end;">
                                                    <a href="admin_published_event.php?edit_title=<?php echo urlencode($row['EVENT_TITLE']); ?>" class="action-link-edit">Edit</a>
                                                    <a href="admin_published_event.php?delete_title=<?php echo urlencode($row['EVENT_TITLE']); ?>" onclick="return confirm('Are sure you want to delete this event?')" class="action-link-remove">Remove</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr id="noResultsRow">
                                        <td colspan="5" style="text-align: center; color: #71717a; padding: 30px; font-style: italic;">
                                            No active published events found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                
                                <tr id="emptySearchRow" style="display: none;">
                                    <td colspan="5" style="text-align: center; color: #71717a; padding: 30px; font-style: italic;">
                                        🔍 No matching events found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
            
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

    /* FUNGSI JAVASCRIPT UNTUK PENAPISAN EVENT */
    function filterEvents() {
        const input = document.getElementById('eventSearchInput');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('eventsTable');
        if (!table) return;
        
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        let hasMatches = false;
        let totalRows = 0;

        for (let i = 0; i < rows.length; i++) {
            // Abaikan baris mesej kosong
            if (rows[i].id === 'emptySearchRow' || rows[i].id === 'noResultsRow') continue;
            
            totalRows++;
            const titleEl = rows[i].querySelector('.searchable-title');
            const venueEl = rows[i].querySelector('.searchable-venue');
            
            if (titleEl || venueEl) {
                const titleText = titleEl ? titleEl.textContent || titleEl.innerText : "";
                const venueText = venueEl ? venueEl.textContent || venueEl.innerText : "";
                
                if (titleText.toLowerCase().indexOf(filter) > -1 || venueText.toLowerCase().indexOf(filter) > -1) {
                    rows[i].style.display = "";
                    hasMatches = true;
                } else {
                    rows[i].style.display = "none";
                }
            }
        }

        // Papar mesej "No matching events found" jika carian tiada hasil
        const emptyRow = document.getElementById('emptySearchRow');
        if (emptyRow && totalRows > 0) {
            if (!hasMatches && filter !== "") {
                emptyRow.style.display = "";
            } else {
                emptyRow.style.display = "none";
            }
        }
    }
    </script>
</body>
</html>
<?php 
ob_end_flush(); 
?>