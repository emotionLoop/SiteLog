<?php
/**
 * Tour is the tour component class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Component
 * @category Tour
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.1.0 BETA
 */
class Tour extends Component {
	
	public static function prefunctions() {//-- Functions to be loaded before processing. Determine action and theme
		if (Usr::logged()) {
			FW::redirect('status/');
		}
		
		FW::addJS('tour.js', array('defer' => 'defer'));	
	}
	
	public static function main() {//-- Stuff to load when the component is called, on content part for the template
?>
		<div id="slideshow-wrapper" class="span-23 prepend-1"> 
			<div id="slideshow-inline-wrapper"> 
				<article class="selected step-1">
					<div class="span-12 last">
						<h2 class="span-10">1. <?php _e('Add a website<span> or server</span>.'); ?></h2>
						<p class="clear"><?php _e("After signup, the first thing you'll want to do is add a new server or website, the one(s) you'll want to monitor."); ?></p>
						<p><?php _e('You can add a server or website by their IP <small>(recommended)</small> or URL/domain name.'); ?></p>
						<p class="buttons"><?php echo FW::url('#', __('Continue the Tour'), array('class' => 'button first continue-tour')); ?> <span class="or"><?php _e('or'); ?></span> <?php echo FW::url('plans-and-pricing/', __('Signup'), array('class' => 'button pink')); ?></p>
					</div>
					<div class="img span-10 prepend-1">
						<?php echo FW::url('images/tour/tour-1-1.png', FW::img('tour/tour-1-thumb.png', __('Add a server or website')), array('class' => 'span-9 fancybox-img', 'rel' => 'gallery-tour-1')); ?>
					</div>
					<div class="img-gallery">
						<?php echo FW::url('images/tour/tour-1-2.png', FW::img('tour/tour-1-2.png', __('Add a server or website')), array('class' => 'span-9 fancybox-img', 'rel' => 'gallery-tour-1')); ?>
					</div>
				</article>
				<article class="step-2">
					<div class="span-12 last">
						<h2 class="span-10">2. <?php _e('Add a service<span>ftp, ping, ssh, etc.</span>.'); ?></h2>
						<p class="clear"><?php _e("Now you have your website all setup, so you'll probably want to monitor its services, right?"); ?></p>
						<p><?php _e('Choose from a pre-defined list (SSH, FTP, etc.) or add your own with customized names and port numbers <small>(wohoo!)</small>.'); ?></p>
						<p class="buttons"><?php echo FW::url('#', __('Continue the Tour'), array('class' => 'button first continue-tour')); ?> <span class="or"><?php _e('or'); ?></span> <?php echo FW::url('plans-and-pricing/', __('Signup'), array('class' => 'button pink')); ?></p>
					</div>
					<div class="img span-10 prepend-1">
						<?php echo FW::url('images/tour/tour-2-1.png', FW::img('tour/tour-2-thumb.png', __('Add a service')), array('class' => 'span-8 fancybox-img', 'rel' => 'gallery-tour-2')); ?>
					</div>
					<div class="img-gallery">
						<?php echo FW::url('images/tour/tour-2-2.png', FW::img('tour/tour-2-2.png', __('Add a service')), array('class' => 'span-9 fancybox-img', 'rel' => 'gallery-tour-2')); ?>
					</div>
				</article> 
				<article class="step-3">
					<div class="span-12 last">
						<h2 class="span-10">3. <?php _e('Profit!'); ?></h2>
						<p class="clear"><?php _e("BAM! You're now allowed into the great world of the \"Status\" page, where you have your own Big Brother thing going on."); ?></p>
						<p><?php _e("Just try not to trigger a SkyNet or we'll have to call some unicorns to put you back in line."); ?></p>
						<p class="buttons"><?php echo FW::url('plans-and-pricing/', __('Signup'), array('class' => 'button pink')); ?></p>
					</div>
					<div class="img span-10 prepend-1">
						<?php echo FW::url('images/tour/tour-3-1.png', FW::img('tour/tour-3-thumb.png', __('Profit!')), array('class' => 'span-9 fancybox-img', 'rel' => 'gallery-tour-3')); ?>
					</div>
					<div class="img-gallery">
						<?php echo FW::url('images/tour/tour-3-2.png', FW::img('tour/tour-3-2.png', __('Profit!')), array('class' => 'span-9 fancybox-img', 'rel' => 'gallery-tour-3')); ?>
						<?php echo FW::url('images/tour/tour-3-3.png', FW::img('tour/tour-3-3.png', __('Profit!')), array('class' => 'span-9 fancybox-img', 'rel' => 'gallery-tour-3')); ?>
						<?php echo FW::url('images/tour/tour-3-4.png', FW::img('tour/tour-3-4.png', __('Profit!')), array('class' => 'span-9 fancybox-img', 'rel' => 'gallery-tour-3')); ?>
					</div>
				</article> 
			</div>
		</div>
		<nav id="slideshow-pages" class="clear span-11 prepend-6 last">
			<ul>
				<li class="selected servers"><small>1</small><span><?php _e('Add servers'); ?></span></li>
				<li class="services"><small>2</small><span><?php _e('Add services'); ?></span></li>
				<li class="profit"><small>3</small><span><?php _e('Profit!'); ?></span></li>
			</ul>
		</nav>
<?php
	}
}
?>