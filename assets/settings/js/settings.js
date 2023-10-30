jQuery(document).ready(function($){
	$('#TMCF_settings_fields_wrap .add').click(function(e){
		e.preventDefault();

		let indexElement = $(this).parents('#TMCF_settings_fields_wrap').find('.fields-wrap tbody tr').length;
		$(this).parents('#TMCF_settings_fields_wrap').find('.fields-wrap tbody').append('<tr><td><input type="text" name="tmcf_fields['+ indexElement +'][name]" value="" placeholder="Name" class="name"></td><td><span class="key-wrap"><input type="text" name="tmcf_fields['+ indexElement +'][key]" value="" placeholder="Key" class="key"><span class="dashicons dashicons-admin-page"></span></span><p class="error">Key is already exist.</p></td> <td><select name="tmcf_fields['+ indexElement +'][type]">'+ $('.sample-fields select').html() +'</select></td></tr>');
	});

	$('#TMCF_settings_fields_wrap').on('blur', '.fields-wrap .name', function(e){
		e.preventDefault();

		$('#TMCF_settings_fields_wrap error').hide();

		var $this = $(this);
		let name = $(this).val();
		let field_name = name.replace(/['"]/g, "");
		$(this).val(field_name);

		let field_key = field_name.replace(/\s/g,'_').toLowerCase();
		let post_id = $(this).parents('#TMCF_settings_fields_wrap').data('post_id');

		jQuery.post(
			tm_settings_object.ajaxurl, 
			{
				'action': 'checking_field_key',
				'field_key': field_key,
				'post_id': post_id
			}, 
			function(response) {

				if ( response.length > 0 ) {
					var return_response = response;

					let field_wrap = $this.parents('tr').siblings();

					if ( field_wrap.length > 0 ) {
						jQuery.map(field_wrap, function(item, index){
							if ( $(item).find('.key').val() == response ) {
								response = response + '_copy';
							}
						});			
					}

					$this.parents('tr').find('.key').val(response);
				} else {
					$this.parents('tr').find('.key').val(field_key);				
				}
			}
		);
	});

	$('#TMCF_settings_fields_wrap').on('blur', '.fields-wrap .key', function(e){
		e.preventDefault();
		$('#TMCF_settings_fields_wrap error').hide();
		
		var $this = $(this);

		let field_key = $(this).val();

		if ( field_key.length > 0 ) {			
			let post_id = $(this).parents('#TMCF_settings_fields_wrap').data('post_id');

			jQuery.post(
				tm_settings_object.ajaxurl, 
				{
					'action': 'checking_field_key',
					'field_key': field_key,
					'post_id': post_id
				}, 
				function(response) {
					if ( response.length > 0 ) {

						let field_wrap = $this.parents('tr').siblings();

						if ( field_wrap.length > 0 ) {
							jQuery.map(field_wrap, function(item, index){
								if ( $(item).find('.key').val() == response ) {
									response = response + '_copy';
								}
							});			
						}

						$this.val(response);
						$this.parents('.key-wrap').siblings('.error').show();

					} else {
						$this.val(field_key);					
					}
				}
			);
		}


	});

	$('#TMCF_settings_fields_wrap').on('click', '.key-wrap .dashicons-admin-page', function(e){
		e.preventDefault();
		var $this = $(this);
		var currentVal = $(this).siblings('input').select();
		document.execCommand("copy");
		$(this).attr('class', 'dashicons dashicons-yes');

		setTimeout(function(){
			$this.attr('class', 'dashicons dashicons-admin-page');
		}, 3000);
	});

});