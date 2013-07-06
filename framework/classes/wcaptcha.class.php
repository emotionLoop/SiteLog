<?php
/**
 * wCpatcha class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wCaptcha
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 1.0.1
 */
 
class wCaptcha {
	protected $form_id = 'frm_captcha';
	protected $type = 0;
	protected $field_name = 'captcha-value';
	protected $js = '';
	protected $css = '';
	protected $html = '';
	protected $hash = '';
	protected $answers = array();
	protected $options = array();
	protected $options_props = array();
	protected $value = '';
	protected $value_props = array();
	protected $jsPacker = 'framework/classes/jspacker.class.php';

	public function __construct($form_id = NULL,$type = NULL, $field_name = NULL, $doCSS = true) {
		$this->hash = sha1('wozia::'.$_SERVER['REMOTE_ADDR'].'::wcaptcha::'.Sys::nonce_tick(1800).'::tick');
		
		if (!is_null($form_id)) {
			$this->form_id = $form_id;
		}
		if (!is_null($type)) {
			$this->type = $type;
		}
		if (!is_null($field_name)) {
			$this->field_name = $field_name;
		}
		$this->answers = array(
			'tv' => array('images/wcaptcha/advertising.png', __('TV','wcaptcha')),
			'flag' => array('images/wcaptcha/flag.png', __('Flag','wcaptcha')),
			'heart' => array('images/wcaptcha/heart.png', __('Heart','wcaptcha')),
			'book' => array('images/wcaptcha/library.png', __('Book','wcaptcha')),
			'lamp' => array('images/wcaptcha/lightbulb.png', __('Light Bulb','wcaptcha')),
			'lock' => array('images/wcaptcha/lock.png', __('Lock','wcaptcha')),
			'pen' => array('images/wcaptcha/pen.png', __('Pen','wcaptcha')),
			'puzzle' => array('images/wcaptcha/product.png', __('Puzzle','wcaptcha')),
			'binoculars' => array('images/wcaptcha/search.png', __('Binoculars','wcaptcha')),
			'star' => array('images/wcaptcha/star.png', __('Star','wcaptcha')),
			'world' => array('images/wcaptcha/world.png', __('World','wcaptcha')),
			'mglass' => array('images/wcaptcha/zoom.png', __('Magnifying Glass','wcaptcha'))
		);
		if ($doCSS) {
			ob_start();
?>
<style type="text/css">
<?php
			switch ($this->type) {
				case 0://-- Horizontal
?>
div.wozia-captcha {
	margin: 0;
	padding: 0;
	display: block;
	width: 350px;
}
div.wozia-possibilities {
	margin: 0;
	padding: 34px 0 0 0;
	display: block;
	width: 200px;
	float: left;
	height: 66px;
}
<?php
				break;
				case 1://-- Vertical
?>
div.wozia-captcha {
	margin: 10px 0 0 10px;
	padding: 0;
	display: block;
	width: 200px;
}
div.wozia-possibilities {
	margin: 0;
	padding: 14px 0 0 0;
	display: block;
	width: 100px;
	float: left;
	height: 46px;
}
div.wozia-possibilities > img {
	padding: 0 4px 4px 4px !important;
}
div.wozia-where2go {
	margin: 0 !important;
}
<?php
				break;
			}
?>
div.wozia-possibilities > img {
	width: 32px;
	height: 32px;
	display: block;
	float: left;
	margin: 0;
	padding: 0 4px;
	z-index: 5;
}
div.wozia-where2go {
	margin: 0 0 0 20px;
	padding: 0;
	display: block;
	width: 100px;
	background: transparent url('images/wcaptcha/circulo-<?php echo Lng::$code; ?>.png') center center no-repeat;
	float: left;
	height: 100px;
}
div.clear {
	clear: both;
}
</style>
<?php
			$this->css = ob_get_clean();
		}
	}
	
	public function showCaptcha() {
		$this->setNewCaptchaValue();
		
ob_start();
?>
<script type="text/javascript">
<?php
				ob_start();
?>
$(document).ready(function() {
	$('div.wozia-captcha > div.wozia-possibilities > img').draggable({ opacity: 0.6, revert: 'invalid' });
	
	$('div.wozia-captcha > div.wozia-where2go').droppable({
		drop: function(event, ui) {
			if (typeof($('#<?php echo $this->field_name; ?>').val()) != 'undefined') return false;
			var validElement = '<input type="hidden" name="<?php echo $this->field_name; ?>" id="<?php echo $this->field_name; ?>" readonly="readonly" value="<?php echo $this->getCaptchaValue(); ?>" />';
			$('#<?php echo $this->form_id; ?>').append(validElement);
		},
		accept: 'div.wozia-captcha > div.wozia-possibilities > img.wc-<?php echo $this->getCaptchaValue(); ?>'
	});
});
<?php
					$js = ob_get_clean();
					$js = str_replace("\\\r\n", "\\n", $js);
					$js = str_replace("\\\n", "\\n", $js);
					$js = str_replace("\\\r", "\\n", $js);
					$js = str_replace("}\r\n", "};\r\n", $js);
					$js = str_replace("}\n", "};\n", $js);
					$js = str_replace("}\r", "};\r", $js);
					require_once($this->jsPacker);
					$myPacker = new JavaScriptPacker($js, 62, true, false);
					$packed = $myPacker->pack();
					echo($packed);
?>
</script>
<?php
		$this->js = ob_get_clean();
		$limit = count($this->options);
		shuffle(&$this->options);
		
		ob_start();
?>
<div class="wozia-captcha">
	<p><?php _e('Drag the','wcaptcha'); ?> <strong><?php echo $this->getCaptchaText(); ?></strong> <?php _e('to the circle on the side','wcaptcha'); ?>.</p>
	<div class="wozia-possibilities">
<?php
		for ($i=0;$i<$limit;$i++) {
			$name = $this->options[$i];
			$image = $this->options_props[$name][0];
			$text = $this->options_props[$name][1];
?>
		<img src="<?php echo $image; ?>" class="wc-<?php echo $name; ?>" alt="<?php //echo $text; ?>" title="<?php //echo $text; ?>" />
<?php
		}
?>
		<div class="clear"></div>
	</div>
	<div class="wozia-where2go">
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
</div>
<div class="clear"></div>
<?php
		$this->html = ob_get_clean();

		echo $this->css;
		echo $this->js;
		echo $this->html;
	}
	
	public function isValidCaptcha() {
		if (isset($_POST[$this->field_name]) && isset($_SESSION[$this->hash]) && ($_POST[$this->field_name] == $_SESSION[$this->hash])) {
			return true;
		}
		return false;
	}
	
	private function setNewCaptchaValue() {
		$this->answers = Sys::shuffle($this->answers);//-- 1.0.1
		$i = 0;
		switch ($this->type) {
			case 0://-- Horizontal
				$limit = 5;
			break;
			case 1://-- Vertical
				$limit = 4;
			break;
		}
		
		$rnd = rand(0, $limit-1);//-- 1.0.1
		
		foreach ($this->answers as $answer=>$answer_props) {
			if ($i >= $limit) continue;
			$this->options[] = $answer;
			$this->options_props[$answer] = $answer_props;
			if ($i == $rnd) {//-- 1.0.1
				$_SESSION[$this->hash] = $answer;
				$this->value = $answer;
				$this->value_props = $answer_props;
			}
			++$i;
		}
	}
	
	private function getCaptchaValue() {
		return $this->value;
		//return $_SESSION[$this->hash];
	}
	
	private function getCaptchaImage() {
		return $this->value_props[0];
	}
	
	private function getCaptchaText() {
		return $this->value_props[1];
	}
}
?>