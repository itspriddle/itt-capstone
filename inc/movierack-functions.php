<?php
/**
 * Functions used for movierack.net
 */

require_once '/home/priddle/movierack-config.php';

function format_sql( $value, $like = NULL ){
/**    
 * format vars for insertion into mysql
 * add %'s when needed for LIKE clauses
 */ 
	// Stripslashes
	if ( get_magic_quotes_gpc() ) {
		$value = stripslashes( $value );
	}

	// Quote if not a number or a numeric string,
	// add % if using LIKE
	if ( !is_numeric( $value ) ) {
		$value = "'". ( $like ? '%' : '' ) . mysql_real_escape_string( $value ) . ( $like ? '%' : '' ) ."'";
	}

	return $value;
}


function get_news_feed( $feed = NULL, $display = 6 ) {
/**
 * Get news feed for home page
 */

require_once INCLDIR .'magpierss/rss_fetch.inc';

if ( $feed == NULL ) $feed = 'all';

switch( $feed ) {
	case 'news':
		$url = 'http://admin.movierack.net/category/movierack/news/feed';
		break;
	case 'reviews':
		$url = 'http://admin.movierack.net/category/movierack/reviews/feed';
		break;
	case 'all':
	default:
		$url = 'http://admin.movierack.net/category/movierack/feed';
		break;
}
$rss = fetch_rss( $url );
$i = 0;
foreach ( $rss->items as $item ) {
    if ( $i >= $display ) break; // print the last 6 articles
    //if ( isset( $_GET['id'] ) && ( substr( substr( $item['link'], 68 ), 0, ( strlen( substr( $item['link'], 68 ) ) - 1 )  ) ) != $_GET['id'] ) continue;
    $pubdate = explode( ' ', $item['pubdate'] );
    $pubtime = explode( ':', $pubdate[4] );

    $thetime = $pubtime[0] .':' . $pubtime[1] .' '. $pubdate[5];
/*
    $html = "<div class=\"content\">\n".
        "  <div class=\"head\"></div>\n".
        "  <div class=\"body\">\n".
        "    <p class=\"header\">{$item['title']}</p>\n".
        "    <div class=\"content-main\">\n".
        "      {$item['content']['encoded']}\n".
        "    </div>\n".
        "    <p class=\"footer\">Posted by: {$item['dc']['creator']} on {$pubdate[2]} {$pubdate[1]}, {$pubdate[3]} @ $thetime </p>\n".
        "  </div>\n".
        "  <div class=\"bottom\"></div>\n".
        "</div>\n";
*/
	$html .= "<div class=\"news-item\">\n".
			"  <h3>{$item['title']}</h3>\n".
			"  <div class=\"content\">\n{$item['content']['encoded']}  </div>\n".
			"  <div class=\"footer\">\n    <p>Posted by: {$item['dc']['creator']} on {$pubdate[2]} {$pubdate[1]}, {$pubdate[3]} @ $thetime </p>\n  </div>\n".
			"</div>\n".
			"<hr />\n";
    $i++;
}
    echo substr( $html, 0, -7 );

}

function browse_navigation( $sql, $perpage, $start, $filter = null ) {
/**
 * create navigation links for browsing movies
 */
	require_once INCLDIR .'pagination.php';
    $paginate = new pagination( $sql, ( isset( $perpage ) ? $perpage : 8 ) , ( isset( $start ) ? $start : 0 ), 4, $delimiter = ' | ', $filter );
    return $paginate->output();
}

function print_movies( $movies, $heading_title = NULL ) {
/**
 * create html for movie tables
 */
require_once INCLDIR .'ratings/_drawrating.php'; 

    $html = "<div id=\"movie-table-cont\">\n";
	if ( $heading_title != NULL ) $html .= "<h3><span class=\"hidden\">$heading_title</span></h3>\n";
    $html .= "<table id=\"movietbl\">\n";
	$cols = 5;
    for ( $i = 0; $i < ceil(  count( $movies ) / $cols ); $i++ ) {
        $html .= "  <tr>\n";
        for ( $j = 0; $j < $cols; $j++ ) {
			$v = ( ( $cols * $i ) + $j );
	        $html .= '    <td>';
	        if ( is_array( $movies[ $v ] ) ) { 
	            $html .='<a href="/movies/'.$movies[ $v ]['movie_id'].'" '.
	                    ' onmouseover="ajax_showTooltip(\'/movie.php?action=do_popup&amp;m='.$movies[ $v ]['movie_id'].'\',this); return false"'.
	                    ' onmouseout="ajax_hideTooltip()" class="tooltip">'. 
						print_movie_cover( $movies[ $v ]['movie_id'] ) ."<br />{$movies[ $v ]['title']}</a><br />";
				$html .= rating_bar( $movies[ $v ]['movie_id'], 5 );

				
			}
			$html .= "</td>\n";
		}
	    $html .= "  </tr>\n";
	}
	$html .="</table>\n".
	        "<div style=\"clear: both\">&nbsp;</div>\n".
	        "</div>\n";
	
    return $html;
}

function browse_movies( $perpage, $start, $sql = NULL, $filter = NULL ) {
/**
 * create html for browsing movies
 */

	if ( !isset( $sql ) || $sql == NULL ) {
		$cust_sql = FALSE;
	    $sql =  "SELECT ".
				" movie_id, ".
				" imdb_id, ".
				" title ".
				"FROM ".
				" mr_movie_details AS md ".
				"WHERE date_updated IS NOT NULL ".
				"ORDER BY title ";
	}
	//echo $sql;
	$sql_limit = "LIMIT ".
 				 ( $start > 1 ? "". ( ( $start * $perpage ) - $perpage ). ", " : '' ). " $perpage ";
	//echo $sql . $sql_limit;

	$res = mysql_query( $sql . $sql_limit );
	$total = mysql_num_rows( $res );
	if ( $total > 0 ) {
		$c = 0;
		while( $r = mysql_fetch_array( $res ) ) {
			$movies[$c]['movie_id'] 	= $r['movie_id'];
			$movies[$c]['imdb_id']		= $r['imdb_id'];
			$movies[$c]['title']		= $r['title'];
			$c++;
		}
/*
		echo $movies[0]['movie_id'].'|';
		echo $movies[1]['movie_id'].'|';
		echo $movies[2]['movie_id'].'|';
		echo count( $movies );
*/
		$html 		= print_movies( $movies );
		$browse_nav = browse_navigation( $sql, $perpage, $start, (( strlen( $filter ) == 1 ) ? $filter  : 'NULL' ) );
		$html      .= '<div id="browselinks">'. $browse_nav[1] ."</div>\n";
	} else { // no movies to browse

	}

	return $html;

}

function search_movies( $search_term, $search_for = array(), $genres = array() ) {
/**
 * build the query to search for movies
 */

	$sql = "SELECT ";

	return $sql;
}
function get_movie_genres( $mid ) {
/**
 * get an array of genre ids for a movie
 */
    $sql = "SELECT genre_id FROM mr_movie_genres WHERE movie_id = $mid";
    $res = mysql_query( $sql );
    if ( $res ) {
        while ( $r = mysql_fetch_array( $res ) ) {
            $genreIDs[] = $r['genre_id'];
        }

        foreach ( $genreIDs as $g ) {
            $sql2 = "SELECT genre_name FROM genres WHERE genre_id = $g";
            $res2 = mysql_query( $sql2 );

            if ( $res2 ) {
                while ( $r = mysql_fetch_array( $res2 ) ) {
                    $movieGenres[] = $r['genre_name'];
                }
            }
        }
        return $movieGenres;
    }
}


function show_filter_genres( $genres = array() ) {
/**
 * the html for the advanced search genre table
 */
    $sql = "SELECT * FROM mr_genres ORDER BY genre_name";
    $res = mysql_query( $sql );

    while ( $r = mysql_fetch_array( $res ) ) {
        $data[] = "<label><input type=\"checkbox\" name=\"genres[]\" value=\"{$r['genre_id']}\" ".
                ( ( @in_array( $r['genre_id'], $genres ) || count( $genres ) == 0 ) ? ' checked="checked"' : '' ).
                " /> {$r['genre_name']}</label>";
    }

    $html = "      <table id=\"filter-genres\">\n";

    for ( $i = 0; $i < ceil( count( $data ) / 3 ); $i++ ) {
        $html .= "        <tr>\n";
        for ( $j = 0; $j < 3; $j++ ) {
            $html .= "          <td>{$data[ ( $i * 3 ) + $j ]}</td>\n";
        }

        $html .= "        </tr>\n";
    }
    $html .= "      </table>\n";
    return $html;
}



function get_movie_cover( $imdbid ) {
/**
 * download the movie cover for a title from
 * imdb.com.  return true or false on success/failure
 */
	require_once INCLDIR . 'imdb/imdb.class.php'; 
    $movie = new imdb( $imdbid );
    $movieid = $imdbid;
    $movie->setid( $movieid );

    if ( ( $photo_url = $movie->photo_localurl()) != FALSE ) {
        return TRUE;
    } else {
        return FALSE;
    }
}



function mr_loggedin() {
/**
 * check if a user has logged in (modified wordpress function)
 */
    // Checks if a user is logged in
    if ( (!empty($_COOKIE[USER_COOKIE]) &&
                !wp_login($_COOKIE[USER_COOKIE], $_COOKIE[PASS_COOKIE], true)) ||
             (empty($_COOKIE[USER_COOKIE])) ) {

        return false;
        exit();
    } else {
        return true;
        exit;
    }
}

function create_download_link( $file ) {
/**
 * creates the URL to download a particular movie
 *
 * creates an MD5 hash of the string "filename.extYYYY-MM-DDusernamePASSWORDHASHxxx.xxx.xxx.xxx"
 * that is: filename, date, username, password (in md5 format), user's ip address
 *
 * this makes it such that a download link or "key" is:
 *	- different for every movie
 *  - different for every user
 *  - different every day
 *  - different per IP address
 *	- different if password is changed
 *
 * this prevents people from sharing links, or posting them on the internet,
 * as the challenge auth on the media server would fail.  
 */
    if ( !mr_loggedin() ) return NULL;
    foreach( wp_get_current_user() as $u => $k ) {
        if( $u == 'user_login' ) {
            $userName = $k;  
        } elseif ( $u == 'user_pass' ) {
            $userPass = $k;
        } 
    }
    $link = 'http://media.movierack.net/movies/'. md5( $file . date('Y-m-d') . $userName . $userPass . $_SERVER['REMOTE_ADDR'] );
    return $link;
}

function print_movie_cover( $movie_id ) {
	$sql = "SELECT imdb_id, title FROM mr_movie_details WHERE movie_id = $movie_id LIMIT 1";
	$res = mysql_query( $sql );
	if ( mysql_num_rows( $res ) != 1 ) {
		return NULL;
		exit;
	}
	while ( $r = mysql_fetch_array( $res ) ) {
		$imdb_id = $r['imdb_id'];
		$title = $r['title'];
	}

	if ( !file_exists( WWWROOT ."/images/movies/$imdb_id.jpg" ) ) {
		get_movie_cover( $imdb_id );
	}

	list($width, $height) = @getimagesize( "/home/priddle/www/images/movies/$imdb_id.jpg" );
	return '<img src="/images/movies/'. $imdb_id .'.jpg" width="'. $width .'" height="'. $height .'" alt="'. $title .'" />';

}

function new_movie_feed( $start = 0, $display = 3 ) {
/**
 * print new movies for the homepage feed.
 */

    $sqlLimit = "$start, $display";
    $sql = "SELECT title, year, movie_id, imdb_id, tagline, filename FROM mr_movie_details AS md ".
           " WHERE DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= md.date_added AND date_updated IS NOT NULL ";
	$resALL = mysql_query( $sql );
    $res = mysql_query( $sql. " LIMIT $sqlLimit " );
    while( $r = mysql_fetch_array( $res ) ) {
        $html .= "<h3>". ( ( strlen( $r['title'] ) > 45 ) ? substr( $r['title'], 0, 45 ) .'...' : $r['title'] ) ." ({$r['year']})</h3>\n".
                 "<p>{$r['tagline']}</p>\n".
				 '<p><a href="/movies/'. $r['movie_id'] ."\">More info</a></p>\n".
				 //( ( mr_loggedin() ) ? '<p><a href="'. create_download_link( $r['filename'] ) ."\">Download Now!</a></p>\n" : '' ).
				 "<hr />\n";
    }
//	$html = substr( $html, 0, -7 );
	$html .= '<div class="mini-nav">'.
			 '<a href="javascript:void(0)" class="prev" onclick="javascript:mr_movie_feed('. 
			 ( ( ( $start - $display ) > 0 ) ? ( $start - $display ): '0' ) .
			 ')">Previous</a>  '.
			 '<a href="javascript:void(0)" class="next" onclick="javascript:mr_movie_feed('. 
			 ( ( ( $start + $display ) < mysql_num_rows( $resALL ) ) ? ( $start + $display ): "$start" ) .
			 ')">Next</a></div>'."\n<br style=\"clear: both\" />";
    return $html;
}

function new_news_feed( $start = 0, $display = 2 ) {
/**
 * print new movies for the homepage feed.
 */

    $sqlLimit = "$start, $display";
    $sql = "SELECT title, year, movie_id, imdb_id, tagline FROM mr_movie_details AS md ".
           " WHERE DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= md.date_added AND date_updated IS NOT NULL ";
    $resALL = mysql_query( $sql );
    $res = mysql_query( $sql. " LIMIT $sqlLimit " );
    while( $r = mysql_fetch_array( $res ) ) {
        $html .= "<h3>{$r['title']} ({$r['year']})</h3>\n".
                 "<p>{$r['tagline']}</p>\n".
                 "<hr />\n";
    }
//  $html = substr( $html, 0, -7 );
    $html .= '<div class="mini-nav">'.
             '<a href="javascript:void(0)" onclick="javascript:mr_movie_feed('.
             ( ( ( $start - $display ) > 0 ) ? ( $start - $display ): '0' ) .
             ')">Previous</a>  '.
             '<a href="javascript:void(0)" onclick="javascript:mr_movie_feed('.
             ( ( ( $start + $display ) < mysql_num_rows( $resALL ) ) ? ( $start + $display ): "$start" ) .
             ')">Next</a></div>'."\n";
    return $html;
}
