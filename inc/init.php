<?php
/**
 * Init class
 * @package Nofollow Post Links
 * @subpackage inc/init.php
 * @since 1.0.0
 */

class NofoplInit {

    private $_file_base;

    public static function getInstance(){
         static $plugin;
         if ( ! isset( $plugin ) ){
             $plugin = new NofoplInit;
         }
         return $plugin;
    }

    /*
     * If you go with a singleton, make it private, public otherwise
     */
    private function __construct() {
        $this->_file_base = NOFOPL_PLUGIN_FILE;
        $this->_onActivation();
        $this->_onDeactivation();
        $this->_loadTextDomain();
        $this->_runPlugin();
    }

    /**
     * On activation plugin
     * @return void
     */
    private function _onActivation() {
        register_activation_hook( $this->_file_base, array( $this, 'pluginActivation' ) );
    }

    /**
     * On deactivation plugin
     * @return void
     */
    private function _onDeactivation() {
        register_deactivation_hook( $this->_file_base, array( $this, 'pluginDeactivation' ) );
    }

    /**
     * Load text domain
     * @return void
     */
    private function _loadTextDomain() {
        add_action( 'plugins_loaded', array( $this, 'loadTextDomain' ) );
    }

    /**
     * Run plugin
     * @return void
     */
    private function _runPlugin() {
        add_action('init', array( $this, 'init') );
    }

    /**
     * Setup on activation plugin
     * 
     * @return void
     */
    public function pluginActivation() {

        if ( ! current_user_can( 'activate_plugins' ) ) 
            return;

        $plugin = isset( $_REQUEST['plugin'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) ) : ''; //phpcs:disable WordPress.Security.NonceVerification.Recommended
        check_admin_referer( "activate-plugin_{$plugin}" );

        // Setup tab.
        set_transient( NofoplSettings::TAB_TRANSIENT, 'tab1');

        // Get options.
        $defaults = NofoplSettings::defaults();
        $options  = get_option( NofoplSettings::OPTIONS_NAME);

        // If options exists do not remove old options.
        // We verify that $options is an array.
        // If it does not exist, CP returns false.
        if( is_array( $options ) ) {
            return;
        }

        // Add option.
        add_option( NofoplSettings::OPTIONS_NAME, $defaults );

    }

    /**
     * Restore post settings on deactivation.
     * But only is used content edit
     *
     * @return void
     */
    public function pluginDeactivation() {

        if( nofopl_settings('global_actions_mode') !== 'content_edit') {
            return;
        }

        global $nofoplManager;
        $nofoplManager->restorePostSettings();

    }


    /** 
     * Setup languages directory
     * 
     * @return void
     */
    public function loadTextDomain() {
        load_plugin_textdomain( 'nofopl', false, NOFOPL_DIR . 'languages/' ); 
    }

    /**
     * Initialize plugin
     * @return void
     */
    public function init() {

        new NofoplGlobalActions;
        new NofoplMetaActions;

        if( ! is_admin() ) {
            return;
        }
    
        $nofoplManager = new NofoplManageNoFollow;
        $GLOBALS['nofoplManager'] = $nofoplManager;
    
        new NofoplSettings( $nofoplManager );
        new NofoplMetabox( $nofoplManager );
        new NofoplAjaxActions( $nofoplManager );
    }
    
}