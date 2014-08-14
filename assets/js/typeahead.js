// fields being attached to must have an ID

(function($) {
$.fn.oTypeAhead = function(options) {
	var defaults = {
		url: null, // will use URL of form if not set
		searchParams: {},
		delay: 300, // ms
		timeout: 2000, // ms
		loading: '<div class="otypeahead-loading">Loading...</div>',
		target: '.otypeahead-results', // jQuery selector
		items: 'li', // jQuery selector
		disableForm: true,
		resultsCallback: function($target) {
			$target.find(options.items).bind('otypeahead-select', function() {
				switch ($(this).data('otypeahead-select-mode')) {
					case 'edit':
						var url = $(this).data('url-edit');
						break;
					case 'view':
						var url = $(this).data('url-view');
						break;
					default:
						var url = $(this).find('a').attr('href');
				}
				if (typeof url != 'undefined') {
					location.href = url;
				}
			});
		}
	};
	var options = $.extend(defaults, options);
	return this.attr('autocomplete', 'off').click(function() {
		return false;
	}).each(function() {
		$(this).data('otypeahead-queue', {}).data('otypeahead-cache', {}).data('otypeahead-key', '');
		var form = $(this).parents('form')[0];
		var $form = $(form);
		if (options.disableForm) {
			$form.submit( function() {
				return false;
			});
		}
// set default URL if none passed in
		var url = options.url;
		if (!url) {
			url = $form.attr('action');
		}
// set default target if none passed in
		var $target = $(options.target);
		if (!$target.size()) {
			$target = $form.find('.otypeahead-results');
		}
		if (!$target.size()) {
// still no target, create one
			$this.after('<div class="otypeahead-results"></div>', function() {
				$target = $form.find('.otypeahead-results');
			});
		}
		$target.addClass('otypeahead-target');
		$('body').click(function() {
			$target.hide();
		});
		$(this).bind('search', function() {
			var $this = $(this);
			var term = $this.val();
// check cache
			var key = url + term;
			$this.data('otypeahead-key', key);
			var queue = $this.data('otypeahead-queue');
			var cache = $this.data('otypeahead-cache');
			if (key in (cache)) {
// set from cache
				$target.html(cache[key]).show();
				options.resultsCallback.call(this, $target);
			}
			else {
				var now = new Date;
				var queued = false;
// check queue
				if (key in (queue) && queue[key].indexOf('pending') != -1) {
					queued = true;
					var requested = queue[key].replace('pending-', '');
					if ((now.getTime() - requested) > options.timeout) {
						queue[key] = 'timeout';
						$this.data('otypeahead-queue', queue);
					}
					else {
						queued = true;
					}
				}
// do search
				if (!queued) {
					$target.html(options.loading).slideDown('fast');
					queue[key] = 'pending-' + now.getTime();
					params = options.searchParams;
					params['key'] = key;
					params[$(this).attr('name')] = term;
					o.post(
						url,
						params,
						function(response) {
// only show if response matches current search
							if ($this.data('otypeahead-key') == response.key) {
								$target.html(response.html);
								options.resultsCallback.call(this, $target);
							}
// refresh data and add to it
							cache = $this.data('otypeahead-cache');
							cache[key] = response.html;
							$this.data('otypeahead-cache', cache);
							queue = $this.data('otypeahead-queue');
							queue[key] = 'complete';
							$this.data('otypeahead-queue', queue);
						},
						'json'
					);
				}
			}
		}).keyup(function(e) {
//console.log(e.which);
			term = $(this).val();
// catch everything excepy up/down arrow
			$current = $target.find(options.items).filter('.otypeahead-current');
			switch (e.which) {
				case 27: // esc
					$target.slideUp('fast', function() {
						$(this).html('');
					});
					break;
				case 16: // shift
					$current.data('otypeahead-select-mode', '');
					break;
				case 13: // enter
					if ($current.size()) {
// select item
						$current.trigger('otypeahead-select').each(function() {
							$target.hide().html('');
						});
					}
					else {
// trigger search
						setTimeout('oTypeAheadTrigger("#' + $(this).attr('id') + '", "' + $.escape(term) + '");', 0);
					}
					return false;
					break;
				case 37: // left
				case 38: // up
				case 39: // right
				case 40: // down
					// do nothing
					break;
				default:
					if (term == '') {
// hide results
						$target.hide().html('');
					}
					else {
// trigger search
						setTimeout('oTypeAheadTrigger("#' + $(this).attr('id') + '", "' + $.escape(term) + '");', options.delay);
					}
					break;
			}
		}).keydown(function(e) {
// catch arrow up/down here
			var $items = $target.find(options.items);
			if ($items.size()) {
				var $current = $items.filter('.otypeahead-current');
				switch (e.which) {
					case 16: // shift
						if ($current.size()) {
							$current.data('otypeahead-select-mode', 'edit');
						}
						break;
					case 40: // down
						if (!$current.size()) {
							$item = $items.filter(':first');
							$item.addClass('otypeahead-current');
							$item.parent().animate({scrollTop: '0'}, 200);
						}
						else {
							var i = 0;
							$items.each(function() {
								if ($(this).hasClass('otypeahead-current')) {
									$(this).removeClass('otypeahead-current')
									var $item = $($items[i + 1]);
									if ($item.size()) {
										$item.addClass('otypeahead-current');
										$scroll = $item.parent();
										oTypeAheadScroll($scroll, $item);
									}
									return false;
								}
								i++;
							});
						}
						return false;
						break;
					case 38: // up
						if (!$current.size()) {
							$item = $items.filter(':last');
							$item.addClass('otypeahead-current');
							oTypeAheadScroll($item.parent(), $item);
						}
						else {
							var i = 0;
							$items.each(function() {
								if ($(this).hasClass('otypeahead-current')) {
									$(this).removeClass('otypeahead-current')
									var $item = $($items[i - 1]);
									if ($item.size()) {
										$item.addClass('otypeahead-current');
										$scroll = $item.parent();
										oTypeAheadScroll($scroll, $item);
									}
									return false;
								}
								i++;
							});
						}
						return false;
						break;
				}
			}
		});
	});
};
})(jQuery);

function oTypeAheadTrigger(sel, term) {
	$field = jQuery(sel);
	if (term == $field.val()) {
		$field.trigger('search');
	}
}

function oTypeAheadScroll($scroll, $item) {
// if hidden, bottom - position to 5 up from visible bottom
// if hidden, top - position to 4 down from visible top
	var itemTop = Math.floor($item.offset().top);
	var itemHeight = Math.ceil($item.height());
	var scrollHeight = Math.ceil($scroll.height());
	var scrollTop = Math.floor($scroll.scrollTop());
	if (itemTop - itemHeight * 2 < 0) {
// console.log('off top: ' + itemTop + ' : ' + scrollTop);
// scroll up - itemTop is negative, so actually subtracting that amount from scrollTop
		$scroll.animate({scrollTop: (scrollTop + itemTop - itemHeight * 8) + 'px'}, 200);
	}
	else if (itemTop > scrollHeight) {
// console.log('off bottom: ' + itemTop + ' : ' + scrollTop);
// scroll down - add scrollHeight + scrollTop to get accurate position
		$scroll.animate({scrollTop: (itemTop - scrollHeight + scrollTop + itemHeight * 7) + 'px'}, 200);
	}
	else {
// console.log('visible: ' + itemTop + ' : ' + $scroll.scrollTop());
// do nothing
	}
}
