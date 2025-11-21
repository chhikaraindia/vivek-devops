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

<ul id="vsc-migration-queries">
	<li class="vsc-migration-query vsc-migration-expandable">
		<p>
			<span>
				<strong><?php esc_html_e( 'Search for', 'vivek-devops' ); ?></strong>
				<small class="vsc-migration-query-find-text vsc-migration-tooltip" title="Search the database for this text"><?php echo esc_html( __( '<text>', 'vivek-devops' ) ); ?></small>
				<strong><?php esc_html_e( 'Replace with', 'vivek-devops' ); ?></strong>
				<small class="vsc-migration-query-replace-text vsc-migration-tooltip" title="Replace the database with this text"><?php echo esc_html( __( '<another-text>', 'vivek-devops' ) ); ?></small>
				<strong><?php esc_html_e( 'in the database', 'vivek-devops' ); ?></strong>
			</span>
			<span class="vsc-migration-query-arrow vsc-migration-icon-chevron-right"></span>
		</p>
		<div>
			<input class="vsc-migration-query-find-input" type="text" placeholder="<?php esc_attr_e( 'Search for', 'vivek-devops' ); ?>" name="options[replace][old_value][]" />
			<input class="vsc-migration-query-replace-input" type="text" placeholder="<?php esc_attr_e( 'Replace with', 'vivek-devops' ); ?>" name="options[replace][new_value][]" />
		</div>
	</li>
</ul>

<button type="button" class="vsc-migration-button-gray" id="vsc-migration-add-new-replace-button">
	<i class="vsc-migration-icon-plus2"></i>
	<?php esc_html_e( 'Add', 'vivek-devops' ); ?>
</button>
