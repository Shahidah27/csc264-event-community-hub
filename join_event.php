<?php
// join_event.php - Resident Event Registration System
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
include('db_connect.php');

// Ensure only authorized Residents can access this process
if (!isset($_SESSION['user_email']) || $_SESSION['role'] !== 'Resident') {
    header("Location: login.php");
    exit();
}

$resident_email = $_SESSION['user_email'];

// Variables to handle on-screen messages instead of window alerts
$message_status = ''; // Can be 'success' or 'info' or 'error'
$display_message = '';
$show_form = true; // Flag to hide the registration inputs if already registered/successful

// ==========================================================================
// 🛠️ STAGE 2: EXECUTE DATABASE INSERTION (AFTER SUBMITTING THE FORM)
// ==========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_final_registration'])) {
    $event_title = $_POST['event_title'];
    
    // FETCH EXPANDED COLUMNS INCLUDING DESCRIPTION & EVENT FLOW (MATCHED WITH UPPERCASE DB SCHEMA)
    $stmt = $conn->prepare("SELECT `TICKET_PRICE`, `EVENT_TYPE`, `DESCRIPTION`, `VENUE`, `DATE`, `TIME`, `EVENT_FLOW` FROM `EVENT` WHERE `EVENT_TITLE` = ?");
    $stmt->bind_param("s", $event_title);
    $stmt->execute();
    $event_info = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$event_info) {
        $message_status = 'error';
        $display_message = 'Event data could not be verified.';
        $show_form = false;
    } else {
        if ($event_info['EVENT_TYPE'] === 'Private') {
            $pax = 1;
            $payment_status = 'Private Access';
        } else {
            $pax = isset($_POST['pax']) ? intval($_POST['pax']) : 1;
            if ($pax < 1) { $pax = 1; }
            $payment_status = ($event_info['EVENT_TYPE'] === 'Free') ? 'Free Entry' : 'Paid';
        }

        // Check duplicate booking right before trying to write to DB using EMAIL_RESIDENT
        $check_stmt = $conn->prepare("SELECT * FROM `EVENT_PARTICIPATION` WHERE `EVENT_TITLE` = ? AND `EMAIL_RESIDENT` = ?");
        $check_stmt->bind_param("ss", $event_title, $resident_email);
        $check_stmt->execute();
        $already_joined = $check_stmt->get_result()->num_rows > 0;
        $check_stmt->close();

        if ($already_joined) {
            $message_status = 'info';
            $display_message = 'You have already registered for this event.';
            $show_form = false;
        } else {
            // Nota: Mengikut skema SQL baru kau, tiada kolum 'participation_id', kunci utamanya komposit (EVENT_TITLE + EMAIL_RESIDENT)
            $insert_stmt = $conn->prepare("INSERT INTO `EVENT_PARTICIPATION` (`EVENT_TITLE`, `EMAIL_RESIDENT`, `PAX`, `PAYMENT_STATUS`) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssis", $event_title, $resident_email, $pax, $payment_status);
            
            if ($insert_stmt->execute()) {
                $message_status = 'success';
                $display_message = '✨ Registration successful! Your pass has been generated.';
                $show_form = false; // Hide form elements since registration completed successfully
            } else {
                $message_status = 'error';
                $display_message = 'System registration failed. Please try again.';
            }
            $insert_stmt->close();
        }
    }
}

// ==========================================================================
// 👁️ STAGE 1: GRAB EVENT TITLE VIA GET OR POST & RUN PRE-CHECK
// ==========================================================================
$event_title = isset($_POST['event_title']) ? $_POST['event_title'] : (isset($_GET['event_title']) ? $_GET['event_title'] : '');

if (!empty($event_title)) {
    // If we haven't already processed a form post successfully, check layout data
    if (empty($display_message)) {
        $stmt = $conn->prepare("SELECT * FROM `EVENT` WHERE `EVENT_TITLE` = ?");
        $stmt->bind_param("s", $event_title);
        $stmt->execute();
        $event_info = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$event_info) {
            $message_status = 'error';
            $display_message = 'Event not found in our database system.';
            $show_form = false;
        } else {
            // Check if user is already a participant on screen loading time
            $check_stmt = $conn->prepare("SELECT * FROM `EVENT_PARTICIPATION` WHERE `EVENT_TITLE` = ? AND `EMAIL_RESIDENT` = ?");
            $check_stmt->bind_param("ss", $event_title, $resident_email);
            $check_stmt->execute();
            $already_joined = $check_stmt->get_result()->num_rows > 0;
            $check_stmt->close();

            if ($already_joined) {
                $message_status = 'info';
                $display_message = 'You have already registered for this event.';
                $show_form = false; // Hides selection form layout cleanly
            }
        }
    }
} else {
    header("Location: browse_events.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Event Registration - SmartVille</title>
    
    <script>
    (function () {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        if (savedTheme === 'light') {
            document.documentElement.classList.add('light-mode');
        } else {
            document.documentElement.classList.remove('light-mode');
        }
        
        const savedSize = localStorage.getItem('fontSize') || '16';
        document.documentElement.style.setProperty('--base-font-size', savedSize + 'px');
        
        const savedFont = localStorage.getItem('fontStyle') || "'Inter', sans-serif";
        document.documentElement.style.setProperty('--base-font-family', savedFont);
    })();
    </script>

    <style>
        :root {
            --base-font-size: 16px;
            --primary-accent: #ec4899;
            --purple-gradient: linear-gradient(135deg, #7c3aed, #c084fc);
        }
        html {
            font-size: var(--base-font-size) !important;
        }
        body {
            font-family: var(--base-font-family, 'Segoe UI', system-ui, sans-serif) !important;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 40px 20px;
            box-sizing: border-box;
            transition: background-color 0.15s ease, color 0.15s ease;
        }
        
        .checkout-card {
            border-radius: 20px;
            padding: 35px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.3);
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }
        
        h2 { margin-top: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.5px; margin-bottom: 20px; }
        h3 { font-size: 15px; margin-bottom: 15px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        
        .status-alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
            line-height: 1.5;
            font-weight: 500;
        }

        .event-details { padding: 20px; border-radius: 12px; margin-bottom: 22px; border-left: 4px solid var(--primary-accent); }
        .event-details p { margin: 8px 0; font-size: 14.5px; }
        
        .info-block-section {
            margin: 20px 0;
            padding: 18px;
            border-radius: 10px;
            font-size: 13.5px;
            line-height: 1.6;
        }
        .info-title {
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            color: var(--primary-accent);
        }
        .flow-timeline {
            margin-top: 10px;
            padding-left: 12px;
            border-left: 2px dashed rgba(236, 72, 153, 0.3);
        }
        .timeline-item {
            position: relative;
            margin-bottom: 8px;
            padding-left: 10px;
        }
        .timeline-item::before {
            content: "•";
            position: absolute;
            left: -10px;
            color: var(--primary-accent);
            font-weight: bold;
        }

        .badge { display: inline-block; padding: 3px 8px; font-size: 11px; font-weight: 700; border-radius: 4px; text-transform: uppercase; }
        .badge-paid { background: rgba(236, 72, 153, 0.15); color: #ec4899; }
        .badge-free { background: rgba(16, 185, 129, 0.15); color: #10b981; }
        .badge-private { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
        
        label { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px; }
        select, input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
            margin-bottom: 18px;
            font-family: inherit;
        }
        select:focus, input:focus { border-color: var(--primary-accent); outline: none; }
        
        .price-summary { display: flex; justify-content: space-between; align-items: center; margin: 20px 0; font-weight: bold; }
        .price-total { color: var(--primary-accent); font-size: 22px; }
        
        .btn-submit {
            background: var(--purple-gradient);
            color: white !important;
            border: none;
            padding: 14px;
            width: 100%;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-submit:hover { opacity: 0.9; }
        
        .btn-dashboard {
            color: white !important;
            padding: 14px;
            width: 100%;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            display: block;
            text-align: center;
            box-sizing: border-box;
        }
        .cancel-link { display: block; text-align: center; margin-top: 18px; font-size: 13px; text-decoration: none; }

        /* Dark Mode Theme Mapping */
        html:not(.light-mode) body { background-color: #070a13 !important; color: #e2e8f0 !important; }
        html:not(.light-mode) .checkout-card { background: #141a2f; border: 1px solid rgba(255, 255, 255, 0.06); }
        html:not(.light-mode) h2, html:not(.light-mode) h3 { color: #ffffff; }
        html:not(.light-mode) label { color: #94a3b8; }
        html:not(.light-mode) .event-details { background: #1e2642; }
        html:not(.light-mode) .event-details p { color: #cbd5e1; }
        html:not(.light-mode) .info-block-section { background: rgba(30, 38, 66, 0.5); border: 1px solid rgba(255,255,255,0.04); }
        html:not(.light-mode) select, html:not(.light-mode) input { background: #070a13; border: 1px solid rgba(255, 255, 255, 0.06); color: white; }
        html:not(.light-mode) select:disabled { background: #1a233d; color: #64748b; cursor: not-allowed; }
        html:not(.light-mode) .btn-dashboard { background: #1e2642; border: 1px solid rgba(255,255,255,0.1); }
        html:not(.light-mode) .btn-dashboard:hover { background: #283358; }
        html:not(.light-mode) .cancel-link { color: #94a3b8; }
        html:not(.light-mode) .cancel-link:hover { color: #ffffff; }
        html:not(.light-mode) .alert-info { background-color: rgba(59, 130, 246, 0.15); border: 1px solid #3b82f6; color: #60a5fa; }
        html:not(.light-mode) .alert-success { background-color: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #34d399; }
        html:not(.light-mode) .alert-error { background-color: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; }

        /* Light Mode Theme Mapping */
        html.light-mode body { background-color: #f4f4f7 !important; color: #0f172a !important; }
        html.light-mode .checkout-card { background: #ffffff; border: 1px solid rgba(0, 0, 0, 0.08); box-shadow: 0 10px 25px rgba(0,0,0,0.06); }
        html.light-mode h2, html.light-mode h3 { color: #0f172a; }
        html.light-mode label { color: #475569; }
        html.light-mode .event-details { background: #f8fafc; border-left: 4px solid #db2777; }
        html.light-mode .event-details p { color: #334155; }
        html.light-mode .info-block-section { background: #f1f5f9; border: 1px solid rgba(0,0,0,0.04); }
        html.light-mode select, html.light-mode input { background: #f1f5f9; border: 1px solid rgba(0, 0, 0, 0.08); color: #0f172a; }
        html.light-mode select:disabled { background: #e2e8f0; color: #94a3b8; cursor: not-allowed; }
        html.light-mode .btn-dashboard { background: #0f172a; border: 1px solid transparent; }
        html.light-mode .btn-dashboard:hover { background: #1e293b; }
        html.light-mode .cancel-link { color: #64748b; }
        html.light-mode .cancel-link:hover { color: #0f172a; }
        html.light-mode .alert-info { background-color: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
        html.light-mode .alert-success { background-color: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        html.light-mode .alert-error { background-color: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
    </style>
</head>
<body>

<div class="checkout-card">
    <h2>Event Registration</h2>
    
    <?php if (!empty($display_message)): ?>
        <div class="status-alert alert-<?php echo $message_status; ?>">
            <?php echo htmlspecialchars($display_message); ?>
        </div>
    <?php endif; ?>
    
    <div class="event-details">
        <p style="font-size: 17px; font-weight: 700; margin-bottom: 12px;">
            Title: <span style="color: inherit;"><?php echo htmlspecialchars($event_title); ?></span>
        </p>
        <?php if (isset($event_info)): ?>
            <p>📅 <strong>Date / Time:</strong> <?php echo htmlspecialchars($event_info['DATE'] ?? 'TBA'); ?> @ <?php echo htmlspecialchars($event_info['TIME'] ?? 'TBA'); ?></p>
            <p>📍 <strong>Location Venue:</strong> <?php echo htmlspecialchars($event_info['VENUE'] ?? 'Community Workspace Hall'); ?></p>
            <p style="margin-top: 12px;"><strong>Access Type:</strong> 
                <?php if ($event_info['EVENT_TYPE'] === 'Paid'): ?>
                    <span class="badge badge-paid">Paid Entry</span>
                <?php elseif ($event_info['EVENT_TYPE'] === 'Private'): ?>
                    <span class="badge badge-private">Private Event</span>
                <?php else: ?>
                    <span class="badge badge-free">Free Entry</span>
                <?php endif; ?>
            </p>
            <p><strong>Price per Pax:</strong> <span style="color: #ec4899; font-weight: 700;">
                <?php 
                if ($event_info['EVENT_TYPE'] === 'Paid') {
                    echo "RM " . number_format((float)$event_info['TICKET_PRICE'], 2); 
                } else if ($event_info['EVENT_TYPE'] === 'Private') {
                    echo "Private Pass Access"; 
                } else {
                    echo "FREE";
                }
                ?>
            </span></p>
        <?php endif; ?>
    </div>

    <?php if (isset($event_info)): ?>
        <div class="info-block-section">
            <div class="info-title">📋 About This Event (Description)</div>
            <div style="margin-bottom: 15px;">
                <?php 
                echo !empty($event_info['DESCRIPTION']) 
                    ? nl2br(htmlspecialchars($event_info['DESCRIPTION'])) 
                    : "No breakdown description provided for this community event."; 
                ?>
            </div>

            <div class="info-title">⏳ Event Flow / Agenda</div>
            <div class="flow-timeline">
                <?php 
                if (!empty($event_info['EVENT_FLOW'])) {
                    $agenda_items = explode("\n", $event_info['EVENT_FLOW']);
                    foreach ($agenda_items as $item) {
                        if (trim($item) !== '') {
                            echo '<div class="timeline-item">' . htmlspecialchars(trim($item)) . '</div>';
                        }
                    }
                } else {
                    echo '<div class="timeline-item"><strong>' . htmlspecialchars($event_info['TIME'] ?? '00:00') . '</strong> - Welcome Check-in & Registration Door Open</div>';
                    echo '<div class="timeline-item"><strong>Opening Sequence</strong> - Main Event Content Activities briefing</div>';
                    echo '<div class="timeline-item"><strong>Closing Window</strong> - Open Interaction Networking, Q&A and Dismissal</div>';
                }
                ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($show_form && isset($event_info)): ?>
        <form action="join_event.php" method="POST">
            <input type="hidden" name="event_title" value="<?php echo htmlspecialchars($event_title); ?>">
            <input type="hidden" name="submit_final_registration" value="1">

            <?php if ($event_info['EVENT_TYPE'] === 'Private'): ?>
                <label for="pax_disabled">Quantity (Pax)</label>
                <input type="hidden" name="pax" value="1">
                <select id="pax_disabled" disabled>
                    <option value="1">1 Person (Fixed for Private Access)</option>
                </select>
            <?php else: ?>
                <label for="pax">Select Quantity (Pax)</label>
                <select name="pax" id="pax" onchange="calculateTotal(this.value)">
                    <option value="1">1 Person</option>
                    <option value="2">2 Persons</option>
                    <option value="3">3 Persons</option>
                    <option value="4">4 Persons</option>
                    <option value="5">5 Persons</option>
                </select>
            <?php endif; ?>

            <?php if ($event_info['EVENT_TYPE'] === 'Paid'): ?>
                <div id="payment-fields">
                    <hr style="border: 0; border-top: 1px solid rgba(120,120,120,0.15); margin: 20px 0;">
                    <h3>💳 Payment Details</h3>
                    
                    <label>Cardholder's Name</label>
                    <input type="text" placeholder="John Doe" required>

                    <label>Credit / Debit Card Number</label>
                    <input type="text" pattern="\d{16}" title="Please enter a valid 16-digit card number" placeholder="0000000000000000" maxlength="16" required>

                    <div style="display: flex; gap: 10px;">
                        <div style="flex: 1;">
                            <label>Expiry Date</label>
                            <input type="text" placeholder="MM/YY" maxlength="5" required>
                        </div>
                        <div style="flex: 1;">
                            <label>CVV</label>
                            <input type="text" pattern="\d{3}" placeholder="123" maxlength="3" required>
                        </div>
                    </div>

                    <div class="price-summary">
                        <span>Total Cost:</span>
                        <span class="price-total" id="display-total">RM <?php echo number_format((float)$event_info['TICKET_PRICE'], 2); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn-submit">
                <?php echo ($event_info['EVENT_TYPE'] === 'Paid') ? '🔒 Pay & Confirm Booking' : '✨ Confirm Registration'; ?>
            </button>
            
            <a href="browse_events.php" class="cancel-link">Cancel and Return</a>
        </form>
    <?php else: ?>
        <div style="margin-top: 25px;">
            <a href="browse_events.php" class="btn-dashboard">Return to Browse Events</a>
        </div>
    <?php endif; ?>
</div>

<script>
    <?php if (isset($event_info)): ?>
    const rate = <?php echo ($event_info['EVENT_TYPE'] === 'Paid') ? (float)$event_info['TICKET_PRICE'] : 0; ?>;
    function calculateTotal(quantity) {
        const totalDisplay = document.getElementById('display-total');
        if (totalDisplay) {
            const total = rate * parseInt(quantity);
            totalDisplay.innerText = "RM " + total.toFixed(2);
        }
    }
    <?php endif; ?>
</script>

</body>
</html>