<?php
/*
Plugin Name: Page Category Organiser
Plugin URI: http://www.yellowswordfish.com/index.php?pagename=page-category-organiser-wordpress-plugin
Description: Organise Pages with categories etc. 
Version: 2.0
Author: Andy Staines
Author URI: http://www.yellowswordfish.com
*/

/*  Copyright 2005/2006  Andy Staines  (email: andy@yellowswordfish.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    For a copy of the GNU General Public License, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

include_once('pageorgfunctions.php');

add_action('admin_menu', 'pageorg_menu');
add_action('edit_page_form', 'porg_addcat');
add_action('save_post', 'porg_updatepagecat');
add_action('delete_post', 'porg_deletepagecat');
// aws: 1.2: New template function
add_filter('wp_head', 'porg_page_head_filter');

//=====| CREATE NEW TABLES |========================================================================================

function pageorg_create_tables()
{
	global $wpdb, $table_prefix;
		
	$wpdb->query("CREATE TABLE IF NOT EXISTS `" . $table_prefix . "page_cats` (`pagecat_ID` bigint(20) NOT NULL auto_increment, `pagecat_name` varchar(55) NOT NULL default '', PRIMARY KEY  (`pagecat_ID`))");
	$wpdb->query("CREATE TABLE IF NOT EXISTS `" . $table_prefix . "page2cat` (`rel_id` bigint(20) NOT NULL auto_increment, `page_id` bigint(20) NOT NULL default '0', `pagecat_id` bigint(20) NOT NULL default '0', PRIMARY KEY (`rel_id`))");

	$wpdb->query('INSERT INTO '. $table_prefix . 'page_cats (pagecat_id, pagecat_name) VALUES (1, "Uncategorised");');

	$pages = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_status = 'static'");
	if ($pages) 
	{
		foreach ($pages as $post) 
		{
			$wpdb->query('INSERT INTO ' . $table_prefix . 'page2cat (page_id, pagecat_id) VALUES (' . $post->ID . ', 1);');
		}
	}

	update_option('porg_version', 10);
	return;
}

//=====| CREATE ADMIN MENUS |=======================================================================================

if(!function_exists('pageorg_menu')) 
{	
	function pageorg_menu() 
	{		
		add_submenu_page('edit.php', 'Page-Organiser', 'Page Organiser', 9, 'page-organiser/pageorgadmin.php');
	}
}

//=====| ADD PAGE CAT TO PAGE ENTRY |===============================================================================

function porg_addcat()
{
	global $wpdb, $table_prefix, $post;
?>

	<fieldset id="pagecat" class="dbx-box">
		<h3 class="dbx-handle"><?php _e('Page Category') ?></h3> 
		<div class="dbx-content">
		<br />
		<label for="catselect">&nbsp;Page Category:</label>
		<select name="catselect">	
<?php
			if(get_option('porg_usepagecats'))
			{
				$sql = "SELECT pagecat_id AS catid, pagecat_name AS catname FROM " . $table_prefix . "page_cats ORDER BY pagecat_id";
			} else {
				$sql = "SELECT cat_ID AS catid, cat_name AS catname FROM " . $table_prefix . "categories ORDER BY cat_ID";
			}
			$catlist = $wpdb->get_results($sql);
		
			foreach ($catlist as $cat) 
			{
				if((porg_get_page_cat($post->ID)) == ($cat->catid))
				{
					$default = 'selected="selected"';
				} else {
					$default = null;
				}
 
				echo "<option $default value=\"" . $cat->catid . '">';
				echo $cat->catname;
				echo "</option>\n";
			}

?>
			</select>		
			
		</div>
	</fieldset>
	<br />
<?php
}

//=====| SAVE PAGE CAT AFTER PAGE EDIT |============================================================================

function porg_updatepagecat($pageid)
{
	if(isset($_POST['catselect']))
	{
		porg_save_pagecat($pageid, $_POST['catselect']);
	}
	return;
}

//=====| DELETE PAGE CAT AFTER PAGE DELETE |========================================================================

function porg_deletepagecat($pageid)
{
	global $table_prefix, $wpdb;

	// if using usual cats then normal delete will take care of it - else delete anyway even if post as it wont hurt
	if(get_option('porg_usepagecats'))
	{
		$sql = 'DELETE FROM ' . $table_prefix . 'page2cat WHERE page_id=' . $pageid . ';';
		$wpdb->query($sql);
		return;
	}

}

//=====| TEMPLATE FUNCTIONS TO RETURN CURRENT DISPLAYED PAGE CAT |==================================================
//aws: 1.2

function porg_page_head_filter()
{
	global $wp_query;
	global $porg_current_cat;
	
	if(is_page())
	{
		$porg_current_cat = porg_get_page_cat($wp_query->post->ID);
	} else {
		$porg_current_cat = 0;
	}
}

function get_page_category()
{
	global $porg_current_cat;
	
	return $porg_current_cat;	
}

?>