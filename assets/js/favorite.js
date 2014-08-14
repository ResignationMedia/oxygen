o.fav = {};
o.fav.currentElem = null;

o.fav.add = function() {
	o.fav.currentElem.addClass('spinner');
	o.fav.formHide();
	var $favForm = $('#fav-form form');
	o.post(
		$favForm.attr('action'),
		{
			url: $favForm.find('input[name=url]').val(),
			title: $favForm.find('input[name=title]').val(),
			key: $favForm.find('input[name=key]').val()
		},
		function(data) {
			o.fav.currentElem.removeClass('spinner');
			switch (data.result) {
				case 'success':
					o.fav.currentElem.addClass('fav').attr('title', 'Remove from Favorites');
					$('#fav').replaceWith(data.html);
					o.fav.itemRemoveActions();
					break;
				case 'error':
					alert('Sorry, please try again.');
					break;
			}
		},
		'json'
	);
};

o.fav.remove = function($elem) {
	if (o.fav.currentElem == null) {
		o.fav.currentElem = $('.fav-action a[data-url="' + $elem.attr('data-url') + '"]');
	}
	o.fav.currentElem.addClass('spinner');
	var $favForm = $('#fav-form form');
	o.post(
		$favForm.data('url-delete'),
		{
			url: $elem.data('url')
		},
		function(data) {
			o.fav.currentElem.removeClass('spinner');
			switch (data.result) {
				case 'success':
					o.fav.currentElem.removeClass('fav').attr('title', 'Add to Favorites');
					$('#fav a[href*="' + data.url + '"]').parent().slideUp(function() {
						$('#fav').replaceWith(data.html);
						o.fav.itemRemoveActions();
					});
					break;
				case 'error':
					alert('Sorry, please try again.');
					break;
			}
		},
		'json'
	);
};

o.fav.itemRemoveActions = function() {
	$('#fav .fav-remove').unbind('click').click(function() {
		o.fav.remove($(this));
	});
};

o.fav.formOpen = function() {
	if ($('#fav-form:visible').size()) {
		return true;
	}
	else {
		return false;
	}
};

o.fav.formHide = function() {
	$('#fav-form').hide('fast');
};

o.fav.formShow = function($elem) {
	var offset = $elem.offset();
	var $favForm = $('#fav-form');
	$favForm.css({
		top: (offset.top + $elem.height() - 3),
		left: (offset.left - 12)
	}).find('input[name=url]').val($elem.attr('data-url')).end()
		.find('input[name=title]').val($elem.attr('data-title')).end()
		.find('input[name=key]').val($elem.attr('data-group')).end()
	.show('fast', function() {
		$favForm.find('.fav-form-title:text').focus().select();
	});
};

$(function($) {
	$('body').on('oxygenReady', function() {
		$('.fav-action a').click(function() {
	// handle multiple favoritable items - only one form allowed to be open at once
			if (o.fav.currentElem != $(this)) {
				o.fav.formHide();
				o.fav.currentElem = $(this);
			}
			if ($(this).is('.fav')) {
				o.fav.remove($(this));
			}
			else if (o.fav.formOpen()) {
	// hide if already open for this item (act as toggle)
				o.fav.formHide();
			}
			else {
				o.fav.formShow($(this));
			}
			return false;
		});

		$('#fav-form').click(function(event) {
			event.stopPropagation();
		}).submit(function() {
			o.fav.add();
			return false;
		});
		$('body').click(function() {
			if (o.fav.formOpen()) {
				o.fav.formHide();
			}
		}).keypress(function(e) {
			switch (e.keyCode) {
				case 27:
				if (o.fav.formOpen()) {
					o.fav.formHide();
				}
				break;
			}
		});
		o.fav.itemRemoveActions();
	});
});
