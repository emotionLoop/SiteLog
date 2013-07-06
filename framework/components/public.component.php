<?php
/**
 * Public is the public component class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Component
 * @category Public
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.1.0 BETA
 */
 
class PublicDashboard extends Component {
	private static $tbl_services = 'tbl_services'; //-- services table
	private static $tbl_user_servers = 'tbl_user_servers'; //-- user servers table
	private static $tbl_user_services = 'tbl_user_services'; //-- user services table

	private static $autoScroll = false;
	private static $autoRefresh = false;

	private static $serverID = 0;
	private static $serverHash = '';
	
	public static function prefunctions() {
		//-- Check for valid Hash & ID
		foreach (ST::$segs as $seg) {
			$res = self::get(array('where' => "SHA1(CONCAT(`id`,':','".DB::prepare(ST::get('salt'))."')) = '".DB::prepare($seg)."' AND `public` = 1 AND `status` = 1", 'limit' => 1));
			if ($res) {
				self::$serverID = $res->id;
				self::$serverHash = $seg;
			}
		}

		if (App::$type != 'ajax' && (empty(self::$serverID) || empty(self::$serverHash))) {
			FW::redirect('?msg=private');
		}

		FW::addJS('public.js', array('defer' => 'defer'));

		//-- Parse variables
		self::$autoScroll = (bool) SS::get('scroll','get',0,'int');
		self::$autoRefresh = (bool) SS::get('refresh','get',0,'int');

		Sys::startCache();
?>
<script>
nonces.updatePublicServicesStatus = '<?php echo Sys::create_nonce('update-public-services'); ?>';

var autoScroll = <?php echo (self::$autoScroll ? 'true' : 'false'); ?>;
var autoRefresh = <?php echo (self::$autoRefresh ? 'true' : 'false'); ?>;
var serverHash = '<?php echo self::$serverHash; ?>';
</script>
<?php
		$js = Sys::endCache();
		FW::set('head',$js);
	}
	
	public static function main() {//-- Stuff to load when the component is called, on content part for the template
		$server = self::getServer(self::$serverID);
?>
	<article class="span-22 prepend-1 append-1">
		<h1 class="pink"><?php echo $server->name; ?> :: <?php _e('Status'); ?></h1>
		<p><?php _e("Below is a list of services being monitored. The date shown is the date of the last check."); ?></p>
<?php
		if (self::$autoScroll && self::$autoRefresh) {
			$scrollURL = 'public/'.self::$serverHash.'/?refresh=1';
			$scrollExtra = array('class' => 'active');

			$refreshURL = 'public/'.self::$serverHash.'/?scroll=1';
			$refreshExtra = array('class' => 'active');
		} elseif (self::$autoScroll && !self::$autoRefresh) {
			$scrollURL = 'public/'.self::$serverHash.'/';
			$scrollExtra = array('class' => 'active');

			$refreshURL = 'public/'.self::$serverHash.'/?refresh=1&scroll=1';
			$refreshExtra = null;
		} elseif (!self::$autoScroll && self::$autoRefresh) {
			$scrollURL = 'public/'.self::$serverHash.'/?scroll=1&refresh=1';
			$scrollExtra = null;

			$refreshURL = 'public/'.self::$serverHash.'/';
			$refreshExtra = array('class' => 'active');
		} else {
			$scrollURL = 'public/'.self::$serverHash.'/?scroll=1';
			$scrollExtra = null;

			$refreshURL = 'public/'.self::$serverHash.'/?refresh=1';
			$refreshExtra = null;
		}
?>
		<div id="buttons-panel">
			<?php echo FW::url($scrollURL, FW::img('theme/scroll.png', __('Auto-scroll page')), $scrollExtra); ?><?php echo FW::url($refreshURL, FW::img('theme/refresh.png', __('Auto-refresh page every hour')), $refreshExtra); ?>
		</div>
<?php
		$services = self::getServerServices($server->id);
?>
	<h3 class="clear">&nbsp;</h3>
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
		<p><?php _e("This server has no services being monitored for this server set. This is no fun without services..."); ?></p>
<?php
		}
?>
	</article>
<?php
	}
	
	public static function ajax() { 
		$nonce 		= SS::get('_wznonce','post','','string');
		$action 	= SS::get('action','post','','string');
		$serverHash = SS::get('server','post','','string');
		
		$res = new stdClass();
		$res->error = '';
		$res->data = new stdClass();

		switch ($action) {
			case 'update':
				if (Sys::verify_nonce($nonce, 'update-public-services')) {
					$data = self::getUserServices($serverHash);
					if ($data) {
						$res->data = $data;
					} else {
						$res->error = 'no-services';
					}
				} else {
					$res->error = __('Something bad happened there... try again... if you dare.');
				}
			break;
		}

		echo json_encode($res);
	}

	public static function getServer($id) {
		$sql = "SELECT * FROM `".self::$tbl_user_servers."` WHERE `id` = '".(int)$id."' AND `public` = 1 AND `status` = 1";
		$result = DB::sexecute($sql);

		return $result;
	}

	public static function getServerServices($id) {
		$sql = "SELECT a.*, b.`name`, b.`port` FROM `".self::$tbl_user_services."` a INNER JOIN `".self::$tbl_services."` b ON (a.`service` = b.`id`) WHERE a.`server` = '".(int)$id."'";
		$results = DB::execute($sql);

		return $results;
	}

	public static function getServerService($id) {
		$sql = "SELECT a.*, b.`name`, b.`port` FROM `".self::$tbl_user_services."` a INNER JOIN `".self::$tbl_services."` b ON (a.`service` = b.`id`) WHERE a.`id` = '".(int)$id."'";
		$result = DB::sexecute($sql);

		return $result;
	}

	public static function getUserServices($hash) {
		$res = self::get(array('where' => "SHA1(CONCAT(`id`,':','".DB::prepare(ST::get('salt'))."')) = '".DB::prepare($hash)."' AND `public` = 1 AND `status` = 1", 'limit' => 1));
		if ($res) {
			$id = $res->id;
		} else {
			$id = 0;
		}

		$sql = "SELECT * FROM `".self::$tbl_user_services."` WHERE `server` = '".(int)$id."'";
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
}
?>
