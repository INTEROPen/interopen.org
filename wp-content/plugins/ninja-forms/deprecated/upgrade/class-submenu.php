<?php if ( ! defined( 'ABSPATH' ) ) exit;

class NF_THREE_Submenu
{
    /**
     * (required) The slug name for the parent menu (or the file name of a standard WordPress admin page)
     *
     * @var string
     */
    public $parent_slug = '';

    /**
     * (required) The text to be displayed in the title tags of the page when the menu is selected
     *
     * @var string
     */
    public $page_title = 'Ninja Forms THREE';

    /**
     * (required) The on-screen name text for the menu
     *
     * @var string
     */
    public $menu_title = 'Ninja Forms THREE';

    /**
     * (required) The capability required for this menu to be displayed to the user.
     *
     * @var string
     */
    public $capability = 'manage_options';

    /**
     * (required) The slug name to refer to this menu by (should be unique for this menu).
     *
     * @var string
     */
    public $menu_slug = 'ninja-forms-three';

    /**
     * (optional) The function that displays the page content for the menu page.
     *
     * @var string
     */
    public $function = 'display';

    public $priority = 9001;

    /**
     * Constructor
     *
     * Translate text and add the 'admin_menu' action.
     */
    public function __construct()
    {
        $this->menu_title = __( 'Update', 'ninja-forms' );
        $this->page_title = __( 'Update to Ninja Forms THREE', 'ninja-forms' );

        $this->capability = apply_filters( 'submenu_' . $this->menu_slug . '_capability', $this->capability );

        add_action( 'admin_menu', array( $this, 'register' ), $this->priority );

        add_action( 'wp_ajax_ninja_forms_upgrade_check', array( $this, 'upgrade_check' ) );
        add_action( 'wp_ajax_ninja_forms_optin', array( $this, 'optin' ) );
        add_action( 'wp_ajax_ninja_forms_optout', array( $this, 'optout' ) );

        add_filter( 'nf_general_settings_advanced', array( $this, 'settings_upgrade_button' ) );
    }

    /**
     * Register the menu page.
     */
    public function register()
    {
        if( ! ninja_forms_three_calc_check() ) return;
        if( ! ninja_forms_three_addons_version_check() ) return;

        if( ! ninja_forms_three_addons_check() ){
            // Hide the submenu
            $this->parent_slug = '';
        }

        $function = ( $this->function ) ? array( $this, $this->function ) : NULL;

        add_submenu_page(
            $this->parent_slug,
            $this->page_title,
            $this->menu_title,
            $this->capability,
            $this->menu_slug,
            $function
        );
    }

    /**
     * Display the menu page.
     */
    public function display()
    {
        global $ninja_forms_tabs_metaboxes;

        $addon_installed = false;
        if ( isset ( $ninja_forms_tabs_metaboxes['ninja-forms-settings']['license_settings']['license_settings']['settings'] ) ) {
            if ( 0 < count( $ninja_forms_tabs_metaboxes['ninja-forms-settings']['license_settings']['license_settings']['settings'] ) ) {
                $addon_installed = true;
            }            
        }
        
        $is_opted_in = get_option( 'ninja_forms_allow_tracking', false );
        $is_opted_out = get_option( 'ninja_forms_do_not_allow_tracking', false );
        if ( ! $addon_installed && ( ! $is_opted_in || $is_opted_out ) ) {
            $opted_in = 0;
        } else {
            $opted_in = 1;
        }

        $all_forms = Ninja_Forms()->forms()->get_all();

        wp_enqueue_style( 'ninja-forms-three-upgrade-styles', plugin_dir_url(__FILE__) . 'upgrade.css' );
        wp_enqueue_style( 'ninja-forms-three-upgrade-jbox', plugin_dir_url(__FILE__) . 'jBox.css' );

        wp_enqueue_script( 'ninja-forms-three-upgrade', plugin_dir_url(__FILE__) . 'upgrade.js', array( 'jquery', 'wp-util' ), '', TRUE );
        wp_enqueue_script( 'ninja-forms-three-upgrade-jbox', plugin_dir_url(__FILE__) . 'jBox.min.js', array( 'jquery', 'wp-util' ), '', TRUE );
        wp_localize_script( 'ninja-forms-three-upgrade', 'nfThreeUpgrade', array(
            'forms' => $all_forms,
            'redirectURL' => admin_url( 'admin.php?page=ninja-forms&nf-switcher=upgrade' ),
            'optedIn' => $opted_in,
        ) );

        include plugin_dir_path( __FILE__ ) . 'tmpl-submenu.html.php';
    }

    public function upgrade_check()
    {
        if( ! isset( $_POST[ 'formID' ] ) ) $this->respond( array( 'error' => 'Form ID not found.' ) );

        $form_id = absint( $_POST[ 'formID' ] );

        $can_upgrade = TRUE;

        $fields = Ninja_Forms()->form( $form_id )->fields;
        $settings = Ninja_Forms()->form( $form_id )->get_all_settings();

        foreach( $fields as $field ){
            if( '_calc' == $field[ 'type' ] ){
                // $can_upgrade = FALSE;
            }
        }

        $this->respond( array(
            'id' => $form_id,
            'title' => $settings[ 'form_title' ],
            'canUpgrade' => $can_upgrade
        ) );
    }

    private function respond( $response =  array() )
    {
        echo wp_json_encode( $response );
        wp_die(); // this is required to terminate immediately and return a proper response
    }

    public function settings_upgrade_button( $settings )
    {
        $settings['update_to_three'] = array(
            'name' => 'update_to_three',
            'type' => '',
            'label' => __('Ninja Forms THREE', 'ninja-forms'),
            'display_function' => array($this, 'settings_upgrade_button_display'),
            'desc' => __('Upgrade to the Ninja Forms THREE.', 'ninja-forms')
        );

        return $settings;
    }

    public function settings_upgrade_button_display()
    {
        include plugin_dir_path( __FILE__ ) . 'tmpl-settings-upgrade-button.html.php';
    }

    public function optin() {
        if ( ! current_user_can( 'manage_options' ) ) return false;

        $api_url = 'http://api.ninjaforms.com/';

        /**
         * Update our tracking option.
         */
        update_option( 'ninja_forms_allow_tracking', true );
        update_option( 'ninja_forms_do_not_allow_tracking', false );

        /**
         * Gather site data before we send.
         *
         * We send the following site data with our passed data:
         * IP Address
         * Email
         * Site Url
         */

        $ip_address = '';
        if ( array_key_exists( 'SERVER_ADDR', $_SERVER ) ) {
            $ip_address = $_SERVER[ 'SERVER_ADDR' ];
        } else if ( array_key_exists( 'LOCAL_ADDR', $_SERVER ) ) {
            $ip_address = $_SERVER[ 'LOCAL_ADDR' ];
        }

        /**
         * Email address of the current user, defaulting to admin email if they do not have one.
         */
        $current_user = wp_get_current_user();
        if ( ! empty ( $current_user->user_email ) ) {
            $email = $current_user->user_email;
        } else {
            $email = get_option( 'admin_email' );
        }        

        $site_data = array(
            'url'           => site_url(),
            'ip_address'    => $ip_address,
            'email'         => $email,
        );

        /**
         * Send our environment variables.
         */
        
        global $wpdb;

        // Plugins
        $active_plugins = (array) get_option( 'active_plugins', array() );

        //WP_DEBUG
        if ( defined('WP_DEBUG') && WP_DEBUG ){
            $debug = 1;
        } else {
            $debug =  0;
        }

        //WPLANG
        if ( defined( 'WPLANG' ) && WPLANG ) {
            $lang = WPLANG;
        } else {
            $lang = 'default';
        }

        $ip_address = '';
        if ( array_key_exists( 'SERVER_ADDR', $_SERVER ) ) {
            $ip_address = $_SERVER[ 'SERVER_ADDR' ];
        } else if ( array_key_exists( 'LOCAL_ADDR', $_SERVER ) ) {
            $ip_address = $_SERVER[ 'LOCAL_ADDR' ];
        }

        $host_name = gethostbyaddr( $ip_address );

        if ( is_multisite() ) {
            $multisite_enabled = 1;
        } else {
            $multisite_enabled = 0;
        }

        $tls = 'unknown';

        $environment = array(
            'nf_version'                => NF_PLUGIN_VERSION,
            'wp_version'                => get_bloginfo('version'),
            'multisite_enabled'         => $multisite_enabled,
            'server_type'               => $_SERVER['SERVER_SOFTWARE'],
            'tls_version'               => $tls,
            'php_version'               => phpversion(),
            'mysql_version'             => $wpdb->db_version(),
            'wp_memory_limit'           => WP_MEMORY_LIMIT,
            'wp_debug_mode'             => $debug,
            'wp_lang'                   => $lang,
            'wp_max_upload_size'        => size_format( wp_max_upload_size() ),
            'php_max_post_size'         => ini_get( 'post_max_size' ),
            'hostname'                  => $host_name,
            'smtp'                      => ini_get('SMTP'),
            'smtp_port'                 => ini_get('smtp_port'),
            'active_plugins'            => $active_plugins,
        );

        /*
         * Send our data using wp_remote_post.
         */
        $response = wp_remote_post(
            $api_url,
            array(
                'body' => array(
                    'slug'          => 'update_environment_vars',
                    'data'          => wp_json_encode( $environment ),
                    'site_data'     => wp_json_encode( $site_data ),
                ),
            )
        );

        $send_email = absint( $_REQUEST[ 'send_email' ] );

        /*
         * Send our opt-in event using wp_remote_post.
         */
        $response = wp_remote_post(
            $api_url,
            array(
                'body' => array(
                    'slug'          => 'optin',
                    'data'          => wp_json_encode( array( 'send_email' => $send_email ) ),
                    'site_data'     => wp_json_encode( $site_data ),
                ),
            )
        );
        
        die();
    }

    public function optout() {
        if ( ! current_user_can( 'manage_options' ) ) return false;
        /**
         * Update our tracking option
         */
        update_option( 'ninja_forms_do_not_allow_tracking', true );

        die();
    }
}

new NF_THREE_Submenu();
