<?php
/**
 * MonirNews Pro — Weather Widget module.
 *
 * Registers a classic WP_Widget that displays current weather data from the
 * OpenWeatherMap API. Results are cached in a transient for 30 minutes.
 *
 * Global settings (API key, default city, unit) are stored in the
 * mnp_weather_settings option managed via the admin Weather view.
 *
 * @package MonirNews_Pro
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MNP_Weather_Widget
 *
 * @since 2.0.0
 */
class MNP_Weather_Widget extends WP_Widget {

	/**
	 * OpenWeatherMap current-weather endpoint.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	const API_URL = 'https://api.openweathermap.org/data/2.5/weather';

	/**
	 * Transient key prefix.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	const CACHE_PREFIX = 'mnp_weather_';

	/**
	 * Cache TTL in seconds (30 minutes).
	 *
	 * @since 2.0.0
	 * @var int
	 */
	const CACHE_TTL = 1800;

	/**
	 * Condition-code to emoji mapping (OpenWeatherMap main groups).
	 *
	 * @since 2.0.0
	 * @var array
	 */
	private $icons = array(
		'Clear'        => '&#9728;',
		'Clouds'       => '&#9729;',
		'Rain'         => '&#127783;',
		'Snow'         => '&#10052;',
		'Thunderstorm' => '&#9928;',
		'Drizzle'      => '&#127782;',
		'Mist'         => '&#127787;',
		'Fog'          => '&#127787;',
	);

	/**
	 * Constructor — register the widget with WordPress.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		parent::__construct(
			'mnp_weather_widget',
			esc_html__( 'MonirNews — Weather', 'monirnews-pro' ),
			array(
				'description' => esc_html__( 'Display current weather for any city.', 'monirnews-pro' ),
				'classname'   => 'mnp-weather-widget',
			)
		);

		add_action( 'widgets_init',       array( $this, 'register_widget' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'output_inline_css' ) );
	}

	/**
	 * Register this widget class.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_widget() {
		register_widget( 'MNP_Weather_Widget' );
	}

	/**
	 * Output minimal inline CSS when the widget is active.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output_inline_css() {
		if ( ! is_active_widget( false, false, 'mnp_weather_widget', true ) ) {
			return;
		}

		$css = '
.mnp-weather{background:linear-gradient(135deg,#2c3e50,#3498db);color:#fff;border-radius:10px;padding:1.25rem;font-family:-apple-system,sans-serif}
.mnp-weather__city{font-weight:700;font-size:1.1rem;margin-bottom:.5rem}
.mnp-weather__main{display:flex;align-items:center;gap:.5rem;margin:.75rem 0}
.mnp-weather__icon{font-size:2rem}
.mnp-weather__temp{font-size:2.2rem;font-weight:800}
.mnp-weather__desc{font-size:.9rem;text-transform:capitalize;opacity:.85;margin-bottom:.75rem}
.mnp-weather__meta{display:flex;gap:1rem;font-size:.8rem;opacity:.75;flex-wrap:wrap}
.mnp-weather__updated{font-size:.75rem;opacity:.6;margin-top:.5rem}
		';

		wp_add_inline_style( 'wp-block-library', $css );
	}

	/**
	 * Front-end output of the classic widget.
	 *
	 * @since 2.0.0
	 * @param array $args     Widget display arguments.
	 * @param array $instance Saved widget instance settings.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		$global   = wp_parse_args( get_option( 'mnp_weather_settings', array() ), array( 'api_key' => '', 'city' => 'London', 'unit' => 'C', 'enabled' => false ) );
		$api_key  = sanitize_text_field( $global['api_key'] );
		$city     = sanitize_text_field( ! empty( $instance['city'] ) ? $instance['city'] : $global['city'] );
		$unit     = in_array( $global['unit'], array( 'C', 'F' ), true ) ? $global['unit'] : 'C';
		$title    = apply_filters( 'widget_title', $instance['title'] ?? '' );

		if ( empty( $api_key ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<p style="padding:1rem;color:#c0392b;font-size:0.85rem;">'
					. esc_html__( 'MonirNews Pro — Weather: add your OpenWeatherMap API key in the admin Weather settings.', 'monirnews-pro' )
					. '</p>';
				echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			return;
		}

		$data = $this->get_weather_data( $city, $api_key );

		if ( is_wp_error( $data ) ) {
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		$this->render_weather_html( $data, $unit );

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render the weather data HTML card.
	 *
	 * @since 2.0.0
	 * @param array  $data Weather data from get_weather_data().
	 * @param string $unit Temperature unit: 'C' or 'F'.
	 * @return void
	 */
	private function render_weather_html( $data, $unit ) {
		$icon   = $this->icons[ $data['condition'] ] ?? '&#127777;';
		$symbol = ( 'F' === $unit ) ? '&deg;F' : '&deg;C';
		$temp   = ( 'F' === $unit )
			? round( ( $data['temp'] * 9 / 5 ) + 32 )
			: round( $data['temp'] );
		?>
		<div class="mnp-weather">
			<div class="mnp-weather__city"><?php echo esc_html( $data['city'] ); ?></div>
			<div class="mnp-weather__main">
				<span class="mnp-weather__icon" aria-hidden="true">
					<?php echo wp_kses( $icon, array() ); ?>
				</span>
				<span class="mnp-weather__temp">
					<?php echo esc_html( $temp ); ?><?php echo wp_kses( $symbol, array() ); ?>
				</span>
			</div>
			<div class="mnp-weather__desc"><?php echo esc_html( $data['description'] ); ?></div>
			<div class="mnp-weather__meta">
				<span class="mnp-weather__humidity">
					<?php
					echo esc_html( sprintf(
						/* translators: %d: humidity percentage */
						__( 'Humidity: %d%%', 'monirnews-pro' ),
						absint( $data['humidity'] )
					) );
					?>
				</span>
				<span class="mnp-weather__wind">
					<?php
					echo esc_html( sprintf(
						/* translators: %s: wind speed m/s */
						__( 'Wind: %s m/s', 'monirnews-pro' ),
						$data['wind']
					) );
					?>
				</span>
			</div>
			<div class="mnp-weather__updated">
				<?php
				echo esc_html( sprintf(
					/* translators: %s: time ago */
					__( 'Updated: %s ago', 'monirnews-pro' ),
					human_time_diff( $data['fetched_at'] )
				) );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the widget settings form in the admin widget editor.
	 *
	 * @since 2.0.0
	 * @param array $instance Saved widget instance settings.
	 * @return void
	 */
	public function form( $instance ) {
		$title = esc_attr( $instance['title'] ?? '' );
		$city  = esc_attr( $instance['city']  ?? '' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', 'monirnews-pro' ); ?>
			</label>
			<input class="widefat" type="text"
				id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'city' ) ); ?>">
				<?php esc_html_e( 'City (overrides global default):', 'monirnews-pro' ); ?>
			</label>
			<input class="widefat" type="text"
				id="<?php echo esc_attr( $this->get_field_id( 'city' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'city' ) ); ?>"
				value="<?php echo esc_attr( $city ); ?>"
				placeholder="<?php esc_attr_e( 'London', 'monirnews-pro' ); ?>">
		</p>
		<?php
	}

	/**
	 * Sanitize and save widget instance settings.
	 *
	 * @since 2.0.0
	 * @param array $new_instance New settings from the form.
	 * @param array $old_instance Previously saved settings.
	 * @return array Sanitized settings array.
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title' => sanitize_text_field( $new_instance['title'] ?? '' ),
			'city'  => sanitize_text_field( $new_instance['city']  ?? '' ),
		);
	}

	/**
	 * Fetch weather data from OpenWeatherMap with 30-minute transient cache.
	 *
	 * Returns temperatures in Celsius; Fahrenheit conversion happens at render time.
	 *
	 * @since 2.0.0
	 * @param string $city    City name.
	 * @param string $api_key OpenWeatherMap API key.
	 * @return array|WP_Error Normalised weather data or WP_Error on failure.
	 */
	public function get_weather_data( $city, $api_key ) {
		$cache_key = self::CACHE_PREFIX . md5( strtolower( $city ) );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$url = add_query_arg(
			array(
				'q'     => rawurlencode( $city ),
				'appid' => $api_key,
				'units' => 'metric',
			),
			self::API_URL
		);

		$response = wp_remote_get( $url, array(
			'timeout'    => 8,
			'user-agent' => 'MonirNews-Pro/' . MNP_VERSION,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		$body   = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $status || empty( $body['main'] ) ) {
			return new WP_Error(
				'mnp_weather_api',
				/* translators: %d: HTTP status code */
				sprintf( __( 'Weather API error (status %d).', 'monirnews-pro' ), $status )
			);
		}

		$data = array(
			'city'        => sanitize_text_field( $body['name'] ),
			'temp'        => floatval( $body['main']['temp'] ),
			'condition'   => sanitize_text_field( $body['weather'][0]['main']        ?? '' ),
			'description' => sanitize_text_field( $body['weather'][0]['description'] ?? '' ),
			'humidity'    => absint( $body['main']['humidity'] ),
			'wind'        => round( floatval( $body['wind']['speed'] ?? 0 ), 1 ),
			'fetched_at'  => time(),
		);

		set_transient( $cache_key, $data, self::CACHE_TTL );

		return $data;
	}
}

// Self-instantiate to trigger widgets_init registration.
new MNP_Weather_Widget();
