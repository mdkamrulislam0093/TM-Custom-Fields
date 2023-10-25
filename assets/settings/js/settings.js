jQuery(document).ready(function($){
	$('#TMCF_settings_fields_wrap .add').click(function(e){
		e.preventDefault();

		let indexElement = $(this).parents('#TMCF_settings_fields_wrap').find('.fields-wrap tbody tr').length;
		$(this).parents('#TMCF_settings_fields_wrap').find('.fields-wrap tbody').append('<tr><td><input type="text" name="tmcf_fields['+ indexElement +'][name]" value="" placeholder="Name" class="name"></td><td><input type="text" name="tmcf_fields['+ indexElement +'][key]" value="" placeholder="Key" class="key" readonly></td> <td><select name="tmcf_fields['+ indexElement +'][type]">'+ $('.sample-fields select').html() +'</select></td></tr>');
	});

	$('#TMCF_settings_fields_wrap').on('blur', '.fields-wrap .name', function(e){
		e.preventDefault();
		let name = $(this).val();
		let clean_name = name.replace(/['"]/g, "");
		$(this).val(clean_name);


		let clean_key = clean_name.replace(/\s/g,'');
		$(this).parents('tr').find('.key').val(clean_key.toLowerCase());
	});
});