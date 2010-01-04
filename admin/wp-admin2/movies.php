<?php
require_once 'admin.php'; 
$title = __('Movies'); 
require_once 'admin-header.php';
require_once '/home/priddle/db_connections/movierack.php';
require_once ABSPATH . WPINC . '/rss-functions.php';
require_once 'movie-functions.php';


//$today = current_time('mysql', 1);
?>

<?php 
if ( isset( $_GET['mid'] ) || isset( $_POST['movie_id'] ) ) { // edit movie 
	if ( isset( $_POST['submit'] ) ) { // if they clicked submit update the db 

	$sqlUpdate = "UPDATE movie_details ".
				 " SET name = ". format_sql( $_POST['title'] ) .", ".
				 " year = ". format_sql( $_POST['year'] ) .", ". 
				 " runtime = ". format_sql( $_POST['runtime'] ) .", ".
				 " mpaa_rating = ". format_sql( $_POST['mpaa_rating'] ) .", ".
				 " summary = ". format_sql( $_POST['summary'] ) .", ".
				 " date_updated = now() ".
				 " WHERE movie_id = {$_POST['movie_id']}".
				 " LIMIT 1";

	$sqlUpdate2 = "UPDATE movies SET imdb_id = '". format_sql( $_POST['imdb_id'] ) ."'".
				  " WHERE movie_id = {$_POST['movie_id']} LIMIT 1";

	$sqlUpdateG1 = "DELETE FROM movie_genres WHERE movie_id = {$_POST['movie_id']}";
	$sqlUpdateG2 = "INSERT INTO movie_genres (movie_id, genre_id) VALUES ";

	foreach ( $_POST['genres'] as $g )
		$sqlUpdateG2 .= "({$_POST['movie_id']}, $g),";

	$sqlUpdateG2 = substr( $sqlUpdateG2, 0, ( strlen( $sqlUpdateG2 ) - 1 ) );
	//echo $sqlUpdate. "|";
	//echo $sqlUpdateG1. "|";
	//echo $sqlUpdateG2. "|";

	

	$res1 = mysql_query( $sqlUpdate )	 	or die ( "Coudn't update {$_POST['title']}!  MySql error: ". mysql_error() );
	$res2 = mysql_query( $sqlUpdate2 )	 	or die ( "Coudn't update {$_POST['imdb_id']}!  MySql error: ". mysql_error() );
	$res3 = mysql_query( $sqlUpdateG1 ) 	or die ( "Coudn't remove existing genres!  MySql error: ". mysql_error() );
	$res4 = mysql_query( $sqlUpdateG2 ) 	or die ( "Coudn't add genres!  MySql error: ". mysql_error() );

	echo "<div id=\"message\" class=\"updated fade\"><p>". stripslashes( $_POST['title'] ) ." updated!</p></div>\n";
} ?>
<div class="wrap">
<?php
$sqlMovie = "SELECT m.movie_id AS mid, imdb_id, ".
            " filename, ".
            " name AS title, ".
            " version_name AS version, ".
            " year, ".
            " runtime, ".
			" rating, ".
            " summary, ".
            " genre_name AS genre " .
            " FROM movies AS m, ".
			" mpaa_ratings AS mr, ".
            " movie_details AS md, ".
            " genres AS g, ".
            " movie_versions AS mv ".
            " WHERE m.movie_id = md.movie_id ".
            " AND md.genre_id = g.genre_id ".
            " AND m.version_id = mv.version_id ".
            " AND md.mpaa_rating = mr.mpaa_rating_id ".
            " AND m.have_details = 'Y' ".
            " AND m.movie_id = '{$_GET['mid']}' ";

$resMovie = mysql_query( $sqlMovie ) or die("No movie found with that ID.");

while ( $r = mysql_fetch_array( $resMovie ) ) {
	$imdb_id = $r['imdb_id'];
	$filename = $r['filename'];
	$title = $r['title'];
	$version = $r['version'];
	$year = $r['year'];
	$runtime = $r['runtime'];
	$mpaa = $r['rating'];
	$summary = $r['summary'];
}
?>

  <h2>Editing <?=$title ?></h2>
  <form id="movie_details" name="movie_details" method="post" action="">
    <fieldset>
      <legend>Title</legend>
      <input type="text" name="title" id="mtitle" maxlength="200" value="<?=$title ?>" />
    </fieldset>
    <fieldset>
      <legend>IMDB ID</legend>
      <input type="text" name="imdb_id" id="imdb_id" maxlength="200" value="<?=$imdb_id ?>" />
    </fieldset>
<!--
    <fieldset>
      <legend>Filename</legend>
      <input type="text" name="filename" id="filename" disabled="disabled" value="<?=$filename ?>" onclick="javascript:alert('You cannot rename a file.  You will need to rename the remote file, and then use the \'Delete Movie\' button below.  Then it will appear as a new movie.');" />
    </fieldset>
-->
    <fieldset>
      <legend>Year Released</legend>
      <select name="year" id="year">
<?php 
//echo "        <option value=\"$year\">$year</option>\n";
for ( $i = date('Y'); $i >= 1900; $i-- ) 
	echo "        <option value=\"$i\"".( $i == $year ? ' selected="selected"' : '' ).">$i</option>\n"; ?>
      </select>
    </fieldset>
    <fieldset>
      <legend>Runtime (Minutes)</legend>
      <input type="text" name="runtime" id="runtime" maxlength="3" value="<?=$runtime ?>" />
    </fieldset>
    <fieldset>
      <legend>MPAA Rating</legend>
<?=show_update_mpaa( $mpaa ) ?>
    </fieldset>
    <fieldset>
      <legend>Genres</legend>
      <div id="update-genre">
<?=show_update_genres( get_movie_genres( $_GET['mid'] ) ) ?>
      </div>
    </fieldset>
    <fieldset>
      <legend>Summary</legend>
      <textarea name="summary" id="summary" rows="6" cols="50"><?=$summary ?></textarea>
    </fieldset>
    <p class="submit" style="text-align: left">
      <input type="submit" name="submit" value="Update Database" onclick="return validate()" />
      <!--<input type="button" name="delete" id="delete" value="Delete Movie" onclick="javascript:delete()" />-->
      <input type="button" name="goback" id="goback" value="Go Back" onclick="javascript:history.go(-1)" />
      <input type="hidden" name="movie_id" value="<?=$_GET['mid'] ?>" />
    </p>
  </form>
<?php } else { ?>
<div class="wrap">
  <h2>Select Movie</h2>
<?=editmovielist( $_GET['start'], $_GET['display'] ) ?>
<?php } ?>
</div>
<?php require './admin-footer.php'; ?>
