<?php
/**
 * Copyright (C) 2014-2020 Vivek Chhikara
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
 * ███████╗███████╗██████╗ ██╗   ██╗███╗   ███╗ █████╗ ███████╗██╗  ██╗
 * ██╔════╝██╔════╝██╔══██╗██║   ██║████╗ ████║██╔══██╗██╔════╝██║ ██╔╝
 * ███████╗█████╗  ██████╔╝██║   ██║██╔████╔██║███████║███████╗█████╔╝
 * ╚════██║██╔══╝  ██╔══██╗╚██╗ ██╔╝██║╚██╔╝██║██╔══██║╚════██║██╔═██╗
 * ███████║███████╗██║  ██║ ╚████╔╝ ██║ ╚═╝ ██║██║  ██║███████║██║  ██╗
 * ╚══════╝╚══════╝╚═╝  ╚═╝  ╚═══╝  ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Unauthorized access' );
}

class VSC_Backup_Export_Controller {

	public static function index() {
		VSC_Backup_Template::render( 'export/index' );
	}

	public static function export( $params = array() ) {
		global $vsc_backup_params;

		// Enhanced error logging
		error_log("VSC EXPORT: Export started");
		error_log("VSC EXPORT: Params received: " . print_r($params, true));

		vsc_backup_setup_environment();
		error_log("VSC EXPORT: Environment setup complete");

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( array_merge( $_GET, $_POST ) );
			error_log("VSC EXPORT: Using GET/POST params: " . print_r($params, true));
		}

		// Set priority
		if ( ! isset( $params['priority'] ) ) {
			$params['priority'] = 5;
		}
		error_log("VSC EXPORT: Priority set to: " . $params['priority']);

		// Set secret key
		$secret_key = null;
		if ( isset( $params['secret_key'] ) ) {
			$secret_key = trim( $params['secret_key'] );
		}
		error_log("VSC EXPORT: Secret key present: " . (empty($secret_key) ? 'NO' : 'YES'));

		try {
			// Ensure that unauthorized people cannot access export action
			vsc_backup_verify_secret_key( $secret_key );
			error_log("VSC EXPORT: Secret key verified");
		} catch ( VSC_Backup_Not_Valid_Secret_Key_Exception $e ) {
			error_log("VSC EXPORT ERROR: Invalid secret key");
			exit;
		}

		$vsc_backup_params = $params;

		// Loop over filters
		if ( ( $filters = vsc_backup_get_filters( 'vsc_backup_export' ) ) ) {
			while ( $hooks = current( $filters ) ) {
				if ( intval( $params['priority'] ) === key( $filters ) ) {
					foreach ( $hooks as $hook ) {
						try {
							error_log("VSC EXPORT: Executing hook: " . print_r($hook['function'], true));

							// Run function hook
							$params = call_user_func_array( $hook['function'], array( $params ) );

							error_log("VSC EXPORT: Hook completed successfully");

						} catch ( VSC_Backup_Database_Exception $e ) {
							error_log("VSC EXPORT ERROR: Database exception - Code: " . $e->getCode() . ", Message: " . $e->getMessage());
							error_log("VSC EXPORT ERROR: Stack trace: " . $e->getTraceAsString());

							if ( defined( 'WP_CLI' ) ) {
								WP_CLI::error( sprintf( __( 'Unable to export. Error code: %s. %s', VSC_BACKUP_PLUGIN_NAME ), $e->getCode(), $e->getMessage() ) );
							} else {
								status_header( $e->getCode() );
								vsc_backup_json_response( array( 'errors' => array( array( 'code' => $e->getCode(), 'message' => $e->getMessage() ) ) ) );
							}
							VSC_Backup_Directory::delete( vsc_backup_storage_path( $params ) );

							// Check if export is performed from scheduled event
							if ( isset( $params['event_id'] ) ) {
								$params['error_message'] = $e->getMessage();
								do_action( 'vsc_backup_status_export_fail', $params );
							}
							exit;
						} catch ( Exception $e ) {
							error_log("VSC EXPORT ERROR: General exception - Message: " . $e->getMessage());
							error_log("VSC EXPORT ERROR: Exception class: " . get_class($e));
							error_log("VSC EXPORT ERROR: Stack trace: " . $e->getTraceAsString());

							if ( defined( 'WP_CLI' ) ) {
								WP_CLI::error( sprintf( __( 'Unable to export: %s', VSC_BACKUP_PLUGIN_NAME ), $e->getMessage() ) );
							} else {
								VSC_Backup_Status::error( __( 'Unable to export', VSC_BACKUP_PLUGIN_NAME ), $e->getMessage() );
								VSC_Backup_Notification::error( __( 'Unable to export', VSC_BACKUP_PLUGIN_NAME ), $e->getMessage() );
							}
							VSC_Backup_Directory::delete( vsc_backup_storage_path( $params ) );

							// Check if export is performed from scheduled event
							if ( isset( $params['event_id'] ) ) {
								$params['error_message'] = $e->getMessage();
								do_action( 'vsc_backup_status_export_fail', $params );
							}
							exit;
						}
					}

					// Set completed
					$completed = true;
					if ( isset( $params['completed'] ) ) {
						$completed = (bool) $params['completed'];
					}

					// Do request
					if ( $completed === false || ( $next = next( $filters ) ) && ( $params['priority'] = key( $filters ) ) ) {
						if ( defined( 'WP_CLI' ) ) {
							if ( ! defined( 'DOING_CRON' ) ) {
								continue;
							}
						}

						if ( isset( $params['vsc_backup_manual_export'] ) ) {
							vsc_backup_json_response( $params );
							exit;
						}

						wp_remote_request(
							apply_filters( 'vsc_backup_http_export_url', add_query_arg( array( 'vsc_backup_import' => 1 ), admin_url( 'admin-ajax.php?action=vsc_backup_export' ) ) ),
							array(
								'method'    => apply_filters( 'vsc_backup_http_export_method', 'POST' ),
								'timeout'   => apply_filters( 'vsc_backup_http_export_timeout', 10 ),
								'blocking'  => apply_filters( 'vsc_backup_http_export_blocking', false ),
								'sslverify' => apply_filters( 'vsc_backup_http_export_sslverify', false ),
								'headers'   => apply_filters( 'vsc_backup_http_export_headers', array() ),
								'body'      => apply_filters( 'vsc_backup_http_export_body', $params ),
							)
						);
						exit;
					}
				}

				next( $filters );
			}
		}

		return $params;
	}

	public static function buttons() {
		$active_filters = array();
		$static_filters = array();

		// All-in-One WP Migration
		if ( defined( 'VSC_BACKUP_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_backup_export_file', VSC_Backup_Template::get_content( 'export/button-file' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_backup_export_file', VSC_Backup_Template::get_content( 'export/button-file' ) );
		}

		// Add FTP Extension
		if ( defined( 'AI1WMFE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_backup_export_ftp', VSC_Backup_Template::get_content( 'export/button-ftp' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_backup_export_ftp', VSC_Backup_Template::get_content( 'export/button-ftp' ) );
		}

		// Add Dropbox Extension
		if ( defined( 'AI1WMDE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_backup_export_dropbox', VSC_Backup_Template::get_content( 'export/button-dropbox' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_backup_export_dropbox', VSC_Backup_Template::get_content( 'export/button-dropbox' ) );
		}

		// Add Google Drive Extension
		if ( defined( 'AI1WMGE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_backup_export_gdrive', VSC_Backup_Template::get_content( 'export/button-gdrive' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_backup_export_gdrive', VSC_Backup_Template::get_content( 'export/button-gdrive' ) );
		}

		// Add Amazon S3 Extension
		if ( defined( 'AI1WMSE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_backup_export_s3', VSC_Backup_Template::get_content( 'export/button-s3' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_backup_export_s3', VSC_Backup_Template::get_content( 'export/button-s3' ) );
		}

		// Add Backblaze B2 Extension
		if ( defined( 'AI1WMAE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_backup_export_b2', VSC_Backup_Template::get_content( 'export/button-b2' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_backup_export_b2', VSC_Backup_Template::get_content( 'export/button-b2' ) );
		}

		// Add OneDrive Extension
		if ( defined( 'AI1WMOE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_backup_export_onedrive', VSC_Backup_Template::get_content( 'export/button-onedrive' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_backup_export_onedrive', VSC_Backup_Template::get_content( 'export/button-onedrive' ) );
		}

		// Add Box Extension
		if ( defined( 'AI1WMBE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_backup_export_box', VSC_Backup_Template::get_content( 'export/button-box' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_backup_export_box', VSC_Backup_Template::get_content( 'export/button-box' ) );
		}

		// Add Mega Extension
		if ( defined( 'AI1WMEE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_backup_export_mega', VSC_Backup_Template::get_content( 'export/button-mega' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_backup_export_mega', VSC_Backup_Template::get_content( 'export/button-mega' ) );
		}

		// Add DigitalOcean Spaces Extension
		if ( defined( 'AI1WMIE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_backup_export_digitalocean', VSC_Backup_Template::get_content( 'export/button-digitalocean' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_backup_export_digitalocean', VSC_Backup_Template::get_content( 'export/button-digitalocean' ) );
		}

		// Add Google Cloud Storage Extension
		if ( defined( 'AI1WMCE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_backup_export_gcloud_storage', VSC_Backup_Template::get_content( 'export/button-gcloud-storage' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_backup_export_gcloud_storage', VSC_Backup_Template::get_content( 'export/button-gcloud-storage' ) );
		}

		// Add Microsoft Azure Extension
		if ( defined( 'AI1WMZE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_backup_export_azure_storage', VSC_Backup_Template::get_content( 'export/button-azure-storage' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_backup_export_azure_storage', VSC_Backup_Template::get_content( 'export/button-azure-storage' ) );
		}

		// Add Amazon Glacier Extension
		if ( defined( 'AI1WMRE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_backup_export_glacier', VSC_Backup_Template::get_content( 'export/button-glacier' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_backup_export_glacier', VSC_Backup_Template::get_content( 'export/button-glacier' ) );
		}

		// Add pCloud Extension
		if ( defined( 'AI1WMPE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_backup_export_pcloud', VSC_Backup_Template::get_content( 'export/button-pcloud' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_backup_export_pcloud', VSC_Backup_Template::get_content( 'export/button-pcloud' ) );
		}

		// Add WebDAV Extension
		if ( defined( 'AI1WMWE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_backup_export_webdav', VSC_Backup_Template::get_content( 'export/button-webdav' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_backup_export_webdav', VSC_Backup_Template::get_content( 'export/button-webdav' ) );
		}

		// Add S3 Client Extension
		if ( defined( 'AI1WMNE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_backup_export_s3_client', VSC_Backup_Template::get_content( 'export/button-s3-client' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_backup_export_s3_client', VSC_Backup_Template::get_content( 'export/button-s3-client' ) );
		}

		return array_merge( $active_filters, $static_filters );
	}

	public static function http_export_headers( $headers = array() ) {
		if ( ( $user = get_option( VSC_BACKUP_AUTH_USER ) ) && ( $password = get_option( VSC_BACKUP_AUTH_PASSWORD ) ) ) {
			if ( ( $hash = base64_encode( sprintf( '%s:%s', $user, $password ) ) ) ) {
				$headers['Authorization'] = sprintf( 'Basic %s', $hash );
			}
		}

		return $headers;
	}

	public static function cleanup() {
		try {
			// Iterate over storage directory
			$iterator = new VSC_Backup_Recursive_Directory_Iterator( VSC_BACKUP_STORAGE_PATH );

			// Exclude index.php
			$iterator = new VSC_Backup_Recursive_Exclude_Filter( $iterator, array( 'index.php', 'index.html' ) );

			// Loop over folders and files
			foreach ( $iterator as $item ) {
				try {
					if ( $item->getMTime() < ( time() - VSC_BACKUP_MAX_STORAGE_CLEANUP ) ) {
						if ( $item->isDir() ) {
							VSC_Backup_Directory::delete( $item->getPathname() );
						} else {
							VSC_Backup_File::delete( $item->getPathname() );
						}
					}
				} catch ( Exception $e ) {
				}
			}
		} catch ( Exception $e ) {
		}
	}
}
