var maxMedia; 

jQuery(document).ready(function($) {

/* Add button for the post editor screen + integrations */ 
var maxMedia = function() {

	this.callback = null; // callback function when clicking 'insert button'
	this.parent = '#poststuff'; // option parent flag as location to write button window. 
	this.window_loaded = null; 
	this.maxm = null;
	this.closeOnCallback = true; 
}

/* Default events and callback */
maxMedia.prototype.init = function() 
{
 
	this.maxm = new maxModal();
	this.maxm.init(); 
	
	$(document).on('click','.maxbutton_media_button',$.proxy(this.clickAddButton, this));
	this.callback = 'this.buttonToEditor'; // default 
}

/* Set the callback after selecting a button in the window */
maxMedia.prototype.setCallback = function (callback)
{ 
 	
	if (typeof callback !== 'function') 
	{
		if (typeof window[callback] === 'function')
			callback = window[callback];
		else if (typeof eval(callback) === 'function') 
		{
			callback = eval(callback); 
		} 
		else
			return false; 
	}

	this.callback = callback;
}

maxMedia.prototype.showShortcodeOptions = function(button_id, target)
{
	this.closeOnCallback = false; 
	
	$currentModal = this.maxm.currentModal; 

	var button = $('[data-button="' + button_id + '"]').find('.shortcode-container'); 

	options = $('<div class="shortcode_options">');


	$('<input>', 
	{
		'type' : 'hidden',
		'id'   : 'mb_shortcode_id', 
		'name' : 'button_id', 
	}).val(button_id).appendTo(options);

	$('<h3>').text('Shortcode Options').appendTo(options);
	
	$('<div class="button_example">').append(button).appendTo(options);
	
	$('<label>', { 
		'for' : 'mb_shortcode_url',	
	}).text(mbtrans.short_url_label).appendTo(options);
	
	
	$('<input>', { 
			'type' : 'text',
			'id'   : 'mb_shortcode_url', 
			'name' : 'shortcode_url', 
			'placeholder' : 'http://',
	}).on('change, keyup', 
		function (e) {
			var url = $(e.target).val(); 
			$('.button_example').find('.maxbutton').prop('href', url); 	
	}).appendTo(options);
	

	$('<label>', { 
		'for' : 'mb_shortcode_text',	
	}).text(mbtrans.short_text_label).appendTo(options);

	
	$('<input>', { 
			'type' : 'text', 
			'name' : 'shortcode_text', 
			'id'   : 'mb_shortcode_text',
	}).on('change, keyup', 
		function (e) {
			var text = $(e.target).val(); 
			$('.button_example').find('.mb-text').text(text); 	
	}).appendTo(options); 

	$('<p>').text(mbtrans.short_options_explain).appendTo(options); 
	
	$('<input>', { 
		'type' : 'button', 
		'name' : 'add_shortcode',
		'class' : 'button-primary',
		'value' : mbtrans.short_add_button, 

	}).on('click', $.proxy(this.addShortcodeOptions, this)).appendTo(options); 
	
	

	this.maxm.setContent( options );
	this.maxm.checkResize(); 

}

maxMedia.prototype.addShortcodeOptions = function(e)
{
	e.preventDefault(); 

	var url = $('#mb_shortcode_url').val(); 
	var text = $('#mb_shortcode_text').val(); 
	var button_id = $('#mb_shortcode_id').val(); 
	
	this.buttonToEditor(button_id, url, text); 
	
	
}

maxMedia.prototype.clickAddButton = function (e) 
{	
	e.preventDefault();
	e.stopPropagation(); 
	$(document).off('click','.pagination span'); // prevent multiple events 
	var self = this; 
	
	if (typeof $(e.target).data('callback') !== 'undefined') 
	{
		this.setCallback($(e.target).data('callback')); 
	}
	
	if (typeof $(e.target).data('parent') !== 'undefined') 
	{
		this.parent = $(e.target).data('parent'); 
	}	

	$(document).on('click', '.button-row', $.proxy(function (e)
	{
		var target = $(e.target); 
 
		if ( typeof $(target).data('button') === 'undefined')
		{
			target = $(target).parents('.button-row');
		}
	
		var button = $(target).data('button'); 
		$('.button-row').removeClass('selected'); 
		$(target).addClass('selected'); 
		$('.controls .insert').data('button', button); 
		this.maxm.currentModal.find('.controls .insert').removeClass('disabled'); 
	},this)); 
		
	$(document).on('click','.pagination span, .pagination-links a', function (e)  // eventception
	{
		e.preventDefault();
		if ( $(e.target).hasClass('disabled'))
			return false;
			
		var page = $(e.target).data('page');
		if (page <= 1) page = 1; 
		
		self.loadPostEditScreen(page); 
	});
	$(document).on('change', '.input-paging', function (e)
	{
		e.preventDefault(); 
		var page = parseInt($(e.target).val()); 
		self.loadPostEditScreen(page); 
	});  
	
	this.loadPostEditScreen(0);
}


// Callback is the add function on button select
maxMedia.prototype.loadPostEditScreen = function(page)
{
	if (typeof page == 'undefined') 
		page = 0; 
	
	var data = { action: 'getAjaxButtons', 
				paged : page, 
			 	//callback: callback,
			 }; 
	var url = mbtrans.ajax_url;
 	var self = this; 
 
 	// show load spinner if any 
 	$('.media-buttons .loading').css('visibility', 'visible'); 
 	
	$.ajax({
	  url: url,
	  data: data,
	  success: function (res) 
	  {
	  	self.putResults(res)
 	  }, 
 	  
	});

	return false;
}
maxMedia.prototype.showPostEditScreen = function ()
{

	this.maxm.parent = this.parent; 
	this.maxm.newModal('media-buttons'); 
 
	this.maxm.setTitle(mbtrans.windowtitle); 

	$(document).trigger('mb_media_buttons_open', this.maxm); 

 
 	this.maxm.show();
 	this.window_loaded = true;
 
}

maxMedia.prototype.putResults = function(res)
{

	this.showPostEditScreen();
	 $('.media-buttons .loading').css('visibility', 'hidden'); 
	
	this.maxm.addControl('insert', '', $.proxy(this.insertAction, this) );  
	this.maxm.setContent(res);
	this.maxm.setControls();
	this.maxm.checkResize(); 

	this.resize(); 
	
	// this feature resizes
	$(window).on('resize', $.proxy(this.resize, this)); 
	
	// events 
	$(document).on('click', ".maxbutton-preview", function(e) { e.preventDefault(); }); // prevent button clicks

	$(document).trigger('mb_media_put_results', [res, this.maxm] ); 
}

/** Contains the -inner-  window to force scrollbar if inner part is bigger. **/
maxMedia.prototype.resize = function(e)
{
	 //contentHeight = this.maxm.currentModal.find('.modal_content').height(); 
	 
	 if (this.maxm.currentModal === null) 
	 	return; // nothing to resize 
	 
	 topHeight = this.maxm.currentModal.find('.modal_header').height() + 17; // padding
	 controlsHeight = this.maxm.currentModal.find('.controls').height() + 21; 
	 modalHeight = this.maxm.currentModal.height(); 
	 
	 this.maxm.currentModal.find('.modal_content').css('height', modalHeight - topHeight - controlsHeight)
	 this.maxm.currentModal.find('.controls .insert').addClass('disabled'); 
	 
}
 
maxMedia.prototype.insertAction = function(e) 
{
		e.preventDefault(); 
 		var button_id = $(e.target).data('button');
 		if (typeof button_id === 'undefined' || parseInt(button_id) <= 0)
 			return; // no button yet. 
		
		if (typeof this.callback == 'function')
			this.callback(button_id, $(e.target) ); 
			
		if (this.closeOnCallback)
		{
			this.maxm.close();
			$(document).trigger('mb_media_buttons_close');
		}
}

maxMedia.prototype.buttonToEditor = function(button_id, url, text)
{
	var shortcode = '[maxbutton id="' + button_id + '"'; 
	
	if (typeof url !== 'undefined' && url.length > 1)
		shortcode += ' url="' + url + '"'; 
	
	if (typeof text !== 'undefined' && text.length > 1)
		shortcode += ' text="' + text + '"';

	shortcode += ' ] '; 
	window.send_to_editor(shortcode);
	this.maxm.close();
}

maxMedia.prototype.getEditor = function () 
{

	var h2style = 'line-height: 32px; padding-left: 40px; background: url("' + mbtrans.icon  + '") no-repeat';
	var cancelstyle = 'margin-left: 10px; margin-top: 10px;'; 
	var editor = $('<div>', { id: 'maxbutton-add-button', class: 'content' }); 

		//.append( $('<a>', { 'class' : 'button-secondary', 'style' : cancelstyle }).text(mbtrans.cancel)	
	editor.append( $('<h2>', { 'style' : h2style } ).text(mbtrans.insert) )
		.append( $('<p>').text(mbtrans.select) ) 
		.append( $('<div>', { id: 'mb_media_buttons' }).append( '<div class="loading"></div>'  )

			   );

	return editor;

}

maxMedia = new maxMedia();
maxMedia.init();
window.maxMedia = maxMedia;



}); // jquery 
