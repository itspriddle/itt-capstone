<?php
 # Movierack.net
 #
 # signup.php
 # username: admin
 # password: HoSTRoCKeT2007
 # A modified wordpress registration page

require('./admin/wp-config.php');
require_once( ABSPATH . WPINC . '/registration-functions.php');

$action = $_REQUEST['action'];
if ( !get_settings('users_can_register') )
	$action = 'disabled';

header( 'Content-Type: ' . get_bloginfo('html_type') . '; charset=' . get_bloginfo('charset') );

switch( $action ) {

case 'register':

	$user_login = sanitize_user( $_POST['user_login'] );
	$user_email = $_POST['user_email'];
	
	$errors = array();
		
	if ( $user_login == '' )
		$errors['user_login'] = __('<strong>ERROR</strong>: Please enter a username.');

	/* checking e-mail address */
	if ($user_email == '') {
		$errors['user_email'] = __('<strong>ERROR</strong>: Please type your e-mail address.');
	} else if (!is_email($user_email)) {
		$errors['user_email'] = __('<strong>ERROR</strong>: The email address isn&#8217;t correct.');
		$user_email = '';
	}

	if ( ! validate_username($user_login) ) {
		$errors['user_login'] = __('<strong>ERROR</strong>: This username is invalid.  Please enter a valid username.');
		$user_login = '';
	}

	if ( username_exists( $user_login ) )
		$errors['user_login'] = __('<strong>ERROR</strong>: This username is already registered, please choose another one.');

	/* checking the email isn't already used by another user */
	$email_exists = $wpdb->get_row("SELECT user_email FROM $wpdb->users WHERE user_email = '$user_email'");
	if ( $email_exists)
		die (__('<strong>ERROR</strong>: This email address is already registered, please supply another.'));

	if ( 0 == count($errors) ) {
		$password = substr( md5( uniqid( microtime() ) ), 0, 7);

		$user_id = wp_create_user( $user_login, $password, $user_email );
		if ( !$user_id )
			$errors['user_id'] = sprintf(__('<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !'), get_settings('admin_email'));
		else
			wp_new_user_notification($user_id, $password);
	}
	
	if ( 0 == count($errors) ) {
			
	?>

<?php require_once '/home/priddle/movierack-config.php'; ?>
<?php include_once INCLDIR . 'page-header.php'; ?>
<div id="register"> 
	<h2><?php _e('Registration Complete') ?></h2>
	<div style="padding: 10px;">
	<p><?php printf(__('Username: %s'), "<strong>" . wp_specialchars($user_login) . "</strong>") ?><br />
	<?php printf(__('Password: %s'), '<strong>' . __('emailed to you') . '</strong>') ?> <br />
	<?php printf(__('E-mail: %s'), "<strong>" . wp_specialchars($user_email) . "</strong>") ?></p>
	<p class="submit"><a href="login.php"><?php _e('Login'); ?> &raquo;</a></p>
	</div>
</div>

<?php include_once INCLDIR . 'page-footer.php'; ?>
		<?php
		break;
	}

default:

?>
<!--
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Movierack &raquo; <?php _e('Registration Form') ?></title>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_settings('blog_charset'); ?>" />
	<style type="text/css">
	#user_email, #user_login, #submit {
		font-size: 1.7em;
	}
	</style>
</head>

<body>
-->
<?php require_once '/home/priddle/movierack-config.php'; ?>
<?php $page_title = 'Sign Up'; ?>
<?php include_once INCLDIR . 'page-header.php'; ?>
<div id="register">
<h2><?php _e('Sign Up for Movierack') ?></h2>
<div style="padding:10px;">
<?php if ( isset($errors) ) : ?>
<div class="error">
	<ul>
	<?php
	foreach($errors as $error) echo "<li>$error</li>";
	?>
	</ul>
</div>
<?php endif; ?>
<form method="post" action="signup.php" id="registerform">
	<p><input type="hidden" name="action" value="register" />
	<label for="user_login"><?php _e('Username:') ?></label><br /> <input type="text" name="user_login" id="user_login" size="20" maxlength="20" value="<?php echo wp_specialchars($user_login); ?>" /><br /></p>
	<p><label for="user_email"><?php _e('E-mail:') ?></label><br /> <input type="text" name="user_email" id="user_email" size="20" maxlength="100" value="<?php echo wp_specialchars($user_email); ?>" /></p>
	<p><?php _e('A password will be emailed to you.') ?></p>
	<p class="submit"><input type="submit" value="<?php _e('Register') ?> &raquo;" id="submit" name="submit" /></p>
</form>
</div>
</div>
<?php include_once INCLDIR .'page-footer.php'; ?>
<?php

break;

case 'disabled':

	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Movierack &raquo; <?php _e('Registration Currently Disabled') ?></title>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_settings('blog_charset'); ?>" />
</head>

<body>

<div id="login">
	<h1>Movierack</h2>
	<h2><?php _e('Registration Disabled') ?></h2>
	<p><?php _e('User registration is currently not allowed.') ?>
	</p>
</div>

</body>
</html>

	<?php
break;

}
?>
