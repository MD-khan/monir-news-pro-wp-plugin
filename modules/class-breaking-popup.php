<?php
/**
 * MonirNews Pro — Breaking News Popup module.
 *
 * Displays a dismissible overlay on page load that highlights posts tagged
 * "breaking". Only renders on singular posts, home, and front-page contexts.
 * Never fires inside wp-admin.
 *
 * Settings are stored in wp_options under the key mnp_popup_settings.
 *
 * @package MonirNews_Pro
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MNP_Breaking_Popup
 *
 * @since 2.0.0
 */
class MNP_Breaking_Popup {

	/**
	 * Module settings merged with defaults.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	private $settings;

	/**
	 * Constructor — loads settings and bootstraps hooks when enabled.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->settings = wp_parse_args(
			get_option( 'mnp_popup_settings', array() ),
			array(
				'enabled'   => false,
				'delay'     => 3,
				'once'      => true,
				'bg_color'  => '#2c3e50',
				'txt_color' => '#ffffff',
				'title'     => '',
			)
		);

		if ( empty( $this->settings['enabled'] ) ) {
			return;
		}

		$this->init();
	}

	/**
	 * Register WordPress hooks for this module.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {
		add_action( 'wp_footer',          array( $this, 'render_popup' ) );
		add_action( 'wp_head',            array( $this, 'output_inline_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ) );
	}

	/**
	 * Output inline CSS for the popup overlay.
	 *
	 * Avoids an extra HTTP request for a small amount of CSS.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output_inline_styles() {
		?>
		<style id="mnp-popup-styles">
		.mnp-popup-overlay{position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.6);display:none;align-items:center;justify-content:center}
		.mnp-popup-overlay.mnp-popup-visible{display:flex}
		.mnp-popup-box{position:relative;max-width:560px;width:90%;border-radius:12px;padding:2rem;color:#fff;max-height:90vh;overflow-y:auto}
		.mnp-popup-close{position:absolute;top:1rem;right:1rem;background:rgba(255,255,255,.2);border:none;color:#fff;width:32px;height:32px;border-radius:50%;font-size:1.2rem;cursor:pointer;line-height:1;display:flex;align-items:center;justify-content:center}
		.mnp-popup-close:hover{background:rgba(255,255,255,.35)}
		.mnp-popup-label{display:inline-block;background:#c0392b;font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;padding:2px 8px;border-radius:3px;margin-bottom:.75rem}
		.mnp-popup-header h2{margin:0 0 1.25rem;font-size:1.3rem}
		.mnp-popup-post{display:flex;gap:1rem;padding:.75rem 0;border-bottom:1px solid rgba(255,255,255,.15)}
		.mnp-popup-post:last-child{border-bottom:none}
		.mnp-popup-post img{width:80px;height:55px;object-fit:cover;border-radius:4px;flex-shrink:0}
		.mnp-popup-post h3{margin:0 0 .25rem;font-size:.95rem}
		.mnp-popup-post h3 a{color:inherit;text-decoration:none}
		.mnp-popup-post h3 a:hover{text-decoration:underline}
		.mnp-popup-date{font-size:.8rem;opacity:.7}
		.mnp-popup-footer{margin-top:1.25rem;text-align:center}
		.mnp-popup-btn{display:inline-block;background:rgba(255,255,255,.2);color:#fff;padding:8px 20px;border-radius:6px;text-decoration:none;font-weight:600;font-size:.875rem;transition:.2s}
		.mnp-popup-btn:hover{background:rgba(255,255,255,.35);color:#fff}
		</style>
		<?php
	}

	/**
	 * Enqueue and localise the popup script.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enqueue_script() {
		wp_register_script( 'mnp-popup', '', array(), MNP_VERSION, true );
		wp_enqueue_script( 'mnp-popup' );

		wp_add_inline_script(
			'mnp-popup',
			'var mnpPopupConfig = ' . wp_json_encode( array(
				'delay'   => absint( $this->settings['delay'] ) * 1000,
				'once'    => ! empty( $this->settings['once'] ),
				'bgColor' => sanitize_hex_color( $this->settings['bg_color'] ),
			) ) . ';
( function () {
	"use strict";
	var popupEl = document.getElementById( "mnp-breaking-popup" );
	if ( ! popupEl ) { return; }
	var closeBtn = document.getElementById( "mnp-popup-close" );
	var SESSION_KEY = "mnp_popup_shown";
	function showPopup() {
		if ( mnpPopupConfig.once && sessionStorage.getItem( SESSION_KEY ) ) { return; }
		popupEl.classList.add( "mnp-popup-visible" );
		if ( mnpPopupConfig.once ) { sessionStorage.setItem( SESSION_KEY, "1" ); }
	}
	function closePopup() {
		popupEl.classList.remove( "mnp-popup-visible" );
	}
	if ( closeBtn ) { closeBtn.addEventListener( "click", closePopup ); }
	popupEl.addEventListener( "click", function ( e ) {
		if ( e.target === popupEl ) { closePopup(); }
	} );
	document.addEventListener( "keydown", function ( e ) {
		if ( "Escape" === e.key ) { closePopup(); }
	} );
	setTimeout( showPopup, mnpPopupConfig.delay || 3000 );
} )();'
		);
	}

	/**
	 * Render the breaking-news popup HTML in the page footer.
	 *
	 * Skips rendering on admin screens, and only runs on singular posts, the
	 * homepage, or the front page.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_popup() {
		if ( is_admin() ) {
			return;
		}

		if ( ! is_singular() && ! is_home() && ! is_front_page() ) {
			return;
		}

		$posts = get_posts( array(
			'numberposts'      => 3,
			'tag'              => 'breaking',
			'suppress_filters' => false,
		) );

		if ( empty( $posts ) ) {
			return;
		}

		$bg    = sanitize_hex_color( $this->settings['bg_color']  ?? '#2c3e50' );
		$title = sanitize_text_field( $this->settings['title'] ?? '' );
		?>
		<div id="mnp-breaking-popup"
			class="mnp-popup-overlay"
			role="dialog"
			aria-modal="true"
			aria-labelledby="mnp-popup-title">

			<div class="mnp-popup-box" style="background:<?php echo esc_attr( $bg ); ?>">

				<button class="mnp-popup-close" type="button" id="mnp-popup-close"
					aria-label="<?php esc_attr_e( 'Close popup', 'monirnews-pro' ); ?>">
					&times;
				</button>

				<div class="mnp-popup-header">
					<span class="mnp-popup-label"><?php esc_html_e( 'Breaking News', 'monirnews-pro' ); ?></span>
					<h2 id="mnp-popup-title">
						<?php echo $title ? esc_html( $title ) : esc_html__( 'Latest Updates', 'monirnews-pro' ); ?>
					</h2>
				</div>

				<div class="mnp-popup-posts">
					<?php foreach ( $posts as $post ) : ?>
						<div class="mnp-popup-post">
							<?php $thumb = get_the_post_thumbnail_url( $post->ID, 'medium' ); ?>
							<?php if ( $thumb ) : ?>
								<img src="<?php echo esc_url( $thumb ); ?>"
									alt="<?php echo esc_attr( $post->post_title ); ?>"
									loading="lazy" width="80" height="55">
							<?php endif; ?>
							<div class="mnp-popup-post__content">
								<h3>
									<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
										<?php echo esc_html( $post->post_title ); ?>
									</a>
								</h3>
								<span class="mnp-popup-date">
									<?php echo esc_html( get_the_date( '', $post->ID ) ); ?>
								</span>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="mnp-popup-footer">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mnp-popup-btn">
						<?php esc_html_e( 'View All News &rarr;', 'monirnews-pro' ); ?>
					</a>
				</div>

			</div>
		</div>
		<?php
	}
}
