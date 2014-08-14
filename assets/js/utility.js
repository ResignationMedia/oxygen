(function($) {
	$.fn.oSetOptions = function(options) {
		this.each(function() {
			$(this).find('option').remove().end().append(options).prop('disabled', false);
		});
		return this;
	};
})(jQuery);

(function($) {
	$.fn.oSetOptionsLoading = function(display) {
		if (typeof display == 'undefined') {
			display = 'Loading...';
		}
		this.each(function() {
			$(this).find('option').remove().end().append('<option>' + display + '</option>').prop('disabled', true);
		});
		return this;
	};
})(jQuery);

// jquery.escape 1.0 - escape strings for use in jQuery selectors
// http://ianloic.com/tag/jquery.escape
// Copyright 2009 Ian McKellar <http://ian.mckellar.org/>
// Just like jQuery you can use it under either the MIT license or the GPL
// (see: http://docs.jquery.com/License)
(function() {
	escape_re = /[#;&,\.\+\*~':"!\^\$\[\]\(\)=>|\/\\]/;
	jQuery.escape = function jQuery$escape(s) {
		var left = s.split(escape_re, 1)[0];
		if (left == s) return s;
		return left + '\\' + s.substr(left.length, 1) + jQuery.escape(s.substr(left.length + 1));
	}
})();