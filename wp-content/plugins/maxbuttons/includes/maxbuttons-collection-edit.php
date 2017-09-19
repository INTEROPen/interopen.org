<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

global $page_title; 

$collections = MB()->getClass("collections"); 

$collection_type = (isset($_REQUEST["collection"])) ? sanitize_text_field($_REQUEST["collection"]) : 'basic'; 
$collection = $collections::getCollection($collection_type);


// handle submits
if ($_POST) {
 
	$collection_id = intval($_POST["collection_id"]); 	
	$collection->set($collection_id);

	// this can be a new id (!) 
	$collection_id = $collection->save($_POST); 	
}
else 
{	
	$collection_id = isset($_REQUEST["collection_id"]) ? intval($_REQUEST["collection_id"]) : 0;  
}

$nonce = wp_create_nonce('collection-edit-' . $collection_id); 
$block_nonce = wp_create_nonce('mbpro_collection_block-' . $collection_id); 
 

$collection->set($collection_id);
$basic = $collection->getBlock("basic")->get();
$collection_name = $basic["name"]; 

$tab = isset($_REQUEST["tab"]) ? sanitize_text_field($_REQUEST["tab"]) : ''; 

$page_title = __("Edit Social Share", "maxbuttons"); 
$admin = MB()->getClass("admin"); 

$action = "<a class='page-title-action add-new-h2' href='" . admin_url() . "admin.php?page=maxbuttons-collections&action=edit&collection=social'>" . __('Add New', 'maxbuttons') . "</a>";

$admin->get_header(array("tabs_active" => true, "title" => $page_title, "title_action" => $action)); 


$button = MB()->getClass("button");

?>
			<?php if ($collection_id > 0): ?>
			<div class="mb-message shortcode">
				<?php $button_name = $button->getName(); ?>
				<?php _e('To use this collection, place the following shortcode anywhere in your site content:', 'maxbuttons') ?>
				<strong>[maxcollection id="<?php echo $collection_id ?>"]</strong> <i><?php _e("or","maxbuttons"); ?></i> <strong>[maxcollection name="<?php echo $collection_name ?>"]</strong> 
 
 
			</div>
			<?php endif; ?>

<?php 
	/* Display admin notices [deprecated]
	@ignore
	*/
	do_action("mb_display_notices"); 
	
	/* Display admin notices 
	
	   Hook to display admin notices on error and other occurences in the editor. Follows WP guidelines on format. Use @see add_notice to add
	   notices.  
	   
	   @since 4.20 
	*/	
	do_action('mb/collection/display_notices'); 	
?>


<form method="post" id='collection_edit' class="mb_ajax_save">
	

	<?php $collection->showBlocks(); ?>


	<?php $collection->display_field_map(); ?>
<input type="hidden" name="action" value="collection-edit"> 
<input type="hidden" name="collection_id" value="<?php echo $collection_id ?>"> 
<input type="hidden" name="nonce" value="<?php echo $nonce ?>">
<input type="hidden" name="block_nonce" value="<?php echo $block_nonce ?>"> 
<input type="hidden" name="tab" value="<?php echo $tab ?>" /> 	
<input type="hidden" name="collection_type" value="<?php echo $collection_type ?>" > 

<div class="form-actions">				
	<input type="submit" data-form='collection_edit' name="submit" value="<?php _e('Save All', 'maxbuttons') ?>" class="button-primary ">
</div>
</form>

<?php 

$admin->get_footer(); 
?>
