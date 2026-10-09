<?php
// Safely capture URL parameter or fallback gracefully to resident portal view
$role = isset($_GET['role']) ? htmlspecialchars($_GET['role']) : 'Resident';

// Ensure the first letter is capitalized to perfectly match database values ('Admin', 'Organizer', 'Resident')
$role = ucfirst(strtolower($role));

$displayRole = ($role === 'Admin') ? 'System Admin' : (($role === 'Organizer') ? 'Event Organizer' : 'Resident');

// Check if an error flag was passed from login_process.php
$hasError = false;
if (isset($_GET['error']) && $_GET['error'] === 'incorrect') {
    $hasError = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - SmartVille Hub</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        
        /* MATCHES THE HOMEPAGE DESIGN STYLE AND DARK OVERLAY LAYER */
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
        
        .split-wrapper { 
            display: flex; 
            background-color: #ffffff; 
            width: 100%; 
            max-width: 1000px; 
            min-height: 550px; 
            border-radius: 16px; 
            overflow: hidden; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.25); 
        }
        
        /* LEFT APP LOGO SHOWCASE PANEL */
        .left-pane {
            flex: 1; 
            background: linear-gradient(135deg, #2b5c8f, #1b3a61);
            padding: 50px; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between; 
            color: #ffffff;
        }
        .brand-logo { font-size: 28px; font-weight: bold; letter-spacing: -0.5px; }
        .intro-text h1 { font-size: 38px; font-weight: 700; margin-bottom: 15px; line-height: 1.2; }
        .intro-text p { font-size: 16px; color: #cbd5e1; line-height: 1.5; }
        .footer-text { font-size: 14px; color: #94a3b8; }

        /* RIGHT SECURITY INTERFACE PANEL */
        .right-pane { 
            flex: 1.1; 
            padding: 50px; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            position: relative;
        }
        .back-link { 
            position: absolute;
            top: 30px;
            left: 50px;
            color: #64748b; 
            text-decoration: none; 
            font-size: 14px; 
            transition: color 0.2s;
        }
        .back-link:hover { color: #0066cc; }

        .form-header { margin-top: 20px; margin-bottom: 30px; }
        .form-header h2 { font-size: 32px; color: #0f172a; font-weight: 700; margin-bottom: 6px; }
        .form-header p { font-size: 15px; color: #64748b; }
        .form-header strong { color: #0066cc; font-weight: 600; }

        .form-group { margin-bottom: 22px; position: relative; }
        .form-group label { display: block; margin-bottom: 8px; color: #334155; font-size: 14px; font-weight: 600; }
        .form-group input { width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 15px; outline: none; transition: border-color 0.2s, box-shadow 0.2s; }
        .form-group input:focus { border-color: #0066cc; }

        /* RED HIGHLIGHT STYLING FOR VALIDATION FAILS */
        .input-error { 
            border-color: #ef4444 !important; 
            background-color: #fef2f2; 
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        /* INLINE ERROR WARNING LABELS UNDER FIELDS */
        .field-error-msg {
            color: #dc2626;
            font-size: 13px;
            font-weight: 500;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* GREEN CONFIRMATION ALERT STYLING FOR SUCCESSFUL PASSWORD UPDATE */
        .success-banner {
            background-color: #f0fdf4;
            border-left: 4px solid #22c55e;
            color: #14532d;
            padding: 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .login-btn { width: 100%; padding: 14px; background-color: #0066cc; color: #ffffff; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; transition: background-color 0.2s; }
        .login-btn:hover { background-color: #0052a3; }
        
        .form-footer { margin-top: 25px; text-align: center; font-size: 14px; color: #64748b; }
        .form-footer p { margin-bottom: 8px; }
        .form-footer a { color: #0066cc; text-decoration: none; font-weight: 600; }
        .form-footer a:hover { text-decoration: underline; }

        /* RESPONSIVE LAYOUT OPTIMIZATION */
        @media (max-width: 768px) {
            .split-wrapper { flex-direction: column; }
            .left-pane, .right-pane { padding: 40px; }
            .back-link { position: static; margin-bottom: 20px; display: inline-block; }
        }
    </style>
</head>
<body>

    <div class="split-wrapper">
        <div class="left-pane">
            <div class="brand-logo">SmartVille</div>
            <div class="intro-text">
                <h1>Welcome Back</h1>
                <p>Log in to access your customized portal options, manage your reservations, and check real-time updates.</p>
            </div>
            <div class="footer-text">&copy; 2026 SmartVille Platform</div>
        </div>

        <div class="right-pane">
            <a href="select_role.php" class="back-link">&larr; Change Account Type</a>
            
            <div class="form-header">
                <h2>Portal Sign In</h2>
                <p>Accessing platform environment as: <strong><?php echo $displayRole; ?></strong></p>
            </div>

            <?php if (isset($_GET['reset']) && $_GET['reset'] === 'success'): ?>
                <div class="success-banner">
                    ✅ Password updated successfully! Please log in below.
                </div>
            <?php endif; ?>

            <form action="login_process.php" method="POST">
                <input type="hidden" name="role" value="<?php echo $role; ?>">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="text" id="email" name="email" required 
                           class="<?php echo $hasError ? 'input-error' : ''; ?>" 
                           placeholder="Enter your registered identifier">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           class="<?php echo $hasError ? 'input-error' : ''; ?>" 
                           placeholder="Enter your password">
                    
                    <?php if ($hasError): ?>
                        <div class="field-error-msg">
                            ⚠️ Invalid username or password. Please try again.
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="login-btn">Log In to Portal</button>
            </form>

            <div class="form-footer">
                <?php if ($role !== 'Admin'): ?>
                    <p><a href="forgot_password.php">Forgot Password?</a></p>
                    <p style="margin-top: 12px;">Don't have an account? <a href="signup.php">Sign Up instead</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>