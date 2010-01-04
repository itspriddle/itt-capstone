<?php
/*
Plugin Name: Dashboard Options
Version: 1.4.1
Plugin URI: http://cjbehm.dyndns.org/wingingit/dashboard-options/
Author: Chris Behm
Author URI: http://cjbehm.dyndns.org/wingingit/
Description: Enables or disables the 3 default Dashboard RSS Feeds, allows custom messages or custom PHP to be executed in the Dashboard and enables the addition of custom RSS feeds in the Dashboard.
*/
$dashboard_options_default_rssitemcount = 10;

if(function_exists(is_plugin_page) && is_plugin_page())
{
	global $user_level;
	
	$dashboard_userlevel_guard = get_option('dashboard_message_ul');
	
	if(get_bloginfo('version') >= 2)
	{
		$dashboard_options_string = "show_dev_feed, show_other_feed, show_technorati_feed";
		if($user_level >= $dashboard_userlevel_guard)
		{
			$dashboard_options_string .= ", dashboard_message_ul, dashboard_show_message, dashboard_message, dashboard_eval_message_as_php, dashboard_custom_feeds";
		}
	}
	else
	{
		$dashboard_options_string = "'show_dev_feed', 'show_other_feed', 'show_technorati_feed'";
		if($user_level >= $dashboard_userlevel_guard)
		{
			$dashboard_options_string .= ", 'dashboard_message_ul', 'dashboard_show_message', 'dashboard_message', 'dashboard_eval_message_as_php', 'dashboard_custom_feeds'";
		}
	}
	?>
	<div class="wrap">
	<h2><?php _e('Dashboard Options') ?></h2>
	Version 1.4.2 <span style="font-size:85%;">[ <a href="#custom_message">Custom Message Options</a> | <a href="#custom_rss">Custom RSS Options</a> ]</a></span>
	<form name="dashoptions" method="post" action="options.php">
		<?php
		if( function_exists('wp_nonce_field') ) {
			wp_nonce_field('update-options');
		}
		?>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="<?php echo $dashboard_options_string ?>" />
		
		<fieldset class="options">
		<legend>RSS Feeds</legend>
		<input name="show_dev_feed" type="checkbox" id="show_dev_feed" value="1" <?php checked('1', get_settings('show_dev_feed')); ?> />
		<label for="show_dev_feed"><?php _e('Load WordPress Development Feed?') ?></label><br />
		
		<p><input name="show_other_feed" type="checkbox" id="show_other_feed" value="1" <?php checked('1', get_settings('show_other_feed')); ?> />
		<label for="show_other_feed"><?php _e('Load WordPress Other News Feed?') ?></label></p>
		
		<input name="show_technorati_feed" type="checkbox" id="show_technorati_feed" value="1" <?php checked('1', get_settings('show_technorati_feed')); ?> />
		<label for="show_technorati_feed"><?php _e('Load Technorati Information Feed?') ?></label>
		
		</fieldset>
		
		<fieldset class="options">
		<legend>Custom Message Options</legend>
		
		
		<?php
		//user level check fix by Lilyan
		if($user_level >= $dashboard_userlevel_guard)
		{
			?>
			<label for="dashboard_message_ul">User Level to Change Message</label> <select name="dashboard_message_ul" id="dashboard_message_ul">
			<?php
			for($i = 1; $i <= 10; $i++)
			{
				$optItem = '<option value="'.$i.'"';
				
				if($i == $dashboard_userlevel_guard)
				{
					$optItem .= ' selected="selected"';
				}
				
				$optItem .= '>'.$i."</option>\n";
				
				_e($optItem);
			}
			?>
			</select><br />
			
			<p><input name="dashboard_show_message" type="checkbox" id="dashboard_show_message" value="1" <?php checked('1', get_settings('dashboard_show_message')); ?> /> <label for="dashboard_show_message">Show Custom Message</label></p>
			
			<p><input name="dashboard_eval_message_as_php" type="checkbox" id="dashboard_eval_message_as_php" value="1" <?php checked('1', get_settings('dashboard_eval_message_as_php')); ?> /> <label for="dashboard_eval_message_as_php">Evaluate Message Field as PHP</label></p>
			
			<label for="dashboard_message">Custom Message</label><br />
			<textarea name="dashboard_message" rows="10" cols="80" id="dashboard_message"><?php _e(get_option('dashboard_message')); ?></textarea>
		</fieldset>
		
		<a name="custom_rss"></a>
		<fieldset class="options">
		<legend>Custom RSS Options</legend>
			
			<label for="dashboard_custom_feeds">Custom Feeds</label><br />
			<textarea name="dashboard_custom_feeds" rows="10" cols="80" id="dashboard_custom_feeds"><?php _e(get_option('dashboard_custom_feeds')); ?></textarea>
			<div class="updated" style="margin:5px;margin-left:0;font-size:85%;">
			<strong>RSS Feed URL`Title`Link`stylesheet class`style`id`max items</strong><br />
			Separate RSS feeds with a return and use the ` (backtick) character to separate fields and everything but the RSS Feed URL can be empty. For example, the following will reproduce the &quot;Other News&quot; RSS feed
			
			<p>http://planet.wordpress.org/feed/`Other News`http://planet.wordpress.org/```planetnews</p>
			
			I realize that the syntax is a little cumbersome, but it's the easiest way to deal with an unlimited number of additional RSS feeds, each with a bunch of optional information. <em>Please note, the default number of items to download is 10.</em>
			</div>
			
			<?php
		}
		else
		{
			_e('<label for="dashboard_message">Custom Message</label><br />');
			_e(get_option('dashboard_message'));
		}
		?>
		</fieldset>
		
		<p class="submit">
			<input type="submit" name="Submit" value="<?php _e('Update Options') ?> &raquo;" />
		</p>
	</form>
	</div>
	<?php
}
else
{
	//options added with initial version
	//add_option (wp 1.5) will add the options only if they don't already exist
	add_option('show_other_feed', 1, 'Used by the Dashboard Options plugin to control whether the WordPress Development feed is loaded');
	add_option('show_dev_feed', 1, 'Used by the Dashboard Options plugin to control whether the WordPress Other News feed is loaded');
	
	//dashboard_message and dashboard_message_ul added 2/20/2005
	add_option('dashboard_message', 'Test', 'Used by the Dashboard Options plugin to display custom text on the Dasbhoard. Modification via WP admin page is guarded by user-level checks.');
	add_option('dashboard_message_ul', 1, 'Used to guard access to changes.');
	add_option('dashboard_show_message', 0, 'Used to toggle the display of the message');
	
	//show_technorati_feed added 2/20/2005
	add_option('show_technorati_feed', 1, 'Used by the Dashboard Options plugin to control whether the Technorati feed is loaded');
	
	//evaluate message text as PHP 2/21/2005
	add_option('dashboard_eval_message_as_php', 0, 'Treat the text in the dashboard custom message as PHP instead of HTML and evaluate it -- can be used to put custom dynamic content into the dashboard.');
	
	//custom RSS feed option added 3/2/2005
	add_option('dashboard_custom_feeds','','Newline (\n) delimited list of RSS feeds to load');
	
	function cjb_dashboard_menus($unused)
	{
		//code to get the path to the plugin inspired by Charlie DeTar & Codex examples
		add_options_page('Dashboard Options', 'Dashboard Options', 5, __FILE__);
		
		return $unused;
	}
	
	function cjb_dashboard_renderfeed($feed, $title = '', $link = '', $divclass = '', $divstyle = '', $divid = '', $feedlimit = -1)
	{
		global $dashboard_options_default_rssitemcount;
		
		$rss = @fetch_rss($feed);
		$output = "";
		
		if($feedlimit < 0)
		{
			$feedlimit = $dashboard_options_default_rssitemcount;
		}
		
		if(isset($rss->items) && 0 != count($rss->items))
		{
			$output = "<div id=\"$divid\" class=\"$divclass\" style=\"$divstyle\">\n";
			if($title != '')
			{
				$output .= "<h3>".$title;
				if($link != '')
				{
					$output .= " <cite><a href=\"".$link."\">More &raquo;</a></cite>";
				}
				
				$output .= "</h3>\n";
			}
			$output .= "<ul>\n";
			
			$rss->items = array_slice($rss->items,0,$feedlimit);
			foreach( $rss->items as $item)
			{
				$output .= "<li><a href=\"".wp_filter_kses($item['link'])."\">".wp_specialchars($item['title'])."</a></li>\n";
				
			}
			
			$output .= "</ul>\n";
			$output .= "</div>\n";
		}
		
		return $output;
	}
	
	function cjb_dashboard_showcustomcontent()
	{
		$customFeeds = split("\n",get_option('dashboard_custom_feeds'));
		foreach($customFeeds as $customFeed)
		{
			list($feed, $title, $link, $class, $style, $id) = split('`',$customFeed);
			if($feed != '')
			{
				echo cjb_dashboard_renderfeed($feed, $title, $link, $class, $style, $id);
			}
		}
	}
	
	function cjb_dashboard_getmessage()
	{
		$messageContent = '';
		if(get_option('dashboard_eval_message_as_php') == 1)
		{
			$messageContent = eval(get_option('dashboard_message'));
		}
		else
		{
			$messageContent = '<p>'.get_option('dashboard_message').'</p>';
		}
		
		return $messageContent;
	}
}

add_action('admin_menu', 'cjb_dashboard_menus');
?>