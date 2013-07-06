<?php
/**
 * Usr is the customized User class, to be changed per project.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Usr
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.1.0 BETA
 */
class Usr extends User {
	private static $startedFB = false;//-- FreshBooks flag

	//-- Recover Password
	public static function recoverPassword($user = false) {
		if (empty($user)) {
			return false;
		}

		if (is_numeric($user)) {
			$id = $user;
			$sql = "SELECT `email` FROM `wz_users` WHERE `id` = '".(int)$id."'";
			$email = DB::get($sql);
		} else {
			$email = $user;
			$sql = "SELECT `id` FROM `wz_users` WHERE `email` = '".DB::prepare($email)."'";
			$id = DB::get($sql);
		}
		
		$new_password = Sys::generatePwd();
		
		$sql = "UPDATE `wz_users` SET `password` = '".DB::prepare(sha1($new_password.':'.ST::get('salt')))."' WHERE `id` = '".(int)$id."'";
		DB::query($sql);
		
		//-- Send Email
		$tpl = FW::eTemplate('recover_password');
		$tpl_search = array('%%PASSWORD%%');
		$tpl_replace = array($new_password);
		
		$msg = str_replace($tpl_search, $tpl_replace, $tpl->msg);
		if (FW::mail($tpl->from_name, $tpl->from_email, $email , $tpl->subject, $msg)) {
			return true;
		} else {
			return false;
		}
	}

	//-- Does an email exist already?
	public static function isDuplicate($email) {
		$sql = "SELECT `id` FROM `wz_users` WHERE `email` = '".DB::prepare($email)."'";
		if (self::logged()) {
			$sql .= " AND `id` != '".(int)self::get('id')."'";
		}
		
		$result = DB::get($sql);

		if (!empty($result)) {
			return true;
		}
		
		return false;
	}

	//-- Register a new user
	public static function register($name, $email, $plan) {
		$result = false;

		//-- Get plan info
		$sql = "SELECT * FROM `tbl_plans` WHERE `sef_name` = '".DB::prepare($plan)."' AND `status` = 1";
		$thePlan = DB::sexecute($sql);
		
		if (!$thePlan) {//-- If we didn't find any, use the premium plan
			$sql = "SELECT * FROM `tbl_plans` WHERE `sef_name` = 'premium' AND `status` = 1";
			$thePlan = DB::sexecute($sql);
		}

		$password = Sys::generatePwd();

		$data = array(
			'name' 			=> $name,
			'email' 		=> $email,
			'password' 		=> sha1($password.':'.ST::get('salt')),
			'plan' 			=> $thePlan->id,
			'language' 		=> 'en',
			'register_date' => date('YmdHis'),
			'status' 		=> 1
		);

		$sql = DB::build($data, 'wz_users', 'insert');
		$id = DB::queryid($sql);

		if (!empty($id)) {
			$result = true;
		}

		//-- If the user signed up successfully
		if ($result) {
			//-- Send welcome email
			$tpl = FW::eTemplate('welcome');

			$tpl_search = array('%%PASSWORD%%', '%%USER%%');
			$tpl_replace = array($password, $name);
			
			$msg = str_replace($tpl_search, $tpl_replace, $tpl->msg);
			
			if (!FW::mail($tpl->from_name, $tpl->from_email, $email, $tpl->subject, $msg)) {
				return false;
			}

			//-- Add the plan to the newly created user
			$uplan = array(
				'user' 		=> $id,
				'plan' 		=> $thePlan->id,
				'start' 	=> date('YmdHis'),
				'end'		=> date('YmdHis', mktime(date('H'), date('i'), date('s'), (date('m') + 1), date('d'), date('Y'))),
				'status' 	=> 1
			);

			$uplan['max_services'] 	= $thePlan->services;
			$uplan['max_emails'] 	= $thePlan->emails;
			$uplan['max_sms'] 		= $thePlan->sms;
			$uplan['mmi'] 			= $thePlan->mmi;
			$uplan['price'] 		= $thePlan->price;
			
			$sql = DB::build($uplan, 'tbl_user_plans', 'insert');
			DB::query($sql);

			//-- Add the user's email to the receivers table
			$ureceiver = array(
				'user' 	=> $id,
				'email' => $email
			);
			
			$sql = DB::build($ureceiver, 'tbl_user_receivers', 'insert');
			DB::query($sql);
		}

		return $result;
	}

	//-- Update current user's account
	public static function updateAccount($name, $email, $password, $company, $country, $language) {
		$data = array(
			'name' 			=> $name,
			'email' 		=> $email,
			'company' 		=> $company,
			'country' 		=> $country,
			'language' 		=> $language,
			'last_update' 	=> date('YmdHis')
		);

		if (!empty($password)) {
			$data['password'] = sha1($password.':'.ST::get('salt'));
		}

		$sql = DB::build($data, 'wz_users', 'update', "WHERE `id` = '".(int)self::get('id')."'");
		$query = DB::query($sql);

		if (!$query) {
			return false;
		}

		//-- If the user changed the password he/she'll need to login again
		if (!empty($password) || $email != self::get('email')) {
			FW::redirect('?msg=re-login');
		} else {
			FW::msg(__('Account data updated!'));
			self::load();
		}

		return true;
	}

	//-- Check if a given password is valid for the current user (used for account deletion)
	public static function isPasswordValid($password) {
		$hpassword = sha1($password.':'.ST::get('salt'));

		$sql = "SELECT `id` FROM `wz_users` WHERE `email` = '".DB::prepare(self::get('email'))."' AND `password` = '".DB::prepare($hpassword)."' AND `status` = 1";
		DB::query($sql);
		if (DB::rows() > 0) {
			return true;
		}
		return false;
	}

	//-- Delete the current user's account
	public static function deleteAccount() {
		$name 	= self::get('name');
		$email 	= self::get('email');

		$id = (int)self::get('id');

		//-- Delete user plans
		$sql = "DELETE FROM `tbl_user_plans` WHERE `user` = '".$id."'";
		DB::query($sql);

		//-- Delete user servers
		$sql = "DELETE FROM `tbl_user_servers` WHERE `user` = '".$id."'";
		DB::query($sql);

		//-- Delete user server services
		$sql = "DELETE FROM `tbl_user_services` WHERE `user` = '".$id."'";
		DB::query($sql);

		//-- Delete user services
		$sql = "DELETE FROM `tbl_services` WHERE `user` = '".$id."' AND `user` != 0";
		DB::query($sql);

		//-- Delete service history log
		$sql = "DELETE FROM `tbl_history_log` WHERE `user` = '".$id."'";
		DB::query($sql);

		//-- Delete user errors
		$sql = "DELETE FROM `tbl_errors_log` WHERE `user` = '".$id."'";
		DB::query($sql);

		//-- Delete user alert receivers
		$sql = "DELETE FROM `tbl_user_receivers` WHERE `user` = '".$id."'";
		DB::query($sql);

		//-- Delete user account
		$sql = "DELETE FROM `wz_users` WHERE `id` = '".$id."'";
		DB::query($sql);
		
		//-- Logout the user
		Usr::logout();

		//-- Send the last email, with info for the newsletter
		$tpl = FW::eTemplate('goodbye');

		$tpl_search = array('%%USER%%');
		$tpl_replace = array($name);
		
		$msg = str_replace($tpl_search, $tpl_replace, $tpl->msg);
		
		FW::mail($tpl->from_name, $tpl->from_email, $email, $tpl->subject, $msg);
		
		FW::redirect('?msg=account');
	}

	//-- Is the current user's plan Free?
	public static function isFree() {
		$plan = self::get('plan');
		if ($plan == 4) {
			return true;
		} else {
			return false;
		}
	}

	//-- Save current user's billing data
	public static function saveBillingData($name, $address, $zipCode, $country, $vatID) {
		$data = array(
			'billingName' 		=> $name,
			'billingAddress' 	=> $address,
			'billingZipCode' 	=> $zipCode,
			'billingCountry' 	=> $country,
			'billingVATID' 		=> $vatID,
			'last_update' 		=> date('YmdHis')
		);

		$sql = DB::build($data, 'wz_users', 'update', "WHERE `id` = '".(int)self::get('id')."'");
		$query = DB::query($sql);

		if (!$query) {
			return false;
		}

		return true;
	}
}
?>