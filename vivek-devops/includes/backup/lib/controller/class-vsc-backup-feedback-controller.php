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

class VSC_Backup_Feedback_Controller {

	public static function feedback( $params = array() ) {
		vsc_backup_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// Set secret key
		$secret_key = null;
		if ( isset( $params['secret_key'] ) ) {
			$secret_key = trim( $params['secret_key'] );
		}

		// Set type
		$type = null;
		if ( isset( $params['vsc_backup_type'] ) ) {
			$type = trim( $params['vsc_backup_type'] );
		}

		// Set e-mail
		$email = null;
		if ( isset( $params['vsc_backup_email'] ) ) {
			$email = trim( $params['vsc_backup_email'] );
		}

		// Set message
		$message = null;
		if ( isset( $params['vsc_backup_message'] ) ) {
			$message = trim( $params['vsc_backup_message'] );
		}

		// Set terms
		$terms = false;
		if ( isset( $params['vsc_backup_terms'] ) ) {
			$terms = (bool) $params['vsc_backup_terms'];
		}

		try {
			// Ensure that unauthorized people cannot access feedback action
			vsc_backup_verify_secret_key( $secret_key );
		} catch ( VSC_Backup_Not_Valid_Secret_Key_Exception $e ) {
			exit;
		}

		$extensions = VSC_Backup_Extensions::get();

		// Exclude File Extension
		if ( defined( 'AI1WMTE_PLUGIN_NAME' ) ) {
			unset( $extensions[ AI1WMTE_PLUGIN_NAME ] );
		}

		$purchases = array();
		foreach ( $extensions as $extension ) {
			if ( ( $uuid = get_option( $extension['key'] ) ) ) {
				$purchases[] = $uuid;
			}
		}

		try {
			VSC_Backup_Feedback::add( $type, $email, $message, $terms, implode( PHP_EOL, $purchases ) );
		} catch ( VSC_Backup_Feedback_Exception $e ) {
			vsc_backup_json_response( array( 'errors' => array( $e->getMessage() ) ) );
			exit;
		}

		vsc_backup_json_response( array( 'errors' => array() ) );
		exit;
	}
}
