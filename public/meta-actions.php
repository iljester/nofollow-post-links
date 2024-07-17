<?php 
/**
 * NofoplMetaActions Class
 * @package Nofollow Post Links
 * @subpackage public/meta-actions.php
 * @since 1.0.0
 */

class NofoplMetaActions {

    /**
     * The constructor
     * 
     * @return void
     */
    public function __construct() {
        add_action('wp_footer', array( $this, 'metaActionJavascript' ), 9 );
        add_filter('the_content', array( $this, 'metaActionContentFilter' ), 9);
    }

    /**
     * Action for metabox mode javascript
     * 
     * @return void
     */
    public function metaActionJavascript() {

        // if is set apply mode metabox
        if( (bool) nofopl_settings('apply_mode_metabox') === false ) {
            return;
        }

        // if not content_edit mode
        if( nofopl_settings('global_actions_mode') !== 'javascript' ) {
            return;
        }

        // global action nofollow exists
        if( nofopl_is_global_action_nofollow() ) {
            return;
        }

        // global action dofollow exists
        if( nofopl_is_global_action_dofollow() ) {
            return;
        }

        global $post;

        $links = get_post_meta( $post->ID, NofoplManageNoFollow::POST_LINKS_META, true );
        if( !is_array( $links )) {
            return;
        }
        $urls = [];
        foreach( $links as $link ) {
            $url = nofopl_get_url_from_link( htmlspecialchars_decode( $link ) );
            $urls[] = $url;
        } 
        $urls_encode = json_encode( $urls ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
        ?>

        <script id="nofopl-mbx-actions-javascript">
            jQuery(function($) {
                const urls = '<?php echo esc_html( $urls_encode ); ?>';
                $('.entry-content a').filter(function() {
                    let attr = $(this).attr('rel');
                    if( typeof attr !== 'undefined') {
                        let expl = attr.split(' ');
                        let attrs = expl.filter(function(e) { return e !== 'nofollow' });
                        $(this).attr('rel', attrs.join(' '));
                    }
                    const url = $(this).attr('href');
                    if( urls.includes(url) ) {
                        $(this).attr('rel', 'nofollow');
                    }
                });
            });
        </script><?php

    }

    /**
     * Content filter for metabox action
     * 
     * @param string $content
     * @return string
     */
    public function metaActionContentFilter( $content ) {

        // if is set apply mode metabox
        if( (bool) nofopl_settings('apply_mode_metabox') === false ) {
            return $content;
        }

        // if not content_edit mode
        if( nofopl_settings('global_actions_mode') !== 'content_filter' ) {
            return $content;
        }

        // global action nofollow exists
        if( nofopl_is_global_action_nofollow() ) {
            return $content;
        }

        // global action dofollow exists
        if( nofopl_is_global_action_dofollow() ) {
            return $content;
        }

        global $post;

        $links = get_post_meta( $post->ID, NofoplManageNoFollow::POST_LINKS_META, true );
        if( ! is_array( $links ) ) {
            return $content;
        }
        $links_dec = [];
        $to_replace = [];
        foreach( $links as $link ) {
            $decode = htmlspecialchars_decode( $link );
            $links_dec[] = $decode;
            $to_replace[] = nofopl_add_nofollow_link( $decode );
        }
        $content_filtered = nofopl_remove_nofollow( $content );
        return str_replace( $links_dec, $to_replace, $content_filtered );

    }

}