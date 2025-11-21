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

<div class="vsc-migration-feedback">
	<ul class="vsc-migration-feedback-types">
		<li>
			<input type="radio" class="vsc-migration-flat-radio-button vsc-migration-feedback-type" id="vsc-migration-feedback-type-1" name="vsc_migration_feedback_type" value="suggestions" />
			<a id="vsc-migration-feedback-type-link-1" href="https://feedback.wp-migration.com" target="_blank">
				<i></i>
				<span>
					<?php esc_html_e( 'I have an idea', 'vivek-devops' ); ?>
					<svg style="width: 14px; position: relative; top: 3px; left: 3px;" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
				</span>
			</a>
		</li>
		<li>
			<input type="radio" class="vsc-migration-flat-radio-button vsc-migration-feedback-type" id="vsc-migration-feedback-type-2" name="vsc_migration_feedback_type" value="help-needed" />
			<label for="vsc-migration-feedback-type-2">
				<i></i>
				<span><?php esc_html_e( 'I need help', 'vivek-devops' ); ?></span>
			</label>
		</li>
	</ul>

	<div class="vsc-migration-feedback-form">
		<div class="vsc-migration-field">
			<input placeholder="<?php esc_attr_e( 'Email address', 'vivek-devops' ); ?>" type="text" id="vsc-migration-feedback-email" class="vsc-migration-feedback-email" />
		</div>
		<div class="vsc-migration-field">
			<textarea rows="3" id="vsc-migration-feedback-message" class="vsc-migration-feedback-message" placeholder="<?php esc_attr_e( 'Describe your issue or feedback...', 'vivek-devops' ); ?>"></textarea>
		</div>
		<div class="vsc-migration-field vsc-migration-feedback-terms-segment">
			<label for="vsc-migration-feedback-terms">
				<input type="checkbox" class="vsc-migration-feedback-terms" id="vsc-migration-feedback-terms" />
				<?php echo wp_kses( __( 'I agree to let All-in-One WP Migration use my <strong>email</strong> to respond to my request.', 'vivek-devops' ), vsc_migration_allowed_html_tags() ); ?>
			</label>
		</div>
		<div class="vsc-migration-field">
			<div class="vsc-migration-buttons">
				<a class="vsc-migration-feedback-cancel" id="vsc-migration-feedback-cancel" href="#"><?php esc_html_e( 'Cancel', 'vivek-devops' ); ?></a>
				<button type="submit" id="vsc-migration-feedback-submit" class="vsc-migration-button-blue vsc-migration-form-submit">
					<i class="vsc-migration-icon-paperplane"></i>
					<?php esc_html_e( 'Send', 'vivek-devops' ); ?>
				</button>
				<span class="spinner"></span>
				<div class="vsc-migration-clear"></div>
			</div>
		</div>
	</div>
</div>
