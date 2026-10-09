<?php
// admin_schedule.php - Venue Scheduling Timeline Tracker
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
include('db_connect.php'); 

if (!isset($_SESSION['user_email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php?role=admin"); 
    exit();
}

// Filter approval status 'published' or 'Approved' to match data immediately after approval in Inbox
$schedule_query = $conn->query("SELECT event_title, venue, date, time, max_participants FROM `event` WHERE LOWER(approval_status) = 'published' OR approval_status = 'Approved' OR approval_status = 'published' ORDER BY date ASC, time ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venue Booking Timelines</title>
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
            margin-bottom: 24px;
        }

        .panel h2 {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 20px 0;
            color: #ffffff;
        }

        /* BAHAGIAN FILTER BARU */
        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 20px;
            align-items: center;
        }

        .search-wrapper {
            position: relative;
            flex: 1;
            min-width: 260px;
        }

        .search-wrapper .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #71717a;
            font-size: 16px;
            pointer-events: none;
        }

        .filter-input {
            width: 100%;
            background: #1c1c34;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            padding: 10px 16px;
            color: #ffffff;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.2s;
            font-family: inherit;
        }

        .filter-input-search {
            padding-left: 40px;
        }

        .filter-date-wrapper {
            width: 200px;
        }

        .filter-input:focus {
            outline: none;
            border-color: #ec4899;
        }

        .btn-reset {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #ef4444;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-reset:hover {
            background: rgba(239, 68, 68, 0.2);
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

        .event-highlight {
            color: #ec4899;
            font-weight: 600;
        }

        /* LIGHT MODE OVERRIDES */
        html.light-mode body { background-color: #f4f4f7 !important; color: #18181b !important; }
        html.light-mode .main-content { background: #f4f4f7 !important; }
        html.light-mode .panel { background: #ffffff !important; border: 1px solid rgba(0,0,0,0.06) !important; }
        html.light-mode .panel h2 { color: #18181b !important; }
        html.light-mode td { color: #18181b !important; border-bottom: 1px solid rgba(0,0,0,0.06); }
        html.light-mode td strong { color: #18181b !important; }
        html.light-mode td span { color: #18181b; }
        html.light-mode td .text-muted { color: #71717a; }

        html.light-mode .filter-input {
            background: #e4e4e7;
            border: 1px solid rgba(0, 0, 0, 0.15);
            color: #18181b;
        }
        html.light-mode .search-wrapper .search-icon { color: #71717a; }

        html.light-mode .btn-control {
            background: #e4e4e7 !important;
            color: #18181b !important;
            border: 1px solid rgba(0,0,0,0.15) !important;
            box-shadow: none !important;
        }
        html.light-mode .btn-control:hover {
            background: #d4d4d8 !important;
            border-color: rgba(0,0,0,0.25) !important;
        }
    </style>
</head>
<body>

    <?php include('admin_sidebar_helper.php'); ?>

    <main class="main-content" id="main-content">
        <div class="content-container" style="padding: 0;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h1 style="margin: 0; font-size: 32px; font-weight: bold; line-height: 1.2; color: inherit;">Platform Venue Reservation Matrices</h1>
                <button type="button" class="btn-control" onclick="toggleSidebar()">
                    &#9776; Toggle Menu
                </button>
            </div>
            
            <div class="panel">
                <h2>Active Asset Allocations & Timeline Bookings</h2>
                
                <!-- BLOK FILTER INPUT (TEMPAT & TARIKH) -->
                <div class="filter-container">
                    <div class="search-wrapper">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="venueSearch" class="filter-input filter-input-search" placeholder="Search venue or event details..." onkeyup="filterSchedule()">
                    </div>
                    <div class="filter-date-wrapper">
                        <input type="date" id="dateSearch" class="filter-input" onchange="filterSchedule()">
                    </div>
                    <div>
                        <button type="button" class="btn-reset" onclick="resetFilters()">Reset</button>
                    </div>
                </div>

                <div style="overflow-x: auto; margin-top:15px;">
                    <table id="scheduleTable">
                        <thead>
                            <tr>
                                <th>Target Location / Venue Site</th>
                                <th>Reserved Date</th>
                                <th>Time Interval Window</th>
                                <th>Engaged Event Details</th>
                                <th>Capacity Profile</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($schedule_query && $schedule_query->num_rows > 0): 
                                while($sched = $schedule_query->fetch_assoc()): ?>
                                    <?php 
                                        // Check if the event date has passed
                                        $is_past = strtotime($sched['date']) < time();
                                    ?>
                                    <tr style="<?php echo $is_past ? 'opacity: 0.55;' : ''; ?>">
                                        <td><strong class="venue-cell">📍 <?php echo htmlspecialchars($sched['venue']); ?></strong></td>
                                        <td><span class="date-cell" data-raw-date="<?php echo htmlspecialchars($sched['date']); ?>">📅 <?php echo htmlspecialchars($sched['date']); ?></span></td>
                                        <td><span style="color: inherit;">⏰ <?php echo htmlspecialchars($sched['time']); ?></span></td>
                                        <td><span class="event-cell event-highlight"><?php echo htmlspecialchars($sched['event_title']); ?></span></td>
                                        <td><span class="text-muted">👥 Limit Max: <?php echo htmlspecialchars($sched['max_participants']); ?> slots</span></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr id="noDataRow">
                                    <td colspan="5" style="text-align: center; color: #71717a; padding: 30px; font-style: italic;">
                                        No operational asset tracking schedules recorded for production deployment.
                                    </td>
                                </tr>
                            <?php endif; ?>
                            
                            <!-- Baris mesej paparan jika hasil carian kosong -->
                            <tr id="emptyFilterRow" style="display: none;">
                                <td colspan="5" style="text-align: center; color: #71717a; padding: 30px; font-style: italic;">
                                    🔍 No matching reservations found for the selected criteria.
                                </td>
                            </tr>
                        </tbody>
                    </table>
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

    // FUNGSI UTAMA UNTUK FILTER TEMPAT & TARIKH SECARA SERENTAK
    function filterSchedule() {
        const textFilter = document.getElementById('venueSearch').value.toLowerCase();
        const dateFilter = document.getElementById('dateSearch').value; // Format: YYYY-MM-DD
        
        const table = document.getElementById('scheduleTable');
        if (!table) return;
        
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        let visibleRowsCount = 0;
        let totalRecords = 0;

        for (let i = 0; i < rows.length; i++) {
            // Abaikan baris mesej kosong
            if (rows[i].id === 'emptyFilterRow' || rows[i].id === 'noDataRow') continue;
            
            totalRecords++;
            
            const venueEl = rows[i].querySelector('.venue-cell');
            const eventEl = rows[i].querySelector('.event-cell');
            const dateEl = rows[i].querySelector('.date-cell');
            
            let matchText = false;
            let matchDate = false;
            
            // 1. Semakan Teks (Tempat & Nama Event)
            const venueText = venueEl ? venueEl.textContent.toLowerCase() : '';
            const eventText = eventEl ? eventEl.textContent.toLowerCase() : '';
            if (venueText.includes(textFilter) || eventText.includes(textFilter)) {
                matchText = true;
            }
            
            // 2. Semakan Tarikh
            if (dateFilter === "") {
                matchDate = true; // Jika admin tak pilih tarikh, abaikan tapisan tarikh
            } else {
                const rowRawDate = dateEl ? dateEl.getAttribute('data-raw-date') : ''; // Nilai asal YYYY-MM-DD dari DB
                if (rowRawDate === dateFilter) {
                    matchDate = true;
                }
            }
            
            // Gabungkan kedua-dua kriteria penapisan
            if (matchText && matchDate) {
                rows[i].style.display = "";
                visibleRowsCount++;
            } else {
                rows[i].style.display = "none";
            }
        }
        
        // Papar mesej jika tiada padanan langsung
        const emptyRow = document.getElementById('emptyFilterRow');
        if (emptyRow && totalRecords > 0) {
            if (visibleRowsCount === 0) {
                emptyRow.style.display = "";
            } else {
                emptyRow.style.display = "none";
            }
        }
    }

    // FUNGSI UNTUK SET SEMULA FILTER (RESET)
    function resetFilters() {
        document.getElementById('venueSearch').value = "";
        document.getElementById('dateSearch').value = "";
        filterSchedule();
    }
    </script>
</body>
</html>