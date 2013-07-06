<?php
header("Content-type: text/plain; charset=utf-8");
include 'config.inc.php';

//-- Remove cancelled plans and their users, sending a goodbye email
$sql = "SELECT * FROM `tbl_user_plans` WHERE `status` = 0";
$results = DB::execute($sql);

if ($results) {
	foreach ($results as $result) {
		$user = HelperGuru::getUser($result->user);

		$id = (int)$user->id;

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

		//-- Send Email
		$tpl = HelperGuru::eTemplate('goodbye');
		$tpl_search = array('%%USER%%');
		$tpl_replace = array($user->name);
		
		$msg = str_replace($tpl_search, $tpl_replace, $tpl->msg);
		HelperGuru::mail($tpl->from_name, $tpl->from_email, $user->email, $tpl->subject, $msg);
	}
}

//-- Get all existent services id's
$sql = "SELECT `id` FROM `tbl_user_services`";
$results = DB::execute($sql);

if ($results) {
	$ids = '';

	foreach ($results as $result) {
		if (!empty($ids)) $ids .= ',';
		$ids .= $result->id;
	}

	//-- Remove inexistent services in the history log
	$sql = "DELETE FROM `tbl_history_log` WHERE `service` NOT IN (".$ids.")";
	DB::query($sql);

	//-- Remove inexistent services in the amazon queue
	$sql = "DELETE FROM `tbl_amazon_queue` WHERE `service` NOT IN (".$ids.")";
	DB::query($sql);
}

DB::end();
?>