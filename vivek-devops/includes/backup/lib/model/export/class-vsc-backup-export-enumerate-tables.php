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

class VSC_Backup_Export_Enumerate_Tables {

	public static function execute( $params, VSC_Backup_Database $mysql = null ) {
		// Set exclude database
		if ( isset( $params['options']['no_database'] ) ) {
			return $params;
		}

		// Get total tables count
		if ( isset( $params['total_tables_count'] ) ) {
			$total_tables_count = (int) $params['total_tables_count'];
		} else {
			$total_tables_count = 1;
		}

		// Set progress
		VSC_Backup_Status::info( __( 'Retrieving a list of WordPress database tables...', VSC_BACKUP_PLUGIN_NAME ) );

		// Get database client
		if ( is_null( $mysql ) ) {
			$mysql = VSC_Backup_Database_Utility::create_client();
		}

		// Include table prefixes
		if ( vsc_backup_table_prefix() ) {
			$mysql->add_table_prefix_filter( vsc_backup_table_prefix() );

			// Include table prefixes (Webba Booking)
			foreach ( array( 'wbk_services', 'wbk_days_on_off', 'wbk_locked_time_slots', 'wbk_appointments', 'wbk_cancelled_appointments', 'wbk_email_templates', 'wbk_service_categories', 'wbk_gg_calendars', 'wbk_coupons' ) as $table_name ) {
				$mysql->add_table_prefix_filter( $table_name );
			}
		}

		// Create tables list file
		$tables_list = vsc_backup_open( vsc_backup_tables_list_path( $params ), 'w' );

		// Exclude selected db tables
		$excluded_db_tables = array();
		if ( isset( $params['options']['exclude_db_tables'], $params['excluded_db_tables'] ) ) {
			$excluded_db_tables = explode( ',', $params['excluded_db_tables'] );
		}

		// Write table line
		foreach ( $mysql->get_tables() as $table_name ) {
			if ( ! in_array( $table_name, $excluded_db_tables ) && vsc_backup_putcsv( $tables_list, array( $table_name ) ) ) {
				$total_tables_count++;
			}
		}

		// Set progress
		VSC_Backup_Status::info( __( 'Done retrieving a list of WordPress database tables.', VSC_BACKUP_PLUGIN_NAME ) );

		// Set total tables count
		$params['total_tables_count'] = $total_tables_count;

		// Close the tables list file
		vsc_backup_close( $tables_list );

		return $params;
	}
}
