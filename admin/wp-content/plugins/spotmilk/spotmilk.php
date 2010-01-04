<?php
/*
Plugin Name: SpotMilk
Plugin URI: http://www.ceprix.net/archives/spotmilk-admin-theme-for-wordpress/
Description: SpotMilk is an admin theme for WordPress 2.0.x, which is originally inspired by <a href="http://www.apple.com/macosx/features/spotlight/">Spotlight</a> on Mac OS X Tiger and the <a href="http://www.maxthemes.com/themes/?theme=Milk">Milk</a> theme by Max Rudberg, and its overall main layout remains based on the default admin theme that comes with WP 2.0.x.
Author: Sunghwa Park
Version: 1.7.2
Author URI: http://www.ceprix.net/
*/

function spotmilk_header() {
	$site_uri = get_settings('siteurl');
	$plugin_uri = $site_uri . '/wp-content/plugins/spotmilk/';
	echo '<link rel="stylesheet" type="text/css" href="' . $plugin_uri . 'spotmilk.css?version=1.7.2" />';
	/* Uncomment the next line if you want to activate BlackMilk */
	/* echo '<link rel="stylesheet" type="text/css" href="' . $plugin_uri . 'blackmilk.css?version=1.7.2" />'; */
	if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
	echo '<link rel="stylesheet" type="text/css" href="' . $plugin_uri . 'ie.css?version=1.7.2" />';
	else if (strstr($_SERVER['HTTP_USER_AGENT'], 'Gecko/'))
	echo '<link rel="stylesheet" type="text/css" href="' . $plugin_uri . 'moz.css?version=1.7.2" />';
	else if (strstr($_SERVER['HTTP_USER_AGENT'], 'AppleWebKit'))
	echo '<link rel="stylesheet" type="text/css" href="' . $plugin_uri . 'webkit.css?version=1.7.2" />';
	}

add_action('admin_head', 'spotmilk_header');

function spotmilk_footer() {
	$version = get_bloginfo('version');
	$site_uri = get_settings('siteurl');
	$plugin_uri = $site_uri . '/wp-content/plugins/spotmilk/';
	$wp = __('Visit WordPress homepage');
	echo '<div id="bottommeta"><p id="logo"><a href="http://wordpress.org/" title="' . $wp . '"><img src="' . $plugin_uri . 'images/wordpress-logo.png" alt="WordPress" /></a><br /><br /><a href="http://codex.wordpress.org/">Documentation</a> | <a href="http://wordpress.org/support/">Support Forums</a></p>';
	echo '<p id="copyright">Powered by <a href="http://wordpress.org/">WordPress</a> ' . $version . ' &amp; <a href="http://www.ceprix.net/archives/spotmilk-admin-theme-for-wordpress/">SpotMilk</a> 1.7.2</p></div>';
	}

add_action('admin_footer', 'spotmilk_footer');

?>