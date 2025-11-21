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

<div class="vsc-migration-import-messages"></div>

<div class="vsc-migration-import-form">
	<div class="hide-if-no-js">
		<div class="vsc-migration-drag-drop-area" id="vsc-migration-drag-drop-area">
			<div id="vsc-migration-import-init">
				<p>
					<i class="vsc-migration-icon-cloud-upload"></i><br />
					<?php esc_html_e( 'Drag & Drop a backup to import it', 'vivek-devops' ); ?>
				</p>
				<div class="vsc-migration-button-group vsc-migration-button-import vsc-migration-expandable">
					<div class="vsc-migration-button-main">
						<span role="list" aria-label="<?php esc_attr_e( 'Import From', 'vivek-devops' ); ?>"><?php esc_html_e( 'Import From', 'vivek-devops' ); ?></span>
						<span class="ai1mw-lines">
							<span class="vsc-migration-line vsc-migration-line-first"></span>
							<span class="vsc-migration-line vsc-migration-line-second"></span>
							<span class="vsc-migration-line vsc-migration-line-third"></span>
						</span>
					</div>
					<ul class="vsc-migration-dropdown-menu vsc-migration-import-providers">
						<?php foreach ( apply_filters( 'vsc_migration_import_buttons', array() ) as $button ) : ?>
							<li>
								<?php echo wp_kses( $button, vsc_migration_allowed_html_tags() ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>

<p style="margin: 0;"><?php echo wp_kses( apply_filters( 'vsc_migration_pro', '' ), vsc_migration_allowed_html_tags() ); ?></p>
