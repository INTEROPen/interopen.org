//var maxTabs; 

jQuery(document).ready(function($) {

var maxTabs = function () { 

} 

maxTabs.prototype.init = function() 
{
	this.tabs(); 
	this.previewTab();

}

maxTabs.prototype.tabs = function () 
{

	var tabslocation = '.sub-tabs'; // tab area selector 
	var tabs = '.mb_tab'; //actual tabs 
	var main_parent = '#maxbuttons'; // the boss of the page, used for view selectors.
	
	if ($(tabs).length === 0)
	{
		return; // no tabs
	}
	
	var view = $(main_parent).data('view'); 
	if (typeof view == 'undefined') 
		view = 'list';
	
 
	if (view == 'list')
	{	
		return; // list view no tabs.	
	}
 
	
	// start to do tabs. 
	$(main_parent).addClass('mb_tabs_active'); 
				
	if ($(tabslocation).length === 0)  // no placeholder, create 
	{
		$tabslocation = $('<h2 class="nav-tab-wrapper sub-tabs"></h2>');
		$tabslocation.insertBefore($(tabs).first());
		$tabslocation = $(tabslocation);
	} 	
	else
		$tabslocation = $(tabslocation);
	
	$(tabs).hide(); 
	$.each($(tabs), function () 
	{
		var titlediv = $(this).children('div.title').first();
		var el = titlediv.clone();  //.text(); 
	
	 
		
		// extract icon from el
		var icon =  el.children('span:first').clone().wrap('<p>').parent().html();
		// get title 
		var title = el.children('span.title').text();
		
		// remove all spans to get title 
		$(el).children('span').remove();
		$(el).children('input,button').remove(); // remove interface elements. 		 

		// remove tab information from the title
		titlediv.children('span:first').remove();		
		titlediv.children('.title').remove(); 

 
		
		if (typeof icon != 'undefined') 
			tab_title = icon + title; 
		else
			tab_title = title; 
			
		var tab = $('<a class="nav-tab" href="javascript:void(0);">' + tab_title + '</a>'); 
		
		title = title.trim();
		title = title.replace(/ /g,"-");
		$(tab).attr('data-tab', title.toLowerCase() );
 
		$(tabslocation).append(tab); 
 
		$(this).attr('data-tab', title.toLowerCase());	 
 
	
	}); 	

	// show first tab	
	var active_tab = $('input[name="tab"]').val(); 
	if(typeof active_tab == 'undefined') 
		active_tab = ''; 
	
	if (active_tab == '')
	{
		$tabslocation.children('.nav-tab').first().addClass('nav-tab-active');
		$(tabs).first().show();
	}
	else
	{
		$tabslocation.children('[data-tab="' + active_tab + '"]').addClass('nav-tab-active');
		$(tabs + '[data-tab="' + active_tab + '"]').show(); 
	}
	
	$tabslocation.children('a').on('click', this.toggleTabs); 
	this.addSaveTab($tabslocation);
}

maxTabs.prototype.addSaveTab = function ($tabslocation)
{
	//var submitButton = $('input[type="submit"]').clone();
	/*var submitText = submitButton = $('input[type="submit"]').attr('value'); 
	var saveTab = $('<ul class="submit-tab"></ul>').append('<li><span class="dashicons dashicons-thumbs-up"></span> ' + submitText + '</li>'); 
 	$(document).on('click', '.submit-tab', function (e)  { 
 		$(e.target).parents('form').submit(); 
 	}); */
 	
 	var saveTab = '<div class="save-indicator dashicons dashicons-warning"></div>'; 
	$tabslocation.append(saveTab);

}

maxTabs.prototype.toggleTabs = function (e)
{
	e.preventDefault(); 
	var tabslocation = '.sub-tabs'; // tab area selector 
	var tabs = '.mb_tab'; //actual tabs 
	
	$(tabslocation).children('a').removeClass('nav-tab-active'); 
	$(this).addClass('nav-tab-active'); 
	
	$(tabs).hide();
	
	var tab = $(this).data('tab'); 
 
	$(tabs + '[data-tab="' + tab + '"]').show(); 
	$('input[name="tab"]').val(tab); 
	
 
	$(document).trigger('maxTabChange', [ tab ]); 
	
}

maxTabs.prototype.previewTab = function ()
{
	var isVisible = $('.mb-preview-window').is(':visible'); 
	var tabslocation = '.sub-tabs'; 
	
	var previewtab = tabslocation + ' a[data-tab="preview"]';

	// init	
 		this.togglePreview();
	
	$(previewtab).off('click'); // overrule 

	$(document).on('click',previewtab, $.proxy(function (e) { 
		e.preventDefault(); 
		e.stopPropagation(); 
		
 
		//var previewtab = e.data["previewtab"];
		var isVisible = $('.mb-preview-window').is(':visible');
	
		if (isVisible) 
		{
			this.togglePreview(false);
			
		} else
		{
			this.togglePreview(true);
		}
		
		$(document).trigger('updatePreviewWindow'); // fix sizes
		return false;

	}, this));

	$('.mb-preview-window .close').on('click', {tab: previewtab }, function (e) { 
		$(e.data.tab).trigger('click');

	});
	
}

maxTabs.prototype.togglePreview = function(show) {

	var tabslocation = '.sub-tabs'; 
	var previewtab = tabslocation + ' a[data-tab="preview"]';
 
	if (typeof show == 'undefined') 
	{
		// no preference, check localStorage
		if(!localStorage.getItem('mb-col-preview')) {
				this.togglePreview(true);
		
		}
		else
		{
			var preview = localStorage.getItem('mb-col-preview');
 
			if (typeof preview == 'string')  // convert
			{
				if (preview == 'true')
					preview = true; 
				else
					preview = false; 
			}
			if (typeof preview == 'boolean') 
			{
				this.togglePreview(preview);
			}
		
		}
	
	}
	else if (show) 
	{

		$('.mb-preview-window').show(); 
		$(previewtab).addClass(' preview-on');
		$(previewtab + ' .dashicons').addClass("dashicons-yes").removeClass('dashicons-no');			
		$(previewtab).removeClass('preview-off');	
	    localStorage.setItem('mb-col-preview', true);			

	}
	else
	{
		$('.mb-preview-window').hide(); 
		$(previewtab).removeClass("preview-on");
		$(previewtab + ' .dashicons').removeClass('dashicons-yes').addClass('dashicons-no'); 
		
		//$(previewtab).css('backgroundColor','#fff');	
		$(previewtab).addClass('preview-off');	
	    localStorage.setItem('mb-col-preview', false);	
	}

}



var mt = new maxTabs(); 
mt.init();

}); /* END OF JQUERY */

