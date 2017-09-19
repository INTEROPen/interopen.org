
	var mbResponsive = function ($) 
	{
		this.jquery = $;
	}


mbResponsive.prototype = { 
	jquery : null, 
	mbAdmin: null,
	responsiveMap: null, 
}

mbResponsive.prototype.init = function (mbAdmin) 
{
	$ = this.jquery; 	
	this.mbAdmin = mbAdmin; 

	if ($('#new-button-form').length == 0)  // Don't load outside button editor
			return; 
			
	this.checkAutoQuery();	

	$('input[name="auto_responsive"]').on('click', $.proxy(this.checkAutoQuery,this)); 
	$('.add_media_query').on('click', $.proxy(this.addMediaQuery, this)); 
	
	this.responsiveMap = JSON.parse(responsiveMap);

	$(document).on('click', '.removebutton', $.proxy(this.removeMediaQuery, this)); 
	//$(document).on('click', '.responsive_preview', $.proxy(this.toggleResponsivePreview, this)); 
	
}

mbResponsive.prototype.checkAutoQuery = function()
{
	$ = this.jquery; 
	
	if ( $('input[name="auto_responsive"]').is(':checked') )
	{

		$('.media_queries_options').hide(); 
		$('.option-design.new-query').hide();
	}
	else 
	{
		$('.media_queries_options').show(); 
		$('.option-design.new-query').show();
	}
}	

mbResponsive.prototype.addMediaQuery = function() 
{
	$ = this.jquery; 
	
	this.mbAdmin.saveIndicator(true); 
	var new_option = $('.media_option_prot').children().clone();

	var new_query = $("#new_query").val(); 
	var new_title = $("#new_query :selected").text(); 
	var new_desc = $("#media_desc").children('#' + new_query).text();
 
	$(new_option).data('query', new_query); 
	$(new_option).children('input[name="media_query[]"]').val(new_query);
	$(new_option).children('.title').text(new_title); 
	$(new_option).children('.description').text(new_desc);
	
	if (new_query == 'custom') 
	{

		$(new_option).find('.custom').removeClass('hidden'); 
	}
	
	var new_index = $('input[name="next_media_index"]').val();
 
 	// rename new field to the proper ID
	$(new_option).find('label, select, input').each(function () { 
 
		var name = $(this).attr('name'); 
		var id = $(this).attr('id');
		var data = $(this).data('field'); 
		var tagname = $(this).prop('tagName').toLowerCase();
		
		if (typeof id !== 'undefined')
			$(this).attr('id', id.replace('[]','[' + new_index + ']'));
		if (typeof name !== 'undefined') 
			$(this).attr('name', name.replace('[]','[' + new_index + ']'));
		if (typeof data !== 'undefined') 
			$(this).data('field', data.replace('[]','[' + new_index + ']'));
		if (tagname == 'label') 
		{
			var attrfor = $(this).attr('for');
			if ( typeof attrfor != 'undefined')
				$(this).attr('for', attrfor.replace('[]','[' + new_index + ']'));
		}

	})
	
	 $(document).trigger('reinitConditionals'); 
	 
	 new_index = parseInt(new_index);
 
	$('input[name="next_media_index"]').val( (new_index+1) ); 

	if (new_query !== 'custom')
	{	
		$('#new_query :selected').prop('disabled', true);
		$('#new_query :selected').prop('selected', false);
	}

	$('.new_query_space').append(new_option);
	
	var pos = $('.new_query_space').offset().top; 
	$(window).scrollTop( (pos-100) ); 
 
}

mbResponsive.prototype.removeMediaQuery = function(e) 
{
	$ = this.jquery; 
	var target = e.target;

	var query = $(target).parents('.media_query').data('query'); 
	$(target).parents('.media_query').fadeOut(function() { $(this).remove() } ); 
	
	$('#new_query option[value="' + query + '"]').prop('disabled', false);
}

mbResponsive.prototype.toggleResponsivePreview = function(e) 
{
	var target = $(e.target); 
	var id = target.attr('id')
	id = id.replace('mq_preview[','');
	id = id.replace(']', ''); 	
	this.renderPreview(id);
}


mbResponsive.prototype.renderPreview = function (id) 
{
	var excluded = ['mq_container_width', 'mq_container_float','mq_custom_minwidth','mq_custom_minheight', 'mq_hide'];
	var responsiveMap = this.responsiveMap;
	var self = this; 

	$.each(responsiveMap, function (index) {
	
		var css = this.css; 
		var field_pattern = '[id^="' + index + '["]';  
		var unit_pattern  = '[id^="' + index + '_unit["]';
		var $field = $(field_pattern).eq(id);

		var field_id = $field.attr('id');
		field_id = field_id.replace(/\[[0-9]\]/gi, '');

		if ($.inArray(field_id, excluded) >= 0 )
		{
			return true; // continue
		}
		if (field_id.indexOf('_unit') >= 0 )
		{ 
			return true;
		}

		var value= $field.val();
 		var unit_val = ''; 
 		
		if (typeof responsiveMap[index + '_unit'] !== 'undefined')
		{
			var unit_val = $(unit_pattern).eq(id).val();
		}
		
		
		var data = { css: css }; 
		if (css == 'font-size')
		{
			data.csspart = 'mb-text,mb-text2';
		}	 
			 
		self.mbAdmin.putCSS(data, value + unit_val)
		

	}); 
}



