o = {};

o.roles = {};

o.auth = {};

o.ajaxCallback = function () {
};

o.ajaxResponseCheck = function (response) {
	switch (response.result) {
		case 'logged-out':
			$('body').append('<div id="logged-out">' + response.html + '</div>');
			o.lightbox({w:480, h:400}, '#logged-out', {modal:1});
			$('#login').submit(
				function () {
					o.ajax(
						'POST',
						$(this).attr('action'),
						{
							username: $(this).find('#username').val(),
							password: $(this).find('#password').val(),
							remember_me: $(this).find('#remember_me').val(),
							o_action: $(this).find('#o_action').val(),
							go: $(this).find('#go').val(),
							request_type: 'ajax'
						},
						function (response) {
							$.closeDOMWindow();
							o.ajaxCallback.call(this, response);
						},
						'json',
						false
					);
// hide login form, replace with spinner
					$(this).after(o.spinner()).remove();
					return false;
				}).find('#username').focus();
			return false;
			break;
		case 'permission-denied':
		case 'fail':
			$('.spinner').remove();
			$('body').append('<div id="ajax-msg">' + response.html + '</div>');
			o.lightbox('sm', '#ajax-msg', {modal:1});
			$('.closeDOMWindow').click(function () {
				$('#DOMWindow, #DOMWindowOverlay').remove();
				$('#ajax-msg').remove();
			});
			return false;
			break;
	}
	return true;
};

o.ajax = function (method, url, params, callback, type, saveCallback) {
	if (typeof type == 'undefined') {
		type = 'json';
	}
	if (typeof saveCallback == 'undefined') {
		saveCallback = true;
	}
	if (saveCallback) {
		o.ajaxCallback = callback;
	}
	$.ajax({
		type: method,
		url: url,
		data: params,
		success: function (response) {
			if (o.ajaxResponseCheck(response)) {
				callback.call(this, response);
			}
		},
		dataType: type
	});
};

o.get = function (url, params, callback, type) {
	o.ajax('GET', url, params, callback, type);
};

o.post = function (url, params, callback, type) {
	if (typeof params == 'string') {
		if (params != '') {
			params += '&';
		}
		params += 'request_type=ajax';
	}
	else {
		params['request_type'] = 'ajax';
	}
	o.ajax('POST', url, params, callback, type);
};

o.lightbox = function (size, sel, settings) {
	var h;
	var w;
	if (typeof size == 'string') {
		switch (size) {
			case 'sm':
			case 'small':
				h = 220;
				w = 320;
				break;
			case 'med':
			case 'medium':
				h = 460;
				w = 650;
				break;
			case 'lg':
			case 'large':
				h = 600;
				w = 800;
				break;
		}
	}
	else {
		h = size.h;
		w = size.w;
	}
	if (typeof settings == 'undefined') {
		settings = {};
	}
	var defaults = {
		borderSize: '0',
		height: h,
		overlayColor: '#ddd',
		width: w,
		windowBGColor: 'false',
		windowSourceID: sel
	};
	$.extend(defaults, settings);
	if ($('#DOMWindow').length) {
		$('#DOMWindow').remove();
	}
	$.openDOMWindow(defaults);
	$('#DOMWindow form:visible:first:has(input:visible) input[type!=checkbox][type!=radio][type!=file]:not(:submit):not(:button):visible:first').focus();
};

o.spinner = function (text) {
	if (typeof text == 'undefined') {
		text = 'Loading...';
	}
	return '<div class="spinner"><span>' + text + '</span></div>';
};

$(function () {
	$('body').on('oxygenReady', function () {
		$login = $('#login');
		$username = $login.find('.username');
		if ($username.size()) {
			$username.focus();
		}
		else {
			$('#str-content form:visible:first:has(:input:visible) :input[type!=checkbox][type!=radio][type!=file]:not(:submit):not(:button):visible:first').focus();
		}
		o.permissions.form();
		$('body').keyup(function (e) {
			switch (e.which) {
				case 27: // esc
					$('#DOMWindow .closeDOMWindow').click();
					break;
			}
		});

		// Change Detection and Warning
		//
		// give 500 ms delay before detecting changes to allow
		// allow programmatic changes to input values
// 		setTimeout(function() {
// 			$("form:not(#hdr-search) :input").on('keydown change', function(e) {
// 				$(window).on('beforeunload', function(e) {
// 					return "It seems you have unsaved changes.  If you continue they will be lost";
// 				});
// 				$(":input").off('keydown change');
// 			});
// 
// 			$("form").on('submit', function() {
// 				$(window).off('beforeunload');
// 			});
// 		}, 500);

		// Date Picker
		$('body').on('focus', '.date-pick', function () {
			if (!$(this).hasClass('date-pick-applied')) {
				$(this).datepicker({
					dateFormat: 'yy-mm-dd',
					showTime: false,
					showSecond: true,
					timeFormat: 'hh:mm:ss T',
					stepMinute: 5,
					stepSecond: 15,
					numberOfMonths: 2,
					nextText: '&rarr;',
					prevText: '&larr;'
				});

				$(this).addClass('date-pick-applied').trigger('focus');
			}
		});
	});
});
