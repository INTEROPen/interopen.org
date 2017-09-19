<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

define('MAXBUTTONS_VERSION_KEY', 'maxbuttons_version');

class maxButtonsPlugin
{

	protected $installed_version = 0;
	protected $plugin_name; 
	protected $plugin_url;
	protected $plugin_path;
	protected $debug_mode = false;
	protected $footer = array();

	protected static $notices = array(); 
	
	protected $mainClasses = array(); 
	
	protected static $instance;
	
	/* Class constructor 
	   Add hooks and actions used by this plugin. Sets plugin environment information
	*/
	function __construct()
	{
		maxUtils::timeInit(); // benchmark timer init. 

		$this->plugin_url =  self::get_plugin_url(); //plugins_url() . '/' . $this->plugin_name;
		$this->plugin_path = self::get_plugin_path(); //plugin_dir_path($rootfile); 
		$this->plugin_name = trim(basename($this->plugin_path), '/');
		
		$this->installed_version = get_option(MAXBUTTONS_VERSION_KEY); 
 
 		if ( defined('MAXBUTTONS_DEBUG') && MAXBUTTONS_DEBUG)
 			$this->debug_mode = true;
 
		add_action('plugins_loaded', array($this, 'load_textdomain'));

		add_filter('widget_text', 'do_shortcode');
		add_shortcode('maxbutton', array($this, 'shortcode')); 

		add_action("mb-footer", array($this, 'do_footer'),10,3); 
		add_action("wp_footer", array($this, "footer")); 
		
		// Media buttons
		add_action('media_buttons', array($this,'media_button'));

		add_filter('plugin_action_links', array($this, "plugin_action_links"), 10, 2);
		add_filter('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 2);
		
		if( is_admin())
		{		
			add_action('admin_enqueue_scripts', array($this,'add_admin_styles'));
			add_action('admin_enqueue_scripts', array($this,'add_admin_scripts'));	
			add_action('admin_enqueue_scripts', array(maxUtils::namespaceit('maxUtils'), 'fixFAConflict'),999);
					
			add_action('admin_init', array($this,'register_settings' ));
	
			add_action('admin_init', array($this, 'do_review_notice')); // Ask for review
			add_action('admin_init', array(maxUtils::namespaceit('maxInstall'),'check_database')); 
					
			add_action('admin_menu', array($this, 'admin_menu'));
			add_action('admin_footer', array($this, "footer"));
			add_filter("admin_footer_text",array($this, "admin_footer_text"));

			// errors in user space. No internal error but user output friendly issues
			add_action("mb/editor/display_notices", array($this,"display_notices"), 99);
			add_action("mb/collection/display_notices", array($this,"display_notices"), 99);
			add_action('mb/header/display_notices', array($this, 'display_notices'), 99);
						
			add_action("wp_ajax_getAjaxButtons", array(maxUtils::namespaceit('maxButtonsAdmin'), 'getAjaxButtons'));
			add_action("wp_ajax_set_review_notice_status", array($this, "setReviewNoticeStatus")); 

			// Collection AJAX
			add_action("wp_ajax_collection-edit", array(maxUtils::namespaceit("maxCollections"), "ajax_save")); 			
			add_action('wp_ajax_mbpro_collection_block', array(maxUtils::namespaceit("maxCollections"), "ajax_action"));  // all block level ajax stuff - for logged in users - backend 	
			add_action('wp_ajax_mb_button_action', array(maxUtils::namespaceit('maxButtons'), "ajax_action")); 
			
			add_action('wp_ajax_maxajax', array(maxUtils::namespaceit('maxUtils'), 'ajax_action')); 
		}
		// FRONT AJAX
		add_action('wp_ajax_mbpro_collection_block_front', array(maxUtils::namespaceit("maxCollections"), "ajax_action_front"));  	 // front end for all users
		add_action('wp_ajax_nopriv_mbpro_collection_block_front', array(maxUtils::namespaceit("maxCollections"), "ajax_action_front"));  
					
		// front scripts
		add_action('wp_enqueue_scripts', array($this, 'front_scripts'));
		add_action('wp_enqueue_scripts', array(maxUtils::namespaceit('maxUtils'), 'fixFAConflict'),999);
		
		$this->setMainClasses(); // struct for override functionality
 		
 		// The second the blocks are being loaded, check dbase integrity 
 		add_action("mb_blockclassesloaded", array($this, "check_database")); 
 
 		// setup page hooks and shortcode
		add_shortcode('maxcollection', array($this, 'collection_shortcode'));
		if (! is_admin()) 
			$hook_bool = maxCollections::setupHooks();  // setup the hooks to insert collections

 		self::$instance = $this;
 		maxIntegrations::init(); // fire the integrations. 
	}
	
	public static function getInstance()
	{
		return self::$instance;
	}
 
	public function setMainClasses()
	{
		$classes = array(
			"button" => "maxButton",
			"buttons" => "maxButtons",
			"block" => "maxBlock", 
			"admin" => "maxButtonsAdmin", 
			"install" => "maxInstall", 
			"groups" => "maxGroups",
			"collections" => "maxCollections", 
			"collection" => "maxCollection",
			"pack" => "maxPack",

		); 
		
		$this->mainClasses = $classes;		
	}
	
	// from block loader action. Checks if all parts of the table are there, or panic if not.
	public function check_database($blocks)
	{
		maxUtils::addTime("Check database");
			
		$sql = "SELECT id,name,status,cache, created "; 
		foreach ($blocks as $block => $class) 
		{
			$sql .= ", $block"; 
		} 
		$sql .= " from " . maxUtils::get_table_name() . " limit 1"; 
 
		global $wpdb; 
		$wpdb->hide_errors();
		$result = $wpdb->get_results($sql); 
		 
  
		// check this query for errors. If there is an error, one or more database fields are missing. Fix that. 
		if (isset($wpdb->last_error) && $wpdb->last_error != '') 
		{
 
		 	$install = $this->getClass("install"); 
			$install::create_database_table();
			$install::migrate();
		}
	 
		
		maxUtils::addTime("End check database");
	}
	
	public function getClass($class)
	{

		if (isset($this->mainClasses[$class])) 
		{
			$load_class = maxUtils::namespaceit($this->mainClasses[$class]); 
			if (method_exists($load_class,'getInstance'))
			{
				return $load_class::getInstance(); 
			}
			return new $load_class;
		}
	}
	 
	/* Load the plugin textdomain */
	public function load_textdomain()
	{
		// see: http://geertdedeckere.be/article/loading-wordpress-language-files-the-right-way 
		$domain = 'maxbuttons';
		// The "plugin_locale" filter is also used in load_plugin_textdomain()
		$locale = apply_filters('plugin_locale', get_locale(), $domain);

		load_textdomain($domain, WP_LANG_DIR.'/maxbuttons/'.$domain.'-'.$locale.'.mo');	
		$res = load_plugin_textdomain('maxbuttons', false, $this->plugin_name . '/languages/');
 
 	}
 	
 	
 	/** WP Settings framework. Registers settings used on maxbuttons-settings.php page */
 	public function register_settings()
 	{
 		register_setting( 'maxbuttons_settings', 'maxbuttons_user_level' );
 		register_setting( 'maxbuttons_settings', 'maxbuttons_noshowtinymce' );
 		register_setting( 'maxbuttons_settings', 'maxbuttons_minify' ); 
 		register_setting( 'maxbuttons_settings', 'maxbuttons_hidedescription' ); 
 		register_setting( 'maxbuttons_settings', 'maxbuttons_forcefa') ;
 		register_setting( 'maxbuttons_settings', 'maxbuttons_borderbox'); 
	}
	
	protected function checkbox_option($options) 
	{
		if (! isset($options["maxbuttons_minify"])) 
			$options["maxbuttons_minify"] = 0; 
		
		return $options;
	
	}

	/** Returns the full path of the plugin installation directory */
 	public static function get_plugin_path()
 	{
 		return plugin_dir_path(MAXBUTTONS_ROOT_FILE); 
 	}
 	
 	/** Returns the full URL of the plugin installation path */ 
 	public static function get_plugin_url()
 	{
 		$url = plugin_dir_url(MAXBUTTONS_ROOT_FILE);
 		return $url;
 	}
 	
 	/** Returns the current installed version */
 	public function get_installed_version()
 	{
 		return $this->installed_version; 
 	}

	/** Installs and adds the main menu and the submenu items */
	function admin_menu() {
		$maxbuttons_capabilities = get_option('maxbuttons_user_level');
		if(!$maxbuttons_capabilities) {
			$maxbuttons_capabilities = 'manage_options';
			settings_fields( 'maxbuttons_settings' );
			update_option('maxbuttons_user_level', $maxbuttons_capabilities);
		}
		$admin_pages = array();

		$page_title = __('MaxButtons: Buttons', 'maxbuttons');
		$menu_title = __('MaxButtons', 'maxbuttons');
		$capability = $maxbuttons_capabilities;
		$admin_capability = 'manage_options';
		$menu_slug = 'maxbuttons-controller';
		$function =  array($this, 'load_admin_page'); 
		$icon_url = $this->plugin_url . 'images/mb-peach-icon.png';
		$submenu_function = array($this, 'load_admin_page'); 
		
		add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, 81);
	
		// We add this submenu page with the same slug as the parent to ensure we don't get duplicates
		$sub_menu_title = __('Buttons', 'maxbuttons');
		$admin_pages[] = add_submenu_page($menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, $function);
	
		// Now add the submenu page for the Add New page
		$submenu_page_title = __('MaxButtons: Add/Edit Button', 'maxbuttons');
		$submenu_title = __('Add New', 'maxbuttons');
		$submenu_slug = 'maxbuttons-controller&action=edit';
		//$submenu_function = 'maxbuttons_button';
		$admin_pages[] = add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);

		// Now add the submenu page for the Export page
		$submenu_page_title = __('MaxButtons: Social Share', 'maxbuttons');
		$submenu_title = __('Social Share', 'maxbuttons');
		$submenu_slug = 'maxbuttons-collections';
		$admin_pages[] = add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);
			
		// Now add the submenu page for the Go Pro page
		$submenu_page_title = __('MaxButtons: Upgrade to Pro', 'maxbuttons');
		$submenu_title = __('Upgrade to Pro', 'maxbuttons');
		$submenu_slug = 'maxbuttons-pro';
		//$submenu_function = 'maxbuttons_pro';
		$admin_pages[] = add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);

		// Now add the submenu page for the Settings page
		$submenu_page_title = __('MaxButtons: Settings', 'maxbuttons');
		$submenu_title = __('Settings', 'maxbuttons');
		$submenu_slug = 'maxbuttons-settings';
		//$submenu_function = 'maxbuttons_settings';
		$admin_pages[] = add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $admin_capability, $submenu_slug, $submenu_function);

		// Now add the submenu page for the Support page
		$submenu_page_title = __('MaxButtons: Support', 'maxbuttons');
		$submenu_title = __('Support', 'maxbuttons');
		$submenu_slug = 'maxbuttons-support';
		//$submenu_function = 'maxbuttons_support';
		$admin_pages[] = add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $admin_capability, $submenu_slug, $submenu_function);
 
	}	
 	
	function load_admin_page($page)
	{
		$page = sanitize_text_field($_GET["page"]);
		 
		switch($page) 
		{
			case "maxbuttons-button": 
				$pagepath = "includes/maxbuttons-button.php"; 
			break;
			case "maxbuttons-support": 
				$pagepath = "includes/maxbuttons-support.php";
			break;
			case "maxbuttons-settings": 
				$pagepath = "includes/maxbuttons-settings.php"; 
			break;
			case "maxbuttons-pro": 
				$pagepath = "includes/maxbuttons-pro.php"; 
			break;
			case "maxbuttons-collections": 
				$pagepath = "includes/maxbuttons-collections.php"; 
			break;			
			default:
				$pagepath = "includes/maxbuttons-controller.php"; 
			break;
		}
		$pagepath = $this->plugin_path . $pagepath; 
		
		include(apply_filters("mb-load-admin-page-$page", $pagepath)); 
	}


	function add_admin_styles($hook) {	
		// only hook in maxbuttons realm. 
		if ( strpos($hook,'maxbuttons') === false && $hook != 'post.php' && $hook != 'post-new.php' )
		{
			if (! isset($_GET['fl_builder'])) // exception for beaver builder
			return;
 		}
			
				 		
 		$fa_url = apply_filters("mb_fa_url", $this->plugin_url . 'assets/libraries/font-awesome/css/font-awesome.min.css'); 
		if ($fa_url != false && $fa_url != '')
		{				
			wp_register_style('mbpro-font-awesome', $fa_url  );
			wp_enqueue_style('mbpro-font-awesome');			
		}
		
		wp_enqueue_style('wp-color-picker'); 
		wp_enqueue_style('maxbuttons-css', $this->plugin_url . 'assets/css/style.css');		
 
	}

	/** Add Admin scripts 
	*
	* Uses WP hook for Admin scripts to add needed js.
	*/
	function add_admin_scripts($hook) {	
		// only hook in maxbuttons realm.
		if ( strpos($hook,'maxbuttons') === false ) //&& $hook != 'post.php' && $hook != 'post-new.php' 
			return;
			
		$version = MAXBUTTONS_VERSION_NUM;
		
		$js_url = trailingslashit($this->plugin_url . 'js'); 
		if (! $this->debug_mode)	
			$js_url .= 'min/'; 
	
		
		wp_enqueue_script('jquery-ui-draggable');
 
		//wp_enqueue_script('maxbuttons-colorpicker-js', $js_url .'colpick.js', array('jquery'), $version, true);

		wp_enqueue_script('maxbutton-admin', $js_url . 'maxbuttons-admin.js', array('jquery', 'jquery-ui-draggable', 'maxbuttons-tabs','maxbuttons-modal', 'wp-color-picker', 'underscore'),$version, true); 
		wp_enqueue_script('maxbutton-js-init', $js_url . 'init.js', array('maxbutton-admin','maxcollections','maxbuttons-modal'),$version, true);
		wp_enqueue_script('maxbuttons-tabs', $js_url . 'maxtabs.js', array('jquery'),$version, true); 
		wp_enqueue_script('maxbuttons-responsive', $js_url . 'responsive.js', array('maxbutton-admin'), $version, true ); 
	
		wp_register_script('maxbuttons-ajax', $js_url . 'maxajax.js', array('maxbutton-admin'), $version, true);
		wp_localize_script('maxbuttons-ajax', 'maxajax', 
							array(
								  'ajax_url' => admin_url( 'admin-ajax.php' ),
								  'ajax_action' => 'maxajax',
								  'nonce' => wp_create_nonce('maxajax'),
						 )); 
						 
		wp_enqueue_script('maxbuttons-ajax');
		
		wp_register_script('maxcollections', $js_url . 'maxcollections.js', 
				array('jquery', 'maxbutton-admin', 'jquery-ui-sortable'),$version, true );	
						
		$local = array();
		$local["ajaxurl"] = admin_url( 'admin-ajax.php' );
		$local["maxurl"] = $this->plugin_url; 		
		wp_localize_script('maxbutton-admin', 'mb_ajax', $local);  
	
		$local = array(
			"leave_page" => __("You have unsaved data, are you sure you want to leave the page?","maxbuttons"),
			'picker_title' => __('Select your buttons','maxbuttons'), 
		
		); 
 		wp_localize_script('maxcollections',  'maxcol_wp', $local);  
 		
		$this->load_modal_script();	
 		
		wp_enqueue_script('maxcollections');  
	}	
	
	/** Load the Modal Script
	*	The modal script is the generic solution for all popups within the plugin.
	*/
	public function load_modal_script() 
	{
		$version = MAXBUTTONS_VERSION_NUM;
		$js_url = trailingslashit($this->plugin_url . 'js'); 
		if (! $this->debug_mode)	
			$js_url .= 'min/'; 
			
		wp_register_script('maxbuttons-modal', $js_url . 'maxmodal.js', array('jquery','jquery-ui-draggable'), $version, true); 
 		// translations of controls and other elements that can be used in maxmodal 
 		$translations = array(
 				'yes' => __("Yes","maxbuttons"), 
 				'no' => __("No","maxbuttons"), 
 				'ok' => __("OK","maxbuttons"), 
 				'cancel' => __("Cancel","maxbuttons"), 
 		); 			
 		wp_localize_script('maxbuttons-modal', 'modaltext', $translations); 		
		wp_enqueue_script('maxbuttons-modal'); 

	}
	
	/** Load Media Buttons Script 
		
		Useful for integrations that don't implement the media button but uses the media button JS for loading the button picker
		
	*/
	public function load_media_script() 
	{
		$version = MAXBUTTONS_VERSION_NUM;

		$js_url = trailingslashit($this->plugin_url . 'js'); 
		if (! $this->debug_mode)	
			$js_url .= 'min/'; 
						
		$fa_url = apply_filters("mb_fa_url", $this->plugin_url . 'assets/libraries/font-awesome/css/font-awesome.min.css'); 
		if ($fa_url != false && $fa_url != '')
		{
			wp_register_style('mbpro-font-awesome', $fa_url);
			wp_enqueue_style('mbpro-font-awesome');	
		}
		
		wp_enqueue_script('mb-media-button', $js_url . 'maxbuttons_media_button.js', array('jquery', 'maxbuttons-modal'), $version, true); 
		$this->load_modal_script();
		
		$translations = array(
		'insert' => __('Insert Button into Editor', 'maxbuttons'), 
		'loading' => __("Loading your buttons","maxbuttons"), 
		'select' => __('Click on a button from the list below to place the button shortcode in the editor.', 'maxbuttons'), 
		'cancel' => __('Cancel', 'maxbuttons'), 
		'windowtitle' => __("Select a MaxButton","maxbuttons"), 
		'icon' => $this->plugin_url . 'images/mb-peach-32.png', 
		'ajax_url' => admin_url( 'admin-ajax.php' ), 
		'short_url_label' => __('Button URL', 'maxbuttons'),  
		'short_text_label' => __('Button Text', 'maxbuttons'),
		'short_options_explain' => __('If you want to change the URL or Text of the Button, enter the appropiate field. If you want to use the button values, just click Add to editor', 'maxbuttons'),
		'short_add_button' => __('Add to Editor', 'maxbuttons'), 
		); 
		
		wp_localize_script('mb-media-button','mbtrans', $translations); 	
	}
	
	/** Load Media Button in WP editor
	*
	* The 'add button' interface in WP post and page editor to simplify adding buttons. Loads button plus required Javascript.
	*/
	function media_button($editor_id) {
		$output = '';

		// options 
		if (get_option('maxbuttons_noshowtinymce') == 1) return;

		$this->load_media_script();

		// Only run in post/page creation and edit screens

			$title = __('Add Button', 'maxbuttons');
			$icon = $this->plugin_url . 'images/mb-peach-icon.png';
			$img = '<span class="wp-media-buttons-icon" style="background-image: url(' . $icon . '); width: 16px; height: 16px; margin-top: 1px;"></span>';
			$output = '<a href="javascript:void(0);" class="button maxbutton_media_button" data-callback="window.maxMedia.showShortcodeOptions" title="' . $title . '" style="padding-left: .4em;">' . $img . ' ' . $title . '</a>'; 

		echo $output;
	}
		
	/** Scripts run on front-end 
		Load font-awesome, and limited javascript for the front-end. This is being kept extremely limited for performance reasons.
	*/
	public function front_scripts() 
	{
					
		$fa_url = apply_filters("mb_fa_url", $this->plugin_url . 'assets/libraries/font-awesome/css/font-awesome.min.css'); 
		if ($fa_url != false && $fa_url != '')
		{
			wp_register_style('mbpro-font-awesome', $fa_url);
			wp_enqueue_style('mbpro-font-awesome');	
		}	
	
		// load backend script on front in Beaver Builder
		if (isset($_GET['fl_builder']))
		{
			$this->add_admin_styles('maxbuttons'); 
		}
	
		wp_enqueue_script('maxbuttons-front', $this->plugin_url . 'js/min/front.js', array('jquery'), true);
		$local = array(); 
		$local["ajaxurl"] = admin_url( 'admin-ajax.php' );

		wp_localize_script('maxbuttons-front', 'mb_ajax', $local);			
	}
	
	/** Extra text to display in admin footer */ 
	function admin_footer_text($text)
	{
		if (! isset($_GET["page"]))
			return $text;
			
		if ( strpos($_GET["page"],'maxbuttons') === false)
			return $text; 
		$text = '';

		$text .=   sprintf("If you like MaxButtons please give us a  %s★★★★★%s rating!", 
			"<a href='https://wordpress.org/support/view/plugin-reviews/maxbuttons#postform' target='_blank'>", 
			"</a>")  ; 
		return $text; 
	
	}
	
	
	function shortcode($atts) 
	{
		 $button = $this->getClass("button");
		 return $button->shortcode($atts); 
	}

	public function collection_shortcode($atts, $content = null)
	{
		$atts = shortcode_atts(array(
				"id" => 0, 
				"name" => '', 
				"nocache" => false,  // these are button options. 
				"mode" => "normal", 
				"style" => "footer", 
				),
				
		$atts);
		
		$id = intval($atts["id"]); 
		$name = sanitize_text_field($atts["name"]); 
		
		if ($id > 0) 		
			$collection = maxCollections::getCollectionByID($id);
		elseif ($atts["name"] != '') 
			$collection = maxCollections::getCollectionByName($name);
		 
 
		if ($collection)
			return $collection->shortcode($atts,$content);
	
	}
	

	function plugin_action_links($links, $file) {
 
		if ($file == plugin_basename(dirname(MAXBUTTONS_ROOT_FILE) . '/maxbuttons.php')) {
			$label = __('Buttons', 'maxbuttons');
			$dashboard_link = '<a href="' . admin_url() . 'admin.php?page=maxbuttons-controller&action=list">' . $label . '</a>';
			array_unshift($links, $dashboard_link);
		}

		return $links;
	}


	function plugin_row_meta($links, $file) {
		if ($file == plugin_basename(dirname(__FILE__) . '/maxbuttons.php')) {
			$links[] = sprintf(__('%sUpgrade to Pro Version%s', 'maxbuttons'), '<a href="http://maxbuttons.com/?ref=mbfree" target="_blank">', '</a>');
		}
	
		return $links;
	}


		function do_footer($id, $code, $type = "css")
		{
		 
			$this->footer[$type][$id] = $code; 
			
		}
		function footer()
		{
 			if(count($this->footer) == 0) return; // nothing
 				
			foreach ($this->footer as $type => $part) 
			{
				// add tag
				if ($type == 'css') 
				{
					echo "<!--email_off--><style type='text/css'>";
				}				
					foreach ($part as $id => $statements)
					{
						if (strlen($statements) > 0) // prevent whitespace 
						echo $statements . "\n"; 
					}
				if ($type == 'css') 
				{
					echo "</style><!--/email_off-->\n"; 
				}	
			}
		
		}
		
		public function do_review_notice () {
				
			$current_user_id = get_current_user_id(); 
			$review = get_user_meta( $current_user_id, 'maxbuttons_review_notice' , true );
   			
   			if ($review == '')
  			{
				//$created = get_option("MBFREE_CREATED");
				$show = time() + (7* DAY_IN_SECONDS); 
				update_user_meta($current_user_id, 'maxbuttons_review_notice', $show);
				return; 	 
  			}

  					
  			$display_review = false;
  			
  			if ($review == 'off') 
  			{	return; // no show 
  			
			}
  			elseif (is_numeric($review))
  			{	
  				$now = time(); 

  				if ($now > $review)
  				{	
					$display_review = true;

  				}
  			}
  			
  			// load style / script. It's seperated since it should show everywhere in admin. 
  			if ($display_review)
  			{
  			  	add_action( 'admin_notices', array( maxUtils::namespaceit('maxAdmin'), 'mb_review_notice'));
  			  	wp_enqueue_style('maxbuttons-review-notice', $this->plugin_url . 'assets/css/review_notice.css');				
  				wp_enqueue_script('maxbuttons-review-notice', $this->plugin_url . 'js/min/review-notice.js',  array('jquery'), true); 

  				$local = array(); 
  				$local["ajaxurl"] = admin_url( 'admin-ajax.php' );
  				wp_localize_script('maxbuttons-review-notice', 'mb_ajax_review', $local);
  			}
 
		}
		
		public function setReviewNoticeStatus() 
		{
			$status = isset($_POST["status"]) ? sanitize_text_field($_POST["status"]) : ''; 
			$current_user_id = get_current_user_id(); 
			
			$updated = false; 
			
			if ($status == 'off') 
			{
				$updated = true; 
				update_user_meta($current_user_id, 'maxbuttons_review_notice', 'off'); 
			
			}
			if ($status == 'later')  
			{
				$updated = true;
				$later = time() + (14 * DAY_IN_SECONDS ); 
				
				update_user_meta($current_user_id, 'maxbuttons_review_notice', $later); 
			}
			if ($status == 'reviewoffer-dismiss') // different ad!
			{
				$updated = true; 
				update_user_meta($current_user_id, 'maxbuttons_review_offer', 'off'); 
				
			}
			
			echo json_encode(array("updated" => $updated)) ; 
			
			exit(); 
		}
		
		/*	Add a notice 
	
			The added notice will be displayed to the user in WordPress format. 
			@see display_notices
	
			@param $type string message | notice | error | fatal
			@param $message string User understandable message
		
		*/
		public static function add_notice($type, $message)
		{
			self::$notices[] = array("type" => $type, 
									"message" => $message
								); 
				
		}
		
		/* Display all notices
		
		Then notices added by @see add_notice will be displayed. This function is called by an action hook
		
		@param $echo echo the results or silently return.
		@return string|null If not written to screen via echo, the HTML output will be returned
		*/
		public function display_notices($echo = true)
		{

			if ($echo === '') $echo = true;
			$notices = self::$notices; 
			$output = ''; 
			if (count($notices) == 0)
				return;
				
			foreach($notices as $index => $notice)
			{
				$type = $notice["type"]; 
				$message = $notice["message"]; 
				$output .= "<div class='mb-message $type'> "; 
				$output .= $message ; 
				$output .= "</div>"; 
			} 

			self::$notices = array(); // empty notices to prevent double display 
			
			if ($echo) echo $output; 
			else return $output;
		}
		
}  // class
 

