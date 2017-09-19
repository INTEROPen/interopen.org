<?php
namespace MaxButtons; 
defined('ABSPATH') or die('No direct access permitted');

/** A block is a combination of related settings. 
*
*  Blocks are grouped and put into the same Database table row. This way related data, it's executing, display and other decision
*   making is seperate from other blocks improving realiability and readability of the code.
*/
	use MaxButtons\maxBlocks  as maxBlocks;
	use MaxButtons\maxField   as maxField;
	
abstract class maxBlock
{
	protected $data = array();  
	
	/** Block constructor 
	*
	* Constructor for a button block. Hooks up all needed filters and inits data for a block. 
	* 
	*/
	function __construct($priority = 10)
	{
		
		// filters for save_post, display etc. Buttons class will the filters. 
		//add_filter('mb-save-fields', array($this, 'save_fields'),10,2); 
		//add_action('mb-admin-fields', array($this,'admin_fields' ) );
		//add_action('mb-data-load', array($this,'set') );
		
		//add_filter('mb-parse-button', array($this, 'parse_button'),10,2 ); 
		//add_filter('mb-js-blocks', array($this, 'parse_js'), 10, 2); 
		//add_filter('mb-parse-element-preview', array($this,'parse_element'), 10,2); 
		
		//add_filter('mb-css-blocks', array($this, 'parse_css'),10,2 ); 
		//add_filter('mb-field-map', array($this, 'map_fields') ); 
		

		$this->fields = apply_filters($this->blockname. "-block-fields",$this->fields); 
		$this->data[$this->blockname] = array(); //empty init
		
	}
 
	/** Save fields runs the POST variable through all blocks
	*
	*  Taking the post variable from the form, the function will attach the submitted data to the block - field logic and 
	*   return a data object to save to the Database. If no value is submitted, the default will be loaded. 
	*   
	*	@param $data Array Data in blockname - field logic format
	*	@param $post Array $_POST style data
	*	
	*	@return $data Array
	*/
	public function save_fields($data, $post)
	{	
		$block = isset($this->data[$this->blockname]) ? $this->data[$this->blockname] : array(); 

		foreach($this->fields as $field => $options) 
		{
			$default = (isset($options["default"])) ? $options["default"] : ''; 
			if (is_string($default) && strpos($default,"px") !== false)
				$block[$field] = (isset($post[$field]) ) ? intval($post[$field]) : $default; 
			elseif( isset($post[$field]) && is_array($post[$field])) 
			{
				$block[$field] = $post[$field];
			}
			else	
				$block[$field] = (isset($post[$field])) ? sanitize_text_field($post[$field]) : $default; 
		}

		$data[$this->blockname] = $block; 
		return $data;	
 
	}
	
	/** Return fields of current block
	* 
	* 	Will return fields of current block only
	* @return Array $fields
	*/
	public function get_fields()
	{
		return $this->fields;
	}
	
	/** Returns Blockname of current block
	* 
	*  
	* @return $string | boolean Name of the block, if set, otherwise false
	*/
	public function get_name() 
	{
		if (isset($this->blockname)) 
			return $this->blockname; 
			
		return false;
	}
	
	
	/* Display Block admin interface
	*	
	*   Writes admin interface to output.
	*  @abstract
	*/
	abstract public function admin_fields();
	
	/** Parse HTML portion of button
	*
	*   This filter is passed through to modify the HTML parts of the button. 
	*   
	*   Note: When changing parts of the DomObj writing new tags / DOM to elements, it's needed to regenerate the Object.
	*   
	*   @param $button DomObj SimpleDOMObject
	*   @param $mode String[normal|preview] Flag to check if loading in preview 
	*   
	*   @return DomObj
	*/
	public function parse_button($button, $mode) { return $button;  } 
	
	/* Parse CSS of the button
	*
	*	This function will go through the blocks, matching the $css definitions of a fields and putting them in the 
	*	correct tags ( partly using csspart ) . 
	*	
	*	@param $css Array [normal|hover|other][cssline] = css declaration
	*	@param $mode String [normal|preview] 
	*	
	*	@return $css Array 
	*/
	public function parse_css($css, $mode = 'normal') { 

		$data = $this->data[$this->blockname]; 

 		// get all fields from this block
 		foreach($this->fields as $field => $field_data)
		{
			// get cssparts, can be comma-seperated value
			$csspart = (isset($field_data["csspart"])) ? explode(",",$field_data["csspart"]) : array('maxbutton'); 
			$csspseudo = (isset($field_data["csspseudo"])) ? explode(",", $field_data["csspseudo"]) : 'normal'; 
			
			// if this field has a css property
			if (isset($field_data["css"])) 
			{
				// get the property value from the data
				$value = isset($data[$field]) ? $data[$field] : ''; 
				$value = str_replace(array(";"), '', $value);  //sanitize
				
				if (isset($field_data["default"]) && strpos($field_data["default"],"px") && ! strpos($value,"px"))
				{
					if ($value == '') $value = 0; // pixel values, no empty but 0 
					$value .= "px"; 
				}
 				if (isset($data[$field])) 
 				{
	 				 foreach($csspart as $part)
	 				 {
		 					if (is_array($csspseudo)) 
		 					{
		 						foreach($csspseudo as $pseudo)
		 							$css[$part][$pseudo][$field_data["css"]] = $value ; 
		 					}
		 					else
								$css[$part][$csspseudo][$field_data["css"]] = $value ;
					  }
				}
			}
		
		}	

		return $css; 		
	}
	
	/* Ability to output custom JS for each button */ 
	public function parse_js($js, $mode = 'normal')
	{
		return $js; 
	}
	
	
	/** Map the Block fields  
	*
	*	This function will take the field name and link it to the defined CSS definition to use in providing the live preview in the 
	*	button editor. I.e. a field with name x will be linked to CSS-property X . Or to a custom Javascript function. 
	*	
	*	@param $map Array [$field_id][css|attr|func|] = property/function
	*	
	*	@return Array
	*/
	public function map_fields($map) 
	{
		foreach($this->fields as $field => $field_data)
		{
 			if (isset($field_data["css"])) 
			{
				$cssdef = $field_data["css"]; 
				$multidef = explode('-',$cssdef); 
				if ( count($multidef) > 1)
				{
					$cssdef = ""; 
 					for($i = 0; $i < count($multidef); $i++)
 					{	
 						if ($i == 0)	
 							$cssdef .= $multidef[$i];
 						else
 							$cssdef .= ucfirst($multidef[$i]);   
 						//$multidef[$i] . ucfirst($multidef[1]); 
 					}
				}					
				$map[$field]["css"] = $cssdef; 
				if ( isset($field_data["default"]) && strpos($field_data["default"],"px") != false )
					$map[$field]["css_unit"] = 'px'; 
		
			}
			if (isset($field_data["csspart"])) 
				$map[$field]["csspart"] = $field_data["csspart"];		
		}
		return $map; 
		
	}
	
	/** Sets the data
	*
	*	This action is called from button class when data is pulled from the database and populates the dataArray to all blocks
	*	
	*/
	function set($dataArray)
	{
 
		$this->data = $dataArray;
	}
 
}
?>
