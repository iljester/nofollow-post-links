<?php 
/**
 * Functions
 * @package No Follow Post Links
 * @subpackage inc/functions.php
 * @since 1.0.0
 */

/**
 * Get setting
 *
 * @param string $setting
 * @return void
 */
function nofopl_settings( $setting ) {
    $options = get_option( NofoplSettings::OPTIONS_NAME );

   	if( isset( $options[$setting] ) ) {
		return $options[$setting];
   }
   return $options;
}

/**
 * Check if backup exists
 *
 * @param integer $post_id
 * @return boolean
 */
function nofopl_backup_exists( $post_id = 0 ) {

    $backup = NofoplManageNoFollow::getPostLinksMeta( $post_id, true );

    if( $backup === '' ) {
        return false;
    } 

    return true;

}

/**
 * Check backup global exists
 *
 * @return void
 */
function nofopl_is_global_action_follow() {

   if( nofopl_is_global_action_nofollow() || nofopl_is_global_action_dofollow() ) {
        return true;
   }

   return false;

}

/**
 * Check if action dofollow exists
 *
 * @return boolean
 */
function nofopl_is_global_action_nofollow() {
    if( absint( get_option(NofoplManageNoFollow::GLOBAL_NOFOLLOW) ) === 1 ) {
        return true;
    }
    return false;
}

/**
 * Check if action dofollow exists
 *
 * @return boolean
 */
function nofopl_is_global_action_dofollow() {
    if( absint( get_option(NofoplManageNoFollow::GLOBAL_DOFOLLOW) ) === 1 ) {
        return true;
    }
    return false;
}

/**
 * Return domains list
 *
 * @return void
 */
function nofopl_domains_list() {

    $domains = nofopl_settings('whitelist_domains');
    return NofoplManageNoFollow::domainsList($domains);

}

/**
 * Get links from post
 *
 * @param string $content
 * @return array
 */
function nofopl_get_links( $content ) {
    return NofoplManageNoFollow::getLinksFromPost( $content );
}

/**
 * Get url from link
 * 
 * @param mixed $link
 * @return string
 */
function nofopl_get_url_from_link( $link ) {
    return NofoplManageNoFollow::getUrlFromLink( $link );
}

/**
 * Exclude links
 *
 * @param string $link
 * @return boolean
 */
function nofopl_exclude_links( $link ) {
    return NofoplManageNoFollow::excludeLinks( $link );
}

/**
 * Add nofollow attribute
 *
 * @param string $link
 * @return string
 */
function nofopl_add_nofollow( $link ) {
    return NofoplManageNoFollow::addNoFollowLink( $link );
}

/**
 * Add no follow attribute on link
 * 
 * @param mixed $link
 * @return array|string
 */
function nofopl_add_nofollow_link( $link ) {
    return NofoplManageNoFollow::addNoFollowLink( $link );
}

/**
 * Remove nofollow attribute
 *
 * @param string $content
 * @return string
 */
function nofopl_remove_nofollow( $content ) {
    return NofoplManageNoFollow::removeNoFollow( $content );
}