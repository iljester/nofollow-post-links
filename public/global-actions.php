<?php
/**
 * NofoplGlobalActions Class
 * @package No Follow Post Links
 * @subpackage public/global-actions.php
 * @since 1.0.0
 */

class NofoplGlobalActions {

    /**
     * The constructor
     * 
     * @return void
     */
    public function __construct() {
        add_action('wp_footer', array( $this, 'globalActionJavascript'));
        add_filter('the_content', array( $this, 'globalActionContentFilter'));
    }

    /**
     * Global actions via javascript
     *
     * @return void
     */
    public function globalActionJavascript() {

        if( nofopl_settings('global_actions_mode') !== 'javascript') {
            return;
        }

        $post_type = get_post_type();
        if( !in_array( $post_type, nofopl_settings('post_types') ) ) {
            return;
        }

        $action_nf = nofopl_is_global_action_nofollow() ? 1 : 0;
        $action_df = nofopl_is_global_action_dofollow() ? 1 : 0;
        $domains_list = wp_json_encode( nofopl_domains_list() );
        ?>
        <script id="nofopl-actions-javascript">
            const actionNF = '<?php echo esc_js( $action_nf); ?>';
            const actionDF = '<?php echo esc_js( $action_df); ?>';
            const domainsList = '<?php echo sanitize_text_field( $domains_list ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>';
            const urlLocation = location.hostname.replaceAll(/(www\.)?/gi, '');
            jQuery(function($) {
                $('.entry-content a').filter(function() {
                    const urlExternal = this.hostname.replaceAll(/(www\.)?/gi, '');
                    if( urlLocation !== urlExternal ) {
                        if( parseInt( actionNF ) === 1 && parseInt( actionDF ) === 0 ) {
                            if( ! domainsList.includes(urlExternal) )
                                $(this).attr('rel', 'nofollow');
                        } 
                        else if( parseInt( actionDF ) === 1 && parseInt( actionNF ) === 0 ) {
                            $(this).removeAttr('rel', 'nofollow');
                        }
                    }
                });
            });
        </script>

        <?php

    }

    /**
     * Global action via content filter
     * 
     * @param string $content
     * @return mixed
     */
    public function globalActionContentFilter( $content ) {

        if( nofopl_settings('global_actions_mode') !== 'content_filter') {
            return $content;
        }
    
        $post_type = get_post_type();
        if( !in_array( $post_type, nofopl_settings('post_types') ) ) {
            return;
        }
    
        if( nofopl_is_global_action_nofollow() && ! nofopl_is_global_action_dofollow() ) {
    
            $post_links = nofopl_get_links( $content );
    
            $selected_links = [];
            foreach( $post_links as $link ) {
    
                if( true === nofopl_exclude_links( $link ) ) {
                    $selected_links[] = $link;
                }
    
                $selected_links[] = nofopl_add_nofollow_link( $link );
    
            }
    
            $content = str_replace( $post_links, $selected_links, $content );
            return $content;
    
        }
    
        elseif(  ! nofopl_is_global_action_nofollow() && nofopl_is_global_action_dofollow() ) {
            $content = nofopl_remove_nofollow( $content );
            return $content;
        }
    
        return $content;
    
    } 

}