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
        // Direct file logging (doesn't rely on WordPress)
        $log_file = VSC_PATH . 'backup-constructor.txt';

        error_log('VSC Backup: Constructor called');
        @file_put_contents($log_file, date('Y-m-d H:i:s') . " - Constructor called\n", FILE_APPEND);

        try {
            error_log('VSC Backup: Defining constants');
            @file_put_contents($log_file, date('Y-m-d H:i:s') . " - Defining constants\n", FILE_APPEND);
            $this->define_constants();
            error_log('VSC Backup: Constants defined');
            @file_put_contents($log_file, date('Y-m-d H:i:s') . " - Constants defined\n", FILE_APPEND);

            error_log('VSC Backup: Loading dependencies');
            @file_put_contents($log_file, date('Y-m-d H:i:s') . " - Loading dependencies\n", FILE_APPEND);
            $this->load_dependencies();
            error_log('VSC Backup: Dependencies loaded');
            @file_put_contents($log_file, date('Y-m-d H:i:s') . " - Dependencies loaded\n", FILE_APPEND);

            error_log('VSC Backup: Initializing hooks');
            @file_put_contents($log_file, date('Y-m-d H:i:s') . " - Initializing hooks\n", FILE_APPEND);
            $this->init_hooks();
            error_log('VSC Backup: Hooks initialized');
            @file_put_contents($log_file, date('Y-m-d H:i:s') . " - Hooks initialized\n", FILE_APPEND);

            error_log('VSC Backup: Constructor completed successfully');
            @file_put_contents($log_file, date('Y-m-d H:i:s') . " - SUCCESS: Constructor completed\n", FILE_APPEND);
        } catch (Throwable $e) {
            error_log('VSC Backup FATAL ERROR in constructor:');
            error_log('  Message: ' . $e->getMessage());
            error_log('  File: ' . $e->getFile());
            error_log('  Line: ' . $e->getLine());
            error_log('  Trace: ' . $e->getTraceAsString());

            // Write to direct file log
            $error_msg = date('Y-m-d H:i:s') . " - FATAL ERROR in constructor\n";
            $error_msg .= "Message: " . $e->getMessage() . "\n";
            $error_msg .= "File: " . $e->getFile() . "\n";
            $error_msg .= "Line: " . $e->getLine() . "\n";
            $error_msg .= "Trace: " . $e->getTraceAsString() . "\n\n";
            @file_put_contents($log_file, $error_msg, FILE_APPEND);

            throw $e; // Re-throw to be caught by main plugin
        }
    }

    /**
     * Define VSC Backup constants
     */
    private function define_constants() {
        // Plugin version (matches VSC version)
        define('VSC_BACKUP_VERSION', VSC_VERSION);

        // Plugin basename (for WordPress hooks)
        // Computed manually to avoid calling plugin_basename() too early
        define('VSC_BACKUP_PLUGIN_BASENAME', basename(dirname(VSC_PATH)) . '/vivek-devops.php');

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
        // Wrap in try-catch for safe loading
        try {
            error_log('VSC Backup: Starting load_dependencies()');

            // Load constants
            error_log('VSC Backup: Loading constants.php');
            require_once VSC_BACKUP_PATH . '/constants.php';
            error_log('VSC Backup: Constants loaded successfully');

            // Load exceptions
            error_log('VSC Backup: Loading exceptions.php');
            require_once VSC_BACKUP_PATH . '/exceptions.php';
            error_log('VSC Backup: Exceptions loaded successfully');

            // Load helper functions
            error_log('VSC Backup: Loading functions.php');
            require_once VSC_BACKUP_PATH . '/functions.php';
            error_log('VSC Backup: Functions loaded successfully');

            // Load vendor libraries (filesystem, archiver, database)
            error_log('VSC Backup: Loading vendor files');
            $this->load_vendor_files();
            error_log('VSC Backup: Vendor files loaded successfully');

            // Load models
            error_log('VSC Backup: Loading model files');
            $this->load_model_files();
            error_log('VSC Backup: Model files loaded successfully');

            // Load controllers
            error_log('VSC Backup: Loading controller files');
            $this->load_controller_files();
            error_log('VSC Backup: Controller files loaded successfully');

            // Initialize main controller immediately
            // (Not delayed - it needs to register its hooks before admin_init fires)
            error_log('VSC Backup: Checking for VSC_Backup_Main_Controller class');
            if (class_exists('VSC_Backup_Main_Controller')) {
                error_log('VSC Backup: Instantiating VSC_Backup_Main_Controller');
                new VSC_Backup_Main_Controller();
                error_log('VSC Backup: Main controller instantiated successfully');
            } else {
                error_log('VSC Backup ERROR: VSC_Backup_Main_Controller class not found!');
            }

            error_log('VSC Backup: load_dependencies() completed successfully');
        } catch (Throwable $e) {
            // Log detailed error information
            error_log('VSC Backup CRITICAL ERROR in load_dependencies():');
            error_log('  Message: ' . $e->getMessage());
            error_log('  File: ' . $e->getFile());
            error_log('  Line: ' . $e->getLine());
            error_log('  Trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Load vendor library files
     */
    private function load_vendor_files() {
        $vendor_path = VSC_BACKUP_LIB_PATH . '/vendor';

        // Bandar templating
        if (file_exists($vendor_path . '/bandar/bandar/lib/Bandar.php')) {
            require_once $vendor_path . '/bandar/bandar/lib/Bandar.php';
        }

        // Filesystem
        $this->require_if_exists($vendor_path . '/servmask/filesystem/class-vsc-backup-file.php');
        $this->require_if_exists($vendor_path . '/servmask/filesystem/class-vsc-backup-directory.php');
        $this->require_if_exists($vendor_path . '/servmask/filesystem/class-vsc-backup-file-htaccess.php');
        $this->require_if_exists($vendor_path . '/servmask/filesystem/class-vsc-backup-file-index.php');
        $this->require_if_exists($vendor_path . '/servmask/filesystem/class-vsc-backup-file-robots.php');
        $this->require_if_exists($vendor_path . '/servmask/filesystem/class-vsc-backup-file-webconfig.php');

        // Database
        $this->require_if_exists($vendor_path . '/servmask/database/class-vsc-backup-database.php');
        $this->require_if_exists($vendor_path . '/servmask/database/class-vsc-backup-database-utility.php');
        $this->require_if_exists($vendor_path . '/servmask/database/class-vsc-backup-database-mysql.php');
        $this->require_if_exists($vendor_path . '/servmask/database/class-vsc-backup-database-mysqli.php');

        // Archiver (compression/extraction)
        $this->require_if_exists($vendor_path . '/servmask/archiver/class-vsc-backup-archiver.php');
        $this->require_if_exists($vendor_path . '/servmask/archiver/class-vsc-backup-compressor.php');
        $this->require_if_exists($vendor_path . '/servmask/archiver/class-vsc-backup-extractor.php');

        // Iterators and filters
        $this->require_if_exists($vendor_path . '/servmask/iterator/class-vsc-backup-recursive-directory-iterator.php');
        $this->require_if_exists($vendor_path . '/servmask/iterator/class-vsc-backup-recursive-iterator-iterator.php');
        $this->require_if_exists($vendor_path . '/servmask/filter/class-vsc-backup-recursive-exclude-filter.php');
        $this->require_if_exists($vendor_path . '/servmask/filter/class-vsc-backup-recursive-extension-filter.php');

        // Cron
        $this->require_if_exists($vendor_path . '/servmask/cron/class-vsc-backup-cron.php');
    }

    /**
     * Require file if it exists
     */
    private function require_if_exists($file) {
        if (file_exists($file)) {
            require_once $file;
        } else {
            error_log('VSC Backup: Missing file - ' . $file);
        }
    }

    /**
     * Load model files
     */
    private function load_model_files() {
        $model_path = VSC_BACKUP_LIB_PATH . '/model';

        // Core models
        $this->require_if_exists($model_path . '/class-vsc-backup-template.php');
        $this->require_if_exists($model_path . '/class-vsc-backup-extensions.php');
        $this->require_if_exists($model_path . '/class-vsc-backup-log.php');
        $this->require_if_exists($model_path . '/class-vsc-backup-status.php');
        $this->require_if_exists($model_path . '/class-vsc-backup-notification.php');
        $this->require_if_exists($model_path . '/class-vsc-backup-message.php');
        $this->require_if_exists($model_path . '/class-vsc-backup-backups.php');
        $this->require_if_exists($model_path . '/class-vsc-backup-compatibility.php');
        $this->require_if_exists($model_path . '/class-vsc-backup-handler.php');

        // Export models
        $export_files = glob($model_path . '/export/class-vsc-backup-export-*.php');
        if ($export_files) {
            foreach ($export_files as $file) {
                $this->require_if_exists($file);
            }
        }

        // Import models
        $import_files = glob($model_path . '/import/class-vsc-backup-import-*.php');
        if ($import_files) {
            foreach ($import_files as $file) {
                $this->require_if_exists($file);
            }
        }
    }

    /**
     * Load controller files
     */
    private function load_controller_files() {
        $controller_path = VSC_BACKUP_LIB_PATH . '/controller';

        $controller_files = glob($controller_path . '/class-vsc-backup-*-controller.php');
        if ($controller_files) {
            foreach ($controller_files as $file) {
                $this->require_if_exists($file);
            }
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
