<?php
/**
 * Copyright (C) 2014-2025 ServMask Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Attribution: This code is part of the All-in-One WP Migration plugin, developed by
 *
 * ███████╗███████╗██████╗ ██╗   ██╗███╗   ███╗ █████╗ ███████╗██╗  ██╗
 * ██╔════╝██╔════╝██╔══██╗██║   ██║████╗ ████║██╔══██╗██╔════╝██║ ██╔╝
 * ███████╗█████╗  ██████╔╝██║   ██║██╔████╔██║███████║███████╗█████╔╝
 * ╚════██║██╔══╝  ██╔══██╗╚██╗ ██╔╝██║╚██╔╝██║██╔══██║╚════██║██╔═██╗
 * ███████║███████╗██║  ██║ ╚████╔╝ ██║ ╚═╝ ██║██║  ██║███████║██║  ██╗
 * ╚══════╝╚══════╝╚═╝  ╚═╝  ╚═══╝  ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Kangaroos cannot jump here' );
}

class VSC_Migration_Main_Controller {

	/**
	 * Main Application Controller
	 *
	 * @return VSC_Migration_Main_Controller
	 */
	public function __construct() {
		register_activation_hook( VSC_MIGRATION_PLUGIN_BASENAME, array( $this, 'activation_hook' ) );

		// Activate hooks
		$this->activate_actions();
		$this->activate_filters();

		// If plugins_loaded has already fired, call vsc_migration_loaded directly
		if ( did_action( 'plugins_loaded' ) ) {
			$this->vsc_migration_loaded();
			$this->vsc_migration_commands();
			$this->vsc_migration_buttons();
			$this->wp_cli();
		}
	}

	/**
	 * Activation hook callback
	 *
	 * @return void
	 */
	public function activation_hook() {
		if ( extension_loaded( 'litespeed' ) ) {
			$this->create_litespeed_htaccess( VSC_MIGRATION_WORDPRESS_HTACCESS );
		}

		$this->setup_backups_folder();
		$this->setup_storage_folder();
		$this->setup_secret_key();
	}

	/**
	 * Initializes language domain for the plugin
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( VSC_MIGRATION_PLUGIN_NAME, false, false );
	}

	/**
	 * Register listeners for actions
	 *
	 * @return void
	 */
	private function activate_actions() {
		// Load core functionality
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'router' ) );
		add_action( 'admin_init', array( $this, 'wp_importing' ), 5 );
		add_action( 'admin_init', array( $this, 'setup_backups_folder' ) );
		add_action( 'admin_init', array( $this, 'setup_storage_folder' ) );
		add_action( 'admin_init', array( $this, 'setup_secret_key' ) );
		add_action( 'admin_init', array( $this, 'check_auto_increment' ) );
		add_action( 'admin_init', array( $this, 'check_user_role_capability' ) );
		add_action( 'admin_init', array( $this, 'schedule_crons' ) );
		add_action( 'admin_init', array( $this, 'load_textdomain' ) );

		// Load commands and buttons
		add_action( 'plugins_loaded', array( $this, 'vsc_migration_loaded' ), 10 );
		add_action( 'plugins_loaded', array( $this, 'vsc_migration_commands' ), 10 );
		add_action( 'plugins_loaded', array( $this, 'vsc_migration_buttons' ), 10 );
		add_action( 'plugins_loaded', array( $this, 'wp_cli' ), 10 );

		// Register scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'register_servmask_scripts_and_styles' ), 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_settings_scripts_and_styles' ), 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_export_scripts_and_styles' ), 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_import_scripts_and_styles' ), 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_backups_scripts_and_styles' ), 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_schedules_scripts_and_styles' ), 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_reset_scripts_and_styles' ), 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_updater_scripts_and_styles' ), 5 );

		// Enqueue scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_export_scripts_and_styles' ), 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_import_scripts_and_styles' ), 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backups_scripts_and_styles' ), 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_schedules_scripts_and_styles' ), 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_reset_scripts_and_styles' ), 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_updater_scripts_and_styles' ), 5 );

		// Handle export/import exceptions (errors)
		add_action( 'vsc_migration_status_export_error', array( $this, 'handle_error_cleanup' ), 5, 2 );
		add_action( 'vsc_migration_status_import_error', array( $this, 'handle_error_cleanup' ), 5, 2 );
	}

	/**
	 * Register listeners for filters
	 *
	 * @return void
	 */
	private function activate_filters() {
		// Add links to plugin list page
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

		// Add custom schedules
		add_filter( 'cron_schedules', array( $this, 'add_cron_schedules' ), 9999 );
	}

	/**
	 * Export and import commands
	 *
	 * @return void
	 */
	public function vsc_migration_commands() {
		// Add export commands
		add_filter( 'vsc_migration_export', 'VSC_Migration_Export_Init::execute', 5 );
		add_filter( 'vsc_migration_export', 'VSC_Migration_Export_Compatibility::execute', 10 );
		add_filter( 'vsc_migration_export', 'VSC_Migration_Export_Archive::execute', 30 );
		add_filter( 'vsc_migration_export', 'VSC_Migration_Export_Config::execute', 50 );
		add_filter( 'vsc_migration_export', 'VSC_Migration_Export_Config_File::execute', 60 );
		add_filter( 'vsc_migration_export', 'VSC_Migration_Export_Enumerate_Content::execute', 100 );
		add_filter( 'vsc_migration_export', 'VSC_Migration_Export_Enumerate_Media::execute', 110 );
		add_filter( 'vsc_migration_export', 'VSC_Migration_Export_Enumerate_Plugins::execute', 120 );
		add_filter( 'vsc_migration_export', 'VSC_Migration_Export_Enumerate_Themes::execute', 130 );
		add_filter( 'vsc_migration_export', 'VSC_Migration_Export_Enumerate_Tables::execute', 140 );
		add_filter( 'vsc_migration_export', 'VSC_Migration_Export_Content::execute', 150 );
		add_filter( 'vsc_migration_export', 'VSC_Migration_Export_Media::execute', 160 );
		add_filter( 'vsc_migration_export', 'VSC_Migration_Export_Plugins::execute', 170 );
		add_filter( 'vsc_migration_export', 'VSC_Migration_Export_Themes::execute', 180 );
		add_filter( 'vsc_migration_export', 'VSC_Migration_Export_Database::execute', 200 );
		add_filter( 'vsc_migration_export', 'VSC_Migration_Export_Database_File::execute', 220 );
		add_filter( 'vsc_migration_export', 'VSC_Migration_Export_Download::execute', 250 );
		add_filter( 'vsc_migration_export', 'VSC_Migration_Export_Clean::execute', 300 );

		// Add import commands
		add_filter( 'vsc_migration_import', 'VSC_Migration_Import_Upload::execute', 5 );
		add_filter( 'vsc_migration_import', 'VSC_Migration_Import_Compatibility::execute', 10 );
		add_filter( 'vsc_migration_import', 'VSC_Migration_Import_Validate::execute', 50 );
		add_filter( 'vsc_migration_import', 'VSC_Migration_Import_Check_Encryption::execute', 75 );
		add_filter( 'vsc_migration_import', 'VSC_Migration_Import_Check_Decryption_Password::execute', 90 );
		add_filter( 'vsc_migration_import', 'VSC_Migration_Import_Confirm::execute', 100 );
		add_filter( 'vsc_migration_import', 'VSC_Migration_Import_Blogs::execute', 150 );
		add_filter( 'vsc_migration_import', 'VSC_Migration_Import_Permalinks::execute', 170 );
		add_filter( 'vsc_migration_import', 'VSC_Migration_Import_Enumerate::execute', 200 );
		add_filter( 'vsc_migration_import', 'VSC_Migration_Import_Content::execute', 250 );
		add_filter( 'vsc_migration_import', 'VSC_Migration_Import_Mu_Plugins::execute', 270 );
		add_filter( 'vsc_migration_import', 'VSC_Migration_Import_Database::execute', 300 );
		add_filter( 'vsc_migration_import', 'VSC_Migration_Import_Users::execute', 310 );
		add_filter( 'vsc_migration_import', 'VSC_Migration_Import_Options::execute', 330 );
		add_filter( 'vsc_migration_import', 'VSC_Migration_Import_Done::execute', 350 );
		add_filter( 'vsc_migration_import', 'VSC_Migration_Import_Clean::execute', 400 );
	}

	/**
	 * Export and import buttons
	 *
	 * @return void
	 */
	public function vsc_migration_buttons() {
		add_filter( 'vsc_migration_export_buttons', 'VSC_Migration_Export_Controller::buttons' );
		add_filter( 'vsc_migration_import_buttons', 'VSC_Migration_Import_Controller::buttons' );
		add_filter( 'vsc_migration_pro', 'VSC_Migration_Import_Controller::pro', 10 );
	}

	/**
	 * All-in-One WP Migration loaded
	 *
	 * @return void
	 */
	public function vsc_migration_loaded() {
		if ( ! defined( 'AI1WMME_PLUGIN_NAME' ) ) {
			if ( is_multisite() ) {
				add_action( 'network_admin_notices', array( $this, 'multisite_notice' ) );
			} else {
				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			}
		} else {
			if ( is_multisite() ) {
				add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );
			} else {
				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			}
		}

		// Add in plugin update message
		foreach ( VSC_Migration_Extensions::get() as $slug => $extension ) {
			add_action( "in_plugin_update_message-{$extension['basename']}", 'VSC_Migration_Updater_Controller::in_plugin_update_message', 10, 2 );
		}

		// Add automatic plugins update
		add_action( 'wp_maybe_auto_update', 'VSC_Migration_Updater_Controller::check_for_updates' );

		// Add HTTP export headers
		add_filter( 'vsc_migration_http_export_headers', 'vsc_migration_auth_headers' );

		// Add HTTP import headers
		add_filter( 'vsc_migration_http_import_headers', 'vsc_migration_auth_headers' );

		// Add HTTP reset headers
		add_filter( 'vsc_migration_http_reset_headers', 'vsc_migration_auth_headers' );

		// Add chunk size limit
		add_filter( 'vsc_migration_max_chunk_size', 'VSC_Migration_Import_Controller::max_chunk_size' );

		// Add plugins API
		add_filter( 'plugins_api', 'VSC_Migration_Updater_Controller::plugins_api', 20, 3 );

		// Add plugins updates
		add_filter( 'pre_set_site_transient_update_plugins', 'VSC_Migration_Updater_Controller::pre_update_plugins' );

		// Add plugins metadata
		add_filter( 'site_transient_update_plugins', 'VSC_Migration_Updater_Controller::update_plugins' );

		// Add "Check for updates" link to plugin list page
		add_filter( 'plugin_row_meta', 'VSC_Migration_Updater_Controller::plugin_row_meta', 10, 2 );

		// Add storage folder daily cleanup cron
		add_action( 'vsc_migration_storage_cleanup', 'VSC_Migration_Export_Controller::cleanup' );
	}

	/**
	 * WP CLI commands
	 *
	 * @return void
	 */
	public function wp_cli() {
		if ( defined( 'WP_CLI' ) && count( VSC_Migration_Extensions::get() ) === 0 ) {
			WP_CLI::add_command( 'ai1wm', 'VSC_Migration_WP_CLI_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command', 'vivek-devops' ) ) );
		}
	}

	/**
	 * Create backups folder with index.php, index.html, .htaccess and web.config files
	 *
	 * @return void
	 */
	public function setup_backups_folder() {
		$this->create_backups_folder( VSC_MIGRATION_BACKUPS_PATH );
		$this->create_backups_htaccess( VSC_MIGRATION_BACKUPS_HTACCESS );
		$this->create_backups_webconfig( VSC_MIGRATION_BACKUPS_WEBCONFIG );
		$this->create_backups_index_php( VSC_MIGRATION_BACKUPS_INDEX_PHP );
		$this->create_backups_index_html( VSC_MIGRATION_BACKUPS_INDEX_HTML );
		$this->create_backups_robots_txt( VSC_MIGRATION_BACKUPS_ROBOTS_TXT );
	}

	/**
	 * Create storage folder with index.php and index.html files
	 *
	 * @return void
	 */
	public function setup_storage_folder() {
		$this->create_storage_folder( VSC_MIGRATION_STORAGE_PATH );
		$this->create_storage_htaccess( VSC_MIGRATION_STORAGE_HTACCESS );
		$this->create_storage_webconfig( VSC_MIGRATION_STORAGE_WEBCONFIG );
		$this->create_storage_index_php( VSC_MIGRATION_STORAGE_INDEX_PHP );
		$this->create_storage_index_html( VSC_MIGRATION_STORAGE_INDEX_HTML );
	}

	/**
	 * Create secret key if they don't exist yet
	 *
	 * @return void
	 */
	public function setup_secret_key() {
		if ( ! get_option( VSC_MIGRATION_SECRET_KEY ) ) {
			update_option( VSC_MIGRATION_SECRET_KEY, vsc_migration_generate_random_string( 12 ) );
		}
	}

	/**
	 * Check auto increment
	 *
	 * @return void
	 */
	public function check_auto_increment() {
		global $wpdb;

		$db_client = VSC_Migration_Database_Utility::create_client();
		if ( ! $db_client->has_auto_increment( $wpdb->options ) ) {
			if ( is_multisite() ) {
				return add_action( 'network_admin_notices', array( $this, 'missing_auto_increment' ) );
			} else {
				return add_action( 'admin_notices', array( $this, 'missing_auto_increment' ) );
			}
		}
	}

	/**
	 * Check user role capability
	 *
	 * @return void
	 */
	public function check_user_role_capability() {
		if ( ( $user = wp_get_current_user() ) && in_array( 'administrator', $user->roles ) ) {
			if ( ! $user->has_cap( 'export' ) || ! $user->has_cap( 'import' ) ) {
				if ( is_multisite() ) {
					return add_action( 'network_admin_notices', array( $this, 'missing_role_capability_notice' ) );
				} else {
					return add_action( 'admin_notices', array( $this, 'missing_role_capability_notice' ) );
				}
			}
		}
	}

	/**
	 * Schedule cron tasks for plugin operation, if not done yet
	 *
	 * @return void
	 */
	public function schedule_crons() {
		if ( ! VSC_Migration_Cron::exists( 'vsc_migration_storage_cleanup' ) ) {
			VSC_Migration_Cron::add( 'vsc_migration_storage_cleanup', 'daily', time() );
		}

		VSC_Migration_Cron::clear( 'vsc_migration_cleanup_cron' );
	}

	/**
	 * Create storage folder
	 *
	 * @param  string Path to folder
	 * @return void
	 */
	public function create_storage_folder( $path ) {
		if ( ! VSC_Migration_Directory::create( $path ) ) {
			if ( is_multisite() ) {
				return add_action( 'network_admin_notices', array( $this, 'storage_path_notice' ) );
			} else {
				return add_action( 'admin_notices', array( $this, 'storage_path_notice' ) );
			}
		}
	}

	/**
	 * Create backups folder
	 *
	 * @param  string Path to folder
	 * @return void
	 */
	public function create_backups_folder( $path ) {
		if ( ! VSC_Migration_Directory::create( $path ) ) {
			if ( is_multisite() ) {
				return add_action( 'network_admin_notices', array( $this, 'backups_path_notice' ) );
			} else {
				return add_action( 'admin_notices', array( $this, 'backups_path_notice' ) );
			}
		}
	}

	/**
	 * Create storage .htaccess file
	 *
	 * @param  string Path to file
	 * @return void
	 */
	public function create_storage_htaccess( $path ) {
		if ( ! VSC_Migration_File_Htaccess::storage( $path ) ) {
			if ( is_multisite() ) {
				return add_action( 'network_admin_notices', array( $this, 'storage_htaccess_notice' ) );
			} else {
				return add_action( 'admin_notices', array( $this, 'storage_htaccess_notice' ) );
			}
		}
	}

	/**
	 * Create storage web.config file
	 *
	 * @param  string Path to file
	 * @return void
	 */
	public function create_storage_webconfig( $path ) {
		if ( ! VSC_Migration_File_Webconfig::storage( $path ) ) {
			if ( is_multisite() ) {
				return add_action( 'network_admin_notices', array( $this, 'storage_webconfig_notice' ) );
			} else {
				return add_action( 'admin_notices', array( $this, 'storage_webconfig_notice' ) );
			}
		}
	}

	/**
	 * Create storage index.php file
	 *
	 * @param  string Path to file
	 * @return void
	 */
	public function create_storage_index_php( $path ) {
		if ( ! VSC_Migration_File_Index::create( $path ) ) {
			if ( is_multisite() ) {
				return add_action( 'network_admin_notices', array( $this, 'storage_index_php_notice' ) );
			} else {
				return add_action( 'admin_notices', array( $this, 'storage_index_php_notice' ) );
			}
		}
	}

	/**
	 * Create storage index.html file
	 *
	 * @param  string Path to file
	 * @return void
	 */
	public function create_storage_index_html( $path ) {
		if ( ! VSC_Migration_File_Index::create( $path ) ) {
			if ( is_multisite() ) {
				return add_action( 'network_admin_notices', array( $this, 'storage_index_html_notice' ) );
			} else {
				return add_action( 'admin_notices', array( $this, 'storage_index_html_notice' ) );
			}
		}
	}

	/**
	 * Create backups .htaccess file
	 *
	 * @param  string Path to file
	 * @return void
	 */
	public function create_backups_htaccess( $path ) {
		if ( ! VSC_Migration_File_Htaccess::backups( $path ) ) {
			if ( is_multisite() ) {
				return add_action( 'network_admin_notices', array( $this, 'backups_htaccess_notice' ) );
			} else {
				return add_action( 'admin_notices', array( $this, 'backups_htaccess_notice' ) );
			}
		}
	}

	/**
	 * Create backups web.config file
	 *
	 * @param  string Path to file
	 * @return void
	 */
	public function create_backups_webconfig( $path ) {
		if ( ! VSC_Migration_File_Webconfig::backups( $path ) ) {
			if ( is_multisite() ) {
				return add_action( 'network_admin_notices', array( $this, 'backups_webconfig_notice' ) );
			} else {
				return add_action( 'admin_notices', array( $this, 'backups_webconfig_notice' ) );
			}
		}
	}

	/**
	 * Create backups index.php file
	 *
	 * @param  string Path to file
	 * @return void
	 */
	public function create_backups_index_php( $path ) {
		if ( ! VSC_Migration_File_Index::create( $path ) ) {
			if ( is_multisite() ) {
				return add_action( 'network_admin_notices', array( $this, 'backups_index_php_notice' ) );
			} else {
				return add_action( 'admin_notices', array( $this, 'backups_index_php_notice' ) );
			}
		}
	}

	/**
	 * Create backups index.html file
	 *
	 * @param  string Path to file
	 * @return void
	 */
	public function create_backups_index_html( $path ) {
		if ( ! VSC_Migration_File_Index::create( $path ) ) {
			if ( is_multisite() ) {
				return add_action( 'network_admin_notices', array( $this, 'backups_index_html_notice' ) );
			} else {
				return add_action( 'admin_notices', array( $this, 'backups_index_html_notice' ) );
			}
		}
	}

	/**
	 * Create backups robots.txt file
	 *
	 * @param  string Path to file
	 * @return void
	 */
	public function create_backups_robots_txt( $path ) {
		if ( ! VSC_Migration_File_Robots::create( $path ) ) {
			if ( is_multisite() ) {
				return add_action( 'network_admin_notices', array( $this, 'backups_robots_txt_notice' ) );
			} else {
				return add_action( 'admin_notices', array( $this, 'backups_robots_txt_notice' ) );
			}
		}
	}

	/**
	 * If the "noabort" environment variable has been set,
	 * the script will continue to run even though the connection has been broken
	 *
	 * @return void
	 */
	public function create_litespeed_htaccess( $path ) {
		if ( ! VSC_Migration_File_Htaccess::litespeed( $path ) ) {
			if ( is_multisite() ) {
				return add_action( 'network_admin_notices', array( $this, 'wordpress_htaccess_notice' ) );
			} else {
				return add_action( 'admin_notices', array( $this, 'wordpress_htaccess_notice' ) );
			}
		}
	}

	/**
	 * Display multisite notice
	 *
	 * @return void
	 */
	public function multisite_notice() {
		VSC_Migration_Template::render( 'main/multisite-notice' );
	}

	/**
	 * Display notice for storage directory
	 *
	 * @return void
	 */
	public function storage_path_notice() {
		VSC_Migration_Template::render( 'main/storage-path-notice' );
	}

	/**
	 * Display notice for .htaccess file in storage directory
	 *
	 * @return void
	 */
	public function storage_htaccess_notice() {
		VSC_Migration_Template::render( 'main/storage-htaccess-notice' );
	}

	/**
	 * Display notice for web.config file in storage directory
	 *
	 * @return void
	 */
	public function storage_webconfig_notice() {
		VSC_Migration_Template::render( 'main/storage-webconfig-notice' );
	}

	/**
	 * Display notice for index.php file in storage directory
	 *
	 * @return void
	 */
	public function storage_index_php_notice() {
		VSC_Migration_Template::render( 'main/storage-index-php-notice' );
	}

	/**
	 * Display notice for index.html file in storage directory
	 *
	 * @return void
	 */
	public function storage_index_html_notice() {
		VSC_Migration_Template::render( 'main/storage-index-html-notice' );
	}

	/**
	 * Display notice for backups directory
	 *
	 * @return void
	 */
	public function backups_path_notice() {
		VSC_Migration_Template::render( 'main/backups-path-notice' );
	}

	/**
	 * Display notice for .htaccess file in backups directory
	 *
	 * @return void
	 */
	public function backups_htaccess_notice() {
		VSC_Migration_Template::render( 'main/backups-htaccess-notice' );
	}

	/**
	 * Display notice for web.config file in backups directory
	 *
	 * @return void
	 */
	public function backups_webconfig_notice() {
		VSC_Migration_Template::render( 'main/backups-webconfig-notice' );
	}

	/**
	 * Display notice for index.php file in backups directory
	 *
	 * @return void
	 */
	public function backups_index_php_notice() {
		VSC_Migration_Template::render( 'main/backups-index-php-notice' );
	}

	/**
	 * Display notice for index.html file in backups directory
	 *
	 * @return void
	 */
	public function backups_index_html_notice() {
		VSC_Migration_Template::render( 'main/backups-index-html-notice' );
	}

	/**
	 * Display notice for robots.txt file in backups directory
	 *
	 * @return void
	 */
	public function backups_robots_txt_notice() {
		VSC_Migration_Template::render( 'main/backups-robots-txt-notice' );
	}

	/**
	 * Display notice for .htaccess file in WordPress directory
	 *
	 * @return void
	 */
	public function wordpress_htaccess_notice() {
		VSC_Migration_Template::render( 'main/wordpress-htaccess-notice' );
	}

	/**
	 * Display notice for missing auto increment
	 *
	 * @return void
	 */
	public function missing_auto_increment() {
		VSC_Migration_Template::render( 'main/missing-auto-increment' );
	}

	/**
	 * Display notice for missing role capability
	 *
	 * @return void
	 */
	public function missing_role_capability_notice() {
		VSC_Migration_Template::render( 'main/missing-role-capability-notice' );
	}

	/**
	 * Add links to plugin list page
	 *
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( $file === VSC_MIGRATION_PLUGIN_BASENAME ) {
			$links[] = VSC_Migration_Template::get_content( 'main/contact-support' );
			$links[] = VSC_Migration_Template::get_content( 'main/translate' );
		}

		return $links;
	}

	/**
	 * Register plugin menus
	 *
	 * @return void
	 */
	public function admin_menu() {
		// Integrate as submenus under VSC Dashboard
		add_submenu_page(
			'vsc-dashboard',
			__( 'Migration - Export', 'vivek-devops' ),
			__( 'Migration', 'vivek-devops' ),
			'manage_options',
			'vsc-migration',
			'VSC_Migration_Export_Controller::index'
		);

		add_submenu_page(
			'vsc-dashboard',
			__( 'Migration - Import', 'vivek-devops' ),
			__( 'Import', 'vivek-devops' ),
			'manage_options',
			'vsc-migration-import',
			'VSC_Migration_Import_Controller::index'
		);

		add_submenu_page(
			'vsc-dashboard',
			__( 'Migration - Backups', 'vivek-devops' ),
			__( 'Backups', 'vivek-devops' ),
			'manage_options',
			'vsc-migration-backups',
			'VSC_Migration_Backups_Controller::index'
		);
	}

	/**
	 * Register ServMask scripts and styles
	 *
	 * @return void
	 */
	public function register_servmask_scripts_and_styles() {
		vsc_migration_register_style(
			'vsc_migration_servmask',
			VSC_Migration_Template::asset_link( 'css/servmask.min.css' )
		);

		vsc_migration_register_script(
			'vsc_migration_util',
			VSC_Migration_Template::asset_link( 'javascript/util.min.js' ),
			array( 'jquery' ),
			VSC_MIGRATION_VERSION,
			false
		);

		vsc_migration_register_script(
			'vsc_migration_servmask',
			VSC_Migration_Template::asset_link( 'javascript/servmask.min.js' ),
			array( 'vsc_migration_util' ),
			VSC_MIGRATION_VERSION,
			false
		);

		wp_localize_script(
			'vsc_migration_servmask',
			'vsc_migration_feedback',
			array(
				'ajax'       => array(
					'url' => wp_make_link_relative( add_query_arg( array( 'vsc_migration_import' => 1 ), admin_url( 'admin-ajax.php?action=vsc_migration_feedback' ) ) ),
				),
				'secret_key' => get_option( VSC_MIGRATION_SECRET_KEY ),
			)
		);

		$upload_limit_text = sprintf(
			/* translators: 1: Max upload file size */
			__(
				'Your file exceeds the <strong>%1$s</strong> upload limit set by your host.<br />%2$s<br />%3$s',
				'vivek-devops'
			),
			esc_html( vsc_migration_size_format( wp_max_upload_size() ) ),
			sprintf(
				/* translators: Link to Unlimited Extension */
				__( 'Our <a href="%s" target="_blank">Unlimited Extension</a> bypasses this!', 'vivek-devops' ),
				'https://servmask.com/products/unlimited-extension?utm_source=file-import&utm_medium=plugin&utm_campaign=ai1wm'
			),
			sprintf(
				/* translators: Link to how to article */
				__( 'If you prefer a manual fix, follow our step-by-step guide on <a href="%s" target="_blank">raising your upload limit</a>.', 'vivek-devops' ),
				'https://help.servmask.com/2018/10/27/how-to-increase-maximum-upload-file-size-in-wordpress/'
			)
		);

		wp_localize_script(
			'vsc_migration_servmask',
			'vsc_migration_locale',
			array(
				// Feedback
				'thanks_for_submitting_your_feedback' => __( 'Thank you! We have received your request and will be in touch soon.', 'vivek-devops' ),

				// Updater
				'check_for_updates'                   => __( 'Check for updates', 'vivek-devops' ),
				'invalid_purchase_id'                 => __( 'Your purchase ID is invalid. Please <a href="mailto:support@servmask.com">contact us</a>.', 'vivek-devops' ),

				// Export
				'stop_exporting_your_website'         => __( 'Are you sure you want to stop the export?', 'vivek-devops' ),
				'preparing_to_export'                 => __( 'Preparing to export...', 'vivek-devops' ),
				'unable_to_export'                    => __( 'Export failed', 'vivek-devops' ),
				'unable_to_start_the_export'          => __( 'Could not start the export. Please refresh and try again.', 'vivek-devops' ),
				'unable_to_run_the_export'            => __( 'Could not run the export. Please refresh and try again.', 'vivek-devops' ),
				'unable_to_stop_the_export'           => __( 'Could not stop the export. Please refresh and try again.', 'vivek-devops' ),
				'please_wait_stopping_the_export'     => __( 'Stopping the export, please wait...', 'vivek-devops' ),
				'close_export'                        => __( 'Close', 'vivek-devops' ),
				'stop_export'                         => __( 'Stop export', 'vivek-devops' ),
				/* translators: 1: Number of backups. */
				'backups_count_singular'              => __( 'You have %d backup', 'vivek-devops' ),
				/* translators: 1: Number of backups. */
				'backups_count_plural'                => __( 'You have %d backups', 'vivek-devops' ),
				'archive_browser_download_error'      => __( 'Could not download backup', 'vivek-devops' ),
				'view_error_log_button'               => __( 'View Error Log', 'vivek-devops' ),

				// Import
				'stop_importing_your_website'         => __( 'Are you sure you want to stop the import?', 'vivek-devops' ),
				'preparing_to_import'                 => __( 'Preparing to import...', 'vivek-devops' ),
				'unable_to_import'                    => __( 'Import failed', 'vivek-devops' ),
				'unable_to_start_the_import'          => __( 'Could not start the import. Please refresh and try again.', 'vivek-devops' ),
				'unable_to_confirm_the_import'        => __( 'Could not confirm the import. Please refresh and try again.', 'vivek-devops' ),
				'unable_to_check_decryption_password' => __( 'Could not check the decryption password. Please refresh and try again.', 'vivek-devops' ),
				'unable_to_prepare_blogs_on_import'   => __( 'Could not prepare blogs for import. Please refresh and try again.', 'vivek-devops' ),
				'unable_to_stop_the_import'           => __( 'Could not stop the import. Please refresh and try again.', 'vivek-devops' ),
				'please_wait_stopping_the_import'     => __( 'Stopping the import, please wait...', 'vivek-devops' ),
				'close_import'                        => __( 'Close', 'vivek-devops' ),
				'stop_import'                         => __( 'Stop import', 'vivek-devops' ),
				'finish_import'                       => __( 'Finish', 'vivek-devops' ),
				'confirm_import'                      => __( 'Proceed', 'vivek-devops' ),
				'confirm_disk_space'                  => __( 'I have enough disk space', 'vivek-devops' ),
				'continue_import'                     => __( 'Continue', 'vivek-devops' ),
				'please_do_not_close_this_browser'    => __( 'Please do not close this browser window or your import will fail', 'vivek-devops' ),
				'backup_encrypted'                    => __( 'The backup is encrypted', 'vivek-devops' ),
				'backup_encrypted_message'            => __( 'Please enter a password to restore the backup', 'vivek-devops' ),
				'submit'                              => __( 'Submit', 'vivek-devops' ),
				'enter_password'                      => __( 'Enter a password', 'vivek-devops' ),
				'repeat_password'                     => __( 'Repeat the password', 'vivek-devops' ),
				'passwords_do_not_match'              => __( 'The passwords do not match', 'vivek-devops' ),
				'upload_failed_connection_lost'       => __( 'Upload failed - connection lost or timeout. Try uploading the file again.', 'vivek-devops' ),
				'upload_failed'                       => __( 'Upload failed', 'vivek-devops' ),
				/* translators: Disk space to free up. */
				'out_of_disk_space'                   => __( 'Not enough disk space.<br /> Free up %s before restoring.', 'vivek-devops' ),
				'file_too_large'                      => sprintf(
					/* translators: 1: Link to Unlimited Extension, 2: Link to how to article */
					__(
						'Your file exceeds the upload limit set by your host web server.<br />%1$s<br />%2$s',
						'vivek-devops'
					),
					sprintf(
						/* translators: Link to Unlimited Extension */
						__( 'Our <a href="%s" target="_blank">Unlimited Extension</a> bypasses this!', 'vivek-devops' ),
						'https://servmask.com/products/unlimited-extension?utm_source=file-upload-webserver&utm_medium=plugin&utm_campaign=ai1wm'
					),
					sprintf(
						/* translators: Link to how to article */
						__( 'If you prefer a manual fix, follow our step-by-step guide on <a href="%s" target="_blank">raising your upload limit</a>.', 'vivek-devops' ),
						'https://help.servmask.com/2018/10/27/how-to-increase-maximum-upload-file-size-in-wordpress/'
					)
				),
				'import_from_file'                    => $upload_limit_text,
				'invalid_archive_extension'           => __(
					'Invalid file type. Please ensure that your file is a <strong>.wpress</strong> file created with All-in-One WP Migration.
					<a href="https://help.servmask.com/knowledgebase/invalid-backup-file/" target="_blank">Technical details</a>',
					'vivek-devops'
				),
				'upgrade'                             => $upload_limit_text,

				// Backups
				'want_to_delete_this_file'            => __( 'Are you sure you want to delete this backup?', 'vivek-devops' ),
				'unlimited'                           => __( 'Backup restore requires the Unlimited Extension. <a href="https://servmask.com/products/unlimited-extension" target="_blank">Get it here</a>', 'vivek-devops' ),
				'restore_from_file'                   => sprintf(
					/* translators: 1: Link to Unlimited Extension */
					__( '"Restore" functionality is available in our <a href="%s" target="_blank">Unlimited Extension</a>.<br /> If you would rather go the manual route, you can still restore by downloading your backup and using "Import from file".', 'vivek-devops' ),
					'https://servmask.com/products/unlimited-extension?utm_source=restore-from-file&utm_medium=plugin&utm_campaign=ai1wm'
				),
				'archive_browser_error'               => __( 'Error', 'vivek-devops' ),
				'archive_browser_list_error'          => __( 'Could not read backup content', 'vivek-devops' ),
				'archive_browser_title'               => __( 'List the content of the backup', 'vivek-devops' ),
				'progress_bar_title'                  => __( 'Reading...', 'vivek-devops' ),
			)
		);
	}

	/**
	 * Register settings scripts and styles
	 *
	 * @return void
	 */
	public function register_settings_scripts_and_styles() {
		vsc_migration_register_script(
			'vsc_migration_settings',
			VSC_Migration_Template::asset_link( 'javascript/settings.min.js' ),
			array( 'vsc_migration_servmask' ),
			VSC_MIGRATION_VERSION,
			false
		);
	}

	/**
	 * Register export scripts and styles
	 *
	 * @return void
	 */
	public function register_export_scripts_and_styles() {
		vsc_migration_register_style(
			'vsc_migration_export',
			VSC_Migration_Template::asset_link( 'css/export.min.css' )
		);

		vsc_migration_register_script(
			'vsc_migration_export',
			VSC_Migration_Template::asset_link( 'javascript/export.min.js' ),
			array( 'vsc_migration_servmask' ),
			VSC_MIGRATION_VERSION,
			false
		);

		wp_localize_script(
			'vsc_migration_export',
			'vsc_migration_export',
			array(
				'ajax'       => array(
					'url' => wp_make_link_relative( add_query_arg( array( 'vsc_migration_import' => 1 ), admin_url( 'admin-ajax.php?action=vsc_migration_export' ) ) ),
				),
				'download'   => array(
					'url' => wp_make_link_relative( add_query_arg( array( 'vsc_migration_import' => 1 ), admin_url( 'admin-ajax.php?action=vsc_migration_backup_download_file' ) ) ),
				),
				'status'     => array(
					'url' => wp_make_link_relative( add_query_arg( array( 'vsc_migration_import' => 1, 'secret_key' => get_option( VSC_MIGRATION_SECRET_KEY ) ), admin_url( 'admin-ajax.php?action=vsc_migration_status' ) ) ),
				),
				'storage'    => array(
					'url' => VSC_MIGRATION_STORAGE_URL,
				),
				'error_log'  => array(
					'pattern' => VSC_MIGRATION_ERROR_NAME,
				),
				'secret_key' => get_option( VSC_MIGRATION_SECRET_KEY ),
			)
		);
	}

	/**
	 * Register import scripts and styles
	 *
	 * @return void
	 */
	public function register_import_scripts_and_styles() {
		vsc_migration_register_style(
			'vsc_migration_import',
			VSC_Migration_Template::asset_link( 'css/import.min.css' )
		);

		vsc_migration_register_script(
			'vsc_migration_import',
			VSC_Migration_Template::asset_link( 'javascript/import.min.js' ),
			array( 'vsc_migration_servmask' ),
			VSC_MIGRATION_VERSION,
			false
		);

		wp_localize_script(
			'vsc_migration_import',
			'vsc_migration_uploader',
			array(
				'max_file_size' => wp_max_upload_size(),
				'url'           => wp_make_link_relative( add_query_arg( array( 'vsc_migration_import' => 1 ), admin_url( 'admin-ajax.php?action=vsc_migration_import' ) ) ),
				'params'        => array(
					'priority'   => 5,
					'secret_key' => get_option( VSC_MIGRATION_SECRET_KEY ),
				),
			)
		);

		wp_localize_script(
			'vsc_migration_import',
			'vsc_migration_import',
			array(
				'ajax'       => array(
					'url' => wp_make_link_relative( add_query_arg( array( 'vsc_migration_import' => 1 ), admin_url( 'admin-ajax.php?action=vsc_migration_import' ) ) ),
				),
				'status'     => array(
					'url' => wp_make_link_relative( add_query_arg( array( 'vsc_migration_import' => 1, 'secret_key' => get_option( VSC_MIGRATION_SECRET_KEY ) ), admin_url( 'admin-ajax.php?action=vsc_migration_status' ) ) ),
				),
				'storage'    => array(
					'url' => VSC_MIGRATION_STORAGE_URL,
				),
				'error_log'  => array(
					'pattern' => VSC_MIGRATION_ERROR_NAME,
				),
				'secret_key' => get_option( VSC_MIGRATION_SECRET_KEY ),
			)
		);

		wp_localize_script(
			'vsc_migration_import',
			'vsc_migration_compatibility',
			array(
				'messages' => VSC_Migration_Compatibility::get( array() ),
			)
		);

		wp_localize_script(
			'vsc_migration_import',
			'vsc_migration_disk_space',
			array(
				'free'   => vsc_migration_disk_free_space( VSC_MIGRATION_STORAGE_PATH ),
				'factor' => VSC_MIGRATION_DISK_SPACE_FACTOR,
				'extra'  => VSC_MIGRATION_DISK_SPACE_EXTRA,
			)
		);
	}

	/**
	 * Register backups scripts and styles
	 *
	 * @return void
	 */
	public function register_backups_scripts_and_styles() {
		vsc_migration_register_style(
			'vsc_migration_backups',
			VSC_Migration_Template::asset_link( 'css/backups.min.css' )
		);

		vsc_migration_register_script(
			'vsc_migration_backups',
			VSC_Migration_Template::asset_link( 'javascript/backups.min.js' ),
			array( 'vsc_migration_export', 'vsc_migration_import' ),
			VSC_MIGRATION_VERSION,
			false
		);

		wp_localize_script(
			'vsc_migration_backups',
			'vsc_migration_backups',
			array(
				'ajax'       => array(
					'url' => wp_make_link_relative( add_query_arg( array( 'vsc_migration_import' => 1 ), admin_url( 'admin-ajax.php?action=vsc_migration_backups' ) ) ),
				),
				'backups'    => array(
					'url' => wp_make_link_relative( add_query_arg( array( 'vsc_migration_import' => 1 ), admin_url( 'admin-ajax.php?action=vsc_migration_backup_list' ) ) ),
				),
				'labels'     => array(
					'url' => wp_make_link_relative( add_query_arg( array( 'vsc_migration_import' => 1 ), admin_url( 'admin-ajax.php?action=vsc_migration_add_backup_label' ) ) ),
				),
				'content'    => array(
					'url' => wp_make_link_relative( add_query_arg( array( 'vsc_migration_import' => 1 ), admin_url( 'admin-ajax.php?action=vsc_migration_backup_list_content' ) ) ),
				),
				'download'   => array(
					'url' => wp_make_link_relative( add_query_arg( array( 'vsc_migration_import' => 1 ), admin_url( 'admin-ajax.php?action=vsc_migration_backup_download_file' ) ) ),
				),
				'secret_key' => get_option( VSC_MIGRATION_SECRET_KEY ),
			)
		);
	}

	/**
	 * Register schedules scripts and styles
	 *
	 * @return void
	 */
	public function register_schedules_scripts_and_styles() {
		vsc_migration_register_style(
			'vsc_migration_schedules',
			VSC_Migration_Template::asset_link( 'css/schedules.min.css' )
		);

		vsc_migration_register_script(
			'vsc_migration_schedules',
			VSC_Migration_Template::asset_link( 'javascript/schedules.min.js' ),
			array( 'vsc_migration_servmask' ),
			VSC_MIGRATION_VERSION,
			false
		);
	}

	/**
	 * Register reset scripts and styles
	 *
	 * @return void
	 */
	public function register_reset_scripts_and_styles() {
		vsc_migration_register_style(
			'vsc_migration_reset',
			VSC_Migration_Template::asset_link( 'css/reset.min.css' )
		);

		vsc_migration_register_script(
			'vsc_migration_reset',
			VSC_Migration_Template::asset_link( 'javascript/reset.min.js' ),
			array( 'vsc_migration_servmask' ),
			VSC_MIGRATION_VERSION,
			false
		);
	}

	/**
	 * Register updater scripts and styles
	 *
	 * @return void
	 */
	public function register_updater_scripts_and_styles() {
		vsc_migration_register_style(
			'vsc_migration_updater',
			VSC_Migration_Template::asset_link( 'css/updater.min.css' )
		);
	}

	/**
	 * Enqueue scripts and styles for Export Controller
	 *
	 * @param  string $hook Hook suffix
	 * @return void
	 */
	public function enqueue_export_scripts_and_styles( $hook ) {
		if ( stripos( 'toplevel_page_vsc_migration_export', $hook ) === false ) {
			return;
		}

		// We don't want heartbeat to occur when exporting
		wp_deregister_script( 'heartbeat' );

		// We don't want auth check for monitoring whether the user is still logged in
		remove_action( 'admin_enqueue_scripts', 'wp_auth_check_load' );

		// Load scripts and styles
		vsc_migration_enqueue_style( 'vsc_migration_export' );
		vsc_migration_enqueue_script( 'vsc_migration_export' );
	}

	/**
	 * Enqueue scripts and styles for Import Controller
	 *
	 * @param  string $hook Hook suffix
	 * @return void
	 */
	public function enqueue_import_scripts_and_styles( $hook ) {
		if ( stripos( 'vivek-devops-migration_page_vsc_migration_import', $hook ) === false ) {
			return;
		}

		// We don't want heartbeat to occur when importing
		wp_deregister_script( 'heartbeat' );

		// We don't want auth check for monitoring whether the user is still logged in
		remove_action( 'admin_enqueue_scripts', 'wp_auth_check_load' );

		// Load scripts and styles
		vsc_migration_enqueue_style( 'vsc_migration_import' );
		vsc_migration_enqueue_script( 'vsc_migration_import' );
	}

	/**
	 * Enqueue scripts and styles for Backups Controller
	 *
	 * @param  string $hook Hook suffix
	 * @return void
	 */
	public function enqueue_backups_scripts_and_styles( $hook ) {
		if ( stripos( 'vivek-devops-migration_page_vsc_migration_backups', $hook ) === false ) {
			return;
		}

		// We don't want heartbeat to occur when restoring
		wp_deregister_script( 'heartbeat' );

		// We don't want auth check for monitoring whether the user is still logged in
		remove_action( 'admin_enqueue_scripts', 'wp_auth_check_load' );

		// Load scripts and styles
		vsc_migration_enqueue_style( 'vsc_migration_backups' );
		vsc_migration_enqueue_script( 'vsc_migration_backups' );
	}

	/**
	 * Enqueue scripts and styles for Schedules page
	 *
	 * @param  string $hook Hook suffix
	 * @return void
	 */
	public function enqueue_schedules_scripts_and_styles( $hook ) {
		if ( stripos( 'vivek-devops-migration_page_vsc_migration_schedules', $hook ) === false ) {
			return;
		}

		// We don't want heartbeat to occur when restoring
		wp_deregister_script( 'heartbeat' );

		// We don't want auth check for monitoring whether the user is still logged in
		remove_action( 'admin_enqueue_scripts', 'wp_auth_check_load' );

		// Load scripts and styles
		vsc_migration_enqueue_style( 'vsc_migration_schedules' );
		vsc_migration_enqueue_script( 'vsc_migration_schedules' );
	}

	/**
	 * Enqueue scripts and styles for Reset page
	 *
	 * @param  string $hook Hook suffix
	 * @return void
	 */
	public function enqueue_reset_scripts_and_styles( $hook ) {
		if ( stripos( 'vivek-devops-migration_page_vsc_migration_reset', $hook ) === false ) {
			return;
		}

		// We don't want heartbeat to occur when restoring
		wp_deregister_script( 'heartbeat' );

		// We don't want auth check for monitoring whether the user is still logged in
		remove_action( 'admin_enqueue_scripts', 'wp_auth_check_load' );

		// Load scripts and styles
		vsc_migration_enqueue_style( 'vsc_migration_reset' );
		vsc_migration_enqueue_script( 'vsc_migration_reset' );
	}

	/**
	 * Enqueue scripts and styles for Updater Controller
	 *
	 * @param  string $hook Hook suffix
	 * @return void
	 */
	public function enqueue_updater_scripts_and_styles( $hook ) {
		if ( 'plugins.php' !== strtolower( $hook ) ) {
			return;
		}

		// Load styles
		vsc_migration_enqueue_style( 'vsc_migration_updater' );
	}

	/**
	 * Outputs menu icon between head tags
	 *
	 * @return void
	 */
	public function admin_head() {
		global $wp_version;

		// Admin header
		VSC_Migration_Template::render( 'main/admin-head', array( 'version' => $wp_version ) );
	}

	/**
	 * Register initial parameters
	 *
	 * @return void
	 */
	public function init() {
		$user = $password = false;

		// Set username
		if ( isset( $_SERVER['PHP_AUTH_USER'] ) ) {
			$user = $_SERVER['PHP_AUTH_USER'];
		} elseif ( isset( $_SERVER['REMOTE_USER'] ) ) {
			$user = $_SERVER['REMOTE_USER'];
		}

		// Set password
		if ( isset( $_SERVER['PHP_AUTH_PW'] ) ) {
			$password = $_SERVER['PHP_AUTH_PW'];
		}

		if ( $user !== false && $password !== false ) {
			update_option( VSC_MIGRATION_AUTH_HEADER, base64_encode( sprintf( '%s:%s', $user, $password ) ) );
		}

		// Check for updates
		if ( isset( $_GET['vsc_migration_check_for_updates'] ) ) {
			if ( check_admin_referer( 'vsc_migration_check_for_updates', 'vsc_migration_nonce' ) ) {
				if ( current_user_can( 'update_plugins' ) ) {
					VSC_Migration_Updater::check_for_updates();
				}
			}
		}
	}

	/**
	 * Register initial router
	 *
	 * @return void
	 */
	public function router() {
		// Public actions
		add_action( 'wp_ajax_nopriv_vsc_migration_export', 'VSC_Migration_Export_Controller::export' );
		add_action( 'wp_ajax_nopriv_vsc_migration_import', 'VSC_Migration_Import_Controller::import' );
		add_action( 'wp_ajax_nopriv_vsc_migration_status', 'VSC_Migration_Status_Controller::status' );
		add_action( 'wp_ajax_nopriv_vsc_migration_backups', 'VSC_Migration_Backups_Controller::delete' );
		add_action( 'wp_ajax_nopriv_vsc_migration_feedback', 'VSC_Migration_Feedback_Controller::feedback' );
		add_action( 'wp_ajax_nopriv_vsc_migration_add_backup_label', 'VSC_Migration_Backups_Controller::add_label' );
		add_action( 'wp_ajax_nopriv_vsc_migration_backup_list', 'VSC_Migration_Backups_Controller::backup_list' );

		// Private actions
		add_action( 'wp_ajax_vsc_migration_export', 'VSC_Migration_Export_Controller::export' );
		add_action( 'wp_ajax_vsc_migration_import', 'VSC_Migration_Import_Controller::import' );
		add_action( 'wp_ajax_vsc_migration_status', 'VSC_Migration_Status_Controller::status' );
		add_action( 'wp_ajax_vsc_migration_backups', 'VSC_Migration_Backups_Controller::delete' );
		add_action( 'wp_ajax_vsc_migration_feedback', 'VSC_Migration_Feedback_Controller::feedback' );
		add_action( 'wp_ajax_vsc_migration_add_backup_label', 'VSC_Migration_Backups_Controller::add_label' );
		add_action( 'wp_ajax_vsc_migration_backup_list', 'VSC_Migration_Backups_Controller::backup_list' );
		add_action( 'wp_ajax_vsc_migration_backup_list_content', 'VSC_Migration_Backups_Controller::backup_list_content' );
		add_action( 'wp_ajax_vsc_migration_backup_download_file', 'VSC_Migration_Backups_Controller::download_file' );
	}

	/**
	 * Enable WP importing
	 *
	 * @return void
	 */
	public function wp_importing() {
		if ( isset( $_GET['vsc_migration_import'] ) ) {
			if ( ! defined( 'WP_IMPORTING' ) ) {
				define( 'WP_IMPORTING', true );
			}
		}
	}

	/**
	 * Add custom cron schedules
	 *
	 * @param  array $schedules List of schedules
	 * @return array
	 */
	public function add_cron_schedules( $schedules ) {
		$schedules['weekly']  = array(
			'display'  => __( 'Weekly', 'vivek-devops' ),
			'interval' => 60 * 60 * 24 * 7,
		);
		$schedules['monthly'] = array(
			'display'  => __( 'Monthly', 'vivek-devops' ),
			'interval' => ( strtotime( '+1 month' ) - time() ),
		);

		return $schedules;
	}

	/**
	 * Handles vsc_migration_status_export_error hook
	 *
	 * @param $params
	 * @param $exception
	 *
	 * @return void
	 */
	public function handle_error_cleanup( $params, $exception = null ) {
		VSC_Migration_Directory::delete( vsc_migration_storage_path( $params ) );
	}
}
