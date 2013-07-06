<?php
/**
 * Content is the content component class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Component
 * @category Content
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.1.0 BETA
 */
class Content extends Component {
	private static $result = false;
	
	public static function prefunctions() {//-- Functions to be loaded before processing. Determine action and theme
		/** Find Content in segments **/
		foreach (ST::$segs as $seg) {
			$res = self::get(array('where' => "`sef_name` = '".DB::prepare($seg)."' AND `status` = 1", 'limit' => 1));
			if ($res) {
				self::$result = $res;
			}
		}
		
		if (!self::$result) {
			FW::redirect('');
		}
		
		if (Lng::$code != ST::get('default_lng')) {
			self::$result->title = Lng::get(self::$result->id, 'title', 'tbl_contents');
			self::$result->seo_description = Lng::get(self::$result->id, 'seo_description', 'tbl_contents');
			self::$result->seo_keywords = Lng::get(self::$result->id, 'seo_keywords', 'tbl_contents');
			self::$result->html = Lng::get(self::$result->id, 'html', 'tbl_contents');
		}
		
		if (!empty(self::$result->title)) FW::set('title', self::$result->title.' | '.ST::get('default_title'));
		if (!empty(self::$result->title)) FW::set('seo_title', self::$result->title.' | '.ST::get('default_title'));
		if (!empty(self::$result->seo_description)) FW::set('seo_description', self::$result->seo_description.' | '.ST::get('default_description'));
		if (!empty(self::$result->seo_keywords)) FW::set('seo_keywords', self::$result->seo_keywords.' | '.ST::get('default_keywords'));
	}
	
	public static function main() {//-- Stuff to load when the component is called, on content part for the template
?>
<article class="span-22 prepend-1 append-1" id="content-<?php echo self::$result->sef_name; ?>">
	<?php echo self::$result->html; ?>
</article>
<?php
	}
}
?>
