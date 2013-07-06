<?php
/**
 * Application base class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.9.1 BETA
 */

/**
 * Gets the application start timestamp.
 */
defined('WZ_BEGIN_TIME') or define('WZ_BEGIN_TIME',microtime(true));
/**
 * This constant defines whether the application should be in debug mode or not. Defaults to false.
 */
defined('WZ_DEBUG') or define('WZ_DEBUG',false);
/**
 * This constant defines how much call stack information (file name and line number) should be logged by System::trace().
 * Defaults to 0, meaning no backtrace information. If it is greater than 0,
 * at most that number of call stacks will be logged. Note, only user application call stacks are considered.
 */
defined('WZ_TRACE_LEVEL') or define('WZ_TRACE_LEVEL',0);

/**
 * This constant defines whether the application should use memcached or not. Defaults to false.
 */
defined('WZ_MEMCACHE') or define('WZ_MEMCACHE',false);
/**
 * This constant defines memcached compression level. Defaults to MEMCACHE_COMPRESSED.
 */
define('WZ_MEMCACHE_COMPRESSION', MEMCACHE_COMPRESSED);
/**
 * This constant defines memcached host. Defaults to localhost.
 */
define('WZ_MEMCACHE_HOST', 'localhost');
/**
 * This constant defines memcached port. Defaults to 11211.
 */
define('WZ_MEMCACHE_PORT', 11211);

/**
 * Application base class. It's the first class used to start and stop the Application.
 *
 * @package wFramework
 * @subpackage Application
 */
class Application {
	/**
	 * Public array for app's internal use. By default it's not used for anything
	 */
	public static $int = array();
	
	/**
	 * Public string to hold the current theme
	 */
	public static $theme = '';
	
	/**
	 * Public string to hold the current component
	 */
	public static $component = '';
	
	/**
	 * Public string to hold the current component's Class name
	 */
	public static $ccomponent = '';
	
	/**
	 * Public array to hold every component's info
	 */
	public static $cinfo = array();
	
	/**
	 * Protected array to hold every component's URL trigger values
	 */
	protected static $ctrigger = array();
	
	/**
	 * Protected array to hold every component
	 */
	protected static $components = array();
	
	/**
	 * Protected array to hold every module
	 */
	protected static $modules = array();
	
	/**
	 * Protected array to hold every module that has been (pre-)loaded
	 */
	protected static $lmodules = array();
	
	/**
	 * Public string to hold the current Application type, which can usually be 'html' or 'ajax'
	 */
	public static $type = 'html';
	
	/**
	 * Public __construct method. In here occurs the whole Application life, since it starts, when the main classes are loaded, the current component is obtained and loaded, theme is loaded, and the Application stops.
	 */
	public function __construct() {
		global $defaults;
		
		/**
		 * To prevent showing errors, we redefine PHP's error_reporting and display_errors according to WZ_DEBUG
		 */
		if (WZ_DEBUG) {
			error_reporting(E_ALL) OR ini_set('error_reporting', E_ALL);
			ini_set('display_errors', 'on');
		} else {
			error_reporting(0) OR ini_set('error_reporting', 0);
			ini_set('display_errors', 'off');
		}

		require('framework/classes/memcached.class.php');
		new MC();
		
		require('framework/classes/system.class.php');
		require('framework/app/sys.class.php');//-- System Wrapper for custom app behaviors
		
		require('framework/classes/framework.class.php');
		require('framework/app/fw.class.php');//-- Framework Wrapper for custom app behaviors
		
		require('framework/classes/database.class.php');
		require('framework/app/db.class.php');//-- Database Wrapper for custom app behaviors
		new DB($defaults);
		
		require('framework/classes/settings.class.php');
		require('framework/app/st.class.php');//-- Settings Wrapper for custom app behaviors
		new ST($defaults);
		
		require('framework/classes/session.class.php');
		require('framework/app/ss.class.php');//-- Session Wrapper for custom app behaviors
		new SS();
		
		require('framework/classes/language.class.php');
		require('framework/app/lng.class.php');//-- Language Wrapper for custom app behaviors
		new Lng();
		
		require('framework/classes/user.class.php');
		require('framework/app/usr.class.php');//-- User Wrapper for custom app behaviors
		new Usr();
		
		require('framework/classes/component.class.php');
		
		require('framework/classes/module.class.php');
		
		require('framework/classes/forms.class.php');
		require('framework/app/frm.class.php');//-- Form Wrapper for custom form behaviors
		
		self::$component = $defaults->component;
		self::$theme = $defaults->theme;
		unset($defaults);
		
		self::findComponents();
		self::fillCInfo();
		self::findModules();
		self::getComponent();
		
		switch (self::$type) {
			case 'ajax':
				new self::$ccomponent;
				call_user_func(array(self::$ccomponent, 'ajax'));
			break;
			case 'html':
			default:
				/** Default SEO implementations **/
				FW::set('title',__(ST::get('default_title')));
				FW::set('seo_title',__(ST::get('default_title')));
				FW::set('seo_description',__(ST::get('default_description')));
				FW::set('seo_keywords',__(ST::get('default_keywords')));
			
				new self::$ccomponent;
				//call_user_func(array(self::$ccomponent, 'prefunctions'));//-- Auto-called from component
			
				require('framework/themes/'.self::$theme.'.php');
			break;
		}
		
		self::stop();
	}
	
	/**
	 * Public start method. this is how the Application really starts. Almost like a __construct() alias, but required for consistency and security.
	 */
	public static function start($type = null) {
		if (!is_null($type)) self::$type = $type;
		new self();
	}
	
	/**
	 * Protected findComponents method. This method looks for every component .xml and respective .php to add as a "valid" component to Application::$components array.
	 */
	protected static function findComponents() {
		$dir = 'framework/components/';
		$handler = opendir($dir);
		while ($file = readdir($handler)) {
			if ($file != '.' && $file != '..' && strpos($file,'.component.php') !== false) {
				if (strpos($file, '.component.php') && file_exists($dir.str_replace('.component.php','.component.xml',$file))) {
					self::$components[] = str_replace('.component.php', '', $file);
				}
			}
		}
		closedir($handler);
	}
	
	/**
	 * Protected fillCInfo method. This method parses every valid component's .xml and adds that info to Application::$cinfo and Application::$ctrigger arrays.
	 */
	protected static function fillCInfo() {
		$dir = 'framework/components/';
		foreach (self::$components as $component) {
			$cp_o = simplexml_load_file($dir.$component.'.component.xml');
			$cp = (string) $cp_o->id;
			self::$cinfo[$cp] = $cp_o;
			
			if (isset($cp_o->triggers) && !empty($cp_o->triggers)) {
				$tmp = (string) $cp_o->triggers;
				$tmp = Sys::S2A($tmp);
				foreach ($tmp as $b) {
					self::$ctrigger[$b] = $cp;
				}
			}
		}
	}
	
	/**
	 * Protected findModules method. This method looks for every module .php to add as a "valid" module to Application::$modules array.
	 */
	protected static function findModules() {
		$dir = 'framework/modules/';
		$handler = opendir($dir);
		while ($file = readdir($handler)) {
			if ($file != '.' && $file != '..' && strpos($file,'.module.php') !== false) {
				self::$modules[] = str_replace('.module.php', '', $file);
			}
		}
		closedir($handler);
	}
	
	/**
	 * Protected getComponent method. This method looks for a match on Application::$ctrigger array or a generic match (like some component to be triggered by any root url, e.g. content) or even and index, in case it exists.
	 * Loads that component's file and fills in Application::$component and Application::$ccomponent.
	 */
	protected static function getComponent() {
		switch (self::$type) {
			case 'ajax':
				$component = SS::get('component','getpost','','string');
				if (empty($component)) {
					Sys::trace('No component requested on ajax');
					if (WZ_DEBUG) {
						Sys::vlog(false);
					}
					self::stop();
				}
				self::$component = $component;
			break;
			case 'html':
			default:
				$tmp = ST::seg(0);
				if (isset(self::$ctrigger[$tmp])) {
					self::$component = self::$ctrigger[$tmp];
				} elseif (empty($tmp) && isset(self::$ctrigger['(index)'])) {
					self::$component = self::$ctrigger['(index)'];
				} elseif (isset(self::$ctrigger['*'])) {
					self::$component = self::$ctrigger['*'];
				}
			break;
		}
		
		require('framework/components/'.self::$component.'.component.php');
		
		self::$ccomponent = (string) self::$cinfo[self::$component]->class;
	}
	
	/**
	 * Public loadModule method. This method loads a module's prefunctions() method and is meant to be executed before any HTML output. The module's name is added to Application::$lmodules array
	 */
	public static function loadModule($module, $classname = '') {
		if (empty($classname)) $classname = ucwords($module);
		
		if (!empty($module) && in_array($module,self::$modules)) {
			if (!in_array($module, self::$lmodules)) {
				require('framework/modules/'.$module.'.module.php');
				
				new $classname;
				call_user_func(array($classname, 'prefunctions'));
				self::$lmodules[] = $module;
			}
		}
	}
	
	/**
	 * Public getModule method. This method loads a module's prefunctions() and main() methods, if the method wasn't loaded previously, ie. isn't in Application::$lmodules. The module has to be a valid one, ie. be in Application::$modules.
	 */
	public static function getModule($module, $classname = '') {
		if (empty($classname)) $classname = ucwords($module);
		
		if (!empty($module) && in_array($module,self::$modules)) {
			if (!in_array($module, self::$lmodules)) {
				require('framework/modules/'.$module.'.module.php');
				
				new $classname;
				call_user_func(array($classname, 'prefunctions'));
			}
			call_user_func(array($classname, 'main'));
		}
	}
	
	/**
	 * Public kill method. This method is meant to be used as a "prettier" way to "DIE". It's used when a DB connection fails, for example.
	 */
	public static function kill($msg = '') {//-- Error page that stops everything.
		@header("Status: 503 Service Unavailable");
		@header('Content-type: text/html; charset=utf-8');
		Sys::startCache();
?>
<!DOCTYPE html>
<html xml:lang="en" lang="en" dir="ltr">
<head>
	<title>Problem loading page</title>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Language" content="en_US" />
<style type="text/css">
body {
	font-family: Arial, sans-serif;
	font-size: 20px;
	font-color: #333;
	background: #CCC;
}
#dead {
	width: 500px;
	margin: 50px auto;
	padding: 10px;
	border: 1px solid #999;
	border-radius: 10px;
	-webkit-border-radius: 10px;
	-moz-border-radius: 10px;
	background: #EFEFEF;
}
#msg {
	font-weight: bold;
	display: block;
	text-align: center;
}
</style>
</head>
<body>
<div id="dead">
	<div id="msg">
		<p><?php echo $msg; ?></p>
	</div>
</div>
</body>
</html>
<?php
		Sys::endCache(false);
		self::stop();
	}
	
	/**
	 * Public stop method. This method stops the DB connection and exits(). It's called at the end of Application::__construct() and on Application::kill()
	 */
	public static function stop() {
		DB::end();
		exit();
	}
}
?>