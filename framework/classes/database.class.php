<?php
/**
 * Database base class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Database
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.9.0 BETA
 */
abstract class Database {
	/**
	 * Protected string Database Hostname (usually localhost)
	 */
	protected static $host;
	
	/**
	 * Protected string Database Username
	 */
	protected static $user;
	
	/**
	 * Protected string Database Password
	 */
	protected static $pass;
	
	/**
	 * Protected string Database Name
	 */
	protected static $database;
	
	/**
	 * Protected string Database Link Identifier
	 */
	protected static $db;
	
	/**
	 * Protected string Database Temporary MySQL Query Identifier Array
	 */
	protected static $query;
	
	/**
	 * Public __construct() method. Loads the $defaults object and starts the Database class
	 *
	 * @uses Database::start()
	 * @param $defaults Object of default db parameters
	 */
	public function __construct($defaults) {
		self::$host = $defaults->db['host'];
		self::$database = $defaults->db['db'];
		self::$user = $defaults->db['user'];
		self::$pass = $defaults->db['pwd'];
		self::$query = array();
		self::start();
	}
	
	/**
	 * Protected start() method. Initializes the Database Connection. Uses Application::kill() on failure.
	 * Also sets the database names as UTF-8.
	 *
	 * @uses mysql_connect()
	 * @uses mysql_select_db()
	 * @uses Database::query()
	 */
	protected static function start() {
		self::$db = @mysql_connect(self::$host, self::$user, self::$pass, true) OR App::kill('The website is temporarily unavailable (E#001).');
		@mysql_select_db(self::$database, self::$db) OR App::kill('The website is temporarily unavailable (E#002).');
		$sql = "SET NAMES 'utf8'";
		self::query($sql);
	}
	
	/**
	 * Public query() method. Executes a query.
	 * If there is an error with the query, triggers a System::trace() to log the problem, if WZ_DEBUG is true.
	 *
	 * @uses mysql_query()
	 * @uses mysql_error()
	 * @uses System::trace()
	 *
	 * @param $sql String MySQL query.
	 * @param $i Integer Query index to use for Database::$query.
	 * @return true/false Boolean if the query was successfull or not (Only returns false if there are Syntax errors on the MySQL query).
	 */
	public static function query($sql,$i=0) {
		if (self::$query[$i] = mysql_query($sql,self::$db)) {
			return true;
		} else {
			Sys::trace(mysql_error(self::$db)."\n\n".$sql,'DB');
		}
		return false;
	}
	
	/**
	 * Public queryid() method. Executes an Insert query and returns the generated ID.
	 * If there is an error with the query, triggers a System::trace() to log the problem, if WZ_DEBUG is true.
	 *
	 * @uses Database::query()
	 * @uses Database::lastid()
	 * @uses mysql_error()
	 *
	 * @param $sql String MySQL query.
	 * @param $i Integer Query index to use for Database::$query.
	 * @return $result Integer the ID of Database::lastid() or 0 by default.
	 */
	public static function queryid($sql,$i=0) {
		$result = 0;
		if (self::query($sql,$i)) {
			$result = self::lastid();
			return $result;
		} else {
			Sys::trace(mysql_error(self::$db)."\n\n".$sql,'DB');
		}
		return $result;
	}
	
	/**
	 * Public fetch() method. Fetches a row from the query result.
	 *
	 * @uses mysql_fetch_object()
	 *
	 * @param $i Integer Query index to use for Database::$query.
	 * @return $result Mixed Object if the row was successfully fetched or Boolean false otherwise.
	 */
	public static function fetch($i=0) {
		if (self::$query[$i]) {
			if ($result = mysql_fetch_object(self::$query[$i])) {
				return $result;
			} else {
				return false;
			}
		}
		return false;
	}
	
	/**
	 * Public rows() method. Fetches the number of affected rows from a query.
	 *
	 * @uses mysql_num_rows()
	 *
	 * @param $i Integer Query index to use for Database::$query.
	 * @return $rows Integer number of affected rows from a query.
	 */
	public static function rows($i=0) {
		$rows = mysql_num_rows(self::$query[$i]);
		return $rows;
	}
	
	/**
	 * Protected fetch_array() method. Fetches all rows from a query result. Use Database::execute($sql,$i) instead.
	 *
	 * @uses mysql_fetch_object()
	 *
	 * @param $i Integer Query index to use for Database::$query.
	 * @return $result Mixed Array of Objects if the query exists and is successful or Boolean false otherwise.
	 */
	protected static function fetch_array($i=0) {
		$results = false;
		if (self::$query[$i]) {
			while($result = mysql_fetch_object(self::$query[$i])) $results[] = $result;
		}
		return $results;
	}
	
	/**
	 * Public execute() method. Executes and fetches all rows from the query.
	 *
	 * @uses Database::query()
	 * @uses Database::fetch_array()
	 *
	 * @param $sql String MySQL query.
	 * @param $i Integer Query index to use for Database::$query.
	 * @return $result Mixed Array of Objects if the query exists and is successful or Boolean false otherwise.
	 */
	public static function execute($sql,$i=0) {
		$results = false;
		self::query($sql,$i);
		$results = self::fetch_array($i);
		return $results;
	}
	
	/**
	 * Public sexecute() method. Executes and fetches first row from the query.
	 *
	 * @uses Database::query()
	 * @uses Database::fetch()
	 *
	 * @param $sql String MySQL query.
	 * @param $i Integer Query index to use for Database::$query.
	 * @return $result Mixed Array of Objects if the query exists and is successful or Boolean false otherwise.
	 */
	public static function sexecute($sql,$i=0) {
		$result = false;
		self::query($sql,$i);
		if (self::$query[$i]) {
			$result = self::fetch($i);
		}
		return $result;
	}
	
	/**
	 * Public get() method. Executes and fetches the first return var from the first row from the query.
	 *
	 * @uses Database::sexecute()
	 * @uses get_object_vars()
	 *
	 * @param $sql String MySQL query.
	 * @param $i Integer Query index to use for Database::$query.
	 * @return $result Mixed requested query var if the query exists and is successful or Boolean false otherwise.
	 */
	public static function get($sql,$i=0) {
		$result = false;
		$result = self::sexecute($sql,$i);
		if ($result) {
			$vars = get_object_vars($result);
			foreach ($vars as $var) {
				return $var;
			}
		}
		return false;
	}
	
	/**
	 * Public build() method. Returns a structured MySQL query.
	 *
	 * @uses Database::prepare()
	 *
	 * @param $array Array with Key/Value pairs to be used as fields.
	 * @param $table String the MySQL table to use.
	 * @param $action String the action to be used to parse the MySQL string. Possible values: 'insert', 'update' and 'select'
	 * @param $extra String MySQL extra information to be added at the end of the parsed query
	 * @return $result String parsed MySQL Query.
	 */
	public static function build($array, $table, $action = 'insert', $extra = '') {
		$sql = "";
		switch ($action) {
			case 'insert' : {
				$sql = "INSERT INTO `".$table."` (";
				$fields = "";
				foreach($array as $name=>$value) {
					$fields .= ",`".$name."`";
				}
				$fields = substr($fields,1);
				$sql .= $fields.") VALUES (";
				$fields = "";
				foreach($array as $name=>$value) {
					$fields .= ",'".self::prepare($value)."'";
				}
				$fields = substr($fields,1);
				$sql .= $fields.") ".$extra.";";
			}break;
			case 'update' : {
				$sql = "UPDATE `".$table."` SET ";
				$fields = "";
				foreach($array as $name=>$value) {
					$fields .= ",`".$name."` = '".self::prepare($value)."'";
				}
				$fields = substr($fields,1);
				$sql .= $fields." ".$extra.";";
			}break;
			case 'select' : {
				$sql = "SELECT `id`";
				$fields = "";
				foreach($array as $name=>$value) {
					$fields .= ",`".$name."`";
				}
				$sql .= $fields." FROM `".$table."` ".$extra.";";
			}break;
		}
		return $sql;
	}
	
	/**
	 * Public getSEF() method. Gets the appropriate SEF Name, according to other items' on the same table.
	 *
	 * @uses Database::prepare()
	 * @uses Database::query()
	 * @uses Database::rows()
	 *
	 * @param $name String The Item name to "SEFize".
	 * @param $table String The MySQL table name to check.
	 * @param $field String The name of the field to check for "SEF Name"'s.
	 * @param $i Integer Query index to use for Database::$query.
	 * @return $return String Unique SEF Name.
	 */
	public static function getSEF($name = '', $table = '', $field = 'sef_name', $i = 0) {
		$sname = Sys::toSEF($name);
		$j = 0;
		$return = $sname;
		$sql = "SELECT `id` FROM `".$table."` WHERE `".$field."` LIKE '".self::prepare($sname)."'";
		self::query($sql,$i);
		while (self::rows($i) > 0) {
			++$j;
			$sql = "SELECT `id` FROM `".$table."` WHERE `".$field."` LIKE '".self::prepare($sname.'-'.$j)."'";
			self::query($sql,$i);
		}
		
		if ($j > 0) $return = $sname.'-'.$j;
		return $return;
	}

	/**
	 * Public nextid() method. Gets the Auto_increment value on a given table.
	 *
	 * @uses Database::prepare()
	 * @uses Database::sexecute()
	 *
	 * @param $table String The MySQL table name to check.
	 * @param $i Integer Query index to use for Database::$query.
	 * @return $return Integer Returned Auto_increment.
	 */
	public static function nextid($table = '',$i=0) {
		$sql = "SHOW TABLE STATUS LIKE '".self::prepare($table)."'";
		$result = self::sexecute($sql,$i);
		$return = $result->Auto_increment;
		return $return;
	}
	
	/**
	 * Public lastid() method. Gets the generated ID from the last query.
	 *
	 * @uses mysql_insert_id()
	 *
	 * @return Integer Last query item ID.
	 */
	public static function lastid() {
		return mysql_insert_id(self::$db);
	}
	
	/**
	 * Public prepare() method. Gets the generated ID from the last query.
	 *
	 * @uses stripslashes()
	 * @uses mysql_real_escape_string()
	 *
	 * @param $string String The MySQL parameter to protect.
	 * @return String Protected MySQL paramenter.
	 */
	public static function prepare($string) {
		$string = stripslashes($string);
		return mysql_real_escape_string($string,self::$db);
	}
	
	/**
	 * Public end() method. Closes the DB connection.
	 *
	 * @uses mysql_close()
	 */
	public static function end() {
		@mysql_close(self::$db);
	}
}