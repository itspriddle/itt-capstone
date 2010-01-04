<?php
// This array constructs the admin menu bar.
//
// Menu item name
// The minimum level the user needs to access the item: between 0 and 10
// The URL of the item's file
$menu[0] = array(__('Home'), 'read', 'index.php');
$menu[2] = array(__('Movies'), 'edit_posts', 'add-movies.php');
$menu[5] = array(__('Add Content'), 'edit_posts', 'post.php');
$menu[10] = array(__('Manage Content'), 'edit_posts', 'edit.php');
//$menu[20] = array(__('Links'), 'manage_links', 'link-manager.php');
//$menu[25] = array(__('Presentation'), 'switch_themes', 'themes.php');
if ( $user_login == 'admin' )
	$menu[30] = array(__('Plugins'), 'activate_plugins', 'plugins.php');
if ( current_user_can('edit_users') )
	if ( $user_login == 'admin' )
		$menu[35] = array(__('Users'), 'read', 'profile.php');
	else
		$menu[35] = array(__('Options'), 'read', 'profile.php');
else
	$menu[35] = array(__('Profile'), 'read', 'profile.php');

if ( $user_login == 'admin' )
	$menu[40] = array(__('Options'), 'manage_options', 'options-general.php');
//$menu[40] = array(__('Options'), 0, 'options-general.php');
//$menu[45] = array(__('Import'), 'import', 'import.php');


$submenu['add-movies.php'][5] = array(__('Add Movies'), 'edit_posts', 'add-movies.php');
$submenu['add-movies.php'][10] = array(__('Edit Movies'), 'edit_posts', 'movies.php');

//$submenu['post.php'][5] = array(__('Write Post'), 'edit_posts', 'post.php');
//$submenu['post.php'][10] = array(__('Write Page'), 'edit_pages', 'page-new.php');
//$submenu['post.php'][10] = array(__('Add Page'), 'edit_pages', 'page-new.php');
$submenu['post.php'][5] = array(__('Add News'), 'edit_posts', 'post.php');
$submenu['post.php'][50] = array(__('Add Review'), 'edit_posts', 'reviews.php');
//$submenu['post.php'][55] = array(__('Edit Content'), 'edit_posts', 'edit.php');

if ( $user_login == 'admin' ) {
	$submenu['edit.php'][5] = array(__('Edit Content'), 'edit_posts', 'edit.php');
	$submenu['edit.php'][10] = array(__('Pages'), 'manage-options', 'edit-pages.php');
}
if ( $user_login == 'admin' )
	$submenu['edit.php'][15] = array(__('Categories'), 'manage_options', 'categories.php');
//$submenu['edit.php'][20] = array(__('Comments'), 'edit_posts', 'edit-comments.php');
//$awaiting_mod = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = '0'");
//$submenu['edit.php'][25] = array(sprintf(__("Awaiting Moderation (%s)"), $awaiting_mod), 'edit_posts', 'moderation.php');
//$submenu['edit.php'][30] = array(__('Files'), 'edit_files', 'templates.php');

$submenu['link-manager.php'][5] = array(__('Manage Links'), 'manage_links', 'link-manager.php');
$submenu['link-manager.php'][10] = array(__('Add Link'), 'manage_links', 'link-add.php');
//$submenu['link-manager.php'][15] = array(__('Link Categories'), 'manage_links', 'link-categories.php');
//$submenu['link-manager.php'][20] = array(__('Import Links'), 'manage_links', 'link-import.php');

if ( $user_login == 'admin' ) {
	$submenu['profile.php'][5] = array(__('Your Profile'), 'read', 'profile.php');
	$submenu['profile.php'][10] = array(__('Authors &amp; Users'), 'edit_users', 'users.php');
}
$submenu['options-general.php'][10] = array(__('General'), 'manage_options', 'options-general.php');
$submenu['options-general.php'][15] = array(__('Writing'), 'manage_options', 'options-writing.php');
$submenu['options-general.php'][20] = array(__('Reading'), 'manage_options', 'options-reading.php');
$submenu['options-general.php'][25] = array(__('Discussion'), 'manage_options', 'options-discussion.php');
$submenu['options-general.php'][30] = array(__('Permalinks'), 'manage_options', 'options-permalink.php');
$submenu['options-general.php'][35] = array(__('Miscellaneous'), 'manage_options', 'options-misc.php');

$submenu['plugins.php'][5] = array(__('Plugins'), 'activate_plugins', 'plugins.php');
$submenu['plugins.php'][10] = array(__('Plugin Editor'), 'edit_plugins', 'plugin-editor.php');

$submenu['themes.php'][5] = array(__('Themes'), 'switch_themes', 'themes.php');
$submenu['themes.php'][10] = array(__('Theme Editor'), 'edit_themes', 'theme-editor.php');

// Create list of page plugin hook names.
foreach ($menu as $menu_page) {
	$admin_page_hooks[$menu_page[2]] = sanitize_title($menu_page[0]);
}

do_action('admin_menu', '');
ksort($menu); // make it all pretty

if (! user_can_access_admin_page()) {
	die( __('You do not have sufficient permissions to access this page.') );
}

?>
