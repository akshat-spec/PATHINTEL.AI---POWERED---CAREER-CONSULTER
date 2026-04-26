<?php
// Initialize the session
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'header.php'?>

<main>
    <!-- HERO SECTION -->
    <!-- DESIGN DECISION: Asymmetric layout with large serif typography to create an editorial feel. -->
    <section id="home" style="position: relative; padding: var(--space-96) 0; min-height: 80vh; display: flex; align-items: center;">
        <div class="parallax-bg" style="background-image: url('./img/main_hero_bg.png');"></div>
        <div class="container" style="position: relative; z-index: 2;">
            <div style="max-width: 640px;" class="reveal stagger-1">
                <h1 style="font-size: clamp(3rem, 8vw, 5rem); line-height: 1; margin-bottom: var(--space-32); color: var(--color-text-primary);">
                    Precision<br><span style="color: var(--color-accent);">Career</span> Guidance.
                </h1>
                
                <?php if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true): ?>
                    <p style="font-size: 1.1rem; color: var(--color-text-secondary); margin-bottom: var(--space-48); max-width: 480px;">
                        Discover your potential. Use our AI-driven models to navigate your path after Engineering.
                    </p>
                    <a href="login.php" class="btn-primary">
                        Begin Analysis <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                    </a>
                <?php else: ?>
                    <p style="font-size: 1.1rem; color: var(--color-text-secondary); margin-bottom: var(--space-48); max-width: 480px;">
                        Welcome back, <?php echo htmlspecialchars($_SESSION["username"]); ?>. Ready to explore your next career milestone?
                    </p>
                    <a href="http://localhost:5000/" class="btn-primary">
                        Enter AI Hub <i data-lucide="layout-dashboard" style="width: 18px; height: 18px;"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- SERVICES / FEATURES -->
    <!-- DESIGN DECISION: Floating modules with intentional white space and subtle hover drift. -->
    <section id="services" style="padding: var(--space-96) 0;">
        <div class="container">
            <div style="margin-bottom: var(--space-64);" class="reveal stagger-1">
                <span style="text-transform: uppercase; letter-spacing: 0.2em; font-size: 0.7rem; color: var(--color-accent); font-weight: 600;">Systems & Logic</span>
                <h2 style="font-size: 2.5rem; margin-top: var(--space-8);">The PathIntel Framework</h2>
            </div>

            <div class="asymmetric-grid">
                <!-- Feature 1 -->
                <div class="float-card reveal stagger-1">
                    <i data-lucide="radar" style="color: var(--color-accent); margin-bottom: var(--space-24); width: 32px; height: 32px;"></i>
                    <h3>Career Prediction</h3>
                    <p style="color: var(--color-text-secondary); font-size: 0.9rem; margin-top: var(--space-16);">
                        Multi-vector analysis of your skills and interests to project the most viable professional roles.
                    </p>
                    <a href="<?php echo (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) ? 'login.php' : 'http://localhost:5000/'; ?>" 
                       style="display: inline-block; margin-top: var(--space-24); font-size: 0.75rem; font-weight: 600; color: var(--color-accent); text-transform: uppercase; letter-spacing: 0.1em;">
                       Explore Path <i data-lucide="arrow-up-right" style="width: 14px; height: 14px; vertical-align: middle;"></i>
                    </a>
                </div>

                <!-- Feature 2 -->
                <div class="float-card reveal stagger-2">
                    <i data-lucide="network" style="color: var(--color-accent); margin-bottom: var(--space-24); width: 32px; height: 32px;"></i>
                    <h3>Knowledge Network</h3>
                    <p style="color: var(--color-text-secondary); font-size: 0.9rem; margin-top: var(--space-16);">
                        A curated repository of research, study materials, and industry insights tailored to your field.
                    </p>
                    <a href="blog.php" 
                       style="display: inline-block; margin-top: var(--space-24); font-size: 0.75rem; font-weight: 600; color: var(--color-accent); text-transform: uppercase; letter-spacing: 0.1em;">
                       Access Intel <i data-lucide="arrow-up-right" style="width: 14px; height: 14px; vertical-align: middle;"></i>
                    </a>
                </div>

                <!-- Feature 3 -->
                <div class="float-card reveal stagger-3">
                    <i data-lucide="graduation-cap" style="color: var(--color-accent); margin-bottom: var(--space-24); width: 32px; height: 32px;"></i>
                    <h3>Online Courses</h3>
                    <p style="color: var(--color-text-secondary); font-size: 0.9rem; margin-top: var(--space-16);">
                        Strategic learning paths from global institutions to bridge your specific skill gaps.
                    </p>
                    <a href="courses.php" 
                       style="display: inline-block; margin-top: var(--space-24); font-size: 0.75rem; font-weight: 600; color: var(--color-accent); text-transform: uppercase; letter-spacing: 0.1em;">
                       View Courses <i data-lucide="arrow-up-right" style="width: 14px; height: 14px; vertical-align: middle;"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ABOUT SECTION -->
    <!-- DESIGN DECISION: Pull-quote style layout to emphasize the purposeful, academic nature of the platform. -->
    <section id="about" style="padding: var(--space-96) 0; background: var(--color-bg-surface);">
        <div class="container">
            <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: var(--space-64); align-items: center;">
                <div class="reveal stagger-1">
                    <h2 style="font-size: 3rem; line-height: 1.1;">Our<br>Philosophy</h2>
                </div>
                <div class="reveal stagger-2">
                    <p class="font-serif" style="font-size: 1.5rem; color: var(--color-text-primary); border-left: 2px solid var(--color-accent); padding-left: var(--space-32);">
                        "Education seekers deserve a personalized experience that transcends generic advice. We empower decisions through structured data and AI analysis."
                    </p>
                    <p style="margin-top: var(--space-32); color: var(--color-text-secondary); font-size: 1rem; line-height: 1.8;">
                        PathIntel.ai is built on the principle that precision matters. We provide access to detailed information on career choices, exams, and placement statistics, all within a disciplined academic environment.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACT CTA -->
    <section id="contact" style="padding: var(--space-96) 0; text-align: center;">
        <div class="container">
            <div class="float-card" style="padding: var(--space-64); border-color: var(--color-accent-glow);">
                <h2 class="font-serif" style="font-size: 2.5rem; margin-bottom: var(--space-24);">Ready to calibrate your career?</h2>
                <p style="color: var(--color-text-secondary); margin-bottom: var(--space-48);">Connect with our experts or start your AI analysis today.</p>
                <div style="display: flex; gap: var(--space-16); justify-content: center;">
                    <a href="contact.php" class="btn-primary">Contact Us</a>
                    <a href="register.php" class="btn-primary" style="border-color: var(--color-border-bright); color: var(--color-text-primary);">Join Platform</a>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    // Initialize icons
    lucide.createIcons();

    // Subtle Parallax Effect
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const parallax = document.querySelector('.parallax-bg');
        if (parallax) {
            parallax.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
    });

    // Intersection Observer for Reveal Animations
    const observerOptions = {
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = "1";
                entry.target.classList.add('reveal');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>

<?php include 'footer.php'?>
</html>
