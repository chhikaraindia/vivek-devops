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

class VSC_Migration_Import_Users {

	public static function execute( $params ) {

		// Check multisite.json file
		if ( is_file( vsc_migration_multisite_path( $params ) ) ) {

			// Set progress
			VSC_Migration_Status::info( __( 'Preparing WordPress users...', 'vivek-devops' ) );

			// Read multisite.json file
			$handle = vsc_migration_open( vsc_migration_multisite_path( $params ), 'r' );

			// Parse multisite.json file
			$multisite = vsc_migration_read( $handle, filesize( vsc_migration_multisite_path( $params ) ) );
			$multisite = json_decode( $multisite, true );

			// Close handle
			vsc_migration_close( $handle );

			vsc_migration_populate_roles();

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
			VSC_Migration_Status::info( __( 'WordPress users prepared.', 'vivek-devops' ) );
		}

		return $params;
	}
}
