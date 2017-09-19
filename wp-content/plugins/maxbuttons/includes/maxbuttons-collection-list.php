<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

global $page_title; 
$page_title = __("Social Share", "maxbuttons"); 
$admin = MB()->getClass("admin"); 

$action = "<a class='page-title-action add-new-h2' href='" . admin_url() . "admin.php?page=maxbuttons-collections&action=edit&collection=social'>" . __('Add New', 'maxbuttons') . "</a>";

$admin->get_header(array("tabs_active" => true, "title" => $page_title, "title_action" => $action)); 

$collections = maxCollections::getCollections(); 
$maxCol = new maxCollection(); 
?>
 
 <a class="page-title-action collection-addnew" href="<?php echo admin_url() ?>admin.php?page=maxbuttons-collections&action=edit&collection=social">
	<?php _e("Add New","maxbuttons"); ?></a> 
 
<?php 

	/* Display admin notices 
	
	   Hook to display admin notices on error and other occurences in the editor. Follows WP guidelines on format. 
	   @since 4.20 
	*/	
	do_action('mb/collection/display_notices'); 	
?>
</span>


<span class="remove_action_title" style="display:none"><?php _e("Removing this collection","maxbuttons"); ?></span>
<span class="remove_action_text"  style="display:none"><?php _e("Are you sure you want to permanently delete this collection?", "maxbuttons"); ?></span>

			

<div class='collection_list'>
<?php 


foreach ($collections as $index => $data) 
{
	$id = $data["collection_id"]; 
	$name = $maxCol->get_meta($id, "collection_name"); 
	$name = isset($name['collection_name']) ? $name["collection_name"] : ''; 


	$collection = maxCollections::getCollectionById($id); 	
	if (! $collection) 
		continue; 
		
	$collection_type = $collection->getType();
	$block_nonce = wp_create_nonce('mbpro_collection_block-' . $id); 

	$button_count = 0; 
	$picker = $collection->getBlock("picker"); 
	if ($picker)
	{
		$picker_data = $picker->get();
		$button_count = count($picker_data["selection"]); 		
	}	
	
?>	
	 
	<div class='collection collection-<?php echo $id ?>' data-id="<?php echo $id ?>" data-blocknonce="<?php echo $block_nonce ?>" data-type="<?php echo $collection_type ?>">	
		<div class="collection_remove dashicons dashicons-no"></div>
 
		<h3 class='title'><a href="?page=maxbuttons-collections&action=edit&collection=social&collection_id=<?php echo $id ?>"><?php echo $name; ?></a> 
		
		<?php if ($button_count > 0): ?> 
		<span class="button-count">(<?php echo $button_count ?> <?php _e("Buttons","maxbuttons"); ?>)</span>
		<?php endif; ?>
		</h3>
	
		<div class="collection-container">
		<?php 

			$args = array("preview" => true, 
			
			);

			$collection->display($args); 
		
		?>
		</div>
		<p>[maxcollection id="<?php echo $id ?>"]</p>
		<p><a href="?page=maxbuttons-collections&action=edit&collection=social&collection_id=<?php echo $id ?>"><?php _e("Edit","maxbuttons"); ?></a></p>
	<!--	<p>[ Edit - Delete ] </p> -->
				
	</div>
<?php
}

if (count($collections) == 0) 
{
	do_action("mb-display-collection-welcome"); 
}
?>
 
</div> <!-- // collection-list --> 
</div> <!-- // main --> 
	<div class="ad-wrap">
		<?php do_action("mb-display-ads"); ?> 
	</div>
	
<?php
$admin->get_footer(); 
