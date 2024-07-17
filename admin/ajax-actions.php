<?php
/**
 * NofoplAjaxActions class
 * @package No Follow Post Links
 * @subpackage admin/ajax-actions.php
 * @since 1.0.0
 */

class NofoplAjaxActions {

    public $nofoplManager;

    public function __construct( $nofoplManager ) {

        $this->nofoplManager = $nofoplManager;

        add_action('wp_ajax_massiveNoFollow', array( $this, 'ajaxGlobalNofollow' ) );
        add_action('wp_ajax_massiveDoFollow', array( $this, 'ajaxGlobalDofollow') );
        add_action('wp_ajax_restorePostSettings', array( $this, 'ajaxRestorePostSettings') );
        add_action('wp_ajax_mapNofollow', array( $this, 'ajaxMapNofollow' ) );
        add_action('wp_ajax_harmonizeFollow', array( $this, 'ajaxHarmonizeFollow' ) );
    }

    /**
     * Global No follow ajax action
     *
     * @return void
     */
    public function ajaxGlobalNofollow() {
        
        // nonce check
        if ( ! wp_verify_nonce( sanitize_key( $_POST['nofoplNonce'] ), 'nofopl-action-nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
            wp_die('-1'); 
        }

        // process action
        if( 
            isset( $_POST['action']) && sanitize_text_field( $_POST['action'] === 'massiveNoFollow' ) &&
            isset( $_POST['nofoplMassive']) && absint( $_POST['nofoplMassive'] ) === 1 ) :

            if( nofopl_is_global_action_nofollow() ) {
                wp_die('-2');
            }

            $nofoplManager = $this->nofoplManager;
            $nofoplManager->globalNoFollow();

            wp_die('1');

        endif; // endif action

        wp_die('-3');
    }

    /**
     * Global do follow ajax action
     *
     * @return void
     */
    function ajaxGlobalDofollow() {

        // nonce check
        if ( ! wp_verify_nonce( sanitize_key( $_POST['nofoplNonce'] ), 'nofopl-action-nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
            wp_die('-1'); 
        }

        // process action
        if( 
            isset( $_POST['action']) && sanitize_text_field( $_POST['action'] === 'massiveDoFollow' ) &&
            isset( $_POST['nofoplMassiveDo']) && absint( $_POST['nofoplMassiveDo'] ) === 1 ) :

            if( nofopl_is_global_action_dofollow() ) {
                wp_die('-2');
            }
            
            $nofoplManager = $this->nofoplManager;
            $nofoplManager->globalDoFollow();

            wp_die('1');

        endif; // endif action

        wp_die('-3');

    }

    /**
     * Restore post settings ajax action
     *
     * @return void
     */
    public function ajaxRestorePostSettings() {

        // nonce check
        if ( ! wp_verify_nonce( sanitize_key( $_POST['nofoplNonce'] ), 'nofopl-action-nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
            wp_die('-1'); 
        }

        if( 
            isset( $_POST['action']) && sanitize_text_field( $_POST['action'] === 'restorePostSettings' ) &&
            isset( $_POST['nofoplRestore']) && absint( $_POST['nofoplRestore'] ) === 1 ) :

            if( ! nofopl_is_global_action_follow() ) {
                wp_die('-2');
            }

            $nofoplManager = $this->nofoplManager;
            $nofoplManager->restorePostSettings();

            wp_die('1');

        endif;

        wp_die('-3');

    }

    /**
     * Map nofollow ajax action
     * 
     * @return void
     */
    public function ajaxMapNofollow() {

        // nonce check
        if ( ! wp_verify_nonce( sanitize_key( $_POST['nofoplNonce'] ), 'nofopl-action-nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
            wp_die('-1'); 
        }

        if( 
            isset( $_POST['action']) && sanitize_text_field( $_POST['action'] === 'mapNofollow' ) &&
            isset( $_POST['nofoplMap']) && absint( $_POST['nofoplMap'] ) === 1 ) :

            if( nofopl_is_global_action_follow() ) {
                wp_die('-2');
            }

            $nofoplManager = $this->nofoplManager;
            $nofoplManager->mapNoFollow();

            wp_die('1');

        endif;

        wp_die('-3');

    }

    /**
     * Harmonize ajax action
     * 
     * @return void
     */
    public function ajaxHarmonizeFollow() {

        // nonce check
        if ( ! wp_verify_nonce( sanitize_key( $_POST['nofoplNonce'] ), 'nofopl-action-nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
            wp_die('-1'); 
        }

        if( 
            isset( $_POST['action']) && sanitize_text_field( $_POST['action'] === 'harmonizeFollow' ) &&
            isset( $_POST['nofoplHarmonize']) && absint( $_POST['nofoplHarmonize'] ) === 1 ) :

            if( nofopl_is_global_action_follow() ) {
                wp_die('-2');
            }

            $domain = isset( $_POST['nofoplHarmonizeDomain'] ) ? sanitize_text_field( wp_unslash( $_POST['nofoplHarmonizeDomain'] ) ) : false;
            $type_rel = isset( $_POST['nofoplHarmonizeType'] ) ? sanitize_text_field( wp_unslash( $_POST['nofoplHarmonizeType'] ) ) : false;

            if( $domain === false || $type_rel === false ) {
                wp_die('-2');
            }

            $whitelist = nofopl_domains_list();
            if( in_array( $domain, $whitelist ) ) {
                wp_die('-4');
            }

            $mode = sanitize_text_field( nofopl_settings('global_actions_mode') );

            $nofoplManager = $this->nofoplManager;
            $nofoplManager->harmonizeFollow( $domain, $type_rel, $mode );

            wp_die('1');

        endif;

        wp_die('-3');

    }


}