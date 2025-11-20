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

class VSC_Backup_Import_Upload {

	private static function validate() {
		if ( ! array_key_exists( 'upload-file', $_FILES ) || ! is_array( $_FILES['upload-file'] ) ) {
			throw new VSC_Backup_Import_Retry_Exception( __( 'Missing upload file.', VSC_BACKUP_PLUGIN_NAME ), 400 );
		}

		if ( ! array_key_exists( 'error', $_FILES['upload-file'] ) ) {
			throw new VSC_Backup_Import_Retry_Exception( __( 'Missing error key in upload file.', VSC_BACKUP_PLUGIN_NAME ), 400 );
		}

		if ( ! array_key_exists( 'tmp_name', $_FILES['upload-file'] ) ) {
			throw new VSC_Backup_Import_Retry_Exception( __( 'Missing tmp_name in upload file.', VSC_BACKUP_PLUGIN_NAME ), 400 );
		}
	}

	public static function execute( $params ) {
		self::validate();

		$error  = $_FILES['upload-file']['error'];
		$upload = $_FILES['upload-file']['tmp_name'];

		// Verify file name extension
		if ( ! vsc_backup_is_filename_supported( vsc_backup_archive_path( $params ) ) ) {
			throw new VSC_Backup_Import_Exception(
				__(
					'The file type that you have tried to upload is not compatible with this plugin. ' .
					'Please ensure that your file is a <strong>.wpress</strong> file that was created with the All-in-One WP migration plugin. ' .
					'<a href="https://help.vivekchhikara.com/knowledgebase/invalid-backup-file/" target="_blank">Technical details</a>',
					VSC_BACKUP_PLUGIN_NAME
				)
			);
		}

		switch ( $error ) {
			case UPLOAD_ERR_OK:
				try {
					vsc_backup_copy( $upload, vsc_backup_archive_path( $params ) );
					vsc_backup_unlink( $upload );
				} catch ( Exception $e ) {
					throw new VSC_Backup_Import_Retry_Exception( sprintf( __( 'Unable to upload the file because %s', VSC_BACKUP_PLUGIN_NAME ), $e->getMessage() ), 400 );
				}
				break;

			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
			case UPLOAD_ERR_PARTIAL:
			case UPLOAD_ERR_NO_FILE:
				// File is too large
				throw new VSC_Backup_Import_Retry_Exception( __( 'The file is too large for this server.', VSC_BACKUP_PLUGIN_NAME ), 413 );

			case UPLOAD_ERR_NO_TMP_DIR:
				throw new VSC_Backup_Import_Retry_Exception( __( 'Missing a temporary folder.', VSC_BACKUP_PLUGIN_NAME ), 400 );

			case UPLOAD_ERR_CANT_WRITE:
				throw new VSC_Backup_Import_Retry_Exception( __( 'Failed to write file to disk.', VSC_BACKUP_PLUGIN_NAME ), 400 );

			case UPLOAD_ERR_EXTENSION:
				throw new VSC_Backup_Import_Retry_Exception( __( 'A PHP extension stopped the file upload.', VSC_BACKUP_PLUGIN_NAME ), 400 );

			default:
				throw new VSC_Backup_Import_Retry_Exception( sprintf( __( 'Unrecognized error %s during upload.', VSC_BACKUP_PLUGIN_NAME ), $error ), 400 );
		}

		vsc_backup_json_response( array( 'errors' => array() ) );
		exit;
	}
}
