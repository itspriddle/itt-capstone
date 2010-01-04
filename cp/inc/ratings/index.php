<?php require('_drawrating.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title>Multiple Ajax Star Rating Bars</title>

<script type="text/javascript" language="javascript" src="js/behavior.js"></script>
<script type="text/javascript" language="javascript" src="js/rating.js"></script>

<link rel="stylesheet" type="text/css" href="css/default.css" />
<link rel="stylesheet" type="text/css" href="css/rating.css" />
</head>

<body>
<?php echo rating_bar('1',5); ?>
<?php rating_bar('2',5); ?>
<?php rating_bar('3',6); ?>
<?php rating_bar('4',8); ?>
<?php rating_bar('5'); ?>
<?php rating_bar('6'); ?>
<?php rating_bar('7'); ?>
<?php rating_bar('8'); ?>
<?php rating_bar('9'); ?>
</body>
</html>
