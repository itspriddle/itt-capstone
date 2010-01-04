<?php

if (empty($wp)) {
	require_once('wp-config.php');
	wp('feed=rss2');
}

header('Content-type: text/xml; charset=' . get_settings('blog_charset'), true);
$more = 1;

?>
<?php echo '<?xml version="1.0" encoding="'.get_settings('blog_charset').'"?'.'>'; ?>

<rss version="2.0" 
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
>

<channel>
	<title><?php bloginfo_rss('name'); ?></title>
	<link><?php bloginfo_rss('url') ?></link>
	<description><?php bloginfo_rss("description") ?></description>
	<pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></pubDate>
	<language><?php echo get_option('rss_language'); ?></language>
	<?php do_action('rss2_head'); ?>
	<?php $items_count = 0; if ($posts) { foreach ($posts as $post) { start_wp(); ?>
	<item>
		<title><?php the_title_rss() ?></title>
<!--		<link><?php permalink_single_rss() ?></link>-->
		<comments><?php comments_link(); ?></comments>
		<dc:creator><?php the_author() ?></dc:creator>
		<pubDate><?php echo date('D, d M Y h:i:s a', ( mktime( get_post_time('H', true), get_post_time('i', true), get_post_time('s', true), get_post_time('m', true), get_post_time('d', true), get_post_time('Y', true) ) - ( 60 * 60 * 5 )) );
		?></pubDate>
		<?php the_category_rss() ?>

<!--		<guid isPermaLink="false"><?php the_guid(); ?></guid>-->
<?php if (get_settings('rss_use_excerpt')) : ?>
		<description><![CDATA[<?php the_excerpt_rss() ?>]]></description>
<?php else : ?>
		<description><![CDATA[<?php the_excerpt_rss() ?>]]></description>
	<?php if ( strlen( $post->post_content ) > 0 ) : ?>
		<content:encoded><![CDATA[<?php the_content('', 0, '') ?>]]></content:encoded>
	<?php else : ?>
		<content:encoded><![CDATA[<?php the_excerpt_rss() ?>]]></content:encoded>
	<?php endif; ?>
<?php endif; ?>
		<wfw:commentRss><?php echo comments_rss(); ?></wfw:commentRss>
<?php rss_enclosure(); ?>
	<?php do_action('rss2_item'); ?>
	</item>
	<?php $items_count++; if (($items_count == get_settings('posts_per_rss')) && empty($m)) { break; } } } ?>
</channel>
</rss>
