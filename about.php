<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - SmartVille Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        body { 
            background: linear-gradient(180deg, #f0f5fa 0%, #e6eff8 100%); /* NEW: Premium soft blueish hue background */
            background-attachment: fixed;
            color: #0f172a; 
            -webkit-font-smoothing: antialiased;
        }
        
        .about-wrapper { max-width: 1200px; margin: 60px auto; padding: 0 24px; }
        
        /* --- BRAND NEW HERO LAYOUT (ABOUT OUR SYSTEM) --- */
        .hero-showcase { 
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 60px;
            align-items: center;
            background: rgba(255, 255, 255, 0.85); /* Modern translucent look */
            backdrop-filter: blur(10px);
            padding: 60px; 
            border-radius: 32px; 
            box-shadow: 0 10px 40px rgba(15, 32, 66, 0.03), 0 20px 50px rgba(0, 102, 204, 0.04); 
            margin-bottom: 90px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.7);
        }

        /* Hiasan latar belakang abstrak digital */
        .hero-showcase::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(0, 102, 204, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        
        .about-content {
            position: relative;
            z-index: 2;
        }

        .system-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: rgba(0, 102, 204, 0.08);
            color: #0066cc;
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 20px;
            border: 1px solid rgba(0, 102, 204, 0.1);
        }
        
        .about-content h2 { 
            color: #0f172a; 
            font-size: 46px; 
            line-height: 1.2;
            margin-bottom: 24px; 
            font-weight: 800; 
            letter-spacing: -1.5px;
        }

        .about-content h2 span {
            background: linear-gradient(135deg, #0066cc, #00a3ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .about-content p { 
            color: #475569; 
            font-size: 17px; 
            line-height: 1.8; 
            margin-bottom: 20px; 
            font-weight: 400; 
        }

        /* Point highlight moden */
        .feature-mini-list {
            margin-top: 25px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
        }

        .feature-item i {
            color: #10b981; /* Ikon tick hijau neon */
            font-size: 16px;
        }
        
        /* Bingkai Imej Terapung */
        .about-image-pane { 
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .image-wrapper-glow {
            width: 100%;
            background: #ffffff;
            padding: 12px;
            border-radius: 24px;
            box-shadow: 0 30px 60px -15px rgba(15, 32, 66, 0.1), 0 0 0 1px rgba(0,0,0,0.01);
            transition: transform 0.5s ease;
        }

        .image-wrapper-glow:hover {
            transform: scale(1.02) rotate(1deg);
        }

        .about-image-pane img { 
            width: 100%; 
            display: block;
            border-radius: 16px; 
            object-fit: cover;
        }
        
        /* --- MODERN TEAM SECTION --- */
        .team-section {
            text-align: center;
            margin-top: 80px;
        }
        
        .team-tag {
            display: inline-block;
            background-color: rgba(0, 102, 204, 0.08);
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #0066cc;
            margin-bottom: 16px;
            border: 1px solid rgba(0, 102, 204, 0.15);
        }
        
        .team-title {
            color: #0f172a;
            font-size: 40px;
            font-weight: 800;
            margin-bottom: 16px;
            letter-spacing: -1px;
        }

        .team-subtitle {
            color: #475569;
            font-size: 16px;
            max-width: 620px;
            margin: 0 auto 55px;
            line-height: 1.6;
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 28px;
        }
        
        /* PREMIUM STUDIO CARD (MODERN UPGRADE) */
        .team-member-card {
            background-color: #ffffff; /* Solid pure white contrasting against blue background */
            padding: 36px 24px;
            border-radius: 28px;
            box-shadow: 0 10px 30px -5px rgba(15, 32, 66, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.8);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            align-items: center; 
            text-align: center; 
        }
        
        .team-member-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 60px -10px rgba(0, 102, 204, 0.15);
            border-color: rgba(0, 102, 204, 0.25);
        }
        
        .avatar-container {
            width: 90px; 
            height: 90px;
            border-radius: 50%; 
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); /* Fallback gradient background */
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            border: 3px solid #ffffff;
            box-shadow: 0 8px 20px rgba(3, 105, 161, 0.12);
            overflow: hidden; /* Ensures image stays clipped inside the circle */
        }

        .avatar-container img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Keeps aspect ratio clean without distortion */
            display: block;
        }

        .member-info-meta {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .member-name {
            color: #0f172a;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -0.3px;
        }
        
        .member-email {
            display: inline-block;
            color: #64748b;
            font-size: 13px;
            text-decoration: none;
            transition: color 0.2s ease;
            font-weight: 500;
            word-break: break-all; 
            padding: 2px 8px;
        }
        
        .member-email:hover {
            color: #0066cc;
        }
        
        .member-socials {
            width: 100%;
            margin-top: auto;
            display: flex;
            justify-content: center;
        }
        
        .insta-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background-color: #f0f7ff;
            color: #0066cc;
            padding: 8px 18px;
            border-radius: 100px; 
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid rgba(0, 102, 204, 0.1);
            transition: all 0.2s ease;
        }
        
        .insta-link i {
            font-size: 14px;
            background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .insta-link:hover {
            background-color: #0066cc;
            color: #ffffff;
            border-color: #0066cc;
            box-shadow: 0 4px 15px rgba(0, 102, 204, 0.25);
        }
        
        .insta-link:hover i {
            -webkit-text-fill-color: #ffffff;
        }
        
        /* RESPONSIVE DESIGN (MOBILE FRIENDLY) */
        @media (max-width: 992px) {
            .hero-showcase { grid-template-columns: 1fr; padding: 40px; gap: 40px; }
            .about-image-pane { order: -1; }
            .about-content { text-align: center; }
            .system-badge { justify-content: center; }
            .about-content h2 { font-size: 36px; }
            .feature-mini-list { align-items: center; }
        }

        @media (max-width: 576px) {
            .about-wrapper { margin: 30px auto; }
            .hero-showcase { padding: 30px 20px; border-radius: 24px; }
            .team-title { font-size: 32px; }
            .team-grid { grid-template-columns: 1fr; max-width: 320px; margin: 0 auto; }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="about-wrapper">
        
        <section class="hero-showcase">
            <div class="about-content">
                <div class="system-badge">
                    <i class="fa-solid fa-bolt"></i> Intelligent Platform
                </div>
                <h2>Automating The Next Generation of <span>Smart Living</span>.</h2>
                <p>SmartVille Hub is designed to completely automate and streamline community bookings, residential registrations, and physical facility requests.</p>
                <p>Our goal is to transition traditional, paper-heavy community workflows into a high-performance, responsive platform accessible anytime, anywhere especially in Segamat.</p>
                
                <div class="feature-mini-list">
                    <div class="feature-item"><i class="fa-solid fa-circle-check"></i> 100% Cloud-Based Infrastructure</div>
                    <div class="feature-item"><i class="fa-solid fa-circle-check"></i> Zero-Paper Digital Registrations</div>
                </div>
            </div>
            
            <div class="about-image-pane">
                <div class="image-wrapper-glow">
                    <img src="overview.png" alt="SmartVille System Overview">
                </div>
            </div>
        </section>

        <section class="team-section">
            <span class="team-tag">The Core Team</span>
            <h2 class="team-title">Curious Minds, Passionate Performers</h2>
            <p class="team-subtitle">We are attentive listeners, empathetic understanders, intelligent thinkers, and solution-driven doers committed to building better living spaces.</p>
            
            <div class="team-grid">
                
                <div class="team-member-card">
                    <div class="avatar-container">
                        <img src="Ammar.png" alt="Ammar Hariz">
                    </div>
                    <div class="member-info-meta">
                        <h3 class="member-name">Ammar Hariz</h3>
                        <a href="mailto:2024287728@student.uitm.edu.my" class="member-email">2024287728@student.uitm.edu.my</a>
                    </div>
                    <div class="member-socials">
                        <a href="https://www.instagram.com/zerooneeeeee15/" target="_blank" class="insta-link">
                            <i class="fab fa-instagram"></i> Instagram
                        </a>
                    </div>
                </div>

                <div class="team-member-card">
                    <div class="avatar-container">
                        <img src="Shahidah.png" alt="Shahidah">
                    </div>
                    <div class="member-info-meta">
                        <h3 class="member-name">Shahidah</h3>
                        <a href="mailto:2024259362@student.uitm.edu.my" class="member-email">2024259362@student.uitm.edu.my</a>
                    </div>
                    <div class="member-socials">
                        <a href="https://www.instagram.com/jyhda__/" target="_blank" class="insta-link">
                            <i class="fab fa-instagram"></i> Instagram
                        </a>
                    </div>
                </div>

                <div class="team-member-card">
                    <div class="avatar-container">
                        <img src="Fareez.png" alt="Fareez Ikhwan">
                    </div>
                    <div class="member-info-meta">
                        <h3 class="member-name">Fareez Ikhwan</h3>
                        <a href="mailto:2024237686@student.uitm.edu.my" class="member-email">2024237686@student.uitm.edu.my</a>
                    </div>
                    <div class="member-socials">
                        <a href="https://www.instagram.com/freezikhwanghafar/" target="_blank" class="insta-link">
                            <i class="fab fa-instagram"></i> Instagram
                        </a>
                    </div>
                </div>

                <div class="team-member-card">
                    <div class="avatar-container">
                        <img src="Fatin.png" alt="Fatin Fareisha">
                    </div>
                    <div class="member-info-meta">
                        <h3 class="member-name">Fatin Fareisha</h3>
                        <a href="mailto:2024237374@student.uitm.edu.my" class="member-email">2024237374@student.uitm.edu.my</a>
                    </div>
                    <div class="member-socials">
                        <a href="https://www.instagram.com/faareishaaa/" target="_blank" class="insta-link">
                            <i class="fab fa-instagram"></i> Instagram
                        </a>
                    </div>
                </div>

            </div>
        </section>
    </div>
</body>
</html>