<?php
/**
 * Menu is the menu module class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Module
 * @category Menu
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.1.0 BETA
 */
class Menu extends Module {
	public static $active = false; //-- the footer menu try to use this var
	public static $menu = false; //--the footer menu try to use this var
	
	private static $tbl_menus = 'tbl_menus';
	
	public static function prefunctions() {//-- Functions to be loaded before processing. Determine action and theme
		/** Get menus from table **/
		$where = Usr::logged() ? ' AND `viewable` > 0': ' AND `viewable` < 2' ;
				
		$sql = "SELECT `id`, `title`, `url` FROM `". self::$tbl_menus ."` WHERE `status` = 1". $where ." ORDER BY `position` ASC";
		$menus = DB::execute($sql);
		if ($menus) {
			self::$menu = $menus;
		}
		
		/** Find Menu active item in segments **/
		foreach (ST::$segs as $seg) {
			$sql = "SELECT `id` FROM `". self::$tbl_menus ."` WHERE `sef_name` = '".DB::prepare($seg)."' AND `status` = 1". $where ." LIMIT 1";
			$res = DB::get($sql);
			if ($res) {
				self::$active = $res;
			}
		}
	}
	
	public static function main() {//-- Stuff to load when the component is called, on content part for the template

		if (self::$menu) {
?>
<ul class="nav">
<?php
			foreach (self::$menu as $menu) {
				if (Lng::$code != ST::get('default_lng')) {
					$menu->title = Lng::get($menu->id,'title','tbl_menus');
				}
?><li<?php if ($menu->id == self::$active) { ?> class="active"<?php } ?>><?php echo FW::url($menu->url, $menu->title); ?></li><?php
			}
?>
</ul>
<?php
		}
	}
}
?>
