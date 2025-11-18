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

class VSC_Backup_Import_Check_Decryption_Password {

	public static function execute( $params ) {
		global $vsc_backup_params;

		// Read package.json file
		$handle = vsc_backup_open( vsc_backup_package_path( $params ), 'r' );

		// Parse package.json file
		$package = vsc_backup_read( $handle, filesize( vsc_backup_package_path( $params ) ) );
		$package = json_decode( $package, true );

		// Close handle
		vsc_backup_close( $handle );

		if ( ! empty( $params['decryption_password'] ) ) {
			if ( vsc_backup_is_decryption_password_valid( $package['EncryptedSignature'], $params['decryption_password'] ) ) {
				$params['is_decryption_password_valid'] = true;

				$archive = new VSC_Backup_Extractor( vsc_backup_archive_path( $params ), $params['decryption_password'] );
				$archive->extract_by_files_array( vsc_backup_storage_path( $params ), array( VSC_BACKUP_MULTISITE_NAME, VSC_BACKUP_DATABASE_NAME ), array(), array() );

				VSC_Backup_Status::info( __( 'Done validating the decryption password.', VSC_BACKUP_PLUGIN_NAME ) );

				$vsc_backup_params = $params;

				return $params;
			}

			$decryption_password_error = __( 'The decryption password is not valid.', VSC_BACKUP_PLUGIN_NAME );

			if ( defined( 'WP_CLI' ) ) {
				WP_CLI::error( $decryption_password_error );
			} else {
				VSC_Backup_Status::backup_is_encrypted( $decryption_password_error );
				exit;
			}
		}

		return $params;
	}
}
