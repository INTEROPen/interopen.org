// All backend JS for collections 
var maxCollection; 

jQuery(document).ready(function($) {

maxCollection = function(){}; 

maxCollection.prototype = {
	 fields: '',
 	 form_updated: false,
 	 modalCurrentPage: 0,
 	 item_maxwidth: 0, 
}

// init
maxCollection.prototype.init = function()
{
	// LIST 
	$('.collection_remove').on('click', $.proxy(this.confirmRemoveCollection, this)); 
	
	/****** ONLY THE COLLECTION FORM ****/ 
	if ($('#collection_edit').length == 0) 
		return; 

	$('.mb-preview-window').draggable(); 
	$('.button-picker .picker-wrapper').on('click', $.proxy(this.addButtontoCollection, this)); 
	
	//$(document).on('mouseenter', '.sortable.buttons .item', $.proxy(this.showTrash, this));
	//$(document).on('mouseleave', '.sortable.buttons .item', $.proxy(this.hideTrash, this));
	$(document).on('click', '.sortable.buttons .button-remove', $.proxy(this.removeButton, this)); 
	
	if ( typeof collectionFieldMap != 'undefined')
		this.fields = $.parseJSON(collectionFieldMap);
	

	// Limit this to the collection edit screen */ 	
	$(document).on('keyup','.mb_ajax_save input[type="text"]', $.proxy(this.update_preview,this)); 
	$(document).on('keyup change','.mb_ajax_save input', $.proxy(this.update_preview,this));  		
	$(document).on('change','.mb_ajax_save select', $.proxy(this.update_preview, this)); 			
	
	this.initSortable(); 
	
	// popup selection 
	$(document).on('click','button[name="picker_popup"]', $.proxy(this.togglePickerPopup, this));

	$(document).on('updatePreviewWindow', $.proxy(this.updatePlacement, this)); // event from other scripts possible
	$(document).on('mbFormSaved', $.proxy(function () { this.form_updated = false; 	this.toggleSaveIndicator(false); },this));
	$(window).on('beforeunload', $.proxy(function () { if (this.form_updated) return maxcol_wp.leave_page; }, this));
	
	$(document).on('click', '#exportCollection', $.proxy(this.exportCollection, this)); 

	this.updatePlacement(); 
	this.updatePlacement(); // running this twice yields better results 
	
	// init, if there are not buttons selected, open selector ( i.e. new collection )
	if ($('input[name="sorted"]').val() == '')
		this.togglePickerPopup();
}

// add a button to the collection on screen, buttons are passed by param.
maxCollection.prototype.addButtontoCollection = function (button)
{
	var $button = $(button).clone(); 
	var btn_id = $button.data('id'); 
	$button.find('.button_name').remove();
	
	//$button.css('height','auto');
	
	var addbtn = $button;
 
	// find span w/ button data
	var bdata = addbtn.find('.button_data').text(); 
	addbtn.find('.button_data').remove(); 
	addbtn.append('<input type="hidden" name="button-data-' + btn_id + '" value="' + bdata + '">');
	
 	this.addButtontoPreview($button.clone()); 
 
	// get data from it and add input type hidden to the sortable.
	$(addbtn).appendTo('.mb_collection_selection .sortable ');

	$('.mb_collection_selection .sortable').sortable('refresh');

	// add new buttons to sort array ( and thus to selection ) 	
	 var order = $(".mb_collection_selection .sortable").sortable('toArray', {attribute: 'data-id'}) 
	 var count = order.length;
	 order = order.toString();
	 $('input[name="sorted"]').val(order);
	 
	 $.proxy( this.updateColPreview({ action: 'new_button', data: bdata, button_id: btn_id, button_count: count }), this); 
	 
}

/* Update the social options preview */ 
maxCollection.prototype.updateColPreview = function (args) 
{
 
	if (typeof args.action !== 'undefined') 
	{
		var action = args.action; 
	}
	else
		action = ''; 
		
	var nonce = $('input[name="block_nonce"]').val(); 
	var collection_id = $('input[name="collection_id"]').val(); 
	var collection_type = $('input[name="collection_type"]').val(); 
	var count = args.button_count; 
	var index = (count-1);
	
	if (action == 'new_button') 
	{
		var ajax_data = { 
		 block_name : 'social', 
		 block_action : 'ajax_new_button', 
		 action : 'mbpro_collection_block', 
		 nonce: nonce, 
		 collection_id: collection_id, 
		 collection_type: collection_type, 
		 block_data : {data : args.data, button_id: args.button_id, index: index },
//		 button_id: args.button_id,	 
		}
		 $.proxy(this.ajaxNewButton(ajax_data), this); 
	}
		
	// update interface, probably via ajax. 

}

maxCollection.prototype.addButtontoPreview = function($button)
{

	$button.find('input').remove(); 
	$button.find('.dashicons').remove(); 
	var button_id = $button.data('id'); 
	var $preview = $("<span class='mb-collection-item' data-id='" + button_id + "'></span>").append($button.children()); 

	$preview.appendTo('.mb-preview-window .maxcollection');	
	this.updatePlacement(); 
}

maxCollection.prototype.ajaxNewButton = function(data)
{
 
	var url = mb_ajax.ajaxurl;
 
	$.ajax({
	  type: "POST",
	  url: url,
	  data: data,
 	  
	}).done($.proxy(this.ajaxNewButtonDone, this));

}

maxCollection.prototype.ajaxNewButtonDone = function (result) 
{
	var json = $.parseJSON(result);
	$('.mb_tab[data-tab="social-options"]').children('.inside').append(json.body); 
	$('.no-buttons').hide();
 	$(document).trigger('reInitConditionals'); // do again conditionals 
}


// update the live preview.
maxCollection.prototype.update_preview = function(e) 
		{
 
			e.preventDefault();

			this.toggleSaveIndicator(true);
			
			var target = $(e.target); 
			var id = $(target).attr('id'); 
 
 			// for multi-fields the id is not bound to an update function. 
 			if (typeof $(target).data('target') !== 'undefined') 
 				id = $(target).data('target'); 
 

			var data = this.fields[id]; 
 
			if (typeof data == 'undefined') 
				return; // field doesn't have updates 
 
			if (typeof data.css != 'undefined') 		
			{
 
				value = target.val(); 
				if (typeof data.css_unit != 'undefined' && value.indexOf(data.css_unit) == -1) 
					value += data.css_unit;

				//$('.output .result').find('a').css(data.css, value);

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
			
			this.updatePlacement(); // update the window size
		};

maxCollection.prototype.putCSS = function(data,value,state) 
{
	state = state || 'both';
 
	 
	var element = '.maxcollection';  
 
	if (typeof data.csspart != 'undefined') 
	{
		var parts = data.csspart.split(",");
		for(i=0; i < parts.length; i++)
		{
			var cpart = parts[i]; 
			//var fullpart = element; 
			if (element !== '.' + cpart)
				var fullpart = element + " ." + cpart;
			else
				var fullpart = element; // lot of stuff on .maxcollection main element

 
  				$('.mb-preview-wrapper').find(fullpart).css(data.css, value); 
		  }
	}
	else
		$('.output .result').find(element).css(data.css, value); 
		

}

/* Check the position ( horizontal - vertical ) of the buttons and change accordingly. */ 
maxCollection.prototype.updatePlacement = function () 
{
	
	var orientation = $('#orientation').val(); 
 
	if (typeof orientation != 'undefined' && orientation !== 'auto') 
	{
		this.pushPreview(orientation); 
	
	}
	else if (orientation == 'auto') 
	{
		var placement = $('#placement').val(); 
		if (placement == 'static-left' || placement == 'static-right') 
			orientation = 'vertical'; 
		else
			orientation = 'horizontal'; 
 
		this.pushPreview(orientation); 
	}
	
}	

maxCollection.prototype.updateCollectionName = function () 
{
	// needs escaping!
	var name = $('#collection_name').val();
 
 	name = name.MBescapeHTML(); 
	 
	var line = $('.mb-message.shortcode').html(); 
	if (line)
	{
		line = line.replace(/\[maxcollection name="(.*)"]/gi, '[maxcollection name="' + name + '"]');
 		$('.mb-message.shortcode').html( line ); 
	} 
	
	
}

var __entityMap = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': '&quot;',
    "'": '&#39;',
    "/": '&#x2F;'
};

String.prototype.MBescapeHTML = function() {
    return String(this).replace(/[&<>"'\/]/g, function (s) {
        return __entityMap[s];
    });
}


/* Update for social block, make {tags} to be more meaningful in the preview */
maxCollection.prototype.parseTags = function (target) 
{
	var option = $(target).parents('.social-option'); 
	var index = $('.social-option').index(option);
	var button = $('.mb-preview-window .mb-collection-item').eq(index);
	var social_button = $(option).find('.maxbutton'); 
	


	var network = $(option).find('[data-target="network"] :selected').text();
	var count = 0; 
	var replacements = { network_name : network, count: count }; 
	
	
	// objects
	var text = $(button).find('.mb-text');
	var text2 = $(button).find('.mb-text2');
	
	
	//strings
	var social_text = $(social_button).find('.mb-text').text(); 
	var social_text2 = $(social_button).find('.mb-text2').text(); 
	
	$(text).text(social_text); 
	$(text2).text(social_text2); 
	
	
	this.replaceTags(text, replacements); 
	this.replaceTags(text2, replacements);

}

maxCollection.prototype.replaceTags = function (element, replacements)
{
	var text = $(element).text(); 
	
	if (typeof $(element).data('original') == 'undefined')
	{
		$(element).data('original', text); 
	}
	else // use original text for this
	{
		text = $(element).data('original'); 
	}
	
	$.each(replacements, function (index, el) 
	{

		text = text.replace('{' + index + '}', el);
		$(element).text(text); 
	}); 
}
	
maxCollection.prototype.pushPreview = function(orientation)
{

	var $window = $('.mb-preview-window.output'); 
	var $items = $('.mb-preview-window .mb-collection-item'); 
	var height = $('.mb-preview-window .maxcollection').height() + 75; 

	$items.css('float','left');
	
	if( $window.css('position') == 'relative')
		return; // don't size on mobile views.
		
	if (orientation == 'horizontal') 
	{
		$window.css('width', $('#maxbuttons').css('width') ); 
		$window.css('top', 'auto'); 
		$window.css('bottom', '10px'); 
		$window.css('height',  height + 'px'); 
		$window.css('right', '10px');
		$window.css('left', 'auto'); 
		$items.css('clear', 'none'); 

	}
	if (orientation == 'vertical') 
	{
		$window.css('width', '30%'); 
		$window.css('right', '20px'); 
		$window.css('top', '25%'); 
		$window.css('height', height + 'px');
		$window.css('left','auto');
		$window.css('bottom', 'auto');
		$items.css('clear','both'); 
	}


}

maxCollection.prototype.exportCollection = function () 
{
	var nonce = $('input[name="block_nonce"]').val(); 
	var collection_id = $('input[name="collection_id"]').val(); 
	var collection_type = $('input[name="collection_type"]').val(); 
		
	var ajax_data = { 
		 block_name : 'export', 
		 block_action : 'export', 
		 action : 'mbpro_collection_block', 
		 nonce: nonce, 
		 collection_id: collection_id, 
		 collection_type: collection_type, 
		 block_data : {},
	}

	var url = mb_ajax.ajaxurl;
 
	$.ajax({
	  type: "POST",
	  url: url,
	  data: ajax_data,
 	  
	}).done(   
		function (result) 
		{
			var json = $.parseJSON(result);
			var location = json.data.location; 
			window.location.href = location;
		}
	);

}
		

maxCollection.prototype.checkPicker = function () 
{
	if ($('.mb_collection_picker').is(':visible'))
		this.initButtonPicker();
}

/* Init sortable interface */
maxCollection.prototype.initSortable = function ()
{
	var mbc = this; 
	
	$('.mb_collection_selection .sortable').sortable({ 
 		
		placeholder: 'sortable-placeholder',
		connectWith: '.maxcollection',
      	start: function(event, ui){
                iBefore = ui.item.index();
        },
 		create: function(event, ui) {
     		//	 var order = $(".mb_collection_selection .sortable").sortable('toArray',  {attribute: 'data-id'}).toString();
     		  	 var order = $(this).sortable('toArray',  {attribute: 'data-id'}).toString();
     			 $('input[name="sorted"]').val(order);
     			 $('input[name="previous_selection"]').val(order);
 		},
 		update: function(event, ui) {
 				iAfter = ui.item.index();
     			// var order = $(".mb_collection_selection .sortable").sortable('toArray',  {attribute: 'data-id'}).toString();
     			 var order = $(this).sortable('toArray',  {attribute: 'data-id'}).toString();
     			 $('input[name="sorted"]').val(order);

				mbc.updateListOrder('.maxcollection .mb-collection-item', iBefore, iAfter); 
				mbc.updateListOrder('.social_block .social-option', iBefore, iAfter);
 		},
 	

	});
}


// removes button from the current collection.
maxCollection.prototype.removeButton = function (e)
{
	e.preventDefault(); 
 
	var old_sort = $('input[name="sorted"]').val();
	var parent = $(e.currentTarget).parents('.item');
	var items = $('.mb_collection_selection .sortable .item');
	var index = items.index(parent);

 	$(e.currentTarget).parents('.item').remove(); 
	$('.maxcollection .mb-collection-item:eq(' + index + ')').remove(); 
	$('.social_block .social-option:eq(' + index + ')').remove(); 

	$('.mb_collection_selection .sortable').sortable('refresh'); 
    var order = $(".mb_collection_selection .sortable").sortable('toArray', {attribute: 'data-id'}).toString();
    $('input[name="sorted"]').val(order);
    
    this.toggleSaveIndicator(true);
     
}

maxCollection.prototype.updateListOrder = function (el, before, after) 
{
    this.toggleSaveIndicator(true);
    evictee = $(el + ':eq('+after+')');
    evictor = $(el + ':eq('+before+')');
  
 
    evictee.replaceWith(evictor);
    if(iBefore > iAfter)
        evictor.after(evictee);
    else
        evictor.before(evictee); 

 	// reorder not only the position but also the name of everything (-buttonId-index ) since it's important when saving.
 	if(el == '.social_block .social-option')
 	{
 		 var elements = $(el).each(function (index) { 
 	    	var id = $(this).data('id'); 
 		 	var html = $(this).html(); 
 		 	var pattern = new RegExp( '-' + id + '-\\d', 'gi');
  		 	html = html.replace(pattern,'-' + id + '-' + index);
 		 	$(this).html(html);
 		 }); 
 	}


}  

/* Confirmation window to delete the collection */
maxCollection.prototype.confirmRemoveCollection = function(e) 
{
	
 	var collection_id = $(e.target).parents('.collection').data('id'); 
 	var nonce = $(e.target).parents('.collection').data('blocknonce'); 
 	var collection_type = $(e.target).parents('.collection').data('type');

	var title = $('.remove_action_title').text(); 
	var text = "<p>" +  $('.remove_action_text').text() + "</p>"; 
	
	var modal = window.maxFoundry.maxmodal; 
	modal.newModal('collection_remove');
	modal.setTitle(title);
	modal.setContent(text);

	modal.addControl('yes', {collection_id: collection_id, nonce: nonce, collection_type: collection_type},
				 $.proxy(this.removeCollection, this) );
	modal.addControl('no', '', $.proxy(modal.close, modal) );
	modal.setControls();
	modal.show(); 

}

maxCollection.prototype.removeCollection = function (e)
{
 
	var collection_id = e.data.collection_id; 
	
	// ajax something 
	var nonce = e.data.nonce; 
	 
	var collection_type = e.data.collection_type;
		
	var ajax_data = { 
		 block_name : 'collection', // invoke on the main class
		 block_action : 'delete', 
		 action : 'mbpro_collection_block', 
		 nonce: nonce, 
		 collection_id: collection_id, 
		 collection_type: collection_type, 
		 block_data : {},
	}

	var url = mb_ajax.ajaxurl;
 
	$.ajax({
	  type: "POST",
	  url: url,
	  data: ajax_data,
 	  
	}).done(   
		function (result) 
		{
			var json = $.parseJSON(result);
		 	var modal = window.maxFoundry.maxmodal; 
		 	
 			var title = json.data.title; 
 			var body = json.data.body; 
 			var collection_id = json.data.collection_id; 

			modal.newModal('collection_removed');
			modal.setTitle(title);
			modal.setContent(body);
			modal.addControl('ok','', $.proxy(modal.close, modal) );
			modal.setControls();
			modal.show();

			$('.collection-' + collection_id).hide(); 		
	
		}
	);
} // removeCollection


maxCollection.prototype.togglePickerPopup = function (e)
{	
	var maxmodal = window.maxFoundry.maxmodal; 
 
	var picker = $('#picker-modal').html();
	maxmodal.newModal('picker-modal');

 	maxmodal.setTitle(maxcol_wp.picker_title);
	maxmodal.setContent(picker);

	maxmodal.show();
	modal = maxmodal.get(); 
	
	// load the events on demand
	$(modal).off('click change keyup');
	$(modal).on('click', '.picker-packages a', $.proxy(this.getModalButtons, this));
	$(modal).on('click', '.picker-main .screen .item', $.proxy(this.toggleInSelection, this)); 
	$(modal).on('click', '.modal_close',   $.proxy(maxmodal.close, maxmodal) ); 
	$(modal).on('click', '.clear-selection',  $.proxy(this.modalClearSelection, this)); 
	$(modal).on('click', 'button[name="add-buttons"]', $.proxy(this.modalAddButtons,this));			
	$(modal).on('click', '.button-remove', $.proxy(this.modalDeleteButton, this) ); 
	
	$(modal).on('click', '.pagination-links a', $.proxy(this.modalLoadPage, this)); 
	$(modal).on('change keyup', '.pagination-links input', $.proxy(this.modalLoadPage, this)); 
	
	if ($(modal).find('.picker-main .current-screen').length == 0 ) // not loaded, load first.
	{
		$(modal).find('.picker-packages ul li a:first').trigger('click');
	}
	
} // initPickerPopup

maxCollection.prototype.getModalButtons = function (e)
{	
	e.preventDefault(); 
	
	var modal = window.maxFoundry.maxmodal.get(); 	
	var target = $(e.target);
	var pack  = $(e.target).data('pack'); 
	var loaded = $(e.target).data('pack-loaded'); 
	
	if (typeof loaded !== 'undefined' && loaded) 
	{
		$(modal).find('.picker-main .screen').hide().removeClass('current-screen'); 
		$(modal).find('.picker-main .screen-' + pack).show().addClass('current-screen');
		this.modalEqualHeight();
		
		// select the package in view
		$(modal).find('.picker-packages ul li a').removeClass('pack-active'); 
		$(e.target).addClass('pack-active');
				
		return; // loaded, already fine. 	
	}
	
	var collection_id = $('input[name="collection_id"]').val(); 
	var collection_type = $('input[name="collection_type"]').val(); 
	var nonce = $('input[name="block_nonce"]').val(); 
				
	var ajax_data = { 
		 block_name : 'collection', // invoke on the main class
		 block_action : 'ajax_getButtons', 
		 action : 'mbpro_collection_block',
		 nonce: nonce, 
		 collection_id: collection_id, 
		 collection_type: collection_type, 
		 block_data : { 'pack' : pack },
	}
	
	var url = mb_ajax.ajaxurl;
 	var self = this; 
 	
	$.ajax({
	  type: "POST",
	  url: url,
	  data: ajax_data,
 	  
	}).done(   
		function (result) 
		{
			self.modalLoadButtons(result, pack, target);
			
		}
	);	

} // getModalButtons

/* Add the AJAX requested buttons to the modal screen */ 
maxCollection.prototype.modalLoadButtons = function (result,pack,target) 
{
	var self = this;
	var modal = window.maxFoundry.maxmodal.get(); 	
	
	$(modal).find('.picker-main .screen').hide().removeClass('current-screen'); 
	
	var json = $.parseJSON(result);
	
	if ( $(modal).find('.picker-main .screen-' + pack).length == 0 ) 
	{
		var el = $('<div class="screen screen-' + pack + '">'); 
		$(modal).find('.picker-main').append(el);
	}
	else 
	{
		var el = $(modal).find(' .picker-main .screen-' + pack);
	}
	
	el.html(json.body); 
	
	$(modal).find('.picker-main .screen-' + pack).show().addClass('current-screen');
 
 	setTimeout( $.proxy(function() { 

		$(modal).find('.picker-main .current-screen .item').each(function()
		{
			self.modalScaleButton(this);	
		
		});
	}, this), 400); 
	 
	this.modalEqualHeight();
	$(target).data('pack-loaded', true); 
	
	// select the package in view
	$(modal).find('.picker-packages ul li a').removeClass('pack-active'); 
	$(target).addClass('pack-active');
	
	$(document).trigger('mb-modalLoadButtons'); 

}

maxCollection.prototype.modalLoadPage = function (e)
{
	e.preventDefault(); 
	
	var page = $(e.target).data("page"); 
	if (typeof page == 'undefined') 
	{
		// check if input number is event 
		if (e.target.type == 'number') 
			var page = $(e.target).val(); 
		else
			return; // no page load if page link is disabled ( current / or n/a ) 
	}
	var collection_id = $('input[name="collection_id"]').val(); 
	var collection_type = $('input[name="collection_type"]').val(); 
	var nonce = $('input[name="block_nonce"]').val(); 

	var pack = $('.picker-packages a.pack-active').data('pack');
	var target = $('.picker-packages a.pack-active');

	var ajax_data = { 
		 block_name : 'collection', // invoke on the main class
		 block_action : 'ajax_getButtons', 
		 action : 'mbpro_collection_block',
		 nonce: nonce, 
		 collection_id: collection_id, 
		 collection_type: collection_type, 
		 block_data : { 'pack' : pack, 'paged' : page },
	}
	
	var url = mb_ajax.ajaxurl;
 	var self = this; 
 	
	$.ajax({
	  type: "POST",
	  url: url,
	  data: ajax_data,
 	  
	}).done(   
		function (result) 
		{
	
			self.modalLoadButtons(result, pack, target);
		}
	);	

}
	
maxCollection.prototype.toggleInSelection = function (e) 
{
	var target = $(e.target);
 	var modal = window.maxFoundry.maxmodal.get(); 	
 	
 	if (! target.hasClass('item')) // find the whole thing.  
 	{	
 		var target = $(e.target).parents('.item'); 
 	}
 	
 	var id = $(target).data('id'); 
 	var exists = $(modal).find('.picker-inselection .items').children('[data-id="' + id + '"]'); 
 	if (exists.length > 0) 
 	{
 		$(target).find('.button-selected').remove();	
 		$(modal).find('.picker-inselection .items').children('[data-id="' + id + '"]').remove();
 		this.modalUpdateCount();
 		return;
 	}
 	
 	var clone = $(target).clone(); 
 	$(clone).find('.dashicons').remove(); 
	$(clone).css('height', '100%');
	$(clone).css('width', '40px'); // projected width from css
	$(clone).find('a').css('verticalAlign', 'middle');
 	
 	if ( $(target).children('.button-selected').length == 0)
	 	$(target).append('<div class="button-selected"><span class="dashicons dashicons-yes">'); 
	
	clone.append('<div class="button-remove"><span class="dashicons dashicons-no">'); 	
 
	$(modal).find('.picker-inselection .items').append(clone);
	
	this.modalScaleButton(clone); 
	this.modalUpdateCount();
	
} // toggleInSelection

maxCollection.prototype.modalEqualHeight = function (e) 
{
	 
	var maxHeight = 0; 
	$(modal).find('.picker-main .current-screen .item').each(function()
		{
			if ( $(this).height() > maxHeight)
				maxHeight = $(this).height(); 
		}
	);


	
	if(maxHeight > 0) // 0 is possible when picker is closed before buttons are done loading.
		$(modal).find('.picker-main .current-screen .item').height(maxHeight);
}

maxCollection.prototype.modalScaleButton = function(item)
{	 
	var maxwidth = $(item).width();

	// switch over width like this, not clone due to performance ( about 30 times ) . 
	$(item).css('width', '100%');	
	var button = $(item).find('a'); 
	var width  = $(button).width(); 
	var height = $(button).height();
 	$(item).css('width', maxwidth); 
 
	if (maxwidth >= width) 
	{
		return; // we don't want to enlarge.
	}
	
	// find the scale to resize it.
	var scale = (maxwidth/width);
	scale = scale.toFixed(2); 

 	var $button = $(item).find('a'); 
 
	var csstext = 'width: calc(' + width + 'px * ' + scale + ')!important; height: calc(' + height + 'px * ' + scale + ') !important;';
	$button.css('cssText', csstext);
	
	$button.children('.mb-text').each(function()
	{
		var text = $(this).css('fontSize'); 

		var textitem = this;
		var cssitems = [ "font-size", "padding-left","padding-right","padding-top","padding-bottom" ]; 
		var item_csstext = ''; 
		
		for( i = 0; i < cssitems.length; i++)
		{
			 
			if ( parseInt ($(this).css(cssitems[i]) ) > 0) 
			{
				var p = $(this).css(cssitems[i]); 
				
				item_csstext += cssitems[i] + ': calc(' + p + ' * ' + scale + ') !important; ';
			}
 
		}
		$(this).css('cssText', item_csstext);
	});	
	
	$icon = $button.children('.mb-icon');
	var iwidth = $icon.width();
	var iheight = $icon.height();  
	
	var cssText = 'width: calc(' + iwidth + 'px * ' + scale + ') !important; height: calc(' + iheight + 'px * ' + scale + ') !important;';
	$icon.css('cssText', cssText); 
	
	var $icon_img = $icon.children('img'); 

	if ($icon_img.length > 0) 
	{
		var iwidth = $icon_img.width(); 
		var iheight = $icon_img.height();
		var cssText = 'width: calc(' + iwidth + 'px * ' + scale + ') !important; height: calc(' + iheight + 'px * ' + scale + ') !important;';	
		$icon_img.css('cssText', cssText); 
	}
		 
}
// Remove all the scaling.
maxCollection.prototype.unScaleButton = function(item)
{
	item.find('a, .mb-text, .mb-icon, img').css('cssText','');
	item.css('cssText','');

}

maxCollection.prototype.modalClearSelection = function (e) 
{
	var modal = window.maxFoundry.maxmodal.get(); 	
	$(modal).find('.picker-inselection .items').html('');
	$(modal).find('.screen .item .button-selected').remove(); 
	this.modalUpdateCount();
}

maxCollection.prototype.modalAddButtons = function (e)
{		
	e.preventDefault; 
	var self = this; 
	var maxmodal = window.maxFoundry.maxmodal; 	
	var modal = window.maxFoundry.maxmodal.get(); 	
	
	$(modal).find('.picker-inselection .items .item').each(function ()
	{
		
		var button = $(this).clone().wrap('<div>').parent().html();
		var $button = $(button);  // load the dom

		self.unScaleButton($button); 
		self.addButtontoCollection( $button );
	}); 
	
	// close modal
//	this.togglePickerPopup();
	maxmodal.close();
	this.modalClearSelection(); // reset selection

}

maxCollection.prototype.modalUpdateCount = function (e)
{
		var modal = window.maxFoundry.maxmodal.get(); 	
			
		// update count 
		var count = $(modal).find('.picker-inselection .items .item').length; 
		$(modal).find('.picker-inselection .info .count').text(count);
			
		if (count == 0) 
			$(modal).find('.picker-inselection .info').hide();
		else
			$(modal).find('.picker-inselection .info').show();
}

maxCollection.prototype.modalDeleteButton = function (e) { 
	var item = $(e.target).parents('.item'); 
	var id = item.data('id'); 
	$('.picker-main').find('[data-id="' + id + '"]').children('.button-selected').remove();
	item.remove(); 
	this.modalUpdateCount();
}

maxCollection.prototype.toggleSaveIndicator = function(toggle)
{	
	if (toggle)
	{
		this.form_updated = true;
		$('.save-indicator').css('display', 'block').addClass('dashicons-warning').removeClass('dashicons-yes'); 
	}
	else
		$('.save-indicator').removeClass('dashicons-warning').addClass('dashicons-yes'); 
}



}); // jquery 
