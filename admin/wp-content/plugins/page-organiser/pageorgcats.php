<?php
/*
Page Category Organiser - Page Categories 2.0
*/

require_once('admin-functions.php');

?>
<div class="wrap">
	<h2>Page Categories</h2>

	<a href="edit.php?page=/page-organiser/pageorgadmin.php">Return to Page Organiser</a>
	<br /><br />

	<a href="edit.php?page=/page-organiser/pageorgoptions.php">Set Page Organiser Options</a>
	<br /><br />
<?php

// first check if we are SAVING a record
if (isset($_POST['useraction']))
{
	if (($_POST['useraction'] == 'new') || ($_POST['useraction'] == 'edit'))
	{
		porg_savecat($_GET['cat']);
	}
}

//Now check if we are deleting a record
if ($_GET['action'] == 'delete')
{
	porg_deletecat($_GET['cat']);
}

//first display index of galleries in the database
porg_pagecat_list();

?>
</div>
<div class="wrap">
	<h2>Add/Edit Page Categories</h2>
<?php

// Are we editing one?
$catid = 0;
if ($_GET['action'] == 'edit') 
{
	$catid = $_GET['cat'];
}

//display the form
porg_editform($catid);	

?>
</div>
<?php

//=====| PAGE CATS LIST |===============================================================================================

function porg_pagecat_list()
{
	global $wpdb, $class, $table_prefix;
?>
		<table id="the-list-x" width="100%" cellpadding="3" cellspacing="3">
			<tr> 
				<th scope="col">ID</th> 
				<th scope="col" align="left">Category Name</th> 
				<th scope="col"></th> 
				<th scope="col"></th> 
			</tr>
			
<?php
	$cats = $wpdb->get_results("SELECT * FROM " . $table_prefix . "page_cats;");
	if ($cats) 
	{
		foreach ($cats as $pagecat) 
		{
			$pagecat->pagecat_name = wp_specialchars($pagecat->pagecat_name);
			$id = $pagecat->pagecat_ID;
			$class = ('alternate' == $class) ? '' : 'alternate';
?>
			<tr id='pagecat-<?php echo $id; ?>' class='<?php echo $class; ?>'> 
				<th scope="row"><?php echo $pagecat->pagecat_ID; ?></th> 
				<td>
					<?php echo($pagecat->pagecat_name); ?> 
				</td> 
				<td>
					<a href='edit.php?page=page-organiser/pageorgcats.php&amp;action=edit&amp;cat=<?php echo($id); ?>' class='edit'>Edit</a>
				</td>

				<?php if($id == 1) { echo("<td style='text-align:center'>Default</td>"); } else { ?>
					<td><a href='edit.php?page=page-organiser/pageorgcats.php&amp;action=delete&amp;cat=<?php echo($id); ?>' class='delete' onclick="return confirm('Are you sure you want to delete this Page Category?')">Delete</a></td>
				<?php } ?>

			</tr> 
<?php
		}
	}
?>
		</table>
<?php
	return;
}

//=====| PAGE CATS FORM |===============================================================================================

function porg_editform($catid)
{

	global $table_prefix, $wpdb;

	//Prepare the data - if catid <> 0 then we are editing one else it's new
	
	if ($catid)
	{
		$results = $wpdb->get_results('SELECT * FROM ' . $table_prefix . 'page_cats WHERE pagecat_id = ' . $catid);
		$catname = $results[0]->pagecat_name;
	}
	elseif ($_POST['useraction']=='new')
	{
		$catname = $_POST['catname'];
	}
?>

	<br />
	<h3>Create/Edit Page Category</h3>
	
	<form name="pagecatform" id="post" method="post" action="edit.php?page=page-organiser/pageorgcats.php">

		<!-- field is to hold page cat id -->
		<input type="hidden" name="catid" value=<?php echo $catid; ?>>
	
		<?php
		// set up the hidden action field
		if ($catid)
		{
			?>
			<input type="hidden" name="useraction" value="edit">
			<?php
		}
		else
		{
			?>
			<input type="hidden" name="useraction" value="new">
			<?php
		}
	
		?>
		<fieldset>
			<br />
			<legend>Page Category</legend>
					
			<table width="50%" cellpadding="3" cellspacing="0">
				<tr>
					<td>
						<td><label for="catname">&nbsp;Page Category Name:</label></td>
					</td>
					<td align="left" valign="top">
						<input type="text" size="25" name="catname" value="<?php echo($catname); ?>">
					</td>
				</tr>
			</table>
	
		</fieldset>
		<br />
		
		<input class="submit" type="submit" name="Submit" value="Save" />

	</form>

	<br />
<?php	

	return;
}

//=====| PAGE CATS SAVE |===============================================================================================

function porg_savecat($catid)
{
	global $table_prefix, $wpdb;

	//Check that all values have been entered
	If (empty($_POST['catname']))
	{
		echo "<div class='updated fade'>\n<p>No Page Category Name entered</p></div>";
		return false;
	}
		
	// Do the INSERT type first
	if ($_POST['useraction']=='new')
	{
		$sql = 'INSERT INTO '. $table_prefix . 'page_cats ';
		$sql = $sql . '(pagecat_name) ';
		$sql = $sql . 'VALUES ("';
		$sql = $sql . $_POST['catname'] . '");';
	
		if(!$wpdb->query($sql))
		{
			echo "<div class='updated fade'>\n<p>Unable to Save New Record</p></div>";
		}
		else
		{
			$_POST['useraction']='';
			echo "<div class='updated fade'>\n<p>New Page Category Created</p></div>";
		}
		return;
	}

	// Now do the UPDATE type
	if ($_POST['useraction']=='edit')
	{
		$sql = 'UPDATE ' . $table_prefix . 'page_cats SET ';
		$sql = $sql . 'pagecat_name = "' . $_POST['catname'] . '"';
		$sql = $sql . ' WHERE pagecat_id = ' . $_POST['catid'] . ';';		

		if(!$wpdb->query($sql))
		{
			echo "<div class='updated fade'>\n<p>Unable to Save Updated Record</p></div>";
		} 
		else 
		{
			echo "<div class='updated fade'>\n<p>Page Category Updated</p></div>";
		}
	}
	return;

}

//=====| PAGE CATS DELETE |=============================================================================================

function porg_deletecat($catid)
{
	global $table_prefix, $wpdb;

	// One job only - to delete a Page Cat
	$sql = 'DELETE FROM ' . $table_prefix . 'page_cats WHERE pagecat_id = ' . $catid .';';
	if(!$wpdb->query($sql))
	{
		echo "<div class='updated fade'>\n<p>Unable to Delete Page Category</p></div>";
		return;
	}
	else
	{
		echo "<div class='updated fade'>\n<p>Page Category Deleted</p></div>";
	}
	
	//any pages in this category need page cat set to default (1)
	$sql = 'UPDATE ' . $table_prefix . 'page2cat SET ';
	$sql = $sql . 'pagecat_id = 1';
	$sql = $sql . ' WHERE pagecat_id = ' . $catid . ';';		

	$wpdb->query($sql);	

	return;
}

?>