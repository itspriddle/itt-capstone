<?php
/*
Plugin Name: Feed Control
Plugin URI: http://www.silpstream.com/blog/
Description: Allows you to control the feeds that your site generates. Add pages to feeds, set sort by modified dates and exclude specific posts.
Version: 1.0
Author: Christopher Hwang
Author URI: http://www.silpstream.com

Feed Control - Allows you to control the feeds that your site generates.
Copyright (C) 2006 Christopher Hwang (email: chris@silpstream.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

add_action('activate_feedcontrol/feedcontrol.php','silpstream_feedcontrol_activate');
add_action('deactivate_feedcontrol/feedcontrol.php','silpstream_feedcontrol_deactivate');

function silpstream_feedcontrol_activate() {
	$options = array(
									'incposts' => '1',
									'incpages' => '1',
									'modpostdate' => '0',
									'sortbymoddate' => '1',
									'excludeposts' => ''
									);

	update_option("feedcontrol_options", $options);
}

function silpstream_feedcontrol_deactivate() {
	delete_option("feedcontrol");
}

add_filter('posts_request', 'silpstream_feedcontrol_modmainfeedrequest');

function silpstream_feedcontrol_modmainfeedrequest($request) {
	global $wpdb;
	$feedcontrolopt = get_option('feedcontrol_options');

	if ( !( is_attachment() || is_archive() || is_single() || is_page() || is_search() || is_home() || is_trackback() || is_404() || is_admin() || is_comments_popup() ) && is_feed() ) {
		$now = gmdate('Y-m-d H:i:59');

		$limit = get_option('posts_per_rss');
		unset($where);
		unset($orderby);

		if ( $feedcontrolopt['incposts'] && $feedcontrolopt['incpages'] ) {
			$where .= " AND (post_status = 'publish' OR post_status = 'static')";
		} elseif ( $feedcontrolopt['incposts'] ) {
			$where .= " AND post_status = 'publish'";
		} elseif ( $feedcontrolopt['incpages'] ) {
			$where .= " AND post_status = 'static'";
		}

		if ( $feedcontrolopt['sortbymoddate'] ) {
			$where .= " AND post_modified_gmt <= '" . $now . "'";
			$orderby .= "post_modified_gmt";
		} else {
			$where .= " AND post_date_gmt <= '" . $now . "'";
			$orderby .= "post_date_gmt";
		}

		$excludeposts = $feedcontrolopt['excludeposts'];
		if ( !empty($excludeposts) ) {
			$exposts = preg_split('/[\s,]+/',$excludeposts);
			if ( count($exposts) ) {
				foreach ( $exposts as $expost ) {
					$where .= " AND ID <> " . intval($expost);
				}
			}
		}

		$request = " SELECT * FROM $wpdb->posts WHERE 1=1" . $where . " ORDER BY " . $orderby . " DESC LIMIT 0, " . $limit;
	}
	return $request;
}

add_filter('the_posts', 'silpstream_feedcontrol_modpostdate');

function silpstream_feedcontrol_modpostdate($posts) {
	$feedcontrolopt = get_option('feedcontrol_options');

	if ( $posts && $feedcontrolopt['modpostdate'] && !( is_attachment() || is_archive() || is_single() || is_page() || is_search() || is_home() || is_trackback() || is_404() || is_admin() || is_comments_popup() ) && is_feed() ) {
		$arrsize = count($posts);
		for( $i = 0; $i < $arrsize; $i++ ) {
			$posts[$i]->post_date = $posts[$i]->post_modified;
			$posts[$i]->post_date_gmt = $posts[$i]->post_modified_gmt;
		}
	}
	return $posts;
}

add_action('admin_menu', 'silpstream_feedcontrol_add_option_page');

function silpstream_feedcontrol_add_option_page() {
	if ( function_exists('add_options_page') ) {
		 add_options_page('Feed Control Settings', 'Feed Control', 8, __FILE__, 'silpstream_feedcontrol_option_page');
	}
}

function silpstream_feedcontrol_option_page() {
	if ( isset($_POST['submit']) ) {

		( !isset($_POST['incposts']) ) ? $incposts = "0" : $incposts = $_POST['incposts'] ;
		( !isset($_POST['incpages']) ) ? $incpages = "0" : $incpages = $_POST['incpages'] ;
		( !isset($_POST['modpostdate']) ) ? $modpostdate = "0" : $modpostdate = $_POST['modpostdate'] ;
		( !isset($_POST['sortbymoddate']) ) ? $sortbymoddate = "0" : $sortbymoddate = $_POST['sortbymoddate'] ;
		( !isset($_POST['excludeposts']) ) ? $excludeposts = "" : $excludeposts = $_POST['excludeposts'] ;

		$options = array(
										'incposts' => $incposts,
										'incpages' => $incpages,
										'modpostdate' => $modpostdate,
										'sortbymoddate' => $sortbymoddate,
										'excludeposts' => $excludeposts
										);

		update_option('feedcontrol_options', $options);
		echo "<div id=\"message\" class=\"updated fade\"><p>";
		echo "<font color=\"red\">Feed Control settings updated...</font><br />";
		echo "</p></div>";
	}

	$feedcontrolopt = get_option('feedcontrol_options');
?>
<form method="post">
<div class='wrap'>
	<h2 id="edit-settings">Feed Control Settings <small class="quickjump"><a href="#preview-rss">RSS2 Preview &darr;</a></small></h2>
	<p><label><input type="checkbox" name="incposts" value="1" <?php if ($feedcontrolopt['incposts']){ echo "checked";} ?> /> Include posts in main feed</label></p>
	<p><label><input type="checkbox" name="incpages" value="1" <?php if ($feedcontrolopt['incpages']){ echo "checked";} ?> /> Include pages in main feed</label></p>
	<p><label><input type="checkbox" name="sortbymoddate" value="1" <?php if ($feedcontrolopt['sortbymoddate']){ echo "checked";} ?> /> Sort by modified date</label></p>
<!--
	<p><label><input type="checkbox" name="modpostdate" value="1" <?php if ($feedcontrolopt['modpostdate']){ echo "checked";} ?> /> Use modified date instead of post date in feed</label></p>
-->
	<p><input type="text" name="excludeposts" value="<?php echo $feedcontrolopt['excludeposts']; ?>" size="15" /> Exclude these posts/pages from feed</p>
</div>
<div class="wrap">
	<p align="center"><input type="submit" name="submit" value="Update Feed Control Settings" /></p>
</div>
</form>
<div class="wrap">
	<h2>Help</h2>
	<p>WordPress sorts by post date by default, clicking on 'Sort by modified date' changes this.</p>
<!--
	<p>Using the modified date instead of the post date in the feed will affect the published dated in the feed only (not your database).</p>
-->
	<p>You can use 'exclude' to hide the posts or pages you don't want in the feed. The format for this is 'ID1,ID2,ID3', where the ID is based on the ID you see when you manage your posts/pages.</p>
	<p>Feed Control only affects the main feed of your site (i.e., http://www.silpstream.com/blog/feed/) not the other single page or category feeds.</p>
	<p>Check for updates at <a href="http://www.silpstream.com/blog/" target="_blank">www.silpstream.com</a>.</p>
</div>
<div id='preview' class='wrap'>
	<h2 id="preview-rss">RSS2 Preview <small class="quickjump"><a href="#edit-settings">edit settings &uarr;</a></small></h2>
	<iframe src="<?php echo add_query_arg('preview', 'true', get_feed_link()); ?>" width="100%" height="400" ></iframe>
</div>
<div class='wrap'>
	<p>
	If you found Feed Control useful, do consider making a <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=chris%40silpstream%2ecom&item_name=Feed%20Control%20v1%2e0&no_shipping=2&no_note=1&tax=0&currency_code=CAD&bn=PP%2dDonationsBF&charset=UTF%2d8" target="_blank">donation</a> to support it's development. Thank you!<br />
	Remember to check <a href="http://www.silpstream.com/" target="_blank">silpstream</a> for updates and new plugins.
	</p>
</div>
<?php
}

add_action('wp_head', 'silpstream_feedcontrol_add2head');

function silpstream_feedcontrol_add2head() {
	$cd = "";
	echo $cd;
}
?>