<?php require_once '/home/priddle/movierack-config.php'; ?>
<?php $page_title = 'New Movies'; ?>
<?php include_once INCLDIR .'page-header.php'; ?>
<div id="new">
<h2>New Movies</h2>
<div style="padding: 10px;">
<?php 
$sql = 	"SELECT title, movie_id, imdb_id FROM mr_movie_details AS md ".
		" WHERE DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= md.date_added AND date_updated IS NOT NULL ORDER BY date_added DESC";

$sql = 	"SELECT title, movie_id, imdb_id FROM mr_movie_details AS md ".
		" WHERE date_updated IS NOT NULL ORDER BY date_added DESC LIMIT 20";
$res = mysql_query( $sql );

            $total_res = @mysql_num_rows( $res );
            if ( $total_res > 0 ) {

				$c = 0;
                while( $r = mysql_fetch_array( $res ) ) {
                    $movies[$c]['movie_id'] = $r['movie_id'];
                    $movies[$c]['imdb_id']  = $r['imdb_id'];
                    $movies[$c]['title']	= $r['title'];
					$c++;
                }
				$table_heading = "";
				echo print_movies( $movies );
            }


?>
</div>
</div>
<?php include_once INCLDIR .'page-footer.php'; ?>
