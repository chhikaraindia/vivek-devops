<?php
/**
 * Plugin Name: Vivek DevOps
 * Description: Vivek DevOps is a comprehensive DevSecOps suite for developers, crafted to transform backend management into a streamlined, secure, and developer-centric experience. It integrates security enforcement, custom branding, UI customization, user access policies, and critical infrastructure tooling into a single lightweight framework.
 * Version: 32.0
 * Author: Vivek Chhikara
 * Author URI: https://vivekchhikara.com
 */

if (!defined('ABSPATH')) exit;

// Plugin version
define('VSC_VERSION', '32.0');
define('VSC_NAME', 'Vivek DevOps');
define('VSC_PATH', plugin_dir_path(__FILE__));
define('VSC_URL', plugin_dir_url(__FILE__));
define('VSC_ASSETS_CSS', VSC_URL . 'assets/css/');
define('VSC_ASSETS_JS', VSC_URL . 'assets/js/');

// Load classes
require_once VSC_PATH . 'includes/class-vsc-database.php';
require_once VSC_PATH . 'includes/class-vsc-core.php';
require_once VSC_PATH . 'includes/class-vsc-vertical-menu.php';
require_once VSC_PATH . 'includes/class-vsc-auth.php';
require_once VSC_PATH . 'includes/class-vsc-dashboard.php';
require_once VSC_PATH . 'includes/class-vsc-snippets.php';
require_once VSC_PATH . 'includes/class-vsc-color-scheme.php';

// Load backup module (clean, simple backup system)
require_once VSC_PATH . 'includes/backup/class-vsc-backup-engine.php';
require_once VSC_PATH . 'includes/backup/class-vsc-backup-ajax.php';
require_once VSC_PATH . 'includes/backup/class-vsc-backup-google-drive.php';
require_once VSC_PATH . 'includes/backup/class-vsc-backup-scheduler.php';
require_once VSC_PATH . 'includes/backup/class-vsc-backup-page.php';

// Initialize
function vsc_init() {
    VSC_Database::get_instance();
    VSC_Core::get_instance();
    VSC_Vertical_Menu::get_instance();
    VSC_Auth::get_instance();
    VSC_Dashboard::get_instance();
    VSC_Snippets::get_instance();
    VSC_Color_Scheme::get_instance();

    // Initialize backup modules
    VSC_Backup_AJAX::get_instance();
    VSC_Backup_Scheduler::get_instance();
    VSC_Backup_Page::get_instance();

    // Enqueue admin styles
    add_action('admin_enqueue_scripts', 'vsc_enqueue_styles');
}
add_action('plugins_loaded', 'vsc_init');

function vsc_enqueue_styles() {
    wp_enqueue_style('vsc-admin', VSC_ASSETS_CSS . 'vsc-admin.css', [], VSC_VERSION);
}

function vsc_enqueue_scripts() {
    // Enqueue Dark Reader library
    wp_enqueue_script('vsc-darkreader', VSC_ASSETS_JS . 'darkreader.min.js', [], VSC_VERSION, true);

    // Enqueue Dark Reader configuration
    wp_enqueue_script('vsc-dark-mode-config', VSC_ASSETS_JS . 'vsc-dark-mode.js', ['vsc-darkreader'], VSC_VERSION, true);
}
add_action('admin_enqueue_scripts', 'vsc_enqueue_scripts');

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
