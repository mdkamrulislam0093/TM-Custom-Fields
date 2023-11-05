jQuery(document).ready(function($){
	$('#TMCF_settings_fields_wrap .add').click(function(e){
		e.preventDefault();

		let indexElement = $('#TMCF_settings_fields_wrap .fields-item-wrap').length;

		let clone_wrap = $(this).parents('#TMCF_settings_fields_wrap').find('.fields-item-wrap:last-child').clone();

		clone_wrap.find('.field-label input').attr('name', 'tmcf_fields['+ indexElement +'][name]').val('');
		clone_wrap.find('.field-key input').attr('name', 'tmcf_fields['+ indexElement +'][key]').val('');
		clone_wrap.find('.field-type select').attr('name', 'tmcf_fields['+ indexElement +'][type]').val('');
		clone_wrap.find('.field-option tbody tr:gt(0)').remove();

		clone_wrap.find('.field-option tbody tr:first-child input[data-name="name"]').attr('name', 'tmcf_fields['+ indexElement +'][option][0][name]').val('');
		clone_wrap.find('.field-option tbody tr:first-child input[data-name="value"]').attr('name', 'tmcf_fields['+ indexElement +'][option][0][value]').val('');
		clone_wrap.attr('data-index', indexElement);

		clone_wrap.find('.field-heading .name').text('Label');
		clone_wrap.find('.field-heading .copy-key').html('<input type="text" value=\'[tmcf key="key"]\' readonly="">');
		clone_wrap.find('.field-heading .type').text('Type');

		clone_wrap.appendTo($(this).parents('#TMCF_settings_fields_wrap').find('.fields-item-contents'));
		clone_wrap.find('.field-content').slideDown();


		jQuery.map($('#TMCF_settings_fields_wrap .fields-item-wrap'), function(item, index){
			$(item).attr('data-index', index);
		});
	});



	$('#TMCF_settings_fields_wrap').on('blur', '.field-label .name', function(e){
		e.preventDefault();

		$('#TMCF_settings_fields_wrap error').hide();

		var $this = $(this);
		let name = $(this).val();
		let field_name = name.replace(/['"]/g, "");
		$(this).val(field_name);
		$(this).parents('.fields-item-wrap').find('.field-heading .name').text(field_name);

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

					let field_wrap = $this.parents('.fields-item-wrap').siblings();

					if ( field_wrap.length > 0 ) {
						jQuery.map(field_wrap, function(item, index){
							if ( $(item).find('.key').val() == response ) {
								response = response + '_copy';
							}
						});			
					}

					$this.parents('.fields-item-wrap').find('.key').val(response).change();
					$this.parents('.fields-item-wrap').find('.copy-key').text('[tmcf key="'+ response +'"]');
				} else {
					$this.parents('.fields-item-wrap').find('.key').val(field_key).change();				
					$this.parents('.fields-item-wrap').find('.copy-key').text('[tmcf key="'+ field_key +'"]');
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
					console.log(response);
					if ( response.length > 0 ) {

						let field_wrap = $this.parents('.fields-item-wrap').siblings();

						if ( field_wrap.length > 0 ) {
							jQuery.map(field_wrap, function(item, index){
								if ( $(item).find('.key').val() == response ) {
									response = response + '_copy';
								}
							});			
						}

						$this.val(response);
						$this.parents('.fields-item-wrap').find('.copy-key').text('[tmcf key="'+ response +'"]');
						$this.siblings('.error').show();

					} else {
						$this.val(field_key);
						$this.parents('.fields-item-wrap').find('.copy-key').text('[tmcf key="'+ field_key +'"]');					
					}
				}
			);
		}


	});

	$('#TMCF_settings_fields_wrap').on('click', '.field-heading .copy-key', function(e){
		e.preventDefault();
		
		var $this = $(this);
		$(this).find('input').select();
		document.execCommand("copy");

		$(this).addClass('copied');

		setTimeout(function(){
			$(this).removeClass('copied');
		}, 3000);

	});

	$('#TMCF_settings_fields_wrap').on('click', '.field-heading .tmcf-col:not(.key-field)', function(e){
		e.preventDefault();
		$(this).parents('.fields-item-wrap').find('.field-content').slideToggle();
		$(this).parents('.fields-item-wrap').toggleClass('active');
	});

	

	$('#TMCF_settings_fields_wrap').on('click', '.field-option .add_option', function(e){
		e.preventDefault();
		var clone_wrap = $(this).parents('table').find('tbody tr:last-child');

		if ( clone_wrap.length > 0 ) {
			clone_option = clone_wrap.clone();
			var current_key = $(this).parents('table').find('tbody tr').length;
			var current_index = $(this).parents('.fields-item-wrap').attr('data-index');
			
			jQuery.map(clone_option.find('input'), function(item, index){
				var name = $(item).data('name');
				$(item).attr('name', 'tmcf_fields['+ current_index +'][option]['+ current_key +']['+ name +']' );
			});

			clone_option.appendTo($(this).parents('table').find('tbody'))
			$(this).parents('table').find('tbody tr:last-child .remove-option').html('<span class="dashicons dashicons-trash"></span>');
		}
	});

	$('#TMCF_settings_fields_wrap').on('click', '.field-control-option .dashicons-trash', function(e){
		e.preventDefault();
		$(this).parents('tr').remove();
	});
	 
	$('#TMCF_settings_fields_wrap').on('click', '.field-heading .tmcf-col .dashicons-trash', function(e){
		e.preventDefault();

		$(this).parents('.fields-item-wrap').remove();

		jQuery.map($('#TMCF_settings_fields_wrap .fields-item-wrap'), function(item, index){
			$(item).attr('data-index', index);
		});		
	});

	$('#TMCF_settings_fields_wrap').on('change', '.field-type select', function(e){
		e.preventDefault();
		$(this).parents('.fields-item-wrap').find('.field-heading .type').text($(this).val());

		if ( $(this).val() == 'select' || $(this).val() == 'checkbox' || $(this).val() == 'radio' ) {
			$(this).parents('.field-content').find('.field-option').slideDown();
			$(this).parents('.field-content').find('.field-placeholder').removeClass('active');
		} else {
			$(this).parents('.field-content').find('.field-option').slideUp();
			$(this).parents('.field-content').find('.field-placeholder').addClass('active');
		}
	});

});