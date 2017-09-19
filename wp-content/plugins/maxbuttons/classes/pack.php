<?php 
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

// basic pack reading functionality for social buttons
class maxPack
{
	protected $default_img_url = ''; 

	protected $pack_path = ''; // path of the pack with trailing slash
	protected $pack_dir = '';  // the name of the packs directory without path. 
	protected $pack_xml = '';  
	protected $pack_url = ''; 
	
	
	protected $img = ''; 
	protected $name = ''; 
	protected $author = ''; 
	protected $author_url = ''; 
	protected $description = ''; 
	protected $is_local = true; 


	public function setPackPath($path)
	{
		$this->pack_path = $path;
	}

	public function getName() 
	{
		return $this->name;
	}
	
	public function getAuthor()
	{
		return $this->author;
	}
	
	public function getDescription()
	{
		return $this->description;
	}
	
	
	public function load_pack($packdata)
	{
		$pack_file = $packdata["file"]; 
		$pack_img = $packdata["img"]; 
		$this->pack_dir = $packdata["dir"];

		$xml = simplexml_load_file($pack_file,null,  LIBXML_NOCDATA);	
		
		if (! $xml) 
		{
			MB()->add_notice('error', __( sprintf('Pack file could not be loaded', $pack_file), 'maxbuttons'));
			return false;
		}
		$this->pack_xml = $xml; 
		
		$pack = $xml->pack[0];
		$packset = current($pack->attributes());
		$packset["image"] = $pack_img;
		$packset["is_local"] = $packdata["is_local"];

		$this->set_pack($packset);		
		return true; // success
	}
	
	/* Return the full XML of the pack */ 
	public function getPackXML()
	{
		return $this->pack_xml;
	}	

	/* Set all the pack attributes */ 
	function set_pack($pack)
	{
		$this->img = (isset($pack["image"])) ? $pack["image"] : ''; 
		$this->name = (isset($pack["name"])) ? $pack["name"] : ''; 
		$this->author = (isset($pack["author"])) ? $pack["author"] : ''; 
		$this->authorurl = (isset($pack["author_url"])) ? $pack["author_url"] : ''; 
		$this->description = (isset($pack["description"])) ? $pack["description"] : ''; 
		$this->is_local = $pack["is_local"];
		if (isset($pack["pack_dir"])) 
			$this->pack_dir = $pack["pack_dir"]; 
		if (isset($pack["pack_url"])) 
			$this->pack_url = $pack["pack_url"]; 
			
	}


	/* Parses old format and current format pack XML into a button_array which button setupdata understands */
	public function parse_pack_button($xmlbutton)
	{
		$button = MB()->getClass("button"); 
		if ( count($xmlbutton->attributes()) > 0)  // old button
				{

					$attrs = current($xmlbutton->attributes()); 
			
					$data = MB()->getClass("install")->convertOldFields($attrs); 

					$button_array = array();
					$button_array["status"] = $data["status"]; 

					$data = $button->save($data,false); // convert from post var to block struct without dbase save


					foreach($data as $block => $values) 
					{ 
						if (is_array($values)) 
							$button_array[$block] = json_encode($values);
						else
							$button_array[$block] = $values; 						 
					}
				 
				}
				else // new button
				{
					//$button = MB()->getClass("button"); 

					$button_array = json_decode(json_encode( (array)$xmlbutton), TRUE);
					
				//	$data = $button->save($button_array, false);
				}
				$temp_id = floor(rand(100000,990000));
				$button_array["id"] =  $temp_id; // fingers crossed
			
				// for some reason on json_decode when value is empty it created an empty array instead of string.
				foreach($button_array as $name => $values)
				{
					if (is_array($values) && count($values) == 0) 
						$button_array[$name] = ''; 
				}


				// icons from pack
				$icon_array = array(); 
				
				if (isset($button_array["icon"])) 
				{	
					$icon_array = maybe_unserialize($button_array["icon"]); 
					if (! is_array($icon_array))  // moving to json_encode
					$icon_array = json_decode($button_array["icon"], true);  

				}
				if (isset($icon_array["icon_url"]) && $icon_array["icon_url"] != '') {
					$path =  str_replace(array('http:','https:'), '', $this->pack_path); // allow for SSL / HTTP  
					$icon_array["icon_url"] =  $path . $icon_array["icon_url"];  // pack path to allow for remote images
					$button_array["icon"] = json_encode($icon_array);
				}

		return $button_array;	
	}



} // class

