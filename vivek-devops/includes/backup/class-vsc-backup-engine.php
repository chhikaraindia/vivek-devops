<?php
/**
 * VSC Backup Engine - Clean, simple backup and restore
 *
 * @package Vivek_DevOps
 * @version 32.0
 */

if (!defined('ABSPATH')) exit;

class VSC_Backup_Engine {

    private static $instance = null;
    private $backup_dir;
    private $temp_dir;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $upload_dir = wp_upload_dir();
        $this->backup_dir = WP_CONTENT_DIR . '/vsc-backups';
        $this->temp_dir = $this->backup_dir . '/temp';

        // Create directories
        $this->init_directories();
    }

    private function init_directories() {
        if (!file_exists($this->backup_dir)) {
            wp_mkdir_p($this->backup_dir);
            file_put_contents($this->backup_dir . '/.htaccess', 'deny from all');
            file_put_contents($this->backup_dir . '/index.php', '<?php // Silence is golden');
        }

        if (!file_exists($this->temp_dir)) {
            wp_mkdir_p($this->temp_dir);
        }
    }

    /**
     * Create a complete backup
     *
     * @return array Success status and backup file path
     */
    public function create_backup() {
        try {
            $backup_id = date('Y-m-d_H-i-s') . '_' . substr(md5(uniqid()), 0, 6);
            $backup_file = $this->backup_dir . "/backup_{$backup_id}.zip";

            // Step 1: Export database
            $db_file = $this->export_database($backup_id);
            if (!$db_file) {
                throw new Exception('Database export failed');
            }

            // Step 2: Create zip archive
            $zip = new ZipArchive();
            if ($zip->open($backup_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new Exception('Could not create backup archive');
            }

            // Add database file
            $zip->addFile($db_file, 'database.sql');

            // Add WordPress core directories
            $this->add_directory_to_zip($zip, ABSPATH . 'wp-admin', 'wp-admin');
            $this->add_directory_to_zip($zip, ABSPATH . 'wp-includes', 'wp-includes');

            // Add wp-content files (excluding our backups)
            $this->add_directory_to_zip($zip, WP_CONTENT_DIR, 'wp-content', [
                $this->backup_dir,
                WP_CONTENT_DIR . '/cache',
                WP_CONTENT_DIR . '/upgrade'
            ]);

            // Add root level files
            $root_files = [
                'index.php',
                'wp-activate.php',
                'wp-blog-header.php',
                'wp-comments-post.php',
                'wp-config.php',
                'wp-config-sample.php',
                'wp-cron.php',
                'wp-links-opml.php',
                'wp-load.php',
                'wp-login.php',
                'wp-mail.php',
                'wp-settings.php',
                'wp-signup.php',
                'wp-trackback.php',
                'xmlrpc.php',
                '.htaccess',
                'robots.txt',
                'readme.html',
                'license.txt'
            ];

            foreach ($root_files as $file) {
                $file_path = ABSPATH . $file;
                if (file_exists($file_path)) {
                    $zip->addFile($file_path, $file);
                }
            }

            $zip->close();

            // Cleanup temp files
            @unlink($db_file);

            // Get backup size
            $size = filesize($backup_file);

            // Save backup metadata
            $this->save_backup_metadata($backup_id, [
                'id' => $backup_id,
                'file' => basename($backup_file),
                'size' => $size,
                'date' => current_time('mysql'),
                'site_url' => get_site_url()
            ]);

            return [
                'success' => true,
                'file' => $backup_file,
                'id' => $backup_id,
                'size' => $size,
                'size_formatted' => size_format($size)
            ];

        } catch (Exception $e) {
            error_log('VSC Backup Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Export database to SQL file
     */
    private function export_database($backup_id) {
        global $wpdb;

        $sql_file = $this->temp_dir . "/db_{$backup_id}.sql";
        $handle = fopen($sql_file, 'w');

        if (!$handle) {
            return false;
        }

        // Write header
        fwrite($handle, "-- VSC Backup Database Export\n");
        fwrite($handle, "-- Date: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- Site: " . get_site_url() . "\n\n");
        fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
        fwrite($handle, "SET time_zone = \"+00:00\";\n\n");

        // Get all tables
        $tables = $wpdb->get_col("SHOW TABLES");

        foreach ($tables as $table) {
            // Table structure
            $create_table = $wpdb->get_row("SHOW CREATE TABLE `$table`", ARRAY_N);
            fwrite($handle, "\n\n-- Table: $table\n");
            fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
            fwrite($handle, $create_table[1] . ";\n\n");

            // Table data
            $rows = $wpdb->get_results("SELECT * FROM `$table`", ARRAY_A);

            if ($rows) {
                foreach ($rows as $row) {
                    $values = array_map(function($value) use ($wpdb) {
                        return is_null($value) ? 'NULL' : "'" . $wpdb->_real_escape($value) . "'";
                    }, array_values($row));

                    $columns = '`' . implode('`,`', array_keys($row)) . '`';
                    $values_str = implode(',', $values);

                    fwrite($handle, "INSERT INTO `$table` ($columns) VALUES ($values_str);\n");
                }
            }
        }

        fclose($handle);
        return $sql_file;
    }

    /**
     * Add directory to zip recursively
     */
    private function add_directory_to_zip($zip, $source_dir, $zip_path = '', $exclude = []) {
        if (!is_dir($source_dir)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $file_path = $file->getRealPath();

            // Check if excluded
            $excluded = false;
            foreach ($exclude as $exclude_path) {
                if (strpos($file_path, $exclude_path) === 0) {
                    $excluded = true;
                    break;
                }
            }

            if ($excluded) {
                continue;
            }

            $relative_path = substr($file_path, strlen($source_dir) + 1);
            $zip_file_path = $zip_path . '/' . $relative_path;

            if ($file->isDir()) {
                $zip->addEmptyDir($zip_file_path);
            } else {
                $zip->addFile($file_path, $zip_file_path);
            }
        }
    }

    /**
     * Restore from backup
     */
    public function restore_backup($backup_id) {
        try {
            $metadata = $this->get_backup_metadata($backup_id);
            if (!$metadata) {
                throw new Exception('Backup not found');
            }

            $backup_file = $this->backup_dir . '/' . $metadata['file'];

            if (!file_exists($backup_file)) {
                throw new Exception('Backup file not found');
            }

            // Extract backup
            $extract_dir = $this->temp_dir . '/restore_' . $backup_id;
            wp_mkdir_p($extract_dir);

            $zip = new ZipArchive();
            if ($zip->open($backup_file) !== true) {
                throw new Exception('Could not open backup file');
            }

            $zip->extractTo($extract_dir);
            $zip->close();

            // Restore database
            $db_file = $extract_dir . '/database.sql';
            if (file_exists($db_file)) {
                $this->import_database($db_file);
            }

            // Restore files
            $this->restore_files($extract_dir);

            // Cleanup
            $this->delete_directory($extract_dir);

            return ['success' => true];

        } catch (Exception $e) {
            error_log('VSC Restore Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Import database from SQL file
     */
    private function import_database($sql_file) {
        global $wpdb;

        $sql = file_get_contents($sql_file);

        // Split into individual queries
        $queries = array_filter(
            array_map('trim', explode(";\n", $sql)),
            function($q) { return !empty($q) && strpos($q, '--') !== 0; }
        );

        foreach ($queries as $query) {
            $wpdb->query($query);
        }
    }

    /**
     * Restore files from extracted backup
     */
    private function restore_files($extract_dir) {
        // Restore WordPress core directories
        $wp_admin_backup = $extract_dir . '/wp-admin';
        if (is_dir($wp_admin_backup)) {
            $this->copy_directory($wp_admin_backup, ABSPATH . 'wp-admin');
        }

        $wp_includes_backup = $extract_dir . '/wp-includes';
        if (is_dir($wp_includes_backup)) {
            $this->copy_directory($wp_includes_backup, ABSPATH . 'wp-includes');
        }

        // Restore wp-content
        $wp_content_backup = $extract_dir . '/wp-content';
        if (is_dir($wp_content_backup)) {
            $this->copy_directory($wp_content_backup, WP_CONTENT_DIR);
        }

        // Restore root level files
        $root_files = [
            'index.php',
            'wp-activate.php',
            'wp-blog-header.php',
            'wp-comments-post.php',
            'wp-config.php',
            'wp-config-sample.php',
            'wp-cron.php',
            'wp-links-opml.php',
            'wp-load.php',
            'wp-login.php',
            'wp-mail.php',
            'wp-settings.php',
            'wp-signup.php',
            'wp-trackback.php',
            'xmlrpc.php',
            '.htaccess',
            'robots.txt',
            'readme.html',
            'license.txt'
        ];

        foreach ($root_files as $file) {
            $backup_file = $extract_dir . '/' . $file;
            if (file_exists($backup_file)) {
                copy($backup_file, ABSPATH . $file);
            }
        }
    }

    /**
     * Copy directory recursively
     */
    private function copy_directory($source, $dest) {
        if (!is_dir($dest)) {
            wp_mkdir_p($dest);
        }

        $files = scandir($source);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $source_path = $source . '/' . $file;
            $dest_path = $dest . '/' . $file;

            if (is_dir($source_path)) {
                $this->copy_directory($source_path, $dest_path);
            } else {
                copy($source_path, $dest_path);
            }
        }
    }

    /**
     * Delete directory recursively
     */
    private function delete_directory($dir) {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->delete_directory($path) : unlink($path);
        }
        rmdir($dir);
    }

    /**
     * Get list of backups
     */
    public function get_backups() {
        $backups = get_option('vsc_backups_metadata', []);

        // Sort by date (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return $backups;
    }

    /**
     * Delete backup
     */
    public function delete_backup($backup_id) {
        $metadata = $this->get_backup_metadata($backup_id);
        if (!$metadata) {
            return false;
        }

        $backup_file = $this->backup_dir . '/' . $metadata['file'];
        if (file_exists($backup_file)) {
            unlink($backup_file);
        }

        // Remove from metadata
        $all_backups = get_option('vsc_backups_metadata', []);
        $all_backups = array_filter($all_backups, function($b) use ($backup_id) {
            return $b['id'] !== $backup_id;
        });
        update_option('vsc_backups_metadata', array_values($all_backups));

        return true;
    }

    /**
     * Save backup metadata
     */
    private function save_backup_metadata($backup_id, $data) {
        $all_backups = get_option('vsc_backups_metadata', []);
        $all_backups[] = $data;
        update_option('vsc_backups_metadata', $all_backups);
    }

    /**
     * Get backup metadata
     */
    private function get_backup_metadata($backup_id) {
        $all_backups = get_option('vsc_backups_metadata', []);
        foreach ($all_backups as $backup) {
            if ($backup['id'] === $backup_id) {
                return $backup;
            }
        }
        return null;
    }

    /**
     * Get backup directory path
     */
    public function get_backup_dir() {
        return $this->backup_dir;
    }
}
