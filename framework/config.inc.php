<?php
/**
 * Configuration file with default values and database connection settings.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Config
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.9.0 BETA
 */

/**
 * Load local config file
 */
$tmpFilePath = dirname(__FILE__);
if ( file_exists( $tmpFilePath.'/config.local.inc.php' ) ) {
	include( $tmpFilePath.'/config.local.inc.php' );
	unset($tmpFilePath);
} else {
	/**
	 * @ignore
	 */
	define('WZ_DEBUG',false);

	/**
	 * @ignore
	 */
	define('WZ_TRACE_LEVEL',5);

	/**
	 * @ignore
	 */
	define('WZ_MEMCACHE',true);

	if (!isset($defaults) || !is_object($defaults)) {
		$defaults = new stdClass();
	}

	if (!isset($defaults->db)) {
		$defaults->db = array('host' => 'localhost', 'db' => 'DATABASE', 'user' => 'DB_USER', 'pwd' => 'DB_PWD');
	}
	if (!isset($defaults->component) || empty($defaults->component)) {
		$defaults->component = 'content';//-- Default component to load
	}
	if (!isset($defaults->icomponent) || empty($defaults->icomponent)) {
		$defaults->icomponent = 'homepage';//-- Default index component to load
	}
	if (!isset($defaults->language) || empty($defaults->language)) {
		$defaults->language = 'en';//-- Default language to use
	}
	if (!isset($defaults->theme) || empty($defaults->theme)) {
		$defaults->theme = 'default';//-- Default theme to use
	}
	if (!isset($defaults->salt) || empty($defaults->salt)) {
		$defaults->salt = 'CUSTOM_SALT';//-- Custom App Salt to use for Hashes.
	}

	require('framework/classes/application.class.php');//-- Application class that loads all the framework classes and objects
	require('framework/app/app.class.php');//-- Application Wrapper for custom app behaviors
}
