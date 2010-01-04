<?php require_once '/home/priddle/movierack-config.php'; ?>
<div id="login">
<!--<script language="javascript" type="text/javascript">

var http_request = false;
   function makePOSTRequest(url, parameters) {
      http_request = false;
      if (window.XMLHttpRequest) { // Mozilla, Safari,...
         http_request = new XMLHttpRequest();
         if (http_request.overrideMimeType) {
            // set type accordingly to anticipated content type
            //http_request.overrideMimeType('text/xml');
            http_request.overrideMimeType('text/html');
         }
      } else if (window.ActiveXObject) { // IE
         try {
            http_request = new ActiveXObject("Msxml2.XMLHTTP");
         } catch (e) {
            try {
               http_request = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {}
         }
      }
      if (!http_request) {
         alert('Cannot create XMLHTTP instance');
         return false;
      }

      http_request.onreadystatechange = alertContents;
      http_request.open('POST', url, true);
      http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      http_request.setRequestHeader("Content-length", parameters.length);
      http_request.setRequestHeader("Connection", "close");
      http_request.send(parameters);
   }
   function alertContents() {
      if (http_request.readyState == 4) {
         if (http_request.status == 200) {
            //alert(http_request.responseText);
            result = http_request.responseText;
            document.getElementById('login').innerHTML = result;
         } else {
            alert('There was a problem with the request.'+ http_request.status);
         }
      }
   }

   function get(obj) {
      var poststr = "log=" + encodeURI( document.getElementById("log").value ) +
                    "&pwd=" + encodeURI( document.getElementById("pwd").value ) +
					( ( document.getElementById('rememberme').checked == true ) ? '&rememberme=forever' : '' ) ;
      makePOSTRequest('login.php', poststr);
   }

</script>-->
<?php 
require_once WPADMIN .'wp-config.php';

function mmr_loggedin() {
    // Checks if a user is logged in
    if ( (!empty($_COOKIE[USER_COOKIE]) &&
                !wp_login($_COOKIE[USER_COOKIE], $_COOKIE[PASS_COOKIE], true)) ||
             (empty($_COOKIE[USER_COOKIE])) ) {
        nocache_headers();

        //wp_redirect(get_settings('siteurl') . '/wp-login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
        return false;
        exit();
    } else {
        return true;
        exit;
    }
}
if ( mr_loggedin() ) { ?>

<?php include_once INCLDIR. 'profile.php'; ?>

<?php } elseif ( $_GET['action'] == 'lostpassword' ) { ?>
<h2>Movierack Login</h2>
<p><?php _e('Please enter your information here. We will send you a new password.') ?></p>
<?php
if ($error)
    echo "<div id='login_error'>$error</div>";
?>

<form name="lostpass" action="javascript:doAJAX('lostpass');" method="post" id="lostpass">
<p>
<input type="hidden" name="action" value="retrievepassword" />
<label><?php _e('Username:') ?><br />
<input type="text" name="user_login" id="user_login" value="" size="20" tabindex="1" /></label></p>
<p><label><?php _e('E-mail:') ?><br />
<input type="text" name="email" id="email" value="" size="20" tabindex="2" /></label><br />
</p>
<p class="submit"><input type="submit" name="submit" id="submit" value="<?php _e('Retrieve Password'); ?> &raquo;" tabindex="3" onclick=" " /></p>
</form>
<?php } else { ?>
    <h2>Members Login</h2>
	<form name="loginform" id="loginform" action="javascript:doAJAX('login')" method="post">
		<p><label>Username:<br /><input type="text" name="log" id="log" value="" size="20" tabindex="1" /></label></p>
		<p><label>Password:<br /> <input type="password" name="pwd" id="pwd" value="" size="20" tabindex="2" /></label></p>
		<p>
		  <label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="3" /> 
		  Remember me</label></p>
		<p class="submit">
			<input type="submit" name="submit" id="submit" value="Login &raquo;" tabindex="4" onclick=" " />

		</p>
		<!--<p><a href="/login.php?action=lostpassword" title="Password Lost and Found">Lost your password?</a></p>-->
		<p><a href="?action=lostpassword" title="Password Lost and Found">Lost your password?</a></p>
	</form>

<?php } ?>
</div>
