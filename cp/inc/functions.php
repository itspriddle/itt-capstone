<?php
function formatSQL( $value, $like = NULL ){
/*************************************************************************
 * format vars for insertion into mysql
 * add %'s when needed for LIKE clauses
 */
   // Stripslashes
   if ( get_magic_quotes_gpc() ) {
       $value = stripslashes( $value );
   }

   // Quote if not a number or a numeric string
   if ( !is_numeric( $value ) ) {
       $value = "'". ( $like ? '%' : '' ) . mysql_real_escape_string( $value ) . ( $like ? '%' : '' ) ."'";
   }

   return $value;
}
