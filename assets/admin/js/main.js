(function($){
	var media_uploader = null;

	$('.gallery_wrapper').on('click', '.add_gallery_single_row .add', function(){
		media_uploader = wp.media({
			frame:    "post", 
			state:    "insert", 
			multiple: true 
		});
		media_uploader.open();

		var current_field = $(this).parents('.gallery_wrapper').data('name');


		media_uploader.on("insert", function(){
			var images = media_uploader.state().get("selection").models

			if ( images.length > 0 ) {
				jQuery.map(images, function(item, index){
					jQuery('.master_box .img_box_container').append('<div class="gallery_single_row"><div class="gallery_area image_container"><input class="meta_image_id" value="'+ item.id +'" type="hidden" name="'+ current_field +'[]" /><img class="gallery_img_url" src="'+item.changed.url+'" height="55" width="55"/></div><span class="button remove" title="Remove"><span class="dashicons dashicons-trash"></span></span><div class="clear"></div></div>');
						jQuery('.master_box').show();	
				});					
			}

		});
	});

	$('.gallery_wrapper').on('click', '.gallery_area', function(){
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

	$('.gallery_wrapper').on('click', '.gallery_single_row .remove', function(){
		$(this).parents('.gallery_single_row').remove();
	});

	$('.img_box_container').sortable();




	$('.tmcf_field_wrapper').on('click', '.copy-key-wrap', function(e){
		e.preventDefault();
		var $this = $(this);
		var currentVal = $(this).find('.copy-key').text();
		var copy_input = $('<input>');
		$('body').append(copy_input);
		copy_input.val(currentVal).select();
		document.execCommand("copy");
		copy_input.remove();
		
		$(this).find('.dashicons-admin-page').attr('class', 'dashicons dashicons-yes');

		setTimeout(function(){
			$this.find('.dashicons-admin-page').attr('class', 'dashicons dashicons-admin-page');
		}, 3000);
	});


})(jQuery)