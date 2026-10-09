<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('db_connect.php');
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password_action'])) {
    $email = $conn->real_escape_string(trim($_POST['email']));
    $role  = $_POST['role']; // Captures 'Organizer' or 'Resident'
    $new_password = $_POST['new_password']; 

    // 🛠️ DYNAMIC ROUTING MAP: Matches your project schema capitalization structures
    if ($role === 'Organizer') {
        $table = 'login_organizer'; 
        $email_col = 'EMAIL_ORGANIZER'; 
        $pass_col = 'PASSWORD_ORGANIZER';
    } else {
        $table = 'login_resident'; 
        $email_col = 'EMAIL_RESIDENT'; 
        $pass_col = 'PASSWORD_RESIDENT';
    }
	
    // Checking whether the e-mail exists in the system record using corrected column variables
    $check_email = $conn->query("SELECT `$email_col` FROM `$table` WHERE LOWER(`$email_col`) = LOWER('$email')");
    
    if ($check_email && $check_email->num_rows > 0) {
        // Update to the new target password parameter configuration
        $update_pass = $conn->query("UPDATE `$table` SET `$pass_col` = '$new_password' WHERE LOWER(`$email_col`) = LOWER('$email')");
        
        if ($update_pass) {
            // Success go to the login.php
            header("Location: login.php?role=" . urlencode($role) . "&reset=success");
            exit();
        } else {
            $message = "<div style='color:#ef4444; background:rgba(239,68,68,0.1); padding:12px; border-radius:6px; font-size:14px; margin-bottom: 20px;'>⚠️ Update Error: Could not overwrite password string.</div>";
        }
    } else {
        $message = "<div style='color:#ef4444; background:rgba(239,68,68,0.1); padding:12px; border-radius:6px; font-size:14px; margin-bottom: 20px;'>⚠️ Account identity not registered in the system database.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Password Recovery</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { min-height: 100vh; display: flex; justify-content: center; align-items: center; background: linear-gradient(rgba(15, 32, 67, 0.8), rgba(15, 32, 67, 0.9)), url('indexbg.jpg') no-repeat center center fixed; background-size: cover; margin:0; padding:20px; }
        .recovery-box { background: white; padding: 40px; border-radius: 16px; max-width: 450px; width: 100%; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #334155; font-size: 14px; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; font-size: 15px; }
        .reset-btn { width: 100%; padding: 14px; background: #0066cc; border: none; border-radius: 6px; color: white; font-weight: 600; cursor: pointer; font-size: 16px; }
        .reset-btn:hover { background: #0052a3; }
    </style>
</head>
<body>
    <div class="recovery-box">
        <h2 style="margin:0 0 10px 0; color:#0f172a; font-size:26px;">Forgot Password?</h2>
        <p style="margin:0 0 25px 0; color:#64748b; font-size:14px;">Reset your access parameters. Enter your email identity context down below.</p>
        
        <?php echo $message; ?>
        
        <form action="forgot_password.php" method="POST">
            <input type="hidden" name="reset_password_action" value="1">
            
            <div class="form-group">
                <label>Select Portal Role Account Type</label>
                <select name="role" required>
                    <option value="Resident">Resident Portal</option>
                    <option value="Organizer">Event Organizer Portal</option>
                </select>
            </div>

            <div class="form-group">
                <label>Registered Email Address</label>
                <input type="email" name="email" required placeholder="e.g., yourname@smartville.local">
            </div>

            <div class="form-group">
                <label>New Secured Password</label>
                <input type="password" name="new_password" required placeholder="Enter new password code parameters">
            </div>

            <button type="submit" class="reset-btn">🔄 Update Password & Proceed to Login</button>
        </form>
        
        <div style="text-align:center; margin-top:20px; font-size:14px;">
            <a href="select_role.php" style="color:#0066cc; text-decoration:none; font-weight:600;">&larr; Abort & Return Back</a>
        </div>
    </div>
</body>
</html>