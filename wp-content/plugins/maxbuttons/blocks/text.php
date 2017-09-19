<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
$blockClass["text"] = "textBlock"; 
$blockOrder[50][] = "text"; 

class textBlock extends maxBlock 
{

	protected $blockname = "text"; 
	protected $fields = array(
	
  					  "text" =>   array("default" => '' ), 
						"font" => array("default" => "Tahoma", 
											  "css" => "font-family", 
											  "csspart" => 'mb-text'
											  ),
											  
						"font_size" => array("default" => "15px",
											  "css" => "font-size",
											  "csspart" => 'mb-text' ),
					/*	"font_size_unit" => array("default" => "em", 
											  "css" => "font_size_unit",
											  "csspart" => "mb-text",
											), */
						"text_align" => array(  
										"default" => "center",
										 "css" => "text-align",
										 "csspart" => "mb-text",
										 
										 ),
										 																	  
						"font_style" => array("default" => "normal",
											  "css" => "font-style",
											  "csspart" => 'mb-text'),
											  
						"font_weight" => array("default" => "normal", 
											  "css" => "font-weight",
											  "csspart" => 'mb-text'),
						"text_shadow_offset_left" => array("default" => "0px",
											  "css" => "text-shadow-left",
											  "csspart" => 'mb-text',
											  "csspseudo" => "normal,hover"
											  ),
											  
						"text_shadow_offset_top" => array("default" => "0px",
											  "css" => "text-shadow-top",
											  "csspart" => 'mb-text',
											  "csspseudo" => "normal,hover"),
						"text_shadow_width" => array("default" => "0px", 
											  "css" => "text-shadow-width",
											  "csspart" => 'mb-text',
											  "csspseudo" => "normal,hover"),

						"padding_top" => array("default" => "18px",
											   "css" => "padding-top",
											   "csspart" => "mb-text"),
						"padding_right" => array("default" => "0px",
												"css" => "padding-right",
											   "csspart" => "mb-text"),
						"padding_bottom" => array("default" => "0px",
												"css" => "padding-bottom",
											   "csspart" => "mb-text"),
						"padding_left" => array("default" => "0px",
												"css" => "padding-left",
											   "csspart" => "mb-text")
						); 
	

	function __construct()
	{
		parent::__construct();
		$this->fields["text"]["default"] = __("YOUR TEXT","maxbuttons"); 
 
	}

	public function map_fields($map)
	{
		$map = parent::map_fields($map);
		$map["text"]["func"] = "updateAnchorText"; 
		$map["text_shadow_offset_left"]["func"] = "updateTextShadow"; 
		$map["text_shadow_offset_top"]["func"] = "updateTextShadow"; 
		$map["text_shadow_width"]["func"] = "updateTextShadow"; 
		
		return $map; 
	}
	public function parse_css($css,  $mode = 'normal')
	{
		$css = parent::parse_css($css);
 
		// allow for font not to be set, but default to theme
		$font_size = isset($css["mb-text"]["normal"]["font-size"]) ? $css["mb-text"]["normal"]["font-size"] : $this->fields['font_size']['default']; 
		if ($font_size == 0 || $font_size == '0px')
			unset($css["mb-text"]["normal"]["font-size"]); 
			
		$css["mb-text"]["normal"]["line-height"] = "1em"; 
		$css["mb-text"]["normal"]["box-sizing"] = "border-box";  // default. 	
		$css["mb-text"]["normal"]["display"] = "block"; 
		$css['mb-text']['normal']['background-color'] = 'unset'; // prevent bg overwriting
		return $css; 
	}	
	public function parse_button($domObj, $mode = 'normal')
	{
		$data = $this->data[$this->blockname]; 
		$anchor = $domObj->find("a",0); 	
		
	 	if (isset($data["text"]) && $data["text"] != '' || $mode == 'preview') 
			$anchor->innertext = "<span class='mb-text'>" . do_shortcode($data["text"]) . "</span>"; 
		return $domObj; 
		
	}
			
	public function admin_fields() 
	{
		$data = $this->data[$this->blockname]; 
		foreach($this->fields as $field => $options)
		{		
 	 	    $default = (isset($options["default"])) ? $options["default"] : ''; 
			$$field = (isset($data[$field])) ? $data[$field] : $default;
			${$field  . "_default"} = $default; 
		}
 
?>
			<div class="mb_tab option-container">
				<div class="title"><?php _e('Text Shadow', 'maxbuttons') ?></div>
				<div class="inside text">
 				<?php
 					// Shadow offset left
 					$field_shadow = new maxField('number') ; 
					$field_shadow->label = __('Shadow Offset Left', 'maxbuttons'); 
					$field_shadow->value = maxUtils::strip_px(maxBlocks::getValue('text_shadow_offset_left')); 
					$field_shadow->id = 'text_shadow_offset_left'; 
					$field_shadow->name = $field_shadow->id; 
					$field_shadow->inputclass = 'tiny'; 
					$field_shadow->output('start'); 
					
					// Shadow offset top
 					$field_shadow = new maxField('number') ; 
					$field_shadow->label = __('Shadow Offset Top', 'maxbuttons'); 
					$field_shadow->value = maxUtils::strip_px(maxBlocks::getValue('text_shadow_offset_top')); 
					$field_shadow->id = 'text_shadow_offset_top'; 
					$field_shadow->name = $field_shadow->id; 
					$field_shadow->inputclass = 'tiny'; 
					$field_shadow->output('','end');

					// Shadow width
 					$field_shadow = new maxField('number') ; 
					$field_shadow->label = __('Shadow Blur', 'maxbuttons'); 
					$field_shadow->value = maxUtils::strip_px(maxBlocks::getValue('text_shadow_width')); 
					$field_shadow->id = 'text_shadow_width'; 
					$field_shadow->min = 0;
					$field_shadow->name = $field_shadow->id; 
					$field_shadow->inputclass = 'tiny'; 
					$field_shadow->output('start','end');
					
 					// Text Color
 					$fshadow = new maxField('color'); 
 					$fshadow->id = 'text_shadow_color'; 
 					$fshadow->name = $fshadow->id; 
 					$fshadow->value = maxBlocks::getColorValue('text_shadow_color'); 
 					$fshadow->label = __('Shadow Color','maxbuttons'); 
 					$fshadow->copycolor = true; 
 					$fshadow->bindto = 'text_shadow_color_hover'; 
 					$fshadow->copypos = 'right'; 
 					$fshadow->output('start'); 
 					
 					// Text Color Hover 
 					$fshadow_hover = new maxField('color'); 
 					$fshadow_hover->id = 'text_shadow_color_hover'; 
 					$fshadow_hover->name = $fshadow_hover->id; 
 					$fshadow_hover->value = maxBlocks::getColorValue('text_shadow_color_hover'); 
 					$fshadow_hover->label = __('Hover','maxbuttons'); 
 					$fshadow_hover->copycolor = true; 
 					$fshadow_hover->bindto = 'text_shadow_color'; 
 					$fshadow_hover->copypos = 'left'; 
 					$fshadow_hover->output('','end'); 														
				?>						

				</div>
			</div>
<?php } // admin fields  
	} // class 
	
?>
