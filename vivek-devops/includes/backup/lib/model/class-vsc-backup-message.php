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

class VSC_Backup_Message {

	public static function flash( $type, $message ) {
		if ( ( $messages = get_option( VSC_BACKUP_MESSAGES, array() ) ) !== false ) {
			return update_option( VSC_BACKUP_MESSAGES, array_merge( $messages, array( $type => $message ) ) );
		}

		return false;
	}

	public static function has( $type ) {
		if ( ( $messages = get_option( VSC_BACKUP_MESSAGES, array() ) ) ) {
			if ( isset( $messages[ $type ] ) ) {
				return true;
			}
		}

		return false;
	}

	public static function get( $type ) {
		$message = null;
		if ( ( $messages = get_option( VSC_BACKUP_MESSAGES, array() ) ) ) {
			if ( isset( $messages[ $type ] ) && ( $message = $messages[ $type ] ) ) {
				unset( $messages[ $type ] );
			}

			// Set messages
			update_option( VSC_BACKUP_MESSAGES, $messages );
		}

		return $message;
	}
}
