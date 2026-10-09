<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only those that log in can access
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_event'])) {
    include('db_connect.php');
    
    $event_title = trim($_POST['event_title']);
    $organizer_email = $_SESSION['user_email'];
    $category = $_POST['category'];
    $description = trim($_POST['description']);
    $date = $_POST['date'];
    $venue = trim($_POST['venue']);
    $event_type = $_POST['event_type'];
    
    // 🛠️ UPDATED: Save raw user entries exactly without forcing decimals or ASCII conversions
    if ($event_type === 'Paid') {
        $ticket_price = trim($_POST['ticket_price']); // Stores raw pricing text, e.g. "25.00"
    } elseif ($event_type === 'Private') {
        $ticket_price = trim($_POST['private_code']);  // Stores custom code strings completely intact, e.g. "ayam"
    } else {
        $ticket_price = "0.00"; // Free options fall back gracefully to standard clean format text
    }
    
    $time = "00:00:00";
    $max_participants = 0;
    $picture = "default.jpg";      
    $event_flow = "To be defined";  

    // Query column names modified to uppercase matching database table schema
    $checkTitle = $conn->prepare("SELECT `EVENT_TITLE` FROM `EVENT` WHERE `EVENT_TITLE` = ?");
    $checkTitle->bind_param("s", $event_title);
    $checkTitle->execute();
    $resTitle = $checkTitle->get_result();
    
    if ($resTitle->num_rows > 0) {
        $message = "<div style='background: #dc2626; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight:500;'>
                        ⚠️ Error: An event named \"" . htmlspecialchars($event_title) . "\" already exists. Title must be unique!
                    </div>";
    } else {
        $sql = "INSERT INTO `EVENT` (`EVENT_TITLE`, `EMAIL_ORGANIZER`, `CATEGORY`, `EVENT_TYPE`, `DESCRIPTION`, `DATE`, `TIME`, `VENUE`, `TICKET_PRICE`, `MAX_PARTICIPANTS`, `PICTURE`, `EVENT_FLOW`, `APPROVAL_STATUS`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
        
        $stmt = $conn->prepare($sql);
        
        // 🛠️ FIXED PARAMETER BIND: Changed the 9th parameter identifier block from 'd' to 's' 
        // because $ticket_price is now processed cleanly as a safe string (VARCHAR)
        $stmt->bind_param("ssssssssssss", 
            $event_title, 
            $organizer_email, 
            $category, 
            $event_type, 
            $description, 
            $date, 
            $time, 
            $venue, 
            $ticket_price, 
            $max_participants, 
            $picture, 
            $event_flow
        );

        if ($stmt->execute()) {
            $message = "<div style='background: #10b981; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight:500;'>
                            🎉 Success! Event proposed successfully. Awaiting Admin verification check.
                        </div>";
        } else {
            $message = "<div style='background: #dc2626; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight:500;'>
                            ⚠️ Database Processing Error: " . $conn->error . "
                        </div>";
        }
        $stmt->close();
    }
    $checkTitle->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Propose New Event - SmartVille</title>
    <link rel="stylesheet" href="style.css">
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            const savedSize = localStorage.getItem('fontSize') || '16';
            if (savedTheme === 'light') {
                document.documentElement.classList.add('light-mode');
            }
            document.documentElement.style.setProperty('--base-font-size', savedSize + 'px');
            
            const sidebarHidden = localStorage.getItem('sidebarHidden');
            if (sidebarHidden === 'true') {
                document.documentElement.classList.add('preload-sidebar-hidden');
            }
        })();
    </script>

    <style>
        :root {
            --base-font-size: 16px;
            --base-font-family: 'Segoe UI', sans-serif;
        }

        html { font-size: var(--base-font-size) !important; font-family: var(--base-font-family) !important; }
        body, nav, .sidebar, .main-content, h1, h2, h3, span, a, p, label, input, textarea, button, select { font-family: var(--base-font-family) !important; }

        body {
            background-color: #06060c !important;
            color: #ffffff;
            margin: 0;
            display: flex;
            overflow-x: hidden;
        }

        body.sidebar-hidden #sidebar, html.preload-sidebar-hidden #sidebar {
            transform: translateX(-240px) !important;
        }
        body.sidebar-hidden .main-content, html.preload-sidebar-hidden .main-content {
            margin-left: 0 !important;
        }

        .main-content { 
            flex: 1;
            margin-left: 240px;
            padding: 40px; 
            min-width: 0;
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-sizing: border-box;
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

        @media (max-width: 992px) { 
            .main-content, body.sidebar-hidden .main-content { margin-left: 0 !important; }
            body:not(.sidebar-hidden) #sidebar { transform: translateX(-240px) !important; }
            body.sidebar-hidden #sidebar { transform: translateX(0) !important; }
        }

        html.light-mode { background-color: #f4f4f7 !important; }
        html.light-mode body, html.light-mode .main-content { background: #f4f4f7 !important; color: #18181b !important; }
        
        html.light-mode .btn-control {
            background: #e4e4e7 !important;
            color: #18181b !important;
            border: 1px solid rgba(0,0,0,0.15) !important;
        }
        html.light-mode .btn-control:hover {
            background: #d4d4d8 !important;
            border-color: rgba(0,0,0,0.25) !important;
        }
    </style>

    <script>
        function handleEventTypeChange() {
            var eventType = document.getElementById("event_type").value;
            var priceGroup = document.getElementById("price_input_group");
            var privateGroup = document.getElementById("private_input_group");
            
            var priceInput = document.getElementById("ticket_price");
            var privateInput = document.getElementById("private_code");
            
            if (eventType === "Paid") {
                priceGroup.style.display = "block";
                privateGroup.style.display = "none";
                
                priceInput.required = true;
                privateInput.required = false;
                priceInput.placeholder = "e.g., 25.00";
            } else if (eventType === "Private") {
                priceGroup.style.display = "none";
                privateGroup.style.display = "block";
                
                priceInput.required = false;
                privateInput.required = true;
                privateInput.placeholder = "Enter your secret access code / numeric pin...";
            } else {
                priceGroup.style.display = "none";
                privateGroup.style.display = "none";
                
                priceInput.required = false;
                privateInput.required = false;
            }
        }
    </script>
</head>
<body>
    
    <?php include('header.php'); ?>

    <main class="main-content" id="main-content">
        <div class="content-container" style="padding: 0;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0px;">
                <h1 style="margin: 0; font-size: 32px; font-weight: bold; line-height: 1.2;">Create Event</h1>
                <button type="button" class="btn-control" onclick="toggleSidebar()">
                    &#9776; Toggle Menu
                </button>
            </div>

            <div style="margin-top: 30px;">
                <?php echo $message; ?>
            </div>

            <div class="panel">
                <h2>Propose New Event (Step 1)</h2>
                <p style="color: #a1a1aa; font-size: 14px; margin-bottom: 25px;">
                    Logged in as Organizer: <strong><?php echo htmlspecialchars($_SESSION['user_email']); ?></strong>
                </p>

                <form action="create_event.php" method="POST">
                    
                    <div class="form-group">
                        <label for="event_title">Unique Event Title</label>
                        <input type="text" id="event_title" name="event_title" placeholder="e.g., Annual Charity Run 2026" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Category Classification</label>
                        <select id="category" name="category" required>
                            <option value="Sports & Recreation">Sports & Recreation</option>
                            <option value="Technology & Coding">Technology & Coding</option>
                            <option value="Lifestyle & Social">Lifestyle & Social</option>
                            <option value="Academic & Study Groups">Academic & Study Groups</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="event_type">Access Type Configuration</label>
                        <select id="event_type" name="event_type" onchange="handleEventTypeChange()" required>
                            <option value="Free">Free Event (No Ticket Charge)</option>
                            <option value="Paid">Paid Event (Requires Entry Fee)</option>
                            <option value="Private">Private Event (Access Code Protected)</option>
                        </select>
                    </div>

                    <div class="form-group" id="price_input_group" style="margin-bottom: 15px; display: none;">
                        <label for="ticket_price">Ticket Details / Admission Pricing Context</label>
                        <input type="text" id="ticket_price" name="ticket_price" placeholder="e.g., 25.00">
                    </div>

                    <div class="form-group" id="private_input_group" style="margin-bottom: 15px; display: none;">
                        <label for="private_code" style="color: #c084fc;">🔑 Custom Private Access Code / PIN</label>
                        <input type="text" id="private_code" name="private_code" maxlength="15" placeholder="e.g., SECRET77 atau 123456">
                        <small style="color: #a1a1aa; display: block; margin-top: 5px;">*Choose your own secret password or number to give to invited guests.</small>
                    </div>

                    <div class="form-group">
                        <label for="date">Scheduled Event Date</label>
                        <input type="date" id="date" name="date" required>
                    </div>

                    <div class="form-group">
                        <label for="venue">Event Venue Location</label>
                        <input type="text" id="venue" name="venue" placeholder="e.g., Community Hall Block C" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Basic Event Overview / Summary Information</label>
                        <textarea id="description" name="description" rows="5" placeholder="Provide general outline metrics detailing the intent of this target project..." required></textarea>
                    </div>

                    <div style="text-align: right; margin-top: 10px;">
                        <button type="submit" name="submit_event" class="btn-primary" style="border-color: rgba(236, 72, 153, 0.4); color: #ec4899; background: rgba(236, 72, 153, 0.05);">Submit for Administrative Approval</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        document.body.classList.toggle('sidebar-hidden');
        
        let isHidden = document.body.classList.contains('sidebar-hidden');
        
        if (sidebar) {
            sidebar.classList.toggle('hidden');
            isHidden = sidebar.classList.contains('hidden') || document.body.classList.contains('sidebar-hidden');
        }
        localStorage.setItem('sidebarHidden', isHidden);
    }

    window.addEventListener('DOMContentLoaded', () => {
        handleEventTypeChange();

        const isHidden = localStorage.getItem('sidebarHidden') === 'true';
        if (isHidden) {
            document.body.classList.add('sidebar-hidden');
        } else {
            document.body.classList.remove('sidebar-hidden');
        }
        document.documentElement.classList.remove('preload-sidebar-hidden');

        const currentTheme = localStorage.getItem('theme') || 'dark';
        if (currentTheme === 'light') {
            document.documentElement.classList.add('light-mode');
        } else {
            document.documentElement.classList.remove('light-mode');
        }
        
        const size = localStorage.getItem('fontSize') || '16';
        document.documentElement.style.setProperty('--base-font-size', size + 'px');
        
        const savedFont = localStorage.getItem('fontStyle') || "'Segoe UI', sans-serif";
        document.documentElement.style.setProperty('--base-font-family', savedFont);
    });
    </script>
</body>
</html>