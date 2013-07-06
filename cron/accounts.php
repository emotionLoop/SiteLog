<?php
//-- NOTE: This cron should only be executed once per day, to avoid multiple repeated emails
header("Content-type: text/plain; charset=utf-8");
include 'config.inc.php';

$now = date('YmdHis');
$twoDaysAgo = date('YmdHis', time() - 172800);
$sevenDaysAgo = date('YmdHis', time() - 604800);
$eightDaysAgo = date('YmdHis', time() - 691200);
$tenDaysAgo = date('YmdHis', time() - 864000);
$twentyThreeDaysAgo = date('YmdHis', time() - 1987200);
$ninetyDaysAgo = date('YmdHis', time() - 7776000);

$oneDayAhead = date('YmdHis', time() + 86400);
$sixDaysAhead = date('YmdHis', time() + 518400);
$sevenDaysAhead = date('YmdHis', time() + 604800);
$thirtyDaysAhead = date('YmdHis', time() + 2592000);

/*
---- Plan status explanation ----
-- 0: Cancelled
-- 1: Active
*/

//-- First, check which plans should be cancelled (another cron will remove them), warn users about it
$sql = "SELECT * FROM `tbl_user_plans` WHERE `end` <= '".DB::prepare($tenDaysAgo)."' AND `status` = 1";
$results = DB::execute($sql);

if ($results) {
	foreach ($results as $result) {
		$sql = "UPDATE `tbl_user_plans` SET `status` = 0 WHERE `id` = '".(int)$result->id."'";
		DB::query($sql);

		$user = HelperGuru::getUser($result->user);
		//-- Send Email
		$tpl = HelperGuru::eTemplate('plan_cancelled');
		$tpl_search = array('%%USER%%');
		$tpl_replace = array($user->name);
		
		$msg = str_replace($tpl_search, $tpl_replace, $tpl->msg);
		HelperGuru::mail($tpl->from_name, $tpl->from_email, $user->email, $tpl->subject, $msg);
	}
}

//-- Now, check which users should be warned about their plans being cancelled (3 days before cancelling)
$sql = "SELECT * FROM `tbl_user_plans` WHERE `end` <= '".DB::prepare($sevenDaysAgo)."' AND `end` >= '".DB::prepare($eightDaysAgo)."' AND `status` = 1";
$results = DB::execute($sql);

if ($results) {
	foreach ($results as $result) {
		$user = HelperGuru::getUser($result->user);
		//-- Send Email
		$tpl = HelperGuru::eTemplate('plan_cancelled_alert');
		$tpl_search = array('%%USER%%');
		$tpl_replace = array($user->name);
		
		$msg = str_replace($tpl_search, $tpl_replace, $tpl->msg);
		HelperGuru::mail($tpl->from_name, $tpl->from_email, $user->email, $tpl->subject, $msg);
	}
}

//-- Now, check through all free plans with no login made in the past 90 days and cancel them, warn users about it
$sql = "SELECT a.* FROM `tbl_user_plans` a INNER JOIN `wz_users` b ON (a.`user` = b.`id`) WHERE  `a`.`end` <= '".DB::prepare($twoDaysAgo)."' AND b.`last_seen` <= '".DB::prepare($ninetyDaysAgo)."' AND a.`status` = 1 AND b.`status` = 1 AND a.`plan` = 4";
$results = DB::execute($sql);

if ($results) {
	foreach ($results as $result) {
		$sql = "UPDATE `tbl_user_plans` SET `status` = 0 WHERE `id` = '".(int)$result->id."'";
		DB::query($sql);

		$user = HelperGuru::getUser($result->user);
		//-- Send Email
		$tpl = HelperGuru::eTemplate('free_plan_cancelled');
		$tpl_search = array('%%USER%%');
		$tpl_replace = array($user->name);
		
		$msg = str_replace($tpl_search, $tpl_replace, $tpl->msg);
		HelperGuru::mail($tpl->from_name, $tpl->from_email, $user->email, $tpl->subject, $msg);
	}
}

//-- Now, check for all plans that are scheduled to be downgraded or cancelled and act accordingly
$sql = "SELECT * FROM `tbl_user_plans` WHERE `end` <= '".DB::prepare($oneDayAhead)."' AND `end` >= '".DB::prepare($now)."' AND `status` = 1 AND `extra` != ''";
$results = DB::execute($sql);

if ($results) {
	foreach ($results as $result) {
		$user = HelperGuru::getUser($result->user);

		$extra = json_decode($result->extra);
		switch ($extra->what) {
			case 'downgrade'://-- NOTE: No need to extend the date, as it'll be done by the queries below, either for payment or free requirements
				$newPlan = HelperGuru::getPlan($extra->plan);

				//-- Enforce new plan limits/info
				$fields = array(
					'plan' => $extra->plan,
					'max_services' => $newPlan->services,
					'max_emails' => $newPlan->emails,
					'mmi' => $newPlan->mmi,
					'price' => $newPlan->price
				);
				$sql = DB::build($fields, 'tbl_user_plans', 'update', "WHERE `id` = '".(int)$result->id."'");
				DB::query($sql);

				//-- Eliminate most recent extra services (another cron will clean up the trash left in other tables)
				if ($result->services > $newPlan->services) {
					$limit = $result->services - $newPlan->services;
					$sql = "DELETE FROM `tbl_user_services` WHERE `user` = '".(int)$user->id."' ORDER BY `id` DESC LIMIT ".(int)$limit;
					DB::query($sql);

					$fields = array(
						'services' => $newPlan->services
					);
					$sql = DB::build($fields, 'tbl_user_plans', 'update', "WHERE `id` = '".(int)$result->id."'");
					DB::query($sql);
				}

				//-- Reset minimum mmi on the active services
				$fields = array(
					'mmi' => $newPlan->mmi
				);
				$sql = DB::build($fields, 'tbl_user_services', 'update', "WHERE `user` = '".(int)$user->id."' AND `mmi` < '".(int)$newPlan->mmi."'");
				DB::query($sql);

				//-- Update user's plan info
				$fields = array(
					'emails' => 0,
					'extra' => ''
				);
				$sql = DB::build($fields, 'tbl_user_plans', 'update', "WHERE `id` = '".(int)$result->id."'");
				DB::query($sql);

				//-- Update user's plan 
				$fields = array(
					'plan' => $newPlan->id
				);
				$sql = DB::build($fields, 'wz_users', 'update', "WHERE `id` = '".(int)$user->id."'");
				DB::query($sql);

				//-- Send Email
				$tpl = HelperGuru::eTemplate('downgraded');
				$tpl_search = array('%%USER%%');
				$tpl_replace = array($user->name);
				
				$msg = str_replace($tpl_search, $tpl_replace, $tpl->msg);
				HelperGuru::mail($tpl->from_name, $tpl->from_email, $user->email, $tpl->subject, $msg);
			break;
			case 'cancel':
				$sql = "UPDATE `tbl_user_plans` SET `status` = 0, `extra` = '' WHERE `id` = '".(int)$result->id."'";
				DB::query($sql);

				//-- Send Email
				$tpl = HelperGuru::eTemplate('plan_cancelled_scheduled');
				$tpl_search = array('%%USER%%');
				$tpl_replace = array($user->name);
				
				$msg = str_replace($tpl_search, $tpl_replace, $tpl->msg);
				HelperGuru::mail($tpl->from_name, $tpl->from_email, $user->email, $tpl->subject, $msg);
			break;
		}
	}
}

//-- Now, check through all free plans that renew in 7 days, with no login made in the past 23 days and send a warning email
$sql = "SELECT a.* FROM `tbl_user_plans` a INNER JOIN `wz_users` b ON (a.`user` = b.`id`) WHERE b.`last_seen` <= '".DB::prepare($twentyThreeDaysAgo)."' AND a.`end` <= '".DB::prepare($sevenDaysAhead)."' AND a.`end` >= '".DB::prepare($sixDaysAhead)."' AND a.`status` = 1 AND b.`status` = 1 AND a.`plan` = 4";
$results = DB::execute($sql);

if ($results) {
	foreach ($results as $result) {
		$user = HelperGuru::getUser($result->user);
		//-- Send Email
		$tpl = HelperGuru::eTemplate('free_plan_cancelled_alert');
		$tpl_search = array('%%USER%%');
		$tpl_replace = array($user->name);
		
		$msg = str_replace($tpl_search, $tpl_replace, $tpl->msg);
		HelperGuru::mail($tpl->from_name, $tpl->from_email, $user->email, $tpl->subject, $msg);
	}
}

//-- Now, check through all free plans that should be renewed and have a login in the past 90 days and extend them for one month days, renewing the values
$sql = "SELECT a.* FROM `tbl_user_plans` a INNER JOIN `wz_users` b ON (a.`user` = b.`id`) WHERE b.`last_seen` >= '".DB::prepare($ninetyDaysAgo)."' AND a.`end` <= '".DB::prepare($oneDayAhead)."' AND a.`end` >= '".DB::prepare($now)."' AND a.`status` = 1 AND b.`status` = 1 AND a.`plan` = 4";
$results = DB::execute($sql);

if ($results) {
	foreach ($results as $result) {
		$sql = "UPDATE `tbl_user_plans` SET `update` = '".DB::prepare($now)."', `end` = '".DB::prepare($thirtyDaysAhead)."', `emails` = 0 WHERE `id` = '".(int)$result->id."'";
		DB::query($sql);
	}
}

//-- Now, check through all paid plans that should be renewed in 7 days, without billing info, and warn users
$sql = "SELECT a.* FROM `tbl_user_plans` a INNER JOIN `wz_users` b ON (a.`user` = b.`id`) WHERE `end` <= '".DB::prepare($sevenDaysAhead)."' AND a.`end` >= '".DB::prepare($sixDaysAhead)."' AND a.`status` = 1 AND b.`status` = 1 AND a.`plan` != 4 AND `b`.`billing` = 0";
$results = DB::execute($sql);

if ($results) {
	foreach ($results as $result) {
		$user = HelperGuru::getUser($result->user);

		$billingURL = str_replace('http://','https://',HelperGuru::$url).'account/billing/';
		$downgradeURL = str_replace('http://','https://',HelperGuru::$url).'account/upgrade/';

		//-- Send Email
		$tpl = HelperGuru::eTemplate('billing_missing');
		$tpl_search = array('%%USER%%', '%%BILLINGURL%%', '%%DOWNGRADEURL%%');
		$tpl_replace = array($user->name, $billingURL, $downgradeURL);
		
		$msg = str_replace($tpl_search, $tpl_replace, $tpl->msg);
		HelperGuru::mail($tpl->from_name, $tpl->from_email, $user->email, $tpl->subject, $msg);
	}
}

DB::end();
?>