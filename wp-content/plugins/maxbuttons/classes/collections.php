<?php 
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

use \RecursiveDirectoryIterator as RecursiveDirectoryIterator; 
use \FilesystemIterator as FilesystemIterator; 
use \RecursiveIteratorIterator as RecursiveIteratorIterator;


class maxCollections
{
	static $init = false; 
	//static $collections = array();
	static $collectionClass = array(); 
	static $collectionBlock = array(); 
	
	static $hooks = array(); 
	
	static $cached_collections = array(); 
	protected static $transientChecked = false; 

	static $collectionButtons = null; // all button ID's in a collection.

	static function init()
	{
		$collection_paths = apply_filters("mbcollection_paths", array( MB()->get_plugin_path() . '/collections/') ); 
		$collectionClass = array(); 
		$collectionBlock = array();
		
		foreach($collection_paths as $cpath)
		{
			$dir_iterator = new RecursiveDirectoryIterator($cpath, FilesystemIterator::SKIP_DOTS);
			$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

			foreach ($iterator as $fileinfo)
			{
				$collection = $fileinfo->getFilename();
				 
			
				if (file_exists($cpath . $collection))
				{
					 require_once( $cpath . $collection);
				}
			}
		}	
		self::$collectionClass = $collectionClass; 
		self::$collectionBlock = $collectionBlock;
		
		self::$init = true;
	
	}

	/* 
	Check our custom transients for expiration. This should be done only once per run or less.
	*/
	static function checkExpireTrans() 
	{
		if (! self::$transientChecked)
		{
			maxUtils::removeExpiredTrans();  // instead of on each button to reduce load.
			self::$transientChecked = true; 
		}
	}
	/* 
	Function to hook into WordPress for automatic display of collections. 
	*/
	static function setupHooks()
	{
		// check for admin side, we need not hooks nor queries for this. 
		if (is_admin()) 
			return; 
		
 
		global $pagenow; 
		if  ( in_array($pagenow, array('wp-login.php', 'wp-register.php')) )
			return;
			
		global $wpdb; 
		$table = maxUtils::get_collection_table_name(); 
		$sql =  "select collection_id, collection_key, collection_value from $table where 
				 collection_key in ('show_homepage','show_page','show_post', 'show_archive') 
				 and collection_id IN (select collection_id from $table where collection_key = 'collection_type' 
				 and collection_value  = 'social' )  ";
		$results = $wpdb->get_results($sql, ARRAY_A); 
		
		$hook_array = array();
		
		foreach($results as $result) 
		{
			$id = $result["collection_id"]; 
			$key = $result["collection_key"]; 
			$placement = $result["collection_value"]; 
		
			// override - if needed you can specify a different placement for each position 
			$placement = apply_filters("mb-collection-$id-$key-placement", $placement); 
		
			switch($placement)
			{
				case "after": 
				case "before": 
				case "after-before": 
					$show_type = "post"; // show on the post loop
				break;
				case "static-left": 
				case "static-right": 
				case "static-top": 
				case "static-bottom": 
					$show_type = "once"; // show once - otherwise for overviews they will be many static buttons. 
				break; 
			}
 
				$hook_array[$show_type][$key][] = array("collection_id" => $id, "placement" => $placement); 
		}
 
		
		self::$hooks = $hook_array; 
		
		if (isset($hook_array["post"]) && count($hook_array["post"]) > 0) 
		{
			add_filter("the_content", array(maxUtils::namespaceit('maxCollections'), "doContentHooks"));
		}
		if (isset($hook_array["once"]) && count($hook_array["once"]) > 0) 
		{
			//self::doFooterHooks(); // the stuff that goes once. 
			add_action('wp_head', array(maxUtils::namespaceit('maxCollections'), 'doFooterHooks')); 
		}
		
		if (count($hook_array) > 0) 
			return true; // yes, bind action and check
		else
			return false; // no binds, don't check.
	}
 
 	/* Try to find the current place we are at in the site ( front / blog / page etc ) .*/
 	protected static function getCurrentHook() 
 	{
		$hook = ''; 
 		if (is_front_page())	// check if home page ( home page > page ) 
 		{
 			$hook = "show_homepage";

 		}
 		elseif (is_page()) // check if page
  		{
			$hook = "show_page"; 
 		}
 		elseif (is_single() || is_home()) // check if post
 		{
 			$hook = "show_post"; 
 		}
 		elseif (is_archive()) 
 		{
 			$hook = "show_archive"; 
 		}
 		

 		return $hook; 
 	}
 
 	static function doContentHooks($content) 
 	{
 		$hook_array = self::$hooks["post"]; 
  		$hook = self::getCurrentHook(); 
  		
 		if ($hook == '') 
 			return $content;  // nothing
 		
 		if (! isset($hook_array[$hook]))  // nothing as well
 			return $content;
 		
 		 $collections = $hook_array[$hook]; 
 		 
 		 // do all collections on hook -- check for placement as well. 
 		 foreach($collections as $settings)
 		 {
 		 	$collection_id = $settings["collection_id"];
 		 	$placement = $settings["placement"]; 

 		 	$col = self::getCollectionByID($collection_id);
		 	$col->set($collection_id); 
		 	$output = $col->display(array("echo" => false)); // output default, no echo
 	
 		 	
 		 	switch($placement) // where to output, rather limited atm. 
 		 	{
 		 		case "before": 
 		 			$place = "before"; 
 		 		break; 
 		 		case "after-before"; 
 		 			$place = "both"; 
 		 		break; 
 		 		default: 
 		 			$place = "after"; 
 		 		break;
 		 	
 		 	}
 		 	


 		 	if($place == 'before' || $place == 'both') 
 		 	{
 		 		$content = $output . $content;
 		 	}
 		 	if($place == 'after' || $place == 'both') 
 		 	{
 		 		
 		 		$content = $content . $output; 
 		 	}
 		 }		
 		
 		 return $content;
 		
 	}
 	
 	static function doFooterHooks() 
 	{
 		$hook_array = self::$hooks["once"]; 
 		$hook = self::getCurrentHook(); 
 		
 		if (! isset($hook_array[$hook]))  // nothing 
 			return;	
 
 		 $collections = $hook_array[$hook]; 
 		 foreach($collections as $settings)
 		 {
 		 	$collection_id = $settings["collection_id"];
 		 	$placement = $settings["placement"]; 
 		 	
 		 	$col = self::getCollectionByID($collection_id);
 		 	$col->set($collection_id); 
 		 
 		 	$output = $col->display(array("echo" => false)); // output default, no echo

			do_action('mb-footer', 'collection-' . $collection_id, $output, 'collection_output'); 
				
 		 }	 
 	}
 	
 	static function checkCachedCollection($collection_id)
 	{
 		/* The cache is pointless since for every collection the shared URL's and fields can change. This means that the same collection
 			can server up multiple situations. Deactivated this when discovered in /blogs/ all have the same share URL on different posts. 
 		*/
 		return false;
 		
 //		if (isset(self::$cached_collections[$collection_id])) 
 //			return self::$cached_collections[$collection_id]; 
 
 //		return false; 			
 	}
 	
 	static function addCachedCollection($collection_id, $data) 
 	{
 		self::$cached_collections[$collection_id] = $data;
 	}
 	
 	public static function isButtonInCollection($button_id)
	{
		if (is_null(self::$collectionButtons))
		{
			global $wpdb; 	
			$table = maxUtils::get_collection_table_name();
			$sql = 'SELECT collection_id, collection_value FROM ' . $table . ' WHERE 
					collection_key = "picker";'; 
			$result = $wpdb->get_results($sql, ARRAY_A); 
			
			$buttonarray = array(); 
			foreach ($result as $index => $row)
			{
				$collection_id = $row['collection_id']; 
				$buttons = json_decode($row['collection_value']); 
				
				if (isset($buttons->selection))
					$buttons = $buttons->selection;
				else
					$buttons = array();
				
				foreach($buttons as $index => $b_id)
				{
					if (isset(static::$collectionButtons[$b_id])) 
						self::$collectionButtons[$b_id][] = $collection_id; 
					else
						self::$collectionButtons[$b_id] = array($collection_id);
				}
				
			}
		
		}
		
		if (isset(self::$collectionButtons[$button_id])) 
			return self::$collectionButtons[$button_id]; 
		else
			return false;
	}
 	
 	static function ajax_save() 
 	{
 		$nonce = sanitize_text_field($_POST["nonce"]);
 		$action  = sanitize_text_field($_POST["action"]); 

 		
	 	$collection_id = intval($_POST["collection_id"]); 	
	 	$collection_type = sanitize_text_field($_POST["collection_type"]); 
	 	
	 	$admin = MB()->getClass('admin'); 
	 	
	 	$result_title = array("success" => __("Your collection was saved","maxbuttons"), 
	 			   			  "error" => __("Error","maxbuttons")
	 			   			);
	 	
	 	$close_text = __('Close', 'maxbuttons'); 
	 	 
	 	$result = array(
	 			   "error" => false, 
	 			   "body" => '', 
	 			   "result" => true,
	 			   "data" => array(),
	 			   "close_text" => $close_text,
	 			   "new_nonce" => 0,		   	
	 	); 
 

	 	
 		if (! wp_verify_nonce($nonce, $action . "-" . $collection_id)) 
 		{
 			$result["error"] = true; 
			$result["body"] = __("Nonce not verified","maxbuttons"); 
			$result["result"] = false;
			$result["title"] = $result_title["error"]; 
			$result["data"] = array("id" => $collection_id);

 			$admin->endAjaxRequest($result);
 		
 		}

	 	if (! isset($collection_type)) 
	 	{
	 		MB()->add_notice("error", __("Collection type not found in save. Aborting","maxbuttons")); 
			$result["error"] = true;
			$result["body"] = MB()->display_notices(false); 
			$result["result"] = false;
			$result["title"] = $result_title["error"]; 			

 			$admin->endAjaxRequest($result);
		}
		
		$collection = self::getCollection($collection_type); 
		$collection->set($collection_id);
		
		$force_reload = false;
		if ($collection_id == 0) 
			$force_reload = true;

		// this can be a new id (!) 
		$collection_id = $collection->save($_POST); 	
 	
 		$result["data"]["id"] = $collection_id; 
 		$result["data"]["new_nonce"] = wp_create_nonce($action . "-" . $collection_id);
 		$result["data"]["reload"] = apply_filters("collections_ajax_force_reload",$force_reload); 
 		
 		$result["title"] = $result_title["success"];
 		 
 		$admin->endAjaxRequest($result);
 		
 		//echo json_encode($result);	
 		//exit(); 
 	}
 	
 	static function ajax_action_front()
 	{
 		// only for trivial front page actions!
 		self::ajax_action(array("ajax_nopriv" => true));
 	}
 	
 	static function ajax_action($args = array())
 	{
 		ob_start(); 
 		$defaults = array("ajax_nopriv" => false); 
 		$args = wp_parse_args($args, $defaults); 
 		
 		
 	 	$admin = MB()->getClass('admin'); 
 	 	
 		$nonce = isset($_POST["nonce"]) ? $_POST["nonce"] : false;
 		$block_name = sanitize_text_field($_POST["block_name"]); 
 		$block_action  = sanitize_text_field($_POST["block_action"]); 
 		$block_data = (isset($_POST["block_data"])) ? $_POST["block_data"] : ''; 
 		$action  = sanitize_text_field($_POST["action"]); 		
 		
 		$collection_id = intval($_POST["collection_id"]); 	
	 	$collection_type = sanitize_text_field($_POST["collection_type"]); 

		if(! $args["ajax_nopriv"])
		{
	 		if (! wp_verify_nonce($nonce, $action . "-" . $collection_id)) 
	 		{
	 			$result["error"] = true; 
				$result["body"] = __("Nonce not verified","maxbuttons"); 
				$result["result"] = false;
				$result["title"] = __("Security error","maxbuttons"); 
				$result["data"] = array("id" => $collection_id);

	 			$admin->endAjaxRequest($result);
	 		
	 		}	
		}
		
	 	$result = array(
	 			   "error" => false, 
	 			   "body" => '', 
	 			   "result" => true,
	 			   "data" => array(),
	 			   "new_nonce" => 0,		   	
	 	); 

		$collection = self::getCollection($collection_type); 
		$collection->set($collection_id);
	
		$result = $collection->doBlockAjax($result, $block_name, $block_action, $block_data); 
		 	
	 	//ob_end_clean();  // prevent PHP errors from breaking JSON response. 
	 	
	 	$admin->endAjaxRequest($result);
	 			$results = $collection->get_meta($name, 'collection_name'); 	
 	}
 
	static function getCollections()
	{
		if (! self::$init) self::init(); 
		
		global $wpdb; 
		
		$table = maxUtils::get_collection_table_name(); 
		
		$sql = "SELECT distinct collection_id from $table"; 
 
		$results = $wpdb->get_results($sql,ARRAY_A); 
		return $results;
	}
	
	/* This will invoke a collection class by name ( i.e. social-collection or basic-collection */ 
	static function getCollection($name)
	{
		if (! self::$init) self::init(); 
 
		if (isset(self::$collectionClass[$name])) 
		{
			$class = maxUtils::namespaceit(self::$collectionClass[$name]); 
			return new $class;
		}
	}
	
	/* This will find an user defined collection from the database by ID */
	static function getCollectionByID($id)
	{
		$collection = new maxCollection(); 
		$results = $collection->get_meta($id, 'collection_type'); 
 
		
		if ( count($results) == 1) 
		{
		
			$type = $results["collection_type"]; 

			$usecol = self::getCollection($type);
			if (! $usecol) 
				return false;
				
			$usecol->set($id);
			return $usecol;
		}
		
		return false;
	}
	
	/* Find a collection from the database by name */ 
	static function getCollectionbyName($name) 
	{
		//$collection = new maxCollection(); 
		global $wpdb; 
		$sql = "select collection_id from " . maxUtils::get_collection_table_name() . " where collection_key = 'collection_name' and collection_value = %s ";
		$sql = $wpdb->prepare($sql, $name); 
		$result = $wpdb->get_row($sql, ARRAY_A); // find first 
		
		
		if (count($result) > 0) 
		{
			if (isset($result["collection_id"])) 
			{
 
				$usecol = self::getCollectionByID($result["collection_id"]);
				return $usecol; 
			}
		
		} 
		return false;	
	}	
	
	static function getBlock($name)
	{
		if (! self::$init) self::init(); 
		
		if (isset(self::$collectionBlock[$name])) 
		{
			$class = maxUtils::namespaceit(self::$collectionBlock[$name]); 
			return new $class;
		}
		else return false;
	}
	

}
