<?php
/**
 * Unistall Plugin
 * @package Nofollow Post Links
 * @subpackage uninstall.php
 * @since 1.0.0
 */

// if uninstall.php is not called by ClassicPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

/**
 * Is multisite?
 */
if( ! is_multisite() ) {

    // delete settings and tag
    delete_option( 'nofopl_settings' );
    delete_transient( 'nofopl_tab');

    // delete actions
    delete_option('nofopl_global_nofollow_action');
    delete_option('nofopl_global_dofollow_action');

} else {

    // delete metas
    $sites = get_sites();
    $original_blog_id = get_current_blog_id();
    foreach( $sites as $site ) {
        switch_to_blog( $site->blog_id );

        // delete settings and tag
        delete_option( 'nofopl_settings' );
        delete_transient( 'nofopl_tab' );

        // delete actions
        delete_option('nofopl_global_nofollow_action');
        delete_option('nofopl_global_dofollow_action');
        switch_to_blog( $original_blog_id );
    }

}
