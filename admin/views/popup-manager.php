<?php
/**
 * MonirNews Pro — Popup Manager view.
 *
 * @package MonirNews_Pro
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'enabled'   => false,
	'delay'     => 3,
	'once'      => true,
	'bg_color'  => '#2c3e50',
	'txt_color' => '#ffffff',
	'title'     => '',
);

$s = wp_parse_args( get_option( 'mnp_popup_settings', array() ), $defaults );

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$saved = isset( $_GET['message'] ) && 'saved' === sanitize_key( $_GET['message'] );

// Recent "breaking" tagged posts for reference.
$breaking_posts = get_posts( array(
	'numberposts'      => 5,
	'tag'              => 'breaking',
	'suppress_filters' => false,
) );
?>

<?php if ( $saved ) : ?>
	<div class="notice notice-success is-dismissible">
		<p><?php esc_html_e( 'Popup settings saved.', 'monirnews-pro' ); ?></p>
	</div>
<?php endif; ?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="mnp_save_popup_settings">
	<?php wp_nonce_field( 'mnp_popup_settings_nonce', 'mnp_nonce' ); ?>

	<!-- Settings card -->
	<div class="mnp-card">
		<div class="mnp-card-header">
			<h2>&#128226; <?php esc_html_e( 'Breaking News Popup', 'monirnews-pro' ); ?></h2>
		</div>
		<div class="mnp-card-body">
			<div class="mnp-form-grid">

				<div class="mnp-form-field full">
					<label class="mnp-field-label" style="display:flex;align-items:center;gap:10px;">
						<label class="mnp-toggle">
							<input type="checkbox" name="enabled" <?php checked( ! empty( $s['enabled'] ) ); ?>>
							<span class="mnp-toggle-slider"></span>
						</label>
						<?php esc_html_e( 'Enable Breaking News Popup', 'monirnews-pro' ); ?>
					</label>
				</div>

				<div class="mnp-form-field">
					<label for="mnp-popup-title"><?php esc_html_e( 'Popup Title', 'monirnews-pro' ); ?></label>
					<input type="text" id="mnp-popup-title" name="title"
						value="<?php echo esc_attr( $s['title'] ); ?>"
						placeholder="<?php esc_attr_e( 'Breaking News', 'monirnews-pro' ); ?>">
				</div>

				<div class="mnp-form-field">
					<label for="mnp-delay"><?php esc_html_e( 'Delay before showing (seconds)', 'monirnews-pro' ); ?></label>
					<input type="number" id="mnp-delay" name="delay"
						value="<?php echo esc_attr( absint( $s['delay'] ) ); ?>" min="0" max="60">
				</div>

				<div class="mnp-form-field">
					<label class="mnp-field-label" style="display:flex;align-items:center;gap:8px;font-weight:normal;cursor:pointer;">
						<input type="checkbox" name="once" <?php checked( ! empty( $s['once'] ) ); ?>>
						<?php esc_html_e( 'Show once per session (sessionStorage)', 'monirnews-pro' ); ?>
					</label>
				</div>

				<div class="mnp-form-field">
					<label for="mnp-bg-color"><?php esc_html_e( 'Background Colour', 'monirnews-pro' ); ?></label>
					<input type="color" id="mnp-bg-color" name="bg_color"
						value="<?php echo esc_attr( $s['bg_color'] ); ?>">
				</div>

				<div class="mnp-form-field">
					<label for="mnp-txt-color"><?php esc_html_e( 'Text Colour', 'monirnews-pro' ); ?></label>
					<input type="color" id="mnp-txt-color" name="txt_color"
						value="<?php echo esc_attr( $s['txt_color'] ); ?>">
				</div>

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

<!-- Breaking posts reference -->
<div class="mnp-card">
	<div class="mnp-card-header">
		<h2><?php esc_html_e( 'Posts Tagged "breaking"', 'monirnews-pro' ); ?></h2>
		<a href="<?php echo esc_url( admin_url( 'edit.php?tag=breaking' ) ); ?>"
			class="mnp-btn mnp-btn-secondary mnp-btn-sm">
			<?php esc_html_e( 'Manage', 'monirnews-pro' ); ?>
		</a>
	</div>
	<div class="mnp-card-body" style="padding:0;">
		<?php if ( empty( $breaking_posts ) ) : ?>
			<div class="mnp-empty">
				<div class="mnp-empty-icon">&#128680;</div>
				<h3><?php esc_html_e( 'No "breaking" tagged posts found', 'monirnews-pro' ); ?></h3>
				<p>
					<?php esc_html_e( 'Tag any post with the tag', 'monirnews-pro' ); ?>
					<code>breaking</code>
					<?php esc_html_e( 'for it to appear in the popup.', 'monirnews-pro' ); ?>
				</p>
			</div>
		<?php else : ?>
			<table class="mnp-ads-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Title', 'monirnews-pro' ); ?></th>
						<th><?php esc_html_e( 'Date', 'monirnews-pro' ); ?></th>
						<th><?php esc_html_e( 'Status', 'monirnews-pro' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $breaking_posts as $post ) : ?>
						<tr>
							<td>
								<a href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>">
									<?php echo esc_html( $post->post_title ); ?>
								</a>
							</td>
							<td style="font-size:0.8rem;color:#888;">
								<?php echo esc_html( get_the_date( '', $post ) ); ?>
							</td>
							<td>
								<span class="mnp-status <?php echo esc_attr( $post->post_status ); ?>">
									<?php echo esc_html( $post->post_status ); ?>
								</span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>
