<?php 
require_once "/home/priddle/db_connections/movierack.php"; 
require_once "movie-functions.php";


$total_movies = mysql_num_rows( mysql_query( "SELECT * FROM mr_movie_details" ) );

$make = make_movies();

$new						= get_new();
$nNew				= count( $new );

$no_imdbid			= get_no_imdbid();
$nNoimdbid			= count( $no_imdbid );

$no_details		 = get_no_details();
$nNodetails 	= count( $no_details );

function ServerStats() {

	$html = <<<EOT
	<div id="server-overview">
		<h2>Server Overview</h2>
		<div>
			<h3>Movie Statistics</h3>
			<p>There are X movies total.</p>
			<p>There <?php echo ( $nNew == 1 ? 'is ' : 'are ' ). "$nNew new ". ( $nNew == 1 ? 'movie' : 'movies' ); ?>.</p>
			<p>There <?php echo ( $nNoimdbid == 1 ? 'is ' : 'are ' ). "$nNoimdbid ". ( $nNoimdbid == 1 ? 'movie' : 'movies' ); ?> without IMDB IDs.</p>
			<p>There <?php echo ( $nNodetails == 1 ? 'is ' : 'are ' ). "$nNodetails ". ( $nNodetails == 1 ? 'movie' : 'movies' ); ?> without details.</p>
		</div>
		<div>
			<h3>Media Server Statistics </h3>
EOT;
	echo $html;
	include "http://media.movierack.net/mr_drive_stats.php";
	$html2 = <<<EOT
		</div>
	</div>
EOT;

	return $html;

}
