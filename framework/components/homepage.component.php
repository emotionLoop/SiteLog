<?php
/**
 * Homepage is the homepage component class.
 *
 * @author Wozia | http://www.wozia.pt
 * @package wFramework
 * @subpackage Component
 * @category Homepage
 * @license GNU GPL v3 | http://www.gnu.org/licenses/gpl.html
 * @version 0.1.0 BETA
 */
class Homepage extends Component {
	
	public static function prefunctions() {//-- Functions to be loaded before processing. Determine action and theme
		App::$theme = 'home';

		if (Usr::logged()) {
			FW::redirect('status/');
		}

		FW::addJS('home.js', array('defer' => 'defer'));
	}
	
	public static function main() {//-- Stuff to load when the component is called, on content part for the template
		$twitter = self::getTwitterFeed();
		$blog = self::getBlogFeed();
?>
	<div id="main">
		<section id="content" class="container">
			<article class="span-12 append-2">
				<h2 class="span-13"><?php _e('Monitor uptime<span> for websites and servers</span>,<br /> the easy way.'); ?></h2>
				<p class="welcome">
					<?php echo FW::url('tour/', __('Take the Tour'), array('class' => 'button first')); ?> 
					<span class="or"><?php _e('or'); ?></span> 
					<?php echo FW::url('plans-and-pricing/', __('Signup'), array('class' => 'button last pink')); ?> 
<?php /*					<span class="or"><?php _e('or'); ?></span> */ ?>
					<?php echo FW::url('http://www.youtube.com/watch?v=QH2-TGUlwu4?autoplay=1&loop=1', __('Nyan Cat!'), array('class'=>'button last nyan-cat')); ?> 
				</p>
				<p class="alignright"><?php _e("SiteLog is the last uptime monitoring tool you'll ever use."); ?><br />
				<?php _e("You'll wonder how you ever lived without it."); ?></p>
			</article>
			<?php echo FW::url('tour/', FW::img('theme/home-imac.png',__("That's how good SiteLog looks!"))); ?>
		</section>
	</div>
	<div id="pre-footer">
		<div class="container">
			<section id="features" class="span-9 push-15">
				<h3><?php _e('Why SiteLog?'); ?></h3>
				<ul>
					<li><span class="span-1"><?php echo FW::img('theme/time-saver.png',__('Easy = Saves Time')); ?></span> <?php _e('Easy = <strong>Saves Time</strong>'); ?></li>
					<li><span class="span-1"><?php echo FW::img('theme/web-based.png',__('Web based = Available Everywhere')); ?></span> <?php _e('Web based = <strong>Available Everywhere</strong>'); ?></li>
					<li><span class="span-1"><?php echo FW::img('theme/simple-setup.png',__('Simple Setup = Install Nothing')); ?></span> <?php _e('Simple Setup = <strong>Install Nothing</strong>'); ?></li>
					<li><span class="span-1"><?php echo FW::img('theme/simple-graphs.png',__('Simple Graphs = Useful')); ?></span> <?php _e('Simple Graphs = <strong>Useful</strong>'); ?></li>
				</ul>
			</section>
			<section id="featured-links" class="span-13 pull-7">
				<div class="span-13 first last">
					<h4><?php _e('Latest on the Blog'); ?></h4>
					<ul>
<?php
		if ($blog) {
			foreach ($blog as $post) {
?>
						<li><?php echo $post->date; ?> // <?php echo FW::url($post->url,$post->text); ?></li>
<?php				
			}
		} else {
?>
						<li><?php _e('Oops... nothing to see here, move along!'); ?></li>
<?php
		}
?>
					</ul>
				</div>
				<div class="span-14">
					<h4><?php _e('Latest on the Internets'); ?></h4>
					<ul>
						<li>web.appstorm.com // <?php echo FW::url('http://web.appstorm.net/reviews/web-dev/monitor-server-uptime-with-sitelog/', __('SiteLog: Monitor Your Sites, Easily'), array('title' => __('SiteLog: Monitor Your Sites, Easily'))); ?></li>
						<li>servage.net // <?php echo FW::url('https://www.servage.net/blog/2011/09/07/monitor-your-website-and-servers-remotely/',__('Monitor your website and servers&hellip;'), array('title' => __('Monitor your website and servers remotely'))); ?></li>
						<li>webdesignerdepot.com // <?php echo FW::url('http://www.webdesignerdepot.com/2011/08/whats-new-for-designers-aug-2011/', __("What's new for designers&hellip;"), array('title' => __("What's new for designers – Aug 2011"))); ?></li>
					</ul>
				</div>
			</section>
		</div>
	</div>
<?php
	}

	public static function getTwitterFeed($limit = 3) {
		$items = MC::get('twitterFeed');

		if (!$items) {
			$rss = @simplexml_load_file(ST::get('feed_twitter'));
			
			if (is_object($rss)) {
				$items = array();
				foreach ($rss->channel->item as $item) {
					if (count($items) == $limit) continue;

					$tmp = new stdClass();
					
					$tmp->text = (string) $item->description;
					$tmp->url = (string) $item->link;
					$tmp->date = date('m.d', strtotime($item->pubDate));

					$tmp->text = str_replace('SiteLogHQ: ', '', $tmp->text);//-- Remove the username
					$tmp->text = Sys::cutText($tmp->text, 25);
					$tmp->text = str_replace('...', '&hellip;', $tmp->text);

					$items[] = $tmp;
				}
				
				MC::set('twitterFeed', $items, 1800);//-- Cache for 30 minutes (60*30), default is 1 hour
			} else {
				$items = MC::get('twitterFeed');
			}
		}

		return $items;
	}

	public static function getBlogFeed($limit = 3) {
		$items = MC::get('blogFeed');

		if (!$items) {
			$rss = @simplexml_load_file(ST::get('feed_blog'));
			
			if (is_object($rss)) {
				$items = array();
				foreach ($rss->channel->item as $item) {
					if (count($items) == $limit) continue;

					$tmp = new stdClass();
					
					$tmp->text = (string) $item->title;
					$tmp->url = (string) $item->link;
					$tmp->date = date('m.d', strtotime($item->pubDate));

					$tmp->text = Sys::cutText($tmp->text, 50);
					$tmp->text = str_replace('...', '&hellip;', $tmp->text);

					$items[] = $tmp;
				}
				
				MC::set('blogFeed', $items);
			} else {
				$items = MC::get('blogFeed');
			}
		}

		return $items;
	}
}
?>