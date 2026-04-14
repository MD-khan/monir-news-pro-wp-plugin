<?php
/**
 * MonirNews Pro — Core singleton class.
 *
 * Bootstraps the plugin: loads text domain, admin panel, frontend modules,
 * and registers the init hook for expired-ad cleanup.
 *
 * @package MonirNews_Pro
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MonirnewsPro
 *
 * @since 2.0.0
 */
class MonirnewsPro {

	/**
	 * Singleton instance.
	 *
	 * @since 2.0.0
	 * @var MonirnewsPro|null
	 */
	private static $instance = null;

	/**
	 * Return the single class instance, creating it on first call.
	 *
	 * @since 2.0.0
	 * @return MonirnewsPro
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor — private to enforce singleton pattern.
	 *
	 * @since 2.0.0
	 */
	private function __construct() {
		$this->load_textdomain();
		$this->load_admin();
		$this->load_modules();
		$this->init_hooks();
	}

	/**
	 * Load the plugin text domain for i18n.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private function load_textdomain() {
		load_plugin_textdomain(
			'monirnews-pro',
			false,
			dirname( MNP_BASENAME ) . '/languages/'
		);
	}

	/**
	 * Load the admin panel class when running in the WordPress admin.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private function load_admin() {
		if ( is_admin() ) {
			require_once MNP_PATH . 'admin/class-admin.php';
			new MNP_Admin();
		}
	}

	/**
	 * Load and instantiate all frontend module classes.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private function load_modules() {
		require_once MNP_PATH . 'modules/class-ad-display.php';
		require_once MNP_PATH . 'modules/class-breaking-popup.php';
		require_once MNP_PATH . 'modules/class-weather-widget.php';

		new MNP_Ad_Display();
		new MNP_Breaking_Popup();
		// Weather widget self-registers via widgets_init.
	}

	/**
	 * Register WordPress hooks owned by the core class.
	 *
	 * Runs update_expired_ads() on every init so ad status is always current.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'init', array( 'MNP_Database', 'update_expired_ads' ) );
	}
}
