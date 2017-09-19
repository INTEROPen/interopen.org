<?php 
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

class maxSN
{
	protected $network = ""; 
	protected $network_data = array(); 
	protected $network_name = ''; 
	
	public function __construct($network) 
	{
		$this->network = $network;
		$this->setNetWork($network);
	
	}

	/* Set the network variables to be using for this inquery */ 
	public function setNetwork($network)
	{
 
		$networks = $this->getSupportedNetworks();
		$this->network_name = $networks[$network]; 
	 	
		$network_data = array(
 			"share_url" => '', // url to send share to (incl. var)
 			"count_api" => '',  // request url
 			"count_function" => "jsonRequest",  // which function to run.
 			"json_return_var" => '',  // which var is the count number?
			"count_check_time" => 4 * HOUR_IN_SECONDS, // how much time before checking again - in seconds
 			"popup" => true, // button opens share popup
 			"popup_dimensions" => array(400,300), // width - height of popup
		); 			
	 	
	 	switch($network)
	 	{
	 		case "bloglovin": 
	 			$network_data["popup"] = false; 
	 			
	 		break;
	 		case "digg": 
	 			$network_data["share_url"] = "http://digg.com/submit?url={url}&title={title}";
	 			$network_data["count_api"] = ''; 
	 			$network_data["popup_dimensions"] = array(600,400);
	 		break;
	 		case "email": 
	 			$network_data["share_url"] = 'mailto:?subject={title}&body={url}'; 
	 			$network_data["count_api"] = '';
	 			$network_data["popup"] = false;
	 		break;
	 		case "facebook": 
				$network_data["share_url"] = 'https://www.facebook.com/sharer.php?u={url}'; 
				$network_data["count_api"] = "https://graph.facebook.com/{url}"; 
				$network_data["json_return_var"] = "share|share_count"; 
	 		break;
	 		case "google-plus": 
	 			$network_data["share_url"] = 'https://plus.google.com/share?url={url}'; 
			 	$network_data["count_api"] = "{url}"; 
				$network_data["popup_dimensions"] = array(600,600);	
				$network_data["count_function"] = "googlePlus"; 
 
	 		break;
	 		case "linkedin": 
	 			$network_data["share_url"] = "https://www.linkedin.com/shareArticle?mini=true&url={url}"; 
	 			$network_data["count_api"] = "https://www.linkedin.com/countserv/count/share?url={url}&format=json"; 
	 			$network_data["json_return_var"] = "count"; 
				$network_data["popup_dimensions"] = array(700,500);	
	 		break;	 
	 		case "pinterest": 
	 			$network_data["share_url"] = "https://www.pinterest.com/pin/create/bookmarklet/?media={img}&url={url}&is_video=false&description={title}";
	 			$network_data["count_api"] = "https://api.pinterest.com/v1/urls/count.json?url={url}"; 
	 			$network_data["json_return_var"] = "count";
				$network_data["count_function"] = "pinterestCount";
				$network_data["popup_dimensions"] = array(750, 500); 
	 		break;	
	 		case "print": 
	 			$network_data["share_url"] = 'javascript:window.print()'; 
	 			$network_data["count_api"] = '';
	 			$network_data["popup"] = false;	 		
	 		break;	
	 		case "stumbleupon": 
	 			$network_data["share_url"] = "https://www.stumbleupon.com/submit?url={url}&title={title}";
	 			$network_data["count_api"] = "https://www.stumbleupon.com/services/1.01/badge.getinfo?url={url}";
	 			$network_data["json_return_var"] = "result|views";
 
	 		break;	
	 		case "reddit": 
	 			$network_data["share_url"] = "https://reddit.com/submit?url={url}&title={title}";
	 			$network_data["count_api"] = "https://buttons.reddit.com/button_info.json?url={url}"; 
	 			$network_data["json_return_var"] = "data|children|0|data|score"; 
 				$network_data["popup_dimensions"] = array(800, 500); 
 
	 		break;
	 		case "twitter":
	 			$network_data["share_url"] = 'https://twitter.com/intent/tweet?url={url}'; 
				$network_data["count_api"] = ""; 
				$network_data["json_return_var"] = ""; 
				$network_data["popup_dimensions"] = array(550, 420); 
	 		break;
	 		case "twitter-follow": 
	 			$network_data["share_url"] = 'https://twitter.com/{user}';
	 			$network_data['count_api'] = ''; 
	 			$network_data['json_return_var'] = 'count'; 
	 			
	 			
	 		break;
			case "vkontakte": 
				$network_data["share_url"] = "https://vkontakte.ru/share.php?url={url}";
				$network_data["count_api"] = "https://vk.com/share.php?act=count&url={url}";
				$network_data["count_function"] = "vKontakteCount";
			break;
			case "whatsapp": 
				$network_data["share_url"] = "whatsapp://send?text={url} {title}"; 
				$network_data["count_api"] = ''; 
			break;
			default: 
				$network_data['popup'] = false; // no network no popup. 
			break;

	 	}
	
	 	$network_data = apply_filters("mb-collection-network-data", $network_data);
	 	$network_data = apply_filters("mb-collection-network-data-" . $network, $network_data);
	 	
	 	$this->network_data = $network_data;  
	}
	
	public function getNetworkName() 
	{
		return $this->network_name; 
	
	}

	// the url to send shares to, by network. 
	public function getShareURL()
	{
		return $this->network_data["share_url"]; 
	}
	
	public function checkPopup() 
	{
		if (isset($this->network_data["popup"]) && $this->network_data["popup"] ) 
			return true; 
		else
			return false; 
	}
	
	public function getPopupDimensions() 
	{
		if (isset($this->network_data["popup_dimensions"]))
			return $this->network_data["popup_dimensions"]; 
		else
			return array(); 
	}

	public function getShareCount($args = array()) 
	{
		if ( $this->network_data["count_api"] == '') 
			return 0; // no api - count always zero.
			
		$defaults = array("url" => "", 
				"preview" => false, 
				"force_share_update" => false, 
		);
		
		$args = wp_parse_args($args,$defaults); 

		$share_url = esc_url($args["url"]); 
		
		$network = $this->network; 
		//$count = get_transient('mb-col-' . $network . '-' . $share_url . '-shares'); 
		$count = maxUtils::get_transient('mbcol-shares-' . $network . '-' . $share_url); 

		if ($args["force_share_update"]) 
			$count = -1; // force an update
			
		if ( ($count === false || $count == -1) && ! $args["preview"])
		{	// request from external - this is done via ajax on runtime.
			return false;
		}
		
		return $count; 
		
	}
	
	public function getRemoteShareCount($share_url)
	{
		$count_api = $this->network_data["count_api"]; 
 		if ($count_api == '') return false; // no api 
	
		$network = $this->network; 
		$timeout = 60; // prevent the same requests from running multiple times ( i.e. one page, many collections on same url ) . 	
		$locked = maxUtils::get_transient('mbcol-shares-' . $network . '-' . $share_url. '-lock');
		
		if ($locked == true) 
			return 'locked';  // try again on next refresh.
		
		//lock out next request while this one is still running. 	
		maxUtils::set_transient('mbcol-shares-' . $network . '-' . $share_url . '-lock', true, $timeout );

 		$count_api = str_replace("{url}", $share_url, $count_api);
 
 		$func = $this->network_data["count_function"]; 
 		$count = $this->$func($count_api); 
 		if (defined('MAXBUTTONS_DEBUG') && MAXBUTTONS_DEBUG) 
 		{
 			$admin = MB()->getClass("admin"); 
 
 			$admin->log("Get Remote Share", "Call: $count_api - Network : " . $this->network . " - Count: $count \n "); 
 		}

		if ($count !== false) 
		{
			$network = $this->network; 
			$check_time = $this->network_data["count_check_time"]; 

			// set count
			maxUtils::set_transient('mbcol-shares-' . $network . '-' . $share_url, $count, $check_time );
		
		}

		// remove lock
		maxUtils::delete_transient('mbcol-shares-' . $network . '-' . $share_url . '-lock');			

 		return $count; 
 	}
	
	protected function jsonRequest($url)
	{
		
		$response = wp_remote_get($url); 
		$result_path = $this->network_data["json_return_var"]; 
 		$result_array = explode("|",$result_path); 
 		if (count($result_array) == 0) 
 			$result_array = array($result_path); 
 		
		
		if (is_wp_error($response) || $response['response']['code'] != 200) {
			return false;
		}
		else {
			$result = wp_remote_retrieve_body($response);
		}
			$result = json_decode($result, true);
  
 		foreach($result_array as $result_val)
 		{
			if (isset($result[$result_val])) 
	 			$result = $result[$result_val]; 
 		 
 		}
 		if (is_int($result)) 
 			return $result; 	
		
		return 0; // some networks don't return the json return var. Only return false on network errors
		
	}
	
	protected function googlePlus($url)
	{
        $args = array(
            'method' => 'POST',
            'headers' => array(
                // setup content type to JSON
                'Content-Type' => 'application/json',
            ),
            // setup POST options to Google API
            'body' => json_encode(array(
                'method' => 'pos.plusones.get',
                'id' => 'p',
                'method' => 'pos.plusones.get',
                'jsonrpc' => '2.0',
                'key' => 'p',
                'apiVersion' => 'v1',
                'params' => array(
                    'nolog' => true,
                    'id' => $url,
                    'source' => 'widget',
                    'userId' => '@viewer',
                    'groupId' => '@self',
                ),
             )),
 
            'sslverify' => false,
        );	
	  $response = wp_remote_post('https://clients6.google.com/rpc', $args);
 
 
	 	if (is_wp_error($response) || $response['response']['code'] != 200) {
			return false;
		}
		else {
			$result = wp_remote_retrieve_body($response);
		} 
	
		$result = json_decode($result, true);
		$count = 0; 
		if (isset($result['result']['metadata']['globalCounts']['count']))
	 	{	$count = intval($result['result']['metadata']['globalCounts']['count']);
	 		return $count;	 	
	 	}

 		return 0;   

	}
	
	protected function vKontakteCount($url)
	{
		$response = wp_remote_get($url); 

		if (is_wp_error($response) || $response['response']['code'] != 200) {
				return false;
			}
		else {
				$result = wp_remote_retrieve_body($response);
		}
		
		preg_match('/(\d+),\s+(\d+)/i', $result, $matches);
		if (isset($matches[2]))  // 0 is both patterns, one is first number (not used?). 
		{	$count = $matches[2]; 
			return $count;
		}	
		return 0;
	}
	
	protected function pinterestCount($url)
	{
		$response = wp_remote_get($url); 

		if (is_wp_error($response) || $response['response']['code'] != 200) {
				return false;
			}
		else {
				$result = wp_remote_retrieve_body($response);
		}
	 
	 	// remove the callback wrapper. 
	 	$result = str_replace("receiveCount(","",$result);
	 	$result = substr($result,0,(strlen($result) -1) ); // remove last char.
	 	$json = json_decode($result, true); 

	 	if (isset($json["count"]))
	 		return $json["count"]; 
	 
		return 0;	
	}
	
	public static function getSupportedNetworks() 
	{
		// alphabetic
		$supported_networks = array(
				"none" => __("Select a network","maxbuttons"),
				"bloglovin" => __("Bloglovin","maxbuttons"),
				"digg" => __("Digg","maxbuttons"), 
				"email" => __("Email","maxbuttons"), 
				"facebook" => __("Facebook","maxbuttons"), 
				"google-plus" => __("Google +","maxbuttons"), 
				"linkedin" => __("LinkedIn","maxbuttons"), 	
				"pinterest" => __("Pinterest","maxbuttons"),
				"print" => __("Print this page","maxbuttons"),		
				"reddit" => __("Reddit", "maxbuttons"),
				"stumbleupon" => __("StumbleUpon","maxbuttons"),	
				"twitter" => __("Twitter","maxbuttons"),
				"vkontakte" => __("Vkontakte","maxbuttons"),
				"whatsapp" => __("Whatsapp", "maxbuttons"), 
			);
		$supported_networks = apply_filters("mb-collection-supported-networks", $supported_networks); 
		return $supported_networks; 		
	}

}
