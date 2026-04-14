<?php
/**
 * MonirNews Pro — Admin controller.
 *
 * Registers the top-level menu and all submenu pages, routes requests to
 * the correct view file, handles CRUD form submissions for ads, and
 * processes settings forms for popup and weather modules.
 *
 * @package MonirNews_Pro
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MNP_Admin
 *
 * @since 2.0.0
 */
class MNP_Admin {

	/**
	 * Constructor — registers all hooks.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu',                           array( $this, 'register_menus' ) );
		add_action( 'admin_enqueue_scripts',                array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_mnp_create_ad',             array( $this, 'handle_create_ad' ) );
		add_action( 'admin_post_mnp_update_ad',             array( $this, 'handle_update_ad' ) );
		add_action( 'admin_post_mnp_delete_ad',             array( $this, 'handle_delete_ad' ) );
		add_action( 'admin_post_mnp_save_popup_settings',   array( $this, 'handle_save_popup_settings' ) );
		add_action( 'admin_post_mnp_save_weather_settings', array( $this, 'handle_save_weather_settings' ) );
		add_action( 'admin_post_mnp_save_settings',         array( $this, 'handle_save_settings' ) );
		add_action( 'wp_ajax_mnp_toggle_ad',                array( $this, 'ajax_toggle_ad' ) );
	}

	// -------------------------------------------------------------------------
	// Menu registration
	// -------------------------------------------------------------------------

	/**
	 * Register the top-level MonirNews Pro menu and all submenu pages.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_menus() {
		add_menu_page(
			esc_html__( 'MonirNews Pro', 'monirnews-pro' ),
			'MonirNews Pro',
			'manage_options',
			'monirnews-pro',
			array( $this, 'render_page' ),
			'dashicons-megaphone',
			3
		);

		add_submenu_page(
			'monirnews-pro',
			esc_html__( 'Dashboard', 'monirnews-pro' ),
			esc_html__( 'Dashboard', 'monirnews-pro' ),
			'manage_options',
			'monirnews-pro',
			array( $this, 'render_page' )
		);

		add_submenu_page(
			'monirnews-pro',
			esc_html__( 'Ad Manager', 'monirnews-pro' ),
			'&#128240; ' . esc_html__( 'Ad Manager', 'monirnews-pro' ),
			'manage_options',
			'monirnews-pro-ads',
			array( $this, 'render_page' )
		);

		add_submenu_page(
			'monirnews-pro',
			esc_html__( 'Popup Manager', 'monirnews-pro' ),
			'&#128226; ' . esc_html__( 'Popup Manager', 'monirnews-pro' ),
			'manage_options',
			'monirnews-pro-popup',
			array( $this, 'render_page' )
		);

		add_submenu_page(
			'monirnews-pro',
			esc_html__( 'Weather', 'monirnews-pro' ),
			'&#127780; ' . esc_html__( 'Weather', 'monirnews-pro' ),
			'manage_options',
			'monirnews-pro-weather',
			array( $this, 'render_page' )
		);

		add_submenu_page(
			'monirnews-pro',
			esc_html__( 'Settings', 'monirnews-pro' ),
			'&#9881; ' . esc_html__( 'Settings', 'monirnews-pro' ),
			'manage_options',
			'monirnews-pro-settings',
			array( $this, 'render_page' )
		);
	}

	// -------------------------------------------------------------------------
	// Asset enqueueing
	// -------------------------------------------------------------------------

	/**
	 * Enqueue admin CSS and JS only on MonirNews Pro pages.
	 *
	 * @since 2.0.0
	 * @param string $hook Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_assets( $hook ) {
		if ( false === strpos( $hook, 'monirnews-pro' ) ) {
			return;
		}

		wp_enqueue_style(
			'mnp-admin',
			MNP_URL . 'admin/css/admin.css',
			array(),
			MNP_VERSION
		);

		wp_enqueue_media();

		wp_enqueue_script(
			'mnp-admin',
			MNP_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			MNP_VERSION,
			true
		);

		wp_localize_script(
			'mnp-admin',
			'mnpAdmin',
			array(
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'toggleNonce'   => wp_create_nonce( 'mnp_toggle_ad' ),
				'mediaTitle'    => esc_html__( 'Select Ad Media', 'monirnews-pro' ),
				'mediaButton'   => esc_html__( 'Use this media', 'monirnews-pro' ),
				'confirmDelete' => esc_html__( 'Are you sure you want to delete this ad? This cannot be undone.', 'monirnews-pro' ),
			)
		);
	}

	// -------------------------------------------------------------------------
	// Page rendering & routing
	// -------------------------------------------------------------------------

	/**
	 * Main render callback for all MonirNews Pro admin pages.
	 *
	 * Outputs the shared wrapper, header, and subnav, then delegates to the
	 * correct view file based on the current page slug.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'monirnews-pro' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : 'monirnews-pro';

		echo '<div class="wrap mnp-admin-wrap">';
		$this->render_header();
		$this->render_subnav( $page );

		switch ( $page ) {
			case 'monirnews-pro-ads':
				$this->route_ads_page();
				break;
			case 'monirnews-pro-popup':
				require MNP_PATH . 'admin/views/popup-manager.php';
				break;
			case 'monirnews-pro-weather':
				require MNP_PATH . 'admin/views/weather.php';
				break;
			case 'monirnews-pro-settings':
				require MNP_PATH . 'admin/views/settings.php';
				break;
			default:
				require MNP_PATH . 'admin/views/dashboard.php';
		}

		echo '</div><!-- .mnp-admin-wrap -->';
	}

	/**
	 * Sub-route within the Ads page: list, create, or edit.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private function route_ads_page() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list';

		if ( 'create' === $action ) {
			require MNP_PATH . 'admin/views/ad-create.php';
			return;
		}

		if ( 'edit' === $action ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$ad_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
			$ad    = $ad_id ? MNP_Database::get_ad( $ad_id ) : null;

			if ( ! $ad ) {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Ad not found.', 'monirnews-pro' ) . '</p></div>';
				require MNP_PATH . 'admin/views/ad-manager.php';
				return;
			}

			require MNP_PATH . 'admin/views/ad-edit.php';
			return;
		}

		require MNP_PATH . 'admin/views/ad-manager.php';
	}

	/**
	 * Output the shared dark admin header bar.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_header() {
		?>
		<div class="mnp-admin-header">
			<h1>
				MonirNews <span class="mnp-pro-badge">PRO</span>
			</h1>
			<span style="opacity:0.6;font-size:0.85rem;">
				v<?php echo esc_html( MNP_VERSION ); ?>
			</span>
		</div>
		<?php
	}

	/**
	 * Output the sub-navigation tab bar.
	 *
	 * @since 2.0.0
	 * @param string $active_page Slug of the currently active page.
	 * @return void
	 */
	public function render_subnav( $active_page ) {
		$nav = array(
			'monirnews-pro'          => esc_html__( 'Dashboard', 'monirnews-pro' ),
			'monirnews-pro-ads'      => '&#128240; ' . esc_html__( 'Ad Manager', 'monirnews-pro' ),
			'monirnews-pro-popup'    => '&#128226; ' . esc_html__( 'Popup', 'monirnews-pro' ),
			'monirnews-pro-weather'  => '&#127780; ' . esc_html__( 'Weather', 'monirnews-pro' ),
			'monirnews-pro-settings' => '&#9881; ' . esc_html__( 'Settings', 'monirnews-pro' ),
		);
		?>
		<div class="mnp-subnav">
			<?php foreach ( $nav as $slug => $label ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $slug ) ); ?>"
					class="<?php echo esc_attr( $active_page === $slug ? 'active' : '' ); ?>">
					<?php echo wp_kses( $label, array() ); ?>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
	}

	// -------------------------------------------------------------------------
	// Form handlers — Ad CRUD
	// -------------------------------------------------------------------------

	/**
	 * Handle Create Ad form submission.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_create_ad() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'monirnews-pro' ) );
		}

		check_admin_referer( 'mnp_create_ad_nonce', 'mnp_nonce' );

		$data = $this->collect_ad_post_data();

		if ( empty( $data['name'] ) ) {
			wp_safe_redirect( add_query_arg(
				array( 'page' => 'monirnews-pro-ads', 'action' => 'create', 'error' => 'invalid' ),
				admin_url( 'admin.php' )
			) );
			exit;
		}

		$id = MNP_Database::insert_ad( $data );

		if ( $id ) {
			wp_safe_redirect( add_query_arg(
				array( 'page' => 'monirnews-pro-ads', 'message' => 'created' ),
				admin_url( 'admin.php' )
			) );
		} else {
			wp_safe_redirect( add_query_arg(
				array( 'page' => 'monirnews-pro-ads', 'action' => 'create', 'error' => 'db' ),
				admin_url( 'admin.php' )
			) );
		}
		exit;
	}

	/**
	 * Handle Update Ad form submission.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_update_ad() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'monirnews-pro' ) );
		}

		check_admin_referer( 'mnp_update_ad_nonce', 'mnp_nonce' );

		$ad_id = isset( $_POST['ad_id'] ) ? absint( $_POST['ad_id'] ) : 0;

		if ( ! $ad_id || ! MNP_Database::get_ad( $ad_id ) ) {
			wp_die( esc_html__( 'Invalid ad ID.', 'monirnews-pro' ) );
		}

		MNP_Database::update_ad( $ad_id, $this->collect_ad_post_data() );

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'monirnews-pro-ads', 'message' => 'updated' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/**
	 * Handle Delete Ad form submission.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_delete_ad() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'monirnews-pro' ) );
		}

		check_admin_referer( 'mnp_delete_ad_nonce', 'mnp_nonce' );

		$ad_id = isset( $_POST['ad_id'] ) ? absint( $_POST['ad_id'] ) : 0;

		if ( $ad_id ) {
			MNP_Database::delete_ad( $ad_id );
		}

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'monirnews-pro-ads', 'message' => 'deleted' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	// -------------------------------------------------------------------------
	// Form handlers — Module settings
	// -------------------------------------------------------------------------

	/**
	 * Handle Save Popup Settings form submission.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_save_popup_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'monirnews-pro' ) );
		}

		check_admin_referer( 'mnp_popup_settings_nonce', 'mnp_nonce' );

		$settings = array(
			'enabled'   => ! empty( $_POST['enabled'] ),
			'delay'     => absint( $_POST['delay'] ?? 3 ),
			'once'      => ! empty( $_POST['once'] ),
			'bg_color'  => sanitize_hex_color( $_POST['bg_color']  ?? '#2c3e50' ),
			'txt_color' => sanitize_hex_color( $_POST['txt_color'] ?? '#ffffff' ),
			'title'     => sanitize_text_field( $_POST['title']    ?? '' ),
		);

		update_option( 'mnp_popup_settings', $settings );

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'monirnews-pro-popup', 'message' => 'saved' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/**
	 * Handle Save Weather Settings form submission.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_save_weather_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'monirnews-pro' ) );
		}

		check_admin_referer( 'mnp_weather_settings_nonce', 'mnp_nonce' );

		$settings = array(
			'enabled' => ! empty( $_POST['enabled'] ),
			'api_key' => sanitize_text_field( $_POST['api_key'] ?? '' ),
			'city'    => sanitize_text_field( $_POST['city']    ?? 'London' ),
			'unit'    => in_array( $_POST['unit'] ?? 'C', array( 'C', 'F' ), true )
				? sanitize_key( $_POST['unit'] )
				: 'C',
		);

		update_option( 'mnp_weather_settings', $settings );

		// Bust the weather cache when settings change.
		delete_transient( 'mnp_weather_' . md5( strtolower( $settings['city'] ) ) );

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'monirnews-pro-weather', 'message' => 'saved' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/**
	 * Handle Save General Settings form submission.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'monirnews-pro' ) );
		}

		check_admin_referer( 'mnp_settings_nonce', 'mnp_nonce' );

		$license_key = sanitize_text_field( wp_unslash( $_POST['license_key'] ?? '' ) );

		if ( ! empty( $license_key ) ) {
			( new MNP_License() )->activate( $license_key );
		}

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'monirnews-pro-settings', 'message' => 'saved' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	// -------------------------------------------------------------------------
	// AJAX handler
	// -------------------------------------------------------------------------

	/**
	 * AJAX handler: toggle an ad's status between active and paused.
	 *
	 * @since 2.0.0
	 * @return void Sends JSON response and exits.
	 */
	public function ajax_toggle_ad() {
		check_ajax_referer( 'mnp_toggle_ad', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'monirnews-pro' ) ) );
		}

		$ad_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$ad    = $ad_id ? MNP_Database::get_ad( $ad_id ) : null;

		if ( ! $ad ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Ad not found.', 'monirnews-pro' ) ) );
		}

		$new_status = ( 'active' === $ad->status ) ? 'paused' : 'active';
		MNP_Database::update_ad( $ad_id, array( 'status' => $new_status ) );

		wp_send_json_success( array( 'status' => $new_status ) );
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Collect raw ad field values from the current POST request.
	 *
	 * Sanitization is handled by MNP_Database::insert_ad / update_ad.
	 *
	 * @since 2.0.0
	 * @return array Raw field values keyed by column name.
	 */
	private function collect_ad_post_data() {
		return array(
			'name'      => wp_unslash( $_POST['name']       ?? '' ),       // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			'type'      => wp_unslash( $_POST['type']       ?? 'image' ),  // phpcs:ignore
			'media_id'  => wp_unslash( $_POST['media_id']   ?? 0 ),        // phpcs:ignore
			'media_url' => wp_unslash( $_POST['media_url']  ?? '' ),       // phpcs:ignore
			'click_url' => wp_unslash( $_POST['click_url']  ?? '' ),       // phpcs:ignore
			'zone'      => wp_unslash( $_POST['zone']       ?? 'sidebar-top' ), // phpcs:ignore
			'start_date' => wp_unslash( $_POST['start_date'] ?? current_time( 'mysql' ) ), // phpcs:ignore
			'end_date'  => wp_unslash( $_POST['end_date']   ?? '' ),       // phpcs:ignore
			'status'    => wp_unslash( $_POST['status']     ?? 'active' ), // phpcs:ignore
			'priority'  => wp_unslash( $_POST['priority']   ?? 10 ),       // phpcs:ignore
			'width'     => wp_unslash( $_POST['width']      ?? 300 ),      // phpcs:ignore
			'height'    => wp_unslash( $_POST['height']     ?? 250 ),      // phpcs:ignore
			'html_code' => wp_unslash( $_POST['html_code']  ?? '' ),       // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		);
	}
}
