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

<div class="vsc-migration-field-set">
	<div class="vsc-migration-accordion vsc-migration-expandable">
		<h4>
			<i class="vsc-migration-icon-arrow-right"></i>
			<?php esc_html_e( 'Advanced options', 'vivek-devops' ); ?>
			<small><?php esc_html_e( '(click to expand)', 'vivek-devops' ); ?></small>
		</h4>
		<ul>
			<li><strong><?php esc_html_e( 'Security Options' ); ?></strong></li>
			<?php if ( vsc_migration_can_encrypt() ) : ?>
				<li class="vsc-migration-encrypt-backups-container">
					<label for="vsc-migration-encrypt-backups">
						<input type="checkbox" id="vsc-migration-encrypt-backups" name="options[encrypt_backups]" />
						<?php esc_html_e( 'Encrypt this backup with a password', 'vivek-devops' ); ?>
					</label>
					<div class="vsc-migration-encrypt-backups-passwords-toggle">
						<div class="vsc-migration-encrypt-backups-passwords-container">
							<div class="vsc-migration-input-password-container">
								<input type="password" placeholder="<?php esc_attr_e( 'Enter a password', 'vivek-devops' ); ?>" name="options[encrypt_password]" id="vsc-migration-backup-encrypt-password">
								<a href="#vsc-migration-backup-encrypt-password" class="vsc-migration-toggle-password-visibility vsc-migration-icon-eye-blocked"></a>
								<div class="vsc-migration-error-message"><?php esc_html_e( 'A password is required', 'vivek-devops' ); ?></div>
							</div>
							<div class="vsc-migration-input-password-container">
								<input type="password" name="options[encrypt_password_confirmation]" placeholder="<?php esc_attr_e( 'Repeat the password', 'vivek-devops' ); ?>" id="vsc-migration-backup-encrypt-password-confirmation">
								<a href="#vsc-migration-backup-encrypt-password-confirmation" class="vsc-migration-toggle-password-visibility vsc-migration-icon-eye-blocked"></a>
								<div class="vsc-migration-error-message"><?php esc_html_e( 'The passwords do not match', 'vivek-devops' ); ?></div>
							</div>
						</div>
					</div>
				</li>
			<?php else : ?>
				<li class="vsc-migration-encrypt-backups-container-disabled">
					<input type="checkbox" id="vsc-migration-encrypt-backups" name="options[encrypt_backups]" disabled />
					<?php esc_html_e( 'Password-protect and encrypt backups', 'vivek-devops' ); ?>
					<a href="https://help.servmask.com/knowledgebase/unable-to-encrypt-and-decrypt-backups/" target="_blank"><span class="vsc-migration-icon-help"></span></a>
				</li>
			<?php endif; ?>

			<li><strong><?php esc_html_e( 'Database Options' ); ?></strong></li>
			<li>
				<label for="vsc-migration-no-spam-comments">
					<input type="checkbox" id="vsc-migration-no-spam-comments" name="options[no_spam_comments]" />
					<?php esc_html_e( 'Exclude spam comments', 'vivek-devops' ); ?>
				</label>
			</li>
			<li>
				<label for="vsc-migration-no-post-revisions">
					<input type="checkbox" id="vsc-migration-no-post-revisions" name="options[no_post_revisions]" />
					<?php esc_html_e( 'Exclude post revisions', 'vivek-devops' ); ?>
				</label>
			</li>
			<li>
				<label for="vsc-migration-no-database">
					<input type="checkbox" id="vsc-migration-no-database" name="options[no_database]" />
					<?php esc_html_e( 'Exclude database', 'vivek-devops' ); ?>
				</label>
			</li>
			<li>
				<label for="vsc-migration-no-email-replace">
					<input type="checkbox" id="vsc-migration-no-email-replace" name="options[no_email_replace]" />
					<?php
					echo wp_kses(
						__( 'Do <strong>not</strong> replace email domain', 'vivek-devops' ),
						vsc_migration_allowed_html_tags()
					);
					?>
				</label>
			</li>

			<?php do_action( 'vsc_migration_export_exclude_db_tables' ); ?>

			<?php do_action( 'vsc_migration_export_include_db_tables' ); ?>

			<li><strong><?php esc_html_e( 'File Options' ); ?></strong></li>
			<li>
				<label for="vsc-migration-no-media">
					<input type="checkbox" id="vsc-migration-no-media" name="options[no_media]" />
					<?php esc_html_e( 'Exclude media library', 'vivek-devops' ); ?>
				</label>
			</li>
			<li>
				<label for="vsc-migration-no-themes">
					<input type="checkbox" id="vsc-migration-no-themes" name="options[no_themes]" />
					<?php esc_html_e( 'Exclude themes', 'vivek-devops' ); ?>
				</label>
			</li>

			<?php do_action( 'vsc_migration_export_inactive_themes' ); ?>

			<li>
				<label for="vsc-migration-no-muplugins">
					<input type="checkbox" id="vsc-migration-no-muplugins" name="options[no_muplugins]" />
					<?php esc_html_e( 'Exclude must-use plugins', 'vivek-devops' ); ?>
				</label>
			</li>

			<li>
				<label for="vsc-migration-no-plugins">
					<input type="checkbox" id="vsc-migration-no-plugins" name="options[no_plugins]" />
					<?php esc_html_e( 'Exclude plugins', 'vivek-devops' ); ?>
				</label>
			</li>

			<?php do_action( 'vsc_migration_export_inactive_plugins' ); ?>

			<?php do_action( 'vsc_migration_export_cache_files' ); ?>

			<?php do_action( 'vsc_migration_export_advanced_settings' ); ?>

		</ul>
	</div>
</div>
