jQuery(document).ready(function($) {	
	
	function runMaxInit()
	{
		if (typeof window.maxFoundry === 'undefined')
			window.maxFoundry = {};

		window.maxFoundry.maxadmin = new maxAdmin(); 
	 	window.maxFoundry.maxadmin.init(); 

		window.maxFoundry.maxmodal = new maxModal(); 
		window.maxFoundry.maxmodal.init(); 
		
		window.maxFoundry.maxcollection = new maxCollection(); 
		window.maxFoundry.maxcollection.init();  
	} 	

	runMaxInit(); 
	
}); /* END OF JQUERY */
