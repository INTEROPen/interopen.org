<?php
namespace MaxButtons; 
defined('ABSPATH') or die('No direct access permitted');

/* Datamodel and base functionality for a button 

*/

use \simple_html_dom as simple_html_dom;
	 
class maxButton
{
	protected $id = 0; 
	protected $document_id = 0; // an id that's not duplicated when there are multiple buttons on the same page. Preferably reliable as well.
	protected $name = '';  
	protected $status = ''; 
	protected $description = ''; 
	protected $cache = ''; 
	
	protected $button_loaded = false;

	protected $data = array(); 
	protected $blocks; // Block Classes
	protected $templates = array(); // .tpl files

	protected $button_css = array(); 
	protected $button_js = array(); 
	
	// output conditions
	protected $load_css = 'footer';  // [ footer, inline, external, element ] 
	protected $load_js  = 'footer'; 
	
	protected $cssParser = false; 
	protected $parsed_css = ''; 

	 
	/* Class constructor 
	   
	   Get als loads the various blocks of which a button is built up. Blocks can be added and removed using the mb-init-blocks filter
	*/
	function __construct()
	{
		maxUtils::addTime("Button construct");

		// the parser
 
		// get all files from blocks map 
		
		// get all blocks via apply filters, do init. Init should not load anything big. 
		$this->loadBlockClasses(); 
		
	}
 
	/* Makes overriding block features possible by subclass
	
	*/
	private function loadBlockClasses()
	{

		// set blocks to the 'block name' that survived. 
		maxUtils::addTime("Load Block classes"); 
		
		//$classes = apply_filters("mb_blockclassesload", $classes);
		$class_array = maxBlocks::getBlockClasses(); 
		$classes = array_map(maxUtils::namespaceit('maxUtils::array_namespace'), $class_array ); 
		
		foreach($classes as $block => $class)
		{
			$block = new $class(); 
			
			$this->blocks[] = $block;
			if (is_admin())
			{
				maxBlocks::add($block); // block collection.
			}
		}

		
		$this->clear(); // init
 		do_action("mb\blockclasses", $class_array); 

	}
	
	/** Simple function to retrieve loaded blocks - * Used by install class */ 
	public function getBlocks() 
	{
		return $this->blocks;
	} 
	
	/**  Get Data from Database and set variables
	*
	*	You can pass either id or name to this function
	*
	*	@return Boolean Returns false when no data was found using either ID or name
	*/
	function set($id = 0, $name = '', $status = 'publish')
	{
		maxUtils::addTime("Button set $id");

 		$id = intval($id);
 		$name = sanitize_text_field($name);
 		$status = sanitize_text_field($status);

		global $wpdb;
		$this->clear(); // clear the internals of any previous work 
		
		// check to see if the value passed is NOT numeric. If it is, use title, else assume numeric
		if($id == 0 && $name != '') {
			$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . maxUtils::get_table_name() . " WHERE name = '%s' and status ='%s'", trim($name), $status ), ARRAY_A);
 
		} else {
			$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . maxUtils::get_table_name() . " WHERE id = %d and status ='%s'", $id, $status), ARRAY_A);
		}
		
		if (count($row) == 0) 
		{
			return false; 		
		} 

		/* Take the id from the query, otherwise ( when button shortcode called by name ) id might not be present properly' */  
 		$button_id = $row["id"]; 
 		maxButtons::buttonLoad(array("button_id" => $button_id)); // central registration
 		
		return $this->setupData($row);
		
	}
	/** Clear button settings
	*
	*  This function prevent that generated values from previous set actions will still be present.
	*/	
	function clear() 
	{
		unset($this->data);
		unset($this->button_css);
		$this->id = 0; // clear id
		$this->button_css = array();
		$this->button_js = array(); 
		$this->data = array('id' => 0);
		$this->data = $this->save(array(),false);

		$this->cache = ''; 
		$this->button_loaded = false;

		foreach($this->blocks as $block)
		{
			$block->set($this->data); // reset blocks 
		}

	}
		
	function setupData($data)
	{

		maxUtils::addTime("Button: Setup data");
		foreach($this->blocks as $block)
		{
			$block_name = $block->get_name();

			if (array_key_exists($block_name, $data))  // strangely isset doesn't work
			{
				$this->data[$block_name] = maybe_unserialize($data[$block_name]); // allow to feed unserialized stuff not from dbase
				if (! is_array($this->data[$block_name])) 
				{
					$this->data[$block_name] = json_decode($data[$block_name], true);
				}
 
			}
		}
		 
		$this->id = $data["id"];

		// Because PHP 5.3 
		$class = maxUtils::namespaceit('maxButtons');
		$this->document_id = $class::getDocumentID(array("button_id" => $this->id));
		$this->cache = isset($data["cache"]) ? trim($data["cache"]) : ''; // not set at button packs / non-dbase buttons!
		$this->data["id"] = $this->id; // needed for certain blocks, to be button aware. 
		$this->data["document_id"] = $this->document_id; // bound to JS and others. 
		$this->name = $data["name"]; 
		$this->status = $data["status"];
		$this->description = $this->data["basic"]["description"]; 

		//do_action('mb-data-load', $this->data);
		foreach($this->blocks as $block)
		{
			$block->set($this->data);
		}
		
		maxBlocks::setData($this->data);


		return true;
		 
	}
	
	function get( ) 
	{
		return $this->data;
	 
	}
	
	function getID() 
	{
		return $this->id; 
	}
	function getDocumentID() 
	{
		return $this->document_id; 
	}
	function getName() 
	{
		return $this->name;
	}
	function getDescription()
	{
		return $this->description;
	}
	
	function getStatus()
	{
		return $this->status; 
	}
	
	function getParsedCSS() 
	{
		return $this->parsed_css; 
	}
	
	function getCSSArray()
	{

		return $this->button_css;
	}
	
	function getCSSParser()
	{
		if (! $this->cssParser)
			$this->cssParser = new maxCSSParser(); 
			
		return $this->cssParser;
	}
	// get the cache
	function getCache() 
	{
		return $this->cache;
	}
	
	// modify the cache at own risk 
	function setCache($cache)
	{
		$this->cache = $cache; 
	}	

	/* Used by collections and import. Use sparingly. Button data is reput to blocks on display */
	function setData($blockname, $data ) 
	{
		foreach($data as $key => $value)
		{
			$this->data[$blockname][$key] = $value; 		
	
		}
	}

	
	/* Tell all blocks to reload the data
	
	   This function will tell all loaded blocks to reload it's data. This is needed when data is changed after the initial button load. 
	   
	*/ 
	function reloadData()
	{
		exit('reload Data - do not use');
		//do_action('mb-data-load', $this->data);
	
	} 

	/* Parse CSS from the elements
	
		@param string $mode [normal,preview,editor] - The view needed.
		@param string $forceCompile Recompile the CSS in any case  
	*/
	function parse_css($mode = "normal", $forceCompile = false )
	{
		$css = $this->button_css; 
 
		if (isset($this->cache) && $this->cache != '' && ! $forceCompile)
		{		 
		
			$css = $this->cache;
			// kill media queries from cache
			if ($mode != 'normal')
			{
				$pattern = "/@media.*}/is"; 
				preg_match($pattern, $css, $matches);
				$css = preg_replace($pattern, '', $css); 
			}

			maxUtils::addTime("Button: Cache loaded");
		}
		else
		{ 
			/* Internal filter, please don't use */ 
			foreach($this->blocks as $block)
			{
				$css = $block->parse_css($css, $mode); 
			}
			//$css = apply_filters('mb-css-blocks', $css, $mode);
			 
			/* Filter the raw CSS array before compile
			
			This filters passes an array with all CSS element before compile time. This should be CSS elements that can be understood by the CSS parser. 
				@since 4.20 
				@param $css CSS Array - split by element and pseudo (normal/hover) 
				
			*/
 			$css = apply_filters('mb/button/rawcss', $css, $mode); 

			$this->button_css = $css;


			$css = $this->getCSSParser()->parse($this->button_css);
			$css = apply_filters('mb/button/compiledcss', $css, $mode); // the final result. 
						
			if ($mode == 'normal') // only in general mode, otherwise things go amiss.
				$this->update_cache($css);

 		}
 		
		$this->parsed_css = $css; 		
		

		return $css; 
	}
	
	/* Call blocks for javascript 
	
		Function will call for all block element to crunch the required javascript for output ( if any ) 
		@param string $mode [normal, preview, editor] 
		
	*/
	function parse_js($mode = "normal") 
	{
		maxUtils::addTime("Button :: parse JS");
		$js = $this->button_js; 
		foreach($this->blocks as $block)
		{
			$js = $block->parse_js($js,$mode); 
			
		}

		$this->button_js = $js; 
	}
	
	/* Parse the actual button
	
	Function adds the basic button components, creates the DOM object for the button and asks all block elements to parse their additions. 
			
		@param string $mode [normal, preview, editor] 
		@return Object DomObj presentation of the button
	*/
	function parse_button($mode = 'normal')
	{
		$name = $this->name; 
		// non-latin breaks CSS / ID's - so move to latin.
		$name = maxUtils::translit($name);
		$name = sanitize_title($name);

		$classes = array("maxbutton-" . $this->id,
						 "maxbutton");
		if ($name != '') 
			$classes[] = "maxbutton-" . $name;
			
		$classes = apply_filters('mb-mainclasses', $classes); 
		$classes = implode(' ', $classes); 
		
		$domObj = new simple_html_dom();
		$domObj->load('<a class="' . $classes . '"></a>'); 
   

 		foreach($this->blocks as $block)
 		{
 			$domObj = $block->parse_button($domObj, $mode);
 		}	
 
 
		$domObj->load($domObj->save());
 
 		$cssParser = $this->getCSSParser();
		$cssParser->loadDom($domObj);

		return $domObj; 
	}

	/* Display all data and html to allow users to edit button settings */ 
	public function admin_fields() 
	{
		foreach($this->blocks as $block)
		{
			$block->admin_fields(); 
			
		}
		//do_action('mb-admin-fields' ); 
 
	}
	
 	/* Display the button */
	public function display($args = array() )
	{	
		maxUtils::startTime('button-display-'. $this->id);
		$defaults = array(
			"mode" => 'normal',
			"preview_part" => "full",
			"echo" => true, 
			"load_css" => "footer", // control how css is loaded. 
			"compile" => false, // possibility to force recompile if needed. 
		);
		$output = ''; // init output; 
		
		$args = wp_parse_args($args, $defaults); 

		$cssParser = $this->getCSSParser(); // init parser
 
	 	$this->load_css = $args["load_css"]; 
 
		if ($this->id == 0) // if button doesn't exists don't display unless in editor
		{
			if (! $args["mode"] == 'editor' ) 
				return;
 
			 $this->clear();	
			
			$this->data["id"] = 0;
			//do_action('mb-data-load', $data);
		}
		
		$mode = (isset($args["mode"])) ? $args["mode"] : "normal"; 		
 		switch($mode)
 		{
			case "preview": 	
		 		$preview = true;
	 			$compile = false;
		 	break;	
 			case "editor": 	 	
		 		$preview = true; 			
  				$compile = true;					
  				 // editor is both compile and preview. 
  			break;
 			break;
 			case "normal": 
 				$preview = false; 
 				$compile = false;
 			break;
 		}


		// Apply filters for general data override

		$this->data = apply_filters('mb/button/data_before_display', $this->data, $mode, array('preview' => $preview, 'compile' => $compile) ); // hooks 

		
		
 		if ( $this->load_css == "element" || $args["preview_part"] != "full" || $args["compile"] == true) { // if css output is on element, for to compile - otherwise inline styles will not be loaded.
 			$compile = true;

 		}
 		else 
 			$compile = false;

		// reload the data into the blocks, might have been altered by shortcode, filters etc.

		foreach($this->blocks as $block)
		{
			$block->set($this->data);
		}

		$domObj = $this->parse_button($mode); 
		
		maxUtils::startTime('button-parse-css-'. $this->id);
		$this->parse_css($mode, $compile); 
		maxUtils::endTime('button-parse-css-'. $this->id);
				
		if (! $preview)  // no js on previews 
			$this->parse_js($mode);  
		
		if ($preview)  // mark it preview
		{

			$domObj->find('a',0)->class .= ' maxbutton-preview';
		}
		
		if ($preview && $args["preview_part"] != 'full')
		{
 
 	 		if ($args["preview_part"] != 'normal')
 	 		{
				$domObj->find('a',0)->class .= ' hover'; 	
				$domObj = $cssParser->outputInline($domObj,'hover');

			}
			else
			{
				$domObj->find('a',0)->class .= ' normal'; 
				$domObj = $cssParser->outputInline($domObj);
			}

		}
		elseif ($this->load_css == 'footer') 
		{
			$css = $this->display_css(false, false); 
			do_action('mb-footer',$this->id, $css); 
			
			if (! $preview)
			{
				$js =  $this->display_js(false, true);
				do_action('mb-footer', $this->document_id, $js, 'js');
			}
		} elseif ($this->load_css == 'inline') 
		{
			if ($args["echo"]) 
				$this->display_css();
			else
				$output .= $this->display_css(false);
		}
		elseif ($this->load_css == 'element') // not possible to load both normal and hover to an element. 
		{
				$domObj->find('a',0)->class .= ' normal'; 
				$domObj = $this->cssParser->outputInline($domObj); 
				//$this->get_element_css($domObj, 'normal'); 
		}
			

 		$output .= $domObj->save();
		
		$output = apply_filters('mb-before-button-output', $output); 
		maxButtons::buttonDone(array("button_id" => $this->id, "document_id" => $this->document_id) );	
		
		maxUtils::endTime('button-display-'. $this->id);
						
		if ($args["echo"])
			echo $output; 
		else
			return $output; 

	}	
	/* Function used to map field id's to display for Frontend Javascript
	
		This function bundles all defined fields into a json encoded variable. This is used for the frontend javascript functions in the 
		administrator area like colorpickers and real-time updating of the button preview
	*/
	public function display_field_map()
	{
		$map = array(); 
		foreach($this->blocks as $block)
		{
			$map = $block->map_fields($map); 
		}
		//$map = apply_filters("mb-field-map",$map); 
		

		echo "<script language='javascript'>"; 	
				echo "var buttonFieldMap = '" . json_encode($map) . "';" ;
		echo "</script>";
	
	}
	
	/* Write parsed CSS to output. 
 	   @param echo Default true. When true, outputs directly, otherwise returns output string 
 	   @param style_tag Default true. When true, outputs a html <style> tags around the output.
 	*/
	public function display_css($echo = true, $style_tag = true)
	{
		$output = ''; 
		
		if ($style_tag)
			$output .=  "<style type='text/css'>";
		
			$output .= $this->parsed_css;
			
		if ($style_tag)
			$output .= "</style>";
		
 
		if ($echo) echo $output; 
		else return $output; 
		
	}
	
	/* Output Parsed Javascripting */
	public function display_js($echo = true, $tag = true)
	{
		$output = '';

		if (count($this->button_js) == 0) 
			return; // no output, holiday
		
		if ($tag) 
		{
			$output .= "<script type='text/javascript'> "; 
			$output .= " if (typeof maxButton" . $this->document_id . " == 'undefined') { ";
			$output .= " function maxButton" . $this->document_id . "() { ";
		}
		
		foreach($this->button_js as $index => $code) 
		{
			$output .= $code; 
		
		}
		
		if ($tag) 
		{
			$output .= " } 
						} 
				window.onload = maxButton" . $this->document_id . "();	
				</script>		"; 
		}

		if ($echo) echo $output; 
		else return $output; 
	}


	/* Makes a copy of the current buttons. 
	
	   The button to be copied -must- be loaded and set	
	*/
	function copy() 
	{		
		$this->id = 0; 
		$data = $this->data;
		$data["name"] = $this->name;
		
		return $this->update($data);		
	}
	/*  Change the publication status of the button.
	
	*/
	function setStatus($status = "publish") 
	{
		$data = $this->data; 
		$data["status"] = sanitize_text_field($status); 

		return $this->update($data); 
			
	}	
	/* Remove the button from database */ 
	public function delete($id) 
	{
		global $wpdb;
		$wpdb->query($wpdb->prepare("DELETE FROM " . maxUtils::get_table_name() . " WHERE id = %d", $id));
	}
		
	/* Save changes to the button 
		
		Updates or saves the button. Existing buttons must load their data and be set -first- or lose all not-passed data. 
		
	   @param post Post data in field - value format (flat $_POST array)
	   @param boolean savedb if false do not save to database 
	*/
	public function save($post, $savedb = true)
	{
 		$post = stripslashes_deep($post); // don't multiply slashes please.
		//$data = apply_filters('mb-save-fields',$this->data, $post); 
 		$data = $this->data;
 
 		foreach($this->blocks as $block) 
 		{
 			$data = $block->save_fields($data, $post); 
 		}
 
		if (! $savedb ) return $data; 
		return $this->update($data); // save to db. 
	
	}
	
	/* Updates the button data to the database. Adds a button if it doesn't exist */
	public function update($data) 
	{		
		global $wpdb; 
		$return = false;  
		
		$fields = array(); 
		foreach($this->blocks as $block)
		{
			$block_name = $block->get_name(); 
			
			if (isset($data[$block_name])) 
			{
				$blockData = $data[$block_name]; 
				$fields[$block_name] = json_encode($blockData);
			}
		}	
 		if (isset($data["name"])) {  // other fields. 
 			$fields["name"] = $data["name"]; 
 		}
 		if (isset($data["status"])) {
 			$fields["status"] = $data["status"]; 
 		}
 
 
		$where = array('id' => $this->id );
		if ($this->id > 0) 
		{
			$where = array('id' => $this->id);
			$where_format = array('%d');
			$result = $wpdb->update(maxUtils::get_table_name(), $fields, $where, null, $where_format);
			$return = true;
		}
		else
		{
 			$fields['created'] = current_time('mysql',1); 

			$result = $wpdb->insert(maxUtils::get_table_name(), $fields);
			$id = $wpdb->insert_id;

 			$this->id = $id;
 			$return = $id; 
		
		}
		
 
		if ($result === false)
		{
			$error = "Database error " . $wpdb->last_error;
			MB()->add_notice('error', $error); 
		 	$install = MB()->getClass("install"); 
			$install::create_database_table(); // run dbdelta to try and fix.
			
		}
		
 		// update the cache 
 		$this->cache = ''; // empty cache 
 		$result = $this->set($this->id); // set the newest values
 		
 		if (! $result ) return false;  
 		
 		$this->display(array("echo" => false, "load_css" => "element")); // do display routing to compile.
 		$css = $this->parsed_css; 		
 		$this->update_cache($css);		

 		return $return;
	}
	
	/* Updates the CSS cache. */
	public function update_cache($css)
	{
		$return = false;
		global $wpdb; 
		
		if ($this->id > 0) 
		{
			$fields = array("cache" => $css); 
			$where = array('id' => $this->id);
			$where_format = array('%d');
			$wpdb->update(maxUtils::get_table_name(), $fields, $where, null, $where_format);
			$return = true;
		
		}
		return $return; 
	}
	
	// Resets all of the button caches.
	public function reset_cache()
	{
		global $wpdb;
		$fields = array("cache" => null); 
		$where = array(1 => 1);
		//$where_format = array('%d');
		$sql = "UPDATE " . maxUtils::get_table_name() . " SET cache = NULL "; 
		$wpdb->query($sql);
 
	}

	
	/* Display button via shortcode
	
	Function that accepts WP shortcode arguments and displays or returns a button
	
	@param $atts array Shortcode Atts 
	@return string HTML presentation of button
	
	*/
	public function shortcode($atts)
	{  		 			
		extract(shortcode_atts(array(
				'id' => '',
				'name' => '',
				'text' => '',
				'url' => '',
				'linktitle' => '', 
				'window' => '',
				'nofollow' => '',
				'nocache' => false, 
				'style' => 'footer', 
				'exclude' => ''

			), $atts));	

		$button_id = $id; 
		$button_name = $name;
 
		if ($button_id > 0) 
			$result = $this->set($button_id); 
		elseif ($button_name != '') 
			$result = $this->set(0, $button_name); 
		else return; // no button id / name
		 
		/* Shortcode cache control
		
		If true the button CSS will be recompiled again. If false the plugin will check the cache for CSS declarations. Set to true if anything is interrupting the caching mechanism. Please note, recompiling causes some load times!
		
		@param boolean $nocache True / False 
		*/  
		$compile = apply_filters("mb/shortcode/nocache", $nocache); 

		if (! $result) 
			return; // shortcode doesn't exist
			
			// If we're not in the admin and the button is in the trash, just return nothing
			if (!is_admin() && $this->status == 'trash') {
				return '';
			}
		// Check to handle excludes
		if ("{$exclude}" != '') {
			global $post;
			
			// Don't render the button if excluded from the current post/page
			$exclude = explode(',', "{$exclude}");
			if (in_array($post->ID, $exclude)) {
				return '';
			}
		}
		
		// Override shortcode options comparing to default button data.
		$overrides = false; 
		if ($text != '') 
		{ 
			$this->data["text"]["text"] = $text; 
			$overrides = true;
		}  
		if ($url != '') 
		{

			$this->data["basic"]["url"]  = $url; 
			//$compile = true; // css change forces recompile
			$overrides = true;
		}
		if ($window != '' && $window =='new') 
		{
			$this->data["basic"]["new_window"] = 1;  
			$overrides = true;
		}
		elseif ($window != '' && $window == 'same') 
		{
			$this->data["basic"]["new_window"] = 0;  
			$overrides = true;		
		}
		
		if ($nofollow != '' && $nofollow == 'true') 
		{
			$this->data["basic"]["nofollow"] = 1; 
			$overrides = true;
		}
		
		if ($linktitle != '') 
		{
			$this->data['basic']['link_title'] = $linktitle; 
			$overrides = true;
		}

		switch($style)
		{
			case "inline": 
				$load_css = 'inline'; 
			break;
			default:
				$load_css = 'footer'; 
			break;
		}		

		// allow for more flexible changes and data manipulation.
		$data = $this->data;
		$this->data = $this->shortcode_overrides($this->data, $atts); 
		$this->data = apply_filters('mb/shortcode/data', $this->data, $atts); 
		
/*		if ($data !== $this->data) 
		{
			$overrides = true; 
		}
*/		
/*		if ($overrides)
		{
			do_action('mb-data-load', $this->data);
		}
*/		
		// if there are no reasons not to display; display
		$args = array("echo" => false, 
					  "load_css" => $load_css, 
					  "compile" => $compile, 
				);
		$args = apply_filters('mb_shortcode_display_args', $args); 
	
				
		$output = $this->display($args);
	 
		return $output;
		
	}
	
	
	public function shortcode_overrides($data, $atts)
	{
		return $data;
	}
	
} // class

