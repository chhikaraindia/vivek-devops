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

class VSC_Backup_Backups {

	/**
	 * Get all backup files
	 *
	 * @return array
	 */
	public static function get_files() {
		$backups = array();

		try {

			// Iterate over directory
			$iterator = new VSC_Backup_Recursive_Directory_Iterator( VSC_BACKUP_BACKUPS_PATH );

			// Filter by extensions
			$iterator = new VSC_Backup_Recursive_Extension_Filter( $iterator, array( 'wpress' ) );

			// Recursively iterate over directory
			$iterator = new VSC_Backup_Recursive_Iterator_Iterator( $iterator, RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD );

			// Get backup files
			foreach ( $iterator as $item ) {
				try {
					if ( vsc_backup_is_filesize_supported( $item->getPathname() ) ) {
						$backups[] = array(
							'path'     => $iterator->getSubPath(),
							'filename' => $iterator->getSubPathname(),
							'mtime'    => $iterator->getMTime(),
							'size'     => $iterator->getSize(),
						);
					} else {
						$backups[] = array(
							'path'     => $iterator->getSubPath(),
							'filename' => $iterator->getSubPathname(),
							'mtime'    => $iterator->getMTime(),
							'size'     => null,
						);
					}
				} catch ( Exception $e ) {
					$backups[] = array(
						'path'     => $iterator->getSubPath(),
						'filename' => $iterator->getSubPathname(),
						'mtime'    => null,
						'size'     => null,
					);
				}
			}

			// Sort backups modified date
			usort( $backups, 'VSC_Backup_Backups::compare' );

		} catch ( Exception $e ) {
		}

		return $backups;
	}

	/**
	 * Count all backup files
	 *
	 * @return integer
	 */
	public static function count_files() {
		return count( VSC_Backup_Backups::get_files() );
	}

	/**
	 * Delete backup file
	 *
	 * @param  string  $file File name
	 * @return boolean
	 */
	public static function delete_file( $file ) {
		if ( vsc_backup_is_filename_supported( $file ) ) {
			return @unlink( vsc_backup_backup_path( array( 'archive' => $file ) ) );
		}
	}

	/**
	 * Get all backup labels
	 *
	 * @return array
	 */
	public static function get_labels() {
		return get_option( VSC_BACKUP_BACKUPS_LABELS, array() );
	}

	/**
	 * Set backup label
	 *
	 * @param  string  $file  File name
	 * @param  string  $label File label
	 * @return boolean
	 */
	public static function set_label( $file, $label ) {
		if ( ( $labels = get_option( VSC_BACKUP_BACKUPS_LABELS, array() ) ) !== false ) {
			$labels[ $file ] = $label;
		}

		return update_option( VSC_BACKUP_BACKUPS_LABELS, $labels );
	}

	/**
	 * Delete backup label
	 *
	 * @param  string  $file File name
	 * @return boolean
	 */
	public static function delete_label( $file ) {
		if ( ( $labels = get_option( VSC_BACKUP_BACKUPS_LABELS, array() ) ) !== false ) {
			unset( $labels[ $file ] );
		}

		return update_option( VSC_BACKUP_BACKUPS_LABELS, $labels );
	}

	/**
	 * Compare backup files by modified time
	 *
	 * @param  array $a File item A
	 * @param  array $b File item B
	 * @return integer
	 */
	public static function compare( $a, $b ) {
		if ( $a['mtime'] === $b['mtime'] ) {
			return 0;
		}

		return ( $a['mtime'] > $b['mtime'] ) ? - 1 : 1;
	}

	/**
	 * Check if backups are downloadable
	 */
	public static function are_downloadable() {
		static $downloadable = null;
		if ( is_null( $downloadable ) ) {
			$downloadable = VSC_Backup_Backups::are_in_wp_content_folder() || strpos( VSC_BACKUP_BACKUPS_PATH, untrailingslashit( ABSPATH ) ) === 0;
		}

		return $downloadable;
	}

	public static function are_in_wp_content_folder() {
		static $in_wp_content = null;
		if ( is_null( $in_wp_content ) ) {
			$in_wp_content = strpos( VSC_BACKUP_BACKUPS_PATH, untrailingslashit( WP_CONTENT_DIR ) ) === 0;
		}

		return $in_wp_content;
	}
}
