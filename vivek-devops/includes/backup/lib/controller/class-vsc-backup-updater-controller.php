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

class VSC_Backup_Updater_Controller {

	public static function plugins_api( $result, $action = null, $args = null ) {
		return VSC_Backup_Updater::plugins_api( $result, $action, $args );
	}

	public static function pre_update_plugins( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		// Check for updates every 11 hours
		if ( ( $last_check_for_updates = get_site_transient( VSC_BACKUP_LAST_CHECK_FOR_UPDATES ) ) ) {
			if ( ( time() - $last_check_for_updates ) < 11 * HOUR_IN_SECONDS ) {
				return $transient;
			}
		}

		// Set last check for updates
		set_site_transient( VSC_BACKUP_LAST_CHECK_FOR_UPDATES, time() );

		// Check for updates
		VSC_Backup_Updater::check_for_updates();

		return $transient;
	}

	public static function update_plugins( $transient ) {
		return VSC_Backup_Updater::update_plugins( $transient );
	}

	public static function check_for_updates() {
		return VSC_Backup_Updater::check_for_updates();
	}

	public static function plugin_row_meta( $plugin_meta, $plugin_file ) {
		return VSC_Backup_Updater::plugin_row_meta( $plugin_meta, $plugin_file );
	}

	public static function in_plugin_update_message( $plugin_data, $response ) {
		$updater = get_option( VSC_BACKUP_UPDATER, array() );

		// Get updater details
		if ( isset( $updater[ $plugin_data['slug'] ]['update_message'] ) ) {
			VSC_Backup_Template::render( 'updater/update', array( 'message' => $updater[ $plugin_data['slug'] ]['update_message'] ) );
		}
	}

	public static function updater( $params = array() ) {
		if ( check_ajax_referer( 'vsc_backup_updater', 'vsc_backup_nonce' ) ) {
			vsc_backup_setup_environment();

			// Set params
			if ( empty( $params ) ) {
				$params = stripslashes_deep( $_POST );
			}

			// Set uuid
			$uuid = null;
			if ( isset( $params['vsc_backup_uuid'] ) ) {
				$uuid = trim( $params['vsc_backup_uuid'] );
			}

			// Set extension
			$extension = null;
			if ( isset( $params['vsc_backup_extension'] ) ) {
				$extension = trim( $params['vsc_backup_extension'] );
			}

			$extensions = VSC_Backup_Extensions::get();

			// Verify whether extension exists
			if ( isset( $extensions[ $extension ] ) ) {
				update_option( $extensions[ $extension ]['key'], $uuid );
			}
		}
	}
}
