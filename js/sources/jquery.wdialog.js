// Alert Call
function wAlert(msg,callback) {
	var popupVars = {
		'content': '<p>' + msg + '</p>',
		'options': ''
	};

	popupVars.options = '<a href="javascript:void(0);" class="ok">' + lng.common.btOk + '</a>';

	getJQTmpl('prompt',popupVars,function(ptpl) {
		var popupHTML = ptpl;

		$.fancybox({
			'opacity': true,
			'modal': true,
			'overlayOpacity': 0.5,
			'padding': 0,
			'content': popupHTML,
			'onComplete': function() {
				$('#prompt a.ok').click(function() {
					$.fancybox.close();
					if ($.isFunction(callback)) callback(true);
				});
			}
		});
	});
}

// Confirm Call
function wConfirm(msg,callback) {
	var popupVars = {
		'content': '<p>' + msg + '</p>',
		'options': ''
	};

	popupVars.options = '<a href="javascript:void(0);" class="cancel">' + lng.common.btCancel + '</a>' + "\n";
	popupVars.options += '<a href="javascript:void(0);" class="ok">' + lng.common.btOk + '</a>';

	getJQTmpl('prompt',popupVars,function(ptpl) {
		var popupHTML = ptpl;

		$.fancybox({
			'opacity': true,
			'modal': true,
			'overlayOpacity': 0.5,
			'padding': 0,
			'content': popupHTML,
			'onComplete': function() {
				$('#prompt a.cancel').click(function() {
					if ($.isFunction(callback)) callback(false);
					$.fancybox.close();
				});

				$('#prompt a.ok').click(function() {
					if ($.isFunction(callback)) callback(true);
					$.fancybox.close();
				});
			}
		});
	});
}

// wDialog Call
function wDialog(xHtml, callback, width, height, okLabel, closeOnOk, useButtons) {
	if (closeOnOk == null || closeOnOk == 'undefined') { closeOnOk = true; }
	
	if (!okLabel) okLabel = false;

	if (useButtons !== false) useButtons = true;

	var popupVars = {
		'content': '' + xHtml + '',
		'options': ''
	};

	popupVars.options = '<a href="javascript:void(0);" class="cancel">' + lng.common.btCancel + '</a>' + "\n";
	if (okLabel) {
		popupVars.options += '<a href="javascript:void(0);" class="ok">' + okLabel + '</a>';
	} else {
		popupVars.options += '<a href="javascript:void(0);" class="ok">' + lng.common.btOk + '</a>';
	}
	if (width) {
		popupVars.class_attr = 'dialog';
	} else {
		popupVars.class_attr = false;
	}

	if (useButtons === false) {
		popupVars.options = '';
	}

	getJQTmpl('prompt',popupVars,function(ptpl) {
		var popupHTML = ptpl;

		var fancyOptions = {
			'opacity': true,
			'autoScale':false,
			'scrolling':'no',
			'modal': false,
			'overlayOpacity': 0.5,
			'padding': 0,
			'content': popupHTML,
			'onComplete': function() {
				$('#prompt a.cancel').click(function() {
					if ($.isFunction(callback)) callback(false);
					$.fancybox.close();
				});

				$('#prompt a.ok').click(function() {
					if ($.isFunction(callback)) callback(true);
					if( closeOnOk ) {
						$.fancybox.close();
					}
				});
			}
		}

		if (width || height) {
			fancyOptions.autoDimensions = false;
			if (width) fancyOptions.width = width;
			if (height) fancyOptions.height = height;
		}

		$.fancybox(fancyOptions);
	});
}

// wPopup Call
function wPopup(xHtml, callback, callbackOnLoad, smaller) {
	var popupVars = {
		'content': '' + xHtml + ''
	};

	var wPopupVars = {
		'popupWidth': 505
	}

	if (smaller) {
		if (smaller === true) {
			popupVars.theClass = 'smaller';
			wPopupVars.popupWidth = 390;
		} else {
			popupVars.theClass = smaller.theClass;
			wPopupVars.popupWidth = smaller.theWidth;
			wPopupVars.popupHeight = smaller.theHeight;
		}
	}

	if ($('#popup-overlay').length > 0) {
		$('#popup-overlay').fadeIn(200);
	}

	getJQTmpl('popup',popupVars,function(ptpl) {
		var popupHTML = ptpl;

		if (!$('#popup-overlay').length) {
			$('body #wrapper').after(popupHTML);
			$('#popup-overlay').hide().fadeIn(200);
		}

		$('#popup-overlay').css({'height': $(document).height()});
		$('#popup-overlay .popup-container').html(xHtml);
		$('.popup').wAnimatePopup(wPopupVars);

		$('#popup-overlay .close').die();
		$('#popup-overlay .close').live('click',function() {
			if ($.isFunction(callback)) {
				callback();
			}
			$('#popup-overlay').fadeOut(200, function() {
				$('#popup-overlay').detach();
			});

			window.location.hash = '';

			return false;
		});

		if ($.isFunction(callbackOnLoad)) {
			callbackOnLoad();
		}
	});
}

jQuery.fn.wAnimatePopup = function(options) {
	var $ = jQuery;
	var opts = $.extend({}, $.fn.wAnimatePopup.defaults, options);

	this.each(function() {
		var popupElement = this;
		var loadingElement = $(popupElement).siblings('.popup-loading');

		if ($(popupElement).hasClass('popup-done')) {
			return false;
		}

		$(popupElement).hide();
		$(loadingElement).hide();
		$(loadingElement).css({width: opts.loadingWidth, height: opts.loadingHeight, top: '50%', left: '50%'});

		var popupTop = ($(window).height() - $(loadingElement).height()) / 2;
		var popupLeft = ($(window).width() - $(loadingElement).width()) / 2;

		$(loadingElement).css({left: popupLeft, top: popupTop});//-- Properly position in the center of the window.
		$(loadingElement).show();

		popupTop = ($(window).height() - opts.popupHeight) / 2;
		popupLeft = ($(window).width() - opts.popupWidth) / 2;

		setTimeout(function() {
			$(loadingElement).animate({
				width: $(popupElement).width(),
				height: $(popupElement).height(),
				top: popupTop,
				left: popupLeft
			}, opts.speedIn, function() {
				$(loadingElement).hide();
				$(popupElement).show();
				$(popupElement).addClass('popup-done');
			});
		},500);
	});
}

//-- Default wAnimatePopup options
jQuery.fn.wAnimatePopup.defaults = {
	speedIn: 200,
	popupWidth: 505,
	popupHeight: 250,
	loadingWidth: 50,
	loadingHeight: 50
};