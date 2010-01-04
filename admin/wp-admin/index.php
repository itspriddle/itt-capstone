<?php
require_once('admin.php'); 
$title = __('Dashboard'); 
require_once('admin-header.php');
require_once (ABSPATH . WPINC . '/rss-functions.php');

$today = current_time('mysql', 1);
?>

<div class="wrap">

<h2><?php _e('Hey there, '.  $user_identity); ?></h2>
<?php include_once "server_stats.php";
/*
<div id="moviectrl">
  <h2>Server Overview</h2>
  <div>
    <h3>Media Server Statistics </h3>
    <!--<p>Current Server: <a href="http://testing1.vtnoc.net/iptv/">testing1.vtnoc.net</a></p>-->
    <?php include "http://admin:kl0wnz@testing1.vtnoc.net/iptv/drive_stats.php"; ?>
  </div>
  <div>
    <h3>Local Statistics</h3>
    <p>These aren't working yet, in case you couldn't tell...</p>
    <p>There are 0 new movies.</p>
    <p>There are 0 movies without IMDB IDs.</p>
    <p>There are 0 movies without details.</p>
    <p>There are 0 movies total.</p>
  </div>
</div>
*/ ?>


<p><?php _e('Use these links to get started:'); ?></p>

<ul>
<li><a href="post.php">Add News or Reviews</a></li>
<li><a href="edit.php">Edit News or Reviews</a></li>
<li><a href="add-movies.php">Add Movies</a></li>
<li><a href="movies.php">Manage Movies</a></li>
</ul>

<?php
if(get_option('dashboard_message') != '' && get_option('dashboard_show_message') == 1)
{
   _e(cjb_dashboard_getmessage());
}
?>

<p><?php
if(get_option('show_dev_feed') == 1)
{
	_e("Below is the latest news from the official WordPress development blog, click on a title to read the full entry. If you need help with WordPress please see our <a href='http://codex.wordpress.org/'>great documentation</a> or if that doesn't help visit the <a href='http://wordpress.org/support/'>support forums</a>.");
}
?></p>
<?php
if( get_option('show_dev_feed') == 1 )
{
   $rss = @fetch_rss('http://wordpress.org/development/feed/');
}
else{ $rss = ''; }
if ( isset($rss->items) && 0 != count($rss->items) ) {
?>
<h3><?php _e('WordPress Development Blog'); ?></h3>
<?php
$rss->items = array_slice($rss->items, 0, 3);
foreach ($rss->items as $item ) {
?>
<h4><a href='<?php echo wp_filter_kses($item['link']); ?>'><?php echo wp_specialchars($item['title']); ?></a> &#8212; <?php printf(__('%s ago'), human_time_diff(strtotime($item['pubdate'], time() ) ) ); ?></h4>
<p><?php echo $item['description']; ?></p>
<?php
	}
}
?>


<?php
if( get_option('show_other_feed') == 1 )
{
   $rss = @fetch_rss('http://planet.wordpress.org/feed/');
}
else{ $rss = ''; }
if ( isset($rss->items) && 0 != count($rss->items) ) {
?>
<div id="planetnews">
<h3><?php _e('Other WordPress News'); ?> <a href="http://planet.wordpress.org/"><?php _e('more'); ?> &raquo;</a></h3>
<ul>
<?php
$rss->items = array_slice($rss->items, 0, 20);
foreach ($rss->items as $item ) {
?>
<li><a href='<?php echo wp_filter_kses($item['link']); ?>'><?php echo wp_specialchars($item['title']); ?></a></li>
<?php
	}
?>
</ul>
</div>
<?php
}
?>
<?php cjb_dashboard_showcustomcontent(); ?>
<div style="clear: both">&nbsp;
<br clear="all" />
</div>
</div>

<?php
require('./admin-footer.php');
?>
