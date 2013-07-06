<?php
/**
 * Module is the customized base module class.
 * All module classes for this application should extend from this base class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Module
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.9.0 BETA
 */
abstract class Module {
	/**
	 * Protected string component action (to be used on prefunctions or main methods).
	 */
	protected static $action = '';
	
	/**
	 * Public __construct() method. This method basically loads the module
	 * @uses Module::prefunctions()
	 */
	public function __construct() {
		self::prefunctions();
	}
	
	/**
	 * Public prefunctions() method. To be used before HTML is outputted for this module
	 */
	public static function prefunctions() {}
	
	/**
	 * Public main() method. To be used on HTML output for this module
	 */
	public static function main() {}
}

?>