<?php

/**
 * @link              https://https://www.iljester.com
 * @since 			  1.0
 * @package           Nofollow Post Links
 *
 * Plugin Name:       Nofollow Post Links
 * Plugin URI:        https://github.com/iljester/nofollow-post-links
 * Description:       Nofollow manager for your contents
 * Version:           1.0.0
 * Author:            Il Jester
 * Author URI:        https://https://www.iljester.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       nofopl
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Definitions
 */
define( 'NOFOPL_VERSION', '1.0.0' );
define( 'NOFOPL_AUTHOR', 'Il Jester');
define( 'NOFOPL_NAME', 'No Follow Post Links');
define( 'NOFOPL', 'nofopl');
define( 'NOFOPL_PLUGIN_FILE', __FILE__);
define( 'NOFOPL_DIR', plugin_dir_path( __FILE__ ) );
define( 'NOFOPL_URL', plugin_dir_url( __FILE__ ) );


/**
 * Includes
 */
include NOFOPL_DIR . 'inc/init.php';
include NOFOPL_DIR . 'inc/functions.php';
include NOFOPL_DIR . 'inc/nofollow.php';

/**
 * Admin
 */
include NOFOPL_DIR . 'admin/settings.php';
include NOFOPL_DIR . 'admin/ajax-actions.php';
include NOFOPL_DIR . 'admin/metabox.php';

/**
 * Public
 */
include NOFOPL_DIR . 'public/global-actions.php';
include NOFOPL_DIR . 'public/meta-actions.php';

/**
 * Assets
 */
include NOFOPL_DIR . 'assets/update-client/UpdateClient.class.php';

/**
 * Instance
 */
NofoplInit::getInstance();