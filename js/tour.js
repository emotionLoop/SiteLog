var activeSlide = 1;
var maxSlides = 0;
var slideWidth = 0;

jQuery(document).ready(function($) {
	maxSlides = $('#slideshow-inline-wrapper article').length;
	slideWidth = $('#slideshow-wrapper').width();
	
	var allWidth = maxSlides * slideWidth;
	
	$('#slideshow-inline-wrapper').width(allWidth);
	
	$('#slideshow-prev').live('click', function() {
		//-- Check if we reached the first slider
		if ((activeSlide - 1) < 1) {
			moveToSlider(maxSlides);
		} else {
			moveToSlider(activeSlide - 1);
		}
	});
	
	$('#slideshow-next').live('click', function() {
		//-- Check if we reached the last slider
		if ((activeSlide + 1) > maxSlides) {
			moveToSlider(1);
		} else {
			moveToSlider(activeSlide + 1);
		}
	});
	
	$('#slideshow-pages ul li').live('click', function() {
		moveToSlider($(this).index() + 1);
	});
	
	$('.continue-tour').live('click', function() {
		//-- Check if we reached the last slider
		if ((activeSlide + 1) > maxSlides) {
			moveToSlider(1);
		} else {
			moveToSlider(activeSlide + 1);
		}
	});

	//-- Zoom
	$('a.fancybox-img').fancybox({
		'overlayOpacity'	: '0.95',
		'overlayColor'		: '#000',
		'padding'			: '0',
	});
});

function moveToSlider(idx) {
	$ = jQuery;
	
	var newLeft = (idx - 1) * slideWidth;
	var newTitle = $('#slideshow-inline-wrapper article:eq(' + (idx - 1) + ')').attr('data-title');
	
	$('#slideshow-inline-wrapper').animate({'left': -newLeft}, 600);
	$('#main-header h1').fadeOut('fast',function() {
		$(this).html(newTitle).fadeIn('fast');
	});
	selectSlide(idx);
}

function selectSlide(idx) {
	$ = jQuery;
	
	$('#slideshow-pages ul li').removeClass('selected');
	$('#slideshow-pages ul li:eq(' + (idx - 1) + ')').addClass('selected');
	
	$('#slideshow-inline-wrapper article').removeClass('selected');
	$('#slideshow-inline-wrapper article:eq(' + (idx - 1) + ')').addClass('selected');
	
	activeSlide = idx;
}