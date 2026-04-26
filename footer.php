    <!-- FOOTER -->
    <!-- DESIGN DECISION: Minimal, elegant footer with intentional white space and clear brand identity. -->
    <footer style="padding: var(--space-64) 0; border-top: 1px solid var(--color-border); background: var(--color-bg-base);">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--space-32);">
                <div>
                    <a href="main.php" class="font-serif" style="font-size: 1.5rem; color: var(--color-accent); text-transform: lowercase; letter-spacing: -1px;">PathIntel.ai</a>
                    <p style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: var(--space-8);">Precision Career Calibration Systems</p>
                </div>
                
                <div style="display: flex; gap: var(--space-32);">
                    <a href="main.php" style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-secondary);">Home</a>
                    <a href="contact.php" style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-secondary);">Contact</a>
                    <a href="login.php" style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-secondary);">Portal</a>
                </div>

                <div style="font-size: 0.7rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                    &copy; <?php echo date("Y"); ?> PathIntel.ai | Built for Tomorrow
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="js/jquery.min.js"></script>
    <script src="js/main.js"></script>
    
    <script>
        // Ensure Lucide icons are initialized on all pages
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</body>