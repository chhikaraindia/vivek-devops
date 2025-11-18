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

class VSC_Backup_Export_Enumerate_Media {

	public static function execute( $params ) {

		$exclude_filters = array();

		// Get total media files count
		if ( isset( $params['total_media_files_count'] ) ) {
			$total_media_files_count = (int) $params['total_media_files_count'];
		} else {
			$total_media_files_count = 1;
		}

		// Get total media files size
		if ( isset( $params['total_media_files_size'] ) ) {
			$total_media_files_size = (int) $params['total_media_files_size'];
		} else {
			$total_media_files_size = 1;
		}

		// Set progress
		VSC_Backup_Status::info( __( 'Retrieving a list of WordPress media files...', VSC_BACKUP_PLUGIN_NAME ) );

		// Exclude selected files
		if ( isset( $params['options']['exclude_files'], $params['excluded_files'] ) ) {
			if ( ( $excluded_files = explode( ',', $params['excluded_files'] ) ) ) {
				foreach ( $excluded_files as $excluded_path ) {
					$exclude_filters[] = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . untrailingslashit( $excluded_path );
				}
			}
		}

		// Create media list file
		$media_list = vsc_backup_open( vsc_backup_media_list_path( $params ), 'w' );

		// Enumerate over media directory
		if ( isset( $params['options']['no_media'] ) === false ) {
			if ( is_dir( vsc_backup_get_uploads_dir() ) ) {

				// Iterate over media directory
				$iterator = new VSC_Backup_Recursive_Directory_Iterator( vsc_backup_get_uploads_dir() );

				// Exclude media files
				$iterator = new VSC_Backup_Recursive_Exclude_Filter( $iterator, apply_filters( 'vsc_backup_exclude_media_from_export', vsc_backup_media_filters( $exclude_filters ) ) );

				// Recursively iterate over content directory
				$iterator = new VSC_Backup_Recursive_Iterator_Iterator( $iterator, RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD );

				// Write path line
				foreach ( $iterator as $item ) {
					if ( $item->isFile() ) {
						if ( vsc_backup_putcsv( $media_list, array( $iterator->getPathname(), $iterator->getSubPathname(), $iterator->getSize(), $iterator->getMTime() ) ) ) {
							$total_media_files_count++;

							// Add current file size
							$total_media_files_size += $iterator->getSize();
						}
					}
				}
			}
		}

		// Set progress
		VSC_Backup_Status::info( __( 'Done retrieving a list of WordPress media files.', VSC_BACKUP_PLUGIN_NAME ) );

		// Set total media files count
		$params['total_media_files_count'] = $total_media_files_count;

		// Set total media files size
		$params['total_media_files_size'] = $total_media_files_size;

		// Close the media list file
		vsc_backup_close( $media_list );

		return $params;
	}
}
