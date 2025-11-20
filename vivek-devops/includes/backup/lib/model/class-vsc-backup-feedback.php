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

class VSC_Backup_Feedback {

	/**
	 * Submit customer feedback to vivekchhikara.com
	 *
	 * @param  string  $type      Feedback type
	 * @param  string  $email     User e-mail
	 * @param  string  $message   User message
	 * @param  integer $terms     User accept terms
	 * @param  string  $purchases Purchases IDs
	 *
	 * @return array
	 */
	public static function add( $type, $email, $message, $terms, $purchases ) {
		// Validate email
		if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) === false ) {
			throw new VSC_Backup_Feedback_Exception( __( 'Your email is not valid.', VSC_BACKUP_PLUGIN_NAME ) );
		}

		// Validate type
		if ( empty( $type ) ) {
			throw new VSC_Backup_Feedback_Exception( __( 'Feedback type is not valid.', VSC_BACKUP_PLUGIN_NAME ) );
		}

		// Validate message
		if ( empty( $message ) ) {
			throw new VSC_Backup_Feedback_Exception( __( 'Please enter comments in the text area.', VSC_BACKUP_PLUGIN_NAME ) );
		}

		// Validate terms
		if ( empty( $terms ) ) {
			throw new VSC_Backup_Feedback_Exception( __( 'Please accept feedback term conditions.', VSC_BACKUP_PLUGIN_NAME ) );
		}

		$response = wp_remote_post(
			VSC_BACKUP_FEEDBACK_URL,
			array(
				'timeout' => 15,
				'body'    => array(
					'type'      => $type,
					'email'     => $email,
					'message'   => $message,
					'purchases' => $purchases,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new VSC_Backup_Feedback_Exception( sprintf( __( 'Something went wrong: %s', VSC_BACKUP_PLUGIN_NAME ), $response->get_error_message() ) );
		}

		return $response;
	}
}
