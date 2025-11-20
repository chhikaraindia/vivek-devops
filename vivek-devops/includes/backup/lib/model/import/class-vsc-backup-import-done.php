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

class VSC_Backup_Import_Done {

	public static function execute( $params ) {
		global $wp_rewrite;

		// Check multisite.json file
		if ( is_file( vsc_backup_multisite_path( $params ) ) ) {

			// Read multisite.json file
			$handle = vsc_backup_open( vsc_backup_multisite_path( $params ), 'r' );

			// Parse multisite.json file
			$multisite = vsc_backup_read( $handle, filesize( vsc_backup_multisite_path( $params ) ) );
			$multisite = json_decode( $multisite, true );

			// Close handle
			vsc_backup_close( $handle );

			// Activate WordPress plugins
			if ( isset( $multisite['Plugins'] ) && ( $plugins = $multisite['Plugins'] ) ) {
				vsc_backup_activate_plugins( $plugins );
			}

			// Deactivate WordPress SSL plugins
			if ( ! is_ssl() ) {
				vsc_backup_deactivate_plugins(
					array(
						vsc_backup_discover_plugin_basename( 'really-simple-ssl/rlrsssl-really-simple-ssl.php' ),
						vsc_backup_discover_plugin_basename( 'wordpress-https/wordpress-https.php' ),
						vsc_backup_discover_plugin_basename( 'wp-force-ssl/wp-force-ssl.php' ),
						vsc_backup_discover_plugin_basename( 'force-https-littlebizzy/force-https.php' ),
					)
				);

				vsc_backup_woocommerce_force_ssl( false );
			}

			// Deactivate WordPress plugins
			vsc_backup_deactivate_plugins(
				array(
					vsc_backup_discover_plugin_basename( 'invisible-recaptcha/invisible-recaptcha.php' ),
					vsc_backup_discover_plugin_basename( 'wps-hide-login/wps-hide-login.php' ),
					vsc_backup_discover_plugin_basename( 'hide-my-wp/index.php' ),
					vsc_backup_discover_plugin_basename( 'hide-my-wordpress/index.php' ),
					vsc_backup_discover_plugin_basename( 'mycustomwidget/my_custom_widget.php' ),
					vsc_backup_discover_plugin_basename( 'lockdown-wp-admin/lockdown-wp-admin.php' ),
					vsc_backup_discover_plugin_basename( 'rename-wp-login/rename-wp-login.php' ),
					vsc_backup_discover_plugin_basename( 'wp-simple-firewall/icwp-wpsf.php' ),
					vsc_backup_discover_plugin_basename( 'join-my-multisite/joinmymultisite.php' ),
					vsc_backup_discover_plugin_basename( 'multisite-clone-duplicator/multisite-clone-duplicator.php' ),
					vsc_backup_discover_plugin_basename( 'wordpress-mu-domain-mapping/domain_mapping.php' ),
					vsc_backup_discover_plugin_basename( 'wordpress-starter/siteground-wizard.php' ),
					vsc_backup_discover_plugin_basename( 'pro-sites/pro-sites.php' ),
					vsc_backup_discover_plugin_basename( 'wpide/WPide.php' ),
					vsc_backup_discover_plugin_basename( 'page-optimize/page-optimize.php' ),
				)
			);

			// Deactivate Swift Optimizer rules
			vsc_backup_deactivate_swift_optimizer_rules(
				array(
					vsc_backup_discover_plugin_basename( 'vsc-backup/vsc-backup.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-azure-storage-extension/vsc-backup-azure-storage-extension.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-b2-extension/vsc-backup-b2-extension.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-backup/vsc-backup-backup.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-box-extension/vsc-backup-box-extension.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-digitalocean-extension/vsc-backup-digitalocean-extension.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-direct-extension/vsc-backup-direct-extension.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-dropbox-extension/vsc-backup-dropbox-extension.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-file-extension/vsc-backup-file-extension.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-ftp-extension/vsc-backup-ftp-extension.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-gcloud-storage-extension/vsc-backup-gcloud-storage-extension.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-gdrive-extension/vsc-backup-gdrive-extension.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-glacier-extension/vsc-backup-glacier-extension.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-mega-extension/vsc-backup-mega-extension.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-multisite-extension/vsc-backup-multisite-extension.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-onedrive-extension/vsc-backup-onedrive-extension.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-pcloud-extension/vsc-backup-pcloud-extension.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-pro/vsc-backup-pro.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-s3-client-extension/vsc-backup-s3-client-extension.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-s3-extension/vsc-backup-s3-extension.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-unlimited-extension/vsc-backup-unlimited-extension.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-url-extension/vsc-backup-url-extension.php' ),
					vsc_backup_discover_plugin_basename( 'vsc-backup-webdav-extension/vsc-backup-webdav-extension.php' ),
				)
			);

			// Deactivate Revolution Slider
			vsc_backup_deactivate_revolution_slider( vsc_backup_discover_plugin_basename( 'revslider/revslider.php' ) );

			// Deactivate Jetpack modules
			vsc_backup_deactivate_jetpack_modules( array( 'photon', 'sso' ) );

			// Flush Elementor cache
			vsc_backup_elementor_cache_flush();

			// Initial DB version
			vsc_backup_initial_db_version();

		} else {

			// Check package.json file
			if ( is_file( vsc_backup_package_path( $params ) ) ) {

				// Read package.json file
				$handle = vsc_backup_open( vsc_backup_package_path( $params ), 'r' );

				// Parse package.json file
				$package = vsc_backup_read( $handle, filesize( vsc_backup_package_path( $params ) ) );
				$package = json_decode( $package, true );

				// Close handle
				vsc_backup_close( $handle );

				// Activate WordPress plugins
				if ( isset( $package['Plugins'] ) && ( $plugins = $package['Plugins'] ) ) {
					vsc_backup_activate_plugins( $plugins );
				}

				// Activate WordPress template
				if ( isset( $package['Template'] ) && ( $template = $package['Template'] ) ) {
					vsc_backup_activate_template( $template );
				}

				// Activate WordPress stylesheet
				if ( isset( $package['Stylesheet'] ) && ( $stylesheet = $package['Stylesheet'] ) ) {
					vsc_backup_activate_stylesheet( $stylesheet );
				}

				// Deactivate WordPress SSL plugins
				if ( ! is_ssl() ) {
					vsc_backup_deactivate_plugins(
						array(
							vsc_backup_discover_plugin_basename( 'really-simple-ssl/rlrsssl-really-simple-ssl.php' ),
							vsc_backup_discover_plugin_basename( 'wordpress-https/wordpress-https.php' ),
							vsc_backup_discover_plugin_basename( 'wp-force-ssl/wp-force-ssl.php' ),
							vsc_backup_discover_plugin_basename( 'force-https-littlebizzy/force-https.php' ),
						)
					);

					vsc_backup_woocommerce_force_ssl( false );
				}

				// Deactivate WordPress plugins
				vsc_backup_deactivate_plugins(
					array(
						vsc_backup_discover_plugin_basename( 'invisible-recaptcha/invisible-recaptcha.php' ),
						vsc_backup_discover_plugin_basename( 'wps-hide-login/wps-hide-login.php' ),
						vsc_backup_discover_plugin_basename( 'hide-my-wp/index.php' ),
						vsc_backup_discover_plugin_basename( 'hide-my-wordpress/index.php' ),
						vsc_backup_discover_plugin_basename( 'mycustomwidget/my_custom_widget.php' ),
						vsc_backup_discover_plugin_basename( 'lockdown-wp-admin/lockdown-wp-admin.php' ),
						vsc_backup_discover_plugin_basename( 'rename-wp-login/rename-wp-login.php' ),
						vsc_backup_discover_plugin_basename( 'wp-simple-firewall/icwp-wpsf.php' ),
						vsc_backup_discover_plugin_basename( 'join-my-multisite/joinmymultisite.php' ),
						vsc_backup_discover_plugin_basename( 'multisite-clone-duplicator/multisite-clone-duplicator.php' ),
						vsc_backup_discover_plugin_basename( 'wordpress-mu-domain-mapping/domain_mapping.php' ),
						vsc_backup_discover_plugin_basename( 'wordpress-starter/siteground-wizard.php' ),
						vsc_backup_discover_plugin_basename( 'pro-sites/pro-sites.php' ),
						vsc_backup_discover_plugin_basename( 'wpide/WPide.php' ),
						vsc_backup_discover_plugin_basename( 'page-optimize/page-optimize.php' ),
					)
				);

				// Deactivate Swift Optimizer rules
				vsc_backup_deactivate_swift_optimizer_rules(
					array(
						vsc_backup_discover_plugin_basename( 'vsc-backup/vsc-backup.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-azure-storage-extension/vsc-backup-azure-storage-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-b2-extension/vsc-backup-b2-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-backup/vsc-backup-backup.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-box-extension/vsc-backup-box-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-digitalocean-extension/vsc-backup-digitalocean-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-direct-extension/vsc-backup-direct-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-dropbox-extension/vsc-backup-dropbox-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-file-extension/vsc-backup-file-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-ftp-extension/vsc-backup-ftp-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-gcloud-storage-extension/vsc-backup-gcloud-storage-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-gdrive-extension/vsc-backup-gdrive-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-glacier-extension/vsc-backup-glacier-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-mega-extension/vsc-backup-mega-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-multisite-extension/vsc-backup-multisite-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-onedrive-extension/vsc-backup-onedrive-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-pcloud-extension/vsc-backup-pcloud-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-pro/vsc-backup-pro.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-s3-client-extension/vsc-backup-s3-client-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-s3-extension/vsc-backup-s3-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-unlimited-extension/vsc-backup-unlimited-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-url-extension/vsc-backup-url-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-webdav-extension/vsc-backup-webdav-extension.php' ),
					)
				);

				// Deactivate Revolution Slider
				vsc_backup_deactivate_revolution_slider( vsc_backup_discover_plugin_basename( 'revslider/revslider.php' ) );

				// Deactivate Jetpack modules
				vsc_backup_deactivate_jetpack_modules( array( 'photon', 'sso' ) );

				// Flush Elementor cache
				vsc_backup_elementor_cache_flush();

				// Initial DB version
				vsc_backup_initial_db_version();
			}
		}

		// Check blogs.json file
		if ( is_file( vsc_backup_blogs_path( $params ) ) ) {

			// Read blogs.json file
			$handle = vsc_backup_open( vsc_backup_blogs_path( $params ), 'r' );

			// Parse blogs.json file
			$blogs = vsc_backup_read( $handle, filesize( vsc_backup_blogs_path( $params ) ) );
			$blogs = json_decode( $blogs, true );

			// Close handle
			vsc_backup_close( $handle );

			// Loop over blogs
			foreach ( $blogs as $blog ) {

				// Activate WordPress plugins
				if ( isset( $blog['New']['Plugins'] ) && ( $plugins = $blog['New']['Plugins'] ) ) {
					vsc_backup_activate_plugins( $plugins );
				}

				// Activate WordPress template
				if ( isset( $blog['New']['Template'] ) && ( $template = $blog['New']['Template'] ) ) {
					vsc_backup_activate_template( $template );
				}

				// Activate WordPress stylesheet
				if ( isset( $blog['New']['Stylesheet'] ) && ( $stylesheet = $blog['New']['Stylesheet'] ) ) {
					vsc_backup_activate_stylesheet( $stylesheet );
				}

				// Deactivate WordPress SSL plugins
				if ( ! is_ssl() ) {
					vsc_backup_deactivate_plugins(
						array(
							vsc_backup_discover_plugin_basename( 'really-simple-ssl/rlrsssl-really-simple-ssl.php' ),
							vsc_backup_discover_plugin_basename( 'wordpress-https/wordpress-https.php' ),
							vsc_backup_discover_plugin_basename( 'wp-force-ssl/wp-force-ssl.php' ),
							vsc_backup_discover_plugin_basename( 'force-https-littlebizzy/force-https.php' ),
						)
					);

					vsc_backup_woocommerce_force_ssl( false );
				}

				// Deactivate WordPress plugins
				vsc_backup_deactivate_plugins(
					array(
						vsc_backup_discover_plugin_basename( 'invisible-recaptcha/invisible-recaptcha.php' ),
						vsc_backup_discover_plugin_basename( 'wps-hide-login/wps-hide-login.php' ),
						vsc_backup_discover_plugin_basename( 'hide-my-wp/index.php' ),
						vsc_backup_discover_plugin_basename( 'hide-my-wordpress/index.php' ),
						vsc_backup_discover_plugin_basename( 'mycustomwidget/my_custom_widget.php' ),
						vsc_backup_discover_plugin_basename( 'lockdown-wp-admin/lockdown-wp-admin.php' ),
						vsc_backup_discover_plugin_basename( 'rename-wp-login/rename-wp-login.php' ),
						vsc_backup_discover_plugin_basename( 'wp-simple-firewall/icwp-wpsf.php' ),
						vsc_backup_discover_plugin_basename( 'join-my-multisite/joinmymultisite.php' ),
						vsc_backup_discover_plugin_basename( 'multisite-clone-duplicator/multisite-clone-duplicator.php' ),
						vsc_backup_discover_plugin_basename( 'wordpress-mu-domain-mapping/domain_mapping.php' ),
						vsc_backup_discover_plugin_basename( 'wordpress-starter/siteground-wizard.php' ),
						vsc_backup_discover_plugin_basename( 'pro-sites/pro-sites.php' ),
						vsc_backup_discover_plugin_basename( 'wpide/WPide.php' ),
						vsc_backup_discover_plugin_basename( 'page-optimize/page-optimize.php' ),
					)
				);

				// Deactivate Swift Optimizer rules
				vsc_backup_deactivate_swift_optimizer_rules(
					array(
						vsc_backup_discover_plugin_basename( 'vsc-backup/vsc-backup.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-azure-storage-extension/vsc-backup-azure-storage-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-b2-extension/vsc-backup-b2-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-backup/vsc-backup-backup.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-box-extension/vsc-backup-box-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-digitalocean-extension/vsc-backup-digitalocean-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-direct-extension/vsc-backup-direct-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-dropbox-extension/vsc-backup-dropbox-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-file-extension/vsc-backup-file-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-ftp-extension/vsc-backup-ftp-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-gcloud-storage-extension/vsc-backup-gcloud-storage-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-gdrive-extension/vsc-backup-gdrive-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-glacier-extension/vsc-backup-glacier-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-mega-extension/vsc-backup-mega-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-multisite-extension/vsc-backup-multisite-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-onedrive-extension/vsc-backup-onedrive-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-pcloud-extension/vsc-backup-pcloud-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-pro/vsc-backup-pro.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-s3-client-extension/vsc-backup-s3-client-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-s3-extension/vsc-backup-s3-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-unlimited-extension/vsc-backup-unlimited-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-url-extension/vsc-backup-url-extension.php' ),
						vsc_backup_discover_plugin_basename( 'vsc-backup-webdav-extension/vsc-backup-webdav-extension.php' ),
					)
				);

				// Deactivate Revolution Slider
				vsc_backup_deactivate_revolution_slider( vsc_backup_discover_plugin_basename( 'revslider/revslider.php' ) );

				// Deactivate Jetpack modules
				vsc_backup_deactivate_jetpack_modules( array( 'photon', 'sso' ) );

				// Flush Elementor cache
				vsc_backup_elementor_cache_flush();

				// Initial DB version
				vsc_backup_initial_db_version();
			}
		}

		// Clear auth cookie (WP Cerber)
		if ( vsc_backup_validate_plugin_basename( 'wp-cerber/wp-cerber.php' ) ) {
			wp_clear_auth_cookie();
		}

		$should_reset_permalinks = false;

		// Switch to default permalink structure
		if ( ( $should_reset_permalinks = vsc_backup_should_reset_permalinks( $params ) ) ) {
			$wp_rewrite->set_permalink_structure( '' );
		}

		// Set progress
		if ( vsc_backup_validate_plugin_basename( 'fusion-builder/fusion-builder.php' ) ) {
			VSC_Backup_Status::done( __( 'Your site has been imported successfully!', VSC_BACKUP_PLUGIN_NAME ), VSC_Backup_Template::get_content( 'import/avada', array( 'should_reset_permalinks' => $should_reset_permalinks ) ) );
		} elseif ( vsc_backup_validate_plugin_basename( 'oxygen/functions.php' ) ) {
			VSC_Backup_Status::done( __( 'Your site has been imported successfully!', VSC_BACKUP_PLUGIN_NAME ), VSC_Backup_Template::get_content( 'import/oxygen', array( 'should_reset_permalinks' => $should_reset_permalinks ) ) );
		} else {
			VSC_Backup_Status::done( __( 'Your site has been imported successfully!', VSC_BACKUP_PLUGIN_NAME ), VSC_Backup_Template::get_content( 'import/done', array( 'should_reset_permalinks' => $should_reset_permalinks ) ) );
		}

		do_action( 'vsc_backup_status_import_done', $params );

		return $params;
	}
}
