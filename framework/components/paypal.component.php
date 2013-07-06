<?php
/**
 * PayPal is the paypal ipn component class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Component
 * @category PayPal
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.1.0 BETA
 */
class PayPal extends Component {
	public static function prefunctions() {//-- Functions to be loaded before processing. Determine action and theme

		//-- Custom variables:
		/*
		uid = user id
		wzt = type, 'u' for upgrade, 'd' for downgrade
		wzto = new plan id
		*/

		//-- Read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';

		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}

		//-- Post back to PayPal system to validate
		$header = "";
		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);

		if ($fp) {
			fputs($fp, $header.$req);
			$found = false;
			while (!feof($fp) && !$found) {
				$res = fgets($fp, 1024);
				if (strcmp($res, "VERIFIED") == 0) {
					//-- Get the variables that matter
					$pp = new stdClass();
					$pp->txn_id 		= SS::get('txn_id','post','','string');
					$pp->ipn_track_id	= SS::get('ipn_track_id','post','','string');
					$pp->txn_type 		= SS::get('txn_type','post','','string');
					$pp->subscr_id 		= SS::get('subscr_id','post','','string');
					$pp->item_number 	= SS::get('item_number','post',0,'int');
					$pp->payment_status = SS::get('payment_status','post','','string');
					$pp->payer_email 	= SS::get('payer_email','post','','string');
					$pp->uid 			= 0;//SS::get('uid','post',0,'int');
					$pp->wzt 			= '';//SS::get('wzt','post','','string');
					$pp->wzto 			= '';//SS::get('wzto','post','','string');
					$pp->custom 		= SS::get('custom','post','','string');
					$pp->receiver_email = SS::get('receiver_email','post','','string');
					$pp->amount1 		= SS::get('amount1','post','','string');
					$pp->amount3 		= SS::get('amount3','post','','string');

					$tmpCustom = explode(',', $pp->custom);

					if ($tmpCustom) {
						if (count($tmpCustom) == 1) {
							$pp->uid = (int)$tmpCustom[0];
						} elseif (count($tmpCustom) == 3) {
							$pp->uid = (int)$tmpCustom[0];
							$pp->wzt = $tmpCustom[1];
							$pp->wzto = $tmpCustom[2];
						}
					}

					//-- Check that ipn_track_id has not been previously processed
					if (!self::wasIPNProcessed($pp)) {
						//-- Process payment
						switch ($pp->txn_type) {
							case 'subscr_signup':
								switch ($pp->wzt) {
									case 'u'://-- Upgrade
										self::upgradePlan($pp->uid, $pp->wzto, $pp->subscr_id);
									break;
									case 'd'://-- Downgrade
										self::downgradePlan($pp->uid, $pp->wzto, $pp->subscr_id);
									break;
									default://-- Regular Signup
										self::subscribedPlan($pp->uid, $pp->subscr_id);
									break;
								}
								$found = true;
							break;
							case 'subscr_payment'://-- Accept payment and renew
								self::renewPlan($pp->uid);
								$found = true;
							break;
							case 'subscr_failed'://-- Failed to subscribe, warn admin
								self::warnPaymentFailed($pp->uid);
								$found = true;
							break;
							case 'subscr_cancel'://-- Accept subscription cancelling and schedule cancel account
								self::cancelPlan($pp->uid);//-- HERE: Comment this if you're manually cancelling a subscription, and uncomment afterwards.
								$found = true;
							break;
						}

						//-- Log the IPN
						self::logIPNProcess($pp);
					} else {
						//-- Warn admin about an IPN retry (after the email notice below is commented)
						//self::warnAboutIPNRetry($pp);
					}
				} else {
					//-- Do nothing
				}
			}
			fclose($fp);
		}

		//-- Log and send email (this should be commented after proven everything is ok)
		Sys::startCache();

		echo '<p><strong>PP</strong><br />';
		var_dump($pp);
		echo '</p>';

		echo '<p><strong>POST</strong><br />';
		var_dump($_POST);
		echo '</p>';

		echo '<p><strong>GET</strong><br />';
		var_dump($_GET);
		echo '</p>';

		echo '<p><strong>SERVER</strong><br />';
		var_dump($_SERVER);
		echo '</p>';

		$msg = Sys::endCache();

		FW::mail('SiteLog', ST::get('default_email'), ST::get('default_email'), 'PayPal IPN LOG', $msg);

		FW::redirect();//-- After all is done, redirect to the homepage
	}
	
	public static function main() {//-- Stuff to load when the component is called, on content part for the template
	}

	//-- Accept subscription and update billing id
	private static function subscribedPlan($userID, $subscriberID) {
		//if (empty($subscriberID)) {
			$subscriberID = 1;
		//}

		$fields = array(
			'billing' => 1//$subscriberID
		);
		$sql = DB::build($fields, 'wz_users', 'update', "WHERE `id` = '".(int)$userID."'");
		DB::query($sql);

		return true;
	}

	//-- Accept payment and upgrade (also notify admin to cancel old subscription)
	private static function upgradePlan($userID, $newPlanID, $subscriberID) {
		$id = self::getUserPlanID($userID);

		if (empty($id) || empty($userID) || empty($newPlanID) || $newPlanID == $id || ($newPlanID < $id && $id != 4) || $newPlanID == 4) {
			return false;
		}

		//if (empty($subscriberID)) {
			$subscriberID = 1;
		//}

		$billingInfo = self::getUserBillingID($userID);

		$newPlan = FW::getPlan($newPlanID);

		$userPlan = self::getUserPlan($userID);
		$daysLeft = Sys::days($userPlan->end);

		if ($daysLeft > 0 || !$newPlan || !$userPlan || (empty($billingInfo) && $userPlan->plan != 4)) {
			return false;
		}

		$upgradePrice = round(($newPlan->price / 30) * abs($daysLeft), 2);
		$upgradePrice = round($upgradePrice - FW::getPlanInfo($id,'price'), 2);//-- We now take what the user has paid already

		$fields = array(
			'plan' => $newPlan->id,
			'max_services' => $newPlan->services,
			'max_emails' => $newPlan->emails,
			'mmi' => $newPlan->mmi,
			'price' => $newPlan->price,
			'update' => date('YmdHis')
		);
		$sql = DB::build($fields, 'tbl_user_plans', 'update', "WHERE `plan` = '".(int)$id."' AND `user` = '".(int)$userID."'");
		DB::query($sql);

		$fields = array(
			'plan' => $newPlan->id,
			'billing' => 1//$subscriberID
		);
		$sql = DB::build($fields, 'wz_users', 'update', "WHERE `id` = '".(int)$userID."'");
		DB::query($sql);

		$billingURL = str_replace('http://','https://',ST::$url).'account/billing/';

		$user = self::getUser($userID);

		//-- Send Email
		$tpl = FW::eTemplate('payment_successful_upgrade');
		$tpl_search = array('%%USER%%', '%%AMOUNT%%', '%%BILLINGURL%%');
		$tpl_replace = array($user->name, $upgradePrice, $billingURL);
		
		$msg = str_replace($tpl_search, $tpl_replace, $tpl->msg);
		FW::mail($tpl->from_name, $tpl->from_email, $user->email, $tpl->subject, $msg);

		return true;
	}

	//-- "Accept payment" and downgrade (also notify admin to cancel old subscription)
	private static function downgradePlan($userID, $newPlanID, $subscriberID) {
		$id = self::getUserPlanID($userID);

		if (empty($id) || empty($userID) || empty($newPlanID) || $newPlanID == $id || ($newPlanID > $id && $newPlanID != 4)) {
			return false;
		}

		//if (empty($subscriberID)) {
			$subscriberID = 1;
		//}

		if ($newPlanID == 4) {
			$subscriberID = 0;
		}

		$tmp = new stdClass();
		$tmp->what = 'downgrade';
		$tmp->plan = $newPlanID;

		$fields = array(
			'extra' => json_encode($tmp),
			'billing' => $subscriberID
		);

		$sql = DB::build($fields, 'tbl_user_plans', 'update', "WHERE `plan` = '".(int)$id."' AND `user` = '".(int)$userID."'");
		DB::query($sql);

		return true;
	}

	private static function renewPlan($userID) {
		$user = self::getUser($userID);
		$userPlan = self::getUserPlan($userID);

		$now = date('YmdHis');
		$thirtyDaysAhead = date('YmdHis', time() + 2592000);

		$billingURL = str_replace('http://','https://',ST::$url).'account/billing/';
		$accountURL = str_replace('http://','https://',ST::$url).'account/';

		$sql = "UPDATE `tbl_user_plans` SET `update` = '".DB::prepare($now)."', `end` = '".DB::prepare($thirtyDaysAhead)."', `emails` = 0 WHERE `id` = '".(int)$userPlan->id."'";
		DB::query($sql);

		//-- Send Email
		$tpl = FW::eTemplate('payment_successful');
		$tpl_search = array('%%USER%%', '%%AMOUNT%%', '%%BILLINGURL%%');
		$tpl_replace = array($user->name, $userPlan->price, $billingURL);
		
		$msg = str_replace($tpl_search, $tpl_replace, $tpl->msg);
		FW::mail($tpl->from_name, $tpl->from_email, $user->email, $tpl->subject, $msg);
	}

	private static function warnPaymentFailed($userID) {
		$user = self::getUser($userID);
		$userPlan = self::getUserPlan($userID);

		$billingURL = str_replace('http://','https://',ST::$url).'account/billing/';
		$accountURL = str_replace('http://','https://',ST::$url).'account/';
		//-- Send Email
		$tpl = FW::eTemplate('payment_failed');
		$tpl_search = array('%%USER%%', '%%AMOUNT%%', '%%BILLINGURL%%', '%%ACCOUNTURL%%');
		$tpl_replace = array($user->name, $userPlan->price, $billingURL, $accountURL);
		
		$msg = str_replace($tpl_search, $tpl_replace, $tpl->msg);
		FW::mail($tpl->from_name, $tpl->from_email, $user->email, $tpl->subject, $msg);
	}

	private static function cancelPlan($userID) {
		$sql = "UPDATE `tbl_user_plans` SET `status` = 0 WHERE `user` = '".(int)$userID."'";
		DB::query($sql);

		$user = self::getUser($userID);

		if ($user) {
			//-- Send Email
			$tpl = FW::eTemplate('plan_cancelled');
			$tpl_search = array('%%USER%%');
			$tpl_replace = array($user->name);
			
			$msg = str_replace($tpl_search, $tpl_replace, $tpl->msg);
			FW::mail($tpl->from_name, $tpl->from_email, $user->email, $tpl->subject, $msg);
		}
	}

	//-- Get the user data for a given user ID
	private static function getUser($userID) {
		$sql = "SELECT * FROM wz_users WHERE id = ".(int)$userID."";
		$result = DB::sexecute($sql);

		return $result;
	}

	//-- Get the current plan for a given user ID
	private static function getUserPlanID($userID) {
		$sql = "SELECT plan FROM wz_users WHERE id = ".(int)$userID."";
		$result = DB::get($sql);

		return $result;
	}

	//-- Get the current billing id for a given user ID
	private static function getUserBillingID($userID) {
		$sql = "SELECT billing FROM wz_users WHERE id = ".(int)$userID."";
		$result = DB::get($sql);

		return $result;
	}

	//-- Get User's Plan
	private static function getUserPlan($userID) {
		$id = self::getUserPlanID($userID);

		$sql = "SELECT a.*, b.name, b.paypal FROM `tbl_user_plans` a INNER JOIN `tbl_plans` b ON (a.`plan` = b.`id`) WHERE a.`plan` = '".(int)$id."' AND a.`user` = '".(int)$userID."'";
		$result = DB::sexecute($sql);

		return $result;
	}

	//-- Check if the given IPN transaction was already processed
	private static function wasIPNProcessed($pp) {
		//$sql = "SELECT id FROM `tbl_ipn_log` WHERE `txn_id` = '".DB::prepare($pp->txn_id)."' AND `track_id` = '".DB::prepare($pp->ipn_track_id)."'";//-- We don't actually need these two, otherwise we'd have to make more conditions to separate everything. The upgrade process generated two IPN's, one without a transaction (new subscription) and another with it (payment)
		$sql = "SELECT id FROM `tbl_ipn_log` WHERE `track_id` = '".DB::prepare($pp->ipn_track_id)."'";
		$result = DB::get($sql);

		if ($result > 0) {
			return true;
		}

		return false;
	}

	//-- Log IPN transaction
	private static function logIPNProcess($pp) {
		Sys::startCache();

		echo 'PP:'."\n";
		var_dump($pp);

		echo "\n\n".'POST:'."\n";
		var_dump($_POST);

		echo "\n\n".'GET:'."\n";
		var_dump($_GET);

		echo "\n\n".'SERVER:'."\n";
		var_dump($_SERVER);

		$ipn_data = Sys::endCache();

		$fields = array(
			'track_id' => $pp->ipn_track_id,
			'txn_id' => $pp->txn_id,
			'ipn_data' => $ipn_data,
			'date' => date('YmdHis')
		);
		$sql = DB::build($fields, 'tbl_ipn_log', 'insert');
		DB::query($sql);
	}

}
?>
