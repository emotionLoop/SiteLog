<?php
/**
 * Framework base class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Framework
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.9.0 BETA
 */
abstract class Framework {
	/**
	 * Protected array $warnings (will have all Application Warnings)
	 */
	protected static $warnings = array();
	
	/**
	 * Protected array $errors (will have all Application Errors)
	 */
	protected static $errors = array();
	
	/**
	 * Protected array $msgs (will have all Application Messages)
	 */
	protected static $msgs = array();
	
	/**
	 * Public array $html (will have SEO meta tags, as well as JS and CSS files. It's also able to have customized html)
	 */
	public static $html = array();
	
	/**
	 * Public error() method. Used to add errors to the Application.
	 *
	 * @uses Framework::$errors
	 */
	public static function error($msg) {
		if (empty($msg)) return false;
		self::$errors[] = $msg;
	}
	
	/**
	 * Public warn() method. Used to add warnings to the Application.
	 *
	 * @uses Framework::$warnings
	 */
	public static function warn($msg) {
		if (empty($msg)) return false;
		self::$warnings[] = $msg;
	}
	
	/**
	 * Public msg() method. Used to add messages to the Application.
	 *
	 * @uses Framework::$msgs
	 */
	public static function msg($msg) {
		if (empty($msg)) return false;
		self::$msgs[] = $msg;
	}
	
	/**
	 * Public vErrors() method. Used display the Application errors, warnings and messages.
	 *
	 * @uses Framework::noErrors()
	 * @uses Framework::noWarns()
	 * @uses Framework::noMsgs()
	 */
	public static function vErrors() {
		$html = '';
		if (!self::noErrors() || !self::noWarns() || !self::noMsgs()) {
			$html .= '<div id="wz-notifications">';
		}
		if (!self::noErrors()) {
			$html .= '<div id="wz-errors" class="wz-notification">';
			foreach (self::$errors as $error) {
				$html .= '<span>'.$error.'</span>';
				$html .= '<div class="close_alert">'.__('Click this box to make it go away!').'</div>';
			}
			$html .= '</div>';
			
		}
		if (!self::noWarns()) {
			$html .= '<div id="wz-warnings" class="wz-notification">';
			foreach (self::$warnings as $warning) {
				$html .= '<span>'.$warning.'</span>';
				$html .= '<div class="close_alert">'.__('Click this box to make it go away!').'</div>';
			}
			$html .= '</div>';
		}
		if (!self::noMsgs()) {
			$html .= '<div id="wz-messages" class="wz-notification">';
			foreach (self::$msgs as $msg) {
				$html .= '<span>'.$msg.'</span>';
				$html .= '<div class="close_alert">'.__('Click this box to make it go away!').'</div>';
			}
			$html .= '</div>';
		}
		if (!self::noErrors() || !self::noWarns() || !self::noMsgs()) {
			$html .= '</div>';
		}
		echo $html;
	}
	
	/**
	 * Public noErrors() method. Used to know if there are errors in the Application or not.
	 *
	 * @uses Framework::$errors
	 *
	 * @return Boolean true if there are no errors, false otherwise.
	 */
	public static function noErrors() {
		if (count(self::$errors) > 0) return false;
		return true;
	}
	
	/**
	 * Public noWarns() method. Used to know if there are warnings in the Application or not.
	 *
	 * @uses Framework::$warnings
	 *
	 * @return Boolean true if there are no warnings, false otherwise.
	 */
	public static function noWarns() {
		if (count(self::$warnings) > 0) return false;
		return true;
	}
	
	/**
	 * Public noMsgs() method. Used to know if there are messages in the Application or not.
	 *
	 * @uses Framework::$msgs
	 *
	 * @return Boolean true if there are no messages, false otherwise.
	 */
	public static function noMsgs() {
		if (count(self::$msgs) > 0) return false;
		return true;
	}
	
	/**
	 * Public isWellProcessed() method. Used to know if there are errors or warnings in the Application.
	 *
	 * @uses Framework::noErrors()
	 * @uses Framework::noWarns()
	 *
	 * @return Boolean true if there are no errors or warnings, false otherwise.
	 */
	public static function isWellProcessed() {
		if (self::noErrors() && self::noWarns()) {
			return true;
		}
		return false;
	}
	
	/**
	 * Public url() method. Used to get an anchor tag.
	 *
	 * @param $url String the URL. Can be relative or absolute. If absolute and $autorel is true, a rel="external" parameter is added.
	 * @param $name String the text to show for the anchor. If empty $url is used.
	 * @param $params Array a Key/Value array with parameters to add to the anchor.
	 * @param $autorel Boolean true if the "rel" parameter should be added dynamically (external or license), false otherwise.
	 *
	 * @return $html String the anchor tag in html.
	 */
	public static function url($url, $name = '', $params = array(), $autorel = true) {
		$attrs = '';
		if (!preg_match('/((ht|f)tp(s)?:\/\/|(javascript|mailto):|#)/i',$url)) {
			$url = ST::$url.$url;
		} elseif (preg_match('/^(#|(#!)[0-9a-zA-Z])/i',$url)) {
			if ($url != '#') {
				$url = ST::$curl.$url;
			}
		} elseif ($autorel) {
			if (stripos($url,'creativecommons.org') !== false) {
				$params['rel'] = 'license';
			} elseif (strpos($url,'#') === false) {
				$params['rel'] = 'external';
			}
		}
		/* Convert & to &amp;'s */
		if (!stristr($url,'mailto:')) {
			$url = str_ireplace('&amp;','&',$url);
			$url = str_ireplace('&','&amp;',$url);
		}
		
		/* Parse parameters to attributes */
		if (is_array($params) && count($params) > 0) {
			foreach ($params as $paramn => $paramv) {
				if (!empty($attrs)) $attrs .= ' ';
				$attrs .= $paramn.'="'.$paramv.'"';
			}
		}
		
		/* Build final string */
		if (empty($name)) $name = $url;
		$html = '<a href="'.$url.'"';
		if (!empty($attrs)) $html .= ' '.$attrs;
		$html .= '>'.$name.'</a>';
		return $html;
	}
	
/**
	 * Public urlanchor() method. Used to get an anchor tag.
	 *
	 * @param $url String the URL. Can be relative or absolute. If absolute and $autorel is true, a rel="external" parameter is added.
	 * @param $name String the text to show for the anchor. If empty $url is used.
	 * @param $params Array a Key/Value array with parameters to add to the anchor.
	 * @param $autorel Boolean true if the "rel" parameter should be added dynamically (external or license), false otherwise.
	 *
	 * @return $html String the anchor tag in html.
	 */
	public static function urlanchor($url, $name = '', $params = array(), $autorel = true) {
		$attrs = '';
		if (!preg_match('/((ht|f)tp(s)?:\/\/|(javascript|mailto):|#)/i',$url)) {
			$url = ST::$url.$url;
		} elseif (preg_match('/(#[0-9a-zA-Z])/i',$url)) {
			$url = ST::$url;
		} elseif ($autorel) {
			if (stripos($url,'creativecommons.org') !== false) {
				$params['rel'] = 'license';
			} elseif (strpos($url,'#') === false) {
				$params['rel'] = 'external';
			}
		}
		/* Convert & to &amp;'s */
		if (!stristr($url,'mailto:')) {
			$url = str_ireplace('&amp;','&',$url);
			$url = str_ireplace('&','&amp;',$url);
		}
		
		/* Parse parameters to attributes */
		if (is_array($params) && count($params) > 0) {
			foreach ($params as $paramn => $paramv) {
				if (!empty($attrs)) $attrs .= ' ';
				$attrs .= $paramn.'="'.$paramv.'"';
			}
		}
		
		/* Build final string */
		if (empty($name)) $name = $url;
		$html = '<a href="'.$url.'"';
		if (!empty($attrs)) $html .= ' '.$attrs;
		$html .= '>'.$name.'</a>';
		return $html;
	}
	
	
	/**
	 * Public img() method. Used to get an image tag.
	 *
	 * @param $url String the URL. Can be relative or absolute. If relative and $ext is false, 'images/' is prepended to $url.
	 * @param $alt String the text to show for the "alt" and "title" parameters of img.
	 * @param $params Array a Key/Value array with parameters to add to the image.
	 * @param $ext Boolean false if the image is not external to the site, true otherwise.
	 *
	 * @return $html String the anchor tag in html.
	 */
	public static function img($url, $alt = '', $params = array(), $ext = false) {
		$attrs = '';
		if (preg_match('/(http(s)?:\/\/)/i',$url)) $ext = true;
		if (!$ext) {
			$url = /*ST::$url.*/'images/'.$url;
		}
		
		/* Parse parameters to attributes */
		if (is_array($params) && count($params) > 0) {
			foreach ($params as $paramn => $paramv) {
				if (!empty($attrs)) $attrs .= ' ';
				$attrs .= $paramn.'="'.$paramv.'"';
			}
		}
		
		/* Build final string */
		$html = '<img src="'.$url.'" alt="'.$alt.'" title="'.$alt.'"';
		if (!empty($attrs)) $html .= ' '.$attrs;
		$html .= ' />';
	
		return $html;
	}
	
	/**
	 * Public set() method. Used to set html to show on the <head> tag.
	 *
	 * @uses Framework::$html
	 *
	 * @param $var String the variable to set. Predefined are 'head', 'title', 'seo_title', 'seo_description' and 'seo_keywords'
	 * @param $val String the value for the variable.
	 * @param $clear Boolean false if the new value should be appended to the Framework::$html array, true otherwise.
	 */
	public static function set($var, $val, $clear = false) {
		switch ($var) {
			case 'head':
				if (!isset(self::$html[$var]) || $clear) self::$html[$var] = array();
				self::$html[$var][] = $val;
			break;
			case 'title':
				self::$html[$var] = htmlentities($val,ENT_COMPAT,'UTF-8');
			break;
			case 'seo_title':
				self::$html[$var] = '<meta name="title" content="'.htmlentities($val,ENT_COMPAT,'UTF-8').'" />';
			break;
			case 'seo_description':
				self::$html[$var] = '<meta name="description" content="'.htmlentities($val,ENT_COMPAT,'UTF-8').'" />';
			break;
			case 'seo_keywords':
				self::$html[$var] = '<meta name="keywords" content="'.htmlentities($val,ENT_COMPAT,'UTF-8').'" />';
			break;
			default:
				if (!isset(self::$html[$var]) || $clear) self::$html[$var] = '';
				self::$html[$var] .= $val;
			break;
		}
	}
	
	/**
	 * Public get() method. Used to get html from Framework::$html to show on the <head> tag.
	 *
	 * @uses Framework::$html
	 * @uses System::A2S()
	 *
	 * @param $var String the variable to get. Predefined are 'head', 'title', 'seo_title', 'seo_description' and 'seo_keywords'
	 * @param $raw Boolean if the value is an array and $raw is fale, it'll be outputted as a concatenated string. If $raw is true, the original value will be outputted.
	 *
	 * @return Mixed Framework::$html[$var], String or null.
	 */
	public static function get($var, $raw = false) {
		if (isset(self::$html[$var])) {
			if (is_array(self::$html[$var]) && !$raw) {
				return Sys::A2S(self::$html[$var],"\n");
			} else {
				return self::$html[$var];
			}
		}
		return null;
	}
	
	/**
	 * Public addJS() method. Used to add JS files to <head>.
	 *
	 * @uses Framework::set()
	 *
	 * @param $url String the URL. Can be relative or absolute. If relative 'js/' is prepended to $url.
	 * @param $params Array a Key/Value array with parameters to add to the script tag.
	 */
	public static function addJS($url, $params = array()) {
		$attrs = '';
		if (!preg_match('/(http(s)?:\/\/)/i',$url)) {
			$url = /*ST::$url.*/'js/'.$url;
		}

		/* Parse parameters to attributes */
		if (is_array($params) && count($params) > 0) {
			foreach ($params as $paramn => $paramv) {
				if (!empty($attrs)) $attrs .= ' ';
				$attrs .= $paramn.'="'.$paramv.'"';
			}
		}

		/* Build final string */
		$html = '<script src="'.$url.'"';
		if (!empty($attrs)) $html .= ' '.$attrs;
		$html .= '></script>';

		self::set('head',$html);
	}
	
	/**
	 * Public addCSS() method. Used to add CSS files to <head>.
	 *
	 * @uses Framework::set()
	 *
	 * @param $url String the URL. Can be relative or absolute. If relative 'css/' is prepended to $url.
	 * @param $media String the media parameter for the <link> tag. Defaults to 'all'.
	 * @param $params Array a Key/Value array with parameters to add to the link tag.
	 */
	public static function addCSS($url, $media = 'all', $params = array()) {
		$attrs = '';
		if (!preg_match('/(http(s)?:\/\/)/i',$url)) {
			$url = /*ST::$url.*/'css/'.$url;
		}

		/* Parse parameters to attributes */
		if (is_array($params) && count($params) > 0) {
			foreach ($params as $paramn => $paramv) {
				if (!empty($attrs)) $attrs .= ' ';
				$attrs .= $paramn.'="'.$paramv.'"';
			}
		}

		/* Build final string */
		$html = '<link rel="stylesheet" href="'.$url.'" media="'.$media.'"';
		if (!empty($attrs)) $html .= ' '.$attrs;
		$html .= ' />';

		self::set('head',$html);
	}
	
	/**
	 * Public mail() method. Used to send an HTML email.
	 *
	 * @uses System::startCache()
	 * @uses System::endCache()
	 * @uses Settings::get()
	 *
	 * @param $name String From name.
	 * @param $email String From email.
	 * @param $to_mail String To email.
	 * @param $subject String Email's subject.
	 * @param $msg String HTML Email message.
	 * @param $attachment String absolute path to a file. Defaults to null.
	 *
	 * @return true/faste Boolean true if mail was sent, false otherwise.
	 */
	public static function mail($name, $email, $to_mail, $subject, $msg, $attachment = null) {
		$sending = false;

		if (!empty($name) && !empty($email) && !empty($to_mail) && !empty($subject) && !empty($msg)) {
			$from_name = $name;
			$from_mail = $email;
			$sending = true;
		}

		if ($sending) {
			$eol = "\n";

			$tosend['email'] = $to_mail;
			$tosend['subject'] = $subject;

			$tosend['message'] = '';
			Sys::startCache();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $subject; ?></title>
</head>
<body>
<?php echo $msg; ?><br />
<?php echo ST::get('email_footer'); ?>
</body>
</html>

<?php
			$tosend['message'] = Sys::endCache();
			$tosend['headers'] = "From: \"".$from_name."\" <".$from_mail.">".$eol;
			$tosend['headers'] .= "Return-path: <".$from_mail.">".$eol;
			$tosend['headers'] .= "MIME-Version: 1.0".$eol;
			if (!empty($attachment)) {
				$file = $attachment;
				$content = file_get_contents($file);
				$content = chunk_split(base64_encode($content));
				$uid = md5(uniqid(time()));
				$f_name = str_replace(ST::get('path'),'',$attachment);
				$tosend['headers'] .= "Content-Type: multipart/mixed; boundary=\"PHP-mixed-".$uid."\"".$eol.$eol;
				$tosend['headers'] .= "This is a multi-part message in MIME format.".$eol;
				$tosend['headers'] .= "--PHP-mixed-".$uid."".$eol;
				$tosend['headers'] .= "Content-Type: multipart/alternative; boundary=\"PHP-alt-".$uid."\"".$eol.$eol;
				$tosend['headers'] .= "--PHP-alt-".$uid."".$eol;
				$tosend['headers'] .= "Content-type: text/html; charset=utf-8".$eol;
				$tosend['headers'] .= "Content-Transfer-Encoding: 7bit".$eol.$eol;
				$tosend['headers'] .= $tosend['message']."".$eol.$eol;
				$tosend['headers'] .= "--PHP-alt-".$uid."--".$eol;
				$tosend['headers'] .= "--PHP-mixed-".$uid."".$eol;
				$tosend['headers'] .= "Content-Type: application/octet-stream; name=\"".$f_name."\"".$eol;
				$tosend['headers'] .= "Content-Transfer-Encoding: base64".$eol;
				$tosend['headers'] .= "Content-Disposition: attachment; filename=\"".$f_name."\"".$eol.$eol;
				$tosend['headers'] .= $content."".$eol.$eol;
				$tosend['headers'] .= "--PHP-mixed-".$uid."--";
				$tosend['message'] = "";//-- The message is already in the headers.
			} else {
				$tosend['headers'] .= "Content-type: text/html; charset=utf-8".$eol;
			}

			if (mail($tosend['email'], $tosend['subject'],  $tosend['message'] , $tosend['headers'])) {
				return true;
			} else {
				return false;
			}
		}//-- if ($sending)
		return false;
	}
	
	/**
	 * Public redirect() method. Used to trigger a browser redirect.
	 *
	 * @uses Settings::get()
	 * @uses header()
	 * @uses Application::stop()
	 *
	 * @param $url String the URL to redirect to. Can be empty, relative or absolute. If empty, Settings' "url" is used.
	 * @param $status Mixed numeric redirect status code (predefined are 301, 302, 303 and 307) or string with code + description. Defaults to null, thus not used, because header() with Location already sets the default.
	 */
	public static function redirect($url = '', $status = null) {
		if (empty($url)) $url = ST::get('url');
		if (!preg_match('/((ht|f)tp(s)?:\/\/)/i', $url)) {
			$url = ST::get('url').$url;
		}
		
		if (!is_null($status)) {
			switch ($status) {
				case 301:
				case '301':
					header("Status: 301 Moved Permanently");
				break;
				case 302:
				case '302':
					header("Status: 302 Found");
				break;
				case 303:
				case '303':
					header("Status: 303 See Other");
				break;
				case 307:
				case '307':
					header("Status: 307 Temporary Redirect");
				break;
				default:
					header("Status: ".$status);
				break;
			}
		}

		header("Location: ".$url);
		App::stop();
	}
	
	/**
	 * Public refresh() method. Used to trigger a browser redirect after X seconds.
	 *
	 * @uses Settings::get()
	 * @uses header()
	 *
	 * @param $url String the URL to redirect to. Can be empty, relative or absolute. If empty, Settings' "url" is used.
	 * @param $seconds Integer the number of seconds to wait before redirecting. Defaults to 5.
	 */
	public static function refresh($url = '',$seconds = 5) {
		if (empty($url)) $url = ST::get('url');
		if (!preg_match('/((ht|f)tp(s)?:\/\/)/i', $url)) {
			$url = ST::get('url').$url;
		}

		header("Refresh: ".$seconds."; url=".$url);
	}
	
	/**
	 * Public eTemplate() method. Used to get an email template.
	 *
	 * @uses Language::$code
	 * @uses Database::prepare()
	 * @uses Database::sexecute()
	 * @uses Settings::get()
	 * @uses Language::get()
	 *
	 * @param $tplname String the identifier name of the email template to get.
	 * @param $lang String the language code to use, if it's not the default.
	 * @return $result Mixed object of the email template, or false if the query didn't go through.
	 */
	public static function eTemplate($tplname, $lang = '') {
		if (empty($lang)) $lang = Lng::$code;

		$sql = "SELECT * FROM `wz_etemplates` WHERE `name` = '".DB::prepare($tplname)."' AND `status` = 1";
		$result = DB::sexecute($sql,89);
		if ($lang != ST::get('default_lng')) {
			$result->from_name = Lng::get($result->id,'from_name','wz_etemplates',$lang);
			$result->from_email = Lng::get($result->id,'from_email','wz_etemplates',$lang);
			$result->subject = Lng::get($result->id,'subject','wz_etemplates',$lang);
			$result->msg = Lng::get($result->id,'msg','wz_etemplates',$lang);
		}

		//if (is_object($result)) $result->msg = nl2br($result->msg);

		return $result;
	}
	
	/**
	 * Public printCaptcha() method. Used to print a captcha field.
	 *
	 * @uses wCaptcha::showCaptcha()
	 *
	 * @param $form_id String the id of the form.
	 * @param $type Integer type. 0: Horizontal, 1: Vertical.
	 * @param $field_name String the name of the captcha field.
	 * @param $doCSS Boolean if the wCaptcha should handle the CSS
	 * @param $echo Boolean true if the captcha should be echoed, false if returned
	 */
	public static function printCaptcha($form_id = null,$type = null, $field_name = null, $doCSS = true, $echo = true) {//-- wCaptcha
		require_once('framework/classes/wcaptcha.class.php');
		
		$wCaptcha = new wCaptcha($form_id,$type,$field_name,$doCSS);
		if (!$echo) {
			ob_start();
		}
		$wCaptcha->showCaptcha();
		if (!$echo) {
			return ob_get_clean();
		}
	}
	
	/**
	 * Public validCaptcha() method. Used to check if a captcha field was validated.
	 *
	 * @uses wCaptcha::isValidCaptcha()
	 *
	 * @param $form_id String the id of the form.
	 * @param $type Integer type. 0: Horizontal, 1: Vertical.
	 * @param $field_name String the name of the captcha field.
	 * @param $doCSS Boolean if the wCaptcha should handle the CSS
	 * @return Boolean true/false if the captcha field was validated.
	 */
	public static function validCaptcha($form_id = null,$type = null, $field_name = null, $doCSS = true) {//-- wCaptcha
		require_once('framework/classes/wcaptcha.class.php');
		
		$wCaptcha = new wCaptcha($form_id,$type,$field_name,$doCSS);
		return $wCaptcha->isValidCaptcha();
	}
}