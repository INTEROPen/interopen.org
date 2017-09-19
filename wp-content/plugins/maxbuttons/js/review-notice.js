jQuery(document).ready(function($) {	

$('.maxbuttons-notice [data-action], .maxbuttons-reviewoffer [data-action]').on('click', mb_review_init_ajax); 

function mb_review_init_ajax (e)
{
	e.preventDefault; 
	
	var new_status = $(e.target).data('action');  
	mb_review_ajax(new_status);
	
	

}

function mb_review_ajax(new_status)
{
	var url = mb_ajax_review.ajaxurl; 
	var data = { action: 'set_review_notice_status', 
				 status : new_status
			 }; 
			 
	$.ajax({
	  method: "POST", 
	  url: url,
	  data: data,
	  success: function (res) 
	  {
		 mb_review_done();
 	  }, 
 	  
	});

}

function mb_review_done()
{
	$('.maxbuttons-notice, .maxbuttons-reviewoffer').fadeOut(); 

}


}); /* END OF JQUERY */
