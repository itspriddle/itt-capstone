
<div id="footer">
<p>
Powered by <a href="http://nevercraft.net/">Nevercraft.net</a> and <a href="http://wordpress.org/">Wordpress</a> <br />
Page generated in: <?php printf(__('%s seconds'), number_format(timer_stop(), 2)); ?>.
</p>

<p>movierack &copy;2006 | ideas are bulletproof</p>

</div>
<?php do_action('admin_footer', ''); ?>
<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>

<?php
if ( (substr(php_sapi_name(), 0, 3) == 'cgi') && spawn_pinger() ) {
	echo '<iframe id="pingcheck" src="' . get_settings('siteurl') .'/wp-admin/execute-pings.php?time=' . time() . '" style="border:none;width:1px;height:1px;"></iframe>';
}
?>

</body>
</html>
