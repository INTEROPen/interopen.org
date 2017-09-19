<?php 
namespace maxButtons; 
defined('ABSPATH') or die('No direct access permitted');

abstract class collectionBlock
{
	protected $data = array();
	protected $collection = null; 
	protected $fields = array(); 
	protected $fielddata = array();
	protected $blockname; 
	protected $is_preview = false; 
	
	
	
	function setCollection($collection)
	{
		$this->collection = $collection;
	}
	
	function set($data)
	{
		//$blockname = $this->blockname; 

		if (! isset($data[$this->blockname])) 
			$blockdata = array(); 
		else
			$blockdata = $data[$this->blockname]; 
		
		
		foreach($this->fields as $field => $options)
		{	
				if ($field == 'multifields')  // multifields can't set default since amount is unknown
				{
					$mf_id = $options["id"]; 
					$mf_fields = $options["fields"]; 
					if (isset($blockdata[$mf_id])) 
					{
						$this->data[$mf_id] = $blockdata[$mf_id]; // why not. 
					}
				}
				else
				{	
					if (isset($blockdata[$field])) 
						$this->data[$field] = $blockdata[$field]; 
					elseif (isset($options["default"])) 
						$this->data[$field] = $options["default"]; 
				}				
		}
		
		return true; 
	
	}
	
	// get the block data
	function get() 
	{
		return $this->data;
	
	}
	
	function setPreview($preview = true)
	{
		$this->is_preview = $preview; 
	
	}
	
	/* Save fields on a per block data 
	
	   Post data is sent unfiltered so sanitization must be done here!
	*/
	function save_fields($data, $post)
	{
		$blockdata = array(); 
		
		//if (isset($this->data[$this->blockname])) 
			$blockdata = $this->data;

		foreach($this->fields as $field => $options) 
		{	

			if ($field == "multifields") // standardize multi fields
			{
				$mf_id = $options["id"]; 
				$mf_fields = $options["fields"]; 
 
				$multidata = array(); 
								
				if (isset($post[$mf_id])) // the collection with the id's. 
				{
					$i = 0; // id's might be duplicate i.e. two similar buttons.
					foreach ($post[$mf_id] as $id) // id is button-id
					{
 
							foreach($mf_fields as $mf_field => $options)
							{
								$default = (isset($options["default"])) ? $options["default"] : ''; 		
						// POST[ field_name - $id ] 
					//
				$multidata[$i][$id][$mf_field] = isset($post[$mf_field . "-" . $id . "-" . $i ]) ? $post[$mf_field . "-" . $id . "-" . $i] : $default;  
						 	}
						 $i++;
					}
				}
				$blockdata[$mf_id] = $multidata;
			}
			else
			{
				$default = (isset($options["default"])) ? $options["default"] : ''; 
				// stripslashes since the WP post var adds them. 
				$blockdata[$field] = (isset($post[$field])) ? stripslashes(sanitize_text_field($post[$field])) : $default;
			}	
		}
		

		$data[$this->blockname] = $blockdata;
		return $data;
	
	}
	
	function parse($domObj, $args)
	{

		return $domObj; // nothing to do by default
	}	
	
	function parseButtons($buttons)
	{
		return $buttons;
	}
	
	// Adepted from block class - maxbuttons
	function parseCSS($css, $args)
	{
		$data = $this->data;
		
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
				
				if ( strpos($field_data["default"],"px") && ! strpos($value,"px"))
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
	
	// default function
	function parseJS($js)
	{
		return $js;
	}

	// for the preview
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
		
	abstract function admin_fields();
	
} // class
