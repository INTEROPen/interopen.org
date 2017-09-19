<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

if (isset($_GET['action']) && $_GET['action'] != '') {
		// extra safety. 
		$action = sanitize_text_field($_GET['action']); 
		
		switch ($action) {
			case 'button':
			case 'edit': 
					include_once 'maxbuttons-button.php';
			break;
			default:
				include_once 'maxbuttons-list.php';
				break;
		}

} else {
	include_once 'maxbuttons-list.php';
}

