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

<div class="ai1wm-field-set">
	<div class="ai1wm-buttons">
		<div class="ai1wm-button-group ai1wm-button-export ai1wm-expandable">
			<div class="ai1wm-button-main">
				<span role="list" aria-label="<?php _e( 'Export To', VSC_BACKUP_PLUGIN_NAME ); ?>"><?php _e( 'Export To', VSC_BACKUP_PLUGIN_NAME ); ?></span>
				<span class="ai1mw-lines">
					<span class="ai1wm-line ai1wm-line-first"></span>
					<span class="ai1wm-line ai1wm-line-second"></span>
					<span class="ai1wm-line ai1wm-line-third"></span>
				</span>
			</div>
			<ul class="ai1wm-dropdown-menu ai1wm-export-providers">
				<?php
				$buttons = apply_filters( 'vsc_backup_export_buttons', array() );
				if (empty($buttons)) {
					// Fallback: Add file export button directly
					echo '<li><a href="#" id="ai1wm-export-file">File</a></li>';
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

<script type="text/javascript">
jQuery(document).ready(function($) {
	console.log('VSC Backup: jQuery loaded');
	console.log('VSC Backup: Looking for export button #ai1wm-export-file');
	console.log('VSC Backup: Button exists:', $('#ai1wm-export-file').length > 0);

	// Add click handler for debugging
	$(document).on('click', '#ai1wm-export-file', function(e) {
		console.log('VSC Backup: Export button clicked!');
		console.log('VSC Backup: typeof vsc_backup_export:', typeof vsc_backup_export);
		if (typeof vsc_backup_export !== 'undefined') {
			console.log('VSC Backup: vsc_backup_export object:', vsc_backup_export);
		} else {
			console.error('VSC Backup ERROR: vsc_backup_export object not found! Scripts not loaded properly.');
			alert('DEBUG: Export scripts not loaded. Check console for details.');
		}
	});
});
</script>
