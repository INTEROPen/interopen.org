<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

$theme = wp_get_theme();
$browser = maxbuttons_get_browser();

if(is_admin()) {
   // wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css', '', '4.0.1', false);
}

function maxbuttons_system_label($label, $value, $spaces_between) {
	$output = "<label>$label</label>";	
	return "<div class='info'>" . $output . trim($value) . "</div>" ;
}

// http://www.php.net/manual/en/function.get-browser.php#101125.
// Cleaned up a bit, but overall it's the same.
function maxbuttons_get_browser() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $browser_name = 'Unknown';
    $platform = 'Unknown';
    $version= "";

    // First get the platform
    if (preg_match('/linux/i', $user_agent)) {
        $platform = 'Linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $user_agent)) {
        $platform = 'Mac';
    }
    elseif (preg_match('/windows|win32/i', $user_agent)) {
        $platform = 'Windows';
    }
    
    // Next get the name of the user agent yes seperately and for good reason
    if (preg_match('/MSIE/i', $user_agent) && !preg_match('/Opera/i', $user_agent)) {
		$browser_name = 'Internet Explorer';
        $browser_name_short = "MSIE";
    }
    elseif (preg_match('/Firefox/i', $user_agent)) {
        $browser_name = 'Mozilla Firefox';
        $browser_name_short = "Firefox";
    }
    elseif (preg_match('/Chrome/i', $user_agent)) {
        $browser_name = 'Google Chrome';
        $browser_name_short = "Chrome";
    }
    elseif (preg_match('/Safari/i', $user_agent)) {
        $browser_name = 'Apple Safari';
        $browser_name_short = "Safari";
    }
    elseif (preg_match('/Opera/i', $user_agent)) {
        $browser_name = 'Opera';
        $browser_name_short = "Opera";
    }
    elseif (preg_match('/Netscape/i', $user_agent)) {
        $browser_name = 'Netscape';
        $browser_name_short = "Netscape";
    }
    
    // Finally get the correct version number
    $known = array('Version', $browser_name_short, 'other');
    $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $user_agent, $matches)) {
        // We have no matching number just continue
    }
    
    // See how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        // We will have two since we are not using 'other' argument yet
        // See if version is before or after the name
        if (strripos($user_agent, "Version") < strripos($user_agent, $browser_name_short)){
            $version= $matches['version'][0];
        }
        else {
            $version= $matches['version'][1];
        }
    }
    else {
        $version= $matches['version'][0];
    }
    
    // Check if we have a number
    if ($version == null || $version == "") { $version = "?"; }
    
    return array(
        'user_agent' => $user_agent,
        'name' => $browser_name,
        'version' => $version,
        'platform' => $platform,
        'pattern' => $pattern
    );
}

function check_charset() {
    global $maxbuttons_installed_version;
    global $wpdb;
    $check = "SHOW FULL COLUMNS FROM " . maxUtils::get_table_name();
    $charset = $wpdb->query($check);
    return $charset;
}
    if(isset($_POST['alter_charset'])) {
        $kludge = 'altering table to be utf-8';
        global $maxbuttons_installed_version;
        global $wpdb;
        $table_name = maxUtils::get_table_name();
        $kludge = $table_name;
        // IMPORTANT: There MUST be two spaces between the PRIMARY KEY keywords
        // and the column name, and the column name MUST be in parenthesis.
        $sql = "ALTER TABLE " . $table_name . " CONVERT TO CHARACTER SET utf8";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $wpdb->query($wpdb->prepare($sql));
    } else {
        $kludge = 'Not yet enabled';
    }

    $charr = check_charset(); 

?>
<?php
$support_link = apply_filters("mb-support-link", 'http://wordpress.org/support/plugin/maxbuttons'); 

$admin = MB()->getClass('admin'); 
$page_title = __("Support","maxbuttons"); 
$action = "<a href='$support_link' class='page-title-action add-new-h2' target='_blank'>" . __("Go to support","maxbuttons") . "</a>";
$admin->get_header(array("title" => $page_title, "title_action" => $action) );
?>
    		
    		<h4><?php printf(__('All support is handled through the %sSupport Forums%s.', 'maxbuttons'), "<a href='$support_link' target='_blank'>" , '</a>') ?></h4>

    <div class="rss-feed">
          <h3><?php _e('Latest Support Questions', 'maxbuttons'); ?></h3>
              <?php
              if( ini_get('allow_url_fopen') && extension_loaded('simplexml') ): 
              
               	try{
                  $content = file_get_contents('https://wordpress.org/support/rss/plugin/maxbuttons');

                  $x = new \SimpleXmlElement($content);
				}
				catch (Exception $e)
				{
					echo "EX"; 
				}
                   
                  echo '<ul >';
                  $i = 0;
                  foreach($x->channel->item as $entry) {
                      if(strpos($entry->title, 'Bas Schuiling') === false) {
                          $title = $entry->title;
                          $title = explode(" ", $title);
                          if (count($title) <= 7) 
                          	$title = array_slice($title, 2); // small reply format
                          else
	                          $title = array_slice($title, 7);
                          $time = $entry->pubDate;
                          $time = substr($time, 0, -9);
                  
                          $support_title = '';
                          foreach($title as $word) {
                              $word = str_replace("\"", "", $word);
                              $support_title .= $word . ' ';
                          }
                          $support_title = trim($support_title);
                          echo '<li><a href="' . $entry->link . '" target="_blank" title="' . $support_title . '"><span>' . $support_title . '</span><br />' . $time . '</a></li>';
                          $i++;
                          if($i == 9) break;
                      }
                  }
                  echo '</ul>';
              	else: 
              		echo _e("Your server doesn't allow us to fetch the latest support questions", "maxbuttons");  
              	
              	endif; // ini_get
              ?>
            </div>




    		<h4><?php _e('You may be asked to provide the information below to help troubleshoot your issue.', 'maxbuttons') ?></h4>
    	 
    <form>	
  
    <div class='system_info'> 
----- Begin System Info ----- <br />


<?php echo maxbuttons_system_label('WordPress Version:', get_bloginfo('version'), 4) ?>

<?php echo maxbuttons_system_label('PHP Version:', PHP_VERSION, 10) ?>

<?php
	global $wpdb;
	$mysql_version = $wpdb->db_version();
				

  echo maxbuttons_system_label('MySQL Version:', $mysql_version, 8) ?>

<?php echo maxbuttons_system_label('Web Server:', $_SERVER['SERVER_SOFTWARE'], 11) ?>

<?php echo maxbuttons_system_label('WordPress URL:', get_bloginfo('wpurl'), 8) ?>

<?php echo maxbuttons_system_label('Home URL:', get_bloginfo('url'), 13) ?>

<?php echo maxbuttons_system_label('PHP cURL Support:', function_exists('curl_init') ? 'Yes' : 'No', 5) ?>

<?php echo maxbuttons_system_label('PHP GD Support:', function_exists('gd_info') ? 'Yes' : 'No', 7) ?>
<?php echo maxbuttons_system_label('PHP Memory Limit:', ini_get('memory_limit'), 5) ?>

<?php echo maxbuttons_system_label('PHP Post Max Size:', ini_get('post_max_size'), 4) ?>

<?php echo maxbuttons_system_label('PHP Upload Max Size:', ini_get('upload_max_filesize'), 2) ?>

<?php echo maxbuttons_system_label('WP_DEBUG:', defined('WP_DEBUG') ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set', 13) ?>
<?php echo maxbuttons_system_label('Multi-Site Active:', is_multisite() ? 'Yes' : 'No', 4) ?>

<?php echo maxbuttons_system_label('Operating System:', $browser['platform'], 5) ?>
<?php echo maxbuttons_system_label('Browser:', $browser['name'] . ' ' . $browser['version'], 14) ?>
<?php echo maxbuttons_system_label('User Agent:', $browser['user_agent'], 11) ?>

Active Theme:
<?php echo maxbuttons_system_label('-', $theme->get('Name') . ' ' . $theme->get('Version'), 1) ?>
<?php echo maxbuttons_system_label('', $theme->get('ThemeURI'), 2) ?>

Active Plugins:
<?php
$plugins = get_plugins();
$active_plugins = get_option('active_plugins', array());

foreach ($plugins as $plugin_path => $plugin) {
	// Only show active plugins
	if (in_array($plugin_path, $active_plugins)) {
		echo maxbuttons_system_label('-', $plugin['Name'] . ' ' . $plugin['Version'], 1);
	
		if (isset($plugin['PluginURI'])) {
			echo maxbuttons_system_label('', $plugin['PluginURI'], 2);
		}
		
		echo "\n";
	}
}
?>
----- End System Info -----
 </div>
</form>
        </div>
        <div class="ad-wrap">
     		<?php do_action("mb-display-ads"); ?> 
    </div>
	 
<?php $admin->get_footer(); ?> 
