var statusTimer = null;
var statusSeconds = 10;

var autoScrollTime = 10000;//-- Default Scrolling Time in milliseconds
var doAutoScroll = false;//-- Defines if the page should autoScroll or not
var elementToUse = 'body';

var autoRefreshTime = 3600000;//-- 1 hour
 
$(document).ready(function() {
	statusTimer = setTimeout("updateStatus()", statusSeconds * 1000);

	//-- Auto-Scroll
	if (autoScroll) {
		startAutoScroll();
	}

	//-- Auto-Refresh
	if (autoRefresh) {
		setTimeout(function() {
			window.location.reload();
		}, autoRefreshTime);
	}
});


function startTimer() {
	clearTimeout(statusTimer);
	statusTimer = setTimeout("updateStatus()", statusSeconds * 1000);
}

function pauseTimer() {
	clearTimeout(statusTimer);
}

function updateStatus() {
	pauseTimer();

	//-- Update Services Status
	var data = {
		_wznonce: nonces.updatePublicServicesStatus,
		component: 'public',
		action: 'update',
		server: serverHash
	};
	
	$.post(ajaxurl, data, function(response) {
		if (response) {
			if (response.error && response.error != 'no-services') {
				$.jGrowl(response.error, {theme: 'error'});
			} else if (response.data) {
				for (var i=0;i<response.data.length;i++) {
					var currentService = response.data[i];
					var currentID = '#service-' + currentService.id;

					if ($(currentID).length) {
						var currentDate = $(currentID).find('time').html();

						$(currentID).find('time').html(currentService.updated);

						//-- If the date changed, we make it blink
						if (currentDate != currentService.updated) {
							//$(currentID).find('time').fadeOut(400).fadeIn(400).fadeOut(400).fadeIn(400).fadeOut(400).fadeIn(400);
							$(currentID).fadeOut(400).fadeIn(400).fadeOut(400).fadeIn(400).fadeOut(400).fadeIn(400);
						}

						if (currentService.status == 1) {
							$(currentID).removeClass('red');
						} else {
							$(currentID).addClass('red');
						}
					}
				}
			}
		} else {
			$.jGrowl(lng.common.failedInternet, {theme: 'error'});
		}
		
		if (!response.error) {
			startTimer();
		}
	},'json');
}

//-- Auto Scroll
function startAutoScroll(theTime) {
	if (theTime) autoScrollTime = theTime;
	if ($('html').height() > 0 && $('html').height() > $('body').height()) {
		elementToUse = 'html';
	}
	
	stopAutoScroll();
	doAutoScroll = true;
	$(elementToUse).animate({scrollTop:0}, 'fast', function() {
		if (doAutoScroll) autoScrollBottom();
	});
}
 
function autoScrollBottom() {
	if (doAutoScroll) $(elementToUse).animate({scrollTop:$('#wrapper').height()-$(window).height()}, autoScrollTime, autoScrollTop);
}
 
function autoScrollTop() {
	if (doAutoScroll) $(elementToUse).animate({scrollTop:0}, autoScrollTime, autoScrollBottom);
}
 
function stopAutoScroll() {
	doAutoScroll = false;
	$(elementToUse).stop(true);
}
