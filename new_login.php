<?php require_once '/home/priddle/movierack-config.php'; ?>
<?php

/**
case 'logout':
case 'lostpassword':
case 'retrievepassword':
        // redefining user_login ensures we return the right case in the email
case 'resetpass' :
case 'login' : 
**/

$action = $_REQUEST['action'];

switch ( $action ) {
	// kill the login
	case 'logout':
		break;

	// the html for the lost password page
	case 'lostpassword':
		break;

	// create a new random pass "key", update mysql 
	// email user reset pass key
	case 'retrievepassword':
		break;

	// validate "reset pass key"
	case 'resetpass':
		break;

	// Validate user
	case 'login':
		break;

	// the html for the login form
	default:

		break;
}
