<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
$blockClass["responsive"] = "responsiveBlock"; 
$blockOrder[90][] = "responsive"; 

class responsiveBlock extends maxBlock 
{
	protected $blockname = 'responsive'; 
	protected $fields = array("options" => '',
							  "auto_responsive" => array("default" => 0),
							  "media_query" => array("default" => array()
							  				), 
							  	  				  
							  ); 
	// multifields for manual conversion // define +/- the same as normal fields. 
	// to find solution in parsing stuff back to button.
	protected $multi_fields = array("mq_button_width" => array("default" => '0', 
															   "css" => "width", 
															   "csspseudo" => "responsive", 
															   
															),
									"mq_button_width_unit" => array("default" => 'px', 
																	"css" => "width_unit",
																	"csspseudo" => "responsive",
															), 
									"mq_button_height" => array("default" => '0', 
															   "css" => "height", 
															   "csspseudo" => "responsive", 
															   
															),
									"mq_button_height_unit" => array("default" => 'px', 
																	"css" => "height_unit",
																	"csspseudo" => "responsive",
															),															
									"mq_container_width" => array("default" => 0, 
																"css" => "width",
																"csspart" => "mb-container",
																"csspseudo" => "responsive",
															),
									"mq_container_float" => array("default" => "", 
																"css" => "float", 
																"csspart" => "mb-container",
																"csspseudo" => "responsive",
														),
									"mq_container_width_unit" => array("default" => "px",
																	"css" => "width_unit",
																	"csspart" => "mb-container", 
															), 
									"mq_font_size" => array("default" => 90, 
															"css" => "font-size", 
															"csspart" => "mb-text",
														),						
									"mq_font_size_unit" => array("default" => "%", 
															    "css" => "font-size_unit", 
															    "csspart" => "mb-text"), 
															 					
									"mq_custom_minwidth" => array("default" => "0", 
															  "css" => "custom_minwidth"), 
									"mq_custom_maxwidth" => array("default" => "0", 
															  "css" => "custom_maxwidth"),
									"mq_hide" 			=> array("default" => '',
																 "css" => "display", 
																 "csspart" => "mb-container, maxbutton, mb-center",
																 
															  ), 
									); 
	
	
	public function parse_css($css, $mode = 'normal')
	{
		if ($mode != 'normal') 
			return $css;

		if (! isset($this->data[$this->blockname])) 
			return $css; 
				
		$data = $this->data[$this->blockname];
 
		if (isset($data["auto_responsive"]) && $data["auto_responsive"] == 1)
		{	// generate auto_rules for responsive.
			$css["maxbutton"]["responsive"]["phone"][0]["width"] = "90%"; 
			$css["mb-container"]["responsive"]["phone"][0]["width"] = "90%"; 
 			$css["mb-container"]["responsive"]["phone"][0]["float"] = "none"; 
 			$css["mb-text"]["responsive"]["phone"][0]["font-size"] = "90%"; 
 			
 			 
 			if ( isset($this->data["text"]["font_size"]) )
 			{
	 			$css["mb-text"]["responsive"]["phone"][0]["font-size"] = floor(intval($this->data["text"]["font_size"]) * 0.8) . 'px';  				
 			}
 			
		}
		
		if (! isset($data["media_query"]))
 			return $css;
 			
		foreach($data["media_query"] as $query => $data ):
			$i = 0;
			
			foreach($data as $index => $fields):
			
		
			foreach($fields as $field => $value)
			{
				$csspart = (isset($this->multi_fields[$field]["csspart"])) ? explode(",",$this->multi_fields[$field]["csspart"]) : array('maxbutton') ; 
				$css_stat = $this->multi_fields[$field]["css"]; 

 
				if ($value == '' || $value == '0') 
				{  }
				elseif ($query != 'custom' && ($field == 'mq_custom_maxwidth' || $field == "mq_custom_minwidth")) 
				{ } // skip custom fields on noncustom query
				else
				{
						foreach($csspart as $j => $part)
						{
  							$part = trim($part); // spaces in array
							$css[$part]["responsive"][$query][$i][$css_stat] = $value; 						
						}	
						
/*					}
					else	
					{	
 
						$css[$csspart]["responsive"][$query][$i][$css_stat] = $value; 
					} */
				}
				
			}
			$i++;	
			endforeach;

		endforeach;
		return $css;
	}
	

	public function save_fields($data, $post)
	{
 
		$queries = (isset($post["media_query"])) ? $post["media_query"] : null; 
		$media_queries = array(); 
		
		if (is_null($queries))
			return $data; 
 
 		foreach($queries as $i => $query)
		{
 
			if ($query != '')
			{
				//$media_queries[$query] = array(); 
			
				// collect the other fields. 
				$c = isset($media_queries[$query]) ? count($media_queries[$query]) : 0; 
				
				foreach($this->multi_fields as $field => $values)
				{
					$default = isset($values["default"]) ? $values["default"] : ''; 

					if (isset($post[$field][$i])) 
					{
						$postval = $post[$field][$i]; 
						if (is_numeric($default))  // sanitize.
						{
							$postval = intval($postval); 
						}
						else
							$value = sanitize_text_field($postval); 
							
						$media_queries[$query][$c][$field] = $postval; 
					}
				}

			}
		}
 
		$data[$this->blockname]["media_query"] = $media_queries;
		
		$data[$this->blockname]["auto_responsive"] = (isset($post["auto_responsive"])) ? $post["auto_responsive"] : 0; 
		
		return $data;
		
	}

	
	public function admin_fields()
	{
		$data = isset($this->data[$this->blockname]) ? $this->data[$this->blockname] : array(); 
		
		$media_names =  maxUtils::get_media_query(1); // nicenames
		$media_desc = maxUtils::get_media_query(3);
		$units = array("px" => __("px","maxbuttons"),
					   "%" => __("%","maxbuttons")
					  );
		$container_floats = array(
							"" => "",
							"none" => __("None","maxbuttons"), 
							"left" => __("Left","maxbuttons"), 
							"right" => __("Right","maxbuttons"),
						);
	
		foreach($this->fields as $field => $options)
		{		
 	 	    $default = (isset($options["default"])) ? $options["default"] : ''; 
			$$field = (isset($data[$field])) ? $data[$field] : $default;
			${$field  . "_default"} = $default; 
		}
		
		// sorting routine via array merge. 
		$fk = array_flip(array_keys($media_query)); 
		$names_used = array_intersect_key($media_names,$fk);		 
 		$media_query = array_merge($names_used,$media_query);

?>
			<script type='text/javascript'>
				var responsiveMap = '<?php echo json_encode($this->multi_fields); ?>';
			</script>
			
			<div class="mb_tab option-container">
				<div class="title"><?php _e('Responsive Settings', 'maxbuttons') ?></div>
				<div class="inside responsive">

					<div class="option-design"> 
						<p class="note"><?php _e("Responsive settings let you decide the behavior of the button on different devices and screen sizes. For instance large buttons on small screens.","maxbuttons") ?></p>	
					<?php 		
				$auto = new maxField('switch'); 
				$auto->label = __('Auto Responsive (experimental)', 'maxinbound');
				$auto->name = 'auto_responsive';
				$auto->id = $auto->name;
				$auto->value = '1'; 
				$auto->checked = checked($auto_responsive, 1, false);  
				$auto->output ('start','end'); 
				?>

						<div class="clear"></div>
						<p class="note"><strong><?php _e("Note:","maxbuttons"); ?> </strong><?php _e(" Auto responsive settings will take a guess only on small screens. To control your responsive settings uncheck this button. This will show more options.","maxbuttons"); ?></p>	
					</div>
 

					<div class="option-design new-query">
						<label for='new_query'><?php _e('New Query', 'maxbuttons') ?></label>
						
						<div class="input">
							<select name="new_query" id="new_query">
							<?php 

							foreach ($media_names as $key => $val):
								$disabled = isset($media_query[$key]) && $key !== 'custom'    ? ' DISABLED ' : ''; 
							?>
							<option value="<?php echo $key ?>" <?php echo $disabled ?> ><?php echo $val ?></option>
							 
							
							<?php endforeach; ?>
							</select>
							<?php //echo maxUtils::selectify("new_query",$media_names,'' ); ?>
							<a href='javascript:void(0)' class="button add_media_query"><?php _e("Add","maxbuttons") ?></a>
						</div>
						
 
						<div class="clear"></div>
					</div> <!-- option design -->
										
				<div class='option-design media_queries_options'>
					<?php
					$i = 0 ; 
					foreach($media_query as $item => $data):
						foreach ($data as $index => $fields):
												 
						if (! isset($fields['mq_hide'])) 
							$fields['mq_hide'] = 0; 
							
						$condition = array('target' => 'mq_hide[' . $i . ']', 'values' => 'unchecked'); 
						$show_conditional = htmlentities(json_encode($condition)); 	
						
						?>
						<div class='media_query' data-query="<?php echo $item ?>" data-id="<?php echo $i ?>"> 
							<span class='removebutton dashicons dashicons-no'></span>
							
							<input type="hidden" name="media_query[<?php echo $i ?>]" value="<?php echo $item ?>"> 
							<label class='title'><?php echo $media_names[$item] ?></label>
							<p class='description'><?php echo $media_desc[$item] ?></p>							
							<?php 
						if ($item == "custom") { $custom_class = 'option custom'; } else { $custom_class = 'option custom hidden '; }

						$min_width = new maxField('number'); 
						$min_width->id = 'mq_custom_minwidth[' . $i . ']'; 
						$min_width->name= $min_width->id; 
						$min_width->label = __('Min Width', 'maxbuttons');
						$min_width->value = $fields['mq_custom_minwidth'];  
						$min_width->inputclass = 'small'; 
						$min_width->main_class = $custom_class; // hide label
						$min_width->output('start', ''); 
   
						
						$max_width = new maxField('number'); 
						$max_width->id = 'mq_custom_maxwidth[' . $i . ']'; 
						$max_width->name = $max_width->id; 
						$max_width->label = __('Max Width','maxbuttons');
						$max_width->value = $fields['mq_custom_maxwidth'];  
						$max_width->inputclass = 'small'; 
						$max_width->mainclass = $custom_class;
						$max_width->output('','end'); 
						

						$font_size = new maxField('number');
						$font_size->id = 'mq_font_size[' . $i . ']'; 
						$font_size->name = $font_size->id; 
						$font_size->value = $fields['mq_font_size']; 
						$font_size->label = __('Font Size', 'maxbuttons'); 
						$font_size->min = 0; 
						$font_size->inputclass = 'tiny'; 
						$font_size->start_conditional = $show_conditional;
						$font_size->output('start', ''); 
						
						$fsize_unit = new maxField('generic'); 
						$fsize_unit->id = 'mq_font_size_unit[' . $i . ']'; 
						$fsize_unit->name = $fsize_unit->id; 
						$fsize_unit->value = $fields['mq_font_size_unit']; 
						$fsize_unit->content = maxUtils::selectify($fsize_unit->id, $units, $fsize_unit->value);
					//	$fsize_unit->conditional = $show_conditional;
						$fsize_unit->output('','end');
						
						// width
						$width = new maxField('number'); 
						$width->id = 'mq_button_width[' . $i . ']'; 
						$width->name = $width->id; 
						$width->value = $fields['mq_button_width']; 
						$width->label = __('Button Width', 'maxbuttons'); 
						$width->min = 0; 
						$width->inputclass = 'tiny';
						$width->start_conditional = $show_conditional; 
						$width->output('start',''); 
						
						$width_unit = new maxField('generic'); 
						$width_unit->id = 'mq_button_width_unit[' . $i . ']'; 
						$width_unit->name = $width_unit->id; 
						$width_unit->value = $fields['mq_button_width_unit']; 
						$width_unit->content = maxUtils::selectify($width_unit->id, $units, $width_unit->value);
					//	$width_unit->conditional = $show_conditional;
						$width_unit->output('','');
						 
						// height
						$height = new maxField('number'); 
						$height->id = 'mq_button_height[' . $i . ']'; 
						$height->name = $height->id; 
						$height->value = $fields['mq_button_height']; 
						$height->label = __("Button Height", "maxbuttons"); 
						$height->min = 0; 
						$height->inputclass = 'tiny'; 
					//	$height->conditional = $show_conditional;
						$height->output('',''); 
						
						$height_unit = new maxField('generic'); 
						$height_unit->id = 'mq_button_height_unit[' . $i . ']'; 
						$height_unit->name = $height_unit->id; 
						$height_unit->value = $fields['mq_button_height_unit'];  
						$height_unit->content = maxUtils::selectify($height_unit->id, $units, $height_unit->value);
					//	$height_unit->conditional = $show_conditional;
						$height_unit->output('','end');
						
						$cwidth = new maxField('number'); 
						$cwidth->id = 'mq_container_width[' . $i . ']'; 
						$cwidth->name = $cwidth->id; 
						$cwidth->label = __('Container Width', 'maxbuttons'); 
						$cwidth->value = $fields['mq_container_width']; 
						$cwidth->min = '0'; 
						$cwidth->inputclass = 'tiny'; 
						$cwidth->start_conditional = $show_conditional;
						$cwidth->output('start', ''); 
						
						$cwidth_unit = new maxField('generic'); 
						$cwidth_unit->id = 'mq_container_width_unit[' . $i . ']'; 
						$cwidth_unit->name = $cwidth_unit->id; 
						$cwidth_unit->value = $fields['mq_container_width_unit']; 
						$cwidth_unit->content = maxUtils::selectify($cwidth_unit->id, $units, $cwidth_unit->value);
					//	$cwidth_unit->conditional = $show_conditional;
						$cwidth_unit->output('','end');
						
						$cfloat = new maxField('generic');
						$cfloat->id = 'mq_container_float[' . $i . ']'; 
						$cfloat->name = $cfloat->id; 
						$cfloat->label = __('Container Float','maxbuttons'); 
						$cfloat->value = $fields['mq_container_float'];
						$cfloat->content = maxUtils::selectify($cfloat->id, $container_floats, $cfloat->value);
						$cfloat->start_conditional = $show_conditional;
						$cfloat->output('start','end'); 
							
						$hide = new maxField('switch'); 
						$hide->id = 'mq_hide[' . $i . ']'; 
						$hide->name = $hide->id; 
						$hide->value = 'none';
						$hide->checked = checked($fields['mq_hide'], $hide->value, false); 
						$hide->label = __('Hide button in this view','maxbuttons'); 
						$hide->output('start','end');
						
						/*
						$preview = new maxField('button'); 
						$preview->id = 'mq_preview[' . $i . ']';
						$preview->inputclass = 'responsive_preview';  
						$preview->button_label = __('Show in preview','maxbuttons');
						$preview->output('', 'end'); 
						*/
						
						$i++;
						//if ($item != 'custom')
						//	unset($media_names[$item]); // remove existing queries from new query selection
						?> 
						</div> <!-- media query -->
					<?php
					endforeach;
						endforeach;	
					
					
					?>				
			</div> <!-- option -design --> 
				<div class="new_query_space"></div>		
			</div> <!-- inside --> 
		
			<input type="hidden" name="next_media_index" value="<?php echo $i ?>" >
			
			<div class='media_option_prot'>

				<div class='media_query' data-query=''> 
							<span class='removebutton dashicons dashicons-no'></span>

							<input type="hidden" name="media_query[]" value=""> 
							<label class='title'></label>
							<p class='description'>&nbsp;</p>
						<?php							
						$custom_class = 'option hidden custom'; 
						$condition = array('target' => 'mq_hide[]', 'values' => 'unchecked'); 
						$show_conditional = htmlentities(json_encode($condition)); 	
						
						$min_width = new maxField('number'); 
						$min_width->id = 'mq_custom_minwidth[]'; 
						$min_width->name= $min_width->id; 
						$min_width->label = __('Min Width', 'maxbuttons');
					//	$min_width->value = $fields['mq_custom_minwidth'];  
						$min_width->inputclass = 'small'; 
						$min_width->main_class = $custom_class; // hide label
						$min_width->output('start', ''); 
						
						$max_width = new maxField('number'); 
						$max_width->id = 'mq_custom_maxwidth[]'; 
						$max_width->name = $max_width->id; 
						$max_width->label = __('Max Width','maxbuttons');
					//	$max_width->value = $fields['mq_custom_maxwidth'];  
						$max_width->inputclass = 'small'; 
						$max_width->mainclass = $custom_class;
						$max_width->output('','end'); 
							

						$font_size = new maxField('number');
						$font_size->id = 'mq_font_size[]'; 
						$font_size->name = $font_size->id; 
						//$font_size->value = $fields['mq_font_size']; 
						$font_size->label = __('Font Size', 'maxbuttons'); 
						$font_size->min = 0; 
						$font_size->inputclass = 'tiny'; 
						$font_size->start_conditional = $show_conditional;
						$font_size->output('start', ''); 
						
						$fsize_unit = new maxField('generic'); 
						$fsize_unit->id = 'mq_font_size_unit[]'; 
						$fsize_unit->name = $fsize_unit->id; 
						//$fsize_unit->value = $fields['mq_font_size_unit']; 
						$fsize_unit->content = maxUtils::selectify($fsize_unit->id, $units, $fsize_unit->value) ;
						$fsize_unit->output('','end');
						
						// width
						$width = new maxField('number'); 
						$width->id = 'mq_button_width[]'; 
						$width->name = $width->id; 
						//$width->value = $fields['mq_button_width']; 
						$width->label = __('Button Width', 'maxbuttons'); 
						$width->min = 0; 
						$width->inputclass = 'tiny'; 
						$width->start_conditional = $show_conditional;
						$width->output('start',''); 
						
						$width_unit = new maxField('generic'); 
						$width_unit->id = 'mq_button_width_unit[]'; 
						$width_unit->name = $width_unit->id; 
						//$width_unit->value = $fields['mq_button_width_unit']; 
						$width_unit->content = maxUtils::selectify($width_unit->id, $units, $width_unit->value);
						$width_unit->output('','');
						 
						// height
						$height = new maxField('number'); 
						$height->id = 'mq_button_height[]'; 
						$height->name = $height->id; 
						//$height->value = $fields['mq_button_height']; 
						$height->label = __("Button Height", "maxbuttons"); 
						$height->min = 0; 
						$height->inputclass = 'tiny'; 
						$height->output('',''); 
						
						$height_unit = new maxField('generic'); 
						$height_unit->id = 'mq_button_height_unit[]'; 
						$height_unit->name = $height_unit->id; 
					//	$height_unit->value = $fields['mq_button_height_unit'];  
						$height_unit->content = maxUtils::selectify($height_unit->id, $units, $height_unit->value) ;
						$height_unit->output('','end');
						
						$cwidth = new maxField('number'); 
						$cwidth->id = 'mq_container_width[]'; 
						$cwidth->name = $cwidth->id; 
						$cwidth->label = __('Container Width', 'maxbuttons'); 
						//$cwidth->value = $fields['mq_container_width']; 
						$cwidth->min = '1'; 
						$cwidth->inputclass = 'tiny'; 
						$cwidth->start_conditional = $show_conditional;
						$cwidth->output('start', ''); 
						
						$cwidth_unit = new maxField('generic'); 
						$cwidth_unit->id = 'mq_container_width_unit[]'; 
						$cwidth_unit->name = $cwidth_unit->id; 
						//$cwidth_unit->value = $fields['mq_container_width_unit']; 
						$cwidth_unit->content = maxUtils::selectify($cwidth_unit->id, $units, $cwidth_unit->value);
						$cwidth_unit->output('','end');
						
						$cfloat = new maxField('generic');
						$cfloat->id = 'mq_container_float[]'; 
						$cfloat->name = $cfloat->id; 
						$cfloat->label = __('Container Float','maxbuttons'); 
					//	$cfloat->value = $fields['mq_container_float'];
						$cfloat->content = maxUtils::selectify($cfloat->id, $container_floats, $cfloat->value);
						$cfloat->start_conditional = $show_conditional;
						$cfloat->output('start','end'); 
						 
						if (! isset($fields['mq_hide'])) 
							$fields['mq_hide'] = 0; 
							
						$hide = new maxField('switch'); 
						$hide->id = 'mq_hide[]'; 
						$hide->name = $hide->id; 
						$hide->value = 'none';
						//$hide->checked = checked($fields['mq_hide'], $hide->value, false); 
						$hide->label = __('Hide button in this view','maxbuttons'); 
						$hide->output('start','end');
						
						/*$preview = new maxField('button'); 
						$preview->id = 'mq_preview[]'; 
						$preview->button_label = __('Show in preview','maxbuttons');
						$preview->output('', 'end'); 
					 	*/					
			?>
				</div> <!-- /media-query --> 

			</div> <!-- /media-query-prot -->
			<div id="media_desc">
			<?php foreach($media_desc as $key => $desc)
			{
				echo "<span id='$key'>$desc</span>";
			
			}
			?>
			</div>
			

		</div> <!-- container --> 
	
<?php	
	}

} // class

?>
