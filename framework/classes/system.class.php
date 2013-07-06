<?php
/**
 * System base class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage System
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.9.1 BETA
 */
abstract class System {
	/**
	 * Constant Integer LOG_LEVEL_INFO. Value of Logging level for info. Value is 1.
	 */
	const LOG_LEVEL_INFO = 1;
	
	/**
	 * Constant Integer LOG_LEVEL_TRACE. Value of Logging level for trace. Value is 2.
	 */
	const LOG_LEVEL_TRACE = 2;
	
	/**
	 * Protected Array $_logger the array of log and trace information.
	 */
	protected static $_logger = array();
	
	/**
	 * Merge user defined arguments into defaults array.
	 * This function is based on Wordpress' wp_parse_args().
	 *
	 * @param string|array $args Value to merge with $defaults
	 * @param array $defaults Array that serves as the defaults.
	 * @return array Merged user defined values with defaults.
	 */
	public static function args($args, $defaults = '') {
		if (is_object($args)) {
			$r = get_object_vars($args);
		} elseif (is_array( $args )) {
			$r =& $args;
		} else {
			self::parse_str($args, $r);
		}
	
		if (is_array( $defaults )) {
			return array_merge($defaults, $r);
		}
		return $r;
	}
	
	/**
	 * Determine if SSL is used.
	 * This function is based on Wordpress' is_ssl().
	 *
	 * @return bool True if SSL, false if not used.
	 */
	public static function is_ssl() {
		if ( isset($_SERVER['HTTPS']) ) {
			if ( 'on' == strtolower($_SERVER['HTTPS']) )
				return true;
			if ( '1' == $_SERVER['HTTPS'] )
				return true;
		} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
			return true;
		}
		return false;
	}
	
	/**
	 * Parses a string into variables to be stored in an array.
	 *
	 * Uses {@link http://www.php.net/parse_str parse_str()} and stripslashes if
	 * {@link http://www.php.net/magic_quotes magic_quotes_gpc} is on.
	 *
	 * This function is based on Wordpress' wp_parse_str().
	 *
	 * @param string $string The string to be parsed.
	 * @param array $array Variables will be stored in this array.
	 */
	public static function parse_str( $string, &$array ) {
		parse_str($string, $array);
		if (get_magic_quotes_gpc()) {
			$array = self::stripslashes_deep($array);
		}
	}
	
	/**
	 * Public trace() method. Trace a variable if WZ_DEBUG is defined as true.
	 *
	 * @uses System::log()
	 *
	 * @param $msg Mixed the variable to trace.
	 * @param $category String the category of the app, used to be easier to understand what the $msg refers to, if not easily understandable
	 */
	public static function trace($msg, $category = 'app') {
		if (WZ_DEBUG) {
			self::log($msg, $category, self::LOG_LEVEL_TRACE);
		}
	}
	
	/**
	 * Public log() method. Log or Trace a variable if WZ_DEBUG is defined as true.
	 *
	 * @uses debug_backtrace()
	 *
	 * @param $msg Mixed the variable to trace.
	 * @param $category String the category of the app, used to be easier to understand what the $msg refers to, if not easily understandable
	 * @param $level Integer level of logging. Possibilities are System::LOG_LEVEL_INFO or System::LOG_LEVEL_TRACE.
	 */
	public static function log($msg, $category = 'app', $level = null) {
		if (WZ_DEBUG) {
			if (is_null($level)) $level = self::LOG_LEVEL_INFO;
			if (is_array($msg) || is_object($msg)) {
				$msg = print_r($msg,true);
			}
			$tracer = array();
			if (WZ_TRACE_LEVEL > 0 && $level === self::LOG_LEVEL_TRACE) {
				$traces = debug_backtrace();
				$count = 0;
				foreach ($traces as $trace) {
					if (isset($trace['file'], $trace['line'])) {
						$tracer[] = $trace['file'].' ('.$trace['line'].')';
						if (++$count >= WZ_TRACE_LEVEL) {
							break;
						}
					}
				}
			}
			self::$_logger[] = array($msg, $tracer, $level, $category);
		}
	}
	
	/**
	 * Public vlog() method. Outputs the System::$_logger array. If $html is true, it prints a "pretty" table. If false, it just does a var_dump()
	 *
	 * @uses System::startCache()
	 * @uses System::endCache()
	 * @uses var_dump()
	 *
	 * @param $html Boolean true if the output should be in html, false if just text.
	 */
	public static function vlog($html = true) {
		if ($html) {
			Sys::startCache();
			
			if (count(self::$_logger) > 0) {
?>
<style type="text/css">
#vlog {
	clear: both;
	display: block;
	width: 100%;
	position: absolute;
	z-index: 20000;
}
#vlog table {
	background: #FFF;
	border-spacing: 1px;
}
#vlog table tr {
}
#vlog table tr th {
	background: #000;
	padding: 5px;
	font-weight: bold;
	color: #FFF;
	text-align: center;
}
#vlog table tr td {
	background: #CCC;
	padding: 5px;
	color: #000;
	text-align: left;
}
#vlog table tr td.center {
	text-align: center;
}
</style>
<div id="vlog">
	<table>
	<tr>
		<th>Message</th>
		<th>Trace</th>
		<th>Level</th>
		<th>Category</th>
	</tr>
<?php
				foreach (self::$_logger as $log) {
?>
	<tr>
		<td><?php echo nl2br($log[0]); ?></td>
		<td><?php echo implode('<br />', $log[1]); ?></td>
		<td class="center"><?php echo $log[2]; ?></td>
		<td class="center"><?php echo $log[3]; ?></td>
	</tr>
<?php
				}
?>
	</table>
</div>
<?php
			}
			Sys::endCache(false);
		} else {
			var_dump(self::$_logger);
		}
	}
	
	
	/**
	 * Appends a trailing slash.
	 *
	 * Will remove trailing slash if it exists already before adding a trailing
	 * slash. This prevents double slashing a string or path.
	 *
	 * The primary use of this is for paths and thus should be used for paths. It is
	 * not restricted to paths and offers no specific path support.
	 *
	 * This function is based on Wordpress' wp_trailingslashit().
	 *
	 * @uses System::untrailingslashit() Unslashes string if it was slashed already.
	 *
	 * @param string $string What to add the trailing slash to.
	 * @return string String with trailing slash added.
	 */
	public static function trailingslashit($string) {
		return self::untrailingslashit($string) . '/';
	}
	
	/**
	 * Removes trailing slash if it exists.
	 *
	 * The primary use of this is for paths and thus should be used for paths. It is
	 * not restricted to paths and offers no specific path support.
	 *
	 * This function is based on Wordpress' wp_untrailingslashit().
	 *
	 * @param string $string What to remove the trailing slash from.
	 * @return string String without the trailing slash.
	 */
	public static function untrailingslashit($string) {
		return rtrim($string, '/');
	}
	
	/**
	 * Adds slashes to escape strings.
	 *
	 * Slashes will first be removed if magic_quotes_gpc is set, see {@link
	 * http://www.php.net/magic_quotes} for more details.
	 *
	 * This function is based on Wordpress' wp_addslashes().
	 *
	 * @param string $gpc The string returned from HTTP request data.
	 * @return string Returns a string escaped with slashes.
	 */
	public static function addslashes($gpc) {
		if ( get_magic_quotes_gpc() )
			$gpc = stripslashes($gpc);
	
		return $gpc;
	}
	
	/**
	 * Properly strip all HTML tags including script and style
	 *
	 * This function is based on Wordpress' wp_strip_tags().
	 *
	 * @param string $string String containing HTML tags
	 * @param bool $remove_breaks optional Whether to remove left over line breaks and white space chars
	 * @return string The processed string.
	 */
	public static function strip_tags($string, $remove_breaks = false) {
		$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
		$string = strip_tags($string);
	
		if ( $remove_breaks ) {
			$string = preg_replace('/[\r\n\t ]+/', ' ', $string);
		}
	
		return trim($string);
	}
	
	/**
	 * Navigates through an array and removes slashes from the values.
	 *
	 * If an array is passed, the array_map() function causes a callback to pass the
	 * value back to the function. The slashes from this value will removed.
	 *
	 * This function is based on Wordpress' wp_stripslashes_deep().
	 *
	 * @param array|string $value The array or string to be striped.
	 * @return array|string Stripped array (or string in the callback).
	 */
	public static function stripslashes_deep($value) {
		if ( is_array($value) ) {
			$value = array_map('Sys::stripslashes_deep', $value);
		} elseif ( is_object($value) ) {
			$vars = get_object_vars( $value );
			foreach ($vars as $key=>$data) {
				$value->{$key} = self::stripslashes_deep( $data );
			}
		} else {
			$value = stripslashes($value);
		}
	
		return $value;
	}
	
	/**
	 * Navigates through an array and encodes the values to be used in a URL.
	 *
	 * Uses a callback to pass the value of the array back to the function as a
	 * string.
	 *
	 * This function is based on Wordpress' wp_urlencode_deep().
	 *
	 * @param array|string $value The array or string to be encoded.
	 * @return array|string $value The encoded array (or string from the callback).
	 */
	public static function urlencode_deep($value) {
		$value = is_array($value) ? array_map('self::urlencode_deep', $value) : urlencode($value);
		return $value;
	}
	
	/**
	 * Unserialize value only if it was serialized.
	 *
	 * This function is based on Wordpress' wp_maybe_unserialize().
	 *
	 * @param string $original Maybe unserialized original, if is needed.
	 * @return mixed Unserialized data can be any type.
	 */
	public static function maybe_unserialize( $original ) {
		if ( self::is_serialized( $original ) ) { // don't attempt to unserialize data that wasn't serialized going in
			return @unserialize( $original );
		}
		return $original;
	}
	
	/**
	 * Check value to find if it was serialized.
	 *
	 * If $data is not an string, then returned value will always be false.
	 * Serialized data is always a string.
	 *
	 * This function is based on Wordpress' wp_is_serialized().
	 *
	 * @param mixed $data Value to check to see if was serialized.
	 * @return bool False if not serialized and true if it was.
	 */
	public static function is_serialized( $data ) {
		// if it isn't a string, it isn't serialized
		if ( !is_string( $data ) )
			return false;
		$data = trim( $data );
		if ( 'N;' == $data )
			return true;
		if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
			return false;
		switch ( $badions[1] ) {
			case 'a' :
			case 'O' :
			case 's' :
				if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
					return true;
				break;
			case 'b' :
			case 'i' :
			case 'd' :
				if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
					return true;
				break;
		}
		return false;
	}
	
	/**
	 * Check whether serialized data is of string type.
	 *
	 * This function is based on Wordpress' wp_is_serialized_string().
	 *
	 * @param mixed $data Serialized data
	 * @return bool False if not a serialized string, true if it is.
	 */
	public static function is_serialized_string( $data ) {
		// if it isn't a string, it isn't a serialized string
		if ( !is_string( $data ) )
			return false;
		$data = trim( $data );
		if ( preg_match( '/^s:[0-9]+:.*;$/s', $data ) ) // this should fetch all serialized strings
			return true;
		return false;
	}
	
	/**
	 * Serialize data, if needed.
	 *
	 * This function is based on Wordpress' wp_maybe_serialize().
	 *
	 * @param mixed $data Data that might be serialized.
	 * @return mixed A scalar data
	 */
	public static function maybe_serialize( $data ) {
		if ( is_array( $data ) || is_object( $data ) )
			return serialize( $data );
	
		if ( self::is_serialized( $data ) )
			return serialize( $data );
	
		return $data;
	}
	
	/**
	 * Escaping for HTML blocks.
	 *
	 * This function is based on Wordpress' esc_html().
	 *
	 * @param string $text
	 * @return string
	 */
	public static function esc_html( $text ) {
		$safe_text = htmlspecialchars( $text, ENT_QUOTES );
		return $safe_text;
	}
	
	/**
	 * Escaping for HTML attributes.
	 *
	 * This function is based on Wordpress' esc_attr().
	 *
	 * @param string $text
	 * @return string
	 */
	public static function esc_attr( $text ) {
		$safe_text = htmlspecialchars( $text, ENT_QUOTES );
		return $safe_text;
	}
	
	/**
	 * Gets the header information to prevent caching.
	 *
	 * The several different headers cover the different ways cache prevention is handled
	 * by different browsers
	 *
	 * This function is based on Wordpress' wp_get_nocache_headers().
	 *
	 * @return array The associative array of header names and field values.
	 */
	public static function get_nocache_headers() {
		$headers = array(
			'Expires' => 'Wed, 11 Jan 1984 05:00:00 GMT',
			'Last-Modified' => gmdate( 'D, d M Y H:i:s' ) . ' GMT',
			'Cache-Control' => 'no-cache, must-revalidate, max-age=0',
			'Pragma' => 'no-cache',
		);

		return $headers;
	}
	
	/**
	 * Sets the headers to prevent caching for the different browsers.
	 *
	 * Different browsers support different nocache headers, so several headers must
	 * be sent so that all of them get the point that no caching should occur.
	 *
	 * This function is based on Wordpress' wp_nocache_headers().
	 *
	 * @uses System::get_nocache_headers()
	 */
	public static function nocache_headers() {
		$headers = self::get_nocache_headers();
		foreach( (array) $headers as $name => $field_value ) {
			@header("{$name}: {$field_value}");
		}
	}
	
	/**
	 * Get the time-dependent variable for nonce creation.
	 *
	 * This function is based on Wordpress' wp_nonce_tick().
	 *
	 * @return int
	 */
	public static function nonce_tick($life = 86400) {
	
		return ceil(time() / $life);
	}
	
	/**
	 * Creates a random, one time use token.
	 *
	 * This function is based on Wordpress' wp_create_nonce().
	 *
	 * @param string|int $action Scalar value to add context to the nonce.
	 * @return string The one use form token
	 */
	public static function create_nonce($action = -1) {
		return sha1(ST::get('ip').':'.md5($action).':'.self::nonce_tick().':'.ST::get('salt'));
	}
	
	/**
	 * Verify that correct nonce was used with time limit.
	 *
	 * This function is based on Wordpress' wp_verify_nonce().
	 *
	 * @param string $nonce Nonce that was used in the form to verify
	 * @param string|int $action Should give context to what is taking place and be the same when nonce was created.
	 * @return bool Whether the nonce check passed or failed.
	 */
	public static function verify_nonce($nonce, $action = -1) {
		//Sys::log(sha1(ST::get('ip').':'.md5($action).':'.self::nonce_tick().':'.ST::get('salt')),$action);
		// Nonce generated 0-24 hours ago
		if ( sha1(ST::get('ip').':'.md5($action).':'.self::nonce_tick().':'.ST::get('salt')) == $nonce )
			return true;
		// Invalid nonce
		return false;
	}
	
	/**
	 * Retrieve or display nonce hidden field for forms.
	 *
	 * The nonce field is used to validate that the contents of the form came from
	 * the location on the current site and not somewhere else. The nonce does not
	 * offer absolute protection, but should protect against most cases. It is very
	 * important to use nonce field in forms.
	 *
	 * If you set $echo to true and set $referer to true, then you will need to
	 * retrieve the {@link Sytem::referer_field() wz referer field}. If you have the
	 * $referer set to true and are echoing the nonce field, it will also echo the
	 * referer field.
	 *
	 * The $action and $name are optional, but if you want to have better security,
	 * it is strongly suggested to set those two parameters. It is easier to just
	 * call the function without any parameters, because validation of the nonce
	 * doesn't require any parameters, but since crackers know what the default is
	 * it won't be difficult for them to find a way around your nonce and cause
	 * damage.
	 *
	 * The input name will be whatever $name value you gave. The input value will be
	 * the nonce creation value.
	 *
	 * This function is based on Wordpress' wp_nonce_field()
	 *
	 * @param string $action Optional. Action name.
	 * @param string $name Optional. Nonce name.
	 * @param bool $referer Optional, default true. Whether to set the referer field for validation.
	 * @param bool $echo Optional, default true. Whether to display or return hidden form field.
	 * @return string Nonce field.
	 */
	public static function nonce_field( $action = -1, $name = "_wznonce", $referer = true , $echo = true ) {
		$name = self::esc_attr( $name );
		$nonce = self::create_nonce( $action );
		$nonce_field = '<input type="hidden" id="' . $name . '" name="' . $name . '" value="' . $nonce . '" />';
		
		if ( $echo )
			echo $nonce_field;
	
		if ( $referer ) {
			self::referer_field( $echo );
		}
	
		return $nonce_field;
	}
	
	/**
	 * Retrieve or display referer hidden field for forms.
	 *
	 * The referer link is the current Request URI from the server super global. The
	 * input name is '_wz_http_referer', in case you wanted to check manually.
	 * This function is based on Wordpress' wp_referer_field
	 *
	 * This function is based on Wordpress' wp_referer_field().
	 *
	 * @param bool $echo Whether to echo or return the referer field.
	 * @return string Referer field.
	 */
	public static function referer_field( $echo = true ) {
		$ref = self::esc_attr( /*$_SERVER['REQUEST_URI']*/ST::get('curl') );
		$referer_field = '<input type="hidden" name="_wz_http_referer" value="'. $ref . '" />';
	
		if ( $echo )
			echo $referer_field;
		return $referer_field;
	}
	
	/**
	 * Convert number of bytes largest unit bytes will fit into.
	 *
	 * It is easier to read 1kB than 1024 bytes and 1MB than 1048576 bytes. Converts
	 * number of bytes to human readable number by taking the number of that unit
	 * that the bytes will go into it. Supports TB value.
	 *
	 * Please note that integers in PHP are limited to 32 bits, unless they are on
	 * 64 bit architecture, then they have 64 bit size. If you need to place the
	 * larger size then what PHP integer type will hold, then use a string. It will
	 * be converted to a double, which should always have 64 bit length.
	 *
	 * Technically the correct unit names for powers of 1024 are KiB, MiB etc.
	 * @link http://en.wikipedia.org/wiki/Byte
	 *
	 * This function is based on Wordpress' wp_size_format().
	 *
	 * @param int|string $bytes Number of bytes. Note max integer size for integers.
	 * @param int $decimals Precision of number of decimal places.
	 * @return bool|string False on failure. Number string on success.
	 */
	public static function size_format( $bytes, $decimals = 2 ) {
		$quant = array(
			// ========================= Origin ====
			'TB' => 1099511627776,  // pow( 1024, 4)
			'GB' => 1073741824,     // pow( 1024, 3)
			'MB' => 1048576,        // pow( 1024, 2)
			'kB' => 1024,           // pow( 1024, 1)
			'B ' => 1,              // pow( 1024, 0)
		);
		foreach ( $quant as $unit => $mag )
			if ( doubleval($bytes) >= $mag )
				return round( $bytes / $mag, $decimals ) . ' ' . $unit;
	
		return false;
	}
	
	/**
	 * Checks to see if a string is utf8 encoded.
	 *
	 * NOTE: This function checks for 5-Byte sequences, UTF8
	 *       has Bytes Sequences with a maximum length of 4.
	 *
	 * @author bmorel at ssi dot fr (modified)
	 *
	 * This function is based on Wordpress' wp_seems_utf8().
	 *
	 * @param string $str The string to be checked
	 * @return bool True if $str fits a UTF-8 model, false otherwise.
	 */
	public static function seems_utf8($str) {
		$length = strlen($str);
		for ($i=0; $i < $length; $i++) {
			$c = ord($str[$i]);
			if ($c < 0x80) $n = 0; # 0bbbbbbb
			elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
			elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
			elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
			elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
			elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
			else return false; # Does not match any model
			for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
				if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
					return false;
			}
		}
		return true;
	}
	
	/**
	 * Converts all accent characters to ASCII characters.
	 *
	 * If there are no accent characters, then the string given is just returned.
	 *
	 * This function is based on Wordpress' wp_remove_accents().
	 *
	 * @param string $string Text that might have accent characters
	 * @return string Filtered string with replaced "nice" characters.
	 */
	public static function remove_accents($string) {
		if ( !preg_match('/[\x80-\xff]/', $string) )
			return $string;
	
		if (self::seems_utf8($string)) {
			$chars = array(
			// Decompositions for Latin-1 Supplement
			chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
			chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
			chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
			chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
			chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
			chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
			chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
			chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
			chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
			chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
			chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
			chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
			chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
			chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
			chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
			chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
			chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
			chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
			chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
			chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
			chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
			chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
			chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
			chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
			chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
			chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
			chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
			chr(195).chr(191) => 'y',
			// Decompositions for Latin Extended-A
			chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
			chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
			chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
			chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
			chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
			chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
			chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
			chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
			chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
			chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
			chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
			chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
			chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
			chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
			chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
			chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
			chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
			chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
			chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
			chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
			chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
			chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
			chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
			chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
			chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
			chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
			chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
			chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
			chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
			chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
			chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
			chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
			chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
			chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
			chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
			chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
			chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
			chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
			chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
			chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
			chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
			chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
			chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
			chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
			chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
			chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
			chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
			chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
			chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
			chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
			chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
			chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
			chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
			chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
			chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
			chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
			chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
			chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
			chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
			chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
			chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
			chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
			chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
			chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
			// Euro Sign
			chr(226).chr(130).chr(172) => 'E',
			// GBP (Pound) Sign
			chr(194).chr(163) => '');
	
			$string = strtr($string, $chars);
		} else {
			// Assume ISO-8859-1 if not UTF-8
			$chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
				.chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
				.chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
				.chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
				.chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
				.chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
				.chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
				.chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
				.chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
				.chr(252).chr(253).chr(255);
	
			$chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";
	
			$string = strtr($string, $chars['in'], $chars['out']);
			$double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
			$double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
			$string = str_replace($double_chars['in'], $double_chars['out'], $string);
		}
	
		return $string;
	}
	
	/**
	 * Public A2O() method. Converts an Array to Object.
	 *
	 * @param $array Array to convert to object.
	 *
	 * @return $object Object converted or empty stdClass() if $array wasn't an array.
	 */
	public static function A2O($array) {//-- Array to Object
		$object = new stdClass();
		if (is_array($array) && count($array) > 0) {
			$object = (object) $array;
		}
		return $object;
	}
	
	/**
	 * Public O2A() method. Converts an Object to Array.
	 *
	 * @param $object Object to convert to array.
	 *
	 * @return $array Array converted or empty array() if $object wasn't an object.
	 */
	public static function O2A($object) {//-- Object to Array
		$array = array();
		if (is_object($object)) {
			$array = (array) $object;
		}
		return $array;
	}
	
	/**
	 * Public A2S() method. Converts an Array to String.
	 *
	 * @param $array Array to convert to string.
	 * @param $sep String the glue to implode the $array elements
	 *
	 * @return $string String converted or empty '' if $array wasn't an array.
	 */
	public static function A2S($array,$sep=',') {//-- Array to String
		$string = '';
		if (count($array) > 0) {
			$string = implode($sep,$array);
		}
		return $string;
	}
	
	/**
	 * Public S2A() method. Converts a String to Array.
	 *
	 * @param $string String to convert to Array.
	 * @param $sep String the separator to explode the $string
	 *
	 * @return $array Array converted or empty array() if $string wasn't a string.
	 */
	public static function S2A($string,$sep=',') {//-- String to Array
		$array = array();
		if (strlen($string) > 0) {
			$array = explode($sep,$string);
		}
		return $array;
	}
	
	/**
	 * Public debug() method. Deprecated way to show a Mixed variable.
	 *
	 * @deprecated use System::log() and System::trace() [with System::vlog() (it's usually at the end of every theme)]
	 *
	 * @param $var Mixed the variable to debug/show.
	 * @param $title String the title for the variable, to be easiear to view.
	 */
	public static function debug($var, $title = '') {
		if (WZ_DEBUG) {
			if (is_array($var) || is_object($var)) {
				if (!empty($title)) echo '<h3 style="font-family: Arial; font-size: 13px; text-align: center; font-weight: bold; color: #000000; background: #FFFFFF; display: block;">»»»»»»»»»»»» '.$title.' ««««««««««««</h3>';
				echo '<pre style="font-family: Arial; font-size: 11px; text-align: left; color: #000000; background: #FFFFFF; display: block;">';
				print_r($var);
				echo '</pre>';
			} else {
				echo '<h3 style="font-family: Arial; font-size: 13px; text-align: center; font-weight: bold; color: #000000; background: #FFFFFF; display: block;">»»»»»»»»»»»» '.(!empty($title)?$title.' --- ':'').$var.' ««««««««««««</h3>';
			}
		}
	}
	
	/**
	 * Public valid() method. Checks if a variable is valid.
	 *
	 * @param $type String the type of variable to check. Predefined values are 'email' 'date' and 'nowdate'.
	 * @param $var String the variable to check.
	 *
	 * @return Boolean true if $var is a valid $type, false otherwise.
	 */
	public static function valid($type, $var) {
		switch ($type) {
			case 'email':
				if (preg_match('/^[a-z0-9._-]+@[a-z0-9._-]+.[a-z]{2,6}$/i', $var)) {
					return true;
				} else {
					return false;
				}
			break;
			case 'sef':
				if (preg_match('/[^a-z0-9-]/', $var)) {
					return false;
				} else {
					return true;
				}
			break;
			case 'username':
				if (preg_match('/[^a-z0-9]/i', $var)) {
					return false;
				} else {
					return true;
				}
			break;
			case 'date':
				if (is_numeric($var) && strlen($var) == 8 && checkdate(substr($var,4,2),substr($var,6,2),substr($var,0,4))) {
					return true;
				} else {
					return false;
				}
			break;
			case 'nowdate':
				if (is_numeric($var) && strlen($var) == 8 && checkdate(substr($var,4,2),substr($var,6,2),substr($var,0,4)) && $var >= date('Ymd')) {
					return true;
				} else {
					return false;
				}
			break;
			default : return false;
		}
		return false;
	}
	
	/**
	 * Public toSEF() method. Converts a String to a Search Engine Friendly string.
	 *
	 * @param $string String to convert to SEF.
	 *
	 * @return $string String converted string.
	 */
	public static function toSEF($string) {
		$string = trim($string);
		$string = str_replace('_',' ',$string);
		$string = str_replace('-',' ',$string);

		while (strpos($string,'  ')) {
			$string = str_replace('  ',' ',$string);
		}

		$string = str_replace(' ','-',$string);
		$string = self::remove_accents($string);
		$string = strtolower($string);
		$string = preg_replace('/[^a-z0-9-]/s','',$string);
		while (strpos($string,'--') !== false) {
			$string = str_replace('--', '-', $string);
		}

		return $string;
	}
	
	/**
	 * Public cutHTML() method. Cuts in length (in number of chars) an HTML string (text is also possible, but it's better to use System::cutText() instead).
	 *
	 * @param $text String to cut.
	 * @param $length Integer number of chars to return. Defaults to 100.
	 * @param $ending String to append to the end of the returned text. Defaults to '...'.
	 * @param $cutWords Boolean true if words can be cut, false if words have to remain uncut. Defaults to true.
	 * @param $considerHtml Boolean true if HTML should be ignored from character count, false otherwise. Defaults to true.
	 *
	 * @return $string String cutted string.
	 */
	public static function cutHTML($text, $length = 100, $ending = '...', $cutWords = true, $considerHtml = true) {
		if ($considerHtml) {
			if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
				return $text;
			}

			preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

			$total_length = strlen($ending);
			$open_tags = array();
			$truncate = '';

			foreach ($lines as $line_matchings) {
				if (!empty($line_matchings[1])) {
					if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|a|p|base|basefont|col|frame|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
					} elseif (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
						$pos = array_search($tag_matchings[1], $open_tags);
						if ($pos !== false) {
							unset($open_tags[$pos]);
						}
					} elseif (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
						array_unshift($open_tags, strtolower($tag_matchings[1]));
					}
					$truncate .= $line_matchings[1];
				}

				$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if ($total_length+$content_length> $length) {
					$left = $length - $total_length;
					$entities_length = 0;
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
						foreach ($entities[0] as $entity) {
							if ($entity[1]+1-$entities_length <= $left) {
								$left--;
								$entities_length += strlen($entity[0]);
							} else {
								break;
							}
						}
					}
					$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
					break;
				} else {
					$truncate .= $line_matchings[2];
					$total_length += $content_length;
				}

				if($total_length>= $length) {
					break;
				}
			}
		} else {
			if (strlen($text) <= $length) {
				return $text;
			} else {
				$truncate = substr($text, 0, $length - strlen($ending));
			}
		}

		if (!$cutWords) {
			$spacepos = strrpos($truncate, ' ');
			if (isset($spacepos)) {
				$truncate = substr($truncate, 0, $spacepos);
			}
		}

		$truncate .= $ending;

		if($considerHtml) {
			foreach ($open_tags as $tag) {
				$truncate .= '</' . $tag . '>';
			}
		}
		return $truncate;
	}
	
	/**
	 * Public cutText() method. Cuts in length (in number of chars, but with width considered) for a text string.
	 *
	 * @param $txt String to cut.
	 * @param $len Integer number of chars to return. Defaults to 30.
	 * @param $ending String to append to the end of the returned text. Defaults to '...'.
	 *
	 * @return $string String cutted string.
	 */
	public static function cutText($txt, $len = 30, $ending = '...') {
		$strlen = strlen($txt);
		
		$small_chars = '.,:;|';
		$big_chars = 'wm@/\\çgqz';
		
		for ($i=0;$i<strlen($txt);$i++) {
			if (stristr($small_chars,$txt{$i})) $strlen -= 0.5;
			if (stristr($big_chars,$txt{$i})) $strlen += 0.5;
			if (ctype_upper($txt{$i})) $strlen += 0.5;
		}

		$strlen = round($strlen);

		if ($strlen > $len) {
			$txt = substr($txt,0,($len - strlen($ending))).$ending;
		}

		return $txt;
	}
	
	/**
	 * Public startCache() method. ob_start() alias.
	 *
	 * @uses ob_start()
	 */
	public static function startCache() {
		ob_start();
	}
	
	/**
	 * Public endCache() method. ob_get_clean() alias.
	 *
	 * @uses ob_get_clean()
	 *
	 * @param $return Boolean true if the method should return ob_get_clean(), false if it should be echoed. Defaults to true.
	 */
	public static function endCache($return = true) {
		if ($return) return ob_get_clean();
		echo ob_get_clean();
	}
	
	/**
	 * Public generatePwd() method. Generates a random string.
	 *
	 * @param $length Integer length of the string to be generated
	 *
	 * @return String generated random string.
	 */
	public static function generatePwd($length = 8) {
		$str = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!?$()=*+-,";
		return substr(str_shuffle($str.$str), 0, $length);
	}
	
	/**
	 * Public date() method. Convert numeric date into an understandeable date.
	 *
	 * @param $date String date to be converted (format from YYYYmmdd to YYYYmmddHHiiss).
	 * @param $format String date format to output.
	 * @param $dateformat Boolean true if strftime() should be used instead of date().
	 *
	 * @return String converted date.
	 */
	public static function date($date, $format = 'd-m-Y', $dateformat = true) {
		if (intval($date) == 0) {
			return '';
		}
		while (strlen($date) < 14) {
			$date .= '0';
		}
		$second = (int) substr($date,12,2);
		$minute = (int) substr($date,10,2);
		$hour = (int) substr($date,8,2);
		$day = (int) substr($date,6,2);
		$month = (int) substr($date,4,2);
		$year = (int) substr($date,0,4);
		$stamp = mktime($hour,$minute,$second,$month,$day,$year);
		if ($dateformat) {
			$format = str_replace('F','%B',$format);
			$format = str_replace('M','%b',$format);
			$format = str_replace('d','%d',$format);
			$format = str_replace('m','%m',$format);
			$format = str_replace('Y','%Y',$format);
			$format = str_replace('H','%H',$format);
			$format = str_replace('i','%M',$format);
			$format = str_replace('s','%S',$format);
			$format = str_replace('l','%w',$format);
			$format = str_replace('y','%y',$format);
			return strftime($format,$stamp);
		} else {
			return date($format,$stamp);
		}
	}
	
	/**
	 * Public parseDate() method. Convert understandeable date into a numeric one.
	 *
	 * @param $date String date to be converted (formats can be Y-m-d or d-m-Y, with any kind of separator).
	 * @param $format String date format from input (available options are 'Y-m-d' or 'd-m-Y').
	 * @param $separator String separator string for day, month, year.
	 * @param $returnf String format of the ouputted date.
	 *
	 * @return String converted date.
	 */
	public static function parseDate($date, $format = 'd-m-Y', $separator = '-', $returnf = 'Ymd') {
		if (strlen($date) < 10 || !strstr($date,$separator)) {
			return 0;
		}

		$date = str_replace($separator,'',$date);
		$day = 0;
		$month = 0;
		$year = 0;
		$hour = 0;
		$minute = 0;
		$second = 0;

		switch ($format) {
			case 'Y-m-d':
				$day = (int) substr($date,6,2);
				$month = (int) substr($date,4,2);
				$year = (int) substr($date,0,4);
			break;
			case 'd-m-Y':
			default:
				$day = (int) substr($date,0,2);
				$month = (int) substr($date,2,2);
				$year = (int) substr($date,4,4);
			break;
		}

		$stamp = mktime($hour,$minute,$second,$month,$day,$year);

		return date($returnf,$stamp);
	}

	/**
	 * Public time() method. Convert numeric date into a timestamp.
	 *
	 * @param $date String date to be converted (format from YYYYmmdd to YYYYmmddHHiiss).
	 *
	 * @return Integer timestamp.
	 */
	public static function time($date) {
		if (intval($date) == 0) {
			return '';
		}
		while (strlen($date) < 14) {
			$date .= '0';
		}
		$second = (int) substr($date,12,2);
		$minute = (int) substr($date,10,2);
		$hour = (int) substr($date,8,2);
		$day = (int) substr($date,6,2);
		$month = (int) substr($date,4,2);
		$year = (int) substr($date,0,4);
		$stamp = mktime($hour,$minute,$second,$month,$day,$year);

		return $stamp;
	}
	
	/**
	 * Public days() method. Returns the difference in days between two numeric dates.
	 *
	 * @param $start_date String start date for comparison.
	 * @param $end_date String end date for comparison. If default_value (0), current date is used.
	 *
	 * @return Integer number of days between the two dates.
	 */
	public static function days($start_date, $end_date = 0) {
		if ($start_date == 0) return 0;
		if ($end_date == 0) $end_date = date('YmdHis');
		while (strlen($end_date) < 14) {
			$end_date .= '0';
		}
		while (strlen($start_date) < 14) {
			$start_date .= '0';
		}

		$e_s = (int) substr($end_date,12,2);
		$e_i = (int) substr($end_date,10,2);
		$e_H = (int) substr($end_date,8,2);
		$e_d = (int) substr($end_date,6,2);
		$e_m = (int) substr($end_date,4,2);
		$e_Y = (int) substr($end_date,0,4);
		$e_stamp = mktime($e_H,$e_i,$e_s,$e_m,$e_d,$e_Y);

		$s_s = (int) substr($start_date,12,2);
		$s_i = (int) substr($start_date,10,2);
		$s_H = (int) substr($start_date,8,2);
		$s_d = (int) substr($start_date,6,2);
		$s_m = (int) substr($start_date,4,2);
		$s_Y = (int) substr($start_date,0,4);
		$s_stamp = mktime($s_H,$s_i,$s_s,$s_m,$s_d,$s_Y);

		$calc = round(($e_stamp - $s_stamp)/60/60/24);
		return $calc;
	}
	
	/**
	 * Public money() method. Returns a number formatted as money.
	 *
	 * @uses number_format()
	 *
	 * @param $number String number to format.
	 * @param $format String format. Use [AMOUNT] where the number should be placed.
	 *
	 * @return $formatted String formatted number.
	 */
	public static function money($number, $format = '&#8364; [AMOUNT]') {
		$formatted = '';
		$fn = number_format($number,2);
		$formatted = str_replace('[AMOUNT]',$fn,$format);
		return $formatted;
	}
	
	/**
	 * Public shuffle() method. Shuffles an associative array. If $list is not an array, it returns $list without any modification.
	 *
	 * @param $list Array to shuffle.
	 *
	 * @return $random Array shuffled array.
	 */
	public static function shuffle($list) {
		if (!is_array($list)) return $list;
		$keys = array_keys($list);
		shuffle($keys);
		$random = array();
		
		foreach ($keys as $key) {
			$random[$key] = $list[$key];
		}
		
		return $random;
	} 
}