o.msgs = {
	/**
	 * Builds the message output.
	 *
	 * @param msgs
	 */
	show: function(msgs) {
		if ( ! msgs.length ) return;

		var msgStr = '';
		for (var i = 0, j = msgs.length; i < j; ++i) {
			msgStr += o.msgs.item(msgs[i]);
		}

		$('ul.msgs').fadeOut();
		$('#str-content').prepend('<ul class="msgs">' + msgStr + '</ul>');
	},

	/**
	 * Builds the specific item view for a message item.
	 *
	 * @param msg
	 * @return string
	 */
	item: function(msg) {
		return '<li class="' + msg.type + '"><span>' + msg.text + '</span></li>';
	},

	/**
	 * Handles the click event to hide messages.
	 */
	handlers: function() {
		$(document).on('click', '.msgs li', function() {
			$(this).slideUp();
		});
	}
};

$(function() {
	$(document).on('oxygenReady', function() {
		o.msgs.handlers();
	});
});
