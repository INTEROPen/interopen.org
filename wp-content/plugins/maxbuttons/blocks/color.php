<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

$blockClass["color"] = "colorBlock"; 
$blockOrder[10][] = "color"; 


class colorBlock extends maxBlock 
{
	protected $blockname = "color"; 
	protected $fields = array("text_color" => array("default" => "#ffffff",
													"css" => "color",
													"csspart" => "mb-text"
													),
						"text_shadow_color" => array("default" => "#505ac7",
													"css" => "text-shadow-color",
													"csspart" => "mb-text"
													),
						"gradient_start_color" => array("default" => "#505ac7",
													"css" => "gradient-start-color"
													),
						"gradient_end_color" => array("default" => "#505ac7",
													"css" => "gradient-end-color"
													), 
						"border_color" => array("default" => "#505ac7",
													"css" => "border-color"
												    ),
						"box_shadow_color" => array("default" => "#333333",
													"css" => "box-shadow-color"
													),
						"text_color_hover" => array("default" => "#505ac7",
													"css" => "color", 
													"csspart" => "mb-text",
													"csspseudo" => "hover", 
													),
						"text_shadow_color_hover" => array("default" => "#333333",
													"css" => "text-shadow-color", 
													"csspart" => "mb-text",
													"csspseudo" => "hover"),
													
						"gradient_start_color_hover" => array("default" => "#ffffff",
													"css" => "gradient-start-color", 
													"csspseudo" => "hover"),
													
						"gradient_end_color_hover" => array("default" => "#ffffff",
													"css" => "gradient-end-color", 
													"csspseudo" => "hover"
													),
													
						"border_color_hover" => array("default" => "#505ac7",
													"css" => "border-color", 
													"csspseudo" => "hover"),	
																								
 						"box_shadow_color_hover" => array("default" => "#333333",
													"css" => "box-shadow-color", 
													"csspseudo" => "hover"),
 						
 						"icon_color" 			 => array( "default" => '#ffffff', 
													"css" => "color", 
													"csspart" => "fa"), 
						
						"icon_color_hover"		 => array( "default" => '#2b469e', 
													"css" => "color", 
													"csspart" => "fa",
													"csspseudo" => "hover",
													),
						); 
	
	public function parse_css($css, $mode = 'normal') { 
		
		$data = $this->data[$this->blockname]; 
 		foreach($this->fields as $field => $field_data) // ensure colors have the correct format
		{
			$value = isset($data[$field]) ? $data[$field] : false; 
			if (! $value) 
				continue; // no color, no check. 
				
			if (substr($value,0,1) !== '#') 
			{
				$value = '#' . $value;
			}
			$this->data[$this->blockname][$field] = $value;
		}
	
		return parent::parse_css($css, $mode);
	}
	
	
	public function admin_fields()  {}
} // class


