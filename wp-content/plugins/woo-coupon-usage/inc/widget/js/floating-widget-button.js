jQuery(document).ready(function($) {
    var popup = $('#wcusage-floating-popup');
    var button = $('#wcusage-floating-btn');
    var isLoaded = false;
    var assetsLoaded = false;

    // Get animation speed from settings
    function getAnimationSpeed() {
        if (typeof wcusage_floating_widget !== 'undefined' && wcusage_floating_widget.animation_speed) {
            return parseInt(wcusage_floating_widget.animation_speed);
        }
        return 200; // default
    }
    
    // Toggle popup
    button.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var animationSpeed = getAnimationSpeed();
        
        if (popup.is(':visible')) {
            if (animationSpeed > 0) {
                popup.fadeOut(animationSpeed);
            } else {
                popup.hide();
            }
        } else {
            if (animationSpeed > 0) {
                popup.fadeIn(animationSpeed);
            } else {
                popup.show();
            }
            
            if (!isLoaded) {
                loadFullWidgetAssets();
                isLoaded = true;
            }
        }
    });
    
    // Close popup when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.wcusage-floating-widget').length) {
            var animationSpeed = getAnimationSpeed();
            if (animationSpeed > 0) {
                popup.fadeOut(animationSpeed);
            } else {
                popup.hide();
            }
        }
    });
    
    // Load full widget assets when popup is first opened
    function loadFullWidgetAssets() {

        // Show loading state
        var loadingHtml = '<div class="wcusage-popup-header">' +
                         '<h3 class="wcusage-popup-title">' + (wcusage_floating_widget.essential_settings.popup_title || 'Loading...') + '</h3>' +
                         '<button class="wcusage-popup-close" id="wcusage-popup-close">&times;</button>' +
                         '</div>' +
                         '<div class="wcusage-popup-content" id="wcusage-popup-content">' +
                         '<div class="wcusage-popup-loading">' + wcusage_floating_widget.loading_text + '</div>' +
                         '</div>';
        popup.html(loadingHtml);
        
        // Bind close button immediately
        $('#wcusage-popup-close').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var animationSpeed = getAnimationSpeed();
            if (animationSpeed > 0) {
                popup.fadeOut(animationSpeed);
            } else {
                popup.hide();
            }
        });
        
        // Load main widget CSS first
        loadCSS(wcusage_floating_widget.plugin_url + 'inc/widget/css/floating-widget.css', 'wcusage-floating-widget-full');
        
        // Load main widget JS and initialize popup
        loadJS(wcusage_floating_widget.plugin_url + 'inc/widget/js/floating-widget-popup.js', 'wcusage-floating-widget-popup', function() {
            console.log('Main popup JS loaded, initializing...');
            // Initialize popup functionality after loading
            if (typeof window.initializeFloatingWidgetPopup === 'function') {
                window.initializeFloatingWidgetPopup();
                
                // Load premium assets AFTER popup is initialized and content is loaded
                setTimeout(function() {
                    loadPremiumAssets();
                }, 1000); // Give popup time to load content
            }
        });
        
        assetsLoaded = true;
    }
    
    // Load premium assets after popup content is ready
    function loadPremiumAssets() {
        console.log('Loading premium assets...');
        
        // Only load premium assets if we're premium and popup content exists
        if (wcusage_floating_widget.is_premium !== '1') {
            console.log('Not premium, skipping premium assets');
            return;
        }
        
        // Check if popup content has been loaded (not just loading screen)
        if ($('#wcusage-popup-content .wcusage-popup-loading').length > 0) {
            console.log('Popup still loading, retrying premium assets in 500ms');
            setTimeout(loadPremiumAssets, 500);
            return;
        }
        
        var premiumAssetsLoaded = 0;
        var totalPremiumAssets = 0;
        
        // Count how many premium assets we need to load
        if (wcusage_floating_widget.social_enabled === '1') totalPremiumAssets += 2; // CSS + JS
        if (wcusage_floating_widget.qr_enabled === '1') totalPremiumAssets += 2; // QR lib + widget JS
        
        if (totalPremiumAssets === 0) {
            console.log('No premium assets to load');
            return;
        }
        
        function onPremiumAssetLoaded() {
            premiumAssetsLoaded++;
            console.log('Premium asset loaded:', premiumAssetsLoaded, '/', totalPremiumAssets);
            
            if (premiumAssetsLoaded >= totalPremiumAssets) {
                console.log('All premium assets loaded, initializing premium features');
                initializePremiumFeatures();
            }
        }
        
        // Load social sharing assets if enabled
        if (wcusage_floating_widget.social_enabled === '1') {
            console.log('Loading social sharing assets...');
            
            loadCSS(wcusage_floating_widget.plugin_url + 'inc/widget/css/widget-social-sharing__premium_only.css', 'wcusage-widget-social-sharing', function() {
                console.log('Social CSS loaded');
                onPremiumAssetLoaded();
            });
            
            loadJS(wcusage_floating_widget.plugin_url + 'inc/widget/js/widget-social-sharing__premium_only.js', 'wcusage-widget-social-sharing', function() {
                console.log('Social JS loaded');
                onPremiumAssetLoaded();
            });
        }
        
        // Load QR code assets if enabled
        if (wcusage_floating_widget.qr_enabled === '1') {
            console.log('Loading QR code assets...');
            
            // Try multiple QR code library paths
            var qrCodePaths = [
                wcusage_floating_widget.plugin_url + 'js/qrcode.min.js',
                wcusage_floating_widget.plugin_url + 'assets/js/qrcode.min.js',
                wcusage_floating_widget.plugin_url + 'inc/assets/js/qrcode.min.js',
                'https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js'
            ];
            
            loadQRCodeLibraryWithFallback(qrCodePaths, 0, function() {
                console.log('QR library loaded');
                onPremiumAssetLoaded();
                
                // Load QR widget functionality after library
                loadJS(wcusage_floating_widget.plugin_url + 'inc/widget/js/widget-qr-codes__premium_only.js', 'wcusage-widget-qr-codes', function() {
                    console.log('QR widget JS loaded');
                    onPremiumAssetLoaded();
                });
            });
        }
    }
    
    // Load QR code library with fallback paths
    function loadQRCodeLibraryWithFallback(paths, index, callback) {
        if (index >= paths.length) {
            console.error('All QR code library paths failed');
            callback(); // Still call callback to continue loading
            return;
        }
        
        var currentPath = paths[index];
        console.log('Trying QR library path:', currentPath);
        
        loadJS(currentPath, 'wcusage-qrcode-lib', callback, function() {
            console.log('QR library failed from:', currentPath);
            loadQRCodeLibraryWithFallback(paths, index + 1, callback);
        });
    }
    
    // Initialize premium features after assets are loaded
    function initializePremiumFeatures() {

        // Wait a bit more to ensure DOM is ready
        setTimeout(function() {
            // Initialize social sharing if available
            if (typeof window.initializeWidgetSocialSharing === 'function') {
                try {
                    window.initializeWidgetSocialSharing();
                } catch (e) {
                    console.error('Error initializing social sharing:', e);
                }
            } else {
                console.log('Social sharing function not available');
            }
            
            // Initialize QR code functionality if available
            if (typeof window.initializeWidgetQRCode === 'function') {
                try {
                    window.initializeWidgetQRCode();
                } catch (e) {
                    console.error('Error initializing QR code:', e);
                }
            } else {
                console.log('QR code function not available');
            }
        }, 200);
    }
    
    // Enhanced utility function to load CSS with callback
    function loadCSS(url, id, callback) {
        if (document.getElementById(id)) {
            if (callback) callback();
            return; // Already loaded
        }
        
        var link = document.createElement('link');
        link.id = id;
        link.rel = 'stylesheet';
        link.href = url;
        link.onload = function() {
            if (callback) callback();
        };
        link.onerror = function() {
            if (callback) callback();
        };
        document.head.appendChild(link);
    }
    
    // Enhanced utility function to load JS with error callback
    function loadJS(url, id, callback, errorCallback) {
        if (document.getElementById(id)) {
            if (callback) callback();
            return; // Already loaded
        }
        
        var script = document.createElement('script');
        script.id = id;
        script.src = url;
        script.onload = function() {
            if (callback) callback();
        };
        script.onerror = function() {
            console.log('JS failed to load:', id);
            if (errorCallback) {
                errorCallback();
            } else if (callback) {
                callback(); // Fallback to success callback to continue loading
            }
        };
        document.head.appendChild(script);
    }
});
