<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an Account - SmartVille Hub</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        
        /* BACKDROP IMAGE WITH THE SAME DARK OVERLAY AS INDEX.PHP */
        body { 
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(rgba(15, 32, 67, 0.75), rgba(15, 32, 67, 0.85)), 
                        url('indexbg.jpg') no-repeat center center fixed;
            background-size: cover;
            padding: 20px;
        }

        /* CARD WRAPPER Split Layout */
        .signup-container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            min-height: 600px;
        }

        /* LEFT BLUE PANEL */
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #2b5c8f, #1b3a61);
            color: #ffffff;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .left-panel .brand-logo {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .left-panel .welcome-text h2 {
            font-size: 40px;
            font-weight: 700;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        .left-panel .welcome-text p {
            color: #cbd5e1;
            font-size: 16px;
            line-height: 1.5;
        }
        .left-panel .footer-text {
            color: #94a3b8;
            font-size: 14px;
        }

        /* RIGHT FORM PANEL */
        .right-panel {
            flex: 1.1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }
        .return-home {
            position: absolute;
            top: 30px;
            left: 50px;
            text-decoration: none;
            color: #64748b;
            font-size: 14px;
            transition: color 0.2s;
        }
        .return-home:hover { color: #0066cc; }

        .form-header h1 {
            color: #0f172a;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            margin-top: 20px;
        }
        .form-header p {
            color: #64748b;
            font-size: 15px;
            margin-bottom: 30px;
        }

        /* FORM INPUT FIELDS */
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            color: #334155;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #0066cc;
        }
        .form-group input::placeholder {
            color: #94a3b8;
        }

        /* BUTTON AND TOGGLE LINKS */
        .submit-btn {
            width: 100%;
            padding: 14px;
            background-color: #0066cc;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .submit-btn:hover { background-color: #0052a3; }

        .login-redirect {
            text-align: center;
            font-size: 14px;
            color: #64748b;
        }
        .login-redirect a {
            color: #0066cc;
            text-decoration: none;
            font-weight: 600;
        }
        .login-redirect a:hover { text-decoration: underline; }

        /* RESPONSIVE DESIGN */
        @media (max-width: 768px) {
            .signup-container { flex-direction: column; }
            .left-panel { padding: 40px; gap: 40px; }
            .right-panel { padding: 40px; }
            .return-home { position: static; margin-bottom: 20px; }
        }
    </style>
</head>
<body>

    <div class="signup-container">
        <div class="left-panel">
            <div class="brand-logo">SmartVille</div>
            <div class="welcome-text">
                <h2>Join our network</h2>
                <p>Create an account to start participating in upcoming neighborhood groups, projects, and activities.</p>
            </div>
            <div class="footer-text">&copy; 2026 SmartVille Platform</div>
        </div>

        <div class="right-panel">
            <a href="index.php" class="return-home">&larr; Return Home</a>
            
            <div class="form-header">
                <h1>Create an Account</h1>
                <p>Please fill up your details to sign up.</p>
            </div>

            <form action="signup_process.php" method="POST">
                <div class="form-group">
                    <label for="role">Account Role Type</label>
                    <select id="role" name="role">
                        <option value="Resident">Resident</option>
                        <option value="Organizer">Organizer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a secure password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Retype your password to match" required>
                </div>

                <button type="submit" class="submit-btn">Register Account</button>
            </form>

            <div class="login-redirect">
                Already have an account? <a href="select_role.php">Log In instead</a>
            </div>
        </div>
    </div>

</body>
</html>