<?php require_once '/home/priddle/movierack-config.php'; ?>
<?php 
/**
 * print new movies for the homepage feed.
 */

if ( !isset( $_GET['start'] ) ) $_GET['start'] = 0;

echo new_movie_feed( $_GET['start'], 3 );

?>
