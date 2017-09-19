<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

$result = '';
$button =  MB()->getClass("button");  
$mbadmin = MB()->getClass("admin"); 
$collections = MB()->getClass('collections'); 
$collection = MB()->getClass('collection'); 

$view = (isset($_GET["view"])) ? sanitize_text_field($_GET["view"]) : "all"; 

// submit
if (isset($_POST) && isset($_POST["mb-list-nonce"])  ) {
	$verify = wp_verify_nonce( $_POST['mb-list-nonce'], 'mb-list' );
	if (! $verify ) echo " Nonce not verifed"; 

	if ($verify && isset($_POST['button-id']) && isset($_POST['bulk-action-select'])) {
		if ($_POST['bulk-action-select'] == 'trash') {
			$count = 0;
			
			foreach ($_POST['button-id'] as $id) {
				$id = intval($id);
				$button->set($id);
				$button->setStatus('trash'); 
				$count++;
			}
			
			if ($count == 1) {
				$result = __('Moved 1 button to the trash.', 'maxbuttons');
			}
			
			if ($count > 1) {
				$result = __('Moved ', 'maxbuttons') . $count . __(' buttons to the trash.', 'maxbuttons');
			}
		}
	}
	if ($verify && $_POST['bulk-action-select'] == 'restore') {
			$count = 0;
			
			foreach ($_POST['button-id'] as $id) {
				$id = intval($id);
				$set = $button->set($id,'','trash');
				$button->setStatus('publish'); 
				
				//maxbuttons_button_restore($id);
				$count++;
			}

			if ($count == 1) {
				$result = __('Restored 1 button.', 'maxbuttons');
			}
			
			if ($count > 1) {
				$result = __('Restored ', 'maxbuttons') . $count . __(' buttons.', 'maxbuttons');
			}
			$view = 'all'; // switch to normal list. 
	}
		
	if ($verify && $_POST['bulk-action-select'] == 'delete') {
		$count = 0;
		
		foreach ($_POST['button-id'] as $id) {
			$id = intval($id);
			$button->delete($id);
			$count++;
		}

		if ($count == 1) {
			$result = __('Deleted 1 button.', 'maxbuttons');
		}
		
		if ($count > 1) {
			$result = __('Deleted ', 'maxbuttons') . $count . __(' buttons.', 'maxbuttons');
		}
	}	
}

if (isset($_GET['message']) && $_GET['message'] == '1') {
	$result = __('Moved 1 button to the trash.', 'maxbuttons');
}

if (isset($_GET['message']) && $_GET['message'] == '1restore') {
	$result = __('Restored 1 button.', 'maxbuttons');
}

if (isset($_GET['message']) && $_GET['message'] == '1delete') {
	$result = __('Deleted 1 button.', 'maxbuttons');
}

$args = array(
	"orderby" => "id", 
	"order" => "DESC", 
	
);

if (isset($_GET["orderby"])) 
	$args["orderby"] = sanitize_text_field($_GET["orderby"]); 
if (isset($_GET["order"])) 
	$args["order"] = sanitize_text_field($_GET["order"]); 

if (isset($_GET["paged"]) && $_GET["paged"] != '') 
{
	$page = intval($_GET["paged"]); 
	$args["paged"] = $page;
}  

if ($view == 'trash') 
	$args["status"] = "trash"; 


$published_buttons = $mbadmin->getButtons($args);

$published_buttons_count = $mbadmin->getButtonCount(array());
$trashed_buttons_count = $mbadmin->getButtonCount(array("status" => "trash")); 

$args["view"] = $view; 

$page_args = $args; 
 

?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#bulk-action-all").click(function() {
			jQuery("#maxbuttons input[name='button-id[]']").each(function() {
				if (jQuery("#bulk-action-all").is(":checked")) {
					jQuery(this).attr("checked", "checked");
				}
				else {
					jQuery(this).removeAttr("checked");
				}
			});
		});
		
	});
</script>

<?php 
$page_title = __("Overview","maxbuttons"); 
$action = "<a class='page-title-action add-new-h2' href='" . admin_url() . "admin.php?page=maxbuttons-controller&action=edit'>" . __('Add New', 'maxbuttons') . "</a>";
$mbadmin->get_header(array("title" => $page_title, "title_action" => $action));
 ?>
 
			<div class="form-actions">
				<a class="button-primary" href="<?php echo admin_url() ?>admin.php?page=maxbuttons-controller&action=edit"><?php _e('Add New', 'maxbuttons') ?></a>
			</div>

			<?php if ($result != '') { ?>
				<div class="mb-notice mb-message"><?php echo $result ?></div>
			<?php } 
	
				do_action('mb-display-reviewoffer');		
			?>


			<p class="status">
			<?php
				$url = admin_url() . "admin.php?page=maxbuttons-controller&action=list";
				$trash_url =  $url . "&view=trash"; 
				
				if ($view == 'trash') 
				{
					$all_line = "<strong><a href='$url'>"  .  __('All', 'maxbuttons') . "</strong></a>";
					$trash_line = __("Trash", "maxbuttons"); 
				}
				else
				{
					$all_line = __("All","maxbuttons"); 
					$trash_line = "<a href='$trash_url'>" . __("Trash","maxbuttons") . "</strong></a>"; 
				}
			?>
				 <?php echo $all_line ?><span class="count"> (<?php echo $published_buttons_count ?>)</span>

				<?php if ($trashed_buttons_count > 0) { ?>
					<span class="separator">|</span>
					<?php echo $trash_line ?> <span class="count">(<?php echo $trashed_buttons_count ?>)</span>
				<?php } ?>
			</p>
			<?php
			do_action("mb-display-meta"); 

			?>
			<form method="post">
				<?php wp_nonce_field("button-copy","copy_nonce"); ?> 
				<?php wp_nonce_field("button-delete","delete_nonce"); ?> 
				<?php wp_nonce_field('button-trash', 'trash_nonce'); ?> 
				<?php wp_nonce_field('button-restore', 'restore_nonce'); ?> 
							
				<input type="hidden" name="view" value="<?php echo $view ?>" /> 
				<?php wp_nonce_field("mb-list","mb-list-nonce");  ?>
				
				<select name="bulk-action-select" id="bulk-action-select">
					<option value=""><?php _e('Bulk Actions', 'maxbuttons') ?></option>
				<?php if ($view == 'all'): ?>
	
					<option value="trash"><?php _e('Move to Trash', 'maxbuttons') ?></option>
				<?php endif; 
					if ($view == 'trash'): ?>
						<option value="restore"><?php _e('Restore', 'maxbuttons') ?></option>
						<option value="delete"><?php _e('Delete Permanently', 'maxbuttons') ?></option>
				<?php endif; ?> 
				</select>
				<input type="submit" class="button" value="<?php _e('Apply', 'maxbuttons') ?>" />
	
		<div class='tablenav top'> 		
	 			<?php do_action("mb-display-pagination", $page_args); ?> 
		</div>
			
						
<?php  // Sorting


			$link_order = (! isset($_GET["order"]) || $_GET["order"] == "DESC") ? "ASC" : 'DESC';
								
			$name_sort_url = add_query_arg(array(
				"orderby" => "name",
				"order" => $link_order
				));	
			$id_sort_url = add_query_arg(array(
				"orderby" => "id",
				"order" => $link_order
				));		
			
			$sort_arrow = ( strtolower($args["order"]) == 'desc') ? 'dashicons-arrow-down' : 'dashicons-arrow-up' 								
?>
			
				<div class="button-list preview-buttons">		
				
					<div class="heading"> 
						<span class='col col_check'><input type="checkbox" name="bulk-action-all" id="bulk-action-all" /></span>
						<span class='col col_button'>
							<a href="<?php echo $id_sort_url ?>">
							<?php _e('Button', 'maxbuttons') ?>	
							<?php if ($args["orderby"] == 'id')
								 echo "<span class='dashicons $sort_arrow'></span>";
							?>
							</a>
						</span>
						<span class="col col_name manage-column column-name sortable <?php echo strtolower($link_order) ?>">
							<a href="<?php echo $name_sort_url ?>">
							<span><?php _e('Name and Description', 'maxbuttons') ?></span>		
							<?php if ($args["orderby"] == 'name')
								 echo "<span class='dashicons $sort_arrow'></span>";
							?>					
 
							</a>
						</span>
						<span class='col col_shortcode'><?php _e('Shortcode', 'maxbuttons') ?></span>						
					</div> <!-- heading --> 
				
					<?php 
						foreach ($published_buttons as $b): 
						$id = $b['id'];
						if($view == 'trash') 
							$button->set($id,'','trash');
						else 
							$button->set($id);
						
						$inCollections = $collections::isButtonInCollection($id); 
					?> 
						<div class='button-row'>
						<span class="col col_check"><input type="checkbox" name="button-id[]" id="button-id-<?php echo $id ?>" value="<?php echo $id ?>" /></span>
						<span class="col col_button"><div class="shortcode-container">
										<?php 
											//echo do_shortcode('[maxbutton id="' . $id . '" externalcss="false" ignorecontainer="true"]'); 
										
										$button->display( array("mode" => "preview") ); 
										?>
								</div>
								<div class="actions">
								<?php if($view == 'all') : ?>
								<a href="<?php admin_url() ?>admin.php?page=maxbuttons-controller&action=button&id=<?php echo $id ?>"><?php _e('Edit', 'maxbuttons') ?></a>
									<span class="separator">|</span>
									<a href='javascript:void(0);' data-buttonaction='copy' data-buttonid="<?php echo $id ?>"><?php _e('Copy', 'maxbuttons') ?></a>
									<span class="separator">|</span>
									<a href="javascript:void(0)" data-buttonaction='trash' data-buttonid="<?php echo $id ?>"><?php _e('Move to Trash', 'maxbuttons') ?></a>
								<?php endif; 
								if ($view == 'trash'): 
								?> 
								<a href="javascript:void(0);" data-buttonaction='restore' data-buttonid="<?php echo $id ?>"><?php _e('Restore', 'maxbuttons') ?></a>
								<span class="separator">|</span>
								<a href="javascript:void(0);" data-buttonaction='delete' data-buttonid="<?php echo $id ?>"><?php _e('Delete Permanently', 'maxbuttons') ?></a>
								<?php endif; ?> 	
								</div>
								
								<?php if ($inCollections): 
									$number = count($inCollections); 
									
								?> 
									<div class='collection_notice'>
					<?php echo _n('In collection:', 'In collections:', $number,'maxbuttons')  ?>
										<?php foreach($inCollections as $col_id)
										{
											$meta = $collection->get_meta($col_id, 'collection_name'); 
											$name = isset($meta['collection_name']) ? $meta['collection_name'] : false; 
											if ($name)
												echo "<span class='name'>$name</span> "; 
										}	
										
										?>
									</div>
										
								<?php 
									endif; 
								?>
						</span>
						<span class="col col_name"><a class="button-name" href="<?php admin_url() ?>admin.php?page=maxbuttons-controller&action=button&id=<?php echo $id ?>"><?php echo $button->getName() ?></a>
									<br />
									<p><?php echo $button->getDescription() ?></p>
						</span>
						<span class="col col_shortcode">									[maxbutton id="<?php echo $id ?>"]<br />
									[maxbutton name="<?php echo $button->getName() ?>"]</span>
						</div> 
					<?php endforeach; 
					
					// buttons ?>	 
		

				</div> <!-- button-list --> 
			</form>
			
	<div class="tablenav bottom"> 		
 			<?php do_action("mb-display-pagination", $page_args); ?> 
	</div>
					
 
	</div>
	<div class="ad-wrap">
		<?php do_action("mb-display-ads"); ?> 
	</div>
	
<?php $mbadmin->get_footer(); ?> 
