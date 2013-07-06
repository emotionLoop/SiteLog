// Show an Error for a field
jQuery.fn.wError = function(options) {
	var $ = jQuery;
	var opts = $.extend({}, $.fn.wError.defaults, options);

	this.each(function() {
		var theID = 'wError-' + $(this).attr('id');

		if ($('#' + theID).length == 0 || $('#' + theID).html() != opts.msgTxt) {
			if ($('#' + theID).length != 0) $('#' + theID).click();
			$(this).after('<div id="' + theID + '" class="wError">' + opts.msgTxt + '</div>');
			$('#' + theID).fadeIn(opts.fadeSpeed);
			$(this).addClass('error');
		}
	});

	$('.wError').live('click',function() {
		var pID = $(this).attr('id').replace('wError-','');
		$(this).fadeOut('fast', function() {
			$(this).remove();
			$('#' + pID).removeClass('error');
		});
	});
};

jQuery.fn.wError.defaults = {
	msgTxt: 'Required',
	fadeSpeed: 'fast'
};