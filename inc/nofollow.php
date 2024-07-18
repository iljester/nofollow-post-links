<?php
/**
 * NofoplManageNoFollow Class
 * @package Nofollow Post Links
 * @subpackage inc/nofollow.php
 * @since 1.0.0
 */

class NofoplManageNoFollow {

    protected $_options;
    protected $_posts;

    const POST_LINKS_META = '_nofopl_post_links';
    const POST_LINKS_META_BKP = '_nofopl_post_links_bkp';
    const GLOBAL_DOFOLLOW = 'nofopl_global_dofollow_action';
    const GLOBAL_NOFOLLOW = 'nofopl_global_nofollow_action';

    /**
     * The constructor
     */
    public function __construct() {

        $this->_options = get_option( NofoplSettings::OPTIONS_NAME );

        $post_types = $this->_options['post_types'];
        $this->_posts = get_posts(
            ['post_type' => $post_types, 'numberposts' => -1]
        );  

    }

    /**
     * Get links from post
     *
     * @param string $content
     * @param boolean $remove_nofollow
     * @return array
     */
    public static function getLinksFromPost( $content, $remove_nofollow = false ) {
        preg_match_all('/(<a[^>]+>)/i', $content, $matches);
        if( (bool) $remove_nofollow === true ) {
            return array_map(function($value) {
                return self::removeNoFollowFromLink( $value );
            }, $matches[0] );
        }
        return $matches[0];
    }

    /**
     * Remove noFollow Link
     * 
     * @param string $link
     * @return array|string|null
     */
    public static function removeNoFollowLink( $link ) {
        preg_match('/\s*rel="(.*?)"\s*/', $link, $match);

        if( !isset( $match[1] ) ) {
            return $link;
        }
        
        $attrs = explode( ' ', trim($match[1]) );
        $values = array_filter($attrs, function ($v) { if( $v !== 'nofollow' ) return $v; });
        if( count($values) > 1 ) {
            $attr = implode( ' ', $values );
        } else {
            $attr = implode( '', $values );
        }
        $link = str_replace( 'rel="' . $match[1] . '"', 'rel="' . $attr . '"', $link );
        $link = preg_replace( '/\s*rel=""\s*/', ' ', $link);
        
        return $link;
    }
    
    /**
     * Remove nofollow in content
     * 
     * @param string $content
     * @param bool $link
     * @return mixed
     */
    public static function removeNoFollow( $content, $link = false ) {
        
        if( (bool) $link === true ) {
            return self::removeNoFollowLink( $content );
        } else {
            preg_match_all('/(<a[^>]+>)/i', $content, $matches);
            if( ! isset( $matches[0] ) ) {
                return $content;
            }
        }
        
        foreach( $matches[0] as $m ) {
            if( stripos( $m, 'rel') !== false ) {
                preg_match('/\s*rel="(.*?)"\s*/', $m, $match);
                $attrs = explode( ' ', trim($match[1]) );
                $values = array_filter($attrs, function ($v) { if( $v !== 'nofollow' ) return $v; });
                if( count($values) > 1 ) {
                    $attr = implode( ' ', $values );
                } else {
                    $attr = implode( '', $values );
                }
                $content = str_replace( 'rel="' . $match[1] . '"', 'rel="' . $attr . '"', $content );
                $content = preg_replace( '/\s*rel=""\s*/', ' ', $content);
                
            } else {
                continue;
            }
        }
       
        return $content;
        
    }
    
    /**
     * Add nofollow Link
     * 
     * @param string $link
     * @return array|string
     */
    public static function addNoFollowLink( $link ) {
        
        if( stripos( $link, ' rel=') !== false ) {
            preg_match('/\s*rel="(.*?)"\s*/', $link, $match);
            $attrs = explode( ' ', trim($match[1]) );
            if( count($attrs) > 0 ) {
                if( !in_array( 'nofollow', $attrs ) ) {
                    $attrs[] = 'nofollow';
                }
                $attr = implode( ' ', $attrs );
            } else {
                $attr = '';
            }
            $link = str_replace( 'rel="' . $match[1] . '"', 'rel="' . $attr . '"', $link );
        } else {
            $link = str_replace( '<a', '<a rel="nofollow"', $link );
        }
        
        return $link;
        
    }
    
    /**
     * Add nofollow in content
     * 
     * @param string $content
     * @param bool $link
     * @return mixed
     */
    public static function addNofollow( $content, $link = false ) {
        
        if( (bool) $link === true ) {
            return self::addNofollowLink( $content );
        } else {
            preg_match_all('/(<a[^>]+>)/i', $content, $matches);
            if( ! isset( $matches[0] ) ) {
                return $content;
            }
        }
        
        foreach( $matches[0] as $m ) {
            if( stripos( $m, ' rel=') !== false ) {
                preg_match('/\s*rel="(.*?)"\s*/', $m, $match);
                $attrs = explode( ' ', trim($match[1]) );
                if( count($attrs) > 0 ) {
                    if( !in_array( 'nofollow', $attrs ) ) {
                        $attrs[] = 'nofollow';
                    }
                    $attr = implode( ' ', $attrs );
                } else {
                    $attr = '';
                }
                $mn = str_replace( 'rel="'. $match[1] .'"', 'rel="' . $attr . '"', $m );
            } else {
                $mn = str_replace( '<a', '<a rel="nofollow"', $m );
            }
            $content = str_replace( $m, $mn, $content );
        }
        
        return $content;
    }

    /**
     * Check if link has nofollow
     * 
     * @param string $link
     * @return bool
     */
    public static function linkHasNofollow( $link ) {
        if( stripos( $link, ' rel=') !== false ) {
            preg_match('/rel="(.*?)"/', $link, $match );
            if( stripos( $match[1], 'nofollow' ) !== false ) {
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * Update post link meta
     * 
     * @param int $post_id
     * @param array $value
     * 
     * @return void
     */
    public static function updatePostLinksMeta( $post_id, $value = [], $backup = false ) {
        $key = (bool) $backup === true ? self::POST_LINKS_META_BKP : self::POST_LINKS_META;
        update_post_meta( $post_id, $key, $value );
    }

    /**
     * Get post links meta
     *
     * @param int $post_id
     * @param string $key
     * @return array
     */
    public static function getPostLinksMeta( $post_id, $backup = false ) {
        $key = (bool) $backup === true ? self::POST_LINKS_META_BKP : self::POST_LINKS_META;
        return get_post_meta( $post_id, $key, true );
    }

    /**
     * Delete post link meta
     * 
     * @param int $post_id
     * @param array $value
     * 
     * @return void
     */
    public static function deletePostLinksMeta( $post_id, $backup = false ) {
        $key = (bool) $backup === true ? self::POST_LINKS_META_BKP : self::POST_LINKS_META;
        delete_post_meta( $post_id, $key );
    }

    /**
     * Save post action
     * 
     * @param int $post_id
     * @param string $content
     * 
     * @return void
     */
    public static function savePost( $post_id, $content ) {
        $current_post = array(
            'ID'           => $post_id,
            'post_content' => $content,
        );
    
        wp_update_post( $current_post  );
    }

    /**
     * Check if action nofollow exists
     *
     * @return bool
     */
    public static function isGlobalActionNofollow() {
        if( absint( get_option(self::GLOBAL_NOFOLLOW) ) === 1 ) {
            return true;
        }
        return false;
    }

    /**
     * Check if action dofollow exists
     *
     * @return bool
     */
    public static function isGlobalActionDofollow() {
        if( absint( get_option(self::GLOBAL_DOFOLLOW) ) === 1 ) {
            return true;
        }
        return false;
    }

    /**
     * Backup exists
     * 
     * @param int $post_id
     * @return boolean
     */
    public static function backupExists( $post_id ) {
        if( ! empty( self::getPostLinksMeta( $post_id, true ) ) ) {
            return true;
        }
        return false;
    }

    /**
     * Update action
     * 
     * @param string $action
     * @param string $remove
     * 
     * @return void
     */
    public static function updateAction( $action, $remove ) {

        // add action
        update_option($action, 1 );

        // delete action nofollow

        if( $action === self::GLOBAL_DOFOLLOW) {
            if( self::isGlobalActionNofollow() ) {
                delete_option($remove);
            }
        }
        elseif ( $action === self::GLOBAL_NOFOLLOW ) {
            if( self::isGlobalActionDofollow() ) {
                delete_option($remove);
            }
        }

    }

    /**
     * Remove host prefix
     *
     * @param string $host
     * @return void
     */
    public static function removeHostPrefix( $host ) {
        return preg_replace('/(www\.)?/', '', $host );
    }

     /**
     * Get host link
     *
     * @param string $link
     * @return string
     */
    public static function getHostLink( $link ) {
        preg_match('/href="(.*?)"/i', $link, $match );
        $parse = wp_parse_url( $match[1] );
        $host = self::removeHostPrefix( $parse['host'] );
        return $host;
    }

    /**
     * Check internal links
     *
     * @param string $url
     * @return bool
     */
    public static function isInternalUrl( $url ) {

        if( ! isset( $url ) ) {
            return false;
        }

        // passed url
        $parse = wp_parse_url( $url );
        $pre_host_url = $parse['host'];
        $host_url = self::removeHostPrefix( $pre_host_url );

        // current site
        $site = wp_parse_url( home_url('/') );
        $pre_host_site = $site['host'];
        $host_site = self::removeHostPrefix( $pre_host_site );

        if( $host_url === $host_site ) {
            return true;
        }
        return false;
    }

    /**
     * Return domains list
     *
     * @param string $domains
     * @return array
     */
    public static function domainsList( $domains ) {
        $pre_domains_list = preg_split("/\n|\r\n/", $domains );
        $domains_list = array_map( ['NofoplManageNoFollow', 'removeHostPrefix'], $pre_domains_list );
        return $domains_list;
    }

    /**
     * Domain dofollow
     *
     * @param string $url
     * @return bool
     */
    public static function isDoFollowDomain( $url ) {

        if( ! isset( $url ) ) {
            return false;
        }

        $parse = wp_parse_url( $url );
        $pre_host_url = $parse['host'];
        $host_url = self::removeHostPrefix( $pre_host_url );

        $options = get_option( NofoplSettings::OPTIONS_NAME );
        $domains = $options['whitelist_domains'];
        $domains_list = self::domainsList( $domains );

        if( in_array( $host_url, $domains_list ) ) {
            return true;
        }

        return false;

    }

    /**
     * Exclude links from actions
     * 
     * @param string $link
     * @return boolean
     */
    public static function excludeLinks( $link ) {

        preg_match('/href="(.*?)"/i', $link, $match );
                
        if( self::isInternalUrl( $match[1] ) || self::isDoFollowDomain( $match[1] ) ) {
            return true;
        } 
        return false;

    }

    /**
     * Encode link after stripped no follow
     * 
     * @param string $link
     * @return string
     */
    public static function encodeStripNoFollow( $link ) {
        return htmlspecialchars( self::removeNoFollowLink($link) );
    }

    /**
     * Parse nofollow in the link
     * 
     * @param string $link
     * @return array
     */
    public static function noFollowParseLink( $link ) {

        // encode link, after remove nofollow
        $encode_link = self::encodeStripNoFollow( $link );
        
        // decode link
        $decode_link = self::addNoFollowLink( $link );
        
        return ['decode_link' => $decode_link, 'encode_link' => $encode_link];
    }

    /**
     * Extract backup
     *
     * @param string $type
     * @param array $backup
     * @return array
     */
    public static function extractBackup( $type = '', $backup = [] ) {

        $content = [];
        $meta = [];

        /**
         * Structure backup[0]['content' => '', 'meta' => ''];
         */
        foreach( $backup as $k => $v ) {
            $content[] = $v['content'];
            $meta[] = $v['meta'];
        }

        if( $type === 'content') {
            return $content;
        }
        elseif( $type === 'meta') {
            return $meta;
        }
        else {
            return [];
        }

    }

    /**
     * Backup validate
     *
     * @param array $backup
     * @return bool
     */
    public static function backupValidate( $backup ) {

        if( ! is_array( $backup ) ) {
            return false;
        }

        $response = true;
        foreach( $backup as $k => $v ) {
            if( ! isset( $v['content'] ) ) {
                $response = false;
                break;
            }

            if( ! isset( $v['meta']) ) {
                $response = false;
                break;
            }
        }
        return $response;

    }

    /**
     * Get url from link
     *
     * @param string $link
     * @return string
     */
    public static function getUrlFromLink( $link ) {
        preg_match('/href="(.*?)"/i', $link, $match );
        return $match[1];
    }

    /**
     * Display info action in metabox
     *
     * @return void
     */
    public static function infoActionOnMetaBox() {

        $action_nf = self::isGlobalActionNofollow();
        $action_df = self::isGlobalActionDofollow();
        $mode = get_option(NofoplSettings::OPTIONS_NAME)['global_actions_mode'];

        $notice = sprintf( esc_html__( 'Mode used: %s.', 'nofopl'), '<strong class="nofopl-text-blue">"' . esc_html( $mode ) . '"</strong>');
        if( $action_nf === true &&  $action_df === false ) {
            $notice = sprintf( esc_html__('Global %1$s is activated. | Mode used: %2$s.', 'nofopl'), '<strong class="nofopl-text-red">"No Follow"</strong>', '<strong class="nofopl-text-blue">"' . esc_html( $mode ) . '"</strong>' );
        }
        else if( $action_nf === true && $action_df === false ) {
            $notice = sprintf( esc_html__('Global %1$s is activated. | Mode used: %2$s.', 'nofopl'), '<strong class="nofopl-text-green">"Do Follow"</strong>', '<strong class="nofopl-text-blue">"' . esc_html( $mode ) . '"</strong>' );
        }
        ?>
            
                <?php printf(
                    '<p class="nofopl-tip-label"><span class="dashicons dashicons-info-outline" style="vertical-align: -5px;"></span> %s</p>',
                        $notice // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ); ?>
            </p>
        <?php

    }

    /**
     * Add global dofollow (aka remove nofollow)
     *
     * @return void
     */
    public function globalDoFollow() {

        if( $this->_options['global_actions_mode'] !== 'content_edit' ) {
            self::updateAction(self::GLOBAL_DOFOLLOW, self::GLOBAL_NOFOLLOW);
            return;
        }

        foreach( $this->_posts as $post ) {

            self::deletePostLinksMeta( $post->ID );
            
            $content = self::removeNoFollow( $post->post_content );
            self::updatePostLinksMeta( $post->ID );
            self::savePost( $post->ID, $content );
        }

        self::updateAction(self::GLOBAL_DOFOLLOW, self::GLOBAL_NOFOLLOW);

    }

    /**
     * Add global nofollow
     *
     * @return void
     */
    public function globalNoFollow() {

        if( $this->_options['global_actions_mode'] !== 'content_edit' ) {
            self::updateAction(self::GLOBAL_NOFOLLOW, self::GLOBAL_DOFOLLOW);
            return;
        }

        foreach( $this->_posts as $post ) :
        
            $post_links = self::getLinksFromPost( $post->post_content );

            if( empty( $post_links ) ) {
                continue;
            }

            $filtered_links  = [];
            $decoded_links   = [];
            $selected_links  = [];

            foreach( $post_links as $link ) {
    
                if( true === self::excludeLinks( $link ) ) {
                    continue;
                }

                /**
                 * Capture links without links excluded
                 */
                $filtered_links[] = $link;

                /**
                 * Create arrays for replace
                 */
                $nofollow_parse_link = self::nofollowParseLink( $link );
                $selected_links[]    = $nofollow_parse_link['encode_link'];
                $decoded_links[]     = $nofollow_parse_link['decode_link'];

            }
        
            // replace and save
            $content = str_replace( $filtered_links, $decoded_links,  $post->post_content );
            self::savePost( $post->ID, $content );

            // update meta
            self::updatePostLinksMeta( $post->ID, $selected_links );

        endforeach;

        // update action
        self::updateAction(self::GLOBAL_NOFOLLOW, self::GLOBAL_DOFOLLOW);

    }

    /**
     * Restore post links
     *
     * @return void
     */
    public function restorePostSettings() {

        if( $this->_options['global_actions_mode'] !== 'content_edit' ) {
            delete_option(self::GLOBAL_DOFOLLOW);
            delete_option(self::GLOBAL_NOFOLLOW);
            return;
        }

        foreach( $this->_posts as $post ) :

            $post_links  = self::getLinksFromPost( $post->post_content );

            if( empty( $post_links ) ) {
                continue;
            }

            $backup = self::getPostLinksMeta( $post->ID, true );

            /**
             * Probably, there is no backup, or there is no nofollow. 
             * In this case, the restore setting must remove all nofollows.
             */
            if( false === self::backupValidate( $backup ) ) {
                $content = self::removeNoFollow( $post->post_content );
                self::deletePostLinksMeta( $post->ID );
                self::savePost( $post->ID, $content );
                continue;
            }

            // restore post links meta
            $backup_meta = self::extractBackup('meta', $backup);
            self::updatePostLinksMeta( $post->ID, $backup_meta );

            // restore post content
            $decoded_links = array_map( function( $value ) {
                return htmlspecialchars_decode( $value );
            }, self::extractBackup('content', $backup) );
            
            $filtered_links = [];
            foreach( $post_links as $link ) {

                if( true === self::excludeLinks( $link ) ) {
                    continue;
                }

                $filtered_links[] = $link;

            }

            // update and save
            $content = str_replace( $filtered_links, $decoded_links, $post->post_content);
            self::savePost( $post->ID, $content );

        endforeach;

        // delete actions
        delete_option(self::GLOBAL_DOFOLLOW);
        delete_option(self::GLOBAL_NOFOLLOW);

    }

    /**
     * Map no follow in the content
     * 
     * @return void
     */
    public function mapNoFollow() {

        foreach( $this->_posts as $post ) :

            $post_links = self::getLinksFromPost( $post->post_content );

            $prev_settings = ! is_array( self::getPostLinksMeta( $post->ID ) ) ?
                [] : self::getPostLinksMeta( $post->ID );
            $prev_backup   = ! is_array( self::getPostLinksMeta( $post->ID, true ) ) ?
                [] : self::getPostLinksMeta( $post->ID, true );

            foreach( $post_links as $link ) {

                if( true === self::excludeLinks( $link ) ) {
                    continue;
                }

                if( (bool) self::linkHasNofollow( $link ) === true ) {
                    $prev_settings[] = self::encodeStripNoFollow( $link );
                    $prev_backup[] = ['content' => htmlspecialchars( $link ), 'meta' => self::encodeStripNoFollow( $link )];
                } else {
                    continue;
                }
            
            }
            self::updatePostLinksMeta( $post->ID, $prev_settings );
            self::updatePostLinksMeta( $post->ID, $prev_backup, true );

        endforeach;

    }

    /**
     * Harmonize follow
     * 
     * @param string $domain
     * @param string $type_rel
     * @param string $mode
     * @return void
     */
    public function harmonizeFollow( $domain, $type_rel, $mode ) {

        foreach( $this->_posts as $post ) :

            $post_links = self::getLinksFromPost( $post->post_content );

            $prev_settings = ! is_array( self::getPostLinksMeta( $post->ID ) ) ?
                [] : self::getPostLinksMeta( $post->ID );

            $prev_backup   = ! is_array( self::getPostLinksMeta( $post->ID, true ) ) ?
                [] : self::getPostLinksMeta( $post->ID, true );

            $filtered_links = [];
            $decoded_links  = [];

            foreach( $post_links as $link ) {

                $host = self::getHostLink( $link );
                if( $domain !== $host ) {
                    continue;
                } else {
                    if( $type_rel === 'dofollow') {
                        if( in_array( self::encodeStripNoFollow( $link ), $prev_settings )) {
                            // remove from post settings
                            $value  = self::encodeStripNoFollow( $link );
                            $key = array_search( $value, $prev_settings );
                            if( $key !== false ) {
                                unset( $prev_settings[$key] );
                            }
                            // remove from backup
                            foreach( $prev_backup as $key => $value ) {
                                if( in_array( htmlspecialchars($link), $value ) ) {
                                    unset( $prev_backup[$key] );
                                }
                            }
                        }
                        // edit content
                        if( $mode === 'content_edit') {
                            $decoded_links[] = self::removeNoFollowLink( $link );
                        }
                    } 
                    elseif( $type_rel === 'nofollow' ) {
                        // update post settings
                        if( ! in_array( self::encodeStripNoFollow( $link ), $prev_settings ) ) {
                            $prev_settings[] = self::encodeStripNoFollow( $link );
                        }
                        // update backup
                        foreach( $prev_backup as $key => $value ) {
                            if( ! in_array( htmlspecialchars($link), $value ) ) {
                                $prev_backup[]   = ['content' => htmlspecialchars( $link ), 'meta' => self::encodeStripNoFollow( $link )];
                            }
                        }
                        // edit content
                        if( $mode === 'content_edit') {
                            $decoded_links[] = self::addNoFollowLink( $link );
                        }
                    } else {
                        continue;
                    }
                    $filtered_links[] = $link;
                }

            }

            self::updatePostLinksMeta( $post->ID, $prev_settings );
            self::updatePostLinksMeta( $post->ID, $prev_backup, true );

            if( $mode === 'content_edit' ) {
                $content = str_replace( $filtered_links, $decoded_links, $post->post_content );
                self::savePost( $post->ID, $content );
            }

        endforeach;

    }

    /**
     * Save metabox data
     * 
     * @param array $data 
     * @param int $post_id
     * @param array $save_function
     * 
     * @return void
     */
    public function saveMetaBoxData( $data = [], $post_id = 0, $save_function = '' ) {

        if( self::isGlobalActionDofollow() || self::isGlobalActionNofollow() ) {
            return;
        }

        $post = get_post( $post_id );

        if( isset( $data ) && is_array( $data ) ) {

            $post_links  = self::getLinksFromPost( $post->post_content );

            if( empty( $post_links ) ) {
                return;
            }
        
            $selected_links = array_map('sanitize_text_field', $data );

            $filtered_links = [];
            $decoded_links  = [];
            $backup_links   = [];
            $manualli_nofollow = []; 

            foreach( $post_links as $link ) {

                if( true === self::excludeLinks( $link ) ) {
                    continue;
                }

                $filtered_links[] = $link;
                $encode_link = self::encodeStripNoFollow( $link );
                if( in_array( $encode_link, $selected_links ) ) {
                    $decode_link = self::addNoFollowLink( $link );
                    $decoded_links[] = $decode_link; 
                    // backup
                    $encode_link_bkp = htmlspecialchars( $decode_link );
                    $backup_links[] = ['content' => $encode_link_bkp, 'meta' => $encode_link];
                } else {
                    $decoded_links[] = self::removeNoFollowLink( $link ); // to replace
                    // backup
                    $backup_links[] = ['content' => $encode_link, 'meta' => ''];
                }
            }
            $content = str_replace( $filtered_links, $decoded_links,  $post->post_content );
            self::updatePostLinksMeta( $post_id, $selected_links );
            self::updatePostLinksMeta( $post_id, $backup_links, true );
        } else {
            $content = self::removeNoFollow($post->post_content);
            self::deletePostLinksMeta( $post_id );
            self::deletePostLinksMeta( $post_id, true );
        }

        if( 
            (bool) $this->_options['apply_mode_metabox'] === false || 
            ( (bool) $this->_options['apply_mode_metabox'] === true && $this->_options['global_actions_mode'] === 'content_edit' )
        ) {
            // unhook this function so it doesn't loop infinitely
            remove_action('save_post', $save_function );
            self::savePost( $post_id, $content );
            add_action('save_post', $save_function );
        }

    }

    /**
     * Metabox Html
     *
     * @param [type] $post
     * @return void
     */
    public function metaBoxHtml( $post ) {

        self::infoActionOnMetaBox();

        if( self::isGlobalActionDofollow() || self::isGlobalActionNofollow() ) {
            return;
        }

        $post_links = self::getLinksFromPost( $post->post_content );
        $selected   = (array) self::getPostLinksMeta( $post->ID );
        
        if( ! empty( $post_links ) ) : 
            foreach( $post_links as $key => $link ) :
                
                preg_match('/href="(.*?)"/i', $link, $match );

                if( ! isset( $match[1] ) ) {
                    continue; // invalid link
                }

                $disabled = '';
                if( self::isDoFollowDomain( $match[1] ) ) {
                    $disabled = 'disabled';
                }
                
                if( self::isInternalUrl( $match[1] ) ) {
                    continue;
                } 

                $encode_link = self::encodeStripNoFollow( $link );
                $checked = in_array( $encode_link, $selected ) ? 1 : 0;
                ?>
                <p>
                <label>
                    <input type="text" disabled style="width:100%" class="nofopl-post-link" id="nofopl-post-link-<?php echo esc_attr( $key+1 ); ?>" value="<?php echo esc_url( self::getUrlFromLink( $link ) ); ?>"><br/>
                    <?php if( ! self::isDoFollowDomain( $match[1] ) ) : ?>
                        <input type="checkbox" class="nofopl-post-link-ckb" id="nofopl-post-link-cbk-<?php echo esc_attr( $key+1 ); ?>" name="iljsnf_nofollow[]" value="<?php echo htmlspecialchars( $encode_link ); /* // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>" <?php checked(1, $checked ); ?><?php echo esc_attr( $disabled ); ?>/> <?php echo esc_html_e('Add nofollow', 'nofopl'); ?>
                    <?php else : ?>
                        <?php printf( esc_html__('%1$sThis domain is in whitelist%2$s', 'nofopl'), '<span class="nofopl-text-green">', '</span>' ); ?>
                    <?php endif; ?>
                </label>
                </p>
            <?php endforeach; ?>
            <?php wp_nonce_field( 'nofopl_meta_box_fields', 'nofopl_meta_box_nonce' );
        else :
            printf( esc_html__('No links', 'nofopl' ), '<p><em>', '</em></p>' );
        endif;

    }

    /**
     * Exclude domain action
     *
     * @return void
     */
    public function excludeDomainBkp( $domains ) {

        $domains_list = self::domainsList( $domains );

        foreach( $this->_posts as $post ) :

            $post_links  = self::getLinksFromPost( $post->post_content );

            $prev_settings = ! is_array( self::getPostLinksMeta( $post->ID ) ) ?
                [] : self::getPostLinksMeta( $post->ID );

            $prev_backup   = ! is_array( self::getPostLinksMeta( $post->ID, true ) ) ?
                [] : self::getPostLinksMeta( $post->ID, true );

            if( empty( $post_links ) ) {
                continue;
            }
    
            $removes = [];
            $metas   = [];
            foreach( $post_links as $link ) :
    
                $host_url = self::getHostLink( $link );
                
                if( in_array( $host_url, $domains_list ) ) {
                    $removes[] =  self::removeNoFollowLink( $link );
                    $metas[] = '';
                } else {
                    $removes[] =  $link;
                    if( stripos( $link, 'rel="nofollow"' ) !== false ) {
                        $metas[] = self::encodeStripNoFollow( $link );
                    } else {
                        $metas[] = '';
                    }
                }
    
            endforeach;
    
            $content = str_replace( $post_links, $removes, $post->post_content );
            self::savePost($post->ID, $content );
    
            // remove empty values
            $filter_metas = array_filter( $metas );

            // update meta
            self::updatePostLinksMeta( $post->ID, $filter_metas );
            self::updatePostLinksMeta( $post->ID, $filter_metas, true );
    
        endforeach;

    }

    public function excludeDomain( $domains ) {

        $domains_list = self::domainsList( $domains );

        foreach( $this->_posts as $post ) :

            $post_links  = self::getLinksFromPost( $post->post_content );

            $prev_settings = ! is_array( self::getPostLinksMeta( $post->ID ) ) ?
                [] : self::getPostLinksMeta( $post->ID );

            $prev_backup   = ! is_array( self::getPostLinksMeta( $post->ID, true ) ) ?
                [] : self::getPostLinksMeta( $post->ID, true );

            if( empty( $post_links ) ) {
                continue;
            }
    
            $filtered_links = [];
            $decoded_links  = [];
            foreach( $post_links as $link ) :
    
                $host_url = self::getHostLink( $link );

                if( ! in_array( $host_url, $domains_list )) {
                    continue;
                }
                
                if( in_array( self::encodeStripNoFollow( $link ), $prev_settings ) ) {
                    // remove from post settings
                    $value  = self::encodeStripNoFollow( $link );
                    $key = array_search( $value, $prev_settings );
                    if( $key !== false ) {
                        unset( $prev_settings[$key] );
                    }
                    // remove from backup
                    foreach( $prev_backup as $key => $value ) {
                        if( in_array( htmlspecialchars($link), $value ) ) {
                            unset( $prev_backup[$key] );
                        }
                    }
                }
                // edit content
                if( $mode === 'content_edit') {
                    $decoded_links[] = self::removeNoFollowLink( $link );
                }
              
            endforeach;

            // update meta
            self::updatePostLinksMeta( $post->ID, $prev_settings );
            self::updatePostLinksMeta( $post->ID, $prev_backup, true );
    
            if( $mode === 'content_edit' ) {
                $content = str_replace( $filtered_links, $decoded_links, $post->post_content );
                self::savePost( $post->ID, $content );
            } 
    
        endforeach;

    }

}