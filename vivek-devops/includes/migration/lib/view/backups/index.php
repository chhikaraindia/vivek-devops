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
					<?php esc_html_e( 'Backups', 'vivek-devops' ); ?>
				</h1>

				<?php if ( is_readable( VSC_MIGRATION_BACKUPS_PATH ) && is_writable( VSC_MIGRATION_BACKUPS_PATH ) ) : ?>
					<div id="vsc-migration-backups-list">
						<?php require_once VSC_MIGRATION_TEMPLATES_PATH . '/backups/backups-list.php'; ?>
					</div>

					<form action="" method="post" id="vsc-migration-export-form" class="vsc-migration-clear">
						<div id="vsc-migration-backups-create">
							<p class="vsc-migration-backups-empty-spinner-holder vsc-migration-hide">
								<span class="spinner"></span>
								<?php esc_html_e( 'Refreshing backup list...', 'vivek-devops' ); ?>
							</p>
							<p class="vsc-migration-backups-empty <?php echo empty( $backups ) ? null : 'vsc-migration-hide'; ?>">
								<?php esc_html_e( 'No backups found. Create a new one?', 'vivek-devops' ); ?>
							</p>
							<p>
								<a href="#" id="vsc-migration-create-backup" class="vsc-migration-button-green">
									<i class="vsc-migration-icon-export"></i>
									<?php esc_html_e( 'Create backup', 'vivek-devops' ); ?>
								</a>
							</p>
						</div>
						<input type="hidden" name="vsc_migration_manual_export" value="1" />
					</form>

					<?php do_action( 'vsc_migration_backups_left_end' ); ?>

				<?php else : ?>

					<?php require_once VSC_MIGRATION_TEMPLATES_PATH . '/backups/backups-permissions.php'; ?>

				<?php endif; ?>
			</div>

			<div id="vsc-migration-backups-list-archive-browser">
				<archive-browser></archive-browser>
			</div>

		</div>

		<?php require_once VSC_MIGRATION_TEMPLATES_PATH . '/common/sidebar-right.php'; ?>

	</div>
</div>
