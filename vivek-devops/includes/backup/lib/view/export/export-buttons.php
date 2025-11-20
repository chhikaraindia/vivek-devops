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
	console.log('VSC Backup: Dropdown toggle script initialized');

	// Toggle dropdown when clicking "Export To" button
	$('.ai1wm-button-main').on('click', function(e) {
		e.preventDefault();
		console.log('VSC Backup: Export To button clicked');
		var $parent = $(this).closest('.ai1wm-button-group');
		console.log('VSC Backup: Parent has ai1wm-open:', $parent.hasClass('ai1wm-open'));
		$parent.toggleClass('ai1wm-open');
		console.log('VSC Backup: After toggle, has ai1wm-open:', $parent.hasClass('ai1wm-open'));
	});

	// Check if export file button exists in dropdown
	console.log('VSC Backup: Export file button exists:', $('#ai1wm-export-file').length > 0);
	console.log('VSC Backup: Checking JavaScript objects...');
	console.log('VSC Backup: typeof ai1wm_export:', typeof ai1wm_export);
	console.log('VSC Backup: typeof ai1wm_locale:', typeof ai1wm_locale);

	if (typeof ai1wm_export !== 'undefined') {
		console.log('VSC Backup: ai1wm_export object:', ai1wm_export);
	} else {
		console.error('VSC Backup ERROR: ai1wm_export object not found!');
	}

	if (typeof ai1wm_locale !== 'undefined') {
		console.log('VSC Backup: ai1wm_locale object:', ai1wm_locale);
	} else {
		console.error('VSC Backup ERROR: ai1wm_locale object not found!');
	}
});
</script>
