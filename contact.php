<?php
// Initialize the session
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | PathIntel.ai</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;600&family=DM+Mono:wght@500&family=Sora:wght@300;400;600&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* THEME SYNC: Deep Space Navy */
        :root {
            --color-bg: #0A0E1A;
            --color-card: #121826;
            --color-teal: #00D4FF;
            --color-teal-soft: rgba(0, 212, 255, 0.15);
            --color-ink: #F8FAFC;
            --color-text-muted: #94A3B8;
            --font-heading: 'Playfair Display', serif;
            --font-mono: 'DM Mono', monospace;
            --font-body: 'Sora', sans-serif;
            --transition-glide: all 400ms cubic-bezier(0.23, 1, 0.32, 1);
            --space-32: 32px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: var(--font-body);
            background-color: var(--color-bg);
            color: var(--color-ink);
            min-height: 100vh;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .decorative-ring {
            position: fixed;
            width: 600px;
            height: 600px;
            border: 1px solid rgba(0, 212, 255, 0.05);
            border-radius: 50%;
            top: 20%;
            left: -10%;
            z-index: -1;
            animation: rotateSlow 60s infinite linear;
        }
        @keyframes rotateSlow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 140px 2rem;
            display: grid;
            grid-template-columns: 0.8fr 1.2fr;
            gap: 100px;
            align-items: start;
        }

        @media (max-width: 992px) {
            .contact-container { grid-template-columns: 1fr; padding-top: 100px; gap: 60px; }
        }

        /* --- INFO PANEL --- */
        .info-panel { animation: slideUp 800ms var(--transition-glide); }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .headline { font-family: var(--font-heading); font-size: 4.5rem; font-weight: 700; line-height: 1.0; margin-bottom: 32px; letter-spacing: -0.02em; }
        .sub-headline { font-size: 1.1rem; color: var(--color-text-muted); margin-bottom: 60px; max-width: 420px; }

        .contact-item { display: flex; align-items: start; gap: 20px; margin-bottom: 40px; }
        .item-label { font-family: var(--font-mono); font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: var(--color-text-muted); margin-bottom: 10px; display: block; }
        .item-value { font-size: 16px; color: var(--color-ink); font-weight: 400; }
        .item-icon { color: var(--color-teal); flex-shrink: 0; margin-top: 6px; }

        /* --- FORM CARD --- */
        .form-card {
            background: var(--color-card);
            padding: 70px;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.5);
            transition: var(--transition-glide);
            animation: slideUp 800ms var(--transition-glide) 150ms backwards;
        }
        .form-card:hover { transform: translateY(-4px); border-color: var(--color-teal); box-shadow: 0 32px 64px -16px rgba(0, 0, 0, 0.6); }

        .input-group { margin-bottom: 28px; position: relative; opacity: 0; animation: fieldFadeIn 400ms forwards; }
        @keyframes fieldFadeIn { to { opacity: 1; } }

        .label { display: block; font-family: var(--font-mono); font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: var(--color-text-muted); margin-bottom: 10px; }
        .input {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 4px;
            padding: 16px 18px;
            font-family: var(--font-body);
            font-size: 15px;
            color: var(--color-ink);
            transition: var(--transition-glide);
        }
        .input:focus { outline: none; border-color: var(--color-teal); box-shadow: 0 0 0 3px var(--color-teal-soft); }
        
        textarea.input { min-height: 160px; resize: vertical; }

        .btn-submit {
            background: transparent;
            color: var(--color-teal);
            border: 1px solid var(--color-teal);
            padding: 18px 48px;
            border-radius: 4px;
            font-family: var(--font-body);
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            cursor: pointer;
            transition: var(--transition-glide);
            display: inline-block;
            margin-left: auto;
        }
        .btn-submit:hover { 
            background: var(--color-teal);
            color: var(--color-bg);
            box-shadow: 0 0 20px var(--color-teal-soft);
        }

        /* Map styling */
        .map-container { margin-top: 60px; border-radius: 4px; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.08); }

        header {
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 5%;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background: rgba(10, 14, 26, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .logo { font-family: var(--font-heading); font-size: 1.6rem; text-decoration: none; color: var(--color-teal); text-transform: lowercase; letter-spacing: -1px; font-weight: 700; }
    </style>
</head>
<body>

    <div class="decorative-ring"></div>

    <header>
        <a href="main.php" class="logo">PathIntel.ai</a>
        <nav style="display: flex; gap: 40px;">
            <a href="main.php" style="text-decoration: none; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: var(--color-ink);">Home</a>
            <a href="courses.php" style="text-decoration: none; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: var(--color-ink);">Academy</a>
        </nav>
    </header>

    <div class="contact-container">
        <!-- INFO PANEL -->
        <div class="info-panel">
            <h1 class="headline">We're<br>Listening.</h1>
            <p class="sub-headline">Our team typically responds within 24 hours on weekdays. Reach out for any inquiries or academic support.</p>

            <div class="contact-item">
                <i data-lucide="mail" class="item-icon" style="width: 20px;"></i>
                <div>
                    <span class="item-label">Direct Email</span>
                    <span class="item-value">support@pathintel.ai</span>
                </div>
            </div>

            <div class="contact-item">
                <i data-lucide="phone" class="item-icon" style="width: 20px;"></i>
                <div>
                    <span class="item-label">Phone Support</span>
                    <span class="item-value">+1 (222) 547-223-45</span>
                </div>
            </div>

            <div class="contact-item">
                <i data-lucide="clock" class="item-icon" style="width: 20px;"></i>
                <div>
                    <span class="item-label">Operational Hours</span>
                    <span class="item-value">Mon — Fri | 09:00 - 18:00 EST</span>
                </div>
            </div>

            <div class="contact-item">
                <i data-lucide="map-pin" class="item-icon" style="width: 20px;"></i>
                <div>
                    <span class="item-label">Main Office</span>
                    <span class="item-value">Santacruz West, Educational District</span>
                </div>
            </div>

            <div class="map-container">
                <div id="contact-map" style="height: 280px;"></div>
            </div>
        </div>

        <!-- FORM CARD -->
        <div class="form-card">
            <form action="#" method="POST">
                <div class="input-group" style="animation-delay: 100ms;">
                    <label class="label">Full Name</label>
                    <input type="text" name="name" class="input" placeholder="e.g. Alex Johnson" required>
                </div>

                <div class="input-group" style="animation-delay: 180ms;">
                    <label class="label">Email Address</label>
                    <input type="email" name="email" class="input" placeholder="alex@university.edu" required>
                </div>

                <div class="input-group" style="animation-delay: 260ms;">
                    <label class="label">Subject of Inquiry</label>
                    <input type="text" name="subject" class="input" placeholder="e.g. Career Analysis Question">
                </div>

                <div class="input-group" style="animation-delay: 340ms;">
                    <label class="label">Your Message</label>
                    <textarea name="message" class="input" placeholder="Tell us how we can help..." required></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 40px;">
                    <button type="submit" class="btn-submit">Dispatch Inquiry</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/jquery.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
    <script type="text/javascript" src="js/google-map.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
