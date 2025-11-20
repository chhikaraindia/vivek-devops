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

class VSC_Backup_Import_Mu_Plugins {

	public static function execute( $params ) {

		// Set progress
		VSC_Backup_Status::info( __( 'Activating mu-plugins...', VSC_BACKUP_PLUGIN_NAME ) );

		$exclude_files = array(
			VSC_BACKUP_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_BACKUP_ENDURANCE_PAGE_CACHE_NAME,
			VSC_BACKUP_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_BACKUP_ENDURANCE_PHP_EDGE_NAME,
			VSC_BACKUP_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_BACKUP_ENDURANCE_BROWSER_CACHE_NAME,
			VSC_BACKUP_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_BACKUP_GD_SYSTEM_PLUGIN_NAME,
			VSC_BACKUP_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_BACKUP_WP_STACK_CACHE_NAME,
			VSC_BACKUP_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_BACKUP_WP_COMSH_LOADER_NAME,
			VSC_BACKUP_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_BACKUP_WP_COMSH_HELPER_NAME,
			VSC_BACKUP_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_BACKUP_WP_ENGINE_SYSTEM_PLUGIN_NAME,
			VSC_BACKUP_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_BACKUP_WPE_SIGN_ON_PLUGIN_NAME,
			VSC_BACKUP_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_BACKUP_WP_ENGINE_SECURITY_AUDITOR_NAME,
			VSC_BACKUP_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_BACKUP_WP_CERBER_SECURITY_NAME,
		);

		// Open the archive file for reading
		$archive = new VSC_Backup_Extractor( vsc_backup_archive_path( $params ) );

		// Unpack mu-plugins files
		$archive->extract_by_files_array( WP_CONTENT_DIR, array( VSC_BACKUP_MUPLUGINS_NAME ), $exclude_files );

		// Close the archive file
		$archive->close();

		// Set progress
		VSC_Backup_Status::info( __( 'Done activating mu-plugins.', VSC_BACKUP_PLUGIN_NAME ) );

		return $params;
	}
}
