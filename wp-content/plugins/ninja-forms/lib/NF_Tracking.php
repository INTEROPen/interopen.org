<?php
/**
 * Tracking functions for reporting plugin usage to the Ninja Forms site for users that have opted in
 *
 * @package     Ninja Forms
 * @subpackage  Admin
 * @copyright   Copyright (c) 2016, The WP Ninjas
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.9.52
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class NF_Tracking
 */
final class NF_Tracking
{
    const OPT_IN = 1;
    const OPT_OUT = 0;
    const FLAG = 'ninja_forms_opt_in';

    /**
     * NF_Tracking constructor.
     */
    public function __construct()
    {
        if( isset( $_GET[ self::FLAG ] ) ){
            add_action( 'admin_init', array( $this, 'maybe_opt_in' ) );
        }

        // Temporary: Report previously opted-in users that were not already reported. @todo Remove after a couple of versions.
        add_action( 'admin_init', array( $this, 'report_optin' ) );

        add_filter( 'nf_admin_notices', array( $this, 'admin_notice' ) );

        add_filter( 'ninja_forms_check_setting_allow_tracking',  array( $this, 'check_setting' ) );
        add_filter( 'ninja_forms_update_setting_allow_tracking', array( $this, 'update_setting' ) );
    }

    /**
     * Check if an opt in/out action should be performed.
     *
     * @access public
     * @hook admin_init
     */
    public function maybe_opt_in()
    {
        if( $this->can_opt_in() ) {

            $opt_in_action = htmlspecialchars( $_GET[ self::FLAG ] );

            if( self::OPT_IN == $opt_in_action ){
                $this->opt_in();
            }

            if( self::OPT_OUT == $opt_in_action ){
                $this->opt_out();
            }
        }
        header( 'Location: ' . admin_url( 'admin.php?page=ninja-forms' ) );
    }

    /**
     * Report that a user has opted-in.
     *
     * @param array $data Dispatch event data.
     */
    function report_optin($data = array() )
    {
        // Only send initial opt-in.
        if( get_option( 'ninja_forms_optin_reported', 0 ) ) return;

        $data = wp_parse_args( $data, array(
            'send_email' => 1 // "Send Email" by default, if not specified (legacy).
        ) );

        Ninja_Forms()->dispatcher()->send( 'optin', $data );
        Ninja_Forms()->dispatcher()->update_environment_vars();

        // Debounce opt-in dispatch.
        update_option( 'ninja_forms_optin_reported', 1 );
    }

    /**
     * Register the Admin Notice for asking users to opt in to tracking
     *
     * @access public
     * @hook nf_admin_notices
     * @param array $notices
     * @return array $notices
     */
    public function admin_notice( $notices )
    {
        // Check if the user is allowed to opt in.
        if( ! $this->can_opt_in() ) return $notices;

        // Check if the user is already opted in/out.
        if( $this->is_opted_in() || $this->is_opted_out()  ) return $notices;

        $notices[ 'allow_tracking' ] = array(
            'title' => __( 'Please help us improve Ninja Forms!', 'ninja-forms' ),
            'msg' => '
                    If you agree, we will collect some server data and information about how you use Ninja Forms. 
                    <em>No submission data will be collected.</em>
                    This data will help us troubleshoot errors and improve your Ninja Forms experience.
                
                <p>    
                    <input id="nf-optin-send-email" type="checkbox" checked="checked"> You can also occasionally send me an email about using Ninja Forms.
                </p>',
            'link' => implode( ' ', array(
                sprintf( __( '%sYes, I want to make Ninja Forms better!%s', 'ninja-forms' ), '<a href="' . $this->get_opt_in_url( admin_url( 'admin.php?page=ninja-forms' ) ) . '" class="button-primary" id="ninja-forms-improve">', '</a>' ),
                sprintf( __( '%sNo, please don\'t collect errors or other data.%s', 'ninja-forms' ), '<a href="' . $this->get_opt_out_url( admin_url( 'admin.php?page=ninja-forms' ) ) . '" class="button-secondary" id="ninja-forms-do-not-improve">', '</a>' ),
            )),
            'int' => 0, // No delay
            'blacklist' => array(
                'ninja-forms-three'
            )
        );

        echo "<script type='text/javascript'>
            jQuery( document ).ready( function( $ ) {
                jQuery( '#ninja-forms-improve' ).click( function( e ) {
                    e.preventDefault();
                    var send_email, url;
                    if ( jQuery( '#nf-optin-send-email' ).attr( 'checked' ) ) {
                        send_email = 1;
                    } else {
                        send_email = 0;
                    }
                    url = jQuery( e.target ).attr( 'href' );
                    window.location.href = url + '&send_email=' + send_email;
                } );
            } );
        </script>";

        return $notices;
    }

    /**
     * Check if the current user is allowed to opt in on behalf of a site
     *
     * @return bool
     */
    private function can_opt_in()
    {
        return current_user_can( apply_filters( 'ninja_forms_admin_opt_in_capabilities', 'manage_options' ) );
    }

    /**
     * Check if a site is opted in
     *
     * @access public
     * @return bool
     */
    public function is_opted_in()
    {
        return (bool) get_option( 'ninja_forms_allow_tracking', $this->is_freemius_opted_in() );
    }

    private function is_freemius_opted_in()
    {
        $freemius = get_option( 'fs_accounts' );
        if( ! $freemius ) return false;
        if( ! isset( $freemius[ 'plugin_data' ] ) ) return false;
        if( ! isset( $freemius[ 'plugin_data' ][ 'ninja-forms' ] ) ) return false;
        if( ! isset( $freemius[ 'plugin_data' ][ 'ninja-forms' ][ 'activation_timestamp' ] ) ) return false;
        return true;
    }

    /**
     * Opt In a site for tracking
     *
     * @access private
     * @return null
     */
    public function opt_in()
    {
        if( $this->is_opted_in() ) return;

        /**
         * Update our tracking options.
         */
        update_option( 'ninja_forms_allow_tracking', true );
        update_option( 'ninja_forms_do_not_allow_tracking', false );

        /**
         * Send updated environment variables.
         */
        Ninja_Forms()->dispatcher()->update_environment_vars();

        /**
         * Send our optin event
         */
        if ( isset ( $_REQUEST[ 'send_email' ] ) ) {
            $send_email = absint( $_REQUEST[ 'send_email' ] );
        } else {
            $send_email = 1;
        }

        $this->report_optin( array( 'send_email' => $send_email ) );
    }

    /**
     * Get the Opt In URL
     *
     * @access private
     * @param string $url
     * @return string $url
     */
    private function get_opt_in_url( $url )
    {
        return add_query_arg( 'ninja_forms_opt_in', self::OPT_IN, $url );
    }

    /**
     * Check if a site is opted out
     *
     * @access public
     * @return bool
     */
    public function is_opted_out()
    {
        return (bool) get_option( 'ninja_forms_do_not_allow_tracking', $this->is_freemius_opted_out() );
    }

    private function is_freemius_opted_out()
    {
        $freemius = get_option( 'fs_accounts' );
        if( ! $freemius ) return false;
        if( ! isset( $freemius[ 'plugin_data' ] ) ) return false;
        if( ! isset( $freemius[ 'plugin_data' ][ 'ninja-forms' ] ) ) return false;
        if( ! isset( $freemius[ 'plugin_data' ][ 'ninja-forms' ][ 'is_anonymous' ] ) ) return false;
        return true;
    }

    /**
     * Opt Out a site from tracking
     *
     * @access private
     * @return null
     */
    private function opt_out()
    {
        if( $this->is_opted_out() ) return;

        // Disable tracking.
        update_option( 'ninja_forms_allow_tracking', false );
        update_option( 'ninja_forms_do_not_allow_tracking', true );

        // Clear dispatch debounce flag.
        update_option( 'ninja_forms_optin_reported', 0 );
    }

    /**
     * Get the Opt Out URL
     *
     * @access private
     * @param string $url
     * @return string $url
     */
    private function get_opt_out_url( $url )
    {
        return add_query_arg( 'ninja_forms_opt_in', self::OPT_OUT, $url );
    }

    public function check_setting( $setting )
    {
        if( $this->is_opted_in() && ! $this->is_opted_out() ) {
            $setting[ 'value' ] = "1";
        } else {
            $setting[ 'value' ] = "0";
        }
        return $setting;
    }

    public function update_setting( $value )
    {
        if( "1" == $value ){ // Allow Tracking
            $this->opt_in();
        } else {
            $this->opt_out();
        }
        return $value;
    }

} // END CLASS NF_Tracking
