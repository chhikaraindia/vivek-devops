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

class VSC_Migration_Backups_Controller {

	public static function index() {
		VSC_Migration_Template::render(
			'backups/index',
			array(
				'backups'      => VSC_Migration_Backups::get_files(),
				'labels'       => VSC_Migration_Backups::get_labels(),
				'downloadable' => VSC_Migration_Backups::are_downloadable(),
			)
		);
	}

	public static function delete( $params = array() ) {
		vsc_migration_setup_environment();

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
			vsc_migration_verify_secret_key( $secret_key );
		} catch ( VSC_Migration_Not_Valid_Secret_Key_Exception $e ) {
			exit;
		}

		try {
			VSC_Migration_Backups::delete_file( $archive );
			VSC_Migration_Backups::delete_label( $archive );
		} catch ( VSC_Migration_Backups_Exception $e ) {
			vsc_migration_json_response( array( 'errors' => array( $e->getMessage() ) ) );
			exit;
		}

		vsc_migration_json_response( array( 'errors' => array() ) );
		exit;
	}

	public static function add_label( $params = array() ) {
		vsc_migration_setup_environment();

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
			vsc_migration_verify_secret_key( $secret_key );
		} catch ( VSC_Migration_Not_Valid_Secret_Key_Exception $e ) {
			exit;
		}

		try {
			VSC_Migration_Backups::set_label( $archive, $label );
		} catch ( VSC_Migration_Backups_Exception $e ) {
			vsc_migration_json_response( array( 'errors' => array( $e->getMessage() ) ) );
			exit;
		}

		vsc_migration_json_response( array( 'errors' => array() ) );
		exit;
	}

	public static function backup_list( $params = array() ) {
		vsc_migration_setup_environment();

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
			vsc_migration_verify_secret_key( $secret_key );
		} catch ( VSC_Migration_Not_Valid_Secret_Key_Exception $e ) {
			exit;
		}

		VSC_Migration_Template::render(
			'backups/backups-list',
			array(
				'backups'      => VSC_Migration_Backups::get_files(),
				'labels'       => VSC_Migration_Backups::get_labels(),
				'downloadable' => VSC_Migration_Backups::are_downloadable(),
			)
		);
		exit;
	}

	public static function backup_list_content( $params = array() ) {
		vsc_migration_setup_environment();

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
			vsc_migration_verify_secret_key( $secret_key );
		} catch ( VSC_Migration_Not_Valid_Secret_Key_Exception $e ) {
			exit;
		}

		try {
			$archive = new VSC_Migration_Extractor( vsc_migration_backup_path( $params ) );
			vsc_migration_json_response( $archive->list_files() );
		} catch ( Exception $e ) {
			vsc_migration_json_response(
				array(
					'error' => __( 'Could not list the backup content. Please ensure the backup file is accessible and not corrupted.', 'vivek-devops' ),
				)
			);
		}

		exit;
	}

	public static function download_file( $params = array() ) {
		vsc_migration_setup_environment();

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
			vsc_migration_verify_secret_key( $secret_key );
		} catch ( VSC_Migration_Not_Valid_Secret_Key_Exception $e ) {
			exit;
		}

		$chunk_size = 1024 * 1024;
		$file_bytes = 0;

		try {
			if ( $handle  = vsc_migration_open( vsc_migration_backup_path( $params ), 'rb' ) ) {
				if ( ! isset( $params['file_size'] ) ) {
					$params['file_size'] = filesize( vsc_migration_backup_path( $params ) );
				}

				if ( ! isset( $params['offset'] ) ) {
					$params['offset'] = 0;
				}

				vsc_migration_seek( $handle, $params['offset'] );
				while ( ! feof( $handle ) && $file_bytes < $params['file_size'] ) {
					$buffer = vsc_migration_read( $handle, min( $chunk_size, $params['file_size'] - $file_bytes ) );
					echo $buffer;
					ob_flush();
					flush();
					$file_bytes += strlen( $buffer );
				}

				vsc_migration_close( $handle );
			}
		} catch ( Exception $exception ) {
		}

		exit;
	}
}
