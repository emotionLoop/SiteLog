// Show wFramework Errors, Warnings or Messages
$(document).ready(function() {
	wzError();
	wzWarning();
	wzMessage();
});

// Show wFramework Errors
function wzError(msg,oid) {
	if ($('#wz-errors').length > 0) {
		$('#wz-errors').fadeIn('slow');
	}
	$('#wz-errors').live('click', function() {
		$(this).fadeOut('fast',function() {
			$(this).remove();
		});
	});
}

// Show wFramework Warnings
function wzWarning(msg,oid) {
	if ($('#wz-warnings').length > 0) {
		$('#wz-warnings').fadeIn('slow');
	}
	$('#wz-warnings').live('click', function() {
		$(this).fadeOut('fast',function() {
			$(this).remove();
		});
	});
}

// Show wFramework Messages
function wzMessage(msg,oid) {
	if ($('#wz-messages').length > 0) {
		$('#wz-messages').fadeIn('slow');
	}
	$('#wz-messages').live('click', function() {
		$(this).fadeOut('fast',function() {
			$(this).remove();
		});
	});
}