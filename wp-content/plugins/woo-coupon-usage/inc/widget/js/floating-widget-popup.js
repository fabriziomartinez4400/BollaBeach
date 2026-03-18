// Floating Widget Popup Functionality - Loaded on demand
(function($) {
    'use strict';
    
    var popup = $('#wcusage-floating-popup');
    var closeBtn = null;
    var isContentLoaded = false;
    var fullSettings = null;

    // Initialize popup functionality
    function initializeFloatingWidgetPopup() {
        // Load Font Awesome
        loadFontAwesome();
        
        // Load popup content
        loadPopupContent();
    }
    
    // Load Font Awesome when widget is opened
    var fontAwesomeLoaded = false;
    function loadFontAwesome() {
        if (!fontAwesomeLoaded) {
            var fontAwesomeUrl = '';
            
            if (typeof wcusage_floating_widget !== 'undefined' && wcusage_floating_widget.plugin_url) {
                fontAwesomeUrl = wcusage_floating_widget.plugin_url + 'fonts/font-awesome/css/all.min.css';
            }
            
            if (fontAwesomeUrl) {
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = fontAwesomeUrl;
                document.head.appendChild(link);
                fontAwesomeLoaded = true;
            }
        }
    }
    
    // Load captcha script dynamically for registration form in the widget
    function loadCaptchaScript(captchaData) {
        if (!captchaData || !captchaData.type) {
            return;
        }
        
        if (captchaData.type === 'recaptcha') {
            // Check if reCAPTCHA script is already loaded
            if (typeof grecaptcha !== 'undefined') {
                // Script already loaded, just render the widget
                renderRecaptcha();
                return;
            }
            var script = document.createElement('script');
            script.src = 'https://www.google.com/recaptcha/api.js?onload=wcusageRecaptchaCallback&render=explicit';
            script.async = true;
            script.defer = true;
            window.wcusageRecaptchaCallback = function() {
                renderRecaptcha();
            };
            document.head.appendChild(script);
        } else if (captchaData.type === 'turnstile') {
            // Check if Turnstile script is already loaded
            if (typeof turnstile !== 'undefined') {
                // Script already loaded, just render the widget
                renderTurnstile(captchaData.site_key);
                return;
            }
            var script = document.createElement('script');
            script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=wcusageTurnstileCallback&render=explicit';
            script.async = true;
            script.defer = true;
            window.wcusageTurnstileCallback = function() {
                renderTurnstile(captchaData.site_key);
            };
            document.head.appendChild(script);
        }
    }
    
    // Render reCAPTCHA widgets in the popup
    function renderRecaptcha() {
        if (typeof grecaptcha === 'undefined') {
            return;
        }
        $('.wcusage-floating-popup .g-recaptcha').each(function() {
            var el = this;
            // Only render if not already rendered
            if ($(el).children().length === 0) {
                grecaptcha.render(el, {
                    'sitekey': $(el).data('sitekey')
                });
            }
        });
    }
    
    // Render Turnstile widgets in the popup
    function renderTurnstile(siteKey) {
        if (typeof turnstile === 'undefined') {
            return;
        }
        $('.wcusage-floating-popup .cf-turnstile').each(function() {
            var el = this;
            // Only render if not already rendered
            if ($(el).children().length === 0) {
                turnstile.render(el, {
                    sitekey: siteKey || $(el).data('sitekey')
                });
            }
        });
    }
    
    // Load popup content via AJAX
    function loadPopupContent() {

        // Update loading state in existing popup structure
        $('#wcusage-popup-content').html('<div class="wcusage-popup-loading">' + wcusage_floating_widget.loading_text + '</div>');
        
        $.ajax({
            url: wcusage_floating_widget.ajax_url,
            type: 'POST',
            data: {
                action: 'wcusage_floating_widget_content',
                security: wcusage_floating_widget.nonce
            },
            success: function(response) {
                if (response && response.success) {
                    // Create the full popup structure with header and content
                    var popupHtml = '<div class="wcusage-popup-header">' +
                                   '<h3 class="wcusage-popup-title">' + (response.data.settings ? response.data.settings.popup_title : wcusage_floating_widget.essential_settings.popup_title) + '</h3>' +
                                   '<button class="wcusage-popup-close" id="wcusage-popup-close">&times;</button>' +
                                   '</div>' +
                                   '<div class="wcusage-popup-content" id="wcusage-popup-content">' +
                                   response.data.content +
                                   '</div>';
                    
                    popup.html(popupHtml);
                    
                    // Store full settings for later use
                    if (response.data.settings) {
                        fullSettings = response.data.settings;
                        wcusage_floating_widget.settings = fullSettings;
                    }
                    
                    bindPopupEvents();
                    initializeTabs();
                    isContentLoaded = true;
                    
                    // Initialize premium features if available
                    initializePremiumFeatures();
                    
                    // Load captcha scripts if registration form is displayed
                    if (response.data.captcha) {
                        loadCaptchaScript(response.data.captcha);
                    }
                } else {
                    var errorMsg = response && response.data ? response.data : wcusage_floating_widget.error_text;
                    var errorHtml = '<div class="wcusage-popup-header">' +
                                   '<h3 class="wcusage-popup-title">Error</h3>' +
                                   '<button class="wcusage-popup-close" id="wcusage-popup-close">&times;</button>' +
                                   '</div>' +
                                   '<div class="wcusage-popup-content">' +
                                   '<div class="wcusage-widget-error">' + errorMsg + '</div>' +
                                   '</div>';
                    popup.html(errorHtml);
                    bindCloseButton();
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX error loading popup content');
                var errorHtml = '<div class="wcusage-popup-header">' +
                               '<h3 class="wcusage-popup-title">Error</h3>' +
                               '<button class="wcusage-popup-close" id="wcusage-popup-close">&times;</button>' +
                               '</div>' +
                               '<div class="wcusage-popup-content">' +
                               '<div class="wcusage-widget-error">' + wcusage_floating_widget.error_text + '</div>' +
                               '</div>';
                popup.html(errorHtml);
                bindCloseButton();
            }
        });
    }
    
    // Bind close button
    function bindCloseButton() {
        closeBtn = $('#wcusage-popup-close');
        closeBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var animationSpeed = getAnimationSpeed();
            if (animationSpeed > 0) {
                popup.fadeOut(animationSpeed);
            } else {
                popup.hide();
            }
        });
    }
    
    // Initialize premium features
    function initializePremiumFeatures() {
        // Wait a bit for premium scripts to load
        setTimeout(function() {
            // Initialize social sharing if function is available
            if (typeof window.initializeWidgetSocialSharing === 'function') {
                window.initializeWidgetSocialSharing();
            }
            
            // Initialize QR code functionality if function is available
            if (typeof window.initializeWidgetQRCode === 'function') {
                window.initializeWidgetQRCode();
            }
        }, 500);
    }
    
    // Helper function to get active coupon selector
    function getActiveCouponSelector() {
        var mainSelector = $('#wcusage-widget-coupon-select-main');
        var legacySelector = $('#wcusage-widget-coupon-select');
        return mainSelector.length ? mainSelector : legacySelector;
    }
    
    // Helper function to handle coupon change
    function handleCouponChange($selector) {
        var couponId = $selector.val();
        var selectedOption = $selector.find('option:selected');
        var couponCode = selectedOption.data('coupon-code');
        
        if (couponId) {
            updateCouponCodeDisplay(couponCode);
            loadCouponStats(couponId);
            updateLinksTab(couponId);
            updateReferralsTab(couponId);
            updatePayoutsTab(couponId);
            updateCreativesTab(couponId);
            updateDashboardLink(couponId);
        }
    }
    
    // Helper function to handle copy referral URL
    function handleCopyReferralUrl(e) {
        e.preventDefault();
        e.stopPropagation();
        var url = $(this).data('url');
        var $button = $(this);

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function() {
                showButtonMessage($button, wcusage_floating_widget.copy_success_text, 'success');
            }).catch(function(err) {
                console.log('Clipboard API failed:', err);
                fallbackCopyTextToClipboard(url, $button);
            });
        } else {
            fallbackCopyTextToClipboard(url, $button);
        }
    }
    
    // Helper function to handle short URL generation
    function handleGenerateShortUrl(e) {
        e.preventDefault();
        e.stopPropagation();
        var url = $(this).data('url');
        var $button = $(this);
        
        if (!url) {
            url = $('#wcusage-generated-url').text();
        }
        
        if (!url) {
            console.log('No URL available for short URL generation');
            return;
        }
        
        generateShortUrl(url, $button);
    }

    // Bind events for popup content
    function bindPopupEvents() {

        // Bind close button
        bindCloseButton();
        
        // Check if this is a registration form instead of dashboard
        if ($('.wcusage-widget-registration-form').length > 0) {
            bindRegistrationFormEvents();
            return;
        }
        
        // Initialize tabs
        initializeTabs();

        // Coupon code copy button
        $('.wcusage-widget-coupon-copy-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var couponCode = $(this).data('coupon-code');
            var $button = $(this);
            
            if (!$button.data('original-icon')) {
                $button.data('original-icon', $button.find('.wcusage-copy-icon').text());
            }
                        
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(couponCode).then(function() {
                    showCouponCopyMessage($button, 'success');
                }).catch(function(err) {
                    console.log('Clipboard API failed:', err);
                    fallbackCopyCouponCode(couponCode, $button);
                });
            } else {
                fallbackCopyCouponCode(couponCode, $button);
            }
        });
        
        // Consolidated coupon selector change handlers
        $(document).on('change', '#wcusage-widget-coupon-select-main, #wcusage-widget-coupon-select', function() {
            handleCouponChange($(this));
        });
        
        // Initialize URL field with current page URL
        initializeUrlField();
        
        // Custom page URL change
        $(document).on('input', '.wcusage-custom-page-url', function() {
            updateReferralUrl();
        });
        
        // Consolidated copy referral URL handlers
        $(document).on('click', '.wcusage-copy-referral-url', handleCopyReferralUrl);
        
        // Consolidated generate short URL handlers
        $(document).on('click', '.wcusage-generate-short-url', handleGenerateShortUrl);
    }
    
    // Get animation speed from settings
    function getAnimationSpeed() {
        if (typeof wcusage_floating_widget !== 'undefined' && wcusage_floating_widget.animation_speed) {
            return parseInt(wcusage_floating_widget.animation_speed);
        }
        return 200; // default
    }
    
    // Initialize tab functionality
    function initializeTabs() {
        // Set first visible tab as active
        var $visibleTabs = $('.wcusage-widget-tab:visible');
        var $visibleTabContents = $('.wcusage-widget-tab-content');
        
        // Remove all active classes first
        $visibleTabs.removeClass('active');
        $visibleTabContents.removeClass('active');
        
        // Set first visible tab and its content as active
        if ($visibleTabs.length > 0) {
            var $firstTab = $visibleTabs.first();
            $firstTab.addClass('active');
            
            var targetTab = $firstTab.data('tab');
            $('#' + targetTab).addClass('active');
        }
        
        // Tab click handlers
        $('.wcusage-widget-tab').on('click', function(e) {
            e.preventDefault();
            var targetTab = $(this).data('tab');
            
            // Remove active class from all tabs and content
            $('.wcusage-widget-tab').removeClass('active');
            $('.wcusage-widget-tab-content').removeClass('active');
            
            // Add active class to clicked tab and corresponding content
            $(this).addClass('active');
            $('#' + targetTab).addClass('active');
        });
    }
    
    // Bind events for registration form
    function bindRegistrationFormEvents() {
        // Handle any specific registration form events here
        $('.wcusage-widget-registration-form form').on('submit', function(e) {
            console.log('Registration form submitted');
        });
    }
    
    // [Include all the other helper functions from floating-widget.js here]
    // This includes all the coupon handling, URL generation, copy functions, etc.
    
    // Initialize URL field with current page URL
    function initializeUrlField() {
        if (typeof wcusage_floating_widget !== 'undefined' && wcusage_floating_widget.current_page_url) {
            $('.wcusage-custom-page-url').val(wcusage_floating_widget.current_page_url);
            // Trigger initial URL generation
            updateReferralUrl();
        }
    }
    
    // Update referral URL when custom page URL changes
    function updateReferralUrl() {
        var customUrl = $('.wcusage-custom-page-url').val();
        var couponSelect = $('#wcusage-widget-coupon-select-main');
        if (!couponSelect.length) {
            couponSelect = $('#wcusage-widget-coupon-select');
        }
        var couponId = couponSelect.length ? couponSelect.val() : null;
        
        // If no coupon selector, try to get from single coupon data
        if (!couponId) {
            var singleCouponData = $('#wcusage-single-coupon-data');
            if (singleCouponData.length) {
                couponId = singleCouponData.data('coupon-id');
            }
        }
        
        if (customUrl && couponId) {
            $.ajax({
                url: wcusage_floating_widget.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcusage_floating_widget_generate_url',
                    page_url: customUrl,
                    coupon_id: couponId,
                    security: wcusage_floating_widget.nonce
                },
                success: function(response) {
                    if (response && response.success) {
                        $('#wcusage-generated-url').text(response.data.url);
                        $('.wcusage-copy-referral-url').data('url', response.data.url);
                        $('.wcusage-generate-short-url').data('url', response.data.url);
                        
                        // Trigger event for social sharing to update
                        $(document).trigger('urlGenerated', { url: response.data.url });
                        
                        // Also trigger the widget social function directly if available
                        if (typeof window.updateWidgetSocialLinksWithUrl === 'function') {
                            window.updateWidgetSocialLinksWithUrl(response.data.url);
                        }
                        
                        // Update QR code if it's visible
                        if (typeof window.updateQRCodeIfVisible === 'function') {
                            window.updateQRCodeIfVisible();
                        }
                    }
                },
                error: function() {
                    console.log('Failed to update referral URL');
                    generateFallbackUrl(customUrl, couponId);
                }
            });
        } else if (customUrl) {
            generateFallbackUrl(customUrl, couponId);
        }
    }

    // Generate fallback URL when AJAX fails
    function generateFallbackUrl(customUrl, couponId) {
        var urlPrefix = (typeof wcusage_floating_widget !== 'undefined' && wcusage_floating_widget.url_prefix) ? 
                       wcusage_floating_widget.url_prefix : 'coupon';
        var couponCode = 'YOUR_COUPON_CODE';
        
        // Try to get coupon code from data attributes first
        if (couponId) {
            var selectedOption = $('#wcusage-widget-coupon-select-main option[value="' + couponId + '"]');
            if (!selectedOption.length) {
                selectedOption = $('#wcusage-widget-coupon-select option[value="' + couponId + '"]');
            }
            if (selectedOption.length && selectedOption.data('coupon-code')) {
                couponCode = selectedOption.data('coupon-code');
            }
        } else {
            // Check for single coupon data
            var singleCouponData = $('#wcusage-single-coupon-data');
            if (singleCouponData.length && singleCouponData.data('coupon-code')) {
                couponCode = singleCouponData.data('coupon-code');
            }
        }
        
        // If we have a valid coupon code, generate the URL directly
        if (couponCode !== 'YOUR_COUPON_CODE') {
            var separator = customUrl.indexOf('?') > -1 ? '&' : '?';
            var fallbackUrl = customUrl + separator + urlPrefix + '=' + encodeURIComponent(couponCode);
            $('#wcusage-generated-url').text(fallbackUrl);
            $('.wcusage-copy-referral-url').data('url', fallbackUrl);
            $('.wcusage-generate-short-url').data('url', fallbackUrl);
            
            // Trigger event for social sharing to update
            $(document).trigger('urlGenerated', { url: fallbackUrl });
            
            // Also trigger the widget social function directly if available
            if (typeof window.updateWidgetSocialLinksWithUrl === 'function') {
                window.updateWidgetSocialLinksWithUrl(fallbackUrl);
            }
            
            // Update QR code if it's visible
            if (typeof window.updateQRCodeIfVisible === 'function') {
                window.updateQRCodeIfVisible();
            }
            
            return;
        }
        
        // If no coupon code found, try AJAX to get it
        if (couponId) {
            $.ajax({
                url: wcusage_floating_widget.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcusage_floating_widget_get_coupon_code',
                    coupon_id: couponId,
                    security: wcusage_floating_widget.nonce
                },
                success: function(response) {
                    if (response && response.success && response.data.coupon_code) {
                        var separator = customUrl.indexOf('?') > -1 ? '&' : '?';
                        var fallbackUrl = customUrl + separator + urlPrefix + '=' + encodeURIComponent(response.data.coupon_code);
                        $('#wcusage-generated-url').text(fallbackUrl);
                        $('.wcusage-copy-referral-url').data('url', fallbackUrl);
                        $('.wcusage-generate-short-url').data('url', fallbackUrl);
                        
                        // Trigger event for social sharing to update
                        $(document).trigger('urlGenerated', { url: fallbackUrl });
                        
                        // Also trigger the widget social function directly
                        if (typeof window.updateWidgetSocialLinksWithUrl === 'function') {
                            window.updateWidgetSocialLinksWithUrl(fallbackUrl);
                        }
                        
                        // Update QR code if it's visible
                        if (typeof window.updateQRCodeIfVisible === 'function') {
                            window.updateQRCodeIfVisible();
                        }
                    } else {
                        setPlaceholderUrl(customUrl, urlPrefix);
                    }
                },
                error: function() {
                    setPlaceholderUrl(customUrl, urlPrefix);
                }
            });
        } else {
            setPlaceholderUrl(customUrl, urlPrefix);
        }
    }
    
    // Helper function to set placeholder URL
    function setPlaceholderUrl(customUrl, urlPrefix) {
        var separator = customUrl.indexOf('?') > -1 ? '&' : '?';
        var fallbackUrl = customUrl + separator + urlPrefix + '=YOUR_COUPON_CODE';
        $('#wcusage-generated-url').text(fallbackUrl);
        $('.wcusage-copy-referral-url').data('url', fallbackUrl);
        $('.wcusage-generate-short-url').data('url', fallbackUrl);
        
        // Trigger event for social sharing to update
        $(document).trigger('urlGenerated', { url: fallbackUrl });
        
        // Also trigger the widget social function directly
        if (typeof window.updateWidgetSocialLinksWithUrl === 'function') {
            window.updateWidgetSocialLinksWithUrl(fallbackUrl);
        }
    }
    
    // Update dashboard link when coupon changes
    function updateDashboardLink(couponId) {
        $.ajax({
            url: wcusage_floating_widget.ajax_url,
            type: 'POST',
            data: {
                action: 'wcusage_floating_widget_get_dashboard_url',
                coupon_id: couponId,
                security: wcusage_floating_widget.nonce
            },
            success: function(response) {
                if (response && response.success && response.data.dashboard_url) {
                    $('#wcusage-main-dashboard-link').attr('href', response.data.dashboard_url);
                }
            },
            error: function() {
                console.log('Failed to update dashboard link');
            }
        });
    }
    
    // Update links tab when coupon changes
    function updateLinksTab(couponId) {
        var linksContent = $('#wcusage-links-content');
        if (linksContent.length === 0) {
            return;
        }
        
        linksContent.html('<div class="wcusage-popup-loading">' + wcusage_floating_widget.loading_text + '</div>');
        
        $.ajax({
            url: wcusage_floating_widget.ajax_url,
            type: 'POST',
            data: {
                action: 'wcusage_floating_widget_links',
                coupon_id: couponId,
                security: wcusage_floating_widget.nonce
            },
            success: function(response) {
                if (response && response.success) {
                    linksContent.html(response.data);
                    
                    // Re-bind events for new content
                    bindLinksEvents();
                    initializeUrlField();
                }
            },
            error: function() {
                linksContent.html('<div class="wcusage-widget-error">Failed to update links tab</div>');
            }
        });
    }
    
    // Update referrals tab when coupon changes
    function updateReferralsTab(couponId) {
        $.ajax({
            url: wcusage_floating_widget.ajax_url,
            type: 'POST',
            data: {
                action: 'wcusage_floating_widget_referrals',
                coupon_id: couponId,
                security: wcusage_floating_widget.nonce
            },
            success: function(response) {
                if (response && response.success) {
                    $('#tab-referrals').html(response.data);
                }
            },
            error: function() {
                console.log('Failed to update referrals tab');
            }
        });
    }
    
    // Update payouts tab when coupon changes
    function updatePayoutsTab(couponId) {
        var payoutsTab = $('#tab-payouts');
        if (payoutsTab.length === 0) {
            return;
        }
        
        $.ajax({
            url: wcusage_floating_widget.ajax_url,
            type: 'POST',
            data: {
                action: 'wcusage_floating_widget_payouts',
                coupon_id: couponId,
                security: wcusage_floating_widget.nonce
            },
            success: function(response) {
                if (response && response.success) {
                    payoutsTab.html(response.data);
                }
            },
            error: function() {
                console.log('Failed to update payouts tab');
            }
        });
    }
    
    // Update creatives tab when coupon changes
    function updateCreativesTab(couponId) {
        var creativesTab = $('#tab-creatives');
        if (creativesTab.length === 0) {
            return;
        }
        
        creativesTab.html('<div class="wcusage-popup-loading">' + wcusage_floating_widget.loading_text + '</div>');
        
        $.ajax({
            url: wcusage_floating_widget.ajax_url,
            type: 'POST',
            data: {
                action: 'wcusage_floating_widget_creatives',
                coupon_id: couponId,
                security: wcusage_floating_widget.nonce
            },
            success: function(response) {
                if (response && response.success) {
                    creativesTab.html(response.data);
                } else {
                    var errorMsg = response && response.data ? response.data : 'Error loading creatives';
                    creativesTab.html('<div class="wcusage-widget-error">' + errorMsg + '</div>');
                }
            },
            error: function() {
                creativesTab.html('<div class="wcusage-widget-error">Failed to update creatives tab</div>');
            }
        });
    }
    
    // Bind events specifically for links tab content
    function bindLinksEvents() {
        // Copy referral URL - use the consolidated handler
        $('.wcusage-copy-referral-url').off('click').on('click', handleCopyReferralUrl);
        
        // Generate short URL - use the consolidated handler
        $('.wcusage-generate-short-url').off('click').on('click', handleGenerateShortUrl);
        
        // Custom URL input
        $('.wcusage-custom-page-url').off('input').on('input', function() {
            updateReferralUrl();
        });
    }
    
    // Load coupon statistics
    function loadCouponStats(couponId) {
        $('.wcusage-widget-stats').html('<div class="wcusage-popup-loading">' + wcusage_floating_widget.loading_text + '</div>');
        
        $.ajax({
            url: wcusage_floating_widget.ajax_url,
            type: 'POST',
            data: {
                action: 'wcusage_floating_widget_stats',
                coupon_id: couponId,
                security: wcusage_floating_widget.nonce
            },
            success: function(response) {
                if (response && response.success) {
                    $('.wcusage-widget-stats').html(response.data);
                } else {
                    var errorMsg = response && response.data ? response.data : wcusage_floating_widget.error_text;
                    $('.wcusage-widget-stats').html('<div class="wcusage-widget-error">' + errorMsg + '</div>');
                }
            },
            error: function() {
                $('.wcusage-widget-stats').html('<div class="wcusage-widget-error">' + wcusage_floating_widget.error_text + '</div>');
            }
        });
    }
    
    // Show message on button
    function showButtonMessage($button, message, type) {
        var originalText = $button.text();
        var messageClass = type === 'success' ? 'wcusage-widget-btn-success' : 'wcusage-widget-btn-error';
        
        $button.text(message);
        $button.addClass(messageClass);
        $button.prop('disabled', true);
        
        setTimeout(function() {
            $button.text(originalText);
            $button.removeClass(messageClass);
            $button.prop('disabled', false);
        }, 2000);
    }
    
    // Update coupon code display when switching coupons
    function updateCouponCodeDisplay(couponCode) {
        $('#wcusage-widget-main-coupon-code').text(couponCode);
        $('.wcusage-widget-coupon-copy-btn').data('coupon-code', couponCode);
        
        // Update coupon description with new discount and commission info
        var couponId = $('#wcusage-widget-coupon-select-main').val() || $('#wcusage-widget-coupon-select').val() || $('#wcusage-single-coupon-data').data('coupon-id');
        if (couponId) {
            updateCouponDescription(couponId);
        }
    }
    
    // Update coupon description when coupon changes
    function updateCouponDescription(couponId) {
        $.ajax({
            url: wcusage_floating_widget.ajax_url,
            type: 'POST',
            data: {
                action: 'wcusage_floating_widget_get_coupon_description',
                coupon_id: couponId,
                security: wcusage_floating_widget.nonce
            },
            success: function(response) {
                if (response && response.success && response.data.description) {
                    $('.wcusage-widget-coupon-description').html(response.data.description);
                }
            },
            error: function() {
                console.log('Failed to update coupon description');
            }
        });
    }
    
    // Show copy message for coupon code button
    function showCouponCopyMessage($button, type) {
        var $icon = $button.find('.wcusage-copy-icon');
        
        if (type === 'success') {
            // Replace copy icon with check icon
            $icon.removeClass('fa-copy').addClass('fa-check');
            $button.addClass('success');
        } else {
            // Replace copy icon with X icon
            $icon.removeClass('fa-copy').addClass('fa-times');
            $button.addClass('error');
        }
        
        $button.prop('disabled', true);
        
        setTimeout(function() {
            // Restore original copy icon
            $icon.removeClass('fa-check fa-times').addClass('fa-copy');
            $button.removeClass('success error');
            $button.prop('disabled', false);
        }, 2000);
    }
    
    // Fallback copy function for coupon code
    function fallbackCopyCouponCode(text, $button) {
        var textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.top = '0';
        textArea.style.left = '0';
        textArea.style.position = 'fixed';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            var successful = document.execCommand('copy');
            if (successful) {
                showCouponCopyMessage($button, 'success');
            } else {
                showCouponCopyMessage($button, 'error');
            }
        } catch (err) {
            console.log('Fallback copy failed:', err);
            showCouponCopyMessage($button, 'error');
        }
        
        document.body.removeChild(textArea);
    }
    
    // Fallback copy function for referral URLs
    function fallbackCopyTextToClipboard(text, $button) {
        var textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.top = '0';
        textArea.style.left = '0';
        textArea.style.position = 'fixed';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            var successful = document.execCommand('copy');
            if (successful) {
                showButtonMessage($button, wcusage_floating_widget.copy_success_text, 'success');
            } else {
                showButtonMessage($button, wcusage_floating_widget.copy_fail_text, 'error');
            }
        } catch (err) {
            console.log('Fallback copy failed:', err);
            showButtonMessage($button, wcusage_floating_widget.copy_fail_text, 'error');
        }
        
        document.body.removeChild(textArea);
    }

    // Generate short URL
    function generateShortUrl(url, $button) {
        $button.prop('disabled', true);
        $button.find('.wcusage-short-url-text').text('Loading...');
        $button.find('.wcusage-short-url-icon').hide();
        $button.find('.wcusage-short-url-spinner').show();
        
        $.ajax({
            url: wcusage_floating_widget.ajax_url,
            type: 'POST',
            data: {
                action: 'wcusage_load_short_url',
                url: url,
                _ajax_nonce: wcusage_floating_widget.shorturl_nonce || ''
            },
            success: function(response) {
                if (response && response.trim()) {
                    $('#wcusage-generated-url').text(response.trim());
                    $('.wcusage-copy-referral-url').data('url', response.trim());
                    $('.wcusage-generate-short-url').data('url', response.trim());
                    
                    if (typeof window.updateQRCodeIfVisible === 'function') {
                        window.updateQRCodeIfVisible();
                    }
                    
                    resetShortUrlButton($button);
                    showButtonMessage($button, 'Short URL Generated!', 'success');
                } else {
                    resetShortUrlButton($button);
                    showButtonMessage($button, 'Failed to generate', 'error');
                }
            },
            error: function() {
                resetShortUrlButton($button);
                showButtonMessage($button, 'Error occurred', 'error');
            }
        });
    }

    // Helper function to reset short URL button state
    function resetShortUrlButton($button) {
        $button.prop('disabled', false);
        $button.find('.wcusage-short-url-text').text('Short URL');
        $button.find('.wcusage-short-url-icon').show();
        $button.find('.wcusage-short-url-spinner').hide();
    }

    // Make initialization function available globally
    window.initializeFloatingWidgetPopup = initializeFloatingWidgetPopup;
    
})(jQuery);