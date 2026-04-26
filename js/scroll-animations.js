/**
 * Scroll-Triggered Animations
 * Intersection Observer API for performance-optimized scroll effects
 * < 50 lines as specified
 */

(function () {
    'use strict';

    // Configuration
    const CONFIG = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    // Create Intersection Observer
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Add Animate.css classes
                entry.target.classList.add('animate__animated', 'animate__fadeInUp');

                // Add visible class for CSS transitions
                entry.target.classList.add('visible');

                // Unobserve after animation (performance optimization)
                observer.unobserve(entry.target);
            }
        });
    }, CONFIG);

    // Initialize on DOM ready
    function initScrollAnimations() {
        // Target elements for animation
        const selectors = [
            '.course',
            '.feature',
            '.list li',
            '.glass-card',
            '.section-header',
            '.about-img'
        ];

        // Observe all matching elements
        selectors.forEach(selector => {
            document.querySelectorAll(selector).forEach(element => {
                element.classList.add('animate-on-scroll');
                observer.observe(element);
            });
        });
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScrollAnimations);
    } else {
        initScrollAnimations();
    }

})();
