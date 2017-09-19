<?php 
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
// Add a MaxButton widget to the pagebuilder 

function maxButtons_add_widget_tabs($tabs) {
    $tabs[] = array(
        'title' => __('MaxButtons', 'maxbuttons'),
        'filter' => array(
            'groups' => array('maxbuttons')
        )
    );

    return $tabs;
}
add_filter('siteorigin_panels_widget_dialog_tabs', maxUtils::namespaceit('maxbuttons_add_widget_tabs'), 20);

function maxbuttons_add_widgets($folders)
{
	$folders[] = plugin_dir_path(__FILE__). 'widgets/';

	return $folders;
}
add_filter('siteorigin_widgets_widget_folders', maxUtils::namespaceit('maxbuttons_add_widgets'), 20);



function maxbuttons_fields_class_paths( $class_paths ) {
   
    $class_paths[] = plugin_dir_path( __FILE__ ) . "fields/";
    return $class_paths;
}
add_filter( 'siteorigin_widgets_field_class_paths', maxUtils::namespaceit('maxbuttons_fields_class_paths'), 20 );

function maxbuttons_class_prefixes( $class_prefixes ) {
    $class_prefixes[] = maxUtils::namespaceit('MaxButton_Widget_Field_');
    return $class_prefixes;
}
add_filter( 'siteorigin_widgets_field_class_prefixes', maxUtils::namespaceit('maxbuttons_class_prefixes'), 20 );





