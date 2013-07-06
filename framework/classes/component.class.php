<?php
/**
 * Component is the customized base component class.
 * All component classes for this application should extend from this base class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Component
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.9.0 BETA
 */
abstract class Component {
	/**
	 * Protected string component id.
	 */
	protected static $id = '';
	
	/**
	 * Protected string component table, if used.
	 */
	protected static $table = '';
	
	/**
	 * Protected string component class name.
	 */
	protected static $class = '';
	
	/**
	 * Protected string component name.
	 */
	protected static $name = '';
	
	/**
	 * Protected string component name, in plural.
	 */
	protected static $namep = '';
	
	/**
	 * Protected array component attributes.
	 */
	protected static $attributes = array();
	
	/**
	 * Protected string component action (to be used on ajax, prefunctions or main methods).
	 */
	protected static $action = '';
	
	/**
	 * Public __construct() method. This method basically loads the component info from Application::$cinfo into the Component class
	 * @uses Component::loadInfo()
	 * @uses Component::prefunctions()
	 */
	public function __construct() {
		self::loadInfo();
		$class = get_class($this);
		call_user_func(array($class, 'prefunctions'));
	}
	
	/**
	 * Public ajax() method. To be used when an ajax call is made that requests this component
	 */
	public static function ajax() {}
	
	/**
	 * Public prefunctions() method. To be used before HTML is outputted for this component
	 */
	public static function prefunctions() {}
	
	/**
	 * Public main() method. To be used on HTML output for this component
	 */
	public static function main() {}
	
	/**
	 * Public url method. To be used when creating a url for an item of this component
	 * 
	 * @uses Framework::url()
	 * @param $args String/Array of parameters
	 * @return $return String URL of the item
	 */
	public static function url($args = '') {
		$defaults = array(
			'id' => null,
			'name' => '',
			'action' => null,
			'absolute' => false,
			'raw' => false
		);
		
		$args = Sys::args($args, $defaults);
		extract($args, EXTR_SKIP);
		
		$url = '';
		if ($absolute) $url .= ST::get('url');
		$url .= self::$id.'/';
		if (!is_null($action)) $url .= $action.'/';
		if (!is_null($id)) $url .= $id.'/';
		
		if ($raw) {
			return $url;
		} else {
			return FW::url($url,$name);
		}
	}
	
	/**
	 * Protected loadInfo() method. Loads the component info from Application::$cinfo into the Component class
	 */
	protected static function loadInfo() {
		$cp = App::$cinfo[App::$component];
		
		self::$id = (string) $cp->id;
		self::$table = (string) isset($cp->table) ? $cp->table : '';
		self::$class = (string) $cp->class;
		self::$name = (string) $cp->name;
		self::$namep = (string) isset($cp->namep) ? $cp->namep : self::$name;
		
		/*if (isset($cp->attributes) && isset($cp->attributes->attr)) {
			foreach ($cp->attributes->attr as $attr) {
				$object = new stdClass();
				
				$object->name = (string) $attr->attributes()->name;
				$object->type = (string) $attr->attributes()->type;
				$object->text = (string) $attr[0];
				$object->default = !isset($attr->attributes()->default) ? 0 : (int) $attr->attributes()->default;
				$object->options = !isset($attr->attributes()->options) ? array() : Sys::S2A($attr->attributes()->options,'|');
				$object->required = !isset($attr->attributes()->required) ? false : (bool) (int) $attr->attributes()->required;
				$object->display = !isset($attr->attributes()->display) ? true : (bool) (int) $attr->attributes()->display;
				
				self::$attributes[] = $object;
			}
		}*/
	}
	
	/**
	 * Public get() method. This method is used to load records from the DB, if a Component::$table is set.
	 *
	 * @uses System::args()
	 * @uses Database::prepare()
	 * @uses Database::sexecute()
	 * @uses Database::execute()
	 * 
	 * @param $args String/Array of parameters
	 * @return $return Mixed value of the method/attribute
	 */
	public static function get($args = '') {
		if (!empty(self::$table)) {
			$defaults = array(
				'select' => '*',
				'from' => '`'.self::$table.'`',
				'where' => '`status` = 1',
				'order' => null,
				'limit' => null,
				'id' => null
			);
			
			$args = Sys::args($args, $defaults);
			extract($args, EXTR_SKIP);
			
			$sql = "SELECT ".$select." FROM ".$from;
			if (!empty($where)) $sql .= " WHERE ".$where;
			if (!is_null($id)) {
				if (!empty($where)) $sql .= " AND ";
				else $sql .= " WHERE ";
				$sql .= "`id` = '".DB::prepare($id)."'";
			}
			if (!is_null($order)) $sql .= " ORDER BY ".$order;
			if (!is_null($limit)) $sql .= " LIMIT ".$limit;
			
			if (is_numeric($limit) && $limit == 1) {
				$result = DB::sexecute($sql);
			} else {
				$result = DB::execute($sql);
			}
			
			return $result;
		}
	}
}

?>