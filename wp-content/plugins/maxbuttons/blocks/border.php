<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
$blockClass["border"] = "borderBlock"; 
$blockOrder[30][] = "border"; 

class borderBlock extends maxBlock 
{
	protected $blockname = "border"; 
	protected $fields = array("radius_top_left" => 
								array("default" => "4px",
									  "css" => "border-top-left-radius"									
									),
						"radius_top_right" => array("default" => "4px",
											 "css" => "border-top-right-radius"
											 ),
						"radius_bottom_left" => array("default" => "4px",
													 "css" => "border-bottom-left-radius"
											),
						"radius_bottom_right" => array("default" => "4px",
													 "css" => "border-bottom-right-radius"
													 ), 
						"border_style" => array("default" => "solid",
												"css" => "border-style"
												),
						"border_width" => array("default" => "2px",
												 "css" => "border-width"
												 ),
						"box_shadow_offset_left" => array("default" => "0px",
												 	"css" => "box-shadow-offset-left",
												 	"csspseudo" => "normal,hover"
												 ),
						"box_shadow_offset_top" => array("default" => "0px",
													"css" => "box-shadow-offset-top",
													"csspseudo" => "normal,hover"),
						"box_shadow_width" => array("default" => "2px",
													"css" => "box-shadow-width",
													"csspseudo" => "normal,hover"),
						'box_shadow_spread' => array('default' => '0px', 
													'css' => 'box-shadow-spread', 
													'csspseudo' => 'normal,hover'),
 
						); 
	

	function __construct()
	{
		parent::__construct();
		$this->fields = apply_filters($this->blockname. "-block-fields",$this->fields); 
		$this->data[$this->blockname] = array(); //empty init		
	}
	
	public function map_fields($map)
	{
		$map = parent::map_fields($map); 
		$map["box_shadow_offset_left"]["func"] = "updateBoxShadow"; 
		$map["box_shadow_offset_top"]["func"] = "updateBoxShadow"; 
		$map["box_shadow_width"]["func"] = "updateBoxShadow"; 
		$map["box_shadow_spread"]["func"] = "updateBoxShadow"; 
		
		$map["radius_top_left"]["func"] = "updateRadius"; 
		$map["radius_top_right"]["func"] = "updateRadius"; 
		$map["radius_bottom_left"]["func"] = "updateRadius"; 
		$map["radius_bottom_right"]["func"] = "updateRadius"; 
		
		
		return $map; 
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
		
		 $maxbuttons_border_styles = array(
			'' => '',
			'dashed' => __('dashed','maxbuttons'),
			'dotted' => __('dotted','maxbuttons'),
			'double' => __('double','maxbuttons'),
			'groove' => __('groove','maxbuttons'),
			'inset'  => __('inset','maxbuttons'),
			'outset' => __('outset','maxbuttons'),
			'ridge'  => __('ridge','maxbuttons'),
			'solid'  => __('solid','maxbuttons')
		);
?>	
	<div class="option-container mb_tab">
				<div class="title"><?php _e('Border', 'maxbuttons') ?></div>
				<div class="inside">
				
				
				<?php 
					// Spacer
					$fspacer = new maxField('spacer'); 
					$fspacer->label = __('Radius','maxbuttons');
					$fspacer->name = 'radius';  
					$fspacer->output('start'); 
									
				// Radius left top
				$radius_tleft = new maxField('number'); 
				//$radius_tleft->label = __('Top Left', 'maxbuttons');
				//$radius_tleft->label = '<span class="dashicons dashicons-arrow-left-alt2 rotate-right"></span>'; 
				$radius_tleft->value = maxUtils::strip_px(maxBlocks::getValue('radius_top_left')); 
				$radius_tleft->id = 'radius_top_left'; 
				$radius_tleft->name = $radius_tleft->id; 
				$radius_tleft->min = 0;
				$radius_tleft->inputclass = 'tiny'; 
				$radius_tleft->publish = false; 
				$rtl = $radius_tleft->output(''); 				
				
				// Radius right top
				$radius_tright = new maxField('number'); 
				//$radius_tright->label = __('Top Right', 'maxbuttons'); 
				//$radius_tright->label = '<span class="dashicons dashicons-arrow-right-alt2 rotate-left"></span>'; 
				$radius_tright->value = maxUtils::strip_px(maxBlocks::getValue('radius_top_right')); 
				$radius_tright->id = 'radius_top_right'; 
				$radius_tright->name = $radius_tright->id; 
				$radius_tright->min = 0;
				$radius_tright->inputclass = 'tiny'; 
				$radius_tright->publish = false; 
				$rtr = $radius_tright->output('', ''); 				

					
				// Radius bottom left
				$radius_bleft = new maxField('number'); 
				//$radius_bleft->label = __('Bottom Left', 'maxbuttons');
				//$radius_bleft->label = '<span class="dashicons dashicons-arrow-left-alt2 rotate-left"></span>'; 
				$radius_bleft->value = maxUtils::strip_px(maxBlocks::getValue('radius_bottom_left')); 
				$radius_bleft->id = 'radius_bottom_left'; 
				$radius_bleft->name = $radius_bleft->id; 
				$radius_bleft->min = 0;
				$radius_bleft->inputclass = 'tiny'; 
				$radius_bleft->publish = false; 
				$rbl = $radius_bleft->output(''); 	
				
				// Radius bottom right
				$radius_bright = new maxField('number'); 
				//$radius_bright->label = __('Bottom Right', 'maxbuttons'); 
				//$radius_bright->label = '<span class="dashicons dashicons-arrow-right-alt2 rotate-right"></span>'; 
				$radius_bright->value = maxUtils::strip_px(maxBlocks::getValue('radius_bottom_right')); 
				$radius_bright->id = 'radius_bottom_right'; 
				$radius_bright->name = $radius_bright->id;
				$radius_bright->min = 0; 
				$radius_bright->inputclass = 'tiny'; 
				$radius_bright->publish = false; 
				$rbr = $radius_bright->output('', ''); 				
				
				// If all same, lock the corners for simultanious change. 
				if ($radius_tleft->value == $radius_tright->value && 
					$radius_tright->value == $radius_bleft->value && 
					$radius_bleft->value = $radius_bright->value) 
				{
					$lock = 'lock'; 
				}
				else
					$lock = 'unlock';
				
				$radius = new maxField('radius');
				$radius->radius_tl = $rtl; 
				$radius->label_tl = __('Top Left','maxbuttons'); 
				$radius->radius_tr = $rtr; 
				$radius->label_tr = __('Top Right','maxbuttons'); 
				$radius->radius_bl = $rbl; 
				$radius->label_bl = __('Bottom Left','maxbuttons'); 
				$radius->radius_br = $rbr;
				$radius->label_br = __('Bottom Right','maxbuttons');
				$radius->lock = $lock;
				$radius->output('','end');
				
				// Border style
				$bstyle = new maxField('generic'); 
 				$bstyle->label = __('Style','maxbuttons'); 
 				$bstyle->name = 'border_style'; 
 				$bstyle->id = $bstyle->name; 
 				$bstyle->value= maxBlocks::getValue('border_style'); 
 				$bstyle->setDefault(maxBlocks::getDefault('border_style')); 
 				$bstyle->content = maxUtils::selectify($bstyle->name, $maxbuttons_border_styles, $bstyle->value); 
 				$bstyle->output('start', 'end'); 

				// Border width
				$bwidth = new maxField('number'); 
				$bwidth->label = __('Width', 'maxbuttons'); 
				$bwidth->name = 'border_width'; 
				$bwidth->id = $bwidth->name; 
				$bwidth->value = maxUtils::strip_px( maxBlocks::getValue('border_width') ); 
				$bwidth->min = 0;
				$bwidth->inputclass = 'tiny'; 
				$bwidth->output('start','end');

				// Border Color
				$bcolor = new maxField('color'); 
				$bcolor->id = 'border_color'; 
				$bcolor->name = $bcolor->id; 
				$bcolor->value = maxBlocks::getColorValue('border_color'); 
				$bcolor->label = __('Border Color','maxbuttons'); 
				$bcolor->copycolor = true; 
 				$bcolor->bindto = 'border_color_hover'; 
				$bcolor->copypos = 'right'; 
				$bcolor->output('start'); 
				
				// Border Color Hover 
				$bcolor_hover = new maxField('color'); 
				$bcolor_hover->id = 'border_color_hover'; 
				$bcolor_hover->name = $bcolor_hover->id; 
				$bcolor_hover->value = maxBlocks::getColorValue('border_color_hover'); 
				$bcolor_hover->label = __('Hover','maxbuttons'); 
				$bcolor_hover->copycolor = true;
				$bcolor_hover->bindto = 'border_color'; 
				$bcolor_hover->copypos = 'left'; 
				$bcolor_hover->output('','end'); 	

				// Shadow offset left
				$bshadow = new maxField('number'); 
				$bshadow->label = __('Shadow Offset Left','maxbuttons'); 
				$bshadow->name = 'box_shadow_offset_left'; 
				$bshadow->id = $bshadow->name; 
				$bshadow->value = maxUtils::strip_px( maxBlocks::getValue('box_shadow_offset_left') ); 
				$bshadow->inputclass = 'tiny'; 
				$bshadow->output('start'); 

				// Shadow offset top 
				$bshadow = new maxField('number'); 
				$bshadow->label = __('Shadow Offset Top','maxbuttons'); 
				$bshadow->name = 'box_shadow_offset_top'; 
				$bshadow->id = $bshadow->name; 
				$bshadow->value = maxUtils::strip_px( maxBlocks::getValue('box_shadow_offset_top') ); 
				$bshadow->inputclass = 'tiny'; 
				$bshadow->output('','end'); 

				// Shadow width
				$bshadow = new maxField('number'); 
				$bshadow->label = __('Shadow Blur','maxbuttons'); 
				$bshadow->name = 'box_shadow_width'; 
				$bshadow->id = $bshadow->name; 
				$bshadow->value = maxUtils::strip_px( maxBlocks::getValue('box_shadow_width') ); 
				$bshadow->inputclass = 'tiny'; 
				$bshadow->output('start',''); 

				$bspread = new maxField('number'); 
				$bspread->label = __('Shadow Spread', 'maxbuttons'); 
				$bspread->value = maxUtils::strip_px(maxBlocks::getValue('box_shadow_spread')); 
				$bspread->id = 'box_shadow_spread'; 
				$bspread->name = $bspread->id; 
				$bspread->inputclass = 'tiny'; 
				$bspread->output('','end');
						
				// Border Shadow Color
				$scolor = new maxField('color'); 
				$scolor->id = 'box_shadow_color'; 
				$scolor->name = $scolor->id; 
				$scolor->value = maxBlocks::getColorValue('box_shadow_color'); 
				$scolor->label = __('Border Shadow Color','maxbuttons'); 
				$scolor->copycolor = true; 
				$scolor->bindto = 'box_shadow_color_hover'; 
				$scolor->copypos = 'right'; 
				$scolor->output('start'); 
				
				// Border Shadow Color Hover 
				$scolor_hover = new maxField('color'); 
				$scolor_hover->id = 'box_shadow_color_hover'; 
				$scolor_hover->name = $scolor_hover->id; 
				$scolor_hover->value = maxBlocks::getColorValue('box_shadow_color_hover'); 
				$scolor_hover->label = __('Hover','maxbuttons'); 
				$scolor_hover->copycolor = true;
				$scolor_hover->bindto = 'box_shadow_color'; 
				$scolor_hover->copypos = 'left'; 
				$scolor_hover->output('','end'); 	
				
				?>

				</div>
			</div>
<?php			
	} // admin fields
 
  } // class  

?>
