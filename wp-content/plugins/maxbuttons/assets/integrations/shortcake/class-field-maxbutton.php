<?php 
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
// Maxbutton field class 

class Shortcake_Field_MaxButton
{

	/**
	 * Shortcake Color Field controller instance.
	 *
	 * @access private
	 * @var object
	 */
	private static $instance;

	/**
	 * All registered post fields.
	 *
	 * @access private
	 * @var array
	 */
	private $post_fields  = array();

	/**
	 * Settings for the Color Field.
	 *
	 * @access private
	 * @var array
	 */
	private $fields = array(
		'MaxButton' => array(
			'template' => 'fusion-shortcake-field-maxbutton',
			'view'     => 'editAttributeFieldMaxButton',
		),
	);

	/**
	 * Get instance of Shortcake Color Field controller.
	 *
	 * Instantiates object on the fly when not already loaded.
	 *
	 * @return object
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
			self::$instance->setup_actions();
		}
		return self::$instance;
	}

	/**
	 * Set up actions needed for Color Field
	 */
	private function setup_actions() {
		add_filter( 'shortcode_ui_fields', array( $this, 'filter_shortcode_ui_fields' ) );
		add_action( 'shortcode_ui_loaded_editor', array( $this, 'load_template' ) );
		add_action( 'enqueue_shortcode_ui', array( $this, 'action_enqueue_shortcode_maxbutton' ), 99 );
				
	}

	/**
	 * Whether or not the color attribute is present in registered shortcode UI
	 *
	 * @return bool
	 */
	private function maxbutton_attribute_present() {

		foreach ( \Shortcode_UI::get_instance()->get_shortcodes() as $shortcode ) {

			if ( empty( $shortcode['attrs'] ) ) {
				continue;
			}

			foreach ( $shortcode['attrs'] as $attribute ) {
				if ( empty( $attribute['type'] ) ) {
					continue;
				}

				if ( 'MaxButton' === $attribute['type'] ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Add Color Field settings to Shortcake fields
	 *
	 * @param array $fields
	 * @return array
	 */
	public function filter_shortcode_ui_fields( $fields ) {
		return array_merge( $fields, $this->fields );
	}

	/**
	 * Output templates used by the color field.
	 */
	public function load_template() {

		if ( ! $this->maxbutton_attribute_present() ) {
			return;
		}

		?>

		<script type="text/html" id="tmpl-fusion-shortcake-field-maxbutton">
			<div class="field-block shortcode-ui-field-maxbutton shortcode-ui-attribute-{{ data.attr }}">
			 	<button class="button-primary maxbutton_media_button" name="{{ data.attr }}" value="{{ data.value }}">{{data.meta.select}}</button>
				<p class='button_preview'>&nbsp;</p>
					
			
				<input id='{{ data.attr }}' type='hidden' name='button_id' value='' >
				<# if ( typeof data.description == 'string' ) { #>
					<p class="description">{{{ data.description }}}</p>
				<# } #>
			</div>
		</script>

		<?php
	}

	public function action_enqueue_shortcode_maxbutton() 
	{
		
		wp_enqueue_script('maxbutton-shortcake', MB()->get_plugin_url() . 'assets/integrations/shortcake/edit-attribute-field-maxbutton.js',
					array('jquery','backbone','mce-view', 'shortcode-ui-js-hooks','shortcode-ui'), null, true);
		//wp_localize_script( 'maxbutton-shortcake', 'shortcodeUIFieldData', $this->fields );
	
	}

} // class
