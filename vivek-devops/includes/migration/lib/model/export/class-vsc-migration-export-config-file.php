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

class VSC_Migration_Export_Config_File {

	public static function execute( $params ) {
		$package_bytes_written = 0;

		// Set archive bytes offset
		if ( isset( $params['archive_bytes_offset'] ) ) {
			$archive_bytes_offset = (int) $params['archive_bytes_offset'];
		} else {
			$archive_bytes_offset = vsc_migration_archive_bytes( $params );
		}

		// Set package bytes offset
		if ( isset( $params['package_bytes_offset'] ) ) {
			$package_bytes_offset = (int) $params['package_bytes_offset'];
		} else {
			$package_bytes_offset = 0;
		}

		// Get total package size
		if ( isset( $params['total_package_size'] ) ) {
			$total_package_size = (int) $params['total_package_size'];
		} else {
			$total_package_size = vsc_migration_package_bytes( $params );
		}

		// What percent of package have we processed?
		$progress = (int) min( ( $package_bytes_offset / $total_package_size ) * 100, 100 );

		// Set progress
		/* translators: Progress. */
		VSC_Migration_Status::info( sprintf( __( 'Archiving configuration...<br />%d%% complete', 'vivek-devops' ), $progress ) );

		// Open the archive file for writing
		$archive = new VSC_Migration_Compressor( vsc_migration_archive_path( $params ) );

		// Set the file pointer to the one that we have saved
		$archive->set_file_pointer( $archive_bytes_offset );

		// Add package.json to archive
		if ( $archive->add_file( vsc_migration_package_path( $params ), VSC_MIGRATION_PACKAGE_NAME, $package_bytes_written, $package_bytes_offset ) ) {

			// Set progress
			VSC_Migration_Status::info( __( 'Configuration archived.', 'vivek-devops' ) );

			// Unset archive bytes offset
			unset( $params['archive_bytes_offset'] );

			// Unset package bytes offset
			unset( $params['package_bytes_offset'] );

			// Unset total package size
			unset( $params['total_package_size'] );

			// Unset completed flag
			unset( $params['completed'] );

		} else {

			// Get archive bytes offset
			$archive_bytes_offset = $archive->get_file_pointer();

			// What percent of package have we processed?
			$progress = (int) min( ( $package_bytes_offset / $total_package_size ) * 100, 100 );

			// Set progress
			/* translators: Progress. */
			VSC_Migration_Status::info( sprintf( __( 'Archiving configuration...<br />%d%% complete', 'vivek-devops' ), $progress ) );

			// Set archive bytes offset
			$params['archive_bytes_offset'] = $archive_bytes_offset;

			// Set package bytes offset
			$params['package_bytes_offset'] = $package_bytes_offset;

			// Set total package size
			$params['total_package_size'] = $total_package_size;

			// Set completed flag
			$params['completed'] = false;
		}

		// Truncate the archive file
		$archive->truncate();

		// Close the archive file
		$archive->close();

		return $params;
	}
}
