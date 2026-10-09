<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('db_connect.php');

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$session_email = trim($_SESSION['user_email']);
$session_role  = $_SESSION['role'] ?? 'Resident'; 
$message = "";

// 🛠️ Tetapkan nama table dan column tepat mengikut UPPERCASE struktur database anda
$target_table = "profile_resident"; 
$email_col    = "EMAIL_RESIDENT"; // Sesuai dengan skema database baru anda

if ($session_role === 'Admin') {
    $target_table = "profile_admin";
    $email_col    = "EMAIL_ADMIN"; 
} elseif ($session_role === 'Organizer') {
    $target_table = "profile_organizer";
    $email_col    = "EMAIL_ORGANIZER"; 
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile_action'])) {
    $full_name = $conn->real_escape_string(trim($_POST['full_name']));
    $phone_num = $conn->real_escape_string(trim($_POST['phone_num']));
    $address   = $conn->real_escape_string(trim($_POST['address']));

    $imgData = null;
    $has_new_image = false;
    if (isset($_FILES['profile_pic_file']) && $_FILES['profile_pic_file']['error'] == 0) {
        $imgData = file_get_contents($_FILES['profile_pic_file']['tmp_name']);
        $has_new_image = true;
    }

    // 🔍 1. Semak rekod sedia ada mengikut nama kolum yang betul
    $check = $conn->prepare("SELECT `$email_col` FROM `$target_table` WHERE LOWER(`$email_col`) = LOWER(?)");
    $check->bind_param("s", $session_email);
    $check->execute();
    $check_result = $check->get_result();
    $existing_row = $check_result->fetch_assoc();
    $check->close();

    if ($existing_row) {
        // 🔄 2. Operasi UPDATE
        if (!$has_new_image) {
            // Jika tiada gambar baru, kekalkan gambar lama tanpa merosakkan BLOB
            $stmt = $conn->prepare("UPDATE `$target_table` SET FULL_NAME = ?, PHONE_NUM = ?, ADDRESS = ? WHERE LOWER(`$email_col`) = LOWER(?)");
            $stmt->bind_param("ssss", $full_name, $phone_num, $address, $session_email);
        } else {
            // Menghantar data BLOB secara terus menggunakan jenis parameter 's' (lebih stabil merentasi konfigurasi server)
            $stmt = $conn->prepare("UPDATE `$target_table` SET FULL_NAME = ?, PHONE_NUM = ?, ADDRESS = ?, PROFILE_PIC = ? WHERE LOWER(`$email_col`) = LOWER(?)");
            $stmt->bind_param("sssss", $full_name, $phone_num, $address, $imgData, $session_email);
        }
    } else {
        // ➕ 3. Operasi INSERT jika profil belum wujud
        if (!$has_new_image) {
            $stmt = $conn->prepare("INSERT INTO `$target_table` (`$email_col`, FULL_NAME, PHONE_NUM, ADDRESS) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $session_email, $full_name, $phone_num, $address);
        } else {
            $stmt = $conn->prepare("INSERT INTO `$target_table` (`$email_col`, FULL_NAME, PHONE_NUM, ADDRESS, PROFILE_PIC) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $session_email, $full_name, $phone_num, $address, $imgData);
        }
    }

    if ($stmt->execute()) {
        $_SESSION['fullname'] = $full_name;
        $message = "<div style='background: #10b981; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight:500;'>✨ Profile updated successfully!</div>";
    } else {
        $message = "<div style='background: #dc2626; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight:500;'>⚠️ Error updating profile data: " . $conn->error . "</div>";
    }
    $stmt->close();
}

// 📥 4. Ambil data terkini untuk paparan UI menggunakan kolum UPPERCASE
$fetch = $conn->prepare("SELECT * FROM `$target_table` WHERE LOWER(`$email_col`) = LOWER(?)");
$fetch->bind_param("s", $session_email);
$fetch->execute();
$profile_data = $fetch->get_result()->fetch_assoc();
$fetch->close();

// Menggunakan array key berasaskan huruf besar sepenuhnya mengikut data skema anda
$name_val    = $profile_data['FULL_NAME'] ?? ($_SESSION['fullname'] ?? '');
$phone_val   = $profile_data['PHONE_NUM'] ?? '';
$address_val = $profile_data['ADDRESS'] ?? '';
$pic_blob    = $profile_data['PROFILE_PIC'] ?? null;

$image_src = ""; 
if ($pic_blob) {
    if (substr($pic_blob, 0, 4) === 'http' || strlen($pic_blob) < 255) {
        $image_src = $pic_blob;
    } else {
        // Guna alternatif getimagesizefromstring yang tidak memerlukan extension finfo diaktifkan
        $image_info = @getimagesizefromstring($pic_blob);
        $mime_type = isset($image_info['mime']) ? $image_info['mime'] : 'image/jpeg';
        
        $image_src = 'data:' . $mime_type . ';base64,' . base64_encode($pic_blob);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Profile Center - SmartVille</title>
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
            --cosmic-glow: rgba(236, 72, 153, 0.25);
            --cosmic-border: rgba(236, 72, 153, 0.4);
            --base-font-size: 16px;
            --base-font-family: 'Segoe UI', sans-serif;
        }

        html { font-size: var(--base-font-size) !important; font-family: var(--base-font-family) !important; }
        body, nav, .sidebar, .main-content, h1, h2, h3, span, a, p, label, input, textarea, button { font-family: var(--base-font-family) !important; }

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

        .content-container { padding: 0px 40px 40px 40px; }
        
        .page-title, .section-title, .user-display-name { color: #ffffff; margin: 0; transition: color 0.3s ease; }
        .sub-text { color: #71717a; font-size: 14px; margin: 0; transition: color 0.3s ease; }

        .profile-badge-header { 
            display: flex; 
            align-items: center; 
            gap: 20px; 
            margin-bottom: 35px; 
            padding-bottom: 25px; 
            border-bottom: 1px solid rgba(255,255,255,0.08); 
            transition: border-color 0.3s;
        }

        .avatar-circle { 
            width: 85px; 
            height: 85px; 
            background: #ec4899; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 30px; 
            font-weight: bold; 
            color: white; 
            text-transform: uppercase; 
            overflow: hidden; 
            border: 2px solid rgba(255,255,255,0.1);
            box-shadow: 0 0 15px rgba(236, 72, 153, 0.2);
        }
        .avatar-circle img { width: 100%; height: 100%; object-fit: cover; }

        .info-display-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px; }

        .panel { background: transparent; }
        .info-card { 
            background: #0f0f1a; 
            padding: 22px; 
            border-radius: 14px; 
            border: 1px solid rgba(255,255,255,0.08); 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .info-card:hover {
            transform: translateY(-2px);
            border-color: var(--cosmic-border);
            box-shadow: 0 0 15px var(--cosmic-glow);
        }

        .info-card small { 
            display: block; 
            color: #71717a; 
            font-size: 11px; 
            margin-bottom: 8px; 
            font-weight: 600; 
            text-transform: uppercase; 
            letter-spacing: 0.8px; 
            transition: color 0.3s;
        }
        .info-card p { font-size: 16px; color: #ffffff; font-weight: 500; word-break: break-all; margin: 0; transition: color 0.3s; }

        .edit-form-wrap { display: none; }
        
        .form-label { display: block; margin-bottom: 8px; color: #ffffff; font-weight: 500; transition: color 0.3s; }
        .form-group input[type="text"], .form-group textarea, .form-group input[type="file"] {
            width: 100%; 
            padding: 12px; 
            background: #1c1c34; 
            border: 1px solid rgba(255,255,255,0.08); 
            border-radius: 8px; 
            color: #ffffff;
            box-sizing: border-box;
            outline: none;
            transition: background 0.3s, color 0.3s, border-color 0.3s;
        }
        .form-group input[readonly] {
            opacity: 0.5; 
            cursor: not-allowed; 
            background: rgba(255,255,255,0.02);
        }

        .btn-primary {
            background: linear-gradient(135deg, #a855f7, #ec4899);
            color: white; border: none; padding: 12px 24px; border-radius: 10px; cursor: pointer; font-weight: 600;
        }
        .btn-secondary-cancel { 
            background: #3f3f46; color: white; padding: 12px 24px; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; margin-right: 10px; 
            transition: background 0.3s, color 0.3s;
        }
        
        @media (max-width: 992px) { 
            .info-display-grid { grid-template-columns: 1fr; } 
            .main-content, body.sidebar-hidden .main-content { margin-left: 0 !important; }
            body:not(.sidebar-hidden) #sidebar { transform: translateX(-240px) !important; }
            body.sidebar-hidden #sidebar { transform: translateX(0) !important; }
        }

        html.light-mode { background-color: #f4f4f7 !important; }
        html.light-mode body, html.light-mode .main-content { background: #f4f4f7 !important; color: #18181b !important; }
        html.light-mode .page-title, html.light-mode .section-title, html.light-mode .user-display-name { color: #18181b !important; }
        html.light-mode .sub-text { color: #52525b !important; }
        html.light-mode .profile-badge-header { border-bottom-color: rgba(0,0,0,0.1); }
        
        html.light-mode .info-card { 
            background: #ffffff !important; 
            border-color: rgba(0,0,0,0.08) !important; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); 
        }
        html.light-mode .info-card small { color: #6b7280 !important; }
        html.light-mode .info-card p { color: #18181b !important; }
        html.light-mode .info-card:hover { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border-color: rgba(0,0,0,0.15) !important; }
        
        html.light-mode .form-label { color: #18181b !important; }
        html.light-mode .form-group input[type="text"], 
        html.light-mode .form-group textarea,
        html.light-mode .form-group input[type="file"] {
            background: #ffffff !important;
            color: #18181b !important;
            border: 1px solid rgba(0, 0, 0, 0.15) !important;
        }
        html.light-mode .form-group input[readonly] {
            background: #f3f4f6 !important;
            color: #6b7280 !important;
        }
        html.light-mode .btn-secondary-cancel {
            background: #e4e4e7 !important;
            color: #18181b !important;
        }
        html.light-mode button[onclick="toggleSidebar()"] {
            background: #e4e4e7 !important;
            color: #18181b !important;
            border: 1px solid rgba(0,0,0,0.15) !important;
        }
    </style>
</head>
<body>

    <?php 
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
        include('admin_sidebar_helper.php'); 
    } elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'Resident') {
        include('resident_sidebar_helper.php'); 
    } else {
        include('header.php'); 
    }
    ?>

    <main class="main-content" id="main-content">
        <div class="content-container" style="padding: 0;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0px;">
                <h1 class="page-title" style="margin: 0; font-size: 32px; font-weight: bold; line-height: 1.2;">Account Center</h1>
                <button type="button" onclick="toggleSidebar()" style="background: #1c1c34; border: 1px solid rgba(255,255,255,0.1); color: white; padding: 8px 16px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; height: 38px; box-sizing: border-box;">
                    &#9776; Toggle Menu
                </button>
            </div>

            <div style="margin-top: 30px;">
                <?php echo $message; ?>
            </div>

            <div id="profile-view-state" class="panel">
                <div class="profile-badge-header">
                    <div class="avatar-circle">
                        <?php if (!empty($image_src)): ?>
                            <img src="<?php echo $image_src; ?>" alt="Profile Picture">
                        <?php else: ?>
                            <?php echo !empty($name_val) ? substr(htmlspecialchars($name_val), 0, 1) : '?'; ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h2 class="user-display-name" style="font-size:22px; font-weight: 600; margin-bottom: 4px;">
                            <?php echo !empty($name_val) ? htmlspecialchars($name_val) : 'SmartVille Member'; ?>
                        </h2>
                        <p class="sub-text">Registered Account Profile Details (<?php echo htmlspecialchars($session_role); ?>)</p>
                    </div>
                </div>

                <div class="info-display-grid">
                    <div class="info-card">
                        <small>Email Address (Account ID)</small>
                        <p><?php echo htmlspecialchars($session_email); ?></p>
                    </div>
                    <div class="info-card">
                        <small>Full Name</small>
                        <p><?php echo !empty($name_val) ? htmlspecialchars($name_val) : '<em>Not provided yet</em>'; ?></p>
                    </div>
                    <div class="info-card">
                        <small>Phone Contact Number</small>
                        <p><?php echo !empty($phone_val) ? htmlspecialchars($phone_val) : '<em>Not provided yet</em>'; ?></p>
                    </div>
                    <div class="info-card">
                        <small>Physical Address Location</small>
                        <p>
                            <?php 
                            if (!empty($address_val)) {
                                $clean_address = str_replace(array('\r\n', '\n'), "\n", $address_val);
                                echo nl2br(htmlspecialchars($clean_address));
                            } else {
                                echo '<em>Not provided yet</em>';
                            }
                            ?>
                        </p>
                    </div>
                </div>

                <button type="button" class="btn-primary" onclick="toggleEditMode(true)">✏️ Update Profile</button>
            </div>

            <div id="profile-edit-state" class="panel edit-form-wrap">
                <h2 class="section-title" style="margin-bottom: 20px; font-weight: 600;">Modify Profile Information</h2>
                
                <form action="profile.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="update_profile_action" value="1">

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Upload Profile Picture</label>
                        <input type="file" name="profile_pic_file" accept="image/*">
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Email Address</label>
                        <input type="text" value="<?php echo htmlspecialchars($session_email); ?>" readonly>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label" for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($name_val); ?>" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label" for="phone_num">Phone Number</label>
                        <input type="text" id="phone_num" name="phone_num" value="<?php echo htmlspecialchars($phone_val); ?>" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label" for="address">Residential Address</label>
                        <textarea id="address" name="address" rows="4" required><?php echo htmlspecialchars($address_val); ?></textarea>
                    </div>

                    <div style="text-align: right; margin-top: 25px;">
                        <button type="button" class="btn-secondary-cancel" onclick="toggleEditMode(false)">Cancel</button>
                        <button type="submit" class="btn-primary">Save Profile Changes</button>
                    </div>
                </form>
            </div>

        </div>
    </main>

    <script>
    function toggleEditMode(showEdit) {
        const viewState = document.getElementById('profile-view-state');
        const editState = document.getElementById('profile-edit-state');
        if(showEdit) {
            viewState.style.display = 'none';
            editState.style.display = 'block';
        } else {
            viewState.style.display = 'block';
            editState.style.display = 'none';
        }
    }
    
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

        if (typeof window.refreshGlobalTypography === 'function') window.refreshGlobalTypography();
    });
    </script>
</body>
</html>