# VIVEK DEVOPS SECURITY FIXES

Complete instructions for fixing all security vulnerabilities in the WordPress plugin.

---

## FIX 1: REMOVE ARBITRARY CODE EXECUTION (eval)

### Problem
Lines 579-588 and 621-627 in `includes/class-vsc-snippets.php` use eval() which allows arbitrary PHP code execution. If an admin account is compromised, attacker can execute any code and take over the server.

```php
// DANGEROUS CODE:
case 'php':
    eval('?>' . $code); // This must be removed
```

### Solution

**File:** `includes/class-vsc-snippets.php`

**Step 1:** Find line 579-588 in the `execute_snippet()` method

**Step 2:** Replace the entire 'php' case:
```php
case 'php':
    // PHP execution disabled for security
    if (WP_DEBUG && current_user_can('manage_options')) {
        echo '<!-- VSC: PHP snippets disabled for security. Use WordPress hooks in functions.php instead. -->';
    }
    break;
```

**Step 3:** Find line 621-627 in the `snippet_shortcode()` method

**Step 4:** Replace the 'php' case:
```php
case 'php':
    // PHP execution disabled for security
    if (WP_DEBUG && current_user_can('manage_options')) {
        echo '<!-- VSC: PHP snippets disabled -->';
    }
    break;
```

**Step 5:** Add admin notice in the `admin_enqueue_scripts()` method after line 213:
```php
add_action('admin_notices', function() {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'vsc_snippet') {
        ?>
        <div class="notice notice-warning">
            <p><strong>Security Notice:</strong> PHP snippet execution has been disabled for security. Use WordPress action/filter hooks in your theme's functions.php instead.</p>
        </div>
        <?php
    }
});
```

---

## FIX 2: STOP WRITING LOG FILES TO PLUGIN DIRECTORY

### Problem
Plugin writes log files to the plugin directory which may be publicly accessible via web browser. This exposes sensitive information like file paths, usernames, and system details.

### Solution

**File:** `vivek-devops.php`

**Step 1:** Find lines 39-45 and remove:
```php
// DELETE THIS:
$error_log = VSC_PATH . 'backup-load-error.txt';
$error_msg = date('Y-m-d H:i:s') . " - BACKUP MODULE LOAD ERROR\n";
$error_msg .= "Message: " . $e->getMessage() . "\n";
$error_msg .= "File: " . $e->getFile() . "\n";
$error_msg .= "Line: " . $e->getLine() . "\n";
$error_msg .= "Trace: " . $e->getTraceAsString() . "\n\n";
@file_put_contents($error_log, $error_msg, FILE_APPEND);
```

**Step 2:** Replace with:
```php
if (WP_DEBUG_LOG) {
    error_log('VSC Backup Module Load Error: ' . $e->getMessage());
    error_log('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
}
```

**Step 3:** Find lines 59-89 and replace all `@file_put_contents()` calls with:
```php
if (class_exists('VSC_Backup')) {
    if (WP_DEBUG_LOG) {
        error_log('VSC Backup: Initializing backup module');
    }
    
    try {
        VSC_Backup::get_instance();
        if (WP_DEBUG_LOG) {
            error_log('VSC Backup: Initialized successfully');
        }
    } catch (Throwable $e) {
        if (WP_DEBUG_LOG) {
            error_log('VSC Backup Initialization Error: ' . $e->getMessage());
        }
    }
} else {
    if (WP_DEBUG_LOG) {
        error_log('VSC Backup: Class not found');
    }
}
```

**File:** `includes/class-vsc-backup.php`

**Step 4:** Add helper logging method after line 65 in the class:
```php
/**
 * Log message if debugging is enabled
 */
private function log($message, $level = 'info') {
    if (WP_DEBUG_LOG) {
        error_log(sprintf('[VSC Backup %s] %s', strtoupper($level), $message));
    }
}
```

**Step 5:** Replace all `@file_put_contents($log_file, ...)` calls with `$this->log('message');`

This includes lines: 23-26, 30-33, 37-39, 42-45, 47-48, 56-62, 293, 323, 339-340, 350, 366, 405

Example:
```php
// OLD:
@file_put_contents($log_file, date('Y-m-d H:i:s') . " - Constructor called\n", FILE_APPEND);

// NEW:
$this->log('Constructor called');
```

---

## FIX 3: ADD RATE LIMITING TO LOGIN

### Problem
No protection against brute force attacks. Attacker can make unlimited login attempts.

### Solution

**File:** `includes/class-vsc-auth.php`

**Step 1:** Add these two methods to the class (after line 26):

```php
/**
 * Check rate limit for an action
 */
private function check_rate_limit($identifier, $action = 'login', $max_attempts = 5, $window = 900) {
    $key = 'vsc_rate_limit_' . $action . '_' . md5($identifier);
    $attempts = get_transient($key);
    
    if ($attempts === false) {
        set_transient($key, 1, $window);
        return true;
    }
    
    if ($attempts >= $max_attempts) {
        return false;
    }
    
    set_transient($key, $attempts + 1, $window);
    return true;
}

/**
 * Clear rate limit on successful authentication
 */
private function clear_rate_limit($identifier, $action = 'login') {
    $key = 'vsc_rate_limit_' . $action . '_' . md5($identifier);
    delete_transient($key);
}
```

**Step 2:** In `handle_login_submission()` method (around line 350), add after nonce verification:

```php
// Check rate limit
$ip = $this->get_user_ip();
if (!$this->check_rate_limit($ip, 'login')) {
    $this->login_error('Too many login attempts. Please try again in 15 minutes.');
    return;
}
```

**Step 3:** In `handle_2fa_verification()` method (around line 470), add after nonce verification:

```php
// Check rate limit for 2FA attempts
$ip = $this->get_user_ip();
if (!$this->check_rate_limit($ip, '2fa', 10, 900)) {
    $this->login_error('Too many 2FA attempts. Please try again in 15 minutes.');
    return;
}
```

**Step 4:** After successful authentication (around line 495), add:

```php
// Clear rate limits on successful login
$this->clear_rate_limit($ip, 'login');
$this->clear_rate_limit($ip, '2fa');
```

---

## FIX 4: REMOVE HARDCODED MASTER USERNAME

### Problem
Line 21 in `vivek-devops.php` contains hardcoded username visible in source code:
```php
define('VSC_MASTER_USERNAME', 'Mighty-Vivek');
```

### Solution

**File:** `vivek-devops.php`

**Step 1:** Delete line 21 completely

**File:** `includes/class-vsc-auth.php`

**Step 2:** Add this method to the class:

```php
/**
 * Get master username from secure location
 */
private function get_master_username() {
    // Check wp-config.php first
    if (defined('VSC_MASTER_USERNAME')) {
        return VSC_MASTER_USERNAME;
    }
    
    // Check environment variable
    $env_username = getenv('VSC_MASTER_USERNAME');
    if ($env_username !== false) {
        return $env_username;
    }
    
    // Fall back to database
    $username = get_option('vsc_master_username');
    if (empty($username)) {
        // Generate random username on first run
        $username = 'admin_' . wp_generate_password(12, false);
        update_option('vsc_master_username', $username);
        
        if (WP_DEBUG_LOG) {
            error_log('VSC Master Username Generated: ' . $username);
            error_log('Add to wp-config.php: define("VSC_MASTER_USERNAME", "' . $username . '");');
        }
    }
    
    return $username;
}
```

**Step 3:** Find all uses of `VSC_MASTER_USERNAME` in the class and replace with `$this->get_master_username()`

---

## FIX 5: CREATE CUSTOM DATABASE TABLE FOR HONEYPOT LOGS

### Problem
Using `wp_options` table for honeypot logs causes performance issues. The table is autoloaded on every page load and not designed for high-volume data.

### Solution

**Step 1:** Create new file `includes/class-vsc-database.php`:

```php
<?php
/**
 * VSC Database Management
 */

if (!defined('ABSPATH')) exit;

class VSC_Database {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        register_activation_hook(VSC_PATH . 'vivek-devops.php', [$this, 'create_tables']);
    }

    /**
     * Create custom database tables
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'vsc_honeypot_logs';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            incident_id VARCHAR(20) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            request_uri TEXT,
            reason VARCHAR(255),
            username_attempt VARCHAR(100),
            timestamp DATETIME NOT NULL,
            INDEX idx_ip (ip_address),
            INDEX idx_timestamp (timestamp),
            INDEX idx_incident (incident_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
```

**Step 2:** In `vivek-devops.php`, add after line 24:

```php
require_once VSC_PATH . 'includes/class-vsc-database.php';
```

**Step 3:** In `vsc_init()` function (line 49), add:

```php
VSC_Database::get_instance();
```

**File:** `includes/class-vsc-auth.php`

**Step 4:** Replace the entire `log_honeypot_incident()` method (around line 202):

```php
private function log_honeypot_incident($reason = '') {
    global $wpdb;
    
    $ip_address = $this->get_user_ip();
    $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
    $request_uri = esc_url_raw($_SERVER['REQUEST_URI'] ?? '');
    $timestamp = current_time('mysql');
    $incident_id = 'HP-' . strtoupper(substr(md5($ip_address . $timestamp), 0, 8));
    
    $wpdb->insert(
        $wpdb->prefix . 'vsc_honeypot_logs',
        [
            'incident_id' => $incident_id,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'request_uri' => $request_uri,
            'reason' => sanitize_text_field($reason),
            'username_attempt' => sanitize_text_field($_POST['log'] ?? ''),
            'timestamp' => $timestamp
        ],
        ['%s', '%s', '%s', '%s', '%s', '%s', '%s']
    );
    
    return $incident_id;
}
```

**Step 5:** Add cleanup functionality in the `__construct()` method:

```php
// Schedule cleanup of old logs
add_action('vsc_cleanup_honeypot_logs', [$this, 'cleanup_honeypot_logs']);
if (!wp_next_scheduled('vsc_cleanup_honeypot_logs')) {
    wp_schedule_event(time(), 'daily', 'vsc_cleanup_honeypot_logs');
}
```

**Step 6:** Add the cleanup method to the class:

```php
/**
 * Delete honeypot logs older than 30 days
 */
public function cleanup_honeypot_logs() {
    global $wpdb;
    $wpdb->query(
        "DELETE FROM {$wpdb->prefix}vsc_honeypot_logs 
         WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY)"
    );
}
```

---

## FIX 6: SECURE SESSION MANAGEMENT

### Problem
Sessions are not configured with security parameters. Missing httponly, secure, and samesite flags. No session ID regeneration after login.

### Solution

**File:** `includes/class-vsc-auth.php`

**Step 1:** Replace `configure_session()` method (around line 58):

```php
public function configure_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    
    session_start([
        'cookie_lifetime' => 0,
        'cookie_secure' => is_ssl(),
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true,
        'use_only_cookies' => true,
        'cookie_path' => COOKIEPATH,
        'cookie_domain' => COOKIE_DOMAIN
    ]);
}
```

**Step 2:** In `handle_2fa_verification()` method, after successful verification (around line 495), add:

```php
// Regenerate session ID to prevent session fixation
if (session_status() === PHP_SESSION_ACTIVE) {
    session_regenerate_id(true);
}
```

---

## FIX 7: SECURE IP ADDRESS DETECTION

### Problem
Trusting `X-Forwarded-For` and other easily-spoofed HTTP headers without validation.

### Solution

**File:** `includes/class-vsc-auth.php`

**Replace the entire `get_user_ip()` method (around line 239):**

```php
private function get_user_ip() {
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Only trust proxy headers if behind a configured trusted proxy
    if (defined('VSC_TRUSTED_PROXY') && $_SERVER['REMOTE_ADDR'] === VSC_TRUSTED_PROXY) {
        $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        
        if (!empty($forwarded)) {
            $ips = array_map('trim', explode(',', $forwarded));
            $validated_ip = filter_var($ips[0], FILTER_VALIDATE_IP);
            if ($validated_ip !== false) {
                $ip = $validated_ip;
            }
        }
    }
    
    // Validate final IP address
    $validated = filter_var($ip, FILTER_VALIDATE_IP);
    return $validated !== false ? $validated : '0.0.0.0';
}
```

---

## FIX 8: ADD SECURITY HEADERS

### Problem
Missing security headers that protect against common attacks.

### Solution

**File:** `vivek-devops.php`

**Add at the end of the file (before closing PHP tag):**

```php
/**
 * Add security headers
 */
function vsc_add_security_headers() {
    if (is_admin()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        if (is_ssl()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}
add_action('send_headers', 'vsc_add_security_headers');
```

---

## FIX 9: SANITIZE ALL SERVER INPUT

### Problem
Direct access to `$_SERVER` variables without sanitization in multiple places.

### Solution

**Search entire codebase for these patterns and sanitize:**

**Pattern 1:** `$_SERVER['HTTP_USER_AGENT']`
```php
// OLD:
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

// NEW:
$user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
```

**Pattern 2:** `$_SERVER['REQUEST_URI']`
```php
// OLD:
$request_uri = $_SERVER['REQUEST_URI'] ?? '';

// NEW:
$request_uri = esc_url_raw($_SERVER['REQUEST_URI'] ?? '');
```

**Pattern 3:** `$_SERVER['HTTP_REFERER']`
```php
// OLD:
$referer = $_SERVER['HTTP_REFERER'] ?? '';

// NEW:
$referer = esc_url_raw($_SERVER['HTTP_REFERER'] ?? '');
```

Apply these changes wherever these variables are used in the codebase.

---

## FIX 10: MOVE BACKUP STORAGE OUTSIDE WEB ROOT

### Problem
Backups are stored in the plugin directory which may be web-accessible.

### Solution

**File:** `includes/class-vsc-backup.php`

**In the `define_constants()` method (around line 82), replace:**

```php
// OLD:
define('VSC_BACKUP_STORAGE_PATH', VSC_PATH . 'storage');
define('VSC_BACKUP_BACKUPS_PATH', VSC_BACKUP_STORAGE_PATH . '/backups');

// NEW:
$upload_dir = wp_upload_dir();
$backup_base = dirname($upload_dir['basedir']); // wp-content directory
define('VSC_BACKUP_STORAGE_PATH', $backup_base . '/vsc-backups');
define('VSC_BACKUP_BACKUPS_PATH', VSC_BACKUP_STORAGE_PATH . '/backups');
```

---

## TESTING AFTER FIXES

Test each fix to verify it works:

### Test 1: eval() Removal
- Try to create a PHP snippet
- Verify it doesn't execute
- Check that CSS/JS/HTML snippets still work

### Test 2: Logging
- Enable WP_DEBUG_LOG in wp-config.php
- Trigger plugin actions
- Check logs appear in wp-content/debug.log
- Verify no log files in plugin directory

### Test 3: Rate Limiting
- Attempt 6 failed logins
- Verify you're blocked after 5 attempts
- Wait 15 minutes and verify access restored

### Test 4: Database Table
- Check database for table: `wp_vsc_honeypot_logs`
- Access wp-login.php and verify log entry created
- Verify no logs in wp_options table

### Test 5: Session Security
- Login and inspect cookies in browser
- Verify secure, httponly, samesite=strict flags present

### Test 6: General Functionality
- Complete login flow (username + 2FA)
- Create/edit/delete snippets
- Run backup
- Navigate all admin pages
- Check for PHP errors in debug log

---

## FILES TO MODIFY

1. `vivek-devops.php`
2. `includes/class-vsc-snippets.php`
3. `includes/class-vsc-auth.php`
4. `includes/class-vsc-backup.php`
5. `includes/class-vsc-database.php` (new file - create this)

---

## CONFIGURATION NEEDED

After implementing fixes, add to `wp-config.php`:

```php
// For master username (generated automatically on first run)
define('VSC_MASTER_USERNAME', 'your-secure-username-here');

// Only if behind CloudFlare or reverse proxy:
// define('VSC_TRUSTED_PROXY', 'your-proxy-ip-here');

// Enable logging during testing:
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

---

## DEPLOYMENT

1. Backup entire site before deploying
2. Test all fixes on staging environment
3. Activate plugin to create database tables
4. Monitor debug logs for errors
5. Test full user workflow
6. Deploy to production
7. Monitor for 24-48 hours

---

All fixes address critical security vulnerabilities. Implement all 10 fixes before deploying to production.
