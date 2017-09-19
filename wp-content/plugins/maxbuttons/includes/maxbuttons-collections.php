<?php 
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

$colID = (isset($_GET["colID"])) ? intval($_GET["colID"]) : ''; 
$action = isset($_GET["action"]) ? sanitize_text_field($_GET["action"]) : ''; 


if (! extension_loaded('simplexml') )
	$action = 'nosimplexml'; 	

switch($action)
{
	case "edit": 
		require_once("maxbuttons-collection-edit.php"); 
	break;
	case 'nosimplexml': 
		require_once('maxbuttons-no-simplexml.php'); 
	break;	
	default; 
		require_once("maxbuttons-collection-list.php"); 
	break; 
}
 




?>
