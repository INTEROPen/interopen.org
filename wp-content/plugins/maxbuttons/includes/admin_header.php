<?php 
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
 

$mainclass = isset($_REQUEST["page"]) ? sanitize_text_field($_REQUEST["page"]) : '';
$action = isset($_REQUEST["action"]) ? sanitize_text_field($_REQUEST["action"]) : $action; 
if ($action !== '')
	$mainclass .= '-' . $action; 
?>

<div id="maxbuttons" class="<?php echo $mainclass ?>" <?php if ($tabs_active) echo "data-view='tabs'" ?>>
	<?php do_action("mb-interface-start");  ?>
	<div class="wrap">
		<h1 class="title"><span><?php _e("MaxButtons:","maxbuttons"); ?> <?php echo $title ?>
		<?php if (isset($title_action) && $title_action != "") {  
			echo $title_action; } ?> 
			</span>
			<div class="logo">
				<?php do_action("mb-display-logo"); ?> 
			</div>
			
		</h1>
		<div class='mb_header_notices'><?php do_action('mb/header/display_notices'); ?></div>		
		<div class="clear"></div>
		<div class="main">
			<?php do_action('mb-display-tabs'); ?>
			
		
