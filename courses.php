<?php
// Initialize the session
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses | PathIntel.ai</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;600&family=DM+Mono:wght@400;500&family=Sora:wght@300;400;600&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* PAGE: Courses */
        /* DESIGN: Structured Weightlessness — matches homepage */
        /* NEW TOKENS: Specific variables for the courses layout */
        :root {
            --color-bg-primary: #0D1117;
            --color-bg-secondary: #131920;
            --color-bg-elevated: #1C2333;
            --color-accent: #F5A623;
            --color-accent-soft: rgba(245, 166, 35, 0.12);
            --color-text-primary: #F0EDE8;
            --color-text-secondary: #8B95A8;
            --color-border-subtle: rgba(255, 255, 255, 0.06);
            --color-border-default: rgba(255, 255, 255, 0.10);
            --shadow-md: 0 8px 32px rgba(0,0,0,0.4);
            --shadow-lg: 0 24px 64px rgba(0,0,0,0.5);
            
            --font-heading: 'Cormorant Garamond', serif;
            --font-body: 'Sora', sans-serif;
            --font-mono: 'DM Mono', monospace;
            
            --transition-glide: all 400ms cubic-bezier(0.23, 1, 0.32, 1);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            background-color: var(--color-bg-primary);
            color: var(--color-text-primary);
            font-family: var(--font-body);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 48px;
        }

        @media (max-width: 768px) {
            .container { padding: 0 24px; }
        }

        /* --- ANIMATIONS --- */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slowRotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes slowDrift {
            from { transform: translateY(0) rotate(0deg); }
            to { transform: translateY(-40px) rotate(10deg); }
        }

        .anim-fade-up {
            opacity: 0;
            animation: fadeUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) both;
        }
        .delay-1 { animation-delay: 100ms; }
        .delay-2 { animation-delay: 200ms; }
        .delay-3 { animation-delay: 300ms; }
        .delay-4 { animation-delay: 400ms; }
        .delay-5 { animation-delay: 500ms; }
        .delay-6 { animation-delay: 600ms; }

        /* --- DECORATIVE ELEMENTS --- */
        .ambient-ring {
            position: fixed;
            width: 600px;
            height: 600px;
            border: 1px solid rgba(245, 166, 35, 0.04);
            border-radius: 50%;
            bottom: -200px;
            right: -200px;
            pointer-events: none;
            z-index: 0;
            animation: slowRotate 90s linear infinite;
        }

        /* --- HERO SECTION --- */
        .hero-courses {
            height: 280px;
            display: flex;
            align-items: center;
            position: relative;
            background-color: var(--color-bg-primary);
            background-image: radial-gradient(rgba(255,255,255,0.08) 1px, transparent 1px);
            background-size: 28px 28px;
            overflow: hidden;
            border-bottom: 1px solid var(--color-border-subtle);
        }

        @media (max-width: 768px) {
            .hero-courses { height: 200px; }
        }

        .hero-content { position: relative; z-index: 2; width: 100%; display: flex; justify-content: space-between; align-items: center; }
        
        .hero-left-label {
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: var(--font-mono);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: var(--color-accent);
            margin-bottom: 16px;
        }
        .hero-left-label::before { content: ""; width: 24px; height: 1px; background: var(--color-accent); }

        .hero-title { font-family: var(--font-heading); font-size: 56px; font-weight: 300; line-height: 1.1; margin-bottom: 12px; }
        .hero-subtitle { font-family: var(--font-body); font-size: 16px; color: var(--color-text-secondary); max-width: 480px; }

        .hero-badge {
            display: inline-block;
            margin-top: 24px;
            padding: 6px 14px;
            background: var(--color-accent-soft);
            border: 1px solid rgba(245, 166, 35, 0.2);
            border-radius: 9999px;
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--color-accent);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .hero-shapes { position: absolute; right: 0; top: 0; width: 40%; height: 100%; pointer-events: none; }
        .hero-shape-arc {
            position: absolute;
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 50%;
            animation: slowDrift 20s infinite alternate ease-in-out;
        }

        /* --- FILTER BAR --- */
        .filter-bar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(13, 17, 23, 0.90);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--color-border-subtle);
            padding: 16px 0;
            transition: background 0.3s ease;
        }

        .filter-bar-content { display: flex; justify-content: space-between; align-items: center; gap: 24px; }

        .search-container { position: relative; width: 320px; }
        .search-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--color-text-secondary); width: 16px; height: 16px; pointer-events: none; }
        .search-input {
            width: 100%;
            background: #131920;
            border: 1px solid var(--color-border-default);
            border-radius: 8px;
            padding: 12px 16px 12px 44px;
            font-family: var(--font-body);
            font-size: 14px;
            color: var(--color-text-primary);
            transition: var(--transition-glide);
        }
        .search-input::placeholder { color: var(--color-text-secondary); opacity: 0.4; }
        .search-input:focus { outline: none; border-color: var(--color-accent); box-shadow: 0 0 0 3px var(--color-accent-soft); }

        .filter-pills { display: flex; gap: 8px; align-items: center; }
        .filter-pill {
            background: transparent;
            border: 1px solid var(--color-border-default);
            border-radius: 9999px;
            padding: 8px 16px;
            font-family: var(--font-mono);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: var(--color-text-secondary);
            cursor: pointer;
            transition: var(--transition-glide);
            text-decoration: none;
        }
        .filter-pill:hover { border-color: var(--color-accent); color: var(--color-accent); }
        .filter-pill.active { background: var(--color-accent-soft); border-color: rgba(245, 166, 35, 0.25); color: var(--color-accent); }

        .sort-dropdown {
            width: 180px;
            position: relative;
        }
        .sort-select {
            width: 100%;
            background: #131920;
            border: 1px solid var(--color-border-default);
            border-radius: 8px;
            padding: 12px 40px 12px 16px;
            font-family: var(--font-body);
            font-size: 13px;
            color: var(--color-text-primary);
            appearance: none;
            cursor: pointer;
            transition: var(--transition-glide);
        }
        .sort-select:focus { outline: none; border-color: var(--color-accent); }
        .sort-arrow { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--color-text-secondary); width: 14px; height: 14px; }

        @media (max-width: 992px) {
            .filter-bar-content { flex-direction: column; align-items: stretch; gap: 16px; }
            .search-container, .sort-dropdown { width: 100%; }
            .filter-pills { overflow-x: auto; padding-bottom: 4px; -ms-overflow-style: none; scrollbar-width: none; }
            .filter-pills::-webkit-scrollbar { display: none; }
        }

        /* --- META BAR --- */
        .results-meta { padding: 24px 0 16px 0; display: flex; justify-content: space-between; align-items: center; }
        .meta-count { font-family: var(--font-mono); font-size: 11px; color: var(--color-text-secondary); }
        .view-toggle { display: flex; gap: 12px; }
        .toggle-btn { background: none; border: none; color: var(--color-text-secondary); cursor: pointer; transition: 0.2s; }
        .toggle-btn:hover { color: var(--color-text-primary); }
        .toggle-btn.active { color: var(--color-accent); }

        /* --- COURSE GRID --- */
        .course-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            margin-top: 16px;
            padding-bottom: 128px;
        }

        @media (max-width: 1200px) { .course-grid { grid-template-columns: repeat(2, 1fr); gap: 24px; } }
        @media (max-width: 768px) { .course-grid { grid-template-columns: 1fr; gap: 16px; } }

        /* --- COURSE CARD --- */
        .course-card {
            background: var(--color-bg-elevated);
            border: 1px solid var(--color-border-subtle);
            border-radius: 16px;
            overflow: hidden;
            transition: var(--transition-glide);
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 1;
            text-decoration: none;
            color: inherit;
        }

        .card-thumb-area {
            height: 200px;
            position: relative;
            overflow: hidden;
        }
        .card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.23, 1, 0.32, 1);
        }
        .card-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(13, 17, 23, 0.7), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .course-card:hover .card-img { transform: scale(1.04); }
        .course-card:hover .card-overlay { opacity: 1; }
        .course-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(255, 255, 255, 0.14);
        }

        .category-tag {
            position: absolute;
            top: 12px;
            left: 12px;
            background: rgba(13, 17, 23, 0.75);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 9999px;
            padding: 4px 12px;
            font-family: var(--font-mono);
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: #FFFFFF;
            z-index: 3;
        }

        .difficulty-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            border-radius: 9999px;
            padding: 4px 12px;
            font-family: var(--font-mono);
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            z-index: 3;
        }
        .diff-beginner { background: rgba(45, 212, 160, 0.2); color: #2DD4A0; border: 1px solid rgba(45, 212, 160, 0.3); }
        .diff-intermediate { background: rgba(245, 166, 35, 0.2); color: #F5A623; border: 1px solid rgba(245, 166, 35, 0.3); }
        .diff-advanced { background: rgba(240, 107, 107, 0.2); color: #F06B6B; border: 1px solid rgba(240, 107, 107, 0.3); }

        .card-body {
            padding: 24px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .instructor-line { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
        .avatar { width: 24px; height: 24px; border-radius: 50%; border: 1px solid var(--color-border-default); background: var(--color-bg-secondary); }
        .instructor-name { font-family: var(--font-body); font-size: 12px; color: var(--color-text-secondary); }
        .instructor-rating { font-family: var(--font-mono); font-size: 12px; color: var(--color-accent); }

        .course-title {
            font-family: var(--font-body);
            font-size: 17px;
            font-weight: 600;
            line-height: 1.3;
            color: var(--color-text-primary);
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            transition: color 0.2s ease;
        }
        .course-card:hover .course-title { color: #FFFFFF; }

        .course-desc {
            font-family: var(--font-body);
            font-size: 13px;
            color: var(--color-text-secondary);
            line-height: 1.6;
            margin-bottom: 16px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-meta { display: flex; gap: 16px; margin-top: auto; padding-top: 16px; }
        .meta-item { display: flex; align-items: center; gap: 6px; font-family: var(--font-mono); font-size: 11px; color: var(--color-text-secondary); }
        .meta-item i { width: 14px; height: 14px; }

        .progress-container { margin-top: 16px; }
        .progress-bar { height: 2px; background: rgba(255, 255, 255, 0.08); border-radius: 9999px; overflow: hidden; margin-bottom: 6px; }
        .progress-fill { height: 100%; background: var(--color-accent); box-shadow: 0 0 6px rgba(245, 166, 35, 0.5); }
        .progress-text { font-family: var(--font-mono); font-size: 10px; color: var(--color-accent); }

        .card-footer {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid var(--color-border-subtle);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price-container { display: flex; flex-direction: column; }
        .price-current { font-family: var(--font-body); font-size: 20px; font-weight: 600; color: var(--color-text-primary); }
        .price-old { font-family: var(--font-body); font-size: 13px; color: var(--color-text-secondary); text-decoration: line-through; }
        .price-free { font-family: var(--font-mono); font-size: 13px; color: var(--color-accent); text-transform: uppercase; letter-spacing: 0.1em; }

        .card-cta {
            background: transparent;
            border: 1px solid var(--color-accent);
            border-radius: 8px;
            color: var(--color-accent);
            padding: 8px 16px;
            font-family: var(--font-body);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            cursor: pointer;
            transition: var(--transition-glide);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-cta:hover { background: var(--color-accent); color: var(--color-bg-primary); }

        /* --- EMPTY STATE --- */
        .empty-state { padding: 128px 0; text-align: center; }
        .empty-icon { color: var(--color-text-secondary); opacity: 0.3; margin-bottom: 24px; }
        .empty-title { font-family: var(--font-body); font-size: 20px; color: var(--color-text-primary); margin-bottom: 8px; }
        .empty-sub { font-family: var(--font-body); font-size: 14px; color: var(--color-text-secondary); margin-bottom: 24px; }

        /* --- CATEGORY DIVIDER --- */
        .cat-divider {
            grid-column: 1 / -1;
            padding: 64px 0 24px 0;
            display: flex;
            align-items: center;
            gap: 24px;
        }
        .cat-line { flex-grow: 1; height: 1px; background: var(--color-border-subtle); }
        .cat-title { font-family: var(--font-heading); font-size: 24px; font-weight: 300; color: var(--color-text-primary); }

        /* Bootstrap Grid Replacement (Internal usage only) */
        .row { display: flex; flex-wrap: wrap; margin-left: -15px; margin-right: -15px; }
    </style>
</head>
<body>

    <!-- Background Decoration -->
    <div class="ambient-ring"></div>

    <!-- Page Header (Editorial Hero) -->
    <header class="hero-courses">
        <div class="container hero-content">
            <div class="hero-left">
                <span class="hero-left-label anim-fade-up">Courses</span>
                <h1 class="hero-title anim-fade-up delay-1">Expand Your Knowledge</h1>
                <p class="hero-subtitle anim-fade-up delay-2">Learn at your own pace. Master skills that matter in the digital age.</p>
                <div class="hero-badge anim-fade-up delay-3">42 Courses Available</div>
            </div>
            
            <div class="hero-shapes">
                <!-- Decorative Arc 1 -->
                <div class="hero-shape-arc" style="width: 300px; height: 300px; top: -50px; right: -50px; opacity: 0.06;"></div>
                <!-- Decorative Arc 2 -->
                <div class="hero-shape-arc" style="width: 500px; height: 500px; bottom: -200px; right: -100px; opacity: 0.04; animation-delay: -5s;"></div>
            </div>
        </div>
    </header>

    <!-- Sticky Filter/Search Bar -->
    <nav class="filter-bar">
        <div class="container filter-bar-content">
            <div class="search-container">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" class="search-input" placeholder="Search courses..." id="courseSearch">
            </div>

            <div class="filter-pills">
                <a href="#web" class="filter-pill active">All Categories</a>
                <a href="#web" class="filter-pill">Web Development</a>
                <a href="#prog" class="filter-pill">Programming</a>
                <a href="#server" class="filter-pill">Server Side</a>
                <a href="#" class="filter-pill">Business</a>
            </div>

            <div class="sort-dropdown">
                <select class="sort-select">
                    <option value="newest">Newest First</option>
                    <option value="popular">Most Popular</option>
                    <option value="rating">Highest Rated</option>
                </select>
                <i data-lucide="chevron-down" class="sort-arrow"></i>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="container">
        
        <div class="results-meta">
            <span class="meta-count">Showing all 20 courses in Academy</span>
            <div class="view-toggle">
                <button class="toggle-btn active"><i data-lucide="layout-grid" style="width: 18px;"></i></button>
                <button class="toggle-btn"><i data-lucide="list" style="width: 18px;"></i></button>
            </div>
        </div>

        <div class="course-grid">
            
            <!-- CATEGORY: WEB DEVELOPMENT -->
            <div class="cat-divider" id="web">
                <h2 class="cat-title">Web Development</h2>
                <div class="cat-line"></div>
            </div>

            <!-- Card 1 -->
            <a href="https://www.w3schools.com/jquery/default.asp" target="_blank" class="course-card anim-fade-up delay-1">
                <div class="card-thumb-area">
                    <span class="category-tag">Web Dev</span>
                    <span class="difficulty-badge diff-intermediate">Intermediate</span>
                    <img src="./img/coursejq.jpg" class="card-img" alt="jQuery">
                    <div class="card-overlay"></div>
                </div>
                <div class="card-body">
                    <div class="instructor-line">
                        <div class="avatar"></div>
                        <span class="instructor-name">W3Schools Academy</span>
                        <span class="instructor-rating">★ 4.8</span>
                    </div>
                    <h3 class="course-title">The Complete jQuery Course</h3>
                    <p class="course-desc">Master DOM manipulation, event handling, and AJAX animations with the world's most popular JS library.</p>
                    <div class="card-meta">
                        <span class="meta-item"><i data-lucide="clock"></i> 12h</span>
                        <span class="meta-item"><i data-lucide="book-open"></i> 24 lessons</span>
                        <span class="meta-item"><i data-lucide="users"></i> 1.2k</span>
                    </div>
                    <div class="card-footer">
                        <span class="price-free">Free</span>
                        <button class="card-cta">Enroll Now</button>
                    </div>
                </div>
            </a>

            <!-- Card 2 -->
            <a href="https://www.w3schools.com/css/default.asp" target="_blank" class="course-card anim-fade-up delay-2">
                <div class="card-thumb-area">
                    <span class="category-tag">Web Design</span>
                    <span class="difficulty-badge diff-beginner">Beginner</span>
                    <img src="./img/course02.jpg" class="card-img" alt="CSS">
                    <div class="card-overlay"></div>
                </div>
                <div class="card-body">
                    <div class="instructor-line">
                        <div class="avatar"></div>
                        <span class="instructor-name">Design Faculty</span>
                        <span class="instructor-rating">★ 4.9</span>
                    </div>
                    <h3 class="course-title">Introduction to CSS3</h3>
                    <p class="course-desc">Learn the fundamentals of styling the web. Grid, Flexbox, and modern CSS variables explained simply.</p>
                    <div class="card-meta">
                        <span class="meta-item"><i data-lucide="clock"></i> 8h</span>
                        <span class="meta-item"><i data-lucide="book-open"></i> 18 lessons</span>
                    </div>
                    <div class="card-footer">
                        <span class="price-free">Free</span>
                        <button class="card-cta">Enroll Now</button>
                    </div>
                </div>
            </a>

            <!-- Card 3 -->
            <a href="https://progate.com/courses/java" target="_blank" class="course-card anim-fade-up delay-3">
                <div class="card-thumb-area">
                    <span class="category-tag">Web Dev</span>
                    <span class="difficulty-badge diff-beginner">Beginner</span>
                    <img src="./img/coursehtml.jpg" class="card-img" alt="HTML">
                    <div class="card-overlay"></div>
                </div>
                <div class="card-body">
                    <div class="instructor-line">
                        <div class="avatar"></div>
                        <span class="instructor-name">Progate Team</span>
                        <span class="instructor-rating">★ 4.7</span>
                    </div>
                    <h3 class="course-title">The Complete Course on HTML5</h3>
                    <p class="course-desc">Start your journey here. Learn semantic HTML and the foundation of every website on the internet.</p>
                    <div class="card-meta">
                        <span class="meta-item"><i data-lucide="clock"></i> 6h</span>
                        <span class="meta-item"><i data-lucide="users"></i> 4.5k</span>
                    </div>
                    <div class="card-footer">
                        <span class="price-free">Free</span>
                        <button class="card-cta">Enroll Now</button>
                    </div>
                </div>
            </a>

            <!-- Card 4 -->
            <a href="https://www.w3schools.com/bootstrap4/default.asp" target="_blank" class="course-card anim-fade-up delay-4">
                <div class="card-thumb-area">
                    <span class="category-tag">Frameworks</span>
                    <span class="difficulty-badge diff-intermediate">Intermediate</span>
                    <img src="./img/coursebtsp.jpg" class="card-img" alt="Bootstrap">
                    <div class="card-overlay"></div>
                </div>
                <div class="card-body">
                    <div class="instructor-line">
                        <div class="avatar"></div>
                        <span class="instructor-name">Tech Faculty</span>
                        <span class="instructor-rating">★ 4.6</span>
                    </div>
                    <h3 class="course-title">Introduction to Bootstrap 4</h3>
                    <p class="course-desc">Build responsive, mobile-first projects on the web with the world’s most popular front-end component library.</p>
                    <div class="card-meta">
                        <span class="meta-item"><i data-lucide="clock"></i> 10h</span>
                        <span class="meta-item"><i data-lucide="book-open"></i> 20 lessons</span>
                    </div>
                    <div class="card-footer">
                        <span class="price-free">Free</span>
                        <button class="card-cta">Enroll Now</button>
                    </div>
                </div>
            </a>

            <!-- Card 5 (Enrolled State Example) -->
            <a href="https://www.udemy.com/course/the-complete-c-programming/" target="_blank" class="course-card anim-fade-up delay-5">
                <div class="card-thumb-area">
                    <span class="category-tag">Web Dev</span>
                    <span class="difficulty-badge diff-advanced">Advanced</span>
                    <img src="./img/courserea.jpg" class="card-img" alt="React">
                    <div class="card-overlay"></div>
                </div>
                <div class="card-body">
                    <div class="instructor-line">
                        <div class="avatar"></div>
                        <span class="instructor-name">Meta Faculty</span>
                        <span class="instructor-rating">★ 4.9</span>
                    </div>
                    <h3 class="course-title">React.js Masterclass</h3>
                    <p class="course-desc">Deep dive into hooks, context API, and high-performance state management with React 18.</p>
                    
                    <!-- Progress Bar for Enrolled -->
                    <div class="progress-container">
                        <div class="progress-bar"><div class="progress-fill" style="width: 68%;"></div></div>
                        <span class="progress-text">68% Complete</span>
                    </div>

                    <div class="card-footer">
                        <span class="price-free">Enrolled</span>
                        <button class="card-cta" style="background: var(--color-accent); color: var(--color-bg-primary);">
                            Continue <i data-lucide="arrow-right" style="width: 14px;"></i>
                        </button>
                    </div>
                </div>
            </a>

            <!-- CATEGORY: PROGRAMMING -->
            <div class="cat-divider" id="prog">
                <h2 class="cat-title">Programming Languages</h2>
                <div class="cat-line"></div>
            </div>

            <!-- Card 6 -->
            <a href="https://www.datacamp.com/tracks/r-programming" target="_blank" class="course-card anim-fade-up delay-6">
                <div class="card-thumb-area">
                    <span class="category-tag">Data Science</span>
                    <span class="difficulty-badge diff-intermediate">Intermediate</span>
                    <img src="./img/course01.jpg" class="card-img" alt="R">
                    <div class="card-overlay"></div>
                </div>
                <div class="card-body">
                    <div class="instructor-line">
                        <div class="avatar"></div>
                        <span class="instructor-name">DataCamp</span>
                        <span class="instructor-rating">★ 4.8</span>
                    </div>
                    <h3 class="course-title">R Programming for Data Science</h3>
                    <p class="course-desc">Master the language of data analysis. Statistical modeling and visualization made professional.</p>
                    <div class="card-meta">
                        <span class="meta-item"><i data-lucide="clock"></i> 20h</span>
                        <span class="meta-item"><i data-lucide="users"></i> 8k</span>
                    </div>
                    <div class="card-footer">
                        <span class="price-free">Free</span>
                        <button class="card-cta">Enroll Now</button>
                    </div>
                </div>
            </a>

            <!-- Card 7 -->
            <a href="https://www.w3schools.com/cpp/default.asp" target="_blank" class="course-card anim-fade-up">
                <div class="card-thumb-area">
                    <span class="category-tag">C++</span>
                    <span class="difficulty-badge diff-advanced">Advanced</span>
                    <img src="./img/coursecpp.jpg" class="card-img" alt="C++">
                    <div class="card-overlay"></div>
                </div>
                <div class="card-body">
                    <div class="instructor-line">
                        <div class="avatar"></div>
                        <span class="instructor-name">CS Faculty</span>
                        <span class="instructor-rating">★ 4.7</span>
                    </div>
                    <h3 class="course-title">Mastering C++ Programming</h3>
                    <p class="course-desc">Performance, memory management, and OOP. The core of high-speed computing.</p>
                    <div class="card-meta">
                        <span class="meta-item"><i data-lucide="clock"></i> 30h</span>
                    </div>
                    <div class="card-footer">
                        <span class="price-free">Free</span>
                        <button class="card-cta">Enroll Now</button>
                    </div>
                </div>
            </a>

            <!-- Card 8 -->
            <a href="https://www.w3schools.com/java/default.asp" target="_blank" class="course-card anim-fade-up">
                <div class="card-thumb-area">
                    <span class="category-tag">Java</span>
                    <span class="difficulty-badge diff-intermediate">Intermediate</span>
                    <img src="./img/coursejava.jpg" class="card-img" alt="Java">
                    <div class="card-overlay"></div>
                </div>
                <div class="card-body">
                    <div class="instructor-line">
                        <div class="avatar"></div>
                        <span class="instructor-name">Java Master</span>
                        <span class="instructor-rating">★ 4.6</span>
                    </div>
                    <h3 class="course-title">Java Enterprise Tutorial</h3>
                    <p class="course-desc">Build robust, scalable applications with one of the most popular enterprise languages.</p>
                    <div class="card-footer">
                        <span class="price-free">Free</span>
                        <button class="card-cta">Enroll Now</button>
                    </div>
                </div>
            </a>

            <!-- CATEGORY: SERVER SIDE -->
            <div class="cat-divider" id="server">
                <h2 class="cat-title">Server Side</h2>
                <div class="cat-line"></div>
            </div>

            <!-- Card 9 -->
            <a href="https://www.w3schools.com/git/default.asp" target="_blank" class="course-card anim-fade-up">
                <div class="card-thumb-area">
                    <span class="category-tag">Backend</span>
                    <span class="difficulty-badge diff-intermediate">Intermediate</span>
                    <img src="./img/course05.jpg" class="card-img" alt="PHP">
                    <div class="card-overlay"></div>
                </div>
                <div class="card-body">
                    <div class="instructor-line">
                        <div class="avatar"></div>
                        <span class="instructor-name">Server Faculty</span>
                        <span class="instructor-rating">★ 4.5</span>
                    </div>
                    <h3 class="course-title">PHP Tips & Techniques</h3>
                    <p class="course-desc">Optimize your backend with modern PHP practices and server-side security protocols.</p>
                    <div class="card-footer">
                        <span class="price-free">Free</span>
                        <button class="card-cta">Enroll Now</button>
                    </div>
                </div>
            </a>

            <!-- Card 10 -->
            <a href="https://www.w3schools.com/git/default.asp" target="_blank" class="course-card anim-fade-up">
                <div class="card-thumb-area">
                    <span class="category-tag">Database</span>
                    <span class="difficulty-badge diff-beginner">Beginner</span>
                    <img src="./img/coursesql.jpg" class="card-img" alt="SQL">
                    <div class="card-overlay"></div>
                </div>
                <div class="card-body">
                    <div class="instructor-line">
                        <div class="avatar"></div>
                        <span class="instructor-name">Data Expert</span>
                        <span class="instructor-rating">★ 4.9</span>
                    </div>
                    <h3 class="course-title">SQL Fundamentals</h3>
                    <p class="course-desc">Relational databases explained. Master queries, joins, and database architecture from scratch.</p>
                    <div class="card-footer">
                        <span class="price-free">Free</span>
                        <button class="card-cta">Enroll Now</button>
                    </div>
                </div>
            </a>

        </div>
    </main>

    <?php include 'footer.php'?>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Intersection Observer for scroll animations
        const observerOptions = { threshold: 0.1 };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('anim-fade-up');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.course-card').forEach(card => observer.observe(card));

        // Sticky Filter Scroll Effect
        window.addEventListener('scroll', () => {
            const filterBar = document.querySelector('.filter-bar');
            if (window.scrollY > 100) {
                filterBar.style.background = 'rgba(13, 17, 23, 0.98)';
                filterBar.style.boxShadow = '0 10px 30px -10px rgba(0,0,0,0.5)';
            } else {
                filterBar.style.background = 'rgba(13, 17, 23, 0.90)';
                filterBar.style.boxShadow = 'none';
            }
        });
    </script>
</body>
</html>
