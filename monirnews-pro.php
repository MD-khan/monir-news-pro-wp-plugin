<?php
/**
 * Plugin Name: MonirNews Pro
 * Plugin URI:  https://monirtechsolutions.com/wp-theme/monirnews
 * Description: Professional ad management and premium features for MonirNews theme.
 * Version:     2.0.0
 * Author:      Md Monirujaman Khan
 * Author URI:  https://monirtechsolutions.com
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: monirnews-pro
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package MonirNews_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MNP_VERSION',    '2.0.0' );
define( 'MNP_PATH',       plugin_dir_path( __FILE__ ) );
define( 'MNP_URL',        plugin_dir_url( __FILE__ ) );
define( 'MNP_BASENAME',   plugin_basename( __FILE__ ) );
define( 'MNP_THEME_SLUG', 'monirnews' );

/**
 * Admin notice shown when MonirNews theme is not active.
 *
 * @since 2.0.0
 * @return void
 */
function mnp_theme_notice() {
	echo '<div class="notice notice-error"><p><strong>MonirNews Pro</strong> '
		. esc_html__( 'requires MonirNews theme to be active.', 'monirnews-pro' )
		. '</p></div>';
}

if ( get_template() !== MNP_THEME_SLUG ) {
	add_action( 'admin_notices', 'mnp_theme_notice' );
	return;
}

require_once MNP_PATH . 'includes/class-database.php';
require_once MNP_PATH . 'includes/class-license.php';
require_once MNP_PATH . 'includes/class-monirnews-pro.php';

register_activation_hook( __FILE__, array( 'MNP_Database', 'create_tables' ) );

add_action( 'plugins_loaded', function () {
	MonirnewsPro::get_instance();
} );
