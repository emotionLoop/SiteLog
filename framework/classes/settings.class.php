<?php
/**
 * Settings base class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Settings
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.9.0 BETA
 */
abstract class Settings {
	/**
	 * Public String $url (Application base absolute URL)
	 */
	public static $url = '';
	
	/**
	 * Public String $path (Application base absolute PATH)
	 */
	public static $path = '';
	
	/**
	 * Public String $curl (Application current absolute URL)
	 */
	public static $curl = '';
	
	/**
	 * Public String $salt (Application salt -- should be overwritten by Settings' "salt")
	 */
	public static $salt = 'A_SALT';
	
	/**
	 * Public Array $ext extra settings from database
	 */
	public static $ext = array();
	
	/**
	 * Public Array $segs segments from Application's current URL
	 */
	public static $segs = array();
	
	/**
	 * Public __construct() method. Defines all attributes.
	 *
	 * @uses $_SERVER
	 * @uses Settings::setURL()
	 * @uses Settings::setCurrentURL()
	 * @uses Settings::setSegments()
	 * @uses Settings::extend()
	 * @uses Settings::get()
	 */
	public function __construct($defaults) {
		if (!isset($_SERVER['SERVER_PORT'])) $_SERVER['SERVER_PORT'] = '';
		if (!isset($_SERVER['SERVER_NAME'])) $_SERVER['SERVER_NAME'] = '';
		if (!isset($_SERVER['SCRIPT_NAME'])) $_SERVER['SCRIPT_NAME'] = '';
		if (!isset($_SERVER['REQUEST_URI'])) $_SERVER['REQUEST_URI'] = '';
		if (!isset($_SERVER['SCRIPT_FILENAME'])) $_SERVER['SCRIPT_FILENAME'] = '';
		if (!isset($_SERVER['QUERY_STRING'])) $_SERVER['QUERY_STRING'] = '';
		if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $_SERVER['HTTP_ACCEPT_LANGUAGE'] = '';
		
		self::$url = self::setURL();
		self::$curl = self::setCurrentURL();
		self::$path = isset($_SERVER['SCRIPT_FILENAME']) ? substr($_SERVER['SCRIPT_FILENAME'],0,strrpos($_SERVER['SCRIPT_FILENAME'],'/')+1) : '';
		self::$salt = $defaults->salt;
		self::$segs = self::setSegments();
		self::extend();
		$tmp = self::get('default_lng');
		if (empty($tmp)) self::$ext['default_lng'] = $defaults->language;
	}
	
	/**
	 * Protected extend() method. Fills Settings::$ext with Settings from the Database.
	 *
	 * @uses Database::query()
	 * @uses Database::fetch()
	 */
	protected static function extend() {
		$sql = "SELECT `name`, `value` FROM `wz_settings`";
		DB::query($sql,89);

		while ($result = DB::fetch(89)) {
			switch ($result->name) {
				default : {
					self::$ext[$result->name] = $result->value;
				}break;
			}
		}
	}
	
	/**
	 * Protected setURL() method. Gets current Application full absolute URL.
	 *
	 * @uses System::is_ssl()
	 */
	protected static function setURL() {
		$tmp = 'http';
		if (Sys::is_ssl()) $tmp .= 's';
		$tmp .= '://';
		if ($_SERVER['SERVER_PORT'] != '80' && $_SERVER['SERVER_PORT'] != '443') {
			$tmp .= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'/'.substr($_SERVER['SCRIPT_NAME'],1,strrpos($_SERVER['SCRIPT_NAME'],'/'));
		} else {
			$tmp .= $_SERVER['SERVER_NAME'].'/'.substr($_SERVER['SCRIPT_NAME'],1,strrpos($_SERVER['SCRIPT_NAME'],'/'));
		}
		return $tmp;
	}
	
	/**
	 * Protected setURL() method. Gets current Application full absolute URL.
	 *
	 * @uses System::is_ssl()
	 *
	 * @return $tmp String URL
	 */
	protected static function setCurrentURL() {
		$tmp = 'http';
		if (Sys::is_ssl()) $tmp .= 's';
		$tmp .= '://';
		if ($_SERVER['SERVER_PORT'] != '80' && $_SERVER['SERVER_PORT'] != '443') {
			$tmp .= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
		} else {
			$tmp .= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		}
		return $tmp;
	}
	
	/**
	 * Protected setSegments() method. Gets current Application URL segments.
	 *
	 * @uses Settings::get()
	 * @uses System::S2A()
	 *
	 * @return $url Array URL segments
	 */
	protected static function setSegments() {
		$url = str_replace(self::get('url'), '', self::get('curl',true));
		$url = Sys::S2A($url,'/');
		$url = array_filter($url);
		return $url;
	}
	
	/**
	 * Public get() method. Get an attribute from Settings.
	 *
	 * @param $var String the attribute to get
	 * @param $parsed Boolean used for 'curl only, if true,$curl won't have the query parameters, if false, it will.
	 *
	 * @return Mixed the attribute value or null if it doesn't exist.
	 */
	public static function get($var, $parsed = false) {
		if (isset(self::$$var) && !empty(self::$$var) && $var != 'ext') {
			if ($parsed) {
				switch ($var) {
					case 'curl':
						return str_replace('?'.$_SERVER['QUERY_STRING'],'',self::$curl);
					break;
					default:
						return self::$$var;
					break;
				}
			} else {
				return self::$$var;
			}
		}
		
		return isset(self::$ext[$var]) ? self::$ext[$var] : null;
	}
	
	/**
	 * Public seg() method. Get a URL segment.
	 *
	 * @param $index Integer the index of the segments to get. Defaults to 0.
	 *
	 * @return Mixed the segment value or null if it doesn't exist.
	 */
	public static function seg($index = 0) {
		return isset(self::$segs[$index]) ? self::$segs[$index] : null;
	}
	
	/**
	 * Public segs() method. Get the number of URL segments.
	 *
	 * @return Integer the number of URL segments.
	 */
	public static function segs() {
		return count(self::$segs);
	}
}
