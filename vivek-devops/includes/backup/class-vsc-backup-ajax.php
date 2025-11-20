<?php
/**
 * VSC Backup AJAX Handler
 *
 * @package Vivek_DevOps
 * @version 32.0
 */

if (!defined('ABSPATH')) exit;

class VSC_Backup_AJAX {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_ajax_vsc_create_backup', [$this, 'create_backup']);
        add_action('wp_ajax_vsc_restore_backup', [$this, 'restore_backup']);
        add_action('wp_ajax_vsc_delete_backup', [$this, 'delete_backup']);
        add_action('wp_ajax_vsc_download_backup', [$this, 'download_backup']);
        add_action('wp_ajax_vsc_get_backups', [$this, 'get_backups']);
        add_action('wp_ajax_vsc_upload_backup', [$this, 'upload_backup']);
    }

    public function create_backup() {
        check_ajax_referer('vsc_backup_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $engine = VSC_Backup_Engine::get_instance();
        $result = $engine->create_backup();

        if ($result['success']) {
            // Optionally upload to Google Drive
            if (get_option('vsc_backup_google_drive_enabled')) {
                $gdrive = VSC_Backup_Google_Drive::get_instance();
                $gdrive->upload_backup($result['file']);
            }

            wp_send_json_success([
                'message' => 'Backup created successfully',
                'backup' => $result
            ]);
        } else {
            wp_send_json_error(['message' => $result['error']]);
        }
    }

    public function restore_backup() {
        check_ajax_referer('vsc_backup_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $backup_id = sanitize_text_field($_POST['backup_id'] ?? '');

        if (empty($backup_id)) {
            wp_send_json_error(['message' => 'Invalid backup ID']);
        }

        $engine = VSC_Backup_Engine::get_instance();
        $result = $engine->restore_backup($backup_id);

        if ($result['success']) {
            wp_send_json_success(['message' => 'Backup restored successfully']);
        } else {
            wp_send_json_error(['message' => $result['error']]);
        }
    }

    public function delete_backup() {
        check_ajax_referer('vsc_backup_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $backup_id = sanitize_text_field($_POST['backup_id'] ?? '');

        if (empty($backup_id)) {
            wp_send_json_error(['message' => 'Invalid backup ID']);
        }

        $engine = VSC_Backup_Engine::get_instance();
        $result = $engine->delete_backup($backup_id);

        if ($result) {
            wp_send_json_success(['message' => 'Backup deleted']);
        } else {
            wp_send_json_error(['message' => 'Failed to delete backup']);
        }
    }

    public function get_backups() {
        check_ajax_referer('vsc_backup_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $engine = VSC_Backup_Engine::get_instance();
        $backups = $engine->get_backups();

        wp_send_json_success(['backups' => $backups]);
    }

    public function download_backup() {
        check_ajax_referer('vsc_backup_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $backup_id = sanitize_text_field($_GET['backup_id'] ?? '');

        if (empty($backup_id)) {
            wp_die('Invalid backup ID');
        }

        $engine = VSC_Backup_Engine::get_instance();
        $backups = $engine->get_backups();

        $backup = null;
        foreach ($backups as $b) {
            if ($b['id'] === $backup_id) {
                $backup = $b;
                break;
            }
        }

        if (!$backup) {
            wp_die('Backup not found');
        }

        $file = $engine->get_backup_dir() . '/' . $backup['file'];

        if (!file_exists($file)) {
            wp_die('Backup file not found');
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }

    public function upload_backup() {
        check_ajax_referer('vsc_backup_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        if (empty($_FILES['backup_file'])) {
            wp_send_json_error(['message' => 'No file uploaded']);
        }

        $file = $_FILES['backup_file'];

        // Validate file type
        $allowed_types = ['application/zip', 'application/x-zip-compressed'];
        $file_type = mime_content_type($file['tmp_name']);

        if (!in_array($file_type, $allowed_types) && pathinfo($file['name'], PATHINFO_EXTENSION) !== 'zip') {
            wp_send_json_error(['message' => 'Invalid file type. Only .zip files are allowed.']);
        }

        // Validate file size (max 500MB)
        $max_size = 500 * 1024 * 1024; // 500MB
        if ($file['size'] > $max_size) {
            wp_send_json_error(['message' => 'File too large. Maximum size is 500MB.']);
        }

        try {
            $engine = VSC_Backup_Engine::get_instance();
            $backup_dir = $engine->get_backup_dir();

            // Generate unique backup ID
            $backup_id = date('Y-m-d_H-i-s') . '_imported_' . substr(md5(uniqid()), 0, 6);
            $backup_file = $backup_dir . "/backup_{$backup_id}.zip";

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $backup_file)) {
                throw new Exception('Failed to save uploaded file');
            }

            // Save metadata
            $metadata = [
                'id' => $backup_id,
                'file' => basename($backup_file),
                'size' => filesize($backup_file),
                'date' => current_time('mysql'),
                'site_url' => 'Imported',
                'imported' => true
            ];

            $all_backups = get_option('vsc_backups_metadata', []);
            $all_backups[] = $metadata;
            update_option('vsc_backups_metadata', $all_backups);

            wp_send_json_success([
                'message' => 'Backup uploaded successfully',
                'backup' => $metadata
            ]);

        } catch (Exception $e) {
            error_log('VSC Upload Error: ' . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
}
