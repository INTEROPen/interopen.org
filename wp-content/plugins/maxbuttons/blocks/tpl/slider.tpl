	  {if:label}	<label for='%%id%%' class='range_label %%name%%'>%%label%%</label> {/if:label}				
<div class='input slider %%name%%'> 


	{if:min_label}<span class='input_label left'>%%min_label%%</span>{/if:min_label} <input type='range' min='%%min%%' max='%%max%%' name='%%name%%' value='%%value%%' step='1' > 
	{if:max_label}<span class='input_label right'>%%max_label%%</span>{/if:max_label}
	
	<p  class='range_value'><output for='%%id%%'>-</output></p>
		
</div>
