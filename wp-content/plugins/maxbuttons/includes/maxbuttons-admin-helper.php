<?php 
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

/* Helper class for uniform elements in admin pages */ 

add_action('mb-display-logo', array(maxUtils::namespaceit('maxAdmin'),'logo')); 
add_action('mb-display-title', array(maxUtils::namespaceit("maxAdmin"),'rate_us'), 20); 
add_action('mb-display-tabs', array(maxUtils::namespaceit('maxAdmin'),'tab_menu')); 
add_action('mb-display-ads', array(maxUtils::namespaceit('maxAdmin'), 'display_ads')); 
add_action('mb-display-pagination', array(maxUtils::namespaceit('maxAdmin'), 'display_pagination'));

add_action('mb-display-collection-welcome', array(maxUtils::namespaceit('maxAdmin'), 'displayCollectionWelcome')); 


class maxAdmin 
{
	protected static $tabs = null;
					
		
	public static function logo()
	{
		$version = self::getAdVersion(); 
		$url = self::getCheckoutURL(); 
		

	?> 
			<?php _e('Brought to you by', 'maxbuttons') ?>
			<a href="http://maxfoundry.com/products/?ref=mbfree" target="_blank"><img src="<?php echo MB()->get_plugin_url() ?>images/max-foundry.png" alt="Max Foundry" title="Max Foundry" /></a> 
			<?php printf(__('Upgrade to MaxButtons Pro today!  %sClick Here%s', 'maxbuttons'), '<a class="simple-btn" href="' . $url . '&utm_source=mbf-dash' . $version . '&utm_medium=mbf-plugin&utm_content=click-here&utm_campaign=cart' . $version . '" target="_blank">', '</a>' ) ?>

 			<?php $twitlink = 'https://twitter.com/intent/user?original_referer=http%3A%2F%2Flocal.max%2Fwp-admin%2Fadmin.php%3Fpage%3Dmaxbuttons-controller&amp;ref_src=twsrc%5Etfw&amp;region=count_link&amp;screen_name=maxfoundry&amp;tw_p=followbutton'; 
 			?>
<?php
	 	return; ?> 
			<!--
			<div class="twitter-follow">

				<div class='follow'><a href='<?php echo $twitlink ?>'><i></i> <?php _e("Follow","maxbuttons"); ?></a></div>
				<a class="note" href="<?php echo $twitlink ?>">
				<u></u><?php printf( __("%dK followers",'maxbuttons'), 10) ?></a></div> -->
			
	<?php
	}
	
	static function tab_items_init()
	{
			self::$tabs = array(
						"list" => array("name" =>  __('Buttons', 'maxbuttons'), 
										 "link" => "page=maxbuttons-controller&action=list",
										 "active" => "maxbuttons-controller", ), 
						"collection" => array("name" => __('Social Share','maxbuttons'),
										 "link" => "page=maxbuttons-collections", 
							 			"active" => "maxbuttons-collections", 
						),										 
						"pro" => array( "name" => __('Upgrade to Pro', 'maxbuttons'),
										 "link" => "page=maxbuttons-pro",
										 "active" => "maxbuttons-pro",
										 ),
						"settings" => array("name" => __('Settings', 'maxbuttons'),
										 "link" => "page=maxbuttons-settings",
										 "active" => "maxbuttons-settings",
										 "userlevel" => 'manage_options'  ), 
						"support" => array("name" => __('Support', 'maxbuttons'), 
										 "link" => "page=maxbuttons-support",
										 "active" => "maxbuttons-support",
										 "userlevel" => 'manage_options'
										 )
			); 
	}
	
	public static function tab_menu()
	{
		 self::tab_items_init(); 
	?>
			<h2 class="tabs">
				<span class="spacer"></span>
		<?php foreach (self::$tabs as $tab => $tabdata) { 
			if (isset($tabdata["userlevel"]) && ! current_user_can($tabdata["userlevel"]))
				continue; 

			$link = admin_url() . "admin.php?" . $tabdata["link"]; 
			$name = $tabdata["name"];
			$active = ''; 
			if ($tabdata["active"] == $_GET["page"])
				$active = "nav-tab-active";
				
				echo "<a class='nav-tab $active' href='$link'>$name</a>"; 

		}
		echo "</h2>";	
	}
	
	public static function getAdversion() 
	{
		$version = MAXBUTTONS_VERSION_NUM; 
		$version = str_replace('.','',$version);
		return $version;
	
	}
	
	public static function getCheckoutURL() 
	{
	 return $url = 'https://maxbuttons.com/checkout/?edd_action=add_to_cart&download_id=24035'; 
	}
	
	public static function display_reviewoffer() 
	{
		$current_user_id = get_current_user_id(); 	
		$display = get_user_meta($current_user_id, 'maxbuttons_review_offer', true); 
		
		if ($display == 'off')
			return;		
		
		if ($display == '') 
		{
			$created = get_option("MBFREE_CREATED");
			$display_time = $created + (8 * WEEK_IN_SECONDS ); 

			if (time() < $display_time)
				return;
		}
			
		add_action( 'admin_notices', array('maxAdmin', 'mb_review_notice'));
	  	wp_enqueue_style('maxbuttons-review-notice', MB()->get_plugin_url() . 'assets/css/review_notice.css');				
		wp_enqueue_script('maxbuttons-review-notice', MB()->get_plugin_url() . 'js/min/review-notice.js',  array('jquery'), true); 

		$local = array(); 
		$local["ajaxurl"] = admin_url( 'admin-ajax.php' );
		wp_localize_script('maxbuttons-review-notice', 'mb_ajax_review', $local);

	}
	
	public static function display_ads()
	{ 
		$plugin_url = MB()->get_plugin_url();
		$ad_url = $plugin_url . '/images/ads/'; 
		$version = self::getAdVersion(); 
		$url = self::getCheckoutURL(); 
	?>	   

        <div class="ads image-ad">
		<a  href="<?php echo $url ?>&utm_source=mbf-dash<?php echo $version ?>&utm_medium=mbf-plugin&utm_content=MBF-sidebar&utm_campaign=cart<?php echo $version ?>" target="_blank" >
        	<img src="<?php echo $plugin_url ?>/images/max_ad.png" width="300"> 
            </a>
        </div>
        
        <div class="ads image-ad"> 
        	<a href="http://www.maxbuttons.com/pricing/?utm_source=mbf-dash<?php echo $version ?>&utm_medium=mbf-plugin&utm_content=EBWG-sidebar-22&utm_campaign=inthecart<?php echo $version ?>" target="_blank"><img src="<?php echo $plugin_url ?>/images/ebwg_ad.png" /></a>
			        	
        </div>
        
        <div class="ads image-ad">
            <a href="https://wordpress.org/plugins/maxgalleria/?utm_source=mbf-dash<?php echo $version ?>&utm_medium=mbf_plugin&utm_content=MG_sidebar&utm_campaign=MG_promote" target="_blank">
            <img src="<?php echo $plugin_url ?>/images/mg_ad.png" /></a>
        </div>
        
   <!--     <div class="ads">
            <h3><i class="fa fa-cogs"></i> <?php _e('Font Awesome Support', 'maxbuttons'); ?></h3>
            <p><?php _e('With MaxButtons Pro you have access to all 439 Font Awesome icons, ready to add to your buttons.', 'maxbuttons'); ?></p>
            <p><?php _e('Never upload another icon again, just choose an icon and go about your normal button-making business.', 'maxbuttons'); ?></p>
            <a class="button-primary" href="http://www.maxbuttons.com/pricing/?utm_source=wordpress&utm_medium=mbrepo&utm_content=button-list-sidebar-99&utm_campaign=plugin"><?php _e('Use Font Awesome!', 'maxbuttons'); ?> <i class="fa fa-arrow-circle-right"></i></a>
        </div> -->
        <?php
	}
	
	/** Display Rating Links
	*
	* 	Displays rating links via mb-display-title hook. 
	*/
	public static function rate_us()
	{
		$output = ''; 
		
		$output .= "<div>"; 
		$output .= sprintf("Enjoying MaxButtons? Please %s rate us ! %s", 
			"<a href='https://wordpress.org/support/view/plugin-reviews/maxbuttons#postform'>", 
			"</a>"
			);
		$output .= "</div>"; 
		echo $output;
	}


	public static function display_pagination($page_args)
	{

		$mbadmin =  MB()->getClass("admin");  
		$pag = $mbadmin->getButtonPages($page_args); 
		if ($pag["first"] == $pag["last"])
		{	return; }

 
		extract($pag);
 
	?>

	<div class="tablenav-pages"><span class="displaying-num"><?php echo $pag["total"] ?> items</span>
	<span class="pagination-links">
	
	<?php if (! $first_url): ?>
	<a class="first-page disabled" href='#'>«</a>
	<?php else: ?>
		<a href="<?php echo $first_url ?>" data-page="1" title="<?php _e("Go to the first page","maxbuttons") ?>" class="first-page <?php if (!$first_url) echo "disabled"; ?>">«</a>
	<?php endif;  ?>
	
	<?php if (! $prev_url): ?>
	<a class="prev-page disabled" href='#'>‹</a>
	<?php else : ?> 
		<a href="<?php echo $prev_url ?>" data-page="<?php echo $prev_page ?>" title="<?php _e("Go to the previous page","maxbuttons"); ?>" class="prev-page <?php if (!$prev_url) echo "disabled"; ?>">‹</a>	
	<?php endif; ?> 
	
	<span class="paging-input"><input data-url="<?php echo $base ?>" class='input-paging' min="1" max="<?php echo $last ?>" type="number" name='paging-number' size="1" value="<?php echo $current ?>"> <?php _e("of","maxbuttons") ?> <span class="total-pages"><?php echo $last ?>
	</span></span>
	
	<?php if (! $next_url): ?>
		<a class="next-page disabled" href='#'>›</a>
	<?php else: ?> 
		<a href="<?php echo $next_url ?>" data-page="<?php echo $next_page ?>" title="<?php _e("Go to the next page","maxbuttons") ?>" class="next-page <?php if (!$next_url) echo "disabled"; ?>">›</a>	
	<?php endif; ?> 

	<?php if (! $last_url): ?>
	<a class="last-page disabled" href='#'>»</a></span></div>
 	<?php else: ?>
 		<a href="<?php echo $last_url ?>" data-page="<?php echo $last ?>" title="<?php _e("Go to the last page","maxbuttons") ?>" class="last-page <?php if (!$last_url) echo "disabled"; ?>">»</a></span></div>	
 	<?php endif; ?> 
 
 
	<?php
	}

    public static function mb_review_notice() {
	   if( current_user_can( 'manage_options' ) ) {  ?>
		  <div class="updated notice maxbuttons-notice">         
		      <div class='review-logo'></div>
		      <div class='mb-notice'>
		      	<p class='title'><?php _e("Rate us Please!","maxbuttons"); ?></p>
		     	<p><?php _e("Your rating is the simplest way to support MaxButtons. We really appreciate it!","maxbuttons"); ?></p>
		    
				  <ul class="review-notice-links">
				    <li> <span class="dashicons dashicons-smiley"></span><a data-action='off' href="javascript:void(0)"><?php _e("I've already left a review","maxbuttons"); ?></a></li>
				    <li><span class="dashicons dashicons-calendar-alt"></span><a data-action='later' href="javascript:void(0)"><?php _e("Maybe Later","maxbuttons"); ?></a></li>
				    <li><span class="dashicons dashicons-external"></span><a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/maxbuttons?filter=5#postform"><?php _e("Sure! I'd love to!","maxbuttons"); ?></a></li>
				  </ul>
		      </div>
		      <a class="dashicons dashicons-dismiss close-mb-notice" href="javascript:void(0)" data-action='off'></a>
 
		  </div>
		<?php     
		}
	  }

	// standard modal  :: DEPRECATED!
	static function formResponseModals()
		{
			return false;
			?>
			<div class='form_modal_wrapper'>
				<a href='#mb-formresponse' rel='leanModal' class='show_response_modal'></a>
				<div id='mb-formresponse' class='max-modal'> 
			
					<div class="content-area"> 
						<div class="modal_header"> 
							<h3 class='title'></h3>
							<div id="modal_close" class="modal_close tb-close-icon"></div>
						</div>
						<p class='content'>&nbsp;</p>
					</div>
					<div class='controls'>
						<div class='ok'>
							<input type="button" name="#" onClick="javascript:document.getElementById('modal_close').click();" class="button-primary" value="<?Php _e("OK", "maxbuttons-pro"); ?>">
						</div>	
						<div class='yesno'>
							<input type='button' class="button-primary yes" name='#' value="<?php _e("Yes","maxbuttons-pro"); ?>"> 
							<input type="button" class="button-primary no" name='#' value="<?php _e("No","maxbuttons-pro"); ?>" onClick="javascript:document.getElementById('modal_close').click();">
						</div>
					</div>
				</div>
			</div>
			<?php
	
		}

	public static function displayCollectionWelcome() 
	{
	?>
		<div class="collection welcome"> 
	<h2><?php _e("Welcome to MaxButtons Social Sharing", "maxbuttons"); ?></h2>
	<p><?php _e("Social Sharing sets are collections of buttons that are primarily used to promote your social media profiles on your site.","maxbuttons"); ?></p>
	<p><?php _e("MaxButtons comes with 5 terrific free sets of Social Sharing buttons for you to use: Notched Box Social Share, Modern Social Share, Round White Social Share, Social Share Squares, Minimalistic Share Buttons.  You can also add any other button you have made to a collection of Social Sharing buttons.","maxbuttons"); ?></p>

<p><?php _e("After clicking the Get Started link below you’ll come to the ‘Select your buttons’ page.  Here you will see the listing of free Social Sharing sets plus all of the buttons that you have on your site can be used in the collection that you are putting together.","maxbuttons"); ?></p>

<p><?php _e("You build your Social Sharing set by selecting the buttons you want in your collection.  Then click the Add selected buttons button in the lower right.  Aside from being included in your collection your selected buttons are now included with all of the other buttons on your site.  You can edit those buttons by going to the Buttons section in the Nav bar on the left.","maxbuttons"); ?></p>

<p><?php printf(__("By upgrading to %sMaxButtons Pro%s you get an 13 additional Social Sharing button sets along with the ability to build your own Social Sharing sets using your own icons, using Google Fonts in your Social Sharing buttons along with all of the features that come with our premium product.","maxbuttons"), "<a href='https://www.maxbuttons.com' target='_blank'>", "</a>"); ?></p>

<p><strong><?php _e("Click Get Started and we will have your social media icons up and running on your site super quick!","maxbuttons"); ?></strong></p>

<p><?php _e("The Max Foundry Team", "maxbuttons"); ?></p>

	<p><a class="page-title-action " href="<?php echo admin_url() ?>admin.php?page=maxbuttons-collections&action=edit&collection=social">
	<?php _e("Get Started","maxbuttons"); ?></a></p>
	
	</div>

	<?php
	}
	
} // class
