<?php
// settings.php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}
include('db_connect.php'); 
$session_role = $_SESSION['role'] ?? 'Resident';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interface Settings</title>
    <link rel="stylesheet" href="style.css">
    <style>
        :root { --base-font-size: 16px; --base-font-family: 'Segoe UI', sans-serif; }
        html { font-size: var(--base-font-size) !important; font-family: var(--base-font-family) !important; }
        body, nav, .sidebar, .main-content, h1, h2, h3, span, a, p, button { font-family: var(--base-font-family) !important; }
        
        body { background-color: #06060c; color: #ffffff; margin: 0; }
        
        /* 🛠️ SAMA DENGAN PROFILE: Menggunakan sistem padding asal */
        .main-content { padding: 40px; }
        .content-container { padding: 0px; }

        /* Tajuk Utama Halaman - Ikut Profile 100% */
        .page-title {
            color: #ffffff;
            margin: 0;
            font-size: 32px;
            font-weight: bold;
            line-height: 1.2;
            transition: color 0.3s ease;
        }

        /* 📋 Butang Toggle Menu Utama - Tempat & Saiz Ikut Profile */
        .btn-toggle-menu {
            background: #1c1c34; 
            border: 1px solid rgba(255,255,255,0.1); 
            color: white; 
            padding: 8px 16px; 
            border-radius: 8px; 
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            height: 38px;
            box-sizing: border-box;
            transition: background 0.3s, color 0.3s, border-color 0.3s;
        }

        .btn-toggle-menu:hover {
            background: #252542;
        }

        /* 🔒 KOTAK SETTING KEKAL STATIC (Ikut aliran grid normal skrin) */
        .settings-card { 
            width: 100%;
            max-width: 650px; /* Kekalkan kelebaran static kotak setting anda */
            padding: 30px;
            background: #0f0f1a; 
            border: 1px solid rgba(255,255,255,0.06); 
            border-radius: 16px;
            transition: background 0.3s, border-color 0.3s;
            box-sizing: border-box;
            margin-top: 35px; /* Jarak atas yang seimbang selepas tajuk */
        }
        
        .settings-row { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 15px 0; 
            border-bottom: 1px solid rgba(255,255,255,0.05); 
            gap: 20px;
        }
        
        .settings-info { flex: 1; }
        .settings-action { display: flex; align-items: center; justify-content: flex-end; min-width: 200px; }
        .settings-label { font-weight: 600; color: #fff; display: block; }
        .settings-desc { font-size: 12px; color: #a1a1aa; }
        
        /* Switch & Slider UI */
        .switch-container { position: relative; display: inline-block; width: 50px; height: 26px; }
        .switch-container input { opacity: 0; width: 0; height: 0; }
        .switch-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #3f3f46; transition: .3s; border-radius: 30px; }
        .switch-slider::before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; transition: .3s; border-radius: 50%; }
        input:checked + .switch-slider { background-color: #ec4899; }
        input:checked + .switch-slider::before { transform: translateX(24px); }
        .range-slider { accent-color: #ec4899; cursor: pointer; }
        
        /* Dropdown Select Box */
        .custom-select { 
            width: 100%;
            max-width: 220px; 
            padding: 10px 14px; 
            background: #1c1c34; 
            border: 1px solid rgba(255,255,255,0.1); 
            border-radius: 8px; 
            color: white;
            font-size: 14px;
            outline: none;
            cursor: pointer;
            transition: background 0.3s, color 0.3s, border-color 0.3s;
        }

        /* ☀️ SUNTIKAN SISTEM LIGHT MODE KHUSUS SETTINGS */
        html.light-mode { background-color: #f4f4f7 !important; }
        html.light-mode body, html.light-mode .main-content { background: #f4f4f7 !important; color: #18181b !important; }
        html.light-mode .page-title { color: #18181b !important; }
        html.light-mode .settings-card { background: #ffffff !important; border-color: rgba(0,0,0,0.08) !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        html.light-mode .settings-label { color: #18181b !important; }
        html.light-mode .settings-desc { color: #52525b !important; }
        html.light-mode #fontSizeDisplay { color: #18181b !important; }
        html.light-mode .custom-select { background: #ffffff !important; color: #18181b !important; border: 1px solid rgba(0, 0, 0, 0.15) !important; }
        html.light-mode .settings-row { border-bottom-color: rgba(0, 0, 0, 0.06); }

        /* Inline fallback override rules untuk Light Mode */
        html.light-mode .btn-toggle-menu-light {
            background: #e4e4e7 !important;
            color: #18181b !important;
            border: 1px solid rgba(0, 0, 0, 0.15) !important;
        }
        html.light-mode .btn-toggle-menu-light:hover {
            background: #d4d4d8 !important;
            color: #18181b !important;
        }
    </style>
</head>
<body>
    <?php 
    if ($session_role === 'Admin') { include('admin_sidebar_helper.php'); } 
    elseif ($session_role === 'Organizer') { include('header.php'); } 
    else { include('resident_sidebar_helper.php'); }
    ?>

    <main class="main-content" id="main-content">
        <div class="content-container">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0px;">
                <h1 class="page-title">Interface Personalization</h1>
                <button id="mainToggleBtn" onclick="toggleSidebar()" class="btn-toggle-menu">
                    &#9776; Toggle Menu
                </button>
            </div>
            
            <div class="settings-card">
                <div class="settings-row">
                    <div class="settings-info">
                        <span class="settings-label">Display Mode Theme</span>
                        <span class="settings-desc">Switch between Dark Mode and Light Mode instantly.</span>
                    </div>
                    <div class="settings-action">
                        <label class="switch-container">
                            <input type="checkbox" id="themeCheckbox" onchange="toggleThemeSwitch()">
                            <span class="switch-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="stats-grid" style="display: none;"></div>

                <div class="settings-row">
                    <div class="settings-info">
                        <span class="settings-label">Typography Scale Base</span>
                        <span class="settings-desc">Change text size dynamically across all sections.</span>
                    </div>
                    <div class="settings-action" style="gap:10px;">
                        <input type="range" min="14" max="22" value="16" id="fontSlider" class="range-slider" oninput="adjustFont(this.value)">
                        <span id="fontSizeDisplay">16px</span>
                    </div>
                </div>

                <div class="settings-row" style="border:none;">
                    <div class="settings-info">
                        <span class="settings-label">System Font Family Styles</span>
                        <span class="settings-desc">Choose a different font scheme for readability.</span>
                    </div>
                    <div class="settings-action">
                        <select id="fontStyleSelect" class="custom-select" onchange="switchFontStyle(this.value)">
                            <option value="'Segoe UI', sans-serif">Segoe UI (Clean)</option>
                            <option value="'Inter', sans-serif">Inter (Modern)</option>
                            <option value="'Georgia', serif">Georgia (Classic)</option>
                        </select>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>
    // Fungsi khas untuk memastikan warna butang dikawal ketat mengikut tema aktif
    function updateToggleButtonTheme(theme) {
        const btn = document.getElementById('mainToggleBtn');
        if (!btn) return;
        
        if (theme === 'light') {
            btn.classList.add('btn-toggle-menu-light');
            btn.style.setProperty('background', '#e4e4e7', 'important');
            btn.style.setProperty('color', '#18181b', 'important');
            btn.style.setProperty('border', '1px solid rgba(0, 0, 0, 0.15)', 'important');
            
            // Tambah event hover manual supaya style.css tidak overwrite sewaktu cerah
            btn.onmouseover = function() {
                this.style.setProperty('background', '#d4d4d8', 'important');
            };
            btn.onmouseout = function() {
                this.style.setProperty('background', '#e4e4e7', 'important');
            };
        } else {
            btn.classList.remove('btn-toggle-menu-light');
            btn.style.removeProperty('background');
            btn.style.removeProperty('color');
            btn.style.removeProperty('border');
            
            btn.onmouseover = function() {
                this.style.setProperty('background', '#252542', 'important');
            };
            btn.onmouseout = function() {
                this.style.removeProperty('background');
            };
        }
    }

    function toggleThemeSwitch() {
        const checkbox = document.getElementById('themeCheckbox');
        if (checkbox.checked) {
            document.documentElement.classList.add('light-mode');
            localStorage.setItem('theme', 'light');
            updateToggleButtonTheme('light');
        } else {
            document.documentElement.classList.remove('light-mode');
            localStorage.setItem('theme', 'dark');
            updateToggleButtonTheme('dark');
        }
        if (typeof window.refreshGlobalTypography === 'function') window.refreshGlobalTypography();
    }
    
    function adjustFont(size) {
        document.documentElement.style.setProperty('--base-font-size', size + 'px');
        document.getElementById('fontSizeDisplay').innerText = size + 'px';
        localStorage.setItem('fontSize', size);
        if (typeof window.refreshGlobalTypography === 'function') window.refreshGlobalTypography();
    }
    
    function switchFontStyle(style) {
        document.documentElement.style.setProperty('--base-font-family', style);
        localStorage.setItem('fontStyle', style);
        if (typeof window.refreshGlobalTypography === 'function') window.refreshGlobalTypography();
    }
    
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        if (sidebar && mainContent) {
            sidebar.classList.toggle('hidden');
            mainContent.classList.toggle('expanded');
            localStorage.setItem('sidebarHidden', sidebar.classList.contains('hidden'));
        }
    }
    
    window.addEventListener('DOMContentLoaded', () => {
        const currentTheme = localStorage.getItem('theme') || 'dark';
        if(document.getElementById('themeCheckbox')) {
            document.getElementById('themeCheckbox').checked = (currentTheme === 'light');
        }
        
        if (currentTheme === 'light') {
            document.documentElement.classList.add('light-mode');
            updateToggleButtonTheme('light');
        } else {
            updateToggleButtonTheme('dark');
        }
        
        const size = localStorage.getItem('fontSize') || '16';
        if(document.getElementById('fontSlider')) document.getElementById('fontSlider').value = size;
        document.documentElement.style.setProperty('--base-font-size', size + 'px');
        if(document.getElementById('fontSizeDisplay')) document.getElementById('fontSizeDisplay').innerText = size + 'px';
        
        const savedFont = localStorage.getItem('fontStyle') || "'Segoe UI', sans-serif";
        document.documentElement.style.setProperty('--base-font-family', savedFont);
        if(document.getElementById('fontStyleSelect')) document.getElementById('fontStyleSelect').value = savedFont;

        if (typeof window.refreshGlobalTypography === 'function') window.refreshGlobalTypography();
    });
    </script>
</body>
</html>