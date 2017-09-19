	{if:label}<label>%%label%%</label>{/if:label}
<div class='input  %%name%%' {if:conditional}data-show="%%conditional%%"{/if:conditional}> 	
	<button id='%%id%%' type='button' class='button {if:inputclass}%%inputclass%%{/if:inputclass}' 
	{if:modal}data-modal='%%modal%%'{/if:modal} > %%button_label%% </button>
	
</div>
