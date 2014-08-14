o.permissions = {};
o.permissions.form = function() {
	$(document).on('change', '#edit_permissions .elm-select', function() {
		var $fieldset = $(this).closest('fieldset');
		var role_id = $(this).val();
		switch (role_id) {
			case '1': // superadmin, select all
				$fieldset.find('fieldset.permissions input:checkbox').prop({
					'checked': true,
					'disabled': true
				}).end().find('fieldset.permissions .select-all').hide();
				break;
			case '-': // custom, enable checkboxes
				$fieldset.find('fieldset.permissions input:checkbox').prop('disabled', false).end()
					.find('fieldset.permissions .select-all').show();
				break;
			default: // role selected, set and disable checkboxes
				var values = o.roles['id_' + role_id].split(',');
				var sel = '';
				for (var i = 0; i < values.length; i++) {
					if (sel != '') {
						sel += ', ';
					}
					sel += 'input:checkbox[value="' + values[i].replace(':', '\\:') + '"]';
				}
				$fieldset.find('fieldset.permissions input:checkbox').prop({
					'checked': false,
					'disabled': true
				}).end().find('fieldset.permissions .select-all').hide();
				$(sel, 'fieldset.permissions').prop('checked', true);
		}
	});
	$('#edit_permissions .elm-select').each(function(){
		if ($(this).val() !== '' || $(this).val() !== '0') {
			$(this).trigger('change');
		}
	});
	$(document).on('click', '.permissions_toggle a, a.permissions_role, .select-all a', function() {
		var values = $(this).attr('rel').split(',');
		var sel = '';
		for (var i = 0; i < values.length; i++) {
			if (sel != '') {
				sel += ', ';
			}

			var input_value = 'input:checkbox[id="' + values[i] + '"]';
			var input_id = '#'+values[i];
			if ($(input_value).length) {
				sel += input_value;
			}
			else if ($(input_id)) {
				sel += input_id;
			}
		}
		if ($(this).hasClass('all')) {
			$(sel).prop('checked', true);
		}
		else if ($(this).hasClass('none')) {
			$(sel).prop('checked', false);
		}
		else {
			$('input:checkbox').prop('checked', false);
			$(sel).prop('checked', true);
		}
		return false;
	});
	$('#glb-search-box a').click(function(e){
		e.preventDefault();
		var $tar = $($(this).attr('href'));
		if ($tar.is(':visible')) {
			$tar.slideUp();
			$(this).html('Show Options');
		}
		else {
			$tar.slideDown();
			$(this).html('Hide Options');
		}
	});
};

$(function(){
	$('body').on('oxygenReady', function() {
		$('#role_add #type, #role_edit #type').change(function(){
			var $this = $(this);
			$('.permissions-group').each(function(){
				if ($(this).attr('id') == 'permissions_group_'+$this.val()) {
					$(this).show();
				} else {
					$(this).hide().find('input:checkbox').prop('checked', false);
				}
			});
		});

		$('#role_add #type, #role_edit #type').trigger('change');
	});
});
