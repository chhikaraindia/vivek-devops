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
        add_action('admin_init', [$this, 'check_and_create_tables']);
    }

    /**
     * Check if tables exist and create them if missing
     */
    public function check_and_create_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vsc_honeypot_logs';

        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;

        if (!$table_exists) {
            $this->create_tables();
        }
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
