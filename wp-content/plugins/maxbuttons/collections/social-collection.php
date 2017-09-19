<?php 
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
$collectionClass["social"] = "socialCollection"; 

class socialCollection extends maxCollection 
{
	protected $collection_type = "social"; 
	protected $uses_blocks = array("picker", "basic","layout", "social","preview", "export");
	
	public function __construct()
	{
		parent::__construct(); 
		add_filter("mb_col_save_collection_data", array($this,"save_collection"), 10, 2 );

	}
	
	// check if button is real button , or insert if not. 
	public function save_collection($data, $post)
	{

		$selection = $data["picker"]["selection"]; 
		$pack_buttons = false;

		foreach($selection as $index => $button_id)
		{

			if (isset($post["button-data-$button_id"]))
			{	
				$post_data = json_decode(base64_decode($post["button-data-$button_id"]), true);

				// check if real = false is set - this is a button from a pack. 
				if (isset($post_data["meta"]["is_virtual"]) && $post_data["meta"]["is_virtual"] == true)
				{
					$pack_buttons = true; // after dealing with virtual button id's, reload is needed
					
					// if so, save button
					$user = wp_get_current_user(); 
					
					$button = MB()->getClass("button"); 
					$button->clear(); 
 
					$post_data["name"] = $post_data["basic"]["name"]; 
					$post_data["status"] = $post_data["basic"]["status"]; 
					
					// set the proper meta's and user edited = false;
					$post_data["meta"]["is_virtual"] = false; // off to the real world 
					$post_data["meta"]["user_edited"] = false;
					$post_data["meta"]["created"] = time();
					$post_data["meta"]["user_created"] = $user->user_login; 
 					$post_data["meta"]["in_collections"] = array($this->collection_id);		
					$post_data["id"] = 0; // new button
				
					$button->setupData($post_data);
										
					$new_button_id = $button->update($post_data);
 
					// save a reference to collection. 
					$data["picker"]["selection"][$index] = $new_button_id;
					
					// update social options to new button id 
					$social = $data["social"]; 

 
					foreach($social as $option => $options) // social-option - data
					{
						foreach($options as $index => $option_data) // index (count) - data
						{
							foreach($option_data as $option_id => $opt_data) // button_id - data
							{
								//echo $option_id . " - $button_id (OPT / BUTT)<BR>"; 
								if ($option_id == $button_id) 
								{			
									$social["social-option"][$index][$new_button_id] = $opt_data; 
									unset($social["social-option"][$index][$button_id]);
								
					
								}
							}
						}
					}
 
					$data["social"] = $social;
				}				
			}
		}
		
		if ($pack_buttons)
		{
			 add_filter("collections_ajax_force_reload", function () { return true; }); 
		
		}
		
		return $data;

//		return $this->collection_id;
		//exit();
	} 

	function editor_getPacks() 
	{	
		$packs = parent::editor_getPacks();
	
		// Collect packs from dynamic amount of dirs
		$path = MB()->get_plugin_path() . "assets/packs/" ; 
		$packs = array_merge($this->getSocialPacks($path), $packs ); 
		return $packs;

	}
	
	function getSocialPacks($path) 
	{
			if (! is_dir($path)) 
			{
				MB()->add_notice("error", "Could not load asset packs");
				return; 
			}
			$dirhandle = opendir($path); 
			$found_packs = array();
			while (false !== ($name = readdir($dirhandle))) {
				if ($name != '.' && $name != '..')
				{
					$found_packs[] = $name;
				}
			} 
		
			sort($found_packs);
		
			foreach($found_packs as $pack)
			{
				$packsClass = MB()->getClass("pack"); 
	 
				$socialpack = array("file" => $path . $pack . "/" . $pack . ".xml", 
								"img" => '', 
								"dir" => '', 
								"is_local" => true,
						);		
				$result = $packsClass->load_pack($socialpack);
				if(! $result)  // failed.
				{	
					continue;  
				}
				$packs[$pack]['tab'] =   $packsClass->getName();
				$packs[$pack]["func"] = array($this, 'editor_getSocialButtons'); 
				$packs[$pack]["rel"] =  "assets/packs/"; 
				$packs[$pack]["path"] = $path . $pack;
			
		
			}
			return $packs;
	}
	
/*	public function editor_getButtons()
	{
		$buttons = parent::editor_getButtons(); 

		$buttons = $this->editor_getSocialButtons($buttons);
	 
		return $buttons;
	} */

	protected function editor_getSocialButtons($packname, $data = array() )
	{
		$buttons = array();
	
		$packsClass = MB()->getClass("pack"); 

		$packs = $this->editor_getPacks(); 
		$data = $packs[$packname];  
 

		//$packName = isset($data["tab"]) ? $data["tab"] : ''; 
		$packpath = $data["path"]; // isset($data["rel"]) ? $data["rel"] : ''; 
		$plugin_path = MB()->get_plugin_path(true);
		$rel = str_replace($plugin_path,'',$packpath);
		
		
		$socialpath = MB()->get_plugin_path(true) . $rel . "/";
		$socialurl = MB()->get_plugin_url(true) . $rel . "/";

		$socialpack = array("file" => $socialpath . "$packname.xml", 
						    "img" => '', 
						    "dir" => $socialurl, 
						    "is_local" => true,
					);
		 

		$packsClass->setPackPath($socialurl);			
		$packsClass->load_pack($socialpack);
		$xml = $packsClass->getPackXML(); 


 		
		foreach( $xml->maxbutton as $xmlbutton)
		{
			 
			$button_array =  $packsClass->parse_pack_button($xmlbutton);

			$button = MB()->getClass('button'); 
			$button->clear(); // also ensures all fields are loaded
				
			$meta = array("is_virtual" => true, // not a real button
						  "created_source" => "collection");
			$button_array["meta"]= json_encode($meta);			
 
			$button->setupData($button_array);

 		 
			if (isset($button_array["collection"])) 
			{
				$coldata = $button_array["collection"];
			
				// overloading the class to retrieve collection data later on social-block at adding time.  
				$button->setData("collection", $coldata); 
			
			} 
	 
			$buttons[] =  $button;
			
		}
		return $buttons;
	}

}


?>
