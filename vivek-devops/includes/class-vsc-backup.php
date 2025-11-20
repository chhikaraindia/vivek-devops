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
        $this->log('Constructor called');

        try {
            $this->log('Defining constants');
            $this->define_constants();
            $this->log('Constants defined');

            $this->log('Loading dependencies');
            $this->load_dependencies();
            $this->log('Dependencies loaded');

            $this->log('Initializing hooks');
            $this->init_hooks();
            $this->log('Hooks initialized');

            $this->log('Constructor completed successfully');
        } catch (Throwable $e) {
            $this->log('FATAL ERROR in constructor: ' . $e->getMessage(), 'error');
            $this->log('File: ' . $e->getFile() . ' Line: ' . $e->getLine(), 'error');

            throw $e; // Re-throw to be caught by main plugin
        }
    }

    /**
     * Log message if debugging is enabled
     */
    private function log($message, $level = 'info') {
        if (WP_DEBUG_LOG) {
            error_log(sprintf('[VSC Backup %s] %s', strtoupper($level), $message));
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
        if (!defined('VSC_BACKUP_PATH')) {
            define('VSC_BACKUP_PATH', VSC_PATH . 'includes/backup');
        }
        if (!defined('VSC_BACKUP_LIB_PATH')) {
            define('VSC_BACKUP_LIB_PATH', VSC_BACKUP_PATH . '/lib');
        }
        if (!defined('VSC_BACKUP_STORAGE_PATH')) {
            $upload_dir = wp_upload_dir();
            $backup_base = dirname($upload_dir['basedir']); // wp-content directory
            define('VSC_BACKUP_STORAGE_PATH', $backup_base . '/vsc-backups');
        }
        if (!defined('VSC_BACKUP_BACKUPS_PATH')) {
            define('VSC_BACKUP_BACKUPS_PATH', VSC_BACKUP_STORAGE_PATH . '/backups');
        }

        // URLs
        define('VSC_BACKUP_URL', VSC_URL . 'includes/backup');
        define('VSC_BACKUP_STORAGE_URL', VSC_URL . 'storage');

        // Backup file extension
        if (!defined('VSC_BACKUP_EXTENSION')) {
            define('VSC_BACKUP_EXTENSION', '.vscbackup');
        }

        // Archive structure names (may also be defined in constants.php)
        if (!defined('VSC_BACKUP_DATABASE_NAME')) {
            define('VSC_BACKUP_DATABASE_NAME', 'database.sql');
        }
        if (!defined('VSC_BACKUP_PACKAGE_NAME')) {
            define('VSC_BACKUP_PACKAGE_NAME', 'package.json');
        }
        if (!defined('VSC_BACKUP_SETTINGS_NAME')) {
            define('VSC_BACKUP_SETTINGS_NAME', 'settings.json');
        }

        // Security (may also be defined in constants.php)
        if (!defined('VSC_BACKUP_SECRET_KEY')) {
            define('VSC_BACKUP_SECRET_KEY', 'vsc_backup_secret_key');
        }

        // Unlimited size by default (merged from unlimited extension)
        if (!defined('VSC_BACKUP_MAX_FILE_SIZE')) {
            define('VSC_BACKUP_MAX_FILE_SIZE', PHP_INT_MAX);
        }
        if (!defined('VSC_BACKUP_MAX_CHUNK_SIZE')) {
            define('VSC_BACKUP_MAX_CHUNK_SIZE', 5 * 1024 * 1024); // 5MB chunks
        }

        // Plugin name for text domain
        if (!defined('VSC_BACKUP_PLUGIN_NAME')) {
            define('VSC_BACKUP_PLUGIN_NAME', 'vsc-backup');
        }

        // Table prefix marker (may also be defined in constants.php)
        if (!defined('VSC_BACKUP_TABLE_PREFIX')) {
            define('VSC_BACKUP_TABLE_PREFIX', 'VSC_PREFIX_');
        }

        // Debug mode (inherits from VSC)
        if (!defined('VSC_BACKUP_DEBUG')) {
            define('VSC_BACKUP_DEBUG', defined('WP_DEBUG') && WP_DEBUG);
        }
    }

    /**
     * Register autoloader for VSC_Backup classes
     */
    private function register_autoloader() {
        spl_autoload_register(function ($class) {
            // Only handle VSC_Backup classes
            if (strpos($class, 'VSC_Backup') !== 0) {
                return;
            }

            // Convert VSC_Backup_Main_Controller to class-vsc-backup-main-controller.php
            $class_name = 'class-' . strtolower(str_replace('_', '-', $class));
            $file_name = $class_name . '.php';

            // Define potential paths
            $paths = [
                VSC_BACKUP_LIB_PATH . '/model/',
                VSC_BACKUP_LIB_PATH . '/model/export/',
                VSC_BACKUP_LIB_PATH . '/model/import/',
                VSC_BACKUP_LIB_PATH . '/controller/',
                VSC_BACKUP_LIB_PATH . '/vendor/servmask/database/',
                VSC_BACKUP_LIB_PATH . '/vendor/servmask/filesystem/',
                VSC_BACKUP_LIB_PATH . '/vendor/servmask/archiver/',
                VSC_BACKUP_LIB_PATH . '/vendor/servmask/iterator/',
                VSC_BACKUP_LIB_PATH . '/vendor/servmask/filter/',
                VSC_BACKUP_LIB_PATH . '/vendor/servmask/cron/',
                VSC_BACKUP_LIB_PATH . '/vendor/servmask/command/',
            ];

            foreach ($paths as $path) {
                $full_path = $path . $file_name;
                if (file_exists($full_path)) {
                    require_once $full_path;
                    return;
                }
            }
        });
    }

    /**
     * Load all backup dependencies
     */
    private function load_dependencies() {
        // Wrap in try-catch for safe loading
        try {
            $this->log('Starting load_dependencies()');

            // Register autoloader for dynamic class loading
            $this->log('Registering autoloader');
            $this->register_autoloader();
            $this->log('Autoloader registered successfully');

            // Load constants
            $this->log('Loading constants.php');
            require_once VSC_BACKUP_PATH . '/constants.php';
            $this->log('Constants loaded successfully');

            // Load exceptions
            $this->log('Loading exceptions.php');
            require_once VSC_BACKUP_PATH . '/exceptions.php';
            $this->log('Exceptions loaded successfully');

            // Load helper functions
            $this->log('Loading functions.php');
            require_once VSC_BACKUP_PATH . '/functions.php';
            $this->log('Functions loaded successfully');

            // Load Bandar templating (non-VSC_Backup class)
            $vendor_path = VSC_BACKUP_LIB_PATH . '/vendor';
            if (file_exists($vendor_path . '/bandar/bandar/lib/Bandar.php')) {
                require_once $vendor_path . '/bandar/bandar/lib/Bandar.php';
                $this->log('Bandar templating loaded');
            }

            // All other classes will be autoloaded on demand
            $this->log('load_dependencies() completed successfully');
        } catch (Throwable $e) {
            // Log detailed error information
            $this->log('CRITICAL ERROR in load_dependencies(): ' . $e->getMessage(), 'error');
            $this->log('File: ' . $e->getFile() . ' Line: ' . $e->getLine(), 'error');
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

        // Initialize Main Controller (registers AJAX handlers + enqueues scripts)
        // This is critical - without it, AJAX handlers don't exist and export/import fail!
        VSC_Backup_Main_Controller::get_instance();
    }

    /**
     * Setup storage folders
     */
    public function setup_storage() {
        $this->log('setup_storage() called');

        try {
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

            $this->log('setup_storage() completed successfully');
        } catch (Throwable $e) {
            $this->log('setup_storage() ERROR: ' . $e->getMessage(), 'error');
            $this->log('File: ' . $e->getFile() . ' Line: ' . $e->getLine(), 'error');
        }
    }

    /**
     * Add backup menu to VSC Dashboard
     */
    public function add_menu() {
        $this->log('add_menu() called');

        try {
            add_submenu_page(
                'vsc-dashboard',
                'Backup and Restore',
                'Backup and Restore',
                'manage_options',
                'vsc-backup',
                array($this, 'render_backup_page')
            );
            $this->log('add_menu() completed successfully');
        } catch (Throwable $e) {
            $this->log('add_menu() ERROR: ' . $e->getMessage(), 'error');
            $this->log('File: ' . $e->getFile() . ' Line: ' . $e->getLine(), 'error');
        }
    }

    /**
     * Render backup page (tabs for export/import/backups)
     */
    public function render_backup_page() {
        $this->log('render_backup_page() called');

        try {
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
            $this->log('render_backup_page() completed successfully');
        } catch (Throwable $e) {
            $this->log('render_backup_page() ERROR: ' . $e->getMessage(), 'error');
            $this->log('File: ' . $e->getFile() . ' Line: ' . $e->getLine(), 'error');

            echo '<div class="wrap"><h1>VSC Backup Error</h1><p>An error occurred. Check debug.log for details.</p></div>';
        }
    }

    // Note: Asset enqueuing is now handled by VSC_Backup_Main_Controller
    // which is initialized in init_hooks() above
}

// Backup module will be initialized by main plugin file
