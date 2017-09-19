<div class='input checkbox %%name%%'> 	
		<input type='checkbox' name='%%name%%' id='%%id%%' data-field='%%name%%' 
			{if:inputclass}class="%%inputclass%%"{/if:inputclass} 
			value='%%value%%' 
			%%checked%% 
			{if:icon} tabindex='-1'{/if:icon} 
		/>
		<label for='%%id%%' {if:title}title="%%title%%"{/if:title} >

		{if:icon}	<i class='dashicons %%icon%%' tabindex='0'></i>	{/if:icon}
		{if:label}	%%label%% {/if:label}	

		</label>
</div>
