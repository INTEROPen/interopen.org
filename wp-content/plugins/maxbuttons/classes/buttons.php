<?php 
namespace MaxButtons;
/**  Buttons class - handles paging issues and sanity check for individual buttons 
*/
class maxButtons 
{
	protected static $loadedButtons = array(); // loaded button in current scope  
	/* 
	
	array [ index ] [ button_id ] [data - document_id, done (bool )
	*/
	protected static $documentArray = array();  // given out document ID's  
	
	// override to give out next document id. This is useful when buttons are set but not displayed directly. 
	protected static $doNext = false;
	protected static $current_doc_id = null; 
	protected static $current_button_id = null; 
	
	protected static $instance = null; 
	
	
	public static function getInstance()
	{
		if (is_null(self::$instance)) 
			self::$instance = new maxButtons(); 
		
		return self::$instance; 	
	}
 
	static function buttonLoad($args)
	{

		$button_id = $args["button_id"]; 
		self::$loadedButtons[] = $button_id; 
		$document_id = self::getDocumentID(array("button_id" => $button_id)); 
		self::$documentArray[] = array($button_id => array('document_id' => $document_id , 'done' => false));	

	}
	
	static function forceNextID() 
	{
		self::$doNext = true;
		self::$current_doc_id = null;
		self::$current_button_id = null; 
	}
	
	static function getDocumentID($args)
	{
		$button_id = $args["button_id"]; 
 	
		if (! is_null(self::$current_doc_id) && self::$current_button_id == $button_id ) 
			return self::$current_doc_id;
 
		
		if (self::$doNext == false)
		{
			foreach(self::$documentArray as $index => $ids) 
			{
				foreach($ids as $doc_button_id => $doc_vars)
				{
					if ($doc_button_id == $button_id) 
					{	
						if (! $doc_vars["done"]) 	
							return $doc_vars["document_id"]; 
					}
			
				}
			
			}
		}	
		// if not found in documentarray make a new one 
		$loaded = self::$loadedButtons; 
		end($loaded); 
		$i = 0; 
		 
		foreach($loaded as $btn_id) 
		{
			if ($btn_id == $button_id)
				$i++; 
		}
		$i--; // minus the current added button..
		
		//$index = key($loaded); // find last index
		if ($i == 0) 
			$document_id = $button_id; 
		else
			$document_id = $button_id . "_" . $i; 
 
		self::$doNext = false;
		self::$current_doc_id = $document_id;
		self::$current_button_id = $button_id; 
		return $document_id; 
	
	}

	static function buttonDone($args)
	{
		$button_id = $args["button_id"]; 
		$document_id = $args["document_id"]; 
 
		foreach(self::$documentArray as $index => $data)
		{
			foreach($data as $doc_button_id => $doc_data)
			{
				if ($doc_button_id == $button_id && $doc_data["document_id"] == $document_id)
				{
					self::$documentArray[$index][$button_id]["done"] = true;
				}
				
			}
		
		}
		
	}
	
	public static function ajax_action() 
	{
		$action = sanitize_text_field($_POST['button_action']); 
		$check = wp_verify_nonce($_POST['nonce'], 'button-' . $action);
		$button_id = intval($_POST["button_id"]);  
		$button = new maxButton();
									
		if  (! $check) 
			exit('Invalid Nonce'); 
		
		switch($action)
		{
			case "delete": 
				$button->delete($button_id); 
				$redirect_url = admin_url() . 'admin.php?page=maxbuttons-controller&action=list&message=1delete'; 
			break;
			
			case "trash": 
				$result = $button->set($button_id);	
			 	if ($result)
					$button->setStatus('trash'); 	
				$redirect_url = admin_url() . 'admin.php?page=maxbuttons-controller&action=list&message=1'; 		
			break; 
			case "restore": 
				$set = $button->set($button_id,'','trash');
				$button->setStatus("publish"); 
				$redirect_url = admin_url() . 'admin.php?page=maxbuttons-controller&action=list&status=trash&message=1restore';
			break;
			case "copy": 
				$button->set($button_id); 
				$new_id = $button->copy();		
				$redirect_url = admin_url() . 'admin.php?page=maxbuttons-controller&action=button&id=' . $new_id;
			break;
		
		}
		
		if (isset($redirect_url)) {
			$response = array('redirection' => $redirect_url); 
			echo json_encode($response);
			exit();
		}
		
		exit(true);
	}

} // class
