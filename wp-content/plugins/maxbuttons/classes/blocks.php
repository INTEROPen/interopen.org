<?php 
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

/** Blocks collection
*
* Class for general block functions - transitional
*/
use \RecursiveDirectoryIterator as RecursiveDirectoryIterator;
use \RecursiveIteratorIterator as RecursiveIteratorIterator;
use \FilesystemIterator as FilesystemIterator; 

class maxBlocks
{
	protected static $blocks;  // collection!
	protected static $block_classes; 
	
	protected static $data; // full data array
	protected static $fields = array(); // all fields 
	
	public static function init() 
	{

	}
	
	/** Find the block classes */ 
	public static function initBlocks() 
	{
		
		$block_paths = apply_filters('mb-block-paths',  array(MB()->get_plugin_path() . "blocks/") );
		 
		//global $blockClass; // load requires only onc

		$newBlocks = array();
		$templates = array(); 
		
		
		foreach($block_paths as $block_path)
		{
			$dir_iterator = new RecursiveDirectoryIterator($block_path, FilesystemIterator::SKIP_DOTS);
			$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

			foreach ($iterator as $fileinfo)
			{

				$path = $fileinfo->getRealPath(); 
				// THIS IS PHP > 5.3.6
				//$extension = $fileinfo->getExtension(); 
				$extension = pathinfo($path, PATHINFO_EXTENSION);
			
				if ($fileinfo->isFile() )
				{
					if ($extension == 'php') 
					{
					 	require_once($path);
					}
					elseif($extension == 'tpl') 
					{	
						$filename = $fileinfo->getBasename('.tpl');
						$templates[$filename] = array('path' => $path); 
					}
				}
			}

		}
			ksort($blockOrder);
			foreach($blockOrder as $prio => $blockArray)
			{
				foreach($blockArray as $block)
				{
					if (isset($blockClass[$block]))
						$newBlocks[$block] = $blockClass[$block]; 
			
				}
			}
			$blockClass = $newBlocks;
			if (is_admin())
			{
				// possible issue with some hosters faking is_admin flag. 
				if (class_exists( maxUtils::namespaceit('maxBlocks') ) && class_exists( maxUtils::namespaceit('maxBlocks') ) )
				{
					maxField::setTemplates($templates); 

				}
				else
				{
					error_log('[MaxButtons] - MaxField class is not set within admin context. This can cause issues when using button editor'); 
				}
			}
		
		//$this->loadBlockClasses($blockClass); 
		
		static::$block_classes = array_values($blockClass);
	}
	
	public static function getBlockClasses() 
	{
		if ( is_null(static::$block_classes) )
			self::initBlocks();
		
		 return static::$block_classes;
	}
	
	
	public static function setData($data) 
	{
		$new_data = array(); //egalite 
		if (! is_array($data) || count($data) == 0) // no data 
			return false; 
			
		foreach($data as $block => $fields) 
		{
			if (is_array($fields)) 
				$new_data = array_merge($new_data, $fields); 
		}
		

		self::$data = $new_data; 
	}
	
	public static function add($block)
	{
		$name = $block->get_name(); 

		static::$blocks[$name] = $block;
		static::$fields = array_merge(self::$fields, $block->get_fields()); 
	}
	
	public static function getValue($fieldname) 
	{

		if (isset(self::$data[$fieldname])) 
			return self::$data[$fieldname]; 
		if (isset(self::$fields[$fieldname])) 
			return self::$fields[$fieldname]['default'];
		
		return false; // dunno. 
	}

	public static function getColorValue($fieldname) 
	{
		$value = self::getValue($fieldname); 
		if (! $value ) 
			return false;
			
		if (substr($value,0,1) !== '#') 
		{
			$value = '#' . $value;
		}
		
		return $value;
	
	}

	public static function getDefault($fieldname) 
	{
		if (isset(self::$fields[$fieldname]['default'])) 
			return self::$fields[$fieldname]['default'];
		
		return false; // dunno
	
	}


}
