<?php
/**
 * Sys is the customized System class, to be changed per project.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Sys
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.1.0 BETA
 */
class Sys extends System {
	/**
	 * Public valid() method. Checks if a variable is valid.
	 *
	 * @param $type String the type of variable to check. Predefined values are 'email' 'date' and 'nowdate'.
	 * @param $var String the variable to check.
	 * @uses System::valid($type, $var) if the $type is not found in this hook
	 *
	 * @return Boolean true if $var is a valid $type, false otherwise.
	 */
	public static function valid($type, $var) {
		switch ($type) {
			case 'ip'://-- IP or Domain
				if (preg_match('/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/i', $var)) {//-- IP
					return true;
				} elseif (preg_match('/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/i', $var)) {//-- Domain
					return true;
				} else {
					return false;
				}
			break;
			case 'port':
				if (preg_match('/^([0-9]){2,5}$/i', $var)) {
					return true;
				} else {
					return false;
				}
			break;
		}
		return System::valid($type, $var);
	}
}
?>