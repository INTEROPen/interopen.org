<?php
namespace MaxButtons;

defined('ABSPATH') or die('No direct access permitted');

class simpleTemplate
{
	private $version = '1.1.1'; 
		
	public static function parse($template_file, $object) 
	{
		if ($template_file == '') 
		{
			return false;
		}
		$template = file_get_contents($template_file); 


		$template = static::checkfor($template, $object);		
		$template = static::checkif($template, $object);		
		$template = static::checkvar($template, $object);			
	
		
		return $template; 
	}
	
	public static function checkvar($template, $object)
	{
		preg_match_all('/%%(.*?)%%/im', $template, $matches); 
		
		if(isset($matches[1]) && count($matches[1]) > 0) 
		{
			for($i = 0; $i < count($matches[1]); $i++)
			{
				$match = $matches[1][$i]; 
				$replace = $matches[0][$i]; 
				
				if (! isset($object->$match)) 
					continue; 
					
				$template = str_replace($replace, $object->$match, $template); 
			}
		}	
		return $template;	
	}
	
	public static function checkif($template, $object) 
	{
		$count = preg_match_all('/{if:(.*?)}(.*){\/if:(\1)}/i', $template, $matches);
		
		if (! isset($matches[0]) || $count == 0) 
			return $template; // no statements; 
		
		/* matches[0] = full statement 
		   matches[1] = name of field + possible value
		   matches[2] = inner content 
		*/

		for($i = 0; $i < $count; $i++) 
		{
			
			$full = $matches[0][$i];
			$field = $matches[1][$i]; 
			$value = null;

			if (strstr($field, '=')) // check for value construct
			{
				list($field, $value) = explode('=', $field);
			} 
 
		
			$content = $matches[2][$i]; 

			if (isset($object->$field) && ($value == null || $object->$field == $value) ) 		
			{ // match
				$template = str_replace($full, $content, $template); 
			}
			else
			{ // no match
				$template = str_replace($full, '', $template); 
			}	 
		}
		return $template; 
	}
	
	public static function checkfor($template, $object) 
	{
		$count = preg_match_all('/\{for:(.*)\}(.*)\{\/for:(.*)\}/isU', $template, $matches);

		if (! isset($matches[0]) || $count == 0) 
			return $template; // no statements; 	
	
		for ($i = 0; $i < $count; $i++)
		{
			$content = ''; 
			$field = $matches[1][$i]; 
			$repeatline = $matches[2][$i]; 
 
			if (isset($object->$field))
			{
				foreach($object->$field as $key => $item)
				{
					if (is_array($item))
					{
 
						$line = $repeatline; 
						
						foreach($item as $subkey => $subitem)
						{
							$line = str_replace('%%' . $subkey . '%%',$subitem, $line);
							$line = static::checkif($line, $object);
						}
						$line = str_replace('%%key%%', $key, $line);												
						$content .= $line;
					}
					else
					{	
						$line = str_replace('%%key%%',$key, $repeatline);
 
						$line = static::checkif($line, $object); // check if statements in the loop.  
						$content .= str_replace('%%item%%', $item, $line);
					}
				}
				
			}
				$template = str_replace($matches[0][$i], $content, $template);
			
		}

		return $template;
	}
	

}
