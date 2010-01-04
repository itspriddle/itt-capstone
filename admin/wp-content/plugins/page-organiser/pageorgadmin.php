<?php
/*
Page Category Organiser Admin Panel 2.0
*/

require_once('admin-functions.php');
include_once('pageorgfunctions.php');

// Check tables and options ==========
$poversion = get_option('porg_version');
if ($poversion <> 10)
{
	pageorg_create_tables();
}

global $porg_usepagecats;
global $catselect;

$porg_usepagecats = get_option('porg_usepagecats');

// are we bulk changing categories
if(!empty($_POST['changecat']))
{
	$catid = $_POST['catselect'];
	$pagelist = $_POST['changecat'];
	foreach($pagelist as $pageid)
	{
		porg_save_pagecat($pageid, $catid);
	}
}

// are we bulk changing menu orders - do a quick scan...
$pagelist = $_POST['id'];
$oldmo = $_POST['mo'];
$newmo = $_POST['menuord'];
for ($i=0;$i<count($pagelist);$i++)
{
	if($oldmo[$i] <> $newmo[$i])
	{
		porg_update_menuorder($pagelist[$i], $newmo[$i]);
	}
}

// are we selecting a specific category to display
if(isset($_POST['catselect']))
{
	$catselect = $_POST['catselect'];
} else {
	$catselect = 1;
}


//=====| ADMIN PAGE |===============================================================================================

?>
<div class="wrap">
<h2>Page Organiser</h2>

<a href="edit.php?page=/page-organiser/pageorgoptions.php">Set Page Organiser Options</a>
<br /><br />
<?php
if($porg_usepagecats)
{
?>
	<a href="edit.php?page=/page-organiser/pageorgcats.php">Manage Page Categories</a>
	<br />
<?php
}

porg_page_cat_form($porg_usepagecats, $catselect);

porg_page_list($porg_usepagecats);

//=====| CATEGORY SELECT |==========================================================================================

function porg_page_cat_form($porg_usepagecats, $catselect)
{
	global $table_prefix, $wpdb;
?>
	<br />
	<form name="pagebycat" action="edit.php?page=page-organiser/pageorgadmin.php" method="post" style="float: left; width: 30em; margin-bottom: 1em;">
		<fieldset>
		<legend>List by Category&hellip;</legend>
		<select name="catselect">
		<option value="0">All Categories</option>
<?php
		porg_populate_catlist($porg_usepagecats, $catselect)
?>
		</select>
			<input type="submit" name="submit" value="Show Pages by Category"  /> 
		</fieldset>
	</form>
	<div class="clear"></div>
<?php
	return;
}

//=====| LIST PAGES BY CATEGORY|====================================================================================

function porg_page_list($porg_usepagecats)
{
?>
	<br />
	
	<form name="bulkchange" action="edit.php?page=page-organiser/pageorgadmin.php" method="post">
		
		<table id="the-list-x" width="100%" cellpadding="3" cellspacing="3">
			<tr> 
				<th scope="col">Page<br />ID</th> 
				<th scope="col" align="left">Page Title</th> 
				<th scope="col" align="center">Page<br />Order</th>
				<th scope="col" align="center">New Page<br />Order</th>
				<th scope="col" align="left">Category</th>
				<th scope="col" align="center">Change<br />Category</th>
			</tr> 
		
<?php
	if(pageorg_rows())
	{
?>
		
		</table>
		<br />
	
		<fieldset>
			<legend>Change Selected Page Categories to&hellip;</legend>
			<select name="catselect">
<?php
		porg_populate_catlist($porg_usepagecats)
?>
			</select>
			<input type="submit" name="bulkchangepagecats" value="Update Selected Page Categories/Page Order Changes" />
		</fieldset>	
	</form>
<?php
	} else {
?>
		</table>
<?php
	}
?>
</div>
<?php
	return;
}

function pageorg_rows($parent = 0, $level = 0, $pages = 0) 
{
	global $table_prefix, $wpdb, $class, $porg_usepagecats, $catselect;

	$sql = porg_get_page_list($porg_usepagecats, $catselect);

	if (!$pages) $pages = $wpdb->get_results($sql);

	if ($pages) 
	{
		foreach ($pages as $pageitem) 
		{
			if ($pageitem->post_parent == $parent) 
			{
				$pageitem->post_title = wp_specialchars($pageitem->post_title);
				$pad = str_repeat('&#8658; ', $level);
				$id = $pageitem->ID;
				$class = ('alternate' == $class) ? '' : 'alternate';
				?>
			  	<tr id='page-<?php echo $id; ?>' class='<?php echo $class; ?>'> 
    				<th scope="row"><?php echo $pageitem->ID; ?></th> 
    				<td>
      					<?php echo $pad; ?><?php echo($pageitem->post_title); ?> 
    				</td> 
					<td align="center">
						<?php echo($pageitem->menu_order); ?>
					</td>
					<td align="center">
						<input name="menuord[]" type="text" size="4" id="<?php echo($id); ?>" value="<?php echo($pageitem->menu_order); ?>" />
					</td>
					<td align="left">
						<?php echo(porg_get_category($id, $porg_usepagecats)); ?>
					</td>
					<td align="center">
						<input name="changecat[]" type="checkbox" id="<?php echo($id); ?>" value="<?php echo($id); ?>" />
					</td>
					<td>
						<a href="<?php echo(get_permalink($id)); ?>" rel="permalink" class="edit"><?php _e('View'); ?></a>
					</td>
					<td>
						<?php if ( current_user_can('edit_pages') ) { echo "<a href='post.php?action=edit&amp;post=$id' class='edit'>" . __('Edit') . "</a>"; } ?>
					</td> 

					<input type="hidden" name="id[]" value="<?php echo($id); ?>">
					<input type="hidden" name="mo[]" value="<?php echo($pageitem->menu_order); ?>" />
					
  				</tr> 

				<?php
				pageorg_rows($id, $level +1, $pages);
			}
		}
		return true;
	}
	return false;
}

?>