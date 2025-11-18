<?php
/**
 * Plugin Name: Vivek DevOps
 * Description: Vivek DevOps is a comprehensive DevSecOps suite for developers, crafted to transform backend management into a streamlined, secure, and developer-centric experience. It integrates security enforcement, custom branding, UI customization, user access policies, and critical infrastructure tooling into a single lightweight framework.
 * Version: 30.13
 * Author: Vivek Chhikara
 * Author URI: https://vivekchhikara.com
 */

if (!defined('ABSPATH')) exit;

// Plugin version
define('VSC_VERSION', '30.13');
define('VSC_NAME', 'Vivek DevOps');
define('VSC_PATH', plugin_dir_path(__FILE__));
define('VSC_URL', plugin_dir_url(__FILE__));
define('VSC_ASSETS_CSS', VSC_URL . 'assets/css/');
define('VSC_ASSETS_JS', VSC_URL . 'assets/js/');

// Master admin
define('VSC_MASTER_USERNAME', 'Mighty-Vivek');

// Load classes
require_once VSC_PATH . 'includes/class-vsc-core.php';
require_once VSC_PATH . 'includes/class-vsc-vertical-menu.php';
require_once VSC_PATH . 'includes/class-vsc-auth.php';
require_once VSC_PATH . 'includes/class-vsc-dashboard.php';
require_once VSC_PATH . 'includes/class-vsc-snippets.php';
require_once VSC_PATH . 'includes/class-vsc-color-scheme.php';

// Load backup module (optional - won't break plugin if it fails)
// Wrap in try-catch to prevent any fatal errors
try {
    if (file_exists(VSC_PATH . 'includes/class-vsc-backup.php')) {
        require_once VSC_PATH . 'includes/class-vsc-backup.php';
    }
} catch (Throwable $e) {
    // Write error directly to file (doesn't rely on WordPress)
    $error_log = VSC_PATH . 'backup-load-error.txt';
    $error_msg = date('Y-m-d H:i:s') . " - BACKUP MODULE LOAD ERROR\n";
    $error_msg .= "Message: " . $e->getMessage() . "\n";
    $error_msg .= "File: " . $e->getFile() . "\n";
    $error_msg .= "Line: " . $e->getLine() . "\n";
    $error_msg .= "Trace: " . $e->getTraceAsString() . "\n\n";
    @file_put_contents($error_log, $error_msg, FILE_APPEND);
}

// Initialize
function vsc_init() {
    VSC_Core::get_instance();
    VSC_Vertical_Menu::get_instance();
    VSC_Auth::get_instance();
    VSC_Dashboard::get_instance();
    VSC_Snippets::get_instance();
    VSC_Color_Scheme::get_instance();

    // Initialize backup module if available
    if (class_exists('VSC_Backup')) {
        error_log('VSC Backup: Class found, initializing...');

        // Also write to direct file log
        $init_log = VSC_PATH . 'backup-init.txt';
        @file_put_contents($init_log, date('Y-m-d H:i:s') . " - VSC_Backup class found, attempting initialization\n", FILE_APPEND);

        try {
            VSC_Backup::get_instance();
            error_log('VSC Backup: Initialized successfully');
            @file_put_contents($init_log, date('Y-m-d H:i:s') . " - SUCCESS: VSC_Backup initialized\n", FILE_APPEND);
        } catch (Throwable $e) {
            // Backup module failed - log but continue
            error_log('VSC Backup INITIALIZATION FAILED:');
            error_log('  Message: ' . $e->getMessage());
            error_log('  File: ' . $e->getFile());
            error_log('  Line: ' . $e->getLine());
            error_log('  Stack trace: ' . $e->getTraceAsString());

            // Also write to direct file log
            $error_msg = date('Y-m-d H:i:s') . " - INITIALIZATION ERROR\n";
            $error_msg .= "Message: " . $e->getMessage() . "\n";
            $error_msg .= "File: " . $e->getFile() . "\n";
            $error_msg .= "Line: " . $e->getLine() . "\n";
            $error_msg .= "Trace: " . $e->getTraceAsString() . "\n\n";
            @file_put_contents($init_log, $error_msg, FILE_APPEND);
        }
    } else {
        error_log('VSC Backup: Class NOT found - backup module not loaded');
        $init_log = VSC_PATH . 'backup-init.txt';
        @file_put_contents($init_log, date('Y-m-d H:i:s') . " - VSC_Backup class NOT found\n", FILE_APPEND);
    }

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
