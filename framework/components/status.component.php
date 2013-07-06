<?php
/**
 * Status is the status component class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Component
 * @category Status
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.1.0 BETA
 */
 
class Status extends Component {
	private static $tbl_plans = 'tbl_plans'; //-- plans table
	private static $tbl_services = 'tbl_services'; //-- services table
	private static $tbl_history = 'tbl_history_log'; //-- services history table
	private static $tbl_user_servers = 'tbl_user_servers'; //-- user servers table
	private static $tbl_user_services = 'tbl_user_services'; //-- user services table

	private static $autoScroll = false;
	private static $autoRefresh = false;
		
	public static function prefunctions() {
		if (!Usr::logged()) {
			FW::redirect('?msg=private');
		}
		
		FW::addJS('status.js', array('defer' => 'defer'));

		//-- Parse variables
		self::$autoScroll = (bool) SS::get('scroll','get',0,'int');
		self::$autoRefresh = (bool) SS::get('refresh','get',0,'int');

		Sys::startCache();
?>
<script defer="defer">
google.load('visualization', '1', {'packages':['corechart']});
lng.status = {
	'uptime': '<?php _e('Uptime (days)'); ?>',
	'downtime': '<?php _e('Downtime (days)'); ?>'
};

nonces.updateServicesStatus = '<?php echo Sys::create_nonce('update-services'); ?>';
nonces.getServiceHistory = '<?php echo Sys::create_nonce('get-history'); ?>';

var autoScroll = <?php echo (self::$autoScroll ? 'true' : 'false'); ?>;
var autoRefresh = <?php echo (self::$autoRefresh ? 'true' : 'false'); ?>;
</script>
<?php
		$js = Sys::endCache();
		FW::set('head',$js);
	}
	
	public static function main() {//-- Stuff to load when the component is called, on content part for the template
		$servers = self::getServers();
?>
	<article class="span-22 prepend-1 append-1">
		<h1 class="pink"><?php _e('Status'); ?></h1>
		<p><?php _e('Below is a list of your servers and services being monitored. The date shown is the date of the last check.'); ?><br />
		<?php _e("Click on a service to get an history log of it. For each plan you get access to the past 7 (Free), 90 (Basic), 180 (Plus) and 365 (Premium) days."); ?></p>
<?php
		if ($servers) {
			if (self::$autoScroll && self::$autoRefresh) {
				$scrollURL = 'status/?refresh=1';
				$scrollExtra = array('class' => 'active');

				$refreshURL = 'status/?scroll=1';
				$refreshExtra = array('class' => 'active');
			} elseif (self::$autoScroll && !self::$autoRefresh) {
				$scrollURL = 'status/';
				$scrollExtra = array('class' => 'active');

				$refreshURL = 'status/?refresh=1&scroll=1';
				$refreshExtra = null;
			} elseif (!self::$autoScroll && self::$autoRefresh) {
				$scrollURL = 'status/?scroll=1&refresh=1';
				$scrollExtra = null;

				$refreshURL = 'status/';
				$refreshExtra = array('class' => 'active');
			} else {
				$scrollURL = 'status/?scroll=1';
				$scrollExtra = null;

				$refreshURL = 'status/?refresh=1';
				$refreshExtra = null;
			}
?>
		<span><?php echo FW::url('servers-and-services/servers/', __('(add a server)'), array('class' => 'green')); ?></span>
		<div id="buttons-panel">
			<?php echo FW::url($scrollURL, FW::img('theme/scroll.png', __('Auto-scroll page')), $scrollExtra); ?><?php echo FW::url($refreshURL, FW::img('theme/refresh.png', __('Auto-refresh page every hour')), $refreshExtra); ?>
		</div>
<?php
			foreach ($servers as $server) {
				$services = self::getServerServices($server->id);
?>
		<h3 class="clear"><?php echo $server->name; ?> <small><?php echo FW::url('servers-and-services/services/#server-'.$server->id, __('(add a service)'), array('class' => 'green')); ?></small></h3>
<?php
				if ($services) {
?>
		<ul class="status">
<?php
					foreach ($services as $service) {
?>
			<li<?php if ($service->status == 0) { ?> class="red"<?php } ?> id="service-<?php echo $service->id; ?>" data-id="<?php echo $service->id; ?>"><?php echo $service->name; ?><?php if (!empty($service->port)) { echo ' :'.$service->port; } ?><br /><time><?php if (!empty($service->updated)) { echo Sys::date($service->updated,'Y.m.d H:i'); } else { _e('in queue...'); } ?></time></li>
<?php
					}
?>
		</ul>
<?php
				} else {
?>
			<p><?php _e("You don't have any services being monitored for this server set. This is no fun without services, so"); ?> <?php echo FW::url('servers-and-services/services/#server-'.$server->id, __('go and add a new service'), array('class' => 'green')); ?></p>
<?php
				}
			}
		} else {
?>
		<h3 class="clear"><?php _e("You don't have any servers set. This is no fun without servers, so"); ?> <?php echo FW::url('servers-and-services/servers/', __('go and add a new server'), array('class' => 'green')); ?></h3>
<?php
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
			case 'update':
				if (Sys::verify_nonce($nonce, 'update-services')) {
					$data = self::getUserServices();
					if ($data) {
						$res->data = $data;
					} else {
						$res->error = 'no-services';
					}
				} else {
					$res->error = __('Something bad happened there... try again... if you dare.');
				}
			break;
			case 'history':
				$service = SS::get('service','post',0,'int');

				if (Sys::verify_nonce($nonce, 'get-history')) {
					$dateFrom 	= SS::get('dateFrom','post','','string');
					$dateTo 	= SS::get('dateTo','post','','string');

					$data = self::getServiceHistory($service, $dateFrom, $dateTo);
					if ($data) {
						$serviceObject = self::getServerService($service);
						$serverObject = self::getServer($serviceObject->server);

						$parsedData = array();
						$totalSeconds = time() - Sys::time($serviceObject->register);
						$totalSecondsUp = 0;
						$totalSecondsDown = 0;

						foreach ($data as $serviceLog) {
							$tmp = new stdClass();

							$tmp->down = Sys::date($serviceLog->date,'Y.m.d H:i');
							if (!empty($serviceLog->recovery)) {
								$tmp->up = Sys::date($serviceLog->recovery,'Y.m.d H:i');

								$totalSecondsDown += (Sys::time($serviceLog->recovery) - Sys::time($serviceLog->date));
							} else {
								$tmp->up = __('N/A');

								$totalSecondsDown += (time() - Sys::time($serviceLog->date));
							}

							$parsedData[] = $tmp;
						}

						$totalSecondsUp = $totalSeconds - $totalSecondsDown;

						$res->data->history = $parsedData;
						$res->data->name = $serviceObject->name;
						if (!empty($serviceObject->port)) {
							$res->data->name .= ' :'.$serviceObject->port;
						}
						$res->data->since = Sys::date($serviceObject->register,'Y.m.d H:i');
						$res->data->count = count($data);
						$res->data->server = $serverObject->name;
						$res->data->uptime = round($totalSecondsUp / 60 / 60 / 24, 2);//-- in days
						$res->data->downtime = round($totalSecondsDown / 60 / 60 / 24, 2);//-- in days

					} else {
						$res->error = __("There is still no history for this service... Which means it never went down! Props! *Fist Bump*");
					}
				} else {
					$res->error = __('Something bad happened there... try again... if you dare.');
				}
			break;
		}

		echo json_encode($res);
	}

	public static function getServers() {
		$sql = "SELECT * FROM `".self::$tbl_user_servers."` WHERE `user` = '".(int)Usr::get('id')."' AND `status` = 1";
		$results = DB::execute($sql);

		return $results;
	}

	public static function getServer($id) {
		$sql = "SELECT * FROM `".self::$tbl_user_servers."` WHERE `user` = '".(int)Usr::get('id')."' AND `id` = '".(int)$id."' AND `status` = 1";
		$result = DB::sexecute($sql);

		return $result;
	}

	public static function getServerServices($id) {
		$sql = "SELECT a.*, b.`name`, b.`port` FROM `".self::$tbl_user_services."` a INNER JOIN `".self::$tbl_services."` b ON (a.`service` = b.`id`) WHERE a.`user` = '".(int)Usr::get('id')."' AND a.`server` = '".(int)$id."'";
		$results = DB::execute($sql);

		return $results;
	}

	public static function getServerService($id) {
		$sql = "SELECT a.*, b.`name`, b.`port` FROM `".self::$tbl_user_services."` a INNER JOIN `".self::$tbl_services."` b ON (a.`service` = b.`id`) WHERE a.`user` = '".(int)Usr::get('id')."' AND a.`id` = '".(int)$id."'";
		$result = DB::sexecute($sql);

		return $result;
	}

	public static function getUserServices() {
		$sql = "SELECT * FROM `".self::$tbl_user_services."` WHERE `user` = '".(int)Usr::get('id')."'";
		$services = DB::execute($sql);

		$results = array();
		if ($services) {
			foreach ($services as $service) {
				$tmp = new stdClass();

				$tmp->id = $service->id;

				if (!empty($service->updated)) {
					$tmp->updated = Sys::date($service->updated,'Y.m.d H:i');
				} else {
					$tmp->updated = __('in queue...');
				}

				$tmp->status = $service->status;

				$results[] = $tmp;
			}
		} else {
			return false;
		}

		return $results;
	}

	public static function getServiceHistory($id, $dateFrom = null, $dateTo = null) {
		$sql = "SELECT * FROM `".self::$tbl_history."` WHERE `user` = '".(int)Usr::get('id')."' AND `service` = '".(int)$id."'";

		if (!empty($dateFrom)) {
			$sql .= " AND `date` >= '".DB::prepare(Sys::date($dateFrom, 'Ymd'))."000000'";
		} else {
			switch (Usr::get('plan')) {
				case 1://-- Basic
					$searchDays = 90;
				break;
				case 2://-- Plus
					$searchDays = 180;
				break;
				case 3://-- Premium
					$searchDays = 365;
				break;
				case 4://-- Free
				default:
					$searchDays = 7;
				break;
			}
			$sql .= " AND `date` >= '".DB::prepare(date('Ymd', time() - ($searchDays * 24 * 60 * 60)))."000000'";
		}

		if (!empty($dateTo)) {
			$sql .= " AND `date` <= '".DB::prepare(Sys::date($dateTo, 'Ymd'))."000000'";
		}

		$sql .= " ORDER BY `date` DESC";

		$results = DB::execute($sql);

		return $results;
	}
}
?>
