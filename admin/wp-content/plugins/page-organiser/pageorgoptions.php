<?php
/*
Page Category Organiser Options Panel 2.0
*/

?>
<div class="wrap"> 
	<h2>Page Organiser Options</h2>
	
<?php	
$location = get_option('siteurl') . '/wp-admin/admin.php?page=page-organiser/pageorgoptions.php'; // Form Action URI

//Lets add default options if they don't exist
add_option('porg_usepagecats', true);

?>
	<a href="edit.php?page=/page-organiser/pageorgadmin.php">Return to Page Organiser</a>
	<br /><br />
	
<?php

//check form submission and update options

if ('process' == $_POST['stage'])
{
	if(isset($_POST['porg_usepagecats']))
	{
		update_option('porg_usepagecats', true);
	} else {
		update_option('porg_usepagecats', false);
	}
	
	echo "<div class='updated fade'><p> Options Updated</p></div>";
}

//Get options for form fields

$porg_usepagecats = get_option('porg_usepagecats');

if($porg_usepagecats)
{
?>
	<a href="edit.php?page=/page-organiser/pageorgcats.php">Manage Page Categories</a>
	<br /><br />
<?php
}


// And now for the form itself...
?>

	<form name="form1" method="post" action="<?php echo $location ?>&amp;updated=true">	
		<input type="hidden" name="stage" value="process" />
		<fieldset>
			<legend><?php _e('Page Organiser Options') ?></legend>
			
			<table width="50%" cellpadding="5" class="editform">
				<tr valign="top">
					<th width="80%" scope="row" style="text-align: left">Use Page Specific Categories:</th>
					<td>
						<input name="porg_usepagecats" type="checkbox" id="porg_usepagecats" value="porg_usepagecats"
						<?php if($porg_usepagecats == TRUE) {?> checked="checked" <?php } ?> />
					</td>
				</tr>
				<tr>
					<td>
						<small>Check this box to use special Page Categories that you define. Leave it unchecked to share existing Post Categories</small>
					</td>
				</tr>
			</table> 
			 
		</fieldset>
		<br />

		<input id="submit" type="submit" name="Submit" value="Update Options"/>
	
	</form> 

</div>