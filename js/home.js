$(document).ready(function() {
	$('.nyan-cat').live('click',function() {
		$.fancybox({
			'padding'		: 0,
			'autoScale'		: true,
			'transitionIn'	: 'none',
			'transitionOut'	: 'none',
			'title'			: $(this).attr('title'),
			'width'			: '100%',
			'height'		: '100%',
			'href'			: $(this).attr('href').replace(new RegExp("watch\\?v=", "i"), 'v/'),
			'type'			: 'swf',
			'modal'			: false,
			'swf'			: {
				'wmode'				: 'transparent',
				'allowfullscreen'	: 'true'
			}
		});

		return false;
	});

	//var code = "38,38,40,40,37,39,37,39,66,65";//-- Konami code
	var code = "78,89,65,78";//-- n-y-a-n

	var kkeys = [];
	$(window).keydown(function(e){
		kkeys.push(e.keyCode);
		if (kkeys.toString().indexOf( code ) >= 0) {
			$('.nyan-cat').click();
			kkeys = [];
		}
	});
});