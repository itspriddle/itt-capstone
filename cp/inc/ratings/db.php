<?php
/*
Page:           db.php
Created:        Aug 2006
This page handles the database update if the user
does NOT have Javascript enabled.	
--------------------------------------------------------- 
ryan masuga, masugadesign.com
ryan@masugadesign.com 
--------------------------------------------------------- */
header("Cache-Control: no-cache");
header("Pragma: nocache");
require('_config-rating.php'); // get the db connection info

//getting the values
$vote_sent = $_REQUEST['j'];
$id_sent = $_REQUEST['q'];
$ip_num = $_REQUEST['t'];
$units = $_REQUEST['c'];
$ip = $_SERVER['REMOTE_ADDR'];
$referer  = $_SERVER['HTTP_REFERER'];

//connecting to the database to get some information
$query = mysql_query("SELECT total_votes, total_value, used_ips FROM mr_movie_ratings WHERE movie_id='$id_sent' ")or die(" Error: ".mysql_error());
$numbers = mysql_fetch_assoc($query);
$checkIP = unserialize($numbers['used_ips']);
$count = $numbers['total_votes']; //how many votes total
$current_rating = $numbers['total_value']; //total number of rating added together and stored
$sum = $vote_sent+$current_rating; // add together the current vote value and the total vote value
$tense = ($count==1) ? "vote" : "votes"; //plural form votes/vote

// checking to see if the first vote has been tallied
// or increment the current number of votes
($sum==0 ? $added=0 : $added=$count+1);

// if it is an array i.e. already has entries the push in another value
((is_array($checkIP)) ? array_push($checkIP,$ip_num) : $checkIP=array($ip_num));
$insertip=serialize($checkIP);

if (($vote_sent >= 1 && $vote_sent <= $units) && ($ip == $ip_num)) { // keep votes within range
	$update = "UPDATE mr_movie_ratings SET total_votes='".$added."', total_value='".$sum."', used_ips='".$insertip."' WHERE movie_id='$id_sent'";
	$result = mysql_query($update);		
} 
header("Location: $referer"); // go back to the page we came from 
exit;
?>
