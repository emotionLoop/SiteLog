<?php
/**
 * Account is the account component class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Component
 * @category Account
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.1.0 BETA
 */
class Account extends Component {
	private static $tbl_tips = 'tbl_tips';//-- tools, scripts & tips table

	private static $page = '';//-- Current page
	
	public static function prefunctions() {//-- Functions to be loaded before processing. Determine action and theme
		if (!Usr::logged()) {
			//-- Listen for login
			$doLogin = (bool) SS::get('doLogin','post',0,'int');
			if ($doLogin) {
				$nonce = SS::get('_wznonce','post','','string');
				if (Sys::verify_nonce($nonce,'do-login')) {
					self::doLogin();
				} else {
					FW::redirect('?msg=time#!/login');
				}
			}

			//-- Listen for recover password
			$doRecover = (bool) SS::get('doRecover','post',0,'int');
			if ($doRecover) {
				$nonce = SS::get('_wznonce','post','','string');
				if (Sys::verify_nonce($nonce,'do-recover')) {
					self::doRecover();
				} else {
					FW::redirect('?msg=time#!/lost-password');
				}
			}

			//-- Listen for signup
			$doSignup = (bool) SS::get('doSignup','post',0,'int');
			if ($doSignup) {
				$nonce = SS::get('_wznonce','post','','string');
				if (Sys::verify_nonce($nonce,'do-signup')) {
					self::doSignup();
				} else {
					FW::redirect('plans-and-pricing/?msg=time');
				}
			}
		
			FW::redirect('?msg=private');
		} else {
			//-- Listen for logout
			$doLogout = (bool) SS::get('doLogout','get',0,'int');
			if ($doLogout) {
				Usr::logout();
				FW::redirect(str_replace('https://', 'http://', ST::$url).'?msg=logout');
			}

			//-- Listen for account updates
			$accountUpdate = (bool) SS::get('accountUpdate','post',0,'int');
			if ($accountUpdate) {
				$nonce = SS::get('_wznonce','post','','string');
				if (Sys::verify_nonce($nonce,'account-update')) {
					self::accountUpdate();
				} else {
					FW::redirect('account/details/?msg=time');
				}
			}

			//-- Listen for plan downgrade
			$downgradePlan = (bool) SS::get('downgradePlan','post',0,'int');
			if ($downgradePlan) {
				$nonce = SS::get('_wznonce','post','','string');
				if (Sys::verify_nonce($nonce,'downgrade-plan')) {
					self::downgradePlan();
				} else {
					FW::redirect('account/upgrade/?msg=time');
				}
			}

			//-- Listen for account billing updates
			$doBilling = (bool) SS::get('doBilling','post',0,'int');
			if ($doBilling) {
				$nonce = SS::get('_wznonce','post','','string');
				if (Sys::verify_nonce($nonce,'do-billing')) {
					self::doBilling();
				} else {
					FW::redirect('account/billing/?msg=time');
				}
			}

			//-- Listen for account deletion
			$cancelAccount = (bool) SS::get('cancelAccount','post',0,'int');
			if ($cancelAccount) {
				$nonce = SS::get('_wznonce','post','','string');
				if (Sys::verify_nonce($nonce,'cancel-account')) {
					self::cancelAccount();
				} else {
					FW::redirect('account/details/?msg=time');
				}
			}
		}

		//-- Figure out which page should we be showing
		foreach (ST::$segs as $seg) {
			if (empty($seg)) continue;

			switch ($seg) {
				case 'tools':
				case 'billing':
				case 'upgrade':
				case 'details':
					self::$page = $seg;
				break;
				default:
					self::$page = 'details';
				break;
			}
		}

		//-- Parse error/success messages
		$error = SS::get('msg','get','','string');
		if (!empty($error)) {
			switch ($error) {
				case 'email-required':
					FW::error(__("To change your email you need to fill in your current password."));
				break;
				case 'password-required':
					FW::error(__("To change your password, you need to fill in your current password."));
				break;
				case 'cancel-plan':
					FW::msg(__("Your plan has been scheduled for cancelling... nice to meet you!"));
				break;
				case 'downgrade-plan':
					FW::msg(__("Your plan has been scheduled for downgrade... you still love us, right?"));
				break;
				case 'upgrade-plan':
					FW::msg(__("Yeehaw! Your plan has been upgraded successfully!"));
				break;
				case 'upgrade-plan-failed':
					FW::error(__("Oops! Seems something's wrong with your billing info. Please fix it and try again!"));
				break;
				case 'billing-updated':
					FW::msg(__("Wohoo! Your Billing info was updated successfully!"));
				break;
				case 'billing-error':
					FW::error(__("Oh boy! It seems your Billing information was not valid! Please try again."));
				break;
				case 'payment-success':
					FW::msg(__("Nice! Your payment seems to have been made successfully! We'll have our systems double-check though ;)"));
				break;
				case 'payment-failed':
					FW::error(__("Uh-oh! It seems your payment didn't go through. Please try again."));
				break;
			}
		}

		FW::addJS('account.js', array('defer' => 'defer'));

		Sys::startCache();
?>
<script defer="defer">
lng.account = {
	'cancel': "<?php _e("Are you sure you want to cancel your account?<br />ALL Your data will be deleted INSTANTLY and you won't be able to retrieve it again!<br />There are no refunds."); ?>",//--'
	'terms': '<?php _e('Sorry, but you have to confirm you understand and agree with our cancellation policy.'); ?>',
	'cancelPlan': '<?php _e("Your plan will be scheduled for cancelling at the end of the paid period. <strong>You will not be billed again</strong>. If instead you want to close your account immediately, you should do that in the \"Personal Details\" section."); ?>',
	'selectPlan': '<?php _e('You have to select a plan!'); ?>'
};

nonces.getTip = '<?php echo Sys::create_nonce('get-tip'); ?>';
nonces.cancelPlan = '<?php echo Sys::create_nonce('cancel-plan'); ?>';
</script>
<?php
		$js = Sys::endCache();
		FW::set('head',$js);
	}
	
	public static function main() {//-- Stuff to load when the component is called, on content part for the template
?>
	<article class="span-22 prepend-1 append-1">
		<h1 class="pink"><?php _e('My Account'); ?></h1>
		<nav id="account-menu">
			<ul>
				<li class="first<?php if (self::$page == 'details') { ?> active<?php } ?>"><?php echo FW::url('account/details/',__('Personal Details')); ?></li><li<?php if (self::$page == 'upgrade') { ?> class="active"<?php } ?>><?php echo FW::url('account/upgrade/',__('Upgrade/Downgrade')); ?></li><li<?php if (self::$page == 'billing') { ?> class="active"<?php } ?>><?php echo FW::url('account/billing/',__('Billing')); ?></li><li<?php if (self::$page == 'tools') { ?> class="active"<?php } ?>><?php echo FW::url('account/tools/',__('Tools, Scripts & Tips')); ?></li>
			</ul>
		</nav>
<?php
		switch (self::$page) {
			case 'tools':
				$tips = self::getTips();
?>
		<div id="tools">
			<h3><?php _e('Below are some tools, scripts and tips for you to look at.'); ?><br />
			<?php _e('Just click the titles and a dialog with the content will show up.'); ?></h3>
			<ul>
<?php
				if ($tips) {
					$c = 0;
					foreach ($tips as $tip) {
						$class = ($c++%2) ? 'pink' : 'green';
?>
				<li><?php echo FW::url('#!/'.$tip->sef_name, $tip->name, array('class' => $class, 'data-id' => $tip->id, 'data-sef' => $tip->sef_name)); ?></li>
<?php						
					}
				}
?>
			</ul>
		</div>
<?php
			break;
			case 'billing':
				$billingInfo = Usr::get('billing');
				$currentPlan = Usr::get('plan');

				$userPlan = FW::getUserPlan();
?>
		<form action="<?php echo ST::$url; ?>account/billing/" method="post" id="billing-form">
			<?php Sys::nonce_field('do-billing'); ?>
			<input type="hidden" name="doBilling" value="1" />
			<p>
				<label class="span-2 pink"><?php _e('Name'); ?></label> <input class="span-9" type="text" name="name" id="name" value="<?php echo Usr::get('billingName'); ?>" /><br />
				<label class="clear span-2 pink"><?php _e('Address'); ?></label> <span class="span-9 required"><input id="address" class="span-9" type="text" name="address" value="<?php echo Usr::get('billingAddress'); ?>" /></span><br />
				<label class="clear span-2 pink"><?php _e('Zip Code'); ?></label> <span class="span-6 required zip"><input id="zipCode" class="span-6" type="text" name="zipCode" value="<?php echo Usr::get('billingZipCode'); ?>" /></span><br />
				<label class="clear span-2 pink"><?php _e('Country'); ?></label><?php Frm::select('country', FW::getCountryArray(), Usr::get('billingCountry'), null, array('class' => 'span-4'), false); ?><br />
				<label class="clear span-2"><?php _e('VAT ID'); ?></label> <input class="span-9" type="text" name="vatID" id="vatID" value="<?php echo Usr::get('billingVATID'); ?>" /><br />
			</p>
			<p class="clear">
				<input class="clear button save" type="submit" name="save" value="<?php _e('Save'); ?>" />
<?php
				if ($userPlan->plan != 4) {
					if ($billingInfo != 0) {
?>
				<span class="span-9 prepend-1"><input class="button pink credit" type="button" name="credit" value="<?php _e('View your PayPal Subscription'); ?>" data-url="https://www.paypal.com/cgi-bin/webscr?cmd=_subscr-find&alias=paypal%40wozia%2ept" id="paypal-subscription" /></span>
<?php
					} else {
						$daysLeft = Sys::days($userPlan->end);
						$payPalURL = str_replace('&amount='.rawurlencode(FW::getPlanInfo($currentPlan,'price')), '', $userPlan->paypal).'&a3='.rawurlencode(FW::getPlanInfo($currentPlan,'price')).'&p3=1&t3=M&a1='.rawurlencode(0.00).'&p1='.abs($daysLeft).'&t1=D&src=1&custom='.Usr::get('id');
?>
				<span class="span-9 prepend-1"><input class="button pink credit" type="button" name="credit" value="<?php _e('Schedule Your PayPal Subscription'); ?>" data-url="<?php echo $payPalURL; ?>" id="paypal-subscription" /></span>
<?php
					}
				}
?>
			</p>
			<p class="clear"><?php _e("You will need a PayPal account to subscribe to our service. We expect to implement an easier credit card payment system in the future."); ?></p>
		</form>
<?php
			break;
			case 'upgrade':
				$plan = FW::getUserPlan();
				
				$statusDays = new stdClass();
				$statusDays->val = Sys::days($plan->end);
				$statusDays->number = abs($statusDays->val);
				$statusDays->text = ($statusDays->number != 1) ? __('days') : __('day');
				$statusDays->suffix = ($statusDays->val < 0) ? __('left') : __('overdue');
				$statusDays->class = ($statusDays->val < 0) ? 'green' : 'red';
?>
		<div class="span-8 current-plan">
			<p><span class="span-3"><?php _e('Your plan:'); ?></span> <strong><?php echo $plan->name; ?></strong></p>
			<p><span class="span-3"><?php _e('Services:'); ?></span> <strong><?php echo $plan->services; ?> /</strong> <?php echo (empty($plan->max_services) ? __('unlimited') : $plan->max_services); ?></p>
			<p><span class="span-3"><?php _e('Emails:'); ?></span> <strong><?php echo $plan->emails; ?> /</strong> <?php echo (empty($plan->max_emails) ? __('unlimited') : $plan->max_emails); ?></p>
			<p><span class="span-3"><?php _e('MMI:'); ?></span> <strong><?php echo $plan->mmi; ?></strong> <?php _e('minutes'); ?></p>
			<p><span class="span-3"><?php _e('Price:'); ?></span> <strong>$<?php echo $plan->price; ?></strong> <?php _e('/ month'); ?></p>
		</div>
		<div class="span-12 status">
			<p><span><?php _e('Status:'); ?></span> <strong class="<?php echo $statusDays->class; ?>"><?php echo $statusDays->number.' '.$statusDays->text.' '.$statusDays->suffix; ?></strong></p>
<?php
				if (!empty($plan->extra)) {
					$extra = json_decode($plan->extra);

					switch ($extra->what) {
						case 'downgrade':
							$newPlan = FW::getPlan($extra->plan);
?>
			<p><span><?php _e('Scheduled:'); ?></span> <strong class="red"><?php echo __('Downgrading to').' '.$newPlan->name; ?></strong></p>
<?php
						break;
						case 'cancel':
?>
			<p><span><?php _e('Scheduled:'); ?></span> <strong class="red"><?php echo __('Cancelling'); ?></strong></p>
<?php
						break;
					}
				}
?>
		</div>	
		<div class="clear prepend-1 buttons">
<?php
				if ($plan->plan != 3) {
?>
			<span><input class="button upgrade" type="submit" name="upgrade" value="<?php _e('Upgrade'); ?>" id="upgrade-plan" /></span>
<?php
				}
				if ($plan->plan != 4) {
?>
			<span><input class="button downgrade" type="submit" name="downgrade" value="<?php _e('Downgrade'); ?>" id="downgrade-plan" /><br /><small class="downgrade"><?php _e('Nooooooooo.'); ?></small></span>
<?php
				}
?>
			<span><input class="button cancel" type="submit" name="cancel" value="<?php _e('Cancel Plan'); ?>" id="cancel-plan" /><br /><small class="cancel"><?php _e('When clicked,<br /> a rainbow disappears.'); ?></small></span>
		</div>
<?php
			break;
			case 'details':
			default:
?>
		<form action="<?php echo ST::$url; ?>account/details/" method="post" id="account-form">
			<?php Sys::nonce_field('account-update'); ?>
			<input type="hidden" name="accountUpdate" value="1" />
			<p>
				<label class="span-4 pink"><?php _e('Name'); ?></label> <span class="span-9 required"><input id="name" class="span-9" type="text" name="name" value="<?php echo Usr::get('name'); ?>" /></span><br />
				<label class="clear span-4 pink"><?php _e('Email'); ?></label> <span class="span-9 required"><input id="email" class="span-9" type="text" name="email" value="<?php echo Usr::get('email'); ?>" /></span><br />
				<label class="clear span-4"><?php _e('Current Password'); ?></label> <input id="current-password" class="span-9" type="password" name="current-password" autocomplete="off" /> <small class="pwd"><?php _e('Fill only if you change email or password'); ?></small><br />
				<label class="clear span-4"><?php _e('New Password'); ?></label> <input id="new-password" class="span-9" type="password" name="new-password" autocomplete="off" /> <small class="pwd"><?php _e('Fill only if changing password'); ?></small><br />
				<label class="clear span-4"><?php _e('Company'); ?></label> <input class="span-9" type="text" name="company" value="<?php echo Usr::get('company'); ?>" /><br />
				<label class="clear span-4 pink"><?php _e('Country'); ?></label> <?php Frm::select('country', FW::getCountryArray(), Usr::get('country'), null, array('class' => 'span-4'), false); ?><br />
				<label class="clear span-4 pink"><?php _e('Language'); ?></label> <?php Frm::select('language', FW::getLanguageArray(), Usr::get('language'), null, array('class' => 'span-4')); ?>
			</p>
			<p class="clear">
				<input class="clear button save" type="submit" name="save" value="<?php _e('Save'); ?>" />
				<input id="cancel-account" class="button cancel" type="submit" name="cancel" value="<?php _e('Cancel/Close Account'); ?>" /><br /><small class="clear cancel span-8 prepend-10"><?php _e('When clicked,<br /> a unicorn dies.'); ?></small>
			</p>
		</form>
<?php
			break;
		}
?>
	</article>
<?php
	}

	public static function ajax() { 
		$nonce 		= SS::get('_wznonce','post','','string');
		$action 	= SS::get('action','post','','string');
		
		$res = new stdClass();
		$res->error = '';
		$res->data = new stdClass();

		switch ($action) {
			case 'get-tip':
				$id = SS::get('tip','post',0,'int');

				if (Sys::verify_nonce($nonce, 'get-tip')) {
					$data = self::getTip($id);
					if ($data) {
						$res->data = '<div class="tool-script-tip">'.$data->content.'</div>';
					} else {
						$res->error = __("That tip doesn't seem to exist...");
					}
				} else {
					$res->error = __('Something bad happened there... try again... if you dare.');
				}
			break;
			case 'cancel-plan':
				if (Sys::verify_nonce($nonce, 'cancel-plan')) {
					self::cancelPlan();
					$res->data = 'account/upgrade/?msg=cancel-plan';
				} else {
					$res->error = __('Something bad happened there... try again... if you dare.');
				}
			break;
		}

		echo json_encode($res);
	}

	public static function getTips() {
		$sql = "SELECT * FROM `".self::$tbl_tips."` WHERE `status` = 1 ORDER BY `position` ASC";
		$results = DB::execute($sql);

		if (Lng::$code != ST::get('default_lng')) {
			$tmpArray = $results;
			$results = array();

			foreach ($tmpArray as $result) {
				$result->name = Lng::get($result->id, 'name', 'tbl_tips');
				$result->content = Lng::get($result->id, 'content', 'tbl_tips');

				$results[] = $result;
			}
		}

		return $results;
	}

	public static function getTip($id) {
		$sql = "SELECT * FROM `".self::$tbl_tips."` WHERE `id` = '".(int)$id."' AND `status` = 1";
		$result = DB::sexecute($sql);

		if (Lng::$code != ST::get('default_lng')) {
			$result->name = Lng::get($result->id, 'name', 'tbl_tips');
			$result->content = Lng::get($result->id, 'content', 'tbl_tips');
		}

		return $result;
	}

	//-- Do Login
	private static function doLogin() {
		$email 		= SS::get('email','post','','string');
		$password 	= SS::get('password','post','','string');
		$remember 	= (bool) SS::get('remember','post',0,'int');
		
		if (empty($password) || empty($email) || !Sys::valid('email',$email)) {
			FW::redirect('?msg=required#!/login');
		}
		
		if (!$remember) { 
			$remember = null;
		} else {
			$days = 15;
			$remember = 60 * 60 * 24 * $days;
		}

		if (Usr::login($email, $password, false, $remember)) {
			FW::redirect('status/');
		} else {
			FW::redirect('?msg=login#!/login');
		}
	}

	//-- Do Recover Password
	private static function doRecover() {
		$email = SS::get('email','post','','string');
		
		if (empty($email) || !Sys::valid('email',$email)) {
			FW::redirect('?msg=required#!/lost-password');
		}

		if (Usr::recoverPassword($email)) {
			FW::redirect('?msg=recover');
		} else {
			FW::redirect('?msg=login#!/lost-password');
		}
	}

	//-- Do Signup
	private static function doSignup() {
		$name 	= SS::get('name','post','','string');
		$email 	= SS::get('email','post','','string');
		$plan 	= SS::get('plan','post','','string');
		$terms 	= (bool) SS::get('terms','post',0,'int');
		
		if (empty($name) || empty($email) || empty($plan) || !$terms || !Sys::valid('email',$email)) {
			FW::redirect('plans-and-pricing/?msg=required');
		}
		
		if (Usr::isDuplicate($email)) {
			FW::redirect('plans-and-pricing/?msg=duplicate');
		}
		
		if (Usr::register($name, $email, $plan)) {
			FW::redirect('?msg=signup');
		} else {
			FW::redirect('plans-and-pricing/?msg=error');
		}
	}

	//-- Account Update
	private static function accountUpdate() {
		$name 		= SS::get('name','post','','string');
		$email 		= SS::get('email','post','','string');
		$cPassword 	= SS::get('current-password','post','','string');
		$nPassword 	= SS::get('new-password','post','','string');
		$company 	= SS::get('company','post','','string');
		$country 	= SS::get('country','post','','string');
		$language 	= SS::get('language','post','','string');
		
		if (empty($name) || empty($email) || empty($country) || empty($language) || !Sys::valid('email',$email)) {
			FW::redirect('account/details/?msg=required');
		}

		//-- If trying to change email, verify current password
		if ($email != Usr::get('email') && (empty($cPassword) || !Usr::isPasswordValid($cPassword))) {
			FW::redirect('account/details/?msg=email-required');
		}

		//-- If trying to change password, verify current password
		if (!empty($nPassword) && (empty($cPassword) || !Usr::isPasswordValid($cPassword))) {
			FW::redirect('account/details/?msg=password-required');
		}
		
		if (Usr::isDuplicate($email)) {
			FW::redirect('account/details/?msg=duplicate');
		}
		
		if (!Usr::updateAccount($name, $email, $nPassword, $company, $country, $language)) {
			FW::redirect('account/details/?msg=error');
		}
	}

	//-- Schedule Plan Downgrade
	private static function downgradePlan() {
		$id 		= Usr::get('plan');
		$user 		= Usr::get('id');
		$newPlanID 	= SS::get('plan','post',0,'int');

		if (empty($id) || empty($user) || empty($newPlanID) || $newPlanID == $id || ($newPlanID > $id && $newPlanID != 4) || $newPlanID != 4) {//-- Force only this form to allow downgrades to Free plans.
			FW::redirect('account/upgrade/?msg=required');
		}

		$tmp = new stdClass();
		$tmp->what = 'downgrade';
		$tmp->plan = $newPlanID;

		$fields = array(
			'extra' => json_encode($tmp)
		);

		$sql = DB::build($fields, 'tbl_user_plans', 'update', "WHERE `plan` = '".(int)$id."' AND `user` = '".(int)$user."'");
		DB::query($sql);

		FW::redirect('account/upgrade/?msg=downgrade-plan');
	}

	//-- Cancel Account
	private static function cancelAccount() {
		$password 	= SS::get('password','post','','string');
		$terms 		= (bool) SS::get('terms','post',0,'int');
		
		if (empty($password) || !$terms) {
			FW::redirect('account/details/?msg=required');
		}
		
		if (!Usr::isPasswordValid($password)) {
			FW::redirect('account/details/?msg=required');
		}
		
		if (!Usr::deleteAccount()) {
			FW::redirect('account/details/?msg=error');
		}
	}

	//-- Schedule Plan Cancelling
	private static function cancelPlan() {
		$id = Usr::get('plan');
		$user = Usr::get('id');

		$tmp = new stdClass();
		$tmp->what = 'cancel';

		$fields = array(
			'extra' => json_encode($tmp)
		);

		$sql = DB::build($fields, 'tbl_user_plans', 'update', "WHERE `plan` = '".(int)$id."' AND `user` = '".(int)$user."'");
		DB::query($sql);
	}

	//-- Save or Update Billing data
	private static function doBilling() {
		$name 		= SS::get('name','post','','string');
		$address 	= SS::get('address','post','','string');
		$zipCode 	= SS::get('zipCode','post','','string');
		$country 	= SS::get('country','post','','string');
		$vatID 		= SS::get('vatID','post','','string');
		
		if (empty($name) || empty($address) || empty($zipCode) || empty($country)) {
			FW::redirect('account/billing/?msg=required');
		}

		if (Usr::saveBillingData($name, $address, $zipCode, $country, $vatID)) {
			FW::redirect('account/billing/?msg=billing-updated');
		} else {
			FW::redirect('account/billing/?msg=billing-error');
		}
	}
}
?>
