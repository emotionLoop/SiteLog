/** Based on original Anchor Slider by Cedric Dugas **/
/** http://www.position-absolute.com **/

// Animate going to an anchor
jQuery.fn.wAnchor = function(options) {
	var $ = jQuery;
	var opts = $.extend({}, $.fn.wAnchor.defaults, options);

	return this.each(function() {
		var caller = this;

		$(caller).click(function(e) {
			e.preventDefault();
			
			var elToGo = 'wAnchor-' + $(caller).attr('href').replace(window.location.href,'');
			var tmpi = elToGo.indexOf('#');
			if (tmpi == -1) {//-- There is a # on the url already
				elToGo = $(caller).attr('href');
				tmpi = elToGo.indexOf('#');
			}
			elToGo = '#wAnchor-' + elToGo.substr(tmpi + 1);
			if ($(elToGo).length == 0) return false;
			
			var topVal = $(elToGo).offset().top;
			$('html:not(:animated),body:not(:animated)').animate({ scrollTop: topVal }, opts.slideSpeed, function() {
				//window.location.hash = elToGo.replace('wAnchor-','');
			});
		  	return false;
		});
		return false;
	});
};

jQuery.fn.wAnchor.defaults = {
	slideSpeed: 'slow'
};

jQuery(document).ready(function($) {
	$('a.wAnchor').wAnchor();
});