<?php
/*
The MIT License (MIT)

Copyright (c) 2017 Twitter Inc.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

namespace Twitter\WordPress\Widgets\Advertising;

/**
 * Add embedded profile timeline as a WordPress widget
 *
 * @see http://codex.wordpress.org/Widgets_API WordPress widgets API
 *
 * @since 2.0.0
 */
class Tracking extends \Twitter\WordPress\Widgets\Widget
{
	/**
	 * Widget base ID
	 *
	 * @since 2.0.1
	 *
	 * @type string
	 */
	const BASE_ID = 'twitter-tracking';

	/**
	 * Class of the related shortcode handler
	 *
	 * @since 2.0.0
	 *
	 * @type string
	 */
	const SHORTCODE_CLASS = '\Twitter\WordPress\Shortcodes\Advertising\Tracking';

	/**
	 * Register widget with WordPress
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function __construct()
	{
		$shortcode_class = static::SHORTCODE_CLASS;
		parent::__construct(
			static::BASE_ID, // Base ID
			$shortcode_class::featureName(), // name
			array(
				'description' => static::getDescription(),
			)
		);
	}

	/**
	 * Get the base ID used to identify widgets of this type installed in a widget area
	 *
	 * @since 2.0.1
	 *
	 * @return string widget base ID
	 */
	public static function getBaseID()
	{
		return static::BASE_ID;
	}

	/**
	 * Describe the functionality offered by the widget
	 *
	 * @since 2.0.0
	 *
	 * @return string description of the widget functionality
	 */
	public static function getDescription()
	{
		return __( 'Track Twitter advertising conversion or build a custom audience for ad targeting', 'twitter' );
	}

	/**
	 * Front-end display of widget
	 *
	 * @since 2.0.0
	 *
	 * @param array $args     Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 *
	 * @return void
	 */
	public function widget( $args, $instance )
	{
		$shortcode_class = static::SHORTCODE_CLASS;
		if ( ! method_exists( $shortcode_class, 'shortcodeHandler' ) ) {
		    return;
		}
		$html = $shortcode_class::shortcodeHandler( $instance );
		if ( ! $html ) {
			return;
		}

		// Allow HTML markup set by author, site
		// @codingStandardsIgnoreLine WordPress.XSS.EscapeOutput.OutputNotEscaped
		echo $args['before_widget'];

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		if ( $title ) {
			// Allow HTML markup set by author, site
			// @codingStandardsIgnoreLine WordPress.XSS.EscapeOutput.OutputNotEscaped
			echo $args['before_title'];

			// Allow HTML in title. Link to Twitter datasource might be common use
			// @codingStandardsIgnoreLine WordPress.XSS.EscapeOutput.OutputNotEscaped
			echo $title;

			// Allow HTML markup set by author, site
			// @codingStandardsIgnoreLine WordPress.XSS.EscapeOutput.OutputNotEscaped
			echo $args['after_title'];
		}

		// escaped in markup builder
		// @codingStandardsIgnoreLine WordPress.XSS.EscapeOutput
		echo $html;

		// Allow HTML markup set by author, site
		// @codingStandardsIgnoreLine WordPress.XSS.EscapeOutput.OutputNotEscaped
		echo $args['after_widget'];
	}

	/**
	 * Settings update form
	 *
	 * @since 2.0.0
	 *
	 * @param array $instance Current settings
	 *
	 * @return void
	 */
	public function form( $instance )
	{
		$shortcode_class = static::SHORTCODE_CLASS;
		if ( ! method_exists( $shortcode_class, 'getShortcodeDefaults' ) ) {
			return;
		}
		$instance = wp_parse_args(
			(array) $instance,
			$shortcode_class::getShortcodeDefaults()
		);

		?><p><label for="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>"><?php echo esc_html( _x( 'Website tag ID', 'Identifier used to track an advertising campaign including conversion and custom audiences', 'twitter' ) . ':' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'id' ) ); ?>" type="text" inputmode="verbatim" spellcheck="false" pattern="<?php echo esc_attr( \Twitter\Helpers\Validators\WebsiteTag::getPattern() ); ?>" maxlength="<?php echo esc_attr( \Twitter\Helpers\Validators\WebsiteTag::MAX_LENGTH ); ?>" value="<?php echo esc_attr( trim( $instance['id'] ) ); ?>" required<?php
			// @codingStandardsIgnoreLine WordPress.XSS.EscapeOutput.OutputNotEscaped
			echo \Twitter\WordPress\Helpers\HTMLBuilder::closeVoidHTMLElement();
		?>></p><?php
	}

	/**
	 * Update a widget instance
	 *
	 * @since 2.0.0
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 *
	 * @return bool|array settings to save or false to cancel saving
	 */
	public function update( $new_instance, $old_instance )
	{
		$new_instance = (array) $new_instance;

		$id = '';
		if ( isset( $new_instance['id'] ) ) {
			$id = \Twitter\Helpers\Validators\WebsiteTag::sanitize( $new_instance['id'] );
		}
		// ID required, otherwise nothing to track
		if ( ! $id ) {
			return false;
		}

		return array(
			'id' => $id,
		);
	}
}
