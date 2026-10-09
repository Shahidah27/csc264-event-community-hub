<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Portal Role - SmartVille Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; 
        }
        
        body { 
            background-color: #f8fafc; /* Premium light mode gray backdrop canvas */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* BACKGROUND GLOW ACCENT */
        body::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(0, 102, 204, 0.04) 0%, rgba(248, 250, 252, 0) 70%);
            filter: blur(100px);
            pointer-events: none;
            z-index: 1;
        }

        .header-container {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
            z-index: 2;
        }

        .header-badge {
            display: inline-block;
            background: rgba(0, 102, 204, 0.06);
            border: 1px solid rgba(0, 102, 204, 0.15);
            color: #0066cc;
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .header-container h1 {
            color: #0f172a;
            font-size: 42px;
            font-weight: 700;
            letter-spacing: -1.5px;
            margin-bottom: 12px;
        }

        .header-container p {
            color: #64748b;
            font-size: 16px;
        }

        /* CARDS GRID SYSTEM */
        .cards-grid {
            display: flex; 
            justify-content: center; 
            gap: 30px; 
            flex-wrap: wrap; 
            padding: 0 20px;
            max-width: 1140px;
            width: 100%;
            margin-bottom: 50px;
            position: relative;
            z-index: 2;
        }

        .role-card-link {
            text-decoration: none; 
            color: inherit;
        }

        /* INDIVIDUAL SHINY ROLE CARD */
        .role-card {
            background: #ffffff; 
            width: 300px; 
            padding: 50px 30px; 
            border-radius: 24px; /* Smoother geometric curves */
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.02); 
            text-align: center; 
            border: 1px solid rgba(15, 23, 42, 0.06); 
            position: relative;
            overflow: hidden; /* Vital to clip the sliding shine glint reflection layer */
            transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), 
                        box-shadow 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), 
                        border-color 0.4s ease;
            cursor: pointer;
        }

        /* LIQUID GLASS REFLECTION GLINT SHEEN */
        .role-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -150%;
            width: 50%;
            height: 100%;
            background: linear-gradient(
                90deg, 
                rgba(255, 255, 255, 0) 0%, 
                rgba(255, 255, 255, 0.7) 50%, 
                rgba(255, 255, 255, 0) 100%
            );
            transform: skewX(-25deg);
            pointer-events: none;
        }

        /* HOVER INTERACTIONS */
        .role-card-link:hover .role-card::before {
            left: 150%;
            transition: left 0.8s ease-in-out;
        }

        .role-card-link:hover .role-card {
            transform: translateY(-8px); 
            border-color: rgba(0, 102, 204, 0.3);
            /* Soft electric blue glow */
            box-shadow: 0 20px 40px rgba(0, 102, 204, 0.08),
                        0 0 25px rgba(0, 210, 255, 0.15);
        }

        /* IMAGE MODULE FRAME WITH HOVER TRANSLATIONS */
        .image-frame {
            width: 100px;
            height: 100px;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .image-frame img {
            height: 85px; 
            width: auto;
            object-fit: contain;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .role-card-link:hover .image-frame img {
            transform: scale(1.1) translateY(-4px);
        }

        .role-card h3 {
            color: #0f172a; 
            font-size: 22px; 
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 12px;
        }

        /* REFINED INTERACTIVE BUTTON BADGE */
        .action-tag {
            color: #0066cc; 
            font-weight: 600; 
            font-size: 14px;
            margin-top: 20px;
            display: inline-block;
            padding: 8px 16px;
            background: rgba(0, 102, 204, 0.04);
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .role-card-link:hover .action-tag {
            background: linear-gradient(135deg, #0066cc, #00b4d8);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.2);
            transform: scale(1.03);
        }

        /* BOTTOM RETURN HOME LINK STYLING */
        .back-home-bottom {
            color: #64748b;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 100px;
            background-color: transparent;
            border: 1px solid transparent;
            position: relative;
            z-index: 2;
        }

        .back-home-bottom:hover {
            color: #0066cc;
            background-color: #ffffff;
            border-color: rgba(15, 23, 42, 0.06);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        }
    </style>
</head>
<body>

    <div class="header-container">
        <span class="header-badge">Secure Gateway</span>
        <h1>Welcome to SmartVille Hub</h1>
        <p>Please select your corresponding portal management role to continue</p>
    </div>

    <div class="cards-grid">
        
        <a href="login.php?role=Resident" class="role-card-link">
            <div class="role-card">
                <div class="image-frame">
                    <img src="resident.jpg" alt="Resident Icon">
                </div>
                <h3>Resident</h3>
                <span class="action-tag">Log In To Portal &rarr;</span>
            </div>
        </a>

        <a href="login.php?role=Organizer" class="role-card-link">
            <div class="role-card">
                <div class="image-frame">
                    <img src="organizer.jpg" alt="Organizer Icon">
                </div>
                <h3>Event Organizer</h3>
                <span class="action-tag">Log In To Portal &rarr;</span>
            </div>
        </a>

        <a href="login.php?role=Admin" class="role-card-link">
            <div class="role-card">
                <div class="image-frame">
                    <img src="admin.png" alt="Admin Icon">
                </div>
                <h3>System Admin</h3>
                <span class="action-tag">Log In To Portal &rarr;</span>
            </div>
        </a>

    </div>

    <a href="index.php" class="back-home-bottom">&larr; Return to Main Homepage</a>

</body>
</html>