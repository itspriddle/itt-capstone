function IsNumeric(sText) {
	var ValidChars = "0123456789.";
	var IsNumber = true;
	var Char;

	for (i = 0; i < sText.length && IsNumber == true; i++)  { 
		Char = sText.charAt(i); 
		if (ValidChars.indexOf(Char) == -1) {
			IsNumber = false;
		}
	}
	return IsNumber;
}


function validate() {
	var $error = '';
	if ( document.getElementById('mtitle').value == '' ) {
		$error = $error + "You must enter a title!\n";
	}
	
	if ( !IsNumeric( document.getElementById('year').value ) ) {
		$error = $error + "Year must be a number!\n";
	}
	
	if ( !IsNumeric( document.getElementById('runtime').value ) ) {
		$error = $error + "Runtime must be a number, in minutes!\n";
	}
	
	if ( document.getElementById('mpaa_rating').value == '' ) {
		$error = $error + "You must enter an MPAA Rating!\n";
	}
	
	if ( !validate_genres() ) {
		$error = $error + "You must select at least 1 genre!\n";
	}
	
	if ( $error == '' ) { 
		return true;
	} else { 
		alert( $error );
		return false;
	}
}

function validate_genres() {
	var $genres = 0;

	for ( $i = 0; $i < document.movie_details.genres.length; $i++ ) {
		if ( document.movie_details.genres[$i].checked == true ) {
			$genres++;
		}
	}
	
	if ( $genres > 0 ) {
		return true;
	} else {
		return false;
	}
}
