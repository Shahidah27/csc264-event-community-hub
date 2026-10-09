<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platform Features - SmartVille Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; 
        }
        
        body { 
            background: linear-gradient(180deg, #f0f5fa 0%, #e6eff8 100%); /* Premium soft blueish hue background */
            background-attachment: fixed;
            color: #0f172a; 
            overflow-x: hidden;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }
        
        /* CONTAINER LAYOUT WITH FLOATING BACKGROUND BLOB EFFECTS */
        .features-container { 
            max-width: 1200px; 
            margin: 80px auto; 
            padding: 0 40px; 
            position: relative;
        }

        /* BACKGROUND GLOW ACCENT TO MAKE CARDS POP SHINY */
        .features-container::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(0, 102, 204, 0.06) 0%, transparent 70__);
            filter: blur(80px);
            pointer-events: none;
            z-index: 1;
        }
        
        /* HEADER STYLING */
        .section-header {
            text-align: center;
            margin-bottom: 60px;
            position: relative;
            z-index: 2;
        }

        .section-badge {
            display: inline-block;
            background: rgba(0, 102, 204, 0.08);
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

        .section-title { 
            color: #0f172a; 
            font-size: 42px; 
            font-weight: 700; 
            letter-spacing: -1.5px;
        }
        
        .section-title::after { 
            content: ''; 
            display: block; 
            width: 50px; 
            height: 4px; 
            background: linear-gradient(90deg, #0066cc, #00d2ff); 
            margin: 16px auto 0; 
            border-radius: 2px;
        }
        
        /* SHINY DYNAMIC GRID SYSTEMS */
        .features-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); 
            gap: 30px; 
            position: relative;
            z-index: 2;
        }
        
        /* THE SHINY GLASS FEATURE CARD */
        .feature-card { 
            background: #ffffff; /* Solid white contrasting against the soft blue layout */
            padding: 45px 35px; 
            border-radius: 24px; 
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 10px 30px -5px rgba(15, 32, 66, 0.04);
            position: relative;
            overflow: hidden; /* Vital to clip the custom sheen shine layers */
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), 
                        box-shadow 0.4s cubic-bezier(0.16, 1, 0.3, 1),
                        border-color 0.4s ease;
        }
        
        /* FIXED THE "SHINY" GLASS REFLECTION HOVER ANIMATION EFFECT syntax error */
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -150%;
            width: 50%;
            height: 100%;
            background: linear-gradient(
                90deg, 
                rgba(255, 255, 255, 0) 0%, 
                rgba(255, 255, 255, 0.6) 50%, 
                rgba(255, 255, 255, 0) 100%
            );
            transform: skewX(-25deg);
            transition: none;
            pointer-events: none;
        }

        .feature-card:hover::before {
            left: 150%;
            transition: left 0.7s ease-in-out;
        }

        .feature-card:hover { 
            transform: translateY(-8px); 
            border-color: rgba(0, 102, 204, 0.25);
            box-shadow: 0 25px 50px -10px rgba(0, 102, 204, 0.12); 
        }
        
        /* CUSTOM METRIC GLOWING ICON FLOATS */
        .feature-icon-wrapper {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(0, 102, 204, 0.06), rgba(0, 210, 255, 0.06));
            border: 1px solid rgba(0, 102, 204, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 24px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .feature-card:hover .feature-icon-wrapper {
            transform: scale(1.1) rotate(3deg);
            background: linear-gradient(135deg, #0066cc, #00d2ff);
            color: #ffffff !important;
            border-color: transparent;
            box-shadow: 0 8px 20px rgba(0, 102, 204, 0.2);
        }

        .feature-card h3 { 
            color: #0f172a; 
            font-size: 22px; 
            margin-bottom: 14px; 
            font-weight: 700; 
            letter-spacing: -0.5px;
        }
        
        .feature-card p { 
            color: #475569; 
            font-size: 15px; 
            line-height: 1.75; 
        }

        /* MOBILE RESPONSIVE OPTIMIZATION */
        @media (max-width: 768px) {
            .features-container { padding: 0 20px; margin: 40px auto; }
            .section-title { font-size: 32px; }
            .features-grid { grid-template-columns: 1fr; gap: 20px; }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="features-container">
        
        <div class="section-header">
            <span class="section-badge">Capabilities</span>
            <h2 class="section-title">Key Platform Features</h2>
        </div>

        <div class="features-grid">
            
            <div class="feature-card">
                <div class="feature-icon-wrapper" style="color: #0066cc;">🏢</div>
                <h3>Facility Booking</h3>
                <p>Easily browse, select, and book community halls, sports courts, and shared spaces instantly with real-time availability checks.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon-wrapper" style="color: #00b4d8;">🔑</div>
                <h3>Resident Registration</h3>
                <p>Quick and structured profile management onboarding for residents to access localized neighborhood verification services safely.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon-wrapper" style="color: #4cc9f0;">📊</div>
                <h3>Organizer Dashboards</h3>
                <p>Dedicated tracking workspace tool built for neighborhood event organizers to coordinate community events and view active attendee approvals.</p>
            </div>
            
        </div>
    </div>

</body>
</html>