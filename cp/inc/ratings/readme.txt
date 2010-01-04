(Unobtusive) AJAX Rating Bars v1.1 (August 2006)
ryan masuga, ryan@masugadesign.com (http://www.masugadesign.com)

Homepage for this script:
http://www.masugadesign.com/the-lab/scripts/unobtrusive-ajax-star-rating-bar/
=============================================================
Use these files however you want, but don't redistribute without 
the proper credits, please. I'd appreciate hearing from you if you're
using this script. If you do contact me and point out your rater version,
I can put a link to your site on the homepage for this script.
Suggestions or improvements welcome!
=============================================================
Based on work found at Komodo Media (http://komodomedia.com) 
and Climax Designs (http://slim.climaxdesigns.com/).
Thanks also to Ben Nolan (http://bennolan.com/behaviour/) for Behavio(u)r!
-------------------------------------------------------------
The necessary files:

PHP Files:
  _config-rating.php
  _drawrating.php
  db.php
  rpc.php

CSS File:
  rating.css

Javascript files:
  behaviour.js
  rating.js

Image files:
  starrating.gif
  working.gif

-------------------------------------------------------------
v1.1 Features:
 * Uses unobtrusive Javascript, so ratings will still work if the user has Javascript off
   (the script has been tested in IE 6, Safari, and FF).
 * keeps Javascript out of the HTML, resulting in cleaner markup
 * There are now some checks in place to discourage monkey-business, like negative numbers, or funky IP's
 * IP lockout is now in the script
 * You can now specify the number of units! If you want 5 stars, just add a 5, otherwise the script defaults to 10.
   Examples:
     <?php rating_bar('2',5); ?> - Outputs a 5-star rating, with an id of 2.
     <?php rating_bar('3b',8); ?> - Outputs an 8-star rating, with an id of 3b.
     <?php rating_bar('66'); ?> - Outputs a standard 10-star rating, with an id of 66.
 * Enter database info in one place rather than three places
 * This script only uses ONE image
-------------------------------------------------------------
Notes:
I did away with the INSERT statement, because someone could get the db.php href and alter that 
(though not the rating or the IP) to add a new line into your db. I couldn't figure out a decent 
way to check that. So, at least for now, any new raters placed on a page have to be entered in 
your db manually, before votes can be registered to that id.
-------------------------------------------------------------
Installation:

=========================================================
1. Make your table for the ratings in your db
=========================================================
CREATE TABLE 'ratings' (
  'id' varchar(11) NOT NULL,
  'total_votes' int(11) NOT NULL default '0',
  'total_value' int(11) NOT NULL default '0',
  'used_ips' longtext,
  PRIMARY KEY  ('id')
) TYPE=MyISAM AUTO_INCREMENT=3 ;


1.1. Put a few raters in your db. You could use phpMyAdmin, 
Cocoa mySQL, or Navicat to do this pretty easily.
---------------------------------------------------
INSERT INTO 'ratings' VALUES (1, 0, 0, ''); etc etc


=========================================================
2. Enter your specific info into _config-rating.php
=========================================================
	$dbhost        = 'localhost';
	$dbuser        = '###';
	$dbpass        = '###';
	$dbname        = '###';
	$tableName     = 'ratings';
	$unitwidth     = 30;


=========================================================
3. Enter this line at the top of any page where you want
   to have rating bars.
=========================================================
<?php require('_drawrating.php'); ?>


=========================================================
4. Point to the right Javacsript and CSS files (you need 
   behavior.js, rating.js, and rating.css)
=========================================================
<script type="text/javascript" language="javascript" src="js/behavior.js"></script>
<script type="text/javascript" language="javascript" src="js/rating.js"></script>
<link rel="stylesheet" type="text/css" href="css/rating.css" />

Remember to make sure to fix paths for the images as well.


=========================================================
5. Drop the function wherever you want a rating bar to 
   appear
=========================================================
<?php rating_bar('1',5); ?> - makes a 5-star rater with ID of 1
<?php rating_bar('2',8); ?> - makes an 8-star rater with ID of 2
<?php rating_bar('4d'); ?> - makes 10-star (default) rater, ID of 4d

If you want to change how the rating bar is rendered, you will need to edit
the _drawrating.php file. Also, you might need to edit the bottom of the rpc.php
file at about line 52, where the $newback variable is.






