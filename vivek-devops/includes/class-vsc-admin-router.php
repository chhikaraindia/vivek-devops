<?php
/**
 * VSC Admin Router
 * DISABLED: /vsc-admin route removed to prevent conflicts
 * Using direct /wp-admin with horizontal menu injection instead (like WP Adminify)
 */

if (!defined('ABSPATH')) exit;

class VSC_Admin_Router {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // /vsc-admin route DISABLED - causes double-wrapping issues
        // Now using class-vsc-horizontal-menu.php to wrap /wp-admin pages directly

        // Redirect /vsc-admin attempts to /wp-admin
        add_action('init', [$this, 'redirect_vsc_admin_to_wp_admin'], 5);
    }

    /**
     * Redirect any /vsc-admin requests to /wp-admin
     */
    public function redirect_vsc_admin_to_wp_admin() {
        $request_uri = $_SERVER['REQUEST_URI'];

        // Check if accessing /vsc-admin
        if (preg_match('#^/vsc-admin#', $request_uri)) {
            // Must be logged in
            if (!is_user_logged_in()) {
                wp_redirect(site_url('/vsc'));
                exit;
            }

            // Redirect to /wp-admin dashboard
            wp_redirect(admin_url());
            exit;
        }
    }
}
