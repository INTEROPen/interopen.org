/*function mbAddListener(element, type, callback) {
 if (element.addEventListener) element.addEventListener(type, callback);
 else if (element.attachEvent) element.attachEvent('on' + type, callback); 
}*/

jQuery(document).ready(function($){
 
	function mbSocialShare(e)
	{
		e.preventDefault();
 		var data = $(this).data('popup'); 
 		
		var url = $(this).find('a').attr("href"); 
		var width = data.width; 
		var height = data.height; 
		var left   = ($(window).width()  - width)  / 2;
		var top    = ($(window).height() - height) / 2;
			    		
		var params = "toolbar=0,scrollbars=1, location=0, width=" + width + ",height=" + height + ",left=" + left + ",top=" + top; 
		var popup = window.open(url, 'mb-social-share-window', params);
		popup.focus();
		mbSocialTrack();
	}

	// track a click ( not a share! )  	
	mbSocialTrack = function (data)
	{
		
	
	}
	

	function mbGetShareCount(el, data)
	{
		var ajax_url = mb_ajax.ajaxurl;
		var share_url = data.share_url;
		var network = data.network; 
		var collection_id = data.collection_id;		 
		var nonce = data.nonce;
		
		var block_data = {
			share_url: share_url, 
		 	network: network,
		};
		
		var data = { 
			block_name : 'social', 
			block_action : 'ajax_get_count', 
			action : 'mbpro_collection_block_front', 
			collection_id: collection_id, 
		 	collection_type: 'social', 	
			block_data: block_data,
		};

		$.ajax({
			type: "POST",
			url: ajax_url, 
			data: data, 
			success: function (result) { 
				mbPutShareCount(result, el);
			
			},
		});
	}
	
	function mbPutShareCount(result, el)
	{
 
		var resJSON = $.parseJSON(result); 
		
		var data = $(el).data('onload'); 
		var threshold = parseInt(data.count_threshold); 
		var count = parseInt(resJSON.data.count); 
 
			
		if (count >= threshold) 
		{
			var text = data.text; 
			var text2 = data.text2; 
 	
			text = text.replace('{count}', count); 
			text = text.replace('{c}', count); 
			text2 = text2.replace('{count}', count); 
			text2 = text2.replace('{c}', count); 
			
			$(el).find('.mb-text').html(text);
			$(el).find('.mb-text2').html(text2); 
		
		}
	
	}
	
	function mb_init()
	{
		if (typeof($) !== 'function') 
		{
			console.log('Maxbuttons : Jquery load conflict.'); 
			return; 
		}
		
		$('.maxcollection .mb-collection-item[data-popup]').on('click',  mbSocialShare ); 
		
		$('.maxcollection .mb-collection-item[data-onload]').each( function () { 
			var collection_id = $(this).parents('.maxcollection').data('collection'); 
 					
 			var data = $(this).data('onload');	
 			data.collection_id = collection_id; 
 			mbGetShareCount(this, data);
 			
		}); 
	}		
	mb_init(); // init JS instructions 
});	
