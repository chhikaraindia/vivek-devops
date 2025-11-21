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

class VSC_Migration_Import_Check_Decryption_Password {

	public static function execute( $params ) {
		global $vsc_migration_params;

		// Read package.json file
		$handle = vsc_migration_open( vsc_migration_package_path( $params ), 'r' );

		// Parse package.json file
		$package = vsc_migration_read( $handle, filesize( vsc_migration_package_path( $params ) ) );
		$package = json_decode( $package, true );

		// Close handle
		vsc_migration_close( $handle );

		if ( ! empty( $params['decryption_password'] ) ) {
			if ( vsc_migration_is_decryption_password_valid( $package['EncryptedSignature'], $params['decryption_password'] ) ) {
				$params['is_decryption_password_valid'] = true;

				$archive = new VSC_Migration_Extractor( vsc_migration_archive_path( $params ), $params['decryption_password'] );
				$archive->extract_by_files_array( vsc_migration_storage_path( $params ), array( VSC_MIGRATION_MULTISITE_NAME, VSC_MIGRATION_DATABASE_NAME ), array(), array() );

				VSC_Migration_Status::info( __( 'Decryption password validated.', 'vivek-devops' ) );

				$vsc_migration_params = $params;

				return $params;
			}

			$decryption_password_error = __( 'The decryption password is not valid. The process cannot continue.', 'vivek-devops' );

			if ( defined( 'WP_CLI' ) ) {
				WP_CLI::error( $decryption_password_error );
			} else {
				VSC_Migration_Status::backup_is_encrypted( $decryption_password_error );
				exit;
			}
		}

		return $params;
	}
}
