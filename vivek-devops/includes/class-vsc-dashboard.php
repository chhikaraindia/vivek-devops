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

        add_submenu_page(
            'vsc-dashboard',
            'Settings',
            'Settings',
            'manage_options',
            'vsc-settings',
            [$this, 'render_settings']
        );

        // Handle settings form submission
        add_action('admin_init', [$this, 'handle_settings_save']);
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

    /**
     * Handle settings save
     */
    public function handle_settings_save() {
        if (!isset($_POST['vsc_settings_nonce']) || !wp_verify_nonce($_POST['vsc_settings_nonce'], 'vsc_save_settings')) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['vsc_super_admin_2'])) {
            $admin_2_email = sanitize_email($_POST['vsc_super_admin_2']);
            update_option('vsc_super_admin_2', $admin_2_email);
            add_settings_error('vsc_settings', 'settings_updated', 'Settings saved successfully!', 'success');
        }
    }

    /**
     * Render settings page
     */
    public function render_settings() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }

        $admin_1 = get_option('vsc_super_admin_1', '');
        $admin_2 = get_option('vsc_super_admin_2', '');
        $hardcoded = 'support@chhikara.in';

        settings_errors('vsc_settings');
        ?>
        <div class="wrap" style="max-width: 800px;">
            <h1>Vivek DevOps Settings</h1>

            <div style="background: #0a0a0a; border: 1px solid #1a1a1a; border-radius: 8px; padding: 30px; margin-top: 20px;">
                <h2 style="color: #ffffff; margin-top: 0;">God Mode Admin Management</h2>
                <p style="color: #cccccc; margin-bottom: 30px;">
                    God Mode admins have full access to all WordPress features. Other admins will have restricted access.
                </p>

                <div style="background: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 6px; padding: 20px; margin-bottom: 30px;">
                    <h3 style="color: #3b82f6; margin-top: 0;">Current God Mode Admins:</h3>

                    <div style="margin-bottom: 15px;">
                        <strong style="color: #ffffff;">Admin 1 (Auto-captured on activation):</strong><br>
                        <code style="background: #0a0a0a; padding: 5px 10px; border-radius: 4px; color: #10b981; display: inline-block; margin-top: 5px;">
                            <?php echo esc_html($admin_1 ? $admin_1 : 'Not set'); ?>
                        </code>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <strong style="color: #ffffff;">Admin 2 (Configurable):</strong><br>
                        <code style="background: #0a0a0a; padding: 5px 10px; border-radius: 4px; color: <?php echo $admin_2 ? '#10b981' : '#999'; ?>; display: inline-block; margin-top: 5px;">
                            <?php echo esc_html($admin_2 ? $admin_2 : 'Not set'); ?>
                        </code>
                    </div>

                    <div>
                        <strong style="color: #ffffff;">Admin 3 (Hardcoded):</strong><br>
                        <code style="background: #0a0a0a; padding: 5px 10px; border-radius: 4px; color: #10b981; display: inline-block; margin-top: 5px;">
                            <?php echo esc_html($hardcoded); ?>
                        </code>
                    </div>
                </div>

                <form method="post" action="">
                    <?php wp_nonce_field('vsc_save_settings', 'vsc_settings_nonce'); ?>

                    <table class="form-table" style="background: transparent;">
                        <tr>
                            <th scope="row" style="color: #ffffff; padding-left: 0;">
                                <label for="vsc_super_admin_2">Admin 2 Email</label>
                            </th>
                            <td>
                                <input type="email"
                                       id="vsc_super_admin_2"
                                       name="vsc_super_admin_2"
                                       value="<?php echo esc_attr($admin_2); ?>"
                                       class="regular-text"
                                       placeholder="admin2@example.com"
                                       style="background: #0a0a0a; border: 1px solid #2a2a2a; color: #ffffff; padding: 8px 12px;">
                                <p class="description" style="color: #999;">
                                    Enter the email address for the second God Mode admin. Leave empty to remove.
                                </p>
                            </td>
                        </tr>
                    </table>

                    <p class="submit" style="padding-left: 0;">
                        <button type="submit" class="button button-primary" style="background: linear-gradient(135deg, #3b82f6, #1e40af); border: none; padding: 10px 20px; font-size: 14px; font-weight: 600;">
                            Save Settings
                        </button>
                    </p>
                </form>

                <div style="background: #1a1a1a; border-left: 3px solid #0ea5e9; padding: 15px; margin-top: 30px; border-radius: 4px;">
                    <h4 style="color: #0ea5e9; margin-top: 0;">ℹ️ Access Control Rules:</h4>
                    <ul style="color: #cccccc; margin-bottom: 0;">
                        <li><strong>God Mode Admins:</strong> Full access to all WordPress features</li>
                        <li><strong>Restricted Admins:</strong> Can only access VSC Dashboard, Blogs, Media, Pages, Comments, and Store</li>
                        <li><strong>Hidden from restricted admins:</strong> Appearance, Plugins, Users, Tools, WP Settings</li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
}
