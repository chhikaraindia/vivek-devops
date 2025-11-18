<?php
/**
 * Horizontal Menu - iframe-Based Architecture
 * Complete UI control with security isolation for DevSecOps platform
 *
 * FIXES:
 * - All menu URLs include vsc_iframe=1 to prevent double rendering
 * - Complete dark mode theming matching Adminify
 * - Border container around content
 */

if (!defined('ABSPATH')) exit;

class VSC_Horizontal_Menu {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Completely hijack WordPress admin rendering
        add_action('admin_init', [$this, 'hijack_admin'], 0);
        add_action('admin_head', [$this, 'inject_css'], 9999);
        add_action('admin_footer', [$this, 'inject_js'], 999999);
        add_filter('admin_body_class', [$this, 'add_body_class']);
    }

    public function add_body_class($classes) {
        // Check if we're in the iframe
        if (isset($_GET['vsc_iframe']) && $_GET['vsc_iframe'] === '1') {
            return $classes . ' vsc-inside-iframe vsc-dark-mode';
        }
        return $classes . ' vsc-ui vsc-dark-mode sticky-menu vsc-frame-wrapper';
    }

    public function hijack_admin() {
        // If we're NOT in the iframe, render our custom wrapper
        if (!isset($_GET['vsc_iframe'])) {
            add_action('in_admin_header', [$this, 'render_frame_wrapper'], -999999);
        }
    }

    /**
     * Add vsc_iframe=1 to URL
     */
    private function add_iframe_param($url) {
        return add_query_arg('vsc_iframe', '1', $url);
    }

    public function render_frame_wrapper() {
        global $menu, $submenu;

        // Get current user
        $current_user = wp_get_current_user();
        $user_avatar = get_avatar_url($current_user->ID, ['size' => 72]);
        $logout_url = wp_logout_url(home_url());

        // Count pending comments
        $comments_count = wp_count_comments();
        $pending_comments = $comments_count->moderated;

        // Build iframe URL (current page with iframe flag)
        $iframe_url = $this->add_iframe_param($_SERVER['REQUEST_URI']);

        ?>
        <div id="frame-vsc-app" class="frame-vsc-app">
            <!-- Progress Bar -->
            <div class="vsc-progress" id="VSC-PreLoaderBar" style="display: none;">
                <div class="vsc-indeterminate"></div>
            </div>

            <!-- Custom Toolbar -->
            <div class="vsc-toolbar-wrapper vsc-horizontal-toolbar">
                <!-- Logo -->
                <div class="frame-vsc-logo">
                    <a href="<?php echo $this->add_iframe_param(admin_url()); ?>" target="vsc-iframe" style="display: flex; align-items: center; gap: 10px;">
                        <img src="<?php echo esc_url(VSC_URL . 'assets/images/logo-vsc-devops.png'); ?>"
                             alt="Vivek DevOps"
                             style="width: 36px; height: 36px; object-fit: contain;"
                             class="vsc-logo-image">
                        <span class="vsc-logo-text">Vivek DevOps</span>
                    </a>
                </div>

                <!-- Toolbar -->
                <div class="vsc-toolbar">
                    <!-- Right Side: User Account Only -->
                    <div class="vsc-admin-bar-top-secondary">
                        <!-- User Account -->
                        <div class="vsc-user-account">
                            <button class="vsc-user-avatar">
                                <img src="<?php echo esc_url($user_avatar); ?>" alt="<?php echo esc_attr($current_user->display_name); ?>" width="36" height="36">
                                <span class="vsc-user-status"></span>
                            </button>
                            <div class="vsc-user-wrapper">
                                <div class="vsc-user-info">
                                    <h3 class="vsc-user-name"><?php echo esc_html($current_user->display_name); ?></h3>
                                    <span><?php echo esc_html($current_user->user_email); ?></span>
                                </div>
                                <ul>
                                    <hr>
                                    <li><a href="<?php echo $this->add_iframe_param(admin_url('profile.php')); ?>" target="vsc-iframe">
                                        <span class="dashicons dashicons-admin-users"></span>
                                        <span>View Profile</span>
                                    </a></li>
                                    <hr>
                                    <li><a href="<?php echo esc_url($logout_url); ?>" class="vsc-logout">
                                        <span class="dashicons dashicons-exit"></span>
                                        <span>Log Out</span>
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Horizontal Menu -->
            <div class="vsc-frame-wrapper vsc-horizontal-menu">
                <div class="vsc-frame-content">
                    <div class="vsc-horizontal-menu-wrapper">
                        <ul>
                            <?php
                            // Render menu items
                            foreach ($menu as $key => $item) {
                                // Skip separators
                                if (strpos($item[4], 'wp-menu-separator') !== false) {
                                    continue;
                                }

                                if (!current_user_can($item[1])) {
                                    continue;
                                }

                                $has_submenu = !empty($submenu[$item[2]]);
                                $submenu_class = $has_submenu ? ' frame-vsc-has-submenu' : '';

                                // Menu URL with iframe param
                                $href = $item[2];
                                if (('index.php' != $item[2]) && (file_exists(WP_PLUGIN_DIR . "/{$item[2]}") || get_plugin_page_hook($item[2], 'admin.php'))) {
                                    $href = admin_url('admin.php?page=' . $item[2]);
                                } else {
                                    $href = admin_url($item[2]);
                                }
                                $href = $this->add_iframe_param($href);

                                // Icon
                                $icon = '';
                                if (!empty($item[6])) {
                                    if (preg_match('/^dashicons/', $item[6])) {
                                        $icon = '<span class="frame-vsc-menu-icon dashicons ' . esc_attr($item[6]) . '"></span>';
                                    } else {
                                        $icon = '<span class="frame-vsc-menu-icon"><img src="' . esc_url($item[6]) . '" alt=""></span>';
                                    }
                                }

                                echo '<li class="frame-vsc-menu-item' . $submenu_class . '">';
                                echo '<a href="' . esc_url($href) . '" target="vsc-iframe">';
                                echo '<div class="frame-vsc-menu-item-wrap">';
                                echo $icon;
                                echo '<span class="frame-vsc-menu-item-name">' . wp_kses_post($item[0]) . '</span>';
                                echo '</div>';

                                if ($has_submenu) {
                                    echo '<span class="frame-vsc-submenu-arrow">';
                                    echo '<span class="dashicons dashicons-arrow-down-alt2"></span>';
                                    echo '</span>';
                                }
                                echo '</a>';

                                // Submenu
                                if ($has_submenu) {
                                    echo '<div class="frame-vsc-submenu-wrapper">';
                                    echo '<ul class="frame-vsc-submenu">';
                                    foreach ($submenu[$item[2]] as $sub_item) {
                                        if (!current_user_can($sub_item[1])) {
                                            continue;
                                        }

                                        // Submenu URL with iframe param
                                        $suburl = $sub_item[2];
                                        if (file_exists(WP_PLUGIN_DIR . "/{$sub_item[2]}") || get_plugin_page_hook($sub_item[2], $item[2])) {
                                            $suburl = admin_url('admin.php?page=' . $sub_item[2]);
                                        } else {
                                            $suburl = admin_url($sub_item[2]);
                                        }
                                        $suburl = $this->add_iframe_param($suburl);

                                        echo '<li class="frame-vsc-submenu-item">';
                                        echo '<a href="' . esc_url($suburl) . '" target="vsc-iframe">' . wp_kses_post($sub_item[0]) . '</a>';
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                    echo '</div>';
                                }

                                echo '</li>';
                            }
                            ?>
                        </ul>
                    </div>

                    <!-- iframe containing actual WordPress admin -->
                    <iframe
                        width="100%"
                        height="100%"
                        id="frame-vsc-app--iframe"
                        name="vsc-iframe"
                        src="<?php echo esc_url($iframe_url); ?>"
                        frameborder="0"
                        style="border: none;">
                    </iframe>
                </div>
            </div>
        </div>

        <?php
        // Prevent WordPress from rendering anything else
        exit;
    }

    public function inject_css() {
        $is_iframe = isset($_GET['vsc_iframe']) && $_GET['vsc_iframe'] === '1';

        ?>
        <style type="text/css">
            /* ========================================
               VSC DARK MODE THEMING
               <?php echo $is_iframe ? 'IFRAME CONTEXT' : 'MAIN FRAME'; ?>
               ======================================== */

            /* CSS Custom Properties for Theming */
            :root {
                --vsc-bg-primary: #000000;
                --vsc-bg-secondary: #0a0a0a;
                --vsc-bg-tertiary: #121212;
                --vsc-bg-card: #1a1a1a;
                --vsc-text-primary: #ffffff;
                --vsc-text-secondary: #999999;
                --vsc-text-muted: #666666;
                --vsc-accent: #0ea5e9;
                --vsc-accent-hover: #60c5ff;
                --vsc-border: #1a1a1a;
                --vsc-border-light: #2a2a2a;
                --vsc-success: #10b981;
                --vsc-warning: #f59e0b;
                --vsc-error: #ef4444;
                --vsc-toolbar-height: 50px;
                --vsc-menu-height: 46px;
                --vsc-wrapper-bg: #1e2022;
            }

            <?php if ($is_iframe): ?>
            /* ========================================
               IFRAME CONTEXT - Hide sidebar & Apply Complete Dark Mode
               ======================================== */

            /* Hide WordPress sidebar and admin bar - Force hide with multiple rules */
            #wpadminbar,
            #adminmenumain,
            #adminmenuback,
            #adminmenuwrap,
            #vsc-menu-main,
            #vsc-menu-back,
            #vsc-menu-wrap {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                width: 0 !important;
                height: 0 !important;
                overflow: hidden !important;
                position: absolute !important;
                left: -9999px !important;
            }
            html.wp-toolbar { padding-top: 0 !important; }
            body {
                margin-left: 0 !important;
                background: var(--vsc-wrapper-bg) !important;
            }

            /* Reset content margins */
            #wpcontent {
                margin-left: 0 !important;
                padding-left: 0 !important;
                margin-top: 0 !important;
                background: var(--vsc-wrapper-bg) !important;
            }

            /* Add border container around content (like Adminify) */
            #wpbody {
                padding: 20px !important;
                background: var(--vsc-wrapper-bg) !important;
            }

            #wpbody-content {
                padding-bottom: 0 !important;
                background: var(--vsc-bg-primary) !important;
                border: 1px solid var(--vsc-border) !important;
                border-radius: 8px !important;
                padding: 20px !important;
                max-width: 100% !important;
                overflow-x: hidden !important;
                box-sizing: border-box !important;
            }

            .wrap {
                background: transparent !important;
                margin: 0 !important;
                max-width: 100% !important;
                overflow-x: hidden !important;
                box-sizing: border-box !important;
            }

            /* Prevent all WordPress content from causing horizontal scroll */
            #wpcontent,
            #wpbody,
            .wrap > *,
            table.widefat,
            .wp-list-table {
                max-width: 100% !important;
                overflow-x: auto !important;
                box-sizing: border-box !important;
            }

            /* Fix WordPress Footer Positioning */
            #wpfooter,
            #vsc-footer {
                margin-left: 0 !important;
                position: static !important;
                padding-left: 20px !important;
                padding-right: 20px !important;
            }

            /* Support obfuscated class names for security */
            #vsc-content {
                margin-left: 0 !important;
                padding-left: 0 !important;
                margin-top: 0 !important;
                background: var(--vsc-wrapper-bg) !important;
            }

            #vsc-body {
                padding: 20px !important;
                background: var(--vsc-wrapper-bg) !important;
            }

            #vsc-body-content {
                padding-bottom: 0 !important;
                background: var(--vsc-bg-primary) !important;
                border: 1px solid var(--vsc-border) !important;
                border-radius: 8px !important;
                padding: 20px !important;
                max-width: 100% !important;
                overflow-x: hidden !important;
                box-sizing: border-box !important;
            }

            /* ============================================
               Dark Mode: Powered by Dark Reader Library
               All WordPress elements automatically themed
               See: assets/js/vsc-dark-mode.js
               ============================================ */

            <?php else: ?>
            /* ========================================
               MAIN FRAME - Custom UI
               ======================================== */

            /* Reset WordPress admin styles */
            #wpadminbar { display: none !important; }
            #adminmenumain { display: none !important; }
            #wpcontent { margin: 0 !important; padding: 0 !important; }
            #wpbody { padding: 0 !important; }
            html.wp-toolbar { padding-top: 0 !important; }

            /* Main App Container */
            .frame-vsc-app {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: var(--vsc-bg-primary);
                display: flex;
                flex-direction: column;
                overflow-x: hidden; /* Prevent horizontal scroll */
                overflow-y: auto; /* Allow vertical scroll if needed */
            }

            /* Progress Bar */
            .vsc-progress {
                height: 3px;
                background: var(--vsc-bg-secondary);
                position: relative;
                overflow: hidden;
            }

            .vsc-indeterminate {
                position: absolute;
                background: var(--vsc-accent);
                animation: vsc-indeterminate 1.5s infinite;
            }

            @keyframes vsc-indeterminate {
                0% { left: -35%; right: 100%; }
                60% { left: 100%; right: -90%; }
                100% { left: 100%; right: -90%; }
            }

            /* Custom Toolbar */
            .vsc-toolbar-wrapper {
                height: var(--vsc-toolbar-height);
                background: var(--vsc-bg-primary);
                border-bottom: 1px solid var(--vsc-border);
                display: flex;
                align-items: center;
                padding: 0 20px;
                gap: 20px;
                flex-shrink: 0;
                position: relative;
                z-index: 10001; /* Above menu bar to allow user dropdown to show */
                overflow: visible; /* Prevent scroll issues with dropdowns */
            }

            .frame-vsc-logo a {
                display: flex;
                align-items: center;
                gap: 10px;
                color: var(--vsc-text-primary);
                text-decoration: none;
                font-weight: 600;
                font-size: 16px;
                line-height: 1;
            }

            .vsc-logo-icon {
                font-size: 28px;
                color: var(--vsc-accent);
                line-height: 1;
                display: flex;
                align-items: center;
            }

            .vsc-logo-text {
                line-height: 1;
                display: flex;
                align-items: center;
            }

            .vsc-toolbar {
                flex: 1;
                display: flex;
                justify-content: space-between;
                align-items: center;
                overflow: visible; /* Allow dropdowns to overflow */
            }

            .vsc-top-menu {
                display: flex;
                gap: 8px;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .vsc-top-menu-item a {
                display: flex;
                align-items: center;
                gap: 6px;
                padding: 6px 12px;
                background: var(--vsc-bg-primary);
                color: var(--vsc-text-primary);
                text-decoration: none;
                border-radius: 4px;
                transition: all 0.2s;
                font-size: 13px;
            }

            .vsc-top-menu-item a:hover {
                background: var(--vsc-accent);
            }

            .vsc-admin-bar-top-secondary {
                display: flex;
                gap: 12px;
                align-items: center;
                overflow: visible; /* Allow dropdowns to overflow */
                margin-left: auto; /* Push to the right side */
            }

            .frame-vsc-search-icon,
            .vsc-comment-trigger,
            .vsc-mode-icon,
            .vsc-preview-trigger,
            .vsc-user-avatar {
                background: transparent;
                border: none;
                color: var(--vsc-text-primary);
                cursor: pointer;
                padding: 6px;
                border-radius: 4px;
                transition: all 0.2s;
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .frame-vsc-search-icon:hover,
            .vsc-comment-trigger:hover,
            .vsc-mode-icon:hover,
            .vsc-preview-trigger:hover,
            .vsc-user-avatar:hover {
                background: var(--vsc-bg-primary);
            }

            .comment-counter {
                position: absolute;
                top: 2px;
                right: 2px;
                background: var(--vsc-error);
                color: white;
                font-size: 9px;
                padding: 1px 4px;
                border-radius: 8px;
                min-width: 14px;
                text-align: center;
                font-weight: 600;
            }

            .vsc-user-avatar img {
                width: 32px;
                height: 32px;
                border-radius: 50%;
            }

            .vsc-user-account {
                position: relative;
            }

            .vsc-user-wrapper {
                display: none !important;
                position: absolute;
                top: 100%;
                right: 0;
                margin-top: 8px;
                background: var(--vsc-bg-secondary);
                border: 1px solid var(--vsc-border);
                border-radius: 6px;
                padding: 12px;
                min-width: 220px;
                z-index: 99999;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
            }

            .vsc-user-account:hover .vsc-user-wrapper {
                display: block !important;
            }

            .vsc-user-wrapper:hover {
                display: block !important;
            }

            .vsc-user-info h3 {
                margin: 0 0 4px 0;
                color: var(--vsc-text-primary);
                font-size: 14px;
            }

            .vsc-user-info span {
                color: var(--vsc-text-secondary);
                font-size: 12px;
            }

            .vsc-user-wrapper ul {
                list-style: none;
                margin: 12px 0 0 0;
                padding: 0;
            }

            .vsc-user-wrapper hr {
                border: none;
                border-top: 1px solid var(--vsc-border);
                margin: 8px 0;
            }

            .vsc-user-wrapper li a {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 8px;
                color: var(--vsc-text-primary);
                text-decoration: none;
                border-radius: 4px;
                transition: all 0.2s;
                font-size: 13px;
            }

            .vsc-user-wrapper li a:hover {
                background: var(--vsc-bg-primary);
            }

            /* Horizontal Menu */
            .vsc-frame-wrapper {
                flex: 1;
                display: flex;
                flex-direction: column;
                overflow-x: hidden; /* Prevent horizontal scroll */
                overflow-y: visible; /* Allow dropdowns to overflow */
            }

            .vsc-frame-content {
                flex: 1;
                display: flex;
                flex-direction: column;
                overflow: visible; /* Allow dropdowns to overflow */
            }

            .vsc-horizontal-menu-wrapper {
                height: var(--vsc-menu-height);
                background: var(--vsc-bg-primary);
                border-bottom: 1px solid var(--vsc-border);
                flex-shrink: 0;
                position: relative;
                z-index: 10000; /* Above iframe */
            }

            .vsc-horizontal-menu-wrapper ul {
                display: flex;
                margin: 0;
                padding: 0;
                list-style: none;
                height: 100%;
            }

            .frame-vsc-menu-item {
                position: relative;
                pointer-events: auto;
            }

            .frame-vsc-menu-item > a {
                display: flex;
                align-items: center;
                gap: 6px;
                padding: 0 16px;
                height: 100%;
                color: var(--vsc-text-primary);
                text-decoration: none;
                border-bottom: 2px solid transparent;
                transition: all 0.2s;
                white-space: nowrap;
                font-size: 13px;
            }

            .frame-vsc-menu-item > a:hover {
                background: var(--vsc-bg-primary);
                border-bottom-color: var(--vsc-accent);
            }

            .frame-vsc-menu-item-wrap {
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .frame-vsc-menu-icon {
                font-size: 16px;
            }

            .frame-vsc-submenu-arrow {
                margin-left: 4px;
                font-size: 12px;
            }

            /* Submenu Dropdown - Bulma-style approach */
            .frame-vsc-submenu-wrapper {
                display: none !important;
                position: absolute !important;
                top: 100% !important;
                left: 0 !important;
                background: var(--vsc-bg-secondary) !important;
                border: 1px solid var(--vsc-border) !important;
                border-radius: 6px !important;
                padding: 6px 0 !important;
                min-width: 180px !important;
                z-index: 999999 !important;
                margin-top: 0 !important;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5) !important;
            }

            /* Show dropdown on hover - Force with !important to override Dark Reader */
            .frame-vsc-menu-item.frame-vsc-has-submenu:hover .frame-vsc-submenu-wrapper {
                display: block !important;
            }

            /* Keep dropdown visible when hovering over it */
            .frame-vsc-submenu-wrapper:hover {
                display: block !important;
            }

            .frame-vsc-submenu {
                list-style: none !important;
                margin: 0 !important;
                padding: 0 !important;
                display: block !important; /* Force vertical layout */
                flex-direction: column !important; /* Override any flex from parent */
            }

            .frame-vsc-submenu-item {
                display: block !important; /* Each item takes full width */
                width: 100% !important;
            }

            .frame-vsc-submenu-item a {
                display: block !important;
                padding: 8px 16px !important;
                color: var(--vsc-text-primary) !important;
                text-decoration: none !important;
                transition: all 0.2s;
                font-size: 13px !important;
                white-space: nowrap !important; /* Prevent text wrapping */
            }

            .frame-vsc-submenu-item a:hover {
                background: var(--vsc-bg-primary);
            }

            /* Notification count styling */
            .frame-vsc-menu-item-name span.awaiting-mod,
            .frame-vsc-menu-item-name span.update-plugins {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: var(--vsc-error);
                color: white;
                font-size: 9px;
                padding: 1px 5px;
                border-radius: 8px;
                margin-left: 6px;
                min-width: 16px;
                font-weight: 600;
            }

            /* Hide count-0 */
            span.count-0 {
                display: none !important;
            }

            /* iframe */
            #frame-vsc-app--iframe {
                flex: 1;
                background: var(--vsc-bg-primary);
                position: relative;
                z-index: 1; /* Below toolbar and menu */
                width: 100%;
                max-width: 100%;
            }

            /* ========================================
               RESPONSIVE CSS - Prevent Horizontal Scroll
               ======================================== */

            /* Prevent body/html overflow */
            html, body {
                overflow-x: hidden !important;
                max-width: 100vw !important;
            }

            /* Ensure all containers fit viewport */
            .frame-vsc-app,
            .vsc-frame-content {
                max-width: 100vw !important;
                overflow-x: hidden;
            }

            .vsc-toolbar-wrapper {
                max-width: 100vw !important;
                /* No overflow constraint - needs visible for dropdowns */
            }

            /* Ensure menu items don't force horizontal scroll */
            .vsc-horizontal-menu-wrapper ul {
                min-width: min-content;
            }

            /* Responsive adjustments for smaller screens */
            @media screen and (max-width: 1366px) {
                /* Reduce padding on smaller screens */
                .vsc-toolbar-wrapper {
                    padding: 0 15px;
                    gap: 15px;
                }

                .frame-vsc-menu-item > a {
                    padding: 0 12px;
                    font-size: 12px;
                }
            }

            @media screen and (max-width: 1024px) {
                /* Further reduce spacing */
                .vsc-toolbar-wrapper {
                    padding: 0 10px;
                    gap: 10px;
                }

                .frame-vsc-menu-item > a {
                    padding: 0 10px;
                }

                .vsc-top-menu {
                    gap: 6px;
                }
            }

            @media screen and (min-width: 1920px) {
                /* For larger screens (Full HD+), center content if needed */
                .frame-vsc-app {
                    max-width: 100vw;
                }
            }

            <?php endif; ?>
        </style>
        <?php
    }

    public function inject_js() {
        $is_iframe = isset($_GET['vsc_iframe']) && $_GET['vsc_iframe'] === '1';

        if ($is_iframe) {
            // JavaScript for INSIDE iframe - intercept all link clicks
            ?>
            <script type="text/javascript">
            (function() {
                // Intercept ALL admin link clicks and add vsc_iframe=1 parameter
                document.addEventListener('click', function(e) {
                    const link = e.target.closest('a');
                    if (!link) return;

                    const href = link.getAttribute('href');
                    if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

                    // Check if link goes to wp-admin
                    const isAdminLink = href.includes('/wp-admin/') || href.startsWith('edit.php') ||
                                       href.startsWith('post.php') || href.startsWith('post-new.php') ||
                                       href.startsWith('admin.php') || href.startsWith('options') ||
                                       href.startsWith('users.php') || href.startsWith('plugins.php') ||
                                       href.startsWith('themes.php') || href.startsWith('tools.php') ||
                                       href.startsWith('upload.php') || href.startsWith('media-new.php') ||
                                       href.includes('page=');

                    if (isAdminLink && !href.includes('vsc_iframe=1')) {
                        e.preventDefault();

                        // Add vsc_iframe=1 parameter
                        const separator = href.includes('?') ? '&' : '?';
                        const newHref = href + separator + 'vsc_iframe=1';

                        // Navigate to new URL
                        window.location.href = newHref;
                    }
                }, true);

                // Obfuscate WordPress class names for security
                // Replace WP-prefixed IDs and classes to hide WordPress fingerprints
                document.addEventListener('DOMContentLoaded', function() {
                    // Map of WordPress IDs/classes to obfuscated versions
                    const obfuscateMap = {
                        'wpbody': 'vsc-body',
                        'wpbody-content': 'vsc-body-content',
                        'wpcontent': 'vsc-content',
                        'wpfooter': 'vsc-footer',
                        'wpheader': 'vsc-header',
                        'wp-toolbar': 'vsc-toolbar-base',
                        'wp-admin': 'vsc-admin',
                        'adminmenumain': 'vsc-menu-main',
                        'adminmenuback': 'vsc-menu-back',
                        'adminmenuwrap': 'vsc-menu-wrap'
                    };

                    // Rename IDs
                    Object.keys(obfuscateMap).forEach(function(wpId) {
                        const element = document.getElementById(wpId);
                        if (element) {
                            element.id = obfuscateMap[wpId];
                        }
                    });

                    // Rename classes
                    Object.keys(obfuscateMap).forEach(function(wpClass) {
                        const elements = document.getElementsByClassName(wpClass);
                        // Convert HTMLCollection to array to avoid live collection issues
                        Array.from(elements).forEach(function(element) {
                            element.classList.remove(wpClass);
                            element.classList.add(obfuscateMap[wpClass]);
                        });
                    });

                    // Remove wp- prefixed classes for additional security
                    const allElements = document.querySelectorAll('[class*="wp-"]');
                    allElements.forEach(function(element) {
                        const classes = Array.from(element.classList);
                        classes.forEach(function(className) {
                            if (className.startsWith('wp-') && !obfuscateMap[className]) {
                                element.classList.remove(className);
                                element.classList.add('vsc-' + className.substring(3));
                            }
                        });
                    });
                });
            })();
            </script>
            <?php
        } else {
            // JavaScript for wrapper (main frame)
            ?>
            <script type="text/javascript">
            (function() {
                // iframe height adjustment
                const iframe = document.getElementById('frame-vsc-app--iframe');
                if (iframe) {
                    function adjustIframe() {
                        const toolbarHeight = document.querySelector('.vsc-toolbar-wrapper').offsetHeight;
                        const menuHeight = document.querySelector('.vsc-horizontal-menu-wrapper').offsetHeight;
                        iframe.style.height = 'calc(100vh - ' + (toolbarHeight + menuHeight) + 'px)';
                    }

                    window.addEventListener('load', adjustIframe);
                    window.addEventListener('resize', adjustIframe);
                }

                // Search functionality
                const searchIcon = document.querySelector('.frame-vsc-search-icon');
                if (searchIcon) {
                    searchIcon.addEventListener('click', function() {
                        // TODO: Implement search modal
                        alert('Search functionality coming soon');
                    });
                }

                // Dark mode toggle
                const modeIcon = document.querySelector('.vsc-mode-icon');
                if (modeIcon) {
                    modeIcon.addEventListener('click', function() {
                        // TODO: Implement dark/light mode toggle
                        alert('Dark mode toggle coming soon');
                    });
                }
            })();
            </script>
            <?php
        }
    }
}
