<?php
/*
Page:           _drawrating.php
Created:        Aug 2006
The function that draws the rating bar.	
--------------------------------------------------------- 
ryan masuga, masugadesign.com
ryan@masugadesign.com 
--------------------------------------------------------- */
require_once('_config-rating.php'); // get the db connection info

function rating_bar($id, $units = NULL) { 
	global $unitwidth;
	$unitwidth = 20;
	//set some variables
	$ip = $_SERVER['REMOTE_ADDR'];
	if ( $units == NULL ) $units = 10;
	$sql = "SELECT total_votes, total_value, used_ips FROM mr_movie_ratings WHERE movie_id = $id ";
	//echo $sql;
	$query 			= mysql_query( $sql ) or die( " Error: " .mysql_error() );
	$numbers 		= mysql_fetch_assoc($query);
	$count 			= $numbers['total_votes']; 				//how many votes total
	$current_rating = $numbers['total_value']; 				//total number of rating added together and stored
	$tense  		= ( $count == 1 ) ? "vote" : "votes"; 	//plural form votes/vote
	
	// determine whether the user has voted, so we know how to draw the ul/li
	$voted			= @mysql_num_rows( mysql_query( "SELECT used_ips FROM mr_movie_ratings WHERE used_ips LIKE '%".$ip."%' AND movie_id='".$id."' " ) ); 
	
	// now draw the rating bar
	$html = "                     <div class=\"ratingblock\" style=\"position: relative; left: 5px\">\n".
			"                       <div id=\"unit_long$id\">\n".
    		"                         <ul id=\"unit_ul$id\" class=\"unit-rating\" style=\"width: ". ( $unitwidth * $units ) ."px;\">\n".
			"                           <li class=\"current-rating\" style=\"width: ". ( @number_format( ($current_rating / $count), 2 ) * $unitwidth ) ."px;\">".
			"Currently ". @number_format( $current_rating / $count, 2 ) ." / $units</li>\n";
	for ($ncount = 1; $ncount <= $units; $ncount++) {
	// loop from 1 to the number of units
		if( !$voted ) { 
		// if the user hasn't yet voted, draw the voting stars 
		
    		$html .= "                           <li><a href=\"/inc/ratings/db.php?".
					 "j=$ncount&amp;".
					 "q=$id&amp;".
					 "t=$ip&amp;".
					 "c=$units\" title=\"$ncount out of $units\" class=\"r$ncount-unit rater\">$ncount</a></li>\n";
		} 
	}
	$ncount = 0; // resets the count

	$html .= "                         </ul>\n".
			 "                      </div>\n".
			 "                    </div>\n";
//			 "                    <p". ( ( $voted ) ? ' class="voted"' : '' ). " style=\"text-align: center\"><strong>Rating</strong><br />".
//			 @number_format( $current_rating / $count, 1 ) .
//			 " / $units<br />$count $tense cast</p>\n";
	//echo $html;
	return $html;
}
?>
