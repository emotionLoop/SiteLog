<?php
/**
 * Plans is the plans component class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Component
 * @category Plans
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.1.0 BETA
 */
 
class Plans extends Component {
	public static function prefunctions() {//-- Functions to be loaded before processing. Determine action and theme
		if (Usr::logged()) {
			FW::redirect('status/');
		}
		
		FW::addJS('plans.js', array('defer' => 'defer'));

		Sys::startCache();
?>
<script defer="defer">
lng.plans = {
	'terms': '<?php _e('Sorry, but you have to read and accept our <a href="/terms-of-use/" rel="external" class="green">Terms of Use</a>'); ?>'
};
</script>
<?php
		$js = Sys::endCache();
		FW::set('head',$js);
	}
	
	public static function main() {//-- Stuff to load when the component is called, on content part for the template
		Sys::startCache();
?>
		<article class="span-22 prepend-1 append-1">
			<h1 class="hide"><?php _e('SiteLog Plans &amp; Pricing'); ?></h1>
			<h2 class="span-12 last prepend-5 append-5"><?php _e('No contracts. No cancellation fees.<br /><strong>30-day FREE Trial</strong>. Pay-as-you-go.'); ?></h2>
			<p class="span-7 choose"><?php _e('Choose a plan by clicking on it'); ?></p>
			<p class="span-7 signup"><?php echo FW::img('theme/60-second-signup.png', __('Signup in 60 seconds')); ?><br />
			<?php _e('Signup in<br />60 seconds'); ?></p>
			<p class="span-7 credit"><?php echo FW::img('theme/credit-card.png', __('No credit card required for Signup')); ?><br />
			<?php _e('No credit card<br />required for Signup'); ?></p>
			<ul id="plans">
				<li id="plus" class="span-7"><a href="plans-and-pricing/#!/signup/<?php echo FW::getPlanInfo(2,'sef_name'); ?>" title="<?php echo FW::getPlanInfo(2,'name'); ?>" class="signup" data-plan="<?php echo FW::getPlanInfo(2,'sef_name'); ?>" data-price="$<?php echo FW::getPlanInfo(2,'price'); ?>">
					<h3 class="pink"><?php echo FW::getPlanInfo(2,'name'); ?></h3>
					<small><?php _e('Most Popular!'); ?></small>
					<ul>
						<li><span><?php echo FW::getPlanInfo(2,'services'); ?></span> <?php _e('services'); ?></li>
						<li><span><?php echo FW::getPlanInfo(2,'emails'); ?></span> <?php _e('emails'); ?></li>
						<li class="bottom"><span><?php echo FW::getPlanInfo(2,'mmi'); ?></span> <?php _e('minutes MMI'); ?></li>
						<li class="bottom price pink">$<?php echo FW::getPlanInfo(2,'price'); ?> <?php _e('<small>per<br />month</small>'); ?></li>
					</ul>
				</a></li>


				<li id="basic" class="span-7"><a href="plans-and-pricing/#!/signup/<?php echo FW::getPlanInfo(1,'sef_name'); ?>" title="<?php echo FW::getPlanInfo(1,'name'); ?>" class="signup" data-plan="<?php echo FW::getPlanInfo(1,'sef_name'); ?>" data-price="$<?php echo FW::getPlanInfo(1,'price'); ?>">
					<h3><?php echo FW::getPlanInfo(1,'name'); ?></h3>
					<ul>
						<li><span><?php echo FW::getPlanInfo(1,'services'); ?></span> <?php _e('services'); ?></li>
						<li><span><?php echo FW::getPlanInfo(1,'emails'); ?></span> <?php _e('emails'); ?></li>
						<li class="bottom"><span><?php echo FW::getPlanInfo(1,'mmi'); ?></span> <?php _e('minutes MMI'); ?></li>
						<li class="bottom price">$<?php echo FW::getPlanInfo(1,'price'); ?> <?php _e('<small>per<br />month</small>'); ?></li>
					</ul>
				</a></li>


				<li id="premium" class="span-7"><a href="plans-and-pricing/#!/signup/<?php echo FW::getPlanInfo(3,'sef_name'); ?>" title="<?php echo FW::getPlanInfo(3,'name'); ?>" class="signup" data-plan="<?php echo FW::getPlanInfo(3,'sef_name'); ?>" data-price="$<?php echo FW::getPlanInfo(3,'price'); ?>">
					<h3><?php echo FW::getPlanInfo(3,'name'); ?></h3>
					<ul>
						<li><span><?php echo FW::getPlanInfo(3,'services'); ?></span> <?php _e('services'); ?></li>
						<li><span><?php echo FW::getPlanInfo(3,'emails'); ?></span> <?php _e('emails'); ?></li>
						<li class="bottom"><span><?php echo FW::getPlanInfo(3,'mmi'); ?></span> <?php _e('minutes MMI'); ?></li>
						<li class="bottom price">$<?php echo FW::getPlanInfo(3,'price'); ?> <?php _e('<small>per<br />month</small>'); ?></li>
					</ul>
				</a></li>
			</ul>
			<p class="clear"><a href="plans-and-pricing/#!/signup/<?php echo FW::getPlanInfo(4,'sef_name'); ?>" class="signup" title="<?php echo FW::getPlanInfo(4,'name'); ?>" data-plan="<?php echo FW::getPlanInfo(4,'sef_name'); ?>" data-price="<?php echo FW::getPlanInfo(4,'price'); ?>"><?php _e('We also have a free plan'); ?> (<?php echo FW::getPlanInfo(4,'services'); ?> <?php _e('services'); ?>, <?php echo FW::getPlanInfo(4,'emails'); ?> <?php _e('emails'); ?>, <?php echo FW::getPlanInfo(4,'mmi'); ?> <?php _e('min.'); ?>)</a></p>
		</article>
		<aside class="span-10 prepend-2 append-1">
			<h4><?php _e('What do you get?'); ?></h4>
			<ul>
				<li><?php _e('A free unicorn ride and all-you-can-eat rainbows (some like to drink them though — weirdos).'); ?></li>
				<li><?php _e('Support who cares about what can we do to make your life easier everyday (only SiteLog related!). Try us.'); ?></li>
				<li><?php _e('Oh, an awesome web app! SiteLog! :D'); ?></li>
			</ul>
		</aside>
		<aside class="span-10">				
			<h4><?php _e('What the hell does it mean?'); ?></h4>
			<ul>
				<li><?php _e('MMI stands for Minimum Monitoring Interval, which is the minimum number of minutes you can set for a service to be checked for.'); ?></li>
				<li><?php _e('Services are... well... services from a server (that hosts websites) like'); ?> <?php echo FW::url('http://en.wikipedia.org/wiki/File_Transfer_Protocol', __('FTP')); ?>, <?php echo FW::url('http://en.wikipedia.org/wiki/Secure_Shell', __('SSH')); ?>, <?php echo FW::url('http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol', __('HTTP')); ?>, <?php _e('etc.'); ?></li>
			</ul>
		</aside>
<?php
		
	}
}
?>
