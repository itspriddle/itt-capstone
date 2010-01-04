<?php
/*
Page:           _config-rating.php
Created:        Aug 2006
Holds info for connecting to the db.
--------------------------------------------------------- 
ryan masuga, masugadesign.com
ryan@masugadesign.com 
--------------------------------------------------------- */

require_once '/home/priddle/db_connections/movierack.php';

	//Connect to  your rating database
	$dbhost        = DB_HOST;
	$dbuser        = DB_USER;
	$dbpass        = DB_PASS;
	$dbname        = DB_NAME;
	$tableName     = 'mr_movie_ratings';
	
	$unitwidth     = 20; // the width (in pixels) of each rating unit (star, etc.)
	// if you changed your graphic to be 50 pixels wide, you should change the value above
	
	//$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die  ('Error connecting to mysql');
	//mysql_select_db($dbname);

	$conn = $connection;

?>
