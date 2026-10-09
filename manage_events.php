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

// 🛠️ FIXED DELETE HANDLER: Uses uppercase table schema rules mapping directly to `EVENT`
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['event_title'])) {
    $title_to_delete = $_POST['event_title'];
    
    // Explicit check: Ensure the event belongs to this user AND its approval status is 'Rejected'
    $delete_stmt = $conn->prepare("DELETE FROM `EVENT` WHERE LOWER(`EMAIL_ORGANIZER`) = LOWER(?) AND `EVENT_TITLE` = ? AND LOWER(`APPROVAL_STATUS`) = 'rejected'");
    $delete_stmt->bind_param("ss", $user_email, $title_to_delete);
    $delete_stmt->execute();
    $delete_stmt->close();
    
    // Redirect cleanly to avoid duplicate form submission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project / Events Log - SmartVille</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .status-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }
        .status-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid #f59e0b; }
        .status-approved { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid #10b981; }
        .status-published { background: rgba(236, 72, 153, 0.1); color: #ec4899; border: 1px solid #ec4899; }
        .status-rejected { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 14px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 14px; color: #e4e4e7; }
        th { color: #a1a1aa; font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }

        .btn-action-view {
            display: inline-block; 
            padding: 6px 14px; 
            background: rgba(236, 72, 153, 0.1); 
            color: #ec4899; 
            border: 1px solid rgba(236, 72, 153, 0.3); 
            border-radius: 6px; 
            text-decoration: none; 
            font-size: 13px; 
            font-weight: 600; 
            transition: all 0.2s ease;
        }
        .btn-action-view:hover {
            background: rgba(236, 72, 153, 0.2);
            color: #ffffff;
        }

        .btn-action-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-action-delete:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #ffffff;
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

        .search-container {
            margin-bottom: 20px;
            width: 100%;
            max-width: 320px;
        }
        .search-input {
            width: 100%;
            padding: 10px 14px;
            background: #1c1c34;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #ffffff;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.2s ease;
        }
        .search-input:focus {
            border-color: #ec4899;
            outline: none;
        }

        /* Light Mode Overrides */
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
        html.light-mode .search-input {
            background: #f4f4f7;
            border: 1px solid rgba(0, 0, 0, 0.15);
            color: #18181b;
        }
        html.light-mode .search-input:focus {
            border-color: #ec4899;
        }
        html.light-mode th { color: #71717a; }
        html.light-mode td { color: #18181b; border-bottom: 1px solid rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <main class="main-content" id="main-content">
        <div class="content-container" style="padding: 0;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h1 style="margin: 0; font-size: 32px; font-weight: bold; line-height: 1.2;">Project / Events Management</h1>
                <button type="button" class="btn-control" onclick="toggleSidebar()">
                    &#9776; Toggle Menu
                </button>
            </div>
            
            <div class="panel">
                <h2>Your Created Events</h2>

                <div class="search-container">
                    <input type="text" id="eventSearch" class="search-input" placeholder="🔍 Search event or category..." onkeyup="filterEvents()">
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Approved By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="eventTableBody">
                        <?php
                        // 🛠️ FIXED MAIN QUERY: Targeted matching case-sensitive table `EVENT`
                        $stmt = $conn->prepare("SELECT `EVENT_TITLE`, `CATEGORY`, `DATE`, `APPROVAL_STATUS`, `EMAIL_ADMIN` FROM `EVENT` WHERE LOWER(`EMAIL_ORGANIZER`) = LOWER(?) ORDER BY `DATE` ASC");
                        $stmt->bind_param("s", $user_email);
                        $stmt->execute();
                        $stmt->store_result();
                        
                        if ($stmt->num_rows > 0) {
                            $stmt->bind_result($event_title, $category, $date, $approval_status, $email_admin);
                            
                            while ($stmt->fetch()) {
                                $status = !empty($approval_status) ? trim($approval_status) : 'Pending';
                                $clean_status = strtolower($status);
                                
                                if ($clean_status == 'pending') {
                                    $badge = "status-pending";
                                } elseif ($clean_status == 'approved') {
                                    $badge = "status-approved";
                                } elseif ($clean_status == 'rejected') {
                                    $badge = "status-rejected";
                                } else {
                                    $badge = "status-published";
                                }

                                echo "<tr>";
                                echo "<td><strong style='font-weight:600;'>" . htmlspecialchars($event_title) . "</strong></td>";
                                echo "<td>" . (!empty($category) ? htmlspecialchars($category) : "<span style='color: #71717a; font-style: italic; font-size: 13px;'>Not Specified</span>") . "</td>";
                                echo "<td>" . htmlspecialchars($date) . "</td>";
                                echo "<td><span class='status-badge {$badge}'>" . htmlspecialchars($status) . "</span></td>";
                                
                                echo "<td>" . (!empty($email_admin) ? htmlspecialchars($email_admin) : "<span style='color: #71717a;'>-</span>") . "</td>";
                                
                                echo "<td>";
                                if ($clean_status == 'approved') {
                                    echo "<a href='finalize_event.php?title=".urlencode($event_title)."' class='btn-primary' style='padding:6px 12px; font-size:13px; color:#ec4899; border-color:rgba(236, 72, 153, 0.3); background:rgba(236, 72, 153,0.05); text-decoration:none; border-radius:6px; border:1px solid;'>Finalize & Publish</a>";
                                } else if ($clean_status == 'published') {
                                    echo "<a href='view_event.php?title=" . urlencode($event_title) . "' class='btn-action-view'>👁️ View</a>";
                                } else if ($clean_status == 'rejected') {
                                    echo "<form method='POST' style='display:inline-block; margin:0;' onsubmit='return confirm(\"Are you sure you want to permanently delete this rejected event?\");'>";
                                    echo "<input type='hidden' name='event_title' value='" . htmlspecialchars($event_title) . "'>";
                                    echo "<button type='submit' name='action' value='delete' class='btn-action-delete'>🗑️ Delete</button>";
                                    echo "</form>";
                                } else {
                                    echo "<span style='color:#71717a; font-size:13px; font-style:italic;'>Awaiting Review</span>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr class='no-data-row'><td colspan='6' style='text-align:center; color:#71717a; padding:30px; font-style:italic;'>No project event records found.</td></tr>";
                        }
                        $stmt->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
    function filterEvents() {
        const input = document.getElementById('eventSearch');
        const filter = input.value.toLowerCase();
        const tbody = document.getElementById('eventTableBody');
        const rows = tbody.getElementsByTagName('tr');

        if (rows.length === 1 && rows[0].classList.contains('no-data-row')) {
            return;
        }

        for (let i = 0; i < rows.length; i++) {
            const titleCol = rows[i].getElementsByTagName('td')[0];
            const categoryCol = rows[i].getElementsByTagName('td')[1];
            
            if (titleCol && categoryCol) {
                const titleText = titleCol.textContent || titleCol.innerText;
                const categoryText = categoryCol.textContent || categoryCol.innerText;
                
                if (titleText.toLowerCase().indexOf(filter) > -1 || categoryText.toLowerCase().indexOf(filter) > -1) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
    }
    </script>
</body>
</html>