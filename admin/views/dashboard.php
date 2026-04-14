<?php
/**
 * MonirNews Pro — Dashboard view.
 *
 * @package MonirNews_Pro
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNP_Database::update_expired_ads();

$counts     = MNP_Database::count_by_status();
$recent_ads = MNP_Database::get_ads( array(
	'status'  => '',
	'limit'   => 5,
	'orderby' => 'created_at',
	'order'   => 'DESC',
) );

$messages = array(
	'created' => __( 'Ad created successfully.', 'monirnews-pro' ),
	'updated' => __( 'Ad updated successfully.', 'monirnews-pro' ),
	'deleted' => __( 'Ad deleted.', 'monirnews-pro' ),
	'saved'   => __( 'Settings saved.', 'monirnews-pro' ),
);

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$msg_key = isset( $_GET['message'] ) ? sanitize_key( $_GET['message'] ) : '';
?>

<?php if ( isset( $messages[ $msg_key ] ) ) : ?>
	<div class="notice notice-success is-dismissible">
		<p><?php echo esc_html( $messages[ $msg_key ] ); ?></p>
	</div>
<?php endif; ?>

<!-- Stats row -->
<div class="mnp-stats-grid">
	<div class="mnp-stat-card">
		<span class="mnp-stat-number"><?php echo esc_html( number_format( $counts['total'] ) ); ?></span>
		<div class="mnp-stat-label"><?php esc_html_e( 'Total Ads', 'monirnews-pro' ); ?></div>
	</div>
	<div class="mnp-stat-card active">
		<span class="mnp-stat-number"><?php echo esc_html( number_format( $counts['active'] ) ); ?></span>
		<div class="mnp-stat-label"><?php esc_html_e( 'Active', 'monirnews-pro' ); ?></div>
	</div>
	<div class="mnp-stat-card">
		<span class="mnp-stat-number"><?php echo esc_html( number_format( $counts['paused'] ) ); ?></span>
		<div class="mnp-stat-label"><?php esc_html_e( 'Paused', 'monirnews-pro' ); ?></div>
	</div>
	<div class="mnp-stat-card scheduled">
		<span class="mnp-stat-number"><?php echo esc_html( number_format( $counts['scheduled'] ) ); ?></span>
		<div class="mnp-stat-label"><?php esc_html_e( 'Scheduled', 'monirnews-pro' ); ?></div>
	</div>
	<div class="mnp-stat-card expired">
		<span class="mnp-stat-number"><?php echo esc_html( number_format( $counts['expired'] ) ); ?></span>
		<div class="mnp-stat-label"><?php esc_html_e( 'Expired', 'monirnews-pro' ); ?></div>
	</div>
</div>

<!-- Quick actions -->
<div class="mnp-card">
	<div class="mnp-card-header">
		<h2><?php esc_html_e( 'Quick Actions', 'monirnews-pro' ); ?></h2>
	</div>
	<div class="mnp-card-body" style="display:flex;gap:1rem;flex-wrap:wrap;">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=monirnews-pro-ads&action=create' ) ); ?>"
			class="mnp-btn mnp-btn-primary">
			&#10133; <?php esc_html_e( 'Create New Ad', 'monirnews-pro' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=monirnews-pro-ads' ) ); ?>"
			class="mnp-btn mnp-btn-secondary">
			&#128203; <?php esc_html_e( 'Manage Ads', 'monirnews-pro' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>"
			class="mnp-btn mnp-btn-secondary">
			&#129513; <?php esc_html_e( 'Go to Widgets', 'monirnews-pro' ); ?>
		</a>
	</div>
</div>

<!-- Recent ads table -->
<div class="mnp-card">
	<div class="mnp-card-header">
		<h2><?php esc_html_e( 'Recent Ads', 'monirnews-pro' ); ?></h2>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=monirnews-pro-ads' ) ); ?>"
			class="mnp-btn mnp-btn-secondary mnp-btn-sm">
			<?php esc_html_e( 'View All', 'monirnews-pro' ); ?>
		</a>
	</div>
	<div class="mnp-card-body" style="padding:0;">
		<?php if ( empty( $recent_ads ) ) : ?>
			<div class="mnp-empty">
				<div class="mnp-empty-icon">&#128240;</div>
				<h3><?php esc_html_e( 'No ads yet', 'monirnews-pro' ); ?></h3>
				<p><?php esc_html_e( 'Create your first ad to start monetising your site.', 'monirnews-pro' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=monirnews-pro-ads&action=create' ) ); ?>"
					class="mnp-btn mnp-btn-primary">
					<?php esc_html_e( 'Create Ad', 'monirnews-pro' ); ?>
				</a>
			</div>
		<?php else : ?>
			<table class="mnp-ads-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'monirnews-pro' ); ?></th>
						<th><?php esc_html_e( 'Zone', 'monirnews-pro' ); ?></th>
						<th><?php esc_html_e( 'Type', 'monirnews-pro' ); ?></th>
						<th><?php esc_html_e( 'Status', 'monirnews-pro' ); ?></th>
						<th><?php esc_html_e( 'Impressions', 'monirnews-pro' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $recent_ads as $ad ) : ?>
						<tr>
							<td>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=monirnews-pro-ads&action=edit&id=' . $ad->id ) ); ?>">
									<?php echo esc_html( $ad->name ); ?>
								</a>
							</td>
							<td><?php echo esc_html( $ad->zone ); ?></td>
							<td><?php echo esc_html( $ad->type ); ?></td>
							<td>
								<span class="mnp-status <?php echo esc_attr( $ad->status ); ?>">
									<?php echo esc_html( $ad->status ); ?>
								</span>
							</td>
							<td><?php echo esc_html( number_format( (int) $ad->impressions ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>

<!-- Plugin info -->
<div class="mnp-card">
	<div class="mnp-card-header">
		<h2><?php esc_html_e( 'Plugin Info', 'monirnews-pro' ); ?></h2>
	</div>
	<div class="mnp-card-body">
		<table style="border-collapse:collapse;font-size:0.9rem;">
			<tr>
				<td style="padding:4px 16px 4px 0;font-weight:600;color:#555;"><?php esc_html_e( 'Version', 'monirnews-pro' ); ?></td>
				<td><?php echo esc_html( MNP_VERSION ); ?></td>
			</tr>
			<tr>
				<td style="padding:4px 16px 4px 0;font-weight:600;color:#555;"><?php esc_html_e( 'Author', 'monirnews-pro' ); ?></td>
				<td>Md Monirujaman Khan</td>
			</tr>
			<tr>
				<td style="padding:4px 16px 4px 0;font-weight:600;color:#555;"><?php esc_html_e( 'Support', 'monirnews-pro' ); ?></td>
				<td><a href="mailto:info@monirtechsolutions.com">info@monirtechsolutions.com</a></td>
			</tr>
			<tr>
				<td style="padding:4px 16px 4px 0;font-weight:600;color:#555;"><?php esc_html_e( 'Website', 'monirnews-pro' ); ?></td>
				<td>
					<a href="https://monirtechsolutions.com" target="_blank" rel="noopener noreferrer">
						monirtechsolutions.com
					</a>
				</td>
			</tr>
		</table>
	</div>
</div>
