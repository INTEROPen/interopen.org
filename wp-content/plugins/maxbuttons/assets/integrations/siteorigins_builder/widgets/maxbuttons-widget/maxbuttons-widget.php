<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
/*
Widget Name: MaxButtons
Description: MaxButtons widget
Author: Max Foundry
Author URI: https://maxbuttons.com
*/

//use \SiteOrigin_Widget as SiteOrigin_Widget;

class Widget_MaxButtons_Widget extends \SiteOrigin_Widget {
	function __construct() {

		
		parent::__construct(
			'sow-maxbutton',
			__('MaxButtons', 'maxbuttons'),
			array(
				'description' => __('MaxButtons for the page builder.', 'maxbuttons'),
				'panels_groups' => array('maxbuttons'),
 				'has_preview' => false, 
			),
			array(

			),
			array(
				'id' => array('type' => 'MaxButton', 
							  'label' => __('Select a maxbutton','maxbuttons'), 
							//  'library' => 'maxbuttons', 
				), 
			 	'text' => array(
					'type' => 'text',
					'label' => __('Button text [optional]', 'maxbuttons'),
				),

				'url' => array(
					'type' => 'link',
					'label' => __('Destination URL [optional]', 'maxbuttons'),
				),

				'window' => array(
					'type' => 'checkbox',
					'default' => false,
					'label' => __('Open in a new window [optional]', 'maxbuttons'),
				),
 
			), 
			plugin_dir_path(__FILE__)
		);

 

	}
	
	function get_template_name($instance) {
		return 'base';
	}
	
    function get_style_name($instance) {
        return '';
    }	
		
}

siteorigin_widget_register('sow-maxbutton', __FILE__, maxUtils::namespaceit('Widget_MaxButtons_Widget') );
