<?php
require_once '/home/priddle/movierack-config.php';
require_once WPADMIN .'wp-config.php';

$action = $_REQUEST['action'];
    $mid = $_GET['m'];

    $sql_old = "SELECT imdb_id, ".
            " date_added, ".
            " filename, ".
            " name AS title, ".
			" mpaa_rating, ".
            " version_name AS version, ".
            " year, ". 
            " runtime, ".
            " genre_name AS genre" .
            " FROM movies AS m, ".
            " movie_details_old AS md, ".
            " genres AS g, ".
            " movie_versions AS mv ".
            " WHERE m.movie_id = md.movie_id ".
            " AND md.genre_id = g.genre_id ".
            " AND m.version_id = mv.version_id ".
            " AND m.have_details = 'Y' ".
            " AND m.movie_id = ". (int) $mid ; 

	$sql = "SELECT filename, date_added, imdb_id, title, version, mpaa_rating, year, runtime, tagline, media_path FROM mr_movie_details WHERE movie_id = ". (int) $mid;

    $res = mysql_query( $sql );
    $total = mysql_num_rows( $res ); 
    if ( $total > 0 ) {
        while ( $r = mysql_fetch_array( $res ) ) {
			$filename 		= $r['filename'];
            $imdb_id 		= $r['imdb_id'];
            $date_added 	= $r['date_added'];
            $title 			= $r['title'];
            $version 		= $r['version'];
			$mpaa_rating 	= $r['mpaa_rating'];
            $year 			= $r['year'];
            $runtime 		= $r['runtime'];
			$tagline		= $r['tagline'];
			$media_path		= $r['media_path'];
        }
        $sql_g = "SELECT genre_name FROM mr_genres g, mr_movie_genres mg WHERE g.genre_id = mg.genre_id AND".
                 " mg.movie_id = ". (int) $mid ." ORDER BY genre_name ";
        //echo $sql_g;
        $res_g = mysql_query( $sql_g );
        while ( $rr= mysql_fetch_array( $res_g ) ) {
            $genres[] = $rr['genre_name'];
        }
        //echo "count: ". count( $genres );
        foreach( $genres as $gs ) {
            $p_genres .= "$gs, ";
        }
        $parsed_genres = substr( $p_genres, 0, ( strlen( $p_genres ) - 2 ) );

		$producerSQL = "SELECT producer_name, producer_role FROM mr_producers AS p, mr_movie_producers AS mp ".
					   " WHERE p.producer_id = mp.producer_id AND mp.movie_id = ". (int) $mid ." ORDER BY mp.producer_id";
		$producerRES = mysql_query( $producerSQL );
		$i = 0;
		while ( $p = mysql_fetch_array( $producerRES ) ) {
			$producers[$i]['name'] = $p['producer_name'];
			$producers[$i]['role'] = $p['producer_role'];
			$i++;
		}

		$writerSQL = "SELECT writer_name FROM mr_writers AS w, mr_movie_writers AS mw ".
					 " WHERE w.writer_id = mw.writer_id AND mw.movie_id = ". (int) $mid ." ORDER BY mw.writer_id";
		$writerRES = mysql_query( $writerSQL );
		while ( $w = mysql_fetch_array( $writerRES ) ) {
			$writers[]['name'] = $w['writer_name'];
		}

		$directorSQL = "SELECT director_name FROM mr_directors AS d, mr_movie_directors AS md ".
                       " WHERE d.director_id = md.director_id AND md.movie_id = ". (int) $mid ." ORDER BY md.director_id";
		$directorRES = mysql_query( $directorSQL );
		while ( $d = mysql_fetch_array( $directorRES ) ) {
			$directors[]['name'] = $d['director_name'];
		}

		$castSQL = "SELECT actor_name, actor_role FROM mr_actors AS a, mr_movie_actors AS ma ".
				   " WHERE a.actor_id = ma.actor_id AND ma.movie_id = ". (int) $mid ." ORDER BY ma.ma_id";
		//echo $castSQL;
		$castRES = mysql_query( $castSQL );
		$i = 0;
		while ( $c = mysql_fetch_array( $castRES ) ) { 
			$cast[$i]['name'] = $c['actor_name'];
			$cast[$i]['role'] = $c['actor_role'];
			$i++;
		}
    } else {
        die();
    }

switch( $action ) {

case 'do_popup':

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>movierack</title>

<link rel="stylesheet" type="text/css" href="/css/movierack-final.css" />

</head>
<body>
<div id="movie-popup">
  <h3><?=$title ?> (<?=$year ?>)</h3>
  <div class="info">
    <p><strong>Version:</strong> <?=$version ?> </p>
    <p><strong>MPAA Rating:</strong> <?=$mpaa_rating ?> </p>
    <p><strong>Runtime:</strong> <?=$runtime ?> minutes </p>
    <p><strong>Genres:</strong> <?=$parsed_genres ?> </p>
	<p><?=$tagline ?></p>
  </div>
  <div class="footer">&nbsp;</div>
</div>
</body>
</html>
<?php
	break;

	case 'movie-details':
	default:
?>
<?php $page_title = "$title Details"; ?>
<?php include_once INCLDIR .'page-header.php'; ?>
<div id="movie-info">
<h2>Movie Details for <?=$title ?></h2>
  <h3><?=$title ?> (<?=$year ?>)</h3>
  <div class="info" style="padding: 10px;">
	<?php if ( !mr_loggedin() ) echo "<p>If you were <a href=\"/index.php\">logged in</a>, you could download this movie.</p>\n"; ?>
	<?php if ( mr_loggedin() && create_download_link( $filename ) != NULL ) echo '<p><a href="'. create_download_link( $filename ) ."\">Download Now</a></p>\n"; ?>
    <p><?=print_movie_cover( $mid ) ?></p>
	<p><?=$tagline ?></p>
    <p><strong>Version</strong><br /> <?=$version ?> </p>
    <p><strong>Runtime</strong><br /> <?=$runtime ?> minutes </p>
    <p><strong>Genres</strong><br /> <?=$parsed_genres ?></p>
    <p><strong>Directors</strong><br /> <?php foreach( $directors as $d ) foreach ( $d as $dn ) $d_html .= "$dn<br />"; echo $d_html; ?></p>
    <p><strong>Producers</strong><br />
	<?php for ( $i = 0; $i < count( $producers ); $i++ ) $p_html .= $producers[ $i ]['name'] . ' ('. $producers[ $i ]['role'] .")<br />"; echo $p_html; ?></p>
    <p><strong>Writers</strong><br /> <?php foreach( $writers as $w ) foreach ( $w as $wn ) $w_html .= "$wn<br />"; echo $w_html; ?></p>
    <p><strong>Cast </strong> <br />
<?php for ( $i = 0; $i < count( $cast ); $i++ ) $c_html .= $cast[ $i ]['name'] . ' as: '. $cast[ $i ]['role'] ."<br />\n"; echo substr( $c_html, 0, -1 ); ?></p>
  </div>
<?php 
?>
</div>

<?php include_once INCLDIR .'page-footer.php'; ?>
<?php
	break;
} // end switch($action) 
?>

