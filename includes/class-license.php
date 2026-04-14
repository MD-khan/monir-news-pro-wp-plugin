<?php
/**
 * MonirNews Pro — License class.
 *
 * Manages license key storage and activation status.
 * Full Freemius integration can be wired in by replacing the activate()
 * method with an API call to the licensing server.
 *
 * @package MonirNews_Pro
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MNP_License
 *
 * @since 2.0.0
 */
class MNP_License {

	/**
	 * Option key for the stored license key.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	const OPTION_KEY = 'mnp_license_key';

	/**
	 * Option key for the license status.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	const OPTION_STATUS = 'mnp_license_status';

	/**
	 * Return the current license status string.
	 *
	 * @since 2.0.0
	 * @return string 'active', 'inactive', or 'invalid'.
	 */
	public function get_status() {
		return get_option( self::OPTION_STATUS, 'inactive' );
	}

	/**
	 * Return the stored license key (masked for display).
	 *
	 * @since 2.0.0
	 * @return string License key or empty string.
	 */
	public function get_license_key() {
		return get_option( self::OPTION_KEY, '' );
	}

	/**
	 * Return whether the license is currently active.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_valid() {
		return 'active' === $this->get_status();
	}

	/**
	 * Activate a license key.
	 *
	 * Stores the key and marks status as active. Replace the body of this
	 * method with a real API call for production use.
	 *
	 * @since 2.0.0
	 * @param string $key Raw license key.
	 * @return bool True on success.
	 */
	public function activate( $key ) {
		$key = sanitize_text_field( $key );

		if ( empty( $key ) ) {
			return false;
		}

		update_option( self::OPTION_KEY,    $key );
		update_option( self::OPTION_STATUS, 'active' );

		return true;
	}

	/**
	 * Deactivate the current license.
	 *
	 * @since 2.0.0
	 * @return bool Always true.
	 */
	public function deactivate() {
		update_option( self::OPTION_STATUS, 'inactive' );
		return true;
	}
}
