<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
$collectionBlock["social"] = "socialCollectionBlock"; 

use \simple_html_dom as simple_html_dom;

class socialCollectionBlock extends collectionBlock
{
	protected $blockname = "social"; 
	protected $fields = array(
				"multifields" => array(
					"id" => "social-option", 
					"fields" => array(
						"network" => array("default" => "none"),
						"display_count" => array("default" => 0 ), 
						"count_threshold" => array("default" => 4),	
						"share_url_setting" => array("default" => "auto"),
						"share_url_custom" => array("default" => ''), 
						"blog_url" => array("default" => ''),  
 
						),
					),
			);
 	
 	protected $social_data = array(); 
 	protected $ajax_remote_count = 0; // failback for hanging remote requests in ajax_get_count. 
 		
							
	function __construct()
	{

		require_once( MB()->get_plugin_path() . "/classes/social-networks.php"); 
		
	}
 	function parse($domObj, $args)
 	{
 
 		$popup = isset($this->social_data["popup"]) ? $this->social_data["popup"] : array(); 
		$onload = isset($this->social_data["onload"]) ? $this->social_data["onload"] : array();		
 
 		$collection_items = $domObj->find('.maxcollection .mb-collection-item'); 
 		$i = 0; 
 
 		foreach($collection_items as $item) 
 		{
			//$this_item = $item->find('.maxbutton'); 
			$tag = 'data-doc-id'; 
			$doc_id = $item->$tag; 
			if (isset($popup[$doc_id])) 
			{
				$tag = 'data-popup'; // annoying.
				$json = htmlentities(json_encode($popup[$doc_id]), ENT_QUOTES, 'UTF-8');
				$item->$tag = $json; 
 			
 			}
 			if (isset($onload[$doc_id])) 
 			{
 				$tag = 'data-onload'; 
 				$json = htmlentities(json_encode($onload[$doc_id]), ENT_QUOTES, 'UTF-8');
 				$item->$tag = $json; 
 			
 			}
 			$i++; 

 		}
 
 		return $domObj;
 		
 	}
 	
 	function parseButtons($buttons)
 	{
 		$mf_field_id = $this->fields["multifields"]["id"]; 	 	

 		// set social behavoir on the buttons, overriding things
 		foreach($buttons as $index => $button) // index is order of buttons and should be kept like that
 		{
 			$button_id = $button->getID(); 
 			  			
	 		if (isset($this->data[$mf_field_id][$index][$button_id]))
	 		{
	 			$button_data = $button->get(); 
 
	 			// can happen if button collection button has been deleted. 
	 			$document_id = isset($button_data["document_id"]) ? $button_data["document_id"] : -1;

	 			$field_data = $this->data[$mf_field_id][$index][$button_id]; 

				$network = $field_data["network"]; 
				$maxSN = new maxSN($network); 
				$network_name = $maxSN->getNetworkName(); 

				$display_count = $field_data["display_count"]; 
				$count_threshold = $field_data["count_threshold"]; 
				
				// collect all variables here for conversion
				$share_data = $this->getShareData($field_data);
				$share_url = $share_data["url"]; 
				$share_title = isset($share_data["title"]) ? $share_data["title"] : '';
				$share_img = isset($share_data["img"]) ? $share_data["img"] : '';  
				
				$share_count = 0;
				
				$apply_vars = array("url" => $share_url, 
								    "count" => $share_count,
								    "title" => $share_title, 
								    "network_name" => $network_name, 
								    "img" => $share_img, 
								  );

				$network_url = $maxSN->getShareURL($network); 
				$network_url = $this->applyVars($network_url, $apply_vars);
				
					
				if ($network_url)
				{ 
					$buttons[$index]->setData("basic",array("url" => $network_url) );
				
				}	

				$count_to_display = ''; 
 				$text = isset($button_data["text"]["text"]) ? $button_data["text"]["text"] : '' ; 
				$text2 = isset($button_data["text"]["text2"]) ? $button_data["text"]["text2"] : ''; 
				
				if ($display_count == 1 && $this->is_supported('display_count', $network) ) 
				{
					$count = $maxSN->getShareCount(array("url" => $share_url, 
							"preview" => $this->is_preview,
					));
					
					if ($count === false) // signal for remote check 
					{
						$this->social_data["onload"][$document_id] = array("network" => $network, "share_url" => esc_url($share_url), "count_threshold" => $count_threshold, "text" => $text, "text2" => $text2 );
					
					}

					if($count >= $count_threshold )
					{
						$count_to_display = $count;	
						
					}
				 }
	 					
				// apply_vars. Always run this to get rid of placeholders ( defaults to emtpy ) 
				$apply_vars = array("count" => $count_to_display, "network_name" => $network_name);

				$text = $this->applyVars($text, $apply_vars);
				$text2 = $this->applyVars($text2, $apply_vars); 
				
				$button->setData("text",array("text" => $text, "text2" => $text2) );  

				// arrange JS stuff
				if ($maxSN->checkPopup() ) 
				{
					
					$dimensions = $maxSN->getPopupDimensions(); // ["popup_dimensions"]; 
					$this->social_data["popup"][$document_id] = array("width" => $dimensions[0], 
						"height" => $dimensions[1], 
						);
				}
 
	 				
	 		 } // isset button data
 			
 		}
 		
 		return $buttons;
 	
	}
 	
 	function getShareData($data)
 	{
 		$share_setting = $data["share_url_setting"]; 
 		$custom = $data["share_url_custom"]; 
 		
 		$url = ''; 
 		$title = ''; 
 		$img = ''; 
 
 		$basic = $this->collection->getBlock("basic");
 		$place_data = $basic->get(); 
		$placement = $place_data["placement"]; 
 		
 		switch($share_setting)
 		{
 			case "home": 
 				$url = get_home_url();
 				$title = get_bloginfo('name'); 
 				$img = $this->getImage(null, true); 
 			break;
 			case "custom-url";
 				$url = $custom;
 				$img = $this->getImage();
 			break;
 			case "current-post": 
				$url = get_permalink(); 
				$title = get_the_title(); 
				$img = $this->getImage(); 
			break; 			
 			case "auto": 
 			default:


	 				/* After / before being placed on the post data, therefore by default (auto) always use the POST URL
	 				Static is being placed once(1) on the page, so get the greated to-share url ( of the whole section ) */ 
	 				
	 				switch($placement)
	 				{
	 					case "before": 
	 					case "after": 
	 					case "after-before": 
	 					 	$url = get_permalink(); 
 							$title = get_the_title(); 
 							$img = $this->getImage(); 
	 					break;
	 					case "static-left": 
	 					case "static-right": 
	 					case "static-top": 
	 					case "static-bottom": 
	 					 	if (is_front_page()) 
 							{
 							 	$url = get_home_url(); 
 								$title = get_bloginfo('name'); 
 				 				$img = $this->getImage(null, true); 			
 							}
 							else // are there any other cases? Page / Archive should give back their main url like this.  
 							{
 								$url = get_permalink(); 
 								$title = get_the_title(); 
 							 	$img = $this->getImage(); 
 							}
	 					break;
	 		
	 		
	 				} // switch 
 
 			break;
 		}
 		$data = array("url" => $url, 
 			"title" => $title, 
 			"img" => $img); 
 		return $data;
 		
 	}
 	
 	function getImage($post_id = null, $home = false)
 	{ 		
		if (is_admin())
			return ''; // in admin all this is not set. 

 		if ($home && get_option('show_on_front') === 'page') 
 		{
 			$post_id = get_option('page_on_front'); 
 			
 		}

 		$thumb_id = get_post_thumbnail_id($post_id); // First try to find thumbnail on the content

 		$image = false; 
		if (is_numeric($thumb_id)) 
		{
			$image = wp_get_attachment_url($thumb_id);
		}
	
		if ($image !== false) 
			return $image; 
		
		// In case of no thumbnails, tries to find first image from current content. 
		
//		if (! is_null($post_id))  
//		{
			$post = get_post($post_id); 
			$content = ''; 
			if (! is_null($post)) 
				$content = $post->post_content;
/*		}		
		else
		{
	
			$content = get_the_content(); 
		} */
		$domObj = new simple_html_dom();
		$domObj->load($content); 
		
		$img = $domObj->find('img', 0);
 		if ($img) 
 		{
 			$image = $img->src; 
 			return $image; 
 		}
 		else
 			return ''; 
 	}
 	
 	function applyVars($string, $vars)
 	{

 		if (isset($vars["url"])) 
	 		$string = str_replace("{url}", urlencode(esc_url($vars["url"])), $string);
 		if (isset($vars["count"])) 
	 	{
	 		$count = $this->formatCount($vars["count"]);
	 		$string = str_replace("{count}", "<span class='share_count'>" . $count . "</span>", $string);
	 		$string = str_replace("{c}", "<span class='share_count'>" . $count . "</span>", $string); 
	 	}
 		if (isset($vars["title"])) 
	 		$string = str_replace("{title}", $vars["title"], $string);
 		if (isset($vars["network_name"])) 
	 		$string = str_replace("{network_name}", $vars["network_name"], $string);
 		
 		if (isset($vars["img"])) 
 			$string = str_replace("{img}", $vars["img"], $string); 

		return $string;
 	}
 	
 	protected function formatCount($raw_count)
 	{
 		$count = $raw_count;
 		if ($count > 1000) 
 		{
 			$count = round(($count/1000), 1). 'K'; 
 		}
 		$count = apply_filters("mb-collection-count-format", $count, $raw_count);
 		return $count;
 	}
 
 	
 
 	// function to get specifics on network like where to get count / where to sent people etc. 

 	
 	function save_fields($data, $post) 
 	{

 		$data = parent::save_fields($data, $post);
  
 		
 		$picker = $this->collection->getBlock("picker"); 
		if ($picker)
		{
	
			$picker_data = $picker->get();
			$selection = $picker_data["selection"]; 
		
		}
		
		$mf_field_id = $this->fields["multifields"]["id"];

		return $data;
 	}
 	
 	function ajax_new_button($result, $data)
 	{
 		ob_start(); 
 		$button_array = json_decode(base64_decode($data["data"]), true);
 		$button_id = $data["button_id"]; 
 		$index = isset($data["index"]) ? intval($data["index"]) : 0; 
 		
 		$data = array(); 
 		$data["index"] = $index;	
 		 	 	
 		$fields = $this->fields["multifields"]["fields"]; 
 		
 		if (isset($button_array["collection"]["social"]))
 		{	$social_data = $button_array["collection"]["social"];  // removed array filter since it removes zero's as well..
 		
 		}
 		else
 			$social_data = array(); 
 

 		foreach($fields as $field => $options)
 		{
 			if (isset($social_data[$field])) 
 				$data[$field] = $social_data[$field]; 
 			else
 				$data[$field] = (isset($options["default"])) ? $options["default"] : ''; 
 		}

 		
 		$this->init_admin_arrays();	 
 		$this->do_social_option($button_id, $data, $button_array ) ;
 
 		$output = ob_get_contents(); 		
 		ob_end_clean(); 

 		$result["body"] = $output; 
 		return $result; 
 		
 	}
 	
 	function ajax_get_count($result, $data) 
 	{
 
 		$network = (isset($data["network"])) ? sanitize_text_field($data["network"]) : ''; 
 		$share_url = (isset($data["share_url"])) ? sanitize_text_field($data["share_url"]) : ''; 
 		
 		if ($network == '') 
 			return $result;
 		
 		// init network 
 		$maxSN = new maxSN($network); 

 		// check if share count appeared in the cache. If this is the case it's possible another process put it there. 
 		$do_remote = true;
 		$share_count = $maxSN->getShareCount(array("url" => $share_url)); 
 		if ($share_count !== false) 
 		{	
 			$count = $share_count;
 			$do_remote = false; // stop annoying other servers
 		}
 		
 		if ($do_remote)
 		{
	 		// returns false, a number or 'locked' in case of locked. 
	 		$count = $maxSN->getRemoteShareCount($share_url);

			/* If the request is lock, another request is probably asking for this and shouldn't take long. 
				Try a maximum of 5 times to prevent server process hanging until php times out ( we want to prevent that ) */ 
	 		if ($count == 'locked') 
	 		{
	 			if ($this->ajax_remote_count < 5) 
	 			{
	 				sleep(1); // in seconds please. 	
	 				// after retry result here will at some point have the count data, extract and return at the end. 
	 				$result = $this->ajax_get_count($result,$data); 
	 				$count = $result["data"]["count"]; 
	 			}
	 			else 
	 				$count = "TIMEOUT"; 
	 			$this->ajax_remote_count++;
	 		}
 		}
 		
 		$result["data"] = array('count' => intval($count) ); 
 
 		return $result;
 	}
 	
 	function init_admin_arrays()
 	{
 	
		global $supported_networks, $share_url_settings; 

		$supported_networks = maxSN::getSupportedNetworks(); 
		
	
		$share_url_settings = array(
				"auto" => __("Auto","maxbuttons"), 
				"current-post" => __("Current post or page","maxbuttons"), 
				"custom-url" => __("Custom URL","maxbuttons"), 
				"home" => __("Homepage URL","maxbuttons"),
		);
	 	
 	}
 	
 	/* These two functions to be merged / or do_supports should use this - next rework */ 
 	function is_supported ($field_type, $network) 
 	{
 		switch ($field_type) 
 		{
	 		case "display_count": 
	 		case "count_threshold": 
				$networks = array("facebook","google-plus","linkedin","pinterest","reddit","stumbleupon","vkontakte"); 	 		 			
 			break;
 			case "share_url_setting":
	 			$networks = array_keys($supported_networks);	 	
	 			// unset exceptions
	 			$networks = array_values(array_diff($networks, array("bloglovin", "none") ));  			
 			break;
 	 		case "blog_url": 
 				$networks = array('bloglovin');	 		
	 		break;		
 		}
 		
 		if (in_array($network, $networks))
 		{
 			return true;
 		}
 		return false;
 	}
 	
 	function do_supports($field, $button_id, $index) 
 	{
 		
		global $supported_networks; 
		
 		$basic_supports = array("share_url_setting", "share_url_custom"); // conditionals interface
	 	$share_supports = array("display_count", "count_threshold"); 
	 	
	 	$networks = array(); 
	 	
	 	switch ($field)
	 	{
	 		case "display_count": 
	 		case "count_threshold": 
				$networks = array("facebook","google-plus","linkedin","pinterest","reddit","stumbleupon","vkontakte"); 
	 		break;
	 		
	 		case "share_url_setting":
	 		//case "share_url_custom": 
	 			$networks = array_keys($supported_networks);	 	
	 			// unset exceptions
	 			$networks = array_values(array_diff($networks, array("bloglovin", "none") )); 
	 			 
	 		break;
	 		
	 		case "blog_url": 
 				$networks = array('bloglovin');	 		
	 		break;
 
	 	}

	 	$conditional = htmlentities(json_encode(array("target" => "network-$button_id-$index", "values" => $networks)));
	 	return $conditional;
	 			
 
 	}

	public function map_fields($map)
	{
		$map = parent::map_fields($map); 
		$map["network"]["func"] = "parseTags"; 
 		$map["display_count"]["func"] = "parseTags"; 
 		
		
		return $map; 
	}
 	
 	function admin_fields()
	{
	
	$picker = $this->collection->getBlock("picker"); 
	if ($picker)
	{
	
		$picker_data = $picker->get();
		$selection = $picker_data["selection"]; 
		
	}
	
	$this->init_admin_arrays();

	
	$mf_field_id = $this->fields["multifields"]["id"]; 	 	
	$mf_fields = $this->fields["multifields"]["fields"]; 	 	
	
	?><div class="mb_tab social_block option-container" data-options="social">
		<div class="title">
			<span class='dashicons dashicons-share'> </span> 
			<span class="title"><?php _e("Social options", "maxbuttons"); ?></span>
	    <span class='manual-box'><a class='manual-toggle' href='javascript:void(0)' data-target="social"> <?php _e("Getting Started","maxbuttons-pro"); ?> </a></span> 
			<span class='right'><button name="save"  data-form='collection_edit' type="submit" class="button button-primary"><?php _e("Save All","maxbuttons"); ?></button>
			</span>		
		</div> 
			<div class="inside">
			<?php

					$defaults = array(); 
					foreach($mf_fields as $mf_field => $options) 
					{
						$defaults[$mf_field] = isset($options["default"]) ? $options["default"] : ""; 
					}

					$buttondata = isset($this->data[$mf_field_id]) ? $this->data[$mf_field_id] : array(); 	
		
					foreach($selection as $index => $button_id)
					{	
						$mf_data = array(); 
						$mf_data = $defaults; // defauls until found 
						
						//foreach($this->data[$mf_field_id] as $b_index => $buttondata)
						//{

						
							if (isset($buttondata[$index][$button_id]))
							{
							
								$mf_data = $buttondata[$index][$button_id];

							 
							}
						
						//}
						$mf_data["index"] = $index;
 						$this->do_social_option($button_id, $mf_data);
						
					}
 				
			?>
	 
	 	<?php if (count($selection) == 0) // no buttons 
	 		{
	 		?>
	 			<p class='no-buttons'><?php _e("No buttons selected yet. Please select some buttons first.","maxbuttons"); ?></p>
	 		
	 		<?php
	 		
	 		}
	 	?>
		</div>	
</div> <!-- option container --> 


	<!-- manual entry -->
	<div class="manual-entry" data-manual="social"> 	
		<h3><?php _e("Social settings", "maxbuttons"); ?>
			 <span class="dashicons dashicons-no window close manual-toggle" data-target="social"></span>  
		</h3>
						
		<p><?php _e("For each button you have selected pick the Social Media network that corresponds to the icon.  For each Social Sharing button you can choose to use the current page, the your homepage URL or a custom URL.","maxbuttons"); ?></p>
	
	</div>

	<?php
	}
	
	function do_social_option($button_id, $data, $button_array = array() ) 
	{

		global $supported_networks, $share_url_settings;
 		
 		//$data = array_filter($data); // packs from xml can give back empty arrays if value is not set ( attribute of pack handler ). 
		
		$defaults = $this->fields['multifields']['fields'];  // array filter removes empty fields, set to defaults. 
		
		// array filter doesn't work since vars need to be set, or be able to have zero. Put to default if array ( xml import )
		$network = is_array($data['network']) ? $defaults['network']['default'] : $data['network'];
		$display_count = is_array($data['display_count']) ? $defaults['display_count']['default'] : $data['display_count'];
		$count_threshold = is_array($data['count_threshold']) ? $defaults['count_threshold']['default'] : $data['count_threshold'];
		$share_url_setting = is_array($data['share_url_setting']) ? $defaults['share_url_setting']['default'] : $data['share_url_setting'];
		$share_url_custom = is_array($data['share_url_custom']) ? $defaults['share_url_custom']['default'] : $data['share_url_custom'];
		$blog_url = is_array($data['blog_url']) ? $defaults['blog_url']['default'] : $data['blog_url'];

		//extract($data);

		$index = isset($data["index"]) ? $data["index"] : 0; 

		$button = MB()->getClass("button");
		 
		if ($button_id > 0 && (! isset($button_array["meta"]) || ! $button_array["meta"]["is_virtual"]) )  
		{
			$button->set($button_id);
		}
		elseif (count($button_array) > 0) 
		{
			$button_array["name"] = $button_array["basic"]["name"]; 
			$button_array["status"] = $button_array["basic"]["status"]; 
			$button->clear(); 
			maxButtons::buttonLoad(array("button_id" => $button_id)); // central registration - from button			
			$button->setupData($button_array);
		}
		
		$document_id = $button->getDocumentID(); 


		?>
	<div class="social-option" data-id="<?php echo $button_id ?>" data-document_id="<?php echo $document_id ?>"> 
		<input type="hidden" name="social-option[]" value="<?php echo $button_id ?>" /> 
		<div class="option"> 
			<div class="shortcode-container">
			<?php	

 					
					$button->display(array("mode" => "preview"));
 					 
			?>

			</div>
					<span class="button_name"><?php echo $button->getName(); ?></span>
		</div>
		
		
		<div class="option  "> 
			<label><?php _e("Sharing network","maxbuttons"); ?></label>
			<?php echo maxUtils::selectify("network-$button_id-$index",$supported_networks, $network, 'network'); ?> 
			
		</div>
		
		<div class="option conditional-option"  data-show="<?php echo $this->do_supports('display_count', $button_id, $index);  ?>"> 
			<label><?php _e("Show Share Counts","maxbuttons"); ?></label>
			<input type="checkbox" name="display_count-<?php echo $button_id ?>-<?php echo $index ?>" value="1" <?php checked($display_count, 1); ?>  /> 
		</div>
		
		<div class="option conditional-option"  data-show="<?php echo $this->do_supports('count_threshold', $button_id, $index);  ?>"> 
			<label><?php _e("Minimum share count","maxbuttons"); ?></label>
			<input type="number" class="tiny" name="count_threshold-<?php echo $button_id ?>-<?php echo $index ?>" min="0" 
			value="<?php echo intval($count_threshold) ?>" />  
		</div>	

		<div class="option conditional-option"  data-show="<?php echo $this->do_supports('share_url_setting', $button_id, $index);  ?>"> 
			<label><?php _e("Share URL settings","maxbuttons"); ?></label>
			<?php echo maxUtils::selectify("share_url_setting-$button_id-$index", $share_url_settings, $share_url_setting); ?>
			 

			<div class="help fa fa-question-circle "> 
			<span><?php _e("Which URL (link) is to be shared. Auto will share the current page on pages, and the full site URL on the homepage", "maxbuttons"); ?> 
				</span>
			</div>
				
		</div>	
		
		<!-- bloglovin --> 
		<div class="option conditional-option"  data-show="<?php echo $this->do_supports('blog_url', $button_id, $index);  ?>"> 
			<label><?php _e("Bloglovin' Blog URL","maxbuttons"); ?></label>
			<input type="text" name="blog_url-<?php echo $button_id ?>-<?php echo $index ?>"
				 value="<?php echo esc_attr($blog_url) ?>"> 
		</div>
		
	<?php   $condition = array("target" => "share_url_setting-$button_id-$index", "values" => array("custom-url")) ;
			$custom_conditional = htmlentities(json_encode($condition )); 	 
	?>	
			
  	 
		<div class="option conditional-option" data-show="<?php echo $custom_conditional ?>">	
			<label><?php _e("Custom URL","maxbuttons"); ?></label> 
			<input type="text" name="share_url_custom-<?php echo $button_id ?>-<?php echo $index ?>"
			 value="<?php echo esc_attr($share_url_custom) ?>"> 
  
		</div>
	</div> <!-- social option --> 
		<?php 
	}
}

?>
