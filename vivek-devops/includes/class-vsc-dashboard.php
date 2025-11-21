<?php
/**
 * VSC Dashboard
 * Custom dashboard home page showing Vivek DevOps features
 */

if (!defined('ABSPATH')) exit;

class VSC_Dashboard {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Register dashboard page
        add_action('admin_menu', [$this, 'register_dashboard_page']);

        // Remove WordPress Dashboard menu - very high priority
        add_action('admin_menu', [$this, 'remove_wordpress_dashboard'], 99999);

        // Rename menu items
        add_action('admin_menu', [$this, 'rename_menu_items'], 99999);

        // Hide menus for non-super admin
        add_action('admin_menu', [$this, 'hide_menus_for_restricted_users'], 100000);

        // Redirect /wp-admin to VSC Dashboard (set as default homepage)
        add_action('load-index.php', [$this, 'redirect_to_vsc_dashboard']);
    }

    /**
     * Register Vivek DevOps Dashboard page
     */
    public function register_dashboard_page() {
        add_menu_page(
            'Vivek DevOps',
            'Vivek DevOps',
            'read',
            'vsc-dashboard',
            [$this, 'render_dashboard'],
            'dashicons-shield-alt',
            2
        );

        // Add submenu items
        add_submenu_page(
            'vsc-dashboard',
            'Home',
            'Home',
            'read',
            'vsc-dashboard',
            [$this, 'render_dashboard']
        );

        add_submenu_page(
            'vsc-dashboard',
            'Updates',
            'Updates',
            'update_core',
            'update-core.php'
        );
    }

    /**
     * Render dashboard home page
     */
    public function render_dashboard() {
        // Safety check: verify user is valid
        if (!function_exists('wp_get_current_user')) {
            return;
        }

        $current_user = wp_get_current_user();

        if (!$current_user || !$current_user->exists()) {
            return;
        }

        // Get feature statuses
        $features = $this->get_feature_statuses();

        // Safety check: verify template exists
        $template_path = VSC_PATH . 'templates/dashboard-home.php';
        if (file_exists($template_path)) {
            include $template_path;
        }
    }

    /**
     * Get status of all features
     */
    private function get_feature_statuses() {
        return [
            [
                'name' => 'Custom Login URL',
                'description' => 'Access admin via /vsc instead of /wp-login.php',
                'status' => 'active',
                'version' => 'V-2.0.1'
            ],
            [
                'name' => '2FA Authentication',
                'description' => 'Google Authenticator TOTP-based two-factor authentication',
                'status' => 'active',
                'version' => 'V-2.0.1'
            ],
            [
                'name' => 'Custom Admin URL',
                'description' => 'Access dashboard via /vsc-admin with custom branding',
                'status' => 'active',
                'version' => 'V-3.0.6'
            ],
            [
                'name' => 'Honeypot System',
                'description' => 'Trap unauthorized access to /wp-login.php and /wp-admin',
                'status' => 'pending',
                'version' => null
            ],
            [
                'name' => 'IP Blocking',
                'description' => 'Database-driven IP blocking with manual whitelist',
                'status' => 'pending',
                'version' => null
            ],
            [
                'name' => 'Activity Logs',
                'description' => 'Comprehensive logging of logins, changes, and actions',
                'status' => 'pending',
                'version' => null
            ],
            [
                'name' => 'Admin Restrictions',
                'description' => 'Hide menus and restrict capabilities for non-super admins',
                'status' => 'partial',
                'version' => 'V-3.0.6'
            ],
            [
                'name' => 'File Integrity Checker',
                'description' => 'Hash-based monitoring with cron checks',
                'status' => 'pending',
                'version' => null
            ],
            [
                'name' => 'Code Snippet Injector',
                'description' => 'Inject custom PHP, CSS, and JS code',
                'status' => 'pending',
                'version' => null
            ],
            [
                'name' => 'SMTP Manager',
                'description' => 'Brevo integration with custom mail routing',
                'status' => 'pending',
                'version' => null
            ],
            [
                'name' => 'Backup & Migration',
                'description' => 'Database + files backup with Google Drive sync',
                'status' => 'pending',
                'version' => null
            ],
            [
                'name' => 'REST API Monitor',
                'description' => 'Status endpoint at /wp-json/vsc/v1/status',
                'status' => 'pending',
                'version' => null
            ],
        ];
    }

    /**
     * Remove WordPress Dashboard menu
     */
    public function remove_wordpress_dashboard() {
        // Remove the default WordPress Dashboard menu
        remove_menu_page('index.php');

        // Also remove Dashboard submenu pages
        remove_submenu_page('index.php', 'index.php');

        // Force remove from global $menu array as backup
        global $menu;
        if (is_array($menu)) {
            foreach ($menu as $key => $item) {
                if (isset($item[2]) && $item[2] === 'index.php') {
                    unset($menu[$key]);
                }
            }
        }
    }

    /**
     * Rename menu items
     */
    public function rename_menu_items() {
        global $menu;

        // Safety check: verify $menu is an array
        if (!is_array($menu)) {
            return;
        }

        foreach ($menu as $key => $item) {
            // Safety check: verify item is array
            if (!is_array($item)) {
                continue;
            }

            // Rename Posts to Blogs
            if (isset($item[0]) && $item[0] === 'Posts') {
                $menu[$key][0] = 'Blogs';
            }

            // Rename WooCommerce to Store
            if (isset($item[0]) && $item[0] === 'WooCommerce') {
                $menu[$key][0] = 'Store';
            }
        }
    }

    /**
     * Hide menus for non-super admin users
     * God Mode = Admin 1 + Admin 2 + Hardcoded Support Email
     */
    public function hide_menus_for_restricted_users() {
        // Safety check: verify function exists
        if (!function_exists('wp_get_current_user') || !function_exists('remove_menu_page')) {
            return;
        }

        $current_user = wp_get_current_user();

        // Safety check: verify user is valid
        if (!$current_user || !$current_user->exists()) {
            return;
        }

        // Get current user email
        $user_email = isset($current_user->user_email) ? $current_user->user_email : '';

        // Get dynamic super admins
        $super_admin_1 = get_option('vsc_super_admin_1', '');
        $super_admin_2 = get_option('vsc_super_admin_2', '');
        $hardcoded_admin = 'support@chhikara.in';

        // Check if user is in God Mode (one of the 3 super admins)
        if ($user_email === $super_admin_1 ||
            $user_email === $super_admin_2 ||
            $user_email === $hardcoded_admin) {
            // Show everything - don't hide any menus
            return;
        }

        // For restricted admins, hide certain menus
        $menus_to_hide = [
            'themes.php',           // Appearance
            'plugins.php',          // Plugins
            'tools.php',            // Tools
            'options-general.php',  // Settings
            'users.php',            // Users
        ];

        foreach ($menus_to_hide as $menu) {
            remove_menu_page($menu);
        }
    }

    /**
     * Redirect /wp-admin (index.php) to VSC Dashboard
     * Makes VSC Dashboard the default homepage
     */
    public function redirect_to_vsc_dashboard() {
        wp_redirect(admin_url('admin.php?page=vsc-dashboard'));
        exit;
    }
}
