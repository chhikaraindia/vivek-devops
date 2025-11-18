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

class VSC_Backup_Backups_Controller {

	public static function index() {
		VSC_Backup_Template::render(
			'backups/index',
			array(
				'backups'      => VSC_Backup_Backups::get_files(),
				'labels'       => VSC_Backup_Backups::get_labels(),
				'downloadable' => VSC_Backup_Backups::are_downloadable(),
				'username'     => get_option( VSC_BACKUP_AUTH_USER ),
				'password'     => get_option( VSC_BACKUP_AUTH_PASSWORD ),
			)
		);
	}

	public static function delete( $params = array() ) {
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

		// Set archive
		$archive = null;
		if ( isset( $params['archive'] ) ) {
			$archive = trim( $params['archive'] );
		}

		try {
			// Ensure that unauthorized people cannot access delete action
			vsc_backup_verify_secret_key( $secret_key );
		} catch ( VSC_Backup_Not_Valid_Secret_Key_Exception $e ) {
			exit;
		}

		try {
			VSC_Backup_Backups::delete_file( $archive );
			VSC_Backup_Backups::delete_label( $archive );
		} catch ( VSC_Backup_Backups_Exception $e ) {
			vsc_backup_json_response( array( 'errors' => array( $e->getMessage() ) ) );
			exit;
		}

		vsc_backup_json_response( array( 'errors' => array() ) );
		exit;
	}

	public static function add_label( $params = array() ) {
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

		// Set archive
		$archive = null;
		if ( isset( $params['archive'] ) ) {
			$archive = trim( $params['archive'] );
		}

		// Set backup label
		$label = null;
		if ( isset( $params['label'] ) ) {
			$label = trim( $params['label'] );
		}

		try {
			// Ensure that unauthorized people cannot access add label action
			vsc_backup_verify_secret_key( $secret_key );
		} catch ( VSC_Backup_Not_Valid_Secret_Key_Exception $e ) {
			exit;
		}

		try {
			VSC_Backup_Backups::set_label( $archive, $label );
		} catch ( VSC_Backup_Backups_Exception $e ) {
			vsc_backup_json_response( array( 'errors' => array( $e->getMessage() ) ) );
			exit;
		}

		vsc_backup_json_response( array( 'errors' => array() ) );
		exit;
	}

	public static function backup_list( $params = array() ) {
		vsc_backup_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_GET );
		}

		// Set secret key
		$secret_key = null;
		if ( isset( $params['secret_key'] ) ) {
			$secret_key = trim( $params['secret_key'] );
		}

		try {
			// Ensure that unauthorized people cannot access backups list action
			vsc_backup_verify_secret_key( $secret_key );
		} catch ( VSC_Backup_Not_Valid_Secret_Key_Exception $e ) {
			exit;
		}

		VSC_Backup_Template::render(
			'backups/backups-list',
			array(
				'backups'      => VSC_Backup_Backups::get_files(),
				'labels'       => VSC_Backup_Backups::get_labels(),
				'downloadable' => VSC_Backup_Backups::are_downloadable(),
			)
		);
		exit;
	}

	public static function backup_list_content( $params = array() ) {
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

		try {
			// Ensure that unauthorized people cannot access backups list action
			vsc_backup_verify_secret_key( $secret_key );
		} catch ( VSC_Backup_Not_Valid_Secret_Key_Exception $e ) {
			exit;
		}

		try {
			$archive = new VSC_Backup_Extractor( vsc_backup_backup_path( $params ) );
			vsc_backup_json_response( $archive->list_files() );
		} catch ( Exception $e ) {
			vsc_backup_json_response(
				array(
					'error' => __( 'Unable to list backup content', VSC_BACKUP_PLUGIN_NAME ),
				)
			);
		}

		exit;
	}

	public static function download_file( $params = array() ) {
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

		try {
			// Ensure that unauthorized people cannot access backups list action
			vsc_backup_verify_secret_key( $secret_key );
		} catch ( VSC_Backup_Not_Valid_Secret_Key_Exception $e ) {
			exit;
		}

		$chunk_size = 1024 * 1024;
		$read       = 0;

		try {
			if ( $handle  = vsc_backup_open( vsc_backup_backup_path( $params ), 'r' ) ) {
				vsc_backup_seek( $handle, $params['offset'] );
				while ( ! feof( $handle ) && $read < $params['file_size'] ) {
					$buffer = vsc_backup_read( $handle, min( $chunk_size, $params['file_size'] - $read ) );
					echo $buffer;
					ob_flush();
					flush();
					$read += strlen( $buffer );
				}
				vsc_backup_close( $handle );
			}
		} catch ( Exception $exception ) {
		}

		exit;
	}
}
