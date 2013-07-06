$(document).ready(function() {
	regJQTmpl('signup-form');

	$('.signup').live('click', function() {
		options = {
			'plan': $(this).data('plan'),
			'planTxt': $(this).prop('title'),
			'price': $(this).data('price')
		};

		getJQTmpl('signup-form', options, function(ptpl) {
			wPopup(ptpl);
		});
	});

	$('#signup-form form').live('submit', function() {
		var goOn = true;

		if (!validateField('name')) {
			goOn = false;
		}
		
		if (!validateField('email','email')) {
			goOn = false;
		}

		if (!$('#terms').is(':checked')) {
			wAlert(lng.plans.terms);
			goOn = false;
		}

		if (!goOn) {
			return false;
		}
	});

	if (window.location.hash && window.location.hash.indexOf('#!/signup/') != -1) {
		var tmp = window.location.hash.split('/');
		if (tmp[2] && $('a[data-plan="' + tmp[2] + '"]').length) {
			$('a[data-plan="' + tmp[2] + '"]').click();
		}
	}
});