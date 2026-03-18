/**
 * Dark Mode Toggle Functionality for Affiliate Dashboard
 */
(function($) {
    'use strict';

    // Wait for DOM to be ready
    $(document).ready(function() {
        
        // Get dark mode settings from localized script
        const darkModeEnabled = typeof wcusage_dark_mode !== 'undefined' && wcusage_dark_mode.enabled === '1';
        const darkModeDefault = typeof wcusage_dark_mode !== 'undefined' && wcusage_dark_mode.default === '1';
        
        // Only proceed if dark mode is enabled
        if (!darkModeEnabled) {
            return;
        }

        const $dashboard = $('.wcu-dash-coupon-area');
        const $toggle = $('#wcu-dark-mode-toggle');
        const darkModeClass = 'wcu-dark-mode';
        const storageKey = 'wcu_dark_mode_preference';

        /**
         * Check if user prefers dark mode
         */
        function getUserPreference() {
            // First check localStorage for user preference
            const stored = localStorage.getItem(storageKey);
            if (stored !== null) {
                return stored === 'true';
            }
            
            // If no preference stored, use admin default setting
            return darkModeDefault;
        }

        /**
         * Apply dark mode to dashboard
         */
        function enableDarkMode() {
            $dashboard.addClass(darkModeClass);
            localStorage.setItem(storageKey, 'true');
            updateToggleButton(true);
        }

        /**
         * Remove dark mode from dashboard
         */
        function disableDarkMode() {
            $dashboard.removeClass(darkModeClass);
            localStorage.setItem(storageKey, 'false');
            updateToggleButton(false);
        }

        /**
         * Update toggle button appearance
         */
        function updateToggleButton(isDark) {
            const $icon = $toggle.find('i');
            
            if (isDark) {
                $icon.removeClass('fa-moon').addClass('fa-sun');
                $toggle.attr('aria-pressed', 'true');
                $toggle.attr('title', wcusage_dark_mode.text_light_mode || 'Light Mode');
            } else {
                $icon.removeClass('fa-sun').addClass('fa-moon');
                $toggle.attr('aria-pressed', 'false');
                $toggle.attr('title', wcusage_dark_mode.text_dark_mode || 'Dark Mode');
            }
        }

        /**
         * Toggle dark mode
         */
        function toggleDarkMode() {
            if ($dashboard.hasClass(darkModeClass)) {
                disableDarkMode();
            } else {
                enableDarkMode();
            }
        }

        /**
         * Initialize dark mode based on user preference
         */
        function initDarkMode() {
            const shouldBeDark = getUserPreference();
            
            if (shouldBeDark) {
                enableDarkMode();
            } else {
                disableDarkMode();
            }
        }

        // Initialize on page load
        initDarkMode();

        // Handle toggle button click
        $toggle.on('click', function(e) {
            e.preventDefault();
            toggleDarkMode();
        });

        // Optional: Listen for system theme changes
        if (window.matchMedia) {
            const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            
            // Only apply system preference if user hasn't set a manual preference
            if (localStorage.getItem(storageKey) === null) {
                if (darkModeMediaQuery.matches && !darkModeDefault) {
                    // System prefers dark but admin default is light
                    // Don't auto-switch, respect admin default
                } else if (!darkModeMediaQuery.matches && darkModeDefault) {
                    // System prefers light but admin default is dark
                    // Don't auto-switch, respect admin default
                }
            }
        }

        // Add keyboard accessibility
        $toggle.on('keydown', function(e) {
            // Enter or Space key
            if (e.which === 13 || e.which === 32) {
                e.preventDefault();
                toggleDarkMode();
            }
        });

    });

})(jQuery);
