<?php
/**
 * Plugin Name: Vivek DevOps
 * Description: Vivek DevOps is a comprehensive DevSecOps suite for developers, crafted to transform backend management into a streamlined, secure, and developer-centric experience. It integrates security enforcement, custom branding, UI customization, user access policies, and critical infrastructure tooling into a single lightweight framework.
 * Version: 30.4
 * Author: Vivek Chhikara
 * Author URI: https://vivekchhikara.com
 */

if (!defined('ABSPATH')) exit;

// Plugin version
define('VSC_VERSION', '30.4');
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

// Initialize
function vsc_init() {
    VSC_Core::get_instance();
    VSC_Vertical_Menu::get_instance();
    VSC_Auth::get_instance();
    VSC_Dashboard::get_instance();
    VSC_Snippets::get_instance();
    VSC_Color_Scheme::get_instance();

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
