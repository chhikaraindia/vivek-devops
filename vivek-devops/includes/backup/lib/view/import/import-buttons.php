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

<div class="ai1wm-import-messages"></div>

<div class="ai1wm-import-form">
	<div class="hide-if-no-js">
		<div class="ai1wm-drag-drop-area" id="ai1wm-drag-drop-area">
			<div id="ai1wm-import-init">
				<p>
					<i class="ai1wm-icon-cloud-upload"></i><br />
					<?php _e( 'Drag & Drop a backup to import it', VSC_BACKUP_PLUGIN_NAME ); ?>
				</p>
				<div class="ai1wm-button-group ai1wm-button-import ai1wm-expandable">
					<div class="ai1wm-button-main">
						<span role="list" aria-label="<?php _e( 'Import From', VSC_BACKUP_PLUGIN_NAME ); ?>"><?php _e( 'Import From', VSC_BACKUP_PLUGIN_NAME ); ?></span>
						<span class="ai1mw-lines">
							<span class="ai1wm-line ai1wm-line-first"></span>
							<span class="ai1wm-line ai1wm-line-second"></span>
							<span class="ai1wm-line ai1wm-line-third"></span>
						</span>
					</div>
					<ul class="ai1wm-dropdown-menu ai1wm-import-providers">
						<?php
						$buttons = apply_filters( 'vsc_backup_import_buttons', array() );
						if (empty($buttons)) {
							// Fallback: Add file import button directly
							?>
							<li>
								<label id="ai1wm-import-file" for="ai1wm-select-file">
									<i class="ai1wm-icon-file"></i> File
									<input id="ai1wm-select-file" type="file" name="file" style="display:none;">
								</label>
							</li>
							<?php
						} else {
							foreach ( $buttons as $button ) :
						?>
							<li>
								<?php echo $button; ?>
							</li>
						<?php
							endforeach;
						}
						?>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>

<p style="margin: 0;"><?php echo apply_filters( 'vsc_backup_pro', '' ); ?></p>
