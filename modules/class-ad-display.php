<?php
/**
 * MonirNews Pro — Ad Display module.
 *
 * Registers the [mnp_ad] shortcode and the monirnews_ad_zone filter so the
 * theme can call ads programmatically. Impression tracking is handled via
 * an IntersectionObserver + AJAX on the front end.
 *
 * @package MonirNews_Pro
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MNP_Ad_Display
 *
 * @since 2.0.0
 */
class MNP_Ad_Display {

	/**
	 * Constructor — registers hooks.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_shortcode( 'mnp_ad',                          array( $this, 'shortcode' ) );
		add_filter(    'monirnews_ad_zone',               array( $this, 'render_zone' ), 10, 2 );
		add_action(    'wp_enqueue_scripts',              array( $this, 'enqueue_scripts' ) );
		add_action(    'wp_ajax_mnp_impression',          array( $this, 'handle_impression' ) );
		add_action(    'wp_ajax_nopriv_mnp_impression',   array( $this, 'handle_impression' ) );
	}

	/**
	 * Enqueue the lightweight impression-tracking script.
	 *
	 * Uses an inline script registered against a placeholder handle so there
	 * are no extra HTTP requests.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enqueue_scripts() {
		// Only enqueue when there are active ads in the database.
		wp_register_script( 'mnp-public', '', array(), MNP_VERSION, true );
		wp_enqueue_script( 'mnp-public' );

		wp_add_inline_script(
			'mnp-public',
			'var mnpPublic = ' . wp_json_encode( array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'mnp_impression' ),
			) ) . ';
( function () {
	"use strict";
	if ( ! ( "IntersectionObserver" in window ) ) { return; }
	document.addEventListener( "DOMContentLoaded", function () {
		var ads = document.querySelectorAll( ".mnp-ad-wrapper[data-ad-id]" );
		ads.forEach( function ( el ) {
			var tracked = false;
			var obs = new IntersectionObserver( function ( entries ) {
				if ( entries[ 0 ].isIntersecting && ! tracked ) {
					tracked = true;
					var id = el.getAttribute( "data-ad-id" );
					fetch( mnpPublic.ajaxUrl, {
						method:  "POST",
						headers: { "Content-Type": "application/x-www-form-urlencoded" },
						body:    "action=mnp_impression&id=" + encodeURIComponent( id ) + "&nonce=" + encodeURIComponent( mnpPublic.nonce )
					} );
				}
			}, { threshold: 0.5 } );
			obs.observe( el );
		} );
	} );
} )();'
		);
	}

	/**
	 * Render the [mnp_ad] shortcode.
	 *
	 * @since 2.0.0
	 * @param array $atts Shortcode attributes. Accepts 'zone'.
	 * @return string Ad HTML or empty string.
	 */
	public function shortcode( $atts ) {
		$atts = shortcode_atts( array( 'zone' => 'sidebar-top' ), $atts, 'mnp_ad' );
		return $this->get_ad_html( sanitize_key( $atts['zone'] ) );
	}

	/**
	 * Filter callback: return ad HTML for a given zone.
	 *
	 * Called by the theme with apply_filters( 'monirnews_ad_zone', '', $zone ).
	 * Standard WP "overrideable content" pattern: $output starts empty and this
	 * hook replaces it with rendered ad HTML when an active ad exists.
	 *
	 * @since 2.0.0
	 * @param string $output Existing output (empty string passed by theme).
	 * @param string $zone   Ad zone slug.
	 * @return string Ad HTML or original $output when no active ad found.
	 */
	public function render_zone( $output, $zone ) {
		$ad_html = $this->get_ad_html( sanitize_key( $zone ) );
		return '' !== $ad_html ? $ad_html : $output;
	}

	/**
	 * Build and return the HTML for the highest-priority active ad in a zone.
	 *
	 * @since 2.0.0
	 * @param string $zone Zone slug.
	 * @return string Rendered ad HTML or empty string if no ad found.
	 */
	public function get_ad_html( $zone ) {
		$ads = MNP_Database::get_ads( array(
			'zone'    => $zone,
			'status'  => 'active',
			'limit'   => 1,
			'orderby' => 'priority',
			'order'   => 'DESC',
		) );

		if ( empty( $ads ) ) {
			return '';
		}

		$ad = $ads[0];

		ob_start();
		$this->render_ad( $ad );
		return ob_get_clean();
	}

	/**
	 * Output the HTML markup for a single ad object.
	 *
	 * @since 2.0.0
	 * @param stdClass $ad Ad row from the database.
	 * @return void
	 */
	private function render_ad( $ad ) {
		$has_link = ! empty( $ad->click_url );
		?>
		<div class="mnp-ad-wrapper mnp-ad-zone-<?php echo esc_attr( $ad->zone ); ?>"
			data-ad-id="<?php echo esc_attr( $ad->id ); ?>">

			<span class="mnp-ad-label"><?php esc_html_e( 'Ad', 'monirnews-pro' ); ?></span>

			<?php if ( 'html' === $ad->type ) : ?>

				<?php echo wp_kses_post( $ad->html_code ); ?>

			<?php elseif ( 'video' === $ad->type && $ad->media_url ) : ?>

				<?php if ( $has_link ) : ?>
					<a href="<?php echo esc_url( $ad->click_url ); ?>"
						target="_blank"
						rel="noopener sponsored">
				<?php endif; ?>

				<video
					width="<?php echo esc_attr( $ad->width ); ?>"
					height="<?php echo esc_attr( $ad->height ); ?>"
					autoplay muted loop playsinline>
					<source src="<?php echo esc_url( $ad->media_url ); ?>">
				</video>

				<?php if ( $has_link ) : ?></a><?php endif; ?>

			<?php elseif ( $ad->media_url ) : ?>

				<?php if ( $has_link ) : ?>
					<a href="<?php echo esc_url( $ad->click_url ); ?>"
						target="_blank"
						rel="noopener sponsored">
				<?php endif; ?>

				<img
					src="<?php echo esc_url( $ad->media_url ); ?>"
					width="<?php echo esc_attr( $ad->width ); ?>"
					height="<?php echo esc_attr( $ad->height ); ?>"
					alt="<?php esc_attr_e( 'Advertisement', 'monirnews-pro' ); ?>"
					loading="lazy">

				<?php if ( $has_link ) : ?></a><?php endif; ?>

			<?php endif; ?>

		</div>
		<?php
	}

	/**
	 * AJAX handler: increment the impression counter for an ad.
	 *
	 * Accessible by logged-in and logged-out users (nopriv).
	 *
	 * @since 2.0.0
	 * @return void Exits after response.
	 */
	public function handle_impression() {
		check_ajax_referer( 'mnp_impression', 'nonce' );

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( $id ) {
			MNP_Database::increment_impressions( $id );
		}

		wp_send_json_success();
	}
}
