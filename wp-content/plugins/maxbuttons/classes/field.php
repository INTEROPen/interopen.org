<?php 
namespace MaxButtons; 
defined('ABSPATH') or die('No direct access permitted');

class maxField
{

	/* Static class variables */
	static $templates = ''; 
	static $position = 0; 

	/* Field data */ 
	public $id; 
	public $name; 
	public $value = '';
		
	/* Layout options */
	public $note; 
	public $label;
	public $title;  
	public $default;

	// Specific options */ 
	public $placeholder = ''; // text / textarea
	public $icon; // checkbox-icon
	public $checked = ''; // checkbox  / radio
	public $input_class = ''; // inputs
	public $before_input; // text
	public $content = ''; // generic / spacer
 	public $min;  // number
 	
 	/* Border radius */ 
 	public $radius_tl; 
 	public $radius_tr; 
 	public $radius_bl, $radius_br; 
 	
	//public 

	/* Template */	
	public $template; 
	public $main_class = 'option';  // row class - start template
	public $esc_function = 'esc_attr';  
	
	/* Publish brake */
	public $publish = true; 
	public $output = ''; 
	
	public function __construct($template = 'text', $args = array() ) 
	{
		self::$position++; 
		$this->template = $template; 
		
		foreach($args as $item => $value) 
		{
			$this->{$item} = $value;
		
		}
	}	
	
	static function setTemplates($templates) 
	{
		self::$templates = $templates; 
	
	}
	
	
	public function setDefault($default) 
	{
		$this->default = __('Default:','maxbuttons') . ' ' . $default; 
	
	}
 
 	/** Output field interface
 	* 
 	*	@param $start_tpl Prepend a template before this field ( e.g. row defition ) 
 	* 	@param $end_tpl   Append a template after this field
 	*/
 	
	public function output($start_tpl = '', $end_tpl = '') 
	{
		if ($this->esc_function) 
		{
			$this->value = call_user_func($this->esc_function, $this->value); 
		}
		
		$output = ''; 
		if ($start_tpl != '') 
		{
			$start_tpl = self::$templates[$start_tpl];
			$output .= simpleTemplate::parse($start_tpl['path'], $this);
		}

		$template = self::$templates[$this->template]; // template name; 		
		do_action('mb/editor/before-field-' . $this->id, $this);
		
		$output .= simpleTemplate::parse($template['path'], $this); 	
	

		if ($end_tpl != '') 
		{
			if (! is_array($end_tpl))
				$end_tpl = array($end_tpl);
				
			foreach($end_tpl as $tpl)
			{
				$tpl = self::$templates[$tpl];
				$output .= simpleTemplate::parse($tpl['path'], $this);
			}
		}
				
		if ($this->publish) 
			echo $output; 	
		do_action('mb/editor/after-field-'. $this->id); // hook for extra fields. 		

		$this->output =  $output;
		return $output; 
	}


}
