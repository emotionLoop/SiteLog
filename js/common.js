var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
var ipPattern = /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/;
var domainPattern = /^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/;
var portPattern = /^([0-9]){2,5}$/;

var jqTemplates = new Array();

$(document).ready(function() {
	//-- Assign target to appropriate links
	$("a[rel='license']").live('click', function() {
		this.target = "_blank";
	});
	$("a[rel='download']").live('click', function() {
		this.target = "_blank";
	});
	$("a[rel='external']").live('click', function() {
		this.target = "_blank";
	});

	//-- Assign javascript:void(0); to links with # as href
	$("a[href='#']").attr("href", "javascript:void(0);");

	$(document).ajaxStop(function() {
		$("a[href='#']").attr("href", "javascript:void(0);");
		setTimeout(function() {
			$("a[href='#']").attr("href", "javascript:void(0);");
		},500);
	});

	$.jGrowl.defaults.closer = false;
	$.jGrowl.defaults.life = 15000;

	//-- Global triggers
	regJQTmpl('popup');
	regJQTmpl('prompt');
	regJQTmpl('login-form');
	regJQTmpl('recover-form');

	$('#login-button, .login-button').live('click', function() {
		getJQTmpl('login-form', null, function(ptpl) {
			wPopup(ptpl);
		});
		window.location.hash = '#!/login';
	});

	$('#login-form form').live('submit', function() {
		var goOn = true;
		
		if (!validateField('email','email')) {
			goOn = false;
		}

		if (!validateField('password')) {
			goOn = false;
		}

		if (!goOn) {
			return false;
		}
	});

	$('#recover-button, .recover-button').live('click', function() {
		getJQTmpl('recover-form', null, function(ptpl) {
			wPopup(ptpl);
		});
		return false;
	});

	$('#recover-form form').live('submit', function() {
		var goOn = true;
		
		if (!validateField('email','email')) {
			goOn = false;
		}

		if (!goOn) {
			return false;
		}
	});

	if (window.location.hash && window.location.hash.indexOf('#!/login') != -1) {
		$('#login-button, .login-button').click();
	}
});

//-- Simple function to preload images.
function preloadImgs() {
	if (arguments) {
		for(var i = 0; i < arguments.length; i++) {
			var tmp = new Image();
			tmp.src = arguments[i];
		}
	}
}

//-- Validate fields using wError
function validateField(fID, type) {
	if (!type) type = 'common';
	
	switch (type) {
		case 'common':
			if ($("#" + fID).val().length < 2) {
				$("#" + fID).wError({ msgTxt: lng.common.required });//-- Show Error
				return false;
			} else {
				$("#wError-" + fID).click();//-- Hide Error
			}
		break;
		case 'email':
			if ($("#" + fID).val().length < 2) {
				$("#" + fID).wError({ msgTxt: lng.common.required });//-- Show Error
				return false;
			} else {
				var tmpS = $("#" + fID).val();
				if (!emailPattern.test(tmpS)) {
					$("#" + fID).wError({ msgTxt: lng.common.invalid });//-- Show Error
					return false;
				} else {
					$("#wError-" + fID).click();//-- Hide Error
				}
			}
		break;
		case 'ip':
			if ($("#" + fID).val().length < 2) {
				$("#" + fID).wError({ msgTxt: lng.common.required });//-- Show Error
				return false;
			}else {
				var tmpS = $("#" + fID).val();
				if (!ipPattern.test(tmpS)) {
					if (!domainPattern.test(tmpS)) {
						$("#" + fID).wError({ msgTxt: lng.common.invalid });//-- Show Error
						return false;
					}else{
						$("#wError-" + fID).click();//-- Hide Error	
					}
				} else {
					$("#wError-" + fID).click();//-- Hide Error
				}
			}  
		break;
		case 'port':
			if ($("#" + fID).val().length < 2) {
				$("#" + fID).wError({ msgTxt: lng.common.required });//-- Show Error
				return false;
			} else {
				var tmpS = $("#" + fID).val();
				if (!portPattern.test(tmpS)) {
					$("#" + fID).wError({ msgTxt: lng.common.invalid });//-- Show Error
					return false;
				} else {
					$("#wError-" + fID).click();//-- Hide Error
				}
			}
		break;
	}
	return true;
}

//-- Get a jQuery template
function getJQTmpl(tpl, vars, callback) {
	$.latte({template: 'jquery_tpl_' + tpl, values: vars}).load(function(tmpl) {
		var ptmpl = $(tmpl.content).tmpl(vars);
		callback(ptmpl, tmpl);
	});
}

//-- Register a jQuery template
function regJQTmpl(tpl) {
	var tmp = {
		name: 'jquery_tpl_' + tpl,
		url: ajaxurl + '&component=jqtmpl&action=load&t=' + tpl + '&_wznonce=' + nonces.getJQTmpl
	};

	jqTemplates.splice(jqTemplates.length,1,tmp);
	$.latte({templates:jqTemplates});
}