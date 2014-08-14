o.audit = {};

$(function() {
	$('body').on('oxygenReady', function() {
		$('p.hst-toggle a').click(function() {
			var $target = $('#hst-' + $(this).parent().attr('data-item'));
			if ($target.html() == '') {
				o.post(
					$(this).attr('href'),
					{
						request_type: 'ajax'
					},
					function(data) {
						if (data.result == 'success') {
							$target.hide().html(data.html).slideDown('fast');
						}
					},
					'json'
				);
			}
			else {
				$target.slideToggle('fast');
			}
			return false;
		});
	});
	$(document).on('submit', 'form.hst', function(e) {
		e.preventDefault()
		var url = $(this).attr('action');
		$(this).find('input:checked').each(function() {
			url += '/' + $(this).val();
		});
		location.href = url;
	});
});
