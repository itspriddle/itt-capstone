<?php header('Content-type: text/xml; charset=UTF-8', true); ?>
<?php require_once '/home/priddle/movierack-config.php'; ?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<rss version="0.92">
<channel>
	<title>Movierack &#x000BB; New Movies</title>
	<link>http://movierack.net</link>
	<description>New movies over the last 30 days</description>
	<language>en</language>
<?php
$sqlNEW = "SELECT * FROM mr_movie_details AS md, mr_movie_ratings AS mr ".
	      " WHERE DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= md.date_added AND md.date_updated IS NOT NULL ".
		  " AND mr.movie_id = md.movie_id ORDER BY md.date_added DESC";
$resNEW = mysql_query( $sqlNEW );

while ( $r = mysql_fetch_array( $resNEW ) ) {
?>
	<item>
		<title><?php echo $r['title'] .' ('. $r['year'] .')'; ?></title>
		<description><![CDATA[ 
		<p><strong>Average Rating:</strong> <?=( ( $r['total_votes'] != 0 ) ? number_format( ( $r['total_value'] / $r['total_votes'] ), 1 ). " ({$r['total_votes']} vote".( $r['total_votes'] > 1 ? 's' : '' ) .")" : 'Not Rated' ) ?><br />
		   <strong>MPAA Rating:</strong> <?=( $r['mpaa_rating'] != NULL ? $r['mpaa_rating'] : 'Not Rated' ) ?><br />
		   <strong>Version:</strong> <?=$r['version'] ?> <br />
		   <strong>Runtime:</strong> <?=$r['runtime'] ?> minutes</p>
		<p><?=$r['tagline'] ?></p>
		]]></description>
		<link>http://movierack.net/movies/<?=$r['movie_id'] ?> </link>
	</item>
<?php } ?>
</channel>
</rss>
