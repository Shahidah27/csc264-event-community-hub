<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - SmartVille Hub</title>
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
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }
        
        /* PREMIUM HEADER WITH GLOWING ACCENT TEXT */
        .contact-header {
            padding: 120px 8%;
            text-align: center; 
            color: #ffffff;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.93) 0%, rgba(30, 41, 59, 0.85) 100%), 
                        url('indexbg.jpg') no-repeat center center;
            background-size: cover;
            position: relative;
        }

        .section-badge {
            display: inline-block;
            background: rgba(0, 210, 255, 0.1);
            border: 1px solid rgba(0, 210, 255, 0.3);
            color: #00d2ff;
            /* Subtly glowing text badge */
            text-shadow: 0 0 10px rgba(0, 210, 255, 0.5);
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 16px;
            backdrop-filter: blur(4px);
        }
        
        .contact-header h1 { 
            font-size: 48px; 
            letter-spacing: -1.5px; 
            margin-bottom: 20px; 
            font-weight: 700;
        }
        
        .contact-header p { 
            font-size: 18px; 
            color: #94a3b8; 
            max-width: 650px; 
            margin: 0 auto; 
            line-height: 1.7; 
        }
        
        /* SHINY AND GLOWING CONTACT CARDS GRID */
        .contact-grid { 
            display: flex; 
            justify-content: center; 
            gap: 30px; 
            padding: 80px 8%; 
            flex-wrap: wrap; 
            position: relative;
            z-index: 2;
        }
        
        .contact-card { 
            flex: 1; 
            min-width: 290px; 
            max-width: 360px; 
            text-align: center; 
            padding: 45px 30px; 
            background: #ffffff; /* Solid pure white for contrast */
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 10px 30px -5px rgba(15, 32, 66, 0.04);
            position: relative;
            overflow: hidden; /* Clips the dynamic sliding sheen flash */
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), 
                        box-shadow 0.4s cubic-bezier(0.16, 1, 0.3, 1),
                        border-color 0.4s ease;
        }

        /* THE "SHINY" LIQUID CHROME GLINT REFLECTION EFFECT */
        .contact-card::before {
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

        /* TRIGGER THE SHINE RUNTIME ON HOVER */
        .contact-card:hover::before {
            left: 150%;
            transition: left 0.8s ease-in-out;
        }

        /* HOVER: CARD TRANSITIONS INTO NEON GLOW CHASSIS */
        .contact-card:hover {
            transform: translateY(-8px);
            border-color: rgba(0, 102, 204, 0.3);
            /* Rich tech-blue ambient neon glow projection */
            box-shadow: 0 25px 50px -10px rgba(0, 102, 204, 0.15), 
                        0 0 25px rgba(0, 210, 255, 0.15);
        }
        
        /* SHINY FLOATING ICON SPHERE WITH ELECTRIC TINT BACKGROUNDS */
        .icon-box {
            width: 68px;
            height: 68px;
            background: linear-gradient(135deg, rgba(0, 102, 204, 0.05), rgba(0, 210, 255, 0.05));
            border: 1px solid rgba(0, 102, 204, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 28px;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 10px rgba(0, 102, 204, 0.02);
        }

        /* HOVER: ICON SPHERE TURNS INTO ELECTRIC ENERGY SOURCE */
        .contact-card:hover .icon-box {
            transform: scale(1.12) translateY(-2px);
            background: linear-gradient(135deg, #0066cc, #00d2ff);
            border-color: transparent;
            box-shadow: 0 8px 20px rgba(0, 102, 204, 0.25),
                        0 0 12px rgba(0, 210, 255, 0.3);
            text-shadow: 0 0 0 white; /* Ensures standard emoji characters blend cleanly */
        }
        
        .contact-card h3 { 
            color: #0f172a; 
            font-size: 16px; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            margin-bottom: 12px; 
            font-weight: 700; 
        }
        
        .contact-card p { 
            color: #64748b; 
            font-size: 14px; 
            line-height: 1.6; 
            margin-bottom: 24px; 
        }
        
        /* HOVER INTERACTION FOR SHINY INTERACTIVE PILL BUTTONS */
        .contact-value { 
            color: #0066cc; 
            font-weight: 600; 
            font-size: 15px; 
            text-decoration: none; 
            display: inline-block;
            padding: 10px 20px;
            background: rgba(0, 102, 204, 0.05);
            border: 1px solid rgba(0, 102, 204, 0.05);
            border-radius: 14px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* BUTTON SHINE GLOW FLIP ON INTERACTION OVER LINK CAPABLE OBJECTS */
        a.contact-value:hover, span.contact-value {
            /* Kept span stable while links receive full hover glow */
        }
        
        a.contact-value:hover {
            background: linear-gradient(135deg, #0066cc, #00b4d8);
            border-color: transparent;
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(0, 102, 204, 0.25);
            transform: scale(1.03);
        }

        /* RESPONSIVE LAYOUT CONFIGURATIONS */
        @media (max-width: 768px) {
            .contact-header { padding: 80px 5%; }
            .contact-header h1 { font-size: 36px; }
            .contact-grid { padding: 40px 20px; gap: 20px; }
            .contact-card { max-width: 100%; }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <section class="contact-header">
        <span class="section-badge">Get In Touch</span>
        <h1>Contact Us</h1>
        <p>Need assistance or have general setup questions? Reach out through any channel below and our neighborhood team will be in touch shortly.</p>
    </section>

    <section class="contact-grid">
        
        <div class="contact-card">
            <div class="icon-box">🏠</div>
            <h3>Visit Us</h3>
            <p>Drop by our local area management office infrastructure building.</p>
            <span class="contact-value">Block A, Community Center</span>
        </div>

        <div class="contact-card">
            <div class="icon-box">📞</div>
            <h3>Call Us</h3>
            <p>Give our help desk a call during working operation hours.</p>
            <span class="contact-value">+60 3-1234 5678</span>
        </div>

        <div class="contact-card">
            <div class="icon-box">✉️</div>
            <h3>Email Us</h3>
            <p>Send your registration inquiries directly to our inbox support line.</p>
            <a href="mailto:support@smartville.local" class="contact-value">support@smartville.local</a>
        </div>
        
    </section>
    
</body>
</html>