<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
$collectionBlock["basic"] = "basicCollectionBlock"; 

class basicCollectionBlock extends collectionBlock
{
	protected $blockname = "basic"; 
	protected $fields = array("name" => array("default" => ""),
				"show_homepage" => array("default" => 0), 
				"show_page" => array("default" => 0),
				"show_post" => array("default" => 0), 
				"show_archive" => array("default" => 0), 
				"placement" => array("default" => "after"), 
				
			  );
							
	function __construct()
	{
		$this->fields["name"]["default"] = __("New Collection","maxbuttons");
	
	}
	
	function parseCSS($css, $args)
	{
		$css["maxcollection"]["normal"]["display"] = "inline-block"; 
		$css["maxcollection"]["normal"]["z-index"] = "9999"; 

		switch($this->data["placement"]) 
		{
			case "static-left": 
				$css["maxcollection"]["normal"]["position"] = "fixed"; 
				$css["maxcollection"]["normal"]["left"] = "0"; // these as string otherwise css compiler doens't like it
				$css["maxcollection"]["normal"]["top"] = "0";  								
			break;
			case "static-right": 
				$css["maxcollection"]["normal"]["position"] = "fixed"; 			
				$css["maxcollection"]["normal"]["right"] = "0";
				$css["maxcollection"]["normal"]["top"] = "0";  								
			break;	
			case "static-top": 
				$css["maxcollection"]["normal"]["position"] = "fixed"; 			
				$css["maxcollection"]["normal"]["left"] = "0";
				$css["maxcollection"]["normal"]["top"] = "0";  								
			break;
			case "static-bottom":
				$css["maxcollection"]["normal"]["position"] = "fixed"; 			
				$css["maxcollection"]["normal"]["left"] = "0";
				$css["maxcollection"]["normal"]["bottom"] = "0";  										 
			break; 
		}
		
		if ($args["preview"] == true) 
		{
			if (isset($css["maxcollection"]["normal"]["position"]) && $css["maxcollection"]["normal"]["position"] == "fixed")
			{
				$css["maxcollection"]["normal"]["position"]= "absolute"; 
			}
			
			
		}

		//$css["maxcollection"]["normal"]["background-color"] = "#fff"; 
		return $css; 
		
	}
	
	public function map_fields($map)
	{
		$map = parent::map_fields($map); 
		$map["collection_name"]["func"] = "updateCollectionName"; 
		$map["placement"]["func"] = "updatePlacement"; 
 
		
		return $map; 
	}
		
	function save_fields($data, $post)
	{
		$data = parent::save_fields($data, $post); 
		$data["collection_name"] = $data[$this->blockname]["name"];  // save as a global meta for searching
		$data["collection_type"] = $this->collection->getType();
		if ($data[$this->blockname]["show_homepage"] == 1)
		{
			$data["show_homepage"] = $data[$this->blockname]["placement"]; 		
		}
		if ($data[$this->blockname]["show_page"] == 1) 
		{
			$data["show_page"] = $data[$this->blockname]["placement"]; 		
		}
		
		if ($data[$this->blockname]["show_post"] == 1)
		{
			$data["show_post"] = $data[$this->blockname]["placement"]; 
		}
		if ($data[$this->blockname]["show_archive"] == 1)
		{
			$data["show_archive"] = $data[$this->blockname]["placement"]; 
		}		
		
		return $data; 
		
	}

	function admin_fields()
	{
	
	extract($this->data); // admin data

	$placement_options = array(
				"after" => __("After","maxbuttons"), 
				"before" => __("Before","maxbuttons"), 
				"after-before" => __("Before + After (both)", "maxbuttons"), 
				"static-left" => __("Static left","maxbuttons"), 
				"static-right" => __("Static right","maxbuttons"),
				"static-top" => __("Static top","maxbuttons"), 
				"static-bottom" => __("Static bottom","maxbuttons"),
	);			
	?><div class="mb_tab option-container" data-options="settings"> 
	<div class="title">
		<span class="dashicons dashicons-admin-settings"></span> 
		<span class="title"><?php _e("Settings","maxbuttons"); ?></span>
		  <span class='manual-box'><a href='javascript:void(0)' class='manual-toggle' data-target="settings"> <?php _e("Getting Started","maxbuttons-pro"); ?> </a></span> 
		<span class='right'><button name="save" type="submit"  data-form='collection_edit' class="button button-primary"><?php _e("Save All","maxbuttons"); ?></button>
		</span>
	</div>
	
	<div class="inside"> 
		
		<div class="option">
			<label for="collection_name"><?php _e("Name"); ?></label>
			<input type="text" id='collection_name' name="name" value="<?php echo esc_attr($name) ?>" /> 
			<div class="help fa fa-question-circle"> 
				<span><?php _e("The name of your collection. It will not be displayed on the site.", "maxbuttons"); ?>
				</span>
			</div>
						
		</div>
		
		<div class="option"> 
			<label for="collection_show"><?php _e("Automatically show on","maxbuttons"); ?></label>
			
			<div class='option-list'>
				<div class="help fa fa-question-circle"> 
					<span><?php _e("Auto-display your buttons on the site. Checking the options will display the buttons on the corresponding part.", "maxbuttons"); ?>
					</span>
				</div>			
				<input type="checkbox" name="show_homepage" value="1" <?php checked(1, $show_homepage) ?> >  
					<?php _e("Homepage","maxbuttons"); ?><br /> 
					
				<input type="checkbox" name="show_page" value="1" <?php checked(1, $show_page) ?> > <?php _e("Pages","maxbuttons"); ?><br>
				<input type="checkbox" name="show_post" value="1" <?php checked(1, $show_post) ?> >  <?php _e("Posts","maxbuttons");  ?><br>				
				<input type="checkbox" name="show_archive" value="1" <?php checked(1,$show_archive) ?> > 
					<?php _e("Category / Archives","maxbuttons"); ?> <br>
					
			
			</div>
		</div>
		
		<div class="option"> 
			<label for="collection_placement"><?php _e("Placement"); ?></label>
			<?php echo maxUtils::selectify("placement", $placement_options, $placement); ?>
				<div class="help fa fa-question-circle"> 
					<span><?php _e("Choose where the buttons will show up.", "maxbuttons"); ?>
					</span>
				</div>	
		</div>
		
		
		
	</div>
</div> <!-- option container --> 

	<div class="manual-entry" data-manual="settings"> 	
		<h3><?php _e("General settings", "maxbuttons"); ?>
			 <span class="dashicons dashicons-no window close manual-toggle" data-target="settings"></span>  
		</h3>
						
		<p><?php _e("Give a name to your collection of buttons and select which portions and locations on your site you want the collection to appear.  Anytime you want to see how your collection is going to look click the Preview tab to open up the Preview tab.  We suggest you do this first. ","maxbuttons"); ?></p>
		

	</div>
	
	
	<?php
		
	
	}

}

?>
