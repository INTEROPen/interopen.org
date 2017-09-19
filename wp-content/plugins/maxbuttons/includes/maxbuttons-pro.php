<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

$url = MB()->get_plugin_url();
$img_url = $url . "images/gopro";
$icon_url = $img_url . '/icons/';  
?>
<?php
$admin = MB()->getClass('admin'); 
$page_title = __("Upgrade to Pro","maxbuttons"); 
$version = maxAdmin::getAdversion(); 
$checkout_url = maxAdmin::getCheckoutURL(); 

$buy_now_top = '<a class="page-title-action add-new-h2 big-maxg-btn" href="
' . $checkout_url . '&utm_source=mbf-dash' . $version . '&utm_medium=mbf-plugin&utm_content=buy-now&utm_campaign=buy-now-top' . $version . '" target="_blank">' . __("Buy Now", "maxbuttons") . "</a>"; 

$middle_buy = $checkout_url . "&utm_source=mbf-dash$version&utm_medium=mbf-plugin&utm_content=buy-now&utm_campaign=buy-now-1selling$version"; 

$bottom_buy = $checkout_url . "&utm_source=mbf-dash$version&utm_medium=mbf-plugin&utm_content=buy-now&utm_campaign=getitnow$version"; 
 
$admin->get_header(array("title" => $page_title, "title_action" => $buy_now_top, 'action' => 'gopro' ) );



?>

   <link href='https://fonts.googleapis.com/css?family=Quicksand:400,700' rel='stylesheet' type='text/css'>   
    <div class="wrapper-inner">
 
 

  <div class="default-section">
    <div class="container">
      <h2>Build Even Better WordPress Buttons with MaxButtons Pro!</h2>
      <p>
        Take your WordPress Buttons, Social Share and Social Icons to the next level
      </p>
      <div class="rating bordered">
        <img src="<?php echo $img_url ?>/stars.png" alt="stars" />
        <p>
          400+ 5 Star Ratings
        </p>
      </div>
      <p>
        Join our over 5,000 customers!
      </p>
    </div> <!-- container --> 
  </div>

  <div class="default-section">
    <div class="container">
      <h2>Powerful Advanced Features</h2>
      <p>
        Add icons, use Google Fonts and More to Fully Customize your Design to Work on your Site
      </p>
      <div class="icon-row">
		    <div class="width-33">
		      <img src="<?php echo $icon_url ?>nocode.png" alt="No Coding Required" />
		      <p>
		        No Coding Required
		      </p>
		    </div>
		    <div class="width-33">
		      <img src="<?php echo $icon_url ?>easycreate.png" alt="Easily Create And Modify Buttons" />
		      <p>
		       Easily Create And Modify Buttons
		      </p>
		    </div>
		    <div class="width-33">
		      <img src="<?php echo $icon_url ?>quickadd.png" alt="img" />
		      <p>
				Quickly Add Buttons to Your Site
		      </p>
		    </div>
		    <div class="clearfix"></div>
		    <div class="width-33">
		      <img src="<?php echo $icon_url ?>responsive.png" alt="img" />
		      <p>
		        Responsive Layout
		      </p>
		    </div>
		    <div class="width-33">
		      <img src="<?php echo $icon_url ?>1000buttons.png" alt="img" />
		      <p>
		        Thousands of Buttons
		      </p>
		    </div>
		    <div class="width-33">
		      <img src="<?php echo $icon_url ?>buttonmanagement.png" alt="img" />
		      <p>
		       Button Management
		      </p>
		    </div>
		    <div class="clearfix"></div>
		    <div class="width-33">
		      <img src="<?php echo $icon_url ?>basic-buttons-pack.png" alt="img" />
		      <p>
	 	       Basic Buttons Pack Included
		      </p>
		    </div>
		    <div class="width-33">
		      <img src="<?php echo $icon_url ?>2lines.png" alt="img" />
		      <p>
		        Two Lines of Text
		      </p>
		    </div>
		    <div class="width-33">
		      <img src="<?php echo $icon_url ?>add-icons.png" alt="img" />
		      <p>
		        Add Icons
		      </p>
		    </div>
		    <div class="clearfix"></div>

		    <div class="width-33">
		      <p>&nbsp;
		      </p>
		    </div>
		    <div class="width-33">
		      <img src="<?php echo $icon_url ?>support.png" alt="img" />
		      <p>
		        Regular Upgrades and Fast Friendly Support
		      </p>
		    </div>
		    <div class="width-33">
				<p>&nbsp;</p>

		    </div>
			 <div class="clearfix"></div>
		</div><!-- icon row --> 

  		<h2>Powerful Integrations</h2>
  		<p>&nbsp;</p>
  		<div class='icon-row'>
  			<div class='clearfix'></div>
		    <div class="width-33">
		      <img src="<?php echo $icon_url ?>visual-composer.png" alt="No Coding Required" />
		      <p>
		        Visual Composer for WordPress
		      </p>
		    </div>
			<div class="width-33">
			  <img src="<?php echo $icon_url ?>site-origin.png" alt="No Coding Required" />
			  <p>
				SiteOrigin Page Builder
			  </p>
			</div>
			<div class="width-33">
				<img src="<?php echo $icon_url ?>easydigitaldownloads.png" alt="No Coding Required" />
				<p>
				Easy Digital Downloads
				</p>
			</div>        
			<div class="clearfix"></div>
		    <div class="width-33">
		      <img src="<?php echo $icon_url ?>beaverbuilder.png" alt="No Coding Required" />
		      <p>
			    Beaver Builder
		      </p>
		    </div>
			<div class="width-33">
			  <img src="<?php echo $icon_url ?>fontawesome.png" alt="No Coding Required" />
			  <p>
				Font Awesome Icons
			  </p>
			</div>
			<div class="width-33">
				<img src="<?php echo $icon_url ?>googleanalytics.png" alt="No Coding Required" />
				<p>
				Google Analytics Event Tracking
				</p>
			</div> 
			<div class="clearfix"></div>
		    <div class="width-33">
		      <p>
		       
		      </p>
		    </div>
			<div class="width-33">
			  <img src="<?php echo $icon_url ?>google-fonts.png" alt="No Coding Required" />
			  <p>
				 Google Fonts
			  </p>
			</div>
			<div class="width-33">
				<p>
				</p>
			</div> 	
			<div class="clearfix"></div>		        
  		</div> <!-- icon-row -->
  	</div> <!-- container -->
  </div> <!-- section --> 
  	

  <div class="default-section">
    <div class="container">
      <h2>#1 Selling WordPress Button Plugin!</h2>
      <div class="btn-row">
        <img src="<?php echo $img_url ?>/s2-price.png" alt="img" class="inline-block" />
        <a href="<?php echo $middle_buy ?>" target="_blank" class="big-maxg-btn inline-block">Buy Now</a>
      </div>
    </div>
  </div>

  <div class="default-section">
    <div class="container">
      <h2>More Effective Buttons Are Made with MaxButton Pro's Advanced Features</h2>
      <img src="<?php echo $img_url ?>/s3-btns.png" alt="img" class="bordered" />
    </div>
  </div>

  <div class="default-section">
    <div class="container">
      <h2>Purchase Professionally Designed, Production Ready Button Packs</h2>
      <p>
        Button packs are sets of buttons with icons and settings already predefined for you, saving you loads of time. We have an ever-growing collection of button packs that you can buy and import into your website (only $5 each). You can then use those buttons as they are, or customize them to fit your needs (below are a few to get you started).
      </p>
      <div class="image-row">
        <div class="width-33">
          <img src="<?php echo $img_url ?>/s4-pack1.png" alt="img" class="bordered" />
        </div>
        <div class="width-33">
          <img src="<?php echo $img_url ?>/s4-pack2.png" alt="img" class="bordered" />
        </div>
        <div class="width-33">
          <img src="<?php echo $img_url ?>/s4-pack3.png" alt="img" class="bordered" />
        </div>
        <div class="clearfix"></div>
        <div class="width-33">
          <img src="<?php echo $img_url ?>/s4-pack4.png" alt="img" class="bordered" />
        </div>
        <div class="width-33">
          <img src="<?php echo $img_url ?>/s4-pack5.png" alt="img" class="bordered" />
        </div>
        <div class="width-33">
          <img src="<?php echo $img_url ?>/s4-pack6.png" alt="img" class="bordered" />
        </div>
        <div class="clearfix"></div>
        <div class="width-33">
          <img src="<?php echo $img_url ?>/s4-pack7.png" alt="img" class="bordered" />
        </div>
        <div class="width-33">
          <img src="<?php echo $img_url ?>/s4-pack8.png" alt="img" class="bordered" />
        </div>
        <div class="width-33">
          <img src="<?php echo $img_url ?>/s4-pack9.png" alt="img" class="bordered" />
        </div>
        <div class="clearfix"></div>
      </div>
    </div>
  </div>

  <div class="default-section">
    <div class="container">
      <h2>10+ Free Sets of Ready to Use Button Packs</h2>
      <p>
        No time to design your own buttons?
      </p>
      <p>
        Download any of our free button packs included with MaxButtons Pro and load them onto your site in seconds!
      </p>
      <div class="image-row">
        <div class="width-33">
          <img src="<?php echo $img_url ?>/s5-set1.png" alt="img" class="bordered" />
        </div>
        <div class="width-33">
          <img src="<?php echo $img_url ?>/s5-set2.png" alt="img" class="bordered" />
        </div>
        <div class="width-33">
          <img src="<?php echo $img_url ?>/s5-set3.png" alt="img" class="bordered" />
        </div>
        <div class="clearfix"></div>
        <div class="width-33">
          <img src="<?php echo $img_url ?>/s5-set4.png" alt="img" class="bordered" />
        </div>
        <div class="width-33">
          <img src="<?php echo $img_url ?>/s5-set5.png" alt="img" class="bordered" />
        </div>
        <div class="width-33">
          <img src="<?php echo $img_url ?>/s5-set6.png" alt="img" class="bordered" />
        </div>
        <div class="clearfix"></div>
        <div class="width-33">
          <img src="<?php echo $img_url ?>/s5-set7.png" alt="img" class="bordered" />
        </div>
        <div class="width-33">
          <img src="<?php echo $img_url ?>/s5-set8.png" alt="img" class="bordered" />
        </div>
        <div class="width-33">
          <img src="<?php echo $img_url ?>/s5-set9.png" alt="img" class="bordered" />
        </div>
        <div class="clearfix"></div>
      </div>
    </div>
  </div>

  <div class="default-section">
    <div class="container">
      <h2>Social Share Capabilities</h2>
      <p>
        MaxButtons Pro includes 17 Designed and Configured Social Share Collections to get the Most Value From your Content Marketing.
      </p>
      <p>
        Use one of our Collections out of the Box or Customize Your Icons with MaxButtons Pro's Editor.
      </p>
      <p>
        Here are Samples of Each of Our Collections.
      </p>
      <div class="social-row image-row">
        <div class="width-33">
          <p>
            5 Social Share Boxes

          <img src="<?php echo $img_url ?>/social-1.png" alt="img" />
          </p>
        </div>
        <div class="width-33">
          <p>
            Gray Social Share Buttons 
          <img src="<?php echo $img_url ?>/social-2.png" alt="img" />
         </p>
        </div>
        <div class="width-33">
          <p>
            Minimalistic Share Buttons
          <img src="<?php echo $img_url ?>/social-3.png" alt="img" />
           </p>
        </div>
         <div class="clearfix"></div>
        <div class="width-33">
          <p>
            Modern Social Share
          <img src="<?php echo $img_url ?>/social-4.png" alt="img" />
          </p>
        </div>
        <div class="width-33">
          <p>
            Monochrome Social Share Buttons
          <img src="<?php echo $img_url ?>/social-5.png" alt="img" />
          </p>
        </div>
        <div class="width-33">
          <p>
            Notched Box Social Share
          <img src="<?php echo $img_url ?>/social-6.png" alt="img" />
          </p>

        </div>
         <div class="clearfix"></div>
        <div class="width-33">
          <p>
            Round sharing collection
          <img src="<?php echo $img_url ?>/social-7.png" alt="img" />
          </p>

        </div>
        <div class="width-33">
          <p>
            Round White Social Share
          <img src="<?php echo $img_url ?>/social-8.png" alt="img" />
          </p>

        </div>
        <div class="width-33">
          <p>
            Rounded Corner Black Icons
          <img src="<?php echo $img_url ?>/social-9.png" alt="img" />
          </p>

        </div>
        
        <div class="clearfix"></div>
         
        <div class="width-33">
          <p>
            Share Plus Buttons
          <img src="<?php echo $img_url ?>/social-10.png" alt="img" />
          </p>

        </div>
        <div class="width-33">
          <p>
            Sharing Circles
          <img src="<?php echo $img_url ?>/social-11.png" alt="img" />
          </p>

        </div>
        <div class="width-33">
          <p>
            Social Counter Buttons
          <img src="<?php echo $img_url ?>/social-12.png" alt="img" />
          </p>

        </div>
        
         <div class="clearfix"></div>
        <div class="width-33">
          <p>
            Social Share Squares
          <img src="<?php echo $img_url ?>/social-13.png" alt="img" />
          </p>

        </div>
        <div class="width-33">
          <p>
            Stacked Rectangles
          <img src="<?php echo $img_url ?>/social-14.png" alt="img" />
          </p>

        </div>
        <div class="width-33">
          <p>
            Stacked Sharing Buttons
          <img src="<?php echo $img_url ?>/social-15.png" alt="img" />
          </p>

        </div>
        
         <div class="clearfix"></div>
        <div class="width-50">
          <p>
            Text Plus Count Share Buttons
          <img src="<?php echo $img_url ?>/social-16.png" alt="img" />
          </p>

        </div>
        <div class="width-50">
          <p>
            Transparent Social Share Squares
          <img src="<?php echo $img_url ?>/social-17.png" alt="img" />
          </p>
        </div>
         <div class="clearfix"></div>
      </div>
    </div>
  </div>

  <div class="default-section">
    <div class="container">
      <h2>Get MaxButtons Pro Now!</h2>
      <div class="btn-row">
        <a href="<?php echo $bottom_buy ?>" target="_blank" class="big-maxg-btn inline-block">Get It Now</a>
      </div>
    </div>
  </div>
    </div>
    <!-- wrapper -->
	
 
<?php $admin->get_footer(); ?> 
