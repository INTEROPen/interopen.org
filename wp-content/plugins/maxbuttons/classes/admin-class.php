<?php
namespace MaxButtons; 
defined('ABSPATH') or die('No direct access permitted');

class maxButtonsAdmin
{
	
	protected $wpdb; 
	protected static $instance = null; 
	
	function __construct()
	{
		global $wpdb; 
		$this->wpdb = $wpdb;
	}
	
	public static function getInstance()
	{
		if (is_null(self::$instance)) 
			self::$instance = new maxButtonsAdmin(); 
		
		return self::$instance; 
	
	}

	public function loadFonts() 
	{
		$fonts = array(
			'' => '',
			'Arial' => 'Arial',
			'Courier New' => 'Courier New', 
			'Georgia' => 'Georgia',
			'Tahoma' => 'Tahoma',
			'Times New Roman' => 'Times New Roman',
			'Trebuchet MS' => 'Trebuchet MS',
			'Verdana' => 'Verdana'
		);
		return $fonts;
	}

	/* Get multiple buttons 
	
		Used for overview pages, retrieve buttons on basis of passed arguments. 
		
		@return array Array of found buttons with argument
	*/	
	
	function getButtons($args = array())
	{
		$defaults = array(
			"status" => "publish", 
			"orderby" => "id", 
			"order" => "DESC",
			"limit" => 20, 
			"paged" => 1, 
		);
		$args = wp_parse_args($args, $defaults); 
		
		$limit = intval($args["limit"]); 
		$page = intval($args["paged"]);
		$escape = array(); 
		$escape[] = $args["status"];
		
		// 'white-list' escaping
		switch ($args["orderby"])
		{
			case "id"; 
				$orderby = "id"; 
			break;
			case "name": 
			default: 
				$orderby = "name"; 	
			break;

		}
		
		switch($args["order"])
		{
			case "DESC": 
			case "desc": 
				$order = "DESC"; 
			break;
			case "ASC": 
			case "asc": 
			default:
				$order = "ASC"; 
			break;
		}

		
		$sql = "SELECT id FROM " . maxUtils::get_table_name() . " WHERE status = '%s'"; 
		if ($args["orderby"] != '')
		{
			$sql .=  " ORDER BY $orderby $order"; 
 
		}	 
	 
	 	if ($limit > 0) 
	 	{

	 		if ($page == 1 ) 
	 			$offset = 0; 
	 		else 
	 			$offset = ($page-1) * $limit;
	 		
	 		$sql .= " LIMIT $offset, $limit "; 
		}
		
		$sql = $this->wpdb->prepare($sql,$escape); 
 		
		$buttons = $this->wpdb->get_results($sql, ARRAY_A);
 
		
		return $buttons;
		
	}
	
	function getButtonCount($args = array())
	{
		$defaults = array(
			"status" => "publish", 
 
		);
		$args = wp_parse_args($args, $defaults); 
		
		$sql = "SELECT count(id) FROM " . maxUtils::get_table_name() . " WHERE status = '%s'"; 
		$sql = $this->wpdb->prepare($sql, $args["status"] ); 
		$result = $this->wpdb->get_var($sql);
		return $result;
		
	}
	
	function getButtonPages($args = array())
	{
		$defaults = array(
			"limit" => 20, 
			"paged" => 1, 
			"status" => "publish", 
			"output" => "list", 			// not used, future arg. 
			"view" => "all",

		);

		$args = wp_parse_args($args, $defaults); 

		$limit = intval($args["limit"]); 
		$page = intval($args["paged"]); 
		$view = $args["view"];

		$total = $this->getButtonCount(array("status" => $args["status"])); 
		
		$num_pages = ceil($total / $limit); 
 
		if ($num_pages == 0) $num_pages = 1; // lowest limit, page 1 
		$output = ''; 
		$url = $_SERVER['REQUEST_URI'];

		$url = remove_query_arg("view", $url); 
		$url = add_query_arg("view", $view, $url);

		$first_url = ($page != 1 ) ? add_query_arg("paged", 1, $url) : false;
		$last_url = ($page != $num_pages) ? add_query_arg("paged", $num_pages, $url) : false;
		$next_url = ($page != $num_pages) ? add_query_arg("paged", ($page + 1), $url) : false;
		$next_page = ($page != $num_pages) ? ($page + 1) : false;
		$prev_page = ($page != 1)  ? ($page -1 ) : false; 
		$prev_url = ($page != 1 ) ? add_query_arg("paged", ($page -1), $url) : false;
		

		$return = array(
			"first" => 1, 
			"base" => remove_query_arg("paged",$url), 
			"first_url" => esc_url($first_url),
			"last"  => $num_pages,
			"last_url" =>  esc_url($last_url),
			"next_url" => esc_url($next_url), 
			"prev_url" => esc_url($prev_url),
			"prev_page" => $prev_page, 
			"next_page" => $next_page, 
			"total" => $total, 
			"current" => $page, 
			
			
			
		);
		
		return $return;
	}
	
	static function getAjaxButtons()
	{
		
		$admin = self::getInstance();
		$args = array(); 

		$paged = (isset($_REQUEST["paged"])) ? intval($_REQUEST["paged"]) : 1; 
		if ($paged > 0) 
			$args["paged" ] = $paged;

		
		$button = new MaxButton(); 
		$buttons = $admin->getButtons($args);
	
		echo "<div id='maxbuttons'><div class='preview-buttons'>";

		echo '<div class="tablenav top"> ';
		echo "<span class='hint'>" . __('Click on a button to select it and add the shortcode to the editor', 'maxbuttons') . "</span>"; 		
		do_action('mb-display-pagination', $args); 
		echo '<span class="loading"></span>'; 
		echo '</div>'; 


		if (count($buttons) == 0) 
		{
 
 			$url = admin_url('admin.php?page=maxbuttons-controller&action=edit');
			echo "<p><strong>" . __("You didn't add any buttons yet!","maxbuttons") . "</strong></p>"; 
			echo "<P>" . sprintf(__("Click %shere%s to add one", "maxbuttons"), 
					"<a href='$url' target='_blank'>", "</a>") . "</strong></p>"; 

		}
		
		foreach($buttons as $b)
		{
			
			$button_id = $b["id"]; 
			$button->set($button_id);
			echo "<div class='button-row button-select' data-button='$button_id'>"; 
			echo "<span class='col col_insert'> "; 

			 echo "<span class='small'>[ID: $button_id ]</span>
			 </span>  "; 
			 
			echo "<span class='col col_button'><div class='shortcode-container'>";
			 $button->display(array("mode" => "preview", "load_css" => "inline" ));
			echo "</div></span>"; 
			echo "<span class='col col_name'>" . $button->getName() . "</span>";
			echo "</div>";  
		}
		echo '<div class="tablenav bottom"> ';
		do_action('mb-display-pagination', $args); 
		echo '<span class="loading"></span>'; 		
		echo '</div>'; 

		
		echo "</div></div>";
		echo "<p>&nbsp;</p>";  
			
		exit(); 
		
	}
	function get_header($args =array() )
	{
		$defaults = array(
			"tabs_active" => false,
			"title" => "",		
			"action" => "", 
			); 
		
		$args = wp_parse_args($args, $defaults); 
		extract($args);
		
		include_once(MB()->get_plugin_path() . "includes/admin_header.php"); 
	
	}

	
	function get_footer()
	{
		include_once(MB()->get_plugin_path() . "includes/admin_footer.php"); 
	
	}
	
	// unified (future way to end ajax requests + feedback 
	function endAjaxRequest($args = array())
	{
		$defaults = array(
			"error" => true, // can have errors and still result true on success
			"result" => true, 
			"body" => "", 
			"title" => "",
			"data" => array(), 
			);
			
		$args = wp_parse_args($args, $defaults); 
		
		echo json_encode($args);
		die(); 
					
	
	}
	
	function log($action, $message) 
	{
		if (! defined('MAXBUTTONS_DEBUG') || ! MAXBUTTONS_DEBUG)
			return; 
		
		$stack = debug_backtrace(); 
		$caller = $stack[1]['function'];	
	
		$dir = MB()->get_plugin_path() . "logs"; 
		if (! is_dir($dir)) 
			@mkdir($dir, 0777, true); // silently fail here. 
	
		if (! is_dir($dir)) 
			return false; 
	
		$file = fopen($dir . "/maxbuttons.log", "a+"); 
		$now = new DateTime() ; 
		$now_format = $now->format("d/M/Y H:i:s"); 
		
		$write_string = "[" . $now_format . "] $action - $message ( $caller )"; 
		fwrite($file, $write_string); 
		fclose($file); 
		 
		
	}
}
