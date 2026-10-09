<?php
include('db_connect.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $role_input             = trim($_POST['role']);
    $email_input            = trim($_POST['email']);
    $password_input         = $_POST['password'];
    $confirm_password_input = $_POST['confirm_password'];

    // Validation: Match check
    if ($password_input !== $confirm_password_input) {
        header("Location: forgot_password.php?error=mismatch");
        exit();
    }

    if ($role_input === 'Organizer') {
        $table_name   = "login_organizer";
        $email_column = "email_organizer";
        $pass_column  = "password_organizer";
    } else {
        $table_name   = "login_resident";
        $email_column = "email_resident";
        $pass_column  = "password_resident";
    }

    $sql_check = "SELECT * FROM $table_name WHERE $email_column = ?";
    $stmt_check = $conn->prepare($sql_check);
    
    if ($stmt_check) {
        $stmt_check->bind_param("s", $email_input);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            $stmt_check->close();

            $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

            $sql_update = "UPDATE $table_name SET $pass_column = ? WHERE $email_column = ?";
            $stmt_update = $conn->prepare($sql_update);

            if ($stmt_update) {
                $stmt_update->bind_param("ss", $hashed_password, $email_input);
                
                if ($stmt_update->execute()) {
                    header("Location: login.php?role=" . urlencode($role_input) . "&reset=success");
                    exit();
                } else {
                    echo "Database update execution failed: " . $conn->error;
                }
                $stmt_update->close();
            }
        } else {
            header("Location: forgot_password.php?error=notfound");
            exit();
        }
    } else {
        echo "SQL statement preparation failed: " . $conn->error;
    }
}

$conn->close();
?>