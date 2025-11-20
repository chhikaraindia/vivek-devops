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
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Unauthorized access' );
}

if ( defined( 'WP_CLI' ) ) {
	class VSC_Backup_WP_CLI_Command extends WP_CLI_Command {
		public function __invoke() {
			if ( is_multisite() ) {
				WP_CLI::error_multi_line(
					array(
						__( 'WordPress Multisite is supported via our All-in-One WP Migration Multisite Extension.', VSC_BACKUP_PLUGIN_NAME ),
						__( 'You can get a copy of it here: https://vivekchhikara.com/products/multisite-extension', VSC_BACKUP_PLUGIN_NAME ),
					)
				);
				exit;
			}

			WP_CLI::error_multi_line(
				array(
					__( 'WordPress CLI is supported via our All-in-One WP Migration Unlimited Extension.', VSC_BACKUP_PLUGIN_NAME ),
					__( 'You can get a copy of it here: https://vivekchhikara.com/products/unlimited-extension', VSC_BACKUP_PLUGIN_NAME ),
				)
			);
			exit;
		}
	}
}
