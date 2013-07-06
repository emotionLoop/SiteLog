var statusTimer = null;
var statusSeconds = 10;

var autoScrollTime = 10000;//-- Default Scrolling Time in milliseconds
var doAutoScroll = false;//-- Defines if the page should autoScroll or not
var elementToUse = 'body';

var autoRefreshTime = 3600000;//-- 1 hour
 
$(document).ready(function() {
	regJQTmpl('history-log');

	statusTimer = setTimeout("updateStatus()", statusSeconds * 1000);

	//-- Add click action to trigger history view
	$('.status li').live('click', function() {
		//-- Get Service history
		var data = {
			_wznonce: nonces.getServiceHistory,
			component: 'status',
			action: 'history',
			service: $(this).data('id')
		};
		
		$.post(ajaxurl, data, function(response) {
			if (response) {
				if (response.error) {
					$.jGrowl(response.error, {theme: 'error'});
				} else if (response.data) {
					getJQTmpl('history-log', response.data, function(ptpl) {
						wPopup(ptpl, null, function() {
							var chartData = new google.visualization.DataTable();
							chartData.addColumn('string', 'Type');
							chartData.addColumn('number', 'Value');
							chartData.addRows([
								[lng.status.uptime, response.data.uptime],
								[lng.status.downtime, response.data.downtime]
							]);

							// Set chart options
							var chartOptions = {
								'width': 480,
								'height': 200,
								'pieResidueSliceColor': '#FFF',
								'colors': ['#090','#C00']
							};

							// Instantiate and draw our chart, passing in some options.
							var chart = new google.visualization.PieChart(document.getElementById('chart-graph'));
							chart.draw(chartData, chartOptions);
						});
					});
				}
			} else {
				$.jGrowl(lng.common.failedInternet, {theme: 'error'});
			}
		},'json');
	});

	//-- Add click actions to toggle chart graphic and history log
	$('#service-history-log #view-chart').live('click', function() {
		$('#service-history-log h3.log').hide();
		$('#service-history-log h3.chart').show();

		$('#service-history-log .history-table').slideUp('fast', function() {
			$('#service-history-log .uptime-chart').slideDown('fast');
		});
	});

	$('#service-history-log #view-log').live('click', function() {
		$('#service-history-log h3.chart').hide();
		$('#service-history-log h3.log').show();

		$('#service-history-log .uptime-chart').slideUp('fast', function() {
			$('#service-history-log .history-table').slideDown();
		});
	});

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
		_wznonce: nonces.updateServicesStatus,
		component: 'status',
		action: 'update'
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
