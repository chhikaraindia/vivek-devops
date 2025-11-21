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
?>

<div class="vsc-migration-container">
	<div class="vsc-migration-row">
		<div class="vsc-migration-left">
			<div class="vsc-migration-holder">
				<h1>
					<i class="vsc-migration-icon-export"></i>
					<?php esc_html_e( 'Export Site', 'vivek-devops' ); ?>
				</h1>

				<?php if ( is_readable( VSC_MIGRATION_STORAGE_PATH ) && is_writable( VSC_MIGRATION_STORAGE_PATH ) ) : ?>

					<form action="" method="post" id="vsc-migration-export-form" class="vsc-migration-clear">

						<?php require_once VSC_MIGRATION_TEMPLATES_PATH . '/export/find-replace.php'; ?>

						<?php do_action( 'vsc_migration_export_left_options' ); ?>

						<?php require_once VSC_MIGRATION_TEMPLATES_PATH . '/export/advanced-settings.php'; ?>

						<?php require_once VSC_MIGRATION_TEMPLATES_PATH . '/export/export-buttons.php'; ?>

						<input type="hidden" name="vsc_migration_manual_export" value="1" />

					</form>

					<?php do_action( 'vsc_migration_export_left_end' ); ?>

				<?php else : ?>

					<?php require_once VSC_MIGRATION_TEMPLATES_PATH . '/export/export-permissions.php'; ?>

				<?php endif; ?>
			</div>
		</div>

		<?php require_once VSC_MIGRATION_TEMPLATES_PATH . '/common/sidebar-right.php'; ?>

	</div>
</div>
