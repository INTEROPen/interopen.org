<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
/* Class to Parse CSS. Load as array with diffent pseudo-types,
	ability to add nested and new root blocks 
	parses via scss
	ability to use complicated css stuff via scss mixins parsing like gradient
	auto-discovery for -unit field types to set units (like px, or %) 
	auto-discovery of fields via domobj.
	
*/

use \Exception as Exception;

class maxCSSParser
{
	protected $struct = array(); 
	protected $domObj = '';
	protected $pseudo = array("hover","active","responsive"); 
	
	protected $data; 
	protected $output_css;
	
	protected $inline = array();
	protected $responsive = array();
	
	// settings
	protected $elements_ignore_zero = array(
		'text-shadow-left',
		'text_shadow-top', 
		'text-shadow-width',
		'box-shadow-offset-left',
		'box-shadow-offset-top', 
		'box-shadow-width',
		'box-shadow-spread', 
	);  // items to ignore if value is zero, otherwise they become unremovable ( where 0 is still something on display)  
	
	protected $important = false;
	
	// log possible problems and incidents for debugging;
	protected $parse_log = array();
	
	function __construct()
	{
		//$root[] = array("a" => array("hover","active","responsive")); 	

	}
	
	function loadDom($domObj)
	{	
		$this->domObj = $domObj;

		$root = $domObj->find(0,0);
		$struct[$root->tag] = array(); 	 
		
		$children = $root->children();
 
		if (count($children) > 0) 
			$struct[$root->tag] = $this->loadRecursive(array(), $children);
		
		$this->struct = $struct;


	}
	
	function loadRecursive($struct, $children)
	{
		foreach($children as $domChild)
		{

			$class = $domChild->class; 
			$class = str_replace(" ",".", $class); // combine seperate classes 
					
  			$struct[$class]["tag"] = $domChild->tag; 
 
			$child_children = $domChild->children(); 

			if (count($child_children) > 0) 
			{
				 
				$struct[$class]["children"] = $this->loadRecursive(array(), $child_children);
			}
		}
 
		return $struct;
	}
	
		
	function parse($data)
	{	
		$this->clear(); 
		
		$struct = $this->struct; 

		$this->data = $data; 

 
 		if (isset($data["settings"]))  // room for settings in parser
 		{
 			$settings = $data["settings"];
 			$this->important = (isset($settings["important"])) ? $settings["important"] : false; 
 			
 			unset($this->data["settings"]); 
 		}

		$elements = array_shift($struct); // first element is a 'stub' root.  

		if ( is_null($elements) )
			return; 

		foreach($elements as $el => $el_data)
		{

			$this->parse_part($el,$el_data); 		
		}


		$this->parse_responsive();
		
		maxUtils::startTime('compile CSS');
		$css = $this->compile($this->output_css);
		maxUtils::endTime('compile CSS');


		return $css;
 
	}
	
	// reset output values. 
	protected function clear() 
	{
		$this->data = ''; 
		$this->output_css = ''; 
		$this->inline = array();
		//$this->struct = array();
		$this->responsive = array();
	
	}
	
	protected function compile($css)
	{
		$scss = new \Leafo\ScssPhp\Compiler();
		$scss->setImportPaths(MB()->get_plugin_path() . "assets/scss");
		
		$minify = get_option("maxbuttons_minify", 1); 
		if ($minify == 1)
			$scss->setFormatter('\Leafo\ScssPhp\Formatter\Compressed');
		
		$compile = " @import '_mixins.scss';  " . $css;
		//maxUtils::addTime("CSSParser: Compile start ");
		
		try
		{
			$css = $scss->compile($compile);
		} catch (Exception $e) { 
 
			$css = $this->output_css; 
		} 

		//maxUtils::addTime("CSSParser: Compile end ");

		return $css;
	}
	
	function parse_part($element, $el_data, $el_add = '')
	{
		maxUtils::addTime("CSSParser: Parse $element ");
				
		$tag = $el_data["tag"]; 
		$element_data = $this->findData($this->data, $element);
		
		// not using scss selectors here since there is trouble w/ the :pseudo selector, which should be put on the maxbutton / a tag.  		
		if ($element != '')
		{	$el_add .= " ." . $element;
 	
 		}
		
	 	if (isset($element_data["responsive"])) 
	 	{
	 		$responsive = $element_data["responsive"]; // doing that at the end
	 		unset($element_data["responsive"]); 

	 		$this->responsive[$el_add] = $responsive;
	 	}
	 
		foreach($element_data as $pseudo => $values)
		{
			
			if ($pseudo != 'normal') 
			{
				// select the maxbutton case, ending with either space or next class -dot. 
				$ps_selector = preg_replace('/.maxbutton$|.maxbutton([.| ])/i',".maxbutton:$pseudo\$1",$el_add);

				$this->output_css .= " $ps_selector { "; 
			}
			else {
				$this->output_css .= " $el_add { ";
			}
			
			$values = $this->doMixins($values);

			foreach($values as $cssTag => $cssVal)
			{
 
				$statement =  $this->parse_cssline($values, $cssTag,$cssVal); ///"$cssTag $css_sep $cssVal$unit$css_end ";

				if ($statement)
				{
					$this->output_css .= $statement ;
		
					if (! isset($this->inline[$pseudo][$element])) $this->inline[$pseudo][$element] = '';
					$this->inline[$pseudo][$element] .= $statement;
				} 
			}

		 	$this->output_css .= " } "; 
		}
			if (isset($el_data["children"])) 
			{
				foreach($el_data["children"] as $child_id => $child_data)
				{
 
					$this->parse_part($child_id, $child_data, $el_add);
				}
			}

	}
	
	function parse_cssline($values, $cssTag, $cssVal, $css_end = ';')
	{

		// unit check - two ways; either unitable items is first or unit declaration.
		if (isset($values[$cssTag . "_unit"])) 
		{
			$unit = $values[$cssTag . "_unit"]; 
		}
		elseif(strpos($cssTag, "_unit") !== false)
		{
			return false; // no print, should be found under first def.					
		}
		else $unit = '';
		

		$important = ($this->is_important()) ? " !important" : ""; 
		$important = ($cssTag == '@include') ? "" : $important; // mixin's problem, no checking here. 
		
		$css_sep = ($cssTag == '@include') ? $css_sep = '' : ':';
		
		if ($cssVal == 0 && in_array($cssTag, $this->elements_ignore_zero))
			return false; 
			
		if($cssVal !== '' && $cssTag !== '')
		{
			$statement = "$cssTag $css_sep $cssVal$unit$important$css_end ";
			return $statement;
		}
		return false;
		
	}
	
	function parse_responsive()
	{

		$responsive = $this->responsive; 
		if (! is_array($responsive) || count($responsive) == 0) 
			return;  

		$media_queries = maxUtils::get_media_query(2); // query names
					
		$output = ''; 
		
		$query_array = array(); 


		foreach($responsive as $element => $queries)
		{
			foreach($queries as $query => $qdata)
			 foreach($qdata as $index => $data)
			{{
				$query_array[$query][$index][$element] = $data; 
			
			}}
		}
 

	 			
		foreach($query_array as $query => $vdata):

			if ($query == 'custom') 
			{

				// first discover the custom size properties. 
				foreach($vdata as $index => $data):
					foreach($data as $element => $values):

					if (isset($values["custom_maxwidth"]) || isset($values["custom_minwidth"])  ) 
					{
						 
						$minwidth = (isset($values["custom_minwidth"])) ? intval($values["custom_minwidth"]) : -1;
						$maxwidth = (isset($values["custom_maxwidth"])) ? intval($values["custom_maxwidth"]) : -1; 
						
 
						unset($vdata[$index][$element]["custom_minwidth"]); 
						unset($vdata[$index][$element]["custom_maxwidth"]);
						
 
						// make it always an integer 
						if ($minwidth == '') $minwidth = 0; 
						if ($maxwidth == '') $maxwidth = 0; 
						
						// if minwidth is not set and maxwidth zero or not set - ignore since it would result in an empty media line. 
						//if ($minwidth <= 0 && $maxwidth <= 0)  
						//{
							//continue;
						//}
						
						if ($minwidth > 0 && $maxwidth > 0) 
							$qdef = "only screen and (min-width: $minwidth" . "px) and (max-width: $maxwidth" . "px)";  
						if ($minwidth >= 0 && $maxwidth <= 0) 
							$qdef = "only screen and (min-width: $minwidth" . "px) "; 
						if ($minwidth <= 0 && $maxwidth > 0)	
							$qdef = "only screen and (max-width: $maxwidth" . "px) "; 			
						
						//break;

					}	
				    endforeach; //foreach data 
				    
				    // The problem is that every 'custom' query needs to run with a different qdef unlike other queries. 
 
					$qdata = array($vdata[$index]);
 
					$output = $this->parse_responsive_definition($output, $qdef, $qdata); 
				endforeach;	// foreach vdata 
			}
			else
			{
 
				$qdef = $media_queries[$query]; 
				$output = $this->parse_responsive_definition($output, $qdef, $vdata);
			}

		endforeach;
 
 
		$this->output_css .= $output;
	}
	
	protected function parse_responsive_definition($output, $qdef, $vdata)
	{
 
		if (! isset($qdef) || $qdef == '')  {
				  
				return; // no definition. 
			}

 
			$output .= "@media ". $qdef . " { "; 

	
		foreach($vdata as $index => $data)
		{
 
			foreach($data as $element => $values) {
			 //foreach($vdat as $index => $values):
				$output .= $element . " { "; 
				$css_end = ';';
				
				// same as parse part, maybe merge in future
				foreach($values as $cssTag => $cssVal)
				{
					// unit check - two ways; either unitable items is first or unit declaration.
					$statement =  $this->parse_cssline($values, $cssTag,$cssVal);
					if($statement)
						$output .= $statement;
					
				}

				$output .= " } "; 
			// endforeach;
			}

		
		  }
		  $output .= " } ";  
		  			
		//endforeach;	
	
		return $output;
	}
	
	private function is_important()
	{

		if ($this->important == 1)
			return true;
		else 
			return false;
	}
	
	function findData($data, $el)
	{
		$classes = explode(".", $el); 

		foreach($data as $part => $values) 
		{
			if (in_array($part, $classes))
				return $data[$part];
		}
 		return array();
	}
	
	function doMixins($values)
	{
		$mixins = array("gradient", "box-shadow", "text-shadow");
 
		foreach($mixins as $mixin) 
		{

			$results = preg_grep("/^$mixin/i",array_keys($values) );
			if (count($results) === 0) 
				continue; // no mixins. 
		
			$mixin_array = array();
		 	foreach($results as $result)
		 	{
		 		$mixin_array[$result] = $values[$result]; 
		 	}

			if (count($mixin_array) > 0)
			{
				switch($mixin)
				{
					case "gradient": 
						$values = $this->mixin_gradient($mixin_array, $values);
				
					break;
					case "box-shadow": 
						$values = $this->mixin_boxshadow($mixin_array, $values);
					break;	
					case "text-shadow": 
						$values = $this->mixin_textshadow($mixin_array, $values);
					break;
				}
			}

		
		} 
		return $values;
	}
	
	function mixin_gradient($results, $values)
	{

		$start = isset($results["gradient-start-color"]) ? $results["gradient-start-color"] : '';
		$end = isset(  $results["gradient-end-color"]  ) ?  $results["gradient-end-color"] : ''; 
		$start_opacity = isset(  $results["gradient-start-opacity"]  ) ?  $results["gradient-start-opacity"] : ''; 
		$end_opacity = isset(  $results["gradient-end-opacity"]  ) ?  $results["gradient-end-opacity"] : ''; 		
		$stop =  (isset( $results["gradient-stop"]) && $results["gradient-stop"] != '') ?  $results["gradient-stop"] . "%" : '45%'; 
		// default to use ( old situation )
		$use_gradient = (isset($results['gradient-use-gradient']) && $results['gradient-use-gradient'] != '') ? $results['gradient-use-gradient'] : 1;
		
		$start = maxUtils::hex2rgba($start, $start_opacity);
		$end = maxUtils::hex2rgba($end, $end_opacity);		
		
		$important = ($this->is_important()) ? "!important" : "";
		//$values = $this->add_include($values, "linear-gradient($start,$end,$stop,$important)");		

		if ($use_gradient == 1)
		{
			$values = $this->add_include($values, "linear-gradient($start,$end,$stop,$important)");			
		}
		else {
			$values['background-color'] = $start; 
		}

		// remove the non-css keys from the value array ( field names ) 
		$values = array_diff_key($values, $results);

		return $values;
 
	}
	
	function mixin_boxshadow($results, $values)
	{
		$width = $results["box-shadow-width"]; 
		$left = $results["box-shadow-offset-left"]; 
		$top = $results["box-shadow-offset-top"]; 
		$spread = isset($results['box-shadow-spread']) ? $results['box-shadow-spread'] : 0; 
		$color = isset($results["box-shadow-color"]) ? $results["box-shadow-color"] : ''; 
		
		$important = ($this->is_important()) ? "!important" : ""; 
		
		if ($width == 0 && $left == 0 && $top == 0) 
			return $values; 
		
		$values = $this->add_include($values, "box-shadow($left, $top, $width, $color,$spread, false, $important) ");			
		$values = array_diff_key($values, $results);

		return $values; 		
	}
	
	function mixin_textshadow($results, $values)
	{
		$width = isset($results["text-shadow-width"]) ? $results["text-shadow-width"] : 0; 
		$left = isset($results["text-shadow-left"]) ? $results["text-shadow-left"] : 0; 
		$top = isset($results["text-shadow-top"]) ? $results["text-shadow-top"] : 0; 
		$color = isset($results["text-shadow-color"]) ? $results["text-shadow-color"] : ''; 
		$important = ($this->is_important()) ? "!important" : ""; 
					
 		if ($width == 0 && $left == 0 && $top == 0) 
		{
			$values = array_diff_key($values, $results); // remove them from the values, prevent incorrect output.
			return $values; 
		}
		
		$values = $this->add_include($values, "text-shadow ($left,$top,$width,$color $important)"); 
		
		$values = array_diff_key($values, $results);
		
		return $values;
	}
	
	private function add_include($values, $include)
	{
		if (isset($values["@include"])) 
			$values["@include"] .= "; @include " . $include; 
		else
			$values["@include"] = $include; 	
		return $values; 
	}
	
	function outputInline($domObj, $pseudo = 'normal')
	{
		$domObj = $domObj->load($domObj->save());
		
		$inline = $this->inline;
		
		// ISSUE #43 Sometimes this breaks 
		if (! isset($inline[$pseudo])) 
			return $domObj;

		$elements = array_keys($inline[$pseudo]);
		if ($pseudo != 'normal') // gather all elements
			$elements = array_merge($elements, array_keys($inline["normal"]));
						
		foreach($elements as $element  )
		{
			$styles = isset($inline[$pseudo][$element]) ? $inline[$pseudo][$element] : ''; 
		
			if ($pseudo != 'normal') // parse all possible missing styles from pseudo el.
				$normstyle = $this->compile($inline['normal'][$element]); 
 
			$normstyle = ''; 
			if ($pseudo != 'normal') // parse all possible missing styles from pseudo el.
				$normstyle = $this->compile($inline['normal'][$element]); 
 
			maxUtils::addTime("CSSParser: Parse inline done");
			
			$styles = $normstyle . $this->compile($styles);
			
			$element = trim(str_replace("."," ", $element)); // molten css class, seperator. 
		 
			$el = $domObj->find('[class*="' . $element . '"]', 0);
			if (is_null($el)) echo "NULL"; 
			$el->style = $styles;
		
		}
		
		return $domObj;
		
	}	
	
	function output() 
	{
		
	
	}
	
}

class compileException extends Exception { 
	protected $code = -1;

}
