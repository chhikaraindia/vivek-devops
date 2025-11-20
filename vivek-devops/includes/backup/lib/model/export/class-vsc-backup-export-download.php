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

class VSC_Backup_Export_Download {

	public static function execute( $params ) {

		// Set progress
		VSC_Backup_Status::info( __( 'Renaming exported file...', VSC_BACKUP_PLUGIN_NAME ) );

		// Open the archive file for writing
		$archive = new VSC_Backup_Compressor( vsc_backup_archive_path( $params ) );

		// Append EOF block
		$archive->close( true );

		// Rename archive file
		if ( rename( vsc_backup_archive_path( $params ), vsc_backup_backup_path( $params ) ) ) {

			$blog_id = null;

			// Get subsite Blog ID
			if ( isset( $params['options']['sites'] ) && ( $sites = $params['options']['sites'] ) ) {
				if ( count( $sites ) === 1 ) {
					$blog_id = array_shift( $sites );
				}
			}

			// Set archive details
			$file = vsc_backup_archive_name( $params );
			$link = vsc_backup_backup_url( $params );
			$size = vsc_backup_backup_size( $params );
			$name = vsc_backup_site_name( $blog_id );

			// Set progress
			VSC_Backup_Status::download(
				sprintf(
					__(
						'<a href="%s" class="ai1wm-button-green ai1wm-emphasize ai1wm-button-download" title="%s" download="%s">' .
						'<span>Download %s</span>' .
						'<em>Size: %s</em>' .
						'</a>',
						VSC_BACKUP_PLUGIN_NAME
					),
					$link,
					$name,
					$file,
					$name,
					$size
				)
			);
		}

		do_action( 'vsc_backup_status_export_done', $params );

		return $params;
	}
}
