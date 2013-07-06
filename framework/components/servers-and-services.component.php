<?php
/**
 * ServersAndServices is the servers and services page component class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Component
 * @category ServersAndServices
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.1.0 BETA
 */
 
class ServersAndServices extends Component {
	private static $tbl_services = 'tbl_services';//-- services table
	private static $tbl_history = 'tbl_history_log';//-- services history table
	private static $tbl_user_plans = 'tbl_user_plans';//-- user plans table
	private static $tbl_user_servers = 'tbl_user_servers';//-- user servers table
	private static $tbl_user_services = 'tbl_user_services';//-- user services table
	private static $tbl_user_receivers = 'tbl_user_receivers';//-- user alert receivers table

	private static $plan = 0;//-- current user's plan

	private static $page = '';//-- Current page
	
	public static function prefunctions() {//-- Functions to be loaded before processing. Determine action and theme
		if (!Usr::logged()) {
			FW::redirect('?msg=private');
		}

		//-- Listen for server adding
		$addServer = (bool) SS::get('addServer','post',0,'int');
		if ($addServer) {
			$nonce = SS::get('_wznonce','post','','string');
			if (Sys::verify_nonce($nonce,'add-server')) {
				self::addServer();
			} else {
				FW::redirect('servers-and-services/servers/?msg=time');
			}
		}

		//-- Listen for server updates
		$updateServer = (bool) SS::get('updateServer','post',0,'int');
		if ($updateServer) {
			$nonce = SS::get('_wznonce','post','','string');
			if (Sys::verify_nonce($nonce,'update-server')) {
				self::updateServer();
			} else {
				FW::redirect('servers-and-services/servers/?msg=time');
			}
		}

		//-- Listen for server service adding
		$addServerService = (bool) SS::get('addServerService','post',0,'int');
		if ($addServerService) {
			$nonce = SS::get('_wznonce','post','','string');
			if (Sys::verify_nonce($nonce,'add-server-service')) {
				self::addServerService();
			} else {
				FW::redirect('servers-and-services/services/?msg=time');
			}
		}

		//-- Listen for server service updates
		$updateServerService = (bool) SS::get('updateServerService','post',0,'int');
		if ($updateServerService) {
			$nonce = SS::get('_wznonce','post','','string');
			if (Sys::verify_nonce($nonce,'update-server-service')) {
				self::updateServerService();
			} else {
				FW::redirect('servers-and-services/services/?msg=time');
			}
		}

		//-- Figure out which page should we be showing
		foreach (ST::$segs as $seg) {
			if (empty($seg)) continue;

			switch ($seg) {
				case 'services':
				case 'servers':
					self::$page = $seg;
				break;
				default:
					self::$page = 'servers';
				break;
			}
		}

		//-- Parse error/success messages
		$error = SS::get('msg','get','','string');
		if (!empty($error)) {
			switch ($error) {
				case 'server-duplicate':
					FW::error(__("The IP/Domain you're trying to use already exists in the database."));
				break;
				case 'server-added':
					FW::msg(__("Bam! The server was added."));
				break;
				case 'server-updated':
					FW::msg(__("Woot?! The server was updated."));
				break;
				case 'server-deleted':
					FW::msg(__("*Sniff* The server was deleted."));
				break;
				case 'server-service-added':
					FW::msg(__("WIN! The server's service was added."));
				break;
				case 'server-service-updated':
					FW::msg(__("Boom! The server's service was updated."));
				break;
				case 'server-service-deleted':
					FW::msg(__("There it goes... The server's service was deleted."));
				break;
			}
		}

		FW::addJS('servers-and-services.js', array('defer' => 'defer'));

		$userServices = self::getUserServices();
		$userMMIs = self::getUserMMIs();
		$alertReceivers = self::getAlertReceivers();
		$userPlan = FW::getUserPlan();

		Sys::startCache();
?>
<script defer="defer">
lng.servers = {
	'delete': "<?php _e("Are you sure you want to delete this server?<br />ALL its data (including all services' history) will be deleted INSTANTLY and you won't be able to retrieve it again!"); ?>",
};
nonces.deleteServer = '<?php echo Sys::create_nonce('delete-server'); ?>';

lng.services = {
	'delete': "<?php _e("Are you sure you want to delete this service?<br />ALL its data (including history) will be deleted INSTANTLY and you won't be able to retrieve it again!"); ?>",//--'
	'noAlert': "<?php _e("Are you sure you don't want to set an alert email? You'll only be able to see the service status on our status panel if so."); ?>",
	'limit': "<?php _e("Unfortunately it seems you've reached the maximum number of services your plan allows. You can either remove some services or upgrade your account."); ?>"//--'
};
nonces.addServerService = '<?php echo Sys::create_nonce('add-server-service'); ?>';
nonces.updateServerService = '<?php echo Sys::create_nonce('update-server-service'); ?>';
nonces.deleteServerService = '<?php echo Sys::create_nonce('delete-server-service'); ?>';
nonces.addUserService = '<?php echo Sys::create_nonce('add-user-service'); ?>';
nonces.addUserReceiver = '<?php echo Sys::create_nonce('add-user-receiver'); ?>';

var maxServices = <?php echo (int)$userPlan->max_services; ?>;
var usedServices = <?php echo (int)$userPlan->services; ?>;
var userServices = new Array();
var userMMIs = new Array();
var alertReceivers = new Array();
<?php
		if ($userServices) {
			$limit = count($userServices);
			for ($i=0;$i<$limit;$i++) {
				$service = $userServices[$i];
?>

userServices[<?php echo $i; ?>] = {
	'id': <?php echo $service->id; ?>,
	'name': "<?php echo $service->name; ?>",
	'port': '<?php echo $service->port; ?>'
};
<?php
			}
		}

		if ($userMMIs) {
			$limit = count($userMMIs);
			for ($i=0;$i<$limit;$i++) {
				$mmi = $userMMIs[$i];
?>

userMMIs[<?php echo $i; ?>] = {
	'min': <?php echo $mmi; ?>,
	'name': "<?php echo FW::humanMMI($mmi); ?>"
};
<?php
			}
		}

		if ($alertReceivers) {
			$limit = count($alertReceivers);
			for ($i=0;$i<$limit;$i++) {
				$receiver = $alertReceivers[$i];
?>

alertReceivers[<?php echo $i; ?>] = "<?php echo $receiver->email; ?>";
<?php
			}
		}
?>
</script>
<?php
		$js = Sys::endCache();
		FW::set('head',$js);
	}
	
	public static function main() {//-- Stuff to load when the component is called, on content part for the template
?>
	<article class="span-22 prepend-1 append-1">
		<h1 class="pink"><?php _e('Servers & Services'); ?></h1>
		<nav id="servers-menu">
			<ul>
				<li class="first<?php if (self::$page == 'servers') { ?> active<?php } ?>"><?php echo FW::url('servers-and-services/servers/',__('Manage Servers')); ?></li><li<?php if (self::$page == 'services') { ?> class="active"<?php } ?>><?php echo FW::url('servers-and-services/services/',__('Manage Services')); ?></li>
			</ul>
		</nav>
<?php
		switch (self::$page) {
			case 'services':
				$servers = self::getServers();
					if ($servers) {
?>
		<table id="manage">
			<thead>
				<tr>
					<td><span class="hide"><?php _e('Server'); ?></span></td>
					<td><?php _e('Service'); ?></td>
					<td><?php _e('MMI'); ?></td>
					<td><?php _e('Alert Receiver'); ?></td>
					<td><?php _e('Recovery Alert?'); ?></td>
					<td class="last"><span class="hide"><?php _e('Edit'); ?></span></td>
					<td class="last"><span class="add"><?php _e('Add a service'); ?></span></td>
				</tr>
			</thead>
			<tbody>
<?php
						foreach ($servers as $server) {
							$services = self::getServerServices($server->id);
?>
				<tr class="server">
					<td><a name="server-<?php echo $server->id; ?>"></a><?php echo $server->name; ?></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td class="last"></td>
					<td class="last"><?php echo FW::url('#', FW::img('theme/add.png', __('Add a service')), array('class' => 'add-server-service', 'data-id' => $server->id)); ?></td>
				</tr>
<?php
							if ($services) {
								foreach ($services as $service) {
									$serviceLabel = $service->name;
									if (!empty($service->port)) {
										$serviceLabel .= ' :'.$service->port;
									}
?>
				<tr data-id="<?php echo $server->id; ?>-<?php echo $service->service; ?>" class="service">
					<td></td>
					<td><?php echo $serviceLabel ?></td>
					<td><?php echo FW::humanMMI($service->mmi); ?></td>
					<td><?php if (!empty($service->alert)) { echo $service->alert; } else { _e('N/A'); } ?></td>
					<td><?php if ($service->recovery == 1) { echo FW::img('theme/yes.png', __('Yes')); } ?></td>
					<td class="last"><?php echo FW::url('#', FW::img('theme/edit.png', __('Edit')), array('class' => 'update-server-service', 'data-id' => $service->id, 'data-server' => $server->id, 'data-service-label' => $serviceLabel, 'data-alert' => $service->alert, 'data-recovery' => $service->recovery, 'data-mmi' => $service->mmi)); ?></td>
					<td class="last"><?php echo FW::url('#', FW::img('theme/delete.png', __('Delete')), array('class' => 'delete-server-service', 'data-id' => $service->id)); ?></td>
				</tr>
<?php
								}
							} else {
?>
				<tr>
					<td colspan="5"><?php _e("You don't have any services being monitored for this server set. You should add some by clicking the plus sign to the right."); ?></td>
					<td class="last"></td>
					<td class="last"></td>
				</tr>
<?php
							}
						}
?>
			</tbody>
		</table>
<?php
				} else {
?>
		<h3><?php _e("You don't have any servers set. This is no fun without servers, so"); ?> <?php echo FW::url('servers-and-services/servers/', __('go and add a new server'), array('class' => 'green')); ?></h3>
<?php
				}
?>
<?php
			break;
			case 'servers':
			default:
				$servers = self::getServers();
				if ($servers) {
?>
		<table id="manage" class="span-14 servers">
			<thead>
				<tr>
					<td class="first"><?php _e('Name'); ?></td>
					<td><?php _e('IP'); ?></td>
					<td><?php _e('Status'); ?></td>
					<td><?php _e('Public URL'); ?></td>
					<td><span class="hide"><?php _e('View Services'); ?></span></td>
					<td><span class="hide"><?php _e('Edit'); ?></span></td>
					<td><span class="hide"><?php _e('Delete'); ?></span></td>
				</tr>
			</thead>
			<tbody>
<?php
					foreach ($servers as $server) {
						$serverStatus = __('N/A');
						$serverClass = '';
						$serverPublic = __('Private');
						$serverPublicClass = 'red';
						$serverPublicURL = '';

						$services = self::getServerServices($server->id);

						if ($services) {
							foreach ($services as $service) {
								if ($service->status == 0) {
									$serverStatus = __('Error');
									$serverClass = 'red';
								}
							}

							if (empty($serverClass)) {
								$serverStatus = __('OK');
								$serverClass = 'green';
							}
						}

						if ($server->status == 0) {
							$serverStatus = __('Disabled');
							$serverClass = 'red';
						}

						if ($server->public == 1) {
							$serverPublicClass = 'green';
							$serverPublicURL = ST::get('url').'public/'.sha1($server->id.':'.ST::get('salt')).'/';
							$serverPublic = FW::url($serverPublicURL, __('View →'));
						}
?>
				<tr>
					<td class="first"><?php echo $server->name; ?></td>
					<td><?php echo $server->ip; ?></td>
					<td<?php if (!empty($serverClass)) { ?> class="<?php echo $serverClass; ?>"<?php } ?>><?php echo $serverStatus; ?></td>
					<td class="<?php echo $serverPublicClass; ?>"><?php echo $serverPublic; ?></td>
					<td class="icon"><?php echo FW::url('servers-and-services/services/#server-'.$server->id, FW::img('theme/copy.png', __('View Services'))); ?></td>
					<td class="icon"><?php echo FW::url('#!/edit/'.$server->id, FW::img('theme/edit.png', __('Edit')), array('class' => 'edit-server', 'data-id' => $server->id, 'data-name' => $server->name, 'data-ip' => $server->ip, 'data-status' => $server->status)); ?></td>
					<td class="icon"><?php echo FW::url('#', FW::img('theme/delete.png', __('Delete')), array('class' => 'delete-server', 'data-id' => $server->id)); ?></td>
				</tr>
<?php
					}
?>
			</tbody>
		</table>
<?php
				} else {
?>
		<div class="span-14" style="float: left">
			<h3><?php _e("You don't have any servers set."); ?></h3>
			<p><?php _e("This is no fun without servers, so use the form on the right to add your first server."); ?></p>
		</div>
<?php
				}
?>
		<form action="<?php echo ST::$url; ?>servers-and-services/servers/" method="post" id="servers-form">
			<?php Sys::nonce_field('add-server'); ?>
			<input type="hidden" name="addServer" value="1" />
			<fieldset>
				<legend><?php _e('Add a New Server'); ?></legend>
				<p>
					<label class="span-2 pink"><?php _e('Name'); ?></label> <span class="span-5 required"><input class="span-5" type="text" name="name" id="server-name" /></span><br />
					<label class="clear span-2 pink"><?php _e('IP/Domain'); ?></label> <span class="span-5 required"><input class="span-5" type="text" name="ip-domain" id="ip-domain" /></span><br />
				</p>
				<p class="clear">
					<input class="clear button save" type="submit" name="add" value="<?php _e('Add Server'); ?>" />
				</p>
			</fieldset>
		</form>
<?php
			break;
		}
?>
	</article>
<?php
	}
	
	public static function ajax() { 
		$result = new stdClass();
		$result->error = '';
		$result->data = '';
		
		$nonce = SS::get('_wznonce','post','','string');
		$action = SS::get('action','post','','string');

		switch ($action) {
			case 'delete-server':
				if (Sys::verify_nonce($nonce, 'delete-server')) {
					$id = SS::get('server','post',0,'int');
					if (self::deleteServer($id)) {
						$result->data = 'servers-and-services/servers/?msg=server-deleted';
					} else {
						$result->error = __("That server doesn't seem to exist... at least not anymore!");
					}
				} else {
					$result->error = __('Something bad happened there... try again... if you dare.');
				}
			break;
			case 'add-server-service':
				if (Sys::verify_nonce($nonce, 'add-server-service')) {
					$server = SS::get('server','post',0,'int');
					$service = SS::get('service','post',0,'int');
					$mmi = SS::get('mmi','post',0,'int');
					$alert = SS::get('alert','post','','string');
					$recovery = SS::get('recovery','post',0,'int');
					if (self::addServerService($server, $service, $mmi, $alert, $recovery)) {
						$result->data = 'servers-and-services/services/?msg=server-service-added';
					} else {
						$result->error = __("Something bad happened there... maybe this service already exists for this server? Or you're at your services limit. Try again... if you dare.");
					}
				} else {
					$result->error = __('Something bad happened there... try again... if you dare.');
				}
			break;
			case 'update-server-service':
				if (Sys::verify_nonce($nonce, 'update-server-service')) {
					$service = SS::get('service','post',0,'int');
					$server = SS::get('server','post',0,'int');
					$mmi = SS::get('mmi','post',0,'int');
					$alert = SS::get('alert','post','','string');
					$recovery = SS::get('recovery','post',0,'int');
					if (self::updateServerService($service, $server, $mmi, $alert, $recovery)) {
						$result->data = 'servers-and-services/services/?msg=server-service-updated';
					} else {
						$result->error = __("Something bad happened there... maybe this service doesn't belong to you? Try again... if you dare.");
					}
				} else {
					$result->error = __('Something bad happened there... try again... if you dare.');
				}
			break;
			case 'delete-server-service':
				if (Sys::verify_nonce($nonce, 'delete-server-service')) {
					$id = SS::get('service','post',0,'int');
					if (self::deleteServerService($id)) {
						$result->data = 'servers-and-services/services/?msg=server-service-deleted';
					} else {
						$result->error = __("That server doesn't seem to exist... at least not anymore!");
					}
				} else {
					$result->error = __('Something bad happened there... try again... if you dare.');
				}
			break;
			case 'add-user-service':
				if (Sys::verify_nonce($nonce, 'add-user-service')) {
					$name = SS::get('name','post','','string');
					$port = SS::get('port','post',0,'int');
					if ($id = self::addUserService($name, $port)) {
						$result->data = $id;
					} else {
						$result->error = __("Something bad happened there... maybe this service's port already exists as a service? Try again... if you dare.");
					}
				} else {
					$result->error = __('Something bad happened there... try again... if you dare.');
				}
			break;
			case 'add-user-receiver':
				if (Sys::verify_nonce($nonce, 'add-user-receiver')) {
					$email = SS::get('email','post','','string');
					if (self::addUserReceiver($email)) {
						$result->data = true;
					} else {
						$result->error = __("Something bad happened there... maybe this alert receiver already exists? Try again... if you dare.");
					}
				} else {
					$result->error = __('Something bad happened there... try again... if you dare.');
				}
			break;
		}
		
		echo json_encode($result);
	}

	public static function getUserServices() {
		$sql = "SELECT * FROM `".self::$tbl_services."` WHERE (`user` = '".(int)Usr::get('id')."' OR `user` = 0) AND `status` = 1 ORDER BY `name` ASC";
		$results = DB::execute($sql);

		return $results;
	}

	public static function getUserMMIs() {
		$sql = "SELECT `mmi` FROM `".self::$tbl_user_plans."` WHERE `user` = '".(int)Usr::get('id')."' AND `status` = 1";
		$mmi = DB::get($sql);

		$mmis = ST::get('mmi_interval');
		if ($mmis) {
			$mmis = explode(',', $mmis);
		}

		$results = array();

		if ($mmis) {
			foreach ($mmis as $minutes) {
				if ($minutes < $mmi) continue;
				$results[] = $minutes;
			}
		}

		return $results;
	}

	public static function getAlertReceivers() {
		$sql = "SELECT * FROM `".self::$tbl_user_receivers."` WHERE `user` = '".(int)Usr::get('id')."' ORDER BY `user` ASC";
		$results = DB::execute($sql);

		return $results;
	}

	public static function getServers() {
		$sql = "SELECT * FROM `".self::$tbl_user_servers."` WHERE `user` = '".(int)Usr::get('id')."' ORDER BY `status` DESC, `name` ASC";
		$results = DB::execute($sql);

		return $results;
	}

	public static function getServerServices($id) {
		$sql = "SELECT a.*, b.`name`, b.`port` FROM `".self::$tbl_user_services."` a INNER JOIN `".self::$tbl_services."` b ON (a.`service` = b.`id`) WHERE a.`user` = '".(int)Usr::get('id')."' AND a.`server` = '".(int)$id."' ORDER BY b.`name` ASC";
		$results = DB::execute($sql);

		return $results;
	}

	//-- Does an ip exist already?
	public static function isServerDuplicate($ip, $updatingID = null) {
		$sql = "SELECT `id` FROM `".self::$tbl_user_servers."` WHERE `ip` = '".DB::prepare($ip)."'";
		if (!empty($updatingID)) {
			$sql .= " AND `id` != '".(int)$updatingID."'";
		}
		
		$result = DB::get($sql);

		if (!empty($result)) {
			return true;
		}
		
		return false;
	}

	//-- Does a service exist already?
	public static function isServerServiceDuplicate($server, $service, $id = null) {
		$sql = "SELECT `id` FROM `".self::$tbl_user_services."` WHERE `server` = '".(int)$server."' AND `service` = '".(int)$service."'";
		if (!empty($id) && is_numeric($id)) {
			$sql .= " AND `id` != '".(int)$id."'";
		}
		$result = DB::get($sql);

		if (!empty($result)) {
			return true;
		}
		
		return false;
	}

	//-- Is this a valid MMI for this user?
	public static function validMMI($mmi) {
		$sql = "SELECT `id` FROM `".self::$tbl_user_plans."` WHERE `user` = '".(int)Usr::get('id')."' AND `status` = 1 AND `mmi` <= '".(int)$mmi."'";
		DB::query($sql);

		if (DB::rows() > 0) {
			return true;
		}
		return false;
	}

	//-- Does a user service exist already?
	public static function isUserServiceDuplicate($port) {
		$sql = "SELECT `id` FROM `".self::$tbl_services."` WHERE (`user` = '".(int)Usr::get('id')."' OR `user` = 0) AND `port` = '".(int)$port."'";
		$result = DB::get($sql);

		if (!empty($result)) {
			return true;
		}
		
		return false;
	}

	//-- Does a user receiver exist already?
	public static function isUserReceiverDuplicate($email) {
		$sql = "SELECT `id` FROM `".self::$tbl_user_receivers."` WHERE `user` = '".(int)Usr::get('id')."' AND `email` = '".DB::prepare($email)."'";
		$result = DB::get($sql);

		if (!empty($result)) {
			return true;
		}
		
		return false;
	}

	//-- Server management methods

	//-- Add Server
	private static function addServer() {
		$name 	= SS::get('name','post','','string');
		$ip 	= SS::get('ip-domain','post','','string');
		
		if (empty($name) || empty($ip) || !Sys::valid('ip',$ip)) {
			FW::redirect('servers-and-services/servers/?msg=required');
		}
		
		if (self::isServerDuplicate($ip)) {
			FW::redirect('servers-and-services/servers/?msg=server-duplicate');
		}

		$data = array(
			'name' 		=> $name,
			'ip' 		=> $ip,
			'user' 		=> Usr::get('id'),
			'register' 	=> date('YmdHis'),
			'public'	=> 0,
			'status' 	=> 1
		);

		$sql = DB::build($data, self::$tbl_user_servers, 'insert');
		$id = DB::queryid($sql);
		
		if (!empty($id)) {
			FW::redirect('servers-and-services/servers/?msg=server-added');
		} else {
			FW::redirect('servers-and-services/servers/?msg=error');
		}
	}

	//-- Update Server
	private static function updateServer() {
		$id 	= SS::get('server','post',0,'int');
		$name 	= SS::get('name','post','','string');
		$ip 	= SS::get('ip-domain','post','','string');
		$public = SS::get('public','post',0,'int');
		$status = SS::get('status','post',0,'int');
		
		if (empty($id) || empty($name) || empty($ip) || !Sys::valid('ip',$ip)) {
			FW::redirect('servers-and-services/servers/?msg=required');
		}
		
		if (self::isServerDuplicate($ip, $id)) {
			FW::redirect('servers-and-services/servers/?msg=server-duplicate');
		}

		$data = array(
			'name' 		=> $name,
			'ip' 		=> $ip,
			'updated' 	=> date('YmdHis'),
			'public' 	=> $public,
			'status' 	=> $status
		);

		$sql = DB::build($data, self::$tbl_user_servers, 'update', "WHERE `id` = '".$id."' AND `user` = '".(int)Usr::get('id')."'");
		DB::query($sql);
		
		FW::redirect('servers-and-services/servers/?msg=server-updated');
	}

	//-- Delete Server
	private static function deleteServer($id) {
		if (empty($id)) {
			return false;
		}

		$sql = "DELETE FROM `".self::$tbl_user_servers."` WHERE `id` = '".(int)$id."' AND `user` = '".(int)Usr::get('id')."'";
		DB::query($sql);

		$sql = "DELETE FROM `".self::$tbl_user_services."` WHERE `server` = '".(int)$id."' AND `user` = '".(int)Usr::get('id')."'";
		DB::query($sql);

		$sql = "DELETE FROM `".self::$tbl_history."` WHERE `server` = '".(int)$id."' AND `user` = '".(int)Usr::get('id')."'";
		DB::query($sql);
		
		return true;
	}

	//-- Server Services management methods

	//-- Add Server Service
	private static function addServerService($server, $service, $mmi, $alert, $recovery) {
		if (empty($server) || empty($service) || empty($mmi) || !self::validMMI($mmi)) {
			return false;
		}
		
		if (self::isServerServiceDuplicate($server, $service)) {
			return false;
		}

		$userPlan = FW::getUserPlan();

		if ($userPlan->services >= $userPlan->max_services && $userPlan->max_services > 0) {
			return false;
		}

		$data = array(
			'user' 		=> Usr::get('id'),
			'server' 	=> $server,
			'service' 	=> $service,
			'mmi' 		=> $mmi,
			'alert' 	=> $alert,
			'recovery' 	=> $recovery,
			'register' 	=> date('YmdHis'),
			'status' 	=> 1
		);

		$sql = DB::build($data, self::$tbl_user_services, 'insert');
		$id = DB::queryid($sql);
		
		if (!empty($id)) {
			$sql = "UPDATE `".self::$tbl_user_plans."` SET `services` = '".(int)++$userPlan->services."' WHERE `id` = '".(int)$userPlan->id."'";
			DB::query($sql);
			return true;
		} else {
			return false;
		}
	}

	//-- Update Server Service
	private static function updateServerService($id, $server, $mmi, $alert, $recovery) {
		if (empty($id) || empty($server) || empty($mmi) || !self::validMMI($mmi)) {
			return false;
		}

		$sql = "SELECT `service` FROM `".self::$tbl_user_services."` WHERE `id` = '".(int)$id."' AND `server` = '".(int)$server."' AND `user` = '".(int)Usr::get('id')."'";
		$service = DB::get($sql);
		
		if (self::isServerServiceDuplicate($server, $service, $id)) {
			return false;
		}

		$data = array(
			'mmi' 		=> $mmi,
			'alert' 	=> $alert,
			'recovery' 	=> $recovery
		);

		$sql = DB::build($data, self::$tbl_user_services, 'update', "WHERE `id` = '".(int)$id."' AND `server` = '".(int)$server."' AND `user` = '".Usr::get('id')."'");
		DB::query($sql);
		
		if (!empty($id)) {
			return true;
		} else {
			return false;
		}
	}

	//-- Delete Server Service
	private static function deleteServerService($id) {
		if (empty($id)) {
			return false;
		}

		$sql = "DELETE FROM `".self::$tbl_user_services."` WHERE `id` = '".(int)$id."' AND `user` = '".(int)Usr::get('id')."'";
		DB::query($sql);

		$sql = "DELETE FROM `".self::$tbl_history."` WHERE `service` = '".(int)$id."' AND `user` = '".(int)Usr::get('id')."'";
		DB::query($sql);

		$userPlan = FW::getUserPlan();

		$sql = "UPDATE `".self::$tbl_user_plans."` SET `services` = '".(int)--$userPlan->services."' WHERE `id` = '".(int)$userPlan->id."'";
		DB::query($sql);
		
		return true;
	}

	//-- User Services management methods

	//-- Add User Service
	private static function addUserService($name, $port) {
		if (empty($name) || empty($port) || !Sys::valid('port', $port)) {
			return false;
		}
		
		if (self::isUserServiceDuplicate($port)) {
			return false;
		}

		$data = array(
			'user' 		=> Usr::get('id'),
			'name' 		=> $name,
			'port' 		=> $port,
			'status' 	=> 1
		);

		$sql = DB::build($data, self::$tbl_services, 'insert');
		$id = DB::queryid($sql);
		
		if (!empty($id)) {
			return $id;
		} else {
			return false;
		}
	}

	//-- Add User Receiver
	private static function addUserReceiver($email) {
		if (empty($email) || !Sys::valid('email', $email)) {
			return false;
		}
		
		if (self::isUserReceiverDuplicate($email)) {
			return false;
		}

		$data = array(
			'user' 	=> Usr::get('id'),
			'email' => $email
		);

		$sql = DB::build($data, self::$tbl_user_receivers, 'insert');
		$id = DB::queryid($sql);
		
		if (!empty($id)) {
			return true;
		} else {
			return false;
		}
	}
}
?>
