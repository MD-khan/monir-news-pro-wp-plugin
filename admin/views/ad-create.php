<?php
/**
 * MonirNews Pro — Create Ad form view.
 *
 * @package MonirNews_Pro
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$error = isset( $_GET['error'] ) ? sanitize_key( $_GET['error'] ) : '';

$zone_defaults = array(
	'header'         => array( 'label' => __( 'Header (728×90)', 'monirnews-pro' ),         'w' => 728, 'h' => 90  ),
	'sidebar-top'    => array( 'label' => __( 'Sidebar Top (300×250)', 'monirnews-pro' ),    'w' => 300, 'h' => 250 ),
	'sidebar-middle' => array( 'label' => __( 'Sidebar Middle (300×250)', 'monirnews-pro' ), 'w' => 300, 'h' => 250 ),
	'in-content'     => array( 'label' => __( 'In-Content (468×60)', 'monirnews-pro' ),      'w' => 468, 'h' => 60  ),
	'footer'         => array( 'label' => __( 'Footer (728×90)', 'monirnews-pro' ),          'w' => 728, 'h' => 90  ),
	'mobile-top'     => array( 'label' => __( 'Mobile Top (320×50)', 'monirnews-pro' ),      'w' => 320, 'h' => 50  ),
);
?>

<?php if ( 'invalid' === $error ) : ?>
	<div class="notice notice-error"><p><?php esc_html_e( 'Ad name is required.', 'monirnews-pro' ); ?></p></div>
<?php elseif ( 'db' === $error ) : ?>
	<div class="notice notice-error"><p><?php esc_html_e( 'Database error. Please try again.', 'monirnews-pro' ); ?></p></div>
<?php endif; ?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="mnp-ad-form">
	<input type="hidden" name="action" value="mnp_create_ad">
	<?php wp_nonce_field( 'mnp_create_ad_nonce', 'mnp_nonce' ); ?>

	<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
		<h2 style="margin:0;font-size:1.2rem;"><?php esc_html_e( 'Create New Ad', 'monirnews-pro' ); ?></h2>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=monirnews-pro-ads' ) ); ?>"
			class="mnp-btn mnp-btn-secondary">
			&#8592; <?php esc_html_e( 'Back to Ads', 'monirnews-pro' ); ?>
		</a>
	</div>

	<!-- Ad Details -->
	<div class="mnp-card">
		<div class="mnp-card-header"><h2><?php esc_html_e( 'Ad Details', 'monirnews-pro' ); ?></h2></div>
		<div class="mnp-card-body">
			<div class="mnp-form-grid">

				<div class="mnp-form-field full">
					<label for="mnp-name"><?php esc_html_e( 'Ad Name', 'monirnews-pro' ); ?> <span style="color:#c0392b;">*</span></label>
					<input type="text" id="mnp-name" name="name" required
						placeholder="<?php esc_attr_e( 'e.g. Sidebar Banner April', 'monirnews-pro' ); ?>">
				</div>

				<div class="mnp-form-field full">
					<label><?php esc_html_e( 'Ad Type', 'monirnews-pro' ); ?></label>
					<div style="display:flex;gap:1.5rem;margin-top:4px;">
						<?php
						$types = array(
							'image' => __( 'Image', 'monirnews-pro' ),
							'gif'   => __( 'GIF', 'monirnews-pro' ),
							'video' => __( 'Video', 'monirnews-pro' ),
							'html'  => __( 'HTML Code', 'monirnews-pro' ),
						);
						foreach ( $types as $val => $label ) :
							?>
							<label style="display:flex;align-items:center;gap:6px;font-weight:normal;cursor:pointer;">
								<input type="radio" name="type" value="<?php echo esc_attr( $val ); ?>"
									<?php checked( 'image', $val ); ?>>
								<?php echo esc_html( $label ); ?>
							</label>
						<?php endforeach; ?>
					</div>
				</div>

			</div>
		</div>
	</div>

	<!-- Media upload (image / gif / video) -->
	<div class="mnp-card" id="mnp-media-section">
		<div class="mnp-card-header"><h2><?php esc_html_e( 'Media', 'monirnews-pro' ); ?></h2></div>
		<div class="mnp-card-body">
			<input type="hidden" id="mnp-media-id"  name="media_id"  value="0">
			<input type="hidden" id="mnp-media-url" name="media_url" value="">

			<div class="mnp-media-upload" id="mnp-upload-area">
				<div style="font-size:2rem;margin-bottom:0.5rem;">&#128247;</div>
				<p style="margin:0;color:#666;"><?php esc_html_e( 'Click to upload from your media library', 'monirnews-pro' ); ?></p>
				<button type="button" class="mnp-btn mnp-btn-secondary" id="mnp-upload-btn" style="margin-top:1rem;">
					<?php esc_html_e( 'Select Media', 'monirnews-pro' ); ?>
				</button>
			</div>

			<div id="mnp-media-preview" class="mnp-media-preview"></div>
		</div>
	</div>

	<!-- HTML code section -->
	<div class="mnp-card" id="mnp-html-section" style="display:none;">
		<div class="mnp-card-header"><h2><?php esc_html_e( 'HTML Ad Code', 'monirnews-pro' ); ?></h2></div>
		<div class="mnp-card-body">
			<div class="mnp-form-field full">
				<label for="mnp-html-code"><?php esc_html_e( 'Ad Code', 'monirnews-pro' ); ?></label>
				<textarea id="mnp-html-code" name="html_code" rows="8" class="mnp-textarea"
					placeholder="<?php esc_attr_e( 'Paste your Google AdSense or custom HTML code here', 'monirnews-pro' ); ?>"></textarea>
				<span class="description">
					<?php esc_html_e( 'Paste your Google AdSense or custom HTML code here. Script tags are allowed.', 'monirnews-pro' ); ?>
				</span>
			</div>
		</div>
	</div>

	<!-- Placement -->
	<div class="mnp-card">
		<div class="mnp-card-header"><h2><?php esc_html_e( 'Placement', 'monirnews-pro' ); ?></h2></div>
		<div class="mnp-card-body">
			<div class="mnp-form-grid">

				<div class="mnp-form-field">
					<label for="mnp-zone"><?php esc_html_e( 'Ad Zone', 'monirnews-pro' ); ?></label>
					<select id="mnp-zone" name="zone">
						<?php foreach ( $zone_defaults as $zone_key => $zone_data ) : ?>
							<option value="<?php echo esc_attr( $zone_key ); ?>">
								<?php echo esc_html( $zone_data['label'] ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="mnp-form-field">
					<label for="mnp-priority"><?php esc_html_e( 'Priority', 'monirnews-pro' ); ?></label>
					<input type="number" id="mnp-priority" name="priority" value="10" min="1" max="100">
					<span class="description"><?php esc_html_e( 'Higher priority shows first (1–100).', 'monirnews-pro' ); ?></span>
				</div>

				<div class="mnp-form-field">
					<label for="mnp-width"><?php esc_html_e( 'Width (px)', 'monirnews-pro' ); ?></label>
					<input type="number" id="mnp-width" name="width" value="300" min="1">
				</div>

				<div class="mnp-form-field">
					<label for="mnp-height"><?php esc_html_e( 'Height (px)', 'monirnews-pro' ); ?></label>
					<input type="number" id="mnp-height" name="height" value="250" min="1">
				</div>

				<div class="mnp-form-field full">
					<label for="mnp-click-url"><?php esc_html_e( 'Click URL', 'monirnews-pro' ); ?></label>
					<input type="url" id="mnp-click-url" name="click_url"
						placeholder="https://example.com">
				</div>

			</div>
		</div>
	</div>

	<!-- Scheduling -->
	<div class="mnp-card">
		<div class="mnp-card-header"><h2><?php esc_html_e( 'Scheduling', 'monirnews-pro' ); ?></h2></div>
		<div class="mnp-card-body">
			<div class="mnp-form-grid">

				<div class="mnp-form-field">
					<label for="mnp-start-date"><?php esc_html_e( 'Start Date', 'monirnews-pro' ); ?></label>
					<input type="datetime-local" id="mnp-start-date" name="start_date"
						value="<?php echo esc_attr( gmdate( 'Y-m-d\TH:i' ) ); ?>">
				</div>

				<div class="mnp-form-field">
					<label for="mnp-end-date"><?php esc_html_e( 'End Date', 'monirnews-pro' ); ?></label>
					<input type="datetime-local" id="mnp-end-date" name="end_date">
					<span class="description"><?php esc_html_e( 'Leave empty for no expiry.', 'monirnews-pro' ); ?></span>
				</div>

				<div class="mnp-form-field">
					<label for="mnp-status"><?php esc_html_e( 'Status', 'monirnews-pro' ); ?></label>
					<select id="mnp-status" name="status">
						<option value="active"><?php esc_html_e( 'Active', 'monirnews-pro' ); ?></option>
						<option value="paused"><?php esc_html_e( 'Paused', 'monirnews-pro' ); ?></option>
						<option value="scheduled"><?php esc_html_e( 'Scheduled', 'monirnews-pro' ); ?></option>
					</select>
				</div>

			</div>
		</div>
	</div>

	<!-- Submit -->
	<div class="mnp-card">
		<div class="mnp-card-body" style="display:flex;gap:1rem;">
			<button type="submit" class="mnp-btn mnp-btn-primary">
				&#128190; <?php esc_html_e( 'Save Ad', 'monirnews-pro' ); ?>
			</button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=monirnews-pro-ads' ) ); ?>"
				class="mnp-btn mnp-btn-secondary">
				<?php esc_html_e( 'Cancel', 'monirnews-pro' ); ?>
			</a>
		</div>
	</div>

</form>
