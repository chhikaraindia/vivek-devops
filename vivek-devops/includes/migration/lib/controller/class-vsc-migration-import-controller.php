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

class VSC_Migration_Import_Controller {

	public static function index() {
		VSC_Migration_Template::render( 'import/index' );
	}

	public static function import( $params = array() ) {
		global $vsc_migration_params;

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( array_merge( $_GET, $_POST ) );
		}

		// Set priority
		if ( ! isset( $params['priority'] ) ) {
			$params['priority'] = 10;
		}

		$vsc_migration_params = $params;

		// Set secret key
		$secret_key = null;
		if ( isset( $params['secret_key'] ) ) {
			$secret_key = trim( $params['secret_key'] );
		}

		vsc_migration_setup_environment();
		vsc_migration_setup_errors();

		try {
			// Ensure that unauthorized people cannot access import action
			vsc_migration_verify_secret_key( $secret_key );
		} catch ( VSC_Migration_Not_Valid_Secret_Key_Exception $e ) {
			exit;
		}

		// Loop over filters
		if ( ( $filters = vsc_migration_get_filters( 'vsc_migration_import' ) ) ) {
			while ( $hooks = current( $filters ) ) {
				if ( intval( $params['priority'] ) === key( $filters ) ) {
					foreach ( $hooks as $hook ) {
						try {

							// Run function hook
							$params = call_user_func_array( $hook['function'], array( $params ) );

						} catch ( VSC_Migration_Upload_Exception $e ) {
							do_action( 'vsc_migration_status_upload_error', $params, $e );

							if ( defined( 'WP_CLI' ) ) {
								/* translators: 1: Error code, 2: Error message. */
								WP_CLI::error( sprintf( __( 'Import failed. Code: %1$s. %2$s', 'vivek-devops' ), $e->getCode(), $e->getMessage() ) );
							}

							status_header( $e->getCode() );
							vsc_migration_json_response( array( 'errors' => array( array( 'code' => $e->getCode(), 'message' => $e->getMessage() ) ) ) );
							exit;
						} catch ( VSC_Migration_Database_Exception $e ) {
							do_action( 'vsc_migration_status_import_error', $params, $e );

							if ( defined( 'WP_CLI' ) ) {
								/* translators: 1: Error code, 2: Error message. */
								WP_CLI::error( sprintf( __( 'Import failed (database error). Code: %1$s. %2$s', 'vivek-devops' ), $e->getCode(), $e->getMessage() ) );
							}

							status_header( $e->getCode() );
							vsc_migration_json_response( array( 'errors' => array( array( 'code' => $e->getCode(), 'message' => $e->getMessage() ) ) ) );
							exit;
						} catch ( Exception $e ) {
							do_action( 'vsc_migration_status_import_error', $params, $e );

							if ( defined( 'WP_CLI' ) ) {
								/* translators: Error message. */
								WP_CLI::error( sprintf( __( 'Import failed: %s', 'vivek-devops' ), $e->getMessage() ) );
							}

							VSC_Migration_Status::error( __( 'Import failed', 'vivek-devops' ), $e->getMessage() );
							VSC_Migration_Notification::error( __( 'Import failed', 'vivek-devops' ), $e->getMessage() );
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

						if ( isset( $params['vsc_migration_manual_import'] ) || isset( $params['vsc_migration_manual_restore'] ) ) {
							vsc_migration_json_response( $params );
							exit;
						}

						wp_remote_request(
							apply_filters( 'vsc_migration_http_import_url', add_query_arg( array( 'vsc_migration_import' => 1 ), admin_url( 'admin-ajax.php?action=vsc_migration_import' ) ) ),
							array(
								'method'    => apply_filters( 'vsc_migration_http_import_method', 'POST' ),
								'timeout'   => apply_filters( 'vsc_migration_http_import_timeout', 10 ),
								'blocking'  => apply_filters( 'vsc_migration_http_import_blocking', false ),
								'sslverify' => apply_filters( 'vsc_migration_http_import_sslverify', false ),
								'headers'   => apply_filters( 'vsc_migration_http_import_headers', array() ),
								'body'      => apply_filters( 'vsc_migration_http_import_body', $params ),
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
		if ( defined( 'VSC_MIGRATION_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_migration_import_file', VSC_Migration_Template::get_content( 'import/button-file' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_migration_import_file', VSC_Migration_Template::get_content( 'import/button-file' ) );
		}

		// Add Google Drive Extension
		if ( defined( 'AI1WMGE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_migration_import_gdrive', VSC_Migration_Template::get_content( 'import/button-gdrive' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_migration_import_gdrive', VSC_Migration_Template::get_content( 'import/button-gdrive' ) );
		}

		// Add FTP Extension
		if ( defined( 'AI1WMFE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_migration_import_ftp', VSC_Migration_Template::get_content( 'import/button-ftp' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_migration_import_ftp', VSC_Migration_Template::get_content( 'import/button-ftp' ) );
		}

		// Add Dropbox Extension
		if ( defined( 'AI1WMDE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_migration_import_dropbox', VSC_Migration_Template::get_content( 'import/button-dropbox' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_migration_import_dropbox', VSC_Migration_Template::get_content( 'import/button-dropbox' ) );
		}

		// Add URL Extension
		if ( defined( 'AI1WMLE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_migration_import_url', VSC_Migration_Template::get_content( 'import/button-url' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_migration_import_url', VSC_Migration_Template::get_content( 'import/button-url' ) );
		}

		// Add Amazon S3 Extension
		if ( defined( 'AI1WMSE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_migration_import_s3', VSC_Migration_Template::get_content( 'import/button-s3' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_migration_import_s3', VSC_Migration_Template::get_content( 'import/button-s3' ) );
		}

		// Add OneDrive Extension
		if ( defined( 'AI1WMOE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_migration_import_onedrive', VSC_Migration_Template::get_content( 'import/button-onedrive' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_migration_import_onedrive', VSC_Migration_Template::get_content( 'import/button-onedrive' ) );
		}

		// Add pCloud Extension
		if ( defined( 'AI1WMPE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_migration_import_pcloud', VSC_Migration_Template::get_content( 'import/button-pcloud' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_migration_import_pcloud', VSC_Migration_Template::get_content( 'import/button-pcloud' ) );
		}

		// Add S3 Client Extension
		if ( defined( 'AI1WMNE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_migration_import_s3_client', VSC_Migration_Template::get_content( 'import/button-s3-client' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_migration_import_s3_client', VSC_Migration_Template::get_content( 'import/button-s3-client' ) );
		}

		// Add Google Cloud Storage Extension
		if ( defined( 'AI1WMCE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_migration_import_gcloud_storage', VSC_Migration_Template::get_content( 'import/button-gcloud-storage' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_migration_import_gcloud_storage', VSC_Migration_Template::get_content( 'import/button-gcloud-storage' ) );
		}

		// Add DigitalOcean Spaces Extension
		if ( defined( 'AI1WMIE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_migration_import_digitalocean', VSC_Migration_Template::get_content( 'import/button-digitalocean' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_migration_import_digitalocean', VSC_Migration_Template::get_content( 'import/button-digitalocean' ) );
		}

		// Add Mega Extension
		if ( defined( 'AI1WMEE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_migration_import_mega', VSC_Migration_Template::get_content( 'import/button-mega' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_migration_import_mega', VSC_Migration_Template::get_content( 'import/button-mega' ) );
		}

		// Add Backblaze B2 Extension
		if ( defined( 'AI1WMAE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_migration_import_b2', VSC_Migration_Template::get_content( 'import/button-b2' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_migration_import_b2', VSC_Migration_Template::get_content( 'import/button-b2' ) );
		}

		// Add Box Extension
		if ( defined( 'AI1WMBE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_migration_import_box', VSC_Migration_Template::get_content( 'import/button-box' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_migration_import_box', VSC_Migration_Template::get_content( 'import/button-box' ) );
		}

		// Add Microsoft Azure Extension
		if ( defined( 'AI1WMZE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_migration_import_azure_storage', VSC_Migration_Template::get_content( 'import/button-azure-storage' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_migration_import_azure_storage', VSC_Migration_Template::get_content( 'import/button-azure-storage' ) );
		}

		// Add WebDAV Extension
		if ( defined( 'AI1WMWE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_migration_import_webdav', VSC_Migration_Template::get_content( 'import/button-webdav' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_migration_import_webdav', VSC_Migration_Template::get_content( 'import/button-webdav' ) );
		}

		// Add Amazon Glacier Extension
		if ( defined( 'AI1WMRE_PLUGIN_NAME' ) ) {
			$active_filters[] = apply_filters( 'vsc_migration_import_glacier', VSC_Migration_Template::get_content( 'import/button-glacier' ) );
		} else {
			$static_filters[] = apply_filters( 'vsc_migration_import_glacier', VSC_Migration_Template::get_content( 'import/button-glacier' ) );
		}

		return array_merge( $active_filters, $static_filters );
	}

	public static function pro() {
		return VSC_Migration_Template::get_content( 'import/pro' );
	}

	public static function max_chunk_size() {
		return min(
			vsc_migration_parse_size( ini_get( 'post_max_size' ), VSC_MIGRATION_MAX_CHUNK_SIZE ),
			vsc_migration_parse_size( ini_get( 'upload_max_filesize' ), VSC_MIGRATION_MAX_CHUNK_SIZE ),
			vsc_migration_parse_size( VSC_MIGRATION_MAX_CHUNK_SIZE )
		);
	}
}
