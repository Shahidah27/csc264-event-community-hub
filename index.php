<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartVille Hub - Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; 
        }
        
        body {
            background-color: #06060c;
            overflow-x: hidden;
        }
        
        .hero-section {
            height: 100vh; 
            display: flex; 
            align-items: center; 
            padding: 0 8%; 
            position: relative;
            /* Deep modern space gradient mixed with your background image asset */
            background: linear-gradient(135deg, rgba(6, 6, 12, 0.92) 40%, rgba(15, 15, 26, 0.75) 100%), 
                        url('indexbg.jpg') no-repeat center center;
            background-size: cover;
        }

        /* Ambient Glowing Background Accents */
        .hero-section::before {
            content: '';
            position: absolute;
            top: 20%;
            right: 10%;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.15) 0%, rgba(168, 85, 247, 0) 70%);
            filter: blur(40px);
            pointer-events: none;
            z-index: 1;
        }

        .hero-content { 
            max-width: 800px; 
            position: relative;
            z-index: 2;
        }
        
        .community-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 8px 16px;
            border-radius: 100px;
            color: #ec4899;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 24px;
            backdrop-filter: blur(10px);
        }
        
        .hero-content h1 { 
            font-size: 68px; 
            font-weight: 700; 
            line-height: 1.12; 
            margin-bottom: 24px; 
            letter-spacing: -2px;
            color: #ffffff;
        }

        .hero-content h1 span {
            background: linear-gradient(135deg, #a855f7, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 19px;
            color: #a1a1aa;
            line-height: 1.6;
            margin-bottom: 40px;
            max-width: 600px;
        }
        
        .btn-container { 
            display: flex; 
            gap: 16px; 
            align-items: center;
        }
        
        .btn { 
            display: inline-flex; 
            align-items: center;
            justify-content: center;
            padding: 16px 36px; 
            font-size: 16px; 
            font-weight: 600; 
            text-decoration: none; 
            border-radius: 14px; /* More modern geometric rounded corner look */
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); 
            height: 56px;
        }

        /* Pink/Purple dashboard matching gradient button style */
        .btn-primary { 
            background: linear-gradient(135deg, #a855f7, #ec4899); 
            color: #ffffff; 
            box-shadow: 0 4px 20px rgba(236, 72, 153, 0.25);
        }
        .btn-primary:hover { 
            opacity: 0.95;
            transform: translateY(-2px); 
            box-shadow: 0 6px 24px rgba(236, 72, 153, 0.35);
        }

        /* Frosted Glass Secondary Button Interaction */
        .btn-secondary { 
            background-color: rgba(255, 255, 255, 0.03); 
            color: #ffffff; 
            border: 1px solid rgba(255, 255, 255, 0.12); 
            backdrop-filter: blur(8px);
        }
        .btn-secondary:hover { 
            background-color: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px); 
        }

        /* Responsive Breakpoints */
        @media (max-width: 768px) {
            .hero-content h1 { font-size: 46px; letter-spacing: -1px; }
            .hero-subtitle { font-size: 16px; }
            .btn-container { flex-direction: column; width: 100%; gap: 12px; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <section class="hero-section">
        <div class="hero-content">
            <div class="community-badge">✨ Next-Gen Living Experience</div>
            <h1>Your Trusted <span>Smart Community</span> Partner</h1>
            
            <p class="hero-subtitle">
                Streamline venue bookings, submit direct feedback, and access digital municipal features seamlessly from your unified residential portal.
            </p>

            <div class="btn-container">
                <a href="select_role.php" class="btn btn-primary">Log In To Portal</a>
                <a href="signup.php" class="btn btn-secondary">Create Account</a>
            </div>
        </div>
    </section>
    
</body>
</html>