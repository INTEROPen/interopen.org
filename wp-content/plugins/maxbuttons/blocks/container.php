<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
$blockClass["container"] = "containerBlock"; 
$blockOrder[70][] = "container";
 
use \simple_html_dom as simple_html_dom;

class containerBlock extends maxBlock 
{
	protected $blockname = "container"; 
	protected $fields = array("container_enabled" => array("default" => "0"),
						"container_center_div_wrap" => array("default" => "0"),
															  
						"container_width" => array("default" => "0px",
												   "css" => "width", 
												   "csspart" => "mb-container"),
						"container_margin_top" => array("default" => "0px",
													"css" => "margin-top",
													"csspart" => "mb-container"), 
						"container_margin_right" => array("default" => "0px",
												   "css" => "margin-right", 
												   "csspart" => "mb-container"),
						"container_margin_bottom" => array("default" => "0px",
												   "css" => "margin-bottom", 
												   "csspart" => "mb-container"),
						"container_margin_left" => array("default" => "0px",
												   "css" => "margin-left", 
												   "csspart" => "mb-container"),							
						"container_alignment" => array("default" => "",
												   "css" => "align", 
												   "csspart" => "mb-container"), 
						); 
	
 
	public function parse_button($domObj, $mode = 'normal')
	{
		$data = $this->data[$this->blockname]; 
		$id = $this->data["id"]; 

 		if ($mode == 'editor')
 			return $domObj; // in previews no container object
	
 
		if ($data["container_enabled"] == 1) 
		{
			$anchor = $domObj->find("a",0); 
			$anchor->outertext = "<span class='maxbutton-" . $id . "-container mb-container'>" . $anchor->outertext . "</span>";  

			
			if ($data["container_center_div_wrap"] == 1) // I heard you like wrapping... 
			{
				$anchor->outertext = "<span class='mb-center maxbutton-" . $id . "-center'>" . $anchor->outertext . "</span>"; 
	
			}
			// reload the dom model with new divs 
			$newhtml = $domObj->save(); 
			$domObj =  new simple_html_dom(); 
			$domObj->load($newhtml);
		}
		

		
		return $domObj;
	}

	public function parse_css($css, $mode = 'normal')
	{
		$css = parent::parse_css($css);
		$data = $this->data[$this->blockname]; 

		$csspart = 'mb-container'; 
		$csspseudo = 'normal';
		
		$css["mb-container"]["normal"]["display"] = "block"; 
		$css["mb-center"]["normal"]["display"] = "block"; 
		$css["mb-center"]["normal"]["text-align"] = "center"; 
		
		if (isset($css[$csspart][$csspseudo]["align"])) 
		{

			if ($css[$csspart][$csspseudo]["align"] != '')
			{ 	

				$stat = explode(":", $css[$csspart][$csspseudo]["align"]); 
				$css[$csspart][$csspseudo][ $stat[0] ] = $stat[1];
		 	}
		 	unset($css[$csspart][$csspseudo]["align"]); 
		}
		if ( isset($css[$csspart][$csspseudo]["width"]) && $data["container_width"] == 0)
		{
			unset($css[$csspart][$csspseudo]["width"]);
		}
		return $css; 
		
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
		 $maxbuttons_container_alignments = array(
	'' => '',
	'display: inline-block' => 'display: inline-block',
	'float: left' => 'float: left',
	'float: right' => 'float: right'
);

		$icon_url = MB()->get_plugin_url() . 'images/icons/' ; 	
 
?>		
	<div class="option-container mb_tab">
				<div class="title"><?php _e('Container', 'maxbuttons') ?></div>
				<div class="inside">
				<?php
				/*$fspacer = new maxField('spacer'); 
				$fspacer->label = __('Use Container', 'maxbuttons'); 
				$fspacer->name = '';  
				$fspacer->output('start'); 
					*/
				$u_container = new maxField('switch'); 
				$u_container->label = __('Use Container', 'maxbuttons'); 
				$u_container->name = 'container_enabled'; 
				$u_container->id = $u_container->name; 
				$u_container->value = 1; 
 
				$u_container->checked = checked( maxBlocks::getValue('container_enabled'), 1, false); 
				$u_container->output('start', 'end');

				$fspacer = new maxField('spacer'); 
				//$fspacer
				$fspacer->name = '';  
				$fspacer->output('start'); 
								
				$wrap_cont = new maxField('switch'); 
				$wrap_cont->name = 'container_center_div_wrap'; 
				$wrap_cont->id = $wrap_cont->name;
				$wrap_cont->value = 1; 
				$wrap_cont->checked = checked( maxBlocks::getValue('container_center_div_wrap'), 1, false); 
				$wrap_cont->label = __('Center the container', 'maxbuttons'); 
				$wrap_cont->output('','end'); 

				$container_width = new maxField('number'); 
				$container_width->name = 'container_width'; 
				$container_width->id = $container_width->name;
				$container_width->min = 0; 
				$container_width->value = maxUtils::strip_px( maxBlocks::getValue('container_width') ); 
				$container_width->label = __('Width', 'maxbuttons');
				$container_width->inputclass = 'small';
				$container_width->output('start','end'); 
				
			 		// Margin - trouble
			 		$ptop = new maxField('number'); 
			 		$ptop->label = __('Margin', 'maxbuttons'); 
			 		$ptop->id = 'container_margin_top';
			 		$ptop->name = $ptop->id; 
 					$ptop->min = 0; 
			 		$ptop->inputclass = 'tiny';
			 		$ptop->before_input = '<img src="' . $icon_url . 'p_top.png" title="' . __("Margin Top","maxbuttons") . '" >'; 
			 		$ptop->value = maxUtils::strip_px(maxBlocks::getValue('container_margin_top')); 
			 		
			 		$ptop->output('start'); 

			 		
			 		$pright = new maxField('number'); 
			 		$pright->id = 'container_margin_right';
			 		$pright->name = $pright->id; 
 					$pright->min = 0;
			 		$pright->inputclass = 'tiny'; 
			 		$pright->before_input = '<img src="' . $icon_url . 'p_right.png" class="icon padding" title="' . __("Margin Right","maxbuttons") . '" >'; 
			 		$pright->value = maxUtils::strip_px(maxBlocks::getValue('container_margin_right')); 
			 		
			 		$pright->output();
			 					 		
			 		$pbottom = new maxField('number'); 
			 		$pbottom->id = 'container_margin_bottom';
			 		$pbottom->name = $pbottom->id; 
 					$pbottom->min = 0; 
			 		$pbottom->inputclass = 'tiny'; 
			 		$pbottom->before_input = '<img src="' . $icon_url . 'p_bottom.png" class="icon padding" title="' . __("Margin Bottom","maxbuttons") . '" >'; 			 
			 		$pbottom->value = maxUtils::strip_px(maxBlocks::getValue('container_margin_bottom')); 
			 		
			 		$pbottom->output();
			 		
			 		$pleft = new maxField('number'); 
			 		$pleft->id = 'container_margin_left';
			 		$pleft->name = $pleft->id; 
 					$pleft->min = 0; 
			 		$pleft->inputclass = 'tiny'; 
			 		$pleft->before_input = '<img src="' . $icon_url . 'p_left.png" class="icon padding" title="' . __("Margin Left","maxbuttons") . '" >'; 
			 		$pleft->value = maxUtils::strip_px(maxBlocks::getValue('container_margin_left')); 
			 		
			 		$pleft->output('','end');	 
	
				
					$align = new maxField('generic');
	 				$align->label = __('Alignment','maxbuttons'); 
	 				$align->name = 'container_alignment'; 
	 				$align->id = $align->name; 
	 				$align->value= maxBlocks::getValue('container_alignment'); 
	 				//$align->setDefault(maxBlocks::getDefault('container_alignment')); 
	 				$align->content = maxUtils::selectify($align->name, $maxbuttons_container_alignments, $align->value); 
	 				$align->output('start', 'end'); 
				?>
 
				</div>
			</div>
<?php 
} // admin_fields

} // class


?>
