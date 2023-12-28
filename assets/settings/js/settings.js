;(function ($){
	$('#TMCF_settings_fields_wrap .add').click(function(e){
		e.preventDefault();

		let indexElement = $('#TMCF_settings_fields_wrap .fields-item-wrap').length;

		let cloneWrap = $(this).parents('#TMCF_settings_fields_wrap').find('.fields-item-wrap:last-child').clone();

		cloneWrap.find('.field-label input').attr('name', 'tmcf_fields['+ indexElement +'][name]').val('');
		cloneWrap.find('.field-key input').attr('name', 'tmcf_fields['+ indexElement +'][key]').val('');
		cloneWrap.find('.field-type select').attr('name', 'tmcf_fields['+ indexElement +'][type]').val('');
		cloneWrap.find('.field-placeholder input').attr('name', 'tmcf_fields['+ indexElement +'][placeholder]').val('');
		cloneWrap.find('.field-option tbody tr:gt(0)').remove();

		cloneWrap.find('.field-option tbody tr:first-child input[data-name="name"]').attr('name', 'tmcf_fields['+ indexElement +'][option][0][name]').val('');
		cloneWrap.find('.field-option tbody tr:first-child input[data-name="value"]').attr('name', 'tmcf_fields['+ indexElement +'][option][0][value]').val('');
		cloneWrap.attr('data-index', indexElement);

		cloneWrap.find('.field-heading .name').text('Label');
		cloneWrap.find('.field-heading .copy-key').html('<input type="text" value=\'[tmcf key="key"]\' readonly="">');
		cloneWrap.find('.field-heading .type').text('Type');

		cloneWrap.appendTo($(this).parents('#TMCF_settings_fields_wrap').find('.fields-item-contents'));
		cloneWrap.find('.field-content').slideDown();

		jQuery.map($('#TMCF_settings_fields_wrap .fields-item-wrap'), function(item, index){
			$(item).find('.field-label input').attr('name', 'tmcf_fields['+ index +'][name]');
			$(item).find('.field-key input').attr('name', 'tmcf_fields['+ index +'][key]');
			$(item).find('.field-type select').attr('name', 'tmcf_fields['+ index +'][type]');
			$(item).find('.field-placeholder input').attr('name', 'tmcf_fields['+ index +'][placeholder]');

			$(item).find('.field-option tbody tr:first-child input[data-name="name"]').attr('name', 'tmcf_fields['+ index +'][option][0][name]');
			$(item).find('.field-option tbody tr:first-child input[data-name="value"]').attr('name', 'tmcf_fields['+ index +'][option][0][value]');
			$(item).attr('data-index', index);

		});
	});



	$('#TMCF_settings_fields_wrap').on('blur', '.field-label .name', function(e){
		e.preventDefault();

		$('#TMCF_settings_fields_wrap error').hide();
		$('#TMCF_settings_fields_wrap').find('.field-type select').removeClass('active');


		var $this = $(this);
		let name = $(this).val();
		let fieldName = name.replace(/['"]/g, "");
		$(this).val(fieldName);
		$(this).parents('.fields-item-wrap').find('.field-heading .name').text(fieldName);

		let field_key = fieldName.replace(/\s/g,'_').toLowerCase();
		let post_id = $(this).parents('#TMCF_settings_fields_wrap').data('post_id');

		jQuery.post(
			tm_settings_object.ajaxurl, 
			{
				'action': 'checking_field_key',
				'nonce': tm_settings_object.nonce,
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
					$this.parents('.fields-item-wrap').find('.copy-key').html('<input type="text" value=\'[tmcf key="'+ response +'"]\' readonly="">');
				} else {
					$this.parents('.fields-item-wrap').find('.key').val(field_key).change();				
					$this.parents('.fields-item-wrap').find('.copy-key').html('<input type="text" value=\'[tmcf key="'+ field_key +'"]\' readonly="">');
				}

				$selectType = $this.parents('.fields-item-wrap').find('.field-type select');

				if ( $selectType != undefined && $selectType.val() == '' ) {
					$selectType.addClass('active');
				}
			}
		);
	});

	$('#TMCF_settings_fields_wrap').on('blur', '.fields-wrap .key', function(e){
		e.preventDefault();
		$('#TMCF_settings_fields_wrap .error').hide();
		$('#TMCF_settings_fields_wrap').find('.field-type select').removeClass('active');
		
		var $this = $(this);
		let field_key = $(this).val();

		if ( field_key.length > 0 ) {			
			let post_id = $(this).parents('#TMCF_settings_fields_wrap').data('post_id');

			jQuery.post(
				tm_settings_object.ajaxurl, 
				{
					'action': 'checking_field_key',
					'nonce': tm_settings_object.nonce,
					'field_key': field_key,
					'post_id': post_id
				}, 
				function(response) {
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
						$this.parents('.fields-item-wrap').find('.copy-key').html('<input type="text" value=\'[tmcf key="'+ response +'"]\' readonly="">');

						if ( field_key != response ) {
							$this.siblings('.error').show();
						}
					} else {
						$this.val(field_key);
						$this.parents('.fields-item-wrap').find('.copy-key').html('<input type="text" value=\'[tmcf key="'+ field_key +'"]\' readonly="">');
					}

					$selectType = $this.parents('.fields-item-wrap').find('.field-type select');

					if ( $selectType != undefined && $selectType.val() == ''  ) {
						$selectType.addClass('active');
					}					
				}
			);
		}
	});

	$('#TMCF_settings_fields_wrap').on('change', '.field-type select', function(e){
		if ( $(this).val() != '' ) {
			$(this).removeClass('active');
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
			$(item).find('.field-label input').attr('name', 'tmcf_fields['+ index +'][name]');
			$(item).find('.field-key input').attr('name', 'tmcf_fields['+ index +'][key]');
			$(item).find('.field-type select').attr('name', 'tmcf_fields['+ index +'][type]');
			$(item).find('.field-placeholder input').attr('name', 'tmcf_fields['+ index +'][placeholder]');
			$(item).find('.field-option tbody tr:first-child input[data-name="name"]').attr('name', 'tmcf_fields['+ index +'][option][0][name]');
			$(item).find('.field-option tbody tr:first-child input[data-name="value"]').attr('name', 'tmcf_fields['+ index +'][option][0][value]');
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

	$('#TMCF_settings_fields_wrap').on('blur', '.field-control .field_option_name', function(e){
		e.preventDefault();

		let optionName = $(this).val();
		let optionNameUn = optionName.replace(/['"]/g, "");
		let optionVal = optionNameUn.replace(/\s/g,'_').toLowerCase();

		let options = $(this).parents('tr').siblings();

		if ( options.length > 0 ) {
			jQuery.map(options, function(item, index){
				if ( $(item).find('.field_option_value').val() == optionVal ) {
					optionVal = optionVal + '_copy';
				}
			});
		}

		$(this).parents('tr').find('.field_option_value').val(optionVal);
	});

	$('#TMCF_settings_fields_wrap').on('blur', '.field-control .field_option_value', function(e){
		e.preventDefault();

		let optionVal = $(this).val();


		let options = $(this).parents('tr').siblings();

		if ( options.length > 0 ) {
			jQuery.map(options, function(item, index){
				if ( $(item).find('.field_option_value').val() == optionVal ) {
					optionVal = optionVal + '_copy';
				}
			});
		}

		$(this).val(optionVal);
	});
	
})(jQuery);