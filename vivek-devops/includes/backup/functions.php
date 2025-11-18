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

/**
 * Get storage absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_storage_path( $params ) {
	if ( empty( $params['storage'] ) ) {
		throw new VSC_Backup_Storage_Exception( __( 'Unable to locate storage path. <a href="https://help.vivekchhikara.com/knowledgebase/invalid-storage-path/" target="_blank">Technical details</a>', VSC_BACKUP_PLUGIN_NAME ) );
	}

	// Validate storage path
	if ( vsc_backup_validate_file( $params['storage'] ) !== 0 ) {
		throw new VSC_Backup_Storage_Exception( __( 'Your storage directory name contains invalid characters. It cannot contain: < > : " | ? * \0. <a href="https://help.vivekchhikara.com/knowledgebase/invalid-storage-name/" target="_blank">Technical details</a>', VSC_BACKUP_PLUGIN_NAME ) );
	}

	// Get storage path
	$storage = VSC_BACKUP_STORAGE_PATH . DIRECTORY_SEPARATOR . basename( $params['storage'] );
	if ( ! is_dir( $storage ) ) {
		mkdir( $storage );
	}

	return $storage;
}

/**
 * Get backup absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_backup_path( $params ) {
	if ( empty( $params['archive'] ) ) {
		throw new VSC_Backup_Archive_Exception( __( 'Unable to locate archive path. <a href="https://help.vivekchhikara.com/knowledgebase/invalid-archive-path/" target="_blank">Technical details</a>', VSC_BACKUP_PLUGIN_NAME ) );
	}

	// Validate archive path
	if ( vsc_backup_validate_file( $params['archive'] ) !== 0 ) {
		throw new VSC_Backup_Archive_Exception( __( 'Your archive file name contains invalid characters. It cannot contain: < > : " | ? * \0. <a href="https://help.vivekchhikara.com/knowledgebase/invalid-archive-name/" target="_blank">Technical details</a>', VSC_BACKUP_PLUGIN_NAME ) );
	}

	return VSC_BACKUP_BACKUPS_PATH . DIRECTORY_SEPARATOR . $params['archive'];
}

/**
 * Validates a file name and path against an allowed set of rules
 *
 * @param  string  $file          File path
 * @param  array   $allowed_files Array of allowed files
 * @return integer
 */
function vsc_backup_validate_file( $file, $allowed_files = array() ) {
	$file = str_replace( '\\', '/', $file );

	// Validates special characters that are illegal in filenames on certain
	// operating systems and special characters requiring special escaping
	// to manipulate at the command line
	$invalid_chars = array( '<', '>', ':', '"', '|', '?', '*', chr( 0 ) );
	foreach ( $invalid_chars as $char ) {
		if ( strpos( $file, $char ) !== false ) {
			return 1;
		}
	}

	return validate_file( $file, $allowed_files );
}

/**
 * Get archive absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_archive_path( $params ) {
	if ( empty( $params['archive'] ) ) {
		throw new VSC_Backup_Archive_Exception( __( 'Unable to locate archive path. <a href="https://help.vivekchhikara.com/knowledgebase/invalid-archive-path/" target="_blank">Technical details</a>', VSC_BACKUP_PLUGIN_NAME ) );
	}

	// Validate archive path
	if ( vsc_backup_validate_file( $params['archive'] ) !== 0 ) {
		throw new VSC_Backup_Archive_Exception( __( 'Your archive file name contains invalid characters. It cannot contain: < > : " | ? * \0. <a href="https://help.vivekchhikara.com/knowledgebase/invalid-archive-name/" target="_blank">Technical details</a>', VSC_BACKUP_PLUGIN_NAME ) );
	}

	// Get archive path
	if ( empty( $params['vsc_backup_manual_restore'] ) ) {
		return vsc_backup_storage_path( $params ) . DIRECTORY_SEPARATOR . $params['archive'];
	}

	return vsc_backup_backup_path( $params );
}

/**
 * Get multipart.list absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_multipart_path( $params ) {
	return vsc_backup_storage_path( $params ) . DIRECTORY_SEPARATOR . VSC_BACKUP_MULTIPART_NAME;
}

/**
 * Get content.list absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_content_list_path( $params ) {
	return vsc_backup_storage_path( $params ) . DIRECTORY_SEPARATOR . VSC_BACKUP_CONTENT_LIST_NAME;
}

/**
 * Get media.list absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_media_list_path( $params ) {
	return vsc_backup_storage_path( $params ) . DIRECTORY_SEPARATOR . VSC_BACKUP_MEDIA_LIST_NAME;
}

/**
 * Get plugins.list absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_plugins_list_path( $params ) {
	return vsc_backup_storage_path( $params ) . DIRECTORY_SEPARATOR . VSC_BACKUP_PLUGINS_LIST_NAME;
}

/**
 * Get themes.list absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_themes_list_path( $params ) {
	return vsc_backup_storage_path( $params ) . DIRECTORY_SEPARATOR . VSC_BACKUP_THEMES_LIST_NAME;
}

/**
 * Get tables.list absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_tables_list_path( $params ) {
	return vsc_backup_storage_path( $params ) . DIRECTORY_SEPARATOR . VSC_BACKUP_TABLES_LIST_NAME;
}

/**
 * Get incremental.content.list absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_incremental_content_list_path( $params ) {
	return vsc_backup_storage_path( $params ) . DIRECTORY_SEPARATOR . VSC_BACKUP_INCREMENTAL_CONTENT_LIST_NAME;
}

/**
 * Get incremental.media.list absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_incremental_media_list_path( $params ) {
	return vsc_backup_storage_path( $params ) . DIRECTORY_SEPARATOR . VSC_BACKUP_INCREMENTAL_MEDIA_LIST_NAME;
}

/**
 * Get incremental.plugins.list absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_incremental_plugins_list_path( $params ) {
	return vsc_backup_storage_path( $params ) . DIRECTORY_SEPARATOR . VSC_BACKUP_INCREMENTAL_PLUGINS_LIST_NAME;
}

/**
 * Get incremental.themes.list absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_incremental_themes_list_path( $params ) {
	return vsc_backup_storage_path( $params ) . DIRECTORY_SEPARATOR . VSC_BACKUP_INCREMENTAL_THEMES_LIST_NAME;
}

/**
 * Get incremental.backups.list absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_incremental_backups_list_path( $params ) {
	return vsc_backup_storage_path( $params ) . DIRECTORY_SEPARATOR . VSC_BACKUP_INCREMENTAL_BACKUPS_LIST_NAME;
}

/**
 * Get package.json absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_package_path( $params ) {
	return vsc_backup_storage_path( $params ) . DIRECTORY_SEPARATOR . VSC_BACKUP_PACKAGE_NAME;
}

/**
 * Get multisite.json absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_multisite_path( $params ) {
	return vsc_backup_storage_path( $params ) . DIRECTORY_SEPARATOR . VSC_BACKUP_MULTISITE_NAME;
}

/**
 * Get blogs.json absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_blogs_path( $params ) {
	return vsc_backup_storage_path( $params ) . DIRECTORY_SEPARATOR . VSC_BACKUP_BLOGS_NAME;
}

/**
 * Get settings.json absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_settings_path( $params ) {
	return vsc_backup_storage_path( $params ) . DIRECTORY_SEPARATOR . VSC_BACKUP_SETTINGS_NAME;
}

/**
 * Get database.sql absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_database_path( $params ) {
	return vsc_backup_storage_path( $params ) . DIRECTORY_SEPARATOR . VSC_BACKUP_DATABASE_NAME;
}

/**
 * Get cookies.txt absolute path
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_cookies_path( $params ) {
	return vsc_backup_storage_path( $params ) . DIRECTORY_SEPARATOR . VSC_BACKUP_COOKIES_NAME;
}

/**
 * Get error log absolute path
 *
 * @return string
 */
function vsc_backup_error_path() {
	return VSC_BACKUP_STORAGE_PATH . DIRECTORY_SEPARATOR . VSC_BACKUP_ERROR_NAME;
}

/**
 * Get archive name
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_archive_name( $params ) {
	return basename( $params['archive'] );
}

/**
 * Get backup URL address
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_backup_url( $params ) {
	static $backups_base_url = '';
	if ( empty( $backups_base_url ) ) {
		if ( VSC_Backup_Backups::are_in_wp_content_folder() ) {
			$backups_base_url = str_replace( untrailingslashit( WP_CONTENT_DIR ), '', VSC_BACKUP_BACKUPS_PATH );
			$backups_base_url = content_url(
				vsc_backup_replace_directory_separator_with_forward_slash( $backups_base_url )
			);
		} else {
			$backups_base_url = str_replace( untrailingslashit( ABSPATH ), '', VSC_BACKUP_BACKUPS_PATH );
			$backups_base_url = site_url(
				vsc_backup_replace_directory_separator_with_forward_slash( $backups_base_url )
			);
		}
	}

	return $backups_base_url . '/' . vsc_backup_replace_directory_separator_with_forward_slash( $params['archive'] );
}

/**
 * Get archive size in bytes
 *
 * @param  array   $params Request parameters
 * @return integer
 */
function vsc_backup_archive_bytes( $params ) {
	return filesize( vsc_backup_archive_path( $params ) );
}

/**
 * Get archive modified time in seconds
 *
 * @param  array   $params Request parameters
 * @return integer
 */
function vsc_backup_archive_mtime( $params ) {
	return filemtime( vsc_backup_archive_path( $params ) );
}

/**
 * Get backup size in bytes
 *
 * @param  array   $params Request parameters
 * @return integer
 */
function vsc_backup_backup_bytes( $params ) {
	return filesize( vsc_backup_backup_path( $params ) );
}

/**
 * Get database size in bytes
 *
 * @param  array   $params Request parameters
 * @return integer
 */
function vsc_backup_database_bytes( $params ) {
	return filesize( vsc_backup_database_path( $params ) );
}

/**
 * Get package size in bytes
 *
 * @param  array   $params Request parameters
 * @return integer
 */
function vsc_backup_package_bytes( $params ) {
	return filesize( vsc_backup_package_path( $params ) );
}

/**
 * Get multisite size in bytes
 *
 * @param  array   $params Request parameters
 * @return integer
 */
function vsc_backup_multisite_bytes( $params ) {
	return filesize( vsc_backup_multisite_path( $params ) );
}

/**
 * Get archive size as text
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_archive_size( $params ) {
	return vsc_backup_size_format( filesize( vsc_backup_archive_path( $params ) ) );
}

/**
 * Get backup size as text
 *
 * @param  array  $params Request parameters
 * @return string
 */
function vsc_backup_backup_size( $params ) {
	return vsc_backup_size_format( filesize( vsc_backup_backup_path( $params ) ) );
}

/**
 * Parse file size
 *
 * @param  string $size    File size
 * @param  string $default Default size
 * @return string
 */
function vsc_backup_parse_size( $size, $default = null ) {
	$suffixes = array(
		''  => 1,
		'k' => 1000,
		'm' => 1000000,
		'g' => 1000000000,
	);

	// Parse size format
	if ( preg_match( '/([0-9]+)\s*(k|m|g)?(b?(ytes?)?)/i', $size, $matches ) ) {
		return $matches[1] * $suffixes[ strtolower( $matches[2] ) ];
	}

	return $default;
}

/**
 * Format file size into human-readable string
 *
 * Fixes the WP size_format bug: size_format( '0' ) => false
 *
 * @param  int|string   $bytes            Number of bytes. Note max integer size for integers.
 * @param  int          $decimals         Optional. Precision of number of decimal places. Default 0.
 * @return string|false False on failure. Number string on success.
 */
function vsc_backup_size_format( $bytes, $decimals = 0 ) {
	if ( strval( $bytes ) === '0' ) {
		return size_format( 0, $decimals );
	}

	return size_format( $bytes, $decimals );
}

/**
 * Get current site name
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function vsc_backup_site_name( $blog_id = null ) {
	return parse_url( get_site_url( $blog_id ), PHP_URL_HOST );
}

/**
 * Get archive file name
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function vsc_backup_archive_file( $blog_id = null ) {
	$name = array();

	// Add domain
	$name[] = parse_url( get_site_url( $blog_id ), PHP_URL_HOST );

	// Add path
	if ( ( $path = parse_url( get_site_url( $blog_id ), PHP_URL_PATH ) ) ) {
		foreach ( explode( '/', $path ) as $directory ) {
			if ( $directory ) {
				$name[] = $directory;
			}
		}
	}

	// Add year, month and day
	$name[] = date_i18n( 'Ymd' );

	// Add hours, minutes and seconds
	$name[] = date_i18n( 'His' );

	// Add unique identifier
	$name[] = vsc_backup_generate_random_string( 6, false );

	return sprintf( '%s.wpress', strtolower( implode( '-', $name ) ) );
}

/**
 * Get archive folder name
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function vsc_backup_archive_folder( $blog_id = null ) {
	$name = array();

	// Add domain
	$name[] = parse_url( get_site_url( $blog_id ), PHP_URL_HOST );

	// Add path
	if ( ( $path = parse_url( get_site_url( $blog_id ), PHP_URL_PATH ) ) ) {
		foreach ( explode( '/', $path ) as $directory ) {
			if ( $directory ) {
				$name[] = $directory;
			}
		}
	}

	return strtolower( implode( '-', $name ) );
}

/**
 * Get archive bucket name
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function vsc_backup_archive_bucket( $blog_id = null ) {
	$name = array();

	// Add domain
	if ( ( $domain = explode( '.', parse_url( get_site_url( $blog_id ), PHP_URL_HOST ) ) ) ) {
		foreach ( $domain as $subdomain ) {
			if ( $subdomain = strtolower( preg_replace( '/[^A-Za-z0-9\-]/', '', $subdomain ) ) ) {
				$name[] = $subdomain;
			}
		}
	}

	// Add path
	if ( ( $path = parse_url( get_site_url( $blog_id ), PHP_URL_PATH ) ) ) {
		foreach ( explode( '/', $path ) as $directory ) {
			if ( ( $directory = strtolower( preg_replace( '/[^A-Za-z0-9\-]/', '', $directory ) ) ) ) {
				$name[] = $directory;
			}
		}
	}

	return strtolower( implode( '-', $name ) );
}

/**
 * Get archive vault name
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function vsc_backup_archive_vault( $blog_id = null ) {
	$name = array();

	// Add domain
	if ( ( $domain = explode( '.', parse_url( get_site_url( $blog_id ), PHP_URL_HOST ) ) ) ) {
		foreach ( $domain as $subdomain ) {
			if ( $subdomain = strtolower( preg_replace( '/[^A-Za-z0-9\-]/', '', $subdomain ) ) ) {
				$name[] = $subdomain;
			}
		}
	}

	// Add path
	if ( ( $path = parse_url( get_site_url( $blog_id ), PHP_URL_PATH ) ) ) {
		foreach ( explode( '/', $path ) as $directory ) {
			if ( ( $directory = strtolower( preg_replace( '/[^A-Za-z0-9\-]/', '', $directory ) ) ) ) {
				$name[] = $directory;
			}
		}
	}

	return strtolower( implode( '-', $name ) );
}

/**
 * Get archive project name
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function vsc_backup_archive_project( $blog_id = null ) {
	$name = array();

	// Add domain
	if ( ( $domain = explode( '.', parse_url( get_site_url( $blog_id ), PHP_URL_HOST ) ) ) ) {
		foreach ( $domain as $subdomain ) {
			if ( $subdomain ) {
				$name[] = $subdomain;
			}
		}
	}

	// Add path
	if ( ( $path = parse_url( get_site_url( $blog_id ), PHP_URL_PATH ) ) ) {
		foreach ( explode( '/', $path ) as $directory ) {
			if ( $directory ) {
				$name[] = $directory;
			}
		}
	}

	return strtolower( implode( '-', $name ) );
}

/**
 * Get archive share name
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function vsc_backup_archive_share( $blog_id = null ) {
	$name = array();

	// Add domain
	if ( ( $domain = explode( '.', parse_url( get_site_url( $blog_id ), PHP_URL_HOST ) ) ) ) {
		foreach ( $domain as $subdomain ) {
			if ( $subdomain = strtolower( preg_replace( '/[^A-Za-z0-9\-]/', '', $subdomain ) ) ) {
				$name[] = $subdomain;
			}
		}
	}

	// Add path
	if ( ( $path = parse_url( get_site_url( $blog_id ), PHP_URL_PATH ) ) ) {
		foreach ( explode( '/', $path ) as $directory ) {
			if ( ( $directory = strtolower( preg_replace( '/[^A-Za-z0-9\-]/', '', $directory ) ) ) ) {
				$name[] = $directory;
			}
		}
	}

	return strtolower( implode( '-', $name ) );
}

/**
 * Generate random string
 *
 * @param  integer $length              String length
 * @param  boolean $mixed_chars         Whether to include mixed characters
 * @param  boolean $special_chars       Whether to include special characters
 * @param  boolean $extra_special_chars Whether to include extra special characters
 * @return string
 */
function vsc_backup_generate_random_string( $length = 12, $mixed_chars = true, $special_chars = false, $extra_special_chars = false ) {
	$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
	if ( $mixed_chars ) {
		$chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	}

	if ( $special_chars ) {
		$chars .= '!@#$%^&*()';
	}

	if ( $extra_special_chars ) {
		$chars .= '-_ []{}<>~`+=,.;:/?|';
	}

	$str = '';
	for ( $i = 0; $i < $length; $i++ ) {
		$str .= substr( $chars, wp_rand( 0, strlen( $chars ) - 1 ), 1 );
	}

	return $str;
}

/**
 * Get storage folder name
 *
 * @return string
 */
function vsc_backup_storage_folder() {
	return uniqid();
}

/**
 * Check whether blog ID is main site
 *
 * @param  integer $blog_id Blog ID
 * @return boolean
 */
function vsc_backup_is_mainsite( $blog_id = null ) {
	return $blog_id === null || $blog_id === 0 || $blog_id === 1;
}

/**
 * Get files absolute path by blog ID
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function vsc_backup_blog_files_abspath( $blog_id = null ) {
	if ( vsc_backup_is_mainsite( $blog_id ) ) {
		return vsc_backup_get_uploads_dir();
	}

	return WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'blogs.dir' . DIRECTORY_SEPARATOR . $blog_id . DIRECTORY_SEPARATOR . 'files';
}

/**
 * Get blogs.dir absolute path by blog ID
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function vsc_backup_blog_blogsdir_abspath( $blog_id = null ) {
	if ( vsc_backup_is_mainsite( $blog_id ) ) {
		return vsc_backup_get_uploads_dir();
	}

	return WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'blogs.dir' . DIRECTORY_SEPARATOR . $blog_id;
}

/**
 * Get sites absolute path by blog ID
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function vsc_backup_blog_sites_abspath( $blog_id = null ) {
	if ( vsc_backup_is_mainsite( $blog_id ) ) {
		return vsc_backup_get_uploads_dir();
	}

	return vsc_backup_get_uploads_dir() . DIRECTORY_SEPARATOR . 'sites' . DIRECTORY_SEPARATOR . $blog_id;
}

/**
 * Get files relative path by blog ID
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function vsc_backup_blog_files_relpath( $blog_id = null ) {
	if ( vsc_backup_is_mainsite( $blog_id ) ) {
		return 'uploads';
	}

	return 'blogs.dir' . DIRECTORY_SEPARATOR . $blog_id . DIRECTORY_SEPARATOR . 'files';
}

/**
 * Get blogs.dir relative path by blog ID
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function vsc_backup_blog_blogsdir_relpath( $blog_id = null ) {
	if ( vsc_backup_is_mainsite( $blog_id ) ) {
		return 'uploads';
	}

	return 'blogs.dir' . DIRECTORY_SEPARATOR . $blog_id;
}

/**
 * Get sites relative path by blog ID
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function vsc_backup_blog_sites_relpath( $blog_id = null ) {
	if ( vsc_backup_is_mainsite( $blog_id ) ) {
		return 'uploads';
	}

	return 'uploads' . DIRECTORY_SEPARATOR . 'sites' . DIRECTORY_SEPARATOR . $blog_id;
}

/**
 * Get files URL by blog ID
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function vsc_backup_blog_files_url( $blog_id = null ) {
	if ( vsc_backup_is_mainsite( $blog_id ) ) {
		return '/wp-content/uploads/';
	}

	return sprintf( '/wp-content/blogs.dir/%d/files/', $blog_id );
}

/**
 * Get blogs.dir URL by blog ID
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function vsc_backup_blog_blogsdir_url( $blog_id = null ) {
	if ( vsc_backup_is_mainsite( $blog_id ) ) {
		return '/wp-content/uploads/';
	}

	return sprintf( '/wp-content/blogs.dir/%d/', $blog_id );
}

/**
 * Get sites URL by blog ID
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function vsc_backup_blog_sites_url( $blog_id = null ) {
	if ( vsc_backup_is_mainsite( $blog_id ) ) {
		return '/wp-content/uploads/';
	}

	return sprintf( '/wp-content/uploads/sites/%d/', $blog_id );
}

/**
 * Get uploads URL by blog ID
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function vsc_backup_blog_uploads_url( $blog_id = null ) {
	if ( vsc_backup_is_mainsite( $blog_id ) ) {
		return sprintf( '/%s/', vsc_backup_get_uploads_path() );
	}

	return sprintf( '/%s/sites/%d/', vsc_backup_get_uploads_path(), $blog_id );
}

/**
 * Get ServMask table prefix by blog ID
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function vsc_backup_servmask_prefix( $blog_id = null ) {
	if ( vsc_backup_is_mainsite( $blog_id ) ) {
		return VSC_BACKUP_TABLE_PREFIX;
	}

	return VSC_BACKUP_TABLE_PREFIX . $blog_id . '_';
}

/**
 * Get WordPress table prefix by blog ID
 *
 * @param  integer $blog_id Blog ID
 * @return string
 */
function vsc_backup_table_prefix( $blog_id = null ) {
	global $wpdb;

	// Set base table prefix
	if ( vsc_backup_is_mainsite( $blog_id ) ) {
		return $wpdb->base_prefix;
	}

	return $wpdb->base_prefix . $blog_id . '_';
}

/**
 * Get default content filters
 *
 * @param  array $filters List of files and directories
 * @return array
 */
function vsc_backup_content_filters( $filters = array() ) {
	return array_merge(
		$filters,
		array(
			VSC_BACKUP_BACKUPS_PATH,
			VSC_BACKUP_BACKUPS_NAME,
			VSC_BACKUP_PACKAGE_NAME,
			VSC_BACKUP_MULTISITE_NAME,
			VSC_BACKUP_DATABASE_NAME,
		)
	);
}

/**
 * Get default media filters
 *
 * @param  array $filters List of files and directories
 * @return array
 */
function vsc_backup_media_filters( $filters = array() ) {
	return array_merge(
		$filters,
		array(
			VSC_BACKUP_BACKUPS_PATH,
		)
	);
}

/**
 * Get default plugin filters
 *
 * @param  array $filters List of plugins
 * @return array
 */
function vsc_backup_plugin_filters( $filters = array() ) {
	return array_merge(
		$filters,
		array(
			VSC_BACKUP_BACKUPS_PATH,
			VSC_BACKUP_PLUGIN_BASEDIR,
			AI1WMZE_PLUGIN_BASEDIR,
			AI1WMAE_PLUGIN_BASEDIR,
			AI1WMVE_PLUGIN_BASEDIR,
			AI1WMBE_PLUGIN_BASEDIR,
			AI1WMIE_PLUGIN_BASEDIR,
			AI1WMXE_PLUGIN_BASEDIR,
			AI1WMDE_PLUGIN_BASEDIR,
			AI1WMTE_PLUGIN_BASEDIR,
			AI1WMFE_PLUGIN_BASEDIR,
			AI1WMCE_PLUGIN_BASEDIR,
			AI1WMGE_PLUGIN_BASEDIR,
			AI1WMRE_PLUGIN_BASEDIR,
			AI1WMEE_PLUGIN_BASEDIR,
			AI1WMME_PLUGIN_BASEDIR,
			AI1WMOE_PLUGIN_BASEDIR,
			AI1WMPE_PLUGIN_BASEDIR,
			AI1WMKE_PLUGIN_BASEDIR,
			AI1WMNE_PLUGIN_BASEDIR,
			AI1WMSE_PLUGIN_BASEDIR,
			VSC_BACKUP_PLUGIN_BASEDIR,
			AI1WMLE_PLUGIN_BASEDIR,
			AI1WMWE_PLUGIN_BASEDIR,
		)
	);

	return $filters;
}

/**
 * Get default theme filters
 *
 * @param  array $filters List of files and directories
 * @return array
 */
function vsc_backup_theme_filters( $filters = array() ) {
	return array_merge(
		$filters,
		array(
			VSC_BACKUP_BACKUPS_PATH,
		)
	);
}

/**
 * Get active ServMask plugins
 *
 * @return array
 */
function vsc_backup_active_servmask_plugins( $plugins = array() ) {
	// WP Migration Plugin
	if ( defined( 'VSC_BACKUP_PLUGIN_BASENAME' ) ) {
		$plugins[] = VSC_BACKUP_PLUGIN_BASENAME;
	}

	// Microsoft Azure Extension
	if ( defined( 'AI1WMZE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMZE_PLUGIN_BASENAME;
	}

	// Backblaze B2 Extension
	if ( defined( 'AI1WMAE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMAE_PLUGIN_BASENAME;
	}

	// Backup Plugin
	if ( defined( 'AI1WMVE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMVE_PLUGIN_BASENAME;
	}

	// Box Extension
	if ( defined( 'AI1WMBE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMBE_PLUGIN_BASENAME;
	}

	// DigitalOcean Spaces Extension
	if ( defined( 'AI1WMIE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMIE_PLUGIN_BASENAME;
	}

	// Direct Extension
	if ( defined( 'AI1WMXE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMXE_PLUGIN_BASENAME;
	}

	// Dropbox Extension
	if ( defined( 'AI1WMDE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMDE_PLUGIN_BASENAME;
	}

	// File Extension
	if ( defined( 'AI1WMTE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMTE_PLUGIN_BASENAME;
	}

	// FTP Extension
	if ( defined( 'AI1WMFE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMFE_PLUGIN_BASENAME;
	}

	// Google Cloud Storage Extension
	if ( defined( 'AI1WMCE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMCE_PLUGIN_BASENAME;
	}

	// Google Drive Extension
	if ( defined( 'AI1WMGE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMGE_PLUGIN_BASENAME;
	}

	// Amazon Glacier Extension
	if ( defined( 'AI1WMRE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMRE_PLUGIN_BASENAME;
	}

	// Mega Extension
	if ( defined( 'AI1WMEE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMEE_PLUGIN_BASENAME;
	}

	// Multisite Extension
	if ( defined( 'AI1WMME_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMME_PLUGIN_BASENAME;
	}

	// OneDrive Extension
	if ( defined( 'AI1WMOE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMOE_PLUGIN_BASENAME;
	}

	// pCloud Extension
	if ( defined( 'AI1WMPE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMPE_PLUGIN_BASENAME;
	}

	// Pro Plugin
	if ( defined( 'AI1WMKE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMKE_PLUGIN_BASENAME;
	}

	// S3 Client Extension
	if ( defined( 'AI1WMNE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMNE_PLUGIN_BASENAME;
	}

	// Amazon S3 Extension
	if ( defined( 'AI1WMSE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMSE_PLUGIN_BASENAME;
	}

	// Unlimited Extension
	if ( defined( 'VSC_BACKUP_PLUGIN_BASENAME' ) ) {
		$plugins[] = VSC_BACKUP_PLUGIN_BASENAME;
	}

	// URL Extension
	if ( defined( 'AI1WMLE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMLE_PLUGIN_BASENAME;
	}

	// WebDAV Extension
	if ( defined( 'AI1WMWE_PLUGIN_BASENAME' ) ) {
		$plugins[] = AI1WMWE_PLUGIN_BASENAME;
	}

	return $plugins;
}

/**
 * Get active sitewide plugins
 *
 * @return array
 */
function vsc_backup_active_sitewide_plugins() {
	return array_keys( get_site_option( VSC_BACKUP_ACTIVE_SITEWIDE_PLUGINS, array() ) );
}

/**
 * Get active plugins
 *
 * @return array
 */
function vsc_backup_active_plugins() {
	return array_values( get_option( VSC_BACKUP_ACTIVE_PLUGINS, array() ) );
}

/**
 * Set active sitewide plugins (inspired by WordPress activate_plugins() function)
 *
 * @param  array   $plugins List of plugins
 * @return boolean
 */
function vsc_backup_activate_sitewide_plugins( $plugins ) {
	$current = get_site_option( VSC_BACKUP_ACTIVE_SITEWIDE_PLUGINS, array() );

	// Add plugins
	foreach ( $plugins as $plugin ) {
		if ( ! isset( $current[ $plugin ] ) && ! is_wp_error( validate_plugin( $plugin ) ) ) {
			$current[ $plugin ] = time();
		}
	}

	return update_site_option( VSC_BACKUP_ACTIVE_SITEWIDE_PLUGINS, $current );
}

/**
 * Set active plugins (inspired by WordPress activate_plugins() function)
 *
 * @param  array   $plugins List of plugins
 * @return boolean
 */
function vsc_backup_activate_plugins( $plugins ) {
	$current = get_option( VSC_BACKUP_ACTIVE_PLUGINS, array() );

	// Add plugins
	foreach ( $plugins as $plugin ) {
		if ( ! in_array( $plugin, $current ) && ! is_wp_error( validate_plugin( $plugin ) ) ) {
			$current[] = $plugin;
		}
	}

	return update_option( VSC_BACKUP_ACTIVE_PLUGINS, $current );
}

/**
 * Get active template
 *
 * @return string
 */
function vsc_backup_active_template() {
	return get_option( VSC_BACKUP_ACTIVE_TEMPLATE );
}

/**
 * Get active stylesheet
 *
 * @return string
 */
function vsc_backup_active_stylesheet() {
	return get_option( VSC_BACKUP_ACTIVE_STYLESHEET );
}

/**
 * Set active template
 *
 * @param  string  $template Template name
 * @return boolean
 */
function vsc_backup_activate_template( $template ) {
	return update_option( VSC_BACKUP_ACTIVE_TEMPLATE, $template );
}

/**
 * Set active stylesheet
 *
 * @param  string  $stylesheet Stylesheet name
 * @return boolean
 */
function vsc_backup_activate_stylesheet( $stylesheet ) {
	return update_option( VSC_BACKUP_ACTIVE_STYLESHEET, $stylesheet );
}

/**
 * Set inactive sitewide plugins (inspired by WordPress deactivate_plugins() function)
 *
 * @param  array   $plugins List of plugins
 * @return boolean
 */
function vsc_backup_deactivate_sitewide_plugins( $plugins ) {
	$current = get_site_option( VSC_BACKUP_ACTIVE_SITEWIDE_PLUGINS, array() );

	// Add plugins
	foreach ( $plugins as $plugin ) {
		if ( isset( $current[ $plugin ] ) ) {
			unset( $current[ $plugin ] );
		}
	}

	return update_site_option( VSC_BACKUP_ACTIVE_SITEWIDE_PLUGINS, $current );
}


/**
 * Set inactive plugins (inspired by WordPress deactivate_plugins() function)
 *
 * @param  array   $plugins List of plugins
 * @return boolean
 */
function vsc_backup_deactivate_plugins( $plugins ) {
	$current = get_option( VSC_BACKUP_ACTIVE_PLUGINS, array() );

	// Remove plugins
	foreach ( $plugins as $plugin ) {
		if ( ( $key = array_search( $plugin, $current ) ) !== false ) {
			unset( $current[ $key ] );
		}
	}

	return update_option( VSC_BACKUP_ACTIVE_PLUGINS, $current );
}

/**
 * Deactivate Jetpack modules
 *
 * @param  array   $modules List of modules
 * @return boolean
 */
function vsc_backup_deactivate_jetpack_modules( $modules ) {
	$current = get_option( VSC_BACKUP_JETPACK_ACTIVE_MODULES, array() );

	// Remove modules
	foreach ( $modules as $module ) {
		if ( ( $key = array_search( $module, $current ) ) !== false ) {
			unset( $current[ $key ] );
		}
	}

	return update_option( VSC_BACKUP_JETPACK_ACTIVE_MODULES, $current );
}

/**
 * Deactivate Swift Optimizer rules
 *
 * @param  array   $rules List of rules
 * @return boolean
 */
function vsc_backup_deactivate_swift_optimizer_rules( $rules ) {
	$current = get_option( VSC_BACKUP_SWIFT_OPTIMIZER_PLUGIN_ORGANIZER, array() );

	// Remove rules
	foreach ( $rules as $rule ) {
		unset( $current['rules'][ $rule ] );
	}

	return update_option( VSC_BACKUP_SWIFT_OPTIMIZER_PLUGIN_ORGANIZER, $current );
}

/**
 * Deactivate sitewide Revolution Slider
 *
 * @param  string  $basename Plugin basename
 * @return boolean
 */
function vsc_backup_deactivate_sitewide_revolution_slider( $basename ) {
	if ( ( $plugins = get_plugins() ) ) {
		if ( isset( $plugins[ $basename ]['Version'] ) && ( $version = $plugins[ $basename ]['Version'] ) ) {
			if ( version_compare( PHP_VERSION, '7.3', '>=' ) && version_compare( $version, '5.4.8.3', '<' ) ) {
				return vsc_backup_deactivate_sitewide_plugins( array( $basename ) );
			}

			if ( version_compare( PHP_VERSION, '7.2', '>=' ) && version_compare( $version, '5.4.6', '<' ) ) {
				return vsc_backup_deactivate_sitewide_plugins( array( $basename ) );
			}

			if ( version_compare( PHP_VERSION, '7.1', '>=' ) && version_compare( $version, '5.4.1', '<' ) ) {
				return vsc_backup_deactivate_sitewide_plugins( array( $basename ) );
			}

			if ( version_compare( PHP_VERSION, '7.0', '>=' ) && version_compare( $version, '4.6.5', '<' ) ) {
				return vsc_backup_deactivate_sitewide_plugins( array( $basename ) );
			}
		}
	}

	return false;
}

/**
 * Deactivate Revolution Slider
 *
 * @param  string  $basename Plugin basename
 * @return boolean
 */
function vsc_backup_deactivate_revolution_slider( $basename ) {
	if ( ( $plugins = get_plugins() ) ) {
		if ( isset( $plugins[ $basename ]['Version'] ) && ( $version = $plugins[ $basename ]['Version'] ) ) {
			if ( version_compare( PHP_VERSION, '7.3', '>=' ) && version_compare( $version, '5.4.8.3', '<' ) ) {
				return vsc_backup_deactivate_plugins( array( $basename ) );
			}

			if ( version_compare( PHP_VERSION, '7.2', '>=' ) && version_compare( $version, '5.4.6', '<' ) ) {
				return vsc_backup_deactivate_plugins( array( $basename ) );
			}

			if ( version_compare( PHP_VERSION, '7.1', '>=' ) && version_compare( $version, '5.4.1', '<' ) ) {
				return vsc_backup_deactivate_plugins( array( $basename ) );
			}

			if ( version_compare( PHP_VERSION, '7.0', '>=' ) && version_compare( $version, '4.6.5', '<' ) ) {
				return vsc_backup_deactivate_plugins( array( $basename ) );
			}
		}
	}

	return false;
}

/**
 * Initial DB version
 *
 * @return boolean
 */
function vsc_backup_initial_db_version() {
	if ( ! get_option( VSC_BACKUP_DB_VERSION ) ) {
		return update_option( VSC_BACKUP_DB_VERSION, get_option( VSC_BACKUP_INITIAL_DB_VERSION ) );
	}

	return false;
}

/**
 * Discover plugin basename
 *
 * @param  string $basename Plugin basename
 * @return string
 */
function vsc_backup_discover_plugin_basename( $basename ) {
	if ( ( $plugins = get_plugins() ) ) {
		foreach ( $plugins as $plugin => $info ) {
			if ( strpos( dirname( $plugin ), dirname( $basename ) ) !== false ) {
				if ( basename( $plugin ) === basename( $basename ) ) {
					return $plugin;
				}
			}
		}
	}

	return $basename;
}

/**
 * Validate plugin basename
 *
 * @param  string  $basename Plugin basename
 * @return boolean
 */
function vsc_backup_validate_plugin_basename( $basename ) {
	if ( ( $plugins = get_plugins() ) ) {
		foreach ( $plugins as $plugin => $info ) {
			if ( $plugin === $basename ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Validate theme basename
 *
 * @param  string  $basename Theme basename
 * @return boolean
 */
function vsc_backup_validate_theme_basename( $basename ) {
	if ( ( $themes = search_theme_directories() ) ) {
		foreach ( $themes as $theme => $info ) {
			if ( $info['theme_file'] === $basename ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Flush WP options cache
 *
 * @return void
 */
function vsc_backup_cache_flush() {
	wp_cache_init();
	wp_cache_flush();

	// Reset WP options cache
	wp_cache_set( 'alloptions', array(), 'options' );
	wp_cache_set( 'notoptions', array(), 'options' );

	// Reset WP sitemeta cache
	wp_cache_set( '1:notoptions', array(), 'site-options' );
	wp_cache_set( '1:ms_files_rewriting', false, 'site-options' );
	wp_cache_set( '1:active_sitewide_plugins', false, 'site-options' );

	// Delete WP options cache
	wp_cache_delete( 'alloptions', 'options' );
	wp_cache_delete( 'notoptions', 'options' );

	// Delete WP sitemeta cache
	wp_cache_delete( '1:notoptions', 'site-options' );
	wp_cache_delete( '1:ms_files_rewriting', 'site-options' );
	wp_cache_delete( '1:active_sitewide_plugins', 'site-options' );

	// Remove WP options filter
	remove_all_filters( 'sanitize_option_home' );
	remove_all_filters( 'sanitize_option_siteurl' );
	remove_all_filters( 'default_site_option_ms_files_rewriting' );
}

/**
 * Flush Elementor cache
 *
 * @return void
 */
function vsc_backup_elementor_cache_flush() {
	delete_post_meta_by_key( '_elementor_css' );
	delete_option( '_elementor_global_css' );
	delete_option( 'elementor-custom-breakpoints-files' );
}

/**
 * Set WooCommerce Force SSL checkout
 *
 * @param  boolean $yes Force SSL checkout
 * @return void
 */
function vsc_backup_woocommerce_force_ssl( $yes = true ) {
	if ( get_option( 'woocommerce_force_ssl_checkout' ) ) {
		if ( $yes ) {
			update_option( 'woocommerce_force_ssl_checkout', 'yes' );
		} else {
			update_option( 'woocommerce_force_ssl_checkout', 'no' );
		}
	}
}

/**
 * Set URL scheme
 *
 * @param  string $url    URL value
 * @param  string $scheme URL scheme
 * @return string
 */
function vsc_backup_url_scheme( $url, $scheme = '' ) {
	if ( empty( $scheme ) ) {
		return preg_replace( '#^\w+://#', '//', $url );
	}

	return preg_replace( '#^\w+://#', $scheme . '://', $url );
}

/**
 * Opens a file in specified mode
 *
 * @param  string   $file Path to the file to open
 * @param  string   $mode Mode in which to open the file
 * @return resource
 * @throws VSC_Backup_Not_Accessible_Exception
 */
function vsc_backup_open( $file, $mode ) {
	$file_handle = @fopen( $file, $mode );
	if ( false === $file_handle ) {
		throw new VSC_Backup_Not_Accessible_Exception( sprintf( __( 'Unable to open %s with mode %s. <a href="https://help.vivekchhikara.com/knowledgebase/invalid-file-permissions/" target="_blank">Technical details</a>', VSC_BACKUP_PLUGIN_NAME ), $file, $mode ) );
	}

	return $file_handle;
}

/**
 * Write contents to a file
 *
 * @param  resource $handle  File handle to write to
 * @param  string   $content Contents to write to the file
 * @return integer
 * @throws VSC_Backup_Not_Writable_Exception
 * @throws VSC_Backup_Quota_Exceeded_Exception
 */
function vsc_backup_write( $handle, $content ) {
	$write_result = @fwrite( $handle, $content );
	if ( false === $write_result ) {
		if ( ( $meta = stream_get_meta_data( $handle ) ) ) {
			throw new VSC_Backup_Not_Writable_Exception( sprintf( __( 'Unable to write to: %s. <a href="https://help.vivekchhikara.com/knowledgebase/invalid-file-permissions/" target="_blank">Technical details</a>', VSC_BACKUP_PLUGIN_NAME ), $meta['uri'] ) );
		}
	} elseif ( null === $write_result ) {
		return strlen( $content );
	} elseif ( strlen( $content ) !== $write_result ) {
		if ( ( $meta = stream_get_meta_data( $handle ) ) ) {
			throw new VSC_Backup_Quota_Exceeded_Exception( sprintf( __( 'Out of disk space. Unable to write to: %s. <a href="https://help.vivekchhikara.com/knowledgebase/out-of-disk-space/" target="_blank">Technical details</a>', VSC_BACKUP_PLUGIN_NAME ), $meta['uri'] ) );
		}
	}

	return $write_result;
}

/**
 * Read contents from a file
 *
 * @param  resource $handle File handle to read from
 * @param  integer  $length Up to length number of bytes read
 * @return string
 * @throws VSC_Backup_Not_Readable_Exception
 */
function vsc_backup_read( $handle, $length ) {
	if ( $length > 0 ) {
		$read_result = @fread( $handle, $length );
		if ( false === $read_result ) {
			if ( ( $meta = stream_get_meta_data( $handle ) ) ) {
				throw new VSC_Backup_Not_Readable_Exception( sprintf( __( 'Unable to read file: %s. <a href="https://help.vivekchhikara.com/knowledgebase/invalid-file-permissions/" target="_blank">Technical details</a>', VSC_BACKUP_PLUGIN_NAME ), $meta['uri'] ) );
			}
		}

		return $read_result;
	}

	return false;
}

/**
 * Seeks on a file pointer
 *
 * @param  resource $handle File handle
 * @param  integer  $offset File offset
 * @param  integer  $mode   Offset mode
 * @return integer
 */
function vsc_backup_seek( $handle, $offset, $mode = SEEK_SET ) {
	$seek_result = @fseek( $handle, $offset, $mode );
	if ( -1 === $seek_result ) {
		if ( ( $meta = stream_get_meta_data( $handle ) ) ) {
			throw new VSC_Backup_Not_Seekable_Exception( sprintf( __( 'Unable to seek to offset %d on %s. <a href="https://help.vivekchhikara.com/knowledgebase/php-32bit/" target="_blank">Technical details</a>', VSC_BACKUP_PLUGIN_NAME ), $offset, $meta['uri'] ) );
		}
	}

	return $seek_result;
}

/**
 * Returns the current position of the file read/write pointer
 *
 * @param  resource $handle File handle
 * @return integer
 */
function vsc_backup_tell( $handle ) {
	$tell_result = @ftell( $handle );
	if ( false === $tell_result ) {
		if ( ( $meta = stream_get_meta_data( $handle ) ) ) {
			throw new VSC_Backup_Not_Tellable_Exception( sprintf( __( 'Unable to get current pointer position of %s. <a href="https://help.vivekchhikara.com/knowledgebase/php-32bit/" target="_blank">Technical details</a>', VSC_BACKUP_PLUGIN_NAME ), $meta['uri'] ) );
		}
	}

	return $tell_result;
}

/**
 * Write fields to a file
 *
 * @param  resource $handle File handle to write to
 * @param  array    $fields Fields to write to the file
 * @return integer
 * @throws VSC_Backup_Not_Writable_Exception
 */
function vsc_backup_putcsv( $handle, $fields ) {
	$write_result = @fputcsv( $handle, $fields );
	if ( false === $write_result ) {
		if ( ( $meta = stream_get_meta_data( $handle ) ) ) {
			throw new VSC_Backup_Not_Writable_Exception( sprintf( __( 'Unable to write to: %s. <a href="https://help.vivekchhikara.com/knowledgebase/invalid-file-permissions/" target="_blank">Technical details</a>', VSC_BACKUP_PLUGIN_NAME ), $meta['uri'] ) );
		}
	}

	return $write_result;
}

/**
 * Closes a file handle
 *
 * @param  resource $handle File handle to close
 * @return boolean
 */
function vsc_backup_close( $handle ) {
	return @fclose( $handle );
}

/**
 * Deletes a file
 *
 * @param  string  $file Path to file to delete
 * @return boolean
 */
function vsc_backup_unlink( $file ) {
	return @unlink( $file );
}

/**
 * Sets modification time of a file
 *
 * @param  string  $file Path to file to change modification time
 * @param  integer $time File modification time
 * @return boolean
 */
function vsc_backup_touch( $file, $mtime ) {
	return @touch( $file, $mtime );
}

/**
 * Changes file mode
 *
 * @param  string  $file Path to file to change mode
 * @param  integer $time File mode
 * @return boolean
 */
function vsc_backup_chmod( $file, $mode ) {
	return @chmod( $file, $mode );
}

/**
 * Copies one file's contents to another
 *
 * @param  string $source_file      File to copy the contents from
 * @param  string $destination_file File to copy the contents to
 */
function vsc_backup_copy( $source_file, $destination_file ) {
	$source_handle      = vsc_backup_open( $source_file, 'rb' );
	$destination_handle = vsc_backup_open( $destination_file, 'ab' );
	while ( $buffer = vsc_backup_read( $source_handle, 4096 ) ) {
		vsc_backup_write( $destination_handle, $buffer );
	}
	vsc_backup_close( $source_handle );
	vsc_backup_close( $destination_handle );
}

/**
 * Check whether file size is supported by current PHP version
 *
 * @param  string  $file         Path to file
 * @param  integer $php_int_size Size of PHP integer
 * @return boolean $php_int_max  Max value of PHP integer
 */
function vsc_backup_is_filesize_supported( $file, $php_int_size = PHP_INT_SIZE, $php_int_max = PHP_INT_MAX ) {
	$size_result = true;

	// Check whether file size is less than 2GB in PHP 32bits
	if ( $php_int_size === 4 ) {
		if ( ( $file_handle = @fopen( $file, 'r' ) ) ) {
			if ( @fseek( $file_handle, $php_int_max, SEEK_SET ) !== -1 ) {
				if ( @fgetc( $file_handle ) !== false ) {
					$size_result = false;
				}
			}

			@fclose( $file_handle );
		}
	}

	return $size_result;
}

/**
 * Check whether file name is supported by All-in-One WP Migration
 *
 * @param  string  $file       Path to file
 * @param  array   $extensions File extensions
 * @return boolean
 */
function vsc_backup_is_filename_supported( $file, $extensions = array( 'wpress' ) ) {
	if ( in_array( pathinfo( $file, PATHINFO_EXTENSION ), $extensions ) ) {
		return true;
	}

	return false;
}

/**
 * Verify secret key
 *
 * @param  string  $secret_key Secret key
 * @return boolean
 * @throws VSC_Backup_Not_Valid_Secret_Key_Exception
 */
function vsc_backup_verify_secret_key( $secret_key ) {
	if ( $secret_key !== get_option( VSC_BACKUP_SECRET_KEY ) ) {
		throw new VSC_Backup_Not_Valid_Secret_Key_Exception( __( 'Unable to authenticate the secret key. <a href="https://help.vivekchhikara.com/knowledgebase/invalid-secret-key/" target="_blank">Technical details</a>', VSC_BACKUP_PLUGIN_NAME ) );
	}

	return true;
}

/**
 * Is scheduled backup?
 *
 * @return boolean
 */
function vsc_backup_is_scheduled_backup() {
	if ( isset( $_GET['vsc_backup_manual_export'] ) || isset( $_POST['vsc_backup_manual_export'] ) ) {
		return false;
	}

	if ( isset( $_GET['vsc_backup_manual_import'] ) || isset( $_POST['vsc_backup_manual_import'] ) ) {
		return false;
	}

	if ( isset( $_GET['vsc_backup_manual_restore'] ) || isset( $_POST['vsc_backup_manual_restore'] ) ) {
		return false;
	}

	if ( isset( $_GET['vsc_backup_manual_reset'] ) || isset( $_POST['vsc_backup_manual_reset'] ) ) {
		return false;
	}

	return true;
}

/**
 * PHP setup environment
 *
 * @return void
 */
function vsc_backup_setup_environment() {
	// Set whether a client disconnect should abort script execution
	@ignore_user_abort( true );

	// Set maximum execution time
	@set_time_limit( 0 );

	// Set maximum time in seconds a script is allowed to parse input data
	@ini_set( 'max_input_time', '-1' );

	// Set maximum backtracking steps
	@ini_set( 'pcre.backtrack_limit', PHP_INT_MAX );

	// Set binary safe encoding
	if ( @function_exists( 'mb_internal_encoding' ) && ( @ini_get( 'mbstring.func_overload' ) & 2 ) ) {
		@mb_internal_encoding( 'ISO-8859-1' );
	}

	// Clean (erase) the output buffer and turn off output buffering
	if ( @ob_get_length() ) {
		@ob_end_clean();
	}

	// Set error handler
	@set_error_handler( 'VSC_Backup_Handler::error' );

	// Set shutdown handler
	@register_shutdown_function( 'VSC_Backup_Handler::shutdown' );
}

/**
 * Get WordPress time zone string
 *
 * @return string
 */
function vsc_backup_get_timezone_string() {
	if ( ( $timezone_string = get_option( 'timezone_string' ) ) ) {
		return $timezone_string;
	}

	if ( ( $gmt_offset = get_option( 'gmt_offset' ) ) ) {
		if ( $gmt_offset > 0 ) {
			return sprintf( 'UTC+%s', abs( $gmt_offset ) );
		} elseif ( $gmt_offset < 0 ) {
			return sprintf( 'UTC-%s', abs( $gmt_offset ) );
		}
	}

	return 'UTC';
}

/**
 * Get WordPress filter hooks
 *
 * @param  string $tag The name of the filter hook
 * @return array
 */
function vsc_backup_get_filters( $tag ) {
	global $wp_filter;

	// Get WordPress filter hooks
	$filters = array();
	if ( isset( $wp_filter[ $tag ] ) ) {
		if ( ( $filters = $wp_filter[ $tag ] ) ) {
			// WordPress 4.7 introduces new class for working with filters/actions called WP_Hook
			// which adds another level of abstraction and we need to address it.
			if ( isset( $filters->callbacks ) ) {
				$filters = $filters->callbacks;
			}
		}

		ksort( $filters );
	}

	return $filters;
}

/**
 * Get WordPress plugins directories
 *
 * @return array
 */
function vsc_backup_get_themes_dirs() {
	$theme_dirs = array();
	foreach ( search_theme_directories() as $theme_name => $theme_info ) {
		if ( isset( $theme_info['theme_root'] ) ) {
			if ( ! in_array( $theme_info['theme_root'], $theme_dirs ) ) {
				$theme_dirs[] = untrailingslashit( $theme_info['theme_root'] );
			}
		}
	}

	return $theme_dirs;
}

/**
 * Get WordPress plugins directory
 *
 * @return string
 */
function vsc_backup_get_plugins_dir() {
	return untrailingslashit( WP_PLUGIN_DIR );
}

/**
 * Get WordPress uploads directory
 *
 * @return string
 */
function vsc_backup_get_uploads_dir() {
	if ( ( $upload_dir = wp_upload_dir() ) ) {
		if ( isset( $upload_dir['basedir'] ) ) {
			return untrailingslashit( $upload_dir['basedir'] );
		}
	}
}

/**
 * Get WordPress uploads URL
 *
 * @return string
 */
function vsc_backup_get_uploads_url() {
	if ( ( $upload_dir = wp_upload_dir() ) ) {
		if ( isset( $upload_dir['baseurl'] ) ) {
			return trailingslashit( $upload_dir['baseurl'] );
		}
	}
}

/**
 * Get WordPress uploads path
 *
 * @return string
 */
function vsc_backup_get_uploads_path() {
	if ( ( $upload_dir = wp_upload_dir() ) ) {
		if ( isset( $upload_dir['basedir'] ) ) {
			return str_replace( ABSPATH, '', $upload_dir['basedir'] );
		}
	}
}

/**
 * i18n friendly version of basename()
 *
 * @param  string $path   File path
 * @param  string $suffix If the filename ends in suffix this will also be cut off
 * @return string
 */
function vsc_backup_basename( $path, $suffix = '' ) {
	return urldecode( basename( str_replace( array( '%2F', '%5C' ), '/', urlencode( $path ) ), $suffix ) );
}

/**
 * i18n friendly version of dirname()
 *
 * @param  string $path File path
 * @return string
 */
function vsc_backup_dirname( $path ) {
	return urldecode( dirname( str_replace( array( '%2F', '%5C' ), '/', urlencode( $path ) ) ) );
}

/**
 * Replace forward slash with current directory separator
 *
 * @param  string $path Path
 * @return string
 */
function vsc_backup_replace_forward_slash_with_directory_separator( $path ) {
	return str_replace( '/', DIRECTORY_SEPARATOR, $path );
}

/**
 * Replace current directory separator with forward slash
 *
 * @param  string $path Path
 * @return string
 */
function vsc_backup_replace_directory_separator_with_forward_slash( $path ) {
	return str_replace( DIRECTORY_SEPARATOR, '/', $path );
}

/**
 * Escape Windows directory separator
 *
 * @param  string $path Path
 * @return string
 */
function vsc_backup_escape_windows_directory_separator( $path ) {
	return preg_replace( '/[\\\\]+/', '\\\\\\\\', $path );
}

/**
 * Should reset WordPress permalinks?
 *
 * @param  array   $params Request parameters
 * @return boolean
 */
function vsc_backup_should_reset_permalinks( $params ) {
	global $wp_rewrite, $is_apache;

	// Permalinks are not supported
	if ( empty( $params['using_permalinks'] ) ) {
		if ( $wp_rewrite->using_permalinks() ) {
			if ( $is_apache ) {
				if ( ! apache_mod_loaded( 'mod_rewrite', false ) ) {
					return true;
				}
			}
		}
	}

	return false;
}

/**
 * Get .htaccess file content
 *
 * @return string
 */
function vsc_backup_get_htaccess() {
	if ( is_file( VSC_BACKUP_WORDPRESS_HTACCESS ) ) {
		return @file_get_contents( VSC_BACKUP_WORDPRESS_HTACCESS );
	}

	return '';
}

/**
 * Get web.config file content
 *
 * @return string
 */
function vsc_backup_get_webconfig() {
	if ( is_file( VSC_BACKUP_WORDPRESS_WEBCONFIG ) ) {
		return @file_get_contents( VSC_BACKUP_WORDPRESS_WEBCONFIG );
	}

	return '';
}

/**
 * Get available space on filesystem or disk partition
 *
 * @param  string $path Directory of the filesystem or disk partition
 * @return mixed
 */
function vsc_backup_disk_free_space( $path ) {
	if ( function_exists( 'disk_free_space' ) ) {
		return @disk_free_space( $path );
	}
}

/**
 * Set response header to json end echo data
 *
 * @param array $data
 * @param int $options
 * @param int $depth
 * @return void
 */
function vsc_backup_json_response( $data, $options = 0 ) {
	if ( ! headers_sent() ) {
		header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset', 'utf-8' ) );
	}

	echo json_encode( $data, $options );
}

/**
 * Determines if the server can encrypt backups
 *
 * @return boolean
 */
function vsc_backup_can_encrypt() {
	if ( ! function_exists( 'openssl_encrypt' ) ) {
		return false;
	}

	if ( ! function_exists( 'openssl_random_pseudo_bytes' ) ) {
		return false;
	}

	if ( ! function_exists( 'openssl_cipher_iv_length' ) ) {
		return false;
	}

	if ( ! function_exists( 'sha1' ) ) {
		return false;
	}

	if ( ! in_array( VSC_BACKUP_CIPHER_NAME, array_map( 'strtoupper', openssl_get_cipher_methods() ) ) ) {
		return false;
	}

	return true;
}

/**
 * Determines if the server can decrypt backups
 *
 * @return boolean
 */
function vsc_backup_can_decrypt() {
	if ( ! function_exists( 'openssl_decrypt' ) ) {
		return false;
	}

	if ( ! function_exists( 'openssl_random_pseudo_bytes' ) ) {
		return false;
	}

	if ( ! function_exists( 'openssl_cipher_iv_length' ) ) {
		return false;
	}

	if ( ! function_exists( 'sha1' ) ) {
		return false;
	}

	if ( ! in_array( VSC_BACKUP_CIPHER_NAME, array_map( 'strtoupper', openssl_get_cipher_methods() ) ) ) {
		return false;
	}

	return true;
}

/**
 * Encrypts a string with a key
 *
 * @param string $string String to encrypt
 * @param string $key    Key to encrypt the string with
 * @return string
 * @throws VSC_Backup_Not_Encryptable_Exception
 */
function vsc_backup_encrypt_string( $string, $key ) {
	$iv_length = vsc_backup_crypt_iv_length();
	$key       = substr( sha1( $key, true ), 0, $iv_length );

	$iv = openssl_random_pseudo_bytes( $iv_length );
	if ( $iv === false ) {
		throw new VSC_Backup_Not_Encryptable_Exception( __( 'Unable to generate random bytes.', VSC_BACKUP_PLUGIN_NAME ) );
	}

	$encrypted_string = openssl_encrypt( $string, VSC_BACKUP_CIPHER_NAME, $key, OPENSSL_RAW_DATA, $iv );
	if ( $encrypted_string === false ) {
		throw new VSC_Backup_Not_Encryptable_Exception( __( 'Unable to encrypt data.', VSC_BACKUP_PLUGIN_NAME ) );
	}

	return sprintf( '%s%s', $iv, $encrypted_string );
}

/**
 * Returns encrypt/decrypt iv length
 *
 * @return int
 * @throws VSC_Backup_Not_Encryptable_Exception
 */
function vsc_backup_crypt_iv_length() {
	$iv_length = openssl_cipher_iv_length( VSC_BACKUP_CIPHER_NAME );
	if ( $iv_length === false ) {
		throw new VSC_Backup_Not_Encryptable_Exception( __( 'Unable to obtain cipher length.', VSC_BACKUP_PLUGIN_NAME ) );
	}

	return $iv_length;
}

/**
 * Decrypts a string with a eky
 *
 * @param string $encrypted_string String to decrypt
 * @param string $key              Key to decrypt the string with
 * @return string
 * @throws VSC_Backup_Not_Encryptable_Exception
 * @throws VSC_Backup_Not_Decryptable_Exception
 */
function vsc_backup_decrypt_string( $encrypted_string, $key ) {
	$iv_length = vsc_backup_crypt_iv_length();
	$key       = substr( sha1( $key, true ), 0, $iv_length );
	$iv        = substr( $encrypted_string, 0, $iv_length );

	$decrypted_string = openssl_decrypt( substr( $encrypted_string, $iv_length ), VSC_BACKUP_CIPHER_NAME, $key, OPENSSL_RAW_DATA, $iv );
	if ( $decrypted_string === false ) {
		throw new VSC_Backup_Not_Decryptable_Exception( __( 'Unable to decrypt data.', VSC_BACKUP_PLUGIN_NAME ) );
	}

	return $decrypted_string;
}

/**
 * Checks if decryption password is valid
 *
 * @param string $encrypted_signature
 * @param string $password
 * @return bool
 */
function vsc_backup_is_decryption_password_valid( $encrypted_signature, $password ) {
	try {
		$encrypted_signature = base64_decode( $encrypted_signature );

		return vsc_backup_decrypt_string( $encrypted_signature, $password ) === VSC_BACKUP_SIGN_TEXT;
	} catch ( VSC_Backup_Not_Decryptable_Exception $exception ) {
		return false;
	}
}
