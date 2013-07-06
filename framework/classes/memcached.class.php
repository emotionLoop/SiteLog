<?php
/**
 * Memcached base class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Memcached
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.9.1 BETA
 */
class MC {
	/**
	 * Protected Object $memCache (Memcache Object)
	 */
	protected static $memCache = '';
	
	/**
	 * Public __construct() method. Basically connects to the Memcache server if it is setup for it.
	 */
	public function __construct() {
		if (WZ_MEMCACHE) {
			self::$memCache = new Memcache;

			self::$memCache->connect(WZ_MEMCACHE_HOST, WZ_MEMCACHE_PORT);

			register_shutdown_function(array('MC','stop'));
		} else {
			self::$memCache = null;
		}
	}
	
	/**
	 * Public stop() method. Stop memcached connection.
	 */
	public static function stop() {
		if (WZ_MEMCACHE) {
			self::$memCache->close();
		}
	}

	/**
	 * Sets a variable with key $name and value $value. $expire is the number of
	 * seconds the variable should live (up to 30 days), or a timestamp for when it
	 * should expire. If no expiration time/date is sent,it'll expire within an hour
	 *
	 * @return Boolean true if it set, false otherwise
	 */
	public static function set($name, $value, $expire = 3600) {
		if (WZ_MEMCACHE) {
			return self::$memCache->set($name, $value, WZ_MEMCACHE_COMPRESSION, $expire);
		} else {
			return false;
		}
	}

	/**
	 * The same as Memcache::set() but only sets the variable if it doesn't exist.
	 *
	 * @return Boolean true if it set, false otherwise
	 */
	public static function add($name, $value, $expire = 3600) {
		if (WZ_MEMCACHE) {
			return self::$memCache->add($name, $value, WZ_MEMCACHE_COMPRESSION, $expire);
		} else {
			return false;
		}
	}

	/**
	 * Deletes a variable with key $name
	 */
	public static function delete($name) {
		if (WZ_MEMCACHE) {
			return self::$memCache->delete($name);
		} else {
			return false;
		}
	}

	/**
	 * Gets a variable with key $names from memcache. $names can be an array with the keys to obtain.
	 *
	 * @return Mixed the value or false if no value was obtained
	 */
	public static function get($names) {
		if (WZ_MEMCACHE) {
			$flags = WZ_MEMCACHE_COMPRESSION;
			if (is_array($names)) {
				$flags = array();
				foreach ($names as $name) {
					$flags[] = WZ_MEMCACHE_COMPRESSION;
				}
			}

			return self::$memCache->get($names, $flags);
		} else {
			return false;
		}
	}

	/**
	 * Gets the namespace for a particular category.
	 *
	 * @param String $category the category/name of the namespace
	 * @return Mixed the namespace name or false if no namespace was obtained
	 */
	public static function getNamespace($category = 'app') {
		if (WZ_MEMCACHE) {
			$ns_key = self::$memCache->get($category.'_namespace_key');
			//-- If not set, initialize it
			if ($ns_key === false) {
				self::$memCache->set($category.'_namespace_key', rand(1, 10000));
				$ns_key = self::$memCache->get($category.'_namespace_key');
			}

			return $ns_key;
		} else {
			return false;
		}
	}

	/**
	 * Clears the namespace for a particular category.
	 *
	 * @param String $category the category/name of the namespace
	 */
	public static function clearNamespace($category = 'app') {
		if (WZ_MEMCACHE) {
			self::$memCache->increment($category . '_namespace_key');
		}
	}
}
?>