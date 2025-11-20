<?php
/**
 * VSC Backup Admin Page
 *
 * @package Vivek_DevOps
 * @version 32.0
 */

if (!defined('ABSPATH')) exit;

class VSC_Backup_Page {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_init', [$this, 'handle_settings']);
    }

    public function add_menu() {
        add_submenu_page(
            'vsc-dashboard',
            'Backup & Restore',
            'Backup',
            'manage_options',
            'vsc-backup',
            [$this, 'render_page']
        );
    }

    public function enqueue_assets($hook) {
        if ($hook !== 'vivek-devops_page_vsc-backup') {
            return;
        }

        wp_enqueue_script(
            'vsc-backup-js',
            VSC_URL . 'includes/backup/assets/backup.js',
            ['jquery'],
            VSC_VERSION,
            true
        );

        wp_localize_script('vsc-backup-js', 'vscBackup', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('vsc_backup_nonce')
        ]);

        wp_enqueue_style(
            'vsc-backup-css',
            VSC_URL . 'includes/backup/assets/backup.css',
            [],
            VSC_VERSION
        );
    }

    public function handle_settings() {
        if (!isset($_POST['vsc_backup_settings_submit'])) {
            return;
        }

        check_admin_referer('vsc_backup_settings');

        // Auto backup settings
        update_option('vsc_backup_auto_enabled', isset($_POST['auto_enabled']));
        update_option('vsc_backup_site_type', sanitize_text_field($_POST['site_type'] ?? 'static'));
        update_option('vsc_backup_keep_count', intval($_POST['keep_count'] ?? 10));

        // Google Drive settings
        update_option('vsc_backup_google_drive_enabled', isset($_POST['gdrive_enabled']));
        update_option('vsc_gdrive_client_id', sanitize_text_field($_POST['gdrive_client_id'] ?? ''));
        update_option('vsc_gdrive_client_secret', sanitize_text_field($_POST['gdrive_client_secret'] ?? ''));

        // Reschedule backups
        $scheduler = VSC_Backup_Scheduler::get_instance();
        $scheduler->schedule_backup();

        add_settings_error('vsc_backup', 'settings_saved', 'Settings saved', 'success');
    }

    public function render_page() {
        $engine = VSC_Backup_Engine::get_instance();
        $backups = $engine->get_backups();
        $scheduler = VSC_Backup_Scheduler::get_instance();
        $gdrive = VSC_Backup_Google_Drive::get_instance();

        $auto_enabled = get_option('vsc_backup_auto_enabled', false);
        $site_type = get_option('vsc_backup_site_type', 'static');
        $keep_count = get_option('vsc_backup_keep_count', 10);
        $gdrive_enabled = get_option('vsc_backup_google_drive_enabled', false);
        $gdrive_client_id = get_option('vsc_gdrive_client_id', '');
        $gdrive_client_secret = get_option('vsc_gdrive_client_secret', '');

        ?>
        <div class="wrap vsc-backup-page">
            <h1>Backup & Restore</h1>

            <?php settings_errors('vsc_backup'); ?>

            <div class="vsc-backup-grid">
                <!-- Manual Backup Section -->
                <div class="vsc-backup-card">
                    <h2>Manual Backup</h2>
                    <p>Create a complete backup of your WordPress site (database + files).</p>
                    <button type="button" id="vsc-create-backup" class="button button-primary button-large">
                        Create Backup Now
                    </button>
                    <div id="vsc-backup-progress" style="display:none; margin-top:15px;">
                        <div class="vsc-spinner"></div>
                        <p>Creating backup...</p>
                    </div>
                </div>

                <!-- Import Backup Section -->
                <div class="vsc-backup-card">
                    <h2>Import Backup</h2>
                    <p>Upload a backup file from another site to restore it here.</p>
                    <form id="vsc-upload-form" enctype="multipart/form-data">
                        <input type="file" id="vsc-backup-file" accept=".zip" style="margin-bottom:10px;">
                        <br>
                        <button type="submit" class="button button-primary button-large">
                            Upload & Import
                        </button>
                    </form>
                    <div id="vsc-upload-progress" style="display:none; margin-top:15px;">
                        <div class="vsc-spinner"></div>
                        <p>Uploading backup...</p>
                    </div>
                </div>

                <!-- Automated Backups -->
                <div class="vsc-backup-card">
                    <h2>Automated Backups</h2>
                    <form method="post">
                        <?php wp_nonce_field('vsc_backup_settings'); ?>

                        <p>
                            <label>
                                <input type="checkbox" name="auto_enabled" <?php checked($auto_enabled); ?>>
                                Enable Automatic Backups
                            </label>
                        </p>

                        <p>
                            <label>Site Type:</label>
                            <select name="site_type">
                                <?php foreach (VSC_Backup_Scheduler::get_presets() as $key => $preset): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($site_type, $key); ?>>
                                        <?php echo esc_html($preset['label']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <br><small><?php
                                $current_preset = VSC_Backup_Scheduler::get_presets()[$site_type];
                                echo esc_html($current_preset['description']);
                            ?></small>
                        </p>

                        <?php if ($auto_enabled): ?>
                            <p><strong>Next Backup:</strong> <?php echo esc_html($scheduler->get_next_backup_time() ?: 'Not scheduled'); ?></p>
                        <?php endif; ?>

                        <p>
                            <label>Keep Last:</label>
                            <input type="number" name="keep_count" value="<?php echo esc_attr($keep_count); ?>" min="1" max="100" style="width:80px;">
                            backups
                        </p>

                        <button type="submit" name="vsc_backup_settings_submit" class="button button-primary">
                            Save Settings
                        </button>
                    </form>
                </div>

                <!-- Google Drive Integration -->
                <div class="vsc-backup-card">
                    <h2>Google Drive Backup</h2>
                    <form method="post">
                        <?php wp_nonce_field('vsc_backup_settings'); ?>

                        <p>
                            <label>
                                <input type="checkbox" name="gdrive_enabled" <?php checked($gdrive_enabled); ?>>
                                Upload Backups to Google Drive
                            </label>
                        </p>

                        <p>
                            <label>Client ID:</label><br>
                            <input type="text" name="gdrive_client_id" value="<?php echo esc_attr($gdrive_client_id); ?>" class="regular-text">
                        </p>

                        <p>
                            <label>Client Secret:</label><br>
                            <input type="text" name="gdrive_client_secret" value="<?php echo esc_attr($gdrive_client_secret); ?>" class="regular-text">
                        </p>

                        <?php if ($gdrive->is_configured()): ?>
                            <p class="vsc-success">✓ Google Drive Connected</p>
                        <?php else: ?>
                            <p><small>Get credentials from <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a></small></p>
                        <?php endif; ?>

                        <button type="submit" name="vsc_backup_settings_submit" class="button button-primary">
                            Save Settings
                        </button>
                    </form>
                </div>
            </div>

            <!-- Backups List -->
            <div class="vsc-backup-card" style="margin-top:30px;">
                <h2>Available Backups (<?php echo count($backups); ?>)</h2>

                <?php if (empty($backups)): ?>
                    <p>No backups available yet. Create your first backup above.</p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="vsc-backups-list">
                            <?php foreach ($backups as $backup): ?>
                                <tr data-backup-id="<?php echo esc_attr($backup['id']); ?>">
                                    <td><?php echo esc_html($backup['date']); ?></td>
                                    <td><?php echo esc_html(size_format($backup['size'])); ?></td>
                                    <td>
                                        <button class="button vsc-restore-backup" data-id="<?php echo esc_attr($backup['id']); ?>">
                                            Restore
                                        </button>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=vsc_download_backup&backup_id=' . $backup['id']), 'vsc_backup_nonce', 'nonce'); ?>" class="button">
                                            Download
                                        </a>
                                        <button class="button vsc-delete-backup" data-id="<?php echo esc_attr($backup['id']); ?>">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
