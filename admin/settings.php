<?php
/**
 * NofoplSettings Class
 * @package No Follow Post Links
 * @subpackage admin/settings.php
 * @since 1.0.0
 */

class NofoplSettings {

    private $_options;
    private $_settings = array();
    private $tabs = [];
    public $nofoplManager;

    public $disabled_nofollow;
    public $disabled_dofollow;
    public $disabled_backup;
    public $disabled_map;
    public $disabled_harmonize;
    public $data_nonce;
       

    private const PAGE_SLUG = NOFOPL . '-settings';
    public const OPTIONS_NAME = NOFOPL . '_settings';
    public const TAB_TRANSIENT = NOFOPL . '_tab';
    private const OPTIONS_GROUP = NOFOPL . '_option_group';

    /**
     * Input generator
     *
     * @param string $id // unique id
     * @param string $name // input name
     * @param mixed $value // value
     * @param string $type // input type
     * @param array $attrs_args // misc (i.e classnames, special attributes)
     * @param int $default //default value (for checkbox)
     *
     * @return void
     */
    public static function getInput( $id = '', $name = '', $value = '', $type = 'text', $attrs_args = [], $default = 1 ) {
        $attrs = '';
        if( ! empty( $attrs_args ) ) {
            foreach( $attrs_args as $k => $v ) {
                $attrs .= esc_attr( $k ) . '="' . esc_attr( $v) . '" ';
            }
        }
        printf(
            '<input type="%s" id="%s" %sname="%s" value="%s"%s/>',
            esc_attr( $type ),
            esc_html( $id ),
            $attrs,
            esc_html( $name ),
            $type === 'checkbox' || $type === 'radio' ? esc_attr( $default ) : esc_attr( $value ),
            $type === 'checkbox' || $type === 'radio' ? checked( $default, $value, false ) : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        );
    } 

    /**
     * Label generator
     * 
     * @param string $input // input name
     * @param string $for // for label attribute
     * @param bool $br // display br after label
     * @return string
     */
    public static function getLabel( $string = '', $for = '', $br = false ) {

        if( ! isset( $string ) || $string === '') {
            return '';
        }
        if( $br === true ) {
            printf( '<label for="%s">%s</label>%s', esc_attr( $for ), esc_html( $string ), '<br>' ); //
        } else {
            printf( '<label for="%s">%s</label>', esc_attr( $for ), esc_html( $string ) );
        }
    }
    
    /**
     * Sanitize array value
     *
     * @param array $value // value to sanitize
     * @return array
     */
    public static function sanitize_array_field( $value ) {
        if( ! is_array( $value ) ) {
            return sanitize_text_field( $value );
        }
        return array_map( function( $value ) {
            if( ! is_array( $value ) ) {
                return sanitize_text_field( $value );
            } 
            return self::sanitize_array_field($value);
        }, $value );
    }

    /**
     * Sanitize array hex color
     *
     * @param array $value // hex color to sanitize
     *
     * @return array
     */
    public static function sanitize_array_hex_color( $value ) {
        if( ! is_array( $value ) ) {
            return sanitize_hex_color( $value );
        }
        return array_map( function( $value ) {
            if( ! is_array( $value ) ) {
                return sanitize_hex_color( $value );
            } 
            return self::sanitize_array_hex_color($value);
        }, $value );
    }

    /**
     * Sanitize urls in array
     *
     * @param array $value // valore da sanitizzare
     *
     * @return array
     */
    public static function esc_urls_raw( $value ) {
        if( ! is_array( $value ) ) {
            return esc_url_raw( $value );
        }
        return array_map( function( $value ) {
            if( ! is_array( $value ) ) {
                return esc_url_raw( $value );
            } 
            return self::esc_url_raw($value);
        }, $value );
    }
    
    /**
     * Sanitize text with specialchars
     *
     * @param string $value
     * @return string
     */
    public static function sanitize_text_specialchars( $value ) {
        
        $value = htmlspecialchars( $value, ENT_COMPAT, 'UTF-8' );
        return sanitize_text_field( $value );
    }

    /**
     * Sanitize text and url in array
     *
     * @param [type] $value
     * @return void
     */
    public static function sanitize_array_text_url( $value ) {

        if( ! is_array( $value ) ) {

            if( filter_var( $value, FILTER_VALIDATE_URL ) !== false ) {
                return esc_url_raw( $value );
            } else {
                return sanitize_text_field( $value );
            }

        } 
        
        $value = array_map( array( 'nofoplSettings', 'sanitize_array_text_url'), $value );
        
        return $value;

    }
        
    /**
     * The constructor
     *
     * @return void
     */
    public function __construct( $nofoplManager ) {

        $this->_options = wp_parse_args( get_option( self::OPTIONS_NAME ), self::defaults() );

        $this->disabled_nofollow = nofopl_is_global_action_nofollow() ? ' disabled' : '';
        $this->disabled_dofollow = nofopl_is_global_action_dofollow() ? ' disabled' : '';
        $this->disabled_backup = nofopl_is_global_action_follow() ? '' : ' disabled';
        $this->disabled_map = nofopl_is_global_action_follow() ? ' disabled' : '';
        $this->disabled_harmonize = nofopl_is_global_action_follow() ? ' disabled' : '';
        $this->data_nonce = wp_create_nonce( 'nofopl-action-nonce' );
        $this->nofoplManager = $nofoplManager;

        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'settings_scripts' ) );
        add_action( 'admin_footer', array( $this, 'js_inline'));
        add_action( 'wp_ajax_saveTab', array( $this, 'save_tab'));
    }

    /**
     * Add scripts and styles for admin
     *
     * @return void
     */
    public function settings_scripts() {

        wp_enqueue_style('nofopl-settings', NOFOPL_URL . 'admin/css/nofopl-style-admin.css' );
        wp_enqueue_script('nofopl-settings', NOFOPL_URL . 'admin/js/nofopl-scripts-admin.js');

        wp_localize_script( 
            'nofopl-settings', 
            'nofopl_args', 
            [
                'message_success' => esc_html__('Success', 'nofopl'),
                'message_failed' => esc_html__('Failed. Check for errors', 'nofopl'),
                'message_e_harmonize' => esc_html__('Failed. This domain is in whitelist.', 'nofopl')
            ] 
        );

    }
    
    /**
     * Hook to view the javascript code that manages the tabs
     *
     * @return void
     */
    public function js_inline() {
      
      	$screen = get_current_screen();
      
      	if( $screen->id !== 'settings_page_nofopl-settings' ) {
          return;
        }

        $tab_saved = get_transient('nofopl_tab');
      
      	if( !isset( $tab_saved ) || $tab_saved === false ) {
            $tab_saved = 'tab1';
        }

        ?>
        <script>
        let tabSaved = '<?php echo esc_html( $tab_saved ); ?>';
        jQuery( function($) {
            $('.form-table').each(function(k) {
                var title = $(this).prev('h2');
                var subtitle = $(this).prev('.subtitle');
                var tooltip = $(this).prev('.nofopl-tip-label');
                if( subtitle.length > 0 ) {
                    title = subtitle.prev('h2');
                }
                $(this).wrap('<div class="tab tab'+(k+1)+'"></div>');
                if( k+1 !== 1 ) {
                    $('.tab'+(k+1)).hide();
                }
                $(this).parent('.tab'+(k+1)).prepend(tooltip);
                $(this).parent('.tab'+(k+1)).prepend(subtitle);
                $(this).parent('.tab'+(k+1)).prepend(title);
            });
            
            $('.tab').hide().removeClass('nav-tab-active');
            $('.' + tabSaved).show().addClass('nav-tab-active');
            $('.nav-tab').on('click', function() {
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                let tab = $(this).attr('data-tab');
                $('.tab').hide();
                $('.' + tab).show();
                $('#tab').val(tab);

                if( tab === 'tab1') {
                    $('.nofopl-plugins-footer').addClass('hide-button-save');
                } else {
                    $('.nofopl-plugins-footer').removeClass('hide-button-save');
                }

                $.post(ajaxurl, {

				action:	'saveTab',
				tab: tab,
                tabNonce: $(this).attr('data-nonce')

				}, function(data, success) {
				});
            });

        });
        </script><?php

    }

    /**
     * Save tab
     *
     * @return void
     */
    public function save_tab() {

        // nonce check
        if ( ! wp_verify_nonce( sanitize_key( $_POST['tabNonce'] ), 'nofopl-tab-nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
            wp_die('-1'); 
        }

        if( 
            isset( $_POST['action']) && sanitize_text_field( wp_unslash( $_POST['action'] ) ) === 'saveTab' &&
            isset( $_POST['tab']) && sanitize_text_field( wp_unslash( $_POST['tab'] ) ) !== '' ) :

            $tab = sanitize_text_field( wp_unslash( $_POST['tab'] ) );
            set_transient(self::TAB_TRANSIENT, $tab );

            wp_die('1');

        endif;

        wp_die('-2');

    }
        
    /**
     * Add page to menu
     *
     * @return void
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            __('No Follow Post Links', 'nofopl'), // PAGE NAME
            __('No Follow Post Links', 'nofopl'), // MENU NAME
            'manage_options', // capability 
            self::PAGE_SLUG, 
            array( $this, 'form' )
        );
    }


    /**
     * Display form
     *
     * @return void
     */
    public function form() {

        // Set class property
        $tabs = $this->tabs;
        $transient = get_transient('nofopl_tab');
        $tab_saved = isset( $transient ) ? get_transient('nofopl_tab') : 'tab1';
        $hide_button_save = $tab_saved !== 'tab1' ? '' : ' hide-button-save';
       ?>
        <div class="wrap nofopl-wrap">
            <h1><?php printf( esc_html__( 'No Follow Post Links', 'nofopl' ) ); ?></h1>
            <nav class="nav-tab-wrapper">
                <?php foreach( $tabs as $k => $l ) : 
                    $current = $k === $tab_saved ? ' nav-tab-active' : '';
                ?>
                    <span class="nav-tab<?php echo esc_attr( $current ); ?>" data-tab="<?php echo esc_attr( $k ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'nofopl-tab-nonce' ) ); ?>"><?php echo wp_kses( $l, ['span' => ['class' => []]] ); ?></span>
                <?php endforeach; ?>
            </nav>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( self::OPTIONS_GROUP );
                do_settings_sections( self::PAGE_SLUG );
            ?>
            <div class="nofopl-plugins-footer<?php echo esc_attr( $hide_button_save ); ?>">
                <hr />
                <label style="color:red; font-weight: bold;">
                    <input type="checkbox" name="reset" value="1" /> <?php esc_html_e('Reset Data', 'nofopl' ); ?>
                </label>
                <p>
                    <?php submit_button('', 'primary', 'submit', false ); ?>
                    <input type="hidden" id="tab" name="tab" value="tab1" />
                </p>
            </div>
        </div>
        <hr/>
        <div class="info">
            <p><?php printf( esc_html__( 'Author: %1$s. Plugin version %2$s.', 'nofopl' ), 
                '<strong>' . NOFOPL_AUTHOR . '</strong>', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                '<strong>' . NOFOPL_VERSION . '</strong>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
             ?> 
            </p>  
        </div>
        <?php
    }
    
    /**
     * Set default values
     *
     * @param string $key
     * @return mixed
     */
    public static function defaults( $key = '' ) {

        $defaults = array(

            // tab 2
            'global_actions_mode' => 'javascript',
            'whitelist_domains' => '',
            'post_types' => get_post_types(['public' => true]),
            'apply_mode_metabox' => 1,
            'hide_metabox' => 0
            
        );
        $key = trim($key);
        if( strlen( $key ) > 0 ) {
            return isset( $defaults[$key] ) ? $defaults[$key] : false;
        }
        return $defaults;
    }
    
    /**
     * Create the fields and insert them into the settings page
     *
     * @return void
     */
    public function page_init() { 

        register_setting(
            self::OPTIONS_GROUP, // Option group
            self::OPTIONS_NAME, // Option name
            array(
                'sanitize_callback' => array( $this, 'sanitize' ), // Sanitize,
                'default' => self::defaults()
            )
        );

        /**
         * Add sections and options
         */
        $settings = array(

            // tab 1
            'actions' => array(
                'section_label' => '',
                'section_tab' => sprintf( __('%s Actions', 'nofopl'), '<span class="dashicons dashicons-update"></span>' ),
                'section_callback' => array( $this, 'actions'),
                'fields' => array(
                    'global_attribute_assignment' => array(
                        'label_field' => __('Attribute assignment', 'nofopl' ),
                        'label_input' => '',
                        'default_value' => '',
                        'sanitize_callback' => 'sanitize_text_field'
                    ),
                    'map_content_nofollow' => array(
                        'label_field' => __('Map content nofollow', 'nofopl' ),
                        'label_input' => '',
                        'default_value' => '',
                        'sanitize_callback' => 'sanitize_text_field'
                    ),
                    'harmonize_follow' => array(
                        'label_field' => __('Harmonize follow', 'nofopl' ),
                        'label_input' => '',
                        'default_value' => '',
                        'sanitize_callback' => 'sanitize_text_field'
                    ),
                )
            ),

            // tab 2
            'settings' => array(
                'section_label' => '',
                'section_tab' => sprintf( __('%s Settings', 'nofopl'), '<span class="dashicons dashicons-admin-settings"></span>' ),
                'section_callback' => array( $this, 'settings'),
                'fields' => array(
                    'global_actions_mode' => array(
                        'label_field' => __('Global actions mode', 'nofopl' ),
                        'label_input' => '',
                        'default_value' => self::defaults('global_actions_mode'),
                        'sanitize_callback' => 'sanitize_text_field'
                    ),
                    'apply_mode_metabox' => array(
                        'label_field' => __('Same for the metabox action', 'nofopl' ),
                        'label_input' => '',
                        'default_value' => self::defaults('post_types'),
                        'sanitize_callback' => 'absint'
                    ),
                    'whitelist_domains' => array(
                        'label_field' => __('Whitelist domains', 'nofopl' ),
                        'label_input' => '',
                        'default_value' => self::defaults('whitelist_domains'),
                        'sanitize_callback' => 'sanitize_textarea_field'
                    ),
                    'post_types' => array(
                        'label_field' => __('Post types for global actions', 'nofopl' ),
                        'label_input' => '',
                        'default_value' => self::defaults('post_types'),
                        'sanitize_callback' => ['NofoplSettings', 'sanitize_array_field']
                    ),
                    'hide_metabox' => array(
                        'label_field' => __('Hide Post Links Metabox', 'nofopl' ),
                        'label_input' => '',
                        'default_value' => self::defaults('post_types'),
                        'sanitize_callback' => 'absint'
                    )
                )
            )
        );

        if( !empty( $settings ) ) :

            $i = 1;
            foreach( $settings as $setting => $args ) {

                add_settings_section(
                    $setting, // ID
                    $args['section_label'], // Title
                    $args['section_callback'], // Callback
                    self::PAGE_SLUG
                );  

                foreach( $args['fields'] as $field_id => $data ) {

                    add_settings_field(
                        $field_id, // ID
                        $data['label_field'], // Title 
                        array( $this, $field_id ), // Callback
                        self::PAGE_SLUG, // Page
                        $setting // Section           
                    );

                    $this->_settings[$field_id] = array(
                        'label_input'       => $data['label_input'],
                        'default_value'     => $data['default_value'],
                        'sanitize_callback' => $data['sanitize_callback']
                    );

                }

                $this->tabs['tab' . ($i++)] = $args['section_tab'];

            }

        endif;
  
    }

    /**
     * Sanitize data
     *
     * @param array $input
     * @return array
     */
    public function sanitize( $input ) {
        
        $settings = $this->_settings;
        $defaults = self::defaults();
        $new_input = [];

        // remove actions
        unset( $settings['global_attribute_assignment']);
        unset( $settings['map_content_nofollow']); 

        // sanitize
        foreach( $settings as $setting => $values ) {
            if( isset( $input[$setting] ) ) {
                $new_input[$setting] = $values['sanitize_callback']( $input[$setting] );
            }
            else {
                $new_input[$setting] = $defaults[$setting];
            }

        }

        // save tab
        if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), self::OPTIONS_GROUP . '-options' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
            $tab_action = isset( $_POST['tab'] ) ? sanitize_text_field( wp_unslash( $_POST['tab'] ) ) : 'tab1';
            set_transient(self::TAB_TRANSIENT, $tab_action);
            if( isset( $_POST['reset'] ) && absint( wp_unslash( $_POST['reset'] ) ) === 1 ) {
                $new_input = self::defaults();
            }
        }

        // action on save
        $nofoplManager = $this->nofoplManager;
        $nofoplManager->excludeDomain( $new_input['whitelist_domains'] );

        // on change mode delete action
        if( $this->_options['global_actions_mode'] !== $new_input['global_actions_mode'] ) {
            delete_option(NofoplManageNoFollow::GLOBAL_DOFOLLOW);
            delete_option(NofoplManageNoFollow::GLOBAL_NOFOLLOW);
        }

        return $new_input;
    }

    /**
     * Actions
     * 
     * @return void
     */
    public function actions(){
        $mode = ucfirst( str_replace('_', ' ', $this->_options['global_actions_mode'] ) );
        printf( esc_html__('%1$s%2$s mode enabled. Go to "settings tab" to change mode action.%3$s', 'nofopl'), '<p class="nofopl-tip-label" style="font-size:1em !important;">', sprintf( '"<strong>%s</strong>"', esc_html($mode) ), '</p>');       
    }

    /**
     * Settings
     * 
     * @return void
     */
    public function settings(){
        // nothing 
    }

    /***************** TAB 1 ******************/

    /**
     * Global attribute assignment
     * 
     * @return void
     */
    public function global_attribute_assignment() {

        echo '<p>';   
        printf( '<button style="width:200px;" type="button" class="button-red button-primary%1$s" id="nofopl-ajax-nofollow" value="1" data-nonce="%2$s"%1$s>%3$s</button>',
            esc_attr( $this->disabled_nofollow ),
            esc_attr( $this->data_nonce ),
            esc_html__('Assign Global Nofollow', 'nofopl' )
        );
        echo '</p>';

        echo '<br>';

        echo '<p>';   
        printf( '<button style="width:200px;" type="button" class="button-green button-primary%1$s" id="nofopl-ajax-dofollow" value="1" data-nonce="%2$s"%1$s>%3$s</button>',
            esc_attr( $this->disabled_dofollow ),
            esc_attr( $this->data_nonce ),
            esc_html__('Assign Global Dofollow', 'nofopl' )
        );
        echo '</p>';

        echo '<br>';

        echo '<p>';    
        printf( '<button style="width:200px;" type="button" class="button-primary%1$s" id="nofopl-ajax-restore" value="1" data-nonce="%2$s"%1$s>%3$s</button>',
            esc_attr( $this->disabled_backup ),
            esc_attr( $this->data_nonce ),
            esc_html__('Restore Meta Settings', 'nofopl' )
        );
        echo '</p>'; 

        echo '<br>';

        printf( esc_html__('%1$sThese actions assign or remove nofollow from links. Use restore meta settings to restore settings before action.%2$s', 'nofopl' ), '<p class="nofopl-description">', '</p>' );


    }

    /**
     * Map content follow
     * 
     * @return void
     */
    public function map_content_nofollow() {

        echo '<p>';    
        printf( '<button style="width:200px;" type="button" class="button-primary%1$s" id="nofopl-ajax-map" value="1" data-nonce="%2$s"%1$s>%3$s</button>',
            esc_attr( $this->disabled_map ),
            esc_attr( $this->data_nonce ),
            esc_html__('Map Content Nofollow', 'nofopl' )
        );
        echo '</p>'; 

        echo '<br>';

        printf( esc_html__('%1$sThis action maps nofollows attributes present in the content and update the meta settings.%2$s', 'nofopl' ), '<p class="nofopl-description">', '</p>' );

    }

    /**
     * Harmonize follow
     * 
     * @return void
     */
    public function harmonize_follow() {

        echo '<p>'; 

        self::getLabel(
            esc_html__('Put domain to harmonize', 'nofopl' ),
            'harmonize-follow-domain' 
        );

        echo '<br>';

        self::getInput(
            'harmonize-follow-domain', 
            'harmonize_follow_domain', 
            '',
            'text'
        );

        echo '<br>';

        $types = [
            'nofollow' => esc_html__('No Follow', 'nofopl'), 
            'dofollow' => esc_html__('Do Follow', 'nofopl')
        ];
        foreach( $types as $type => $label ) :
            self::getInput(
                'harmonize-' . $type, 
                'harmonize_follow_type', 
                'nofollow',
                'radio',
                ['class' => 'harmonize-follow-type'],
                $type
            );
            self::getLabel(
            $label,
            'harmonize-' . $type 
            );
            echo '&nbsp;&nbsp;';
        endforeach;

        echo '</p><br><p>';

        printf( '<button style="width:200px;" type="button" class="button-primary%1$s" id="nofopl-ajax-harmonize" value="1" data-nonce="%2$s"%1$s>%3$s</button>',
            esc_attr( $this->disabled_harmonize ),
            esc_attr( $this->data_nonce ),
            esc_html__('Harmonize', 'nofopl' )
        );
        echo '</p>'; 

        echo '<br>';

        printf( esc_html__('%1$sSearch domain (i.e. mydomain.com) and harmonizes every link for this domain.%2$s', 'nofopl' ), '<p class="nofopl-description">', '</p>' );

    }

    /***************** TAB 2 ******************/

    /**
     * Global action mode
     * 
     * @return void
     */
    public function global_actions_mode() {

        $option =  $this->_options['global_actions_mode'];
        $name = self::OPTIONS_NAME . '[global_actions_mode]';
        $options = [
            'javascript'     => __('Javascript', 'nofopl' ), 
            'content_filter' => __('Content Filter', 'nofopl' ), 
            'content_edit'   => __('Content Edit', 'nofopl' )
        ];
        $opt_sel = '';
        foreach( $options as $opt => $label ) {
            $opt_sel .= '<option value="' . esc_attr( $opt ) . '" ' . selected( $opt, $option, false ) . '>' . esc_html( $label ) . '</option>'; 
        }
        printf( '<select id="global_actions_mode" name="%s">%s</select>',
            esc_attr( $name ),
            $opt_sel // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        );

        printf( '<p class="nofopl-tip-label">%s%s%s</p>',
            sprintf( esc_html__( '"%1$sJavascript%2$s": will be used Javascript to change follow attribute (client-side mode).%3$s', 'nofopl'), '<strong>', '</strong>', '<br>' ),
            sprintf( esc_html__( '"%1$sContent Filter%2$s": "the_content" filter will be used on the content output (server-side mode).%3$s', 'nofopl' ), '<strong>', '</strong>', '<br>'),
            sprintf( esc_html__( '"%1$sContent edit%2$s": the post content will be modified (server-side mode). %3$sWarning! Make a backup of database before using this mode%4$s.', 'nofopl' ), '<strong>', '</strong>', '<strong class="nofopl-text-red">', '</strong>' )
        );

    }

    /**
     * Apply mode at metabox action
     *
     * @return void
     */
    public function apply_mode_metabox() {

        echo '<p>';
        self::getInput(
            'hide_metabox', 
            self::OPTIONS_NAME . '[apply_mode_metabox]', 
            esc_attr( $this->_options['apply_mode_metabox'] ),
            'checkbox'
        );
        self::getLabel(
            $this->_settings['apply_mode_metabox']['label_input'], 
            'hide_metabox'
        );
        echo '</p>';
        printf( esc_html__('%1$sIf set, the mode for the global action above will also be applied to the metabox action. If unset, content_edit mode will be used.%2$s', 'nofopl' ), '<p class="nofopl-description">', '</p>' );

    }

    /**
     * White list domain
     *
     * @return void
     */
    public function whitelist_domains() {
        echo '<p>';
        self::getLabel(
            $this->_settings['whitelist_domains']['label_input'], 
            'whitelist_domains',
            true
        );
        printf('<textarea id="%s" name="%s" cols="40" rows="5">%s</textarea>',
            'whitelist_domains',
            esc_attr( self::OPTIONS_NAME . '[whitelist_domains]' ),
            esc_attr( $this->_options['whitelist_domains'] )
        );
        echo '</p>';
        printf( esc_html__('%1$sExclude domains in the list from nofollow assignment. Put one domain per line (i.e mydomain.com).%2$sSuggestion. If you have removed a domain from the whitelist, you can re-nofollow it using the harmonize action.%3$s', 'nofopl' ), '<p class="nofopl-description">', '<br>', '</p>' );
        
  
    }

    /**
     * Choose post types
     *
     * @return void
     */
    public function post_types() {
        $option =  $this->_options['post_types'];
        $name = self::OPTIONS_NAME . '[post_types][]';
        $options = get_post_types(['public' => true]);
        $opt_sel = '';
        foreach( $options as $opt => $label ) {
            $selected = in_array( $opt, $option ) ? 'selected' : '';
            $opt_sel .= '<option value="' . esc_attr( $opt ) . '" ' . esc_html( $selected ) . '>' . esc_html( $label ) . '</option>'; 
        }
        printf( '<select id="button_close_pos" name="%s" multiple>%s</select>',
            esc_attr( $name ),
            $opt_sel // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        );
        printf( esc_html__('%1$sHold down the left shift key to select multiple items.%2$s', 'nofopl' ), '<p class="nofopl-description">', '</p>' );
  
    }

    /**
     * Hide metabox
     *
     * @return void
     */
    public function hide_metabox() {

        echo '<p>';
        self::getInput(
            'hide_metabox', 
            self::OPTIONS_NAME . '[hide_metabox]', 
            esc_attr( $this->_options['hide_metabox'] ),
            'checkbox'
        );
        self::getLabel(
            $this->_settings['hide_metabox']['label_input'], 
            'hide_metabox'
        );
        echo '</p>';
        printf( esc_html__('%1$sIf set, the metabox will be hidden for post types not selected above.%2$s', 'nofopl' ), '<p class="nofopl-description">', '</p>' );

    }

}