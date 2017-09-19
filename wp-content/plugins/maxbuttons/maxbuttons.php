<?php
/*
Plugin Name: MaxButtons
Plugin URI: http://maxbuttons.com
Description: The best WordPress button generator. This is the free version; the Pro version <a href="http://maxbuttons.com/?ref=mbfree">can be found here</a>.
Version: 6.21
Author: Max Foundry
Author URI: http://maxfoundry.com
Text Domain: maxbuttons 
Domain Path: /languages

Copyright 2017 Max Foundry, LLC (http://maxfoundry.com)
*/
namespace MaxButtons; 

if (! defined('MAXBUTTONS_ROOT_FILE'))
	define("MAXBUTTONS_ROOT_FILE", __FILE__);
if (! defined('MAXBUTTONS_VERSION_NUM'))
	define('MAXBUTTONS_VERSION_NUM', '6.21');
	
define('MAXBUTTONS_RELEASE',"28 Jul 2017"); 


if (! function_exists('MaxButtons\maxbutton_double_load')) 
{
	function maxbutton_double_load()
	{
		$message =  __("Already found an instance of MaxButtons running. Please check if you are trying to activate two MaxButtons plugins and deactivate one. ","maxbuttons" );
		echo "<div class='error'><h4>$message</h4></div>"; 
		return;
	}
}

if (function_exists("MaxButtons\MB")) 
{
	add_action('admin_notices', 'MaxButtons\maxbutton_double_load');
	return;
} 


// In case of development, copy this to wp-config.php
// define("MAXBUTTONS_DEBUG", true);
// define("MAXBUTTONS_BENCHMARK",true); 

require_once("classes/maxbuttons-class.php"); 

// core
require_once('classes/button.php');
require_once('classes/buttons.php'); 
require_once("classes/installation.php"); 	
require_once("classes/max-utils.php"); 

// more core
require_once("classes/block.php"); 

require_once('classes/field.php'); 
require_once('classes/blocks.php'); 


require_once("classes/maxCSSParser.php");
require_once("classes/admin-class.php");

require_once("classes/collections.php"); 
require_once("classes/collection.php"); 
require_once("classes/collection-block.php"); 
require_once("classes/pack.php");
require_once("classes/integrations.php");

require_once("includes/maxbuttons-admin-helper.php"); 
 

// external libraries 
require_once("assets/libraries/scssphp/scss.inc.php");
require_once("assets/libraries/simple-template/simple_template.php"); 

if (! class_exists('simple_html_dom_node'))
	require_once("assets/libraries/simplehtmldom/simple_html_dom.php");


// runtime.
if (! function_exists("MaxButtons\MB"))	{
	function MB()
	{
		return maxButtonsPlugin::getInstance();
	}
}
$m = new maxButtonsPlugin();	


// Activation / deactivation
register_activation_hook(__FILE__, array(maxUtils::namespaceit("maxInstall"),'activation_hook') );
register_deactivation_hook(__FILE__,array(maxUtils::namespaceit("maxInstall"), 'deactivation_hook') );

