<?php
/**
 * MonirNews Pro — General Settings view.
 *
 * @package MonirNews_Pro
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$license = new MNP_License();
$status  = $license->get_status();
$has_key = (bool) $license->get_license_key();

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$saved = isset( $_GET['message'] ) && 'saved' === sanitize_key( $_GET['message'] );
?>

<?php if ( $saved ) : ?>
	<div class="notice notice-success is-dismissible">
		<p><?php esc_html_e( 'Settings saved.', 'monirnews-pro' ); ?></p>
	</div>
<?php endif; ?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="mnp_save_settings">
	<?php wp_nonce_field( 'mnp_settings_nonce', 'mnp_nonce' ); ?>

	<!-- License -->
	<div class="mnp-card">
		<div class="mnp-card-header">
			<h2>&#128273; <?php esc_html_e( 'License', 'monirnews-pro' ); ?></h2>
			<span class="mnp-status <?php echo esc_attr( $status ); ?>">
				<?php echo 'active' === $status
					? esc_html__( 'Active', 'monirnews-pro' )
					: esc_html__( 'Inactive', 'monirnews-pro' ); ?>
			</span>
		</div>
		<div class="mnp-card-body">
			<div class="mnp-form-field">
				<label for="mnp-license-key"><?php esc_html_e( 'License Key', 'monirnews-pro' ); ?></label>
				<input type="text" id="mnp-license-key" name="license_key"
					value="<?php echo $has_key ? esc_attr( str_repeat( '*', 24 ) ) : ''; ?>"
					placeholder="<?php esc_attr_e( 'Enter your license key', 'monirnews-pro' ); ?>"
					autocomplete="off"
					style="max-width:420px;">
				<?php if ( $has_key ) : ?>
					<span class="description">
						<?php esc_html_e( 'A key is already stored. Enter a new key to replace it.', 'monirnews-pro' ); ?>
					</span>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="mnp-card">
		<div class="mnp-card-body" style="display:flex;gap:1rem;">
			<button type="submit" class="mnp-btn mnp-btn-primary">
				&#128190; <?php esc_html_e( 'Save Settings', 'monirnews-pro' ); ?>
			</button>
		</div>
	</div>
</form>

<!-- Plugin info & links -->
<div class="mnp-card">
	<div class="mnp-card-header">
		<h2><?php esc_html_e( 'About MonirNews Pro', 'monirnews-pro' ); ?></h2>
	</div>
	<div class="mnp-card-body">
		<table style="border-collapse:collapse;font-size:0.9rem;line-height:2;">
			<tr>
				<td style="padding-right:16px;font-weight:600;color:#555;white-space:nowrap;"><?php esc_html_e( 'Version', 'monirnews-pro' ); ?></td>
				<td><?php echo esc_html( MNP_VERSION ); ?></td>
			</tr>
			<tr>
				<td style="padding-right:16px;font-weight:600;color:#555;white-space:nowrap;"><?php esc_html_e( 'Author', 'monirnews-pro' ); ?></td>
				<td>
					<a href="https://monirtechsolutions.com" target="_blank" rel="noopener noreferrer">
						Md Monirujaman Khan
					</a>
				</td>
			</tr>
			<tr>
				<td style="padding-right:16px;font-weight:600;color:#555;white-space:nowrap;"><?php esc_html_e( 'Support', 'monirnews-pro' ); ?></td>
				<td><a href="mailto:info@monirtechsolutions.com">info@monirtechsolutions.com</a></td>
			</tr>
			<tr>
				<td style="padding-right:16px;font-weight:600;color:#555;white-space:nowrap;"><?php esc_html_e( 'Documentation', 'monirnews-pro' ); ?></td>
				<td>
					<a href="https://monirtechsolutions.com/wp-theme/monirnews/docs" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'View Docs', 'monirnews-pro' ); ?>
					</a>
				</td>
			</tr>
		</table>
	</div>
</div>

<!-- Shortcode reference -->
<div class="mnp-card">
	<div class="mnp-card-header">
		<h2><?php esc_html_e( 'Shortcode Reference', 'monirnews-pro' ); ?></h2>
	</div>
	<div class="mnp-card-body" style="font-size:0.9rem;line-height:1.8;">
		<p><?php esc_html_e( 'Insert an ad zone anywhere in your content:', 'monirnews-pro' ); ?></p>
		<code style="background:#f4f4f4;padding:6px 12px;border-radius:4px;display:inline-block;margin-bottom:1rem;">
			[mnp_ad zone="header"]
		</code>
		<p><?php esc_html_e( 'Available zones:', 'monirnews-pro' ); ?></p>
		<ul style="margin:0 0 0 1.5rem;">
			<li><code>header</code> — <?php esc_html_e( '728×90', 'monirnews-pro' ); ?></li>
			<li><code>sidebar-top</code> / <code>sidebar-middle</code> — <?php esc_html_e( '300×250', 'monirnews-pro' ); ?></li>
			<li><code>in-content</code> — <?php esc_html_e( '468×60', 'monirnews-pro' ); ?></li>
			<li><code>footer</code> — <?php esc_html_e( '728×90', 'monirnews-pro' ); ?></li>
			<li><code>mobile-top</code> — <?php esc_html_e( '320×50', 'monirnews-pro' ); ?></li>
		</ul>
		<p style="margin-top:1rem;"><?php esc_html_e( 'In a theme template:', 'monirnews-pro' ); ?></p>
		<code style="background:#f4f4f4;padding:6px 12px;border-radius:4px;display:inline-block;">
			&lt;?php echo do_shortcode( '[mnp_ad zone="sidebar-top"]' ); ?&gt;
		</code>
	</div>
</div>
