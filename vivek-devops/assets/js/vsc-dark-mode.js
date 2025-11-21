/**
 * Vivek DevOps - Dark Mode Configuration
 * Using Dark Reader library (same as Adminify)
 */

(function() {
    'use strict';

    // Wait for Dark Reader to load
    if (typeof DarkReader === 'undefined') {
        console.error('VSC: Dark Reader library not loaded');
        return;
    }

    // Dark Reader Configuration (matching Adminify's settings)
    const darkModeConfig = {
        brightness: 120,
        contrast: 100,
        sepia: 0,
        grayscale: 0
    };

    // Exception Rules - Force these elements to stay light
    // (Based on Adminify's implementation)
    const lightElementsCSS = `
        /* CRITICAL: Force dropdown display on hover - Override everything */
        .frame-vsc-menu-item.frame-vsc-has-submenu:hover .frame-vsc-submenu-wrapper {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .frame-vsc-submenu-wrapper:hover {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .frame-vsc-submenu-wrapper {
            display: none !important;
        }

        /* User Account Dropdown */
        .vsc-user-account:hover .vsc-user-wrapper {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .vsc-user-wrapper:hover {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .vsc-user-wrapper {
            display: none !important;
        }


        /* Form Elements - Keep white for readability */
        .ReactModal__Content,
        .select2-selection,
        .select2-results__options,
        .select2-dropdown,
        .form-cancel-btn,
        .folders-tabs,
        .folder-tab-menu,
        .folder-tab-menu a,
        .custom-checkbox span,
        .preview-inner-box,
        .preview-inner-box .form-options,
        .media-buttons,
        .popup-form-content,
        .select2-results__option,
        div.tagsinput,
        .settings-tabs-list .nav-tab,
        input[type=number],
        .bulk-action-select .bulk-action-select__control,
        .bulk-action-select__menu,
        .wprf-section-fields,
        .wprf-section-title,
        .wprf-input-radio-option:not(.wprf-option-selected) .wprf-input-label,
        .wprf-control-field .components-button,
        .wprf-select__menu,
        .wprf-select__control,
        .wprf-tab-content,
        .bootstrap-select,
        .bg-white {
            background: white !important;
        }

        /* Text Elements - Force white text */
        .big-pluspro-btn,
        .dokan-btn,
        .close_dp_help,
        .dup-btn,
        .folder-tab-menu a,
        .pro-feature-popup .pro-feature-content > a,
        .user-upgrade-inline-btn,
        .pro-feature-popup .pro-feature-content .pro-user-title,
        .pro-feature-popup .pro-feature-content .pro-user-desc,
        .folder-access,
        .add-new-folder,
        .media-select option,
        .popup-form-content,
        .nf-button:hover,
        .wp-react-form h1,
        .wp-react-form h2,
        .wp-react-form h3,
        .wp-react-form h4,
        .wprf-input-label,
        .wprf-tab-nav-item:not(:hover),
        .swift-btn,
        .wpcode-button,
        .yoast-button-upsell,
        .aioseo-button:hover,
        .el-button {
            color: white !important;
        }

        /* Logo Adjustments */
        .dokan-admin-header-logo img,
        .dup-header img {
            filter: grayscale(1) brightness(5);
        }

        .jetpack-logo,
        .it-ui-list div[direction="horizontal"] > svg {
            fill: black !important;
        }
    `;

    // Inject exception styles
    const styleElement = document.createElement('style');
    styleElement.textContent = lightElementsCSS;
    document.head.appendChild(styleElement);

    // Enable Dark Reader
    DarkReader.enable(darkModeConfig, {
        css: '',
        ignoreImageAnalysis: [],
        ignoreInlineStyle: [],
        invert: []
    });

    // Expose global control (like Adminify does)
    window.VSCDarkMode = {
        enable: function(config) {
            DarkReader.enable(config || darkModeConfig);
        },
        disable: function() {
            DarkReader.disable();
        },
        toggle: function() {
            if (DarkReader.isEnabled()) {
                DarkReader.disable();
            } else {
                DarkReader.enable(darkModeConfig);
            }
        },
        isEnabled: function() {
            return DarkReader.isEnabled();
        }
    };

    console.log('VSC Dark Mode: Enabled with Dark Reader');

    // CRITICAL FIX: Force dropdown functionality with JavaScript
    // Run after Dark Reader to ensure it works
    setTimeout(function() {
        const menuItems = document.querySelectorAll('.frame-vsc-menu-item.frame-vsc-has-submenu');

        console.log('VSC: Found ' + menuItems.length + ' menu items with dropdowns');

        menuItems.forEach(function(menuItem) {
            const submenu = menuItem.querySelector('.frame-vsc-submenu-wrapper');

            if (!submenu) {
                console.log('VSC: No submenu found for item');
                return;
            }

            console.log('VSC: Setting up dropdown for:', menuItem);

            // Show on hover
            menuItem.addEventListener('mouseenter', function() {
                console.log('VSC: Mouse entered menu item');
                submenu.style.display = 'block';
                submenu.style.visibility = 'visible';
                submenu.style.opacity = '1';
            });

            // Hide on leave
            menuItem.addEventListener('mouseleave', function(e) {
                // Check if mouse is moving to the submenu
                if (!submenu.contains(e.relatedTarget)) {
                    console.log('VSC: Mouse left menu item');
                    submenu.style.display = 'none';
                    submenu.style.visibility = 'hidden';
                    submenu.style.opacity = '0';
                }
            });

            // Keep open when hovering submenu
            submenu.addEventListener('mouseleave', function(e) {
                if (!menuItem.contains(e.relatedTarget)) {
                    console.log('VSC: Mouse left submenu');
                    submenu.style.display = 'none';
                    submenu.style.visibility = 'hidden';
                    submenu.style.opacity = '0';
                }
            });
        });

        // User Account Dropdown - Same fix
        const userAccount = document.querySelector('.vsc-user-account');
        if (userAccount) {
            const userWrapper = userAccount.querySelector('.vsc-user-wrapper');

            if (userWrapper) {
                console.log('VSC: Setting up user account dropdown');

                // Show on hover
                userAccount.addEventListener('mouseenter', function() {
                    console.log('VSC: Mouse entered user account');
                    userWrapper.style.display = 'block';
                    userWrapper.style.visibility = 'visible';
                    userWrapper.style.opacity = '1';
                });

                // Hide on leave
                userAccount.addEventListener('mouseleave', function(e) {
                    if (!userWrapper.contains(e.relatedTarget)) {
                        console.log('VSC: Mouse left user account');
                        userWrapper.style.display = 'none';
                        userWrapper.style.visibility = 'hidden';
                        userWrapper.style.opacity = '0';
                    }
                });

                // Keep open when hovering wrapper
                userWrapper.addEventListener('mouseleave', function(e) {
                    if (!userAccount.contains(e.relatedTarget)) {
                        console.log('VSC: Mouse left user wrapper');
                        userWrapper.style.display = 'none';
                        userWrapper.style.visibility = 'hidden';
                        userWrapper.style.opacity = '0';
                    }
                });
            }
        }
    }, 1000); // Wait 1 second for Dark Reader to fully load

})();
