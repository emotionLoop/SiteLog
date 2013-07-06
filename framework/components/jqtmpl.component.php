<?php
/**
 * JQTmpl is the jQuery Templates component class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Component
 * @category JQTmpl
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.1.0 BETA
 *
 * @ignore
 */
class JQTmpl extends Component {
	public static function ajax() {
		$result = new stdClass();
		$result->error = '';
		$result->data = new stdClass();
		
		$nonce = SS::get('_wznonce','postget','','string');
		$action = SS::get('action','postget','','string');
		$template = SS::get('t','postget','','string');
		
		switch ($action) {
			case 'load':
			default:
				if (Sys::verify_nonce($nonce,'get-jqtmpl')) {
?>
<script type="text/x-jquery-tmpl">
<?php
					switch ($template) {
						case 'prompt':
?>
<div id="prompt"{{if theClass}} class="${theClass}"{{/if}}>
	{{html content}}
	<div class="options">
		{{html options}}
	</div>
</div>
<?php
						break;
						case 'popup':
?>
<div id="popup-overlay">
	<div class="popup-loading"></div>
	<div class="popup{{if theClass}} ${theClass}{{/if}}">
		<span class="close"></span>
		<div class="popup-container">
		{{html content}}
		</div>
	</div>
</div>
<?php
						break;
						case 'login-form':
?>
<div id="login-form" class="popup-form">
	<form action="<?php echo str_replace('http://', 'https://', ST::$url); ?>account/" method="post" name="login-form">
	<?php Sys::nonce_field('do-login'); ?>
	<input type="hidden" name="doLogin" value="1" />
	<fieldset>
		<legend><?php _e('Login'); ?></legend>
		<p>
			<label class="span-2"><?php _e('Email'); ?></label><span class="span-9 required"><input id="email" class="span-9" type="text" name="email" value="" placeholder="email@example.com" /></span><br />
			<label class="clear span-2"><?php _e('Password'); ?></label><span class="span-9 required"><input id="password" class="span-9" type="password" name="password" value="" /></span><br />
			<label class="checkbox"><input class="clear" type="checkbox" name="remember" value="1" /><small><?php _e('Remember me'); ?></small></label><br />
			<input class="clear button" type="submit" name="login" value="<?php _e('Login'); ?>" />
		</p>
		<p class="span-7 details"><small><a href="#!/lost-password" id="recover-button"><?php _e('Forgot password?'); ?></a><br />
		<?php _e("Don't have an account yet?"); ?> <?php echo FW::url('plans-and-pricing/', __('Get yourself a shiny new account!')); ?></small></p>
	</fieldset>
	</form>
</div>
<?php
						break;
						case 'recover-form':
?>
<div id="recover-form" class="popup-form">
	<form action="<?php echo ST::$url; ?>account/" method="post" name="recover-form">
	<?php Sys::nonce_field('do-recover'); ?>
	<input type="hidden" name="doRecover" value="1" />
	<fieldset>
		<legend><?php _e('Recover Password'); ?></legend>
		<p>
			<label class="span-2"><?php _e('Email'); ?></label><span class="span-9 required"><input id="email" class="span-9" type="text" name="email" placeholder="email@example.com" /></span><br />
			<input class="clear button" type="submit" name="recover" value="<?php _e('Get New Password'); ?>" />
		</p>
		<p class="span-7 details"><small><a href="#!/login" class="login-button"><?php _e('Want to login instead?'); ?></a><br />
		<?php _e("Don't have an account yet?"); ?> <?php echo FW::url('plans-and-pricing/', __('Get yourself a shiny new account!')); ?></small></p>
	</fieldset>
	</form>
</div>
<?php
						break;
						case 'signup-form':
?>
<div id="signup-form" class="popup-form">
	<form action="<?php echo ST::$url; ?>account/" method="post" name="signup-form">
	<?php Sys::nonce_field('do-signup'); ?>
	<input type="hidden" name="doSignup" value="1" />
	<input type="hidden" name="plan" value="${plan}" />
	<fieldset>
		<legend><?php _e('Signup'); ?></legend>
		<p>
			<label class="span-2"><?php _e('Name'); ?></label> <span class="span-9 required"><input id="name" class="span-9" type="text" name="name" placeholder="John Doe" /></span><br />
			<label class="span-2"><?php _e('Email'); ?></label> <span class="span-9 required"><input id="email" class="span-9" type="text" name="email" placeholder="email@example.com" /></span><br />
			<label class="checkbox"><input id="terms" class="clear" type="checkbox" name="terms" value="1" /> <?php _e('<small>I read and accept the (boooring)</small></label> <a href="/terms-of-use/" rel="external" class="green"><small class="link">Terms of Use</small></a>'); ?><br />
			<input class="clear button" type="submit" name="signup" value="<?php _e('Signup'); ?>" />
		</p>
		<p class="span-6 details"><?php _e("<small>You chose the <span class=\"green\">\${planTxt}</span> plan, for {{if price != '0'}}<span class=\"green\">\${price}</span> / month{{else}}<span class=\"green\">Free!</span>{{/if}}<br />You will get a 30-day FREE Trial. Because we like you!</small>"); ?></p>
	</fieldset>
	</form>
</div>
<?php
						break;
						case 'cancel-form':
?>
<div id="cancel-form" class="popup-form">
	<form action="<?php echo ST::$url; ?>account/" method="post" name="cancel-form">
	<?php Sys::nonce_field('cancel-account'); ?>
	<input type="hidden" name="cancelAccount" value="1" />
	<fieldset>
		<legend><?php _e('Cancel Account'); ?></legend>
		<p><?php _e('Canceling your account is immediate.'); ?></p>
		<p><?php _e('By canceling your account, you have no right to any refunds.'); ?></p>
		<p><?php _e('All your data will be deleted instantly.'); ?></p>
		<p><?php _e('This cannot be undone.'); ?></p>
		<p>
			<label class="span-2"><?php _e('Password'); ?></label> <span class="span-9 required"><input id="cancel-password" class="span-9" type="password" name="password" /></span><br />
			<label class="checkbox"><input id="terms" class="clear" type="checkbox" name="terms" value="1" /> <?php _e("<small>I understand and agree with SiteLog's cancellation policy, and I wish to cancel and delete my account.</small>"); ?></label><br />
			<input class="clear button" type="submit" name="cancel" value="<?php _e('Delete Account Now!'); ?>" />
		</p>
	</fieldset>
	</form>
</div>
<?php
						break;
						case 'servers-form':
?>
<div id="update-servers-form" class="popup-form">
	<form action="<?php echo ST::$url; ?>servers-and-services/servers/" method="post" name="update-servers-form">
		<?php Sys::nonce_field('update-server'); ?>
		<input type="hidden" name="updateServer" value="1" />
		<input type="hidden" name="server" value="${server}" />
		<fieldset>
			<legend><?php _e('Update Server'); ?></legend>
			<p>
				<label class="span-2 pink"><?php _e('Name'); ?></label> <span class="span-9 required"><input class="span-9" type="text" name="name" id="update-server-name" value="${name}" /></span><br />
				<label class="span-2 pink"><?php _e('IP/Domain'); ?></label> <span class="span-9 required"><input class="span-9" type="text" name="ip-domain" id="update-ip-domain" value="${ip}" /></span><br />
				<label class="clear span-2 pink"><?php _e('Access'); ?></label> 
				<span class="span-9 required">
					<select class="span-4" name="public" id="update-public">
						<option value="0"{{if public == 0}} selected="selected"{{/if}}><?php _e('Private'); ?></option>
						<option value="1"{{if public == 1}} selected="selected"{{/if}}><?php _e('Public'); ?></option>
					</select>
				</span><br />
				<label class="clear span-2 pink"><?php _e('Monitoring'); ?></label> 
				<span class="span-9 required">
					<select class="span-4" name="status" id="update-status">
						<option value="0"{{if status == 0}} selected="selected"{{/if}}><?php _e('Inactive'); ?></option>
						<option value="1"{{if status == 1}} selected="selected"{{/if}}><?php _e('Active'); ?></option>
					</select>
				</span><br />
			</p>
			<p class="clear">
				<input class="clear button save" type="submit" name="update" value="<?php _e('Update Server'); ?>" />
			</p>
		</fieldset>
	</form>
</div>
<?php
						break;
						case 'services-form':
							//-- NOTE: The following form is not submitted (its values are passed to an ajax function), hence the no need for a nonce, etc.
?>
<div id="add-service" class="popup-form">
	<form action="#" method="post" name="add-service" id="add-service-form">
		<input type="hidden" name="server" value="${server}" id="server" />
		<fieldset>
			<legend><?php _e('Add a New Service'); ?></legend>
			<p>
				<label class="clear span-3"><?php _e('Service'); ?></label> 
				<select class="span-5" name="service" id="service">
					{{each($idx, $val) services}}<option value="${$val.id}">${$val.name}{{if $val.port != '0'}} :${$val.port}{{/if}}</option>{{/each}}
				</select><?php echo FW::img('theme/add.png', __('Add a Service'), array('class' => 'add add-user-service')); ?><br />
				<label class="clear span-3"><?php _e('MMI'); ?></label> 
				<select class="span-5" name="mmi" id="mmi">
					{{each($idx, $val) mmis}}<option value="${$val.min}">${$val.name}</option>{{/each}}
				</select>
			</p>
			<p class="clear">
				<label class="clear span-3"><?php _e('Alert Receiver'); ?></label> 
				<select class="span-5" name="alert" id="alert">
					<option value="">No one...</option>
					{{each($idx, $val) receivers}}<option value="${$val}">${$val}</option>{{/each}}
				</select><?php echo FW::img('theme/add.png', __('Add an email'), array('class' => 'add add-user-receiver')); ?><br />
				<label class="clear span-3"><?php _e('Recovery Alert'); ?></label> 
				<select class="span-5" name="recovery" id="recovery">
					<option value="0"><?php _e('No'); ?></option>
					<option value="1"><?php _e('Yes'); ?></option>
				</select>
				<input class="button" type="submit" name="add" value="<?php _e('Add Service'); ?>" />
			</p>
		</fieldset>
	</form>
</div>
<?php
						break;
						case 'services-update-form':
							//-- NOTE: The following form is not submitted (its values are passed to an ajax function), hence the no need for a nonce, etc.
?>
<div id="update-service" class="popup-form">
	<form action="#" method="post" name="update-service" id="update-service-form">
		<input type="hidden" name="service" value="${service}" id="service" />
		<input type="hidden" name="server" value="${server}" id="server" />
		<fieldset>
			<legend><?php _e('Update Server Service'); ?></legend>
			<p>
				<label class="clear span-3"><?php _e('Service'); ?></label> 
				<span class="static-label">${serviceLabel}</span>
				<label class="clear span-3"><?php _e('MMI'); ?></label> 
				<select class="span-5" name="mmi" id="mmi">
					{{each($idx, $val) mmis}}<option value="${$val.min}"{{if $val.min == mmi}} selected="selected"{{/if}}>${$val.name}</option>{{/each}}
				</select>
			</p>
			<p class="clear">
				<label class="clear span-3"><?php _e('Alert Receiver'); ?></label> 
				<select class="span-5" name="alert" id="alert">
					<option value="">No one...</option>
					{{each($idx, $val) receivers}}<option value="${$val}"{{if $val == alert}} selected="selected"{{/if}}>${$val}</option>{{/each}}
				</select><?php echo FW::img('theme/add.png', __('Add an email'), array('class' => 'add add-user-receiver')); ?><br />
				<label class="clear span-3"><?php _e('Recovery Alert'); ?></label> 
				<select class="span-5" name="recovery" id="recovery">
					<option value="0"><?php _e('No'); ?></option>
					<option value="1"{{if recovery == '1'}} selected="selected"{{/if}}><?php _e('Yes'); ?></option>
				</select>
				<input class="button" type="submit" name="update" value="<?php _e('Update Service'); ?>" />
			</p>
		</fieldset>
	</form>
</div>
<?php
						break;
						case 'user-services-form':
							//-- NOTE: The following form is not submitted (its values are passed to an ajax function), hence the no need for a nonce, etc.
							//-- NOTE 2: The following template requires an extra div, because it's not being parsed, but directly implemented in another popup (prompt)
?>
<div>
<div id="add-user-service" class="popup-form">
	<form action="#" method="post" name="add-user-service" id="add-user-service-form">
		<fieldset class="span-7">
			<legend><?php _e('Add a New Customized Service'); ?></legend>
			<p>
				<label class="span-2 pink"><?php _e('Name'); ?></label> <span class="span-4 required"><input class="span-4" type="text" name="custom-service-name" id="custom-service-name" value="" /></span><br />
				<label class="span-2 pink clear"><?php _e('Port'); ?></label> <span class="span-4 required"><input class="span-4" type="text" name="custom-service-port" id="custom-service-port" value="" /></span><br />
			</p>
			<p class="clear">
				<input class="button" type="submit" name="add" value="<?php _e('Add Custom Service'); ?>" />
			</p>
		</fieldset>
	</form>
</div>
</div>
<?php
						break;
						case 'user-receivers-form':
							//-- NOTE: The following form is not submitted (its values are passed to an ajax function), hence the no need for a nonce, etc.
							//-- NOTE 2: The following template requires an extra div, because it's not being parsed, but directly implemented in another popup (prompt)
?>
<div>
<div id="add-user-receiver" class="popup-form">
	<form action="#" method="post" name="add-user-receiver" id="add-user-receiver-form">
		<fieldset class="span-8">
			<legend><?php _e('Add a New Alert Receiver'); ?></legend>
			<p>
				<label class="span-2 pink"><?php _e('Email'); ?></label> <span class="span-5 required"><input class="span-5" type="text" name="custom-receiver-email" id="custom-receiver-email" value="" /></span><br />
			</p>
			<p class="clear">
				<input class="button" type="submit" name="add" value="<?php _e('Add Alert Receiver'); ?>" />
			</p>
		</fieldset>
	</form>
</div>
</div>
<?php
						break;
						case 'history-log':
?>
<div id="service-history-log">
	<h3 class="pink log"><?php _e('History Log for ${name}'); ?> <small><?php echo FW::url('#', __('(view the uptime chart)'), array('class' => 'green', 'id' => 'view-chart')); ?></small></h3>
	<h3 class="pink chart"><?php _e('Uptime Chart for ${name}'); ?> <small><?php echo FW::url('#', __('(view the history log)'), array('class' => 'green', 'id' => 'view-log')); ?></small></h3>
	<p><?php _e('Started recording at <strong>${since}</strong> on <strong>${server}</strong>'); ?></h3>
	<div class="history-table">
		<table class="history-log">
		<thead>
		<tr>
			<td><?php _e('Went down on'); ?></td>
			<td><?php _e('And back up at'); ?></td>
		</tr>
		</thead>
		<tbody>
		{{each($idx, $val) history}}
		<tr>
			<td>${$val.down}</td>
			<td>${$val.up}</td>
		</tr>
		{{/each}}
		</tbody>
		</table>
	</div>
	<div class="uptime-chart">
		<div id="chart-graph">
		</div>
	</div>
</div>
<?php
						break;
						case 'upgrade-form':
							$billingInfo = Usr::get('billing');
							$currentPlan = Usr::get('plan');
							$startPlan = $currentPlan + 1;

							if ($currentPlan == 4) {
								$startPlan = 1;
							}

							$userPlan = FW::getUserPlan();
							$daysLeft = Sys::days($userPlan->end);
?>
<div id="upgrade-form" class="popup-form">
	<form action="<?php echo ST::$url; ?>account/" method="post" name="upgrade-form">
	<?php Sys::nonce_field('upgrade-plan'); ?>
	<input type="hidden" name="upgradePlan" value="1" />
	<fieldset>
		<legend><?php _e('Upgrade'); ?></legend>
		<p><?php _e('The upgrade is instant.'); ?></p>
		<p><?php _e("You've got to have your billing info filled."); ?></p>
		<p><?php _e("You'll be charged the number of days left in the current plan, and in the renewal date, the full monthly value."); ?></p>
		<p><?php _e("Select a new plan below."); ?></p>
		<p>
<?php
							if ($billingInfo != 0 || $currentPlan == 4) {//-- Only show if there is billing info filled, or a free account
								if ($daysLeft <= 0) {//-- Only show if the plan hasn't expired
									for ($i=$startPlan;$i<4;$i++) {
										$upgradePrice = round((FW::getPlanInfo($i,'price') / 30) * abs($daysLeft), 2);
										$upgradePrice = round($upgradePrice - FW::getPlanInfo($currentPlan,'price'), 2);//-- We now take what the user has paid already

										$paypalURL = '';
										if ($currentPlan == 4) {//-- If it's from a Free plan, a normal subscription is required.
											$paypalURL = FW::getPlanInfo($i,'paypal').'&custom='.Usr::get('id').',u,'.$i;
										} else {
											$paypalURL = 'https://www.paypal.com/cgi-bin/webscr?cmd=_xclick-subscriptions&business=paypal%40wozia%2ept&lc=PT&item_name=SiteLog%20Plan%20Upgrade%20'.$userPlan->name.'-'.FW::getPlanInfo($i,'name').'&a3='.rawurlencode(FW::getPlanInfo($i,'price')).'&p3=1&t3=M&currency_code=USD&button_subtype=services&no_note=1&no_shipping=1&rm=1&return=https%3a%2f%2fsiteloghq%2ecom%2faccount%2fbilling%2f%3fmsg%3dpayment%2dsuccess&cancel_return=https%3a%2f%2fsiteloghq%2ecom%2faccount%2fbilling%2f%3fmsg%3dpayment%2dfailed&bn=PP%2dBuyNowBF%3abtn_buynowCC_LG%2egif%3aNonHosted&notify_url=https%3a%2f%2fsiteloghq%2ecom%2fpaypal%2f&a1='.rawurlencode($upgradePrice).'&p1='.abs($daysLeft).'&t1=D&src=1&custom='.Usr::get('id').',u,'.$i;
										}
?>
			<label class="span-3 clear" for="plan-<?php echo $i; ?>"><input id="plan-<?php echo $i; ?>" class="span-1" type="radio" name="plan" value="<?php echo $i; ?>" data-url="<?php echo $paypalURL; ?>" /> <?php echo FW::getPlanInfo($i,'name'); ?></label>
			<span class="span-8 plan-description" for="plan-<?php echo $i; ?>"><span><?php _e('Upgrade:'); ?> <strong>$<?php echo $upgradePrice; ?></strong></span><br /><span><?php _e('Price:'); ?> <strong>$<?php echo FW::getPlanInfo($i,'price'); ?></strong><?php _e('/ month'); ?></span><br /><span><?php _e('Services:'); ?> <strong><?php echo FW::getPlanInfo($i,'services'); ?></strong></span>, <span><?php _e('Emails:'); ?> <strong><?php echo FW::getPlanInfo($i,'emails'); ?></strong></span>, <span><?php _e('MMI:'); ?> <strong><?php echo FW::getPlanInfo($i,'mmi'); ?></strong> <?php _e('min.'); ?></span></span><br />

<?php
									}
?>
			<input class="clear button" type="submit" name="upgrade" value="<?php _e('Upgrade Plan Now!'); ?>" />
<?php
								} else {
?>
			<span class="red"><?php _e("We're sorry but your plan has already expired. Please contact us to know what to do."); ?></span>
<?php
								}
							} else {
?>
			<span class="red"><?php _e("We're sorry but you haven't filled your billing details yet. Please do that first."); ?></span>
<?php
							}
?>
		</p>
	</fieldset>
	</form>
</div>
<?php
				//-- PayPal Upgrade, single payment for date difference
				// https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=paypal%40wozia%2ept&lc=PT&item_name=SiteLog%20Plan%20Upgrade%20Free%20%2d%20Basic&amount=10%2e00&currency_code=USD&button_subtype=services&no_note=1&no_shipping=1&rm=1&return=https%3a%2f%2fsiteloghq%2ecom%2faccount%2fbilling%2f%3fmsg%3dpayment%2dsuccess&cancel_return=https%3a%2f%2fsiteloghq%2ecom%2faccount%2fbilling%2f%3fmsg%3dpayment%2dfailed&bn=PP%2dBuyNowBF%3abtn_buynowCC_LG%2egif%3aNonHosted&notify_url=https%3a%2f%2fsiteloghq%2ecom%2fpaypal%2f
				// to modify current, add modify=2

				//-- PayPal Subscription
				// Basic: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick-subscriptions&business=paypal%40wozia%2ept&lc=PT&item_name=SiteLog%20Basic&item_number=1&no_note=1&no_shipping=1&rm=1&return=https%3a%2f%2fsiteloghq%2ecom%2faccount%2fbilling%2f%3fmsg%3dpayment%2dsuccess&cancel_return=https%3a%2f%2fsiteloghq%2ecom%2faccount%2fbilling%2f%3fmsg%3dpayment%2dfailed&src=1&a3=9%2e95&p3=1&t3=M&currency_code=USD&bn=PP%2dSubscriptionsBF%3abtn_subscribeCC_LG%2egif%3aNonHosted&notify_url=https%3a%2f%2fsiteloghq%2ecom%2fpaypal%2f
				// Plus: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick-subscriptions&business=paypal%40wozia%2ept&lc=PT&item_name=SiteLog%20Plus&item_number=2&no_note=1&no_shipping=1&rm=1&return=https%3a%2f%2fsiteloghq%2ecom%2faccount%2fbilling%2f%3fmsg%3dpayment%2dsuccess&cancel_return=https%3a%2f%2fsiteloghq%2ecom%2faccount%2fbilling%2f%3fmsg%3dpayment%2dfailed&src=1&a3=24%2e95&p3=1&t3=M&currency_code=USD&bn=PP%2dSubscriptionsBF%3abtn_subscribeCC_LG%2egif%3aNonHosted&notify_url=https%3a%2f%2fsiteloghq%2ecom%2fpaypal%2f
				// Premium: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick-subscriptions&business=paypal%40wozia%2ept&lc=PT&item_name=SiteLog%20Premium&item_number=3&no_note=1&no_shipping=1&rm=1&return=https%3a%2f%2fsiteloghq%2ecom%2faccount%2fbilling%2f%3fmsg%3dpayment%2dsuccess&cancel_return=https%3a%2f%2fsiteloghq%2ecom%2faccount%2fbilling%2f%3fmsg%3dpayment%2dfailed&src=1&a3=49%2e95&p3=1&t3=M&currency_code=USD&bn=PP%2dSubscriptionsBF%3abtn_subscribeCC_LG%2egif%3aNonHosted&notify_url=https%3a%2f%2fsiteloghq%2ecom%2fpaypal%2f

				// Basic (Upgrade subscription with trial): https://www.paypal.com/cgi-bin/webscr?cmd=_xclick-subscriptions&business=paypal%40wozia%2ept&lc=PT&item_name=SiteLog%20Basic&item_number=1&no_note=1&no_shipping=1&rm=1&return=https%3a%2f%2fsiteloghq%2ecom%2faccount%2fbilling%2f%3fmsg%3dpayment%2dsuccess&cancel_return=https%3a%2f%2fsiteloghq%2ecom%2faccount%2fbilling%2f%3fmsg%3dpayment%2dfailed&a1=15%2e00&p1=10&t1=D&src=1&a3=9%2e95&p3=1&t3=M&currency_code=USD&bn=PP%2dSubscriptionsBF%3abtn_subscribeCC_LG%2egif%3aNonHosted
?>
<?php
						break;
						case 'downgrade-form':
							$currentPlan = Usr::get('plan');
							$startPlan = $currentPlan - 1;

							$userPlan = FW::getUserPlan();
							$daysLeft = Sys::days($userPlan->end);
?>
<div id="downgrade-form" class="popup-form">
	<form action="<?php echo ST::$url; ?>account/" method="post" name="downgrade-form">
	<?php Sys::nonce_field('downgrade-plan'); ?>
	<input type="hidden" name="downgradePlan" value="1" />
	<fieldset>
		<legend><?php _e('Downgrade'); ?></legend>
		<p><?php _e('Your plan will be scheduled for downgrade at the end of the paid period.'); ?></p>
		<p><?php _e("Select a new plan below."); ?></p>
		<p>
<?php
							for ($i=$startPlan;$i>0;$i--) {
								$paypalURL = '';
								if ($currentPlan == 4) {//-- If it's from a Free plan, a normal subscription is required.
									$paypalURL = FW::getPlanInfo($i,'paypal').'&custom='.Usr::get('id');
								} else {
									$paypalURL = 'https://www.paypal.com/cgi-bin/webscr?cmd=_xclick-subscriptions&business=paypal%40wozia%2ept&lc=PT&item_name=SiteLog%20Plan%20Downgrade%20'.$userPlan->name.'-'.FW::getPlanInfo($i,'name').'&a3='.rawurlencode(FW::getPlanInfo($i,'price')).'&p3=1&t3=M&currency_code=USD&button_subtype=services&no_note=1&no_shipping=1&rm=1&return=https%3a%2f%2fsiteloghq%2ecom%2faccount%2fbilling%2f%3fmsg%3dpayment%2dsuccess&cancel_return=https%3a%2f%2fsiteloghq%2ecom%2faccount%2fbilling%2f%3fmsg%3dpayment%2dfailed&bn=PP%2dBuyNowBF%3abtn_buynowCC_LG%2egif%3aNonHosted&notify_url=https%3a%2f%2fsiteloghq%2ecom%2fpaypal%2f&a1='.rawurlencode(0.00).'&p1='.abs($daysLeft).'&t1=D&src=1&custom='.Usr::get('id').',d,'.$i;
								}
?>
			<label class="span-3 clear" for="plan-<?php echo $i; ?>"><input id="plan-<?php echo $i; ?>" class="span-1" type="radio" name="plan" value="<?php echo $i; ?>" data-url="<?php echo $paypalURL; ?>" /> <?php echo FW::getPlanInfo($i,'name'); ?></label>
			<span class="span-8 plan-description" for="plan-<?php echo $i; ?>"><span><?php _e('Price:'); ?> <strong>$<?php echo FW::getPlanInfo($i,'price'); ?></strong><?php _e('/ month'); ?></span><br /><span><?php _e('Services:'); ?> <strong><?php echo FW::getPlanInfo($i,'services'); ?></strong></span>, <span><?php _e('Emails:'); ?> <strong><?php echo FW::getPlanInfo($i,'emails'); ?></strong></span>, <span><?php _e('MMI:'); ?> <strong><?php echo FW::getPlanInfo($i,'mmi'); ?></strong> <?php _e('min.'); ?></span></span><br />

<?php
							}
							$i = 4;//-- Free plan
?>
			<label class="span-3 clear" for="plan-<?php echo $i; ?>"><input id="plan-<?php echo $i; ?>" class="span-1" type="radio" name="plan" value="<?php echo $i; ?>" data-url="" /> <?php echo FW::getPlanInfo($i,'name'); ?></label>
			<span class="span-8 plan-description" for="plan-<?php echo $i; ?>"><span><?php _e('Price:'); ?> <strong><?php _e('Free'); ?></strong></span><br /><span><?php _e('Services:'); ?> <strong><?php echo FW::getPlanInfo($i,'services'); ?></strong></span>, <span><?php _e('Emails:'); ?> <strong><?php echo FW::getPlanInfo($i,'emails'); ?></strong></span>, <span><?php _e('MMI:'); ?> <strong><?php echo FW::getPlanInfo($i,'mmi'); ?></strong> <?php _e('min.'); ?></span></span><br />

			<input class="clear button" type="submit" name="upgrade" value="<?php _e('Schedule Plan Downgrade'); ?>" />
		</p>
	</fieldset>
	</form>
</div>
<?php
						break;
					}
?>
</script>
<?php
					App::stop();
				} else {
					$result->error = __('Invalid nonce, please try again.');
				}
			break;
		}
		echo json_encode($result);
	}	
}
?>