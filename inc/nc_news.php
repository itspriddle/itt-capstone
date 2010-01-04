<?php require_once '/home/priddle/movierack-config.php'; ?>
<?php
/**
 * Get news feed for home page
 */

require_once INCLDIR .'magpierss/rss_fetch.inc';
$url = 'http://admin.movierack.net/category/news/feed';
$rss = fetch_rss( $url );
$i = 0;	
foreach ( $rss->items as $item ) {
	if ( $i > 6 ) break; // print the last 6 articles
	if ( isset( $_GET['id'] ) && ( substr( substr( $item['link'], 68 ), 0, ( strlen( substr( $item['link'], 68 ) ) - 1 )  ) ) != $_GET['id'] ) continue;
	$pubdate = explode( ' ', $item['pubdate'] );
	$pubtime = explode( ':', $pubdate[4] );
	
	$thetime = $pubtime[0] .':' . $pubtime[1] .' '. $pubdate[5];
	
	$html = "<div class=\"content\">\n".
		"  <div class=\"head\"></div>\n".
		"  <div class=\"body\">\n".
		"    <p class=\"header\">{$item['title']}</p>\n".
		"    <div class=\"content-main\">\n".
		"      {$item['content']['encoded']}\n".
		"    </div>\n".
		"    <p class=\"footer\">{$pubdate[2]} {$pubdate[1]}, {$pubdate[3]} @ $thetime </p>\n".
		"  </div>\n".
		"  <div class=\"bottom\"></div>\n".
		"</div>\n";
	echo $html;
	$i++;
}

?>
