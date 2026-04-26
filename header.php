<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PathIntel.ai | Intelligent Career Guidance</title>

    <!-- Google Fonts: Playfair Display & Sora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Sora:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Core Design System -->
    <link type="text/css" rel="stylesheet" href="css/core.css"/>
    
    <!-- Preserve Chatbot System -->
    <link type="text/css" rel="stylesheet" href="css/chatbot.css"/>

    <style>
        /* DESIGN DECISION: Custom navigation styles following the asymmetric grid and floating intent. */
        .glass-nav {
            height: 80px;
            display: flex;
            align-items: center;
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            transition: var(--transition-glide);
        }

        .nav-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .logo-text {
            font-size: 1.5rem;
            color: var(--color-accent);
            text-transform: lowercase;
            letter-spacing: -1px;
        }

        .nav-links {
            display: flex;
            gap: var(--space-32);
            align-items: center;
        }

        .nav-links a {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--color-text-secondary);
        }

        .nav-links a:hover, .nav-links a.active {
            color: var(--color-accent);
        }

        .dropdown {
            position: relative;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--color-bg-surface);
            border: 1px solid var(--color-border);
            min-width: 200px;
            box-shadow: var(--shadow-float);
            padding: var(--space-16) 0;
            z-index: 100;
        }

        .dropdown:hover .dropdown-content {
            display: block;
            animation: fadeInSlideUp 300ms ease forwards;
        }

        .dropdown-content a {
            display: block;
            padding: var(--space-8) var(--space-24);
            text-transform: none;
            letter-spacing: normal;
        }
    </style>
</head>

<body>
    <header class="glass-nav">
        <div class="container nav-inner">
            <a href="main.php" class="logo-text font-serif">PathIntel.ai</a>
            
            <nav class="nav-links">
                <a href="main.php">Home</a>
                <div class="dropdown">
                    <a href="javascript:void(0)" class="dropbtn">Services <i data-lucide="chevron-down" style="width: 14px; height: 14px; vertical-align: middle;"></i></a>
                    <div class="dropdown-content">
                        <?php if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true): ?>
                            <a href="login.php">Career Prediction</a>
                        <?php else: ?>
                            <a href="http://localhost:5000/">Career Prediction</a>
                        <?php endif; ?>
                        <a href="courses.php">Courses</a>
                        <a href="blog.php">Knowledge Network</a>
                    </div>
                </div>
                <a href="main.php#about">About</a>
                <a href="contact.php">Contact</a>
                
                <?php if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true): ?>
                    <a href="login.php">Login</a>
                    <a href="register.php" class="btn-primary" style="padding: 10px 20px;">Register</a>
                <?php else: ?>
                    <span class="user-greeting" style="font-size: 0.8rem; color: var(--color-text-muted);">Hi, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                    <a href="logout.php">Logout</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>