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
?>

<div class="ai1wm-container">
	<div class="ai1wm-row">
		<div class="ai1wm-left">
			<div class="ai1wm-holder">
				<h1>
					<i class="ai1wm-icon-publish"></i>
					<?php _e( 'Import Site', VSC_BACKUP_PLUGIN_NAME ); ?>
				</h1>

				<?php if ( is_readable( VSC_BACKUP_STORAGE_PATH ) && is_writable( VSC_BACKUP_STORAGE_PATH ) ) : ?>

					<form action="" method="post" id="ai1wm-import-form" class="ai1wm-clear" enctype="multipart/form-data">

						<?php do_action( 'vsc_backup_import_left_options' ); ?>

						<?php include VSC_BACKUP_TEMPLATES_PATH . '/import/import-buttons.php'; ?>

						<input type="hidden" name="vsc_backup_manual_import" value="1" />

					</form>

					<?php do_action( 'vsc_backup_import_left_end' ); ?>

				<?php else : ?>

					<?php include VSC_BACKUP_TEMPLATES_PATH . '/import/import-permissions.php'; ?>

				<?php endif; ?>
			</div>
		</div>

		<?php include VSC_BACKUP_TEMPLATES_PATH . '/common/sidebar-right.php'; ?>

	</div>
</div>
