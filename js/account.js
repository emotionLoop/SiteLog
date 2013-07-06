$(document).ready(function() {
	regJQTmpl('cancel-form');

	//-- Trigger on cancel account
	$('#cancel-account').live('click', function() {
		wConfirm(lng.account.cancel, function(r) {
			if (r) {
				getJQTmpl('cancel-form', null, function(ptpl) {
					wPopup(ptpl);
				});
			}
		});

		return false;
	});

	//-- Validate Cancel Account form
	$('#cancel-form').live('submit', function() {
		var goOn = true;

		if (!validateField('cancel-password')) {
			goOn = false;
		}

		if (!$('#terms').is(':checked')) {
			wAlert(lng.account.terms);
			goOn = false;
		}

		if (!goOn) {
			return false;
		}
	});

	//-- Validate Personal Details form
	$('#account-form').live('submit', function() {
		var goOn = true;

		if (!validateField('name')) {
			goOn = false;
		}
		
		if (!validateField('email','email')) {
			goOn = false;
		}

		if (!goOn) {
			return false;
		}
	});

	//-- Upgrade/Downgrade part
	regJQTmpl('upgrade-form');
	regJQTmpl('downgrade-form');

	//-- Trigger on upgrade plan
	$('#upgrade-plan').live('click', function() {
		getJQTmpl('upgrade-form', null, function(ptpl) {
			wPopup(ptpl);
		});

		return false;
	});

	//-- Validate Upgrade form
	$('#upgrade-form').live('submit', function() {
		var goOn = true;

		if (!$('#upgrade-form input[name="plan"]:checked').length) {
			$.jGrowl(lng.account.selectPlan, {theme: 'error'});
			return false;
		}

		if (!goOn) {
			return false;
		}

		window.location = $('#upgrade-form input[name="plan"]:checked').data('url');

		return false;
	});

	//-- Trigger on downgrade plan
	$('#downgrade-plan').live('click', function() {
		getJQTmpl('downgrade-form', null, function(ptpl) {
			wPopup(ptpl);
		});

		return false;
	});

	//-- Validate Downgrade form
	$('#downgrade-form').live('submit', function() {
		var goOn = true;

		if (!$('#downgrade-form input[name="plan"]:checked').length) {
			$.jGrowl(lng.account.selectPlan, {theme: 'error'});
			return false;
		}

		if (!goOn) {
			return false;
		}

		if ($('#downgrade-form input[name="plan"]:checked').data('url').length) {
			window.location = $('#downgrade-form input[name="plan"]:checked').data('url');
			return false;
		}
	});

	//-- Trigger on cancel plan
	$('#cancel-plan').live('click', function() {
		wConfirm(lng.account.cancelPlan, function(r) {
			if (r) {
				var data = {
					_wznonce: nonces.cancelPlan,
					component: 'account',
					action: 'cancel-plan'
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

	//-- Billing part
	
	//-- Trigger on paypal subscription button
	$('#paypal-subscription').live('click', function() {
		if ($(this).data('url').length) {
			window.open($(this).data('url'), 'paypal-subscription');
		}

		return false;
	});

	//-- Validate Billing form
	$('#billing-form').live('submit', function() {
		var goOn = true;

		if (!validateField('name')) {
			goOn = false;
		}

		if (!validateField('address')) {
			goOn = false;
		}

		if (!validateField('zipCode')) {
			goOn = false;
		}

		if (!goOn) {
			return false;
		}
	});

	//-- Tools part

	//-- Trigger on tool/script/tip
	$('#tools ul li a').live('click', function() {
		var data = {
			_wznonce: nonces.getTip,
			component: 'account',
			action: 'get-tip',
			tip: $(this).data('id')
		};
		
		$.post(ajaxurl, data, function(response) {
			if (response) {
				if (response.error) {
					$.jGrowl(response.error, {theme: 'error'});
				} else if (response.data) {
					wPopup(response.data, null, null, {'theClass': 'tool', 'theWidth': 900, 'theHeight': 700});
				}
			} else {
				$.jGrowl(lng.common.failedInternet, {theme: 'error'});
			}
		},'json');
	});

	//-- Auto-open tool/script/tip
	if ($('#tools').length && window.location.hash.length) {
		var tmp = window.location.hash.replace('#!/','');
		if ($('#tools ul li a[data-sef="' + tmp + '"]').length) {
			$('#tools ul li a[data-sef="' + tmp + '"]').click();
		}
	}
});