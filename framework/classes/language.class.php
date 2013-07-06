<?php
/**
 * Language base class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Language
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.9.0 BETA
 */
abstract class Language {
	/**
	 * Public String $code (language code). Defaults to 'en'
	 */
	public static $code = 'en';
	
	/**
	 * Public String $charset (language charset). Defaults to 'utf-8'
	 */
	public static $charset = 'utf-8';
	
	/**
	 * Public String $dir (language dir). Defaults to 'ltr'
	 */
	public static $dir = 'ltr';
	
	/**
	 * Public String $locale (language locale). Defaults to 'en_US'
	 */
	public static $locale = 'en_US';
	
	/**
	 * Public String $table (language DB table). Defaults to 'wz_texts'
	 */
	protected static $table = 'wz_texts';
	
	/**
	 * Public Array $ext (language extra string translations).
	 */
	protected static $ext = array();
	
	/**
	 * Public __construct() method. Set language attributes.
	 *
	 * @uses Database::query()
	 * @uses Database::fetch()
	 * @uses setlocale()
	 */
	public function __construct() {
		self::$code = self::setLanguage();
		$sql = "SELECT `lid`, `hash`, `category`, `text` FROM `".self::$table."` WHERE `lid` = '".self::$code."'";
		DB::query($sql,89);
		while ($result = DB::fetch(89)) {
			switch($result->hash) {
				case 'charset':
					self::$charset = $result->text;
				break;
				case 'dir':
					self::$dir = $result->text;
				break;
				case 'locale':
					self::$locale = $result->text;
				break;
				default:
					self::$ext[$result->hash.':'.sha1($result->category)] = $result->text;
				break;
			}
		}
		setlocale(LC_ALL,self::$locale);
	}
	
	/**
	 * Public t() method. Get the translation or original value for a given text, within a category.
	 * Triggers a System::trace() if the $category is empty.
	 *
	 * @uses System::trace()
	 * @uses Database::prepare()
	 * @uses Database::get()
	 * @uses Database::build()
	 * @uses Database::query()
	 *
	 * @param $text String the original text to be translated.
	 * @param $category String the category of the app, used to be easier to understand what the $text refers to, if not easily understandable
	 * @param $lang String the code of the language to use. If empty, current language is used. Defaults to null
	 *
	 * @return $txt String Translated text or original text if no translation was found.
	 */
	public static function t($text, $category = 'app', $lang = null) {
		$text = trim($text);
		$txt = '';
		if (empty($text)) return $txt;
		if (empty($category)) {
			Sys::trace('Category is empty for '.$text);
			return $text;
		}

		if (empty($lang)) $lang = self::$code;
		$hash = sha1($text);
		
		if ($lang == self::$code && isset(self::$ext[$hash.':'.sha1($category)])) {
			return self::$ext[$hash.':'.sha1($category)];
		}

		$sql = "SELECT `text` FROM `".self::$table."` WHERE `lid` = '".DB::prepare($lang)."' AND `category` = '".DB::prepare($category)."' AND `hash` = '".DB::prepare(sha1($text))."'";
		$res = DB::get($sql,89);
		if ($res) {
			return $res;
		} else {
			$fields = array(
				'lid' => $lang,
				'category' => $category,
				'text' => $text,
				'hash' => $hash
			);
			$sql = DB::build($fields,self::$table,'insert');
			DB::query($sql,89);
			if ($lang == self::$code) {
				self::$ext[$hash.':'.sha1($category)] = $text;
			}
			$txt = $text;
		}

		return $txt;
	}
	
	/**
	 * Public get() method. Get the translation for DB object field. Triggers a System::trace() if the translation/field doesn't exist for the given parameters.
	 *
	 * @uses Database::prepare()
	 * @uses Database::get()
	 * @uses System::trace()
	 *
	 * @param $id Integer the ID of the object to get the translation for
	 * @param $field String the name of the field of the object
	 * @param $table String the name of the table of the object
	 * @param $lang String the code of the language to use. If empty, current language is used. Defaults to null
	 *
	 * @return $res String Translated text or original text if no translation was found. If the $table/$field doesn't exist, an empty string is returned.
	 */
	public static function get($id, $field, $table, $lang = null) {
		if (empty($lang)) $lang = self::$code;

		$sql = "SELECT `value` FROM `".$table."_lng"."` WHERE `lid` = '".DB::prepare($lang)."' AND `field` = '".DB::prepare($field)."' AND `object` = '".DB::prepare($id)."'";
		$res = DB::get($sql,89);
		if ($res) {
			return $res;
		} else {
			$sql = "SELECT `".$field."` FROM `".$table."` WHERE `id` = '".DB::prepare($id)."'";
			$res = DB::get($sql,89);
			if ($res) return $res;
		}
		Sys::trace('Translation/field does not exist for '.$table.':'.$field);
		return '';
	}
	
	/**
	 * Protected setLanguage() method. Return the current language, based on Settings' available_lngs, Settings' default_lng, user browser preferences, cookies and request variables.
	 *
	 * @uses System::S2A()
	 * @uses Settings::get()
	 * @uses Session::get()
	 * @uses Session::cook()
	 *
	 * @return $res String Current user chosen language code.
	 */
	protected static function setLanguage() {
		$a_langs = Sys::S2A(ST::get('available_lngs'));
		$d_lang = ST::get('default_lng');
		$u_langs = Sys::S2A(strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']));//-- No need to check if it's set, since System already handled that
		$r_lang = SS::get('lng', 'get','','string');
		$c_lang = '';
		$ck_lng = SS::get('lng','cookie','','string');

		if (!empty($r_lang) && in_array($r_lang,$a_langs)) {
			SS::cook('lng',$r_lang);
			$c_lang = $r_lang;
		} elseif (!empty($ck_lng) && in_array($ck_lng,$a_langs)) {
			SS::cook('lng',$ck_lng);
			$c_lang = $ck_lng;
		} else {
			$flag = false;
			foreach ($u_langs as $tmp) {
				if ($flag) continue;
				list($u_lang) = Sys::S2A($tmp,';');
				if (isset($u_lang) && in_array($u_langs,$a_langs)) {
					SS::cook('lng',$u_lang);
					$c_lang = $u_lang;
					$flag = true;
				}
			}
			if (!$flag) {
				SS::cook('lng',$d_lang);
				$c_lang = $d_lang;
			}
		}

		return $c_lang;
	}
}


/**
 * Retrieves the translation of $text. If there is no translation the original text is returned.
 * An alias of Language::t()
 *
 * @uses Language::t()
 *
 * @param string $text Text to translate
 * @param string $category Optional. Category to retrieve the translated text
 * @return string Translated text
 */
function __( $text, $category = 'app' ) {
	return Lng::t( $text, $category );
}

/**
 * Displays the returned translated text from Language::t().
 *
 * @see __() Echoes returned string
 *
 * @param string $text Text to translate
 * @param string $category Optional. Category to retrieve the translated text
 */
function _e( $text, $category = 'app' ) {
	echo Lng::t( $text, $category );
}

/**
 * Retrieve the plural or single form based on the amount.
 *
 * @uses __()
 *
 * @param string $single The text that will be used if $number is 1
 * @param string $plural The text that will be used if $number is not 1
 * @param int $number The number to compare against to use either $single or $plural
 * @param string $category Optional. The category identifier the text should be retrieved in
 * @return string Either $single or $plural translated text
 */
function _n( $single, $plural, $number, $category = 'app' ) {
	if ($number == 1) {
		return __( $single, $category );
	} else {
		return __( $plural, $category );
	}
}

/**
 * Retrieve the plural or single form based on the amount.
 *
 * @uses _e()
 *
 * @param string $single The text that will be used if $number is 1
 * @param string $plural The text that will be used if $number is not 1
 * @param int $number The number to compare against to use either $single or $plural
 * @param string $category Optional. The category identifier the text should be retrieved in
 * @return string Either $single or $plural translated text
 */
function _ne( $single, $plural, $number, $category = 'app' ) {
	if ($number == 1) {
		_e( $single, $category );
	} else {
		_e( $plural, $category );
	}
}
