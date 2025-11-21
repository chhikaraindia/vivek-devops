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

class VSC_Migration_Import_Mu_Plugins {

	public static function execute( $params ) {

		// Set progress
		VSC_Migration_Status::info( __( 'Activating mu-plugins...', 'vivek-devops' ) );

		$exclude_files = array(
			VSC_MIGRATION_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_MIGRATION_ENDURANCE_PAGE_CACHE_NAME,
			VSC_MIGRATION_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_MIGRATION_ENDURANCE_PHP_EDGE_NAME,
			VSC_MIGRATION_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_MIGRATION_ENDURANCE_BROWSER_CACHE_NAME,
			VSC_MIGRATION_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_MIGRATION_GD_SYSTEM_PLUGIN_NAME,
			VSC_MIGRATION_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_MIGRATION_WP_STACK_CACHE_NAME,
			VSC_MIGRATION_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_MIGRATION_WP_COMSH_LOADER_NAME,
			VSC_MIGRATION_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_MIGRATION_WP_COMSH_HELPER_NAME,
			VSC_MIGRATION_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_MIGRATION_WP_ENGINE_SYSTEM_PLUGIN_NAME,
			VSC_MIGRATION_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_MIGRATION_WPE_SIGN_ON_PLUGIN_NAME,
			VSC_MIGRATION_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_MIGRATION_WP_ENGINE_SECURITY_AUDITOR_NAME,
			VSC_MIGRATION_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_MIGRATION_WP_CERBER_SECURITY_NAME,
			VSC_MIGRATION_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_MIGRATION_SQLITE_DATABASE_INTEGRATION_NAME,
			VSC_MIGRATION_MUPLUGINS_NAME . DIRECTORY_SEPARATOR . VSC_MIGRATION_SQLITE_DATABASE_ZERO_NAME,
		);

		// Open the archive file for reading
		$archive = new VSC_Migration_Extractor( vsc_migration_archive_path( $params ) );

		// Unpack mu-plugins files
		$archive->extract_by_files_array( WP_CONTENT_DIR, array( VSC_MIGRATION_MUPLUGINS_NAME ), $exclude_files );

		// Close the archive file
		$archive->close();

		// Set progress
		VSC_Migration_Status::info( __( 'Mu-plugins activated.', 'vivek-devops' ) );

		return $params;
	}
}
