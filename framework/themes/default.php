<?php
	//App::loadModule('tmpl');
	
	header('Content-type: text/html; charset='.Lng::$charset);
?>
<!DOCTYPE html>
<html xml:lang="<?php echo Lng::$code; ?>" lang="<?php echo Lng::$code; ?>" dir="<?php echo Lng::$dir; ?>">
<head>
<title><?php echo FW::get('title'); ?></title>
	<meta http-equiv="Content-type" content="text/html; charset=<?php echo Lng::$charset; ?>" />
	<meta http-equiv="Content-Language" content="<?php echo Lng::$locale; ?>" />
	<?php echo FW::get('seo_title')."\n"; ?>
	<?php echo FW::get('seo_description')."\n"; ?>
	<?php echo FW::get('seo_keywords')."\n"; ?>
	<base href="<?php echo ST::get('url'); ?>" />
	<meta name="robots" content="index,follow" />
	<meta name="googlebot" content="index,follow" />

	<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
	<link rel="icon" href="images/favicon.ico" type="image/x-icon" />
	
	<!-- Cascading Style Sheets -->
	<link rel="stylesheet" media="screen, projection" href="css/blueprint/screen.css" />
	<link rel="stylesheet" href="css/blueprint/print.css" media="print" />
	
	<!--[if gte IE 8]>
		<link rel="stylesheet" href="css/blueprint/ie.css" media="screen, projection" />
	<![endif]-->
	
	<link rel="stylesheet" media="all" href="<?php echo Sys::is_ssl() ? 'https' : 'http'; ?>://fonts.googleapis.com/css?family=Indie+Flower" />
	<link rel="stylesheet" href="css/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
	<link rel="stylesheet" href="css/jquery.jgrowl.css" media="screen" />

	<link rel="stylesheet" href="css/wframework.css" media="all" />
	<link rel="stylesheet" href="css/style.css" media="all" />
		
	<script src="<?php echo Sys::is_ssl() ? 'https' : 'http'; ?>://www.google.com/jsapi?key=<?php echo ST::get('google_apikey'); ?>"></script>
	<script>
		google.load("jquery", "1.7");
		google.load("jqueryui", "1.8");
	</script>
	<script>
		var lng = {
			'common': {
				'required':'<?php _e('Required'); ?>',
				'invalid':'<?php _e('Invalid'); ?>',
				'alert':'<?php _e('Warning'); ?>',
				'confirm':'<?php _e('Are you sure?'); ?>',
				'btOk':'<?php _e('Ok'); ?>',
				'btCancel':'<?php _e('Cancel'); ?>',
				'error':'<?php _e('Error'); ?>',
				'success':'<?php _e('Success'); ?>',
				'failedInternet':'<?php _e('Connection lost. Please verify your Internet connection and try again.'); ?>'
			}
		};
		var nonces = {
			'getJQTmpl':'<?php echo Sys::create_nonce('get-jqtmpl'); ?>'
		};
		var ajaxurl = '<?php echo ST::get('url'); ?>ajax/?r=' + Math.random();//-- No ajax caching
		var mainURL = '<?php echo ST::get('url'); ?>';
	</script>
	
	<script src="js/jquery.all.min.js"></script>
	<script src="js/common.js"></script>
	
	<?php echo FW::get('head'); ?>
</head> 
<body class="<?php echo App::$component?>">
	<div id="wrapper">
		<header>
			<div class="container">
				<div id="logo" class="span-3 first"> 
					<h1>
						<?php echo FW::url('',FW::img('theme/sitelog-logo.png','SiteLog - Monitor your websites and servers the easy way')); ?>
					</h1>
				</div> 
				<nav id="main-menu" class="span-14"> 
					<?php App::getModule('menu'); ?>
				</nav>
				<div id="login" class="span-7 last">
					<?php App::getModule('account_menu'); ?>
				</div>
				<noscript><div id="noscript-warning" class="wz-notification"><?php _e('This website requires Javascript to be enabled to function properly.'); ?></div></noscript>
			</div>
		</header>
		<div id="main">
			<section id="content" class="container">
				<?php call_user_func(array(App::$ccomponent, 'main')); ?>
			</section>
		</div>
		<div class="padding"></div>
	</div>
	<footer id="footer" class="clear">			
		<div class="container">
			<section id="social" class="span-8 first">
				<span><?php echo FW::url('https://twitter.com/SiteLogHQ',FW::img('social/twitter.png',__('Follow us on Twitter'))); ?></span> 
				<span><?php echo FW::url('https://www.facebook.com/SiteLog',FW::img('social/facebook.png',__('Like us on Facebook'))); ?></span> 
			</section>
			<section id="policies" class="span-8">
				<?php echo FW::url('privacy-policy/',__('Privacy Policy')); ?> <span>&middot;</span> <?php echo FW::url('terms-of-use/',__('Terms of Use')); ?>
			</section>
			<section id="copyright" class="span-8 last">
				<?php echo FW::url('',__('SiteLog')); ?>
				<span><?php echo ST::get('sitelog_version'); ?></span> // &copy; <?php _e('by'); ?> <?php echo FW::url('http://emotionloop.com',__('emotionLoop')); ?>
			</section>
		</div>
	</footer>
	<?php FW::vErrors(); ?>
	<?php if (WZ_DEBUG) { Sys::vlog(); } ?>
	<!--[if lte IE 9 ]>
		<script type="text/javascript">
			function oldIEVersion() {
				document.getElementById('wrapper').innerHTML = '<h2>Sorry but you need to use a <a href="https://www.google.com/chrome" target="_blank">better browser</a> or <a href="http://www.google.com/chromeframe/" target="_blank">install Google Chrome Frame</a> to view our website.</h2>';
				document.getElementById('footer').innerHTML = '';
			}
		</script>
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/chrome-frame/1/CFInstall.min.js"></script>
		<script type="text/javascript">window.attachEvent('onload',function(){CFInstall.check({mode:'overlay', destination:'http://siteloghq.com', onmissing:function(){oldIEVersion();}})})</script>
	<![endif]-->
</body> 
</html> 
