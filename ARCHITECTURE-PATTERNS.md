# Architecture Patterns Learned from WP Adminify

## Overview
This document summarizes key architectural patterns and WordPress best practices observed in WP Adminify plugin that we'll apply to Vivek DevOps plugin.

---

## 1. Plugin Structure

### Constants Definition
```php
// Define all plugin constants at the top
define('WP_ADMINIFY_VER', $version);
define('WP_ADMINIFY_FILE', __FILE__);
define('WP_ADMINIFY_PATH', trailingslashit(plugin_dir_path(__FILE__)));
define('WP_ADMINIFY_URL', trailingslashit(plugins_url('/', __FILE__)));
define('WP_ADMINIFY_ASSETS', WP_ADMINIFY_URL . 'assets/');
```

**Key Learnings:**
- Use `trailingslashit()` for directory paths
- Separate constants for PATH (filesystem) and URL (web access)
- Define assets URL for easy asset loading

---

## 2. Singleton Pattern

### Implementation
```php
class WP_Adminify {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Initialize here
    }
}
```

**Key Learnings:**
- Use singleton for main plugin class
- Private constructor prevents direct instantiation
- Each module should also use singleton pattern

---

## 3. Module Organization

### Directory Structure
```
plugin-root/
├── Inc/
│   ├── Admin/
│   │   ├── Admin.php (Main admin controller)
│   │   ├── AdminSettings.php (Settings handler)
│   │   └── Options/ (Settings pages)
│   ├── Modules/
│   │   ├── MenuEditor/
│   │   │   ├── MenuEditor.php (Main class)
│   │   │   ├── MenuEditorModel.php (Data handling)
│   │   │   ├── MenuEditorOptions.php (Settings)
│   │   │   └── MenuEditorAssets.php (Scripts/Styles)
│   │   └── [Other Modules]/
│   └── Classes/
│       ├── Assets.php
│       └── Upgrade.php
├── assets/
│   ├── css/
│   └── js/
└── vendor/ (Composer autoload)
```

**Key Learnings:**
- Each module has its own directory
- Separate Model, Options, and Assets classes
- Use namespaces: `WPAdminify\Inc\Modules\MenuEditor`

---

## 4. Activation/Deactivation Hooks

### Pattern Used
```php
// In main plugin file
register_activation_hook(PLUGIN_FILE, ['PluginClass', 'activation_hook']);
register_deactivation_hook(PLUGIN_FILE, ['PluginClass', 'deactivation_hook']);

// For modules with database tables
register_activation_hook(PLUGIN_FILE, ['ModuleClass', 'activation_hook']);
register_uninstall_hook(PLUGIN_FILE, ['ModuleClass', 'deactivation_hook']);
```

**Activation Hook Tasks:**
- Create/update database tables
- Set default options
- Store activation timestamp
- Run version upgrades
- Create custom upload directories

---

## 5. Menu Customization Approach

### How WP Adminify Handles Menu Editor

**Filters Used:**
- `parent_file` - Modify menu hierarchy (priority 800-900)
- `admin_body_class` - Add custom body classes
- `upload_mimes` - Allow SVG icons for custom menu icons

**AJAX Actions:**
- `adminify_save_menu_settings` - Save menu configuration
- `adminify_reset_menu_settings` - Reset to defaults
- `adminify_export_menu_settings` - Export configuration
- `adminify_import_menu_settings` - Import configuration

**Key Learnings:**
- Menu settings stored in WordPress options table
- Use role-based visibility filtering
- Support custom icons (SVG with sanitization)
- Allow drag-and-drop reordering

---

## 6. Role-Based Access Control

### Pattern
```php
public function check_user_access() {
    $current_user = wp_get_current_user();
    $settings = $this->get_settings();

    // Check if user/role should see menu item
    if (in_array($current_user->user_login, $settings['hidden_for_users'])) {
        return false;
    }

    foreach ($current_user->roles as $role) {
        if (in_array($role, $settings['hidden_for_roles'])) {
            return false;
        }
    }

    return true;
}
```

**Key Learnings:**
- Check both individual users and roles
- Master admin should bypass all restrictions
- Use WordPress capabilities system
- Store restrictions in serialized arrays

---

## 7. Assets Loading

### Only Load in Admin Area
```php
public function enqueue_assets() {
    // Only load in admin
    if (!is_admin()) {
        return;
    }

    wp_enqueue_style(
        'plugin-admin-styles',
        PLUGIN_URL . 'assets/css/admin.css',
        [],
        PLUGIN_VERSION
    );

    wp_enqueue_script(
        'plugin-admin-scripts',
        PLUGIN_URL . 'assets/js/admin.js',
        ['jquery'],
        PLUGIN_VERSION,
        true
    );

    // Localize for AJAX
    wp_localize_script('plugin-admin-scripts', 'pluginData', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('plugin_nonce')
    ]);
}
```

**Key Learnings:**
- Never load admin assets on frontend
- Use `wp_localize_script()` for passing PHP data to JavaScript
- Always version assets for cache busting
- Create nonces for AJAX security

---

## 8. Database Table Creation

### Pattern for Custom Tables
```php
public static function activation_hook() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'plugin_table';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        action varchar(255) NOT NULL,
        details text,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
```

**Key Learnings:**
- Use `dbDelta()` for table creation (handles updates)
- Always use `$wpdb->prefix` for table names
- Use charset from WordPress settings
- Add indexes for frequently queried columns
- Use `datetime DEFAULT CURRENT_TIMESTAMP` for timestamps

---

## 9. Settings Storage

### Options Pattern
```php
// Single serialized array for all settings
update_option('_pluginname', $settings_array);

// Retrieve
$settings = get_option('_pluginname', []);

// Backup before major changes
$old_data = get_option('_pluginname');
update_option('_pluginname_backup', $old_data);
```

**Key Learnings:**
- Store related settings in one serialized array
- Prefix option names with underscore to hide from admin
- Create backups before updates
- Use default values in `get_option()`

---

## 10. AJAX Security

### Pattern
```php
// Server-side
public function ajax_handler() {
    // Verify nonce
    check_ajax_referer('plugin-security-nonce', 'security');

    // Check capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    // Sanitize input
    $data = wp_kses_post_deep(wp_unslash($_POST['data']));

    // Process...

    // Send response
    wp_send_json_success($result);
}

// Client-side
jQuery.ajax({
    url: pluginData.ajaxUrl,
    type: 'POST',
    data: {
        action: 'plugin_action',
        security: pluginData.nonce,
        data: formData
    }
});
```

**Key Learnings:**
- Always verify nonces with `check_ajax_referer()`
- Check user capabilities
- Sanitize all inputs with appropriate functions
- Use `wp_send_json_success()` and `wp_send_json_error()`

---

## 11. SVG Icon Handling (Security)

### Sanitization Pattern
```php
use enshrined\svgSanitize\Sanitizer;

public function sanitize_svg_file($file) {
    if ($file_type['ext'] === 'svg') {
        $file_contents = file_get_contents($file['tmp_name']);

        // Validate SVG tag exists
        if (strpos($file_contents, '<svg') === false) {
            $file['error'] = 'Invalid SVG file';
            return $file;
        }

        // Sanitize
        $sanitizer = new Sanitizer();
        $clean_svg = $sanitizer->sanitize($file_contents);

        if ($clean_svg) {
            file_put_contents($file['tmp_name'], $clean_svg);
        } else {
            $file['error'] = 'Failed to sanitize';
        }
    }
    return $file;
}
```

**Key Learnings:**
- Never trust user-uploaded SVGs
- Use dedicated SVG sanitization library
- Validate file before sanitization
- Only allow for administrators

---

## 12. Custom Upload Directories

### Pattern
```php
public function custom_upload_dir($dir) {
    return [
        'path'   => $dir['basedir'] . '/plugin-custom-icons',
        'url'    => $dir['baseurl'] . '/plugin-custom-icons',
        'subdir' => '/plugin-custom-icons',
    ] + $dir;
}

// Usage
add_filter('upload_dir', [$this, 'custom_upload_dir']);
$attachment_id = media_handle_upload('file', 0);
remove_filter('upload_dir', [$this, 'custom_upload_dir']);
```

---

## 13. Version Upgrade System

### Pattern
```php
public function maybe_run_upgrades() {
    $current_version = get_option('plugin_version');

    if (version_compare($current_version, PLUGIN_VERSION, '<')) {
        $this->run_upgrades($current_version);
        update_option('plugin_version', PLUGIN_VERSION);
    }
}
```

---

## Application to Vivek DevOps Plugin

### We Will Apply:

1. **Singleton pattern** for main class and all modules
2. **Namespace structure**: `VivekDevOps\Modules\Login`, etc.
3. **Separate classes** for Model, Options, Assets per module
4. **AJAX security** with nonces and capability checks
5. **Custom tables** using `dbDelta()` pattern
6. **Settings storage** in serialized option arrays
7. **Assets loading** only in admin area
8. **Role-based access** with master admin bypass
9. **Version tracking** and upgrade system
10. **Security best practices** for all user inputs

### We Will NOT Use:

- Freemius SDK (licensing system) - not needed
- Their specific UI framework - we have our own black theme
- Premium/addons system - single unified plugin
- Their specific database schema - we have custom requirements

---

---

# Activity Logging Patterns from Activity Log by Elementor

## Overview
Activity Log (aryo-activity-log) by Elementor provides comprehensive logging of all WordPress admin actions. Version 2.11.2 analyzed.

---

## 1. Database Table Design

### Custom Table Structure
```php
CREATE TABLE `{$wpdb->prefix}aryo_activity_log` (
    `histid` int(11) NOT NULL AUTO_INCREMENT,
    `user_caps` varchar(70) NOT NULL DEFAULT 'guest',
    `action` varchar(255) NOT NULL,
    `object_type` varchar(255) NOT NULL,
    `object_subtype` varchar(255) NOT NULL DEFAULT '',
    `object_name` varchar(255) NOT NULL,
    `object_id` int(11) NOT NULL DEFAULT '0',
    `user_id` int(11) NOT NULL DEFAULT '0',
    `hist_ip` varchar(55) NOT NULL DEFAULT '127.0.0.1',
    `hist_time` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`histid`),
    KEY `user_caps` (`user_caps`),
    KEY `action` (`action`),
    KEY `object_type` (`object_type`),
    KEY `object_subtype` (`object_subtype`),
    KEY `object_name` (`object_name`),
    KEY `user_id` (`user_id`),
    KEY `hist_ip` (`hist_ip`),
    KEY `hist_time` (`hist_time`)
) $charset_collate;
```

**Key Learnings:**
- **Separate table for logs** - not stored in wp_options for performance
- **Extensive indexing** - all searchable columns have indexes
- **Unix timestamp** - `hist_time` as int for better performance than DATETIME
- **User capabilities** - stores role as string (administrator, editor, etc.)
- **Flexible object tracking** - type, subtype, name, and ID for any WordPress object
- **IP address logging** - varchar(55) supports both IPv4 and IPv6

---

## 2. Hook-Based Architecture

### Modular Hook Classes
Each WordPress component has its own hook class:
- `AAL_Hook_Users` - Login, logout, registration, profile updates, failed logins
- `AAL_Hook_Posts` - Post creation, updates, deletion, status changes
- `AAL_Hook_Plugins` - Plugin activation, deactivation, installation
- `AAL_Hook_Themes` - Theme switches, updates
- `AAL_Hook_Options` - Settings changes
- `AAL_Hook_Menus` - Menu modifications
- `AAL_Hook_Comments` - Comment actions
- `AAL_Hook_Emails` - Email sending tracking
- `AAL_Hook_Attachments` - Media uploads, deletions
- `AAL_Hook_Widgets` - Widget changes
- `AAL_Hook_Taxonomies` - Category/tag modifications

**Pattern:**
```php
class AAL_Hook_Users extends AAL_Hook_Base {

    public function hooks_wp_login($user_login, $user) {
        aal_insert_log([
            'action' => 'logged_in',
            'object_type' => 'Users',
            'object_subtype' => 'Session',
            'user_id' => $user->ID,
            'object_id' => $user->ID,
            'object_name' => $user->user_nicename,
        ]);
    }

    public function __construct() {
        add_action('wp_login', [&$this, 'hooks_wp_login'], 10, 2);
        parent::__construct();
    }
}
```

**Key Learnings:**
- **Separation of concerns** - each WordPress area has its own hook class
- **Descriptive method names** - `hooks_{action_name}` naming convention
- **Consistent logging** - all use `aal_insert_log()` helper function
- **Standard WordPress hooks** - hooks into existing WP actions/filters

---

## 3. Logging API

### Helper Function Pattern
```php
// Global helper function
function aal_insert_log($args = array()) {
    AAL_Main::instance()->api->insert($args);
}

// API class method
public function insert($args) {
    global $wpdb;

    $args = wp_parse_args($args, [
        'action' => '',
        'object_type' => '',
        'object_subtype' => '',
        'object_name' => '',
        'object_id' => '',
        'hist_ip' => $this->_get_ip_address(),
        'hist_time' => current_time('timestamp'),
    ]);

    $args = $this->setup_userdata($args);

    // Duplicate check
    $check_duplicate = $wpdb->get_row(/* SQL query */);
    if ($check_duplicate) {
        return;
    }

    // Insert
    $wpdb->insert($wpdb->activity_log, $args, $formats);

    do_action('aal_insert_log', $args);
}
```

**Key Learnings:**
- **Global helper function** for easy logging from anywhere
- **Automatic metadata** - IP, timestamp, user data added automatically
- **Duplicate prevention** - checks before inserting
- **Extensibility hook** - `do_action()` after insert for custom integrations
- **Skip filter** - `aal_skip_insert_log` filter to prevent certain logs

---

## 4. IP Address Detection

### Smart IP Detection with Privacy Options
```php
protected function _get_ip_address() {
    $header_key = AAL_Main::instance()->settings->get_option('log_visitor_ip_source');

    if (empty($header_key)) {
        $header_key = 'REMOTE_ADDR';
    }

    // Privacy option
    if ('no-collect-ip' === $header_key) {
        return '';
    }

    $visitor_ip_address = '';
    if (!empty($_SERVER[$header_key])) {
        $visitor_ip_address = $_SERVER[$header_key];
    }

    $remote_address = apply_filters('aal_get_ip_address', $visitor_ip_address);

    if (!empty($remote_address) && filter_var($remote_address, FILTER_VALIDATE_IP)) {
        return $remote_address;
    }

    return '127.0.0.1';
}
```

**Key Learnings:**
- **Configurable IP source** - support for proxies (X-Forwarded-For, etc.)
- **Privacy compliance** - option to not collect IPs (GDPR)
- **Validation** - always validate with `filter_var()`
- **Safe fallback** - returns localhost if invalid
- **Extensibility** - filter for custom IP detection

---

## 5. User Data Setup

### Automatic User Context
```php
private function setup_userdata($args) {
    $user = get_user_by('id', get_current_user_id());

    if ($user) {
        $args['user_caps'] = strtolower(key($user->caps));
        if (empty($args['user_id'])) {
            $args['user_id'] = $user->ID;
        }
    } else {
        $args['user_caps'] = 'guest';
        if (empty($args['user_id'])) {
            $args['user_id'] = 0;
        }
    }

    return $args;
}
```

**Key Learnings:**
- **Automatic user detection** - gets current logged-in user
- **Guest handling** - user_id = 0, caps = 'guest' for non-logged-in actions
- **Role extraction** - uses `key($user->caps)` to get primary role
- **Override capability** - only sets if not already provided

---

## 6. Log Maintenance & Cleanup

### Scheduled Cleanup
```php
public function __construct() {
    add_action('admin_init', [$this, 'maybe_add_schedule_delete_old_items']);
    add_action('aal/maintenance/clear_old_items', [$this, 'delete_old_items']);
}

public function maybe_add_schedule_delete_old_items() {
    if (!wp_next_scheduled('aal/maintenance/clear_old_items')) {
        wp_schedule_event(time(), 'daily', 'aal/maintenance/clear_old_items');
    }
}

public function delete_old_items() {
    global $wpdb;

    $logs_lifespan = absint(AAL_Main::instance()->settings->get_option('logs_lifespan'));
    if (empty($logs_lifespan)) {
        return;
    }

    $wpdb->query($wpdb->prepare(
        'DELETE FROM `' . $wpdb->activity_log . '`
            WHERE `hist_time` < %d',
        strtotime('-' . $logs_lifespan . ' days', current_time('timestamp'))
    ));
}
```

**Key Learnings:**
- **WordPress cron** - use `wp_schedule_event()` for recurring tasks
- **Daily cleanup** - automatically delete old logs
- **Configurable retention** - admin can set how many days to keep
- **Safe deletion** - only if lifespan is set
- **Prepared statements** - always use `$wpdb->prepare()`

---

## 7. Failed Login Tracking

### Optional Failed Login Logging
```php
public function hooks_wrong_password($username) {
    if ('no' === AAL_Main::instance()->settings->get_option('logs_failed_login')) {
        return;
    }

    aal_insert_log([
        'action' => 'failed_login',
        'object_type' => 'Users',
        'object_subtype' => 'Session',
        'user_id' => 0,
        'object_id' => 0,
        'object_name' => $username,
    ]);
}

// Hook
add_filter('wp_login_failed', [&$this, 'hooks_wrong_password']);
```

**Key Learnings:**
- **Configurable logging** - admin can enable/disable sensitive logs
- **No user ID for failures** - uses 0 since user doesn't exist or isn't verified
- **Username tracking** - stores attempted username for analysis
- **WordPress filter** - uses `wp_login_failed` filter

---

## 8. Custom Capability for Viewing Logs

### Adding Custom Capabilities
```php
protected static function _create_tables() {
    // ... table creation ...

    $admin_role = get_role('administrator');
    if ($admin_role instanceof WP_Role && !$admin_role->has_cap('view_all_aryo_activity_log')) {
        $admin_role->add_cap('view_all_aryo_activity_log');
    }
}

protected static function _remove_tables() {
    // ... table deletion ...

    $admin_role = get_role('administrator');
    if ($admin_role && $admin_role->has_cap('view_all_aryo_activity_log')) {
        $admin_role->remove_cap('view_all_aryo_activity_log');
    }
}
```

**Key Learnings:**
- **Custom capabilities** - create plugin-specific capabilities
- **Role modification** - add/remove caps on activation/deactivation
- **Proper cleanup** - remove custom caps on uninstall
- **Capability checking** - use `current_user_can('view_all_aryo_activity_log')`

---

## 9. Multisite Support

### Network-Wide Activation
```php
public static function activate($network_wide) {
    global $wpdb;

    if (function_exists('is_multisite') && is_multisite() && $network_wide) {
        $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);
            self::_create_tables();
            restore_current_blog();
        }
    } else {
        self::_create_tables();
    }
}

// Hook for new sites
add_action('wpmu_new_blog', ['AAL_Maintenance', 'mu_new_blog_installer'], 10, 6);
add_action('delete_blog', ['AAL_Maintenance', 'mu_delete_blog'], 10, 2);
```

**Key Learnings:**
- **Network activation** - handle multisite activation properly
- **Switch/restore blog** - use WordPress multisite functions
- **New site hook** - automatically setup for new sites in network
- **Cleanup on site deletion** - remove tables when site is deleted

---

## 10. Export Functionality

### Privacy & GDPR Compliance
```php
// Separate exporter classes
new AAL_Export();
new AAL_Privacy();

// Privacy class handles WordPress Privacy Tools integration
// Export class handles CSV/other format exports
```

**Key Learnings:**
- **GDPR compliance** - export/erasure via WordPress Privacy Tools
- **Multiple export formats** - support CSV and other formats
- **Separate concerns** - privacy and export in different classes
- **WordPress integration** - use built-in privacy exporters/erasers

---

## Application to Vivek DevOps Activity Log

### We Will Implement:

1. **Custom table with extensive indexing** for performance
2. **Hook-based architecture** - separate class for each WordPress area
3. **Global helper function** - `vsc_log_activity()` for easy logging
4. **Automatic metadata** - IP, timestamp, user role captured automatically
5. **Duplicate prevention** - check before inserting
6. **Scheduled cleanup** - daily cron to delete old logs
7. **Failed login tracking** - for honeypot integration
8. **IP privacy options** - GDPR compliance
9. **Custom capabilities** - `view_vsc_activity_log` for master admin
10. **Export functionality** - CSV export for logs
11. **Multisite support** - if needed in future

### Our Specific Additions:

1. **Security event priorities** - Critical vs informational
2. **Email alerts** - for critical security events
3. **Integration with SMTP module** - email notifications
4. **Integration with honeypot** - log trap attempts
5. **Integration with 2FA** - log authentication attempts
6. **File integrity logs** - from integrity checker module

### Database Schema for VSC:
```sql
CREATE TABLE `{$wpdb->prefix}vsc_logs` (
    `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) NOT NULL DEFAULT '0',
    `user_role` varchar(70) NOT NULL DEFAULT 'guest',
    `action` varchar(255) NOT NULL,
    `object_type` varchar(255) NOT NULL,
    `object_subtype` varchar(255) NOT NULL DEFAULT '',
    `object_name` varchar(255) NOT NULL,
    `object_id` bigint(20) NOT NULL DEFAULT '0',
    `severity` enum('info','warning','critical') NOT NULL DEFAULT 'info',
    `ip_address` varchar(55) NOT NULL DEFAULT '127.0.0.1',
    `user_agent` text,
    `details` text,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`log_id`),
    KEY `user_id` (`user_id`),
    KEY `user_role` (`user_role`),
    KEY `action` (`action`),
    KEY `object_type` (`object_type`),
    KEY `severity` (`severity`),
    KEY `ip_address` (`ip_address`),
    KEY `created_at` (`created_at`)
) $charset_collate;
```

---

---

# Two Factor Authentication Patterns from Simba TFA

## Overview
Two Factor Authentication (TFA) plugin by Simba Hosting provides TOTP/HOTP based 2FA for WordPress login. Version 1.15.5 analyzed.

---

## 1. Secret Key Storage & Encryption

### Mu-Plugin for Encryption Key
```php
// Auto-creates mu-plugins/simba-tfa-encryption-key.php
public function insert_contents() {
    $encryption_key = base64_encode($this->simba_tfa->random_bytes(16));
    $code = "<?php\n";
    $code .= "if (!defined('SIMBA_TFA_DB_ENCRYPTION_KEY')) define('SIMBA_TFA_DB_ENCRYPTION_KEY', '{$encryption_key}');";

    file_put_contents($this->file_path, $code);
}
```

**Key Learnings:**
- **Encryption key stored in mu-plugins/** - separate from database
- **Auto-generation** - creates encryption key on first use
- **Two-layer security** - attacker needs both DB access AND file system access
- **Random 16-byte key** - properly generated using cryptographically secure random_bytes()
- **WordPress constant** - SIMBA_TFA_DB_ENCRYPTION_KEY

---

## 2. User Meta Storage

### Data Stored Per User
```php
// Private key (encrypted)
update_user_meta($user_id, 'tfa_priv_key_64', $encrypted_key);

// Last used passwords (to prevent replay)
update_user_meta($user_id, 'tfa_last_pws', $hashed_codes_array);

// Last successful login time window
update_user_meta($user_id, 'tfa_last_login', $time_window);

// Emergency backup codes (encrypted)
update_user_meta($user_id, 'simba_tfa_emergency_codes_64', $encrypted_codes);

// HOTP counter (if using HOTP instead of TOTP)
update_user_meta($user_id, 'tfa_hotp_counter', $encrypted_counter);

// Algorithm type
update_user_meta($user_id, 'tfa_algorithm_radio', 'totp'); // or 'hotp'
```

**Key Learnings:**
- **All stored in user_meta table** - no custom tables needed
- **Encrypted values** - private keys and emergency codes encrypted
- **Replay prevention** - stores last used codes to prevent reuse
- **Time window tracking** - for TOTP validation
- **Algorithm flexibility** - supports both TOTP and HOTP

---

## 3. TOTP Code Generation

### Generate OTP Pattern
```php
public function generateOTP($user_ID, $key_b64, $length = 6, $counter = false) {
    $key = $this->decryptString($key_b64, $user_ID);
    $alg = $this->get_user_otp_algorithm($user_ID);

    if ('hotp' == $alg) {
        $counter = $counter ? $counter : $this->getUserCounter($user_ID);
        $otp_res = $this->otp_helper->generateByCounter($key, $counter);
    } else {
        // TOTP - time based
        $time = $counter ? $counter : time();
        $otp_res = $this->otp_helper->generateByTime($key, $this->time_window_size, $time);
    }

    return $otp_res->toHotp($length);
}
```

**Key Learnings:**
- **HOTP library** - uses standard HOTP class (RFC 4226/6238 compliant)
- **30-second time windows** - standard TOTP window size
- **6-digit codes** - default length
- **Decrypt before use** - keys stored encrypted, decrypted only when needed
- **Algorithm abstraction** - supports both TOTP (time) and HOTP (counter)

---

## 4. Code Verification & Replay Prevention

### Verification Pattern
```php
public function check_code_for_user($user_id, $user_code, $allow_emergency_code = true) {
    $tfa_priv_key = get_user_meta($user_id, 'tfa_priv_key_64', true);
    $tfa_last_pws = get_user_meta($user_id, 'tfa_last_pws', true);

    // Generate valid codes for time window (1.5 minutes)
    $codes = $this->generate_otps_for_login_check($user_id, $tfa_priv_key);

    // Check if code was recently used (replay attack prevention)
    if (in_array($this->hash($user_code, $user_id), $tfa_last_pws)) {
        return false;
    }

    $match = false;
    foreach ($codes as $index => $code) {
        if (hash_equals(trim($code->toHotp(6)), trim($user_code))) {
            $match = true;
            break;
        }
    }

    // Check emergency codes if normal code doesn't match
    if (!$match && $allow_emergency_code) {
        $emergency_codes = get_user_meta($user_id, 'simba_tfa_emergency_codes_64', true);
        foreach ($emergency_codes as $key => $emergency_code) {
            $dec = trim($this->decryptString($emergency_code, $user_id));
            if (hash_equals($dec, trim($user_code))) {
                $match = true;
                unset($emergency_codes[$key]); // One-time use
                update_user_meta($user_id, 'simba_tfa_emergency_codes_64', $emergency_codes);
                break;
            }
        }
    }

    if ($match) {
        // Store used code to prevent replay
        $tfa_last_pws[] = $this->hash($user_code, $user_id);
        update_user_meta($user_id, 'tfa_last_pws', $tfa_last_pws);
        update_user_meta($user_id, 'tfa_last_login', $current_time_window);
    }

    return $match;
}
```

**Key Learnings:**
- **Time window flexibility** - checks codes from 1.5 minutes (3 time windows)
- **Timing-safe comparison** - uses `hash_equals()` to prevent timing attacks
- **Replay prevention** - stores hashes of recently used codes
- **Emergency codes** - fallback for device loss
- **One-time emergency codes** - removed after use
- **Constant-time string comparison** - critical for security

---

## 5. Login Flow Integration

### Authenticate Filter Hook
```php
// In constructor
add_filter('authenticate', array($this, 'tfaVerifyCodeAndUser'), 99999999999, 3);

public function tfaVerifyCodeAndUser($user, $username, $password) {
    // Skip if not a WP_User object (already failed authentication)
    if (!($user instanceof WP_User)) {
        return $user;
    }

    // Check if 2FA is enabled for this user
    if (!$this->is_activated_for_user($user->ID)) {
        return $user;
    }

    // User authenticated with password, now check 2FA
    // If no 2FA code provided yet, return error prompting for code
    // If code provided, verify it

    return $user_or_error;
}
```

**Key Learnings:**
- **Very late priority** - 99999999999 to run after password verification
- **Non-intrusive** - only affects users with 2FA enabled
- **Returns WP_Error** - if 2FA fails, prevents login
- **AJAX-based** - uses JavaScript to handle 2FA prompt without page reload

---

## 6. QR Code Generation

### QR Code for Secret Setup
```javascript
// Uses jquery-qrcode library
jQuery('#tfa_qr_code').empty().qrcode({
    text: qr_url,
    width: 200,
    height: 200
});

// QR URL format
otpauth://totp/{site_name}:{username}?secret={secret}&issuer={site_name}
```

**Key Learnings:**
- **Client-side QR generation** - jquery-qrcode.js library
- **Standard otpauth:// URI** - works with Google Authenticator, Authy, etc.
- **Includes issuer** - helps users identify which site
- **200x200px** - standard readable size

---

## 7. Failed Attempts Tracking

### Brute Force Protection
```php
// Constants
define('TFA_INCORRECT_MAX_ATTEMPTS_ALLOWED_LIMIT', 5);
define('TFA_INCORRECT_ATTEMPTS_WITHIN_MINUTES_LIMIT', 30);

// Track incorrect attempts
$incorrect_attempts = get_user_meta($user_id, 'tfa_incorrect_attempts', true);
$attempt_times = get_user_meta($user_id, 'tfa_attempt_times', true);

// Check if too many attempts
if (count($attempt_times) >= TFA_INCORRECT_MAX_ATTEMPTS_ALLOWED_LIMIT) {
    // Check if within time limit
    if ((time() - min($attempt_times)) < (TFA_INCORRECT_ATTEMPTS_WITHIN_MINUTES_LIMIT * 60)) {
        return new WP_Error('too_many_attempts', __('Too many incorrect codes'));
    }
}
```

**Key Learnings:**
- **Attempt limiting** - 5 failed attempts in 30 minutes
- **Time-based lockout** - temporary lockout, not permanent
- **User-specific** - tracked per user
- **Configurable** - via constants

---

## 8. Emergency Backup Codes

### Generation & Usage
```php
// Generate emergency codes
public function generate_emergency_codes($user_id, $count = 10) {
    $codes = array();
    for ($i = 0; $i < $count; $i++) {
        $code = $this->randString($this->emergency_codes_length);
        $codes[] = $this->encryptString($code, $user_id);
    }
    update_user_meta($user_id, 'simba_tfa_emergency_codes_64', $codes);
    return $codes;
}

// One-time use
if (hash_equals($dec, trim($user_code))) {
    $match = true;
    unset($emergency_codes[$key]); // Remove after use
    update_user_meta($user_id, 'simba_tfa_emergency_codes_64', $emergency_codes);
}
```

**Key Learnings:**
- **Pre-generated codes** - created when 2FA is enabled
- **One-time use** - deleted after successful use
- **8-character codes** - easier to type than 6-digit TOTP
- **Encrypted storage** - same security as TOTP secrets
- **Count tracking** - admin can see how many remain

---

## 9. Provider Pattern

### Modular Algorithm Support
```php
// Load providers
$load_providers = apply_filters('simbatfa_load_providers', array('totp'));

foreach ($load_providers as $provider_id) {
    $class_name = "Simba_TFA_Provider_$provider_id";
    if (!class_exists($class_name)) {
        require_once(__DIR__.'/providers/'.$provider_id.'/loader.php');
    }
    $this->controllers[$provider_id] = new $class_name($this);
}
```

**Key Learnings:**
- **Provider-based architecture** - extensible for future 2FA methods
- **Filter for extensibility** - other plugins can add providers
- **Current providers** - TOTP is primary, HOTP supported
- **Future-proof** - could add SMS, email, hardware tokens, etc.

---

## 10. Encryption Implementation

### OpenSSL Encryption
```php
public function encryptString($string, $salt_suffix, $force_encrypt = false) {
    $key = base64_decode(SIMBA_TFA_DB_ENCRYPTION_KEY);

    $iv_size = openssl_cipher_iv_length('AES-128-CBC');
    $iv = random_bytes($iv_size);

    $enc = openssl_encrypt($string, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

    $enc = $iv.$enc; // Prepend IV
    return base64_encode($enc);
}

private function decryptString($enc_b64, $salt_suffix) {
    $key = base64_decode(SIMBA_TFA_DB_ENCRYPTION_KEY);

    $enc_conc = base64_decode($enc_b64);
    $iv_size = openssl_cipher_iv_length('AES-128-CBC');

    $iv = substr($enc_conc, 0, $iv_size);
    $enc = substr($enc_conc, $iv_size);

    return openssl_decrypt($enc, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
}
```

**Key Learnings:**
- **AES-128-CBC** - industry standard encryption
- **Random IV** - new IV for each encryption (critical!)
- **IV prepended** - stored with ciphertext
- **OpenSSL preferred** - fallback to mcrypt for old PHP
- **Base64 encoding** - for database storage

---

## Application to Vivek DevOps 2FA

### We Will Implement:

1. **TOTP-based authentication** - using proven HOTP/TOTP library
2. **Encrypted secret storage** - encryption key in wp-config.php or mu-plugin
3. **User meta storage** - no custom tables needed
4. **QR code generation** - jquery-qrcode for easy setup
5. **Emergency backup codes** - 10 codes, one-time use
6. **Replay prevention** - track last used codes
7. **Time window flexibility** - 1.5 minute window for code entry
8. **Failed attempt limiting** - 5 attempts in 30 minutes
9. **Custom 2FA page** - `/vsc-2fa` instead of inline prompt
10. **Integration with login module** - hooks into custom login flow

### Our Specific Additions:

1. **Mandatory for master admin** - Mighty-Vivek must use 2FA
2. **Custom 2FA URL** - `/vsc-2fa` page instead of modal
3. **Activity logging integration** - log all 2FA attempts
4. **SMTP integration** - email on failed 2FA attempts
5. **Honeypot integration** - failed 2FA counts toward IP blocking
6. **Dashboard display** - show 2FA status prominently

### VSC User Meta Keys:
```php
// Per user
'vsc_tfa_enabled' => '1' or '0'
'vsc_tfa_secret' => 'encrypted_base32_secret'
'vsc_tfa_last_codes' => array of hashed codes
'vsc_tfa_emergency_codes' => array of encrypted emergency codes
'vsc_tfa_failed_attempts' => array of timestamps
'vsc_tfa_setup_date' => datetime of setup
```

### 2FA Flow for VSC:
1. User logs in at `/vsc` with username + password
2. If 2FA enabled → redirect to `/vsc-2fa`
3. Show QR code if first time, otherwise show code input
4. Verify code with time window tolerance
5. Check replay prevention
6. Log attempt (success or failure)
7. On success → redirect to `/vsc-dashboard`
8. On failure → increment attempts, possibly block IP

---

---

# Plugin 4: Code Snippets (v3.9.1)

**Purpose:** Custom PHP/CSS/JS code execution system for Vivek DevOps Code Injector module.

## Technical Implementation Details

### Core Files Analyzed:
- `php/class-db.php` (350 lines) - Database table management, caching, multisite support
- `php/snippet-ops.php` (772 lines) - CRUD operations, validation, execution
- `php/evaluation/class-evaluate-functions.php` (194 lines) - Snippet evaluation engine
- `php/class-validator.php` - PHP code validation before execution

### Key Implementation Insights:

**1. WordPress Cache Integration:**
Code Snippets uses `wp_cache_get/set()` with a custom cache group (`CACHE_GROUP`) to cache active snippets, reducing database queries.

**2. Multisite Support:**
Separate tables for site-wide (`wp_snippets`) and network-wide (`wp_ms_snippets`) snippets, with `active_shared_network_snippets` option for cross-site activation.

**3. Code Validation:**
Uses a `Validator` class to parse PHP code with `token_get_all()` before activation, preventing syntax errors from breaking the site.

**4. Auto-Deactivation on Errors:**
When saving a snippet, if validation or test execution fails, the snippet is automatically deactivated (`active = 0`) to prevent site breakage.

**5. Revision Tracking:**
Each snippet has a `revision` column that increments on updates, enabling version history.

**6. Flat File Execution:**
Alternative to database storage - snippets can be loaded from PHP files using `require_once` instead of `eval()`.

---

## Pattern 1: Snippet Database Table Structure

Code Snippets uses a dedicated table with smart indexing for snippet management.

### Implementation:
```php
// php/class-db.php
public function create_table( $table_name, $wpdb ) {
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id           BIGINT(20)   NOT NULL AUTO_INCREMENT,
        name         TINYTEXT     NOT NULL,
        description  TEXT         NOT NULL DEFAULT '',
        code         LONGTEXT     NOT NULL DEFAULT '',
        tags         LONGTEXT     NOT NULL DEFAULT '',
        scope        VARCHAR(15)  NOT NULL DEFAULT 'global',
        priority     SMALLINT     NOT NULL DEFAULT 10,
        active       TINYINT(1)   NOT NULL DEFAULT 0,
        modified     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY scope (scope(10)),
        KEY active (active)
    ) $charset_collate;";

    $wpdb->query( $sql );
}
```

### Key Features:
- `LONGTEXT` for code (up to 4GB)
- `scope` field: 'global', 'admin', 'front-end', 'single-use'
- `priority` for execution order (default: 10)
- `active` flag for enabling/disabling snippets
- Indexed on `scope` and `active` for fast queries
- `modified` timestamp with auto-update

### Application to Vivek DevOps:
```php
// Our vsc_code_snippets table structure
CREATE TABLE `{$wpdb->prefix}vsc_code_snippets` (
    `snippet_id` bigint(20) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text,
    `code` longtext NOT NULL,
    `language` enum('php','css','js') NOT NULL DEFAULT 'php',
    `scope` varchar(20) NOT NULL DEFAULT 'global',
    `priority` smallint NOT NULL DEFAULT 10,
    `active` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `modified_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`snippet_id`),
    KEY `scope` (`scope`),
    KEY `active` (`active`),
    KEY `language` (`language`)
) $charset_collate;
```

## Pattern 2: Safe Code Execution with eval()

Code Snippets uses output buffering and try-catch to safely execute user code.

### Implementation:
```php
// php/snippet-ops.php
function execute_snippet( $code, $id = 0, $force = false ) {
    if ( empty( $code ) ) {
        return false;
    }

    // Check safe mode
    if ( ! $force && defined( 'CODE_SNIPPETS_SAFE_MODE' ) && CODE_SNIPPETS_SAFE_MODE ) {
        return false;
    }

    // Start output buffering to capture any output
    ob_start();

    try {
        // Execute the code
        $result = eval( $code );
    } catch ( ParseError $parse_error ) {
        // Handle parse errors gracefully
        $result = $parse_error;
    } catch ( Error $error ) {
        $result = $error;
    }

    // Clean the output buffer without displaying
    ob_end_clean();

    do_action( 'code_snippets/after_execute_snippet', $id );

    return $result;
}
```

### Key Features:
- Output buffering prevents accidental echo/print
- Try-catch handles ParseError and Error
- Safe mode constant for emergency disable
- Hook for post-execution logging
- Returns result for error checking

### Application to Vivek DevOps:
```php
// modules/code-injector/execute.php
function vsc_execute_snippet( $code, $snippet_id, $language = 'php' ) {
    if ( defined( 'VSC_SAFE_MODE' ) && VSC_SAFE_MODE ) {
        return new WP_Error( 'safe_mode', 'Code execution disabled in safe mode' );
    }

    if ( $language === 'php' ) {
        ob_start();
        try {
            $result = eval( $code );
            $output = ob_get_clean();

            // Log successful execution
            vsc_log_activity( 'snippet_executed', 'code_snippet', $snippet_id, 'info' );

            return $result;
        } catch ( ParseError | Error $e ) {
            ob_end_clean();

            // Log error and auto-deactivate snippet
            vsc_log_activity( 'snippet_error', 'code_snippet', $snippet_id, 'critical', [
                'error' => $e->getMessage()
            ]);
            vsc_deactivate_snippet( $snippet_id );

            return new WP_Error( 'execution_error', $e->getMessage() );
        }
    }
}
```

## Pattern 3: Snippet Scopes and Conditional Execution

Code Snippets executes snippets based on scope (global, admin, front-end, single-use).

### Implementation:
```php
// php/evaluation/class-evaluate-functions.php:153-175
public function evaluate_db_snippets(): bool {
    // Determine scopes to execute
    $scopes = [ 'global', 'single-use', is_admin() ? 'admin' : 'front-end' ];
    $active_snippets = $this->db->fetch_active_snippets( $scopes );

    foreach ( $active_snippets as $snippet ) {
        $snippet_id = $snippet['id'];
        $code = $snippet['code'];

        // Single-use snippets are auto-deactivated before execution
        if ( 'single-use' === $snippet['scope'] ) {
            $this->quick_deactivate_snippet( $snippet_id, $table_name );
        }

        if ( apply_filters( 'code_snippets/allow_execute_snippet', true, $snippet_id, $table_name ) ) {
            execute_snippet( $code, $snippet_id );
        }
    }

    return true;
}
```

### Key Features:
- Dynamic scope selection based on `is_admin()`
- Single-use snippets auto-deactivate before execution
- Filter hook for external control
- Runs early on `plugins_loaded` (priority 1)

### Application to Vivek DevOps:
```php
// Scope-based execution for VSC
add_action( 'plugins_loaded', 'vsc_execute_active_snippets', 1 );
function vsc_execute_active_snippets() {
    global $wpdb;

    $scope = is_admin() ? 'admin' : 'front-end';
    $snippets = $wpdb->get_results( $wpdb->prepare(
        "SELECT snippet_id, code, language, scope
         FROM {$wpdb->prefix}vsc_code_snippets
         WHERE active = 1 AND scope IN ('global', %s)
         ORDER BY priority ASC",
        $scope
    ) );

    foreach ( $snippets as $snippet ) {
        if ( $snippet->language === 'php' ) {
            vsc_execute_snippet( $snippet->code, $snippet->snippet_id, 'php' );
        }
    }
}
```

## Pattern 4: Priority-Based Execution Order

Snippets execute in priority order (lower number = earlier execution).

### Implementation:
```php
// Database query with ORDER BY
$wpdb->get_results(
    "SELECT * FROM {$wpdb->prefix}snippets
     WHERE active = 1 AND scope IN ('global', 'admin')
     ORDER BY priority ASC, id ASC"
);
```

### Application to Vivek DevOps:
- Use `priority` field for execution order
- Default priority: 10
- Critical snippets: priority 1-5
- Normal snippets: priority 10
- Late snippets: priority 15-20

---

# Plugin 5: WP Mail SMTP (v4.7.0)

**Purpose:** SMTP configuration and email management for Vivek DevOps SMTP Mail Router.

## Technical Implementation Details

### Core Files Analyzed:
- `src/MailCatcherTrait.php` (559 lines) - Main email sending logic with hooks
- `src/Providers/MailerAbstract.php` (703 lines) - Abstract base class for all mailers
- `src/Options.php` (49,221 lines) - Centralized options management
- `src/Core.php` (34,254 lines) - Plugin initialization and core functionality

### Key Implementation Insights:

**1. MailCatcher Pattern:**
WP Mail SMTP extends PHPMailer by using a `MailCatcherTrait` that overrides the `send()` method to intercept all emails before sending.

**2. Provider Abstraction:**
All email providers (SMTP, Gmail, SendGrid, Brevo, etc.) extend `MailerAbstract` class, providing a consistent interface for different services.

**3. Email Blocking System:**
Checks `wp_mail_smtp()->is_blocked()` before sending, with special exemption for test emails (identified by `X-Mailer-Type` header).

**4. Debug Events:**
Uses `DebugEvents::add_debug()` to log all email attempts, with SMTP debug level 3 (commands, data, connection status) for troubleshooting.

**5. API vs SMTP Routing:**
Automatically routes to `smtp_send()` for SMTP/Mail/Pepipost mailers, or `api_send()` for API-based providers (SendGrid, Mailgun, etc.).

**6. Error Handling:**
Catches exceptions during send, logs to `Debug::set()`, and fires `wp_mail_smtp_mailcatcher_send_failed` action hook.

**7. Options Storage:**
All settings stored in single `wp_options` entry (`wp_mail_smtp`) as serialized array, with encryption for passwords using `Crypto` helper class.

---

## Pattern 1: wp_mail() Function Override

WP Mail SMTP intercepts the default `wp_mail()` function using PHPMailer filters.

### Implementation:
```php
// src/MailCatcherTrait.php
add_action( 'phpmailer_init', [ $this, 'phpmailer_init' ], PHP_INT_MAX );

public function phpmailer_init( $phpmailer ) {
    // Set SMTP configuration
    $phpmailer->isSMTP();
    $phpmailer->Host       = get_option( 'wp_mail_smtp_host' );
    $phpmailer->Port       = get_option( 'wp_mail_smtp_port' );
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Username   = get_option( 'wp_mail_smtp_username' );
    $phpmailer->Password   = $this->get_decrypted_password();
    $phpmailer->SMTPSecure = get_option( 'wp_mail_smtp_encryption' ); // 'tls' or 'ssl'
}
```

### Key Features:
- Uses `phpmailer_init` hook with maximum priority
- Doesn't replace `wp_mail()`, just configures PHPMailer
- Supports TLS/SSL encryption
- Password stored encrypted in wp_options

### Application to Vivek DevOps:
```php
// modules/smtp-router/smtp-config.php
add_action( 'phpmailer_init', 'vsc_configure_smtp', PHP_INT_MAX );
function vsc_configure_smtp( $phpmailer ) {
    $smtp_settings = get_option( 'vsc_smtp_settings', [] );

    if ( empty( $smtp_settings['enabled'] ) ) {
        return; // SMTP not enabled
    }

    $phpmailer->isSMTP();
    $phpmailer->Host       = 'smtp-relay.brevo.com'; // Brevo SMTP
    $phpmailer->Port       = 587;
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Username   = $smtp_settings['username'];
    $phpmailer->Password   = vsc_decrypt( $smtp_settings['password'] );
    $phpmailer->SMTPSecure = 'tls';
    $phpmailer->From       = $smtp_settings['from_email'];
    $phpmailer->FromName   = $smtp_settings['from_name'];

    // Set daily limit tracking
    vsc_track_email_sent();
}
```

## Pattern 2: Provider-Based Configuration

WP Mail SMTP supports multiple providers (Gmail, SendGrid, Brevo, etc.) with different auth methods.

### Implementation:
```php
// src/Providers/ProviderAbstract.php
abstract class ProviderAbstract {
    protected $mailer;
    protected $options;

    abstract public function set_from( $phpmailer );
    abstract public function set_return_path( $phpmailer );
    abstract public function is_valid_setup();
}

// src/Providers/Gmail/Mailer.php
class Gmail extends ProviderAbstract {
    public function set_from( $phpmailer ) {
        if ( ! empty( $this->options['gmail']['user_email'] ) ) {
            $phpmailer->From = $this->options['gmail']['user_email'];
        }
    }

    public function is_valid_setup() {
        return ! empty( $this->options['gmail']['client_id'] ) &&
               ! empty( $this->options['gmail']['client_secret'] );
    }
}
```

### Application to Vivek DevOps:
- Vivek DevOps uses Brevo exclusively (no multi-provider)
- Simplified configuration with hardcoded SMTP host
- Daily limit: 300 emails/day
- Track usage in wp_options

## Pattern 3: Email Logging and Debugging

WP Mail SMTP logs all email attempts for debugging.

### Implementation:
```php
// Log email sending
add_action( 'wp_mail_failed', 'wp_mail_smtp_log_failed', 10, 1 );
function wp_mail_smtp_log_failed( $wp_error ) {
    global $wpdb;

    $wpdb->insert(
        $wpdb->prefix . 'wp_mail_smtp_emails',
        [
            'subject'    => $wp_error->get_error_data( 'wp_mail_failed' )['subject'],
            'recipients' => implode( ',', $wp_error->get_error_data( 'wp_mail_failed' )['to'] ),
            'status'     => 'failed',
            'error'      => $wp_error->get_error_message(),
            'date'       => current_time( 'mysql' ),
        ]
    );
}
```

### Application to Vivek DevOps:
```php
// Log in activity log instead of separate table
add_action( 'wp_mail_succeeded', 'vsc_log_email_success' );
add_action( 'wp_mail_failed', 'vsc_log_email_failure' );

function vsc_log_email_success( $mail_data ) {
    vsc_log_activity( 'email_sent', 'email', 0, 'info', [
        'to'      => implode( ',', $mail_data['to'] ),
        'subject' => $mail_data['subject'],
    ]);
}

function vsc_log_email_failure( $wp_error ) {
    vsc_log_activity( 'email_failed', 'email', 0, 'warning', [
        'error' => $wp_error->get_error_message(),
    ]);
}
```

## Pattern 4: Daily Send Limit Tracking

Track email sending to prevent exceeding provider limits.

### Implementation:
```php
// Track daily sends
function vsc_track_email_sent() {
    $today = date( 'Y-m-d' );
    $count = get_transient( 'vsc_smtp_daily_count_' . $today );

    if ( $count === false ) {
        set_transient( 'vsc_smtp_daily_count_' . $today, 1, DAY_IN_SECONDS );
    } else {
        set_transient( 'vsc_smtp_daily_count_' . $today, $count + 1, DAY_IN_SECONDS );
    }

    // Check if limit exceeded (Brevo: 300/day)
    if ( $count >= 300 ) {
        vsc_log_activity( 'smtp_limit_exceeded', 'smtp', 0, 'critical' );
        // Optionally disable SMTP temporarily
    }
}
```

---

# Plugin 6: All-in-One WP Migration (v7.101)

**Purpose:** Backup and migration functionality for Vivek DevOps additional feature.

## Pattern 1: Custom .wpress Archive Format

All-in-One WP Migration uses a proprietary `.wpress` format for backup files.

### Implementation:
```php
// constants.php:94-100
define( 'AI1WM_ARCHIVE_TOOLS_URL', 'https://servmask.com/archive/tools' );

// functions.php:94-101
function ai1wm_is_filename_supported( $filename ) {
    if ( pathinfo( $filename, PATHINFO_EXTENSION ) === 'wpress' ) {
        return true;
    }
    return false;
}

// Archive contains:
// - database.sql (MySQL dump with find/replace)
// - package.json (metadata: PHP version, WP version, plugins, themes)
// - multisite.json (multisite configuration if applicable)
// - wp-content/ (files: uploads, plugins, themes)
```

### Key Features:
- Single file for entire site
- Includes database + files
- Metadata for compatibility checks
- Gzip compression
- Self-contained (can restore anywhere)

### Application to Vivek DevOps:
```php
// modules/backup/backup-manager.php
class VSC_Backup_Manager {

    public function create_backup( $name = '' ) {
        $timestamp = time();
        $backup_name = $name ?: 'vsc-backup-' . date( 'Y-m-d-His', $timestamp ) . '.vscbackup';
        $backup_path = WP_CONTENT_DIR . '/vsc-backups/' . $backup_name;

        // Create backup archive
        $archive = new ZipArchive();
        $archive->open( $backup_path, ZipArchive::CREATE );

        // Add database dump
        $sql_file = $this->export_database();
        $archive->addFile( $sql_file, 'database.sql' );

        // Add metadata
        $metadata = [
            'timestamp'   => $timestamp,
            'wp_version'  => get_bloginfo( 'version' ),
            'php_version' => PHP_VERSION,
            'site_url'    => site_url(),
            'home_url'    => home_url(),
        ];
        $archive->addFromString( 'metadata.json', json_encode( $metadata ) );

        // Add critical files (wp-config.php, .htaccess)
        if ( file_exists( ABSPATH . 'wp-config.php' ) ) {
            $archive->addFile( ABSPATH . 'wp-config.php', 'wp-config.php' );
        }

        $archive->close();

        return $backup_path;
    }
}
```

## Pattern 2: Chunked File Processing

All-in-One WP Migration uses chunking to handle large files without memory issues.

### Implementation:
```php
// constants.php:434-440
define( 'AI1WM_MAX_CHUNK_SIZE', 5 * 1024 * 1024 ); // 5MB chunks
define( 'AI1WM_MAX_CHUNK_RETRIES', 10 );

// Chunked read/write
function ai1wm_copy( $source, $destination, $chunk_size = AI1WM_MAX_CHUNK_SIZE ) {
    $source_handle = fopen( $source, 'rb' );
    $dest_handle = fopen( $destination, 'wb' );

    while ( ! feof( $source_handle ) ) {
        $chunk = fread( $source_handle, $chunk_size );
        fwrite( $dest_handle, $chunk );
    }

    fclose( $source_handle );
    fclose( $dest_handle );
}
```

### Application to Vivek DevOps:
```php
// For large file operations (database dump, file scanning)
define( 'VSC_CHUNK_SIZE', 512 * 1024 ); // 512KB chunks

function vsc_chunk_file_operation( $file_path, $callback ) {
    $handle = fopen( $file_path, 'rb' );
    $offset = 0;

    while ( ! feof( $handle ) ) {
        $chunk = fread( $handle, VSC_CHUNK_SIZE );
        call_user_func( $callback, $chunk, $offset );
        $offset += VSC_CHUNK_SIZE;
    }

    fclose( $handle );
}
```

## Pattern 3: Incremental Backup with Lists

All-in-One WP Migration creates list files to track what's included in backups.

### Implementation:
```php
// constants.php:143-165
define( 'AI1WM_CONTENT_LIST_NAME', 'content.list' );
define( 'AI1WM_MEDIA_LIST_NAME', 'media.list' );
define( 'AI1WM_PLUGINS_LIST_NAME', 'plugins.list' );
define( 'AI1WM_THEMES_LIST_NAME', 'themes.list' );
define( 'AI1WM_TABLES_LIST_NAME', 'tables.list' );

// Create list of files to backup
function ai1wm_create_content_list( $storage_path ) {
    $list_file = fopen( $storage_path . '/content.list', 'w' );

    $iterator = new RecursiveDirectoryIterator( WP_CONTENT_DIR );
    $iterator = new RecursiveIteratorIterator( $iterator );

    foreach ( $iterator as $file ) {
        if ( $file->isFile() ) {
            fputcsv( $list_file, [ $file->getPathname(), $file->getSize() ] );
        }
    }

    fclose( $list_file );
}
```

### Application to Vivek DevOps:
- For file integrity checker: Create baseline file list
- Store in vsc_integrity_baseline table instead of files
- Compare current state vs. baseline

## Pattern 4: Database Export with Table Prefix Replacement

All-in-One WP Migration replaces table prefixes during export for portability.

### Implementation:
```php
// lib/model/export/class-ai1wm-export-database.php:112-138
$old_table_prefixes = $new_table_prefixes = array();

// Replace wp_ with SERVMASK_PREFIX_
if ( ai1wm_table_prefix() ) {
    $old_table_prefixes[] = ai1wm_table_prefix(); // e.g., 'wp_'
    $new_table_prefixes[] = 'SERVMASK_PREFIX_';   // Placeholder
}

// Set column prefixes (for option_name, meta_key)
$old_column_prefixes = [ 'wp_user_roles', 'wp_capabilities' ];
$new_column_prefixes = [ 'SERVMASK_PREFIX_user_roles', 'SERVMASK_PREFIX_capabilities' ];

$db_client->set_old_table_prefixes( $old_table_prefixes )
    ->set_new_table_prefixes( $new_table_prefixes )
    ->set_old_column_prefixes( $old_column_prefixes )
    ->set_new_column_prefixes( $new_column_prefixes );
```

### Key Features:
- Makes backups portable across sites
- Replaces in table names and column values
- Handles user meta keys (wp_capabilities → SERVMASK_PREFIX_capabilities)
- On import, replaces SERVMASK_PREFIX_ with target site's prefix

### Application to Vivek DevOps:
```php
// Database export for VSC backups
function vsc_export_database() {
    global $wpdb;

    $tables = $wpdb->get_col( "SHOW TABLES" );
    $sql_dump = '';

    foreach ( $tables as $table ) {
        // Export table structure
        $create_table = $wpdb->get_row( "SHOW CREATE TABLE `$table`", ARRAY_N );
        $sql_dump .= $create_table[1] . ";\n\n";

        // Export table data with chunking
        $offset = 0;
        while ( $rows = $wpdb->get_results( "SELECT * FROM `$table` LIMIT $offset, 1000", ARRAY_A ) ) {
            foreach ( $rows as $row ) {
                $values = array_map( [ $wpdb, 'prepare' ], array_fill( 0, count( $row ), '%s' ), $row );
                $sql_dump .= "INSERT INTO `$table` VALUES (" . implode( ', ', $values ) . ");\n";
            }
            $offset += 1000;
        }

        $sql_dump .= "\n";
    }

    return $sql_dump;
}
```

## Pattern 5: Backup File Management with Labels

All-in-One WP Migration allows custom labels for backups (stored in wp_options).

### Implementation:
```php
// lib/model/class-ai1wm-backups.php:122-139
public static function set_label( $file, $label ) {
    if ( ( $labels = get_option( AI1WM_BACKUPS_LABELS, array() ) ) !== false ) {
        $labels[ $file ] = $label;
    }
    return update_option( AI1WM_BACKUPS_LABELS, $labels );
}

public static function get_labels() {
    return get_option( AI1WM_BACKUPS_LABELS, array() );
}
```

### Application to Vivek DevOps:
```php
// Store backup metadata in wp_options
$backup_meta = get_option( 'vsc_backup_metadata', [] );
$backup_meta[ $backup_name ] = [
    'label'       => $custom_label,
    'created_at'  => time(),
    'size'        => filesize( $backup_path ),
    'tables'      => count( $tables ),
    'description' => 'Pre-update backup',
];
update_option( 'vsc_backup_metadata', $backup_meta );
```

## Pattern 6: Storage Path Validation

All-in-One WP Migration validates file paths to prevent directory traversal attacks.

### Implementation:
```php
// functions.php:113-127
function ai1wm_validate_file( $file, $allowed_files = array() ) {
    $file = str_replace( '\\', '/', $file );

    // Validates special characters that are illegal in filenames
    $invalid_chars = array( '<', '>', ':', '"', '|', '?', '*', chr( 0 ) );
    foreach ( $invalid_chars as $char ) {
        if ( strpos( $file, $char ) !== false ) {
            return 1; // Invalid
        }
    }

    return validate_file( $file, $allowed_files ); // WordPress core function
}
```

### Application to Vivek DevOps:
```php
// Validate all file paths in VSC modules
function vsc_validate_path( $path ) {
    // Remove null bytes
    $path = str_replace( chr( 0 ), '', $path );

    // Check for directory traversal
    if ( strpos( $path, '..' ) !== false ) {
        return new WP_Error( 'invalid_path', 'Directory traversal detected' );
    }

    // Validate filename characters
    $invalid = array( '<', '>', ':', '"', '|', '?', '*' );
    foreach ( $invalid as $char ) {
        if ( strpos( $path, $char ) !== false ) {
            return new WP_Error( 'invalid_chars', 'Invalid characters in path' );
        }
    }

    return true;
}
```

---

## Summary of All Patterns

Now that we've analyzed **6 WordPress plugins** (WP Adminify, Activity Log, Two Factor Authentication, Code Snippets, WP Mail SMTP, All-in-One WP Migration), we have documented **50+ architectural patterns** for building Vivek DevOps:

### Core Architecture (WP Adminify - 13 patterns)
1. Singleton pattern
2. Module-based architecture
3. Hook-based initialization
4. Database table creation with dbDelta
5. Settings storage (JSON in wp_options)
6. Role-based access control
7. Asset loading (CSS/JS)
8. AJAX with nonces
9. SVG sanitization
10. Custom upload handling
11. Version upgrade handling
12. Admin menu customization
13. Activation/deactivation hooks

### Activity Logging (Activity Log - 10 patterns)
14. Custom database table for logs
15. Hook-based logging architecture
16. Logging API with duplicate prevention
17. IP address detection
18. User data setup (role/ID)
19. Scheduled log cleanup
20. Failed login tracking
21. Custom capabilities
22. Multisite support
23. GDPR compliance

### Two-Factor Authentication (TFA - 10 patterns)
24. Secret storage with encryption
25. User meta storage
26. TOTP/HOTP generation
27. Code verification with timing-safe comparison
28. Login flow integration
29. QR code generation
30. Failed attempts tracking
31. Emergency backup codes
32. Provider pattern
33. Mu-plugin for encryption keys

### Code Execution (Code Snippets - 4 patterns)
34. Snippet database table
35. Safe eval() execution
36. Scope-based execution
37. Priority-based ordering

### Email Management (WP Mail SMTP - 4 patterns)
38. wp_mail() override via phpmailer_init
39. Provider-based configuration
40. Email logging
41. Daily send limit tracking

### Backup System (All-in-One WP Migration - 6 patterns)
42. Custom archive format (.wpress/.vscbackup)
43. Chunked file processing
44. Incremental backup with lists
45. Database export with prefix replacement
46. Backup metadata and labels
47. Storage path validation

## Next Steps

With comprehensive research complete on **6 major WordPress plugins**, we can now build Vivek DevOps (VSC) with:
- ✅ Clean, maintainable architecture
- ✅ WordPress coding standards
- ✅ Security-first approach
- ✅ Modular, extensible design
- ✅ Comprehensive activity logging
- ✅ Performance-optimized database design
- ✅ Industry-standard 2FA implementation
- ✅ Encrypted secret storage
- ✅ Safe code execution with error handling
- ✅ SMTP email routing
- ✅ Backup and migration capabilities

**Ready to begin implementation of Vivek DevOps V-1!**
