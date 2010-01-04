<?php header('Content-type: text/xml; charset=UTF-8', true); ?>
<?php require_once '/home/priddle/movierack-config.php'; ?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<rss version="2.0" 
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
>

<channel>
	<title>Movierack &#x000BB; New Movies</title>
	<link>http://movierack.net</link>
	<description>New movies over the last 30 days</description>
	<pubDate><?php echo date('D, d M Y H:i:s +0000'); ?></pubDate>
	<language>en</language>
<?php
$sqlNEW = "SELECT * FROM mr_movie_details AS md ".
          " WHERE DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= md.date_added AND date_updated IS NOT NULL ORDER BY md.date_added DESC";
$resNEW = mysql_query( $sqlNEW );

while ( $r = mysql_fetch_array( $resNEW ) ) : ?>
	<item>
		<title><?php echo $r['title'] .' ('. $r['year'] .')'; ?></title>
		<pubDate><?php echo $r['date_added']; ?></pubDate>
		<description><![CDATA[
		<p><strong>Rated:</strong> <?=( $r['mpaa_rating'] != NULL ? $r['mpaa_rating'] : 'No Rating' ) ?><br />
		   <strong>Version:</strong> <?=$r['version'] ?> <br />
		   <strong>Runtime:</strong> <?=$r['runtime'] ?> minutes</p>
		<p><?=$r['tagline'] ?></p>
		]]></description>
		<link>http://movierack.net/movies/<?=$r['movie_id'] ?> </link>
	</item>
<?php endwhile; ?>


</channel>
</rss>
