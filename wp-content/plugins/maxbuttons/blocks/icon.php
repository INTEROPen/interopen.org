<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
$blockClass["icon"] = "iconBlock"; 
$blockOrder[60][] = "icon"; 

use \simple_html_dom as simple_html_dom;

class iconBlock extends maxBlock 
{
	protected $blockname = "icon"; 
 	protected $fields = array("use_fa_icon" => array("default" => 0),
							  "fa_icon_value" => array("default" => '',
							  						 "css" => ''),
							  						 
							  "fa_icon_size" => array("default" => '30px', 
							  						 "css" => 'font-size', 
							  						 "csspart" => "fa"),
							  
							  "icon_id" => array('default' => '', 
							  					 'css' => ''
							  					),				  
							  "icon_url" => 	array('default' => '', 
							  						  'css' => ''), 
							  /*"background_url" => array('default' => '', 
							  						  'css' => 'background-image',
							  					),		 */				  
							  "icon_alt" => 	array('default' => '', 
							  						  'css' => ''), 
							  						
							  						  
							  "icon_position"	=> array('default' => 'left',  
							  						 'css' => 'text-align', 
							  						 'csspart' => 'mb-icon'),  
 
							  'icon_padding_top' => array('default' => '13px',
							  						  'css' => 'padding-top', 
							  						  'csspart' => 'mb-icon'),
							  						   
							  'icon_padding_right' => array('default' => '6px',
							  						  'css' => 'padding-right', 
							  						  'csspart' => 'mb-icon'), 	
							  						  						  						  
							  'icon_padding_bottom' => array('default' => '0px',
							  						  'css' => 'padding-bottom', 
							  						  'csspart' => 'mb-icon'), 
							  						  
							  'icon_padding_left' => array('default' => '18px',
							  						  'css' => 'padding-left', 
							  						  'csspart' => 'mb-icon'), 						  						   
							 ); 

 
	public function parse_css($css, $mode = 'normal') 
	{

		$csspart = 'mb-icon'; 
		$csspseudo = 'normal'; 

 		$data = isset($this->data[$this->blockname]) ?  $this->data[$this->blockname] : array(); 
 		if (count($data) == 0)
 			return $css; // no icons present here.
		
		$css = parent::parse_css($css); 
		$css["mb-icon"]["normal"]["line-height"] = "0px";  // prevent rendering bigger div than icon
		$css["mb-icon"]["normal"]["display"] = "block";
		$css['mb-icon']['normal']['background-color'] = 'unset'; // prevent background overwrite. 
 
		/*if (isset($css["maxbutton"]["normal"]["background-image"]))
		{
			$url = $css["maxbutton"]["normal"]["background-image"]; 
			$css["maxbutton"]["normal"]["background-image"] = "url($url)"; 
		
		}
			print_R($css["maxbutton"]);	 		
		*/
			
		if (isset($css[$csspart][$csspseudo]["text-align"]) && $css[$csspart][$csspseudo]["text-align"] != '') 
		{  
			switch( $css[$csspart][$csspseudo]["text-align"])
			{
				case "left": 
					$css[$csspart][$csspseudo]["float"] = 'left';
					unset($css[$csspart][$csspseudo]["text-align"]); 
				break;
				case "right":
					$css[$csspart][$csspseudo]["float"] = 'right';
					unset($css[$csspart][$csspseudo]["text-align"]);  
					
				break; 
				case "top": 
				case "bottom": 				
					$css[$csspart][$csspseudo]["text-align"] = 'center'; 
				break;
			}
		}	

 
		return $css;
	}
	
 	public function parse_button($domObj, $mode = 'normal')
 	{
 		$data = isset($this->data[$this->blockname]) ?  $this->data[$this->blockname] : array(); 
 		if (count($data) == 0)
 			return $domObj; // no icons present here.
   
		$id = $this->data["id"]; 
 
		$icon_url = $data["icon_url"]; 
		$use_fa_icon= $data["use_fa_icon"]; 
		$position = $data["icon_position"]; 

		if ($icon_url == '' && ($use_fa_icon == 0 || $use_fa_icon == '') ) return $domObj; // no icon
 
		$anchor = $domObj->find("a",0); 
 		$anchor_text = ''; 
 		
		if ($use_fa_icon == 1) 
		{
			if ($data["fa_icon_value"] == '') return $domObj; // still no icon 
		 	$anchor_text = '<span class="mb-icon  "><i class="fa ' . $data["fa_icon_value"] . '"></i></span>';
		}
		else 
		{
		$anchor_text = '<span class="mb-icon  "><img src="' . $data["icon_url"] . '" alt="' . $data["icon_alt"] . '" border="0" /></span>' ; 
		}  	
		if ($position == 'bottom')
			$anchor->innertext = $anchor->innertext . $anchor_text;		 
		else
			$anchor->innertext = $anchor_text . $anchor->innertext;
		
		$newhtml = $domObj->save(); 
		
		$domObj =  new simple_html_dom(); 
		$domObj->load($newhtml);

 		return $domObj; 
 		
 	} 
 	
 	// empty save, leave the data alone plz. 
 	public function save_fields($data, $post)
 	{
 		return $data; 
 	}

	public function admin_fields() 
	{
		
		$data = isset($this->data[$this->blockname]) ? $this->data[$this->blockname] : array(); 
		
		$icon_url = isset($data["icon_url"]) ? $data["icon_url"] : '';
		
		if ($icon_url == '' ) 
			return;  // hide if no setting
		
 
		
		?>
			<div class="option-container mb_tab">
				<div class="title"><?php _e('Icons and images', 'maxbuttons') ?></div>
				<div class="inside"> 
					<?php _e("This setting can only be changed in MaxButtons Pro","maxbuttons"); ?> 
					
					<p><img src="<?php echo $icon_url ?>"> </p>
				</div>
			</div>		
		<?php 
		
		
	}

} // class

