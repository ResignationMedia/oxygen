o.list = {};
o.list.current = null;
o.list.spinner = function() {
	this.current.css('position', 'relative').append('<div class="grid-loading"></div>').find('.grid-loading').css({
		height: this.current.height() + 'px',
		width: this.current.width() + 'px',
		opacity: 0
	}).fadeTo('fast', 0.5);
};
o.list.navigate = function() {
	this.spinner();
	var filter = {};
	if ($('.grid-filter form').length) {
		filter = $('.grid-filter form').serialize();
	}

	$(this.current).trigger('grid-filter', filter);
	o.post(
		this.current.data('url') + '/' + this.current.data('page') + '/' + this.current.data('sort') + '/' + this.current.data('order'),
		filter,
		function(data) {
			if (data.result == 'success') {
				// @deprecated so that we can hack in HTML5 support later through .append()
				//o.list.current.replaceWith(data.html);
				$html = $(data.html);
				o.list.current.trigger('listUnloaded');
				o.list.current.hide().after($html).remove();
				o.list.init($html[0]);
				o.listFilter.init();
			}
		},
		'json'
	);
};
o.list.init = function(ele) {
	ele = ele || '.oxygen-grid';
	$(ele).each(function() {
		$(this).on('click', '.sort-by-btn', function(e) {
			e.preventDefault();
		}).on('click', '.pgn a, .list-sort a', function() {
			o.list.current = $(this).closest('.oxygen-grid[data-url]');
			var page = $(this).data('page');
			var sort = $(this).data('sort');
			if (typeof sort != 'undefined') {
				o.list.current.data({
					'sort': sort,
					'page': 1
				});

				var order = $(this).attr('data-order');
				if (typeof order != 'undefined') {
					o.list.current.data({
						'order': order
					});
				}
			}
			else if (typeof page != 'undefined') {
				o.list.current.data({
					'page': page
				});
			}
			o.list.navigate();
			return false;
		});
		$(this).trigger('listLoaded');
	});
};
o.listFilter = {};
o.listFilter.current = null;
o.listFilter.init = function() {
	var $filter = $('.grid-filter');

	$('.filter').click(function(e){
		e.preventDefault();
		
		o.listFilter.current = $(this);
		o.list.current = $(this).parents('.oxygen-grid[data-url]');

		if ($filter.html() == '') {
			$.get($(this).attr('href'), {}, function(response){
				$filter.html(response.html);
				o.listFilter.action();
				datePicker();
			}, 'json');
		} else {
			o.listFilter.action();
		}
	});
};
o.listFilter.action = function() {
	var $filter = $('.grid-filter');
	if ($filter.is(':hidden')) {
		this.current.html('Cancel').addClass('lnk-delete');
	} else {
		this.current.html('Filter').removeClass('lnk-delete');
	}

	$filter.slideToggle();
};
$(function() {
	$('body').on('oxygenReady', function() {
		o.list.init();
		o.listFilter.init();
	});
});
