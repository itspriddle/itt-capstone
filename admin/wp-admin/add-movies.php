<?php
require_once 'admin.php'; 
$title = __('Movies'); 
require_once 'admin-header.php';
//require_once ABSPATH . WPINC . '/rss-functions.php';

require_once 'movie-functions.php';

require_once '/home/priddle/db_connections/movierack.php';

$today = current_time('mysql', 1);



?>

<?php if ( $_GET['step'] == 2 ) {
	$add_new = add_new();
	echo $add_new['msg'];
} elseif ( $_GET['step'] == 3 ) {

} elseif ( $_GET['step'] == 4 ) {
	$add_imdbids = add_imdbids();
	echo $add_imdbids['msg'];
} elseif ( $_GET['step'] == 5 ) {
	$add_details = retrieve_details();
	echo $add_details['msg'];

}
?>
<div class="wrap">
<h2><?php _e('Manage Movies'); ?></h2>

<?php 
/*
-- for the fade message
if ( isset($_GET['posted']) ) {
<div id="message" class="updated fade"><p><?php printf(__('Entry saved. <!--<a href="%s">View site &raquo;</a>-->'), get_bloginfo('home') . '/'); ?></p></div>
*/


if ( !isset( $_GET['step'] ) || $_GET['step'] == 1 ) {
	echo movieinit(); 
} elseif ( $_GET['step'] == 2 ) {
	//$new = add_new();
	echo $add_new['main'];
} elseif ( $_GET['step'] == 3 ) {
	echo lookup_imdbids( "{$_SERVER['PHP_SELF']}?step=4" );
} elseif ( $_GET['step'] == 4 ) {
	echo $add_imdbids['main'];
} elseif ( $_GET['step'] == 5 ) {
	echo $add_details['main'];
}

?>

</div>

<?php require_once './admin-footer.php'; ?>
