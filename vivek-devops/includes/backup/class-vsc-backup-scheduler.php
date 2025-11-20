<?php
/**
 * VSC Backup Scheduler - Automated backups based on site type
 *
 * @package Vivek_DevOps
 * @version 32.0
 */

if (!defined('ABSPATH')) exit;

class VSC_Backup_Scheduler {

    private static $instance = null;

    // Site type presets
    const PRESETS = [
        'static' => [
            'label' => 'Static/Business Website',
            'interval' => 'monthly',
            'description' => 'Once per month - suitable for simple websites with minimal changes'
        ],
        'ecommerce' => [
            'label' => 'E-commerce Store',
            'interval' => 'daily',
            'description' => 'Once every 24 hours - for stores with daily orders and inventory changes'
        ],
        'social' => [
            'label' => 'Social Media / Community',
            'interval' => 'fourhourly',
            'description' => 'Every 4 hours - for active social platforms with constant content updates'
        ],
        'custom' => [
            'label' => 'Custom Schedule',
            'interval' => 'custom',
            'description' => 'Define your own backup frequency'
        ]
    ];

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Add custom cron intervals
        add_filter('cron_schedules', [$this, 'add_custom_intervals']);

        // Register cron hook
        add_action('vsc_automated_backup', [$this, 'run_automated_backup']);

        // Schedule backup if enabled
        if (!wp_next_scheduled('vsc_automated_backup')) {
            $this->schedule_backup();
        }
    }

    /**
     * Add custom cron intervals
     */
    public function add_custom_intervals($schedules) {
        $schedules['fourhourly'] = [
            'interval' => 4 * HOUR_IN_SECONDS,
            'display' => __('Every 4 Hours')
        ];

        $schedules['twicedaily'] = [
            'interval' => 12 * HOUR_IN_SECONDS,
            'display' => __('Twice Daily')
        ];

        return $schedules;
    }

    /**
     * Schedule automated backup
     */
    public function schedule_backup() {
        $enabled = get_option('vsc_backup_auto_enabled', false);

        if (!$enabled) {
            $this->unschedule_backup();
            return;
        }

        $site_type = get_option('vsc_backup_site_type', 'static');
        $preset = self::PRESETS[$site_type] ?? self::PRESETS['static'];
        $interval = $preset['interval'];

        // Use custom interval if set
        if ($site_type === 'custom') {
            $interval = get_option('vsc_backup_custom_interval', 'daily');
        }

        // Clear existing schedule
        $this->unschedule_backup();

        // Schedule new backup
        wp_schedule_event(time(), $interval, 'vsc_automated_backup');
    }

    /**
     * Unschedule automated backup
     */
    public function unschedule_backup() {
        $timestamp = wp_next_scheduled('vsc_automated_backup');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'vsc_automated_backup');
        }
    }

    /**
     * Run automated backup
     */
    public function run_automated_backup() {
        error_log('VSC: Running automated backup');

        $engine = VSC_Backup_Engine::get_instance();
        $result = $engine->create_backup();

        if ($result['success']) {
            error_log('VSC: Automated backup created successfully');

            // Upload to Google Drive if enabled
            if (get_option('vsc_backup_google_drive_enabled')) {
                $gdrive = VSC_Backup_Google_Drive::get_instance();
                $upload_result = $gdrive->upload_backup($result['file']);

                if ($upload_result['success']) {
                    error_log('VSC: Backup uploaded to Google Drive');
                } else {
                    error_log('VSC: Failed to upload backup to Google Drive: ' . $upload_result['error']);
                }
            }

            // Cleanup old backups
            $this->cleanup_old_backups();

        } else {
            error_log('VSC: Automated backup failed: ' . $result['error']);
        }
    }

    /**
     * Cleanup old backups (keep last 10)
     */
    private function cleanup_old_backups() {
        $engine = VSC_Backup_Engine::get_instance();
        $backups = $engine->get_backups();

        $keep_count = get_option('vsc_backup_keep_count', 10);

        if (count($backups) > $keep_count) {
            $to_delete = array_slice($backups, $keep_count);

            foreach ($to_delete as $backup) {
                $engine->delete_backup($backup['id']);
            }

            error_log('VSC: Cleaned up ' . count($to_delete) . ' old backups');
        }
    }

    /**
     * Get site type presets
     */
    public static function get_presets() {
        return self::PRESETS;
    }

    /**
     * Get next scheduled backup time
     */
    public function get_next_backup_time() {
        $timestamp = wp_next_scheduled('vsc_automated_backup');
        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
    }
}
