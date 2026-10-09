<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('db_connect.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($conn->real_escape_string($_POST['email']));
    $password = $_POST['password'];
    $role     = $conn->real_escape_string($_POST['role']); // 'Admin', 'Organizer' or 'Resident'

	//Make sure the first letter change to capital letter for matching accurancy
    $role = ucfirst(strtolower($role));

    if ($role === 'Admin') {
        $target_table = 'login_admin';
        $email_col    = 'EMAIL_ADMIN';
        $password_col = 'PASSWORD_ADMIN';
    } elseif ($role === 'Organizer') {
        $target_table = 'login_organizer';
        $email_col    = 'EMAIL_ORGANIZER';
        $password_col = 'PASSWORD_ORGANIZER';
    } else {
        $target_table = 'login_resident';
        $email_col    = 'EMAIL_RESIDENT';
        $password_col = 'PASSWORD_RESIDENT';
    }
	
	//Search for user based in e-mel first 
    $query = "SELECT * FROM `$target_table` WHERE `$email_col` = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Database login query compilation failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $db_password = $user[$password_col];
		
		//Checking password 
        $password_valid = false;
        if (password_verify($password, $db_password)) {
            $password_valid = true; 
        } else if ($password === $db_password) {
            $password_valid = true; 
        }

        if ($password_valid) {
            $_SESSION['user_email'] = $user[$email_col];
            $_SESSION['role']       = $role;
            
			//ID mapping
            $_SESSION['user_id']    = $user['id'] ?? $user['id_resident'] ?? $user['resident_id'] ?? 1;
            
			//Take fullname, if not, system will cut the e-mail before @ as temporary name
            if (!empty($user['fullname'])) {
                $_SESSION['fullname'] = $user['fullname'];
            } elseif (!empty($user['name_resident'])) {
                $_SESSION['fullname'] = $user['name_resident'];
            } else {
                $_SESSION['fullname'] = ucwords(strstr($user[$email_col], '@', true));
            }
            
			//Go to user interface dashboard
            if ($role === 'Admin') {
                header("Location: admin_dashboard.php");
            } elseif ($role === 'Organizer') {
                header("Location: organizer_dashboard.php");
            } else {
                header("Location: browse_events.php"); 
            }
            exit();
        }
    }
    
	//if e-mail not found or wrong password, go back to the login interface
    header("Location: login.php?role=" . strtolower($role) . "&error=incorrect");
    exit();

} else {
    header("Location: select_role.php");
    exit();
}
?>