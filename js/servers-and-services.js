$(document).ready(function() {
	//-- Servers Part
	regJQTmpl('servers-form');

	//-- Trigger on update server
	$('#manage .icon .edit-server').live('click', function() {
		var options = {
			'server': $(this).data('id'),
			'name': $(this).data('name'),
			'ip': $(this).data('ip'),
			'public': $(this).data('public'),
			'status': $(this).data('status'),
		};
		getJQTmpl('servers-form', options, function(ptpl) {
			wPopup(ptpl);
		});

		return false;
	});

	//-- Validate Update Server form
	$('#update-servers-form').live('submit', function() {
		var goOn = true;

		if (!validateField('update-server-name')) {
			goOn = false;
		}

		if (!validateField('update-ip-domain','ip')) {
			goOn = false;
		}

		if (!goOn) {
			return false;
		}
	});

	//-- Trigger on delete server
	$('#manage .icon .delete-server').live('click', function() {
		var theServerID = $(this).data('id');
		wConfirm(lng.servers.delete, function(r) {
			if (r) {
				var data = {
					_wznonce: nonces.deleteServer,
					component: 'servers-and-services',
					action: 'delete-server',
					server: theServerID
				};
				
				$.post(ajaxurl, data, function(response) {
					if (response) {
						if (response.error) {
							$.jGrowl(response.error, {theme: 'error'});
						} else if (response.data) {
							window.location.href = mainURL + response.data;
						}
					} else {
						$.jGrowl(lng.common.failedInternet, {theme: 'error'});
					}
				},'json');
			}
		});

		return false;
	});

	//-- Validate Add Server form
	$('#servers-form').live('submit', function() {
		var goOn = true;

		if (!validateField('server-name')) {
			goOn = false;
		}

		if (!validateField('ip-domain','ip')) {
			goOn = false;
		}

		if (!goOn) {
			return false;
		}
	});

	//-- Server Services Part
	regJQTmpl('services-form');
	regJQTmpl('services-update-form');

	//-- Trigger on add server service
	$('#manage .server .last .add-server-service').live('click', function() {
		//-- Check for limit
		if (usedServices >= maxServices && maxServices > 0) {
			$.jGrowl(lng.services.limit, {theme: 'error'});
			return false;
		}
		
		var options = {
			server: $(this).data('id'),
			mmis: userMMIs,
			receivers: alertReceivers
		};

		//-- Remove services already being used by this server
		var userServerServices = userServices;
		for (var i=0;i<userServerServices.length;i++) {
			if (userServerServices[i]) {
				$('#manage .service[data-id^="' + options.server + '-"]').each(function(idx, val) {
					if (userServerServices[i]) {
						var tmp = options.server + '-' + userServerServices[i].id;
						if ($(this).data('id') == tmp) {
							userServerServices.splice(i,1);
						}
					}
				});
			}
		}

		options.services = userServerServices;

		getJQTmpl('services-form', options, function(ptpl) {
			wPopup(ptpl, null, null, true);
		});

		return false;
	});

	//-- Validate Add Server Service form
	$('#add-service-form').live('submit', function() {
		if ($('#alert').val().length == 0) {
			wConfirm(lng.services.noAlert, function(r) {
				if (r) {
					var data = {
						_wznonce: nonces.addServerService,
						component: 'servers-and-services',
						action: 'add-server-service',
						server: $('#server').val(),
						service: $('#service').val(),
						mmi: $('#mmi').val(),
						alert: $('#alert').val(),
						recovery: $('#recovery').val(),
					};
					
					$.post(ajaxurl, data, function(response) {
						if (response) {
							if (response.error) {
								$.jGrowl(response.error, {theme: 'error'});
							} else if (response.data) {
								window.location.href = mainURL + response.data;
							}
						} else {
							$.jGrowl(lng.common.failedInternet, {theme: 'error'});
						}
					},'json');
				}
			});
		} else {
			var data = {
				_wznonce: nonces.addServerService,
				component: 'servers-and-services',
				action: 'add-server-service',
				server: $('#server').val(),
				service: $('#service').val(),
				mmi: $('#mmi').val(),
				alert: $('#alert').val(),
				recovery: $('#recovery').val(),
			};
			
			$.post(ajaxurl, data, function(response) {
				if (response) {
					if (response.error) {
						$.jGrowl(response.error, {theme: 'error'});
					} else if (response.data) {
						window.location.href = mainURL + response.data;
					}
				} else {
					$.jGrowl(lng.common.failedInternet, {theme: 'error'});
				}
			},'json');
		}

		return false;
	});

	//-- Trigger on update server service
	$('#manage .service .last .update-server-service').live('click', function() {
		var options = {
			service: $(this).data('id'),
			server: $(this).data('server'),
			serviceLabel: $(this).data('service-label'),
			recovery: $(this).data('recovery'),
			alert: $(this).data('alert'),
			mmi: $(this).data('mmi'),
			mmis: userMMIs,
			receivers: alertReceivers
		};

		getJQTmpl('services-update-form', options, function(ptpl) {
			wPopup(ptpl, null, null, true);
		});

		return false;
	});

	//-- Validate Update Server Service form
	$('#update-service-form').live('submit', function() {
		if ($('#alert').val().length == 0) {
			wConfirm(lng.services.noAlert, function(r) {
				if (r) {
					var data = {
						_wznonce: nonces.updateServerService,
						component: 'servers-and-services',
						action: 'update-server-service',
						service: $('#service').val(),
						server: $('#server').val(),
						mmi: $('#mmi').val(),
						alert: $('#alert').val(),
						recovery: $('#recovery').val(),
					};
					
					$.post(ajaxurl, data, function(response) {
						if (response) {
							if (response.error) {
								$.jGrowl(response.error, {theme: 'error'});
							} else if (response.data) {
								window.location.href = mainURL + response.data;
							}
						} else {
							$.jGrowl(lng.common.failedInternet, {theme: 'error'});
						}
					},'json');
				}
			});
		} else {
			var data = {
				_wznonce: nonces.updateServerService,
				component: 'servers-and-services',
				action: 'update-server-service',
				service: $('#service').val(),
				server: $('#server').val(),
				mmi: $('#mmi').val(),
				alert: $('#alert').val(),
				recovery: $('#recovery').val(),
			};
			
			$.post(ajaxurl, data, function(response) {
				if (response) {
					if (response.error) {
						$.jGrowl(response.error, {theme: 'error'});
					} else if (response.data) {
						window.location.href = mainURL + response.data;
					}
				} else {
					$.jGrowl(lng.common.failedInternet, {theme: 'error'});
				}
			},'json');
		}

		return false;
	});

	//-- Trigger on delete server service
	$('#manage .service .last .delete-server-service').live('click', function() {
		var theServiceID = $(this).data('id');
		wConfirm(lng.services.delete, function(r) {
			if (r) {
				var data = {
					_wznonce: nonces.deleteServerService,
					component: 'servers-and-services',
					action: 'delete-server-service',
					service: theServiceID
				};
				
				$.post(ajaxurl, data, function(response) {
					if (response) {
						if (response.error) {
							$.jGrowl(response.error, {theme: 'error'});
						} else if (response.data) {
							window.location.href = mainURL + response.data;
						}
					} else {
						$.jGrowl(lng.common.failedInternet, {theme: 'error'});
					}
				},'json');
			}
		});

		return false;
	});

	//-- User Services Part
	regJQTmpl('user-services-form');

	//-- Trigger on add user service
	$('#add-service .add-user-service').live('click', function() {
		getJQTmpl('user-services-form', null, function(ptpl) {
			var content = ptpl.html();
			wDialog(content, null, null, null, null, null, false);
		});

		return false;
	});

	//-- Validate Add User Service form
	$('#add-user-service-form').live('submit', function() {
		var goOn = true;

		if (!validateField('custom-service-name')) {
			goOn = false;
		}

		if (!validateField('custom-service-port','port')) {
			goOn = false;
		}

		if (!goOn) {
			return false;
		}

		var data = {
			_wznonce: nonces.addUserService,
			component: 'servers-and-services',
			action: 'add-user-service',
			name: $('#custom-service-name').val(),
			port: $('#custom-service-port').val(),
		};
		
		$.post(ajaxurl, data, function(response) {
			if (response) {
				if (response.error) {
					$.jGrowl(response.error, {theme: 'error'});
				} else if (response.data) {
					//-- Add new item to the js array, html select and close this dialog
					userServices.push({
						'id': response.data,
						'name': data.name,
						'port': data.port
					});

					var xHtml = '<option value="' + response.data + '">' + data.name + ' :' + data.port + '</option>';
					$('#service').append(xHtml);
					$('#service option').prop('selected',false);
					$('#service option[value=' + response.data +']').prop('selected',true);
					$('#service').val(response.data);

					$.fancybox.close();
				}
			} else {
				$.jGrowl(lng.common.failedInternet, {theme: 'error'});
			}
		},'json');

		return false;
	});

	//-- User Alerts Receiver Part
	regJQTmpl('user-receivers-form');

	//-- Trigger on add user alert receiver
	$('#add-service .add-user-receiver, #update-service .add-user-receiver').live('click', function() {
		getJQTmpl('user-receivers-form', null, function(ptpl) {
			var content = ptpl.html();
			wDialog(content, null, null, null, null, null, false);
		});

		return false;
	});

	//-- Validate Add Alert Receiver form
	$('#add-user-receiver-form').live('submit', function() {
		var goOn = true;

		if (!validateField('custom-receiver-email','email')) {
			goOn = false;
		}

		if (!goOn) {
			return false;
		}

		var data = {
			_wznonce: nonces.addUserReceiver,
			component: 'servers-and-services',
			action: 'add-user-receiver',
			email: $('#custom-receiver-email').val()
		};
		
		$.post(ajaxurl, data, function(response) {
			if (response) {
				if (response.error) {
					$.jGrowl(response.error, {theme: 'error'});
				} else if (response.data) {
					//-- Add new item to the js array, html select and close this dialog
					alertReceivers.push(data.email);

					var xHtml = '<option value="' + data.email + '">' + data.email + '</option>';
					$('#alert').append(xHtml);
					$('#alert option').prop('selected',false);
					$('#alert option[value="' + data.email +'"]').prop('selected',true);
					$('#alert').val(data.email);

					$.fancybox.close();
				}
			} else {
				$.jGrowl(lng.common.failedInternet, {theme: 'error'});
			}
		},'json');

		return false;
	});
});