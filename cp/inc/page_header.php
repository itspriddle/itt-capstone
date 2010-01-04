<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Movierack.net : Administration <?php if ( isset( $page_title ) ) echo " : $page_title"; ?></title>
<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="/js/tiny_init.js"></script>
<script type="text/javascript" src="/js/dropdown.js"></script>
<link rel="stylesheet" type="text/css" href="/css/admin.css" />
</head>
<body>
<div id="container">
	<div id="top">
		<h1><a href="/home"><span>Movierack Administration</span></a></h1>
		<div id="navigation">
			<a href="/home" class="parent nav1">&raquo; Home</a>
			<a href="/movies" class="parent nav2" onmouseover="dropdownmenu(this, event, 'movies-menu')">&raquo; Movies</a>
			<div id="movies-menu" class="dropdown bg2">
				<a href="/movies/add">Add Movies</a>
				<a href="/movies/edit">Edit Movies</a>
				<a href="/movies/delete">Delete Movies</a>
			</div>
			<a href="/shows" class="parent nav3" onmouseover="dropdownmenu(this, event, 'shows-menu')">&raquo; Shows</a>
			<div id="shows-menu" class="dropdown bg3">
				<a href="/shows/add">Add Shows</a>
				<a href="/shows/edit">Edit Shows</a>
				<a href="/shows/delete">Delete Shows</a>
			</div>
			<a href="/users" class="parent nav4" onmouseover="dropdownmenu(this, event, 'users-menu')">&raquo; Users</a>
			<div id="users-menu" class="dropdown bg4">
				<a href="/users/add">Add Users</a>
				<a href="/users/edit">Edit Users</a>
				<a href="/users/delete">Delete User</a>
			</div>
			<br style="height: 1px; clear: both;" />
		</div>

<!--
		<div id="subnav">
<?php
switch($_GET['page']) {
	case 'movies':
		echo '<a href="/movies">Manage Movies</a> | <a href="/movies/add">Add Movies</a>';
	break;

	case 'shows':
		echo '<a href="/shows">Manage Shows</a> | <a href="/shows/add">Add Shows</a>';
	break;

	case 'users':
		echo '<a href="/users">Manage Users</a> | <a href="/users/add">Add User</a>';
	break;
}
?>
		</div>
-->
	</div>
	<div id="content">
	<?php if ( $h2 ) echo "<h2>". ucfirst( $page_title ) ."</h2>\n"; ?> 
