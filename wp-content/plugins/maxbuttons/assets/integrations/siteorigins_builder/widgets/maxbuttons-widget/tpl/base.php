<?php
namespace MaxButtons; 

$button_class = MB()->getClass('button'); 
 
//$args = array();  

//	$args["load_css"] = 'inline'; 

$id = isset($instance["id"]) ? $instance["id"] : 0;
if ($id == 0) 
	return; // no button set no button 

 
$url = isset($instance["url"]) ? sow_esc_url($instance["url"]) : ''; 
$text = isset($instance["text"]) ? $instance["text"] : ''; 
$window = isset($instance["window"]) ? $instance["window"] : 0; 
if ($window == 1) $window = 'new'; 

$shortcode_args = array("id" => $id, "url" => $url, "text" => $text, "window" => $window); 


if (isset($instance["is_preview"]) && $instance["is_preview"])
	$shortcode_args["style"] = "inline"; 

echo $button_class->shortcode($shortcode_args); 
//$button_class->display($args); 

	return; 
?>

