<?php
ob_start(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalize Event Workspace - SmartVille</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { 
            background-color: #070a13 !important; 
            color: #ffffff; 
            font-family: Inter, system-ui, -apple-system, sans-serif;
            margin: 0;
        }
        
        .main-content {
            padding: 40px;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .content-container {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .panel {
            background: #0f1424; 
            border: 1px solid #1e293b;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        .wizard-nav { display: flex; gap: 12px; margin-bottom: 35px; }
        
        .wizard-step { 
            flex: 1; padding: 14px; text-align: center; 
            background: #181120; 
            border-radius: 10px; color: #f9a8d4; font-size: 13px; font-weight: 600;
            border: 1px solid #4c1d37; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .wizard-step.active { 
            background: #ec4899; color: #ffffff; border-color: #ec4899;
            box-shadow: 0 0 15px rgba(236, 72, 153, 0.4);
        }

        .step-container { display: none; }
        .step-container.active { display: block; }

        .form-group label { 
            display: block; margin-bottom: 8px; 
            color: #cbd5e1; font-size: 14px; font-weight: 500; 
        }

        .form-group input[type="text"], 
        .form-group input[type="number"], 
        .form-group input[type="time"], 
        .form-group textarea, 
        .form-group select {
            background-color: #111726; 
            color: #ffffff; 
            border: 1px solid #334155; 
            padding: 12px 16px; 
            border-radius: 8px; 
            width: 100%; 
            font-size: 14px;
            outline: none; 
            box-sizing: border-box; 
            transition: background-color 0.2s, border-color 0.2s, color 0.2s;
        }

        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { 
            border-color: #ec4899; 
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.3); 
        }

        .form-group input[disabled], 
        .form-group select[disabled], 
        .form-group select:disabled, 
        .form-group input[readonly] {
            background-color: #0b0f19; 
            color: #94a3b8; 
            cursor: not-allowed; 
            opacity: 1; 
            border-color: #1e293b;
        }

        .form-group input[type="file"] { 
            background: #111726; padding: 20px; border-radius: 8px; width: 100%; 
            color: #94a3b8; border: 1px dashed #334155; cursor: pointer; 
        }

        .venue-payment-gate { 
            background: rgba(236, 72, 153, 0.05); 
            border: 1px dashed rgba(236, 72, 153, 0.5); 
            padding: 20px; 
            border-radius: 10px; 
            margin-top: 25px; 
        }

        .btn-wrapper { 
            display: flex; justify-content: flex-end; gap: 12px; margin-top: 25px; 
            border-top: 1px solid rgba(255, 255, 255, 0.06); padding-top: 20px; 
        }

        .btn-control { 
            background: #1c1c34; 
            color: #ffffff; 
            border: 1px solid rgba(255,255,255,0.1); 
            padding: 10px 16px; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 500; 
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: background 0.2s, border-color 0.2s;
        }
        .btn-control:hover { background: #252542; }

        html.light-mode body { background-color: #f4f4f7 !important; color: #18181b !important; }
        html.light-mode .main-content { background: #f4f4f7 !important; }
        html.light-mode .panel { background: #ffffff !important; border: 1px solid rgba(0,0,0,0.06) !important; }
        html.light-mode h1, html.light-mode h3 { color: #18181b !important; }
        html.light-mode .form-group label { color: #4b5563 !important; }

        html.light-mode .form-group input[type="text"], 
        html.light-mode .form-group input[type="number"], 
        html.light-mode .form-group input[type="time"], 
        html.light-mode .form-group textarea, 
        html.light-mode .form-group select,
        html.light-mode .form-group input[type="file"] {
            background-color: #f9fafb !important; color: #18181b !important; border: 1px solid #d1d5db !important; 
        }

        html.light-mode .form-group input[disabled], 
        html.light-mode .form-group select[disabled], 
        html.light-mode .form-group input[readonly] {
            background-color: #e5e7eb !important; color: #6b7280 !important; border-color: #d1d5db !important;
        }

        html.light-mode .wizard-step { background: #f3f4f6 !important; border: 1px solid #e5e7eb !important; color: #b45309 !important; }
        html.light-mode .wizard-step.active { background: #ec4899 !important; color: #ffffff !important; border-color: #ec4899 !important; }
        html.light-mode .btn-control { background: #e4e4e7 !important; color: #18181b !important; border: 1px solid rgba(0,0,0,0.15) !important; }
        html.light-mode .btn-control:hover { background: #d4d4d8 !important; }
        html.light-mode .venue-payment-gate { background: rgba(236, 72, 153, 0.02) !important; }
    </style>
</head>
<body>

<?php
include('db_connect.php'); 
include('header.php'); 

if(!isset($_GET['title'])) { 
    header("Location: manage_events.php"); 
    exit; 
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$title = $_GET['title'];
$user_email = $_SESSION['user_email'];

$stmt = $conn->prepare("SELECT * FROM `EVENT` WHERE `EVENT_TITLE` = ? AND LOWER(`EMAIL_ORGANIZER`) = LOWER(?) AND LOWER(`APPROVAL_STATUS`) = 'approved'");
$stmt->bind_param("ss", $title, $user_email);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

if(!$event) { 
    die("<div style='background:#f4f4f7; color:#ef4444; padding:40px; text-align:center; font-family:sans-serif;'><h3>Error: Event workspace access denied.</h3><p style='color:#71717a; margin-top:10px;'>Event could not be found, belongs to another user, or has already been finalized.</p></div>"); 
}

$is_free_event = (trim(strtolower($event['EVENT_TYPE'])) === 'free');
$is_private_event = (trim(strtolower($event['EVENT_TYPE'])) === 'private');

// 🔥 DIBAIKI: Jangan gunakan intval()! Kekalkan string teks asal supaya password "AYAMMM" tak hancur jadi 0.
if ($is_private_event) {
    $default_ticket_price = htmlspecialchars($event['TICKET_PRICE']); 
} else {
    $default_ticket_price = $is_free_event ? '0.00' : htmlspecialchars($event['TICKET_PRICE']);
}

$venue_fee = 150.00;
?>

    <main class="main-content" id="main-content">
        <div class="content-container">
            <div class="header-top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <div>
                    <h1 style="margin: 0; font-size: 32px; font-weight: bold;">Finalize & Publish Event</h1>
                    <div style="color: #94a3b8; font-size: 14px; margin-top: 4px;">
                        Complete configuration specs for: <strong style="color: #ec4899; font-weight: 600;"><?php echo htmlspecialchars($event['EVENT_TITLE']); ?></strong>
                    </div>
                </div>
                <div class="top-controls">
                    <a href="manage_events.php" class="btn-control">← Back to Log</a>
                </div>
            </div>
            
            <div class="panel">
                <div class="wizard-nav">
                    <div class="wizard-step active" id="nav-step1">Step 1: Banner & Description</div>
                    <div class="wizard-step" id="nav-step2">Step 2: Time & Event Flow</div>
                    <div class="wizard-step" id="nav-step3">Step 3: Venue Payment & Pricing</div>
                </div>

                <form id="finalizeForm" enctype="multipart/form-data">
                    <input type="hidden" name="event_title" value="<?php echo htmlspecialchars($event['EVENT_TITLE']); ?>">
                    <input type="hidden" id="eventTypeDetector" name="event_type" value="<?php echo htmlspecialchars($event['EVENT_TYPE']); ?>">
                    <input type="hidden" name="venue_fee" value="<?php echo $venue_fee; ?>">

                    <div class="step-container active" id="step1">
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label>Description Summary</label>
                            <textarea name="description" rows="5" required><?php echo htmlspecialchars($event['DESCRIPTION']); ?></textarea>
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label>Upload Promotional Banner</label>
                            <input type="file" name="picture" accept="image/*" required>
                        </div>
                        <div class="btn-wrapper">
                            <button type="button" class="btn-control" style="color:#ec4899; border-color:rgba(236,72,153,0.4); background:rgba(236,72,153,0.1);" onclick="showStep(2)">Next Step →</button>
                        </div>
                    </div>

                    <div class="step-container" id="step2">
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label>Event Start Time</label>
                            <input type="time" name="time" required>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label>Event Classification Access Type <span style="color: #94a3b8; font-size: 12px; font-weight: normal; margin-left: 6px;">(Locked)</span></label>
                            <select disabled>
                                <option value="Free" <?php echo (strtolower($event['EVENT_TYPE'])=='free')?'selected':''; ?>>Free</option>
                                <option value="Paid" <?php echo (strtolower($event['EVENT_TYPE'])=='paid')?'selected':''; ?>>Paid</option>
                                <option value="Private" <?php echo (strtolower($event['EVENT_TYPE'])=='private')?'selected':''; ?>>Private</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label>Event Category <span style="color: #94a3b8; font-size: 12px; font-weight: normal; margin-left: 6px;">(Locked)</span></label>
                            <input type="text" name="category" readonly value="<?php echo htmlspecialchars($event['CATEGORY'] ?? 'General'); ?>">
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label>Event Flow / Timeline Agenda</label>
                            <textarea name="event_flow" rows="5" placeholder="e.g. 08:00 AM - Registrations open..." required></textarea>
                        </div>
                        <div class="btn-wrapper">
                            <button type="button" class="btn-control" onclick="showStep(1)">Back</button>
                            <button type="button" class="btn-control" style="color:#ec4899; border-color:rgba(236,72,153,0.4); background:rgba(236,72,153,0.1);" onclick="showStep(3)">Next Step →</button>
                        </div>
                    </div>

                    <div class="step-container" id="step3">
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label id="priceLabel"><?php echo $is_private_event ? '🔑 Private Access Invite PIN' : 'Ticket Entry Price'; ?> <span style="color: #94a3b8; font-size: 12px; font-weight: normal; margin-left: 6px;">(Locked)</span></label>
                            <input type="text" id="ticketPriceInput" name="ticket_price" 
                                value="<?php echo $default_ticket_price; ?>" 
                                readonly 
                                required>
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label>Maximum Attendance Capacity</label>
                            <input type="number" name="max_participants" placeholder="e.g. 150" min="1" required>
                        </div>

                        <div class="venue-payment-gate">
                            <h3 style="color: #ec4899; margin-top: 0; margin-bottom: 5px;">🔒 Venue Rental Payment Mandatory</h3>
                            <p style="color: #94a3b8; font-size: 13px; margin-bottom: 15px;">
                                You are required to process the venue fee of <strong>RM <?php echo number_format($venue_fee, 2); ?></strong> before hosting authority can publish this live.
                            </p>
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label style="font-size: 12px; color: #94a3b8;">Cardholder Name</label>
                                <input type="text" name="card_name" placeholder="e.g. Ahmad Dani" required>
                            </div>
                            <div class="form-group">
                                <label style="font-size: 12px; color: #94a3b8;">Card / Dummy Receipt Reference Number</label>
                                <input type="text" name="payment_ref" placeholder="e.g. TXN-SMARTVILLE-9981" required>
                            </div>
                        </div>

                        <div class="btn-wrapper">
                            <button type="button" class="btn-control" onclick="showStep(2)">Back</button>
                            <button type="submit" class="btn-control" style="background:#10b981; color:#ffffff; border-color:#10b981;">Pay Venue & Publish Live</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        function showStep(s) {
            document.querySelectorAll('.step-container').forEach(e => e.classList.remove('active'));
            document.querySelectorAll('.wizard-step').forEach(e => e.classList.remove('active'));
            document.getElementById('step' + s).classList.add('active');
            document.getElementById('nav-step' + s).classList.add('active');
        }

        document.getElementById('finalizeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const eventType = document.getElementById('eventTypeDetector').value.trim().toLowerCase();
            const priceInput = document.getElementById('ticketPriceInput');
            
            // 🔥 DIBAIKI: Hanya tukar ke 0.00 jika jenis event adalah free sahaja!
            if (eventType === 'free') {
                priceInput.value = '0.00';
            }

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            
            submitBtn.innerText = 'Processing Payment & Publishing...';
            submitBtn.disabled = true;

            fetch('publish_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === 'success') {
                    window.location.href = 'manage_events.php';
                } else {
                    alert('Server Response Error: ' + data);
                    submitBtn.innerText = 'Pay Venue & Publish Live';
                    submitBtn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Connection failure error.');
                submitBtn.innerText = 'Pay Venue & Publish Live';
                submitBtn.disabled = false;
            });
        });
    </script>
</body>
</html>
<?php 
ob_end_flush(); 
?>