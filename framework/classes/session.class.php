<?php
/**
 * Session base class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Session
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.9.0 BETA
 */
abstract class Session {
	/**
	 * Public String $ip (user IP)
	 */
	public static $ip = '';
	
	/**
	 * Public String $referer (user Referer)
	 */
	public static $referer = '';
	
	/**
	 * Public String $agent (user Agent)
	 */
	public static $agent = '';
	
	/**
	 * Public String $browser (user Browser)
	 */
	public static $browser = '';
	//public static $browser_o = new sdtClass();
	
	/**
	 * Public String $os (user Operating System)
	 */
	public static $os = '';
	
	/**
	 * Public __construct() method. Sets all Session attributes.
	 *
	 * @uses Session::get_browser()
	 * @uses Session::get_os()
	 */
	public function __construct() {
		self::$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
		self::$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		self::$agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		//self::$browser_o = get_browser();//-- Object
		//self::$browser = isset(self::$browser_o->parent) ? self::$browser_o->parent : '';//-- String
		self::$browser = self::get_browser();//-- String
		self::$os = self::get_os();//-- String
	}
	
	/**
	 * Public get() method. Gets a variable from a determined source, checking for the correct requested type.
	 *
	 * @param $var String the name of the variable to get.
	 * @param $from String the name of the source to use (predefined are 'post', 'files', 'request', 'env', 'cookie', 'session', 'getpost', 'postget' and 'get'). Defaults to 'get'.
	 * @param $default String the default value, in case none is found from the source. Defaults to ''.
	 * @param $type String the type of variable to get (predefined are 'bool', 'boolean', 'array', 'object', 'int', 'integer', 'string'). Defaults to 'string'.
	 *
	 * @return Mixed the requested variable, $default or, if neither are of the requested type, an "empty" evaluated value of the same type.
	 */
	public static function get($var, $from = 'get', $default = '', $type = 'string') {
		$return = $default;
		
		switch ($from) {
			case 'post':
				$source = isset($_POST) ? $_POST : array();
			break;
			case 'files':
				$source = isset($_FILES) ? $_FILES : array();
			break;
			case 'request':
				$source = isset($_REQUEST) ? $_REQUEST : array();
			break;
			case 'env':
				$source = isset($_ENV) ? $_ENV : array();
			break;
			case 'cookie':
				$source = isset($_COOKIE) ? $_COOKIE : array();
			break;
			case 'session':
				$source = isset($_SESSION) ? $_SESSION : array();
			break;
			case 'getpost':
			case 'postget':
				if (isset($_GET) && isset($_POST)) {
					$source = array_merge($_GET,$_POST);
				} elseif (isset($_GET)) {
					$source = $_GET;
				} elseif (isset($_POST)) {
					$source = $_POST;
				} else {
					$source = array();
				}
			break;
			case 'get':
			default:
				$source = isset($_GET) ? $_GET : array();
			break;
		}
		
		switch ($type) {
			case 'bool':
			case 'boolean':
				if (isset($source[$var]) && is_bool($source[$var])) {
					$return = $source[$var];
				} elseif (!is_bool($default)) {
					$return = false;
				}
			break;
			case 'array':
				if (isset($source[$var]) && is_array($source[$var])) {
					$return = $source[$var];
				} elseif (!is_array($default)) {
					$return = array();
				}
			break;
			case 'object':
				if (isset($source[$var]) && is_object($source[$var])) {
					$return = $source[$var];
				} elseif (!is_object($default)) {
					$return = new stdClass();
				}
			break;
			case 'int':
			case 'integer':
				if (isset($source[$var]) && is_numeric($source[$var])) {
					$return = $source[$var];
				} elseif (!is_numeric($default)) {
					$return = 0;
				}
			break;
			case 'string':
			default:
				if (isset($source[$var]) && is_string($source[$var])) {
					$return = $source[$var];
				} elseif (!is_string($default)) {
					$return = '';
				}
			break;
		}
		
		return $return;
	}
	
	/**
	 * Public cook() method. Sets a cookie using default values or Settings' cookie_path and cookie_domain, if they exist.
	 *
	 * @uses System::is_ssl()
	 * @uses Settings::get()
	 * @uses setcookie()
	 */
	public static function cook($name, $value, $expire = null) {
		$d_expire = time() + 86400;
		$path = '';
		$domain = '';
		$secure = Sys::is_ssl() ? true : false;
		$httponly = true;

		if ($expire === null) {
			$expire = $d_expire;
		} elseif ($expire <= time() && !empty($expire)) {
			$expire += time();
		}

		$tmp = ST::get('cookie_path');
		if (!empty($tmp)) $path = $tmp;

		$tmp = ST::get('cookie_domain');
		if (!empty($tmp)) $domain = $tmp;

		setcookie($name,$value,$expire,$path,$domain,$secure,$httponly);
	}
	
	/**
	 * Protected get_browser() method. Tries to figure out what browser the current user is using.
	 *
	 * @param $user_agent String user agent string to use. If empty, $_SERVER['HTTP_USER_AGENT'] is used. Defaults to null.
	 *
	 * @return $browser String the browser found. If none is found, returns 'Unknown'
	 */
	protected static function get_browser($user_agent = null) {
		$return = 'Unknown';
		
		if (empty($user_agent) && isset($_SERVER['HTTP_USER_AGENT'])) {
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
		} else {
			return $return;
		}
		
		$browsers = array (
			'Internet Explorer 5' => 'MSIE 5',
			'Internet Explorer 6' => 'MSIE 6',
			'Internet Explorer 7' => 'MSIE 7',
			'Internet Explorer 8' => 'MSIE 8',
			'Internet Explorer 9' => 'MSIE 9',
			'Internet Explorer' => 'MSIE',
			'Firefox 2' => 'Firefox 2',
			'Firefox 3' => 'Firefox 3',
			'Firefox 4' => 'Firefox 4',
			'Firefox' => 'Firefox',
			'Opera' => 'Opera',
			'Chrome' => 'Chrome',
			'Safari' => 'Safari',
			'Webkit' => 'Webkit',
			'Gecko' => 'Gecko'
		);
	 
		foreach($browsers as $browser=>$pattern) {
			if (preg_match('/' . $pattern . '/i', $user_agent)) {
				return $browser;
			}
		}
		return $return;
	}
	
	/**
	 * Protected get_os() method. Tries to figure out what operating system the current user is using.
	 *
	 * @param $user_agent String user agent string to use. If empty, $_SERVER['HTTP_USER_AGENT'] is used. Defaults to null.
	 *
	 * @return $os String the OS found. If none is found, returns 'Unknown'
	 */
	protected static function get_os($user_agent = null) {
		$return = 'Unknown';
		
		if (empty($user_agent) && isset($_SERVER['HTTP_USER_AGENT'])) {
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
		} else {
			return $return;
		}
		
		$oses = array (
			'Windows 7' => 'Windows NT 6.1',
			'Windows Vista' => 'Windows NT 6.0',
			'Windows 2003' => 'Windows NT 5.2',
			'Windows XP' => '(Windows NT 5.1)|(Windows XP)',
			'Windows 2000' => '(Windows NT 5.0)|(Windows 2000)',
			'Windows NT 4.0' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
			'Windows ME' => '(Windows 98)|(Win 9x 4.90)|(Windows ME)',
			'Windows 98' => '(Windows 98)|(Win98)',
			'Windows 95' => '(Windows 95)|(Win95)|(Windows_95)',
			'Windows CE' => '(Windows CE)|(Win95)|(Windows_95)',
			'Windows 3.11' => 'Win16',
			'Open BSD' => 'OpenBSD',
			'Sun OS' => 'SunOS',
			'Ubuntu' => 'Ubuntu',
			'Android' => '(Android)',
			'Linux' => '(Linux)|(X11)',
			'iPhone' => 'iPhone',
			'iPad' => 'iPad',
			'Mac OS X' => 'Mac OS X',
			'Macintosh' => '(Mac_PowerPC)|(Macintosh)',
			'QNX' => 'QNX',
			'BeOS' => 'BeOS',
			'OS/2' => 'OS/2',
			'Search Bot' => '(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp/cat)|(msnbot)|(ia_archiver)'
		);
	 
		foreach($oses as $os=>$pattern) {
			if (preg_match('/' . addslashes($pattern) . '/i', $user_agent)) {
				return $os;
			}
		}
		return $return;
	}
}