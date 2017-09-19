<?php
/*
 * Meta Slider. Slideshow plugin for WordPress.
 *
 * Plugin Name: Meta Slider
 * Plugin URI:  https://www.metaslider.com
 * Description: Easy to use slideshow plugin. Create SEO optimised responsive slideshows with Nivo Slider, Flex Slider, Coin Slider and Responsive Slides.
 * Version:     3.5.1
 * Author:      Team Updraft
 * Author URI:  https://www.metaslider.com
 * License:     GPL-2.0+
 * Copyright:   2017 Simba Hosting Ltd
 *
 * Text Domain: ml-slider
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // disable direct access
}

if ( ! class_exists( 'MetaSliderPlugin' ) ) :

/**
 * Register the plugin.
 *
 * Display the administration panel, insert JavaScript etc.
 */
class MetaSliderPlugin {

    /**
     * @var string
     */
    public $version = '3.5.1';


    /**
     * @var MetaSlider
     */
    public $slider = null;


    /**
     * Init
     */
    public static function init() {

        $metaslider = new self();

    }


    /**
     * Constructor
     */
    public function __construct() {

        $this->define_constants();
        $this->includes();
        $this->setup_actions();
        $this->setup_filters();
        $this->setup_shortcode();
        $this->register_slide_types();

    }


    /**
     * Define Meta Slider constants
     */
    private function define_constants() {

        define( 'METASLIDER_VERSION',    $this->version );
        define( 'METASLIDER_BASE_URL',   trailingslashit( plugins_url( 'ml-slider' ) ) );
        define( 'METASLIDER_ASSETS_URL', trailingslashit( METASLIDER_BASE_URL . 'assets' ) );
        define( 'METASLIDER_PATH',       plugin_dir_path( __FILE__ ) );

    }


    /**
     * All Meta Slider classes
     */
    private function plugin_classes() {

        return array(
            'metaslider'             => METASLIDER_PATH . 'inc/slider/metaslider.class.php',
            'metacoinslider'         => METASLIDER_PATH . 'inc/slider/metaslider.coin.class.php',
            'metaflexslider'         => METASLIDER_PATH . 'inc/slider/metaslider.flex.class.php',
            'metanivoslider'         => METASLIDER_PATH . 'inc/slider/metaslider.nivo.class.php',
            'metaresponsiveslider'   => METASLIDER_PATH . 'inc/slider/metaslider.responsive.class.php',
            'metaslide'              => METASLIDER_PATH . 'inc/slide/metaslide.class.php',
            'metaimageslide'         => METASLIDER_PATH . 'inc/slide/metaslide.image.class.php',
            'metasliderimagehelper'  => METASLIDER_PATH . 'inc/metaslider.imagehelper.class.php',
            'metaslidersystemcheck'  => METASLIDER_PATH . 'inc/metaslider.systemcheck.class.php',
            'metaslider_widget'      => METASLIDER_PATH . 'inc/metaslider.widget.class.php',
            'simple_html_dom'        => METASLIDER_PATH . 'inc/simple_html_dom.php'
        );

    }


    /**
     * Load required classes
     */
    private function includes() {

        $autoload_is_disabled = defined( 'METASLIDER_AUTOLOAD_CLASSES' ) && METASLIDER_AUTOLOAD_CLASSES === false;

        if ( function_exists( "spl_autoload_register" ) && ! ( $autoload_is_disabled ) ) {

            // >= PHP 5.2 - Use auto loading
            if ( function_exists( "__autoload" ) ) {
                spl_autoload_register( "__autoload" );
            }

            spl_autoload_register( array( $this, 'autoload' ) );

        } else {

            // < PHP5.2 - Require all classes
            foreach ( $this->plugin_classes() as $id => $path ) {
                if ( is_readable( $path ) && ! class_exists( $id ) ) {
                    require_once( $path );
                }
            }

        }

    }


    /**
     * Autoload Meta Slider classes to reduce memory consumption
     */
    public function autoload( $class ) {

        $classes = $this->plugin_classes();

        $class_name = strtolower( $class );

        if ( isset( $classes[$class_name] ) && is_readable( $classes[$class_name] ) ) {
            require_once( $classes[$class_name] );
        }

    }


    /**
     * Register the [metaslider] shortcode.
     */
    private function setup_shortcode() {

        add_shortcode( 'metaslider', array( $this, 'register_shortcode' ) );
        add_shortcode( 'ml-slider', array( $this, 'register_shortcode' ) ); // backwards compatibility

    }


    /**
     * Hook Meta Slider into WordPress
     */
    private function setup_actions() {

        add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 9553 );
        add_action( 'init', array( $this, 'register_post_types' ) );
        add_action( 'init', array( $this, 'register_taxonomy' ) );
        add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
        add_action( 'admin_footer', array( $this, 'admin_footer' ), 11 );
        add_action( 'widgets_init', array( $this, 'register_metaslider_widget' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_global_styles'), 11 );


        add_action( 'admin_post_metaslider_preview', array( $this, 'do_preview' ) );
        add_action( 'admin_post_metaslider_hide_go_pro_page', array( $this, 'hide_go_pro_page' ) );
        add_action( 'admin_post_metaslider_switch_view', array( $this, 'switch_view' ) );
        add_action( 'admin_post_metaslider_delete_slide', array( $this, 'delete_slide' ) );
        add_action( 'admin_post_metaslider_delete_slider', array( $this, 'delete_slider' ) );
        add_action( 'admin_post_metaslider_create_slider', array( $this, 'create_slider' ) );
        add_action( 'admin_post_metaslider_update_slider', array( $this, 'update_slider' ) );

        add_action( 'media_upload_vimeo', array( $this, 'upgrade_to_pro_tab' ) );
        add_action( 'media_upload_youtube', array( $this, 'upgrade_to_pro_tab' ) );
        add_action( 'media_upload_post_feed', array( $this, 'upgrade_to_pro_tab' ) );
        add_action( 'media_upload_layer', array( $this, 'upgrade_to_pro_tab' ) );

    }


    /**
     * Hook Meta Slider into WordPress
     */
    private function setup_filters() {

        add_filter( 'media_upload_tabs', array( $this, 'custom_media_upload_tab_name' ), 998 );
        add_filter( 'media_view_strings', array( $this, 'custom_media_uploader_tabs' ), 5 );
        add_filter( 'media_buttons_context', array( $this, 'insert_metaslider_button' ) );

        // add 'go pro' link to plugin options
        $plugin = plugin_basename( __FILE__ );

        add_filter( "plugin_action_links_{$plugin}", array( $this, 'upgrade_to_pro_link' ) );

        // html5 compatibility for stylesheets enqueued within <body>
        add_filter( 'style_loader_tag', array( $this, 'add_property_attribute_to_stylesheet_links' ), 11, 2 );

    }


    /**
     * Register Meta Slider widget
     */
    public function register_metaslider_widget() {

        register_widget( 'MetaSlider_Widget' );

    }


    /**
     * Register ML Slider post type
     */
    public function register_post_types() {

        $show_ui = false;

        $capability = apply_filters( 'metaslider_capability', 'edit_others_posts' );

        if ( is_admin() && current_user_can( $capability ) && ( isset($_GET['show_ui']) || defined("METASLIDER_DEBUG") && METASLIDER_DEBUG ) ) {
            $show_ui = true;
        }

        register_post_type( 'ml-slider', array(
                'query_var' => false,
                'rewrite' => false,
                'public' => false,
                'exclude_from_search' => true,
                'publicly_queryable' => false,
                'show_in_nav_menus' => false,
                'show_ui' => $show_ui,
                'labels' => array(
                    'name' => 'Meta Slider'
                )
            )
        );

        register_post_type( 'ml-slide', array(
                'query_var' => false,
                'rewrite' => false,
                'public' => false,
                'exclude_from_search' => true,
                'publicly_queryable' => false,
                'show_in_nav_menus' => false,
                'show_ui' => $show_ui,
                'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt'),
                'labels' => array(
                    'name' => 'Meta Slides'
                )
            )
        );

    }


    /**
     * Register taxonomy to store slider => slides relationship
     */
    public function register_taxonomy() {

        $show_ui = false;

        $capability = apply_filters( 'metaslider_capability', 'edit_others_posts' );

        if (is_admin() && current_user_can( $capability ) && ( isset($_GET['show_ui']) || defined("METASLIDER_DEBUG") && METASLIDER_DEBUG ) ) {
            $show_ui = true;
        }

        register_taxonomy( 'ml-slider', array('attachment', 'ml-slide'), array(
                'hierarchical' => true,
                'public' => false,
                'query_var' => false,
                'rewrite' => false,
                'show_ui' => $show_ui,
                'label' => "Slider"
            )
        );

    }


    /**
     * Register our slide types
     */
    private function register_slide_types() {

        $image = new MetaImageSlide();

    }


    /**
     * Add the menu page
     */
    public function register_admin_menu() {
        global $user_ID;

        $title = apply_filters( 'metaslider_menu_title', 'Meta Slider' );

        $capability = apply_filters( 'metaslider_capability', 'edit_others_posts' );

        $page = add_menu_page( $title, $title, $capability, 'metaslider', array(
                $this, 'render_admin_page'
            ), "", 9501 );

        // ensure our JavaScript is only loaded on the Meta Slider admin page
        add_action( 'admin_print_scripts-' . $page, array( $this, 'register_admin_scripts' ) );
        add_action( 'admin_print_styles-' . $page, array( $this, 'register_admin_styles' ) );
        add_action( 'load-' . $page, array( $this, 'help_tab' ) );

        if ( ! is_plugin_active( 'ml-slider-pro/ml-slider-pro.php' ) && get_user_meta( $user_ID, "metaslider_hide_go_pro", true ) !== 'true' ) {

            $page = add_submenu_page(
                'metaslider',
                __( 'Go Pro!', 'ml-slider' ),
                __( 'Go Pro!', 'ml-slider' ),
                $capability,
                'metaslider-go-pro',
                array( $this, 'go_pro_page' )
            );

            add_action( 'admin_print_styles-' . $page, array( $this, 'register_admin_styles' ) );

        }

    }

    /**
     * Enqueue CSS for admin menu item font icon
     *
     * @since 3.4
     */
    public function admin_enqueue_global_styles() {
        wp_enqueue_style( 'metaslider-global', METASLIDER_ASSETS_URL . 'metaslider/global.css', array(), METASLIDER_VERSION );
    }


    /**
     * Go Pro page content
     */
    public function go_pro_page() {

        $upgrade_link = esc_url( add_query_arg(
            array(
                'utm_source' => 'lite',
                'utm_medium' => 'nag',
                'utm_campaign' => 'pro'
            ), 'https://www.metaslider.com/upgrade/' ) );

        $link = apply_filters( 'metaslider_hoplink', $upgrade_link );

        $hide_link = '<a href="' . admin_url( "admin-post.php?action=metaslider_hide_go_pro_page" ) . '">Hide this page</a>';
        $gopro_link = "<a class='button button-primary' href='{$link}' target='_blank'>Find out more</a>";
        $support_link = '<a href="https://wordpress.org/support/plugin/ml-slider">Support</a>';
        $documentation_link = '<a href="https://www.metaslider.com/documentation/">Documentation</a>';

        ?>
            <h2>Supercharge Your Sliders with Meta Slider Pro!</h2>

            <ul class='metaslider_gopro'>
                <li>Create <b>animated HTML slides</b> using the drag &amp; drop layer editor (WYSIWYG)</li>
                <li>Insert <b>YouTube</b> and <b>Vimeo</b> videos into your slideshows</li>
                <li>Automatically populate your slideshows with your <b>latest blog posts</b> or custom post types</li>
                <li>Customize the look of your slideshows with the <b>Theme Editor</b> (25+ settings including custom arrow images, dot colors and caption styling)</li>
                <li>Give your slideshows a gallery feel with <b>thumbnail navigation</b></li>
                <li>Feature <b>WooCommerce</b> products in your slideshows</li>
                <li>Show your latest events from <b>The Events Calendar</b> plugin</li>
                <li><b>Easy to install</B> - Meta Slider Pro installs as a seperate plugin alongside Meta Slider and seamlessly adds in the new functionality</li>
                <li><b>Easy to update</b> - new updates will be displayed on your plugins page (just like your other plugins!)</li>
                <li>Upgrade with confidence with our <b>30 day no questions money back guarantee</b></li>
                <li>Meta Slider Pro users receive <b>priority support</b> from our dedicated team, we’re on hand to help you get the most of Meta Slider</li>
            </ul>

            <p><?php echo $gopro_link; ?></p>
            <p><?php echo $support_link; ?> <?php echo $documentation_link; ?></p>
            <p><em>Don't want to see this? <?php echo $hide_link; ?></em></p>
        <?php

    }

    /**
     *  Store the users preference to hide the go pro page.
     */
    public function hide_go_pro_page() {
        global $user_ID;

        if ( ! get_user_meta( $user_ID, "metaslider_hide_go_pro" ) ) {
            add_user_meta( $user_ID, "metaslider_hide_go_pro", "true" );
        }

        wp_redirect( admin_url( "admin.php?page=metaslider" ) );

    }


    /**
     * Shortcode used to display slideshow
     *
     * @return string HTML output of the shortcode
     */
    public function register_shortcode( $atts ) {

        extract( shortcode_atts( array(
            'id' => false,
            'restrict_to' => false
        ), $atts, 'metaslider' ) );


        if ( ! $id ) {
            return false;
        }

        // handle [metaslider id=123 restrict_to=home]
        if ($restrict_to && $restrict_to == 'home' && ! is_front_page()) {
            return;
        }

        if ($restrict_to && $restrict_to != 'home' && ! is_page( $restrict_to ) ) {
            return;
        }

        // we have an ID to work with
        $slider = get_post( $id );

        // check the slider is published and the ID is correct
        if ( ! $slider || $slider->post_status != 'publish' || $slider->post_type != 'ml-slider' ) {
            return "<!-- meta slider {$atts['id']} not found -->";
        }

        // lets go
        $this->set_slider( $id, $atts );
        $this->slider->enqueue_scripts();

        return $this->slider->render_public_slides();

    }


    /**
     * Initialise translations
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain( 'ml-slider', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    }


    /**
     * Add the help tab to the screen.
     */
    public function help_tab() {

        $screen = get_current_screen();

        // documentation tab
        $screen->add_help_tab( array(
                'id'    => 'documentation',
                'title' => __( 'Documentation', 'ml-slider' ),
                'content'   => "<p><a href='https://www.metaslider.com/documentation/' target='blank'>Meta Slider Documentation</a></p>",
            )
        );

    }


    /**
     * Rehister admin styles
     */
    public function register_admin_styles() {

        wp_enqueue_style( 'metaslider-admin-styles', METASLIDER_ASSETS_URL . 'metaslider/admin.css', false, METASLIDER_VERSION );
        wp_enqueue_style( 'metaslider-colorbox-styles', METASLIDER_ASSETS_URL . 'colorbox/colorbox.css', false, METASLIDER_VERSION );
        wp_enqueue_style( 'metaslider-tipsy-styles', METASLIDER_ASSETS_URL . 'tipsy/tipsy.css', false, METASLIDER_VERSION );

        do_action( 'metaslider_register_admin_styles' );

    }


    /**
     * Register admin JavaScript
     */
    public function register_admin_scripts() {

        // media library dependencies
        wp_enqueue_media();

        // plugin dependencies
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'metaslider-admin-script', METASLIDER_ASSETS_URL . 'metaslider/admin.js', array( 'jquery' ), METASLIDER_VERSION, true );
        wp_enqueue_script( 'metaslider-colorbox', METASLIDER_ASSETS_URL . 'colorbox/jquery.colorbox-min.js', array( 'jquery' ), METASLIDER_VERSION, true );
        wp_enqueue_script( 'metaslider-tipsy', METASLIDER_ASSETS_URL . 'tipsy/jquery.tipsy.js', array( 'jquery' ), METASLIDER_VERSION, true );

        wp_dequeue_script( 'link' ); // WP Posts Filter Fix (Advanced Settings not toggling)
        wp_dequeue_script( 'ai1ec_requirejs' ); // All In One Events Calendar Fix (Advanced Settings not toggling)

        $this->localize_admin_scripts();

        do_action( 'metaslider_register_admin_scripts' );

    }


    /**
     * Localise admin script
     */
    public function localize_admin_scripts() {

        wp_localize_script( 'metaslider-admin-script', 'metaslider', array(
                'url' => __( "URL", "ml-slider" ),
                'caption' => __( "Caption", "ml-slider" ),
                'new_window' => __( "New Window", "ml-slider" ),
                'confirm' => __( "Are you sure?", "ml-slider" ),
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'change_image' => __( "Select replacement image", "ml-slider"),
                'resize_nonce' => wp_create_nonce( 'metaslider_resize' ),
                'addslide_nonce' => wp_create_nonce( 'metaslider_addslide' ),
                'changeslide_nonce' => wp_create_nonce( 'metaslider_changeslide' ),
                'iframeurl' => admin_url( 'admin-post.php?action=metaslider_preview' ),
                'useWithCaution' => __( "Caution: This setting is for advanced developers only. If you're unsure, leave it checked.", "ml-slider" )
            )
        );

    }


    /**
     * Outputs a blank page containing a slideshow preview (for use in the 'Preview' iFrame)
     */
    public function do_preview() {

        remove_action('wp_footer', 'wp_admin_bar_render', 1000);

        if ( isset( $_GET['slider_id'] ) && absint( $_GET['slider_id'] ) > 0 ) {
            $id = absint( $_GET['slider_id'] );

            ?>
            <!DOCTYPE html>
            <html>
                <head>
                    <style type='text/css'>
                        body, html {
                            overflow: hidden;
                            margin: 0;
                            padding: 0;
                        }
                    </style>
                    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
                    <meta http-equiv="Pragma" content="no-cache" />
                    <meta http-equiv="Expires" content="0" />
                </head>
                <body>
                    <?php echo do_shortcode("[metaslider id={$id}]"); ?>
                    <?php wp_footer(); ?>
                </body>
            </html>
            <?php
        }

        die();

    }


    /**
     * Check our WordPress installation is compatible with Meta Slider
     */
    public function do_system_check() {

        $systemCheck = new MetaSliderSystemCheck();
        $systemCheck->check();

    }


    /**
     * Update the tab options in the media manager
     */
    public function custom_media_uploader_tabs( $strings ) {

        //update strings
        if ( ( isset( $_GET['page'] ) && $_GET['page'] == 'metaslider' ) ) {
            $strings['insertMediaTitle'] = __( "Image", "ml-slider" );
            $strings['insertIntoPost'] = __( "Add to slider", "ml-slider" );
            // remove options

            $strings_to_remove = array(
                'createVideoPlaylistTitle',
                'createGalleryTitle',
                'insertFromUrlTitle',
                'createPlaylistTitle'
            );

            foreach ($strings_to_remove as $string) {
                if (isset($strings[$string])) {
                    unset($strings[$string]);
                }
            }
        }

        return $strings;

    }


    /**
     * Add extra tabs to the default wordpress Media Manager iframe
     *
     * @var array existing media manager tabs
     */
    public function custom_media_upload_tab_name( $tabs ) {

        $metaslider_tabs = array( 'post_feed', 'layer', 'youtube', 'vimeo' );

        // restrict our tab changes to the meta slider plugin page
        if ( ( isset( $_GET['page'] ) && $_GET['page'] == 'metaslider' ) || ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], $metaslider_tabs ) ) ) {
            $newtabs = array();

            if ( function_exists( 'is_plugin_active' ) && ! is_plugin_active( 'ml-slider-pro/ml-slider-pro.php' ) ) {
                $newtabs = array(
                    'post_feed' => __( "Post Feed", "metaslider" ),
                    'vimeo' => __( "Vimeo", "metaslider" ),
                    'youtube' => __( "YouTube", "metaslider" ),
                    'layer' => __( "Layer Slide", "metaslider" )
                );
            }

            if ( isset( $tabs['nextgen'] ) ) 
                unset( $tabs['nextgen'] );


            if ( is_array( $tabs ) ) {
                return array_merge( $tabs, $newtabs );
            } else {
                return $newtabs;
            }
            
        }

        return $tabs;


    }


    /**
     * Set the current slider
     */
    public function set_slider( $id, $shortcode_settings = array() ) {

        $type = 'flex';

        if ( isset( $shortcode_settings['type'] ) ) {
            $type = $shortcode_settings['type'];
        } else if ( $settings = get_post_meta( $id, 'ml-slider_settings', true ) ) {
            if ( is_array( $settings ) && isset( $settings['type'] ) ) {
                $type = $settings['type'];
            }
        }

        if ( ! in_array( $type, array( 'flex', 'coin', 'nivo', 'responsive' ) ) ) {
            $type = 'flex';
        }

        $this->slider = $this->load_slider( $type, $id, $shortcode_settings );

    }


    /**
     * Create a new slider based on the sliders type setting
     */
    private function load_slider( $type, $id, $shortcode_settings ) {

        switch ( $type ) {
            case( 'coin' ):
                return new MetaCoinSlider( $id, $shortcode_settings );
            case( 'flex' ):
                return new MetaFlexSlider( $id, $shortcode_settings );
            case( 'nivo' ):
                return new MetaNivoSlider( $id, $shortcode_settings );
            case( 'responsive' ):
                return new MetaResponsiveSlider( $id, $shortcode_settings );
            default:
                return new MetaFlexSlider( $id, $shortcode_settings );

        }
    }


    /**
     *
     */
    public function update_slider() {

        check_admin_referer( "metaslider_update_slider" );

        $capability = apply_filters( 'metaslider_capability', 'edit_others_posts' );

        if ( ! current_user_can( $capability ) ) {
            return;
        }

        $slider_id = absint( $_POST['slider_id'] );

        if ( ! $slider_id ) {
            return;
        }

        // update settings
        if ( isset( $_POST['settings'] ) ) {

            $new_settings = $_POST['settings'];

            $old_settings = get_post_meta( $slider_id, 'ml-slider_settings', true );

            // convert submitted checkbox values from 'on' or 'off' to boolean values
            $checkboxes = apply_filters( "metaslider_checkbox_settings", array( 'noConflict', 'fullWidth', 'hoverPause', 'links', 'reverse', 'random', 'printCss', 'printJs', 'smoothHeight', 'center', 'carouselMode', 'autoPlay' ) );

            foreach ( $checkboxes as $checkbox ) {
                if ( isset( $new_settings[$checkbox] ) && $new_settings[$checkbox] == 'on' ) {
                    $new_settings[$checkbox] = "true";
                } else {
                    $new_settings[$checkbox] = "false";
                }
            }

            $settings = array_merge( (array)$old_settings, $new_settings );

            // update the slider settings
            update_post_meta( $slider_id, 'ml-slider_settings', $settings );

        }

        // update slideshow title
        if ( isset( $_POST['title'] ) ) {

            $slide = array(
                'ID' => $slider_id,
                'post_title' => esc_html( $_POST['title'] )
            );

            wp_update_post( $slide );

        }

        // update individual slides
        if ( isset( $_POST['attachment'] ) ) {

            foreach ( $_POST['attachment'] as $slide_id => $fields ) {
                do_action( "metaslider_save_{$fields['type']}_slide", $slide_id, $slider_id, $fields );
            }

        }

    }


    /**
     * Delete a slide. This doesn't actually remove the slide from WordPress, simply untags
     * it from the slide taxonomy.
     */
    public function delete_slide() {

        // check nonce
        check_admin_referer( "metaslider_delete_slide" );

        $capability = apply_filters( 'metaslider_capability', 'edit_others_posts' );

        if ( ! current_user_can( $capability ) ) {
            return;
        }

        $slide_id = absint( $_GET['slide_id'] );
        $slider_id = absint( $_GET['slider_id'] );

        // Get the existing terms and only keep the ones we don't want removed
        $new_terms = array();
        $current_terms = wp_get_object_terms( $slide_id, 'ml-slider', array( 'fields' => 'ids' ) );
        $term = get_term_by( 'name', $slider_id, 'ml-slider' );

        foreach ( $current_terms as $current_term ) {
            if ( $current_term != $term->term_id ) {
                $new_terms[] = absint( $current_term );
            }
        }

        // untag slide from slider
        wp_set_object_terms( $slide_id, $new_terms, 'ml-slider' );

        // For new format slides - also trash the slide
        if ( get_post_type( $slide_id ) === 'ml-slide' ) {
            $id = wp_update_post( array(
                    'ID' => $slide_id,
                    'post_status' => 'trash'
                )
            );
        }

        wp_redirect( admin_url( "admin.php?page=metaslider&id={$slider_id}" ) );

    }


    /**
     * Delete a slider (send it to trash)
     */
    public function delete_slider() {

        // check nonce
        check_admin_referer( "metaslider_delete_slider" );

        $capability = apply_filters( 'metaslider_capability', 'edit_others_posts' );

        if ( ! current_user_can( $capability ) ) {
            return;
        }

        $slider_id = absint( $_GET['slider_id'] );

        if ( get_post_type( $slider_id ) != 'ml-slider' ) {
            wp_redirect( admin_url( "admin.php?page=metaslider" ) );
            wp_die();
        }

        // send the post to trash
        $id = wp_update_post( array(
                'ID' => $slider_id,
                'post_status' => 'trash'
            )
        );

        $this->delete_all_slides_from_slider($slider_id);

        $slider_id = $this->find_slider( 'modified', 'DESC' );

        wp_redirect( admin_url( "admin.php?page=metaslider&id={$slider_id}" ) );

    }


    /**
     * Trashes all new format slides for a given slideshow ID
     *
     * @param int $slider_id
     * @return int - The ID of the slideshow from which the slides were removed
     */
    private function delete_all_slides_from_slider($slider_id) {
        // find slides and trash them
        $args = array(
            'force_no_custom_order' => true,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'post_type' => array('ml-slide'),
            'post_status' => array('publish'),
            'lang' => '', // polylang, ingore language filter
            'suppress_filters' => 1, // wpml, ignore language filter
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'ml-slider',
                    'field' => 'slug',
                    'terms' => $slider_id
                )
            )
        );

        $query = new WP_Query( $args );

        while ( $query->have_posts() ) {
            $query->next_post();
            $id = $query->post->ID;

            $id = wp_update_post( array(
                    'ID' => $id,
                    'post_status' => 'trash'
                )
            );
        }

        return $slider_id;
    }


    /**
     *
     */
    public function switch_view() {
        global $user_ID;

        $view = $_GET['view'];

        $allowed_views = array('tabs', 'dropdown');

        if ( ! in_array( $view, $allowed_views ) ) {
            return;
        }

        delete_user_meta( $user_ID, "metaslider_view" );

        if ( $view == 'dropdown' ) {
            add_user_meta( $user_ID, "metaslider_view", "dropdown");
        }

        wp_redirect( admin_url( "admin.php?page=metaslider" ) );

    }


    /**
     * Create a new slider
     */
    public function create_slider() {

        // check nonce
        check_admin_referer( "metaslider_create_slider" );

        $capability = apply_filters( 'metaslider_capability', 'edit_others_posts' );

        if ( ! current_user_can( $capability ) ) {
            return;
        }

        $defaults = array();

        // if possible, take a copy of the last edited slider settings in place of default settings
        if ( $last_modified = $this->find_slider( 'modified', 'DESC' ) ) {
            $defaults = get_post_meta( $last_modified, 'ml-slider_settings', true );
        }

        // insert the post
        $id = wp_insert_post( array(
                'post_title' => __( "New Slider", "ml-slider" ),
                'post_status' => 'publish',
                'post_type' => 'ml-slider'
            )
        );

        // use the default settings if we can't find anything more suitable.
        if ( empty( $defaults ) ) {
            $slider = new MetaSlider( $id, array() );
            $defaults = $slider->get_default_parameters();
        }

        // insert the post meta
        add_post_meta( $id, 'ml-slider_settings', $defaults, true );

        // create the taxonomy term, the term is the ID of the slider itself
        wp_insert_term( $id, 'ml-slider' );

        wp_redirect( admin_url( "admin.php?page=metaslider&id={$id}" ) );

    }



    /**
     * Find a single slider ID. For example, last edited, or first published.
     *
     * @param string $orderby field to order.
     * @param string $order direction (ASC or DESC).
     * @return int slider ID.
     */
    private function find_slider( $orderby, $order ) {

        $args = array(
            'force_no_custom_order' => true,
            'post_type' => 'ml-slider',
            'num_posts' => 1,
            'post_status' => 'publish',
            'suppress_filters' => 1, // wpml, ignore language filter
            'orderby' => $orderby,
            'order' => $order
        );

        $the_query = new WP_Query( $args );

        while ( $the_query->have_posts() ) {
            $the_query->the_post();
            return $the_query->post->ID;
        }

        wp_reset_query();

        return false;

    }


    /**
     * Get sliders. Returns a nicely formatted array of currently
     * published sliders.
     *
     * @param string $sort_key
     * @return array all published sliders
     */
    public function all_meta_sliders( $sort_key = 'date' ) {

        $sliders = array();

        // list the tabs
        $args = array(
            'post_type' => 'ml-slider',
            'post_status' => 'publish',
            'orderby' => $sort_key,
            'suppress_filters' => 1, // wpml, ignore language filter
            'order' => 'ASC',
            'posts_per_page' => -1
        );

        $args = apply_filters( 'metaslider_all_meta_sliders_args', $args );

        // WP_Query causes issues with other plugins using admin_footer to insert scripts
        // use get_posts instead
        $all_sliders = get_posts( $args );

        foreach( $all_sliders as $slideshow ) {

            $active = $this->slider && ( $this->slider->id == $slideshow->ID ) ? true : false;

            $sliders[] = array(
                'active' => $active,
                'title' => $slideshow->post_title,
                'id' => $slideshow->ID
            );

        }

        return $sliders;

    }


    /**
     * Compare array values
     *
     * @param array $elem1
     * @param array $elem2
     * @return bool
     */
    private function compare_elems( $elem1, $elem2 ) {

        return $elem1['priority'] > $elem2['priority'];

    }


    /**
     *
     * @param array $aFields - array of field to render
     * @return string
     */
    public function build_settings_rows( $aFields ) {

        // order the fields by priority
        uasort( $aFields, array( $this, "compare_elems" ) );

        $return = "";

        // loop through the array and build the settings HTML
        foreach ( $aFields as $id => $row ) {
            // checkbox input type
            if ( $row['type'] == 'checkbox' ) {
                $return .= "<tr><td class='tipsy-tooltip' title=\"{$row['helptext']}\">{$row['label']}</td><td><input class='option {$row['class']} {$id}' type='checkbox' name='settings[{$id}]' {$row['checked']} />";

                if ( isset( $row['after'] ) ) {
                    $return .= "<span class='after'>{$row['after']}</span>";
                }

                $return .= "</td></tr>";
            }

            // navigation row
            if ( $row['type'] == 'navigation' ) {
                $navigation_row = "<tr class='{$row['type']}'><td class='tipsy-tooltip' title=\"{$row['helptext']}\">{$row['label']}</td><td><ul>";

                foreach ( $row['options'] as $k => $v ) {

                    if ( $row['value'] === true && $k === 'true' ) {
                        $checked = checked( true, true, false );
                    } else if ( $row['value'] === false && $k === 'false' ) {
                        $checked = checked( true, true, false );
                    } else {
                        $checked = checked( $k, $row['value'], false );
                    }

                    $disabled = $k == 'thumbnails' ? 'disabled' : '';
                    $navigation_row .= "<li><label><input type='radio' name='settings[{$id}]' value='{$k}' {$checked} {$disabled}/>{$v['label']}</label></li>";
                }

                $navigation_row .= "</ul></td></tr>";

                $return .= apply_filters( 'metaslider_navigation_options', $navigation_row, $this->slider );
            }

            // navigation row
            if ( $row['type'] == 'radio' ) {
                $navigation_row = "<tr class='{$row['type']}'><td class='tipsy-tooltip' title=\"{$row['helptext']}\">{$row['label']}</td><td><ul>";

                foreach ( $row['options'] as $k => $v ) {
                    $checked = checked( $k, $row['value'], false );
                    $class = isset( $v['class'] ) ? $v['class'] : "";
                    $navigation_row .= "<li><label><input type='radio' name='settings[{$id}]' value='{$k}' {$checked} class='radio {$class}'/>{$v['label']}</label></li>";
                }

                $navigation_row .= "</ul></td></tr>";

                $return .= apply_filters( 'metaslider_navigation_options', $navigation_row, $this->slider );
            }

            // header/divider row
            if ( $row['type'] == 'divider' ) {
                $return .= "<tr class='{$row['type']}'><td colspan='2' class='divider'><b>{$row['value']}</b></td></tr>";
            }

            // slideshow select row
            if ( $row['type'] == 'slider-lib' ) {
                $return .= "<tr class='{$row['type']}'><td colspan='2' class='slider-lib-row'>";

                foreach ( $row['options'] as $k => $v ) {
                    $checked = checked( $k, $row['value'], false );
                    $return .= "<input class='select-slider' id='{$k}' rel='{$k}' type='radio' name='settings[type]' value='{$k}' {$checked} />
                    <label for='{$k}'>{$v['label']}</label>";
                }

                $return .= "</td></tr>";
            }

            // number input type
            if ( $row['type'] == 'number' ) {
                $return .= "<tr class='{$row['type']}'><td class='tipsy-tooltip' title=\"{$row['helptext']}\">{$row['label']}</td><td><input class='option {$row['class']} {$id}' type='number' min='{$row['min']}' max='{$row['max']}' step='{$row['step']}' name='settings[{$id}]' value='" . absint( $row['value'] ) . "' /><span class='after'>{$row['after']}</span></td></tr>";
            }

            // select drop down
            if ( $row['type'] == 'select' ) {
                $return .= "<tr class='{$row['type']}'><td class='tipsy-tooltip' title=\"{$row['helptext']}\">{$row['label']}</td><td><select class='option {$row['class']} {$id}' name='settings[{$id}]'>";
                foreach ( $row['options'] as $k => $v ) {
                    $selected = selected( $k, $row['value'], false );
                    $return .= "<option class='{$v['class']}' value='{$k}' {$selected}>{$v['label']}</option>";
                }
                $return .= "</select></td></tr>";
            }

            // theme drop down
            if ( $row['type'] == 'theme' ) {
                $return .= "<tr class='{$row['type']}'><td class='tipsy-tooltip' title=\"{$row['helptext']}\">{$row['label']}</td><td><select class='option {$row['class']} {$id}' name='settings[{$id}]'>";
                $themes = "";

                foreach ( $row['options'] as $k => $v ) {
                    $selected = selected( $k, $row['value'], false );
                    $themes .= "<option class='{$v['class']}' value='{$k}' {$selected}>{$v['label']}</option>";
                }

                $return .= apply_filters( 'metaslider_get_available_themes', $themes, $this->slider->get_setting( 'theme' ) );

                $return .= "</select></td></tr>";
            }

            // text input type
            if ( $row['type'] == 'text' ) {
                $return .= "<tr class='{$row['type']}'><td class='tipsy-tooltip' title=\"{$row['helptext']}\">{$row['label']}</td><td><input class='option {$row['class']} {$id}' type='text' name='settings[{$id}]' value='" . esc_attr( $row['value'] ) . "' /></td></tr>";
            }

            // text input type
            if ( $row['type'] == 'textarea' ) {
                $return .= "<tr class='{$row['type']}'><td class='tipsy-tooltip' title=\"{$row['helptext']}\" colspan='2'>{$row['label']}</td></tr><tr><td colspan='2'><textarea class='option {$row['class']} {$id}' name='settings[{$id}]' />{$row['value']}</textarea></td></tr>";
            }

            // text input type
            if ( $row['type'] == 'title' ) {
                $return .= "<tr class='{$row['type']}'><td class='tipsy-tooltip' title=\"{$row['helptext']}\">{$row['label']}</td><td><input class='option {$row['class']} {$id}' type='text' name='{$id}' value='" . esc_attr( $row['value'] ) . "' /></td></tr>";
            }
        }

        return $return;

    }


    /**
     * Return an indexed array of all easing options
     *
     * @return array
     */
    private function get_easing_options() {

        $options = array(
            'linear', 'swing', 'jswing', 'easeInQuad', 'easeOutQuad', 'easeInOutQuad',
            'easeInCubic', 'easeOutCubic', 'easeInOutCubic', 'easeInQuart',
            'easeOutQuart', 'easeInOutQuart', 'easeInQuint', 'easeOutQuint',
            'easeInOutQuint', 'easeInSine', 'easeOutSine', 'easeInOutSine',
            'easeInExpo', 'easeOutExpo', 'easeInOutExpo', 'easeInCirc', 'easeOutCirc',
            'easeInOutCirc', 'easeInElastic', 'easeOutElastic', 'easeInOutElastic',
            'easeInBack', 'easeOutBack', 'easeInOutBack', 'easeInBounce', 'easeOutBounce',
            'easeInOutBounce'
        );

        foreach ( $options as $option ) {
            $return[$option] = array(
                'label' => ucfirst( preg_replace( '/(\w+)([A-Z])/U', '\\1 \\2', $option ) ),
                'class' => ''
            );
        }

        return $return;

    }

    /**
     * Output the slideshow selector.
     *
     * Show tabs or a dropdown list depending on the users saved preference.
     */
    public function print_slideshow_selector() {
        global $user_ID;

        $add_url = wp_nonce_url( admin_url( "admin-post.php?action=metaslider_create_slider" ), "metaslider_create_slider" );

        if ( $tabs = $this->all_meta_sliders() ) {

            if ( $this->get_view() == 'tabs' ) {

                echo "<div style='display: none;' id='screen-options-switch-view-wrap'>
                <a class='switchview dashicons-before dashicons-randomize tipsy-tooltip' title='" . __("Switch to Dropdown view", "ml-slider") . "' href='" . admin_url( "admin-post.php?action=metaslider_switch_view&view=dropdown") . "'>" . __("Dropdown", "ml-slider") . "</a></div>";

                echo "<h3 class='nav-tab-wrapper'>";

                foreach ( $tabs as $tab ) {

                    if ( $tab['active'] ) {
                        echo "<div class='nav-tab nav-tab-active'><input type='text' name='title'  value='" . esc_attr( $tab['title'] ) . "' onfocus='this.style.width = ((this.value.length + 1) * 9) + \"px\"' /></div>";
                    } else {
                        echo "<a href='?page=metaslider&amp;id={$tab['id']}' class='nav-tab'>" . esc_html( $tab['title'] ) . "</a>";
                    }

                }

                echo "<a href='{$add_url}' id='create_new_tab' class='nav-tab'>+</a>";
                echo "</h3>";

            } else {

                if ( isset( $_GET['add'] ) && $_GET['add'] == 'true' ) {

                    echo "<div id='message' class='updated'><p>" . __( "New slideshow created. Click 'Add Slide' to get started!", "ml-slider" ) . "</p></div>";

                }

                echo "<div style='display: none;' id='screen-options-switch-view-wrap'><a class='switchview dashicons-before dashicons-randomize tipsy-tooltip' title='" . __("Switch to Tab view", "ml-slider") . "' href='" . admin_url( "admin-post.php?action=metaslider_switch_view&view=tabs") . "'>" . __("Tabs", "ml-slider") . "</a></div>";

                echo "<div class='dropdown_container'><label for='select-slider'>" . __("Select Slider", "ml-slider") . ": </label>";
                echo "<select name='select-slider' onchange='if (this.value) window.location.href=this.value'>";

                $tabs = $this->all_meta_sliders( 'title' );

                foreach ( $tabs as $tab ) {

                    $selected = $tab['active'] ? " selected" : "";

                    if ( $tab['active'] ) {

                        $title = $tab['title'];

                    }

                    echo "<option value='?page=metaslider&amp;id={$tab['id']}'{$selected}>{$tab['title']}</option>";

                }

                echo "</select> " . __( 'or', "ml-slider" ) . " ";
                echo "<a href='{$add_url}'>" . __( 'Add New Slideshow', "ml-slider" ) . "</a></div>";

            }
        } else {
            echo "<h3 class='nav-tab-wrapper'>";
            echo "<a href='{$add_url}' id='create_new_tab' class='nav-tab'>+</a>";
            echo "<div class='bubble'>" . __( "Create your first slideshow", "ml-slider" ) . "</div>";
            echo "</h3>";
        }
    }


    /**
     * Return the users saved view preference.
     */
    public function get_view() {
        global $user_ID;

        if ( get_user_meta( $user_ID, "metaslider_view", true ) ) {
            return get_user_meta( $user_ID, "metaslider_view", true );
        }

        return 'tabs';
    }


    /**
     * Render the admin page (tabs, slides, settings)
     */
    public function render_admin_page() {

        // default to the latest slider
        $slider_id = $this->find_slider( 'modified', 'DESC' );

        // load a slider by ID
        if ( isset( $_REQUEST['id'] ) ) {
            $temp_id = absint( $_REQUEST['id'] );

            // check valid post ID
            if ( get_post( $temp_id ) ) {
                $slider_id = $temp_id;
            }
        }

        // finally, set the slider
        if ( $slider_id > 0 ) {
            $this->set_slider( $slider_id );
        }

        $this->upgrade_to_pro_cta();
        $this->do_system_check();

        $slider_id = $this->slider ? $this->slider->id : 0;

        ?>

        <script type='text/javascript'>
            var metaslider_slider_id = <?php echo $slider_id; ?>;
        </script>

        <div class="wrap metaslider">
            <form accept-charset="UTF-8" action="<?php echo admin_url( 'admin-post.php'); ?>" method="post">
                <input type="hidden" name="action" value="metaslider_update_slider">
                <input type="hidden" name="slider_id" value="<?php echo $slider_id; ?>">
                <?php wp_nonce_field( 'metaslider_update_slider' ); ?>

                <?php $this->print_slideshow_selector(); ?>

                <?php if ( ! $this->slider ) return; ?>

                <div id='poststuff'>
                    <div id='post-body' class='metabox-holder columns-2'>

                        <div id='post-body-content'>
                            <div class="left">

                                <?php do_action( "metaslider_admin_table_before", $this->slider->id ); ?>

                                <table class="widefat sortable">
                                    <thead>
                                        <tr>
                                            <th style="width: 100px;">
                                                <h3><?php _e( "Slides", "ml-slider" ) ?></h3>
                                                <?php do_action( "metaslider_admin_table_header_left", $this->slider->id ); ?>
                                            </th>
                                            <th>
                                                <a href='#' class='button alignright add-slide' data-editor='content' title='<?php _e( "Add Slide", "ml-slider" ) ?>'>
                                                    <span class='wp-media-buttons-icon'></span> <?php _e( "Add Slide", "ml-slider" ) ?>
                                                </a>
                                                <?php do_action( "metaslider_admin_table_header_right", $this->slider->id ); ?>
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                            $this->slider->render_admin_slides();
                                        ?>
                                    </tbody>
                                </table>

                                <?php do_action( "metaslider_admin_table_after", $this->slider->id ); ?>

                            </div>
                        </div>

                        <div id="postbox-container-1" class="postbox-container">
                            <div class='right'>
                                <div class="ms-postbox" id="metaslider_configuration">
                                    <div class='configuration'>
                                        <input class='alignright button button-primary' type='submit' name='save' id='ms-save' value='<?php _e( "Save", "ml-slider" ) ?>' />
                                        <input class='alignright button button-primary' type='submit' name='preview' id='ms-preview' value='<?php _e( "Save & Preview", "ml-slider" ) ?>' data-slider_id='<?php echo $this->slider->id ?>' data-slider_width='<?php echo $this->slider->get_setting( 'width' ) ?>' data-slider_height='<?php echo $this->slider->get_setting( 'height' ) ?>' />
                                        <span class="spinner"></span>
                                    </div>
                                    <div class="inside">
                                        <table class="settings">
                                            <tbody>
                                                <?php
                                                    $aFields = array(
                                                        'type' => array(
                                                            'priority' => 0,
                                                            'type' => 'slider-lib',
                                                            'value' => $this->slider->get_setting( 'type' ),
                                                            'options' => array(
                                                                'flex'       => array( 'label' => __( "Flex Slider", "ml-slider" ) ),
                                                                'responsive' => array( 'label' => __( "R. Slides", "ml-slider" ) ),
                                                                'nivo'       => array( 'label' => __( "Nivo Slider", "ml-slider" ) ),
                                                                'coin'       => array( 'label' => __( "Coin Slider", "ml-slider" ) )
                                                            )
                                                        ),
                                                        'width' => array(
                                                            'priority' => 10,
                                                            'type' => 'number',
                                                            'size' => 3,
                                                            'min' => 0,
                                                            'max' => 9999,
                                                            'step' => 1,
                                                            'value' => $this->slider->get_setting( 'width' ),
                                                            'label' => __( "Width", "ml-slider" ),
                                                            'class' => 'coin flex responsive nivo',
                                                            'helptext' => __( "Slideshow width", "ml-slider" ),
                                                            'after' => __( "px", "ml-slider" )
                                                        ),
                                                        'height' => array(
                                                            'priority' => 20,
                                                            'type' => 'number',
                                                            'size' => 3,
                                                            'min' => 0,
                                                            'max' => 9999,
                                                            'step' => 1,
                                                            'value' => $this->slider->get_setting( 'height' ),
                                                            'label' => __( "Height", "ml-slider" ),
                                                            'class' => 'coin flex responsive nivo',
                                                            'helptext' => __( "Slideshow height", "ml-slider" ),
                                                            'after' => __( "px", "ml-slider" )
                                                        ),
                                                        'effect' => array(
                                                            'priority' => 30,
                                                            'type' => 'select',
                                                            'value' => $this->slider->get_setting( 'effect' ),
                                                            'label' => __( "Effect", "ml-slider" ),
                                                            'class' => 'effect coin flex responsive nivo',
                                                            'helptext' => __( "Slide transition effect", "ml-slider" ),
                                                            'options' => array(
                                                                'random'             => array( 'class' => 'option coin nivo' , 'label' => __( "Random", "ml-slider" ) ),
                                                                'swirl'              => array( 'class' => 'option coin', 'label' => __( "Swirl", "ml-slider" ) ),
                                                                'rain'               => array( 'class' => 'option coin', 'label' => __( "Rain", "ml-slider" ) ),
                                                                'straight'           => array( 'class' => 'option coin', 'label' => __( "Straight", "ml-slider" ) ),
                                                                'sliceDown'          => array( 'class' => 'option nivo', 'label' => __( "Slide Down", "ml-slider" ) ),
                                                                'sliceUp'            => array( 'class' => 'option nivo', 'label' => __( "Slice Up", "ml-slider" ) ),
                                                                'sliceUpLeft'        => array( 'class' => 'option nivo', 'label' => __( "Slide Up Left", "ml-slider" ) ),
                                                                'sliceUpDown'        => array( 'class' => 'option nivo', 'label' => __( "Slice Up Down", "ml-slider" ) ),
                                                                'slideUpDownLeft'    => array( 'class' => 'option nivo', 'label' => __( "Slide Up Down Left", "ml-slider" ) ),
                                                                'fold'               => array( 'class' => 'option nivo', 'label' => __( "Fold", "ml-slider" ) ),
                                                                'fade'               => array( 'class' => 'option nivo flex responsive', 'label' => __( "Fade", "ml-slider" ) ),
                                                                'slideInRight'       => array( 'class' => 'option nivo', 'label' => __( "Slide In Right", "ml-slider" ) ),
                                                                'slideInLeft'        => array( 'class' => 'option nivo', 'label' => __( "Slide In Left", "ml-slider" ) ),
                                                                'boxRandom'          => array( 'class' => 'option nivo', 'label' => __( "Box Random", "ml-slider" ) ),
                                                                'boxRain'            => array( 'class' => 'option nivo', 'label' => __( "Box Rain", "ml-slider" ) ),
                                                                'boxRainReverse'     => array( 'class' => 'option nivo', 'label' => __( "Box Rain Reverse", "ml-slider" ) ),
                                                                'boxRainGrowReverse' => array( 'class' => 'option nivo', 'label' => __( "Box Rain Grow Reverse", "ml-slider" ) ),
                                                                'slide'              => array( 'class' => 'option flex', 'label' => __( "Slide", "ml-slider" ) )
                                                            ),
                                                        ),
                                                        'theme' => array(
                                                            'priority' => 40,
                                                            'type' => 'theme',
                                                            'value' => $this->slider->get_setting( 'theme' ),
                                                            'label' => __( "Theme", "ml-slider" ),
                                                            'class' => 'effect coin flex responsive nivo',
                                                            'helptext' => __( "Slideshow theme", "ml-slider" ),
                                                            'options' => array(
                                                                'default' => array( 'class' => 'option nivo flex coin responsive' , 'label' => __( "Default", "ml-slider" ) ),
                                                                'dark'    => array( 'class' => 'option nivo', 'label' => __( "Dark (Nivo)", "ml-slider" ) ),
                                                                'light'   => array( 'class' => 'option nivo', 'label' => __( "Light (Nivo)", "ml-slider" ) ),
                                                                'bar'     => array( 'class' => 'option nivo', 'label' => __( "Bar (Nivo)", "ml-slider" ) ),
                                                            ),
                                                        ),
                                                        'links' => array(
                                                            'priority' => 50,
                                                            'type' => 'checkbox',
                                                            'label' => __( "Arrows", "ml-slider" ),
                                                            'class' => 'option coin flex nivo responsive',
                                                            'checked' => $this->slider->get_setting( 'links' ) == 'true' ? 'checked' : '',
                                                            'helptext' => __( "Show the previous/next arrows", "ml-slider" )
                                                        ),
                                                        'navigation' => array(
                                                            'priority' => 60,
                                                            'type' => 'navigation',
                                                            'label' => __( "Navigation", "ml-slider" ),
                                                            'class' => 'option coin flex nivo responsive',
                                                            'value' => $this->slider->get_setting( 'navigation' ),
                                                            'helptext' => __( "Show the slide navigation bullets", "ml-slider" ),
                                                            'options' => array(
                                                                'false'      => array( 'label' => __( "Hidden", "ml-slider" ) ),
                                                                'true'       => array( 'label' => __( "Dots", "ml-slider" ) ),
                                                            )
                                                        ),
                                                    );

                                                    if ( $this->get_view() == 'dropdown' ) {
                                                        $aFields['title'] = array(
                                                            'type' => 'title',
                                                            'priority' => 5,
                                                            'class' => 'option flex nivo responsive coin',
                                                            'value' => get_the_title($this->slider->id),
                                                            'label' => __( "Title", "ml-slider" ),
                                                            'helptext' => __( "Slideshow title", "ml-slider" )
                                                        );
                                                    }

                                                    $aFields = apply_filters( 'metaslider_basic_settings', $aFields, $this->slider );

                                                    echo $this->build_settings_rows( $aFields );
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="ms-postbox ms-toggle closed" id="metaslider_advanced_settings">
                                    <div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span><?php _e( "Advanced Settings", "ml-slider" ) ?></span></h3>
                                    <div class="inside">
                                        <table>
                                            <tbody>
                                                <?php
                                                    $aFields = array(
                                                        'fullWidth' => array(
                                                            'priority' => 5,
                                                            'type' => 'checkbox',
                                                            'label' => __( "Stretch", "ml-slider" ),
                                                            'class' => 'option flex nivo responsive',
                                                            'after' => __( "100% wide output", "ml-slider" ),
                                                            'checked' => $this->slider->get_setting( 'fullWidth' ) == 'true' ? 'checked' : '',
                                                            'helptext' => __( "Stretch the slideshow output to fill it's parent container", "ml-slider" )
                                                        ),
                                                        'center' => array(
                                                            'priority' => 10,
                                                            'type' => 'checkbox',
                                                            'label' => __( "Center align", "ml-slider" ),
                                                            'class' => 'option coin flex nivo responsive',
                                                            'checked' => $this->slider->get_setting( 'center' ) == 'true' ? 'checked' : '',
                                                            'helptext' => __( "Center align the slideshow", "ml-slider" )
                                                        ),
                                                        'autoPlay' => array(
                                                            'priority' => 20,
                                                            'type' => 'checkbox',
                                                            'label' => __( "Auto play", "ml-slider" ),
                                                            'class' => 'option flex nivo responsive',
                                                            'checked' => $this->slider->get_setting( 'autoPlay' ) == 'true' ? 'checked' : '',
                                                            'helptext' => __( "Transition between slides automatically", "ml-slider" )
                                                        ),
                                                        'smartCrop' => array(
                                                            'priority' => 30,
                                                            'type' => 'select',
                                                            'label' => __( "Image Crop", "ml-slider" ),
                                                            'class' => 'option coin flex nivo responsive',
                                                            'value' => $this->slider->get_setting( 'smartCrop' ),
                                                            'options' => array(
                                                                'true' => array( 'label' => __( "Smart Crop", "ml-slider" ), 'class' => '' ),
                                                                'false' => array( 'label' => __( "Standard", "ml-slider" ), 'class' => '' ),
                                                                'disabled' => array( 'label' => __( "Disabled", "ml-slider" ), 'class' => '' ),
                                                                'disabled_pad' => array( 'label' => __( "Disabled (Smart Pad)", "ml-slider" ), 'class' => 'option flex' ),
                                                            ),
                                                            'helptext' => __( "Smart Crop ensures your responsive slides are cropped to a ratio that results in a consistent slideshow size", "ml-slider" )
                                                        ),
                                                        'carouselMode' => array(
                                                            'priority' => 40,
                                                            'type' => 'checkbox',
                                                            'label' => __( "Carousel mode", "ml-slider" ),
                                                            'class' => 'option flex showNextWhenChecked',
                                                            'checked' => $this->slider->get_setting( 'carouselMode' ) == 'true' ? 'checked' : '',
                                                            'helptext' => __( "Display multiple slides at once. Slideshow output will be 100% wide.", "ml-slider" )
                                                        ),
                                                        'carouselMargin' => array(
                                                            'priority' => 45,
                                                            'min' => 0,
                                                            'max' => 9999,
                                                            'step' => 1,
                                                            'type' => 'number',
                                                            'label' => __( "Carousel margin", "ml-slider" ),
                                                            'class' => 'option flex',
                                                            'value' => $this->slider->get_setting( 'carouselMargin' ),
                                                            'helptext' => __( "Pixel margin between slides in carousel.", "ml-slider" ),
                                                            'after' => __( "px", "ml-slider" )
                                                        ),
                                                        'random' => array(
                                                            'priority' => 50,
                                                            'type' => 'checkbox',
                                                            'label' => __( "Random", "ml-slider" ),
                                                            'class' => 'option coin flex nivo responsive',
                                                            'checked' => $this->slider->get_setting( 'random' ) == 'true' ? 'checked' : '',
                                                            'helptext' => __( "Randomise the order of the slides", "ml-slider" )
                                                        ),
                                                        'hoverPause' => array(
                                                            'priority' => 60,
                                                            'type' => 'checkbox',
                                                            'label' => __( "Hover pause", "ml-slider" ),
                                                            'class' => 'option coin flex nivo responsive',
                                                            'checked' => $this->slider->get_setting( 'hoverPause' ) == 'true' ? 'checked' : '',
                                                            'helptext' => __( "Pause the slideshow when hovering over slider, then resume when no longer hovering.", "ml-slider" )
                                                        ),
                                                        'reverse' => array(
                                                            'priority' => 70,
                                                            'type' => 'checkbox',
                                                            'label' => __( "Reverse", "ml-slider" ),
                                                            'class' => 'option flex',
                                                            'checked' => $this->slider->get_setting( 'reverse' ) == 'true' ? 'checked' : '',
                                                            'helptext' => __( "Reverse the animation direction", "ml-slider" )
                                                        ),
                                                        'delay' => array(
                                                            'priority' => 80,
                                                            'type' => 'number',
                                                            'size' => 3,
                                                            'min' => 500,
                                                            'max' => 10000,
                                                            'step' => 100,
                                                            'value' => $this->slider->get_setting( 'delay' ),
                                                            'label' => __( "Slide delay", "ml-slider" ),
                                                            'class' => 'option coin flex responsive nivo',
                                                            'helptext' => __( "How long to display each slide, in milliseconds", "ml-slider" ),
                                                            'after' => __( "ms", "ml-slider" )
                                                        ),
                                                        'animationSpeed' => array(
                                                            'priority' => 90,
                                                            'type' => 'number',
                                                            'size' => 3,
                                                            'min' => 0,
                                                            'max' => 2000,
                                                            'step' => 100,
                                                            'value' => $this->slider->get_setting( 'animationSpeed' ),
                                                            'label' => __( "Animation speed", "ml-slider" ),
                                                            'class' => 'option flex responsive nivo',
                                                            'helptext' => __( "Set the speed of animations, in milliseconds", "ml-slider" ),
                                                            'after' => __( "ms", "ml-slider" )
                                                        ),
                                                        'slices' => array(
                                                            'priority' => 100,
                                                            'type' => 'number',
                                                            'size' => 3,
                                                            'min' => 0,
                                                            'max' => 20,
                                                            'step' => 1,
                                                            'value' => $this->slider->get_setting( 'slices' ),
                                                            'label' => __( "Number of slices", "ml-slider" ),
                                                            'class' => 'option nivo',
                                                            'helptext' => __( "Number of slices", "ml-slider" ),
                                                            'after' => __( "ms", "ml-slider" )
                                                        ),
                                                        'spw' => array(
                                                            'priority' => 110,
                                                            'type' => 'number',
                                                            'size' => 3,
                                                            'min' => 0,
                                                            'max' => 20,
                                                            'step' => 1,
                                                            'value' => $this->slider->get_setting( 'spw' ),
                                                            'label' => __( "Number of squares", "ml-slider" ) . " (" . __( "Width", "ml-slider" ) . ")",
                                                            'class' => 'option nivo',
                                                            'helptext' => __( "Number of squares", "ml-slider" ),
                                                            'after' => ''
                                                        ),
                                                        'sph' => array(
                                                            'priority' => 120,
                                                            'type' => 'number',
                                                            'size' => 3,
                                                            'min' => 0,
                                                            'max' => 20,
                                                            'step' => 1,
                                                            'value' => $this->slider->get_setting( 'sph' ),
                                                            'label' => __( "Number of squares", "ml-slider" ) . " (" . __( "Height", "ml-slider" ) . ")",
                                                            'class' => 'option nivo',
                                                            'helptext' => __( "Number of squares", "ml-slider" ),
                                                            'after' => ''
                                                        ),
                                                        'direction' => array(
                                                            'priority' => 130,
                                                            'type' => 'select',
                                                            'label' => __( "Slide direction", "ml-slider" ),
                                                            'class' => 'option flex',
                                                            'helptext' => __( "Select the sliding direction", "ml-slider" ),
                                                            'value' => $this->slider->get_setting( 'direction' ),
                                                            'options' => array(
                                                                'horizontal' => array( 'label' => __( "Horizontal", "ml-slider" ), 'class' => '' ),
                                                                'vertical' => array( 'label' => __( "Vertical", "ml-slider" ), 'class' => '' ),
                                                            )
                                                        ),
                                                        'easing' => array(
                                                            'priority' => 140,
                                                            'type' => 'select',
                                                            'label' => __( "Easing", "ml-slider" ),
                                                            'class' => 'option flex',
                                                            'helptext' => __( "Animation easing effect", "ml-slider" ),
                                                            'value' => $this->slider->get_setting( 'easing' ),
                                                            'options' => $this->get_easing_options()
                                                        ),
                                                        'prevText' => array(
                                                            'priority' => 150,
                                                            'type' => 'text',
                                                            'label' => __( "Previous text", "ml-slider" ),
                                                            'class' => 'option coin flex responsive nivo',
                                                            'helptext' => __( "Set the text for the 'previous' direction item", "ml-slider" ),
                                                            'value' => $this->slider->get_setting( 'prevText' ) == 'false' ? '' : $this->slider->get_setting( 'prevText' )
                                                        ),
                                                        'nextText' => array(
                                                            'priority' => 160,
                                                            'type' => 'text',
                                                            'label' => __( "Next text", "ml-slider" ),
                                                            'class' => 'option coin flex responsive nivo',
                                                            'helptext' => __( "Set the text for the 'next' direction item", "ml-slider" ),
                                                            'value' => $this->slider->get_setting( 'nextText' ) == 'false' ? '' : $this->slider->get_setting( 'nextText' )
                                                        ),
                                                        'sDelay' => array(
                                                            'priority' => 170,
                                                            'type' => 'number',
                                                            'size' => 3,
                                                            'min' => 0,
                                                            'max' => 500,
                                                            'step' => 10,
                                                            'value' => $this->slider->get_setting( 'sDelay' ),
                                                            'label' => __( "Square delay", "ml-slider" ),
                                                            'class' => 'option coin',
                                                            'helptext' => __( "Delay between squares in ms", "ml-slider" ),
                                                            'after' => __( "ms", "ml-slider" )
                                                        ),
                                                        'opacity' => array(
                                                            'priority' => 180,
                                                            'type' => 'number',
                                                            'size' => 3,
                                                            'min' => 0,
                                                            'max' => 1,
                                                            'step' => 0.1,
                                                            'value' => $this->slider->get_setting( 'opacity' ),
                                                            'label' => __( "Opacity", "ml-slider" ),
                                                            'class' => 'option coin',
                                                            'helptext' => __( "Opacity of title and navigation", "ml-slider" ),
                                                            'after' => ''
                                                        ),
                                                        'titleSpeed' => array(
                                                            'priority' => 190,
                                                            'type' => 'number',
                                                            'size' => 3,
                                                            'min' => 0,
                                                            'max' => 10000,
                                                            'step' => 100,
                                                            'value' => $this->slider->get_setting( 'titleSpeed' ),
                                                            'label' => __( "Caption speed", "ml-slider" ),
                                                            'class' => 'option coin',
                                                            'helptext' => __( "Set the fade in speed of the caption", "ml-slider" ),
                                                            'after' => __( "ms", "ml-slider" )
                                                        ),
                                                        'developerOptions' => array(
                                                            'priority' => 195,
                                                            'type' => 'divider',
                                                            'class' => 'option coin flex responsive nivo',
                                                            'value' => __( "Developer options", "ml-slider" )
                                                        ),
                                                        'cssClass' => array(
                                                            'priority' => 200,
                                                            'type' => 'text',
                                                            'label' => __( "CSS classes", "ml-slider" ),
                                                            'class' => 'option coin flex responsive nivo',
                                                            'helptext' => __( "Specify any custom CSS Classes you would like to be added to the slider wrapper", "ml-slider" ),
                                                            'value' => $this->slider->get_setting( 'cssClass' ) == 'false' ? '' : $this->slider->get_setting( 'cssClass' )
                                                        ),
                                                        'printCss' => array(
                                                            'priority' => 210,
                                                            'type' => 'checkbox',
                                                            'label' => __( "Print CSS", "ml-slider" ),
                                                            'class' => 'option coin flex responsive nivo useWithCaution',
                                                            'checked' => $this->slider->get_setting( 'printCss' ) == 'true' ? 'checked' : '',
                                                            'helptext' => __( "Uncheck this is you would like to include your own CSS", "ml-slider" )
                                                        ),
                                                        'printJs' => array(
                                                            'priority' => 220,
                                                            'type' => 'checkbox',
                                                            'label' => __( "Print JS", "ml-slider" ),
                                                            'class' => 'option coin flex responsive nivo useWithCaution',
                                                            'checked' => $this->slider->get_setting( 'printJs' ) == 'true' ? 'checked' : '',
                                                            'helptext' => __( "Uncheck this is you would like to include your own Javascript", "ml-slider" )
                                                        ),
                                                        'noConflict' => array(
                                                            'priority' => 230,
                                                            'type' => 'checkbox',
                                                            'label' => __( "No conflict mode", "ml-slider" ),
                                                            'class' => 'option flex',
                                                            'checked' => $this->slider->get_setting( 'noConflict' ) == 'true' ? 'checked' : '',
                                                            'helptext' => __( "Delay adding the flexslider class to the slideshow", "ml-slider" )
                                                        ),
                                                    );

                                                    $aFields = apply_filters( 'metaslider_advanced_settings', $aFields, $this->slider );

                                                    echo $this->build_settings_rows( $aFields );
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="ms-postbox shortcode ms-toggle" id="metaslider_usage">
                                    <div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span><?php _e( "Usage", "ml-slider" ) ?></span></h3>
                                    <div class="inside">
                                        <ul class='tabs'>
                                            <li rel='tab-1' class='selected'><?php _e( "Shortcode", "ml-slider" ) ?></li>
                                            <li rel='tab-2'><?php _e( "Template Include", "ml-slider" ) ?></li>
                                        </ul>
                                        <div class='tabs-content'>
                                            <div class='tab tab-1'>
                                            <p><?php _e( "Copy & paste the shortcode directly into any WordPress post or page.", "ml-slider" ); ?></p>
                                            <input readonly='readonly' type='text' value='[metaslider id=<?php echo $this->slider->id ?>]' /></div>
                                            <div class='tab tab-2' style='display: none'>
                                            <p><?php _e( "Copy & paste this code into a template file to include the slideshow within your theme.", "ml-slider" ); ?></p>
                                            <textarea readonly='readonly'>&lt;?php &#13;&#10;    echo do_shortcode("[metaslider id=<?php echo $this->slider->id ?>]"); &#13;&#10;?></textarea></div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ( ! is_plugin_active('ml-slider-pro/ml-slider-pro.php') ) : ?>

                                <div class="ms-postbox social" id="metaslider_social">
                                    <div class="inside">
                                        <ul class='info'>
                                            <li style='width: 33%;'>
                                                <a href="https://twitter.com/share" class="twitter-share-button" data-url="https://www.metaslider.com" data-text="Check out Meta Slider, an easy to use slideshow plugin for WordPress" data-hashtags="metaslider, wordpress, slideshow">Tweet</a>
                                                <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
                                            </li>
                                            <li style='width: 34%;'>
                                                <div class="g-plusone" data-size="medium" data-href="https://www.metaslider.com"></div>
                                                <script type="text/javascript">
                                                  (function() {
                                                    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                                                    po.src = 'https://apis.google.com/js/plusone.js';
                                                    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
                                                  })();
                                                </script>
                                            </li>
                                            <li style='width: 33%;'>
                                                <iframe style='border:none; overflow:hidden; width:80px; height:21px;' src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fwpmetaslider&amp;width=120&amp;layout=button_count&amp;action=like&amp;show_faces=true&amp;share=false&amp;height=21&amp;appId=156668027835524" scrolling="no" frameborder="0" allowTransparency="true"></iframe>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <?php endif; ?>

                                <?php $url = wp_nonce_url( admin_url( "admin-post.php?action=metaslider_delete_slider&amp;slider_id={$this->slider->id}" ), "metaslider_delete_slider" ); ?>

                                <a class='delete-slider alignright button-secondary' href='<?php echo $url ?>'><?php _e( "Delete Slider", "ml-slider" ) ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }


    /**
     * Append the 'Add Slider' button to selected admin pages
     */
    public function insert_metaslider_button( $context ) {

        $capability = apply_filters( 'metaslider_capability', 'edit_others_posts' );

        if ( ! current_user_can( $capability ) ) {
            return $context;
        }

        global $pagenow;

        if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) {
            $context .= '<a href="#TB_inline?&inlineId=choose-meta-slider" class="thickbox button" title="' .
                __( "Select slideshow to insert into post", "ml-slider" ) .
                '"><span class="wp-media-buttons-icon" style="background: url(' . METASLIDER_ASSETS_URL .
                '/metaslider/matchalabs.png); background-repeat: no-repeat; background-position: left bottom;"></span> ' .
                __( "Add slider", "ml-slider" ) . '</a>';
        }

        return $context;

    }


    /**
     * Append the 'Choose Meta Slider' thickbox content to the bottom of selected admin pages
     */
    public function admin_footer() {

        global $pagenow;

        // Only run in post/page creation and edit screens
        if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) {
            $sliders = $this->all_meta_sliders( 'title' );
            ?>

            <script type="text/javascript">
                jQuery(document).ready(function() {
                  jQuery('#insertMetaSlider').on('click', function() {
                    var id = jQuery('#metaslider-select option:selected').val();
                    window.send_to_editor('[metaslider id=' + id + ']');
                    tb_remove();
                  })
                });
            </script>

            <div id="choose-meta-slider" style="display: none;">
                <div class="wrap">
                    <?php
                        if ( count( $sliders ) ) {
                            echo "<h3 style='margin-bottom: 20px;'>" . __( "Insert Meta Slider", "ml-slider" ) . "</h3>";
                            echo "<select id='metaslider-select'>";
                            echo "<option disabled=disabled>" . __( "Choose slideshow", "ml-slider" ) . "</option>";
                            foreach ( $sliders as $slider ) {
                                echo "<option value='{$slider['id']}'>{$slider['title']}</option>";
                            }
                            echo "</select>";
                            echo "<button class='button primary' id='insertMetaSlider'>" . __( "Insert slideshow", "ml-slider" ) . "</button>";
                        } else {
                            _e( "No slideshows found", "ml-slider" );
                        }
                    ?>
                </div>
            </div>

            <?php
        }
    }


    /**
     * Return the meta slider pro upgrade iFrame
     */
    public function upgrade_to_pro_tab() {

        if ( function_exists( 'is_plugin_active' ) && ! is_plugin_active( 'ml-slider-pro/ml-slider-pro.php' ) ) {
            return wp_iframe( array( $this, 'upgrade_to_pro_iframe' ) );
        }

    }


    /**
     * Media Manager iframe HTML
     */
    public function upgrade_to_pro_iframe() {

        wp_enqueue_style( 'metaslider-admin-styles', METASLIDER_ASSETS_URL . 'metaslider/admin.css', false, METASLIDER_VERSION );
        wp_enqueue_script( 'google-font-api', 'http://fonts.googleapis.com/css?family=PT+Sans:400,700' );

        $link = apply_filters( 'metaslider_hoplink', 'https://www.metaslider.com/upgrade/' );
        $link .= '?utm_source=lite&amp;utm_medium=more-slide-types&amp;utm_campaign=pro';

        echo implode("", array(
            "<div class='metaslider_pro'>",
                "<p>Get the Pro Addon pack to add support for: <b>Post Feed</b> Slides, <b>YouTube</b> Slides, <b>HTML</b> Slides & <b>Vimeo</b> Slides</p>",
                "<a class='probutton' href='{$link}' target='_blank'>Get <span class='logo'><strong>Meta</strong>Slider</span><span class='super'>Pro</span></a>",
                "<span class='subtext'>Opens in a new window</span>",
            "</div>"
        ));

    }

    /**
     * Add settings link on plugin page
     */
    public function upgrade_to_pro_link( $links ) {

        if ( function_exists( 'is_plugin_active' ) && ! is_plugin_active( 'ml-slider-pro/ml-slider-pro.php' ) ) {
            $links[] = '<a href="https://www.metaslider.com/upgrade/" target="_blank">' . __( "Go Pro", "ml-slider" ) . '</a>';
        }

        return $links;

    }


    /**
     * Upgrade CTA.
     */
    public function upgrade_to_pro_cta() {

        if ( function_exists( 'is_plugin_active' ) && ! is_plugin_active( 'ml-slider-pro/ml-slider-pro.php' ) ) {

            $upgrade_link = esc_url( add_query_arg(
                array(
                    'utm_source' => 'lite',
                    'utm_medium' => 'nag',
                    'utm_campaign' => 'pro'
                ), 'https://www.metaslider.com/upgrade/' ) );

            $link = apply_filters( 'metaslider_hoplink', $upgrade_link );

            $text = "Meta Slider v" . METASLIDER_VERSION . " - " . __( 'Upgrade to Pro $39', "ml-slider" );

            echo "<div style='display: none;' id='screen-options-link-wrap'><a target='_blank' class='show-settings dashicons-before dashicons-performance' href='{$link}'>{$text}</a></div>";

        }

    }


    /**
     * Add a 'property=stylesheet' attribute to the Meta Slider CSS links for HTML5 validation
     *
     * @since 3.3.4
     * @param string $tag
     * @param string $handle
     */
    public function add_property_attribute_to_stylesheet_links( $tag, $handle ) {

        if ( strpos( $handle, 'metaslider' ) !== FALSE && strpos( $tag, "property='" ) === FALSE ) {
            // we're only filtering tags with metaslider in the handle, and links which don't already have a property attribute
            $tag = str_replace( "/>", "property='stylesheet' />", $tag );
        }

        return $tag;

    }

}

endif;

add_action( 'plugins_loaded', array( 'MetaSliderPlugin', 'init' ), 10 );
