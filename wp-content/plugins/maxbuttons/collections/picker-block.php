<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

$collectionBlock["picker"] = "pickerCollectionBlock"; 

class pickerCollectionBlock extends collectionBlock
{
	protected $blockname = "picker"; 
	protected $fields = array("selection" => array("default" => array() ), 
							  
	); 
	
	// get all buttons in collection, set as proper button object/
	function getButtons()
	{ // should maybe but a 'light init' of buttons?
		$selection = $this->data["selection"]; 
		
		$buttonsArray = array(); 
 		if (count($selection) == 0) 
 			return $buttonsArray; 
 
		$table = maxUtils::get_table_name();
		$status = 'publish'; 
		
		$query_selection =  $selection; 
		 
		$selectmask = implode( ', ', array_fill( 0, count( $query_selection ), '%d' ) );
		$sql = "SELECT DISTINCT * FROM {$table} WHERE id in (" . $selectmask . ") and status = '%s' ";
		$prepare_values = $query_selection;
 
		array_push($prepare_values, $status); 

		global $wpdb; 
		$sql = $wpdb->prepare($sql, $prepare_values ); 
		$results = $wpdb->get_results($sql, ARRAY_A); 
		
		$data_array = array(); 
		
		foreach ($results as $result) 
		{	
			$data_array[$result['id']] = $result; 
		}

		foreach($selection as $button_id)
		{
			$button = MB()->getClass("button"); 

			// performance: bypass the set ( = query ) for every button 
			//$button->set($button_id); 
 
			$data = isset($data_array[$button_id]) ? $data_array[$button_id] : array(); 
			if (count($data) > 0) 
			{
				maxButtons::buttonLoad(array("button_id" => $button_id)); // central registration - from button
				$button->setupData($data); 
			}	
			else
				continue; // non-existing buttons should not show.

			maxButtons::forceNextID();
			
			$buttonsArray[] = $button;
		}

 
		return $buttonsArray;
	}	
		
	function parse($domObj, $args)
	{

		$buttons = $this->collection->getLoadedButtons(); 
		
		$output = ''; 
 	
 		$collectionObj = $domObj->find('.maxcollection', 0); 
 		
		$btn_display = ''; 

		foreach($buttons as $button)
		{
 
				//$button->reloadData(); 
				$button_id = $button->getID(); 

 				$document_id = $button->getDocumentID();

				$default_args = array("mode" => "normal", 
							  "compile" => false,
							  "load_type" => "footer", 
							  ); 
				$button_args = wp_parse_args($args, $default_args);
				$button_args["load_css"] = $button_args["load_type"]; 
				
				$button_args["echo"] = false;  // non-optional.
				 
 				if (isset($args['preview']) && $args["preview"]) 
					$button_args["mode"] = "preview"; // buttons work with mode.

				$btn_display .= "<span class='mb-collection-item item-$button_id ' data-doc-id='" . $document_id . "'>" . $button->display($button_args) . "</span>";
				

		}		

		
		$collectionObj->innertext =  $btn_display;
 		
 		$domObj->load($domObj->save()); 
 		
		return $domObj;
		
	}
	
	function save_fields($data, $post)
	{
		$blockdata = $this->data; 
		
		$selection = array(); 

		$button = MB()->getClass("button");
					
		if (isset($post["sorted"])) 
		{
			$sorted = array_filter(explode(",",sanitize_text_field($post["sorted"]))); 
			$i = 0;
			foreach($sorted as $button_id)
			{

				if (intval($button_id) > 0)
				{
					$selection[$i] = $button_id;
					$i++;
				
					$set_bool = $button->set($button_id);
					if (! $set_bool) continue; // buttons that don't exist, like virtuals.
					
					$button_data = $button->get();
					$collection_id = $this->collection->getID(); 
					$collection_meta = isset($button_data["meta"]["in_collections"]) ? 
						$button_data["meta"]["in_collections"] : array(); 
					
					if (! is_array($collection_meta)) 
						$collection_meta = maybe_unserialize($collection_meta); 
					
					if (is_array($collection_meta)) 
					{
						$key = array_search($collection_id, $collection_meta); 
						if ($key === false) 
						{
							$collection_meta[] = $collection_id;
							$button_data["meta"]["in_collections"] = $collection_meta; 
							$button->update($button_data);
						}
					}
				}
			}
		}
		/* If a button id is in the previous selection, but not in the current, maybe the button should be deleted if the
			button appears to be auto-generated only for this collection */ 
		 if (isset($post["previous_selection"])) 
		{
			$previous = array_filter(explode(",",sanitize_text_field($post["previous_selection"]))); 
			foreach($previous as $button_id)
			{
				if (! in_array($button_id, $selection) && intval($button_id) > 0)
					$this->collection->maybe_delete_button($button_id);
			}
		
		}
		$data[$this->blockname]["selection"] = $selection;
		return $data;
	}

	function admin_fields()
	{
		extract($this->data); // admin data
		$btns = $this->collection->editor_getPacks();		
	?>
	
	<?php ob_start(); ?> 
	<div id="picker-modal"> 
			<div class='picker-packages'> 
 
				<ul>
				<?php foreach ($btns as $index => $data)
				{ ?>
					<li><a href="#" data-pack="<?php echo $index ?>"><?php echo $data["tab"]; ?></a></li>
				
				<?php } ?>
				</ul>
			</div>
			<div class='picker-main' >
			</div>
			<div class='picker-inselection'> 
				<div class='info'>
					<span class='count'></span> <?php _e("Selected","maxbuttons"); ?>
					<button type="button" name="clear" class='button-link clear-selection'><?php _e("Clear","maxbuttons"); ?></button>
				</div>
				<div class='items'>
				</div>
				<div class='add'> 
					<button type="button" name="add-buttons" class="button button-primary"><?php _e("Add selected buttons","maxbuttons"); ?></button>
				</div>
			</div>
		
		
	</div>	
	
	<?php 
	global $mb_pick_modal; 
	$mb_pick_modal = ob_get_contents(); 

	// print this outside of the main div since it messes with z-index 
	add_action('mb-interface-end', maxUtils::namespaceit('mb_print_modal') ); 
	function mb_print_modal()
	{
		global $mb_pick_modal; 
		echo $mb_pick_modal;
	}
	ob_end_clean(); 	

	?>
	<div class="mb_tab option-container">
		<div class="title">
			<span class="dashicons dashicons-list-view"></span> 
			<span class='title'><?php _e("Buttons", "maxbuttons"); ?></span>
			<button name="picker_popup" type="button" class="button-primary"><?php _e("Select Social Share Icons","maxbuttons"); ?></button>
			<span class='right'><button name="save" type="submit"  data-form='collection_edit' class="button button-primary"><?php _e("Save All","maxbuttons"); ?></button>
			</span>
		</div> 
		<div class="inside">	
 
		<div class="option preview_collection">
			<label> <?php _e("Current Selection","maxbuttons"); ?></label>
			<p><?php _e("Drag the buttons to change the order of your selection. You can remove the button by clicking on the remove icon.", 
				"maxbuttons"); ?> 
			</p>

			
			<div class="mb_collection_selection"> 
				<input type="hidden" name="sorted" value="" /> 
				<input type="hidden" name="previous_selection" value="" /> 

				<div class="sortable buttons">
 
					<?php
					
					foreach($selection as $button_id)
					{	
						echo "<div class='shortcode-container item' data-id='$button_id'>";
						$button = MB()->getClass("button"); 
						$button->set($button_id);
						echo "<div id='maxbutton-$button_id'>"; 
						$button->display(array( "load_css" => "inline", 'mode' => 'preview' ));
						echo "</div>"; 
						echo "<div class='button-remove'><span class='dashicons dashicons-no'></span></div>"; 
						echo "</div>"; 
					}
 
					?>
 
				</div>
			</div>
	    </div>  <!-- option --> 
	</div> <!-- inside --> 
</div> <!-- tab -->
	
	<?php

	}

}

?>
