<?php
/**
 * Core Class - Minimal
 */

if (!defined('ABSPATH')) exit;

class VSC_Core {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Customize admin footer
        add_filter('admin_footer_text', [$this, 'custom_admin_footer_text'], 999);
        add_filter('update_footer', [$this, 'custom_update_footer'], 999);
    }

    /**
     * Customize admin footer left text
     */
    public function custom_admin_footer_text($text) {
        return 'Powered by Vivek Chhikara';
    }

    /**
     * Customize admin footer right text (version area)
     */
    public function custom_update_footer($text) {
        $ip_address = $this->get_user_ip();

        if (!empty($ip_address)) {
            return 'Your IP is ' . esc_html($ip_address);
        }

        // If no IP detected, return empty to hide this section
        return '';
    }

    /**
     * Get user's IP address
     */
    private function get_user_ip() {
        $ip_address = '';

        // Check various headers for IP address
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Can contain multiple IPs, get the first one
            $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip_address = trim($ip_list[0]);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            $ip_address = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED'])) {
            $ip_address = $_SERVER['HTTP_FORWARDED'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }

        // Validate IP address
        $ip_address = filter_var($ip_address, FILTER_VALIDATE_IP);

        return $ip_address ? $ip_address : '';
    }
}
