<?php 
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

// probably load this after all plugins are loaded. 
class maxIntegrations
{


	static function init() 
	{
		// check and init after plugin loaded. 
		add_action('plugins_loaded', array(maxUtils::namespaceit('maxIntegrations'), 'load_integrations'), 999); 

		// integrations that fire right now, like ones that are based on actions and filters. 
		// This are the ones that also can't crash the plugin, since it's hook based - no hook - no call. 
		self::doDirectInit();  
		
		//remove_action( 'wp_ajax_fusion_pallete_elements', array( $instance,'get_pallete_elements') );		
	}
	
	
	static function load_integrations()
	{
	
	}
	
	static function doDirectInit() 
	{
		$integration_path = MB()->get_plugin_path() . 'assets/integrations/'; 
		require_once( $integration_path . "siteorigins_builder/sitebuilder.php"); 
		require_once( $integration_path . "shortcake/shortcake.php"); 

		do_action('maxbutton-direct-integrations'); 
	
	}

} // class
