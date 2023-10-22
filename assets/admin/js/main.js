(function($){
	var media_uploader = null;

	$('#gallery_wrapper').on('click', '#add_gallery_single_row .add', function(){
		media_uploader = wp.media({
			frame:    "post", 
			state:    "insert", 
			multiple: true 
		});
		media_uploader.open();

		media_uploader.on("insert", function(){
			var images = media_uploader.state().get("selection").models

			if ( images.length > 0 ) {
				jQuery.map(images, function(item, index){
					jQuery('#master_box .img_box_container').append('<div class="gallery_single_row"><div class="gallery_area image_container"><input class="meta_image_id" value="'+ item.id +'" type="hidden" name="galleries[]" /><img class="gallery_img_url" src="'+item.changed.url+'" height="55" width="55"/></div><span class="button remove" title="Remove"><svg width="16" height="16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/></svg></span><div class="clear"></div></div>');
						jQuery('#master_box').show();	
				});					
			}

		});
	});

	$('#gallery_wrapper').on('click', '.gallery_area', function(){
		media_uploader = wp.media({
			frame:    "post", 
			state:    "insert", 
			multiple: false
		});
		media_uploader.open();
		media_uploader.on("insert", function(){
			var item = media_uploader.state().get("selection").first().toJSON();
			jQuery(this).find('.meta_image_id').val(item.id);
			jQuery(this).find('.gallery_img_url').attr('src', item.url);
		});
	});

	$('#gallery_wrapper').on('click', '.gallery_single_row .remove', function(){
		$(this).parents('.gallery_single_row').remove();
	});

	$('.img_box_container').sortable();
})(jQuery)