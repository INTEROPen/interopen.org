<?php 
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

class maxInstall
{
	 static function activation_hook($network_wide) {
		if ($network_wide) {
			static::call_function_for_each_site( array('self','activate_plugin') );
		}
		else {
			static::activate_plugin();
		}
	}
	 static function deactivation_hook($network_wide) {

		if ($network_wide) {
			static::call_function_for_each_site( array('self','deactivate_plugin') );
		}
		else {
			static::deactivate_plugin();
		}
	}	

	// This should be done - once! Removed in time as well.
	static function check_database()
	{
		$checked = get_option("MB_DBASECHECK", true); 
		if ($checked !== false) // removing this option.
			delete_option('MB_DBASECHECK'); 
		
		$version = MAXBUTTONS_VERSION_NUM;
		$installed_version = MB()->get_installed_version(); 
		if ($version !== $installed_version)
		{
			$table = maxUtils::get_table_name(); 

			self::activate_plugin(); // always run the DBdelta on version mismatch.
			self::clear();
		}
	}

	static function activate_plugin($gocreate = true)
	{
		$button = new maxButton();
		$button->reset_cache(); //refresh cache
	
		static::create_database_table();
		static::migrate(); 
		static::upgradeUTF();
		update_option(MAXBUTTONS_VERSION_KEY, MAXBUTTONS_VERSION_NUM);
		$created = get_option("MBFREE_CREATED"); 
		if ($created == '' && $gocreate) 
		{  update_option("MBFREE_CREATED", time()); 
       	   update_option("MBFREE_HOMEURL", home_url()); 
		}

	}
	
	/** Function to clear out obsolete and old options, items etc. */
	static function clear() 
	{
		delete_option('MB_DBASECHECK');
	}
	
	/** Move data from old version database to new version 
		
	   Check if new database table is empty ( aka new ) to prevent migrating the same data multiple times then copy all rows from old table to the new one.  
	*/
	static function migrate()
	{
		global $wpdb; 
		
		$old_table = maxUtils::get_table_name(true); 
		$table = maxUtils::get_table_name(); 
 	
 		if (! self::maxbuttons_database_table_exists($old_table))
 		{
 			return; 
 		}
 	
		$sql = "SELECT id from $table"; 
		$result = $wpdb->get_results($sql, ARRAY_A); 
		if(count($result) > 0) 	return; // don't do this if table already has data. 

		$sql = "SELECT * FROM $old_table"; 
		$rows = $wpdb->get_results($sql, ARRAY_A); 
		
		if (count($rows) == 0 ) // no button in all table; all is good. 
			return true; 
		
 
		foreach($rows as $row) 
		{	
			$data = static::convertOldFields($row);
			$id = $data["id"]; 
			global $wpdb; 
			$wpdb->insert($table, array("id" => $id)); 
			
			//$data = apply_filters("mb-migration-data",$data, $row); 
			$button = new maxButton();
			$button->set($id);
			$button->save($data);
		}
	}
	
	/** Import fields from the old database format ( pre version 3.0 ) */
	static function convertOldFields($row)
	{
			$data = array(); 

			
			$data["id"] = (isset($row["id"])) ? $row["id"] : -1; 
			$data["name"] = $row["name"];
			$data["status"] = isset($row["status"]) ? $row["status"] : 'publish';   // happens with downloadable packs.
			$data["description"] = $row["description"]; 
			$data["url"] = $row["url"];
			$data["text"] = $row["text"];
			$data["new_window"] =  (isset($row["new_window"]) && $row["new_window"] != "") ? 1 : 0;
			$data["nofollow"] =  (isset($row["nofollow"]) && $row["nofollow"] != "") ? 1 : 0; 
			
			$data["font"] = $row["text_font_family"]; 
			$data["font_size"] = $row["text_font_size"]; 
			$data["font_style"] = $row["text_font_style"]; 
			$data["font_weight"] = $row["text_font_weight"]; 
			$data["text_shadow_offset_left"] = $row["text_shadow_offset_left"]; 
			$data["text_shadow_offset_top"] = $row["text_shadow_offset_top"]; 
			$data["text_shadow_width"] = $row["text_shadow_width"]; 
			$data["padding_top"] = $row["text_padding_top"]; 
			$data["padding_right"] = $row["text_padding_right"]; 
			$data["padding_bottom"] = $row["text_padding_bottom"]; 	
			$data["padding_left"] = $row["text_padding_left"];
			 
			$data["radius_top_left"] = $row["border_radius_top_left"]; 
			$data["radius_top_right"] = $row["border_radius_top_right"]; 
			$data["radius_bottom_left"] = $row["border_radius_bottom_left"]; 
			$data["radius_bottom_right"] = $row["border_radius_bottom_right"]; 
			$data["border_style"] = $row["border_style"]; 
			$data["border_width"] = $row["border_width"]; 
			$data["box_shadow_offset_left"] = $row["box_shadow_offset_left"]; 
			$data["box_shadow_offset_top"] = $row["box_shadow_offset_top"];		
			$data["box_shadow_width"] = $row["box_shadow_width"]; 
			
			$data["text_color"] = $row["text_color"]; 
			$data["text_shadow_color"] = $row["text_shadow_color"]; 
			$data["gradient_start_color"] = $row["gradient_start_color"]; 
			$data["gradient_end_color"] = $row["gradient_end_color"]; 
			$data["border_color"] = $row["border_color"]; 
			$data["box_shadow_color"] = $row["box_shadow_color"]; 
			
			$data["text_color_hover"] = $row["text_color_hover"]; 
			$data["text_shadow_color_hover"] = $row["text_shadow_color_hover"]; 
			$data["gradient_start_color_hover"] = $row["gradient_start_color_hover"]; 
			$data["gradient_end_color_hover"] = $row["gradient_end_color_hover"]; 
			$data["border_color_hover"] = $row["border_color_hover"]; 
			$data["box_shadow_color_hover"] = $row["box_shadow_color_hover"]; 
			
			$data["gradient_stop"] = (isset($row["gradient_stop"])) ? $row["gradient_stop"] : 45; 
			$data["gradient_start_opacity"] = (isset($row["gradient_start_opacity"])) ? $row["gradient_start_opacity"] : 100;
			$data["gradient_end_opacity"] = (isset($row["gradient_end_opacity"])) ? $row["gradient_end_opacity"] : 100;
			$data["gradient_start_opacity_hover"] = (isset($row["gradient_start_opacity_hover"])) ? $row["gradient_start_opacity_hover"] : 100;			
			$data["gradient_end_opacity_hover"] = (isset($row["gradient_end_opacity_hover"])) ? $row["gradient_end_opacity_hover"] : 100;
						
			$data["container_enabled"] =  (isset($row["container_enabled"]) && $row["container_enabled"] != "") ? 1 : 0; 
			$data["container_center_div_wrap"] = (isset($row["container_center_div_wrap_enabled"]) && $row["container_center_div_wrap_enabled"] != "") ? 1 : 0; 
			$data["container_width"] = isset($row["container_width"]) ? $row["container_width"] : ''; 
			$data["container_margin_top"] = isset($row["container_margin_top"]) ? $row["container_margin_top"] : ''; 
			$data["container_margin_right"] = isset($row["container_margin_right"]) ? $row["container_margin_right"] : ''; 
			$data["container_margin_bottom"] = isset($row["container_margin_bottom"]) ? $row["container_margin_right"] : ''; 
			$data["container_margin_left"] = isset($row["container_margin_left"]) ? $row["container_margin_left"] : ''; 
			$data["container_alignment"] = isset($row["container_alignment"]) ? $row["container_alignment"] : '';
			
			$data["status"] = (isset($row["status"])) ? $row["status"] : 'publish'; 
			 
			$data["external_css"] =  (isset($row["external_css"]) && $row["external_css"] != "") ? 1: 0; 
			$data["important_css"] =  (isset($row["important_css"]) && $row["important_css"] != "") ? 1 : 0;
			
			// icon
	 		$data["use_fa_icon"] = (isset($row["use_font_awesome_icon"]) && $row["use_font_awesome_icon"] != '') ? 1 : 0; 
	 		$data["fa_icon_value"] = (isset($row["font_awesome_icon"])) ? $row["font_awesome_icon"] : '';
	 		$data["fa_icon_size"] = (isset($row["font_awesome_icon_size"])) ? $row["font_awesome_icon_size"] : ''; 
	 		$data["icon_url"] = (isset($row["icon_url"])) ? $row["icon_url"] : '';
	 		$data["icon_alt"] = (isset($row["icon_alt"])) ? $row["icon_alt"] : ''; 
	 		$data["icon_position"] = (isset($row["icon_position"])) ? $row["icon_position"] : '';
	 		$data["icon_padding_top"] = (isset($row["icon_padding_top"])) ? $row["icon_padding_top"] : '';
	 		$data["icon_padding_bottom"] = (isset($row["icon_padding_bottom"])) ? $row["icon_padding_bottom"] : '';
	 		$data["icon_padding_left"] = (isset($row["icon_padding_left"])) ? $row["icon_padding_left"] : '';
	 		$data["icon_padding_right"] = (isset($row["icon_padding_right"]))? $row["icon_padding_right"] : '';

			// dimension
			$data["button_width"] = (isset($row["width"])) ? $row["width"] : ''; 
			$data["button_height"] = (isset($row["height"])) ? $row["height"] : ''; 

			// colorPro 
			$data["icon_color"] = (isset($row["icon_color"])) ? $row["icon_color"] : ''; 
			$data["icon_color_hover"] = (isset($row["icon_color_hover"])) ? $row["icon_color_hover"] : ''; 
			
			// textPro 
			$data["text2"] = $row["text2"]; 
			$data["font2"] = $row["text2_font_family"]; 
			$data["font_size2"] = $row["text2_font_size"]; 
			$data["font_style2"] = $row["text2_font_style"]; 
			$data["font_weight2"] = $row["text2_font_weight"]; 
			$data["padding_top2"] = $row["text2_padding_top"]; 
			$data["padding_right2"] = $row["text2_padding_right"]; 
			$data["padding_bottom2"] = $row["text2_padding_bottom"]; 	
			$data["padding_left2"] = $row["text2_padding_left"];
		
			$data["text_align"] = (isset($row["text_align"])) ? $row["text_align"] : ''; 
			$data["text_align2"] = (isset($row["text2_align"])) ? $row["text2_align"] : ''; 
	 		// here old / new Pro Database fields. 			
			
			return $data;
	}
	
	static function deactivate_plugin()
	{ 
			delete_option(MAXBUTTONS_VERSION_KEY);
	}


	static function maxbuttons_database_table_exists($table_name) {
		global $wpdb;
		return strtolower($wpdb->get_var("SHOW TABLES LIKE '$table_name'")) == strtolower($table_name);
	}


	 static function create_database_table() {
	 //global $maxbuttons_installed_version;
	 
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		$table_name = maxUtils::get_table_name();
		$button = new maxButton();
		$blocks = $button->getBlocks();
	
		// IMPORTANT: There MUST be two spaces between the PRIMARY KEY keywords
		// and the column name, and the column name MUST be in parenthesis.
		$sql = "CREATE TABLE " . $table_name . " ( 
					id int NOT NULL AUTO_INCREMENT, 
					 name varchar(100) NULL, 
					 status varchar(10) default 'publish' NOT NULL, 
					 cache text, 
				";
				

		foreach($blocks as $block)
		{
			$block_name = $block->get_name(); 
			
			$sql .= "" . $block_name . " TEXT NULL, \n "; 	
		}
	 
		$sql .= "updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				 created TIMESTAMP DEFAULT 0 NOT NULL, 
				 PRIMARY KEY  (id) )"; 

		$result = dbDelta($sql);		// always dbdelta			

		// Reset the cache if there were any left from before
		$button->reset_cache(); 

		// Collection table
		$collection_table_name = maxUtils::get_collection_table_name();
		
		$sql = "CREATE TABLE " . $collection_table_name . " ( 
					meta_id int(11) NOT NULL AUTO_INCREMENT, 
					collection_id int(11) NOT NULL, 
					collection_key varchar(255), 
					collection_value text, 
					PRIMARY KEY  (meta_id) ) 
					
				";

		dbDelta($sql);
		
		$collection_trans_table = maxUtils::get_coltrans_table_name();
		$sql = "CREATE TABLE $collection_trans_table ( 
 				name varchar(1000), 
				value varchar(255),
				expire int(11)
				); 
		";		
		$res = dbDelta($sql); 
	}
	
	/** Attempt to upgrade UTF table to UTFmb4 - see this article https://make.wordpress.org/core/2015/04/02/the-utf8mb4-upgrade/
	*/
	public static function upgradeUTF() 
	{
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		if (! function_exists('maybe_convert_table_to_utf8mb4')) 
			return; // Versions before 4.2.0 
	
		$table_name = maxUtils::get_table_name();
		$collection_table_name = maxUtils::get_collection_table_name();
	
		maybe_convert_table_to_utf8mb4($table_name);
		maybe_convert_table_to_utf8mb4($collection_table_name); 
	}
 
 	/** Routine for activation for WPMU - All blogs */
	public static function call_function_for_each_site($function) {
		global $wpdb;
	
		// Hold this so we can switch back to it
		$root_blog = $wpdb->blogid;
	
		// Get all the blogs/sites in the network and invoke the function for each one
		$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach ($blog_ids as $blog_id) {
			switch_to_blog($blog_id);
			call_user_func($function);
		}
	
		// Now switch back to the root blog
		switch_to_blog($root_blog);
	}
} // class

