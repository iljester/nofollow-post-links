<?php
/**
 * NofoplMetabox Class
 * @package Nofollow Post Links
 * @subpackage admin/metabox.php
 * @since 1.0.0
 */

class NofoplMetaBox{

    public $nofoplManager;
    protected $_options;

    /**
     * The constructor
     */
    public function __construct( $nofopl_manager = null ) {

        $this->nofoplManager = $nofopl_manager;

        $this->_options = get_option( NofoplSettings::OPTIONS_NAME );
        $hide_metabox   = $this->_options['hide_metabox'];
        $post_types     = $this->_options['post_types'];
        $post_type      = get_post_type();

        if( ! in_array( $post_type, $post_types )  ) {
            if( (bool) $hide_metabox === false ) {
                add_action( 'add_meta_boxes', array( $this, 'addMetaBox' ) );
                add_action( 'save_post', array( $this, 'save') );
            }
        } else {
            add_action( 'add_meta_boxes', array( $this, 'addMetaBox' ) );
            add_action( 'save_post', array( $this, 'save') );
        }

    }

    /**
     * Add metaboxes
     *
     * @return void
    */
    public function addMetaBox(){
        add_meta_box(
        'nofopl_description',
        __( 'External Post Links', 'nofopl' ),
        [$this, 'callback'],
        get_post_types(['public' => true]),
        'advanced',
        'default'
        );
    }

    /**
     * Callback metabox
     *
     * @param object $post
     * @return void
     */
    public function callback( $post ){ 
        $nofoplManager = $this->nofoplManager;
        ?>
        <div class="nofopl-inside">
            <?php $nofoplManager->metaBoxHtml( $post ); ?>
        </div>
        <?php
    }

    /**
     * Save metabox
     *
     * @param object $post_id
     * @return void
     */
    public function save( $post_id ) {

        if ( ! isset( $_POST['nofopl_meta_box_nonce'] ) )
            return;
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nofopl_meta_box_nonce'] ) ), 'nofopl_meta_box_fields' ) )
            return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;
        if ( ! current_user_can( 'edit_post', $post_id ) )
            return;

        $nofoplManager = $this->nofoplManager;

        $data = isset( $_POST['iljsnf_nofollow'] ) ? NofoplSettings::sanitize_array_field( wp_unslash( $_POST['iljsnf_nofollow'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $nofoplManager->saveMetaBoxData( $data, $post_id, array( $this, 'save') );

    }

}