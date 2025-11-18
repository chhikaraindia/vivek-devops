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

class VSC_Backup_Import_Users {

	public static function execute( $params ) {

		// Check multisite.json file
		if ( is_file( vsc_backup_multisite_path( $params ) ) ) {

			// Set progress
			VSC_Backup_Status::info( __( 'Preparing users...', VSC_BACKUP_PLUGIN_NAME ) );

			// Read multisite.json file
			$handle = vsc_backup_open( vsc_backup_multisite_path( $params ), 'r' );

			// Parse multisite.json file
			$multisite = vsc_backup_read( $handle, filesize( vsc_backup_multisite_path( $params ) ) );
			$multisite = json_decode( $multisite, true );

			// Close handle
			vsc_backup_close( $handle );

			// Set WordPress super admins
			if ( isset( $multisite['Admins'] ) && ( $admins = $multisite['Admins'] ) ) {
				foreach ( $admins as $username ) {
					if ( ( $user = get_user_by( 'login', $username ) ) ) {
						if ( $user->exists() ) {
							$user->set_role( 'administrator' );
						}
					}
				}
			}

			// Set progress
			VSC_Backup_Status::info( __( 'Done preparing users.', VSC_BACKUP_PLUGIN_NAME ) );
		}

		return $params;
	}
}
