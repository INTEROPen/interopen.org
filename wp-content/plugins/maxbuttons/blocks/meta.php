<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

$blockClass["meta"] = "metaBlock"; 
$blockOrder[100][] = "meta"; 

class metaBlock extends maxBlock 
{
	protected $blockname = "meta"; 
	protected $fields = array("created" => array("default" => 0), 
							  "modified" => array("default" => 0), 
							  "user_created" => array("default" => ''), // user logged in on creation
							  "user_modified" => array("default" => ''), 
							  "created_source" => array("default" => 'unknown'),  // from editor / pack / collection ? 
					 		  "user_edited" => array("default" => false), // did a user ever edit this button? 
					 		  "in_collections" => array("default" => array() ), // map collections this button is in. 
					 		  "is_virtual" => array("default" => false), // this button is not really in the database
			);		 		   
							  
	

	/*function __construct()
	{
		parent::__construct();
 
	} */
	
	public function save_fields($data, $post) 
	{
		$data = parent::save_fields($data,$post);
		
		$blockdata = $data[$this->blockname]; 
		$button_id = isset($data["id"]) ? $data["id"] : 0; 
		$user = wp_get_current_user(); 
		
		
		if ($button_id == 0) 
		{
			$blockdata["created"] = time();
			$blockdata["user_created"] = $user->user_login; 
		}
 
			$blockdata["modified"] = time(); 
			$blockdata["user_modified"] = $user->user_login;
			

		$data[$this->blockname] =  $blockdata; 
 
		return $data; 
	}

 
	public function admin_fields() 
	{
		//return false; 
	$data = (isset($this->data[$this->blockname]) && is_array($this->data[$this->blockname]))  ? $this->data[$this->blockname] : array(); 
	
		foreach($this->fields as $field => $options)
		{		
 	 	    $default = (isset($options["default"])) ? $options["default"] : ''; 
			$$field = (isset($data[$field])) ? $data[$field] : $default;
			${$field  . "_default"} = $default; 
			
		}

 
		if(! isset($data["id"]) || $data["id"] == 0) 
			$created_source = 'editor'; // button born at the editor
 
	
	?>
		<input type="hidden" name="created" value="<?php echo $created ?>"> 
		<input type="hidden" name="user_created" value="<?php echo $user_created ?>"> 
				
		<input type='hidden' name='created_source' value="<?php echo $created_source ?>"> 
		<input type='hidden' name='user_edited' value='true'>
 
		<?php if (is_array($in_collections)) {
			foreach ($in_collections as $collection_id) 
			{
				?>
				<input type="hidden" name="in_collections[]" value="<?php echo $collection_id ?>"> 
				<?php
			
			}
		
		
		} ?>
		<input type="hidden" name="is_virtual" value="<?php echo $is_virtual; ?>"> 



		
	<?php
		if (defined("MAXBUTTONS_DEBUG") && MAXBUTTONS_DEBUG):

?>
		<div class="option-container mb_tab">
				<div class="title"><?php _e('Meta', 'maxbuttons') ?></div>
				<div class="inside">				
						<?php foreach($data as $key => $val) { 
							if (! is_array($val))
							{
								$try_json = json_decode($val);
								if (! is_null($try_json)) 
								$val = $try_json;
							}
							echo "<div class='option'> <label>$key</label>"; 
							echo "<div>" . print_r($val,true) . "&nbsp;</div></div>"; 
						}	
						?>

					
				</div>
		</div>
	<?php	
		endif; 
	 }  // admin_display
		 
 } // class 
 
 ?>
