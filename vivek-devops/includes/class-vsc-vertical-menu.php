<?php
/**
 * VSC Vertical Menu
 * Customizes WordPress default left sidebar menu with Vivek DevOps branding
 * PLUS adds custom top admin bar
 */

if (!defined('ABSPATH')) exit;

class VSC_Vertical_Menu {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Enqueue custom admin styles
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);

        // Add custom top admin bar and sidebar logo
        add_action('admin_head', [$this, 'inject_custom_ui']);

        // Hide default WordPress admin bar (we'll add our own)
        add_action('after_setup_theme', [$this, 'hide_default_admin_bar']);
    }

    /**
     * Hide default WordPress admin bar
     */
    public function hide_default_admin_bar() {
        show_admin_bar(false);
    }

    /**
     * Enqueue custom admin sidebar styles
     */
    public function enqueue_admin_styles() {
        wp_enqueue_style(
            'vsc-vertical-menu',
            VSC_ASSETS_CSS . 'vsc-vertical-menu.css',
            [],
            VSC_VERSION
        );
    }

    /**
     * Inject custom top admin bar and sidebar logo
     */
    public function inject_custom_ui() {
        $current_user = wp_get_current_user();
        $user_avatar = get_avatar_url($current_user->ID, ['size' => 36]);
        $logout_url = wp_logout_url(home_url());
        ?>
        <style>
            /* Hide default WordPress admin bar */
            #wpadminbar {
                display: none !important;
            }

            html.wp-toolbar {
                padding-top: 0 !important;
            }

            /* Custom top admin bar */
            #vsc-top-bar {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                width: 100%;
                height: 60px;
                background: #0a0a0a;
                border-bottom: 1px solid #1a1a1a;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 30px 0 15px;
                z-index: 99999;
                overflow: visible;
            }

            /* Ensure no horizontal scrollbar covers logout button */
            body.wp-admin {
                overflow-x: hidden;
            }

            #vsc-top-bar-logo {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            #vsc-top-bar-logo a {
                display: flex;
                align-items: center;
                gap: 12px;
                text-decoration: none;
                transition: opacity 0.2s;
            }

            #vsc-top-bar-logo a:hover {
                opacity: 0.8;
            }

            #vsc-top-bar-logo img.vsc-logo-img {
                height: 40px;
                width: auto;
            }

            #vsc-top-bar-logo span {
                color: #ffffff;
                font-size: 18px;
                font-weight: 600;
            }

            #vsc-top-bar-user {
                display: flex;
                align-items: center;
                gap: 20px;
                flex-shrink: 0;
                min-width: fit-content;
                margin-right: 15px;
            }

            #vsc-top-bar-avatar {
                display: flex;
                align-items: center;
                gap: 10px;
                color: #cccccc;
                font-size: 14px;
            }

            #vsc-top-bar-avatar img {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                border: 2px solid #3b82f6;
            }

            #vsc-top-bar-logout,
            #vsc-top-bar-logout:link,
            #vsc-top-bar-logout:visited,
            #vsc-top-bar-logout:hover,
            #vsc-top-bar-logout:active,
            #vsc-top-bar-logout:focus {
                background: linear-gradient(135deg, #ef4444, #dc2626) !important;
                color: #ffffff !important;
                border: none !important;
                padding: 8px 20px;
                border-radius: 4px;
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
                text-decoration: none !important;
                display: inline-block;
                transition: all 0.2s;
                white-space: nowrap;
                flex-shrink: 0;
            }

            #vsc-top-bar-logout:hover,
            #vsc-top-bar-logout:focus {
                background: linear-gradient(135deg, #dc2626, #b91c1c) !important;
                transform: translateY(-1px);
                color: #ffffff !important;
            }

            /* Adjust content area for top bar */
            #wpbody {
                padding-top: 60px !important;
            }

            /* Add padding to menu items so they don't hide under top bar */
            #adminmenuwrap {
                position: relative;
                padding-top: 61px;
            }

            /* Responsive adjustments */
            @media screen and (max-width: 782px) {
                #vsc-top-bar {
                    left: 0;
                }
            }
        </style>

        <script>
            jQuery(document).ready(function($) {
                // Inject top admin bar
                $('body').prepend(`
                    <div id="vsc-top-bar">
                        <div id="vsc-top-bar-logo">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=vsc-dashboard')); ?>">
                                <img src="<?php echo esc_url(VSC_URL . 'assets/images/new logo vsc devops.png'); ?>"
                                     alt="Vivek DevOps"
                                     class="vsc-logo-img">
                                <span>Vivek DevOps</span>
                            </a>
                        </div>
                        <div id="vsc-top-bar-user">
                            <div id="vsc-top-bar-avatar">
                                <img src="<?php echo esc_url($user_avatar); ?>" alt="<?php echo esc_attr($current_user->display_name); ?>">
                                <span><?php echo esc_html($current_user->display_name); ?></span>
                            </div>
                            <a href="<?php echo esc_url($logout_url); ?>" id="vsc-top-bar-logout">Logout</a>
                        </div>
                    </div>
                `);
            });
        </script>
        <?php
    }
}
