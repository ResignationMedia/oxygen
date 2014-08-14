o.search = {};

o.search.bind = function() {
	$('#hdr-search  #s-terms').oTypeAhead({
		searchParams: {
			search: 'search',
			request_type: 'ajax'
		},
		loading: o.spinner(),
		items: 'ul li, p.s-tips-view-all',
		target: '#hdr-search .s-tips',
		disableForm: false
	});
};

$(function() {
	$('body').on('oxygenReady', function() {
		o.search.bind();
	});
});
