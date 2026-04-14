<?php
/**
 * MonirNews Pro — Ad Manager list view.
 *
 * @package MonirNews_Pro
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Status filter.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$filter_status = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';

// Pagination.
$per_page = 20;
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$paged    = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$offset   = ( $paged - 1 ) * $per_page;
$total    = MNP_Database::count_ads( array( 'status' => $filter_status ) );
$pages    = $total > 0 ? (int) ceil( $total / $per_page ) : 1;

$ads = MNP_Database::get_ads( array(
	'status'  => $filter_status,
	'limit'   => $per_page,
	'offset'  => $offset,
	'orderby' => 'created_at',
	'order'   => 'DESC',
) );

// Notice messages.
$messages = array(
	'created' => __( 'Ad created successfully.', 'monirnews-pro' ),
	'updated' => __( 'Ad updated successfully.', 'monirnews-pro' ),
	'deleted' => __( 'Ad deleted.', 'monirnews-pro' ),
);
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$msg_key  = isset( $_GET['message'] ) ? sanitize_key( $_GET['message'] ) : '';
$base_url = admin_url( 'admin.php?page=monirnews-pro-ads' );
?>

<?php if ( isset( $messages[ $msg_key ] ) ) : ?>
	<div class="notice notice-success is-dismissible">
		<p><?php echo esc_html( $messages[ $msg_key ] ); ?></p>
	</div>
<?php endif; ?>

<div class="mnp-card">
	<div class="mnp-card-header">
		<h2><?php esc_html_e( 'Advertisement Manager', 'monirnews-pro' ); ?></h2>
		<a href="<?php echo esc_url( add_query_arg( 'action', 'create', $base_url ) ); ?>"
			class="mnp-btn mnp-btn-primary">
			&#10133; <?php esc_html_e( 'Create New Ad', 'monirnews-pro' ); ?>
		</a>
	</div>

	<!-- Filter tabs -->
	<div style="padding:0 1.5rem;border-bottom:1px solid #f0f0f0;">
		<?php
		$filter_tabs = array(
			''          => __( 'All', 'monirnews-pro' ),
			'active'    => __( 'Active', 'monirnews-pro' ),
			'paused'    => __( 'Paused', 'monirnews-pro' ),
			'scheduled' => __( 'Scheduled', 'monirnews-pro' ),
			'expired'   => __( 'Expired', 'monirnews-pro' ),
		);
		foreach ( $filter_tabs as $tab_status => $tab_label ) :
			$tab_url   = '' === $tab_status ? $base_url : add_query_arg( 'status', $tab_status, $base_url );
			$is_active = $filter_status === $tab_status;
			$count     = MNP_Database::count_ads( array( 'status' => $tab_status ) );
			?>
			<a href="<?php echo esc_url( $tab_url ); ?>"
				style="display:inline-block;padding:10px 16px;text-decoration:none;font-size:0.85rem;font-weight:<?php echo $is_active ? '700' : '500'; ?>;color:<?php echo $is_active ? '#c0392b' : '#555'; ?>;border-bottom:2px solid <?php echo $is_active ? '#c0392b' : 'transparent'; ?>;margin-bottom:-1px;">
				<?php echo esc_html( $tab_label ); ?>
				<span style="background:#eee;color:#666;font-size:0.75rem;padding:1px 6px;border-radius:10px;margin-left:4px;">
					<?php echo esc_html( $count ); ?>
				</span>
			</a>
		<?php endforeach; ?>
	</div>

	<div class="mnp-card-body" style="padding:0;">
		<?php if ( empty( $ads ) ) : ?>
			<div class="mnp-empty">
				<div class="mnp-empty-icon">&#128240;</div>
				<h3>
					<?php
					echo '' !== $filter_status
						/* translators: %s: status label */
						? esc_html( sprintf( __( 'No %s ads found.', 'monirnews-pro' ), $filter_status ) )
						: esc_html__( 'No ads yet.', 'monirnews-pro' );
					?>
				</h3>
				<?php if ( '' === $filter_status ) : ?>
					<a href="<?php echo esc_url( add_query_arg( 'action', 'create', $base_url ) ); ?>"
						class="mnp-btn mnp-btn-primary">
						<?php esc_html_e( 'Create your first ad', 'monirnews-pro' ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<table class="mnp-ads-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Preview', 'monirnews-pro' ); ?></th>
						<th><?php esc_html_e( 'Name', 'monirnews-pro' ); ?></th>
						<th><?php esc_html_e( 'Zone', 'monirnews-pro' ); ?></th>
						<th><?php esc_html_e( 'Type', 'monirnews-pro' ); ?></th>
						<th><?php esc_html_e( 'Start', 'monirnews-pro' ); ?></th>
						<th><?php esc_html_e( 'End', 'monirnews-pro' ); ?></th>
						<th><?php esc_html_e( 'Status', 'monirnews-pro' ); ?></th>
						<th><?php esc_html_e( 'Impr.', 'monirnews-pro' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'monirnews-pro' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $ads as $ad ) : ?>
						<tr>
							<!-- Preview -->
							<td>
								<?php if ( $ad->media_url && in_array( $ad->type, array( 'image', 'gif' ), true ) ) : ?>
									<img src="<?php echo esc_url( $ad->media_url ); ?>"
										class="mnp-ad-thumb"
										alt="<?php echo esc_attr( $ad->name ); ?>">
								<?php elseif ( 'video' === $ad->type && $ad->media_url ) : ?>
									<div class="mnp-ad-thumb-placeholder">&#127916;</div>
								<?php elseif ( 'html' === $ad->type ) : ?>
									<div class="mnp-ad-thumb-placeholder">&#128196;</div>
								<?php else : ?>
									<div class="mnp-ad-thumb-placeholder">&#128240;</div>
								<?php endif; ?>
							</td>

							<!-- Name -->
							<td>
								<strong>
									<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'edit', 'id' => $ad->id ), $base_url ) ); ?>">
										<?php echo esc_html( $ad->name ); ?>
									</a>
								</strong>
								<div style="font-size:0.75rem;color:#999;">
									<?php
									printf(
										/* translators: %dpx x %dpx */
										esc_html__( '%1$d&times;%2$d px', 'monirnews-pro' ),
										(int) $ad->width,
										(int) $ad->height
									);
									?>
								</div>
							</td>

							<!-- Zone -->
							<td><code style="font-size:0.8rem;"><?php echo esc_html( $ad->zone ); ?></code></td>

							<!-- Type -->
							<td><?php echo esc_html( strtoupper( $ad->type ) ); ?></td>

							<!-- Start -->
							<td style="font-size:0.8rem;white-space:nowrap;">
								<?php echo esc_html( substr( $ad->start_date, 0, 10 ) ); ?>
							</td>

							<!-- End -->
							<td style="font-size:0.8rem;white-space:nowrap;color:#999;">
								<?php echo $ad->end_date ? esc_html( substr( $ad->end_date, 0, 10 ) ) : '&mdash;'; ?>
							</td>

							<!-- Status -->
							<td>
								<span class="mnp-status <?php echo esc_attr( $ad->status ); ?>"
									id="mnp-status-<?php echo esc_attr( $ad->id ); ?>">
									<?php echo esc_html( $ad->status ); ?>
								</span>
							</td>

							<!-- Impressions -->
							<td><?php echo esc_html( number_format( (int) $ad->impressions ) ); ?></td>

							<!-- Actions -->
							<td>
								<div class="mnp-table-actions">
									<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'edit', 'id' => $ad->id ), $base_url ) ); ?>"
										class="mnp-btn mnp-btn-secondary mnp-btn-sm">
										<?php esc_html_e( 'Edit', 'monirnews-pro' ); ?>
									</a>

									<?php if ( in_array( $ad->status, array( 'active', 'paused' ), true ) ) : ?>
										<button type="button"
											class="mnp-btn mnp-btn-secondary mnp-btn-sm mnp-toggle-status"
											data-id="<?php echo esc_attr( $ad->id ); ?>">
											<?php echo 'active' === $ad->status
												? esc_html__( 'Pause', 'monirnews-pro' )
												: esc_html__( 'Activate', 'monirnews-pro' ); ?>
										</button>
									<?php endif; ?>

									<form method="post"
										action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
										class="mnp-delete-form"
										style="display:inline;">
										<input type="hidden" name="action"  value="mnp_delete_ad">
										<input type="hidden" name="ad_id"   value="<?php echo esc_attr( $ad->id ); ?>">
										<?php wp_nonce_field( 'mnp_delete_ad_nonce', 'mnp_nonce' ); ?>
										<button type="submit" class="mnp-btn mnp-btn-danger mnp-btn-sm">
											<?php esc_html_e( 'Delete', 'monirnews-pro' ); ?>
										</button>
									</form>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<!-- Pagination -->
			<?php if ( $pages > 1 ) : ?>
				<div style="padding:1rem 1.5rem;display:flex;gap:6px;align-items:center;border-top:1px solid #f0f0f0;">
					<?php for ( $p = 1; $p <= $pages; $p++ ) : ?>
						<a href="<?php echo esc_url( add_query_arg( array( 'status' => $filter_status, 'paged' => $p ), $base_url ) ); ?>"
							class="mnp-btn mnp-btn-sm <?php echo $p === $paged ? 'mnp-btn-primary' : 'mnp-btn-secondary'; ?>">
							<?php echo esc_html( $p ); ?>
						</a>
					<?php endfor; ?>
					<span style="font-size:0.8rem;color:#888;margin-left:8px;">
						<?php
						printf(
							/* translators: %1$d: total count */
							esc_html__( '%1$d ads total', 'monirnews-pro' ),
							(int) $total
						);
						?>
					</span>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>
