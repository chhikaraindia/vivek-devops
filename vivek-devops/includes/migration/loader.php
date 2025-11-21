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

// Include all the files that you want to load in here
if ( defined( 'WP_CLI' ) ) {
	require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/command/class-vsc-migration-wp-cli-command.php';
}

require_once VSC_MIGRATION_VENDOR_PATH . '/bandar/bandar/lib/Bandar.php';
require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/cron/class-vsc-migration-cron.php';

require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/filesystem/class-vsc-migration-directory.php';
require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/filesystem/class-vsc-migration-file-htaccess.php';
require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/filesystem/class-vsc-migration-file-index.php';
require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/filesystem/class-vsc-migration-file-robots.php';
require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/filesystem/class-vsc-migration-file-webconfig.php';
require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/filesystem/class-vsc-migration-file.php';

require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/filter/class-vsc-migration-recursive-exclude-filter.php';
require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/filter/class-vsc-migration-recursive-extension-filter.php';

require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/iterator/class-vsc-migration-recursive-directory-iterator.php';
require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/iterator/class-vsc-migration-recursive-iterator-iterator.php';

require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/archiver/class-vsc-migration-archiver.php';
require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/archiver/class-vsc-migration-compressor.php';
require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/archiver/class-vsc-migration-extractor.php';

require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/database/class-vsc-migration-database.php';
require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/database/class-vsc-migration-database-mysql.php';
require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/database/class-vsc-migration-database-mysqli.php';
require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/database/class-vsc-migration-database-sqlite.php';
require_once VSC_MIGRATION_VENDOR_PATH . '/servmask/database/class-vsc-migration-database-utility.php';

require_once VSC_MIGRATION_CONTROLLER_PATH . '/class-vsc-migration-backups-controller.php';
require_once VSC_MIGRATION_CONTROLLER_PATH . '/class-vsc-migration-export-controller.php';
require_once VSC_MIGRATION_CONTROLLER_PATH . '/class-vsc-migration-feedback-controller.php';
require_once VSC_MIGRATION_CONTROLLER_PATH . '/class-vsc-migration-import-controller.php';
require_once VSC_MIGRATION_CONTROLLER_PATH . '/class-vsc-migration-main-controller.php';
require_once VSC_MIGRATION_CONTROLLER_PATH . '/class-vsc-migration-reset-controller.php';
require_once VSC_MIGRATION_CONTROLLER_PATH . '/class-vsc-migration-schedules-controller.php';
require_once VSC_MIGRATION_CONTROLLER_PATH . '/class-vsc-migration-status-controller.php';
require_once VSC_MIGRATION_CONTROLLER_PATH . '/class-vsc-migration-updater-controller.php';

require_once VSC_MIGRATION_MODEL_PATH . '/export/class-vsc-migration-export-archive.php';
require_once VSC_MIGRATION_MODEL_PATH . '/export/class-vsc-migration-export-clean.php';
require_once VSC_MIGRATION_MODEL_PATH . '/export/class-vsc-migration-export-compatibility.php';
require_once VSC_MIGRATION_MODEL_PATH . '/export/class-vsc-migration-export-config-file.php';
require_once VSC_MIGRATION_MODEL_PATH . '/export/class-vsc-migration-export-config.php';
require_once VSC_MIGRATION_MODEL_PATH . '/export/class-vsc-migration-export-content.php';
require_once VSC_MIGRATION_MODEL_PATH . '/export/class-vsc-migration-export-database-file.php';
require_once VSC_MIGRATION_MODEL_PATH . '/export/class-vsc-migration-export-database.php';
require_once VSC_MIGRATION_MODEL_PATH . '/export/class-vsc-migration-export-download.php';
require_once VSC_MIGRATION_MODEL_PATH . '/export/class-vsc-migration-export-enumerate-content.php';
require_once VSC_MIGRATION_MODEL_PATH . '/export/class-vsc-migration-export-enumerate-media.php';
require_once VSC_MIGRATION_MODEL_PATH . '/export/class-vsc-migration-export-enumerate-plugins.php';
require_once VSC_MIGRATION_MODEL_PATH . '/export/class-vsc-migration-export-enumerate-tables.php';
require_once VSC_MIGRATION_MODEL_PATH . '/export/class-vsc-migration-export-enumerate-themes.php';
require_once VSC_MIGRATION_MODEL_PATH . '/export/class-vsc-migration-export-init.php';
require_once VSC_MIGRATION_MODEL_PATH . '/export/class-vsc-migration-export-media.php';
require_once VSC_MIGRATION_MODEL_PATH . '/export/class-vsc-migration-export-plugins.php';
require_once VSC_MIGRATION_MODEL_PATH . '/export/class-vsc-migration-export-themes.php';

require_once VSC_MIGRATION_MODEL_PATH . '/import/class-vsc-migration-import-blogs.php';
require_once VSC_MIGRATION_MODEL_PATH . '/import/class-vsc-migration-import-check-decryption-password.php';
require_once VSC_MIGRATION_MODEL_PATH . '/import/class-vsc-migration-import-check-encryption.php';
require_once VSC_MIGRATION_MODEL_PATH . '/import/class-vsc-migration-import-clean.php';
require_once VSC_MIGRATION_MODEL_PATH . '/import/class-vsc-migration-import-compatibility.php';
require_once VSC_MIGRATION_MODEL_PATH . '/import/class-vsc-migration-import-confirm.php';
require_once VSC_MIGRATION_MODEL_PATH . '/import/class-vsc-migration-import-content.php';
require_once VSC_MIGRATION_MODEL_PATH . '/import/class-vsc-migration-import-database.php';
require_once VSC_MIGRATION_MODEL_PATH . '/import/class-vsc-migration-import-done.php';
require_once VSC_MIGRATION_MODEL_PATH . '/import/class-vsc-migration-import-enumerate.php';
require_once VSC_MIGRATION_MODEL_PATH . '/import/class-vsc-migration-import-mu-plugins.php';
require_once VSC_MIGRATION_MODEL_PATH . '/import/class-vsc-migration-import-options.php';
require_once VSC_MIGRATION_MODEL_PATH . '/import/class-vsc-migration-import-permalinks.php';
require_once VSC_MIGRATION_MODEL_PATH . '/import/class-vsc-migration-import-upload.php';
require_once VSC_MIGRATION_MODEL_PATH . '/import/class-vsc-migration-import-users.php';
require_once VSC_MIGRATION_MODEL_PATH . '/import/class-vsc-migration-import-validate.php';

require_once VSC_MIGRATION_MODEL_PATH . '/class-vsc-migration-backups.php';
require_once VSC_MIGRATION_MODEL_PATH . '/class-vsc-migration-compatibility.php';
require_once VSC_MIGRATION_MODEL_PATH . '/class-vsc-migration-deprecated.php';
require_once VSC_MIGRATION_MODEL_PATH . '/class-vsc-migration-extensions.php';
require_once VSC_MIGRATION_MODEL_PATH . '/class-vsc-migration-feedback.php';
require_once VSC_MIGRATION_MODEL_PATH . '/class-vsc-migration-handler.php';
require_once VSC_MIGRATION_MODEL_PATH . '/class-vsc-migration-log.php';
require_once VSC_MIGRATION_MODEL_PATH . '/class-vsc-migration-message.php';
require_once VSC_MIGRATION_MODEL_PATH . '/class-vsc-migration-notification.php';
require_once VSC_MIGRATION_MODEL_PATH . '/class-vsc-migration-status.php';
require_once VSC_MIGRATION_MODEL_PATH . '/class-vsc-migration-template.php';
require_once VSC_MIGRATION_MODEL_PATH . '/class-vsc-migration-updater.php';
