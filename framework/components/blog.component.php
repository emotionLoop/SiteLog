<?php
/**
 * Blog is the blog component class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Component
 * @category Blog
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.1.0 BETA
 */
class Blog extends Component {
	private static $page = 1;
	private static $perPage = 3;
	private static $postID = 0;
	private static $result = false;
	
	public static function prefunctions() {//-- Functions to be loaded before processing. Determine action and theme
		FW::addJS('blog.js', array('defer' => 'defer'));
		//-- Default action
		self::$action = 'list';
		
		FW::set('title',__('Blog').' | '.ST::get('default_title'));
		
		//-- Check if there is an article in the ST:$segs
		foreach (ST::$segs as $seg) {
			if (is_numeric($seg)) {//-- Paging for list
				self::$page = $seg;
			} elseif ($seg == 'rss') {//-- RSS
				self::$action = 'rss';
				self::printRSS();
			} else {//-- Viewing an article
				$res = self::get(array('where' => "`sef_name` = '".DB::prepare($seg)."' AND `status` = 1", 'limit' => 1));
				if ($res) {
					self::$result = $res;
					self::$action = 'view';
					
					if (Lng::$code != ST::get('default_lng')) {
						self::$result->title = Lng::get(self::$result->id,'title','tbl_contents');
						self::$result->seo_description = Lng::get(self::$result->id,'seo_description','tbl_contents');
						self::$result->seo_keywords = Lng::get(self::$result->id,'seo_keywords','tbl_contents');
						self::$result->html = Lng::get(self::$result->id,'html','tbl_contents');
					}
					
					if (!empty(self::$result->title)) {
						FW::set('title',__('Blog').' | '.self::$result->title.' | '.ST::get('default_title'));
						FW::set('seo_title',__('Blog').' | '.self::$result->title.' | '.ST::get('default_title'));
					}
					if (!empty(self::$result->seo_description)) FW::set('seo_description',self::$result->seo_description.' | '.ST::get('default_description'));
					if (!empty(self::$result->seo_keywords)) FW::set('seo_keywords',self::$result->seo_keywords.' | '.ST::get('default_keywords'));
				}
			}
		}
		
	}
	
	public static function main() {//-- Stuff to load when the component is called, on content part for the template
		switch (self::$action) {
			case 'view':
				$item = self::$result;
?>
<article class="span-22 prepend-1 append-1">
	<h1><?php echo $item->title; ?></h1>
	<h2><time><?php echo Sys::date($item->date,'%A, F %e, Y'); ?></time></h2>
	<div class="post-img">
		<?php echo FW::img('blog/'.$item->image,$item->title); ?>
	</div>
	<div class="post-text single-item">
		<?php echo $item->html; ?>
	</div>
	<footer class="span-22 prepend-1 append-1">
		<p class="blog-go-back"><?php echo FW::url(self::$id.'/', __('&laquo; go back'), array('class' => 'green')); ?></p>
	</footer>
</article>
<?php
			break;
			case 'list':
			default:
		//-- Get number of pages
		$sql = "SELECT COUNT(`id`) AS num FROM `".self::$table."` WHERE `status` = 1";
		$tmp = DB::get($sql);
		if (!$tmp) {
			$pages = 1;
		} else {
			$pages = ceil($tmp / self::$perPage);
		}
		
		//-- Get Items
		$args = array(
			'order' => '`date` DESC',
			'limit' => ((self::$page - 1) * self::$perPage).', '.self::$perPage
		);
		
		$items = self::get($args);
		if ($items) {
			foreach ($items as $item) {
				if (Lng::$code != ST::get('default_lng')) {
					$item->title = Lng::get($item->id,'title','tbl_blog');
					$item->summary = Lng::get($item->id,'summary','tbl_blog');
				}
?>
		<article class="span-22 prepend-1 append-1">
			<div class="post-img">
				<?php echo self::url(array('id' => $item->sef_name, 'name' => FW::img('blog/'.$item->image,$item->title))); ?>
			</div>
			<h2><?php echo $item->title; ?></h2>
			<time><?php echo Sys::date($item->date,'Y.m.d'); ?></time>
			<div class="post-text">
				<?php echo $item->summary; ?>
			</div>
			<p class="read-more"><?php echo FW::url(self::url(array('id' => $item->sef_name, 'raw' => true)), __('read more'), array('class' => 'green')); ?></p>
		</article>
<?php
			}
		} else {
?>
		<article>
			<div class="post-text">
				<p><?php _e('There are no posts to show here, move along...'); ?></p>
			</div>
		</article>
<?php
		}
?>
		<ul id="pagination" class="clear span-<?php if ($pages == 1) { ?>1<?php } else { ?>2<?php } ?> prepend-<?php if ($pages == 1) { ?>12<?php } else { ?>11<?php } ?> append-11 last">
<?php
		for ($i=0;$i<$pages;$i++) {
?>
			<li<?php if ($i == (self::$page - 1)) { ?> class="active"<?php } ?> data-url="<?php echo self::$id.'/'.($i + 1).'/'; ?>"><a href="#"><?php echo ($i + 1); ?></a></li>
<?php
		}
?>
		</ul>
</section>
<?php
			break;
		}
	}
	
	private static function printRSS($limit = 10) {
		header('Content-type: application/rss+xml; charset='.Lng::$charset);
		Sys::startCache();
		echo '<'.'?xml version="1.0" ?'.'>'."\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
	<title><?php _e('Blog'); ?> | <?php echo ST::get('default_title'); ?></title>
	<link><?php echo ST::get('url').self::$id.'/'; ?></link>
	<atom:link href="<?php echo ST::get('url').self::$id.'/rss/'; ?>" rel="self" type="application/rss+xml" />
	<description><?php echo ST::get('default_description'); ?></description>
	<language><?php echo Lng::$code; ?></language>
	<image>
		<title><?php _e('Blog'); ?> | <?php echo ST::get('default_title'); ?></title>
		<url><?php echo ST::get('url').'images/theme/logo-rss.png'; ?></url>
		<link><?php echo ST::get('url').self::$id.'/'; ?></link>
	</image>
	<ttl>60</ttl>
	<generator><?php echo ST::get('url'); ?></generator>
<?php
		//-- Get Items
		$args = array(
			'order' => '`date` DESC',
			'limit' => $limit
		);
		
		$items = self::get($args);
		if ($items) {
			foreach ($items as $item) {
				if (Lng::$code != ST::get('default_lng')) {
					$item->title = Lng::get($item->id,'title','tbl_blog');
					$item->sef_name = Lng::get($item->id,'sef_name','tbl_blog');
					$item->image = Lng::get($item->id,'image','tbl_blog');
					//$item->summary = Lng::get($item->id,'summary','tbl_blog');
					$item->html = Lng::get($item->id,'html','tbl_blog');
				}
?>
	<item>
		<title><?php echo $item->title; ?></title>
		<link><?php echo ST::get('url').self::url(array('id' => $item->sef_name, 'raw' => true)); ?></link>
		<pubDate><?php echo Sys::date($item->date,'%a, %e %b Y H:i:s %z'); ?></pubDate>
		<guid isPermaLink="true"><?php echo ST::get('url').self::url(array('id' => $item->sef_name, 'raw' => true)); ?></guid>
		<description>
		<![CDATA[<p style="text-align: center;"><?php echo self::url(array('id' => $item->sef_name, 'name' => FW::img(ST::get('url').'images/blog/'.$item->image,$item->title))); ?></p>
			<?php echo $item->html; ?>]]>
		</description>
	</item>
<?php
			}
		}
?>
</channel>
</rss>
<?php
		$rss = Sys::endCache();
		echo $rss;
		App::stop();
	}
}
?>