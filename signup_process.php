<?php
session_start();

include('db_connect.php'); 

$email            = isset($_POST['email']) ? trim($_POST['email']) : '';
$role             = isset($_POST['role']) ? trim($_POST['role']) : 'Resident';
$password         = isset($_POST['password']) ? $_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// Basic input verification check
if (empty($email) || empty($password) || empty($confirm_password)) {
    die("<script>alert('Error: Please fill in all fields completely.'); window.history.back();</script>");
}
if ($password !== $confirm_password) {
    die("<script>alert('Error: Passwords do not match. Please retype carefully.'); window.history.back();</script>");
}

// DYNAMIC SCHEMA MAPPING
// Assigns table and column mappings dynamically based on the form dropdown selection
if ($role === 'Organizer') {
    $target_table = 'login_organizer';
    $email_col    = 'email_organizer';
    $password_col = 'password_organizer';
} else {
    // Defaulting to resident structure setup
    $target_table = 'login_resident';
    $email_col    = 'email_resident';
    $password_col = 'password_resident';
}

// 2. DUPLICATION CHECK: Queries specific table and prefix columns
$check_query = "SELECT `$email_col` FROM `$target_table` WHERE `$email_col` = ?";
$check = $conn->prepare($check_query);
if (!$check) {
    die("Database check compilation failed: " . $conn->error);
}

$check->bind_param("s", $email); 
$check->execute(); 
$check->store_result();

if ($check->num_rows > 0) { 
    die("<script>alert('Error: An account with this email address already exists!'); window.history.back();</script>"); 
}
$check->close();

// 3. INSERTION ACTION: Adds the clean record fields safely into XAMPP phpMyAdmin
$insert_query = "INSERT INTO `$target_table` (`$email_col`, `$password_col`) VALUES (?, ?)";
$insert = $conn->prepare($insert_query);
if (!$insert) {
    die("Database insertion compilation failed: " . $conn->error);
}

$insert->bind_param("ss", $email, $password);

if ($insert->execute()) {
    // 4. Fixed to redirect directly back to index.php upon success
    echo "<script>alert('Registration successful as an " . htmlspecialchars($role) . "!'); window.location.href = 'index.php';</script>";
} else {
    echo "Error processing database registration: " . $insert->error;
}

$insert->close(); 
$conn->close();
?>