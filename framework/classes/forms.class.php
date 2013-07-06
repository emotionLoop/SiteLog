<?php
/**
 * Forms base class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Forms
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.9.0 BETA
 */
abstract class Forms {
	/**
	 * Public start() method. This method outputs the starting <form> tag.
	 *
	 * @uses Forms::attr()
	 *
	 * @param $id String the id and name attributes for the form.
	 * @param $action String the action attribute for the form.
	 * @param $attrs Array Key/Value of extra attributes for the form.
	 * @param $method String the method attribute for the form.
	 * @param $echo Boolean true if the form tag should be echoed, false if returned
	 */
	public static function start($id = null, $action = '', $attrs = array(), $method = 'post', $echo = true) {
		$str = '<form action="'.$action.'" method="'.$method.'"';
		if (!empty($id)) {
			$str .= ' id="'.$id.'" name="'.$id.'"';
		}
		$str .= !empty($attrs) ? self::attr($attrs).'>': '>';
		if ($echo) {
			echo $str;
		} else {
			return $str;
		}
	}
	
	/**
	 * Protected attr() method. This method returns an attributes key/value array as an attributes string.
	 * Triggers a System::trace if the $attrs parameter isn't an array.
	 *
	 * @uses System::trace()
	 *
	 * @param $attrs Array Key/Value of attributes.
	 */
	protected static function attr($attrs) {
		$str = '';
		if (!is_array($attrs)) {
			Sys::trace('Attributes are not an array!','Forms');
			return $str;
		}
		// check minimized attributes
		$min_atts = array('checked', 'disabled', 'readonly', 'multiple', 'selected', 'autofocus');
		foreach ($attrs as $key=>$val) {
			if (in_array($key, $min_atts)) {
				if (!empty($val)) {
					$str .= ' '.$key.'="'.$key.'"';
				}
			} else {
				$str .= ' '.$key.'="'.$val.'"';
			}
		}
		return $str;
	}
	
	/**
	 * Public button() method. This method outputs a button field.
	 *
	 * @uses Forms::attr()
	 *
	 * @param $name String the name of the button, that will also be used as ID.
	 * @param $type String the type of the button. Usually button, sometimes submit.
	 * @param $text String the text to show on the button.
	 * @param $attrs Array Key/Value of extra attributes for the button.
	 * @param $echo Boolean true if the button should be echoed, false if returned
	 */
	public static function button($name, $type, $text, $attrs = array(), $echo = true) {
		$str = '<button type="'.$type.'" name="'.$name.'" id="'.$name.'"';
		if (!empty($attrs)) {
			$str .= self::attr($attrs);
		}
		$str .= '>'.$text.'</button>';
		if ($echo) {
			echo $str;
		} else {
			return $str;
		}
	}
	
	/**
	 * Public input() method. This method outputs an input field.
	 *
	 * @uses Forms::attr()
	 * @uses System::esc_attr()
	 *
	 * @param $name String the name of the input, that will also be used as ID.
	 * @param $type String the type of the input.
	 * @param $value String the value of the input.
	 * @param $attrs Array Key/Value of extra attributes for the input.
	 * @param $echo Boolean true if the input should be echoed, false if returned
	 */
	public static function input($name, $type, $value = '', $attrs = array(), $echo = true) {
		$str = '<input type="'.$type.'" name="'.$name.'" id="'.$name.'" value="'.Sys::esc_attr($value).'"';
		if (!empty($attrs)) {
			$str .= self::attr($attrs);
		}
		$str .= ' />';
		if ($echo) {
			echo $str;
		} else {
			return $str;
		}
	}
	
	/**
	 * Public textarea() method. This method outputs a textarea field.
	 *
	 * @uses Forms::attr()
	 * @uses System::esc_attr()
	 * @uses System::esc_html()
	 *
	 * @param $name String the name of the textarea, that will also be used as ID.
	 * @param $value String the value of the textarea.
	 * @param $rows Integer the number of rows.
	 * @param $cols Integer the number of columns.
	 * @param $attrs Array Key/Value of extra attributes for the textarea.
	 * @param $echo Boolean true if the textarea should be echoed, false if returned
	 */
	public static function textarea($name, $value = '', $rows = 4, $cols = 30, $attrs = array(), $echo = true) {
		$str = '<textarea name="'.$name.'" id="'.$name.'" rows="'.$rows.'" cols="'.$cols.'"';
		if (!empty($attrs)) {
			$str .= self::attr($attrs);
		}
		$str .= '>'.Sys::esc_html($value).'</textarea>';
		if ($echo) {
			echo $str;
		} else {
			return $str;
		}
	}
	
	/**
	 * Public label() method. This method outputs a label field.
	 *
	 * @uses Forms::attr()
	 *
	 * @param $forID String the for attribute. Basically the ID of the field this is a label of.
	 * @param $text String the text to show for the label.
	 * @param $attrs Array Key/Value of extra attributes for the textarea.
	 * @param $echo Boolean true if the textarea should be echoed, false if returned
	 */
	public static function label($forID, $text, $attrs = array(), $echo = true) {
		$str = '<label for="'.$forID.'"';
		if (!empty($attrs)) {
			$str .= self::attr($attrs);
		}
		$str .= '>'.$text.'</label>';
		if ($echo) {
			echo $str;
		} else {
			return $str;
		}
	}
	
	/**
	 * Public select() method. This method outputs a select field.
	 *
	 * @uses Forms::attr()
	 *
	 * @param $name String the name of the select, that will also be used as ID.
	 * @param $option_list Array the options Key/Value array if $bVal is true, Value array if $bVal is false.
	 * @param $value Mixed the selected value.
	 * @param $header String a first entry for the options.
	 * @param $attrs Array Key/Value of extra attributes for the select.
	 * @param $bVal Boolean true if $option_list array keys are to be used as values, false otherwise.
	 * @param $echo Boolean true if the select should be echoed, false if returned
	 */
	public static function select($name, $option_list, $value = null, $header = null, $attrs = array(), $bVal = true, $echo = true) {
		$str = '<select name="'.$name.'" id="'.$name.'"';
		if (!empty($attrs)) {
			$str .= self::attr($attrs);
		}
		$str .= '>'."\n";
		if (isset($header)) {
			$str .= '	<option value="">'.$header.'</option>'."\n";
		}
		foreach ($option_list as $val => $text) {
			$str .= $bVal ? '	<option value="'.$val.'"': '	<option';
			if (!empty($value) && ($value == $val || $value == $text)) {
				$str .= ' selected="selected"';
			}
			$str .= '>'.$text.'</option>'."\n";
		}
		$str .= '</select>';
		if ($echo) {
			echo $str;
		} else {
			return $str;
		}
	}
	
	/**
	 * Public captcha() method. This method outputs a captcha field.
	 *
	 * @uses Framework::printCaptcha()
	 *
	 * @param $form_id String the id of the form.
	 * @param $type Integer type. 0: Horizontal, 1: Vertical.
	 * @param $field_name String the name of the captcha field.
	 * @param $doCSS Boolean if the wCaptcha should handle the CSS
	 * @param $echo Boolean true if the captcha should be echoed, false if returned
	 */
	public static function captcha($form_id = null,$type = null, $field_name = null, $doCSS = true, $echo = true) {
		if ($echo) FW::printCaptcha($form_id, $type, $field_name, $doCSS, $echo);
		else return FW::printCaptcha($form_id, $type, $field_name, $doCSS, $echo);
	}
	
	/**
	 * Public end() method. This method outputs the closing <form> tag.
	 *
	 * @param $echo Boolean true if the select should be echoed, false if returned
	 */
	public static function end($echo = true) {
		$str = '</form>';
		if ($echo) {
			echo $str;
		} else {
			return $str;
		}
	}
}
?>