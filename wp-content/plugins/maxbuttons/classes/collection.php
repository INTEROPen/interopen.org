<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

use \simple_html_dom as simple_html_dom;

class maxCollection 
{
	protected $collection_type = 'none'; 
	protected $uses_blocks = array();
	protected $blocks = array(); // array of objects 
	
	protected $collection_id = 0;
	
	// layout functions
	protected $cssParser; 
	protected $collection_js; 
	
	// derived from button class, prepare for maxcss parser
	//protected $collection_css = array('normal' => array() ,':hover' => array() ,':visited' => array(), "responsive" => array()); 
	protected $buttons = array(); // array of buttons, ready for display - pre parse


	/* Constructor 
	   Sets the blocks used in this collection. These are mostly derived from subclasses of the specific collection types.
	*/
	public function __construct()
	{
		maxCollections::checkExpireTrans(); 
		
		foreach($this->uses_blocks as $block_name )
		{
			$block = maxCollections::getBlock($block_name);
			if ($block)
			{
				$block->setCollection($this);
				$block->set(array()); // defaults
				$this->blocks[$block_name] = $block;
			}
		}
		
	
	}
	/* Get all buttons that are currently loaded */ 
	public function getLoadedButtons()
	{
		return $this->buttons;
	}
	

	
	/* Get a certain block class by name */
	public function getBlock($blockname) 
	{
 
		if (isset($this->blocks[$blockname]))
			return $this->blocks[$blockname]; 
		else
			return false;
	}
	
	/* Get the type of the collection */ 
	public function getType()
	{
		return $this->collection_type;
	}	
	
	/* Get the ID of the collection */ 
	public function getID() 
	{
		return $this->collection_id; 
	}
	
	/** Get the MaxCSSParser classes which handles SCSS. This function is derived from the one in Button class */ 
	public function getCSSParser()
	{
		if (! $this->cssParser)
			$this->cssParser = new maxCSSParser(); 
			
		return $this->cssParser;
	}
	
	/** Set the collection by ID 
	@param $collection_id ID of the collection stored in database
	*/	
	public function set($collection_id)
	{
		$this->collection_id = $collection_id; 
		
		// make every block set it's stuff. 
		$values = $this->get_meta($collection_id);

		foreach($this->blocks as $block)
		{
 
			$block->set($values);
			// run every blocks setter 
		
		}
	}
	

	/** Interface for handling AJAX requests to communicate with the seperate blocks. 
		@param $result The initial (default) result to JSON back 
		@param $block_name The name of the block in question 
		@param $action The name of the block function to call 
		@param $data Data specified by JS to use for this function 
		@return $result - Standardized result block including error, body 
	*/
	public function doBlockAJAX($result, $block_name, $action, $data)
	{
		// core class is being called.
		if ($block_name == 'collection') 
		{
			$result = $this->$action($result, $data); 
			return $result;
		}
		
		if (isset($this->blocks[$block_name])) 
		{
			$block= $this->blocks[$block_name]; 
			$result = $block->$action($result, $data); 
			return $result;
		
		}
		
		$result["error"] = true;
		$result["title"] = __("Error : Block was not there","maxbuttons");
		return $result;
	}

	/** Save the collection data to the database. This will invoke the save functions of the blocks itself to gather, verify and manipulate data */ 
	public function save($post)
	{
		$data = array(); 

		// Collection id needed to save meta data and others.  
		if ($this->collection_id == 0) // assume new 
		{
			$collection_id = $this->getNewCollectionID(); 
 
			$this->collection_id = $collection_id;
		}	
		
		// run this by each block and collect data
	 	foreach($this->blocks as $block)
	 	{
	 		$data = $block->save_fields($data, $post);
	 	}
	 	
	 	$data = apply_filters("mb_col_save_collection_data", $data, $post); 
	 	
	 	// clean what was not set 
	 	$this->clean_meta($data); 
	 	
	 	// write data as a per block per key value into the database 
	 	foreach($data as $blockname => $blockdata)
	 	{
	 		// seems dangerous
	 		$meta_id = $this->update_meta($this->collection_id, $blockname, $blockdata); 
  
	 	}
 
	 	return $this->collection_id;
	 	
	}
	
	// remove post meta not in data post. 
	public function clean_meta($data)
	{
		$data_blocks = array_keys($data); 
 
		
		$meta = $this->get_meta($this->collection_id); 
		 
		
		foreach($meta as $meta_key => $values)
		{
			if (! in_array($meta_key, $data_blocks)) 
			{
				$this->delete_meta($this->collection_id, $meta_key);
			}
		
		}
	
	}
	
	protected function update_meta($collection_id, $collection_key, $collection_value) 
	{
		global $wpdb; 
		$table = maxUtils::get_collection_table_name(); 
		
		if ($collection_value == '') return; // no data no database entry 
		if (is_array($collection_value) && count($collection_value) == 0) return; // same for empty arrays
		
		if (is_array($collection_value))	
			$collection_value = json_encode($collection_value);
		
		if ($collection_id == 0) // assume new 
		{
			$collection_id = $this->getNewCollectionID(); 
 
			$this->collection_id = $collection_id;
		}	
		
		$sql = "SELECT meta_id from $table where collection_id = %d and collection_key = %s ";
		$sql = $wpdb->prepare($sql, $collection_id, $collection_key);
		
		$results = $wpdb->get_results($sql, ARRAY_A); 
					
		if (count($results) == 1) 
		{
			$meta_id = $results[0]["meta_id"]; 
			$data = array("collection_value" => $collection_value);  // what's being updated
			$where = array("meta_id" => $meta_id); 
 
			$wpdb->update($table, $data, $where);
			return $meta_id; 
		}
		if (count($results) == 0)
		{
			$data = array("collection_value" => $collection_value, 
						"collection_key" => $collection_key, 
						"collection_id" => $collection_id, 
					);

			$meta_id = $wpdb->insert($table, $data); 
 
			return $meta_id;
		}
		else 
		{
			MB()->add_notice("error", __("Update Collection meta has multiple rows, this should not be possible.","maxbuttons")); 
			
		}
	}
	
	
	/** Determine the next ID for collection */ 
	protected function getNewCollectionID()
	{
		global $wpdb; 
		$table = maxUtils::get_collection_table_name(); 
			
		$sql = "SELECT max(collection_id) as max from $table"; 
		
		$max = intval($wpdb->get_var($sql));
 
		$max = $max + 1;
		return $max;  
	}
	
	protected function delete_meta($collection_id, $collection_key)
	{
		if (intval($collection_id) > 0 && $collection_key != "")
		{
			global $wpdb; 
		//	delete_post_meta($collection_id, $collection_key);
			$table = maxUtils::get_collection_table_name(); 
			$where = array("collection_id" => $collection_id, 
						   "collection_key" => $collection_key
						  ); 
			$where_format = array("%d", "%s"); 
			$wpdb->delete($table, $where, $format); 
			
		}
	}


	/** Delete the collection. This is done via AJAX request */
	public function delete($result, $data) 
	{
		global $wpdb; 
		if(! $this->collection_id > 0)
			return false; 
		
		$picker = $this->getBlock("picker"); 
		$picker_data = $picker->get(); 
		$buttons = $picker_data["selection"]; 
		$button = MB()->getClass("button");
		
		$buttons_removed = 0; 
		
		foreach($buttons as $button_id) 
		{
			$deleted = $this->maybe_delete_button($button_id); 
			if ($deleted) 
				$buttons_removed++; 
				
		}
			
		$table = maxUtils::get_collection_table_name();
		$where = array("collection_id" => $this->collection_id); 
		$where_format = array("%d");
		$wpdb->delete($table, $where, $where_format); 
		
 		$result["data"]["body"] = __("The collection is removed","maxbuttons"); 
 		$result["data"]["title"] = __("Removed","maxbuttons"); 
 		$result["data"]["buttons_removed"] = $buttons_removed; 
 		$result["data"]["collection_id"] = $this->collection_id; 
 		return $result;
		
		
	}
	
	/** On deletion of a collection check for each button if this is an auto-generated button just made for this collection and if 
	no changes where made to this button. If both conditions are true, remove the button */
	function maybe_delete_button($button_id)
	{
			$button = MB()->getClass("button");
			
			$button->set($button_id);
			$button_data = $button->get(); 
			
			$collection_id= $this->collection_id;
			
			// remove unedited buttons created for this collection - use with care. 
			if (isset($button_data["meta"]["user_edited"])) 
			{
				$created_source = (isset($button_data["meta"]["created_source"])) ? $button_data["meta"]["created_source"] : ''; 
				if ($button_data["meta"]["user_edited"] === false && $created_source == 'collection')
				{
					$in_collections = $button_data["meta"]["in_collections"]; 
					
					$key = array_search($collection_id, $in_collections); 
					
					if ($key !== false)
					{
						unset($button_data["meta"]["in_collections"][$key]); 
						
						if (count($button_data["meta"]["in_collections"]) == 0) 
						{	$button->delete($button_id); 
							return true;
						}
						else
						{
							if ($button_id > 0) // safety. 
							$button->update($button_data);
						}
					}
				}
				
			
			}
		return false;
	}
		
	function get_meta ($collection_id, $collection_key = '')
	{
		global $wpdb; 
		$table = maxUtils::get_collection_table_name(); 
		
		$prepare = array($collection_id); 
		
		$sql = "SELECT * from $table where collection_id = %d ";
		if ($collection_key != '')  
		{	
			$sql .= " and collection_key = %s "; 
			array_push($prepare, $collection_key);
			
		}

		$sql = $wpdb->prepare($sql, $prepare); 

		$results = $wpdb->get_results($sql, ARRAY_A); 
 
		$result_array = array(); // format array by field name = values to feed blocks and others.
		if (! is_null($results)) 
		{
			$nr = array(); 
			foreach($results as $row) 
			{
				$key = $row["collection_key"]; 
				/* A field can be either plain text or JSON */
				if(json_decode($row["collection_value"]))
					$value = json_decode($row["collection_value"], true); 
				else
					$value = $row["collection_value"];  
				$result_array[$key] = $value; 
				
				//$row["collection_value"] = unserialize($row["collection_value"]);
			} 
			
			return $result_array;
		}
		else
		{
			return false;
		}
	}
	
	function display($args = array()) 
	{
		maxUtils::startTime('collection-display');
		$defaults = array(
				"preview" => false,
				"echo" => true, 	 
				"style_tag" => false, 
				"compile" => false,
				"js_tag" => true, 
				"load_type" => "footer",
		); 
		
		$args = wp_parse_args($args, $defaults); 

		$cache = MaxCollections::checkCachedCollection($this->collection_id); 
		
		if (! $cache) 
		{
			$cssParser = $this->getCSSParser(); 
			$domObj = $this->parse($args); 
			$css 	= $this->parseCSS($args); 

			$js = $this->parseJS($args);

			$output = $domObj->save();
			unset($domObj);

		
		// CSS & JS output control
		$output .= $this->displayCSS($css, $args);
		$output .= $this->displayJS($js, $args);
		}
		else 
			$output = $cache; 
					
		MaxCollections::addCachedCollection($this->collection_id, $output); 
		
		maxUtils::endTime('collection-display');
		
		if ($args["echo"]) 
		{
			echo $output;
		}
		else
		{
			return $output;
		}

	}
	
	public function displayCSS($css, $args = array() ) // $echo = true, $style_tag = true)
	{
	
		$default = array(
			"echo" => true,
			"style_tag" => true, 
			"load_type" => "footer",
		); 
		$args = wp_parse_args($args, $default); 
		if ($args['load_type'] == 'inline')
			$args['style_tag'] =true; 
	
		$output = ''; 
		
		if ($args["style_tag"])
			$output .=  "<style type='text/css'>";
		
			$output .= $css;
			
		if ($args["style_tag"])
			$output .= "</style>";
		

 		if ($args["load_type"] == 'footer') 
 		{	

 			do_action('mb-footer','collection-' . $this->collection_id, $output); 
 		}
 		elseif ($args["load_type"] == 'inline') 
 		{
 			if ($args["echo"]) echo $output; 
 			else return $output;
 		
 		}
	
	}
	
	public function displayJS($js, $args = array() ) // $echo = true, $style_tag = true)
	{
		$default = array(
			"echo" => true,
			"js_tag" => true, 
			"load_type" => "footer",
			"preview" => false, 
		); 
		

		$args = wp_parse_args($args, $default); 
	
		if ($args["preview"]) 
			return; // no js on previews. 
		
		$output = '';

		if (count($js) == 0) 
			return; // no output, holiday
		
		if ($args["js_tag"]) 
		{
			$output .= "<script type='text/javascript'> "; 			
			
		}
		
		foreach($js as $index => $code) 
		{
			$output .= $code; 
		}
		
		if ($args["js_tag"]) 
		{
			$output .= " // } 
					//	} 
			//	window.onload = MBcollection" . $this->collection_id . "();	
				</script>		"; 
		}

		
 		if ($args["load_type"] == 'footer') 
 		{	

 			do_action('mb-footer','collection-' . $this->collection_id, $output, "js"); 
 		}
 		elseif ($args["load_type"] == 'inline') 
 		{
 			if ($args["echo"]) echo $output; 
 			else return $output;
 		
 		} 
	}		
	
	public function display_field_map() 
	{
		$map = array(); 
		foreach ($this->blocks as $block) 
		{
			$map = $block->map_fields($map); 
		}
		echo "<script language='javascript'>"; 	
				echo "var collectionFieldMap = '" . json_encode($map) . "';" ;
		echo "</script>";
	}
	
	/* Parses the collection, via the blocks */
	function parse($args)
	{
		$preview = isset($args["preview"]) ? $args["preview"] : false; 
 
		$domObj = new simple_html_dom();
		$collection_id = $this->collection_id; 
		
		$node = "<div class='maxcollection maxcollection-" . $collection_id . "' data-collection='" . $collection_id . "'>
					   </div>"; 
		$node = apply_filters("mb-col-basic-container",$node); 	
		$domObj->load($node);
		
		// use picker to get button classes in array
		$picker = $this->getBlock("picker"); 
		$this->buttons = $picker->getButtons();

		// changes to buttons in this function		
		maxUtils::startTime('collection-parse-parsebuttons');		
		foreach($this->blocks as $block)
		{	
			$block->setPreview($preview);
			$this->buttons = $block->parseButtons($this->buttons);

					 
		}
		maxUtils::endTime('collection-parse-parsebuttons');		

		maxUtils::startTime('collection-parse-blockparse');		
		// general parsing		

		foreach($this->blocks as $block)
		{			
			$domObj = $block->parse($domObj, $args);
		}
	 	maxUtils::endTime('collection-parse-blockparse');		
	 	
	 	$this->buttons = array();
	 	
	 	$cssParser = $this->getCSSParser();
	 	$domObj->load($domObj->save() );
		$cssParser->loadDom($domObj);
			
		return $domObj;
	}
	function parseCSS($args)
	{
		 $css = array();
		 
		foreach($this->blocks as $block)
		{	
			$css = $block->parseCSS($css, $args);
		}

		$css = $this->getCSSParser()->parse($css);
		
		return $css;
	}
	
	function parseJS($args)
	{
		$js = array(); 
		
		$defaults = array("preview" => false, 
		); 
		
		$args = wp_parse_args($args, $defaults); 
	
		if ($args["preview"]) 
			return false; // no js on previews		
		 
		foreach($this->blocks as $block)
		{	
			$js = $block->parseJS($js, $args);

		}

		return $js;	
	}
	
	
	function shortcode($atts, $content = null) 
	{
		
		// ugly -need to rework logic here. 
		//$collection = maxCollections::getCollection('social'); 
		$display_args = shortcode_atts(array(
					"echo" => false, 
					"mode" => "normal",
					"nocache" => false, 
					"style" => 'footer', 
				),
				
		$atts);

		$collection_id = $this->collection_id;
 
		//$this->set($collection_id);
		$display_args["compile"] = $display_args["nocache"]; 
		$display_args['load_type'] = $display_args['style']; 
		unset($display_args['style']); 
		unset($display_args["nocache"]); 
 
		$output = $this->display($display_args);
	
		return $output;
	} 
	
	
	// Get the pack definition that are present in the system.
	function editor_getPacks() 
	{			
		//$pack_paths = apply_filters('mb-col-pack-paths',  array(MB()->get_plugin_path() . "/") );
		$packs["maxbuttons"]["func"] = array($this, 'editor_getButtons'); 
		$packs["maxbuttons"]["tab"] = __("Your MaxButtons","maxbuttons"); 
		return $packs; 
		
	}
	
	public function ajax_getButtons($result, $data) 
	{
		$packs = $this->editor_getPacks(); 
		$req_pack= $data["pack"]; 

		foreach($packs as $index => $pack_data)
		{
			if ($req_pack == $index)
			{
				$buttons = call_user_func($pack_data["func"], $index, $data);
				
			}
		}
		$output = ''; 

 
		$page_args = array(); 
		if (isset($data["paged"])) 
		{
			$page_args["paged"] = $data["paged"];
		}	
		 
		if ($req_pack == 'maxbuttons') 
		{ 
			$page_args["limit"] = 18; 
			ob_start(); 
			do_action("mb-display-meta");
			do_action("mb-display-pagination", $page_args);
			$pagination = "<div class='tablenav top'>" .ob_get_contents() . "</div>"; 	
			ob_end_clean();
		
		$output .= $pagination;
 		}
 		
		foreach($buttons as $button)
		{
			$button_data = $button->get();

			$button_id = $button->getID();
			$name = $button->getName(); 
			
			$meta = isset($button_data["meta"]) ? $button_data["meta"] : array(); 
			//do_action('mb-data-load',$button_data); // weakness of the bubbling filter model
			
			$output .= "<div class='item shortcode-container' data-id='$button_id'> ";		
			$output .= $button->display(array("mode" => "preview", 'echo' => false, 'load_css' => 'inline') ); //"mode" => "preview", "compile" => true,
			$output .= "<span class='button_data'>" .  base64_encode(json_encode($button_data)) . "</span>"; 
			$output .= "<span class='button_name'>" . $name . "</span>"; 
			$output .= "</div>"; 

		}

		if ($req_pack == 'maxbuttons') 
		{
			$output .= $pagination;
		}
					
		$result["body"] = $output;
		return $result;
			
	}
	
	// show the available buttons 
	function editor_getButtons($pack, $data)
	{
		$button_array = array();
		
		$admin = MB()->getClass("admin"); 
		$button = MB()->getClass("button"); 
		
		$paged = (isset($data["paged"])) ? $data["paged"] : 1; 

		$buttons = $admin->getButtons(array(
				"orderby" => "id", 
				"order" => "DESC",
				"paged" => $paged, 
				"limit" => 18,
		)); 
 		

		foreach($buttons as $btn)
		{
			$id = $btn["id"]; 
			$b = MB()->getClass('button'); 
			$b->clear();
			$b->set($id);
			$button_data = $b->get(); 
			// exclude auto-generated non-user edited buttons. 
			if ( ! isset($button_data["meta"]["user_edited"]) || ($button_data["meta"]["user_edited"] == true || $button_data["meta"]["created_source"] != "collection"))
				$button_array[]  = $b; // the object 
		}
		return $button_array;
	}
	
	function showBlocks()
	{

		foreach($this->blocks as $block)
		{
			echo $block->admin_fields();
 
		}
	
	}
} //class

