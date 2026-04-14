<?php
/**
 * MonirNews Pro — Weather Widget settings view.
 *
 * @package MonirNews_Pro
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'enabled' => false,
	'api_key' => '',
	'city'    => 'London',
	'unit'    => 'C',
);

$s = wp_parse_args( get_option( 'mnp_weather_settings', array() ), $defaults );

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$saved = isset( $_GET['message'] ) && 'saved' === sanitize_key( $_GET['message'] );
?>

<?php if ( $saved ) : ?>
	<div class="notice notice-success is-dismissible">
		<p><?php esc_html_e( 'Weather settings saved.', 'monirnews-pro' ); ?></p>
	</div>
<?php endif; ?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="mnp_save_weather_settings">
	<?php wp_nonce_field( 'mnp_weather_settings_nonce', 'mnp_nonce' ); ?>

	<div class="mnp-card">
		<div class="mnp-card-header">
			<h2>&#127780; <?php esc_html_e( 'Weather Widget', 'monirnews-pro' ); ?></h2>
		</div>
		<div class="mnp-card-body">
			<div class="mnp-form-grid">

				<div class="mnp-form-field full">
					<label style="display:flex;align-items:center;gap:10px;">
						<label class="mnp-toggle">
							<input type="checkbox" name="enabled" <?php checked( ! empty( $s['enabled'] ) ); ?>>
							<span class="mnp-toggle-slider"></span>
						</label>
						<?php esc_html_e( 'Enable Weather Widget', 'monirnews-pro' ); ?>
					</label>
				</div>

				<div class="mnp-form-field">
					<label for="mnp-api-key">
						<?php esc_html_e( 'OpenWeatherMap API Key', 'monirnews-pro' ); ?>
					</label>
					<input type="text" id="mnp-api-key" name="api_key"
						value="<?php echo esc_attr( $s['api_key'] ); ?>"
						placeholder="<?php esc_attr_e( 'Paste your API key here', 'monirnews-pro' ); ?>"
						autocomplete="off">
					<span class="description">
						<?php esc_html_e( 'Free API key from', 'monirnews-pro' ); ?>
						<a href="https://openweathermap.org/api" target="_blank" rel="noopener noreferrer">openweathermap.org</a>
					</span>
				</div>

				<div class="mnp-form-field">
					<label for="mnp-city"><?php esc_html_e( 'Default City', 'monirnews-pro' ); ?></label>
					<input type="text" id="mnp-city" name="city"
						value="<?php echo esc_attr( $s['city'] ); ?>"
						placeholder="London">
					<span class="description">
						<?php esc_html_e( 'Used when no city is set per-widget. Changing this clears the weather cache.', 'monirnews-pro' ); ?>
					</span>
				</div>

				<div class="mnp-form-field full">
					<label><?php esc_html_e( 'Temperature Unit', 'monirnews-pro' ); ?></label>
					<div style="display:flex;gap:2rem;margin-top:4px;">
						<label style="display:flex;align-items:center;gap:6px;font-weight:normal;cursor:pointer;">
							<input type="radio" name="unit" value="C" <?php checked( $s['unit'], 'C' ); ?>>
							<?php esc_html_e( 'Celsius (°C)', 'monirnews-pro' ); ?>
						</label>
						<label style="display:flex;align-items:center;gap:6px;font-weight:normal;cursor:pointer;">
							<input type="radio" name="unit" value="F" <?php checked( $s['unit'], 'F' ); ?>>
							<?php esc_html_e( 'Fahrenheit (°F)', 'monirnews-pro' ); ?>
						</label>
					</div>
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

<!-- Usage instructions -->
<div class="mnp-card">
	<div class="mnp-card-header">
		<h2><?php esc_html_e( 'How to Use', 'monirnews-pro' ); ?></h2>
	</div>
	<div class="mnp-card-body" style="font-size:0.9rem;line-height:1.7;">
		<ol>
			<li><?php esc_html_e( 'Enable the widget and add your OpenWeatherMap API key above.', 'monirnews-pro' ); ?></li>
			<li>
				<?php esc_html_e( 'Go to', 'monirnews-pro' ); ?>
				<a href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>">
					<?php esc_html_e( 'Appearance → Widgets', 'monirnews-pro' ); ?>
				</a>
				<?php esc_html_e( 'and drag the "MonirNews — Weather" widget into a widget area.', 'monirnews-pro' ); ?>
			</li>
			<li><?php esc_html_e( 'You can override the city per widget instance.', 'monirnews-pro' ); ?></li>
			<li><?php esc_html_e( 'Weather data is cached for 30 minutes.', 'monirnews-pro' ); ?></li>
		</ol>
	</div>
</div>
