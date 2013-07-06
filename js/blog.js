$(document).ready(function() {
	$('#pagination li').live('click', function() {
		window.location = $('base').attr('href') + $(this).attr('data-url');
	});
});