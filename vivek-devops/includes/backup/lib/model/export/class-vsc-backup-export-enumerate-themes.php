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

class VSC_Backup_Export_Enumerate_Themes {

	public static function execute( $params ) {

		$exclude_filters = array();

		// Get total themes files count
		if ( isset( $params['total_themes_files_count'] ) ) {
			$total_themes_files_count = (int) $params['total_themes_files_count'];
		} else {
			$total_themes_files_count = 1;
		}

		// Get total themes files size
		if ( isset( $params['total_themes_files_size'] ) ) {
			$total_themes_files_size = (int) $params['total_themes_files_size'];
		} else {
			$total_themes_files_size = 1;
		}

		// Set progress
		VSC_Backup_Status::info( __( 'Retrieving a list of WordPress theme files...', VSC_BACKUP_PLUGIN_NAME ) );

		// Exclude inactive themes
		if ( isset( $params['options']['no_inactive_themes'] ) ) {
			foreach ( search_theme_directories() as $theme_name => $theme_info ) {
				if ( ! in_array( $theme_name, array( get_template(), get_stylesheet() ) ) ) {
					if ( isset( $theme_info['theme_root'] ) ) {
						$exclude_filters[] = $theme_info['theme_root'] . DIRECTORY_SEPARATOR . $theme_name;
					}
				}
			}
		}

		// Exclude selected files
		if ( isset( $params['options']['exclude_files'], $params['excluded_files'] ) ) {
			if ( ( $excluded_files = explode( ',', $params['excluded_files'] ) ) ) {
				foreach ( $excluded_files as $excluded_path ) {
					$exclude_filters[] = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . untrailingslashit( $excluded_path );
				}
			}
		}

		// Create themes list file
		$themes_list = vsc_backup_open( vsc_backup_themes_list_path( $params ), 'w' );

		// Enumerate over themes directory
		if ( isset( $params['options']['no_themes'] ) === false ) {
			foreach ( vsc_backup_get_themes_dirs() as $theme_dir ) {
				if ( is_dir( $theme_dir ) ) {

					// Iterate over themes directory
					$iterator = new VSC_Backup_Recursive_Directory_Iterator( $theme_dir );

					// Exclude themes files
					$iterator = new VSC_Backup_Recursive_Exclude_Filter( $iterator, apply_filters( 'vsc_backup_exclude_themes_from_export', vsc_backup_theme_filters( $exclude_filters ) ) );

					// Recursively iterate over themes directory
					$iterator = new VSC_Backup_Recursive_Iterator_Iterator( $iterator, RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD );

					// Write path line
					foreach ( $iterator as $item ) {
						if ( $item->isFile() ) {
							if ( vsc_backup_putcsv( $themes_list, array( $iterator->getPathname(), $iterator->getSubPathname(), $iterator->getSize(), $iterator->getMTime() ) ) ) {
								$total_themes_files_count++;

								// Add current file size
								$total_themes_files_size += $iterator->getSize();
							}
						}
					}
				}
			}
		}

		// Set progress
		VSC_Backup_Status::info( __( 'Done retrieving a list of WordPress theme files.', VSC_BACKUP_PLUGIN_NAME ) );

		// Set total themes files count
		$params['total_themes_files_count'] = $total_themes_files_count;

		// Set total themes files size
		$params['total_themes_files_size'] = $total_themes_files_size;

		// Close the themes list file
		vsc_backup_close( $themes_list );

		return $params;
	}
}
