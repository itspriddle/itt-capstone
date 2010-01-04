<?php 
//vars 
$file_name = "online.dat.php"; 
$c_time = time(); 
$timeout = 300; 
$time = $c_time - $timeout; 
$ip = getenv("REMOTE_ADDR"); 

if ( !file_exists( $file_name ) ){ 
	$fp = fopen( $file_name, "w" ); 
	fwrite( $fp, "<?php die('Restricted File');?> \n" ); 
	fclose( $fp ); 
} 

//write to file 
$fp = fopen( $file_name, "a" ); 
$write = $ip ."||". $c_time ."\n"; 
fwrite( $fp, $write ); 
fclose( $fp ); 

//open file to as array, to count online 
$file_array = file( $file_name ); 
$online_array = array(); 
for( $x = 1; $x < count( $file_array ); $x++ ){ 
	list( $ip, $ip_time ) = explode( "||", $file_array[$x] ); 
	if( $ip_time >= $time ){ 
		array_push( $online_array, $ip ); 
	} 
}//end for 

$online = array_unique( $online_array ); 
$online = count( $online ); 

echo 'User'. ( ( $online > 1 ) ? 's' : '' ) . " online: $online";

?> 
