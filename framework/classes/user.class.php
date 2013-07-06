<?php
/**
 * User base class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage User
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.9.1 BETA
 */
abstract class User {
	/**
	 * Protected Integer $id (user id)
	 */
	protected static $id = 0;
	
	/**
	 * Protected String $email (user email)
	 */
	protected static $email = '';
	
	/**
	 * Protected String $password (user password)
	 */
	protected static $password = '';
	
	/**
	 * Protected String $language (user language)
	 */
	protected static $language = '';
	
	/**
	 * Protected Integer $register_date (user register_date)
	 */
	protected static $register_date = 0;
	
	/**
	 * Protected Integer $last_update (user last_update)
	 */
	protected static $last_update = 0;
	
	/**
	 * Protected Integer $previous_login (user previous_login)
	 */
	protected static $previous_login = 0;
	
	/**
	 * Protected Integer $last_login (user last_login)
	 */
	protected static $last_login = 0;
	
	/**
	 * Protected Integer $last_seen (user last_seen)
	 */
	protected static $last_seen = 0;
	
	/**
	 * Protected String $usertype (user usertype)
	 */
	protected static $usertype = '';
	
	/**
	 * Protected Integer $status (user status)
	 */
	protected static $status = 0;
	
	/**
	 * Protected Array $data (user data)
	 */
	protected static $data = array();
	
	/**
	 * Protected String $cookiename (user cookiename)
	 */
	protected static $cookiename = '';
	
	/**
	 * Protected String $hash (user hash)
	 */
	protected static $hash = '';
	
	/**
	 * Public __construct() method. Basically it checks for a valid cookie and fills in the user data if found.
	 *
	 * @uses Session::$ip
	 * @uses Session::$agent
	 * @uses Settings::get()
	 * @uses Session::$cookiename
	 * @uses Database::prepare()
	 * @uses Database::query()
	 * @uses Database::rows()
	 * @uses Database::fetch()
	 * @uses User::load()
	 */
	public function __construct() {
		$last_seen = date('YmdHis');
		self::$id = 0;
		self::$cookiename = sha1(SS::$ip.':'.SS::$agent.':'.ST::get('salt'));
		self::$hash = SS::get(self::$cookiename, 'cookie', '', 'string');
		if (empty(self::$hash)) return false;

		$sql = "SELECT `id` FROM `wz_users` WHERE SHA1(CONCAT(`email`,':',`password`,':','".sha1(SS::$ip.':'.ST::get('salt'))."')) = '".DB::prepare(self::$hash)."' AND `status` = 1";
		DB::query($sql,89);
		if (DB::rows(89) != 1) return false;
		$result = DB::fetch(89);

		self::$id = $result->id;
		self::load();

		$sql = "UPDATE `wz_users` SET `last_seen` = '".DB::prepare($last_seen)."' WHERE `id` = '".DB::prepare(self::$id)."'";
		DB::query($sql,89);
		self::$last_seen = $last_seen;
	}
	
	/**
	 * Public get() method. Get a data field from the current user.
	 *
	 * @param $var String the field to get
	 * @return Mixed the field value or null if it doesn't exist.
	 */
	public static function get($var) {
		if (isset(self::$$var) && !empty(self::$$var) && $var != 'data') {
			return self::$$var;
		}
		return isset(self::$data[$var]) ? self::$data[$var] : null;
	}
	
	/**
	 * Protected load() method. Loads the current user (using User::$id) data into User::$data.
	 *
	 * @uses Database::prepare()
	 * @uses Database::query()
	 * @uses Database::fetch()
	 * @uses System::O2A()
	 */
	protected static function load() {
		$sql = "SELECT * FROM `wz_users` WHERE `id` = '".DB::prepare(self::$id)."' AND `status` = 1";
		DB::query($sql,89);
		if (DB::rows(89) != 1) return false;
		$result = DB::fetch(89);
		$result = Sys::O2A($result);

		foreach($result as $name=>$value) {
			if (isset(self::$$name)) self::$$name = $value;
			else self::$data[$name] = $value;
		}
	}
	
	/**
	 * Protected ulogin() method. Update login information (previous_login, last_login and last_seen).
	 *
	 * @uses Database::build()
	 * @uses Database::prepare()
	 * @uses Database::query()
	 */
	protected static function ulogin() {
		$now = date('YmdHis');

		$fields = array(
			'previous_login' => self::$last_login,
			'last_login' => $now,
			'last_seen' => $now
		);

		$sql = DB::build($fields,'wz_users','update',"WHERE `id` = '".DB::prepare(self::$id)."'");
		DB::query($sql,89);
	}
	
	/**
	 * Public login() method. Log in a user.
	 *
	 * @uses Session::get()
	 * @uses Database::prepare()
	 * @uses Database::query()
	 * @uses Database::rows()
	 * @uses Database::fetch()
	 * @uses Session::cook()
	 * @uses User::$cookiename
	 * @uses Session::$ip
	 * @uses Settings::get()
	 * @uses User::$language
	 * @uses User::ulogin()
	 *
	 * @param $email String the email of the user. If not set or empty, $_POST['email'] is used
	 * @param $password String the password of the user. If not set or empty, $_POST['password'] is used
	 * @param $pwd_hashed Boolean true if $password is already hashed. Defaults to false.
	 * @param $expire Mixed Integer the number of seconds for the cookie/session to expire. Defaults to null (1 day).
	 *
	 * @return Boolean true if the login was successfull, false otherwise.
	 */
	public static function login($email = null, $password = null, $pwd_hashed = false, $expire = null) {
		if (empty($email)) {
			$email = SS::get('email', 'post', '', 'string');
		}
		
		if (empty($password)) {
			$password = SS::get('password', 'post', '', 'string');
		}

		if (empty($email) || empty($password)) {
			return false;
		}
		
		if ($pwd_hashed) {
			$hpassword = $password;
		} else {
			$hpassword = sha1($password.':'.ST::get('salt'));
		}

		$sql = "SELECT `id`, `email`, `last_login`, `language` FROM `wz_users` WHERE `email` = '".DB::prepare($email)."' AND `password` = '".DB::prepare($hpassword)."' AND `status` = 1";
		DB::query($sql,89);
		if (DB::rows(89) > 0) {
			$result = DB::fetch(89);

			self::$id = $result->id;
			self::$language = $result->language;
			self::$last_login = $result->last_login;

			SS::cook(self::$cookiename,sha1($email.':'.$hpassword.':'.sha1(SS::$ip.':'.ST::get('salt'))),$expire);
			SS::cook('lng',self::$language);
			if ($expire !== null) {
				SS::cook('remember',$expire,$expire);
			}
			self::ulogin();
			return true;
		}
		return false;
	}
	
	/**
	 * Public logged() method. Used to know if the current user is logged in.
	 *
	 * @return Boolean true if the user is logged in, false otherwise
	 */
	public static function logged() {
		if (self::$id > 0) return true;
		return false;
	}
	
	/**
	 * Public logout() method. Logs out a user.
	 *
	 * @uses Session::cook()
	 * @uses User::$cookiename
	 *
	 * @return Boolean true.
	 */
	public static function logout() {
		SS::cook(self::$cookiename,' ',0);
		SS::cook('remember',' ',0);
		self::$id = 0;
		return true;
	}
	
	/**
	 * Public allowed() method. Used to know if the current user is of the defined usertypes
	 *
	 * @uses User::logged()
	 * @uses System::S2A()
	 * @uses User::$usertype
	 *
	 * @param Mixed Array with all usertypes to check or all usertypes, each as an argument 
	 *
	 * @return Boolean true if the current usertype is of the ones to check, false otherwise.
	 */
	public static function allowed() {
		if (!self::logged()) return false;
		$args = func_get_args();
		if (!is_array($args)) $args = Sys::S2A($args);
		foreach ($args as $usertype) {
			if (strstr($usertype,self::$usertype)) return true;
		}
		return false;
	}
	
	/**
	 * Public recook() method. Used to update the current user cookie.
	 *
	 * @uses Database::prepare()
	 * @uses Database::query()
	 * @uses Database::rows()
	 * @uses Database::fetch()
	 * @uses Session::cook()
	 * @uses User::$cookiename
	 * @uses Session::$ip
	 * @uses Settings::get()
	 *
	 * @param Mixed Array with all usertypes to check or all usertypes, each as an argument 
	 *
	 * @return Boolean true if the current usertype is of the ones to check, false otherwise.
	 */
	public static function recook() {
		$expire = SS::get('remember', 'cookie', 0, 'int');
		if ($expire == 0) {
			$expire = null;
		}

		$sql = "SELECT `id`, `email`, `password` FROM `wz_users` WHERE `id` = '".DB::prepare(self::$id)."' AND `status` = 1";
		DB::query($sql,89);
		if (DB::rows(89) > 0) {
			$result = DB::fetch(89);

			self::$id = $result->id;

			SS::cook(self::$cookiename,sha1($result->email.':'.$result->password.':'.sha1(SS::$ip.':'.ST::get('salt'))),$expire);
			if ($expire !== null) {
				SS::cook('remember',$expire,$expire);
			}
			return true;
		}
	}
}
