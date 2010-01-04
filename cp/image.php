<?
include_once "/home/priddle/db_connections/movierack.php";
/*
$file = '/home/priddle/www/movierack-logo.gif';
$handle = fopen( $file, 'r');
$file_content = fread($handle,filesize($file));
fclose($handle);

$encoded = chunk_split(base64_encode($file_content));
//$sql = "INSERT INTO images SET sixfourdata='$encoded'";
*/
$result = @mysql_query("SELECT * FROM images WHERE imgid=1");

if (!$result)
{
echo("Error performing query: " . mysql_error() . "");
exit();
}

while ( $row = mysql_fetch_array($result) )
{
$imgid = $row["imgid"];
$encodeddata = $row["sixfourdata"];
}
//mysql_close( $connection );
header("Content-type: image/gif");
echo base64_decode($encodeddata);


