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

class VSC_Backup_Export_Database {

	public static function execute( $params ) {
		// Set exclude database
		if ( isset( $params['options']['no_database'] ) ) {
			return $params;
		}

		// Set query offset
		if ( isset( $params['query_offset'] ) ) {
			$query_offset = (int) $params['query_offset'];
		} else {
			$query_offset = 0;
		}

		// Set table index
		if ( isset( $params['table_index'] ) ) {
			$table_index = (int) $params['table_index'];
		} else {
			$table_index = 0;
		}

		// Set table offset
		if ( isset( $params['table_offset'] ) ) {
			$table_offset = (int) $params['table_offset'];
		} else {
			$table_offset = 0;
		}

		// Set table rows
		if ( isset( $params['table_rows'] ) ) {
			$table_rows = (int) $params['table_rows'];
		} else {
			$table_rows = 0;
		}

		// Set total tables count
		if ( isset( $params['total_tables_count'] ) ) {
			$total_tables_count = (int) $params['total_tables_count'];
		} else {
			$total_tables_count = 1;
		}

		// What percent of tables have we processed?
		$progress = (int) ( ( $table_index / $total_tables_count ) * 100 );

		// Set progress
		VSC_Backup_Status::info( sprintf( __( 'Exporting database...<br />%d%% complete<br />%s records saved', VSC_BACKUP_PLUGIN_NAME ), $progress, number_format_i18n( $table_rows ) ) );

		// Get tables list file
		$tables_list = vsc_backup_open( vsc_backup_tables_list_path( $params ), 'r' );

		// Loop over tables
		$tables = array();
		while ( list( $table_name ) = fgetcsv( $tables_list ) ) {
			$tables[] = $table_name;
		}

		// Close the tables list file
		vsc_backup_close( $tables_list );

		// Get database client
		$mysql = VSC_Backup_Database_Utility::create_client();

		// Exclude spam comments
		if ( isset( $params['options']['no_spam_comments'] ) ) {
			$mysql->set_table_where_query( vsc_backup_table_prefix() . 'comments', "`comment_approved` != 'spam'" )
				->set_table_where_query( vsc_backup_table_prefix() . 'commentmeta', sprintf( "`comment_ID` IN ( SELECT `comment_ID` FROM `%s` WHERE `comment_approved` != 'spam' )", vsc_backup_table_prefix() . 'comments' ) );
		}

		// Exclude post revisions
		if ( isset( $params['options']['no_post_revisions'] ) ) {
			$mysql->set_table_where_query( vsc_backup_table_prefix() . 'posts', "`post_type` != 'revision'" );
		}

		$old_table_prefixes = $old_column_prefixes = array();
		$new_table_prefixes = $new_column_prefixes = array();

		// Set table prefixes
		if ( vsc_backup_table_prefix() ) {
			$old_table_prefixes[] = vsc_backup_table_prefix();
			$new_table_prefixes[] = vsc_backup_servmask_prefix();
		} else {
			foreach ( $tables as $table_name ) {
				$old_table_prefixes[] = $table_name;
				$new_table_prefixes[] = vsc_backup_servmask_prefix() . $table_name;
			}
		}

		// Set column prefixes
		if ( strlen( vsc_backup_table_prefix() ) > 1 ) {
			$old_column_prefixes[] = vsc_backup_table_prefix();
			$new_column_prefixes[] = vsc_backup_servmask_prefix();
		} else {
			foreach ( array( 'user_roles', 'capabilities', 'user_level', 'dashboard_quick_press_last_post_id', 'user-settings', 'user-settings-time' ) as $column_prefix ) {
				$old_column_prefixes[] = vsc_backup_table_prefix() . $column_prefix;
				$new_column_prefixes[] = vsc_backup_servmask_prefix() . $column_prefix;
			}
		}

		$mysql->set_tables( $tables )
			->set_old_table_prefixes( $old_table_prefixes )
			->set_new_table_prefixes( $new_table_prefixes )
			->set_old_column_prefixes( $old_column_prefixes )
			->set_new_column_prefixes( $new_column_prefixes );

		// Exclude column prefixes
		$mysql->set_reserved_column_prefixes( array( 'wp_force_deactivated_plugins', 'wp_page_for_privacy_policy' ) );

		// Exclude site options
		$mysql->set_table_where_query( vsc_backup_table_prefix() . 'options', sprintf( "`option_name` NOT IN ('%s', '%s', '%s', '%s', '%s', '%s')", VSC_BACKUP_STATUS, VSC_BACKUP_SECRET_KEY, VSC_BACKUP_AUTH_USER, VSC_BACKUP_AUTH_PASSWORD, VSC_BACKUP_BACKUPS_LABELS, VSC_BACKUP_SITES_LINKS ) );

		// Set table select columns
		if ( ( $column_names = $mysql->get_column_names( vsc_backup_table_prefix() . 'options' ) ) ) {
			if ( isset( $column_names['option_name'], $column_names['option_value'] ) ) {
				$column_names['option_value'] = sprintf( "(CASE WHEN option_name = '%s' THEN 'a:0:{}' WHEN (option_name = '%s' OR option_name = '%s') THEN '' ELSE option_value END) AS option_value", VSC_BACKUP_ACTIVE_PLUGINS, VSC_BACKUP_ACTIVE_TEMPLATE, VSC_BACKUP_ACTIVE_STYLESHEET );
			}

			$mysql->set_table_select_columns( vsc_backup_table_prefix() . 'options', $column_names );
		}

		// Set table prefix columns
		$mysql->set_table_prefix_columns( vsc_backup_table_prefix() . 'options', array( 'option_name' ) )
			->set_table_prefix_columns( vsc_backup_table_prefix() . 'usermeta', array( 'meta_key' ) );

		// Export database
		if ( $mysql->export( vsc_backup_database_path( $params ), $query_offset, $table_index, $table_offset, $table_rows ) ) {

			// Set progress
			VSC_Backup_Status::info( __( 'Done exporting database.', VSC_BACKUP_PLUGIN_NAME ) );

			// Unset query offset
			unset( $params['query_offset'] );

			// Unset table index
			unset( $params['table_index'] );

			// Unset table offset
			unset( $params['table_offset'] );

			// Unset table rows
			unset( $params['table_rows'] );

			// Unset total tables count
			unset( $params['total_tables_count'] );

			// Unset completed flag
			unset( $params['completed'] );

		} else {

			// What percent of tables have we processed?
			$progress = (int) ( ( $table_index / $total_tables_count ) * 100 );

			// Set progress
			VSC_Backup_Status::info( sprintf( __( 'Exporting database...<br />%d%% complete<br />%s records saved', VSC_BACKUP_PLUGIN_NAME ), $progress, number_format_i18n( $table_rows ) ) );

			// Set query offset
			$params['query_offset'] = $query_offset;

			// Set table index
			$params['table_index'] = $table_index;

			// Set table offset
			$params['table_offset'] = $table_offset;

			// Set table rows
			$params['table_rows'] = $table_rows;

			// Set total tables count
			$params['total_tables_count'] = $total_tables_count;

			// Set completed flag
			$params['completed'] = false;
		}

		return $params;
	}
}
