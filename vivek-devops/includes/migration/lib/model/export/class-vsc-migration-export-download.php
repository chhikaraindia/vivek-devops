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

class VSC_Migration_Export_Download {

	public static function execute( $params ) {

		// Set progress
		VSC_Migration_Status::info( __( 'Renaming export file...', 'vivek-devops' ) );

		// Open the archive file for writing
		$archive = new VSC_Migration_Compressor( vsc_migration_archive_path( $params ) );

		// Append EOF block
		$archive->close( true );

		// Rename archive file
		if ( rename( vsc_migration_archive_path( $params ), vsc_migration_backup_path( $params ) ) ) {

			$blog_id = null;

			// Get subsite Blog ID
			if ( isset( $params['options']['sites'] ) && ( $sites = $params['options']['sites'] ) ) {
				if ( count( $sites ) === 1 ) {
					$blog_id = array_shift( $sites );
				}
			}

			// Set archive details
			$file = vsc_migration_archive_name( $params );
			$link = vsc_migration_backup_url( $params );
			$size = vsc_migration_backup_size( $params );
			$name = vsc_migration_site_name( $blog_id );

			// Set progress
			if ( vsc_migration_direct_download_supported() ) {
				VSC_Migration_Status::download(
					sprintf(
						/* translators: 1: Link to archive, 2: Archive title, 3: File name, 4: Archive title, 5: File size. */
						__(
							'<a href="%1$s" class="vsc-migration-button-green vsc-migration-emphasize vsc-migration-button-download" title="%2$s" download="%3$s">
							<span>Download %2$s</span>
							<em>Size: %4$s</em>
							</a>',
							'vivek-devops'
						),
						$link,
						$name,
						$file,
						$size
					)
				);
			} else {
				VSC_Migration_Status::download(
					sprintf(
						/* translators: 1: Archive title, 2: File name, 3: Archive title, 4: File size. */
						__(
							'<a href="#" class="vsc-migration-button-green vsc-migration-emphasize vsc-migration-direct-download" title="%1$s" download="%2$s">
							<span>Download %3$s</span>
							<em>Size: %4$s</em>
							</a>',
							'vivek-devops'
						),
						$name,
						$file,
						$name,
						$size
					)
				);
			}
		}

		do_action( 'vsc_migration_status_export_done', $params );

		if ( isset( $params['vsc_migration_manual_backup'] ) ) {
			do_action( 'vsc_migration_status_backup_created', $params );
		}

		return $params;
	}
}
