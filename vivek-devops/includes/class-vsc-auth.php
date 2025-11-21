<?php
/**
 * VSC Authentication Class
 * Handles 2FA login system with Google Authenticator
 */

if (!defined('ABSPATH')) exit;

// Load helper libraries
require_once VSC_PATH . 'includes/lib/class-vsc-hotp.php';
require_once VSC_PATH . 'includes/lib/class-vsc-base32.php';

class VSC_Auth {
    private static $instance = null;
    private $otp_helper;
    private $time_window_size = 30; // 30 seconds
    private $check_back_time_windows = 2;
    private $check_forward_time_windows = 1;
    private $otp_length = 6;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->otp_helper = new VSC_HOTP();

        // Configure session to expire on browser close
        add_action('init', [$this, 'configure_session'], 1);

        // Intercept custom login URL
        add_action('init', [$this, 'intercept_login_url']);

        // Disable default WordPress login for non-whitelisted users
        add_action('login_init', [$this, 'disable_default_login']);

        // Intercept wp-login.php form submissions (honeypot)
        add_action('authenticate', [$this, 'honeypot_authenticate'], 1, 3);

        // Handle login form submissions
        add_action('init', [$this, 'handle_login_submission']);

        // Logout handling
        add_action('wp_logout', [$this, 'handle_logout']);

        // Customize WordPress login page
        add_action('login_enqueue_scripts', [$this, 'customize_wp_login_page']);
        add_filter('login_headerurl', [$this, 'login_header_url']);
        add_filter('login_headertext', [$this, 'login_header_text']);

        // Schedule cleanup of old honeypot logs
        add_action('vsc_cleanup_honeypot_logs', [$this, 'cleanup_honeypot_logs']);
        if (!wp_next_scheduled('vsc_cleanup_honeypot_logs')) {
            wp_schedule_event(time(), 'daily', 'vsc_cleanup_honeypot_logs');
        }
    }

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

    /**
     * Configure session settings for browser-close expiration
     */
    public function configure_session() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if (!headers_sent()) {
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
    }

    /**
     * Intercept /vsc login URL
     */
    public function intercept_login_url() {
        // Don't intercept if this is a form submission
        if (isset($_POST['vsc_login_action'])) {
            return;
        }

        $request_uri = esc_url_raw($_SERVER['REQUEST_URI'] ?? '');

        // Check if accessing /vsc or /vsc-login
        if (preg_match('#^/vsc(-login)?/?$#', $request_uri)) {
            $this->show_login_page();
            exit;
        }

        // QR code setup page
        if (preg_match('#^/vsc/setup-2fa/?$#', $request_uri)) {
            $this->show_qr_setup_page();
            exit;
        }

        // 2FA verification page
        if (preg_match('#^/vsc/verify-2fa/?$#', $request_uri)) {
            $this->show_2fa_verification_page();
            exit;
        }
    }

    /**
     * Disable default WordPress login - HONEYPOT TRAP
     * Only triggers for direct browser access, not internal WordPress operations
     */
    public function disable_default_login() {
        // Allow certain internal WordPress operations
        $action = $_REQUEST['action'] ?? '';

        // Whitelist specific actions that WordPress/plugins use internally
        $allowed_actions = [
            'logout',           // WordPress logout
            'lostpassword',     // Password reset
            'resetpass',        // Password reset confirmation
            'rp',              // Password reset link
            'postpass',        // Post password
            'confirmaction',   // Confirm action
        ];

        // Allow these internal WordPress actions
        if (in_array($action, $allowed_actions)) {
            return;
        }

        // Check if WooCommerce is redirecting here
        $referer = esc_url_raw($_SERVER['HTTP_REFERER'] ?? '');
        if (!empty($referer) && strpos($referer, site_url()) === 0) {
            // Internal redirect from our own site - might be WooCommerce
            if (strpos($referer, '/my-account') !== false ||
                strpos($referer, 'wc-ajax') !== false) {
                return; // Allow WooCommerce
            }
        }

        // Check if it's a REST API request
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return; // Allow REST API
        }

        // Check if it's an AJAX request
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return; // Allow AJAX
        }

        // Check if it's a CRON job
        if (defined('DOING_CRON') && DOING_CRON) {
            return; // Allow CRON
        }

        // If we get here, it's likely a direct browser access to wp-login.php
        // Trigger honeypot
        $this->trigger_honeypot('Direct wp-login.php access detected');
    }

    /**
     * Honeypot authentication filter - intercepts login attempts
     * ONLY triggers for direct wp-login.php page access
     * Allows internal WordPress authentication (WooCommerce, REST API, etc.)
     */
    public function honeypot_authenticate($user, $username, $password) {
        global $pagenow;

        // Only trigger honeypot if we're on the actual wp-login.php page
        // This allows WooCommerce, Elementor, and other plugins to authenticate normally
        if ($pagenow === 'wp-login.php' && (!empty($username) || !empty($password))) {
            // Check if this is a programmatic login (no HTTP_REFERER or from same site)
            $referer = esc_url_raw($_SERVER['HTTP_REFERER'] ?? '');
            $site_url = site_url();

            // If referer is from our own site, it might be WooCommerce login form
            // Check if it's specifically from WooCommerce my-account page
            if (!empty($referer) && strpos($referer, $site_url) === 0) {
                // Check for WooCommerce login
                if (strpos($referer, '/my-account') !== false ||
                    strpos($referer, 'wc-ajax') !== false ||
                    isset($_POST['woocommerce-login-nonce'])) {
                    // Allow WooCommerce login
                    return $user;
                }
            }

            // All other wp-login.php attempts are honeypot triggers
            $this->trigger_honeypot('Login attempt via wp-login.php');
        }

        return $user;
    }

    /**
     * Trigger honeypot - log incident and show BUSTED page
     */
    private function trigger_honeypot($reason = '') {
        // Log the incident
        $incident_id = $this->log_honeypot_incident($reason);

        // Get user IP
        $ip_address = $this->get_user_ip();

        // Show BUSTED page
        include VSC_PATH . 'templates/honeypot-busted.php';
        exit;
    }

    /**
     * Log honeypot incident to database
     */
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

    /**
     * Get user's IP address
     */
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

    /**
     * Show custom login page
     */
    private function show_login_page() {
        // Check if already logged in
        if (is_user_logged_in()) {
            wp_redirect(admin_url());
            exit;
        }

        include VSC_PATH . 'templates/login-page.php';
    }

    /**
     * Show QR code setup page
     */
    private function show_qr_setup_page() {
        session_start();

        // Verify user has passed email/password check
        if (!isset($_SESSION['vsc_pending_user_id'])) {
            wp_redirect(site_url('/vsc'));
            exit;
        }

        $user_id = $_SESSION['vsc_pending_user_id'];

        // Generate or get existing secret key
        $secret_key = $this->get_or_create_secret($user_id);

        // Generate QR code URL
        $qr_url = $this->generate_qr_code_url($user_id, $secret_key);

        // Show QR code page
        include VSC_PATH . 'templates/qr-setup-page.php';
    }

    /**
     * Show 2FA verification page
     */
    private function show_2fa_verification_page() {
        session_start();

        // Verify user has passed email/password check
        if (!isset($_SESSION['vsc_pending_user_id'])) {
            wp_redirect(site_url('/vsc'));
            exit;
        }

        include VSC_PATH . 'templates/2fa-verify-page.php';
    }

    /**
     * Handle login form submissions
     */
    public function handle_login_submission() {
        if (!isset($_POST['vsc_login_action'])) {
            return;
        }

        $action = $_POST['vsc_login_action'];

        switch ($action) {
            case 'login':
                $this->process_login();
                break;

            case 'verify_setup':
                $this->process_setup_verification();
                break;

            case 'verify_2fa':
                $this->process_2fa_verification();
                break;
        }
    }

    /**
     * Process email/password login
     */
    private function process_login() {
        if (!isset($_POST['vsc_login_nonce']) || !wp_verify_nonce($_POST['vsc_login_nonce'], 'vsc_login')) {
            wp_die('Security check failed');
        }

        // Check rate limit
        $ip = $this->get_user_ip();
        if (!$this->check_rate_limit($ip, 'login')) {
            $this->login_error('Too many login attempts. Please try again in 15 minutes.');
            return;
        }

        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];

        // Get user by email
        $user = get_user_by('email', $email);

        if (!$user) {
            $this->login_error('Invalid email or password');
            return;
        }

        // Check if user is administrator
        if (!in_array('administrator', $user->roles)) {
            $this->login_error('Access denied. Administrators only.');
            return;
        }

        // Verify password
        if (!wp_check_password($password, $user->user_pass, $user->ID)) {
            $this->login_error('Invalid email or password');
            return;
        }

        // Check if 2FA is set up
        $has_2fa = $this->user_has_2fa($user->ID);

        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['vsc_pending_user_id'] = $user->ID;

        if (!$has_2fa) {
            // Redirect to QR setup
            wp_redirect(site_url('/vsc/setup-2fa'));
            exit;
        } else {
            // Redirect to 2FA verification
            wp_redirect(site_url('/vsc/verify-2fa'));
            exit;
        }
    }

    /**
     * Process QR setup verification
     */
    private function process_setup_verification() {
        if (!isset($_POST['vsc_setup_nonce']) || !wp_verify_nonce($_POST['vsc_setup_nonce'], 'vsc_setup_2fa')) {
            wp_die('Security check failed');
        }

        session_start();

        if (!isset($_SESSION['vsc_pending_user_id'])) {
            wp_redirect(site_url('/vsc'));
            exit;
        }

        $user_id = $_SESSION['vsc_pending_user_id'];
        $code = sanitize_text_field($_POST['code']);

        // Verify code
        if ($this->verify_otp_code($user_id, $code)) {
            // Mark 2FA as set up
            update_user_meta($user_id, 'vsc_2fa_enabled', true);

            // Log the user in
            $this->complete_login($user_id);
        } else {
            // Show error and redirect back
            $_SESSION['vsc_error'] = 'Invalid code. Please try again.';
            wp_redirect(site_url('/vsc/setup-2fa'));
            exit;
        }
    }

    /**
     * Process 2FA verification
     */
    private function process_2fa_verification() {
        if (!isset($_POST['vsc_2fa_nonce']) || !wp_verify_nonce($_POST['vsc_2fa_nonce'], 'vsc_verify_2fa')) {
            wp_die('Security check failed');
        }

        // Check rate limit for 2FA attempts
        $ip = $this->get_user_ip();
        if (!$this->check_rate_limit($ip, '2fa', 10, 900)) {
            $_SESSION['vsc_error'] = 'Too many 2FA attempts. Please try again in 15 minutes.';
            wp_redirect(site_url('/vsc/verify-2fa'));
            exit;
        }

        session_start();

        if (!isset($_SESSION['vsc_pending_user_id'])) {
            wp_redirect(site_url('/vsc'));
            exit;
        }

        $user_id = $_SESSION['vsc_pending_user_id'];
        $code = sanitize_text_field($_POST['code']);

        // Verify code
        if ($this->verify_otp_code($user_id, $code)) {
            // Log the user in
            $this->complete_login($user_id);
        } else {
            // Show error and redirect back
            $_SESSION['vsc_error'] = 'Invalid code. Please try again.';
            wp_redirect(site_url('/vsc/verify-2fa'));
            exit;
        }
    }

    /**
     * Complete login and redirect to dashboard
     */
    private function complete_login($user_id) {
        // Clear pending session
        unset($_SESSION['vsc_pending_user_id']);

        // Clear rate limits on successful login
        $ip = $this->get_user_ip();
        $this->clear_rate_limit($ip, 'login');
        $this->clear_rate_limit($ip, '2fa');

        // Regenerate session ID to prevent session fixation
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        // Set WordPress auth cookies with session expiration
        wp_set_auth_cookie($user_id, false); // false = session-only cookie

        // Redirect to WordPress admin (which auto-redirects to VSC Dashboard)
        wp_redirect(admin_url());
        exit;
    }

    /**
     * Handle logout
     */
    public function handle_logout() {
        // Destroy session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Check if user has 2FA enabled
     */
    private function user_has_2fa($user_id) {
        $enabled = get_user_meta($user_id, 'vsc_2fa_enabled', true);
        $secret = get_user_meta($user_id, 'vsc_2fa_secret', true);
        return $enabled && !empty($secret);
    }

    /**
     * Get or create secret key for user
     */
    private function get_or_create_secret($user_id) {
        $secret = get_user_meta($user_id, 'vsc_2fa_secret', true);

        if (empty($secret)) {
            $secret = $this->generate_secret();
            $encrypted_secret = $this->encrypt_secret($secret, $user_id);
            update_user_meta($user_id, 'vsc_2fa_secret', $encrypted_secret);
        } else {
            $secret = $this->decrypt_secret($secret, $user_id);
        }

        return $secret;
    }

    /**
     * Generate random secret key
     */
    private function generate_secret() {
        // Generate 10 random bytes for Google Authenticator compatibility
        $chars = '23456789QWERTYUPASDFGHJKLZXCVBNM';
        $secret = '';
        for ($i = 0; $i < 10; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return strtoupper($secret);
    }

    /**
     * Encrypt secret (simple encryption using WordPress salts)
     */
    private function encrypt_secret($secret, $user_id) {
        $key = $this->get_encryption_key($user_id);
        $iv_size = openssl_cipher_iv_length('AES-128-CBC');
        $iv = openssl_random_pseudo_bytes($iv_size);
        $encrypted = openssl_encrypt($secret, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt secret
     */
    private function decrypt_secret($encrypted, $user_id) {
        $key = $this->get_encryption_key($user_id);
        $data = base64_decode($encrypted);
        $iv_size = openssl_cipher_iv_length('AES-128-CBC');
        $iv = substr($data, 0, $iv_size);
        $encrypted_data = substr($data, $iv_size);
        return openssl_decrypt($encrypted_data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * Get encryption key based on WordPress constants
     */
    private function get_encryption_key($user_id) {
        $salt = defined('AUTH_KEY') ? AUTH_KEY : 'vsc-default-key';
        return substr(hash('sha256', $salt . $user_id), 0, 16);
    }

    /**
     * Generate QR code URL for Google Authenticator
     */
    private function generate_qr_code_url($user_id, $secret) {
        $user = get_userdata($user_id);
        $site_name = get_bloginfo('name');

        // Create otpauth:// URL
        $url = 'otpauth://totp/' . rawurlencode($site_name) . ':' . rawurlencode($user->user_login);
        $url .= '?secret=' . VSC_Base32::encode($secret);
        $url .= '&issuer=' . rawurlencode($site_name);

        return $url;
    }

    /**
     * Generate OTP code for user
     */
    public function generate_otp($user_id) {
        $encrypted_secret = get_user_meta($user_id, 'vsc_2fa_secret', true);

        if (empty($encrypted_secret)) {
            return false;
        }

        $secret = $this->decrypt_secret($encrypted_secret, $user_id);
        $time = time();
        $otp_result = $this->otp_helper->generateByTime($secret, $this->time_window_size, $time);

        return $otp_result->toHOTP($this->otp_length);
    }

    /**
     * Verify OTP code for user
     */
    public function verify_otp_code($user_id, $user_code) {
        $encrypted_secret = get_user_meta($user_id, 'vsc_2fa_secret', true);

        if (empty($encrypted_secret)) {
            return false;
        }

        $secret = $this->decrypt_secret($encrypted_secret, $user_id);

        // Get recent codes to prevent replay
        $recent_codes = get_user_meta($user_id, 'vsc_2fa_recent_codes', true);
        if (!is_array($recent_codes)) {
            $recent_codes = [];
        }

        // Check if code was recently used
        $code_hash = hash('sha256', $user_code . $user_id);
        if (in_array($code_hash, $recent_codes)) {
            return false; // Code already used
        }

        // Generate OTPs for time window
        $codes = $this->otp_helper->generateByTimeWindow(
            $secret,
            $this->time_window_size,
            -1 * $this->check_back_time_windows,
            $this->check_forward_time_windows
        );

        // Check if code matches
        $match = false;
        foreach ($codes as $code_obj) {
            $generated_code = $code_obj->toHOTP($this->otp_length);
            if (hash_equals(trim($generated_code), trim($user_code))) {
                $match = true;
                break;
            }
        }

        if ($match) {
            // Add code to recent codes list
            $recent_codes[] = $code_hash;

            // Keep only last 3 codes
            if (count($recent_codes) > 3) {
                array_shift($recent_codes);
            }

            update_user_meta($user_id, 'vsc_2fa_recent_codes', $recent_codes);
            update_user_meta($user_id, 'vsc_2fa_last_login', time());
        }

        return $match;
    }

    /**
     * Show login error
     */
    private function login_error($message) {
        session_start();
        $_SESSION['vsc_error'] = $message;
        wp_redirect(site_url('/vsc'));
        exit;
    }

    /**
     * Reset 2FA for user (manual database intervention)
     */
    public function reset_2fa($user_id) {
        delete_user_meta($user_id, 'vsc_2fa_secret');
        delete_user_meta($user_id, 'vsc_2fa_enabled');
        delete_user_meta($user_id, 'vsc_2fa_recent_codes');
        delete_user_meta($user_id, 'vsc_2fa_last_login');
    }

    /**
     * Customize WordPress login page to match VSC styling
     */
    public function customize_wp_login_page() {
        ?>
        <style>
            body.login {
                background: #0a0a0a !important;
            }

            body.login div#login {
                padding-top: 5%;
            }

            body.login div#login h1 a {
                background-image: none !important;
                width: 60px;
                height: 60px;
                margin: 0 auto 20px;
                background: linear-gradient(135deg, #3b82f6, #1e40af) !important;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                <?php
                $site_icon_url = get_site_icon_url(60);
                if ($site_icon_url) {
                    echo 'background-image: url(' . esc_url($site_icon_url) . ') !important;';
                    echo 'background-size: cover !important;';
                    echo 'background-position: center !important;';
                }
                ?>
            }

            #login h1 a::before {
                <?php if (!get_site_icon_url(60)): ?>
                content: "🛡️";
                font-size: 32px;
                <?php endif; ?>
            }

            body.login div#login p.message {
                text-align: center;
                font-size: 16px;
                font-weight: 600;
                color: #ffffff;
                margin-bottom: 10px;
                background: transparent;
                border: none;
            }

            body.login div#login p.description {
                text-align: center;
                font-size: 14px;
                color: #888;
                margin-bottom: 30px;
            }

            .login form {
                background: #1a1a1a !important;
                border: 1px solid #2a2a2a !important;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5) !important;
            }

            .login label {
                color: #b0b0b0 !important;
                font-size: 14px !important;
            }

            .login input[type="text"],
            .login input[type="password"],
            .login input[type="email"] {
                background: #0f0f0f !important;
                border: 1px solid #2a2a2a !important;
                color: #e0e0e0 !important;
                font-size: 14px !important;
            }

            .login input[type="text"]:focus,
            .login input[type="password"]:focus,
            .login input[type="email"]:focus {
                border-color: #3b82f6 !important;
                box-shadow: none !important;
            }

            .wp-core-ui .button-primary {
                background: linear-gradient(135deg, #3b82f6, #1e40af) !important;
                border: none !important;
                box-shadow: none !important;
                text-shadow: none !important;
                font-weight: 600 !important;
                transition: all 0.3s ease !important;
            }

            .wp-core-ui .button-primary:hover {
                background: linear-gradient(135deg, #4a92ff, #2e50bf) !important;
                transform: translateY(-1px);
            }

            .wp-core-ui .button-primary:active {
                background: linear-gradient(135deg, #2a72df, #0e30af) !important;
                transform: translateY(0);
            }

            #login .message,
            #login .success {
                background: #1a2a1a !important;
                border-left: 4px solid #6ad66a !important;
                color: #6ad66a !important;
            }

            #login .error {
                background: #2a1a1a !important;
                border-left: 4px solid #ff4444 !important;
                color: #ff6666 !important;
            }

            .login #backtoblog a,
            .login #nav a {
                color: #888 !important;
                font-size: 12px !important;
            }

            .login #backtoblog a:hover,
            .login #nav a:hover {
                color: #3b82f6 !important;
            }

            body.login::after {
                content: "Powered by <?php echo esc_html(VSC_NAME); ?> <?php echo esc_html(VSC_VERSION); ?>";
                position: fixed;
                bottom: 20px;
                width: 100%;
                text-align: center;
                font-size: 12px;
                color: #666;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Add site name as message
                const form = document.getElementById('loginform');
                if (form) {
                    const siteTitle = document.querySelector('#login h1 a');
                    if (siteTitle) {
                        const message = document.createElement('p');
                        message.className = 'message';
                        message.textContent = '<?php echo esc_js(get_bloginfo('name')); ?>';
                        form.insertBefore(message, form.firstChild);

                        const desc = document.createElement('p');
                        desc.className = 'description';
                        desc.textContent = 'DevSecOps Secure Access';
                        form.insertBefore(desc, form.children[1]);
                    }
                }
            });
        </script>
        <?php
    }

    /**
     * Change login header URL to home page
     */
    public function login_header_url() {
        return home_url();
    }

    /**
     * Change login header text to site name
     */
    public function login_header_text() {
        return get_bloginfo('name');
    }
}
