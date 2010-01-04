<?php
/*
Page Category Organiser Support Functions 2.0
*/

function porg_get_category($pageid, $porg_usepagecats)
{
	global $wpdb, $table_prefix;

	if($porg_usepagecats)
	{
		$cat = $table_prefix . 'page_cats';
		$rel = $table_prefix . 'page2cat';
		$sql = 	'SELECT ' . $cat . '.pagecat_name, ' . $rel . '.pagecat_id, ' . $rel . '.page_id FROM ' . $cat . ' LEFT JOIN ' . $rel . ' ON ' . $cat . '.pagecat_ID = ' . $rel . '.pagecat_id WHERE ' . $rel . '.page_id = ' . $pageid;
		$cat = $wpdb->get_results($sql);
		return $cat[0]->pagecat_name;
	} else {
		$cat = $table_prefix . 'categories';
		$rel = $table_prefix . 'post2cat';
		$sql = 	'SELECT ' . $cat . '.cat_name, ' . $rel . '.category_id, ' . $rel . '.post_id FROM ' . $cat . ' LEFT JOIN ' . $rel . ' ON ' . $cat . '.cat_ID = ' . $rel . '.category_id WHERE ' . $rel . '.post_id = ' . $pageid;
		$cat = $wpdb->get_results($sql);
		return $cat[0]->cat_name;
	}
}

function porg_get_page_list($porg_usepagecats, $catselect)
{
	global $wpdb, $table_prefix;
	
	if($porg_usepagecats)
	{
		$p = $table_prefix . 'posts.';
		$c = $table_prefix . 'page2cat.';
		$pt = $table_prefix . 'posts';
		$ct = $table_prefix . 'page2cat';
		$sql = "SELECT " . $p . "ID, " . $p . "post_title, " . $p . "post_status, " . $p . "post_parent, " . $p . "menu_order, " . $c . "pagecat_id FROM " . $pt . " LEFT JOIN " . $ct . " ON " . $p . "ID = " . $c . "page_id WHERE ";
		if($catselect == 0)
		{
			$sql .= $p . "post_status = 'static' ORDER BY " . $c . "pagecat_id, " . $p . "menu_order";
		} else {
			$sql .= $p . "post_status = 'static' AND " . $c . "pagecat_id = " . $catselect . " ORDER BY " . $p . "menu_order";
		}
		return $sql;
	} else {
		$p = $table_prefix . 'posts.';
		$c = $table_prefix . 'post2cat.';	
		$pt = $table_prefix . 'posts';
		$ct = $table_prefix . 'post2cat';
		$sql = "SELECT " . $p . "ID, " . $p . "post_title, " . $p . "post_status, " . $p . "post_parent, " . $p . "menu_order, " . $c . "category_id FROM " . $pt . " LEFT JOIN " . $ct . " ON " . $p . "ID = " . $c . "post_id WHERE ";
		if($catselect == 0)
		{
			$sql .= $p . "post_status = 'static' ORDER BY " . $c . "category_id, " . $p . "menu_order";
		} else {
			$sql .= $p . "post_status = 'static' AND " . $c . "category_id = " . $catselect . " ORDER BY " . $p . "menu_order";
		}
		return $sql;
	}
}

function porg_get_catid($porg_usepagecats, $catname)
{
	global $wpdb, $table_prefix;

	if($porg_usepagecats)
	{
		$sql = "SELECT pagecat_id FROM " . $table_prefix . "page_cats WHERE pagecat_name = '" . $catname . "'";
		$id = $wpdb->get_var($sql);
		return $id;
	} else {
		$sql = "SELECT cat_id FROM " . $table_prefix . "categories WHERE cat_name = '" . $catname . "'";
		$id = $wpdb->get_var($sql);
		return $id;
	}
}

function porg_get_catname($porg_usepagecats, $catid)
{
	global $wpdb, $table_prefix;

	if($porg_usepagecats)
	{
		$sql = "SELECT pagecat_name FROM " . $table_prefix . "page_cats WHERE pagecat_id = '" . $catid . "'";
		$catname = $wpdb->get_var($sql);
		return $catname;
	} else {
		$sql = "SELECT cat_name FROM " . $table_prefix . "categories WHERE cat_id = '" . $catid . "'";
		$catname = $wpdb->get_var($sql);
		return $catname;
	}
}

function porg_get_page_cat($pageid)
{
	global $wpdb, $table_prefix;

	if(get_option('porg_usepagecats'))
	{
		$sql = "SELECT pagecat_id FROM " . $table_prefix . "page2cat WHERE page_id = " . $pageid;
		$id = $wpdb->get_var($sql);
		return $id;
	} else {
		$sql = "SELECT category_id FROM " . $table_prefix . "post2cat WHERE post_id = " . $pageid;
		$id = $wpdb->get_var($sql);
		return $id;
	}
}

function porg_save_pagecat($pageid, $catid)
{
	global $wpdb, $table_prefix;

	if(get_option('porg_usepagecats'))
	{
		// to find out if update or new try finding in table...
		$currentcat = porg_get_page_cat($pageid);
		if(isset($currentcat))
		{
			//update then - check if changed
			if($currentcat <> $catid)
			{
				//it needs an update
				$sql = 'UPDATE ' . $table_prefix . 'page2cat SET ';
				$sql = $sql . 'pagecat_id = ' . $catid;
				$sql = $sql . ' WHERE page_id = ' . $pageid . ';';		
				$wpdb->query($sql);
			}
		} else {
			//add new
			$sql = 'INSERT INTO ' . $table_prefix . 'page2cat (page_id, pagecat_id) VALUES (' . $pageid . ', ' . $catid .');';
			$wpdb->query($sql);
		}
	} else {
		// to find out if update or new try finding in table...
		$currentcat = porg_get_page_cat($pageid);
		if(isset($currentcat))
		{
			//update then - check if changed
			if($currentcat <> $catid)
			{
				//it needs an update
				$sql = 'UPDATE ' . $table_prefix . 'post2cat SET ';
				$sql = $sql . 'category_id = ' . $catid;
				$sql = $sql . ' WHERE post_id = ' . $pageid . ';';		
				$wpdb->query($sql);
			}
		} else {
			//add new
			$sql = 'INSERT INTO ' . $table_prefix . 'post2cat (post_id, category_id) VALUES (' . $pageid . ', ' . $catid .');';
			$wpdb->query($sql);	
		}
	}
	return;
}

function porg_populate_catlist($porg_usepagecats, $catselect)
{
	global $wpdb, $table_prefix;
	
	if($porg_usepagecats)
	{
		$sql = "SELECT pagecat_id AS catid, pagecat_name AS catname FROM " . $table_prefix . "page_cats ORDER BY pagecat_id";
	} else {
		$sql = "SELECT cat_ID AS catid, cat_name AS catname FROM " . $table_prefix . "categories ORDER BY cat_ID";
	}
	$catlist = $wpdb->get_results($sql);

	foreach ($catlist as $cat) 
	{
		if($cat->catid == $catselect)
			$default = 'selected="selected"';
		else
			$default = null;
		
		echo "<option $default value=\"" . $cat->catid . '">';
		echo $cat->catname;
		echo "</option>\n";
	}
	return;
}

function porg_update_menuorder($pageid, $menuorder)
{
	global $wpdb, $table_prefix;
	
	$sql = 'UPDATE ' . $table_prefix . 'posts SET menu_order = ' . $menuorder . ' WHERE ID = ' . $pageid . ';';
	$wpdb->query($sql);	
	return;
}

//===| TEMPLATE TAG |==================================================================================================================
function list_pages_by_category($catid=0, $show_children=true, $omit1=true, $mainhead='', $beforecat='', $aftercat='', $displayit=true, $sub_on_main=false, $bold=false)
{
	global $pagelist, $index;
	global $wp_query;

	$index = 0;
	$porg_usepagecats = get_option('porg_usepagecats');

	porg_page_rows(0, 0, 0, $catid, $porg_usepagecats);
	
	$index = 0;
	$entry = 0;
	$output = $mainhead . "\n";
	$output .= "<ul class='pagelist'>\n";
	$mainlevel = 1;
	$sublevel = 0;
	$currentcatid = -1;
	$currentparent = 0;
	
	// if we are omitting cat 1 then find the entry point in the array
	if($omit1)
	{
		for ($entry=0;$entry<count($pagelist);$entry++)
		{	
			if($pagelist[$entry]->pagecat_id <> 1)
			{
				$index = $entry;
				break;
			}
		}
	}

	// main loop
	for ($index=$entry;$index<count($pagelist);$index++)
	{
		// check if current page is the one displaying
		$currentboldstart = '';
		$currentboldend = '';
		if((is_page() && ($pagelist[$index]->ID == $wp_query->post->ID)))
		{
			if($bold)
			{
				$currentboldstart = '<strong>';
				$currentboldend = '</strong>';
			} else {
				$currentboldstart = '';
				$currentboldend = '';
			}
		}

		// check if starting a new category
		if($pagelist[$index]->pagecat_id <> $currentcatid)
		{
			if($index > $entry) 
			{
				$output .= "</ul>\n</li>\n";
				$mainlevel = $mainlevel -1;
			}
			$currentcatid = $pagelist[$index]->pagecat_id;
			$output .= "<li class='pagecat'>" . $beforecat . porg_get_catname($porg_usepagecats, $currentcatid) . $aftercat . "\n";
			$output .= "<ul class='pagemain'>\n";
			$mainlevel = $mainlevel + 1;
		}
		
		// check if ending a subpage set
		if(($currentparent > 0) && ($pagelist[$index]->post_parent == 0))
		{
			$output .= str_repeat("</ul>\n</li>\n", $sublevel);
			$mainlevel = $mainlevel - $sublevel;
			$sublevel = 0;
			$currentparent = 0;
		}
		
		// what sort of entry - main or sub page
		if($pagelist[$index]->post_parent == $currentparent)
		{
			$output .= '<li><a href = "' . get_permalink($pagelist[$index]->ID) . '" >' . $currentboldstart . $pagelist[$index]->post_title . $currentboldend . "</a>";
		} else {
			if($show_children)
			{
				if($sub_on_main)
				{
					if(((is_page() && ($pagelist[$index]->post_parent == $wp_query->post->ID))) || ((is_page() && ($wp_query->post->post_parent == $pagelist[$index]->post_parent))))
					{
						$display_subs = true;
					} else {
						$display_subs = false;
					}
				} else {
					$display_subs = true;
				}
				
				if($display_subs)
				{
					$output .= "<ul class='pagesub'>\n";
					$output .= '<li><a href = "' . get_permalink($pagelist[$index]->ID) . '" >' . $currentboldstart . $pagelist[$index]->post_title . $currentboldend . "</a></li>\n";
					$sublevel = $sublevel + 1;
					$mainlevel = $mainlevel + 1;
					$currentparent = $pagelist[$index]->post_parent;
				}
			}
		}
		if ($currentparent == $pagelist[$index+1]->post_parent)
		{
			$output .= "</li>\n";
		} else {
			$output .= "\n";
		}
	}
	$output .= str_repeat("</ul>\n", $mainlevel-1);
	$output .= "</li>\n</ul>\n";

	$pagelist = '';
	$index = '';

	if($displayit)
	{
		echo $output;
	} else {
		return $output;
	}
}

function porg_page_rows($parent = 0, $level = 0, $pages = 0, $catid, $porg_usepagecats) 
{
	global $table_prefix, $wpdb, $pagelist, $index;

	$sql = porg_get_page_list($porg_usepagecats, $catid);

	if (!$pages) $pages = $wpdb->get_results($sql);

	if ($pages) 
	{
		foreach ($pages as $pageitem) 
		{

			if ($pageitem->post_parent == $parent) 
			{
				$pagelist[$index]->post_title = wp_specialchars($pageitem->post_title);
				$pagelist[$index]->ID = $pageitem->ID;
				$pagelist[$index]->post_parent = $pageitem->post_parent;
				
				if($porg_usepagecats)
				{
					$pagelist[$index]->pagecat_id = $pageitem->pagecat_id;
				} else {
					$pagelist[$index]->pagecat_id = $pageitem->category_id;
				}
				$index++;
				
				porg_page_rows($pageitem->ID, $level +1, $pages,  $catid, $porg_usepagecats);
			}
		}
	}
}

//===| TEMPLATE TAG |==================================================================================================================
function display_category($separator, $parents)
{
	global $wp_query;
	
	if(is_page())
	{
		echo(porg_get_category($wp_query->post->ID, get_option('porg_usepagecats')));
	} else {
		the_category($separator, $parents);
	}
	return;
}
?>