<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

global $page_title; 
$page_title = __("Enable SimpleXML", "maxbuttons"); 
$admin = MB()->getClass("admin"); 


$admin->get_header(array("tabs_active" => true, "title" => $page_title, "title_action" => $action)); 

$admin = MB()->getClass('admin'); 
$page_title = __("Packs","maxbuttons-pro"); 
 
$admin->get_header(array("title" => $page_title) );

?>

<div> 

<h4><?php _e('PHP Module SimpleXML not found. This module will not work','maxbuttons') ?></h4>
<p>
	<?php _e('To enable this module please install the SimpleXML PHP module. If you don\'t know how, ask your support from your 
	hosting provider','maxbuttons'); ?>

	<ul>
		<li><a target="_blank" href='http://php.net/manual/en/simplexml.installation.php'><?php _e('SimpleXML installation','maxbuttons');?></a>
		</li>
	</ul> 

</p>

</div>
</div> <!-- main --> 
	<div class="offers ad-wrap">
		<?php do_action("mb-display-ads"); ?> 
	</div>

<?php $admin->get_footer(); ?> 
