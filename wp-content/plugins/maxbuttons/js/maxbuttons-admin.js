 
var maxAdmin;


jQuery(document).ready(function($) {
 
maxAdmin = function ()
{
 //	$ = jquery;
 	return this;
}
maxAdmin.prototype = {
	//initialized: false,
 	colorUpdateTime: true,
 	fields: null,
 	button_id: null, 
 	form_updated: false,
 	tabs: null,
 
 	
}; // MaxAdmin

maxAdmin.prototype.init = function () {
		this.button_id = $('input[name="button_id"]').val(); 
				
 		// Prevents the output button from being clickable (also in admin list view )	
		$(document).on('click', ".maxbutton-preview", function(e) { e.preventDefault(); });		
		$(document).on('click', '.output .preview-toggle', $.proxy(this.toggle_preview, this)); 

 		// overview input paging
 		$('#maxbuttons .input-paging').on('change', $.proxy(this.do_paging, this));
	
		$('.manual-toggle').on('click', $.proxy(this.toggleManual, this)); 
		$('.manual-entry').draggable({ 
			cancel: 'p, li',
		}); 

 		$(document).on('submit', 'form.mb_ajax_save', $.proxy(this.formAjaxSave, this)); 

		// copy / delete / trash action buttons via ajax 		
		$(document).on('click', '[data-buttonaction]', $.proxy(this.button_action, this )); 
		
		// conditionals 
		$(document).on('reInitConditionals', $.proxy(this.initConditionials, this));
		this.initConditionials(); // conditional options
		
		// range inputs 
		$(document).on('change, input', 'input[type="range"]', $.proxy(this.updateRange, this )); 
		this.updateRange(null); 
	
		/*
		****
		 ### After this only init for button main edit screen 
		****

		*/
		if ($('#new-button-form').length == 0) 
			return; 

					
		if (this.button_id > 0) {
			$("#maxbuttons .mb-message").show();
		} 
		
		this.initResponsive(); // responsive edit interface 
		
		 $("#maxbuttons .output").draggable({	
			cancel: '.nodrag',
		});  
		
		$('#maxbuttons .color-field').wpColorPicker(
			{	
				width: 300,

				change: $.proxy( _.throttle(function(event, ui) {
						event.preventDefault();
						var color = ui.color.toString();
						this.update_color(event,ui, color);  
				}, 200), this), 
				
			}
		);
		/* Copy Color Interface */ 
		$('.input.color .arrows').on('click', $.proxy(this.copyColor, this) ); 
		
		$('#radius_toggle').on('click', $.proxy(this.toggleRadiusLock,this)); 
		
		if ( typeof buttonFieldMap != 'undefined')
			this.fields = $.parseJSON(buttonFieldMap);
		
 		// bind to all inputs, except for color-field or items with different handler. 
 		$('input').not('.color-field').on('keyup change', $.proxy(this.update_preview,this)); 
 		$('input.color-field').on('focus', $.proxy(this.select_field, this)); 
 		
 		$('select').on('change', $.proxy(this.update_preview, this)); 

		$(window).on('beforeunload', $.proxy(function () { if (this.form_updated) return maxcol_wp.leave_page; }, this));
		$(document).on('keyup', 'input', function (e) {

			if (e.keyCode && e.keyCode == 13) 
			{
				$(":input")[$(":input").index(document.activeElement) + 1].focus();
				return false;
			}
		});
		
		$(".button-save").click( $.proxy(function() {	
			this.saveIndicator(false); // prevent alert when saving.		
			$("#new-button-form").submit();
			return false;
		}, this) );
		
		// Expand shortcode tabs for more examples. 
		$('.shortcode-expand').on('click', this.toggleShortcode); 

		
}; // INIT

		
maxAdmin.prototype.repaint_preview = function () 
{
	$('.mb_tab input[type="text"]').trigger('change');
	$('.mb_tab input[type="number"]').trigger('change');	 
	$('.mb_tab select').trigger('change'); 
	$('.mb_tab input[type="hidden"]').trigger('change'); 
	$('.mb_tab input[type="radio"]:checked').trigger('change'); 
	$('.mb_tab input[type="checkbox"]:checked').trigger('change'); 
	
	//$(document).trigger('colorUpdate', ['#text_color', $('#text_color').val() ]);
	//$(document).trigger('colorUpdate', ['#text_color_hover', $('#text_color_hover').val() ]);	
	
}
		
maxAdmin.prototype.update_preview = function(e) 
{
	e.preventDefault();
	this.saveIndicator(true); 
	var target = $(e.target); 
	
	// migration to data field			
	var field = $(target).data('field'); 
	if (typeof field == 'undefined')
		var id = $(target).attr('id'); // this should change to be ready for the option to have two the same fields on multi locations.
	else
		var id = field;

	var data = this.fields[id]; 

	if (typeof data == 'undefined') 
		return; // field doesn't have updates 

	if (typeof data.css != 'undefined') 		
	{
		value = target.val(); 

		if (typeof data.css_unit != 'undefined' && value.indexOf(data.css_unit) == -1) 
			value += data.css_unit;
	
		// a target that is checkbox but not checked should unset (empty) value. 
		if (target.is(':checkbox') && ! target.is(':checked') ) 
			value = ''; 

		this.putCSS(data, value);
	}
	if (typeof data.attr != 'undefined') 
	{
		$('.output .result').find('a').attr(data.attr, target.val());
	}
	if (typeof data.func != 'undefined')
	{

		eval('this.'+ data.func + '(target)');
	}
};

maxAdmin.prototype.select_field = function(e)
{
	$(e.target).select(); 
}	

maxAdmin.prototype.button_action = function(e)
{
	e.preventDefault(); 
	var action = $(e.target).data('buttonaction'); 
	
	this.form_updated = false; 

	var button_id = $(e.target).data('buttonid'); 
	var nonce = $('input[name="' + action + '_nonce"]').val(); 
	
	var url = mb_ajax.ajaxurl;
	var data = 
	{
		action: 'mb_button_action',
		button_action: action, 
		button_id: button_id, 
		nonce: nonce, 

	};

	$.post({
		url: url, 
		data: data, 
		success: function (data) {
			response = JSON.parse(data);
			
			if (typeof response.redirection != 'undefined') 
			{
				window.location = response.redirection;  
			}
		}, 
		error: function () {
			console.log('error in button action' + action);
		},
	});
}

/* Check the copy modal and display a warning if the button has been changes */ 
maxAdmin.prototype.checkCopyModal = function(modal)
{
	if (this.form_updated)
	{
		modal.currentModal.find('.mb-message').show();
		
	}	
	else
		$(modal.currentModal).find('.mb-message').hide();
}
		
maxAdmin.prototype.toggle_preview = function (e)
{
	if ( $('.output .inner').is(':hidden') )
	{
		$('.output .inner').show(); 
		$('.output').css('height', 'auto'); 		
		$('.preview .preview-toggle').removeClass('dashicons-arrow-down').addClass('dashicons-arrow-up');		
	}
	else
	{
		$('.output .inner').hide(); 
		$('.output').css('height', 'auto'); 		
		$('.preview .preview-toggle').removeClass('dashicons-arrow-up').addClass('dashicons-arrow-down');
	}
}; 

		
maxAdmin.prototype.putCSS = function(data,value,state) 
{
	state = state || 'both';

	var element = '.maxbutton';  
	if (state == 'hover') 
		element = 'a.hover '; 
	else if(state == 'normal') 
		element = 'a.normal '; 
	 
	if (typeof data.csspart != 'undefined') 
	{
		var parts = data.csspart.split(",");
		for(i=0; i < parts.length; i++)
		{
			var cpart = parts[i]; 
			var fullpart = element + " ." + cpart;
  				$('.output .result').find(fullpart).css(data.css, value); 
		  }
	}
	else
		$('.output .result').find(element).css(data.css, value); 
}

maxAdmin.prototype.update_color = function(event, ui, color)
	{
			event.preventDefault();
			this.saveIndicator(true); 		

			var target = $(event.target);
			//var color = target.val(); 
				
			if (color.indexOf('#') === -1)
				color = '#' + color; 
				
			var id = target.attr('id'); 
			$('#' + id).val(color); // otherwise field value is running 1 click behind.

		
			if(id.indexOf('box_shadow') !== -1)
			{
				this.updateBoxShadow(target); 
			}
			else if(id.indexOf('text_shadow') !== -1)
			{
				this.updateTextShadow(target); 
			}			
			else if (id.indexOf('gradient') !== -1)
			{
				if (id.indexOf('hover') == -1)
					this.updateGradient();
				else
					this.updateGradient(true);			
			}
			else if (id == 'button_preview')
			{
				$(".output .result").css('backgroundColor',  color); 
			}
			else  // simple update
			{

				if (id.indexOf('hover') == -1)
				{	
					state = 'normal';
				}
				else
				{
					state = 'hover'; 
				}
				
				var data = this.fields[id]; 

				this.putCSS(data, color, state);	
				return;
			}
 

		};
		
maxAdmin.prototype.copyColor = function (e)
{
	e.preventDefault();
	e.stopPropagation(); // stop the color picker from closing itself.
	
	var target = $(e.target); 
	var bindto = $(e.target).parents('[data-bind]'); 
	var fieldId = '#' + bindto.data('id'); // Field which is used
	var bindId = '#' + bindto.data('bind'); // Field is bound to.
	
	// check which arrow was pressed
	if (target.hasClass('arrow-right'))
		var arrow_click = 'right'; 
	else
		var arrow_click = 'left';
	
	// check on which side the interface is. If arrows are on right side, it's the left side (...)
	if (bindto.hasClass('right') )
		var if_side = 'left'; 
	else
		var if_side = 'right'; 
	
	/* Decide which color to replace. If interface is left - then right click is copy to other element, but if interface is right, right is overwrite current element. 
		Left : right click - copy, left replace. 
		Right : right click - replace, left copy. 
	*/
	if (if_side == 'left') 
	{
		if (arrow_click == 'right') 
			copy = true;
		else
			copy = false;
	}
	else if (if_side == 'right') 
	{
		if (arrow_click == 'right') 
			copy = false;
		else
			copy = true;
	}

	if ( copy )
	{
		$(bindId).val( $(fieldId).val() ); 
		$(bindId).trigger('change');
		$(bindId).wpColorPicker('color', $(fieldId).val()); 
	}
	else
	{
		$(fieldId).val( $(bindId).val() ); 
		$(fieldId).trigger('change');
		$(fieldId).wpColorPicker('color', $(bindId).val());		
 	}
 	
}
		
maxAdmin.prototype.updateGradient = function(hover)
		{
			hover = hover || false;
			
			var hovtarget = ''; 	
			if (hover)
				hovtarget = "_hover"; 
	
			var stop = parseInt($('#gradient_stop').val()); 

			if (isNaN(stop) )
				stop = 45;
				
			var gradients_on = $('#use_gradient').prop('checked'); 
				 
			var start = this.hexToRgb($('#gradient_start_color' + hovtarget).val());
			var end = this.hexToRgb($('#gradient_end_color' + hovtarget).val());
			var startop = parseInt($('#gradient_start_opacity' + hovtarget).val());
			var endop = parseInt($('#gradient_end_opacity' + hovtarget).val());
 
 			if (! gradients_on)
 			{
 				end = start; 
 				endop = startop; 
 			}
 
 			if(isNaN(startop)) startop = 100; 
 			if(isNaN(endop)) endop = 100;
 			
			if (!hover)
				var button = $('.output .result').find('a.normal'); 			
			else
				var button = $('.output .result').find('a.hover');


 			
					
			button.css("background", "linear-gradient( rgba(" + start + "," + (startop/100) + ") " + stop + "%," + " rgba(" + end + "," + (endop/100) + ") )"); 
			button.css("background", "-moz-linear-gradient( rgba(" + start + "," + (startop/100) + ") " + stop + "%," + " rgba(" + end + "," + (endop/100) + ") )"); 
			button.css("background", "-o-linear-gradient( rgba(" + start + "," + (startop/100) + ") " + stop + "%," + " rgba(" + end + "," + (endop/100) + ") )"); 
			button.css("background", "-webkit-gradient(linear, left top, left bottom, color-stop(" +stop+ "%, rgba(" + start + "," + (startop/100) + ")), color-stop(1, rgba(" + end + "," + (endop/100) + ") ));"); 
			
			 		
		}
		
maxAdmin.prototype.hexToRgb = function(hex) {
 
			hex = hex.replace('#','');
			var bigint = parseInt(hex, 16);
			var r = (bigint >> 16) & 255;
			var g = (bigint >> 8) & 255;
			var b = bigint & 255;
 
			return r + "," + g + "," + b;
		}
		
maxAdmin.prototype.updateBoxShadow = function (target)
		{
			target = target || null;

			var left = $("#box_shadow_offset_left").val();
			var top = $("#box_shadow_offset_top").val();
			var width = $("#box_shadow_width").val();		
			var spread = $('#box_shadow_spread').val();				
			
			var color = $("#box_shadow_color").val();
			var hovcolor = $("#box_shadow_color_hover").val();
			
			$('.output .result').find('a.normal').css("boxShadow",left + 'px ' + top + 'px ' + width + 'px ' + spread + 'px ' + color);	
			$('.output .result').find('a.hover').css("boxShadow",left + 'px ' + top + 'px ' + width + 'px ' + spread + 'px ' + hovcolor);		
		}
		
maxAdmin.prototype.updateTextShadow = function(target,hover)
		{
			hover = hover || false; 

			var left = $("#text_shadow_offset_left").val();
			var top = $("#text_shadow_offset_top").val();
			var width = $("#text_shadow_width").val();				
			
			var color = $("#text_shadow_color").val();
			var hovcolor = $("#text_shadow_color_hover").val();
		
			var id = $(target).attr('id');
			var data = this.fields[id]; 
	
			data.css = 'textShadow'; 
			
			var value = left + 'px ' + top + 'px ' + width + 'px ' + color; 
			this.putCSS(data, value, 'normal'); 
			
			value = left + 'px ' + top + 'px ' + width + 'px ' + hovcolor;
			this.putCSS(data, value, 'hover'); 
			
		}
		
maxAdmin.prototype.updateAnchorText = function (target)
		{
			var preview_text = $('.output .result').find('a .mb-text');

			// This can happen when the text is removed, button is saved, so the preview doesn't load the text element. 
			if (preview_text.length === 0) 
			{	
				$('.output .result').find('a').append('<span class="mb-text"></span>'); 
			$('.output .result').find('a .mb-text').css({'display':'block','line-height':'1em','box-sizing':'border-box'}); 
			
				this.repaint_preview();
			}
			$('.output .result').find('a .mb-text').text(target.val());
		}
		
maxAdmin.prototype.updateGradientOpacity = function(target)
		{
			this.updateGradient(true);
			this.updateGradient(false);
		}

maxAdmin.prototype.updateDimension = function (target)
{
	var dimension = $(target).val(); 
	var id = $(target).attr('id'); 
	var data = this.fields[id]; 
	if (dimension > 0) 	
		this.putCSS(data, dimension);
	else
		this.putCSS(data, 'auto'); 
}

maxAdmin.prototype.updateRadius = function(target)
{
	var value = target.val();
	var fields = ['radius_bottom_left', 'radius_bottom_right', 'radius_top_left', 'radius_top_right']; 

	if ( $('#radius_toggle').data('lock') == 'lock')
	{
		for(i=0; i < fields.length; i++)
		{
			var id = fields[i]; 
			$('#' + id).val(value);
			var data = this.fields[id];
			this.putCSS(data,value + 'px');

		} 
		
	}
}

maxAdmin.prototype.toggleRadiusLock = function (event) 
{
	var target = $(event.target); 
	var lock = $(target).data('lock'); 
	if (lock == 'lock')
	{ 
		$(target).removeClass('dashicons-lock').addClass('dashicons-unlock');
		$(target).data('lock', 'unlock');
	}
	else if (lock == 'unlock') 
	{
		$(target).removeClass('dashicons-unlock').addClass('dashicons-lock');
		$(target).data('lock', 'lock');	
	}
	
}


maxAdmin.prototype.initResponsive = function()
{

	window.maxFoundry.maxadmin.responsive = new mbResponsive($); 
	window.maxFoundry.maxadmin.responsive.init(this); 

}	


maxAdmin.prototype.do_paging = function(e)
{
	var page = parseInt($(e.target).val()); 

	if (page <= parseInt($(e.target).attr('max')) )
	{
		var url = $(e.target).data("url"); 
		window.location = url + "&paged=" + page;

	}
}


maxAdmin.prototype.toggleShortcode = function (e)
{
	if ($('.shortcode-expand').hasClass('closed'))
	{
		$(' .mb-message.shortcode .expanded').css('display','inline-block');
		$('.shortcode-expand span').removeClass('dashicons-arrow-down').addClass('dashicons-arrow-up'); 
		$('.shortcode-expand').removeClass('closed').addClass('open');
	}
	else
	{
		$(' .mb-message.shortcode .expanded').css('display','none');
		$('.shortcode-expand span').addClass('dashicons-arrow-down').removeClass('dashicons-arrow-up'); 
		$('.shortcode-expand').addClass('closed').removeClass('open');	
	}
 
}

maxAdmin.prototype.toggleManual = function (e)
{
	e.preventDefault();
	var $target = $(e.target); 
	 
	var subject = $target.data("target"); 
	var $newWindow = $('.manual-entry[data-manual="' + subject + '"]'); 
 
	if ($newWindow.is(':visible')) 
	{
		$newWindow.hide(); 
		return true;
	}

	var offset = $('[data-options="' + subject + '"]').position() ; 
	// top + height to position under manual link.
	var top = offset.top + $target.height();
	
	$newWindow.css('top', top); 
	$newWindow.css('right',15);
	$newWindow.css('left', 'auto');
 
	$newWindow.show();
}

maxAdmin.prototype.initConditionials = function () 
{
	var mAP = this; 

	
	$('[data-show]').each(function () { 
		var condition  = $(this).data('show');
		var target = condition.target; 
		var values = condition.values; 
		var self = this; 
 
		$(document).on('change','[name="' + target + '"]', {child: this, values: values}, $.proxy(mAP.updateConditional, mAP) );
		$('[name="' + target + '"]').trigger('change'); 

	}); 


}

maxAdmin.prototype.updateConditional = function (event)
{
	var data = event.data; 
 
	var cond_values = data.values; 
	var cond_child = data.child;
	
	var target = $(event.currentTarget); 
	var value = $(target).val(); 

	// if type = checkbox: cond_value checked means it has to be 'checked' to show. Otherwise 'unchecked' go hide. 
	if (target.attr('type') === 'checkbox') 
	{
		
		var checked = $(target).prop('checked'); 

		if (cond_values == 'checked' && checked) 
			value = 'checked'; 
		else if (cond_values == 'unchecked' && !checked)
			value = 'unchecked'; 
		else
			value = 0; 
	
	}	

	if (cond_values.indexOf(value) >= 0)
	{
 
		$(cond_child).fadeIn('fast');
		$(cond_child).find('input, select').trigger('change');
	}
	else
	{
		$(cond_child).fadeOut('fast');
		$(cond_child).find('input, select').trigger('change');
	}

}

maxAdmin.prototype.updateRange = function (event) 
{
	if (typeof event == 'undefined' || event === null ) 
	{
		var targets = $('input[type="range"]'); 
	}
	else 
	{
		var targets = [event.target]; 
	}
	
	$(targets).each(function () { 
		var value = $(this).val(); 
		$(this).parents('.input').find('.range_value output').val(value + '%');
	
	});  
	
}

maxAdmin.prototype.saveIndicator = function(toggle)
{
	if (toggle)
		this.form_updated = true;
	else
		this.form_updated = false;
}

// General AJAX form save
maxAdmin.prototype.formAjaxSave = function (e)
{
	e.preventDefault(); 
	var url = mb_ajax.ajaxurl;
	var form = $(e.target); 

	var data = form.serialize();

	
	$.ajax({
	  type: "POST",
	  url: url,
	  data: data,
 	  
	}).done($.proxy(this.saveDone, this));
}

/*
maxAdmin.prototype.buttonSubmit = function (e)
{	

	e.preventDefault(); 
	$('[data-form]').prop('disabled', true);
	var formName =  $(e.target).data('form'); 
	$('#' + formName).submit();

} 
*/

maxAdmin.prototype.saveDone = function (res)
{
	$('[data-form]').prop('disabled', false);
	
	var json = $.parseJSON(res);
	
	var result = json.result;
	var title = json.title; 

	
	var collection_id = json.data.id; 
 
	if (typeof json.data.new_nonce !== 'undefined')
	{
		var nonce = json.data.new_nonce; 
	 	$('input[name="nonce"]').val(json.data.new_nonce);
	}
	
	if (result)
	{
		// if collection is new - add collection_id to the field
		$('input[name="collection_id"]').val(collection_id); 
		
		// replace the location to the correct collection
		var href = window.location.href; 
		if (href.indexOf('collection_id') === -1)
			window.history.replaceState({}, '', href + '&collection_id=' + collection_id); 
		
		// trigger other updates if needed
		$(document).trigger('mbFormSaved');
		
		// update previous selection to current state;
		var order = $('input[name="sorted"]').val();
		$('input[name="previous_selection"]').val(order);
		
		// in case the interface needs to be reloaded.  
		if (json.data.reload)
		{
			document.location.reload(true);
		}
		
	}
	if (! result)
	{
		$modal = window.maxFoundry.maxmodal; 
		$modal.newModal('collection_error');
		$modal.setTitle(title);
		$modal.setContent(json.body);
 
		$modal.setControls('<button class="modal_close button-primary">' + json.close_text + '</button>'); 		
		$modal.show(); 

	}
}


}); /* END OF JQUERY */


