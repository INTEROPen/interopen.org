<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
$collectionBlock["layout"] = "layoutCollectionBlock"; 

class layoutCollectionBlock extends collectionBlock
{
	protected $blockname = "layout"; 
	protected $fields = array(
						"margin_left" => array("default" => "0px", 
												"css" => "margin-left", 
												"csspart" => "maxcollection"),
						
						"margin_right" => array("default" => "0px", 
												"css" => "margin-right", 
												"csspart" => "maxcollection"), 
						
						"margin_top" => array("default" => "0px", 
												"css" => "margin-top", 
												"csspart" => "maxcollection"), 
						
						"margin_bottom" => array("default" => "0px", 
												"css" => "margin-bottom", 
												"csspart" => "maxcollection"), 
						"orientation" => array("default" => "auto"), 
						
						"item_margin_right" => array("default" => "0px",
											     "css" => "margin-right", 
											  	"csspart" => "mb-collection-item", 
											 	),
						"item_margin_bottom" => array("default" => "0px",
												"css" => "margin-bottom", 
												"csspart" => "mb-collection-item", 
											),
						"ignore_container" => array("default" => 1), 
						"static_position_ver" => array("default" => "Auto", 
												),
						"static_position_hor" => array("default" => "Auto",
												),
						 
			);					
	public function map_fields($map)
	{
		$map = parent::map_fields($map); 
		$map["orientation"]["func"] = "updatePlacement"; 
 
		return $map; 
	}
	
	function parseButtons($buttons)
	{
		// searches the cache for the container statement ending with -container or -center and tries to remove then 
		// not very optimal code, but this is a way to maintain cache loading while altering css. 
 
		if ($this->data["ignore_container"] == 0) 
			return $buttons; // ignore container off .

		foreach($buttons as $button)
		{
			$cache = $button->getCache(); 
 
			// chops all css statements in a different line
			preg_match_all('/([^{]+)\s*\{\s*([^}]+)\s*}/i',$cache, $matches);
			foreach($matches[0] as $cssline) 
			{
				// tries to find the last part of the statement from - to { 
				preg_match_all('/.([^-])*{/i', $cssline, $item);
 
				$item = trim(str_replace("{","",$item[0][0]));
					if ($item == '-center' || $item == '-container') 
					{
						$cache = str_replace($cssline,'',$cache);
					}
				
			
			}
		
			//([^{]+)\s*\{\s*([^}]+)\s*}  { split } 
			// match last thing .([^-])*{
			$button->setCache($cache);
 
			$data = $button->get(); 

			$container = isset($data["container"]) ? $data["container"] : array(); 
			foreach($container as $key => $value) 
			{
				if ($key != 'container_enabled' && $key != 'container_center_div_wrap')  // causes container to be removed otherwise
				$container[$key] = ''; 
			}
 
			$button->setdata("container", $container); 
		}
		return $buttons;
	
	}
	
	function parseCSS($css, $args)
	{	
		$css = parent::parseCSS($css, $args); 
 
		
		$css["mb-collection-item"]["normal"]["float"] = "left"; 
 		$css["mb-collection-item"]["normal"]["display"] = "inline-block"; 
 		$css["mb-collection-item a"]["normal"]["cursor"] = "pointer"; // by default social share = call to action.
		
		//$button_spacing = $this->data["button_spacing"]; 
		$orientation = $this->data["orientation"];
		
		$static_position_ver = $this->data["static_position_ver"]; 
		$static_position_hor = $this->data["static_position_hor"]; 
		
		$basic = $this->collection->getBlock("basic");
		$picker_data = $basic->get(); 
		$placement = $picker_data["placement"]; 
		
		
		if ($orientation == 'auto') 
		{
//			$basic = $this->collection->getBlock("basic");
 
			switch($placement) // auto align on basis of placement on screen
			{
				 case "static-left":  // vertical items
				 case "static-right": 
				 	$orientation = "vertical"; 
				break; 
				case "static-top":  // horizontal
				case "static-bottom":
				default: 
					$orientation = "horizontal";
				break; 	
			}	
		}
		
		switch($orientation) 
		{
 			case "horizontal": 
				//$css["mb_collection_item"]["normal"]["float"] 
				// todo
				//$css["mb-collection-item"]["normal"]["margin-right"] = $button_spacing . "px";
				//$css["mb-collection-item:last-child"]["normal"]["margin-right"] = "0"; 
			break;	
			
			case "vertical"; 
				$css["mb-collection-item"]["normal"]["clear"] = "both"; 
				//if ($button_spacing > 0) 
				//{
					//unset($css["mb-collection-item"]["normal"]["margin-right"]) ;
				//$css["mb-collection-item"]["normal"]["clear"] = "left"; 	
					//$css["mb-collection-item"]["normal"]["margin-bottom"] = $button_spacing . "px";
					
				//}			
			break;
		
		}	

		if ($args["preview"] == true)
			return $css; // don't process move than this in preview.
		 
 		switch($placement)
 		{
 			case "static-left": 
 			case "static-right": 
 				switch($static_position_ver)
 				{
 					case "auto": 
 					case "center": 
 						$css["maxcollection"]["normal"]["top"] = "50%"; 
						$css["maxcollection"]["normal"]["transform"] = "translateY(-50%)";	
 					break;
 					case "top":
 						// nothing
 					break; 
 					case "bottom":
 						unset($css["maxcollection"]["normal"]["top"]); 
 						$css["maxcollection"]["normal"]["bottom"] = "0";
 					break; 
 				}
 			
 			break;
 			
 			case "static-top": 
 			case "static-bottom": 
 				switch($static_position_hor)
 				{
 					case "auto": 
 					case "center": 
 						$css["maxcollection"]["normal"]["left"] = "50%"; 
 						$css["maxcollection"]["normal"]["transform"] = "translateX(-50%)"; 
 					break;
 					case "left": 
 					
 					break;
 					case "right": 
 						unset($css["maxcollection"]["normal"]["left"]); 
 						$css["maxcollection"]["normal"]["right"] = "0"; 
 					break;
 				
 				}
 			break;

 		}
 
		return $css;
	
	}
	
	function save_fields($data, $post) 
	{
		$data = parent::save_fields($data, $post);	
		if (! isset($post["ignore_container"]))
			$data[$this->blockname]["ignore_container"] = 0; 
		
		return $data;
	
	}
	
	function admin_fields()
	{
		extract($this->data); 
		
		$orientation_array = array(
						"auto" => __("Auto","maxbuttons"), 
						"horizontal" => __("Horizontal","maxbuttons"), 
						"vertical" => __("Vertical","maxbuttons")
					); 
		
		$staticposver = array(
						"auto" => __("Auto","maxbuttons"), 
						"center" => __("Center","maxbuttons"), 
						"top" => __("Top", "maxbuttons"),
						"bottom" => __("Bottom", "maxbuttons"), 
					); 
		$staticposhor = array(
						"auto" => __("Auto","maxbuttons"), 
						"center" => __("Center","maxbutton-pro"), 
						"left" => __("Left","maxbuttons"), 
						"right" => __("Right","maxbuttons"),
					); 
							
			

	$px =  __("px","maxbuttons"); 
	?><div class="mb_tab option-container layout-block" data-options="layout"> 
	<div class="title">
		<span class="dashicons dashicons-admin-appearance"></span>
		<span class="title"><?php _e("Layout","maxbuttons"); ?></span>
	    <span class='manual-box'><a class='manual-toggle' href='javascript:void(0)' data-target="layout"> <?php _e("Getting Started","maxbuttons-pro"); ?> </a></span> 
		<span class='right'><button name="save" type="submit"  data-form='collection_edit' class="button button-primary"><?php _e("Save All","maxbuttons"); ?></button>
		</span>		
	</div>
	
	<div class="inside"> 
		<div class="option"> 
			<label for="margin-left"><?php _e("Margin left","maxbuttons"); ?></label>
			<input type="number" class="tiny" id="margin_left" name="margin_left" value="<?php echo intval($margin_left) ?>" class="tiny"> <?php echo $px; ?>
		</div>

		<div class="option"> 
			<label for="margin-right"><?php _e("Margin right","maxbuttons"); ?></label>
			<input type="number" name="margin_right" id="margin_right" value="<?php echo intval($margin_right) ?>" class="tiny"> <?php echo $px; ?>
		</div>

		<div class="option"> 
			<label for="margin-top"><?php _e("Margin bottom","maxbuttons"); ?></label>
			<input type="number" name="margin_bottom" id="margin_bottom" value="<?php echo maxUtils::strip_px($margin_top) ?>" class="tiny"> <?php echo $px; ?>
		</div>
		
		<div class="option"> 
			<label for="margin-bottom"><?php _e("Margin top","maxbuttons"); ?></label>
			<input type="number" name="margin_top" id="margin_top" value="<?php echo maxUtils::strip_px($margin_bottom) ?>" class="tiny"> <?php echo $px; ?>
		</div>						

		<div class="option"> 
			<label for="orientation"><?php _e("Orientation","maxbuttons"); ?></label>
			<?php echo maxUtils::selectify("orientation", $orientation_array, $orientation); ?>
		</div>
		
		<div class="option"> 
			<label for="item_margin_right"><?php _e("Item margin right","maxbuttons"); ?></label>
			<input type="number" class="tiny" name="item_margin_right" id="item_margin_right" value="<?php echo maxUtils::strip_px($item_margin_right) ?>"> <?php echo $px; ?>
		</div>
		
		<div class="option"> 
			<label for="item_margin_bottom"><?php _e("Item margin bottom","maxbuttons"); ?></label>
			<input type="number" class="tiny" name="item_margin_bottom" id="item_margin_bottom" value="<?php echo maxUtils::strip_px($item_margin_bottom) ?>"> 
				<?php echo $px ?> 
 
		</div>
		
		<div class="option"> 
			<label for="ignore_container"><?php _e("Remove container width and margins","maxbuttons"); ?></label>
			<input type="checkbox" name="ignore_container" value="1" <?php checked($ignore_container,1) ?>>
			<div class="help fa fa-question-circle "> 
				<span><?php _e("Removes the margins and widths of the button container.", "maxbuttons"); ?> 
					</span>
			</div>
						
		</div>
	</div>
	
	<?php 
	$condition = array("target" => "placement", "values" => array("static-left","static-right","static-top", "static-bottom")) ;
	$static_conditional = htmlentities(json_encode($condition )); 
	?>
	<br>
	<div class='conditional-option option-container' data-show="<?php echo $static_conditional ?>">
		<div class="title"> <?php _e("Static positioning","maxbuttons"); ?></div>		
		<div class="inside"> 
	<?php 
	$condition = array("target" => "placement", "values" => array("static-left","static-right")) ;
	$static_conditional = htmlentities(json_encode($condition )); 
	?>
			<div class="option conditional-option" data-show="<?php echo $static_conditional ?>"> 
				<label for="static_position_ver"><?php _e("Static position vertical","maxbuttons"); ?></label>
				<?php echo maxUtils::selectify("static_position_ver", $staticposver,$static_position_ver); ?>
		
			</div>
		<?php 
	$condition = array("target" => "placement", "values" => array("static-top", "static-bottom")) ;
	$static_conditional = htmlentities(json_encode($condition )); 
	?>	
			<div class="option conditional-option" data-show="<?php echo $static_conditional ?>"> 
				<label for="static_position_hor"><?php _e("Static position horizontal","maxbuttons"); ?></label>
				<?php echo maxUtils::selectify("static_position_hor", $staticposhor,$static_position_hor); ?>
		
			</div>
		</div>
	</div>
	<!-- manual entry -->
	<div class="manual-entry" data-manual="layout"> 	
		<h3><?php _e("Layout settings", "maxbuttons"); ?>
			 <span class="dashicons dashicons-no window close manual-toggle" data-target="layout"></span>  
		</h3>
						
		<p><?php _e("The first 4 options - margins left, right, buttom and top - are for positioning the entire collection.  Click the Preview tab on so you can see the changes to your collection as you make them.  The Orientation options let’s you choose between Auto, Horizontal and Vertical. Image margin allows you to set the spacing between the icons.","maxbuttons"); ?></p>
	
	</div>	
</div> <!-- tab --> 
		
	<?php
		
	
	}

}

?>
