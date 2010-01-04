function show_advanced_search() {
	$advancedDiv = document.getElementById('advanced-search');
	$vis      	 = $advancedDiv.style.display;
	if ( $vis == 'block' ) {
		$advancedDiv.style.display = 'none';
	} else {
		$advancedDiv.style.display = 'block';
	}
}


var http_request = false;
var alertDiv;
	function doAJAX( action ) {
		switch( action ) {
			case 'login':
				url = 'login.php';
				poststr = "log=" + encodeURI( document.getElementById("log").value ) +
						  "&pwd=" + encodeURI( document.getElementById("pwd").value ) +
						  ( ( document.getElementById('rememberme').checked == true ) ? 
							'&rememberme=forever' : '' );
				alertDiv = action;
				break;
			case 'lostpass':
				url = 'login.php';
				poststr = "action=retrievepassword&user_login=" + encodeURI( document.getElementById("user_login").value ) +
						  "&email="+ encodeURI( document.getElementById("email").value );
				alertDiv = 'login';
				break;

			default: 
				break;
		}
		makePOSTRequest(url, poststr);
	}

function makePOSTRequest(url, parameters) {

//	switch( url ) {
//		case 'login.php':
//			action = 'login';
//			break;
//	}	
	
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
			document.getElementById( alertDiv ).innerHTML = result;
		} else {
			alert('There was a problem with the request.'+ http_request.status);
		}
	}
}





