<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

class mbCake 
{


	public static function init() 
	{
	 	add_action('register_shortcode_ui', array(maxUtils::namespaceit('mbCake'), 'register')); 
	 	add_action('init', array(maxUtils::namespaceit('mbCake'), 'initField'));
	 	add_action('shortcode_ui_after_do_shortcode', array(maxUtils::namespaceit('mbCake'), 'shortcode')); 
	 	
	 	// Load FA within TinyMCE
	 	add_action('admin_enqueue_scripts', array(maxUtils::namespaceit('mbCake'), 'editor_styles')) ; 
	 	
	}

	public static function initField() 
	{
		require_once('class-field-maxbutton.php'); 
		
		Shortcake_Field_MaxButton::get_instance(); 	
	
	}
	
	public static function shortcode($shortcode) 
	{

		if (strpos ($shortcode, 'maxbutton') === false) 
			return; // not our shorts 
 
		// style controls the output - if set to something else assume css is there. 
		if (strpos ( $shortcode, 'style') === false)
		{
			preg_match('/id=?("|\'|)([0-9]+)/i', $shortcode, $match); 
			if (count($match) == 0) 
				return; // happens when adding new button from shortcake 

			$button_id = $match[2];  	
 
			$button = new maxButton(); 
			$button->set($button_id); 
		
			$button->parse_button(); 
			$button->parse_css('preview');
			$button->display_css(); 
		}
	}
	
	public static function editor_styles()
	{
			$fa_url = apply_filters("mb_fa_url", MB()->get_plugin_url() . 'assets/libraries/font-awesome/css/font-awesome.min.css'); 
			if ($fa_url != false && $fa_url != '')
			{				
				add_editor_style($fa_url);
			}
	}

	public static function register()
	{

		shortcode_ui_register_for_shortcode( 'maxbutton',
		array(
			/*
			 * How the shortcode should be labeled in the UI. Required argument.
			 */
			'label' => esc_html__( 'MaxButtons', 'maxbuttons' ),
			/*
			 * Include an icon with your shortcode. Optional.
			 * Use a dashicon, or full URL to image.
			 */
			'listItemImage' => '<img src="' . MB()->get_plugin_url() . 'assets/integrations/shortcake/assets/banner.png">', 
			//'dashicons-editor-quote',
			/*
			 * Limit this shortcode UI to specific posts. Optional.
			 */
			//'post_type' => array( 'post' ),
			
			/*
			 * Register UI for the "inner content" of the shortcode. Optional.
			 * If no UI is registered for the inner content, then any inner content
			 * data present will be backed up during editing.
			 */
		/*
			 * Register UI for attributes of the shortcode. Optional.
			 *
			 * If no UI is registered for an attribute, then the attribute will 
			 * not be editable through Shortcake's UI. However, the value of any 
			 * unregistered attributes will be preserved when editing.
			 * 
			 * Each array must include 'attr', 'type', and 'label'.
			 * 'attr' should be the name of the attribute.
			 * 'type' options include: text, checkbox, textarea, radio, select, email, 
			 *     url, number, and date, post_select, attachment, color.
			 * Use 'meta' to add arbitrary attributes to the HTML of the field.
			 * Use 'encode' to encode attribute data. Requires customization to callback to decode.
			 * Depending on 'type', additional arguments may be available.
			 */
			'attrs' => array(

				array(
					'label'       => esc_html__( 'Button', 'maxbuttons' ),
					'attr'        => 'id',
					'type'        => 'MaxButton',
					'meta' 		  => array('select' => __("Select a button","maxbuttons")), 

				),
				array(
					'label'  => esc_html__( 'Custom URL [optional]', 'maxbuttons' ),
					'attr'   => 'url',
					'type'   => 'url',
					'encode' => true,
					'meta'   => array(
						'placeholder' => esc_html__( 'http://', '' ),
						'data-test'   => 1,
					),
				),
				array( 
					'label' => esc_html__( 'Custom Text [optional]', 'maxbuttons' ),
					'attr' => 'text',
					'type' => 'text',
					'query' => array( 'post_type' => 'page' ),

				),
			),
		)
	);	
	
	
	}

}

mbCake::init(); 
