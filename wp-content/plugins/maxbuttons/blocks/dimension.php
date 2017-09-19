<?php 
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
$blockClass["dimension"] = "dimensionBlock";
$blockOrder[20][] = "dimension";

class dimensionBlock extends maxBlock
{
	protected $blockname = "dimension"; 
	protected $fields = array("button_width" => array("default" => '160px', 
										"css" => "width", 
										), 
							  "button_height" => array("default" => '50px', 
							  			"css" => "height")
							  ); 
	
	public function parse_css($css, $mode = 'normal') 
	{
		$css = parent::parse_css($css, $mode); 

		if (! isset($css["maxbutton"]["normal"]["width"])) return $css; 
		
		// do not allow zero's. 
		if (isset($css["maxbutton"]["normal"]["width"]) && ($css["maxbutton"]["normal"]["width"] == 0 || $css["maxbutton"]["normal"]["width"] == '') )
			unset($css["maxbutton"]["normal"]["width"]);

		if (isset($css["maxbutton"]["normal"]["height"]) && ($css["maxbutton"]["normal"]["height"] == 0 ||  $css["maxbutton"]["normal"]["height"] == '') )
			unset($css["maxbutton"]["normal"]["height"]);
			
		
	/*	if ($css["normal"]["width"] > maxbuttons_strip_px(0) || $css["normal"]["height"] > maxbuttons_strip_px(0)) 
		{
			$css["normal"]["display"] = "inline-block"; 
		}
	*/
	
		return $css;
	}

	public function map_fields($map)
	{
		$map = parent::map_fields($map);
 
		$map["button_width"]["func"] = "updateDimension"; 
		$map["button_height"]["func"] = "updateDimension"; 

		return $map; 
 
	}
	

	public function admin_fields()
	{
		return;
		
		$data = $this->data[$this->blockname]; 
 
		foreach($this->fields as $field => $options)
		{		
 	 	    $default = (isset($options["default"])) ? $options["default"] : ''; 
			${$field} = (isset($data[$field])) ? $data[$field] : $default;
			${$field  . "_default"} = $default; 
		}
	?>
	<div class="mb_tab option-container">
		<div class="title"><?php _e('Dimensions', 'maxbuttons') ?></div>
		<div class="inside">
			<div class="option-design">
			<div class="label"><?php _e('Button Width', 'maxbuttons') ?></div>
			<div class="input"><input class="tiny-nopad" type="text" id="button_width" name="button_width" value="<?php echo maxUtils::strip_px($button_width) ?>" />px</div>
			<div class="clear"></div>
			</div>
			<div class="option-design">
			<div class="label"><?php _e('Button Height', 'maxbuttons') ?></div>
			<div class="input"><input class="tiny-nopad" type="text" id="button_height" name="button_height" value="<?php echo maxUtils::strip_px($button_height) ?>" />px</div>
			<div class="clear"></div>
			</div>
		</div>
	</div>
<?php
	} // admin_fields
	
	

} // class 

?>
