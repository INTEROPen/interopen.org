( function( $ ) {


	var sui = window.Shortcode_UI;
	//var construct = sui.utils.shortcodeViewConstructor; 
	
	sui.views.editAttributeFieldMaxButton = sui.views.editAttributeField.extend( {

	updateButton: function(id)
	{
		this.setValue(id); 
		var preview = this.$el.find('.button_preview');

		var query =  [{
				counter: 1, 
				nonce: shortcodeUIData.nonces.preview,
				post_id: $( '#post_ID' ).val(), 
				shortcode: this.shortcode.formatShortcode(), 
			}];

		// provisional
		wp.ajax.post( 'bulk_do_shortcode', {
			queries: query,
		}).done( function( response ) {
			var button = response[1].response; 
			preview.html(button);
		})		
 
	},
	
	render: function () 
	{
		var self = this;
		var maxMedia = window.maxMedia; 
		
		var data = jQuery.extend( {
			id: 'shortcode-ui-' + this.model.get( 'attr' ) + '-' + this.model.cid,
		}, this.model.toJSON() );

		maxMedia.setCallback( $.proxy(this.updateButton, this) );
  		
		this.$el.html( this.template( data ) );
		this.triggerCallbacks();
	
		$(document).on('mb_media_buttons_open', function () 
		{
				$('.media-modal, .media-modal-backdrop').hide(); 		
				
		}); 
		
		$(document).on('modal_close', function () 
		{
				$('.media-modal, .media-modal-backdrop').show(); 		
		});
		
		this.updateButton( this.model.get( 'value' ) );
		
		return this;
	}
	
	}); // extend
 
} )( jQuery );
 
