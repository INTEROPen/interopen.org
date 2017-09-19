<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

// new class for the future. 
class maxUtils
{
 
	protected static $timings = array();
	protected static $time_operations = array(); 
	protected static $timer = 0;
		
	
	/** Callback for array filter to prepend namepaces. **/ 
	public static function array_namespace($var) 
	{
		$namespace = __NAMESPACE__ . '\\'; // PHP 5.3 
		return ($namespace . $var); 
	}	
	
	public static function namespaceit($var)
	{
		$namespace = __NAMESPACE__ . '\\'; // PHP 5.3 
		return $namespace . $var;
	}
	
	// central ajax action handler 
	public static function ajax_action()
	{
		$status = 'error'; 

		$plugin_action = isset($_POST['plugin_action']) ? sanitize_text_field($_POST['plugin_action']) : ''; 
		$nonce = isset($_POST['nonce']) ? $_POST['nonce'] : false; 
		$message = __( sprintf("No Handler found for action %s ", $plugin_action), 'maxbuttons'); 
				
		if (! wp_verify_nonce($nonce, 'maxajax') )
		{
			$message = __('Nonce not verified', 'maxbuttons');
		}
		else
		{
			do_action('maxbuttons/ajax/' . $plugin_action, $_POST); 			
		}
		
		echo json_encode( array( 'status' => $status, 
								 'message' => $message, 
							    )
						); 
		wp_die(); 
	}
	
	public static function translit($string) 
	{
		require_once(MB()->get_plugin_path() . "assets/libraries/url_slug.php"); 
		$string = mb_url_slug($string, array("transliterate" => true)); 
		return $string;
	}

	public static function selectify($name, $array, $selected, $target = '', $class = '')
	{
		// optional target for js updating
		if ($target != '' ) 
			$target = " data-target='$target' "; 
		if ($class != '') 
			$class = " class='$class' "; 
		$output = "<select name='$name' id='$name' $target $class>";
		
		foreach($array as $key => $value) 
		{
			$output .= "<option value='$key' " . selected($key, $selected, false) . ">$value</option>"; 
		}
		$output .= "</select>"; 
		
		return $output;
	
	}


	static function hex2rgba($color, $opacity) {
		// Grab the hex color and remove #
		$hex = str_replace("#", "", $color);

		// Convert hex to rgb
		if(strlen($color) == 3) {
			// If in the #fff variety
			$r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
			$g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
			$b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
		} else {
			// If in the #ffffff variety
			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));
		}
	
		// The array of rgb values
		$rgb_array = array($r, $g, $b);
	
		// Catch for opacity when the button has not been saved
		if($opacity == '') {
			$alpha = 1;
		} else {
			// Alpha value in decimal when an opacity has been set
			$alpha = $opacity / 100;
		}

		// The rgb values separated by commas
		$rgb = implode(", ", $rgb_array);
	
		// Spits out rgba(0, 0, 0, 0.5) format
		return 'rgba(' . $rgb . ', ' . $alpha . ')';
	}
	
	static function strip_px($value) {
		return rtrim( intval($value), 'px');
	}

	static function generate_font_sizes($start, $end, $step  = 1) 
	{
		$sizes = array(); 
		 
		for ($i = $start; $i <= $end; $i += $step)
		{
			$sizes[$i] = $i; 
			
		}
		return $sizes; 
		
	}


	static function get_media_query($get_option = 1)
	{
		
		$queries = array("phone" =>  "only screen and (max-width : 480px)",
					"phone_land" => "only screen and (min-width : 321px) and (max-width : 480px)", 
				  	"phone_portrait" => " only screen and (max-width : 320px)", 
				  	"ipad" => "only screen and (min-width : 768px) and (max-width : 1024px)",
				  	"medium_phone" => "only screen and (min-width: 480px) and (max-width: 768px)",
				  	"ipad_land" => "only screen and (min-device-width : 768px) and (max-device-width : 1024px) and (orientation : landscape)", 
				  	"ipad_portrait" => "only screen and (min-device-width : 768px) and (max-device-width : 1024px) and (orientation : portrait)",
				  	"desktop" => "only screen and (min-width : 1224px)",
				  	"large_desktop" => "only screen and (min-width : 1824px)", 
					   );
 
		 $query_names = array(
						  	"phone" => __("Small phones","maxbuttons"), 
						  	"phone_land" => __("Small phones (landscape)","maxbuttons"), 
						  	"phone_portrait" => __("Small phones (portrait)","maxbuttons"), 
						  	"medium_phone" => __("Medium-size (smart)phone","maxbuttons"),
						  	"ipad" => __("Ipad (all) / Large phones","maxbuttons"),
						  	"ipad_land" => __("Ipad landscape","maxbuttons"), 
						  	"ipad_portrait" => __("Ipad portrait","maxbuttons"),
						  	"desktop" => __("Desktop","maxbuttons"),
						  	"large_desktop" => __("Large desktops","maxbuttons"), 
						  	"custom" => __("Custom size","maxbuttons"), 
						  	);	
						  	
		$query_descriptions = array(
							"phone" => __("Optimized for small smartphones ( screen sizes under 480px )","maxbuttons"),
							"phone_land" => __("Optimzed for small smartphones in landscape and higher ( screen sizes 321px - 480px)","maxbuttons"), 
							"phone_portrait" => __("Optimized for small phones ( screen size max 320px )","maxbuttons"), 
							"ipad" => __("Optimized for devices between 768px and 1024px","maxbuttons"), 
							"medium_phone" => __("Optimized for medium sizes devices between 480px and 768px","maxbuttons"), 
							"ipad_land" => __("Optimized for devices between 768px and 1024px in landscape","maxbuttons"), 
							"ipad_portrait" => __("Optimized for deviced between 768px and 1024 in portrait","maxbuttons"), 
							"desktop" => __("Desktop screens from 1224px","maxbuttons"),
							"large_desktop" => __("Large desktop screens, from 1824px","maxbuttons"),
							"custom" => __("Set your own breakpoints","maxbuttons"), 
							); 
							
	
		switch($get_option)
		{
			case 1: 
				return $query_names;
			break;
			case 2: 
				return $queries; 
			break;
			case 3: 
				return $query_descriptions;
			break;
		}
	
	}
	
	static function get_buttons_table_name($old = false) 
	{
		self::addTime('Legacy Function call : get_buttons_table_name'); 
		return self::get_table_name($old);
	}
	
	static function get_table_name($old = false) {
		global $wpdb;
		if ($old)
			return $wpdb->prefix . 'maxbuttons_buttons';
		else
			return $wpdb->prefix . 'maxbuttonsv3'; 
	}

	static function get_collection_table_name() { 
		global $wpdb; 
		return $wpdb->prefix . 'maxbuttons_collections'; 
	
	}

	static function get_coltrans_table_name() { 
		global $wpdb; 
		return $wpdb->prefix . 'maxbuttons_collections_trans'; 
	
	}
	
	/* Replacement function for Wordpress' transients and problematic name length.  */
	static function get_transient($name) 
	{
		global $wpdb; 
//		self::removeExpiredTrans(); 
		
		if ($name == '') 
			return false;
		
		$table = self::get_coltrans_table_name();
		
		$sql = "SELECT value FROM $table where name= '%s' "; 
		$sql = $wpdb->prepare($sql, $name); 
		
		$var = $wpdb->get_var($sql); 
		
		if (is_null($var)) 
			$var = false;
			
		return $var; 
		
	}
	
	
	static function set_transient($name, $value , $expire = -1 )
	{
		global $wpdb; 
 
			
		if ($expire == -1 )
			$expire = HOUR_IN_SECONDS * 4; 
			
		if ($name == '') 
			return false; 
		
		$expire_time = time() + $expire; 
		
		$table = self::get_coltrans_table_name();
				
		// prevent doubles, remove any present by this name
		self::delete_transient($name); 
		 
		$wpdb->insert($table, 
			array("name" => $name, 
				  "value" => $value, 
				  "expire" => $expire_time
		 	), 
		 	array("%s","%s","%d")); 
		
			
	}	
	
	static function delete_transient($name) 
	{
		global $wpdb; 
		
 
		$table = self::get_coltrans_table_name();
		$wpdb->delete($table, array("name" => $name), array('%s') ); 

	}
	
	static function removeExpiredTrans() 
	{
		global $wpdb; 
		
		$table = self::get_coltrans_table_name();
		$sql = "DELETE FROM $table WHERE expire <  UNIX_TIMESTAMP(NOW())"; 
		$return = $wpdb->query($sql);
 
		if($return === false)
		{
			$error = "Database error " . $wpdb->last_error;
			MB()->add_notice('error', $error); 
			$install = MB()->getClass('install');
			$install->create_database_table(); 
		}
 
	}
	
	/** Function will try to unload any FA scripts other than MB from WP. In case of conflict */ 
	static function fixFAConflict() 
	{
		$forcefa = get_option('maxbuttons_forcefa'); 
		
		if ($forcefa != '1') 
			return;
	
		global $wp_styles;

		$our_fa_there = false; 
		
		foreach($wp_styles->registered as $script => $details) 
		{
 			if ($script == 'mbpro-font-awesome') 
 			{
 				$our_fa_there = true; 
 				break;
 			}			
		}
		
		// fix nothing on pages where we are not loading.
		if (! $our_fa_there) 
			return; 
		

		// Loop through all registered styles and remove any that appear to be Font Awesome.
		foreach ( $wp_styles->registered as $script => $details ) {
			$src = isset($details->src) ? $details->src : false;

 			if ($script == 'mbpro-font-awesome') 
 			{
 				$mbpro_src = $src;
 				continue; // exclude us
 			}
 			
			if ( false !== strpos( $script, 'fontawesome' ) || false !== strpos( $script, 'font-awesome' ) ) {
				wp_dequeue_style( $script );
			}
			if ($src && ( false !== strpos($src, 'font-awesome') || false !== strpos($src, 'fontawesome') ) )
			{
				wp_dequeue_style( $script );			
			}

		}
		
		// This is a fix specific for NGGallery since they load their scripts weirdly / wrongly, but do check for the presence of a style named 'fontawesome' . 
		wp_register_style('fontawesome', $src);

	}
		

	static function timeInit()
	{
		if ( ! defined('MAXBUTTONS_BENCHMARK') || MAXBUTTONS_BENCHMARK !== true)
			return;
				
		self::$timer = microtime(true);

		if (is_admin()) 
			add_filter("admin_footer",array(self::namespaceit('maxUtils'), "showTime"), 100); 
		else
			add_action("wp_footer",array(self::namespaceit('maxUtils'), "showTime")); 
	
	}

	static function addTime($msg)
	{
		if ( ! defined('MAXBUTTONS_BENCHMARK') || MAXBUTTONS_BENCHMARK !== true)
			return;

		
		self::$timings[] = array("msg" => $msg,"time" => microtime(true)); 
	}
	
	static function startTime($operation)
	{
		if ( ! defined('MAXBUTTONS_BENCHMARK') || MAXBUTTONS_BENCHMARK !== true)
			return;
			
		self::$time_operations[$operation][] = array("start" => microtime(true), 
												   "end" => 0, 
											); 
	
	}
	
	static function endTime($operation) 
	{
		if ( ! defined('MAXBUTTONS_BENCHMARK') || MAXBUTTONS_BENCHMARK !== true)
			return;
			
		$timedcount = count(self::$time_operations[$operation]); 
		for ($i = 0; $i < $timedcount; $i++) 
		{
			if (self::$time_operations[$operation][$i]["end"] == 0)
			{
				self::$time_operations[$operation][$i]["end"] = microtime(true);
				break;
			}
		}
		 
	}
	
	static function showTime()
	{
		if ( ! defined('MAXBUTTONS_BENCHMARK') || MAXBUTTONS_BENCHMARK !== true)
		return;
			
			$timer = self::$timer;
			$text = ''; 
			$text .=  "<div id='mb-timer'>"; 
			$text .= "<p><strong>Timed Operations</strong></p>"; 
			
			foreach(self::$time_operations as $operation => $operations) 
			{
				foreach($operations as $index => $data)
				{
					$start = $data["start"]; 
					$end = $data["end"]; 
					$duration = $end - $start; 
				
				
					$text .= "<span class='first'>$duration</span>
							  <span class='second'>$operation</span> 
							  <span class='third'>&nbsp; </span><br /> 
							 "; 
							 
			
				}
			}
			
			
			$text .= "<p><strong>" . __("MaxButtons Loading Time:","maxbuttons") . "</strong></p>"; 
			$prev_time =0;
			
			$time_array = array(); 
			
			foreach(self::$timings as $timing)
			{
				$cum = ($timing["time"] - $prev_time); 
				$text .= "<span class='first'>" . ($timing["time"] - $timer) . "</span><span class='second'> " . $timing["msg"] . "</span><span class='third'>$cum</span> <br /> "; 
				//$time_array[$cum] = $timing["msg"]; 
				$prev_time = $timing["time"];
			}

			/*ksort($time_array);
		
			$text .= "<br><br><strong>By time taken:</strong><br>"; 
			foreach($time_array as $timeline)
			{
				$text .= "$timeline <br />"; 
			}
		*/
			$text .= "</div> ";
			$text .= "<style>#mb-timer { margin-left: 180px; } 
					#mb-timer span { 
						display: inline-block;
						font-size: 12px;
					}
					#mb-timer span.first { 
						width: 170px; 
					}
					#mb-timer span.second { 
						width: 300px; 
					}
					#mb-timer span.third { 
						width: 150px; 
					}										
					</style>"; 
					
			echo $text;
			
			
			//return $filter . $text; 
	}
}
