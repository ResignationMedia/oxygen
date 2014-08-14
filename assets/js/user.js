o.user = {};
o.user.apiKeyBtn = null;
o.user.apiKeyTarget = null;

o.user.apiAccess = function() {
	$(document).on('click', '.api_key_reset', function() {
		o.user.apiKeyBtn = $(this);
		o.user.apiKeyTarget = $('#' + $(this).data('target'));
		o.user.apiKeyTarget.html(o.spinner());
		o.user.apiKeyBtn.prop('disabled', true);
		o.post(
			o.user.apiKeyBtn.data('url'),
			{
				save: true,
				nonce: $('#nonce').val(),
				id: o.user.apiKeyBtn.data('id')
			},
			function (data) {
				if (data.result == 'success') {
					o.user.apiKeyTarget.html(data.html);
					o.user.apiKeyBtn.prop('disabled', false);
				}
				else {
					alert('Sorry, something unexpected happened.');
				}
			},
			'json'
		);
	});
};

$(function() {
	$('body').on('oxygenReady', function() {
		o.user.apiAccess();
	});
});
