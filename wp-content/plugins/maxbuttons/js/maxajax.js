
/** New AJAX Call methods
/* Get the standard AJAX vars for this plugin */ 

var maxAjax = function(jquery) {
	$ = jquery;

}

maxAjax.prototype.init = function() 
{

	// default actions that trigger ajax action.
	$(document).on('click', '.mb-ajax-form .mb-ajax-submit', $.proxy(this.ajaxForm, this ));
	$(document).on('click', '.mb-ajax-action', $.proxy(this.ajaxCall, this )); 
	$(document).on('change', '.mb-ajax-action-change', $.proxy(this.ajaxCall, this));
	$(document).trigger('maxajax_init'); // for hanging in other actions.
}

maxAjax.prototype.ajaxInit = function() 
{
	data = { 
		action: maxajax.ajax_action,
		nonce:  maxajax.nonce,	
	}
	
	return data;
}

maxAjax.prototype.ajaxForm = function (e)
{
	var target = $(e.target); 
	var form = $(target).parents('form'); 
	var action = $(target).data('action'); 
	
	var data = this.ajaxInit(); 
	data['form'] = form.serialize(); 
	data['plugin_action'] = action;
//	data['action'] = 'mb_button_action'; 
	
	this.showSpinner(target); 
	
	this.ajaxPost(data); 
	 
	
}

/* Ajax call functionality */
maxAjax.prototype.ajaxCall = function (e) 
{

	e.preventDefault(); 
	var target = e.target; 

	var param = false; 	
	var plugin_action = $(target).data('action'); 
	var check_param = $(target).data('param'); 
	var param_input = $(target).data('param-input'); 
	
	if (typeof check_param !== 'undefined') 
		param = check_param;
	if (typeof param_input !== 'undefined') 
		param = $(param_input).val(); 
	
	data = this.ajaxInit();

	data['plugin_action'] = plugin_action;
	data['param'] = param;
	data['post'] = $('form').serialize(); // send it all

	this.showSpinner(target); 

	this.ajaxPost(data);
}

maxAjax.prototype.showSpinner = function(target)
{	
	var spinner = '<div class="ajax-load-spinner"></div>';
	$('.ajax-load-spinner').remove(); 
	$(target).after(spinner);
	//return spinner;
}

maxAjax.prototype.ajaxPost = function(data, successHandler, errorHandler)
{
	var self = this;
	
	if (typeof successHandler == 'undefined') 
	{
		var action = data['plugin_action']; 
		var successHandler = function (r,s,o,) { self.defaultSuccessHandler(r,s,o,action) } ;  
			
	}
	
	if (typeof errorHandler == 'undefined') 
	{
		var action = data['plugin_action']; 
		var errorHandler = function (r,s,o,) { self.defaultErrorHandler(r,s,o,action) } ;
	}	


	$.ajax({
		type: "POST", 
		url: maxajax.ajax_url,
		data: data,
		success: successHandler,
		error: errorHandler,
		}); 		
}

maxAjax.prototype.defaultSuccessHandler = function (result, status, object, action) 
{
		$(document).trigger('maxajax_success_' + action, [result, status, object]);

}

maxAjax.prototype.defaultErrorHandler = function(jq,status,error, action) 
{
			$(document).trigger('maxajax_error_' + action, jq, status, error); 
			console.log(jq);
			console.log(status);
			console.log(error);	
}


jQuery(document).ready(function($) {

	if (typeof window.maxFoundry === 'undefined') 
		window.maxFoundry = {} ; 
	
	window.maxFoundry.maxAjax = new maxAjax($);

	window.maxFoundry.maxAjax.init();

}); /* END OF JQUERY */

