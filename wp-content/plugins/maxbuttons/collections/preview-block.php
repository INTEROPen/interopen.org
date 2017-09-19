<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
$collectionBlock["preview"] = "previewCollectionBlock"; 

class previewCollectionBlock extends collectionBlock
{
	protected $blockname = "preview"; 
 
 	function ajax_update()
 	{
 		
 	
 	}
 
	function admin_fields()
	{
 
 
	?><div class="mb_tab option-container"> 
		<div class="title">
			<span class="dashicons dashicons-no"></span>
			<span class="title"><?php _e("Preview","maxbuttons"); ?></span>
		</div>
	</div>
	
	<div class='mb-preview-window output'> 
		<div class="header"><?php _e("Preview", 'maxbuttons'); ?>
			<span class="close tb-close-icon"></span>
		</div>
		<div class="mb-preview-wrapper shortcode-container">

		<?php 
		$args = array(
			"preview" => true
		
		); 
		$this->collection->display($args); 
		?>
		</div>
	</div>
	
	<?php
		
	
	}

}

?>
