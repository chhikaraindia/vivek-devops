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

class VSC_Migration_Import_Done {

	public static function execute( $params ) {
		global $wp_rewrite;

		// Check multisite.json file
		if ( is_file( vsc_migration_multisite_path( $params ) ) ) {

			// Read multisite.json file
			$handle = vsc_migration_open( vsc_migration_multisite_path( $params ), 'r' );

			// Parse multisite.json file
			$multisite = vsc_migration_read( $handle, filesize( vsc_migration_multisite_path( $params ) ) );
			$multisite = json_decode( $multisite, true );

			// Close handle
			vsc_migration_close( $handle );

			// Activate WordPress plugins
			if ( isset( $multisite['Plugins'] ) && ( $plugins = $multisite['Plugins'] ) ) {
				vsc_migration_activate_plugins( $plugins );
			}

			// Deactivate WordPress SSL plugins
			if ( ! is_ssl() ) {
				vsc_migration_deactivate_plugins(
					array(
						vsc_migration_discover_plugin_basename( 'really-simple-ssl/rlrsssl-really-simple-ssl.php' ),
						vsc_migration_discover_plugin_basename( 'wordpress-https/wordpress-https.php' ),
						vsc_migration_discover_plugin_basename( 'wp-force-ssl/wp-force-ssl.php' ),
						vsc_migration_discover_plugin_basename( 'force-https-littlebizzy/force-https.php' ),
					)
				);

				vsc_migration_woocommerce_force_ssl( false );
			}

			// Deactivate WordPress plugins
			vsc_migration_deactivate_plugins(
				array(
					vsc_migration_discover_plugin_basename( 'invisible-recaptcha/invisible-recaptcha.php' ),
					vsc_migration_discover_plugin_basename( 'wps-hide-login/wps-hide-login.php' ),
					vsc_migration_discover_plugin_basename( 'hide-my-wp/index.php' ),
					vsc_migration_discover_plugin_basename( 'hide-my-wordpress/index.php' ),
					vsc_migration_discover_plugin_basename( 'mycustomwidget/my_custom_widget.php' ),
					vsc_migration_discover_plugin_basename( 'lockdown-wp-admin/lockdown-wp-admin.php' ),
					vsc_migration_discover_plugin_basename( 'rename-wp-login/rename-wp-login.php' ),
					vsc_migration_discover_plugin_basename( 'wp-simple-firewall/icwp-wpsf.php' ),
					vsc_migration_discover_plugin_basename( 'join-my-multisite/joinmymultisite.php' ),
					vsc_migration_discover_plugin_basename( 'multisite-clone-duplicator/multisite-clone-duplicator.php' ),
					vsc_migration_discover_plugin_basename( 'wordpress-mu-domain-mapping/domain_mapping.php' ),
					vsc_migration_discover_plugin_basename( 'wordpress-starter/siteground-wizard.php' ),
					vsc_migration_discover_plugin_basename( 'pro-sites/pro-sites.php' ),
					vsc_migration_discover_plugin_basename( 'wpide/WPide.php' ),
					vsc_migration_discover_plugin_basename( 'page-optimize/page-optimize.php' ),
					vsc_migration_discover_plugin_basename( 'update-services/update-services.php' ),
				)
			);

			// Deactivate Swift Optimizer rules
			vsc_migration_deactivate_swift_optimizer_rules(
				array(
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration/vivek-devops-migration.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-azure-storage-extension/vivek-devops-migration-azure-storage-extension.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-b2-extension/vivek-devops-migration-b2-extension.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-backup/vivek-devops-migration-backup.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-box-extension/vivek-devops-migration-box-extension.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-digitalocean-extension/vivek-devops-migration-digitalocean-extension.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-direct-extension/vivek-devops-migration-direct-extension.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-dropbox-extension/vivek-devops-migration-dropbox-extension.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-file-extension/vivek-devops-migration-file-extension.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-ftp-extension/vivek-devops-migration-ftp-extension.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-gcloud-storage-extension/vivek-devops-migration-gcloud-storage-extension.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-gdrive-extension/vivek-devops-migration-gdrive-extension.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-glacier-extension/vivek-devops-migration-glacier-extension.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-mega-extension/vivek-devops-migration-mega-extension.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-multisite-extension/vivek-devops-migration-multisite-extension.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-onedrive-extension/vivek-devops-migration-onedrive-extension.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-pcloud-extension/vivek-devops-migration-pcloud-extension.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-pro/vivek-devops-migration-pro.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-s3-client-extension/vivek-devops-migration-s3-client-extension.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-s3-extension/vivek-devops-migration-s3-extension.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-unlimited-extension/vivek-devops-migration-unlimited-extension.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-url-extension/vivek-devops-migration-url-extension.php' ),
					vsc_migration_discover_plugin_basename( 'vivek-devops-migration-webdav-extension/vivek-devops-migration-webdav-extension.php' ),
				)
			);

			// Deactivate Revolution Slider
			vsc_migration_deactivate_revolution_slider( vsc_migration_discover_plugin_basename( 'revslider/revslider.php' ) );

			// Deactivate Jetpack modules
			vsc_migration_deactivate_jetpack_modules( array( 'photon', 'sso' ) );

			// Flush Elementor cache
			vsc_migration_elementor_cache_flush();

			// Initial DB version
			vsc_migration_initial_db_version();

		} else {

			// Check package.json file
			if ( is_file( vsc_migration_package_path( $params ) ) ) {

				// Read package.json file
				$handle = vsc_migration_open( vsc_migration_package_path( $params ), 'r' );

				// Parse package.json file
				$package = vsc_migration_read( $handle, filesize( vsc_migration_package_path( $params ) ) );
				$package = json_decode( $package, true );

				// Close handle
				vsc_migration_close( $handle );

				// Activate WordPress plugins
				if ( isset( $package['Plugins'] ) && ( $plugins = $package['Plugins'] ) ) {
					vsc_migration_activate_plugins( $plugins );
				}

				// Activate WordPress template
				if ( isset( $package['Template'] ) && ( $template = $package['Template'] ) ) {
					vsc_migration_activate_template( $template );
				}

				// Activate WordPress stylesheet
				if ( isset( $package['Stylesheet'] ) && ( $stylesheet = $package['Stylesheet'] ) ) {
					vsc_migration_activate_stylesheet( $stylesheet );
				}

				// Deactivate WordPress SSL plugins
				if ( ! is_ssl() ) {
					vsc_migration_deactivate_plugins(
						array(
							vsc_migration_discover_plugin_basename( 'really-simple-ssl/rlrsssl-really-simple-ssl.php' ),
							vsc_migration_discover_plugin_basename( 'wordpress-https/wordpress-https.php' ),
							vsc_migration_discover_plugin_basename( 'wp-force-ssl/wp-force-ssl.php' ),
							vsc_migration_discover_plugin_basename( 'force-https-littlebizzy/force-https.php' ),
						)
					);

					vsc_migration_woocommerce_force_ssl( false );
				}

				// Deactivate WordPress plugins
				vsc_migration_deactivate_plugins(
					array(
						vsc_migration_discover_plugin_basename( 'invisible-recaptcha/invisible-recaptcha.php' ),
						vsc_migration_discover_plugin_basename( 'wps-hide-login/wps-hide-login.php' ),
						vsc_migration_discover_plugin_basename( 'hide-my-wp/index.php' ),
						vsc_migration_discover_plugin_basename( 'hide-my-wordpress/index.php' ),
						vsc_migration_discover_plugin_basename( 'mycustomwidget/my_custom_widget.php' ),
						vsc_migration_discover_plugin_basename( 'lockdown-wp-admin/lockdown-wp-admin.php' ),
						vsc_migration_discover_plugin_basename( 'rename-wp-login/rename-wp-login.php' ),
						vsc_migration_discover_plugin_basename( 'wp-simple-firewall/icwp-wpsf.php' ),
						vsc_migration_discover_plugin_basename( 'join-my-multisite/joinmymultisite.php' ),
						vsc_migration_discover_plugin_basename( 'multisite-clone-duplicator/multisite-clone-duplicator.php' ),
						vsc_migration_discover_plugin_basename( 'wordpress-mu-domain-mapping/domain_mapping.php' ),
						vsc_migration_discover_plugin_basename( 'wordpress-starter/siteground-wizard.php' ),
						vsc_migration_discover_plugin_basename( 'pro-sites/pro-sites.php' ),
						vsc_migration_discover_plugin_basename( 'wpide/WPide.php' ),
						vsc_migration_discover_plugin_basename( 'page-optimize/page-optimize.php' ),
						vsc_migration_discover_plugin_basename( 'update-services/update-services.php' ),
					)
				);

				// Deactivate Swift Optimizer rules
				vsc_migration_deactivate_swift_optimizer_rules(
					array(
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration/vivek-devops-migration.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-azure-storage-extension/vivek-devops-migration-azure-storage-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-b2-extension/vivek-devops-migration-b2-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-backup/vivek-devops-migration-backup.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-box-extension/vivek-devops-migration-box-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-digitalocean-extension/vivek-devops-migration-digitalocean-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-direct-extension/vivek-devops-migration-direct-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-dropbox-extension/vivek-devops-migration-dropbox-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-file-extension/vivek-devops-migration-file-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-ftp-extension/vivek-devops-migration-ftp-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-gcloud-storage-extension/vivek-devops-migration-gcloud-storage-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-gdrive-extension/vivek-devops-migration-gdrive-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-glacier-extension/vivek-devops-migration-glacier-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-mega-extension/vivek-devops-migration-mega-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-multisite-extension/vivek-devops-migration-multisite-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-onedrive-extension/vivek-devops-migration-onedrive-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-pcloud-extension/vivek-devops-migration-pcloud-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-pro/vivek-devops-migration-pro.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-s3-client-extension/vivek-devops-migration-s3-client-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-s3-extension/vivek-devops-migration-s3-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-unlimited-extension/vivek-devops-migration-unlimited-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-url-extension/vivek-devops-migration-url-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-webdav-extension/vivek-devops-migration-webdav-extension.php' ),
					)
				);

				// Deactivate Revolution Slider
				vsc_migration_deactivate_revolution_slider( vsc_migration_discover_plugin_basename( 'revslider/revslider.php' ) );

				// Deactivate Jetpack modules
				vsc_migration_deactivate_jetpack_modules( array( 'photon', 'sso' ) );

				// Flush Elementor cache
				vsc_migration_elementor_cache_flush();

				// Initial DB version
				vsc_migration_initial_db_version();
			}
		}

		// Check blogs.json file
		if ( is_file( vsc_migration_blogs_path( $params ) ) ) {

			// Read blogs.json file
			$handle = vsc_migration_open( vsc_migration_blogs_path( $params ), 'r' );

			// Parse blogs.json file
			$blogs = vsc_migration_read( $handle, filesize( vsc_migration_blogs_path( $params ) ) );
			$blogs = json_decode( $blogs, true );

			// Close handle
			vsc_migration_close( $handle );

			// Loop over blogs
			foreach ( $blogs as $blog ) {

				// Activate WordPress plugins
				if ( isset( $blog['New']['Plugins'] ) && ( $plugins = $blog['New']['Plugins'] ) ) {
					vsc_migration_activate_plugins( $plugins );
				}

				// Activate WordPress template
				if ( isset( $blog['New']['Template'] ) && ( $template = $blog['New']['Template'] ) ) {
					vsc_migration_activate_template( $template );
				}

				// Activate WordPress stylesheet
				if ( isset( $blog['New']['Stylesheet'] ) && ( $stylesheet = $blog['New']['Stylesheet'] ) ) {
					vsc_migration_activate_stylesheet( $stylesheet );
				}

				// Deactivate WordPress SSL plugins
				if ( ! is_ssl() ) {
					vsc_migration_deactivate_plugins(
						array(
							vsc_migration_discover_plugin_basename( 'really-simple-ssl/rlrsssl-really-simple-ssl.php' ),
							vsc_migration_discover_plugin_basename( 'wordpress-https/wordpress-https.php' ),
							vsc_migration_discover_plugin_basename( 'wp-force-ssl/wp-force-ssl.php' ),
							vsc_migration_discover_plugin_basename( 'force-https-littlebizzy/force-https.php' ),
						)
					);

					vsc_migration_woocommerce_force_ssl( false );
				}

				// Deactivate WordPress plugins
				vsc_migration_deactivate_plugins(
					array(
						vsc_migration_discover_plugin_basename( 'invisible-recaptcha/invisible-recaptcha.php' ),
						vsc_migration_discover_plugin_basename( 'wps-hide-login/wps-hide-login.php' ),
						vsc_migration_discover_plugin_basename( 'hide-my-wp/index.php' ),
						vsc_migration_discover_plugin_basename( 'hide-my-wordpress/index.php' ),
						vsc_migration_discover_plugin_basename( 'mycustomwidget/my_custom_widget.php' ),
						vsc_migration_discover_plugin_basename( 'lockdown-wp-admin/lockdown-wp-admin.php' ),
						vsc_migration_discover_plugin_basename( 'rename-wp-login/rename-wp-login.php' ),
						vsc_migration_discover_plugin_basename( 'wp-simple-firewall/icwp-wpsf.php' ),
						vsc_migration_discover_plugin_basename( 'join-my-multisite/joinmymultisite.php' ),
						vsc_migration_discover_plugin_basename( 'multisite-clone-duplicator/multisite-clone-duplicator.php' ),
						vsc_migration_discover_plugin_basename( 'wordpress-mu-domain-mapping/domain_mapping.php' ),
						vsc_migration_discover_plugin_basename( 'wordpress-starter/siteground-wizard.php' ),
						vsc_migration_discover_plugin_basename( 'pro-sites/pro-sites.php' ),
						vsc_migration_discover_plugin_basename( 'wpide/WPide.php' ),
						vsc_migration_discover_plugin_basename( 'page-optimize/page-optimize.php' ),
						vsc_migration_discover_plugin_basename( 'update-services/update-services.php' ),
					)
				);

				// Deactivate Swift Optimizer rules
				vsc_migration_deactivate_swift_optimizer_rules(
					array(
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration/vivek-devops-migration.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-azure-storage-extension/vivek-devops-migration-azure-storage-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-b2-extension/vivek-devops-migration-b2-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-backup/vivek-devops-migration-backup.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-box-extension/vivek-devops-migration-box-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-digitalocean-extension/vivek-devops-migration-digitalocean-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-direct-extension/vivek-devops-migration-direct-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-dropbox-extension/vivek-devops-migration-dropbox-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-file-extension/vivek-devops-migration-file-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-ftp-extension/vivek-devops-migration-ftp-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-gcloud-storage-extension/vivek-devops-migration-gcloud-storage-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-gdrive-extension/vivek-devops-migration-gdrive-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-glacier-extension/vivek-devops-migration-glacier-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-mega-extension/vivek-devops-migration-mega-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-multisite-extension/vivek-devops-migration-multisite-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-onedrive-extension/vivek-devops-migration-onedrive-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-pcloud-extension/vivek-devops-migration-pcloud-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-pro/vivek-devops-migration-pro.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-s3-client-extension/vivek-devops-migration-s3-client-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-s3-extension/vivek-devops-migration-s3-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-unlimited-extension/vivek-devops-migration-unlimited-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-url-extension/vivek-devops-migration-url-extension.php' ),
						vsc_migration_discover_plugin_basename( 'vivek-devops-migration-webdav-extension/vivek-devops-migration-webdav-extension.php' ),
					)
				);

				// Deactivate Revolution Slider
				vsc_migration_deactivate_revolution_slider( vsc_migration_discover_plugin_basename( 'revslider/revslider.php' ) );

				// Deactivate Jetpack modules
				vsc_migration_deactivate_jetpack_modules( array( 'photon', 'sso' ) );

				// Flush Elementor cache
				vsc_migration_elementor_cache_flush();

				// Initial DB version
				vsc_migration_initial_db_version();
			}
		}

		// Clear auth cookie (WP Cerber)
		if ( vsc_migration_validate_plugin_basename( 'wp-cerber/wp-cerber.php' ) ) {
			wp_clear_auth_cookie();
		}

		$should_reset_permalinks = false;

		// Switch to default permalink structure
		if ( ( $should_reset_permalinks = vsc_migration_should_reset_permalinks( $params ) ) ) {
			$wp_rewrite->set_permalink_structure( '' );
		}

		// Set progress
		if ( vsc_migration_validate_plugin_basename( 'fusion-builder/fusion-builder.php' ) ) {
			VSC_Migration_Status::done( __( 'Your site has been imported successfully!', 'vivek-devops' ), VSC_Migration_Template::get_content( 'import/avada', array( 'should_reset_permalinks' => $should_reset_permalinks ) ) );
		} elseif ( vsc_migration_validate_plugin_basename( 'oxygen/functions.php' ) ) {
			VSC_Migration_Status::done( __( 'Your site has been imported successfully!', 'vivek-devops' ), VSC_Migration_Template::get_content( 'import/oxygen', array( 'should_reset_permalinks' => $should_reset_permalinks ) ) );
		} else {
			VSC_Migration_Status::done( __( 'Your site has been imported successfully!', 'vivek-devops' ), VSC_Migration_Template::get_content( 'import/done', array( 'should_reset_permalinks' => $should_reset_permalinks ) ) );
		}

		do_action( 'vsc_migration_status_import_done', $params );

		return $params;
	}
}
