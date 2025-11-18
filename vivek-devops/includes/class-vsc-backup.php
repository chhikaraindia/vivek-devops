<?php
/**
 * VSC Backup & Restore System
 * Full site backup, migration, and restore functionality
 * Based on All-in-One WP Migration (GPL Licensed)
 * Rebranded and customized for Vivek DevOps
 */

if (!defined('ABSPATH')) exit;

class VSC_Backup {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->define_constants();
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Define VSC Backup constants
     */
    private function define_constants() {
        // Plugin version (matches VSC version)
        define('VSC_BACKUP_VERSION', VSC_VERSION);

        // Backup module paths
        define('VSC_BACKUP_PATH', VSC_PATH . 'includes/backup');
        define('VSC_BACKUP_LIB_PATH', VSC_BACKUP_PATH . '/lib');
        define('VSC_BACKUP_STORAGE_PATH', VSC_PATH . 'storage');
        define('VSC_BACKUP_BACKUPS_PATH', VSC_BACKUP_STORAGE_PATH . '/backups');

        // URLs
        define('VSC_BACKUP_URL', VSC_URL . 'includes/backup');
        define('VSC_BACKUP_STORAGE_URL', VSC_URL . 'storage');

        // Backup file extension
        define('VSC_BACKUP_EXTENSION', '.vscbackup');

        // Archive structure names
        define('VSC_BACKUP_DATABASE_NAME', 'database.sql');
        define('VSC_BACKUP_PACKAGE_NAME', 'package.json');
        define('VSC_BACKUP_SETTINGS_NAME', 'settings.json');

        // Security
        define('VSC_BACKUP_SECRET_KEY', 'vsc_backup_secret_key');

        // Unlimited size by default (merged from unlimited extension)
        define('VSC_BACKUP_MAX_FILE_SIZE', PHP_INT_MAX);
        define('VSC_BACKUP_MAX_CHUNK_SIZE', 5 * 1024 * 1024); // 5MB chunks

        // Plugin name for text domain
        define('VSC_BACKUP_PLUGIN_NAME', 'vsc-backup');

        // Table prefix marker
        define('VSC_BACKUP_TABLE_PREFIX', 'VSC_PREFIX_');

        // Debug mode (inherits from VSC)
        define('VSC_BACKUP_DEBUG', defined('WP_DEBUG') && WP_DEBUG);
    }

    /**
     * Load all backup dependencies
     */
    private function load_dependencies() {
        // Load constants
        require_once VSC_BACKUP_PATH . '/constants.php';

        // Load exceptions
        require_once VSC_BACKUP_PATH . '/exceptions.php';

        // Load helper functions
        require_once VSC_BACKUP_PATH . '/functions.php';

        // Load vendor libraries (filesystem, archiver, database)
        $this->load_vendor_files();

        // Load models
        $this->load_model_files();

        // Load controllers
        $this->load_controller_files();

        // Initialize main controller
        new VSC_Backup_Main_Controller();
    }

    /**
     * Load vendor library files
     */
    private function load_vendor_files() {
        $vendor_path = VSC_BACKUP_LIB_PATH . '/vendor';

        // Bandar templating
        require_once $vendor_path . '/bandar/bandar/lib/Bandar.php';

        // Filesystem
        require_once $vendor_path . '/servmask/filesystem/class-vsc-backup-file.php';
        require_once $vendor_path . '/servmask/filesystem/class-vsc-backup-directory.php';
        require_once $vendor_path . '/servmask/filesystem/class-vsc-backup-file-htaccess.php';
        require_once $vendor_path . '/servmask/filesystem/class-vsc-backup-file-index.php';
        require_once $vendor_path . '/servmask/filesystem/class-vsc-backup-file-robots.php';
        require_once $vendor_path . '/servmask/filesystem/class-vsc-backup-file-webconfig.php';

        // Database
        require_once $vendor_path . '/servmask/database/class-vsc-backup-database.php';
        require_once $vendor_path . '/servmask/database/class-vsc-backup-database-utility.php';
        require_once $vendor_path . '/servmask/database/class-vsc-backup-database-mysql.php';
        require_once $vendor_path . '/servmask/database/class-vsc-backup-database-mysqli.php';

        // Archiver (compression/extraction)
        require_once $vendor_path . '/servmask/archiver/class-vsc-backup-archiver.php';
        require_once $vendor_path . '/servmask/archiver/class-vsc-backup-compressor.php';
        require_once $vendor_path . '/servmask/archiver/class-vsc-backup-extractor.php';

        // Iterators and filters
        require_once $vendor_path . '/servmask/iterator/class-vsc-backup-recursive-directory-iterator.php';
        require_once $vendor_path . '/servmask/iterator/class-vsc-backup-recursive-iterator-iterator.php';
        require_once $vendor_path . '/servmask/filter/class-vsc-backup-recursive-exclude-filter.php';
        require_once $vendor_path . '/servmask/filter/class-vsc-backup-recursive-extension-filter.php';

        // Cron
        require_once $vendor_path . '/servmask/cron/class-vsc-backup-cron.php';
    }

    /**
     * Load model files
     */
    private function load_model_files() {
        $model_path = VSC_BACKUP_LIB_PATH . '/model';

        // Core models
        require_once $model_path . '/class-vsc-backup-template.php';
        require_once $model_path . '/class-vsc-backup-extensions.php';
        require_once $model_path . '/class-vsc-backup-log.php';
        require_once $model_path . '/class-vsc-backup-status.php';
        require_once $model_path . '/class-vsc-backup-notification.php';
        require_once $model_path . '/class-vsc-backup-message.php';
        require_once $model_path . '/class-vsc-backup-backups.php';
        require_once $model_path . '/class-vsc-backup-compatibility.php';
        require_once $model_path . '/class-vsc-backup-handler.php';

        // Export models
        foreach (glob($model_path . '/export/class-vsc-backup-export-*.php') as $file) {
            require_once $file;
        }

        // Import models
        foreach (glob($model_path . '/import/class-vsc-backup-import-*.php') as $file) {
            require_once $file;
        }
    }

    /**
     * Load controller files
     */
    private function load_controller_files() {
        $controller_path = VSC_BACKUP_LIB_PATH . '/controller';

        foreach (glob($controller_path . '/class-vsc-backup-*-controller.php') as $file) {
            require_once $file;
        }
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Setup storage folders on activation
        add_action('admin_init', array($this, 'setup_storage'));

        // Add backup menu to VSC Dashboard
        add_action('admin_menu', array($this, 'add_menu'), 20);

        // Enqueue assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Setup storage folders
     */
    public function setup_storage() {
        // Create backups directory
        if (!file_exists(VSC_BACKUP_BACKUPS_PATH)) {
            wp_mkdir_p(VSC_BACKUP_BACKUPS_PATH);
        }

        // Create storage directory
        if (!file_exists(VSC_BACKUP_STORAGE_PATH)) {
            wp_mkdir_p(VSC_BACKUP_STORAGE_PATH);
        }

        // Protect storage with .htaccess
        $htaccess_file = VSC_BACKUP_BACKUPS_PATH . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            file_put_contents($htaccess_file, 'deny from all');
        }

        // Protect storage with index.php
        $index_file = VSC_BACKUP_BACKUPS_PATH . '/index.php';
        if (!file_exists($index_file)) {
            file_put_contents($index_file, '<?php // Silence is golden');
        }

        // Generate secret key if not exists
        if (!get_option(VSC_BACKUP_SECRET_KEY)) {
            update_option(VSC_BACKUP_SECRET_KEY, wp_generate_password(32, true, true));
        }
    }

    /**
     * Add backup menu to VSC Dashboard
     */
    public function add_menu() {
        add_submenu_page(
            'vsc-dashboard',
            'Backup and Restore',
            'Backup and Restore',
            'manage_options',
            'vsc-backup',
            array($this, 'render_backup_page')
        );
    }

    /**
     * Render backup page (tabs for export/import/backups)
     */
    public function render_backup_page() {
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'export';

        ?>
        <div class="wrap vsc-backup-wrap">
            <h1>🔄 VSC Backup & Restore</h1>

            <nav class="nav-tab-wrapper">
                <a href="?page=vsc-backup&tab=export" class="nav-tab <?php echo $tab === 'export' ? 'nav-tab-active' : ''; ?>">
                    Export Site
                </a>
                <a href="?page=vsc-backup&tab=import" class="nav-tab <?php echo $tab === 'import' ? 'nav-tab-active' : ''; ?>">
                    Import Site
                </a>
                <a href="?page=vsc-backup&tab=backups" class="nav-tab <?php echo $tab === 'backups' ? 'nav-tab-active' : ''; ?>">
                    Manage Backups
                </a>
            </nav>

            <div class="vsc-backup-tab-content">
                <?php
                switch ($tab) {
                    case 'import':
                        VSC_Backup_Import_Controller::index();
                        break;
                    case 'backups':
                        VSC_Backup_Backups_Controller::index();
                        break;
                    case 'export':
                    default:
                        VSC_Backup_Export_Controller::index();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue backup assets
     */
    public function enqueue_assets($hook) {
        if ($hook !== 'vivek-devops_page_vsc-backup') {
            return;
        }

        // Enqueue WordPress media library
        wp_enqueue_media();

        // Will add CSS/JS here later
    }
}

// Backup module will be initialized by main plugin file
