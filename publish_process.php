<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('db_connect.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $title = isset($_POST['event_title']) ? trim($_POST['event_title']) : '';
    $desc  = isset($_POST['description']) ? trim($_POST['description']) : '';
    $time  = isset($_POST['time']) ? trim($_POST['time']) : '00:00:00';
    $type  = isset($_POST['event_type']) ? trim($_POST['event_type']) : 'Paid';
    $flow  = isset($_POST['event_flow']) ? trim($_POST['event_flow']) : '';
    
    // 🔥 MENGGUNAKAN STRING MUTLAK: Kekalkan string text "AYAMMM"
    $price = isset($_POST['ticket_price']) ? strval(trim($_POST['ticket_price'])) : '0.00'; 
    
    $max_pax   = isset($_POST['max_participants']) ? intval($_POST['max_participants']) : 0;
    $venue_fee = isset($_POST['venue_fee']) ? floatval($_POST['venue_fee']) : 150.00;

    // Pengurusan fail gambar banner
    $pic_path = "";
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] == 0) {
        $dir = "uploads/";
        if (!file_exists($dir)) { 
            mkdir($dir, 0777, true); 
        }
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($_FILES["picture"]["tmp_name"]);
        
        if (in_array($file_type, $allowed_types)) {
            $pic_path = $dir . time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "_", basename($_FILES["picture"]["name"]));
            move_uploaded_file($_FILES["picture"]["tmp_name"], $pic_path);
        }
    }

    if (!empty($pic_path)) {
        $sql = "UPDATE `EVENT` SET 
                `DESCRIPTION` = ?, 
                `TIME` = ?, 
                `EVENT_TYPE` = ?, 
                `EVENT_FLOW` = ?, 
                `TICKET_PRICE` = ?, 
                `MAX_PARTICIPANTS` = ?,
                `VENUE_FEE` = ?,
                `PICTURE` = ?,
                `PUBLISH_STATUS` = 'Published',
                `APPROVAL_STATUS` = 'Published' 
                WHERE `EVENT_TITLE` = ?";
        $stmt = $conn->prepare($sql);
        
        $types = "sssssidss";
        $stmt->bind_param($types, $desc, $time, $type, $flow, $price, $max_pax, $venue_fee, $pic_path, $title);
    } else {
        $sql = "UPDATE `EVENT` SET 
                `DESCRIPTION` = ?, 
                `TIME` = ?, 
                `EVENT_TYPE` = ?, 
                `EVENT_FLOW` = ?, 
                `TICKET_PRICE` = ?, 
                `MAX_PARTICIPANTS` = ?,
                `VENUE_FEE` = ?,
                `PUBLISH_STATUS` = 'Published',
                `APPROVAL_STATUS` = 'Published' 
                WHERE `EVENT_TITLE` = ?";
        $stmt = $conn->prepare($sql);
        
        $types = "sssssids";
        $stmt->bind_param($types, $desc, $time, $type, $flow, $price, $max_pax, $venue_fee, $title);
    }

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Database Update Error: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
    exit();
} else {
    echo "Invalid request method.";
    exit();
}
?>