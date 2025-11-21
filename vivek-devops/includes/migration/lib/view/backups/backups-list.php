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

<?php if ( $backups ) : ?>
	<form action="" method="post" id="vsc-migration-backups-form" class="vsc-migration-clear">
		<table class="vsc-migration-backups">
			<thead>
				<tr>
					<th class="vsc-migration-column-name"><?php esc_html_e( 'Name', 'vivek-devops' ); ?></th>
					<th class="vsc-migration-column-date"><?php esc_html_e( 'Date', 'vivek-devops' ); ?></th>
					<th class="vsc-migration-column-size"><?php esc_html_e( 'Size', 'vivek-devops' ); ?></th>
					<th class="vsc-migration-column-actions"></th>
				</tr>
			</thead>
			<tbody>
				<tr class="vsc-migration-backups-list-spinner-holder vsc-migration-hide">
					<td colspan="4" class="vsc-migration-backups-list-spinner">
						<span class="spinner"></span>
						<?php esc_html_e( 'Refreshing backup list...', 'vivek-devops' ); ?>
					</td>
				</tr>

				<?php foreach ( $backups as $backup ) : ?>
				<tr>
					<td class="vsc-migration-column-name">
						<?php if ( ! empty( $backup['path'] ) ) : ?>
							<i class="vsc-migration-icon-folder"></i>
							<?php echo esc_html( $backup['path'] ); ?>
							<br />
						<?php endif; ?>
						<i class="vsc-migration-icon-file-zip"></i>
						<span class="vsc-migration-backup-filename">
							<?php echo esc_html( basename( $backup['filename'] ) ); ?>
						</span>
						<span class="vsc-migration-backup-label-description vsc-migration-hide <?php echo empty( $labels[ $backup['filename'] ] ) ? null : 'vsc-migration-backup-label-selected'; ?>">
							<br />
							<?php esc_html_e( 'Click to label this backup', 'vivek-devops' ); ?>
							<i class="vsc-migration-icon-edit-pencil vsc-migration-hide"></i>
						</span>
						<span class="vsc-migration-backup-label-text <?php echo empty( $labels[ $backup['filename'] ] ) ? 'vsc-migration-hide' : null; ?>">
							<br />
							<span class="vsc-migration-backup-label-colored">
								<?php if ( ! empty( $labels[ $backup['filename'] ] ) ) : ?>
									<?php echo esc_html( $labels[ $backup['filename'] ] ); ?>
								<?php endif; ?>
							</span>
							<i class="vsc-migration-icon-edit-pencil vsc-migration-hide"></i>
						</span>
						<span class="vsc-migration-backup-label-holder vsc-migration-hide">
							<br />
							<input type="text" class="vsc-migration-backup-label-field" data-archive="<?php echo esc_attr( $backup['filename'] ); ?>" data-value="<?php echo empty( $labels[ $backup['filename'] ] ) ? null : esc_attr( $labels[ $backup['filename'] ] ); ?>" value="<?php echo empty( $labels[ $backup['filename'] ] ) ? null : esc_attr( $labels[ $backup['filename'] ] ); ?>" />
						</span>
					</td>
					<td class="vsc-migration-column-date">
						<?php echo esc_html( sprintf( /* translators: Human time diff */ __( '%s ago', 'vivek-devops' ), human_time_diff( $backup['mtime'] ) ) ); ?>
					</td>
					<td class="vsc-migration-column-size">
						<?php if ( ! is_null( $backup['size'] ) ) : ?>
							<?php echo esc_html( vsc_migration_size_format( $backup['size'], 2 ) ); ?>
						<?php else : ?>
							<?php esc_html_e( 'Over 2GB', 'vivek-devops' ); ?>
						<?php endif; ?>
					</td>
					<td class="vsc-migration-column-actions vsc-migration-backup-actions">
						<div>
							<a href="#" role="menu" aria-haspopup="true" class="vsc-migration-backup-dots" title="<?php esc_attr_e( 'More', 'vivek-devops' ); ?>" aria-label="<?php esc_attr_e( 'More', 'vivek-devops' ); ?>">
								<i class="vsc-migration-icon-dots-horizontal-triple"></i>
							</a>
							<div class="vsc-migration-backup-dots-menu">
								<ul role="menu">
									<li>
										<a tabindex="-1" href="#" role="menuitem" class="vsc-migration-backup-restore" data-archive="<?php echo esc_attr( $backup['filename'] ); ?>" data-size="<?php echo esc_attr( $backup['size'] ); ?>" aria-label="<?php esc_attr_e( 'Restore', 'vivek-devops' ); ?>">
											<i class="vsc-migration-icon-cloud-upload"></i>
											<span><?php esc_html_e( 'Restore', 'vivek-devops' ); ?></span>
										</a>
									</li>
									<?php if ( $downloadable ) : ?>
										<li>
											<?php if ( vsc_migration_direct_download_supported() ) : ?>
												<a tabindex="-1" href="<?php echo esc_url( vsc_migration_backup_url( array( 'archive' => $backup['filename'] ) ) ); ?>" role="menuitem" download="<?php echo esc_attr( $backup['filename'] ); ?>" aria-label="<?php esc_attr_e( 'Download', 'vivek-devops' ); ?>">
													<i class="vsc-migration-icon-arrow-down"></i>
													<?php esc_html_e( 'Download', 'vivek-devops' ); ?>
												</a>
											<?php else : ?>
												<a tabindex="-1" class="vsc-migration-backup-download" href="#" role="menuitem" download="<?php echo esc_attr( $backup['filename'] ); ?>" aria-label="<?php esc_attr_e( 'Download', 'vivek-devops' ); ?>">
													<i class="vsc-migration-icon-arrow-down"></i>
													<?php esc_html_e( 'Download', 'vivek-devops' ); ?>
												</a>
											<?php endif; ?>
										</li>
									<?php else : ?>
										<li class="vsc-migration-disabled">
											<a tabindex="-1" href="#" role="menuitem" aria-label="<?php esc_attr_e( 'Downloading is not possible because backups directory is not accessible.', 'vivek-devops' ); ?>" title="<?php esc_attr_e( 'Downloading is not possible because backups directory is not accessible.', 'vivek-devops' ); ?>">
												<i class="vsc-migration-icon-arrow-down"></i>
												<?php esc_html_e( 'Download', 'vivek-devops' ); ?>
											</a>
										</li>
									<?php endif; ?>
									<li>
										<a tabindex="-1" href="#" class="vsc-migration-backup-list-content" data-archive="<?php echo esc_attr( $backup['filename'] ); ?>" role="menuitem" aria-label="<?php esc_attr_e( 'Show backup content', 'vivek-devops' ); ?>">
											<i class="vsc-migration-icon-file-content"></i>
											<span><?php esc_html_e( 'List', 'vivek-devops' ); ?></span>
										</a>
									</li>
									<li class="divider"></li>
									<li>
										<a tabindex="-1" href="#" class="vsc-migration-backup-delete" data-archive="<?php echo esc_attr( $backup['filename'] ); ?>" role="menuitem" aria-label="<?php esc_attr_e( 'Delete', 'vivek-devops' ); ?>">
											<i class="vsc-migration-icon-close"></i>
											<span><?php esc_html_e( 'Delete', 'vivek-devops' ); ?></span>
										</a>
									</li>
								</ul>
							</div>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<input type="hidden" name="vsc_migration_manual_restore" value="1" />
	</form>
<?php endif; ?>
