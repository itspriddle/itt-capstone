<?php require_once '/home/priddle/movierack-config.php'; ?>
<?php 
$page_title = 'Browse All Movies';
include_once INCLDIR . 'page-header.php'; 
?>
<div id="browse">
<h2>Browse Movies</h2>
<div style="padding: 10px;">
<?php

if ( isset( $_GET['filter'] ) ) {
	if ( strlen( $_GET['filter'] ) != 1 )
		echo "Filter error!";
	$sql = "SELECT movie_id, imdb_id, title FROM mr_movie_details WHERE date_updated IS NOT NULL AND title LIKE '{$_GET['filter']}%'";
}
echo browse_movies( ( !isset( $_GET['perpage'] ) ? 10 : $_GET['perpage'] ), $_GET['start'], $sql, $_GET['filter'] );

?>
</div>
</div>
<?php include_once INCLDIR . 'page-footer.php'; ?>
