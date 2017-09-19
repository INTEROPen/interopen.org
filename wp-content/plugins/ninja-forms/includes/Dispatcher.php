<?php

/**
 * Handles sending information to our api.ninjaforms.com endpoint.
 *
 * @since  3.2
 */
final class NF_Dispatcher
{
    private $api_url = 'http://api.ninjaforms.com/';

    /**
     * Returns bool true if we are opted-in or have a premium add-on.
     * If a premium add-on is installed, then users have opted into tracked via our terms and conditions.
     * If no premium add-ons are installed, check to see if the user has opted in or out of anonymous usage tracking.
     *
     * @since  version
     * @return bool
     */
    public function should_we_send() {

        /**
         * TODO:
         * Prevent certain URLS or IPs from submitting. i.e. staging, 127.0.0.1, localhost, etc.
         */

        if ( ! has_filter( 'ninja_forms_settings_licenses_addons' ) && ( ! Ninja_Forms()->tracking->is_opted_in() || Ninja_Forms()->tracking->is_opted_out() ) ) {
            return false;
        }
        return true;
    }

    /**
     * Package up our environment variables and send those to our API endpoint.
     * 
     * @since  3.2
     * @return void
     */
    public function update_environment_vars() {
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

        $tls = WPN_Helper::get_tls();
        if ( ! $tls ) $tls = 'unknown';

        $environment = array(
            'nf_version'                => Ninja_Forms::VERSION,
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

        $this->send( 'update_environment_vars', $environment );
    }

    /**
     * Sends a campaign slug and data to our API endpoint.
     * Checks to ensure that the user has 1) opted into tracking or 2) they have a premium add-on installed.
     * 
     * @since  3.2
     * @param  string       $slug   Campaign slug
     * @param  array        $data   Array of data being sent. Should NOT already be a JSON string.
     * @return void
     */
    public function send( $slug, $data = array() ) {

        if ( ! $this->should_we_send() ) {
            return false;
        }

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

        /*
         * Send our data using wp_remote_post.
         */
         $response = wp_remote_post(
            $this->api_url,
            array(
                'body' => array(
                    'slug'          => $slug,
                    'data'          => wp_json_encode( $data ),
                    'site_data'     => wp_json_encode( $site_data ),
                ),
            )
        );
    }
}
