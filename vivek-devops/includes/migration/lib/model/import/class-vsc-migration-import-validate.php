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

class VSC_Migration_Import_Validate {

	public static function execute( $params ) {

		// Verify file if size > 2GB and PHP = 32-bit
		if ( ! vsc_migration_is_filesize_supported( vsc_migration_archive_path( $params ) ) ) {
			throw new VSC_Migration_Import_Exception(
				wp_kses(
					__(
						'Your server uses 32-bit PHP and cannot process files larger than 2GB. Please switch to 64-bit PHP and try again.
						<a href="https://help.servmask.com/knowledgebase/php-32bit/" target="_blank">Technical details</a>',
						'vivek-devops'
					),
					vsc_migration_allowed_html_tags()
				)
			);
		}

		// Verify file name extension
		if ( ! vsc_migration_is_filename_supported( vsc_migration_archive_path( $params ) ) ) {
			throw new VSC_Migration_Import_Exception(
				wp_kses(
					__(
						'Invalid file type. Please ensure your file is a <strong>.wpress</strong> backup created with All-in-One WP Migration.
						<a href="https://help.servmask.com/knowledgebase/invalid-backup-file/" target="_blank">Technical details</a>',
						'vivek-devops'
					),
					vsc_migration_allowed_html_tags()
				)
			);
		}

		// Set archive bytes offset
		if ( isset( $params['archive_bytes_offset'] ) ) {
			$archive_bytes_offset = (int) $params['archive_bytes_offset'];
		} else {
			$archive_bytes_offset = 0;
		}

		// Set file bytes offset
		if ( isset( $params['file_bytes_offset'] ) ) {
			$file_bytes_offset = (int) $params['file_bytes_offset'];
		} else {
			$file_bytes_offset = 0;
		}

		// Get total archive size
		if ( isset( $params['total_archive_size'] ) ) {
			$total_archive_size = (int) $params['total_archive_size'];
		} else {
			$total_archive_size = vsc_migration_archive_bytes( $params );
		}

		// What percent of archive have we processed?
		$progress = (int) min( ( $archive_bytes_offset / $total_archive_size ) * 100, 100 );

		// Set progress
		/* translators: Progress. */
		VSC_Migration_Status::info( sprintf( __( 'Unpacking archive...<br />%d%% complete', 'vivek-devops' ), $progress ) );

		// Open the archive file for reading
		$archive = new VSC_Migration_Extractor( vsc_migration_archive_path( $params ) );

		// Set the file pointer to the one that we have saved
		$archive->set_file_pointer( $archive_bytes_offset );

		// Validate the archive file consistency
		if ( ! $archive->is_valid() ) {
			throw new VSC_Migration_Import_Exception(
				wp_kses(
					__( 'The archive file appears to be corrupted. Follow <a href="https://help.servmask.com/knowledgebase/corrupted-archive/" target="_blank">this article</a> for possible fixes.', 'vivek-devops' ),
					vsc_migration_allowed_html_tags()
				)
			);
		}

		// Flag to hold if file data has been processed
		$completed = true;

		if ( $archive->has_not_reached_eof() ) {
			$file_bytes_written = 0;

			// Unpack package.json, multisite.json and database.sql files
			if ( ( $completed = $archive->extract_by_files_array( vsc_migration_storage_path( $params ), array( VSC_MIGRATION_PACKAGE_NAME, VSC_MIGRATION_MULTISITE_NAME, VSC_MIGRATION_DATABASE_NAME ), array(), array(), $file_bytes_written, $file_bytes_offset ) ) ) {
				$file_bytes_offset = 0;
			}

			// Get archive bytes offset
			$archive_bytes_offset = $archive->get_file_pointer();
		}

		// End of the archive?
		if ( $archive->has_reached_eof() ) {

			// Check package.json file
			if ( false === is_file( vsc_migration_package_path( $params ) ) ) {
				throw new VSC_Migration_Import_Exception(
					wp_kses(
						__(
							'Please ensure your file was created with the All-in-One WP Migration plugin.
							<a href="https://help.servmask.com/knowledgebase/invalid-backup-file/" target="_blank">Technical details</a>',
							'vivek-devops'
						),
						vsc_migration_allowed_html_tags()
					)
				);
			}

			// Set progress
			VSC_Migration_Status::info( __( 'Archive unpacked.', 'vivek-devops' ) );

			// Unset archive bytes offset
			unset( $params['archive_bytes_offset'] );

			// Unset file bytes offset
			unset( $params['file_bytes_offset'] );

			// Unset total archive size
			unset( $params['total_archive_size'] );

			// Unset completed flag
			unset( $params['completed'] );

		} else {

			// What percent of archive have we processed?
			$progress = (int) min( ( $archive_bytes_offset / $total_archive_size ) * 100, 100 );

			// Set progress
			/* translators: Progress. */
			VSC_Migration_Status::info( sprintf( __( 'Unpacking archive...<br />%d%% complete', 'vivek-devops' ), $progress ) );

			// Set archive bytes offset
			$params['archive_bytes_offset'] = $archive_bytes_offset;

			// Set file bytes offset
			$params['file_bytes_offset'] = $file_bytes_offset;

			// Set total archive size
			$params['total_archive_size'] = $total_archive_size;

			// Set completed flag
			$params['completed'] = $completed;
		}

		// Close the archive file
		$archive->close();

		return $params;
	}
}
