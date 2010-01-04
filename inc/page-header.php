<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>movierack<?php if ( isset( $page_title ) ) echo " &raquo; $page_title"; ?></title>
<script language="javascript" type="text/javascript" src="/js/tooltip/ajax-dynamic-content.js"></script>
<script language="javascript" type="text/javascript" src="/js/tooltip/ajax.js"></script>
<script language="javascript" type="text/javascript" src="/js/tooltip/ajax-tooltip.js"></script>
<script language="javascript" type="text/javascript" src="/js/movierack.js"></script>
<script language="javascript" type="text/javascript" src="/js/ratings/behavior.js"></script>
<script language="javascript" type="text/javascript" src="/js/ratings/rating.js"></script>
<script language="javascript" type="text/javascript" src="/js/navigate.js"></script>

<?php // CSS Picker would go here ?>

<link rel="stylesheet" type="text/css" href="/css/movierack-final.css" />

</head>
<body>
<div id="overall">
  <div id="top">
    <h1><span>Movierack: Something for everyone</span></h1>
    <ul id="nav">
      <li id="nav-home"><a href="http://movierack.net/"><span>Home</span></a></li>
      <li id="nav-new"><a href="http://movierack.net/new"><span>New Movies</span></a></li>
      <li id="nav-browse"><a href="http://movierack.net/browse"><span>Browse</span></a></li>
      <li id="nav-reviews"><a href="http://movierack.net/reviews"><span>Reviews</span></a></li>
      <li id="nav-shows"><a href="http://movierack.net/shows"><span>Shows</span></a></li>
      <li id="nav-signup"><a href="http://movierack.net/signup"><span>Sign Up </span></a></li>
    </ul>
  </div>
  <div id="content"<?php if ( $_SERVER['PHP_SELF'] == '/index.php' ) echo ' class="homepage"'; ?>>
